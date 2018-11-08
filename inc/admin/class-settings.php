<?php 

namespace Tahlil\Inc\Admin;

class Settings {

	private $plugin_text_domain;
	private $_nonce = 'tahlil_admin';
	private $option_name = 'tahlil_settings';

	public function __construct($plugin_text_domain) {
		$this->plugin_text_domain = $plugin_text_domain;

		add_action( 'admin_menu', array( $this, 'add_tahlil_settings_menu' ) );
		add_action( 'wp_ajax_store_admin_data', array( $this, 'storeAdminData' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScripts' ) );
	}

	public function addAdminScripts()
	{
		wp_enqueue_script('tahlil-admin', plugin_dir_url( __FILE__ )  . '/js/admin.js', array(), 1.0);
		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);
		wp_localize_script('tahlil-admin', 'tahlil_exchanger', $admin_options);
	}

	public function storeAdminData()
	{
	 
	    if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
			die('Invalid Request!');
	 
	    update_option($this->option_name, array(
	    	'youtube_api_key' => $_POST['tahlil_youtube_api_key']
	    ));
	 
	    echo __('Saved!', $this->plugin_text_domain);

	    die();	 
	}

	public function getData() {
    	return get_option($this->option_name, array());
	}

	/**
	 * Add Tahlil Settings menu
	 *
	 * @since 1.0.0
	 */
	public function add_tahlil_settings_menu() {
		add_submenu_page(
			"edit.php?post_type=cpt_condolances",
      		__('Settings', $this->plugin_text_domain),
      		__('Settings', $this->plugin_text_domain),
      		'moderate_comments',
      		'tahlil-settings',
      		array( $this, 'load_tahlil_settings_page' )
		);
	}

	/**
	 * Display the Tahlil Settings Page
	 *
	 * Callback for add_tahlil_settings_menu()
	 *
	 * @since 1.0.0
	 */
	public function load_tahlil_settings_page() {
		include_once('views/partials-tahlil-settings.php');
	}
}
