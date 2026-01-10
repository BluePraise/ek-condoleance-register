<?php
/**
 * Condoleance Comments Template
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

// Exit if accessed directly or if password protected.
if (!defined('ABSPATH') || post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <div class="comments-header mb-4">
            <h3 class="comments-title">
                <?php
                $comments_number = get_comments_number();
                if (1 === $comments_number) {
                    echo __('Reactie <span>(1)</span>', 'condoleance-register');
                } else {
                    printf(
                        __('Reacties <span>(%s)</span>', 'condoleance-register'),
                        number_format_i18n($comments_number)
                    );
                }
                ?>
            </h3>
        </div>

        <ol class="commentlist">
            <?php
            wp_list_comments(
                array(
                    'style'      => 'ol',
                    'short_ping' => true,
                )
            );
            ?>
        </ol>

        <?php the_comments_navigation(); ?>

    <?php endif; ?>

    <?php
    // If comments are closed and there are comments, leave a note.
    if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
        ?>
        <p class="no-comments"><?php _e('Reacties zijn gesloten.', 'condoleance-register'); ?></p>
    <?php endif; ?>

    <?php
    // Display the comment form (will be wrapped in modal by the plugin).
    comment_form();
    ?>

</div><!-- #comments -->
