<?php 
if (!defined('ABSPATH')) { exit(); }

$mobile_change = get_theme_option('mobile_change', array('ctext'));
?>
			</div>			                                            	
		</div>
		
		<!-- footer -->
		<div class="footer_wrap">
			<div class="footer">

				<?php if ($mobile_change['ctext']) { ?>
					<div class="copyright"><?php echo apply_filters('comment_text', $mobile_change['ctext']); ?></div>
				<?php } ?>
				
				<div class="topped js_to_top" title="<?php _e('to the top', 'pntheme'); ?>"><span><?php _e('to the top', 'pntheme'); ?></span></div>

				<a href="<?php echo web_vers_link(); ?>" rel="nofollow" class="webversion_link"><span><?php _e('Go to a Original version', 'pntheme'); ?></span></a>
					
					<div class="clear"></div>
			</div>
		</div>
		<!-- end footer -->
		
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>