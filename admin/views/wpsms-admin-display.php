<?php

/**
 * Provide a dashboard view for the plugin.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      0.9.1
 *
 * @package    Wpsms
 * @subpackage Wpsms/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<!--[if IE 8]>
	<script type="text/javascript">
		document.body.className += ' is-ie-8';
	</script>
<![endif]-->

<div class="wrap wpsms-settings-admin-panel">
	
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

		<p>
			<a href="#general-settings"><?php echo __( 'General Settings', 'wp-social-media-slider' ); ?></a> | 
			<a href="#facebook-settings"><?php echo __( 'Facebook Settings', 'wp-social-media-slider' ); ?></a> | 
			<a href="#instagram-settings"><?php echo __( 'Instagram Settings', 'wp-social-media-slider' ); ?></a> | 
			<a href="#twitter-settings"><?php echo __( 'Twitter Settings', 'wp-social-media-slider' ); ?></a>
		</p>

	<div class="notifications">
		<div class="update-nag">
			If you'd like to display Facebook and Instagram posts in your slider, <a href="http://wpsocialmediaslider.com" target="_blank">purchase the full version</a>!
		</div>

		<?php  if ( !function_exists('curl_version') ) : ?>
			<div class="error">
				<p><?php printf( __( 'Please %1senable the Curl PHP extension%2s on your server.', 'wp-social-media-slider' ),
						'<a href="http://wpsocialmediaslider.com/docs#troubleshooting" target="_blank">',
						'</a>'); ?>
				</p>
			</div>
		<?php endif; ?>

	</div>

	<form method="post" action="options.php" enctype="multipart/form-data">
		<?php settings_fields('wpsms_settings') ?>
		<?php do_settings_sections($this->plugin_name) ?>

		<p class="submit">
			<input name="submit" type="submit" class="button-primary" value="Save Changes" />
		</p>
	</form>

</div>