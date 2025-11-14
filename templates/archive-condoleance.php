<?php
/**
 * Archive Condoleance Template
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="condoleance-archive">

    <header class="archive-header">
        <h1 class="archive-title">
            <?php
            if (is_search()) {
                printf(
                    /* translators: %s: search query */
                    esc_html__('Search Results for: %s', 'condoleance-register'),
                    '<span>' . get_search_query() . '</span>'
                );
            } else {
                esc_html_e('In Memoriam', 'condoleance-register');
            }
            ?>
        </h1>
        <?php
        $total = $wp_query->found_posts ?? 0;
        if ($total > 0) :
            ?>
            <p class="archive-description">
                <?php
                printf(
                    /* translators: %d: number of memorials */
                    esc_html(_n('Honoring %d life', 'Honoring %d lives', $total, 'condoleance-register')),
                    $total
                );
                ?>
            </p>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>

        <div class="condoleance-grid">
            <?php
            while (have_posts()) :
                the_post();

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
                        <h2 class="card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

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
                                            esc_html__('Passed: %s', 'condoleance-register'),
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
                                    esc_html(_n('%d candle', '%d candles', $candle_count, 'condoleance-register')),
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
                                        esc_html(_n('%d message', '%d messages', $comment_count, 'condoleance-register')),
                                        $comment_count
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <a href="<?php the_permalink(); ?>" class="card-link button">
                            <?php esc_html_e('View Memorial', 'condoleance-register'); ?>
                        </a>
                    </div>

                </article>

            <?php endwhile; ?>
        </div>

        <?php
        // Pagination
        the_posts_pagination([
            'mid_size' => 2,
            'prev_text' => sprintf(
                '<span class="nav-prev-text">%s</span>',
                esc_html__('Previous', 'condoleance-register')
            ),
            'next_text' => sprintf(
                '<span class="nav-next-text">%s</span>',
                esc_html__('Next', 'condoleance-register')
            ),
            'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__('Page', 'condoleance-register') . ' </span>',
            'class' => 'condoleance-pagination',
        ]);
        ?>

    <?php else : ?>

        <div class="no-results">
            <p><?php esc_html_e('No memorials found.', 'condoleance-register'); ?></p>
            <?php if (is_search()) : ?>
                <p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('condoleance')); ?>" class="button">
                        <?php esc_html_e('View All Memorials', 'condoleance-register'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<?php
get_footer();
