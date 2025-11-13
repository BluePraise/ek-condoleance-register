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

        // Set activation timestamp.
        update_option('condoleance_register_activated', time());
        update_option('condoleance_register_version', CONDOLEANCE_REGISTER_VERSION);
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
