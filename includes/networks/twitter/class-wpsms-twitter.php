<?php

require_once __DIR__ . "/../class-wpsms-base-network.php";

/**
* Load recent posts from Facebook
 *
 * @since      1.0.6
 * @package    Wpsms
 * @subpackage Wpsms/includes
 * @author     Pete Molinero <pete@laternastudio.com>
 */

class Wpsms_Twitter extends Wpsms_Base_Network {

	/**
	 * The type identifier of the plugin
	 *
	 * @since    1.0.6
	 */
	protected $type = 'twitter';	


	/**
	 * Retrieve the settings for the network
	 *
	 * @return  array
	 */
	public function get_settings() {

		// Load settings and delete empty values
		$settings = $this->remove_empty_settings( get_option('wpsms_twitter_settings', array() ) );

		// The default values for the settings
		$defaults = [
			'enable'              => '0',
			'username'            => '',
			'consumer_key'        => '',
			'consumer_key_secret' => '',
			'access_token'        => '',
			'access_token_secret' => '',
		];

		// Any keys not present will be added with the default value
		$settings = $settings + $defaults;

		return $settings;		
	}

	/**
	 * Retrieve the posts from the Twitter API
	 *
	 * @since   1.0.6
	 * @return  array  an array of post objects
	 */
	public function get_posts( $count ) {

		// Don't return anything if it's disabled
		if ( $this->settings['enable'] !== '1' ) return false;

		// Load Twitter class
		require_once('TwitterOAuth.php');

		// Create the connection
		$twitter = new TwitterOAuth(
			$this->settings['consumer_key'],
			$this->settings['consumer_key_secret'],
			$this->settings['access_token'],
			$this->settings['access_token_secret']
			);

		// Migrate over to SSL/TLS
		$twitter->ssl_verifypeer = true;

		// Load the Tweets
		$tweets = $twitter->get('statuses/user_timeline', array('screen_name' => $this->settings['username'], 'exclude_replies' => 'true', 'include_rts' => 'false', 'count' => $count ));
		$post_collection = [];

		// Return an empty set if there were no tweets or there were errors
		if ( empty($tweets) || isset( $tweets->errors ) ) return $post_collection;

	    foreach($tweets as $tweet) {

	    	// Correct any special character issues
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

	    	$twitter_post = new Wpsms_Post( array(
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

		return $post_collection;
	}

	/**
	 * Get the public view for the post
	 *
	 * The public view for the post is the way that it appears within the
	 * social media slider. That is, the view that is used to display the
	 * actual post data.
	 *
	 * @since  1.0.6
	 * @param  object  $post  The post object
	 * @return string         The rendered view
	 */
	public function get_public_view( $post ) {
		return $this->render( plugin_dir_path( __FILE__ ) . 'views/wpsms-twitter-public-view.php', array( 'post' => $post ) );
	}


	/**
	 * Get the admin view for the post
	 *
	 * The admin view for the post is what appears on the settings page,
	 * above the actual settings. Otherwise known as the "instructions."
	 *
	 * @since  1.0.6
	 * @return string         The rendered view
	 */
	public function get_admin_view() {
		return $this->render( plugin_dir_path( __FILE__ ) . 'views/wpsms-twitter-admin-view.php' );
	}


	/**
	 * Register all of the settings needed for Twitter
	 * 
	 * @return void
	 */
	public function register_settings() {

		register_setting('wpsms_settings', 'wpsms_twitter_settings');

		add_settings_section(
	        'wpsms_twitter_section',
	        __( 'Twitter Settings', 'wp-social-media-slider' ),
	        array($this, 'settings_callback'),
	        $this->plugin_name
	    );

	    // Enable or disable Twitter Posts
	    add_settings_field(
	    	'twitter_enable',
	    	__( 'Enable Twitter', 'wp-social-media-slider' ),
	    	array( $this, 'enable_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter username
	    add_settings_field(
	    	'twitter_username',
	    	__( 'Twitter Username', 'wp-social-media-slider' ),
	    	array( $this, 'username_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter consumer key
	    add_settings_field(
	    	'twitter_consumer_key',
	    	__( 'Twitter Consumer Key', 'wp-social-media-slider' ),
	    	array( $this, 'consumer_key_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter consumer secret
	    add_settings_field(
	    	'twitter_consumer_key_secret',
	    	__( 'Twitter Consumer Key Secret', 'wp-social-media-slider' ),
	    	array( $this, 'consumer_key_secret_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter access token
	    add_settings_field(
	    	'twitter_access_token',
	    	__( 'Twitter Access Token', 'wp-social-media-slider' ),
	    	array( $this, 'access_token_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');

	    // The Twitter access token secret
	    add_settings_field(
	    	'twitter_access_token_secret',
	    	__( 'Twitter Access Token Secret', 'wp-social-media-slider' ),
	    	array( $this, 'access_token_secret_setting' ),
	    	$this->plugin_name,
	    	'wpsms_twitter_section');
	}

	public function settings_callback() {
	    echo $this->get_admin_view();
	}

	public function enable_setting() {

		printf( '<div class="onoffswitch">
				    <input type="checkbox" name="wpsms_twitter_settings[enable]" class="onoffswitch-checkbox" id="twitter_enable" value="1" ' . checked('1', $this->settings['enable'], false) . ' >
				    <label class="onoffswitch-label" for="twitter_enable">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>');
	}

	public function username_setting() {
		echo "<input name='wpsms_twitter_settings[username]' type='text' value='{$this->settings['username']}' />";
	}

	public function consumer_key_setting() {
		echo "<input name='wpsms_twitter_settings[consumer_key]' type='text' value='{$this->settings['consumer_key']}' />";
	}

	public function consumer_key_secret_setting() {
		echo "<input name='wpsms_twitter_settings[consumer_key_secret]' type='text' value='{$this->settings['consumer_key_secret']}' />";
	}

	public function access_token_setting() {
		echo "<input name='wpsms_twitter_settings[access_token]' type='text' value='{$this->settings['access_token']}' />";
	}

	public function access_token_secret_setting() {
		echo "<input name='wpsms_twitter_settings[access_token_secret]' type='text' value='{$this->settings['access_token_secret']}' />";
	}

}