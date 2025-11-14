<?php
/**
 * Comments Handler
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
 * Handles condoleance comments functionality.
 *
 * @since 2.0.0
 */
class Comments
{
    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_filter('comment_form_defaults', [$this, 'customize_comment_form']);
        add_action('comment_post', [$this, 'save_comment_meta'], 10, 1);
        // More hooks will be added as we build out features.
    }

    /**
     * Customize comment form for condoleances.
     *
     * @since 2.0.0
     * @param array<string, mixed> $defaults Form defaults.
     * @return array<string, mixed> Modified defaults.
     */
    public function customize_comment_form(array $defaults): array
    {
        if (!is_singular('condoleance')) {
            return $defaults;
        }

        $defaults['title_reply'] = __('Berichten', 'condoleance-register');
        $defaults['comment_field'] = sprintf(
            '<p class="comment-form-comment"><label for="comment">%s</label><textarea id="comment" name="comment" cols="45" rows="8" required="required"></textarea></p>',
            esc_html__('Uw bericht', 'condoleance-register')
        );

        return $defaults;
    }

    /**
     * Save comment meta data.
     *
     * @since 2.0.0
     * @param int $comment_id Comment ID.
     * @return void
     */
    public function save_comment_meta(int $comment_id): void
    {
        // Implementation will follow in next phase.
    }
}
