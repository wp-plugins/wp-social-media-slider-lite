<?php
/**
 * The view file for the public facing Twitter posts.
 *
 * This file can be overwritten by copying to a /wp-social-media-slider folder
 * inside your child theme directory.
 *
 * @since  1.1.1
 */
?>

<div class="owl-item wpsms-item wpsms-twitter">

	<?php do_action( 'wpsms-before-post', $post ); ?>

	<?php // The link to the social network account, and the post time ?>
	<div class="wpsms-meta-info wpsms-icon-twitter">
		<a href="http://www.twitter.com/<?php echo $post->username; ?>" target="_blank">@<?php echo $post->username; ?></a>
		<p class="post-time"><?php echo $post->date; ?></p>
	</div>

	<?php // Display the post image at the left, if one exists ?>
	<?php if ( !empty( $post->images ) ) : ?>
		<a class="wpsms-magnific wpsms-image" href="<?php echo $post->images[0]->standard_resolution->url; ?>">
			<img src="<?php echo $post->images[0]->thumbnail->url; ?>" />
		</a>
	<?php endif; ?>

	<?php // Display the html of the actual tweet ?>
	<p class="wpsms-body-text">
		<?php echo $post->html; ?>
		<?php if( $post->shortened ) : ?>
			<a href="<?php echo $post->url; ?>" class="wpsms-read-more">
				<?php echo $post->read_more_text; ?>
			</a>
		<?php endif; ?>	
	</p>

	<?php do_action( 'wpsms-after-post', $post ); ?>

</div>