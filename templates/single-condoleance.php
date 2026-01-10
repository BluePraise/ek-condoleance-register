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
    $hero_image = get_post_meta(get_the_ID(), 'condoleance_hero_image', true);
    $photos = get_post_meta(get_the_ID(), 'condoleance_photos', true);
    $candles_data = get_post_meta(get_the_ID(), 'condoleance_candles_data', true);
    $candle_count = is_array($candles_data) ? ($candles_data['count'] ?? 0) : 0;
?>

    <div class="hero-banner condoleance-hero">
        <div class="container">
            <h1 class="banner-heading"><?php the_title(); ?></h1>
        </div>
    </div>

    <div class="block-content">
        <div class="container-fluid">
            <div class="col-xs-12">

                <?php do_action('condoleance_before_content', get_the_ID()); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('condoleance-single'); ?>>
                    <div class="condoleance-content" style="margin-bottom: 40px">
                        <?php the_content(); ?>
                    </div>
                    <?php if (!empty($photos) && is_array($photos)) : ?>
                        <?php do_action('condoleance_before_gallery', get_the_ID()); ?>
                        <div class="condoleance-gallery">
                            <?php
                            foreach ($photos as $photo_id => $photo_url) :
                                $caption = wp_get_attachment_caption($photo_id);
                            ?>
                                <div class="condoleance-gallery__item">
                                    <a href="<?php echo esc_url($photo_url); ?>" data-lightbox="condoleance-gallery" data-title="<?php echo esc_attr($caption); ?>">
                                        <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($caption); ?>" class="condoleance-gallery__image">
                                        <?php if ($caption) : ?>
                                            <span class="condoleance-gallery__caption"><?php echo esc_html($caption); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php do_action('condoleance_after_gallery', get_the_ID()); ?>
                    <?php endif; ?>

                    <div class="condoleance-info">
                        <div class="condoleance-candle-section">
                            <?php
                            // Display candle GIF
                            $candle_gif = apply_filters('condoleance_candle_gif', site_url('/wp-content/uploads/2020/02/kaarsje.gif'));
                            ?>
                            <img src="<?php echo esc_url($candle_gif); ?>" alt="<?php esc_attr_e('Gif van een kaarsje', 'condoleance-register'); ?>" class="condoleance-info__candle-gif">

                            <div class="candle-count-display">
                                <span class="candle-label">
                                    <span class="condoleance-candle-count" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                                        <?php echo esc_html($candle_count); ?>
                                    </span>
                                    <?php echo esc_html(_n('kaarsje aangestoken', 'kaarsjes aangestoken', $candle_count, 'condoleance-register')); ?>
                                </span>
                            </div>
                            <button
                                class="condoleance-light-candle button"
                                data-post-id="<?php echo esc_attr(get_the_ID()); ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#candleModal"
                                aria-label="<?php esc_attr_e('Steek een kaarsje aan', 'condoleance-register'); ?>">
                                <?php esc_html_e('Steek een kaarsje aan', 'condoleance-register'); ?>
                            </button>
                            <div class="condoleance-notification alert d-none mt-5" role="alert"></div>
                            <!-- Bootstrap Modal -->
                            <div class="modal fade" id="candleModal" tabindex="-1" aria-labelledby="candleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="candleModalLabel"><?php esc_html_e('Steek een kaarsje aan', 'condoleance-register'); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Form for lighting a candle -->
                                            <form class="condoleance-light-candle-form" method="post">
                                                <input type="hidden" name="post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label"><?php esc_html_e('Jouw naam', 'condoleance-register'); ?></label>
                                                    <input type="text" class="form-control" name="name" id="name" required>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" name="anonymous" id="anonymous" value="0">
                                                    <label class="form-check-label" for="anonymous">Ik wil anoniem blijven.</label>
                                                </div>
                                                <button type="submit" class="button btn btn-primary w-100"><?php esc_html_e('Bevestig', 'condoleance-register'); ?></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php do_action('condoleance_before_comments', get_the_ID()); ?>

                    <?php
                    // Support for legacy Tahlil plugin hooks
                    if (has_action('reactie_comment_content') || has_action('tahlil_reactie_form')) {
                        do_action('reactie_comment_content');
                        do_action('tahlil_reactie_form');
                    }

                    // Standard WordPress comments
                    if (comments_open() || get_comments_number()) {
                        comments_template();
                    }
                    ?>

                    <?php do_action('condoleance_after_comments', get_the_ID()); ?>

                </article>

                <?php do_action('condoleance_after_content', get_the_ID()); ?>

            </div>
        </div>
    </div>

<?php
endwhile;

get_footer();
