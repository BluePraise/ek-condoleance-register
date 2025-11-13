<?php
/**
 * Admin functionality handler
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

namespace CondoleanceRegister\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles admin-specific functionality.
 *
 * @since 2.0.0
 */
class Admin
{
    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_action('admin_notices', [$this, 'show_migration_notice']);
        add_action('admin_init', [$this, 'handle_migration_action']);
        add_action('admin_menu', [$this, 'add_settings_page'], 20);
    }

    /**
     * Show migration notice if needed.
     *
     * @since 2.0.0
     * @return void
     */
    public function show_migration_notice(): void
    {
        if (!get_option('condoleance_register_show_migration_notice')) {
            return;
        }

        $count = get_option('condoleance_register_migration_count', 0);
        $migration_url = wp_nonce_url(
            admin_url('admin.php?page=condoleance-register-settings&action=migrate'),
            'condoleance_migrate',
            'condoleance_migrate_nonce'
        );

        printf(
            '<div class="notice notice-warning is-dismissible">
                <p><strong>%s</strong></p>
                <p>%s</p>
                <p><a href="%s" class="button button-primary">%s</a> <a href="%s" class="button">%s</a></p>
            </div>',
            esc_html__('Condoleance Register - Data Migration Available', 'condoleance-register'),
            sprintf(
                /* translators: %d: number of old condoleance posts */
                esc_html(_n(
                    'We found %d condoleance from the old Tahlil plugin. Would you like to migrate it?',
                    'We found %d condoleances from the old Tahlil plugin. Would you like to migrate them?',
                    $count,
                    'condoleance-register'
                )),
                (int) $count
            ),
            esc_url($migration_url),
            esc_html__('Migrate Now', 'condoleance-register'),
            esc_url(add_query_arg('condoleance_dismiss_migration', '1')),
            esc_html__('Dismiss', 'condoleance-register')
        );
    }

    /**
     * Handle migration and dismiss actions.
     *
     * @since 2.0.0
     * @return void
     */
    public function handle_migration_action(): void
    {
        // Handle dismiss.
        if (isset($_GET['condoleance_dismiss_migration'])) {
            delete_option('condoleance_register_show_migration_notice');
            wp_safe_redirect(admin_url('edit.php?post_type=condoleance'));
            exit;
        }

        // Handle migration.
        if (isset($_GET['action']) && 'migrate' === $_GET['action']) {
            if (!isset($_GET['condoleance_migrate_nonce']) ||
                !wp_verify_nonce($_GET['condoleance_migrate_nonce'], 'condoleance_migrate')) {
                wp_die(esc_html__('Security check failed.', 'condoleance-register'));
            }

            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have permission to perform this action.', 'condoleance-register'));
            }

            // Run migration.
            $migrator = new Migrator();
            $result = $migrator->migrate();

            if ($result['success']) {
                delete_option('condoleance_register_show_migration_notice');
                delete_option('condoleance_register_migration_needed');

                add_settings_error(
                    'condoleance_register_migration',
                    'migration_success',
                    sprintf(
                        /* translators: %d: number of migrated posts */
                        esc_html__('Successfully migrated %d condoleances!', 'condoleance-register'),
                        $result['migrated']
                    ),
                    'success'
                );
            } else {
                add_settings_error(
                    'condoleance_register_migration',
                    'migration_error',
                    esc_html__('Migration failed. Please check error logs.', 'condoleance-register'),
                    'error'
                );
            }

            set_transient('condoleance_register_admin_notices', get_settings_errors(), 30);
            wp_safe_redirect(admin_url('edit.php?post_type=condoleance'));
            exit;
        }
    }

    /**
     * Add settings page.
     *
     * @since 2.0.0
     * @return void
     */
    public function add_settings_page(): void
    {
        add_submenu_page(
            'edit.php?post_type=condoleance',
            __('Settings', 'condoleance-register'),
            __('Settings', 'condoleance-register'),
            'manage_options',
            'condoleance-register-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Render settings page.
     *
     * @since 2.0.0
     * @return void
     */
    public function render_settings_page(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php settings_errors('condoleance_register_migration'); ?>
            <p><?php esc_html_e('Settings page coming soon...', 'condoleance-register'); ?></p>
        </div>
        <?php
    }
}
