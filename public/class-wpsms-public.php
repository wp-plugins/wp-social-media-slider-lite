<?php

/**
 * The public facing functionality of the plugin.
 *
 * @since      0.9.1
 * @package    Wpsms
 * @subpackage Wpsms/public
 * @link       http://wpsocialmediaslider.com
 * @author     Pete Molinero <pete@laternastudio.com>
 */

class Wpsms_Public {

	/**
	 * The saved settings for the plugin
	 *
	 * @since    0.9.1
	 * @access   private
	 * @var      class    $version    The repo for the plugin
	 */
	public $settings;

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
	public function __construct( $plugin_name, $version, $repo, $settings, $networks, $log ) {
		$this->settings    = $settings;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->repo        = $repo;
		$this->networks    = $networks;
		$this->log         = $log;
	}

	public function display_wp_social_media_slider( $atts ) {

		if ( function_exists('curl_version') ) {
			ob_start();
				include_once( 'views/wpsms-lazy-loader.php' );
			$str = ob_get_clean();
		} else {
			$str = sprintf( __( 'Please %1senable the Curl PHP extension%2s on your server.', 'wp-social-media-slider' ),
						'<a href="http://wpsocialmediaslider.com/docs#troubleshooting" target="_blank">',
						'</a>');
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

		$shortcode_atts = $_GET['shortcode_atts'];

		$all_posts      = $this->repo->get_social_media( $shortcode_atts );
		$all_posts_html = array();

		// Get the html for each post based on its social media network
		foreach( $all_posts as $post ) {
			if( array_key_exists( $post->type, $this->networks ) ) {
				$all_posts_html[] = $this->networks[ $post->type ]->get_public_view( $post );
			}
		}

		// If ajax refreshing is turned off, we'll always tell the system
		// not to do a cache refresh
		if ( $this->settings[ 'ajax_cache_refresh' ] == '1' ) {
			$is_it_time_to_refresh = $this->repo->is_it_time_to_refresh( $shortcode_atts );
		}
		else {
			$is_it_time_to_refresh = 'Ajax cache refresh is turned off.';
		}

		$response = array(
			'log_data'       => $this->settings[ 'log_data' ],
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

		$shortcode_atts = $_GET['shortcode_atts'];

		$refresh = $this->repo->refresh_cache( $shortcode_atts );

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


	public function variable_limit_length( $limit, $post ) {

		switch ( $post->type ) {
			case 'twitter':
				if ( count( $post->images ) > 0 ) {
					$limit -= 30;
				}
				break;
		}

		return $limit;

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
