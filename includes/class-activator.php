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
