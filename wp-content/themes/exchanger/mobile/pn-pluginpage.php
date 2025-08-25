<?php 
if (!defined('ABSPATH')) { exit(); }

mobile_theme_include('header'); 
?>

	<?php 
	if (have_posts()) : ?>
    <?php while (have_posts()) : the_post();  ?>		

		<?php the_content(); ?>				
		
	<?php endwhile; ?>
    <?php endif; ?>			
	
	<div class="clear"></div>

	<?php do_action('premium_after_content'); ?>
		
<?php 

mobile_theme_include('footer'); 