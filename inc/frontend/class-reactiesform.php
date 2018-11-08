<?php

namespace Tahlil\Inc\Frontend;

class Reactiesform {
	public function __construct() {
		add_action( 'comment_form_logged_in_after', array($this, 'pmg_comment_tut_fields') );
		add_action( 'comment_form_after_fields', array($this, 'pmg_comment_tut_fields') );
		add_action( 'wp_head', array($this, 'pmg_comment_tut_style_cheater') );
		add_action( 'add_meta_boxes_comment', array($this, 'pmg_comment_tut_add_meta_box') );
		add_action( 'edit_comment', array($this, 'pmg_comment_tut_edit_comment') );
		add_action( 'comment_post', array($this, 'pmg_comment_tut_insert_comment'), 10, 1 );
		add_filter( 'comment_text', array($this, 'pmg_comment_tut_add_title_to_text'), 99, 2 );
	}

	/**
	 * Add our field to the comment form
	 */
	public function pmg_comment_tut_fields()
	{
		if (is_singular('cpt_condolances')) {
	    ?>
	    <p class="comment-form-title">
	    	<input type="hidden" name="comment_type" value="reactie">
	        <label for="pmg_comment_title"><?php _e( 'Title' ); ?></label>
	        <input type="text" name="pmg_comment_title" id="pmg_comment_title" />
	    </p>
	    <?php
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
	    	__( 'Comment Title' ), 
	    	array($this, 'pmg_comment_tut_meta_box_cb'), 
	    	'comment', 
	    	'normal', 
	    	'high'
	    );
	}

	public function pmg_comment_tut_meta_box_cb( $comment )
	{
	    $title = get_comment_meta( $comment->comment_ID, 'pmg_comment_title', true );
	    wp_nonce_field( 'pmg_comment_update', 'pmg_comment_update', false );
	    ?>
	    <p>
	        <label for="pmg_comment_title"><?php _e( 'Comment Title' ); ?></label>
	        <input type="text" name="pmg_comment_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
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
	        	wp_update_comment(array(
	        		'comment_ID' => $comment_id,
	        		'comment_type' => 'reactie'
	        	));
	    	}
	    }
	}

	/**
	 * add our headline to the comment text
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

	// update 2012-09-12 to show how to put the title in the comments list table
}