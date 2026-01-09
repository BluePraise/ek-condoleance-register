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
        add_action('admin_init', [$this, 'register_settings']);
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
     * Register plugin settings.
     *
     * @since 2.0.0
     * @return void
     */
    public function register_settings(): void
    {
        register_setting('condoleance_register_settings', 'condoleance_enable_archive', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting('condoleance_register_settings', 'condoleance_show_search_on_archive', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting('condoleance_register_settings', 'condoleance_archive_per_page', [
            'type' => 'integer',
            'default' => 10,
            'sanitize_callback' => 'absint',
        ]);

        add_settings_section(
            'condoleance_archive_settings',
            __('Archive Settings', 'condoleance-register'),
            [$this, 'render_archive_section'],
            'condoleance-register-settings'
        );

        add_settings_field(
            'condoleance_enable_archive',
            __('Enable Archive Page', 'condoleance-register'),
            [$this, 'render_enable_archive_field'],
            'condoleance-register-settings',
            'condoleance_archive_settings'
        );

        add_settings_field(
            'condoleance_show_search_on_archive',
            __('Show Search on Archive', 'condoleance-register'),
            [$this, 'render_show_search_field'],
            'condoleance-register-settings',
            'condoleance_archive_settings'
        );

        add_settings_field(
            'condoleance_archive_per_page',
            __('Items Per Page', 'condoleance-register'),
            [$this, 'render_per_page_field'],
            'condoleance-register-settings',
            'condoleance_archive_settings'
        );
    }

    /**
     * Render archive settings section description.
     *
     * @since 2.0.0
     * @return void
     */
    public function render_archive_section(): void
    {
        echo '<p>' . esc_html__('Configure how the condoleance archive page works.', 'condoleance-register') . '</p>';
    }

    /**
     * Render enable archive field.
     *
     * @since 2.0.0
     * @return void
     */
    public function render_enable_archive_field(): void
    {
        $value = get_option('condoleance_enable_archive', true);
        $archive_url = get_post_type_archive_link('condoleance');
        ?>
        <label>
            <input type="checkbox" name="condoleance_enable_archive" value="1" <?php checked($value, true); ?>>
            <?php esc_html_e('Enable automatic archive at', 'condoleance-register'); ?>
            <code><?php echo esc_url($archive_url); ?></code>
        </label>
        <p class="description">
            <?php esc_html_e('When disabled, use the [condoleance_register] shortcode on a custom page instead.', 'condoleance-register'); ?>
        </p>
        <?php
    }

    /**
     * Render show search field.
     *
     * @since 2.0.0
     * @return void
     */
    public function render_show_search_field(): void
    {
        $value = get_option('condoleance_show_search_on_archive', true);
        ?>
        <label>
            <input type="checkbox" name="condoleance_show_search_on_archive" value="1" <?php checked($value, true); ?>>
            <?php esc_html_e('Display search field on archive page', 'condoleance-register'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('Allows visitors to search through condoleances on the current page.', 'condoleance-register'); ?>
        </p>
        <?php
    }

    /**
     * Render per page field.
     *
     * @since 2.0.0
     * @return void
     */
    public function render_per_page_field(): void
    {
        $value = get_option('condoleance_archive_per_page', 10);
        ?>
        <input type="number" name="condoleance_archive_per_page" value="<?php echo esc_attr($value); ?>" min="1" max="100" class="small-text">
        <p class="description">
            <?php esc_html_e('Number of condoleances to display per archive page.', 'condoleance-register'); ?>
        </p>
        <?php
    }

    /**
     * Render settings page.
     *
     * @since 2.0.0
     * @return void
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Show any saved notices
        settings_errors('condoleance_register_migration');
        settings_errors('condoleance_register_settings');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('condoleance_register_settings');
                do_settings_sections('condoleance-register-settings');
                submit_button();
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e('Usage Guide', 'condoleance-register'); ?></h2>
            <div class="card">
                <h3><?php esc_html_e('Display Options', 'condoleance-register'); ?></h3>
                <p><?php esc_html_e('You have two ways to display condoleances:', 'condoleance-register'); ?></p>
                
                <h4>1. <?php esc_html_e('Archive Page (Automatic)', 'condoleance-register'); ?></h4>
                <p>
                    <?php esc_html_e('Enable the archive above and condoleances will automatically appear at:', 'condoleance-register'); ?>
                    <br><code><?php echo esc_url(get_post_type_archive_link('condoleance')); ?></code>
                </p>

                <h4>2. <?php esc_html_e('Shortcode (Manual)', 'condoleance-register'); ?></h4>
                <p><?php esc_html_e('Place this shortcode on any page or post:', 'condoleance-register'); ?></p>
                <code>[condoleance_register per_page="10" show_pagination="yes"]</code>
                <p class="description">
                    <?php esc_html_e('Perfect for custom layouts or page builders.', 'condoleance-register'); ?>
                </p>

                <h4><?php esc_html_e('Other Shortcodes', 'condoleance-register'); ?></h4>
                <ul>
                    <li><code>[condoleance_meta]</code> - <?php esc_html_e('Display dates and service information', 'condoleance-register'); ?></li>
                    <li><code>[light_a_candle]</code> - <?php esc_html_e('Show candle lighting widget', 'condoleance-register'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}
