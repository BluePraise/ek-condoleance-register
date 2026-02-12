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

get_template_part('template-parts/hero-banner');
?>

<div class="content-wrapper">

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
                esc_html_e('Condoleance Register', 'condoleance-register');
            }
            ?>
        </h1>
    </header>

    <?php if (have_posts()) : ?>

        <?php if (get_option('condoleance_show_search_on_archive', true)) : ?>
            <div class="condoleance-search">
                <input type="search" class="js-search-field" placeholder="<?php esc_attr_e('Zoek op naam', 'condoleance-register'); ?>" aria-label="<?php esc_attr_e('Zoek in condoleances', 'condoleance-register'); ?>">
            </div>
        <?php endif; ?>

        <div class="condoleance-register-list js-search-list">
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

                    <h3 class="obituary-name">
                        <a href="<?php the_permalink(); ?>" class="js-search-item"><?php the_title(); ?></a>
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

                    <?php if (has_excerpt()) : ?>
                        <div class="card-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="card-image-link">
                            <?php the_post_thumbnail('medium', ['class' => 'card-image']); ?>
                        </a>
                    <?php else : ?>
                        <div class="card-image-placeholder">
                            <span class="placeholder-icon">🕊️</span>
                        </div>
                    <?php endif; ?>

                    <div class="card-meta">
                        <span class="candle-count">
                            <span class="candle-icon">🕯️</span>
                            <?php if ($candle_count < 1) : ?>
                                <?php
                                printf(
                                    esc_html__('Er zijn nog geen kaarsjes aangestoken', 'condoleance-register')
                                );
                                ?>
                            <?php else : ?>
                                <?php
                                printf(
                                    /* translators: %d: number of candles */
                                    esc_html(_n('Er is %d kaarsje aangestoken', 'Er zijn %d kaarsjes aangestoken', $candle_count, 'condoleance-register')),
                                    $candle_count
                                );
                                ?>
                            <?php endif; ?>
                        </span>

                        <?php
                        $comment_count = get_comments_number();
                        ?>
                        <span class="comment-count">
                            <span class="comment-icon">💬</span>
                            <?php if ($comment_count < 1) :
                                printf(
                                    esc_html__('Er zijn nog geen berichten geplaatst', 'condoleance-register')
                                );
                            else:
                                printf(
                                    /* translators: %d: number of comments */
                                    esc_html(_n('Er is %d bericht geplaatst', 'er zijn %d berichten geplaatst', $comment_count, 'condoleance-register')),
                                    $comment_count
                                );
                            endif; ?>
                        </span>
                        <a href="<?php the_permalink(); ?>" class="card-link button">
                            <?php esc_html_e('Condoleer', 'condoleance-register'); ?>
                        </a>
                    </div>

                </article>

            <?php endwhile; ?>
            </div>
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

</div>

<?php
get_footer();
