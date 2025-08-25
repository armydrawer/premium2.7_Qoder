<?php 
if( !defined( 'ABSPATH')){ exit(); } 
get_header(); 
?>

<div class="page_wrap">

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>
						
	<div class="text">
					
		<?php the_content(); ?>
						
			<div class="clear"></div>
	</div>
				
<?php endwhile; ?>								
<?php endif; ?>

</div>			

<?php do_action('premium_after_content'); ?>		

<?php 
get_footer();?>