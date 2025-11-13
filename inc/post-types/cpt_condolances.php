<?php

$post_type = 'cpt_condolances';

/**
 * Custom post type registration
 */
function cpt_condolances() {

	$labels = array(
		'name'                  => _x( 'Overzicht Register', 'Post Type General Name', 'tahlil' ),
		'singular_name'         => _x( 'Condoleance', 'Post Type Singular Name', 'tahlil' ),
		'menu_name'             => __( 'Condoleance Register', 'tahlil' ),
		'name_admin_bar'        => __( 'Condoleances', 'tahlil' ),
		'archives'              => __( 'Item Archives', 'tahlil' ),
		'attributes'            => __( 'Item Attributes', 'tahlil' ),
		'parent_item_colon'     => __( 'Parent Item:', 'tahlil' ),
		'all_items'             => __( 'Overzicht Bekijken', 'tahlil' ),
		'add_new_item'          => __( 'Toevoegen', 'tahlil' ),
		'add_new'               => __( 'Toevoegen', 'tahlil' ),
		'new_item'              => __( 'Nieuw', 'tahlil' ),
		'edit_item'             => __( 'Bewerk', 'tahlil' ),
		'update_item'           => __( 'Bijwerken', 'tahlil' ),
		'view_item'             => __( 'Bekijk', 'tahlil' ),
		'view_items'            => __( 'Bekijk alle', 'tahlil' ),
		'search_items'          => __( 'Zoek', 'tahlil' ),
		'not_found'             => __( 'Not found', 'tahlil' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'tahlil' ),
		'featured_image'        => __( 'Foto Toevoegen', 'tahlil' ),
		'set_featured_image'    => __( 'Set featured image', 'tahlil' ),
		'remove_featured_image' => __( 'Verwijder foto', 'tahlil' ),
		'use_featured_image'    => __( 'Gebruik als foto', 'tahlil' ),
		'insert_into_item'      => __( 'Insert into item', 'tahlil' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'tahlil' ),
		'items_list'            => __( 'Items list', 'tahlil' ),
		'items_list_navigation' => __( 'Items list navigation', 'tahlil' ),
		'filter_items_list'     => __( 'Filter items list', 'tahlil' ),
	);
	$args = array(
		'label'                 => __( 'Condoleance', 'tahlil' ),
		'description'           => __( 'All of the memories lies with us', 'tahlil' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'comments', 'revisions', 'excerpt' ),
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
		'rewrite'               => array( 'slug' => 'condoleances'),
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
		'title'            => esc_html__( 'Details van overledene', 'cmb2' ),
		'object_types'     => array( 'cpt_condolances' ),
		'context'       => 'normal',
		'priority'      => 'high',
	) );

	$cmb->add_field( array(
		'name'     => esc_html__( 'Datum geboren', 'cmb2' ),
		'id'       => $prefix . 'birthday',
		'type'     => 'text_date',
		'attributes' => array(
			'data-datepicker' => json_encode( array(
				'yearRange' => '-95:'. ( date( 'Y' )),
				'dateFormat' => "dd/mm/yy"
			) ),
		),

  ) );
  
  $cmb->add_field( array(
		'name'     => esc_html__( 'Datum overlijden', 'cmb2' ),
		'id'       => $prefix . 'deathday',
		'type'     => 'text_date',
		'attributes' => array(
			'data-datepicker' => json_encode( array(
				'yearRange' => '-95:'. ( date( 'Y' )),
				'dateFormat' => "dd/mm/yy"

			) ),
		),
	) );
	

	$cmb->add_field( array(
		'name'    => esc_html__( 'Fotos', 'cmb2' ),
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
    $classes[] = 'my-custom-class';

    return $classes;    
}, 10, 3 );

add_filter( 'manage_cpt_condolances_posts_columns', 'set_custom_edit_book_columns' );
function set_custom_edit_book_columns($columns) {
	unset($columns['title']);
	unset($columns['comments']);
	unset($columns['date']);
    $columns['thumbnail'] = __( 'Foto', 'tahlil' );
    $columns['title'] = __( 'Naam', 'tahlil' );
    $columns['date'] = __( 'Datum', 'tahlil' );
    $columns['author'] = __( 'Geplaatst door', 'tahlil' );
    $columns['comments'] = __( 'Reacties', 'tahlil' );

    return $columns;
}

// Add the data to the custom columns
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
        	echo get_the_date('d M Y', $post_id);
        	break;
        case 'author' :
        	echo get_the_author($post_id);
        	break;
        case 'comments':
        	echo get_comments_number($post_id);
        	break;
    }
}

// change placeholder
add_filter( 'enter_title_here', 'wpb_change_title_text' );
function wpb_change_title_text( $title ){
     $screen = get_current_screen();
  
     if  ( 'cpt_condolances' == $screen->post_type ) {
          $title = 'Naam';
     }
     return $title;
}