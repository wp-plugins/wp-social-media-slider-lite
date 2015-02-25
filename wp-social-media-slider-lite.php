<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             0.9.1
 * @package           Wpsms
 *
 * @wordpress-plugin
 * Plugin Name:       WP Social Media Slider Lite
 * Plugin URI:        http://wpsocialmediaslider.com
 * Description:       A simple slider that displays recent posts from Twitter.
 * Version:           1.0.6
 * Author:            Laterna Studio
 * Author URI:        http://laternastudio.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-social-media-slider-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpsms-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpsms-deactivator.php';

/** This action is documented in includes/class-wp-social-media-slider-activator.php */
register_activation_hook( __FILE__, array( 'Wpsms_Activator', 'activate' ) );

/** This action is documented in includes/class-wp-social-media-slider-deactivator.php */
register_deactivation_hook( __FILE__, array( 'Wpsms_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpsms.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.9.1
 */
function run_socal_media_slider() {

	$plugin = new Wpsms();
	$plugin->run();

}
run_socal_media_slider();
