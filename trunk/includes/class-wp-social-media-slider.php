<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      0.9.1
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.9.1
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/includes
 * @author     Your Name <email@example.com>
 */
class WP_Social_Media_Slider {

	/**
	 * The property that stores all of the saved settings.
	 *
	 * @since    0.9.1
	 */
	private $settings;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.9.1
	 * @access   protected
	 * @var      WP_Social_Media_Slider_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The loader that's responsible for grabbing social media posts
	 *
	 * @since    0.9.1
	 * @access   protected
	 * @var      WP_Social_Media_Slider_Loader    $repo    Grabs social media posts
	 */
	protected $repo;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.9.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.9.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Simple method to log data to a file.
	 *
	 * @since    0.9.1
	 */
	public function log( $data ) {
		error_log( print_r( $data, true ) );
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    0.9.1
	 */
	public function __construct() {

		$this->plugin_name = 'wp-social-media-slider';
		$this->version = '1.0.5';
		$this->settings = $this->set_default_settings( get_option('wpsms_settings', array() ) );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Define the defaults that will be used for plugin settings.
	 *
	 * Defaults perform a valuable purpose: providing a consistent outcome
	 * if the user doesn't set a value or a key is somehow missing.
	 *
	 * @since    0.9.1
	 */
	public function set_default_settings( $settings ) {

		// Perform a quick trim on all of the values
		foreach ($settings as $key => $setting) {
			$settings[$key] = ( is_string( $setting ) ) ? trim( $setting ) : $setting;
		}

		// Add the time of the last update
		$settings[ 'time_of_last_refresh' ] = get_option( 'wpsms_time_of_last_refresh', '0' );
		$settings[ 'activated' ] = get_option( 'wpsms_activated', '0' );
		$settings[ 'license_key' ] = get_option( 'wpsms_license_key', '0' );

		// Delete all empty values
		foreach ($settings as $key => $setting) {
			if ( $setting == '' ) {
				unset( $settings[ $key ] );
			}
		}

		$defaults = [
			'total_posts'                 => '10',
			'cache_length'                => 60,
			'ajax_cache_refresh'          => '0',
			'post_cache'                  => '0',
			'display_color'               => '#000000',
			'auto_play'                   => '0',
			'custom_js_init'              => false,
			'twitter_enable'              => '0',
			'twitter_username'            => '',
			'twitter_consumer_key'        => '',
			'twitter_consumer_key_secret' => '',
			'twitter_access_token'        => '',
			'twitter_access_token_secret' => '',
			'time_of_last_update'         => '0',
			'activated'                   => 'false',
			'license_key'                 => ''
		];

		// Any keys not present will be added with the default value
		$settings = $settings + $defaults;

		return $settings;
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Social_Media_Slider_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Social_Media_Slider_i18n. Defines internationalization functionality.
	 * - WP_Social_Media_Slider_Admin. Defines all hooks for the dashboard.
	 * - WP_Social_Media_Slider_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.9.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-social-media-slider-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-social-media-slider-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-social-media-slider-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-social-media-slider-public.php';

		$this->loader = new WP_Social_Media_Slider_Loader();

		/**
		 * The class responsible for directly accessing the social networks and loading posts
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-social-media-slider-repo.php';

		$this->repo = new WP_Social_Media_Slider_Repo( $this->settings );


		/**
		 * The main object class for social media posts.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-social-media-slider-post.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Social_Media_Slider_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.9.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Social_Media_Slider_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    0.9.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Social_Media_Slider_Admin( $this->get_plugin_name(), $this->get_version(), $this->repo, $this->settings );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add the options page and menu item.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add the options to the settings page.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings_and_fields' );

		// Add the action link to the plugins page
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.9.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Social_Media_Slider_Public( $this->get_plugin_name(), $this->get_version(), $this->repo, $this->settings );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'slider_color_styles' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'expose_ajaxurl_js' );

		$this->loader->add_action( 'wp_ajax_nopriv_wpsms_lazy_load_posts', $plugin_public, 'lazy_load_posts' );
		$this->loader->add_action( 'wp_ajax_wpsms_lazy_load_posts', $plugin_public, 'lazy_load_posts' );

		$this->loader->add_action( 'wp_ajax_nopriv_wpsms_refresh_cache', $plugin_public, 'refresh_cache' );
		$this->loader->add_action( 'wp_ajax_wpsms_refresh_cache', $plugin_public, 'refresh_cache' );

		add_shortcode( 'wp-social-media-slider', array( $plugin_public, 'display_wp_social_media_slider' ) );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.9.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.9.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.9.1
	 * @return    WP_Social_Media_Slider_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.9.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
