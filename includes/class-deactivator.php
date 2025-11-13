<?php
/**
 * Plugin Deactivation Handler
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
 * Handles plugin deactivation.
 *
 * @since 2.0.0
 */
class Deactivator
{
    /**
     * Plugin deactivation tasks.
     *
     * @since 2.0.0
     * @return void
     */
    public static function deactivate(): void
    {
        // Flush rewrite rules.
        flush_rewrite_rules();

        // Clear scheduled events if any.
        wp_clear_scheduled_hook('condoleance_register_cleanup');

        // Clear transients.
        self::clear_transients();
    }

    /**
     * Clear all plugin transients.
     *
     * @since 2.0.0
     * @return void
     */
    private static function clear_transients(): void
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_condoleance_register_') . '%'
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_timeout_condoleance_register_') . '%'
            )
        );
    }
}
