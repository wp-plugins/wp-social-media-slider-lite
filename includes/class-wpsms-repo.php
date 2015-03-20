<?php
/**
 * Load posts from each of the social networks
 *
 * @link       http://wpsocialmediaslider.com
 * @since      0.9.1
 *
 * @package    Wpsms
 * @subpackage Wpsms/includes
 */

class Wpsms_Repo {

	/**
	 * The property that stores all of the saved settings.
	 *
	 * @since    0.9.1
	 */
	private $settings;

	/**
	 * The registered social media networks.
	 *
	 * @since    1.0.6
	 */
	private $networks;

	/**
	 * The registered logger
	 *
	 * @since    1.0.6
	 */
	private $log;

	/**
	 * Repository constructer. Sets up the settings property.
	 *
	 * @since    0.9.1
	 */
	public function __construct( $settings, $networks, $log ) {
		$this->settings = $settings;
		$this->networks = $networks;
		$this->log      = $log;

		// Load the object that posts are stored in
		require_once dirname(__FILE__) . '/networks/class-wpsms-post.php';
	}


	/**
	 * Retrieve the posts from the various repositories.
	 *
	 * @since    0.9.1
	 */
	public function get_social_media( $shortcode_atts ) {
		
		// Grab the time of last refresh
		$refresh_data              = json_decode( $this->settings[ 'time_of_last_refresh' ], true );
		$all_times_of_last_refresh = is_array( $refresh_data ) ? $refresh_data : array();

		// Determine if posts have NEVER been loaded.
		if ( !$this->post_cache_exists( $shortcode_atts ) ) {
			$this->log->output( "Forcing refresh because a cache didn't exist..." );
			$this->refresh_cache( $shortcode_atts );
		}
		else {
			$this->log->output( "A cache existed." );
		}

		// If AJAX Cache Refresh is turned off, we may need to
		// refresh the cache right now.
		if ( $this->settings['ajax_cache_refresh'] == '0' ) {

			// Refresh if the cache has expired
			if( $this->is_it_time_to_refresh( $shortcode_atts ) ) {
				$this->refresh_cache( $shortcode_atts );
			}
		}

		$all_posts = $this->get_post_cache( $shortcode_atts );

		return $all_posts;
	}


	/**
	 * Check to see if it is time to refresh cache
	 * 
	 * @return boolean
	 */
	public function is_it_time_to_refresh( $shortcode_atts ) {

		// Find out if it is time to update, in which case we'll refresh the cache
		// via an ajax request after we've sent back the posts.
		
		$refresh_data              = json_decode( $this->settings[ 'time_of_last_refresh' ], true );
		$all_times_of_last_refresh = is_array( $refresh_data ) ? $refresh_data : array();
		$cache_length              = intval( $this->settings[ 'cache_length' ] );

		// Get the time of last refresh for this specific slider url
		if ( array_key_exists( (string) $shortcode_atts['url'], $all_times_of_last_refresh ) ) {
			$time_of_last_refresh = intval( $all_times_of_last_refresh[ $shortcode_atts['url'] ] );
		}
		else {
			$time_of_last_refresh =  0;
		}
		
		// Check to see if the cache length has been passed
		$is_it_time_to_refresh = ( $time_of_last_refresh + $cache_length ) < time();
		return $is_it_time_to_refresh;
	}


	/**
	 * Update all of the posts in the cache.
	 * 
	 * @return void
	 */
	public function refresh_cache( $shortcode_atts ) {

		$this->log->output( "Refreshing..." );

		// Retrieve posts from each of these social media networks
		$posts = array();

		foreach( $this->networks as $network ) {
			$network_posts = $network->get_posts( $this->settings['total_posts'], $shortcode_atts );
			if ( $network_posts !== false )	$posts[ $network->get_type() ] = $network_posts;
		}

		// Truncate the number of posts based on the display type
		if( $this->settings['display_type'] == '2' ) {
			$all_posts = $this->truncate_by_network_equally( $posts );
		}
		else {

			// Join the arrays together with the addition operator
			$joined_posts = array();

			foreach( $posts as $network ) {
				$joined_posts += $network;
			}

			$all_posts = $this->truncate_by_most_recent( $joined_posts );
		}

		if ( !empty( $all_posts ) ) {

			$refresh_data              = json_decode( $this->settings[ 'time_of_last_refresh' ], true );
			$all_times_of_last_refresh = is_array( $refresh_data ) ? $refresh_data : array();
			$all_times_of_last_refresh[ $shortcode_atts['url'] ] = time();

			// Save the time of this update
			update_option( 'wpsms_time_of_last_refresh', json_encode( $all_times_of_last_refresh ) );

			$cache_data                                = get_option( 'wpsms_post_cache' );
			$all_post_caches                           = is_array( $cache_data ) ? $cache_data : array();
			$all_post_caches[ $shortcode_atts['url'] ] = json_encode( $all_posts );

			// Save the posts to the cache
			update_option( 'wpsms_post_cache', $all_post_caches );	
			
			return true;		
		}
		else {
			return false;
		}
	}


