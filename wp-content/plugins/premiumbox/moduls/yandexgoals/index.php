<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Yandex.metrica goals[:en_US][ru_RU:]Цели яндекс.метрики[:ru_RU]
description: [en_US:]Yandex.metrica goals[:en_US][ru_RU:]Цели яндекс.метрики[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

add_filter('all_metasettings_option', 'pn_all_metasettings_option');
function pn_all_metasettings_option($options) {
	
	$plugin = get_plugin_class();
	
	$n_options = array();
	$n_options['ya_goal1'] = array(
		'view' => 'inputbig',
		'title' => __('Choosing exchange direction', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal1'),
		'name' => 'ya_goal1',
		'work' => 'input',
	);			
	$n_options['ya_goal2'] = array(
		'view' => 'inputbig',
		'title' => __('Entering exchange amount in exchange form', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal2'),
		'name' => 'ya_goal2',
		'work' => 'input',
	);
	$n_options['ya_goal3'] = array(
		'view' => 'inputbig',
		'title' => __('Entering personal data in exchange form', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal3'),
		'name' => 'ya_goal3',
		'work' => 'input',
	);
	$n_options['ya_goal4'] = array(
		'view' => 'inputbig',
		'title' => __('Create order button', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal4'),
		'name' => 'ya_goal4',
		'work' => 'input',
	);
	$n_options['ya_goal5'] = array(
		'view' => 'inputbig',
		'title' => __('Create order button (in step 2)', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal5'),
		'name' => 'ya_goal5',
		'work' => 'input',
	);
	$n_options['ya_goal6'] = array(
		'view' => 'inputbig',
		'title' => __('Cancel order button', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal6'),
		'name' => 'ya_goal6',
		'work' => 'input',
	);
	$n_options['ya_goal7'] = array(
		'view' => 'inputbig',
		'title' => __('Go to payment/I paid button', 'pn'),
		'default' => $plugin->get_option('seo', 'ya_goal7'),
		'name' => 'ya_goal7',
		'work' => 'input',
	);
	$options = pn_array_insert($options, 'ya_metrika', $n_options);
	
	return $options;
}

add_action('wp_enqueue_scripts', 'seopremiumbox_themeinit', 0);
function seopremiumbox_themeinit() {
	
	$plugin = get_plugin_class();
	$ya_metrika = pn_strip_input($plugin->get_option('seo', 'ya_metrika'));
	if ($ya_metrika) {
		wp_enqueue_script('jquery-yametrika-js', $plugin->plugin_url . 'moduls/yandexgoals/js/yaMetrika.js', false, $plugin->vers('0.1'));
	}
	
}

add_action('wp_footer' , 'wp_footer_seopremiumbox', 11);
function wp_footer_seopremiumbox() {
	
	$plugin = get_plugin_class();
	$ya_metrika = pn_strip_input($plugin->get_option('seo', 'ya_metrika'));
	if ($ya_metrika) {
	?>
	<script type="text/javascript">
	jQuery(function($) { 
	
		$(document).PremiumYaMetrika({
			'id': <?php echo $ya_metrika; ?>,
<?php 
$r = 0;
while ($r++ < 7) {
?>
			'goal_<?php echo $r; ?>': '<?php echo pn_strip_input($plugin->get_option("seo", "ya_goal" . $r)); ?>',
<?php } ?>
			'test': <?php echo is_debug_mode(); ?>
		}); 
		
	});
	</script>
	<?php
	}
	
}											