<?php

$post_type = 'cpt_condolances';

/**
 * Custom post type registration
 */
function cpt_condolances() {

	$labels = array(
		'name'                  => _x( 'Condolances', 'Post Type General Name', 'tahlil' ),
		'singular_name'         => _x( 'Condolance', 'Post Type Singular Name', 'tahlil' ),
		'menu_name'             => __( 'Register Condolances', 'tahlil' ),
		'name_admin_bar'        => __( 'Condolances', 'tahlil' ),
		'archives'              => __( 'Item Archives', 'tahlil' ),
		'attributes'            => __( 'Item Attributes', 'tahlil' ),
		'parent_item_colon'     => __( 'Parent Item:', 'tahlil' ),
		'all_items'             => __( 'All Deceased Persons', 'tahlil' ),
		'add_new_item'          => __( 'Add New Person', 'tahlil' ),
		'add_new'               => __( 'Add New Person', 'tahlil' ),
		'new_item'              => __( 'New Item', 'tahlil' ),
		'edit_item'             => __( 'Edit Item', 'tahlil' ),
		'update_item'           => __( 'Update Item', 'tahlil' ),
		'view_item'             => __( 'View Item', 'tahlil' ),
		'view_items'            => __( 'View Items', 'tahlil' ),
		'search_items'          => __( 'Search Item', 'tahlil' ),
		'not_found'             => __( 'Not found', 'tahlil' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'tahlil' ),
		'featured_image'        => __( 'Featured Image', 'tahlil' ),
		'set_featured_image'    => __( 'Set featured image', 'tahlil' ),
		'remove_featured_image' => __( 'Remove featured image', 'tahlil' ),
		'use_featured_image'    => __( 'Use as featured image', 'tahlil' ),
		'insert_into_item'      => __( 'Insert into item', 'tahlil' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'tahlil' ),
		'items_list'            => __( 'Items list', 'tahlil' ),
		'items_list_navigation' => __( 'Items list navigation', 'tahlil' ),
		'filter_items_list'     => __( 'Filter items list', 'tahlil' ),
	);
	$args = array(
		'label'                 => __( 'Condolance', 'tahlil' ),
		'description'           => __( 'Post Type Description', 'tahlil' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'show_in_rest'          => true,
	);
	register_post_type( 'cpt_condolances', $args );
}

add_action( 'init', 'cpt_condolances', 0 );


/**
 * Custom meta box
 */
function cmb_condolances() {
	$prefix = 'cmb_condalances_';

	/**
	 * Metabox for the user profile screen
	 */
	$cmb = new_cmb2_box( array(
		'id'               => $prefix . 'detail',
		'title'            => esc_html__( 'Detailed Information', 'cmb2' ), // Doesn't output for user boxes
		'object_types'     => array( 'cpt_condolances' ), // Tells CMB2 to use user_meta vs post_meta
	) );

	$cmb->add_field( array(
		'name'     => esc_html__( 'Birthday', 'cmb2' ),
		'id'       => $prefix . 'birthday',
		'type'     => 'text_date'
  ) );
  
  $cmb->add_field( array(
		'name'     => esc_html__( 'Deathday', 'cmb2' ),
		'id'       => $prefix . 'deathday',
		'type'     => 'text_date'
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Photos', 'cmb2' ),
		'id'      => $prefix . 'photos',
		'type'    => 'file_list',
	) );
}
add_action( 'cmb2_admin_init', 'cmb_condolances' );

/**
 * Modify Table
 */
is_admin() && add_filter( 'post_class', function( $classes, $class, $post_id ) {
    $post = get_post( $post_id );

    // Decide whether to add a class or not …

    $classes[] = 'my-custom-class';

    return $classes;    
}, 10, 3 );

add_filter( 'manage_cpt_condolances_posts_columns', 'set_custom_edit_book_columns' );
function set_custom_edit_book_columns($columns) {
	unset($columns['title']);
	unset($columns['comments']);
	unset($columns['date']);
    $columns['thumbnail'] = __( 'Photo', 'tahlil' );
    $columns['title'] = __( 'Name', 'tahlil' );
    $columns['date'] = __( 'Date', 'tahlil' );
    $columns['author'] = __( 'Published By', 'tahlil' );
    $columns['comments'] = __( 'Reacties', 'tahlil' );

    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_cpt_condolances_posts_custom_column' , 'custom_book_column', 10, 2 );
function custom_book_column( $column, $post_id ) {
    switch ( $column ) {
        case 'thumbnail' :
            echo get_the_post_thumbnail($post_id, array(50, 50)); 
            break;
        case 'title' :
        	echo get_the_post_title($post_id);
        	break;
        case 'date' :
        	echo get_the_date($post_id);
        	break;
        case 'author' :
        	echo get_the_author($post_id);
        	break;
        case 'comments':
        	echo get_comments_number($post_id);
        	break;
    }
}