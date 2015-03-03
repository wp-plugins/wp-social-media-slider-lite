<?php

/**
 * The main object class for posts.
 *
 * This class is used for all posts returned from social media networks.
 * This ensures that they all have a consistent and reliable format.
 *
 * @link       http://wpsocialmediaslider.com
 * @since      0.9.1
 *
 * @package    Wpsms
 * @subpackage Wpsms/includes
 */

class Wpsms_Post {

	/**
	 * The images of the post
	 *
	 * @since    0.9.1
	 */
	 public $images;

	/**
	 * The formatted html of the post.
	 *
	 * @since    0.9.1
	 */
	 public $html;

	/**
	 * The date of the post.
	 *
	 * @since    0.9.1
	 */
	 public $date;

	/**
	 * The type of the post ( i.e. twitter, facebook, or instagram)
	 *
	 * @since    0.9.1
	 */
	 public $type;

	/**
	 * The user's username
	 *
	 * @since    0.9.1
	 */
	 public $username;

	/**
	 * The url of the user's social media page
	 *
	 * @since    0.9.1
	 */
	 public $user_url;

	/**
	 * The url of the social media post
	 *
	 * @since    0.9.1
	 */
	 public $url;

	/**
	 * The primary url that the post links to, if applicable
	 *
	 * @since    0.9.1
	 */
	 public $link;

	/**
	 * Whether or not the post was shortened
	 *
	 * @since    0.9.1
	 */
	 public $shortened;

	/**
	 * The Unix timestamp of the post
	 *
	 * @since    0.9.1
	 */
	 public $unix_timestamp;

	/**
	 * Initialize the variables used to maintain the actions and filters.
	 *
	 * @since    0.9.1
	 */
	public function __construct( $attrs ) {
		$this->images = ( isset( $attrs['images'] ) ) ? $attrs['images'] : array() ;
		$this->html = ( isset( $attrs['html'] ) ) ? $attrs['html'] : '' ;
		$this->date = ( isset( $attrs['date'] ) ) ? $attrs['date'] : '' ;
		$this->type = ( isset( $attrs['type'] ) ) ? $attrs['type'] : '' ;
		$this->username = ( isset( $attrs['username'] ) ) ? $attrs['username'] : '' ;
		$this->user_url = ( isset( $attrs['user_url'] ) ) ? $attrs['user_url'] : '' ;
		$this->url = ( isset( $attrs['url'] ) ) ? $attrs['url'] : '' ;
		$this->link = ( isset( $attrs['link'] ) ) ? $attrs['link'] : '' ;
		$this->shortened = ( isset( $attrs['shortened'] ) ) ? $attrs['shortened'] : false ;
		$this->unix_timestamp = ( isset( $attrs['unix_timestamp'] ) ) ? $attrs['unix_timestamp'] : '' ;
	}

}
