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
        add_shortcode('condoleance_meta', [$this, 'render_meta_shortcode']);
        add_shortcode('condolances_meta', [$this, 'render_meta_shortcode']); // Legacy alias

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

        $per_page = absint($atts['per_page']);
        $show_pagination = 'yes' === $atts['show_pagination'];
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        $args = [
            'post_type' => 'condoleance',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $query = new \WP_Query($args);

        ob_start();
?>
        <div class="condoleance-register-list">
            <?php if ($query->have_posts()) : ?>
                <div class="condoleance-grid">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        $this->render_condoleance_card();
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>

                <?php if ($show_pagination && $query->max_num_pages > 1) : ?>
                    <nav class="condoleance-pagination">
                        <?php
                        $big = 999999999;
                        echo paginate_links([
                            'base' => str_replace((string) $big, '%#%', esc_url(get_pagenum_link($big))),
                            'format' => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total' => $query->max_num_pages,
                            'prev_text' => esc_html__('« Previous', 'condoleance-register'),
                            'next_text' => esc_html__('Next »', 'condoleance-register'),
                            'type' => 'list',
                            'mid_size' => 2,
                        ]);
                        ?>
                    </nav>
                <?php endif; ?>
            <?php else : ?>
                <p class="no-condoleances"><?php esc_html_e('No memorials found.', 'condoleance-register'); ?></p>
            <?php endif; ?>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Render a single condoleance card for the grid.
     *
     * @since 2.0.0
     * @return void
     */
    private function render_condoleance_card(): void
    {
        $birth_date = get_post_meta(get_the_ID(), 'condoleance_birth_date', true);
        $death_date = get_post_meta(get_the_ID(), 'condoleance_death_date', true);
        $candles_data = get_post_meta(get_the_ID(), 'condoleance_candles_data', true);
        $candle_count = is_array($candles_data) ? ($candles_data['count'] ?? 0) : 0;
    ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('condoleance-card'); ?>>
            <h3 class="obituary-name">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            <!-- Obituary Dates -->
            <?php if ($birth_date || $death_date) : ?>
                <div class="card-dates">
                    <?php if ($birth_date && $death_date) : ?>
                        <span class="date-range">
                            <?php echo esc_html($birth_date); ?> - <?php echo esc_html($death_date); ?>
                        </span>
                    <?php elseif ($death_date) : ?>
                        <span class="death-date">
                            <?php
                            printf(
                                /* translators: %s: date of death */
                                esc_html__('Overleden: %s', 'condoleance-register'),
                                esc_html($death_date)
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <!-- End of Obituary Dates -->

            <?php if (has_post_thumbnail()) : ?>
                <a href="<?php the_permalink(); ?>" class="card-image-link">
                    <?php the_post_thumbnail('medium', ['class' => 'card-image']); ?>
                </a>
            <?php else : ?>
                <div class="card-image-placeholder">
                    <span class="placeholder-icon">🕊️</span>
                </div>
            <?php endif; ?>



                <?php if (has_excerpt()) : ?>
                    <div class="card-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>

                <div class="card-meta">
                    <!-- /* if no candles, this section will be empty */ -->
                    <?php if ($candle_count > 0) : ?>
                        <span class="candle-count">
                            <span class="candle-icon">🕯️</span>
                            <?php
                            printf(
                                /* translators: %d: number of candles */
                                esc_html(_n('er is %d kaarsje aangestoken', 'er zijn %d kaarsjes aangestoken', $candle_count, 'condoleance-register')),
                                $candle_count
                            );
                            ?>
                        </span>
                    <?php endif; ?>

                    <?php
                    $comment_count = get_comments_number();
                    if ($comment_count > 0) :
                    ?>
                        <span class="comment-count">
                            <span class="comment-icon">💬</span>
                            <?php
                            printf(
                                /* translators: %d: number of comments */
                                esc_html(_n('er is %d bericht', 'er zijn %d berichten', $comment_count, 'condoleance-register')),
                                $comment_count
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>

                <a href="<?php the_permalink(); ?>" class="card-link button">
                    <?php esc_html_e('Condoleer', 'condoleance-register'); ?>
                </a>

        </article>
    <?php
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
        global $post;

        $atts = shortcode_atts([
            'post_id' => $post ? $post->ID : 0,
            'show_count' => 'yes',
            'show_names' => 'no',
        ], $atts, 'light_a_candle');

        $post_id = absint($atts['post_id']);
        $show_count = 'yes' === $atts['show_count'];
        $show_names = 'yes' === $atts['show_names'];

        if (!$post_id || 'condoleance' !== get_post_type($post_id)) {
            return '<p class="error">' . esc_html__('Invalid memorial.', 'condoleance-register') . '</p>';
        }

        $candles_data = get_post_meta($post_id, 'condoleance_candles_data', true);
        $candle_count = is_array($candles_data) ? ($candles_data['count'] ?? 0) : 0;
        $candle_users = is_array($candles_data) && isset($candles_data['users']) ? $candles_data['users'] : [];

        ob_start();
    ?>
        <div class="condoleance-candle-widget">
            <div class="candle-widget-inner">
                <div class="candle-icon-large">🕯️</div>

                <?php if ($show_count) : ?>
                    <div class="candle-count-display">
                        <span class="condoleance-candle-count" data-post-id="<?php echo esc_attr($post_id); ?>">
                            <?php echo esc_html($candle_count); ?>
                        </span>
                        <span class="candle-label">
                            <?php
                            printf(
                                esc_html(_n('candle lit', 'candles lit', $candle_count, 'condoleance-register')),
                                $candle_count
                            );
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <button
                    class="condoleance-light-candle button"
                    data-post-id="<?php echo esc_attr($post_id); ?>"
                    aria-label="<?php esc_attr_e('Light a candle', 'condoleance-register'); ?>">
                    <?php esc_html_e('Light a Candle', 'condoleance-register'); ?>
                </button>

                <?php if ($show_names && !empty($candle_users)) : ?>
                    <div class="candle-users">
                        <h4 class="candle-users-title"><?php esc_html_e('Recent Candles:', 'condoleance-register'); ?></h4>
                        <ul class="candle-users-list">
                            <?php
                            $recent_users = array_slice(array_reverse($candle_users), 0, 10);
                            foreach ($recent_users as $user) :
                            ?>
                                <li class="candle-user">
                                    <span class="user-name"><?php echo esc_html($user['name']); ?></span>
                                    <span class="user-date"><?php echo esc_html(human_time_diff(strtotime($user['date']), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'condoleance-register'); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Render condoleance meta information shortcode.
     *
     * @since 2.0.0
     * @param array<string, mixed> $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render_meta_shortcode(array $atts): string
    {
        global $post;

        $atts = shortcode_atts([
            'post_id' => $post ? $post->ID : 0,
            'show_dates' => 'yes',
            'show_location' => 'yes',
            'show_service' => 'yes',
        ], $atts, 'condoleance_meta');

        $post_id = absint($atts['post_id']);
        $show_dates = 'yes' === $atts['show_dates'];
        $show_location = 'yes' === $atts['show_location'];
        $show_service = 'yes' === $atts['show_service'];

        if (!$post_id || 'condoleance' !== get_post_type($post_id)) {
            return '';
        }

        $birth_date = get_post_meta($post_id, 'condoleance_birth_date', true);
        $death_date = get_post_meta($post_id, 'condoleance_death_date', true);
        $service_date = get_post_meta($post_id, 'condoleance_service_date', true);
        $service_time = get_post_meta($post_id, 'condoleance_service_time', true);
        $service_location = get_post_meta($post_id, 'condoleance_service_location', true);
        $funeral_home = get_post_meta($post_id, 'condoleance_funeral_home', true);

        ob_start();
    ?>
        <div class="condoleance-meta-info">
            <?php if ($show_dates && ($birth_date || $death_date)) : ?>
                <div class="meta-item meta-dates">
                    <?php if ($birth_date) : ?>
                        <div class="meta-row">
                            <span class="meta-label"><?php esc_html_e('Geboren:', 'condoleance-register'); ?></span>
                            <span class="meta-value"><?php echo esc_html($birth_date); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($death_date) : ?>
                        <div class="meta-row">
                            <span class="meta-label"><?php esc_html_e('Overleden:', 'condoleance-register'); ?></span>
                            <span class="meta-value"><?php echo esc_html($death_date); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($show_service && ($service_date || $service_time || $funeral_home)) : ?>
                <div class="meta-item meta-service">
                    <h4 class="meta-heading"><?php esc_html_e('Uitvaart', 'condoleance-register'); ?></h4>
                    <?php if ($service_date) : ?>
                        <div class="meta-row">
                            <span class="meta-label"><?php esc_html_e('Datum:', 'condoleance-register'); ?></span>
                            <span class="meta-value"><?php echo esc_html($service_date); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($service_time) : ?>
                        <div class="meta-row">
                            <span class="meta-label"><?php esc_html_e('Tijd:', 'condoleance-register'); ?></span>
                            <span class="meta-value"><?php echo esc_html($service_time); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($funeral_home) : ?>
                        <div class="meta-row">
                            <span class="meta-label"><?php esc_html_e('Uitvaartcentrum:', 'condoleance-register'); ?></span>
                            <span class="meta-value"><?php echo esc_html($funeral_home); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($show_location && $service_location) : ?>
                <div class="meta-item meta-location">
                    <h4 class="meta-heading"><?php esc_html_e('Locatie', 'condoleance-register'); ?></h4>
                    <div class="meta-row">
                        <span class="meta-value"><?php echo wp_kses_post(wpautop($service_location)); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php do_action('condoleance_meta_after', $post_id); ?>
        </div>
<?php
        return ob_get_clean();
    }
}
