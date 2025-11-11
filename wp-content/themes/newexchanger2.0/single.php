<?php
if( !defined( 'ABSPATH')){ exit(); }
get_header();

?>
<div class="single_news_wrap">

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post();

$blog_url = get_blog_url();

$image_arr = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
$image = trim(is_isset($image_arr,0));
?>



	<div class="mb-m-30 mb-t-45 mb-35">
		<div class="news-block single">
			<?php do_action('seodata_post', $post, 'single'); ?>
			<div class="news-block__wrapper single_news">
				<div class="news-block__image">
					<img class="news-img" src="<?php echo $image; ?>" alt="">
				</div>
				<div class="news-block__right">
					<div class="text-content mb-m-20 mb-15">
						<?php the_content(); ?>
					</div>
					<span class="time mb-m-15 mb-10"><?php the_time(get_option('date_format').', '.get_option('time_format')); ?></span>
					<div class="tegs_wrapper">
						<?php the_tags('<ul class="tegs"><li>'. __('Tags','pntheme') .':</li> ', '', '</ul>' ); ?>
					</div>
					<ol class="news-breadcrumb">
						<li class="news-breadcrumb-item"><?php _e('Category','pntheme'); ?>:&nbsp;<?php the_terms( $post->ID, 'category','<span itemprop="articleSection">',', ','</span>'); ?></li>
					</ol>
					<div class="news-block__more">
						<a href="<?php echo $blog_url;?>" class="more position-right"><?php _e('Back to news','pntheme'); ?></a>
					</div>
				</div>
			</div>
		</div>

	</div>

<?php endwhile; ?>
<?php endif; ?>

</div>

<?php comments_template( '', true ); ?>

<?php

get_footer();
