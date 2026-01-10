<?php
/**
 * Virtual Candles Handler
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
 * Handles virtual candle lighting functionality.
 *
 * @since 2.0.0
 */
class Candles
{
    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    /**
     * Register REST API routes.
     *
     * @since 2.0.0
     * @return void
     */
    public function register_rest_routes(): void
    {
        register_rest_route('condoleance-register/v1', '/candles/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'light_candle_rest'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ],
                'name' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * REST API handler for lighting a candle.
     *
     * @since 2.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response|\WP_Error Response object or error.
     */
    public function light_candle_rest(\WP_REST_Request $request)
    {
        $post_id = (int) $request['id'];
        $json_params = $request->get_json_params();
        $name = isset($json_params['name']) ? sanitize_text_field($json_params['name']) : '';
        $anonymous = isset($json_params['anonymous']) ? (bool) $json_params['anonymous'] : false;

        if ('condoleance' !== get_post_type($post_id)) {
            return new \WP_Error('invalid_post', __('Invalid condoleance.', 'condoleance-register'), ['status' => 404]);
        }

        $result = $this->light_candle($post_id, $name, $anonymous);

        if ($result) {
            $candles_data = get_post_meta($post_id, 'condoleance_candles_data', true);
            $users = is_array($candles_data) && isset($candles_data['users']) ? $candles_data['users'] : [];

            return rest_ensure_response([
                'success' => true,
                'count' => $this->get_candle_count($post_id),
                'users' => $users,
                'message' => __('Het kaarsje is aangestoken.', 'condoleance-register'),
            ]);
        }

        return new \WP_Error('candle_error', __('Het is niet gelukt om het kaarsje aan te steken.', 'condoleance-register'), ['status' => 500]);
    }

    /**
     * Light a candle for a condoleance.
     *
     * @since 2.0.0
     * @param int    $post_id Post ID.
     * @param string $name    Name of person lighting candle (optional).
     * @param bool   $anonymous Whether the user wants to remain anonymous.
     * @return bool Whether candle was lit successfully.
     */
    private function light_candle(int $post_id, string $name = '', bool $anonymous = false): bool
    {
        $candles = get_post_meta($post_id, 'condoleance_candles_data', true);

        if (!is_array($candles)) {
            $candles = ['count' => 0, 'users' => []];
        }

        $candles['count'] = (int) ($candles['count'] ?? 0) + 1;

        if ($name) {
            $candles['users'][] = [
                'name' => sanitize_text_field($name),
                'anonymous' => $anonymous,
                'date' => current_time('mysql'),
                'ip' => $this->get_client_ip(),
            ];
        }

        return update_post_meta($post_id, 'condoleance_candles_data', $candles);
    }

    /**
     * Get candle count for a post.
     *
     * @since 2.0.0
     * @param int $post_id Post ID.
     * @return int Candle count.
     */
    private function get_candle_count(int $post_id): int
    {
        $candles = get_post_meta($post_id, 'condoleance_candles_data', true);
        return (int) ($candles['count'] ?? 0);
    }

    /**
     * Get client IP address (anonymized for GDPR).
     *
     * @since 2.0.0
     * @return string Anonymized IP address.
     */
    private function get_client_ip(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // Anonymize IP for GDPR compliance.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            $parts[count($parts) - 1] = '0';
            return implode(':', $parts);
        }

        return '';
    }
}
