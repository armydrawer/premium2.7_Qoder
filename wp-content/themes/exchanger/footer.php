<?php 
if (!defined('ABSPATH')) { exit(); }

$f_change = get_theme_option('f_change', array('ctext', 'timetable', 'phone', 'vk', 'fb', 'tw'));
?>
				<?php if (!is_front_page() and !is_page_template('pn-notsidebar.php')) { ?>
				</div>
				<div class="sidebar">
					<?php get_sidebar(); ?>
				</div>	
				<?php } ?>	

					<div class="clear"></div>
			</div>
		</div>
		
		<!-- footer --> 
		<div class="footer_wrap">
			<div class="footer">
		
				<div class="footer_left">
					<div class="copyright">
						<?php if ($f_change['ctext']) { ?>
							<?php echo apply_filters('comment_text', $f_change['ctext']); ?>
						<?php } else { ?>
							&copy; <?php echo get_copy_date('2015'); ?> PremiumExchanger.com — <?php _e('electronic currency exchange service.', 'pntheme'); ?>
						<?php } ?>
					</div>
					
					<div class="footer_menu">
						<?php
						wp_nav_menu(array(
							'sort_column' => 'menu_order',
							'container' => 'div',
							'container_class' => 'menu',
							'menu_class' => 'hmenu',
							'menu_id' => '',
							'depth' => '1',
							'fallback_cb' => 'no_menu',
							'theme_location' => 'the_bottom_menu'
						));						
						?>
							<div class="clear"></div>
					</div>				
				</div>
				
				<div class="footer_center">
					<div class="footer_soc">
						<?php 
						$self_link = lang_self_link();
						$self_link = urlencode($self_link);
						
						$arr = array('vk', 'fb', 'tw');
						foreach ($arr as $ar) {
							if ($f_change[$ar]) {
								$link = $f_change[$ar];
								$link_class = '';
								if (strstr($link,'[soc_link]')) {
									$link_class = 'social_link';
									
									if ('vk' == $ar) {
										$link = 'https://vk.com/share.php?url='.$self_link;
									} elseif ('fb' == $ar) {
										$link = 'https://www.facebook.com/sharer/sharer.php?u='.$self_link;
									} elseif ('tw' == $ar) {
										$link = 'https://twitter.com/share?url='.$self_link;
									}	
								}
								$link = esc_url($link);
							?>
								<a href="<?php echo $link; ?>" class="<?php echo $link_class; ?>" target="_blank" rel="nofollow"><img src="<?php echo PN_TEMPLATEURL; ?>/images/<?php echo $ar; ?>-ico.png" alt="" /></a>
							<?php 
							} 
						}
						?>	
							<div class="clear"></div>
					</div>
					
					<?php if (function_exists('mobile_vers_link')) { ?>
					<div class="mobile_link_wrap">
						<a href="<?php echo mobile_vers_link(); ?>" class="mobile_link"><span><?php _e('Mobile version', 'pntheme'); ?></span></a>
							<div class="clear"></div>
					</div>		
					<?php } ?>
				</div>
				
				<div class="footer_line"></div>
				
				<div class="footer_right">
				
					<?php if ($f_change['phone']) { ?>
					<div class="footer_phone">
						<span><?php echo pn_strip_input($f_change['phone']); ?></span>
					</div>
					<?php } ?>
					
					<?php if ($f_change['timetable']) { ?>
					<div class="footer_timetable">
						<?php echo apply_filters('comment_text', $f_change['timetable']); ?>
					</div>
					<?php } ?>				
				
				</div>	
				
					<div class="clear"></div>
			</div>
		</div>
		<!-- end footer -->
		
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>