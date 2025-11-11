<?php
if( !defined( 'ABSPATH')){ exit(); }

get_header();
?>

	<?php if (is_category() or is_tax() or is_tag()) { ?>
		<?php
		$description = trim(term_description());
		if (strlen($description) > 0) {
		?>
			<div class="term_description">
				<div class="text">
					<?php echo apply_filters('the_content',$description); ?>
					<div class="clear"></div>
				</div>
			</div>
		<?php } ?>
	<?php } ?>

	<div class="many_news_wrap">
		<div class="mb-m-30 mb-t-45 mb-35">

			<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post();
				$image_arr = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
				$image = trim(is_isset($image_arr,0));
				$class = '';
				if ($image) { $class = 'has_img'; }
				$link = get_permalink($post->ID);
				$title = pn_strip_input(ctv_ml($post->post_title));
			?>

				<div class="news-block" itemscope itemtype="https://schema.org/NewsArticle">

					<?php do_action('seodata_post', $post); ?>

					<div class="news-block__wrapper">
						<?php if($image){ ?>
						<div class="news-block__left">
							<div class="news-block__img mb-m-10">
								<img src="<?php echo $image; ?>" alt="">
							</div>
							<ol class="news-breadcrumb">
								<li class="news-breadcrumb-item"><?php _e('Category','pntheme'); ?>:&nbsp;<?php the_terms( $post->ID, 'category','<span itemprop="articleSection">',', ','</span>'); ?></li>
							</ol>
						</div>
						<?php } ?>
						<div class="news-block__right">
							<span class="time mb-m-15 mb-10"><?php the_time(get_option('date_format').', '.get_option('time_format')); ?></span>
							<h2 class="h4-title news-block__title mb-20">
								<a href="<?php echo $link; ?>" rel="bookmark" title="<?php echo $title; ?>"><span><?php the_title(); ?></span></a>
							</h2>
							<div class="text-content mb-m-20 mb-20">
								<?php the_excerpt(); ?>
							</div>
							<?php if(!$image){ ?>
							<ul class="tegs mb-m-25 mb-35">
								<?php the_tags('<ul class="tegs mb-m-25 mb-35"><li>'. __('Tags','pntheme') .':</li> ', ', ', '</ul>' ); ?>
							</ul>
							<?php } ?>
							<div class="news-block__more">
								<a href="<?php echo $link; ?>" class="more position-right"><?php _e('Read more','pntheme'); ?></a>
							</div>
						</div>
					</div>

				</div>

			<?php endwhile; ?>

			<?php else : ?>

			<div class="text">
				<p><?php _e('Unfortunately this section is empty','pntheme'); ?></p>
			</div>

			<?php endif; ?>

		</div>

		<?php the_pagenavi(); ?>
	</div>
<?php

get_footer();
