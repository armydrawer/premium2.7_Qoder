<?php
if (!defined('ABSPATH')) { exit(); }
	
if (!function_exists('convert_link_ml')) {
		
	function convert_link_ml($link) {
		
		if (get_site_lang() != get_locale()) {
			$key = get_lang_key(get_locale());
			if (false === strpos($link, get_site_url_or() . $key . '/')) {
				return str_replace(rtrim(get_site_url_or(), '/'), get_site_url_or() . $key, $link);
			}
		}
		
		return $link;
	}
		
	function get_the_terms_ml($terms) {
		$in_key = array('name', 'description', 'post_title', 'post_content', 'post_excerpt');
		if (is_array($terms)) { 
			$lang = get_locale();
			$new_terms = array();
			foreach ($terms as $key => $class) {	
				if (is_object($class)) {
					$new_class = array();
					foreach ($class as $cl_key => $cl_zn) {
						if (in_array($cl_key, $in_key)) {
							$new_class[$cl_key] = ctv_ml($cl_zn);
						} else {
							$new_class[$cl_key] = $cl_zn;
						}
					}
					$new_terms[$key] = (object)$new_class;
				}
			}
			
			return $new_terms;
		}
		
		return $terms;
	}	
		
	function get_term_ml($term) {
		if (is_object($term)) {
			$lang = get_locale();
			$new_class = new stdClass;
			foreach ($term as $cl_key => $cl_zn) {
				if ('name' == $cl_key or 'description' == $cl_key) {
					$new_class->$cl_key = ctv_ml($cl_zn);
				} else {
					$new_class->$cl_key = $cl_zn;
				}				
			}	
				
			if (class_exists('WP_Term')) {
				return new WP_Term($new_class);
			} else {
				return $new_class;
			}
		}
			
		return $term;
	}	
		
	function get_bloginfo_rss_ml($output, $show) {
		
		if ('url' == $show or 'wpurl' == $show) {
			return convert_link_ml($output);
		}
		
		return $output;
	}
		
	function hide_inline_action($actions) {
		
		if (isset($actions['inline hide-if-no-js'])) {
			unset($actions['inline hide-if-no-js']);
		}
		
		return $actions;
	}		
		
 	function add_form_fields_ml($tag) {
		?>
		<script type="text/javascript">
		jQuery(function($) {
			
			$('#tag-name').parents('.form-field').hide();
			$('#tag-description').parents('.form-field').hide();

			$('.terms_name_val').on('change', function() {
				var vale = $(this).val();
				$('#tag-name').val(vale);
			});
			
		});		
		</script>
			
		<input type="hidden" name="tag_ml_filter" value="1" />
			
		<?php 
		$admin_lang = get_admin_lang();
		$langs = get_langs_ml();
		foreach ($langs as $key) { ?>
			<div class="form-field <?php if ($admin_lang == $key) { ?>form-required<?php } ?> term-name-wrap">
				<label><?php _ex('Name', 'term name'); ?> (<?php echo get_title_forkey($key); ?>)</label>
				<input name="<?php echo $key; ?>_tag_name_ml" <?php if ($admin_lang == $key) { ?>class="terms_name_val" aria-required="true"<?php } ?> type="text" value="" size="40" />
				<p><?php _e('The name is how it appears on your site.', 'premium'); ?></p>
			</div>		
		<?php 
		} 
		
		foreach ($langs as $key) { ?>
			<div class="form-field term-name-wrap">
				<label><?php _e('Description', 'premium'); ?> (<?php echo get_title_forkey($key); ?>)</label>
				<textarea name="<?php echo $key; ?>_description_ml" rows="5" cols="40"></textarea>
				<p><?php _e('The description is not prominent by default; however, some themes may show it.', 'premium'); ?></p>
			</div>		
		<?php 
		} 		
	} 
		  
	function created_tags_ml($id) {
		global $wpdb;
			
		if (isset($_POST['tag_ml_filter'])) {
				
			$name = pn_strip_input(is_param_post_ml('tag_name_ml'));
			$wpdb->update($wpdb->prefix . "terms", array('name' => $name), array('term_id' => $id));
				
			$description = pn_strip_text(is_param_post_ml('description_ml'));
			$wpdb->update($wpdb->prefix . "term_taxonomy", array('description' => $description), array('term_id' => $id));
			
		}
	}	
		
 	function edit_form_fields_ml($tag) {
		?>
		<script type="text/javascript">
		jQuery(function($) {
			
			$('#name').parents('.form-field').hide();
			$('#description').parents('.form-field').hide();
				
			$('.terms_name_val').on('change', function() {
				var vale = $(this).val();
				$('#name').val(vale);
			});
			
		});		
		</script>
			
		<input type="hidden" name="tag_ml_edit" value="1" />
			
		<table class="form-table">
		<?php 
		$admin_lang = get_admin_lang();
		$langs = get_langs_ml();
			
		$name_to_pattern = get_value_ml($tag->name);
		$description_to_pattern = get_value_ml($tag->description);
			
		foreach ($langs as $key) { 
			$val = is_isset($name_to_pattern, $key);
		?>
				<tr class="form-field <?php if ($admin_lang == $key) { ?>form-required<?php } ?> term-name-wrap">
					<th scope="row">
						<label><?php _ex('Name', 'term name'); ?> (<?php echo get_title_forkey($key); ?>)</label>
					</th>
					<td>
						<input name="<?php echo $key; ?>_tag_name_ml" type="text" value="<?php echo pn_strip_input($val); ?>" size="40" <?php if ($admin_lang == $key) { ?>class="terms_name_val" aria-required="true"<?php } ?> />
						<p class="description"><?php _e('The name is how it appears on your site.', 'premium'); ?></p>
					</td>
				<tr>			
		<?php 
		}	

		foreach ($langs as $key) { 
			$val = is_isset($description_to_pattern, $key);
		?>
			<tr class="form-field form-required term-name-wrap">
				<th scope="row">
					<label><?php _e('Description', 'premium'); ?> (<?php echo get_title_forkey($key); ?>)</label>
				</th>
				<td>
					<textarea name="<?php echo $key; ?>_description_ml" rows="5" cols="50" class="large-text"><?php echo pn_strip_text($val); ?></textarea>
					<p class="description"><?php _e('The description is not prominent by default; however, some themes may show it.', 'premium'); ?></p>
				</td>
			</tr>			
		<?php 
		} 		
		?>
		</table>
		<?php	
	}  
		
	function edit_tags_ml($id) {
		global $wpdb;
			
		if (isset($_POST['tag_ml_edit'])) {
				
			$name = pn_strip_input(is_param_post_ml('tag_name_ml'));
			$wpdb->update($wpdb->prefix . "terms", array('name' => $name), array('term_id' => $id));
				
			$description = pn_strip_text(is_param_post_ml('description_ml'));
			$wpdb->update($wpdb->prefix . "term_taxonomy", array('description' => $description), array('term_id' => $id));
			
		} 
	}			
		
 	function edit_form_before_permalink_mli($post) {
		if (isset($post->post_type) and 'attachment' != $post->post_type) {
		?>
			<style>
			#titlewrap, 
			#postdivrich {
				display: none!important;	
			} 
			input.post_title_multi {
				padding: 3px 8px;
				font-size: 1.7em;
				line-height: 100%;
				height: 1.7em;
				width: 100%;
				outline: none;
				margin: 0;
				background-color: #fff;
			}				
			</style>
			
			<input type="hidden" name="title_ml_admin" value="1" />
					
			<?php 
			$admin_lang = get_admin_lang();
			$langs = get_langs_ml();

			$title_to_pattern = get_value_ml(pn_strip_input($post->post_title));
			$title_placeholder = apply_filters('enter_title_here', __('Enter title here', 'premium'), $post);
			foreach ($langs as $key) {
				$val = is_isset($title_to_pattern, $key);
				?>
						
				<div style="margin: 0 0 10px 0;">
					<div style="margin: 0 0 3px 0; font-weight: 600;"><?php echo $title_placeholder; ?> (<?php echo get_title_forkey($key); ?>)</div>
					<input type="text" name="<?php echo $key; ?>_title_ml" size="30" class="post_title_multi <?php if ($admin_lang == $key) { ?>post_title_currentlang<?php } ?>" value="<?php echo $val; ?>" spellcheck="true" autocomplete="off" />
				</div>
						
				<?php
			} 
			?>
		<?php
		}
	}  
		
 	function edit_form_after_title_ml($post) {
		if (isset($post->post_type)) {
			if ('attachment' != $post->post_type) {	
		?>
			<div style="padding: 10px 0 0 0;">
				<input type="hidden" name="content_ml_admin" value="1" />
				<?php 
				if (class_exists('PremiumForm')) {
					$form = new PremiumForm();
					$form->wp_editor('content_ml', $post->post_content, 24, true, 1); 
				}
				?>	
			</div>	
		<?php
			} 	
		}
	} 	
		
	function new_excerpt_metabox_ml($post_id) {
		if (function_exists("add_meta_box")) {
			$args = array('public' => true, 'capability_type' => 'post' );
			$post_types = get_post_types($args,'names');
			if (is_array($post_types)) {
				foreach ($post_types as $pt) {
					if ('attachment' != $pt) {
						remove_meta_box('postexcerpt', $pt, 'normal');
						add_meta_box("pn_excerpt_id", __('Excerpt'), "excerpt_box_ml", $pt, "normal");
					}
				}
			}
		}
	} 

 	function excerpt_box_ml($post) { 
		$post_id = $post->ID; 				
		?>
		<input type="hidden" name="excerpt_ml_admin" value="1" />
		<?php 
		if (class_exists('PremiumForm')) {
			$form = new PremiumForm();
			$form->wp_editor('excerpt_ml', $post->post_excerpt, 7, false, 1);
		}
		?>		
		<?php 
	}  	
		
	function edit_post_ml($post_id) {
		global $wpdb;
		
		if (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
		
		$post_id = intval($post_id);
		if ($post_id) {
			$parr = array();
			if (isset($_POST['title_ml_admin'])) {					
				$parr['post_title'] = pn_strip_input(is_param_post_ml('title_ml'));					
			}
			if (isset($_POST['content_ml_admin'])) {
				$parr['post_content'] = pn_strip_text(is_param_post_ml('content_ml'));			
			}	
			if (isset($_POST['excerpt_ml_admin'])) {
				$parr['post_excerpt'] = pn_strip_text(is_param_post_ml('excerpt_ml'));			
			}
			if (count($parr) > 0) {
				$wpdb->update($wpdb->prefix . "posts", $parr, array('ID' => $post_id)); 
			}
		}
	} 
			
	if (is_admin()) {
		
		add_action('edit_form_before_permalink', 'edit_form_before_permalink_mli');
		add_action('edit_form_after_title', 'edit_form_after_title_ml' );
		
		add_action("admin_menu", "new_excerpt_metabox_ml", 0);
		add_action("edit_post", "edit_post_ml");
		
		add_action('init', 'all_posttypes_set_langs', 10000); 
		function all_posttypes_set_langs() {
			
			$args = array('public' => true);
			$post_types = get_post_types($args,'names');
			if (is_array($post_types)) {
				foreach ($post_types as $pt) {
					if ('attachment' != $pt) {
						add_action($pt . '_row_actions', 'hide_inline_action');
					}
				}
			}
			
			$taxonomies = get_taxonomies('', 'objects');
			if (is_array($taxonomies)) {
				$not = array('nav_menu', 'link_category', 'post_format');
				foreach ($taxonomies as $tax) {    
					$name = $tax->name;
					if (!in_array($name, $not)) {
						
						add_action($name . '_row_actions', 'hide_inline_action');
						add_action($name . '_add_form_fields', 'add_form_fields_ml');
						add_action($name . '_edit_form', 'edit_form_fields_ml');
						add_action('edit_' . $name, 'edit_tags_ml');
						add_action('created_' . $name, 'created_tags_ml');
						
					}
				}
			}
			
		}
		
		add_filter('term_name',	'ctv_ml', 0);
		add_filter('the_category', 'ctv_ml', 0);
		add_filter('list_cats', 'ctv_ml', 0);
		add_filter('term_description', 'ctv_ml', 0);
		/* add_filter('terms_to_edit', 'ctv_ml', 0); */
		
	} else {  

		add_filter('self_link', 'convert_link_ml');
		add_filter('post_type_archive_link', 'convert_link_ml');
		add_filter('term_link',	'convert_link_ml');	
		add_filter('author_feed_link', 'convert_link_ml');
		add_filter('author_link', 'convert_link_ml');
		add_filter('day_link', 'convert_link_ml');
		add_filter('get_comment_author_url_link', 'convert_link_ml');
		add_filter('month_link', 'convert_link_ml');
		add_filter('page_link',	'convert_link_ml');
		add_filter('post_link',	'convert_link_ml');
		add_filter('post_type_link', 'convert_link_ml');
		add_filter('year_link',	'convert_link_ml');
		add_filter('the_permalink',	'convert_link_ml');
		add_filter('get_comment_link',	'convert_link_ml', 0);
		add_filter('category_feed_link', 'convert_link_ml');
		add_filter('feed_link',	'convert_link_ml');
		add_filter('get_comments_pagenum_link',	'convert_link_ml');
		add_filter('cancel_comment_reply_link',	'convert_link_ml');
		add_filter('tag_feed_link',	'convert_link_ml');
		add_filter('get_pagenum_link',	'convert_link_ml');
		add_filter('post_comments_feed_link', 'convert_link_ml');
		add_filter('get_bloginfo_rss', 'get_bloginfo_rss_ml', 0, 2);
		add_filter('get_the_terms', 'get_the_terms_ml', 0);
		add_filter('get_term', 'get_term_ml', 0);
		
	}	
		
	function the_title_navmenu($string) { 
		if (is_admin()) {
			$req = explode('/', is_isset($_SERVER, 'REQUEST_URI'));
			$end_req = end($req);
			$end_req = explode('?', $end_req);
			$end_req = $end_req[0];
			
			$ref = explode('/', is_isset($_SERVER, 'HTTP_REFERER'));
			$end_ref = end($ref);
			$end_ref = explode('?', $end_ref);
			$end_ref = $end_ref[0];
			
			if ($end_req and 'nav-menus.php' == $end_req or $end_req and 'admin-ajax.php' == $end_req and $end_ref and 'nav-menus.php' == $end_ref) {
				return $string;
			} 
		} 
		
		return ctv_ml($string);
	}		
	add_filter('the_title',	'the_title_navmenu', 0);
	/* add_filter('get_terms', 'get_the_terms_ml', 0); */

	add_filter('option_blogname', 'ctv_ml', 0);
	add_filter('option_blogdescription', 'ctv_ml', 0); 
	add_filter('get_the_excerpt', 'ctv_ml', 0);
	add_filter('the_content', 'ctv_ml', 0);
	add_filter('the_excerpt_rss', 'ctv_ml', 0);
	add_filter('single_post_title',	'ctv_ml', 0);
	add_filter('get_wp_title_rss', 'ctv_ml', 0);
	add_filter('wp_title_rss', 'ctv_ml', 0);
	add_filter('the_title_rss', 'ctv_ml', 0);
	add_filter('the_content_rss', 'ctv_ml', 0);
	add_filter('get_pages',	'get_the_terms_ml', 0);

} 