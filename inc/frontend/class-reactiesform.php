<?php

namespace Tahlil\Inc\Frontend;

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

		// add title to comment text
		// add_filter( 'comment_text', array($this, 'pmg_comment_tut_add_title_to_text'), 99, 2 );

		// add single template for cpt_condolances
		add_filter('single_template', array($this, 'my_custom_template'));
	}

	/**
	 * Modify Standard Wordpress Comment Form
	 */
	public function modify_standard_comment_form()
	{
		add_action( 'comment_form_top', array($this, 'pmg_comment_tut_fields') );
		add_filter( 'comment_form_defaults', array($this, 'modify_commentform_title') );
		add_filter( 'comment_form_field_comment', array($this, 'modify_commentform_comment') );
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
		<p class="comment-form-title">
			<input type="hidden" name="comment_type" value="reactie">
	    	<label for="pmg_comment_title">Title</label>
	    	<input type="text" aria-required="true" placeholder="Title..." name="pmg_comment_title" id="pmg_comment_title" />
		</p>
		<p class="comment-form-comment">
			<label for="comment">Message</label>
			<textarea placeholder="message..." id="comment" name="comment" aria-required="true"></textarea>
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

	    // add_meta_box('shiba_comment_xtra_box', __('Extra Arguments'), array($this, 'my_metabox'), 'comment', 'normal');
	}

	public function my_metabox($comment) {
    ?>
        <table class="form-table editcomment comment_xtra">
        <tbody>
        <tr valign="top">
            <td class="first"><?php _e( 'Comment Post:' ); ?></td>
            <td><input type="text" id="shiba_comment_post_ID" name="shiba_comment_post_ID" size="10" class="code" value="<?php echo esc_attr($comment->comment_post_ID); ?>" tabindex="1" /></td>
        </tr>
        <tr valign="top">
            <td class="first"><?php _e( 'Comment Parent:' ); ?></td>
            <td><input type="text" id="shiba_comment_parent" name="shiba_comment_parent" size="10" class="code" value="<?php echo esc_attr($comment->comment_parent); ?>" tabindex="1" /></td>
        </tr>
        <tr valign="top">
            <td class="first"><?php _e( 'Author IP:' ); ?></td>
            <td><input type="text" id="shiba_comment_author_IP" name="shiba_comment_author_IP" size="20" class="code" value="<?php echo esc_attr($comment->comment_author_IP); ?>" tabindex="1" /></td>
        </tr>
       </tbody>
       </table>
    <?php
}

	/**
	 * Add custom metabox form comment form
	 * admin
	 */
	public function pmg_comment_tut_meta_box_cb( $comment )
	{
	    $title = get_comment_meta( $comment->comment_ID, 'pmg_comment_title', true );
	    $type = get_comment_meta( $comment->comment_ID, 'pmg_comment_type', true );
	    $content = get_comment_meta( $comment->comment_ID, 'pmg_comment_content', true );

	    wp_nonce_field( 'pmg_comment_update', 'pmg_comment_update', false );
	    ?>
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
	    
	    if( isset( $_POST['comment_type'] ) ) {
	    	if ($_POST['comment_type'] == 'reactie') {
	    		if( isset( $_POST['pmg_comment_title'] ) ) {
	        		update_comment_meta( $comment_id, 'pmg_comment_title', esc_attr( $_POST['pmg_comment_title'] ) );
	    		}
	        	wp_update_comment( array(
	        		'comment_ID' => $comment_id,
	        		'comment_type' => 'reactie'
	        	));
	    	}
	    }
	}

	/**
	 * Save our title (from the front end)
	 */
	public function pmg_comment_tut_insert_comment( $comment_id )
	{
	    
	    if( isset( $_POST['comment_type'] ) ) {
	    	if ($_POST['comment_type'] == 'reactie') {
	    		if( isset( $_POST['pmg_comment_title'] ) ) {
	        		update_comment_meta( $comment_id, 'pmg_comment_title', esc_attr( $_POST['pmg_comment_title'] ) );
	    		}
	    		if( isset( $_POST['pmg_comment_content'] ) ) {
	        		update_comment_meta( $comment_id, 'pmg_comment_content', esc_attr( $_POST['pmg_comment_content'] ) );
	    		}
	    		if( isset( $_POST['pmg_comment_type'] ) ) {
	        		update_comment_meta( $comment_id, 'pmg_comment_type', esc_attr( $_POST['pmg_comment_type'] ) );
	    		}
	        	wp_update_comment(array(
	        		'comment_ID' => $comment_id,
	        		'comment_type' => 'reactie'
	        	));
	    	}
	    }
	}

	/**
	 * Hook in way late to avoid colliding with default
	 * WordPress comment text filters
	 */
	public function pmg_comment_tut_add_title_to_text( $text, $comment )
	{
	    if( is_admin() ) return $text;
	    if( $title = get_comment_meta( $comment->comment_ID, 'pmg_comment_title', true ) )
	    {
	        $title = '<h3>' . esc_attr( $title ) . '</h3>';
	        $text = $title . $text;
	    }
	    return $text;
	}

	/**
	 * add single template for reacties
	 */
	public function my_custom_template($template) {
	    global $post;

	    if ($post->post_type == "cpt_condolances" && $template !== locate_template(array("single-cpt_condolances.php"))){
        	return plugin_dir_path( __FILE__ ) . "views/single-cpt_condolances.php";
    	}
	    return $template;
	}
}