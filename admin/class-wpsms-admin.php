<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @since      0.9.1
 * @package    Wpsms
 * @subpackage Wpsms/admin
 * @link       http://wpsocialmediaslider.com
 * @author     Pete Molinero <pete@laternastudio.com>
 */

class Wpsms_Admin {

	/**
	 * The saved settings for the plugin
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      class    $version    The repo for the plugin
	 */
	private $settings;

	/**
	 * The registered social media networks.
	 *
	 * @since    1.0.6
	 */
	private $networks;

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The repo for the plugin.
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      class    $version    The repo for the plugin
	 */
	private $repo;

	/**
	 * The registered logger
	 *
	 * @since    1.0.6
	 */
	private $log;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.9.1
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $repo, $settings, $networks, $log ) {
		$this->settings    = $settings;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->repo        = $repo;
		$this->networks    = $networks;
		$this->log         = $log;
	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.9.1
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			'WP Social Media Slider Lite',
			'Social Media Slider',
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.9.1
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/wpsms-admin-display.php' );
	}

	/**
	 * Register the settings and fields for the plugin settings page.
	 *
	 * @since    0.9.1
	 */
	public function register_settings_and_fields() {

		register_setting('wpsms_settings', 'wpsms_settings');

		add_settings_section(
	        'wpsms_main_section',
	        __( 'General Settings', 'wp-social-media-slider' ),
	        array($this, 'wpsms_main_settings_callback'),
	        $this->plugin_name
	    );

	    // The total number of posts to display
	    add_settings_field(
	    	'total_posts',
	    	__( 'Total Number of Posts', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_total_posts' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'cache_length',
	    	__( 'Cache Duration (in sec.)', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_cache_length' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // Only refresh the cache via AJAX
	    add_settings_field(
	    	'ajax_cache_refresh',
	    	__( 'AJAX Cache Refresh', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_ajax_cache_refresh_setting' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'display_color',
	    	__( 'Display Color', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_display_color' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'auto_play',
	    	__( 'Auto-play Slider', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_auto_play_setting' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // Custom JS Initialization
	    add_settings_field(
	    	'custom_js_init',
	    	__( 'Custom JS Initialization', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_custom_js_init' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'log_data',
	    	__( 'Log Post Data to Console (used for debugging)', 'wp-social-media-slider' ),
	    	array( $this, 'wpsms_log_data_setting' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

		// Perform individual network registrations
		foreach ( $this->networks as $network ) {
			$network->register_settings();
		}
	}


	public function wpsms_main_settings_callback() {
	    include_once( 'views/wpsms-general-instructions.php' );
	}

	/**
	 * Input field creation methods
	 *
	 * @since    0.9.1
	 */

	public function wpsms_total_posts() {
		echo "<input name='wpsms_settings[total_posts]' type='number' value='{$this->settings['total_posts']}' />";
	}

	public function wpsms_cache_length() {
		echo "<input name='wpsms_settings[cache_length]' type='number' value='{$this->settings['cache_length']}' />";
	}

	public function wpsms_display_color() {
		echo "<input name='wpsms_settings[display_color]' type='text' class='wpsms-minicolors' value='{$this->settings['display_color']}' />";
	}

	public function wpsms_ajax_cache_refresh_setting() {

		printf( '<div class="onoffswitch">
				    <input type="checkbox" name="wpsms_settings[ajax_cache_refresh]" class="onoffswitch-checkbox" id="ajax_cache_refresh" value="1" ' . checked('1', $this->settings['ajax_cache_refresh'], false) . ' >
				    <label class="onoffswitch-label" for="ajax_cache_refresh">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>');
	}

	public function wpsms_auto_play_setting() {

		printf( '<div class="onoffswitch">
				    <input type="checkbox" name="wpsms_settings[auto_play]" class="onoffswitch-checkbox" id="auto_play" value="1" ' . checked('1', $this->settings['auto_play'], false) . ' >
				    <label class="onoffswitch-label" for="auto_play">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>');
	}

	public function wpsms_custom_js_init() {
		printf("<textarea id='wpsms-custom-js-init' class='wpsms-custom-js-init' name='wpsms_settings[custom_js_init]'>{$this->settings['custom_js_init']}</textarea>");
	}

	public function wpsms_log_data_setting() {

		printf( '<div class="onoffswitch">
				    <input type="checkbox" name="wpsms_settings[log_data]" class="onoffswitch-checkbox" id="log_data" value="1" ' . checked('1', $this->settings['log_data'], false) . ' >
				    <label class="onoffswitch-label" for="log_data">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>');
	}


	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    0.9.1
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>'
			),
			$links
		);

	}


	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    0.9.1
	 */
	public function enqueue_styles( $hook ) {

		// We only want to load these files if we are actually on the settings page for this plugin
		if ( 'settings_page_wp-social-media-slider-lite' != $hook ) {
	        return;
	    }

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpsms-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    0.9.1
	 */
	public function enqueue_scripts( $hook ) {

		// We only want to load these files if we are actually on the settings page for this plugin
		if ( 'settings_page_wp-social-media-slider-lite' != $hook ) {
	        return;
	    }

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpsms-admin.min.js', array( 'jquery' ), $this->version, false );
	}

}
