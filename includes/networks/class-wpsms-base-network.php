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
	public function __construct( $plugin_name, $log ) {
		$this->log         = $log;
		$this->settings    = $this->get_settings();
		$this->plugin_name = $plugin_name;
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


}