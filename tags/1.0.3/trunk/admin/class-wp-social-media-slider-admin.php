<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.9.1
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/admin
 * @author     Your Name <email@example.com>
 */
class WP_Social_Media_Slider_Admin {

	/**
	 * The saved settings for the plugin
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      class    $version    The repo for the plugin
	 */
	private $settings;

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
	 * Simple method to log data to a file.
	 *
	 * @since    0.9.1
	 */
	public function log( $data ) {
		error_log( print_r( $data, true ) );
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.9.1
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $repo, $settings ) {
		
		$this->settings = $settings;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->repo = $repo;

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

		include_once( 'partials/wp-social-media-slider-admin-display.php' );
	}

	/**
	 * Register the settings and fields for the plugin settings page.
	 *
	 * @since    0.9.1
	 */
	public function register_settings_and_fields() {

		register_setting('wpsms_settings', 'wpsms_settings');
		//update_option('wpsms_settings', '');

		add_settings_section(
	        'wpsms_main_section',            // ID used to identify this section and with which to register options
	        'General Settings',                     // Title to be displayed on the administration page
	        array($this, 'wpsms_main_settings_callback'),  // Callback used to render the description of the section
	        $this->plugin_name                   // Page on which to add this section of options
	    );

	    // The total number of posts to display
	    add_settings_field(
	    	'total_posts',
	    	'Total Number of Posts',
	    	array( $this, 'wpsms_total_posts' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'cache_length',
	    	'Cache Duration (in sec.)',
	    	array( $this, 'wpsms_cache_length' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // Only refresh the cache via AJAX
	    add_settings_field(
	    	'ajax_cache_refresh',
	    	'AJAX Cache Refresh',
	    	array( $this, 'wpsms_ajax_cache_refresh_setting' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'display_color',
	    	'Display Color',
	    	array( $this, 'wpsms_display_color' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // The length of the time that posts will be stored for
	    add_settings_field(
	    	'auto_play',
	    	'Auto-play Slider',
	    	array( $this, 'wpsms_auto_play_setting' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    // Custom JS Initialization
	    add_settings_field(
	    	'custom_js_init',
	    	'Custom JS Intialization',
	    	array( $this, 'wpsms_custom_js_init' ),
	    	$this->plugin_name,
	    	'wpsms_main_section');

	    /**
	     * The Twitter settings.
	     */
		add_settings_section(
	        'wpsms_twitter_section',                          // ID used to identify this section and with which to register options
	        'Twitter Settings',                             // Title to be displayed on the administration page
	        array($this, 'wpsms_twitter_settings_callback'),  // Callback used to render the description of the section
	        $this->plugin_name                               // Page on which to add this section of options
	    );

	    // Enable or disable Twitter Posts
	    add_settings_field(
	    	'twitter_enable',
	    	'Enable Twitter',
	    	array( $this, 'wpsms_enable_twitter_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter username
	    add_settings_field(
	    	'twitter_username',
	    	'Twitter Username',
	    	array( $this, 'wpsms_twitter_username_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter consumer key
	    add_settings_field(
	    	'twitter_consumer_key',
	    	'Twitter Consumer Key',
	    	array( $this, 'wpsms_twitter_consumer_key_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter consumer secret
	    add_settings_field(
	    	'twitter_consumer_key_secret',
	    	'Twitter Consumer Key Secret',
	    	array( $this, 'wpsms_twitter_consumer_key_secret_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter access token
	    add_settings_field(
	    	'twitter_access_token',
	    	'Twitter Access Token',
	    	array( $this, 'wpsms_twitter_access_token_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter access token secret
	    add_settings_field(
	    	'twitter_access_token_secret',
	    	'Twitter Access Token Secret',
	    	array( $this, 'wpsms_twitter_access_token_secret_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	}


	public function wpsms_main_settings_callback() {
	    include_once( 'partials/wp-social-media-slider-general-instructions.php' );
	}

	public function wpsms_twitter_settings_callback() {
	    include_once( 'partials/wp-social-media-slider-twitter-instructions.php' );
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

	public function wpsms_enable_twitter_setting() {

		printf( '<div class="onoffswitch">
				    <input type="checkbox" name="wpsms_settings[twitter_enable]" class="onoffswitch-checkbox" id="twitter_enable" value="1" ' . checked('1', $this->settings['twitter_enable'], false) . ' >
				    <label class="onoffswitch-label" for="twitter_enable">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>');
	}

	public function wpsms_twitter_username_setting() {
		echo "<input name='wpsms_settings[twitter_username]' type='text' value='{$this->settings['twitter_username']}' />";
	}

	public function wpsms_twitter_consumer_key_setting() {
		echo "<input name='wpsms_settings[twitter_consumer_key]' type='text' value='{$this->settings['twitter_consumer_key']}' />";
	}

	public function wpsms_twitter_consumer_key_secret_setting() {
		echo "<input name='wpsms_settings[twitter_consumer_key_secret]' type='text' value='{$this->settings['twitter_consumer_key_secret']}' />";
	}

	public function wpsms_twitter_access_token_setting() {
		echo "<input name='wpsms_settings[twitter_access_token]' type='text' value='{$this->settings['twitter_access_token']}' />";
	}

	public function wpsms_twitter_access_token_secret_setting() {
		echo "<input name='wpsms_settings[twitter_access_token_secret]' type='text' value='{$this->settings['twitter_access_token_secret']}' />";
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
		if ( 'settings_page_wp-social-media-slider' != $hook ) {
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
		if ( 'settings_page_wp-social-media-slider' != $hook ) {
	        return;
	    }

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpsms-admin.min.js', array( 'jquery' ), $this->version, false );
	}

}
