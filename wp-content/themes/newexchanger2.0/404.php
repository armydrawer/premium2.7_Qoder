<?php  
if( !defined( 'ABSPATH')){ exit(); } 
get_header(); 

?>
<div class="page_wrap">	
	<div class="text">
			
		<h3><?php _e('What does it mean?','pntheme'); ?></h3>

		<ul>
			<li><?php _e('Page has been renamed','pntheme'); ?></li>
			<li><?php _e('Page has been deleted','pntheme'); ?></li>
			<li><?php _e('Pages never existed','pntheme'); ?></li>						
		</ul>
						
		<a class="btn btn-primary go_to_main" href="<?php echo get_site_url_ml() ?>"><?php printf(__('Go to main page','pntheme')); ?></a>
						
		<div class="clear"></div>
	</div>
</div>

<?php get_footer();?>