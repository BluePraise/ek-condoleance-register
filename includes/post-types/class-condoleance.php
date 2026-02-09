<?php
/**
 * Condoleance Custom Post Type
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

namespace CondoleanceRegister\PostTypes;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers and manages the Condoleance custom post type.
 *
 * @since 2.0.0
 */
class Condoleance
{
    /**
     * Post type slug.
     *
     * @var string
     */
    public const POST_TYPE = 'condoleance';

    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_action('init', [$this, 'register']);
        add_action('cmb2_admin_init', [$this, 'register_meta_boxes']);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'customize_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'render_custom_column'], 10, 2);
        add_filter('enter_title_here', [$this, 'change_title_placeholder'], 10, 2);
    }

    /**
     * Register the custom post type.
     *
     * @since 2.0.0
     * @return void
     */
    public function register(): void
    {
        $labels = [
            'name' => _x('Condoleance Register', 'Post Type General Name', 'condoleance-register'),
            'singular_name' => _x('Condoleance', 'Post Type Singular Name', 'condoleance-register'),
            'menu_name' => __('Condoleance Register', 'condoleance-register'),
            'name_admin_bar' => __('Condoleances', 'condoleance-register'),
            'archives' => __('Condoleance Archives', 'condoleance-register'),
            'attributes' => __('Condoleance Attributes', 'condoleance-register'),
            'parent_item_colon' => __('Parent Condoleance:', 'condoleance-register'),
            'all_items' => __('All Condoleances', 'condoleance-register'),
            'add_new_item' => __('Add New Condoleance', 'condoleance-register'),
            'add_new' => __('Add New', 'condoleance-register'),
            'new_item' => __('New Condoleance', 'condoleance-register'),
            'edit_item' => __('Edit Condoleance', 'condoleance-register'),
            'update_item' => __('Update Condoleance', 'condoleance-register'),
            'view_item' => __('View Condoleance', 'condoleance-register'),
            'view_items' => __('View Condoleances', 'condoleance-register'),
            'search_items' => __('Search Condoleance', 'condoleance-register'),
            'not_found' => __('No condoleances found', 'condoleance-register'),
            'not_found_in_trash' => __('No condoleances found in Trash', 'condoleance-register'),
            'featured_image' => __('Memorial Photo', 'condoleance-register'),
            'set_featured_image' => __('Set memorial photo', 'condoleance-register'),
            'remove_featured_image' => __('Remove memorial photo', 'condoleance-register'),
            'use_featured_image' => __('Use as memorial photo', 'condoleance-register'),
            'insert_into_item' => __('Insert into condoleance', 'condoleance-register'),
            'uploaded_to_this_item' => __('Uploaded to this condoleance', 'condoleance-register'),
            'items_list' => __('Condoleances list', 'condoleance-register'),
            'items_list_navigation' => __('Condoleances list navigation', 'condoleance-register'),
            'filter_items_list' => __('Filter condoleances list', 'condoleance-register'),
        ];

        $args = [
            'label' => __('Condoleance', 'condoleance-register'),
            'description' => __('Memorial pages for deceased individuals', 'condoleance-register'),
            'labels' => $labels,
            'supports' => ['title', 'editor', 'thumbnail', 'comments', 'revisions', 'excerpt', 'custom-fields'],
            'taxonomies' => [],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-heart',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'show_in_rest' => true,
            'rest_base' => 'condoleren',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rewrite' => [
                'slug' => 'condoleren',
                'with_front' => false,
            ],
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register meta boxes using CMB2.
     *
     * @since 2.0.0
     * @return void
     */
    public function register_meta_boxes(): void
    {
        $prefix = 'condoleance_';

        // Deceased person details.
        $cmb = new_cmb2_box([
            'id' => $prefix . 'details',
            'title' => esc_html__('Gegevens over de overledene', 'condoleance-register'),
            'object_types' => [self::POST_TYPE],
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
        ]);

        $cmb->add_field([
            'name' => esc_html__('Geboortedatum', 'condoleance-register'),
            'id' => $prefix . 'birth_date',
            'type' => 'text_date',
            'date_format' => 'd/m/Y',
            'attributes' => [
                'data-datepicker' => wp_json_encode([
                    'yearRange' => '-120:' . date('Y'),
                    'dateFormat' => 'dd/mm/yy',
                    'changeMonth' => true,
                    'changeYear' => true,
                ]),
            ],
        ]);

        $cmb->add_field([
            'name' => esc_html__('Datum van Overlijden', 'condoleance-register'),
            'id' => $prefix . 'death_date',
            'type' => 'text_date',
            'date_format' => 'd/m/Y',
            'attributes' => [
                'data-datepicker' => wp_json_encode([
                    'yearRange' => '-120:' . date('Y'),
                    'dateFormat' => 'dd/mm/yy',
                    'changeMonth' => true,
                    'changeYear' => true,
                ]),
            ],
        ]);

        $cmb->add_field([
            'name' => esc_html__('Hero Achtergrondafbeelding', 'condoleance-register'),
            'id' => $prefix . 'hero_image',
            'type' => 'file',
            'options' => [
                'url' => false,
            ],
            'query_args' => ['type' => 'image'],
            'preview_size' => 'medium',
            'description' => esc_html__('Upload een foto voor de hero banner. Als dit niet is ingesteld, wordt de een donkere achtergrond gebruikt.', 'condoleance-register'),
        ]);

        $cmb->add_field([
            'name' => esc_html__('Fotogalerij', 'condoleance-register'),
            'id' => $prefix . 'photos',
            'type' => 'file_list',
            'preview_size' => 'medium',
            'query_args' => ['type' => 'image'],
            'text' => [
                'add_upload_files_text' => esc_html__('Voeg fotos toe', 'condoleance-register'),
            ],
        ]);

        // Candle data (read-only, managed by JS).
        $cmb->add_field([
            'name' => esc_html__('Virtuele Kaarsen', 'condoleance-register'),
            'id' => $prefix . 'candles',
            'type' => 'textarea_small',
            'attributes' => [
                'readonly' => 'readonly',
                'disabled' => 'disabled',
            ],
            'description' => esc_html__('Aantal virtuele kaarsen. Wordt automatisch beheerd.', 'condoleance-register'),
            'save_field' => false,
            'default_cb' => [$this, 'get_candle_count_display'],
        ]);

        // Service Information.
        $cmb_service = new_cmb2_box([
            'id' => $prefix . 'service_info',
            'title' => esc_html__('Dienst Informatie', 'condoleance-register'),
            'object_types' => [self::POST_TYPE],
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
        ]);

        $cmb_service->add_field([
            'name' => esc_html__('Dienst Datum', 'condoleance-register'),
            'id' => $prefix . 'service_date',
            'type' => 'text_date',
            'date_format' => 'd/m/Y',
        ]);

        $cmb_service->add_field([
            'name' => esc_html__('Dienst Tijd', 'condoleance-register'),
            'id' => $prefix . 'service_time',
            'type' => 'text_time',
            'time_format' => 'H:i',
        ]);

        $cmb_service->add_field([
            'name' => esc_html__('Dienst Locatie', 'condoleance-register'),
            'id' => $prefix . 'service_location',
            'type' => 'textarea_small',
            'description' => esc_html__('Voer het adres of de locatiegegevens in.', 'condoleance-register'),
        ]);

        $cmb_service->add_field([
            'name' => esc_html__('Lokatie', 'condoleance-register'),
            'id' => $prefix . 'funeral_home',
            'type' => 'text',
        ]);
    }

    /**
     * Get candle count for display in meta box.
     *
     * @since 2.0.0
     * @return string
     */
    public function get_candle_count_display(): string
    {
        global $post;

        if (!$post) {
            return '0';
        }

        $candles = get_post_meta($post->ID, 'condoleance_candles_data', true);
        $count = is_array($candles) && isset($candles['count']) ? $candles['count'] : 0;

        return sprintf(
            /* translators: %d: number of candles */
            _n('%d candle lit', '%d candles lit', $count, 'condoleance-register'),
            $count
        );
    }

    /**
     * Customize admin columns.
     *
     * @since 2.0.0
     * @param array<string, string> $columns Existing columns.
     * @return array<string, string> Modified columns.
     */
    public function customize_columns(array $columns): array
    {
        // Remove default columns.
        unset($columns['date']);

        // Add custom columns.
        $new_columns = [
            'cb' => $columns['cb'],
            'thumbnail' => __('Photo', 'condoleance-register'),
            'title' => __('Name', 'condoleance-register'),
            'birth_date' => __('Birth Date', 'condoleance-register'),
            'death_date' => __('Death Date', 'condoleance-register'),
            'candles' => __('Candles', 'condoleance-register'),
            'comments' => $columns['comments'] ?? __('Comments', 'condoleance-register'),
            'date' => __('Published', 'condoleance-register'),
        ];

        return $new_columns;
    }

    /**
     * Render custom column content.
     *
     * @since 2.0.0
     * @param string $column Column name.
     * @param int    $post_id Post ID.
     * @return void
     */
    public function render_custom_column(string $column, int $post_id): void
    {
        switch ($column) {
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, [50, 50]);
                } else {
                    echo '<span class="dashicons dashicons-format-image" style="font-size: 50px; color: #ddd;"></span>';
                }
                break;

            case 'birth_date':
                $date = get_post_meta($post_id, 'condoleance_birth_date', true);
                echo $date ? esc_html($date) : '—';
                break;

            case 'death_date':
                $date = get_post_meta($post_id, 'condoleance_death_date', true);
                echo $date ? esc_html($date) : '—';
                break;

            case 'candles':
                $candles = get_post_meta($post_id, 'condoleance_candles_data', true);
                $count = is_array($candles) && isset($candles['count']) ? $candles['count'] : 0;
                printf(
                    '<span class="dashicons dashicons-heart" style="color: #d63638;"></span> %d',
                    (int) $count
                );
                break;
        }
    }

    /**
     * Change title placeholder text.
     *
     * @since 2.0.0
     * @param string    $title Existing placeholder.
     * @param \WP_Post $post  Post object.
     * @return string Modified placeholder.
     */
    public function change_title_placeholder(string $title, \WP_Post $post): string
    {
        if (self::POST_TYPE === $post->post_type) {
            return __('Enter deceased person\'s name', 'condoleance-register');
        }

        return $title;
    }
}
