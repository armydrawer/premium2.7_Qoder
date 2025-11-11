<?php
if( !defined( 'ABSPATH')){ exit(); }

/*

Template Name: Promo page template

*/

get_header();
?>


<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>

		<?php the_content(); ?>
		<?php echo theme_promo_block(0); ?>


<?php endwhile; ?>
<?php endif; ?>



<?php do_action('premium_after_content'); ?>

<?php
get_footer();?>
