<?php 

namespace Tahlil\Inc\Admin;

class Reacties {
	private $plugin_text_domain;
	private $reacties_table;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
	}

	/**
	 * Add Manage Reacties menu
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_menu() {
		$page_hook = add_submenu_page(
      		"edit.php?post_type=cpt_condolances",
      		__('Manage Reacties', $this->plugin_text_domain),
      		__('Manage Reacties', $this->plugin_text_domain),
      		'moderate_comments',
      		'manage-reacties',
      		array( $this, 'render_reacties_table' )
      	);

		add_action( 'load-'.$page_hook, array( $this, 'load_reacties_table_screen_options' ) );
	}

	public function load_reacties_table_screen_options() {
		$this->reacties_table = new Reacties_Table( $this->plugin_text_domain );	
	}

	public function render_reacties_table() {
		// render the List Table
		include_once( 'views/partials-reacties-table-display.php' );
	}
}
