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

    // Allow themes to override hero styling
    $hero_enabled = apply_filters('condoleance_hero_enabled', true);
    ?>

    <?php if ($hero_enabled) : ?>
        <div class="hero-banner condoleance-hero">
            <div class="container">
                <h1 class="banner-heading"><?php the_title(); ?></h1>
            </div>
        </div>
    <?php endif; ?>

    <div class="block-content">
        <div class="container-fluid">
            <div class="col-xs-12">

                <?php do_action('condoleance_before_content', get_the_ID()); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('condoleance-single'); ?>>

                    <?php if (!$hero_enabled) : ?>
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
                    <?php endif; ?>

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

                    <div class="condoleance-content" style="margin-bottom: 40px">
                        <?php the_content(); ?>
                    </div>

                    <div class="condoleance-info">
                        <div class="condoleance-info__meta">
                            <h6 class="condoleance-info__heading"><?php esc_html_e('Informatie', 'condoleance-register'); ?></h6>
                            <?php echo do_shortcode('[condoleance_meta]'); ?>
                        </div>

                        <div class="condoleance-info__candle">
                            <?php
                            $candle_gif = apply_filters('condoleance_candle_gif', site_url('/wp-content/uploads/2020/02/kaarsje.gif'));
                            if ($candle_gif && @getimagesize($candle_gif)) :
                                ?>
                                <img src="<?php echo esc_url($candle_gif); ?>" alt="<?php esc_attr_e('Gif van een kaarsje', 'condoleance-register'); ?>" class="condoleance-info__candle-gif">
                            <?php else : ?>
                                <div class="condoleance-info__candle-icon">🕯️</div>
                            <?php endif; ?>
                        </div>

                        <div class="condoleance-info__widget">
                            <?php echo do_shortcode('[light_a_candle]'); ?>
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
