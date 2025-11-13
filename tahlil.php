<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Condoleance Register
 * Plugin URI:        https://www.envisionwebdesign.nl
 * Description:       -
 * Version:           1.0.0
 * Author:            Muhammad Uzair Usman
 * Author URI:        https://www.envisionwebdesign.nl *
 * License:           -
 * License URI:       -
 * Text Domain:       tahlil
 * Domain Path:       /languages
 */

namespace Tahlil;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */

define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
define( NS . 'PLUGIN_NAME', 'Tahlil' );
define( NS . 'PLUGIN_VERSION', '1.0.0' );
define( NS . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );
define( NS . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );
define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( NS . 'PLUGIN_TEXT_DOMAIN', 'tahlil' );


/**
 * Autoload Classes
 */

require_once( PLUGIN_NAME_DIR . 'inc/libraries/autoloader.php' );

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */

register_activation_hook( __FILE__, array( NS . 'Inc\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */

register_deactivation_hook( __FILE__, array( NS . 'Inc\Core\Deactivator', 'deactivate' ) );

/**
 *  Register Libraries
 */
require_once( PLUGIN_NAME_DIR . 'inc/libraries/cmb2/init.php' );

/**
 *  Register Post Types
 */
require_once( PLUGIN_NAME_DIR . 'inc/post-types/cpt_condolances.php' );

/**
 * Register Shortcode
 */
require_once( PLUGIN_NAME_DIR . 'inc/shortcodes/condolances.php' );
require_once( PLUGIN_NAME_DIR . 'inc/shortcodes/lightacandle.php' );
require_once( PLUGIN_NAME_DIR . 'inc/shortcodes/condolances-meta.php' );

/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    1.0.0
 */
class Tahlil {

	static $init;
	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init() {

		if ( null == self::$init ) {
			self::$init = new Inc\Core\Init();
			self::$init->run();
		}

		return self::$init;
	}

}

/*
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 *
 */
function tahlil_init() {
		// since it's buggy, we keep it yet till the wordpress core fixed it.
		// add_filter ('comments_array', function($comments) {
  //       	return array_reverse($comments);
  //   	});
		// add_filter( 'comments_array', 'array_reverse' );

		// comment cookies, for security sake, 
		add_action( 'set_comment_cookies', function( $comment, $user ) {
			setcookie( 'ta_comment_wait_approval', '1', 0, '/' );
		}, 10, 2 );

		// comment success message
		add_action( 'init', function() {
			if( isset( $_COOKIE['ta_comment_wait_approval'] ) && $_COOKIE['ta_comment_wait_approval'] === '1' ) {
				setcookie( 'ta_comment_wait_approval', '0', 0, '/' );
				add_action( 'comment_form_before', function() {
					if (is_singular('cpt_condolances')) {
						echo "<div id='success-message-wrapper'>
								<div id='wait_approval'>
									Uw reactie is goed ontvangen en zal snel na de administratieve beoordeling worden gepubliceerd. Bedankt namens de nabestaanden!
								</div>
							</div>";
					} else {
						echo "<div id='success-message-wrapper'>
								<div id='wait_approval'>
									Je reactie is geplaatst.
								</div>
							</div>";
					}
				});
			}
		});

		add_filter( 'comment_post_redirect', function( $location, $comment ) {
			$location = get_permalink( $comment->comment_post_ID ) . '#success-message-wrapper';
			return $location;
		}, 10, 2 );

		return Tahlil::init();
}

$min_php = '5.6.0';

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
		tahlil_init();
}