	/**
	 * Get the posts from the cache
	 * 
	 * @return   array  An array of the posts from the cache
	 */
	public function get_post_cache( $shortcode_atts ) {

		$cache_data      = get_option( 'wpsms_post_cache' );
		$all_post_caches = is_array( $cache_data ) ? $cache_data : array();

		if( array_key_exists( $shortcode_atts['url'], $all_post_caches ) ) {
			$posts = json_decode( $all_post_caches[ $shortcode_atts['url'] ] );
		}
		else {
			$posts = false;
		}

		return $posts;
	}

	/**
	 * Check if there is an existing post cache for the slider
	 *
	 * @since   1.1.3
	 * @param   array  $shortcode_atts  all of the defined shortcode attributes
	 * @return  bool                    whether or not it exists
	 */
	public function post_cache_exists( $shortcode_atts ) {

		$cache_data      = get_option( 'wpsms_post_cache' );
		$all_post_caches = is_array( $cache_data ) ? $cache_data : array();

		if( array_key_exists( $shortcode_atts['url'], $all_post_caches ) ) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Check if there is an existing refresh time for the slider
	 *
	 * @since   1.1.3
	 * @param   array  $shortcode_atts  all of the defined shortcode attributes
	 * @return  bool                    whether or not it exists
	 */
	public function refresh_time_exists( $shortcode_atts ) {

		$refresh_data              = json_decode( $this->settings[ 'time_of_last_refresh' ], true );
		$all_times_of_last_refresh = is_array( $refresh_data ) ? $refresh_data : array();
		$cache_length              = intval( $this->settings[ 'cache_length' ] );

		// Get the time of last refresh for this specific slider url
		if ( array_key_exists( (string) $shortcode_atts['url'], $all_times_of_last_refresh ) ) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Truncate posts based on most recent
	 *
	 * This method of truncating posts relies entirely on the post date.
	 * For example, if it wants to limit to 20 posts, it will simply
	 * truncate to the 20 most recent posts.
	 *
	 * @since      0.9.1
	 */
	public function truncate_by_most_recent( $all_posts ) {

		// In this case, the keys are the time of posting
		krsort( $all_posts );

		// Truncate to the correct number of posts
		while( count( $all_posts ) > $this->settings['total_posts'] ) {
			array_pop( $all_posts );
		}

		return $all_posts;

	}


	/**
	 * Truncate posts while maintaining equal numbers per social media network
	 *
	 * This method of truncating posts tries to enforce that the same number
	 * of posts is used from each social media network. For example, if 15 posts
	 * are requested, it will pull 5 from each social media network. In cases
	 * where a number not divisible by 3 is provided, it will simply pull an extra
	 * one or two posts from one or two of the networks. The same steps are taken
	 * if one of the social networks does not have enough posts available to satisfy
	 * the needs of the request.
	 *
	 * @since      0.9.1
	 */
	public function truncate_by_network_equally( $posts ) {

		$selected_posts = array();
		$posts_by_network = array();

		// Convert posts to a numerically indexed array
		foreach ( $posts as $key => $network_posts ) {
			$posts_by_network[] = $network_posts;
		}

		$index = 0;

		while ( count( $selected_posts ) < $this->settings['total_posts'] ) {

			// Grab the most recent post from the given social media network
			$post = reset( $posts_by_network[ $index ] ); // Set pointer to the first element and return the value
			$post_key = key( $posts_by_network[ $index ] ); // returns the index element of the current array position.

			// Remove the array element
			unset( $posts_by_network[ $index ][$post_key] );

			if( $post ) {
				$selected_posts[ $post_key ] = $post;
			}

			// We're cycling through the social networks, adding posts from each.
			// The index helps us to advance to the next network when we've pulled
			// a post from the current network. Here we increase or reset the index.
			$index = ( $index < count( $posts_by_network ) - 1 ) ? $index + 1 : 0;

			// Make there are enough total posts to continue going
			$is_post_remaining = false;

			foreach ( $posts_by_network as $network ) {
				if ( !empty( $network ) ) {
					$is_post_remaining = true;
				}
			}
			
			// If no remaining posts were found, break
			if ( !$is_post_remaining ) break;

		}

		// Sort the array by key in reverse order
		// In this case, the keys are the time of posting
		krsort( $selected_posts );

		return $selected_posts;
	}
}
