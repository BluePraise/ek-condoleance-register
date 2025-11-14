<?php
/**
 * Single Condoleance Template
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

while (have_posts()) :
    the_post();

    $birth_date = get_post_meta(get_the_ID(), 'condoleance_birth_date', true);
    $death_date = get_post_meta(get_the_ID(), 'condoleance_death_date', true);
    $photos = get_post_meta(get_the_ID(), 'condoleance_photos', true);
    $candles_data = get_post_meta(get_the_ID(), 'condoleance_candles_data', true);
    $candle_count = is_array($candles_data) ? ($candles_data['count'] ?? 0) : 0;
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('condoleance-single'); ?>>

        <header class="condoleance-header">
            <h1 class="condoleance-title"><?php the_title(); ?></h1>

            <?php if ($birth_date || $death_date) : ?>
                <div class="condoleance-dates">
                    <?php if ($birth_date) : ?>
                        <span class="birth-date">
                            <span class="date-label"><?php esc_html_e('Geboortedatum:', 'condoleance-register'); ?></span>
                            <time><?php echo esc_html($birth_date); ?></time>
                        </span>
                    <?php endif; ?>

                    <?php if ($death_date) : ?>
                        <span class="death-date">
                            <span class="date-label"><?php esc_html_e('Overlijdensdatum:', 'condoleance-register'); ?></span>
                            <time><?php echo esc_html($death_date); ?></time>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </header>

        <?php if (has_post_thumbnail()) : ?>
            <div class="condoleance-featured-image">
                <?php the_post_thumbnail('full-width', ['class' => 'memorial-photo']); ?>
            </div>
        <?php endif; ?>

        <div class="condoleance-content">
            <?php the_content(); ?>
        </div>

        <?php if (!empty($photos) && is_array($photos)) : ?>
            <div class="condoleance-gallery">
            <h2 class="gallery-title"><?php esc_html_e('Fotogalerij', 'condoleance-register'); ?></h2>
            <?php // if there is only one photo, apply a different class ?>
                <div class="<?php echo count($photos) === 1 ? 'gallery single-photo' : 'gallery-grid multiple-photos'; ?>">
                    <?php foreach ($photos as $photo_id => $photo_url) : ?>
                        <div class="gallery-item">
                            <a href="<?php echo esc_url($photo_url); ?>" data-lightbox="condoleance-gallery">
                                <?php echo wp_get_attachment_image($photo_id, 'medium', false, ['class' => 'gallery-image']); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="condoleance-candles-section">
            <div class="candle-widget">
                <div class="candle-icon">🕯️</div>
                <div class="candle-count-display">
                    <span class="condoleance-candle-count" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
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
                <button
                    class="condoleance-light-candle button"
                    data-post-id="<?php echo esc_attr(get_the_ID()); ?>"
                    aria-label="<?php esc_attr_e('Kaarsje aansteken', 'condoleance-register'); ?>"
                >
                    <?php esc_html_e('Kaarsje aansteken', 'condoleance-register'); ?>
                </button>
            </div>
        </div>

        <?php
        // Comments section
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>

    </article>

    <?php
endwhile;

get_footer();
