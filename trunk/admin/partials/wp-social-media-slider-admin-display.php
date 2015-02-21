<?php

/**
 * Provide a dashboard view for the plugin.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      0.9.1
 *
 * @package    WP_Social_Media_Slider
 * @subpackage WP_Social_Media_Slider/admin/partials
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
		<a href="#general-settings">General Settings</a> | 
		<a href="#twitter-settings">Twitter Settings</a> | 
		<a href="http://wpsocialmediaslider.com/#contact-us" target="_blank">Suggest a Feature</a>
	</p>

	<div class="notifications">
		<div class="activation-form update-nag">
			<p>To unlock Facebook and Instagram, please <a href="http://wpsocialmediaslider.com" target="_blank">purchase the full version</a> of WP Social Media Slider.</p>
		</div>
	</div>

	<form method="post" action="options.php" enctype="multipart/form-data">
		<?php settings_fields('wpsms_settings') ?>
		<?php do_settings_sections($this->plugin_name) ?>

		<p class="submit">
			<input name="submit" type="submit" class="button-primary" value="Save Changes" />
		</p>
	</form>

</div>