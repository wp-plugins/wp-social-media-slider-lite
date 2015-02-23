<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.9.1
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/public
 * @author     Your Name <email@example.com>
 */
class WP_Social_Media_Slider_Public {

	/**
	 * The saved settings for the plugin
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      class    $version    The repo for the plugin
	 */
	public $settings;

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
	 * Log some data.
	 */
	public function log( $data ) {
		error_log( print_r( $data, true ) );
	}

	public function expose_ajaxurl_js() {
		echo "<script>var ajaxurl = '" . admin_url('admin-ajax.php') . "';</script>";
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.9.1
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $repo, $settings ) {

		$this->settings = $settings;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->repo = $repo;
	}

	public function display_wp_social_media_slider() {

		if ( function_exists('curl_version') ) {
			ob_start();
				include_once( 'partials/wp-social-media-slider-lazy-loader.php' );
			$str = ob_get_clean();			
		} else {
			$str = "Please <a href='http://wpsocialmediaslider.com/docs#troubleshooting' target='_blank'>enable the Curl PHP extension</a> on your server.";
		}

		return $str;
	}

	/**
	 * Lazy load the social media posts
	 *
	 * It takes some time to communicate with the various social media
	 * networks, so we'll lazy load them to help make sure that the page
	 * loads quickly.
	 * 
	 * @return void This is an ajax action, so it simply dies in the end.
	 */
	public function lazy_load_posts() {

		$all_posts      = $this->repo->get_social_media();
		$all_posts_html = array();

		foreach( $all_posts as $post ) {

			// Capture output
			ob_start();

			include( 'partials/wp-social-media-slider-twitter-post.php' );

			// Append the output to an array
			$all_posts_html[] = ob_get_clean();

		}

		// If ajax refreshing is turned off, we'll always tell the system
		// not to do a cache refresh
		if ( $this->settings[ 'ajax_cache_refresh' ] == '1' ) {
			$is_it_time_to_refresh = $this->repo->is_it_time_to_refresh();
		}
		else {
			$is_it_time_to_refresh = 'Ajax cache refresh is turned off.';
		}

		$response = array(
			'time_to_update' => (string)$is_it_time_to_refresh,
			'posts'          => $all_posts_html,
			'status'         => 'Successfully collected posts!'
			);

		die( json_encode( $response ) );
	}


	/**
	 * Refresh the posts in the cache.
	 * 
	 * @return void
	 */
	public function refresh_cache() {

		$refresh = $this->repo->refresh_cache();

		if ( $refresh ) {
			$response = array(
				'status' => "The post cache was refreshed!",
				);
		}
		else {
			$response = array(
				'status' => "The post cache could not be refreshed.",
				);
		}

		die( json_encode( $response ) );
	}


	public function slider_color_styles() {
		printf("
			<style>
				.wpsms .wpsms-meta-info:before,
				.wpsms .owl-next,
				.wpsms .owl-prev,
				.wpsms a {
					color: {$this->settings['display_color']}
				}
			</style>
			");
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.9.1
	 */
	public function enqueue_styles() {
		
		global $wp_styles;
		
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpsms-public.css', array(), $this->version, 'all' );

	    /**
	     * Load our IE version-specific stylesheet:
	     * <!--[if IE 8]> ... <![endif]-->
	     */
	    wp_enqueue_style( $this->plugin_name . '-ie8', plugin_dir_url( __FILE__ ) . "/css/wpsms-ie8.css", array(), $this->version, 'all'  );
	    $wp_styles->add_data( $this->plugin_name . '-ie8', 'conditional', 'lte IE 9' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.9.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpsms-public.min.js', array( 'jquery' ), $this->version, false );
	}

}
