<div class='wpsms-instructions' >

	<!-- A dummy element for the anchor of our in-page navigation -->
	<span class='dummy-anchor' id="twitter-settings" ></span>

	<div class='section-heading' id="twitter-settings" >
		<?php echo __( 'Twitter Settings', 'wp-social-media-slider' ); ?>
		<span> / </span>
		<span>
			<a href="http://wpsocialmediaslider.com/docs/#setting-up-twitter" target="_blank" ><?php echo __( 'Setup Guide', 'wp-social-media-slider' ); ?></a>
		</span>
		<span> / </span>
		<p id="wpsms-connection-status-twitter" class="wpsms-connection-status">Checking connection status...</p>
		<script>
			(function( $ ) {
				'use strict';
				$( function() {
					wpsmsConnection.checkStatus( 'connection_status_twitter', '#wpsms-connection-status-twitter' );
				});
			})( jQuery );
		</script>	
	</div>

	<br style="clear: both;" />
</div>