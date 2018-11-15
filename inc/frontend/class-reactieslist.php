<?php
namespace Tahlil\Inc\Frontend;

if (!defined('ABSPATH')) { exit; }

/**
 * 
 */
class ReactiesList
{
	public function __construct()
	{
        add_action( 'reactie_comment_content', array($this, 'reactie_comment_content') );
        add_filter( 'archive_template', array($this, 'get_archive_template') );
        add_filter( 'comment_text',     array($this, 'displayAttachment'), 10, 3);
	}

    /**
     * Action 'reactie_comment_content'
     */
    public function reactie_comment_content()
    {
        add_action( 'comment_form_top',  array($this, 'displayBeforeForm') );
        add_filter( 'comment_text', array($this, 'pmg_comment_reactie_to_text'), 99, 2 );
    }

    /**
     * hack
     */
    public function displayBeforeForm()
    {
        echo '</form><form action="'. get_home_url() .'/wp-comments-post.php" method="POST" enctype="multipart/form-data" id="attachmentForm" class="comment-form" novalidate>';
    }

    /**
     * Displays attachment in comment, according to
     * position selected in settings, and according to way selected in admin.
     *
     * @param $comment
     * @return string
     */
    public function displayAttachment($comment)
    {
        if (!is_admin()) return $comment;

        $attachmentId = get_comment_meta(get_comment_ID(), 'attachmentId', TRUE);
        if(is_numeric($attachmentId) && !empty($attachmentId)){
            // atachement info
            $attachmentLink = wp_get_attachment_url($attachmentId);
            $attachmentMeta = wp_get_attachment_metadata($attachmentId);
            $attachmentName = basename(get_attached_file($attachmentId));
            $attachmentType = get_post_mime_type($attachmentId);
            $attachmentRel  = '';

            // let's do wrapper html
            $contentBefore  = '<div class="attachmentFile"><p>';
            $contentAfter   = '</p><div class="clear clearfix"></div></div>';
            $contentInner = $attachmentName;
            $contentInnerFinal = '<a '.$attachmentRel.' class="attachmentLink" target="_blank" href="'. admin_url() .'/upload.php?item='.$attachmentId.'" title="Download: '. $attachmentName .'">';
            $contentInnerFinal .= $contentInner;
            $contentInnerFinal .= '</a>';

            // bring a sellotape, this needs taping together
            $contentInsert = $contentBefore . $contentInnerFinal . $contentAfter;

            // attachment comment position
            $comment = $contentInsert . $comment;
        }

        return $comment;
    }

    /**
     * Hook in way late to avoid colliding with default
     * WordPress comment text filters
     */
    public function pmg_comment_reactie_to_text( $text, $comment )
    {
        if ( is_admin() ) return $text;

        $title = '';
        $content = '';
        $type = get_comment_meta( $comment->comment_ID, 'pmg_comment_type', true );

        // title
        if (get_comment_meta( $comment->comment_ID, 'pmg_comment_title', true )) {
            $title = get_comment_meta( $comment->comment_ID, 'pmg_comment_title', true );
            $title = '<span class="reactie-heading">Title</span><p>' . esc_attr( $title ) . '</p>';
        }

        // video or music
        if (get_comment_meta( $comment->comment_ID, 'pmg_comment_content', true )) {
            $content_meta = get_comment_meta( $comment->comment_ID, 'pmg_comment_content', true );
            $content = wpautop($content_meta);

            if ($type == 'video' || $type == 'music') {
                $content = '<iframe width="300" height="200" src="https://www.youtube.com/embed/'.$content_meta.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }

            $content = '<div class="reactie-caption '.$type.'"><span class="reactie-heading">Content</span>' . $content . '</div>';
        }

        // photo
        if (get_comment_meta( $comment->comment_ID, 'attachmentId', true )) {
            $attachmentId = get_comment_meta(get_comment_ID(), 'attachmentId', TRUE);
            if ( is_numeric($attachmentId) && !empty($attachmentId) ) {
                // atachement info
                $attachmentLink = wp_get_attachment_url($attachmentId);
                $attachmentMeta = wp_get_attachment_metadata($attachmentId);
                $attachmentName = basename(get_attached_file($attachmentId));
                $attachmentType = get_post_mime_type($attachmentId);
                
                // display
                $contentBefore  = '<div class="attachmentFile">';
                $contentAfter   = '</div>';
                $attachmentRel = 'rel="lightbox"';
                $contentInner = wp_get_attachment_image($attachmentId, 'full');
                $contentInnerFinal = '<a '.$attachmentRel.' class="attachmentLink" target="_blank" href="'. $attachmentLink .'" title="Download: '. $attachmentName .'">';
                $contentInnerFinal .= $contentInner;
                $contentInnerFinal .= '</a>';

                $content = $contentBefore . $contentInnerFinal . $contentAfter;

                $content = '<div class="reactie-caption '.$type.'"><span class="reactie-heading">Content</span>' . $content . '</div>';
            }
        }

        if ($type == 'quote' || $type == 'poetry' || $type == 'words') {
            $text = '<div class="reactie-caption '.$type.'">' . $text . '</div>';               
        }


        $text = $title . $content . '<span class="reactie-heading">Message</span>'. $text;
        return $text;
    }

    /**
     * use archive template
     */
    public function get_archive_template( $template ) {
         global $post;

        if (is_post_type_archive ( 'cpt_condolances' ) && $template !== locate_template(array("archive-cpt_condolances.php"))){
            return plugin_dir_path( __FILE__ ) . "views/archive-cpt_condolances.php";
        }
        return $template;
    }

}