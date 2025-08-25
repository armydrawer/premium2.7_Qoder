<?php
if (!defined('ABSPATH')) { exit(); }

global $post;

if (!isset($post->ID)) {
	return;
}

if (post_password_required()) {
	return;
}	

$post_type_opencomment = apply_filters('post_type_opencomment', $post->comment_status, $post->post_type);
if ('open' != $post_type_opencomment) {
	return;
}	

$ui = wp_get_current_user();
$user_id = intval(is_isset($ui, 'ID'));
?>
<div id="comments">
	<div class="comments_wrap">
		<div class="comments_div">
			<div class="comments_title"><?php printf(__('Comments for news "%s"', 'pntheme'), ctv_ml($post->post_title)); ?></div>
	
			<?php
			if(have_comments()){ 
			?>
				<ul class="commentlist">
					
					<?php
					wp_list_comments(array('callback' => 'theme_comment_posts', 'end-callback' => 'theme_comment_posts_end'));
					?>
					
					<div class="clear"></div>
				</ul>
			<?php
			} else { 
			?>
				<div class="comment_no_item"><?php _e('No comments', 'pntheme'); ?></div>
			<?php 
			}
			?>

		</div>
	</div>
	
	<?php  
    if (get_comment_pages_count() > 1 or get_option('page_comments')) {
    ?>
    <div class="navigation comment-navigation">
		<div class="nav-previous"><?php previous_comments_link(__('&larr; Older Comments', 'pntheme')); ?></div>
		<div class="nav-next"><?php next_comments_link(__('Newer Comments &rarr;', 'pntheme')); ?></div>
			<div class="clear"></div>
    </div>
    <?php
    }
    ?>	
	
	<?php 
	if ('1' == get_option('comment_registration') and !$user_id) { ?>
		<div class="comment_no_item"><?php _e('Commenting is available only to registered users', 'pntheme'); ?></div>
	<?php 
	} else { 
	?> 	
		<div id="respond" class="comment_form">
			<div class="comment_form_title"><?php _e('your comment', 'pntheme'); ?> <?php cancel_comment_reply_link('[' . __('cancel reply', 'pntheme') . ']') ?></div>
			<form class="ajax_post_form" action="<?php echo get_pn_action('commentform'); ?>" method="post" id="commentform">

				<?php if (!$user_id) { ?>
				
					<div class="comment_form_line">
						<p><label for="author"><?php _e('Name', 'pntheme'); ?> <?php if ($req) { echo '<span class="req">*</span>'; } ?></label></p>
						<input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" />
					</div>
					
					<div class="comment_form_line">
						<p><label for="email"><?php _e('E-mail', 'pntheme'); ?> <?php if ($req) { echo '<span class="req">*</span>'; } ?></label></p>
						<input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" />
					</div>	
					
					<?php /* ?>
					<div class="comment_form_line">
						<p><label for="url"><?php _e('Website','pntheme'); ?></label></p>
						<input type="text" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" />
					</div>					
					<?php */ ?>
				<?php } ?>
				
				<div class="comment_form_line">
					<p><label for="comment"><?php _e('Comment text', 'pntheme'); ?></label></p>
					<textarea name="comment" id="comment"></textarea>
				</div>
				
				<?php do_action('comment_form', $post->ID); ?>
				
				<div class="comment_form_line"><input name="submit" class="submit" type="submit" value="<?php _e('Add comment', 'pntheme'); ?>" /></div>

				<div class="resultgo"></div>

				<?php comment_id_fields(); ?>
			
			</form>
		</div>	
	<?php 
	} 
	?>
	
</div> 