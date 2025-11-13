<?php
/**
 * Main Plugin Class
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
 * Main plugin class - Singleton pattern.
 *
 * @since 2.0.0
 */
final class Plugin
{
    /**
     * Single instance of the class.
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Post type handler.
     *
     * @var PostTypes\Condoleance|null
     */
    private ?PostTypes\Condoleance $post_type = null;

    /**
     * Admin handler.
     *
     * @var Admin\Admin|null
     */
    private ?Admin\Admin $admin = null;

    /**
     * Frontend handler.
     *
     * @var Frontend\Frontend|null
     */
    private ?Frontend\Frontend $frontend = null;

    /**
     * Private constructor to prevent direct instantiation.
     *
     * @since 2.0.0
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Get singleton instance.
     *
     * @since 2.0.0
     * @return Plugin
     */
    public static function get_instance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 2.0.0
     * @return void
     */
    private function init_hooks(): void
    {
        add_action('init', [$this, 'init_components'], 0);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Initialize plugin components.
     *
     * @since 2.0.0
     * @return void
     */
    public function init_components(): void
    {
        // Register custom post type.
        $this->post_type = new PostTypes\Condoleance();

        // Initialize admin components.
        if (is_admin()) {
            $this->admin = new Admin\Admin();
        }

        // Initialize frontend components.
        if (!is_admin()) {
            $this->frontend = new Frontend\Frontend();
        }
    }

    /**
     * Enqueue frontend assets.
     *
     * @since 2.0.0
     * @return void
     */
    public function enqueue_frontend_assets(): void
    {
        // Only load on relevant pages.
        if (!is_singular('condoleance') && !is_post_type_archive('condoleance')) {
            return;
        }

        wp_enqueue_style(
            'condoleance-register-frontend',
            CONDOLEANCE_REGISTER_URL . 'assets/css/frontend.css',
            [],
            CONDOLEANCE_REGISTER_VERSION
        );

        wp_enqueue_script(
            'condoleance-register-frontend',
            CONDOLEANCE_REGISTER_URL . 'assets/js/frontend.js',
            ['jquery', 'wp-api'],
            CONDOLEANCE_REGISTER_VERSION,
            true
        );

        wp_localize_script('condoleance-register-frontend', 'condoleanceRegister', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('condoleance-register/v1'),
            'nonce' => wp_create_nonce('condoleance_register_nonce'),
            'strings' => [
                'error' => esc_html__('An error occurred. Please try again.', 'condoleance-register'),
                'success' => esc_html__('Success!', 'condoleance-register'),
            ],
        ]);
    }

    /**
     * Enqueue admin assets.
     *
     * @since 2.0.0
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void
    {
        // Only load on condoleance post type pages.
        $screen = get_current_screen();
        if (!$screen || 'condoleance' !== $screen->post_type) {
            return;
        }

        wp_enqueue_style(
            'condoleance-register-admin',
            CONDOLEANCE_REGISTER_URL . 'assets/css/admin.css',
            [],
            CONDOLEANCE_REGISTER_VERSION
        );

        wp_enqueue_script(
            'condoleance-register-admin',
            CONDOLEANCE_REGISTER_URL . 'assets/js/admin.js',
            ['jquery'],
            CONDOLEANCE_REGISTER_VERSION,
            true
        );
    }

    /**
     * Prevent cloning.
     *
     * @since 2.0.0
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization.
     *
     * @since 2.0.0
     * @return void
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
