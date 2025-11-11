<?php
if( !defined( 'ABSPATH')){ exit(); }

remove_action('wp_head','start_post_rel_link',10,0);
remove_action('wp_head','index_rel_link');
remove_action('wp_head','adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action('wp_head','wp_shortlink_wp_head', 10, 0 );
remove_action('wp_head','feed_links_extra', 3);
remove_action('wp_head','feed_links', 2);

remove_action('wp_head','print_emoji_detection_script',7);
remove_action('wp_print_styles','print_emoji_styles',10);

function new_excerpt_length($length) {
	return 22;
}
add_filter('excerpt_length', 'new_excerpt_length');

function new_excerpt_more($more) {
	return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');

add_filter('comment_text', 'not_transform_quotes',100);
add_filter('the_title', 'not_transform_quotes',100);
add_filter('the_content', 'not_transform_quotes',100);
add_filter('the_excerpt', 'not_transform_quotes', 100);
function not_transform_quotes($content){
    return str_replace(array('&#171;','&#187;'),'"',$content);
}

register_nav_menu('the_top_menu', __('Top menu for Guests','pntheme'));
register_nav_menu('the_top_menu_user', __('Top menu for Users','pntheme'));
register_nav_menu('the_bottom_menu', __('Bottom menu','pntheme'));

function no_menu(){

}

if(function_exists('add_theme_support')){
    add_theme_support('post-thumbnails');
	add_image_size('site-thumbnail', 370, 150, true);
}

register_sidebar(array(
    'name'=> __('Sidebar'),
	'id' => 'unique-sidebar-id',
	'before_title' => '<div class="widget_title"><div class="widget_titlevn">',
	'after_title' => '</div></div>',
	'before_widget' => '<div class="widget"><div class="widget_ins">',
	'after_widget' => '<div class="clear"></div></div></div>',
));

add_action('wp_enqueue_scripts', 'my_themeinit', 0);
function my_themeinit(){

	$plugin = get_plugin_class();
	$plugin_url = get_premium_url();

	wp_enqueue_style('nunito-sans', is_ssl_url("https://fonts.googleapis.com/css?family=Nunito:300,300i,400,400i,600,600i,700,700i&display=swap&subset=cyrillic,cyrillic-ext,latin-ext"), false, $plugin->vers());
	wp_enqueue_script("jquery select", $plugin_url . "js/jquery-select/script.min.js", false, $plugin->vers('0.7'));
	wp_enqueue_script("jquery-table", $plugin_url ."js/jquery-table/script.min.js", false, $plugin->vers('0.5'));
	wp_enqueue_script("jquery-checkbox", $plugin_url .'js/jquery-checkbox/script.min.js', false, $plugin->vers('0.2'));
	wp_enqueue_script('jquery-site-js', PN_TEMPLATEURL .'/js/site.js', false, $plugin->vers());
	wp_enqueue_style('theme-style', PN_TEMPLATEURL . "/style.css", false, $plugin->vers());

}

function the_lang_list($wrap_class=''){
	$list = '';

	if(is_ml()){
		$list .= '<div class="'. $wrap_class .'">';

		$lang = get_locale();
		$langs = get_langs_ml();

		$list .= '
		<div class="langlist_div">
			<div class="langlist_title"><span>'. get_lang_key($lang) .'</span></div>
			<div class="langlist_ul">
		';

			foreach($langs as $lan){
				$cl = '';
				if($lan == $lang){ $cl = '';}
				$list .= '
				<a href="'. get_lang_vers($lan) .'" rel="nofollow" class="langlist_li '. $cl .'">
					<div class="langlist_liimg">
						<img src="'. get_lang_icon($lan) .'" alt="" class="'.$lan.'" />
					</div>
					'. get_title_forkey($lan) .'
				</a>';
			}

		$list .= '
			</div>
		</div>';

		$list .= '</div>';
	}

	echo $list;
}

function the_flags_list($wrap_class=''){
	$list = '';

	if(is_ml()){
		$list .= '<div class="'. $wrap_class .'">';

		$lang = get_locale();
		$langs = get_langs_ml();

		foreach($langs as $lan){
			$cl = '';
			if($lan == $lang){ $cl = 'active';}
			$list .= '
			<a href="'. get_lang_vers($lan) .'" rel="nofollow" class="langlist_li '.$lan.' '. $cl .' "></a>';
		}

		$list .= '</div>';
	}

	echo $list;
}

add_filter('merchant_footer', function ($html) {
    $change = get_option('h_change');
    $mode   = isset($change['switcher']) ? (int)$change['switcher'] : 0; // 0=light,1=dark,2=switcher

    ob_start(); ?>
    <script>
      (function($){
        var $html = $('html');
        var $body = $('body');
        var $both = $html.add($body);

        function setTheme(t){
          t = (t === 'dark') ? 'dark' : 'light';
          $both.removeClass('light dark').addClass(t);
        }


        var mode  = <?php echo (int)$mode; ?>;
        var saved = localStorage.getItem('theme'); // 'light' | 'dark' | null

        if (mode === 0) {
          setTheme('light');
          localStorage.removeItem('theme');
        } else if (mode === 1) {
          setTheme('dark');
          localStorage.removeItem('theme');
        } else {
          if (saved !== 'light' && saved !== 'dark') {
            saved = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
          }
          setTheme(saved);
        }


        $body.css('visibility','visible');


        window.addEventListener('storage', function(e){
          if (e.key === 'theme') setTheme(e.newValue === 'dark' ? 'dark' : 'light');
        });
      })(jQuery);
    </script>
    <?php
    return $html . ob_get_clean();
}, 1000);
