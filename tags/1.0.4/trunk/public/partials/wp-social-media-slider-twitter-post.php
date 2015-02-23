<div class="owl-item wpsms-item wpsms-twitter">
	<div class="wpsms-meta-info wpsms-icon-twitter">
		<a href="http://www.twitter.com/<?php echo $post->username; ?>" target="_blank">@<?php echo $post->username; ?></a>
		<p class="post-time"><?php echo $post->date; ?></p>
	</div>
	<?php if ( !empty( $post->images ) ) : ?>
		<a class="wpsms-magnific" href="<?php echo $post->images[0]->standard_resolution->url; ?>">
			<img src="<?php echo $post->images[0]->thumbnail->url; ?>" />
		</a>
	<?php endif; ?>
	<?php echo $post->html; ?>
</div>