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
        add_filter('wp_list_comments_args', [$this, 'set_custom_comment_callback']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_comment_scripts']);
        add_action('comment_form_top', [$this, 'add_comment_type_selector']);
        add_action('comment_form_before', [$this, 'open_comment_modal']);
        add_action('comment_form_after', [$this, 'close_comment_modal']);
        add_action('condoleance_before_comments', [$this, 'add_modal_trigger_button']);
        add_filter('comments_template', [$this, 'load_custom_comments_template']);
    }

    /**
     * Load custom comments template for condoleance posts.
     *
     * @since 2.0.0
     * @param string $template Path to comments template.
     * @return string Modified template path.
     */
    public function load_custom_comments_template(string $template): string
    {
        if (is_singular('condoleance')) {
            $plugin_template = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/comments.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    /**
     * Add button to trigger comment modal.
     *
     * @since 2.0.0
     * @return void
     */
    public function add_modal_trigger_button(): void
    {
        if (!is_singular('condoleance')) {
            return;
        }
        ?>
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#commentModal">
                <?php esc_html_e('Plaats een bericht', 'condoleance-register'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Open Bootstrap modal wrapper before comment form.
     *
     * @since 2.0.0
     * @return void
     */
    public function open_comment_modal(): void
    {
        if (!is_singular('condoleance')) {
            return;
        }
        ?>
        <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commentModalLabel"><?php esc_html_e('Plaats een bericht', 'condoleance-register'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
        <?php
    }

    /**
     * Close Bootstrap modal wrapper after comment form.
     *
     * @since 2.0.0
     * @return void
     */
    public function close_comment_modal(): void
    {
        if (!is_singular('condoleance')) {
            return;
        }
        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue comment-related scripts and styles.
     *
     * @since 2.0.0
     * @return void
     */
    public function enqueue_comment_scripts(): void
    {
        if (!is_singular('condoleance')) {
            return;
        }

        wp_enqueue_media();
    }

    /**
     * Add comment type selector interface.
     *
     * @since 2.0.0
     * @return void
     */
    public function add_comment_type_selector(): void
    {
        if (!is_singular('condoleance')) {
            return;
        }
        ?>
        <div id="comment-type-selector" class="mb-4">
            <label class="d-block form-label fw-bold text-uppercase mb-2"><?php esc_html_e('Voeg je bericht of herinnering toe met:', 'condoleance-register'); ?></label>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary comment-type-btn active" data-type="text">
                    <?php esc_html_e('Tekst', 'condoleance-register'); ?>
                </button>
                <button type="button" class="btn btn-outline-primary comment-type-btn" data-type="photo">
                    <?php esc_html_e('Foto', 'condoleance-register'); ?>
                </button>
                <button type="button" class="btn btn-outline-primary comment-type-btn" data-type="video">
                    <?php esc_html_e('Video', 'condoleance-register'); ?>
                </button>
                <button type="button" class="btn btn-outline-primary comment-type-btn" data-type="quote">
                    <?php esc_html_e('Citaat', 'condoleance-register'); ?>
                </button>
            </div>
        </div>

        <div id="photo-upload-section" class="mb-3" style="display: none;">
            <label class="form-label"><?php esc_html_e('Upload foto:', 'condoleance-register'); ?></label>
            <input type="file" id="comment_photo" name="comment_photo" accept="image/*" class="form-control" />
            <input type="hidden" id="comment_attachment_id" name="comment_attachment_id" />
        </div>

        <div id="video-url-section" class="mb-3" style="display: none;">
            <label class="form-label"><?php esc_html_e('YouTube video URL:', 'condoleance-register'); ?></label>
            <input type="url" id="comment_video_url" name="comment_video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." />
            <small class="form-text text-muted"><?php esc_html_e('Plak een YouTube video link', 'condoleance-register'); ?></small>
        </div>

        <input type="hidden" id="comment_type" name="comment_type" value="text" />

        <script>
        jQuery(document).ready(function($) {
            $('.comment-type-btn').on('click', function() {
                $('.comment-type-btn').removeClass('active');
                $(this).addClass('active');

                var type = $(this).data('type');
                $('#comment_type').val(type);

                // Hide all special sections
                $('#photo-upload-section, #video-url-section').hide();

                // Show relevant section
                if (type === 'photo') {
                    $('#photo-upload-section').show();
                } else if (type === 'video') {
                    $('#video-url-section').show();
                }
            });

            // Handle photo upload
            $('#comment_photo').on('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;

                var formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'upload_attachment');

                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce("media-form"); ?>');
                    },
                    success: function(response) {
                        if (response.success && response.data.id) {
                            $('#comment_attachment_id').val(response.data.id);
                        }
                    }
                });
            });
        });
        </script>
        <?php
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

        $defaults['title_reply'] = __('Plaats een bericht', 'condoleance-register');
        $defaults['logged_in_as'] = '';
        $defaults['title_reply_before'] = '';
        $defaults['title_reply_after'] = '';
        $defaults['label_submit'] = __('Plaats bericht', 'condoleance-register');
        $defaults['submit_button'] = '<button type="submit" class="btn btn-primary">%4$s</button>';

        // Remove website field and cookies checkbox
        $fields = $defaults['fields'] ?? [];
        unset($fields['url']);
        unset($fields['cookies']);
        $defaults['fields'] = $fields;

        // Add title field before comment field
        $defaults['comment_field'] = '<p class="comment-form-title">' .
            '<label for="comment_title">' . __('Titel', 'condoleance-register') . '</label>' .
            '<input type="text" id="comment_title" name="comment_title" class="form-control" placeholder="' . esc_attr__('Type hier je onderwerp...', 'condoleance-register') . '" required />' .
            '</p>' .
            '<p class="comment-form-comment">' .
            '<label for="comment">' . __('Bericht', 'condoleance-register') . '</label>' .
            '<textarea id="comment" name="comment" cols="45" rows="8" required="required" class="form-control" placeholder="' . esc_attr__('Schrijf je bericht hier...', 'condoleance-register') . '"></textarea>' .
            '</p>';

        return $defaults;
    }

    /**
     * Set custom comment callback for condoleance posts.
     *
     * @since 2.0.0
     * @param array<string, mixed> $args wp_list_comments arguments.
     * @return array<string, mixed> Modified arguments.
     */
    public function set_custom_comment_callback(array $args): array
    {
        if (is_singular('condoleance')) {
            $args['callback'] = [$this, 'custom_comment_display'];
        }
        return $args;
    }

    /**
     * Custom comment display callback (removes "says").
     *
     * @since 2.0.0
     * @param \WP_Comment $comment Comment object.
     * @param array<string, mixed> $args Comment arguments.
     * @param int $depth Comment depth.
     * @return void
     */
    public function custom_comment_display(\WP_Comment $comment, array $args, int $depth): void
    {
        $tag = ('div' === $args['style']) ? 'div' : 'li';
        ?>
        <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent', $comment); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <div class="comment-meta">
                    <div class="comment-author vcard">
                        <?php
                        if (0 != $args['avatar_size']) {
                            echo get_avatar($comment, $args['avatar_size']);
                        }
                        ?>
                        <span class="fn"><?php echo get_comment_author_link($comment); ?></span>
                    </div>
                    <div class="comment-date">
                        <a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>">
                            <time datetime="<?php comment_time('c'); ?>">
                                <?php
                                /* translators: 1: comment date, 2: comment time */
                                printf(__('%1$s om %2$s'), get_comment_date('', $comment), get_comment_time());
                                ?>
                            </time>
                        </a>
                        <?php edit_comment_link(__('Wijzigen'), '<span class="edit-link">', '</span>'); ?>
                    </div>
                </div>

                <?php
                // Get comment metadata (support both new and legacy Tahlil keys)
                $comment_title = get_comment_meta($comment->comment_ID, 'condoleance_comment_title', true);
                if (empty($comment_title)) {
                    $comment_title = get_comment_meta($comment->comment_ID, 'pmg_comment_title', true);
                }

                $comment_type = get_comment_meta($comment->comment_ID, 'condoleance_comment_type', true);
                if (empty($comment_type)) {
                    $comment_type = get_comment_meta($comment->comment_ID, 'pmg_comment_type', true);
                }

                $attachment_id = get_comment_meta($comment->comment_ID, 'condoleance_attachment_id', true);
                if (empty($attachment_id)) {
                    $attachment_id = get_comment_meta($comment->comment_ID, 'attachmentId', true);
                }

                $video_url = get_comment_meta($comment->comment_ID, 'condoleance_video_url', true);
                if (empty($video_url)) {
                    $video_url = get_comment_meta($comment->comment_ID, 'pmg_comment_content', true);
                }

                // Display title if present
                if ($comment_title) :
                ?>
                    <div class="comment-title-section">
                        <span class="comment-heading me-2"><?php esc_html_e('Titel', 'condoleance-register'); ?></span>
                        <p class="comment-title-text"><?php echo esc_html($comment_title); ?></p>
                    </div>
                <?php endif; ?>

                <?php
                // Display photo attachment if present
                if ($attachment_id && $comment_type === 'photo') :
                    $image_url = wp_get_attachment_url($attachment_id);
                    $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                ?>
                    <div class="comment-attachment photo">
                        <a href="<?php echo esc_url($image_url); ?>" data-lightbox="comment-<?php echo $comment->comment_ID; ?>" alt="<?php echo esc_attr($image_alt); ?>">
                            <?php echo wp_get_attachment_image($attachment_id, 'large', false, ['class' => 'img-fluid']); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php
                // Display YouTube video if present
                if ($video_url && $comment_type === 'video') :
                    $video_id = $this->extract_youtube_id($video_url);
                    if ($video_id) :
                ?>
                    <div class="comment-attachment video">
                        <iframe width="100%" height="315" src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                <?php
                    endif;
                endif;
                ?>

                <div class="comment-content <?php echo esc_attr($comment_type ? 'comment-type-' . $comment_type : ''); ?>">
                    <span class="comment-heading"><?php esc_html_e('Bericht', 'condoleance-register'); ?></span>
                    <?php comment_text(); ?>
                </div>
            </article>
        <?php
    }

    /**
     * Extract YouTube video ID from URL.
     *
     * @since 2.0.0
     * @param string $url YouTube URL.
     * @return string|false Video ID or false.
     */
    private function extract_youtube_id(string $url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return false;
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
        if (!is_singular('condoleance')) {
            return;
        }

        // Save title
        if (isset($_POST['comment_title']) && !empty($_POST['comment_title'])) {
            $title = sanitize_text_field($_POST['comment_title']);
            update_comment_meta($comment_id, 'condoleance_comment_title', $title);
        }

        // Save comment type
        if (isset($_POST['comment_type']) && !empty($_POST['comment_type'])) {
            $type = sanitize_text_field($_POST['comment_type']);
            update_comment_meta($comment_id, 'condoleance_comment_type', $type);
        }

        // Save attachment ID (for photos)
        if (isset($_POST['comment_attachment_id']) && !empty($_POST['comment_attachment_id'])) {
            $attachment_id = absint($_POST['comment_attachment_id']);
            update_comment_meta($comment_id, 'condoleance_attachment_id', $attachment_id);
        }

        // Save video URL
        if (isset($_POST['comment_video_url']) && !empty($_POST['comment_video_url'])) {
            $video_url = esc_url_raw($_POST['comment_video_url']);
            update_comment_meta($comment_id, 'condoleance_video_url', $video_url);
        }
    }
}
