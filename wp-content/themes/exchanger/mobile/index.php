<?php  
if (!defined('ABSPATH')) { exit(); } 

mobile_theme_include('header'); 
?>

	<h1 class="page_wrap_title">
		<?php if (is_category() or is_tag() or is_tax()) { ?>
			<?php single_term_title(); ?>
		<?php } elseif (is_home()) { ?>
			<?php _e('News', 'pntheme'); ?>
		<?php } ?>
	</h1>

	<?php if (is_category() or is_tax() or is_tag()) { ?>
		<?php 
		$description = trim(term_description()); 
		if (strlen($description) > 0) {
		?>
			<div class="term_description">
				<div class="text">
					<?php echo apply_filters('the_content', $description); ?>
					<div class="clear"></div>
				</div>	
			</div>
		<?php } ?>
	<?php } ?>	

	<div class="many_news_wrap">
		<div class="many_news">
	
		<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>	
		
			<div class="one_news" itemscope itemtype="https://schema.org/NewsArticle">
				
				<?php do_action('seodata_post', $post); ?>
				
				<h2 class="one_news_title">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><span><?php the_title(); ?></span></a>
				</h2>
				
				<div class="one_news_date">
					<?php the_time(get_option('date_format')); ?>
				</div>
					<div class="clear"></div>
				
				<div class="one_news_excerpt">
					<div class="text" itemprop="articleBody">
						<a href="<?php the_permalink();?>">
							<?php the_excerpt(); ?>
						</a>
							<div class="clear"></div>
					</div>
				</div>
				
				<div class="metabox_div">
					<div class="metabox_cats">
						<span><?php _e('Category', 'pntheme'); ?>:</span> <?php the_terms($post->ID, 'category', '<span itemprop="articleSection">', ', ', '</span>'); ?>
					</div>
					
					<?php the_tags('<div class="metabox_tags"><span>' . __('Tags', 'pntheme') . ':</span> ', ', ', '</div>'); ?>
				</div>	

				<a href="<?php the_permalink();?>" itemprop="url" class="one_news_more"><?php _e('Read more', 'pntheme'); ?></a>
				    <div class="clear"></div>				
			</div>	
		
		<?php endwhile; ?>
		<?php else : ?>
		
		<div class="noitem">
			<p><?php _e('Unfortunately this section is empty', 'pntheme'); ?></p>								
		</div>
		
		<?php endif; ?>
		
		</div>
		<?php the_pagenavi(); ?>
	</div>

<?php 

mobile_theme_include('footer'); 