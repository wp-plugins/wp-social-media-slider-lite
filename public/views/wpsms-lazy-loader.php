<?php
/**
 * The template for lazy loading.
 *
 * This file is what is actually placed in the page when the shortcode
 * is use. It then waits for the page to load, then pulls in the posts.
 *
 * @link       http://www.laternastudio.com
 * @since      0.9.1
 *
 * @package    Wpsms
 * @subpackage Wpsms/public/partials
 */
?>

<!--[if IE 8]>
	<script type="text/javascript">
		document.body.className += ' is-ie-8';
	</script>
<![endif]-->

<?php
	$instance_id = ( is_array( $atts ) && array_key_exists( 'id', $atts ) ) ? $atts['id'] : '0';
?>

<!-- This line is included to prevent the flash of
	 unstyled icons before the icon font is loaded -->
<div style="font-family: wpsmsfont;"></div>

<div class="wpsms-container">
	<div class="wpsms" id="wpsms-<?php echo $instance_id; ?>" >

		<?php for( $i = 0; $i < 10; $i++ ) : ?>
			<div class="item lazy-load-spinner">
				<div class="spinner">
				  <div class="rect1"></div>
				  <div class="rect2"></div>
				  <div class="rect3"></div>
				  <div class="rect4"></div>
				  <div class="rect5"></div>
				</div>
			</div>
		<?php endfor; ?>

	</div>
</div>

<script>
	(function( $ ) {
		'use strict';

		$( document ).ready( function() {

			/**
			 * Initialize the social media slider's carousel
			 */
			<?php
				// Use the custom initializer
				if ( $this->settings['custom_js_init'] ) {
					printf( $this->settings[ 'custom_js_init' ] );
				}
				// Or print out the default javascript initializer
				else {
					?>
					$(".wpsms#wpsms-<?php echo $instance_id; ?>").owlCarousel({
						    loop:true,
						    margin:40,
						    nav:true,
							<?php
								if ( $this->settings['auto_play'] == '1' ) {
									printf('
										autoplay:true,
										autoplayTimeout:3000,
										autoplayHoverPause:true,
										');
								}
							?>
							navText: ['',''],
							stagePadding: 1,
						    responsive:{
						        0:{
						            items:1
						        },
						        600:{
						            items:2
						        },
						        1000:{
						            items:3
						        }
						    }
						});
					<?php
				}
			?>

			// Populate the slider helper
			SliderHelper.build();

			// Pause the slider (until it loads)
			SliderHelper.pause();

			<?php if ( count ( $atts ) > 0 && !empty( $atts ) ) { ?>
				// The shortcodes variables
				var wpsms_shortcode_atts = $.parseJSON ( '<?php echo json_encode( $atts ); ?>' );
			<?php } else { ?>
				var wpsms_shortcode_atts = {};
			<?php } ?>

			wpsms_shortcode_atts.url = "<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>";

			// Grab the posts via AJAX
			LazyLoader.loadPosts( SliderHelper, wpsms_shortcode_atts );

		});


	})( jQuery );
	
</script>