<?php 
if( !defined( 'ABSPATH')){ exit(); }

/*

Template Name: Page template without sidebar

*/

get_header(); 
?>

<div class="page_wrap">

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>
										
	<?php the_content(); ?>
				
<?php endwhile; ?>								
<?php endif; ?>	

</div>

<?php do_action('premium_after_content'); ?>
		
<?php get_footer();?>