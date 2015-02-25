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
		require_once __DIR__ . '/networks/class-wpsms-post.php';
	}


	/**
	 * Retrieve the posts from the various repositories.
	 *
	 * @since    0.9.1
	 */
	public function get_social_media() {
		
		$time_of_last_refresh = intval( $this->settings[ 'time_of_last_refresh' ] );

		// Determine if posts have NEVER been loaded.
		if ( $time_of_last_refresh == 0 ) {
			$this->refresh_cache();
		}

		// If AJAX Cache Refresh is turned off, we may need to
		// refresh the cache right now.
		if ( $this->settings['ajax_cache_refresh'] == '0' ) {

			// Refresh if the cache has expired
			if( $this->is_it_time_to_refresh() ) {
				$this->refresh_cache();
			}
		}

		$all_posts = $this->get_post_cache();

		return $all_posts;
	}


	/**
	 * Check to see if it is time to refresh cache
	 * 
	 * @return boolean
	 */
	public function is_it_time_to_refresh() {

		// Find out if it is time to update, in which case we'll refresh the cache
		// via an ajax request after we've sent back the posts.
		$time_of_last_refresh = intval( $this->settings[ 'time_of_last_refresh' ] );
		$cache_length         = intval( $this->settings[ 'cache_length' ] );

		// Check to see if the cache length has been passed
		$is_it_time_to_refresh = ( $time_of_last_refresh + $cache_length ) < time();

		return $is_it_time_to_refresh;
	}


	/**
	 * Update all of the posts in the cache.
	 * 
	 * @return void
	 */
	public function refresh_cache() {

		// Retrieve posts from each of these social media networks
		$posts = array();

		foreach( $this->networks as $network ) {
			$posts[ $network->get_type() ] = $network->get_posts( $this->settings['total_posts'] );
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
			// Save the time of this update
			update_option( 'wpsms_time_of_last_refresh', time() );

			// Save the posts to the cache
			update_option( 'wpsms_post_cache', json_encode( $all_posts ) );	
			
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
	public function get_post_cache() {
		$posts = get_option( 'wpsms_post_cache' );
		return json_decode( $posts );
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
	public function truncate_by_network_equally( $networks ) {

		$all_posts = array();
		$index = 0;

		while ( count( $all_posts ) < $this->settings['total_posts'] ) {

			// Grab the most recent post from the given social media network
			$post = reset( $networks[ $index ] ); // Set pointer to the first element and return the value
			$post_key = key( $networks[ $index ] ); // returns the index element of the current array position.

			// Remove the array element
			unset( $networks[ $index ][$post_key] );

			if( $post ) {
				$all_posts[ $post_key ] = $post;
			}

			// We're cycling through the social networks, adding posts from each.
			// The index helps us to advance to the next network when we've pulled
			// a post from the current network. Here we increase or reset the index.
			$index = ( $index < count( $networks ) - 1 ) ? $index + 1 : 0;

			// Make there are enough total posts to continue going
			$is_post_remaining = false;

			foreach ( $networks as $network ) {
				if ( !empty( $network ) ) {
					$is_post_remaining = true;
				}
			}
			
			// If no remaining posts were found, break
			if ( !$is_post_remaining ) break;

		}

		// Sort the array by key in reverse order
		// In this case, the keys are the time of posting
		krsort( $all_posts );

		return $all_posts;
	}
}
