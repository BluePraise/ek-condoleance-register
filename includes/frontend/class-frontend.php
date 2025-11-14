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
            'columns' => 3,
        ], $atts, 'condoleance_register');

        $per_page = absint($atts['per_page']);
        $show_pagination = 'yes' === $atts['show_pagination'];
        $columns = absint($atts['columns']);
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
        <div class="condoleance-register-list" data-columns="<?php echo esc_attr($columns); ?>">
            <?php if ($query->have_posts()) : ?>
                <div class="condoleance-grid condoleance-grid-<?php echo esc_attr($columns); ?>">
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
            <?php if (has_post_thumbnail()) : ?>
                <a href="<?php the_permalink(); ?>" class="card-image-link">
                    <?php the_post_thumbnail('medium', ['class' => 'card-image']); ?>
                </a>
            <?php else : ?>
                <div class="card-image-placeholder">
                    <span class="placeholder-icon">🕊️</span>
                </div>
            <?php endif; ?>

            <div class="card-content">
                <h3 class="card-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>

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
                                    /* translators: %s: death date */
                                    esc_html__('Overleden: %s', 'condoleance-register'),
                                    esc_html($death_date)
                                );
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (has_excerpt()) : ?>
                    <div class="card-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>

                <div class="card-meta">
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
            </div>
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
                    aria-label="<?php esc_attr_e('Light a candle', 'condoleance-register'); ?>"
                >
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
}
