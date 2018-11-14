<?php

namespace Tahlil\Inc\Frontend;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 */

class Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;	

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since		1.0.0
	 * @param		string $plugin_name       The name of this plugin.
	 * @param		string $version    The version of this plugin.
	 * @param		string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 
			$this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nds-admin-form-demo-frontend.css', 
			array(), 
			$this->version, 
			'all' 
		);

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 
			$this->plugin_name . '-youtube-client',
			'https://apis.google.com/js/client.js?onload=onClientLoad', 
			array( 'jquery' ), 
			$this->version, 
			true 
		);

		wp_enqueue_script( 
			$this->plugin_name . '-jquery-ui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js', 
			array( 'jquery' ), 
			$this->version, 
			true 
		);

		wp_enqueue_script( 
			$this->plugin_name . '-youtube-search',
			plugin_dir_url( __FILE__ ) . 'js/youtube-search.js', 
			array( 'jquery' ), 
			$this->version, 
			true 
		);

		wp_enqueue_script( 
			$this->plugin_name . '-jquery-validate',
			plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js', 
			array( 'jquery' ), 
			$this->version, 
			true 
		);

		wp_enqueue_script( 
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/nds-admin-form-demo-frontend.js', 
			array( 'jquery' ), 
			$this->version, 
			true 
		);

	}
}
