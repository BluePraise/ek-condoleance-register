<?php

namespace Tahlil\Inc\Frontend;

if (!defined('ABSPATH')) { exit; }

class Reactiesform {
	public function __construct() {
		// add style
		add_action( 'wp_head', array($this, 'pmg_comment_tut_style_cheater') );

		// modify comment form for frontend
		add_action( 'tahlil_reactie_form', array($this, 'modify_standard_comment_form') );
		
		// admin 
		add_action( 'add_meta_boxes_comment', array($this, 'pmg_comment_tut_add_meta_box') );

		// edit comment form
		add_action( 'edit_comment', array($this, 'pmg_comment_tut_edit_comment') );

		// insert comment to db
		add_action( 'comment_post', array($this, 'pmg_comment_tut_insert_comment'), 10, 1 );

		// add single template for cpt_condolances
		add_filter( 'single_template', array($this, 'my_custom_template') );

		// enqueu
		add_action( 'wp_enqueue_scripts', array( $this, 'tahlil_ajax_comments_scripts' ) );
	}


	/**
	 *  Ajax script comments
	 */
	function tahlil_ajax_comments_scripts() {
        wp_enqueue_script(
            '-tahlil-ajax-comments',
            plugin_dir_url( __FILE__ ) . 'js/tahlil-ajax-comments.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );

        $data = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'tahlil_ajax_comments' )
        );

