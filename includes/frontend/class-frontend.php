<?php
/**
 * Frontend functionality handler
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

namespace CondoleanceRegister\Frontend;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles frontend-specific functionality.
 *
 * @since 2.0.0
 */
class Frontend
{
    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        // Register shortcodes.
        add_shortcode('condoleance_register', [$this, 'render_register_shortcode']);
        add_shortcode('light_a_candle', [$this, 'render_candle_shortcode']);

        // Initialize components.
        new Comments();
        new Candles();
    }

    /**
     * Render condoleance register shortcode.
     *
     * @since 2.0.0
     * @param array<string, mixed> $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render_register_shortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_pagination' => 'yes',
        ], $atts, 'condoleance_register');

        // Implementation will follow in next phase.
        return '<div class="condoleance-register-list">Coming soon...</div>';
    }

    /**
     * Render light a candle shortcode.
     *
     * @since 2.0.0
     * @param array<string, mixed> $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render_candle_shortcode(array $atts): string
    {
        // Implementation will follow in next phase.
        return '<div class="condoleance-candle-widget">Coming soon...</div>';
    }
}
