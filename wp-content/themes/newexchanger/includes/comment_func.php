<?php
if (!defined('ABSPATH')) { exit(); }

remove_filter('comment_flood_filter', 'wp_throttle_comment_flood', 10, 3);

function theme_comment_posts($comment, $args, $depth) { 

	$comment_author_url = esc_url($comment->comment_author_url);
	?>	
	<li id="li-comment-<?php echo $comment->comment_ID; ?>">
		<div class="comment" id="comment-<?php echo $comment->comment_ID; ?>" itemprop="review" itemscope itemtype="https://schema.org/Review">
			<div class="comment_top">
				<div class="comment_top_ins">
					<a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>" class="comment_permalink" title="<?php _e('Link for comment', 'pntheme'); ?>">#</a>
					
					<meta itemprop="name" content="<?php echo pn_strip_input(wp_trim_words($comment->comment_content, 2)); ?>">
					<meta itemprop="datePublished" content="<?php comment_time('Y-m-d'); ?>">
					
					<div itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
						<meta itemprop="worstRating" content="1"> 
						<meta itemprop="ratingValue" content="5"> 
						<meta itemprop="bestRating" content="5">
					</div>
					
					<div class="comment_author">
						<?php if ($comment_author_url) { ?><a href="<?php echo $comment_author_url;?>" rel="nofollow noreferrer noopener" target="_blank"><?php } ?>
							<span itemprop="author"><?php echo pn_strip_input($comment->comment_author); ?></span>
						<?php if ($comment_author_url) { ?></a><?php } ?>
						
						<?php edit_comment_link(__('Edit comment', 'pntheme'), '<span class="link_edit">[', ']</span>'); ?>
					</div>
					<div class="comment_date"><?php comment_time('d.m.Y, H:i'); ?></div>
				</div>
			</div>
			<?php if (0 == $comment->comment_approved) { ?>
				<div class="comment_notapproved">
					<?php _e('Comment is moderation', 'pntheme'); ?>
				</div>
			<?php } ?>
			
			<div class="comment_text">
				<div class="text" itemprop="description">
					<?php comment_text(); ?>
						<div class="clear"></div>
				</div>	
			</div>
				<div class="clear"></div>

			<?php comment_reply_link(array_merge($args, array('reply_text' => '[' . __('reply comment', 'pntheme') . ']', 'login_text' => '', 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>					
		</div>
	<?php	 
}

function theme_comment_posts_end() {
    echo '</li><!-- end comments -->';
}