        wp_localize_script( '-tahlil-ajax-comments', 'TAHLIL_AJAX_COMMENTS', $data );
	}

	/**
	 * Modify Standard Wordpress Comment Form
	 */
	public function modify_standard_comment_form()
	{
		add_action( 'comment_form_top', array($this, 'pmg_comment_tut_fields') );
		add_filter( 'comment_form_defaults', array($this, 'modify_commentform_title') );
		add_filter( 'comment_form_default_fields', array($this, 'remove_website_field') );
		add_filter( 'comment_form_field_comment', array($this, 'modify_commentform_comment') );
	}

	// remove website field
	public function remove_website_field($fields) {
    	unset($fields['url']);
    	return $fields;
	}
 
	// title
	public function modify_commentform_title($defaults)
	{
		$defaults['title_reply'] = __( 'Leave a Condolance' );
		$defaults['logged_in_as'] = '';
		return $defaults;
	}

	// title
	public function modify_commentform_comment()
	{
		$defaults = '
		<p class="comment-form-comment">
			<!--label for="comment">Bericht</label-->
			<textarea id="comment" name="comment" aria-required="true"></textarea>
		</p>';
   
		return $defaults;
	}

	// add comment meta
	public function pmg_comment_tut_fields()
	{
		if (is_singular('cpt_condolances')) {
	    	include_once(plugin_dir_path(__FILE__) . '/views/form/reactie.php');
	    }
	}

	/**
	 * Cheating.  Get everything to be styled nicely in Twenty Elevent
	 */
	public function pmg_comment_tut_style_cheater()
	{
	    ?>
	    <style type="text/css">
	    </style>
	    <?php
	}

	/**
	 * Add the title to our admin area, for editing, etc
	 */
	public function pmg_comment_tut_add_meta_box()
	{
	    add_meta_box( 
	    	'pmg-comment-title', 
	    	__( 'Reactie Detail' ), 
	    	array($this, 'pmg_comment_tut_meta_box_cb'), 
	    	'comment', 
	    	'normal', 
	    	'high'
	    );
	}

	/**
	 * Add custom metabox form comment form
	 * admin
	 */
	public function pmg_comment_tut_meta_box_cb( $comment )
	{
		$current_screen = get_current_screen();
		if (isset($current_screen->post_type) && $current_screen->post_type == 'cpt_condolances') {
			echo '<style>.editcomment tr:nth-child(3) {display: none;}</style>';
		}
	    $title = get_comment_meta( $comment->comment_ID, 'pmg_comment_title', true );
	    $type = get_comment_meta( $comment->comment_ID, 'pmg_comment_type', true );
	    $content = get_comment_meta( $comment->comment_ID, 'pmg_comment_content', true );

	    wp_nonce_field( 'pmg_comment_update', 'pmg_comment_update', false );
	    ?>
	    <input type="hidden" name="comment_type" value="reactie">
	    <p>
	        <label for="pmg_comment_title"><?php _e( 'Title' ); ?></label>
	        <input type="text" name="pmg_comment_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
	    </p>
	    <p>
	        <label for="pmg_comment_type"><?php _e( 'Type' ); ?></label>
	        <input type="text" name="pmg_comment_type" value="<?php echo esc_attr( $type ); ?>" class="widefat" />
	    </p>
	    <p>
	        <label for="pmg_comment_content"><?php _e( 'Content' ); ?></label>
	        <textarea rows="8" name="pmg_comment_content" class="widefat"><?php echo $content; ?></textarea>
	    </p>
	    <?php
	}

	/**
	 * Save our comment (from the admin area)
	 */
	public function pmg_comment_tut_edit_comment( $comment_id )
	{
	    if( ! isset( $_POST['pmg_comment_update'] ) || ! wp_verify_nonce( $_POST['pmg_comment_update'], 'pmg_comment_update' ) ) return;
	    
		
		if( isset( $_POST['pmg_comment_title'] ) ) {
    		update_comment_meta( $comment_id, 'pmg_comment_title', esc_attr( $_POST['pmg_comment_title'] ) );
		}

		if( isset( $_POST['pmg_comment_content'] ) ) {
			if ($_POST['pmg_comment_type'] == 'video') {
				if (!isset($_POST['selected_video_audio']) || $_POST['selected_video_audio'] == '') {
					wp_die('Please chooose a video');
				}
				$content = $_POST['selected_video_audio'];
				update_comment_meta( $comment_id, 'pmg_comment_content', $content);
			} else if ($_POST['pmg_comment_type'] == 'music') {
				if (!isset($_POST['selected_video_audio']) || $_POST['selected_video_audio'] == '') {
					wp_die('Please chooose a video');
				}
				$content = $_POST['selected_video_audio'];
				update_comment_meta( $comment_id, 'pmg_comment_content', $content);
			} else {
    			update_comment_meta( $comment_id, 'pmg_comment_content', esc_attr( $_POST['pmg_comment_content'] ) );
			}
		}

		if( isset( $_POST['pmg_comment_type'] ) ) {
    		update_comment_meta( $comment_id, 'pmg_comment_type', esc_attr( $_POST['pmg_comment_type'] ) );
		}
	}

	/**
	 * Save our title (from the front end)
	 */
	public function pmg_comment_tut_insert_comment( $comment_id )
	{
		if ($_POST['comment_parent'] > 0) return $comment_id;

	    if( isset( $_POST['comment_type'] ) ) {
	    	if ($_POST['comment_type'] == 'reactie') {

				// comment title
	    		if( isset( $_POST['pmg_comment_title'] ) ) {
	        		update_comment_meta( $comment_id, 'pmg_comment_title', esc_attr( $_POST['pmg_comment_title'] ) );
				}
				
				// comment content
	    		if( isset( $_POST['pmg_comment_content'] ) ) {
	    			if ($_POST['pmg_comment_type'] == 'video') {
	    				if (!isset($_POST['selected_video_audio']) || $_POST['selected_video_audio'] == '') {
	    					wp_die('Please choose a video');
	    				}
	    				$content = $_POST['selected_video_audio'];
	    				update_comment_meta( $comment_id, 'pmg_comment_content', $content);
	    			} else if ($_POST['pmg_comment_type'] == 'music') {
	    				if (!isset($_POST['selected_video_audio']) || $_POST['selected_video_audio'] == '') {
	    					wp_die('Please choose a music video');
	    				}
	    				$content = $_POST['selected_video_audio'];
	    				update_comment_meta( $comment_id, 'pmg_comment_content', $content);
	    			} else {
	        			update_comment_meta( $comment_id, 'pmg_comment_content', esc_attr( $_POST['pmg_comment_content'] ) );
	    			}
				}
				
				// comment type
	    		if( isset( $_POST['pmg_comment_type'] ) ) {
	        		update_comment_meta( $comment_id, 'pmg_comment_type', esc_attr( $_POST['pmg_comment_type'] ) );
				}
				
				// begin update comment with those custom fields
	        	wp_update_comment(array(
	        		'comment_ID' => $comment_id,
	        		'comment_type' => 'reactie'
	        	));
	    	}
	    }
	}

	/**
	 * add single template for cpt_condolances
	 */
	public function my_custom_template($template) {
	    global $post;

	    if ($post->post_type == "cpt_condolances" && $template !== locate_template(array("single-cpt_condolances.php"))){
        	return plugin_dir_path( __FILE__ ) . "views/single-cpt_condolances.php";
    	}
	    return $template;
	}
}