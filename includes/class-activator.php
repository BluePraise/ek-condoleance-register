<?php
/**
 * Plugin Activation Handler
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

namespace CondoleanceRegister;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin activation.
 *
 * @since 2.0.0
 */
class Activator
{
    /**
     * Plugin activation tasks.
     *
     * @since 2.0.0
     * @return void
     */
    public static function activate(): void
    {
        // Check if WordPress version is compatible.
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            wp_die(
                esc_html__('Condoleance Register requires WordPress 6.0 or higher.', 'condoleance-register'),
                esc_html__('Plugin Activation Error', 'condoleance-register'),
                ['back_link' => true]
            );
        }

        // Register post type for flush_rewrite_rules().
        require_once CONDOLEANCE_REGISTER_PATH . 'includes/post-types/class-condoleance.php';
        $condoleance = new PostTypes\Condoleance();
        $condoleance->register();

        // Flush rewrite rules.
        flush_rewrite_rules();

        // Set default options.
        self::set_default_options();

        // Check for old Tahlil plugin data.
        self::check_migration_needed();

        // Create custom database tables.
        self::create_tables();

        // Set activation timestamp.
        update_option('condoleance_register_activated', time());
        update_option('condoleance_register_version', CONDOLEANCE_REGISTER_VERSION);
    }

    /**
     * Create custom database tables.
     *
     * @since 2.1.0
     * @return void
     */
    public static function create_tables(): void
    {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'condoleance_candles';
        $charset_collate = $wpdb->get_charset_collate();

        // Drop old UNIQUE constraint before dbDelta so anonymous one-off tokens
        // can be inserted multiple times (deduplication is handled in PHP).
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $unique_exists = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.statistics
                 WHERE table_schema = %s AND table_name = %s AND index_name = 'session_post' AND non_unique = 0",
                DB_NAME,
                $table_name
            )
        );
        if ($unique_exists) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->query("ALTER TABLE {$table_name} DROP INDEX session_post");
        }

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            session_token varchar(64) NOT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            anonymous tinyint(1) NOT NULL DEFAULT 0,
            ip_address varchar(45) NOT NULL DEFAULT '',
            lit_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY session_post (session_token, post_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Migrate existing post meta candle data into wp_condoleance_candles.
     *
     * Reads from two sources:
     *   1. condoleance_candles_data (v2.0 plugin) — keys: count, users[]{name, anonymous, date}
     *   2. cmb_condalances_candles  (Tahlil plugin) — keys: count, authors[]{candle_name, candle_date}
     *
     * Only inserts rows that have real user data. The gap between the stored count
     * and the number of tracked users is saved as condoleance_candle_legacy_count
     * so the total display remains accurate without fabricating anonymous rows.
     *
     * Safe to run multiple times — posts that already have rows skip inserts but
     * always get their legacy_count recalculated from the actual table state,
     * which corrects any stale or doubled values from previous migration runs.
     *
     * @since 2.1.1
     * @return void
     */
    public static function migrate_post_meta_candles(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'condoleance_candles';

        // Process all published condoleance posts — not just empty ones.
        // Posts that already have rows skip the INSERT phase but still get
        // their legacy_count corrected based on the actual table state.
        $post_ids = $wpdb->get_col(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             WHERE p.post_type = 'condoleance'
               AND p.post_status = 'publish'"
        );

        if (empty($post_ids)) {
            return;
        }

        foreach ($post_ids as $raw_id) {
            $post_id     = (int) $raw_id;
            $table_count = (int) $wpdb->get_var(
                $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id )
            );

            // Determine historical total from v2.0 meta or Tahlil meta.
            $v2_meta = get_post_meta($post_id, 'condoleance_candles_data', true);

            if (is_array($v2_meta) && !empty($v2_meta['count'])) {
                $total = (int) $v2_meta['count'];
                $users = is_array($v2_meta['users'] ?? null) ? $v2_meta['users'] : [];
                $source = 'v2';
            } else {
                $tahlil_meta = get_post_meta($post_id, 'cmb_condalances_candles', true);
                $tahlil_meta = is_array($tahlil_meta) ? $tahlil_meta : [];
                $total = (int) ($tahlil_meta['count'] ?? 0);
                $users = is_array($tahlil_meta['authors'] ?? null) ? $tahlil_meta['authors'] : [];
                $source = 'tahlil';
            }

            if ($total === 0) {
                continue;
            }

            // If the table has more rows than the historical total the migration ran
            // multiple times and created duplicates. Clean up the historical rows
            // (ip_address = '') for this post and re-insert from scratch.
            // Rows with a real IP were lit by actual visitors and are never touched.
            if ($table_count > $total) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$table} WHERE post_id = %d AND ip_address = ''",
                        $post_id
                    )
                );
                $table_count = (int) $wpdb->get_var(
                    $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id )
                );
            }

            // Only insert rows for posts that have none yet.
            if ($table_count === 0) {
                if ($source === 'v2') {
                    foreach ($users as $user) {
                        $name = $user['name'] ?? '';
                        if ($name === '' && empty($user['anonymous'])) {
                            // Skip empty-name non-anonymous entries (corrupt data).
                            continue;
                        }
                        $wpdb->insert(
                            $table,
                            [
                                'post_id'       => $post_id,
                                'session_token' => bin2hex(random_bytes(32)),
                                'name'          => sanitize_text_field($name),
                                'anonymous'     => (int) ($user['anonymous'] ?? false),
                                'ip_address'    => '',
                                'lit_at'        => self::parse_legacy_date($user['date'] ?? ''),
                            ],
                            ['%d', '%s', '%s', '%d', '%s', '%s']
                        );
                    }
                } else {
                    // Tahlil only tracked named candles; anonymous ones are
                    // accounted for via legacy_count below.
                    foreach ($users as $user) {
                        $name = $user['candle_name'] ?? '';
                        if ($name === '') {
                            continue;
                        }
                        $wpdb->insert(
                            $table,
                            [
                                'post_id'       => $post_id,
                                'session_token' => bin2hex(random_bytes(32)),
                                'name'          => sanitize_text_field($name),
                                'anonymous'     => 0,
                                'ip_address'    => '',
                                'lit_at'        => self::parse_legacy_date($user['candle_date'] ?? ''),
                            ],
                            ['%d', '%s', '%s', '%d', '%s', '%s']
                        );
                    }
                }

                // Refresh count after inserts.
                $table_count = (int) $wpdb->get_var(
                    $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id )
                );
            }

            // Always recalculate legacy_count from the actual table state so that
            // stale or incorrect values (including the doubling bug) are corrected.
            $legacy = max(0, $total - $table_count);
            if ($legacy > 0) {
                update_post_meta($post_id, 'condoleance_candle_legacy_count', $legacy);
            } else {
                delete_post_meta($post_id, 'condoleance_candle_legacy_count');
            }
        }
    }

    /**
     * Parse a legacy date string from the old Tahlil/candles post meta format.
     *
     * Handles the following formats:
     *   - MySQL datetime: "Y-m-d H:i:s" (passthrough)
     *   - Dutch/EU:       "d/m/Y at H:i" or "d-m-Y at H:i" (single-digit parts ok)
     *   - American:       "m/d/Y at H:i" or "m/d/Y at H:i AM/PM"
     *
     * Returns a MySQL datetime string, falling back to now on failure.
     *
     * @since 2.1.2
     * @param string $date Raw date string from old meta.
     * @return string MySQL datetime (Y-m-d H:i:s).
     */
    private static function parse_legacy_date(string $date): string
    {
        $date = trim($date);

        if (empty($date)) {
            return current_time('mysql');
        }

        // Already a valid MySQL datetime.
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $date)) {
            return $date;
        }

        // "d/m/Y at H:i [AM|PM]" or "d-m-Y at H:i [AM|PM]" with optional single-digit parts.
        // e.g. "07/04/2020 at 12:32", "1-11-2020 at 17:7", "03/02/2021 at 10:7", "2/14/2024 at 8:41 AM"
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4}) at (\d{1,2}):(\d{1,2})(?:\s*(AM|PM))?$#i', $date, $m)) {
            $day   = (int) $m[1];
            $month = (int) $m[2];
            $year  = (int) $m[3];
            $hour  = (int) $m[4];
            $min   = (int) $m[5];
            $ampm  = isset($m[6]) ? strtoupper($m[6]) : '';

            // AM/PM adjustment.
            if ($ampm === 'PM' && $hour < 12) {
                $hour += 12;
            } elseif ($ampm === 'AM' && $hour === 12) {
                $hour = 0;
            }

            // Detect American M/D/Y: if first component > 12 it must be the day (d/m/Y).
            // If second component > 12, it must be the day, so first is the month (m/d/Y) — swap.
            if ($month > 12 && $day <= 12) {
                [$day, $month] = [$month, $day];
            }

            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return sprintf('%04d-%02d-%02d %02d:%02d:00', $year, $month, $day, $hour, $min);
            }
        }

        // Last resort: let PHP try.
        $ts = strtotime($date);
        if ($ts !== false && $ts > 0) {
            return gmdate('Y-m-d H:i:s', $ts);
        }

        return current_time('mysql');
    }

    /**
     * Set default plugin options.
     *
     * @since 2.0.0
     * @return void
     */
    private static function set_default_options(): void
    {
        $defaults = [
            'enable_candles' => true,
            'enable_photos' => true,
            'enable_videos' => true,
            'enable_music' => true,
            'comments_per_page' => 10,
            'show_birth_date' => true,
            'show_death_date' => true,
            'youtube_api_key' => '',
        ];

        foreach ($defaults as $key => $value) {
            $option_key = 'condoleance_register_' . $key;
            if (false === get_option($option_key)) {
                add_option($option_key, $value);
            }
        }
    }

    /**
     * Check if migration from Tahlil plugin is needed.
     *
     * @since 2.0.0
     * @return void
     */
    private static function check_migration_needed(): void
    {
        global $wpdb;

        // Check if old cpt_condolances posts exist.
        $old_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'cpt_condolances'"
        );

        if ($old_posts > 0) {
            // Flag that migration is needed.
            update_option('condoleance_register_migration_needed', true);
            update_option('condoleance_register_migration_count', (int) $old_posts);

            // Add admin notice.
            add_option('condoleance_register_show_migration_notice', true);
        }
    }
}
