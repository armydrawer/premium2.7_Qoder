<?php
if( !defined( 'ABSPATH')){ exit(); }
 
function bread_page($id,$array,$pof, $fid, $position=2){
	
	if(!is_array($array)){ $array = array(); }
	$position = intval($position);
	
    if($id){
		$id = intval($id);
		
        global $wpdb;
        $post_data = $wpdb->get_row("SELECT ID, post_title, post_parent FROM ".$wpdb->prefix."posts WHERE post_type='page' AND post_status='publish' AND ID='$id'");
        
		if(isset($post_data->ID)){
			if($post_data->ID != $pof and $post_data->ID != $fid){
				$array[]= '
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="'. get_permalink($post_data->ID) .'" itemprop="item">
						<span itemprop="name">'. pn_strip_input(ctv_ml($post_data->post_title)) .'</span>
					</a>
					<meta itemprop="position" content="'. $position .'" />
				</li>';
			}
			
			$position++;
			bread_page($post_data->post_parent,$array,$pof,$fid, $position);			
		}
		
    } else {
        $array = array_reverse($array);
        foreach($array as $sarray){
            echo $sarray;
        }
    } 
}
 
function the_breadcrumb($home_title='', $news_title=''){
global $post, $wp_query; 

	$home_title = trim($home_title);
	if(!$home_title){ $home_title =  __('Home','pntheme'); }
	$news_title = trim($news_title);
	if(!$news_title){ $news_title = __('News','pntheme'); }
  
 	$pof = get_option('page_on_front');
    $sof = get_option('show_on_front');	
	$home_url = get_site_url_ml();
    if($sof == 'page'){
        $blog_url = get_permalink(get_option('page_for_posts'));
    } else {
        $blog_url = $home_url;
    }	
	?>
	<ul itemscope itemtype="https://schema.org/BreadcrumbList">
		<li class="first" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="<?php echo $home_url;?>" itemprop="item">
				<span itemprop="name"><?php echo $home_title; ?></span>
			</a>
			<meta itemprop="position" content="1" />
		</li>
		
	    <?php if(is_singular('post') or is_tag() or is_category()){ ?>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="<?php echo $blog_url; ?>" itemprop="item">
					<span itemprop="name"><?php echo $news_title; ?></span>
				</a>
				<meta itemprop="position" content="2" />
			</li>
		<?php } elseif(is_singular()){ 
			$post_type_obj = get_post_type_object($post->post_type);
			if($post_type_obj->hierarchical != 1){
		?>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="/<?php echo $post_type_obj->query_var; ?>/" itemprop="item">
					<span itemprop="name"><?php echo $post_type_obj->label; ?></span>
				</a>
				<meta itemprop="position" content="2" />
			</li>		
		<?php 
			}
		} elseif(is_page()){ ?>
			<?php bread_page($post->ID,'',$pof, $post->ID, 2); ?>
		<?php } ?>
		
			<div class="clear"></div>
	</ul>
	<?php	
}

function the_breadcrumb_title(){
	if(is_category() or is_tag() or is_tax()){ 
		single_term_title(); 
	} elseif(is_404()){ 
		_e('Error 404','pntheme'); 
	} elseif(is_home()){ 
		_e('News','pntheme');
	} elseif(is_post_type_archive() and is_object($wp_query)) { 
		echo $wp_query->queried_object->label;	
	} elseif(function_exists('is_pn_page') and is_pn_page('exchange') and function_exists('get_exchange_title')) { 
		echo get_exchange_title();	 				
	} else { 
		if(function_exists('is_pn_page') and is_pn_page('hst') and function_exists('get_exchangestep_title')){
			echo get_exchangestep_title();
		} else {
			the_title();
		}
	}	
}