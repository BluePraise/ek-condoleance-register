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
     * Cookie name for the session token.
     */
    private const COOKIE_NAME = 'condoleance_candle_session';

    /**
     * Cookie lifetime in seconds (1 year).
     */
    private const COOKIE_LIFETIME = YEAR_IN_SECONDS;

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
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'light_candle_rest'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'validate_callback' => static fn($param) => is_numeric($param),
                    ],
                ],
            ],
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_candle_status_rest'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'id'            => [
                        'required'          => true,
                        'validate_callback' => static fn($param) => is_numeric($param),
                    ],
                    'session_token' => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ]);
    }

    /**
     * REST API handler: check whether the current session already lit a candle.
     *
     * @since 2.1.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_candle_status_rest(\WP_REST_Request $request)
    {
        $post_id = (int) $request['id'];

        if ('condoleance' !== get_post_type($post_id)) {
            return new \WP_Error('invalid_post', __('Invalid condoleance.', 'condoleance-register'), ['status' => 404]);
        }

        $token   = $this->resolve_token_from_request($request);
        $already = $this->session_has_lit($post_id, $token); // only true for named candles
        $count   = $this->get_candle_count($post_id);
        $users   = $this->get_candle_users($post_id);

        return rest_ensure_response([
            'already_lit' => $already,
            'count'       => $count,
            'users'       => $users,
        ]);
    }

    /**
     * REST API handler for lighting a candle.
     *
     * @since 2.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function light_candle_rest(\WP_REST_Request $request)
    {
        $post_id     = (int) $request['id'];
        $json_params = $request->get_json_params() ?? [];
        $name        = isset($json_params['name']) ? sanitize_text_field($json_params['name']) : '';
        $anonymous   = isset($json_params['anonymous']) ? (bool) $json_params['anonymous'] : false;
        $token       = $this->resolve_token_from_request($request);

        if ('condoleance' !== get_post_type($post_id)) {
            return new \WP_Error('invalid_post', __('Invalid condoleance.', 'condoleance-register'), ['status' => 404]);
        }

        // Named candles: one per session per page.
        if (!$anonymous && $this->session_has_lit($post_id, $token)) {
            return rest_ensure_response([
                'success'     => false,
                'already_lit' => true,
                'count'       => $this->get_candle_count($post_id),
                'users'       => $this->get_candle_users($post_id),
                'message'     => __('U heeft al een kaarsje aangestoken.', 'condoleance-register'),
            ]);
        }

        // Anonymous candles: allow, but rate-limit by IP.
        if ($anonymous && $this->ip_rate_limited($post_id)) {
            return rest_ensure_response([
                'success' => false,
                'count'   => $this->get_candle_count($post_id),
                'users'   => $this->get_candle_users($post_id),
                'message' => __('U heeft te veel kaarsjes aangestoken. Probeer het later opnieuw.', 'condoleance-register'),
            ]);
        }

        // Anonymous candles use a one-off token so the session is never blocked.
        $store_token = $anonymous ? bin2hex(random_bytes(32)) : $token;
        $result      = $this->store_candle($post_id, $store_token, $name, $anonymous);

        if ($result) {
            return rest_ensure_response([
                'success'       => true,
                'already_lit'   => true,
                'count'         => $this->get_candle_count($post_id),
                'users'         => $this->get_candle_users($post_id),
                'session_token' => $token,
                'message'       => __('Het kaarsje is aangestoken.', 'condoleance-register'),
            ]);
        }

        return new \WP_Error('candle_error', __('Het is niet gelukt om het kaarsje aan te steken.', 'condoleance-register'), ['status' => 500]);
    }

    /**
     * Store a candle record in the database and sync post meta count.
     *
     * @since 2.1.0
     * @param int    $post_id   Post ID.
     * @param string $token     Session token.
     * @param string $name      Visitor name (optional).
     * @param bool   $anonymous Whether to display anonymously.
     * @return bool
     */
    private function store_candle(int $post_id, string $token, string $name = '', bool $anonymous = false): bool
    {
        global $wpdb;

        $table  = $wpdb->prefix . 'condoleance_candles';
        $result = $wpdb->insert(
            $table,
            [
                'post_id'       => $post_id,
                'session_token' => $token,
                'name'          => $name,
                'anonymous'     => (int) $anonymous,
                'ip_address'    => $this->get_client_ip(),
                'lit_at'        => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s']
        );

        if (false === $result) {
            return false;
        }

        // Keep post meta in sync so archive templates and shortcodes still work.
        $count = $this->get_candle_count($post_id);
        $users = $this->get_candle_users($post_id);
        update_post_meta($post_id, 'condoleance_candles_data', ['count' => $count, 'users' => $users]);

        return true;
    }

    /**
     * Check whether a named (non-anonymous) candle already exists for this session + post.
     *
     * Anonymous candles use one-off tokens and are never checked here, so
     * they can never block the button from being re-enabled.
     *
     * @since 2.1.0
     * @param int    $post_id Post ID.
     * @param string $token   Session token.
     * @return bool
     */
    private function session_has_lit(int $post_id, string $token): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'condoleance_candles';
        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND session_token = %s AND anonymous = 0",
                $post_id,
                $token
            )
        );

        return $count > 0;
    }

    /**
     * Rate-limit anonymous candles by anonymized IP: max 5 per post per hour.
     *
     * @since 2.1.0
     * @param int $post_id Post ID.
     * @return bool True when the limit has been reached.
     */
    private function ip_rate_limited(int $post_id): bool
    {
        global $wpdb;

        $ip    = $this->get_client_ip();
        $table = $wpdb->prefix . 'condoleance_candles';
        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table}
                 WHERE post_id = %d AND ip_address = %s AND anonymous = 1
                   AND lit_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                $post_id,
                $ip
            )
        );

        return $count >= 5;
    }

    /**
     * Get candle count for a post from the database.
     *
     * @since 2.1.0
     * @param int $post_id Post ID.
     * @return int
     */
    private function get_candle_count(int $post_id): int
    {
        global $wpdb;

        $table = $wpdb->prefix . 'condoleance_candles';
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id)
        );
    }

    /**
     * Get candle users list for a post (name, anonymous flag, date).
     *
     * @since 2.1.0
     * @param int $post_id Post ID.
     * @return array<int, array{name: string, anonymous: bool, date: string}>
     */
    private function get_candle_users(int $post_id): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'condoleance_candles';
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT name, anonymous, lit_at AS date FROM {$table} WHERE post_id = %d ORDER BY lit_at DESC",
                $post_id
            ),
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        return array_map(static fn(array $row) => [
            'name'      => $row['name'],
            'anonymous' => (bool) $row['anonymous'],
            'date'      => $row['date'],
        ], $rows);
    }

    /**
     * Resolve the session token from the request body or query param.
     *
     * The JS layer owns the cookie and always sends the token explicitly so
     * we never fall back to the server-side cookie. Falling back to the cookie
     * would cause the status check to return already_lit:true on every reload
     * because the cookie persists across sessions.
     *
     * @since 2.1.0
     * @param \WP_REST_Request $request Request object.
     * @return string 64-char hex token.
     */
    private function resolve_token_from_request(\WP_REST_Request $request): string
    {
        // 1. Check JSON body (POST requests).
        $json = $request->get_json_params() ?? [];
        if (!empty($json['session_token'])) {
            $candidate = sanitize_text_field($json['session_token']);
            if ($this->is_valid_token($candidate)) {
                return $candidate;
            }
        }

        // 2. Check query string (GET requests) — registered as a route arg so
        //    get_param() will return it correctly.
        $query_token = $request->get_param('session_token');
        if (!empty($query_token)) {
            $candidate = sanitize_text_field((string) $query_token);
            if ($this->is_valid_token($candidate)) {
                return $candidate;
            }
        }

        // 3. No valid token provided — return a throwaway token that has no DB
        //    rows, so the status check always returns already_lit:false rather
        //    than accidentally matching a cookie from a previous visit.
        return bin2hex(random_bytes(32));
    }

    /**
     * Check server-side (from the PHP cookie) whether this visitor has already
     * lit a named candle for the given post. Used by templates to render the
     * button state without a JS round-trip.
     *
     * Returns false for anonymous candles — anonymous visitors can always light again.
     *
     * @since 2.1.0
     * @param int $post_id Post ID.
     * @return bool
     */
    public static function has_lit(int $post_id): bool
    {
        if (empty($_COOKIE[self::COOKIE_NAME])) {
            return false;
        }

        $token = sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME]));
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'condoleance_candles';
        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND session_token = %s AND anonymous = 0",
                $post_id,
                $token
            )
        );

        return $count > 0;
    }

    /**
     * Get or generate a persistent session token from the server-side cookie.
     *
     * @since 2.1.0
     * @return string 64-char hex token.
     */
    public function get_session_token(): string
    {
        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            $token = sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME]));
            if ($this->is_valid_token($token)) {
                return $token;
            }
        }

        return bin2hex(random_bytes(32));
    }

    /**
     * Validate a session token format (64 hex chars).
     *
     * @since 2.1.0
     * @param string $token Token to validate.
     * @return bool
     */
    private function is_valid_token(string $token): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * Return cookie configuration for use by JS localisation.
     *
     * @since 2.1.0
     * @return array{name: string, lifetime: int}
     */
    public static function cookie_config(): array
    {
        return [
            'name'     => self::COOKIE_NAME,
            'lifetime' => self::COOKIE_LIFETIME,
        ];
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

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts    = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts                    = explode(':', $ip);
            $parts[count($parts) - 1] = '0';
            return implode(':', $parts);
        }

        return '';
    }
}
