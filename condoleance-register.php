<?php
/**
 * Plugin Name:       Condoleance Register 2.0
 * Plugin URI:        https://magaliechetrit.com
 * Description:       Modern condoleance and memorial management system with virtual candles, comments, and photo galleries
 * Version:           2.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Magalie Chetrit
 * Author URI:        https://www.magaliechetrit.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       condoleance-register
 * Domain Path:       /languages
 *
 * @package CondoleanceRegister
 */

declare(strict_types=1);

namespace CondoleanceRegister;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants.
define('CONDOLEANCE_REGISTER_VERSION', '2.0.0');
define('CONDOLEANCE_REGISTER_FILE', __FILE__);
define('CONDOLEANCE_REGISTER_PATH', plugin_dir_path(__FILE__));
define('CONDOLEANCE_REGISTER_URL', plugin_dir_url(__FILE__));
define('CONDOLEANCE_REGISTER_BASENAME', plugin_basename(__FILE__));

// Check PHP version requirement.
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function () {
        printf(
            '<div class="error"><p>%s</p></div>',
            esc_html__('Condoleance Register requires PHP 8.0 or higher. Please upgrade PHP.', 'condoleance-register')
        );
    });
    return;
}

// Autoloader.
require_once CONDOLEANCE_REGISTER_PATH . 'includes/autoloader.php';

/**
 * Initialize the plugin.
 *
 * @since 2.0.0
 */
function init(): void
{
    // Load text domain for translations.
    load_plugin_textdomain(
        'condoleance-register',
        false,
        dirname(CONDOLEANCE_REGISTER_BASENAME) . '/languages'
    );

    // Initialize core plugin.
    Plugin::get_instance();
}

add_action('plugins_loaded', __NAMESPACE__ . '\init');

/**
 * Activation hook.
 *
 * @since 2.0.0
 */
function activate(): void
{
    require_once CONDOLEANCE_REGISTER_PATH . 'includes/class-activator.php';
    Activator::activate();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\activate');

/**
 * Deactivation hook.
 *
 * @since 2.0.0
 */
function deactivate(): void
{
    require_once CONDOLEANCE_REGISTER_PATH . 'includes/class-deactivator.php';
    Deactivator::deactivate();
}

register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivate');
