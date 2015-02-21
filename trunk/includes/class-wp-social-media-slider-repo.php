<?php

/**
 * Load posts from each of the social networks
 *
 * @link       http://example.com
 * @since      0.9.1
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/includes
 */

/**
 * Load posts from each of the social networks.
 *
 * This class defines all code necessary to fetch posts from social networks.
 *
 * @since      0.9.1
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/includes
 * @author     Pete Molinero <pete@laternastudio.com>
 */
class WP_Social_Media_Slider_Repo {

	/**
	 * The property that stores all of the saved settings.
	 *
	 * @since    0.9.1
	 */
	private $settings;

	/**
	 * Simple method to log data to a file.
	 *
	 * @since    0.9.1
	 */
	public function log( $data ) {
		error_log( print_r( $data, true ) );
	}

	/**
	 * Repository constructer. Sets up the settings property.
	 *
	 * @since    0.9.1
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
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

			// Check to see if it is time to refresh
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

		// Retrieve posts from Twitter
		$twitter_posts = $this->twitter_posts() ?: [];
		$all_posts = $this->truncate_by_most_recent( $twitter_posts );

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
	 * Load recent posts from Twitter
	 *
	 * Load up the most recent posts from Twitter. Can specify
	 * either a date range or number of posts.
	 *
	 * @since    0.9.1
	 */
	public function twitter_posts() {


		if ( $this->settings['twitter_enable'] == '1' ) {
			# Load Twitter class
			require_once('TwitterOAuth.php');

			# Create the connection
			$twitter = new TwitterOAuth(
				$this->settings['twitter_consumer_key'],
				$this->settings['twitter_consumer_key_secret'],
				$this->settings['twitter_access_token'],
				$this->settings['twitter_access_token_secret']);

			# Migrate over to SSL/TLS
			$twitter->ssl_verifypeer = true;

			# Load the Tweets
			$tweets = $twitter->get('statuses/user_timeline', array('screen_name' => $this->settings['twitter_username'], 'exclude_replies' => 'true', 'include_rts' => 'false', 'count' => $this->settings['total_posts']));
			$post_collection = [];

			# Example output
			if( !empty($tweets) && !isset( $tweets->errors ) ) {

			    foreach($tweets as $tweet) {

			    	// Access as an object
			        $tweet_html = htmlentities( $tweet->text, ENT_NOQUOTES, 'UTF-8');;

			        // Make links active
			        $tweet_html = preg_replace("@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@", '<a href="$1" target="_blank">$1</a>', $tweet_html);

			        // Linkify user mentions
			        $tweet_html = preg_replace("/@(\w+)/", '<a href="http://twitter.com/$1" target="_blank">@$1</a>', $tweet_html);

			        // Linkify tags
			    	$tweet_html = preg_replace("/\s+#(\w+)/", ' <a href="https://twitter.com/search?q=%23$1" target="_blank">#$1</a>', $tweet_html);

			        // Parse the tweet created_at into a php date
			        $tweet_date = strtotime( $tweet->created_at );

			        $tweet_images = array();

			    	// Add any existing images to the object
			    	if ( !empty( $tweet->entities->media ) ) {
			    		foreach( $tweet->entities->media as $image ) {

			    			$tmp_image = new stdClass();
			    			
			    			$tmp_image->thumbnail = new stdClass();
			    			$tmp_image->thumbnail->url = $image->media_url . ':thumb';
			    			$tmp_image->thumbnail->width = $image->sizes->thumb->w;
			    			$tmp_image->thumbnail->height = $image->sizes->thumb->h;

			    			$tmp_image->low_resolution = new stdClass();
			    			$tmp_image->low_resolution->url = $image->media_url . ':small';
			    			$tmp_image->low_resolution->width = $image->sizes->small->w;
			    			$tmp_image->low_resolution->height = $image->sizes->small->h;

			    			$tmp_image->standard_resolution = new stdClass();
			    			$tmp_image->standard_resolution->url = $image->media_url . ':large';
			    			$tmp_image->standard_resolution->width = $image->sizes->large->w;
			    			$tmp_image->standard_resolution->height = $image->sizes->large->h;

			    			$tweet_images[] = $tmp_image;
			    		}
			    	}

			    	$this->log( $tweet_images );

			    	$twitter_post = new WP_Social_Media_Slider_Post( array(
						'type'           => 'twitter',
						'unix_timestamp' => $tweet_date,
						'username'       => $tweet->user->screen_name,
						'images'         => $tweet_images
			    		));

			    	

			    	// Set up filters
			    	$twitter_post->html = apply_filters( 'wpsms-post-html', $tweet_html, $twitter_post );
			    	$twitter_post->date = apply_filters( 'wpsms-post-date', $this->ago( $tweet_date ), $twitter_post );

			    	$post_collection[ $tweet_date ] = $twitter_post;
			    }
			}

			return $post_collection;
		}
		else return false;
	}


	public function ago($time)
	{
		date_default_timezone_set('America/New_York');
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

}
