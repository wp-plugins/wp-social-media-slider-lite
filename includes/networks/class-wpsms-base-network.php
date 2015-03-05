<?php

/**
* Load recent posts from a social media network
 *
 * @since      1.0.6
 * @package    Wpsms
 * @subpackage Wpsms/includes
 * @author     Pete Molinero <pete@laternastudio.com>
 */

class Wpsms_Base_Network {

	/**
	 * The property that stores all of the saved settings.
	 *
	 * @since    0.9.1
	 */
	protected $settings;

	/**
	 * The official name of the plugin
	 *
	 * @since    0.9.1
	 */
	protected $plugin_name;

	/**
	 * The type identifier of the network
	 *
	 * @since    0.9.1
	 */
	protected $type;

	/**
	 * The registered logger
	 *
	 * @since    1.0.6
	 */
	public $log;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.9.1
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $settings, $log ) {
		$this->log         = $log;
		$this->settings    = $this->get_settings() + $settings;
		$this->plugin_name = $plugin_name;

		add_action( 'wp_ajax_connection_status_' . $this->type, array( $this, 'connection_status' ) );
	}

	/**
	 * Return the type identifier for the network
	 *
	 * @since   1.0.6
	 * @return  string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Remove empty settings from the settings array
	 *
	 * @since   1.0.6
	 * @return  array  the array with the removed settings
	 */
	public function remove_empty_settings( array $settings ) {

		foreach ($settings as $key => $setting) {
			if ( $setting == '' ) {
				unset( $settings[ $key ] );
			}
		}

		return $settings;
	}


	/**
	 * Return the formatted time 
	 *
	 * @since    0.9.1
	 * @param    string   $time  the Unix timestamp
	 * @return   string          the formatted date/time
	 */
	public function ago( $time )
	{

		// Set the timezone using the Wordpress timezone (if it is set)
		$user_timezone = get_option( 'timezone_string' );

		if ( !empty( $user_timezone ) ) {
			date_default_timezone_set( $user_timezone );
		}
		
		$yesterday = strtotime('today midnight');
		$now       = time();

		if ( $time < ($now - $yesterday)) {

			$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
			$lengths = array("60","60","24","7","4.35","12","10");

			$now = time();

			   $difference     = $now - $time;
			   $tense         = "ago";

			for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			   $difference /= $lengths[$j];
			}

			$difference = round($difference);

			if($difference != 1) {
			   $periods[$j].= "s";
			}

			$post_time = "Posted $difference $periods[$j] ago ";
		}
		else {
			$post_time = "Posted on ".date( 'F j, Y', $time);
		}

		return $post_time;
	}


	/**
	 * Include a file (which involves variable substitution)
	 *
	 * @since  1.0.6
	 * @param  string  $path  The path to the file
	 * @param  array   $vars  An associative array of any variables for the view
	 * @return string         The rendered view
	 */
	public function render( $path, $vars = array() ) {

		// If there are variables in the array, extract them
		if ( !empty( $vars ) ) extract( $vars );

		ob_start();
		include( $path );
		$output = ob_get_contents();
		ob_end_clean();		
		return $output;		
	}


	/**
	 * Get the path of the view file, allowing for child theme overrides
	 *
	 * @since   1.1.1
	 * @param   string  $filename  the filename of the view file (not incl. path)
	 * @return  string             the full path of the view file
	 */
	public function get_view_path( $filename ) {

		$child_theme_path = get_stylesheet_directory() . '/wp-social-media-slider/' . $filename;
		$default_path     = plugin_dir_path( __FILE__ ) . $this->type . '/views/' . $filename;

		if ( file_exists( $child_theme_path ) ) {
			return $child_theme_path;
		}
		else {
			return $default_path;
		}

	}

	/**
	 * Shorten a string of text, but don't cut in the middle of a word.
	 *
	 * @since   1.1.1
	 * @param   string  $string  The string to be shortened
	 * @param   int     $limit   The number of characters to limit to
	 * @param   object  $post    The post object
	 * @return  string           The shortened string
	 */
	public function soft_shorten( $string, $limit, $post ) {

		$words = explode( ' ', $string );
		$shortened = '';
		$limit = apply_filters( 'wpsms_limit_length', $limit, $post );

		// Continue adding words until we've passed the limit
		foreach ( $words as $word ) {

			$shortened_plus = ( $shortened == '' ) ? $word : $shortened . ' ' . $word;

			if ( $limit >= strlen( $shortened_plus ) ) {
				$shortened = $shortened_plus;
			} else {
				return $shortened . '...';
			}
		}

		return $shortened;
	}

	/**
	 * Determine whether or not the post will be shortened
	 *
	 * @since   1.1.1
	 * @param   string  $string  The string to be shortened
	 * @param   int     $limit   The number of characters to limit to
	 * @return  string           The shortened string
	 */
	public function is_shortened( $string, $limit, $post ) {

		$limit = apply_filters( 'wpsms_limit_length', $limit, $post );

		if ( strlen( $string ) > $limit ) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Add anchor tags around all urls in text
	 *
	 * @since   1.1.1
	 * @param   string  $text  The text to be parsed
	 * @return  string         The text with anchors in it
	 */
	public function linkify( $text ) {
		$with_links = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
		return $with_links;
	}


	/**
	 * Get an object with the connection status
	 *
	 * @since   1.1.2
	 * @return  string  the html indicator
	 */
	public function connection_status() {
		
		$connection = $this->validate_connection();

		if ( $connection->status ) {
			$html = "<span> </span>Connected!";
		}
		else {
			$html = sprintf ( "<span> </span>Connection <a href='#wpsms-connection-info-modal-%1s' id='open-twitter-info-modal' class='wpsms-info'>error</a>!", $this->type );

			$html .= sprintf ( "<div id='wpsms-connection-info-modal-%1s' class='white-popup mfp-hide'>
						<h3>%2s</h3>
						<p>%3s</p>
						<textarea class='wpsms-error-textarea'>%4s</textarea>
					</div>",
					$this->type,
					__( 'Error Details', 'wp-social-media-slider' ),
					__( 'Be sure to include this error information when you submit a support request.', 'wp-social-media-slider' ),
					print_r( $connection->error, true )
					);
		}

		$response = array(
			'html' => $html,
			);

		$response['status'] = ( $connection->status ) ? 'connected' : 'not connected';

		die( json_encode( $response ) );

	}


}