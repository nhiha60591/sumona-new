<?php
/* 
include theme related functions and filters 
*/

/* to fetch the front page page template - v 1.1.2 theme-functions.php file */
global $wpdb;
$pageid='';
if(!get_option('directory_frontpage')){
	$wp_pages = get_pages(array(
	'meta_key' => '_wp_page_template',
	'meta_value' => 'page-templates/front-page.php'
	));
	foreach($wp_pages as $page){
		$pageid = $page->ID;
	}
	if(!$pageid){
		$page_meta = array('_wp_page_template'=>'page-templates/front-page.php','Layout'=>'2c-l'); 
		$page_info_arr[] = array('post_title'=>'Front page',
								'post_content'=>'',
								'post_meta'=> $page_meta);
		if(function_exists('set_page_info_autorun'))
			set_page_info_autorun(@$pages_array,$page_info_arr); /* function to save.autosave the pages */
		$wp_pages = get_pages(array(
		'meta_key' => '_wp_page_template',
		'meta_value' => 'page-templates/front-page.php'
		));
		foreach($wp_pages as $page){
		 $pageid = $page->ID;
		}
	}
	update_option('directory_frontpage',$pageid);
}else{
	$pageid = get_option('directory_frontpage');
}
/* show custom home page for this theme*/
if(get_option('show_on_front') && !get_option('page_update_first')){
	update_option('show_on_front','page');
	update_option('page_on_front', $pageid);
	update_option('page_update_first', 1);
}
/* Hook to change the height of croausal slider image  */
add_filter('carousel_slider_height', 'responsive_crousal_height');
/* return height for crausal slider image */
function responsive_crousal_height($height){
	$height = 400;
	return $height;
}
/* set default hight and width of slider images */
add_filter('supreme_slider_width','supreme_slider_width_',11);
add_filter('supreme_slider_height','supreme_slider_height',11);
function supreme_slider_height($height){
	return 300;
}
function supreme_slider_width_($width){
	return 300;
}
/*
	Display Goggle map full widget on list and event listing page
 */
add_action('before_main','directory_theme_before_main');
function directory_theme_before_main(){
	global $post;	
	if(!is_single() && !is_author() && !is_home()){
		$tmpdata = get_option('templatic_settings');	
		if(isset($tmpdata['google_map_full_width']) && $tmpdata['google_map_full_width']=='yes'){
			remove_action('after_ecategiry_header','tmpl_after_ecategiry_header');
			remove_action('after_directory_header','after_directory_header');
			if(function_exists('tevolution_get_post_type'))
				$custom_post_type = apply_filters('directory_post_type_template',tevolution_get_post_type());
			
			$taxonomies = get_object_taxonomies( get_post_type() );
			$texonomy_name = $taxonomies[0];
			if(is_array(@$custom_post_type) && in_array(get_post_type(),@$custom_post_type)){
				if ( is_active_sidebar( 'after_directory_header') && (get_post_type() == 'listing') ) : ?>
						<div id="category-widget" class="category-widget">
							<?php dynamic_sidebar('after_directory_header'); ?>
						</div>
                <?php else: ?>
						<div id="category-widget" class="category-widget">
							<?php dynamic_sidebar('after_'.$texonomy_name.'_header'); ?>
						</div>	
               <?php endif;
			}
		}
	}
}

/* return the result of tevolution is activated or not */
if(!function_exists('is_tevolution_active')){
	function is_tevolution_active(){
		if(is_plugin_active('Tevolution/templatic.php')){
			return true;
		}else{
			return false;
		}
	}
}
add_filter('post_class','featured_post_class');

/* add class to featured [post */
function featured_post_class($class){
	global $post;
	if(is_author()){
		$featured=get_post_meta($post->ID,'featured_c',true);						
		$featured=($featured=='c')?'featured_c':'';	
		$class[]=$featured;
	}else{
		if(is_search())
		$featured=get_post_meta($post->ID,'featured_c',true);						
		$featured=(@$featured=='c')?'featured_c':'';	
		$class[]=' post '.$featured;	
	}
	
	return $class;
}
/* 
Name : directory_bdw_get_images_plugin
description : Resize image
*/
if(!function_exists('directory_bdw_get_images_plugin')){
	function directory_bdw_get_images_plugin($iPostID,$img_size='thumb',$no_images='') 
	{
		$arrImages = get_children('order=ASC&orderby=menu_order ID&post_type=attachment&post_mime_type=image&post_parent=' . $iPostID );	
		$counter = 0;
		$return_arr = array();	
		
		if (has_post_thumbnail( $iPostID ) && is_tax()){
			$img_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $iPostID ), 'thumbnail' );
			$imgarr['id'] = $id;
			$imgarr['file'] = $img_arr[0];
			$return_arr[] = $imgarr;
		}else{
		
			if($arrImages) 
			{
			   foreach($arrImages as $key=>$val)
			   {
					$id = $val->ID;
					if($val->post_title!="")
					{
						if($img_size == 'thumb')
						{
							$img_arr = wp_get_attachment_image_src($id, 'thumbnail'); // Get the thumbnail url for the attachment
							$imgarr['id'] = $id;
							$imgarr['file'] = $img_arr[0];
							$return_arr[] = $imgarr;
						}
						else
						{
							$img_arr = wp_get_attachment_image_src($id, $img_size); 
							$imgarr['id'] = $id;
							$imgarr['file'] = $img_arr[0];
							$return_arr[] = $imgarr;
						}
					}
					$counter++;
					if($no_images!='' && $counter==$no_images)
					{
						break;	
					}
					
			   }
			}	
		}  return $return_arr;
	}
}
add_action('templ_before_container_breadcrumb','breadcrumb_trail');
/*
 Name : supreme_layouts
 Description: Conditional logic deciding the layout of certain pages.
*/
function supreme_layouts() {
	if ( current_theme_supports( 'theme-layouts' ) ) {
		$global_layout = supreme_get_settings( 'supreme_global_layout' );
		$woocommerce_layout = supreme_get_settings( 'supreme_woocommerce_layout' );
		$layout = theme_layouts_get_layout();
		if ( !is_singular() && $global_layout !== 'layout_default' && function_exists( "supreme_{$global_layout}" ) ) {
			add_filter( 'get_theme_layout', 'supreme_' . $global_layout );
		} // end global layout control
		
		if ( is_singular() && $layout == 'layout-default' && $global_layout !== 'layout_default' && function_exists( "supreme_{$global_layout}" ) ) {
			add_filter( 'get_theme_layout', 'supreme_' . $global_layout );
		} // end singular layout control relative to global layout control
		
		if ( function_exists ( 'bbp_loaded' ) ) {
			if ( is_bbpress() && !is_singular() && $bbpress_layout !== 'layout_default' && function_exists( "supreme_{$bbpress_layout}" ) ) {
				add_filter( 'get_theme_layout', 'supreme_' . $bbpress_layout );
			}
			elseif ( is_bbpress() && is_singular() && $layout == 'layout-default' && $bbpress_layout !== 'layout_default' && function_exists( "supreme_{$bbpress_layout}" ) ) {
				add_filter( 'get_theme_layout', 'supreme_' . $bbpress_layout );
			}
		} // end bbpress layout control
		
		remove_post_type_support('admanager','theme-layouts');
		if ( function_exists ( 'is_woocommerce' ) ) {
			if ( is_woocommerce() && !is_singular() && $woocommerce_layout !== 'layout_default' && function_exists( "supreme_{$woocommerce_layout}" ) ) {
				add_filter( 'get_theme_layout', 'supreme_' . $woocommerce_layout );
			}
			elseif ( is_woocommerce() && is_singular() && $layout == 'layout-default' && $woocommerce_layout !== 'layout_default' && function_exists( "supreme_{$woocommerce_layout}" ) ) {
				add_filter( 'get_theme_layout', 'supreme_' . $woocommerce_layout );
			}
		} // end woocommerce layout control
	}
}
/* Paginaton start BOF
   Function that performs a Boxed Style Numbered Pagination (also called Page Navigation).
   Function is largely based on Version 2.4 of the WP-PageNavi plugin */
function directory_pagenavi_plugin($before = '', $after = '') {
    global $wpdb, $wp_query,$paged;
	
    $pagenavi_options = array();
   // $pagenavi_options['pages_text'] = ('Page %CURRENT_PAGE% of %TOTAL_PAGES%:');
    $pagenavi_options['pages_text'] = '';
    $pagenavi_options['current_text'] = '%PAGE_NUMBER%';
    $pagenavi_options['page_text'] = '%PAGE_NUMBER%';
    $pagenavi_options['first_text'] = __('First Page',THEME_DOMAIN);
    $pagenavi_options['last_text'] = __('Last Page',THEME_DOMAIN);
    $pagenavi_options['next_text'] = '<strong class="next page-numbers">'.__('Next',THEME_DOMAIN).'<span class="meta-nav">&rarr;</span></strong>';
    $pagenavi_options['prev_text'] = '<strong class="prev page-numbers"><span class="meta-nav">&larr;</span> '.__('Previous',THEME_DOMAIN).'</strong>';
    $pagenavi_options['dotright_text'] = '...';
    $pagenavi_options['dotleft_text'] = '...';
    $pagenavi_options['num_pages'] = 5; //continuous block of page numbers
    $pagenavi_options['always_show'] = 0;
    $pagenavi_options['num_larger_page_numbers'] = 0;
    $pagenavi_options['larger_page_numbers_multiple'] = 5;
 
    if (!is_single()) {
        $request = $wp_query->request;
        $posts_per_page = intval(get_query_var('posts_per_page'));
        $paged = intval(get_query_var('paged'));
        $numposts = $wp_query->found_posts;
        $max_page = $wp_query->max_num_pages;
 
        if(empty($paged) || $paged == 0) {
            $paged = 1;
        }
 
        $pages_to_show = intval($pagenavi_options['num_pages']);
        $larger_page_to_show = intval($pagenavi_options['num_larger_page_numbers']);
        $larger_page_multiple = intval($pagenavi_options['larger_page_numbers_multiple']);
        $pages_to_show_minus_1 = $pages_to_show - 1;
        $half_page_start = floor($pages_to_show_minus_1/2);
        $half_page_end = ceil($pages_to_show_minus_1/2);
        $start_page = $paged - $half_page_start;
 
        if($start_page <= 0) {
            $start_page = 1;
        }
 
        $end_page = $paged + $half_page_end;
        if(($end_page - $start_page) != $pages_to_show_minus_1) {
            $end_page = $start_page + $pages_to_show_minus_1;
        }
        if($end_page > $max_page) {
            $start_page = $max_page - $pages_to_show_minus_1;
            $end_page = $max_page;
        }
        if($start_page <= 0) {
            $start_page = 1;
        }
 
        $larger_per_page = $larger_page_to_show*$larger_page_multiple;
        //templ_round_num() custom function - Rounds To The Nearest Value.
        $larger_start_page_start = (templ_round_num($start_page, 10) + $larger_page_multiple) - $larger_per_page;
        $larger_start_page_end = templ_round_num($start_page, 10) + $larger_page_multiple;
        $larger_end_page_start = templ_round_num($end_page, 10) + $larger_page_multiple;
        $larger_end_page_end = templ_round_num($end_page, 10) + ($larger_per_page);
 
        if($larger_start_page_end - $larger_page_multiple == $start_page) {
            $larger_start_page_start = $larger_start_page_start - $larger_page_multiple;
            $larger_start_page_end = $larger_start_page_end - $larger_page_multiple;
        }
        if($larger_start_page_start <= 0) {
            $larger_start_page_start = $larger_page_multiple;
        }
        if($larger_start_page_end > $max_page) {
            $larger_start_page_end = $max_page;
        }
        if($larger_end_page_end > $max_page) {
            $larger_end_page_end = $max_page;
        }
        if($max_page > 1 || intval($pagenavi_options['always_show']) == 1) {
            $pages_text = str_replace("%CURRENT_PAGE%", number_format_i18n($paged), @$pagenavi_options['pages_text']);
            $pages_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pages_text);
			previous_posts_link($pagenavi_options['prev_text']);
       
            if ($start_page >= 2 && $pages_to_show < $max_page) {
                $first_page_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pagenavi_options['first_text']);
                echo '<a href="'.esc_url(get_pagenum_link()).'" class="first page-numbers" title="'.$first_page_text.'">'.$first_page_text.'</a>';
                if(!empty($pagenavi_options['dotleft_text'])) {
                    echo '<span class="expand page-numbers">'.$pagenavi_options['dotleft_text'].'</span>';
                }
            }
 
            if($larger_page_to_show > 0 && $larger_start_page_start > 0 && $larger_start_page_end <= $max_page) {
                for($i = $larger_start_page_start; $i < $larger_start_page_end; $i+=$larger_page_multiple) {
                    $page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['page_text']);
                    echo '<a href="'.esc_url(get_pagenum_link($i)).'" class="page-numbers" title="'.$page_text.'">'.$page_text.'</a>';
                }
            }
 
            for($i = $start_page; $i  <= $end_page; $i++) {
                if($i == $paged) {
                    $current_page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['current_text']);
                    echo '<a  class="current page-numbers">'.$current_page_text.'</a>';
                } else {
                    $page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['page_text']);
                    echo '<a href="'.esc_url(get_pagenum_link($i)).'" class="page-numbers" title="'.$page_text.'"><strong>'.$page_text.'</strong></a>';
                }
            }
 
            if ($end_page < $max_page) {
                if(!empty($pagenavi_options['dotright_text'])) {
                    echo '<span class="expand page-numbers">'.$pagenavi_options['dotright_text'].'</span>';
                }
                $last_page_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pagenavi_options['last_text']);
                echo '<a class="page-numbers" href="'.esc_url(get_pagenum_link($max_page)).'" title="'.$last_page_text.'">'.$last_page_text.'</a>';
            }
           
            if($larger_page_to_show > 0 && $larger_end_page_start < $max_page) {
                for($i = $larger_end_page_start; $i <= $larger_end_page_end; $i+=$larger_page_multiple) {
                    $page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['page_text']);
                    echo '<a href="'.esc_url(get_pagenum_link($i)).'" class="page-numbers" title="'.$page_text.'">'.$page_text.'</a>';
                }
            }
            echo $after;
			 next_posts_link($pagenavi_options['next_text'], $max_page);
        }
    }
}


/* search for filter for 404 page*/

add_filter( 'get_search_form', 'search_form_for_404_display' );
function search_form_for_404_display($searchform){
	$searchform ='<div class="404_search">
			<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">
			   <input type="text" value="" name="s" id="search_near" class="searchpost" PLACEHOLDER="'.__('Search',THEME_DOMAIN).'" />
               <input type="text" name="location" id="location" class="location" PLACEHOLDER="'.__('Address',THEME_DOMAIN).'" value="" />
			   <input type="hidden" name="post_type" value="listing" />
			   <input type="hidden" name="nearby" value="search" />
               <input type="submit" alt="" class="sgo" value="'.__('Search',THEME_DOMAIN).'" />
          </form></div>';
	return $searchform;
}
/*
	Remove unnecessary woocommece js from listing and detail page
*/

add_action('wp_head','remove_woocommerce_script');
function remove_woocommerce_script(){
	if(is_plugin_active('woocommerce/woocommerce.php')){
		global $post;
		if(function_exists('is_woocommerce') && !is_woocommerce() && !is_checkout() && !is_cart()){
			wp_deregister_script( 'jquery-cookie' );
			wp_deregister_script( 'jquery-blockui' );
			wp_deregister_script( 'wc-cart-fragments' );
			wp_deregister_script( 'wc-add-to-cart' );
			wp_deregister_script( 'jquery-placeholder' );
		}
		
		/* Remove the wp auto p tag add in woocommerce page */
		if($post->ID == get_option('woocommerce_cart_page_id') || $post->ID == get_option('woocommerce_checkout_page_id') || $post->ID == get_option('woocommerce_pay_page_id') || $post->ID == get_option('woocommerce_thanks_page_id') || $post->ID == get_option('woocommerce_myaccount_page_id') || $post->ID == get_option('woocommerce_edit_address_page_id') || $post->ID == get_option('woocommerce_view_order_page_id') || $post->ID == get_option('woocommerce_change_password_page_id') || $post->ID == get_option('woocommerce_logout_page_id') || $post->ID == get_option('woocommerce_lost_password_page_id') )
		{
			remove_filter( 'the_content', 'wpautop',12 );
		}
	}
}

/*
	add google map marker option in add/edit taxonomy page on backend.
 */
add_action('tevolution_add_custom_post_field','directory_add_custom_post_field');
function directory_add_custom_post_field($edit_post){
	global $wpdb;
	$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');	
	$taxonomy=@$edit_post[@$_REQUEST['post-type']]['slugs'][0];
	
	/* directory plug in and event plug in taxonomy available then return marker custom field */
	
	?>
     <tr>
     	<th valign="top"><label for="marker" class="form-textarea-label"><?php echo __('Category map markers',ADMINDOMAIN) ;?></label></th>
          <td>
		  <div class="input-switch">
          	<input type="checkbox" id="taxonomy_marker" name="taxonomy_marker" value="enable" <?php if($taxonomy!='' && $tevolution_taxonomy_marker[$taxonomy]=='enable'){echo 'checked';}?> />&nbsp;<label for="taxonomy_marker"><?php echo __('Enable',ADMINDOMAIN);?></label>
          </div>
		  <p class="description"><?php echo __('Enabling this will allow you to assign a custom icon(marker) for categories within this post type. The marker can be set while creating the category.',ADMINDOMAIN);?></p>
		  </td>
     </tr>
     <?php	
	if(isset($_POST['submit-taxonomy']) && $_POST['submit-taxonomy'] !='' && isset($_POST['taxonomy_marker']) && $_POST['taxonomy_marker']!='')	{
		
		$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');
		if($tevolution_taxonomy_marker){
			$taxonomy_marker=array($_POST['taxonomy_slug']=>$_POST['taxonomy_marker']);
			$tevolution_taxonomy_marker=array_merge($tevolution_taxonomy_marker,$taxonomy_marker);
			update_option('tevolution_taxonomy_marker',$tevolution_taxonomy_marker);
		}else{
			$tevolution_taxonomy_marker=array($_POST['taxonomy_slug']=>$_POST['taxonomy_marker']);
			update_option('tevolution_taxonomy_marker',$tevolution_taxonomy_marker);
		}
		
	}else{
		$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');
		unset($tevolution_taxonomy_marker[@$_POST['taxonomy_slug']]);
		update_option('tevolution_taxonomy_marker',$tevolution_taxonomy_marker);
	}
}

/*
 * Function Name: tevolution_add_marker_fields 
 * Return: display marker upload field in category page on backend
 */
add_action('admin_init','tevolution_add_marker_fields');
function tevolution_add_marker_fields(){
	$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');
	if(!empty($tevolution_taxonomy_marker)){
		foreach($tevolution_taxonomy_marker as $key=>$value){
			if($key=='ecategory' || $key=='listingcategory'){
				continue;
			}
			add_action('edited_'.$key,'marker_custom_fields_AlterFields');
			add_action('created_'.$key,'marker_custom_fields_AlterFields');
			add_filter('manage_'.$key.'_custom_column', 'manage_marker_category_columns', 10, 3);
			add_filter('manage_edit-'.$key.'_columns', 'marker_category_columns');
			
			if(isset($_GET['taxonomy']) && ($_GET['taxonomy']== $key)) 
			{
				$taxnow=$_GET['taxonomy'];
				add_action($taxnow.'_edit_form_fields','marker_custom_fields_EditFields',11);
				add_action($taxnow.'_add_form_fields','marker_custom_fields_AddFieldsAction',11);		
			}
		}
	}
}
/*
Name:directory_theme_breadcrumb
Desc: return breadcrumb
*/
function directory_theme_breadcrumb(){
	global $wpdb,$post;
	if ( current_theme_supports( 'breadcrumb-trail' ) && supreme_get_settings('supreme_show_breadcrumb')) breadcrumb_trail( array( 'separator' => '&raquo;' ) );
}

function marker_custom_fields_EditFields($tag)
{
	marker_custom_fields_AddFields($tag,'edit');	
}
function marker_custom_fields_AddFieldsAction($tag)
{
	marker_custom_fields_AddFields($tag,'add');
}

/*
 * Function Name: marker_custom_fields_AddFields
 * display custom field in event and listing category page
 */
function marker_custom_fields_AddFields($tag,$screen)
{	
	$tax = @$tag->taxonomy;
	?>
     	<div class="form-field-category">
		<tr class="form-field form-field-category">
			<th scope="row" valign="top"><label for="cat_icon"><?php echo __("Map Marker", ADMINDOMAIN); ?></label></th>
			<td> 
                    <input id="cat_icon" type="text" size="60" name="cat_icon" value="<?php echo (@$tag->term_icon)? @$tag->term_icon:''; ?>"/>	
                    <?php echo __('Or',ADMINDOMAIN);?>
                    <a data-id="cat_icon" id="Map Marker" type="submit" class="upload_file_button button"><?php  echo __('Browse',ADMINDOMAIN);?></a>
                    <p class="description"><?php echo __('It will appear on the homepage Google map for listings placed in this category. ',ADMINDOMAIN);?></p>    
			</td>
		</tr>
		</div>
	<?php
}

/*
 * Function Name: marker_custom_fields_AlterFields
 * add/ edit listing and event custom taxonomy custom field 
 */
function marker_custom_fields_AlterFields($termId)
{
	global $wpdb;
	$term_table=$wpdb->prefix."terms";		
	$cat_icon=$_POST['cat_icon'];		
	//update the service price value in terms table field	
	if(isset($_POST['cat_icon'])){
		$sql="update $term_table set term_icon='".$cat_icon."' where term_id=".$termId;
		$wpdb->query($sql);
	}
	
}

/*
 * Function Name: marker_category_columns
 * manage columns for event and listing custom taxonomy
 */
function marker_category_columns($columns)
{
	$columns['icon'] = __('Map Marker',ADMINDOMAIN);
	return $columns;	
}

/*
 * Function Name: manage_marker_category_columns
 * display listing and event custom taxonomy custom field display in category columns
 */
function manage_marker_category_columns($out, $column_name, $term_id){
	global $wpdb;
	$term_table=$wpdb->prefix."terms";		
	$sql="select * from $term_table where term_id=".$term_id;
	$term=$wpdb->get_results($sql);	
	
	switch ($column_name) {
		case 'icon':					
				 $out= ($term[0]->term_icon)?'<img src="'.$term[0]->term_icon.'" >':'<img src="'.apply_filters('tmpl_default_map_icon',TEVOLUTION_DIRECTORY_URL.'images/pin.png').'" >';
			break; 
		default:
			break;
	}
	return $out;	
}

/* Return page layouts for pages, preview page and success page( comes with tevolution plugin )*/
add_filter('get_theme_layout','directory_custom_page_layout');

function directory_custom_page_layout($global_layout){

	if(isset($_REQUEST['page']) && ($_REQUEST['page']=='preview' || $_REQUEST['page']=='success')){		
		$global_layout=supreme_plugin_layouts( $global_layout );
	}
	return $global_layout;
}


/*
 * Function Name: directory_site_get_avatar
 * Return: profile_photo user custom filed not blank then display user custom photo.
 */
add_filter('get_avatar', 'directory_site_get_avatar', 10, 5);
function directory_site_get_avatar($avatar, $id_or_email, $size, $default, $alt){	
	global $pagenow;
	if($pagenow=='edit-comments.php'){
		return $avatar;	
	}
	if(is_object($id_or_email) && @$id_or_email->comment_author_email){
		$users = get_user_by( 'email',@$id_or_email->comment_author_email );
		if(@get_user_meta($users->ID,'profile_photo',true) !='' && @is_single()){		
			$imgpath = get_user_meta($users->ID,'profile_photo',true);		
			$avatar = "<img class='avatar avatar-".$size." photo' src='".$imgpath."' alt='".$alt."' height='".$size."' width='".$size."' />";
		}
	}else{
		if(get_user_meta($id_or_email,'profile_photo',true) !=''){		
			$imgpath = get_user_meta($id_or_email,'profile_photo',true);		
			$avatar = "<img class='avatar avatar-".$size." photo' src='".$imgpath."' alt='".$alt."' height='".$size."' width='".$size."' />";
		}
	}
	
	return $avatar;
}

/* To find and replace the Old supreme* slug with new different front end/back end slugs */

add_action('admin_init','directory_theme_localization_slugs');
function directory_theme_localization_slugs(){
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		if(!get_option('directory_theme_localization'))
		{
			$found='';
			$files_to_search =  TEMPLATE_DIR.'functions.php';
			$old = 'define\(\ \'THEME_DOMAIN';
			$old1 = 'define\(\ \'ADMINDOMAIN';
			$new ="\tdefine( 'THEME_DOMAIN', 'supreme'); ";
			$new1 ="\tdefine( 'ADMINDOMAIN', 'supreme' );";
			if(function_exists('tevolution_replace_line'))
			{
				tevolution_replace_line($old,$new,$files_to_search);
				tevolution_replace_line($old1,$new1,$files_to_search);
			}
			update_option('directory_theme_localization','1');
		}	
		if(get_option('directory_theme_localization') == 1)
		{
			add_action('admin_notices','directory_theme_text_domain_message');
		}
	
		if(isset($_REQUEST['ch_domain']) && $_REQUEST['ch_domain'] =='directorythemedomain'){
			$found='';
				$files_to_search =  TEMPLATE_DIR.'functions.php';
				$old = 'define\(\ \'THEME_DOMAIN';
				$old1 = 'define\(\ \'ADMINDOMAIN';
				$new ="\tdefine( 'THEME_DOMAIN', 'templatic'); ";
				$new1 ="\tdefine( 'ADMINDOMAIN', 'templatic-admin' );";
				if(function_exists('tevolution_replace_line'))
				{
					tevolution_replace_line($old,$new,$files_to_search);
					tevolution_replace_line($old1,$new1,$files_to_search);
				}
				update_option('directory_theme_localization',2);
		}
	}
}

/* 
Name: directory_theme_text_domain_message
Desc: Change the localization slug if use wpml plugin
*/

function directory_theme_text_domain_message(){
	$url = admin_url('index.php?ch_domain=domain');
	$message = "<div id=\"error\" class=\"updated\">\n";
	$message .= '<p><a href="'.admin_url('index.php?ch_domain=directorythemedomain').'"> Click here </a> to continue with new localization text domain</p>';
	$message .= "</div>";
	echo $message;
}

/**
 * Display the language string for the number of comments the current post has.
 */
function templatic_comments_number( $zero = false, $one = false, $more = false, $deprecated = '' ) {

	$number = get_comments_number();

	if ( $number > 1 )
		$output =  ( false === $more ) ? number_format_i18n($number)." ".__('Comments',THEME_DOMAIN) : number_format_i18n($number)." ".$more;
	elseif ( $number == 0 )
		$output = ( false === $zero ) ? __('No Comments',THEME_DOMAIN) : $zero;
	else // must be one
		$output = ( false === $one ) ? __('1 Comment',THEME_DOMAIN) : $one;

	/**
	 * Filter the comments count for display.
	 *
	 * @since 1.5.0
	 *
	 * @param string $output A translatable string formatted based on whether the count is equal to 0, 1, or 1+. @see _n()
	 * @param int    $number The number of post comments.
	 */
	echo apply_filters( 'comments_number', $output, $number );
}

if(!function_exists('templatic_get_theme_data')){
	/*
	Name: templatic_get_theme_data
	Desc: return the theme data
	*/
	function templatic_get_theme_data( $theme_file ) {
		$theme = new WP_Theme( basename( dirname( $theme_file ) ), dirname( dirname( $theme_file ) ) );

		$theme_data = array(
			'Name' => $theme->get('Name'),
			'URI' => $theme->display('ThemeURI', true, false),
			'Description' => $theme->display('Description', true, false),
			'Author' => $theme->display('Author', true, false),
			'AuthorURI' => $theme->display('AuthorURI', true, false),
			'Version' => $theme->get('Version'),
			'Template' => $theme->get('Template'),
			'Status' => $theme->get('Status'),
			'Tags' => $theme->get('Tags'),
			'Title' => $theme->get('Name'),
			'AuthorName' => $theme->get('Author'),
		);

		foreach ( apply_filters( 'extra_theme_headers', array() ) as $extra_header ) {
			if ( ! isset( $theme_data[ $extra_header ] ) )
				$theme_data[ $extra_header ] = $theme->get( $extra_header );
		}

		return $theme_data;
	}
}
/*
	default markers available for listings and taxonomies
*/
add_action("admin_init","directory_map_marker_enable");
function directory_map_marker_enable(){
	if(!get_option('existing_user')){
		$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');
		if(empty($tevolution_taxonomy_marker)){
			update_option('tevolution_taxonomy_marker',array('listingcategory'=>'enable','ecategory'=>'enable'));
		}else{
			update_option('tevolution_taxonomy_marker',array_merge($tevolution_taxonomy_marker,array('listingcategory'=>'enable','ecategory'=>'enable')));
		}
		update_option('existing_user',1);
	}
}


/**** ---- v 1.1.2 theme-functions.php file end ---***/

//Theme Setting Options file
if(file_exists(get_template_directory().'/library/functions/theme_options.php')){
	require_once(get_template_directory().'/library/functions/theme_options.php'); // framework theme setting options file
}
/* @This file contain the framework custom functions */
if ( ! function_exists( 'supreme_entry_meta' ) ) :
/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own supreme_entry_meta() to override in a child theme.
 *
 * @since Twenty Twelve 1.0
 */
 add_action('templ_the_taxonomies','supreme_entry_meta');
 function supreme_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$utility_text='';
	$categories_list = get_the_category_list(  ', ' );
	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', ', ' );
	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);
	
	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		echo '<div class="post_info_meta">'; 
		echo "<span>";
		_e( 'Posted in', THEME_DOMAIN );
		echo "</span> "; 
		echo " ".$categories_list." "; 
		echo "<span>";
		_e( 'and tagged', THEME_DOMAIN );
		echo "</span> "; 
		echo " ".$tag_list." "; 
		do_action('tmpl_archive_post_meta');
		echo '</div>';
		
	} elseif ( $categories_list ) {
		echo '<div class="post_info_meta">'; 
		echo "<span>";
		_e( 'Posted in', THEME_DOMAIN );
		echo "</span> "; 
		echo " ".$categories_list." "; 
			do_action('tmpl_archive_post_meta');
		echo '</div>';	
	} 
	
}
endif;
/* Add extra support for post types. */
add_action( 'init', 'supreme_add_post_type_support' );
/* Add extra file headers for themes. */
add_filter( 'extra_theme_headers', 'supreme_extra_theme_headers' );
/**
 * This function is for adding extra support for features not default to the core post types.
 * Excerpts are added to the 'page' post type.  Comments and trackbacks are added for the
 * 'attachment' post type.  Technically, these are already used for attachments in core, but 
 * they're not registered.
 */
function supreme_add_post_type_support() {
	/* Add support for excerpts to the 'page' post type. */
	add_post_type_support( 'page', array( 'excerpt' ) );
	/* Add support for track backs to the 'attachment' post type. */
	add_post_type_support( 'attachment', array( 'trackbacks' ) );
}
/**
 * Creates custom theme headers.  This is the information shown in the header block of a theme's 'style.css' 
 * file.  Themes are not required to use this information, but the framework does make use of the data for 
 * displaying additional information to the theme user.
 *
 */
function supreme_extra_theme_headers( $headers ) {
	/* Add support for 'Template Version'. This is for use in child themes to note the version of the parent theme. */
	if ( !in_array( 'Template Version', $headers ) )
		$headers[] = 'Template Version';
	/* Add support for 'License'.  Proposed in the guidelines for the WordPress.org theme review. */
	if ( !in_array( 'License', $headers ) )
		$headers[] = 'License';
	/* Add support for 'License URI'. Proposed in the guidelines for the WordPress.org theme review. */
	if ( !in_array( 'License URI', $headers ) )
		$headers[] = 'License URI';
	/* Add support for 'Support URI'.  This should be a link to the theme's support forums. */
	if ( !in_array( 'Support URI', $headers ) )
		$headers[] = 'Support URI';
	/* Add support for 'Documentation URI'.  This should be a link to the theme's documentation. */
	if ( !in_array( 'Documentation URI', $headers ) )
		$headers[] = 'Documentation URI';
	/* Return the array of custom theme headers. */
	return $headers;
}
/**
 * Looks for a template based on the supreme_get_context() function.  If the $template parameter
 * is a directory, it will look for files within that directory.  Otherwise, $template becomes the 
 * template name prefix.  The function looks for templates based on the context of the current page
 * being viewed by the user.
 *
 */
function get_atomic_template( $template ) {
	$templates = array();
	$theme_dir = trailingslashit( THEME_DIR ) . $template;
	$child_dir = trailingslashit( CHILD_THEME_DIR ) . $template;
	if ( is_dir( $child_dir ) || is_dir( $theme_dir ) ) {
		$dir = true;
		$templates[] = "{$template}/index.php";
	}
	else {
		$dir = false;
		$templates[] = "{$template}.php";
	}
	foreach ( supreme_get_context() as $context )
		$templates[] = ( ( $dir ) ? "{$template}/{$context}.php" : "{$template}-{$context}.php" );
	return locate_template( array_reverse( $templates ), true );
}
/**
 * Generates the relevant template info.  Adds template meta with theme version.  Uses the theme 
 * name and version from style.css.  In 0.6, added the supreme_meta_template 
 * filter hook.
 *
 */
function supreme_meta_template() {
	$theme = wp_get_theme( get_template(), get_theme_root( get_template_directory() ) );
	
	$template = '<meta name="template" content="' . esc_attr( $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ) . '" />' . "\n";
	
	echo apply_atomic( 'meta_template', $template );
}
/**
 * Dynamic element to wrap the site title in.  If it is the front page, wrap it in an <h1> element.  One other 
 * pages, wrap it in a <div> element. 
 *
 */
function supreme_site_title() {
	/* If viewing the front page of the site, use an <h1> tag.  Otherwise, use a <div> tag. */
	$tag = 'h1';
	/* Get the site title.  If it's not empty, wrap it with the appropriate HTML. */
	if ( $title = get_bloginfo( 'name' ) ){
		if(function_exists('icl_register_string')){
			icl_register_string(THEME_DOMAIN,$title,$title);
			$title1 = icl_t(THEME_DOMAIN,$title,$title);
		}else{
			$title1 = $title;
		}
		$title = sprintf( '<%1$s id="site-title"><a href="%2$s" title="%3$s" rel="home"><span>%4$s</span></a></%1$s>', tag_escape( $tag ), home_url(), esc_attr( $title1 ), $title1 );
	}	
	/* Display the site title and apply filters for developers to overwrite. */
	echo apply_atomic( 'site_title', $title );
}
/**
 * Dynamic element to wrap the site description in.  If it is the front page, wrap it in an <h2> element.  
 * On other pages, wrap it in a <div> element.
 */
function supreme_site_description() {
	/* If viewing the front page of the site, use an <h2> tag.  Otherwise, use a <div> tag. */
	$tag = ( is_front_page() ) ? 'h2' : 'div';
	$tmpdata = get_option(supreme_prefix().'_theme_settings');
	/* Get the site description.  If it's not empty, wrap it with the appropriate HTML. */
	if ( $desc = get_bloginfo( 'description' ) ){
		if(function_exists('icl_register_string')){
			icl_register_string(THEME_DOMAIN,$desc,$desc);
			$desc1 = icl_t(THEME_DOMAIN,$desc,$desc);
		}else{
			$desc1 = $desc;
		}
		$desc = sprintf( '<%1$s id="site-description"><span>%2$s</span></%1$s>', tag_escape( $tag ), $desc1 );
	}	
		/* Display the site description and apply filters for developers to overwrite. */
		echo apply_atomic( 'site_description', $desc );
}
/**
 * Checks if a post of any post type has a custom template.  This is the equivalent of WordPress' 
 * is_page_template() function with the exception that it works for all post types.
 *
 */
function supreme_has_post_template( $template = '' ) {
	/* Assume we're viewing a singular post. */
	if ( is_singular() ) {
		/* Get the queried object. */
		$post = get_queried_object();
		/* Get the post template, which is saved as metadata. */
		$post_template = get_post_meta( get_queried_object_id(), "_wp_{$post->post_type}_template", true );
		/* If a specific template was input, check that the post template matches. */
		if ( !empty( $template) && ( $template == $post_template ) )
			return true;
		/* If no specific template was input, check if the post has a template. */
		elseif ( empty( $template) && !empty( $post_template ) )
			return true;
	}
	/* Return false for everything else. */
	return false;
}
/**
 * Defines the theme prefix. This allows developers to infinitely change the theme. In theory,
 * one could use the Hybrid core to create their own theme or filter 'hybrid_prefix' with a 
 * plugin to make it easier to use hooks across multiple themes without having to figure out
 * each theme's hooks (assuming other themes used the same system).
 *
 * @since 0.7.0
 * @access public
 * @uses get_template() Defines the theme prefix based on the theme directory.
 * @global object $supreme The global Hybrid object.
 * @return string $supreme->prefix The prefix of the theme.
 */
function supreme_prefix() {
	global $supreme;
	$supreme->prefix = sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
	return $supreme->prefix;
}
/**
 * Adds contextual filter hooks to the theme.  This allows users to easily filter context-based content 
 * without having to know how to use WordPress conditional tags.  The theme handles the logic.
 *
 * An example of a basic hook would be 'hybrid_entry_meta'.  The apply_atomic() function extends 
 * that to give extra hooks such as 'hybrid_singular_entry_meta', 'hybrid_singular-post_entry_meta', 
 * and 'hybrid_singular-post-ID_entry_meta'.
 *
 * @since 0.7.0
 * @access public
 * @uses supreme_prefix() Gets the theme prefix.
 * @uses supreme_get_context() Gets the context of the current page.
 * @param string $tag Usually the location of the hook but defines what the base hook is.
 * @param mixed $value The value on which the filters hooked to $tag are applied on.
 * @param mixed $var,... Additional variables passed to the functions hooked to $tag.
 * @return mixed $value The value after it has been filtered.
 */
function apply_atomic( $tag = '', $value = '' ) {
	if ( empty( $tag ) )
		return false;
	/* Get theme prefix. */
	$pre = supreme_prefix();
	/* Get the args passed into the function and remove $tag. */
	$args = func_get_args();
	array_splice( $args, 0, 1 );
	/* Apply filters on the basic hook. */
	$value = $args[0] = apply_filters_ref_array( "{$pre}_{$tag}", $args );
	/* Loop through context array and apply filters on a contextual scale. */
	foreach ( supreme_get_context() as $context )
		$value = $args[0] = apply_filters_ref_array( "{$pre}_{$context}_{$tag}", $args );
	/* Return the final value once all filters have been applied. */
	return $value;
}
/**
 * Wraps the output of apply_atomic() in a call to do_shortcode(). This allows developers to use 
 * context-aware functionality alongside shortcodes. Rather than adding a lot of code to the 
 * function itself, developers can create individual functions to handle shortcodes.
 *
 * @since 0.7.0
 * @access public
 * @param string $tag Usually the location of the hook but defines what the base hook is.
 * @param mixed $value The value to be filtered.
 * @return mixed $value The value after it has been filtered.
 */
function apply_atomic_shortcode( $tag = '', $value = '' ) {
	return do_shortcode( apply_atomic( $tag, $value ) );
}
/**
 * The theme can save multiple things in a transient to help speed up page load times. We're
 * setting a default of 12 hours or 43,200 seconds (60 * 60 * 12).
 *
 * @since 0.8.0
 * @access public
 * @return int Transient expiration time in seconds.
 */
function hybrid_get_transient_expiration() {
	return apply_filters( supreme_prefix() . '_transient_expiration', 43200 );
}
/**
 * Function for setting the content width of a theme.  This does not check if a content width has been set; it 
 * simply overwrites whatever the content width is.
 *
 * @since 1.2.0
 * @access public
 * @global int $content_width The width for the theme's content area.
 * @param int $width Numeric value of the width to set.
 */
function supreme_set_content_width( $width = '' ) {
	global $content_width;
	$content_width = absint( $width );
}
/**
 * Loads the Hybrid theme settings once and allows the input of the specific field the user would 
 * like to show.  Hybrid theme settings are added with 'autoload' set to 'yes', so the settings are 
 * only loaded once on each page load.
 */
function supreme_get_settings( $option = '' ) {
	//return if option is not passed
	if ( !$option )
		return false;
	
	//Check for theme prefix
	if(function_exists('supreme_prefix')){
		$pref = supreme_prefix();
	}else{
		$pref = get_template();
	}
	//Getting theme options array
	$theme_settings = get_option($pref.'_theme_settings');	
	//return value of requested theme option
	
	if(isset($theme_settings[$option])):
		return $theme_settings[$option];
	else:
		return '';
	endif;
}
/**
 * Loads the Hybrid theme settings once and allows the input of the specific field the user would 
 * like to show.  Hybrid theme settings are added with 'autoload' set to 'yes', so the settings are 
 * only loaded once on each page load.
 */
if(!function_exists('theme_get_settings')){
	function theme_get_settings($option=''){
		global $supreme;
		/* If no specific option was requested, return false. */
		if(!$option){return false;}
		/* Call get_option() to get an array of theme settings. */
		$theme_options = get_option(supreme_prefix().'_theme_settings');
		/* If the settings isn't an array or the specific option isn't in the array, return false. */
		if( !is_array( $theme_options ) || empty($theme_options)){return false;}
		/* If the specific option is an array, return it. */
		if ( is_array( $theme_options ) && isset($theme_options[$option]) ){
			return wp_kses_stripslashes($theme_options[$option]);
		/* Strip slashes from the setting and return. */
		}else{
			return false;
		}	
	}
}
/**
Name :supreme_default_theme_settings
Description : get supreme default theme settings
 */
function supreme_default_theme_settings() {
	/* Set up some default variables. */
	$settings = array();
	$prefix = supreme_prefix();
	/* Get theme-supported meta boxes for the settings page. */
	$supports = get_theme_support( 'supreme-core-theme-settings' );
	/* If the current theme supports the footer meta box and shortcodes, add default footer settings. */
	if ( is_array( $supports[0] ) && in_array( 'footer', $supports[0] ) && current_theme_supports( 'supreme-core-shortcodes' ) ) {
		/* If there is a child theme active, add the [child-link] shortcode to the $footer_insert. */
		if ( is_child_theme() )
			$settings['footer_insert'] = '<p class="copyright">' . __( 'Copyright &#169; [the-year] [site-link].', THEME_DOMAIN ) . '</p>' . "\n\n" . '<p class="credit">' . __( 'Powered by [wp-link], [theme-link], and [child-link].', THEME_DOMAIN ) . '</p>';
		/* If no child theme is active, leave out the [child-link] shortcode. */
		else
			$settings['footer_insert'] = '<p class="copyright">' . __( 'Copyright &#169; [the-year] [site-link].', THEME_DOMAIN ) . '</p>' . "\n\n" . '<p class="credit">' . __( 'Powered by [wp-link] and [theme-link].', THEME_DOMAIN ) . '</p>';
	}
	/* Return the $settings array and provide a hook for overwriting the default settings. */
	return apply_filters( "{$prefix}_default_theme_settings", $settings );
}
/**
Name : supreme_get_post_categories
Args : label
Description : Return the categories of post
**/
function supreme_get_categories($label,$taxonomy,$class,$tags_label,$tag_taxonomy){
	$label = $label;
	$tags_label = $tags_label;
	
	if(function_exists('icl_register_string')){
		icl_register_string(THEME_DOMAIN,$label,$label);
	}
	
	if(function_exists('icl_t')){
		$label1 = icl_t(THEME_DOMAIN,$label,$label);
	}else{
		$label1 = $label; 
	}
	if(function_exists('icl_register_string')){
		icl_register_string(THEME_DOMAIN,$tags_label,$tags_label);
	}
	
	if(function_exists('icl_t')){
		$tags_label1 = icl_t(THEME_DOMAIN,$tags_label,$tags_label);
	}else{
		$tags_label1 = $tags_label; 
	}
	echo apply_atomic_shortcode( 'entry_meta', '<div class="entry-meta '.$class.'">' .' '. sprintf(__( '[entry-terms taxonomy="%s" before="%s"] [entry-terms taxonomy="%s" before="%s"]', THEME_DOMAIN ),$taxonomy,$label1,$tag_taxonomy,$tags_label1) . '</div>' );
}

/* Functions file for loading scripts and stylesheets.  This file also handles the output of attachment files  */
add_action( 'wp_enqueue_scripts', 'supreme_register_scripts', 1 ); /* Register Supreme Core scripts. */
add_action( 'wp_enqueue_scripts', 'supreme_enqueue_scripts' );/* Load Supreme Core scripts. */
add_filter( 'stylesheet_uri', 'supreme_debug_stylesheet', 10, 2 ); /* Load the development stylsheet in script debug mode. */
/* Add all image sizes to the image editor to insert into post. */
add_filter( 'image_size_names_choose', 'hybrid_image_size_names_choose' );
/**
 * Registers JavaScript files for the framework.  This function merely registers scripts with WordPress using
 * the wp_register_script() function.   */
function supreme_register_scripts() {
	/* Supported JavaScript. */
	$supports = get_theme_support( 'supreme-core-javascript' );
}
/**
 * Tells WordPress to load the scripts needed for the framework using the wp_enqueue_script() function.
 */
function supreme_enqueue_scripts() {
	/* Supported JavaScript. */
	$supports = get_theme_support( 'supreme-core-javascript' );
	/* Load the comment reply script on singular posts with open comments if threaded comments are supported. */
	if ( is_singular() && get_option( 'thread_comments' ) && comments_open() )
		wp_enqueue_script( 'comment-reply' );
}
/**
 * Function for using a debug stylesheet when developing.  To develop with the debug stylesheet, 
 * SCRIPT_DEBUG must be set to 'true' in the 'wp-config.php' file.  This will check if a 'style.dev.css'
 * file is present within the theme folder and use it if it exists.  Else, it defaults to 'style.css'.
 */
function supreme_debug_stylesheet( $stylesheet_uri, $stylesheet_dir_uri ) {
	/* If SCRIPT_DEBUG is set to true and the theme supports 'dev-stylesheet'. */
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && current_theme_supports( 'dev-stylesheet' ) ) {
		/* Remove the stylesheet directory URI from the file name. */
		$stylesheet = str_replace( trailingslashit( $stylesheet_dir_uri ), '', $stylesheet_uri );
		/* Change the stylesheet name to 'style.dev.css'. */
		$stylesheet = str_replace( '.css', '.dev.css', $stylesheet );
		/* If the stylesheet exists in the stylesheet directory, set the stylesheet URI to the dev stylesheet. */
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $stylesheet ) )
			$stylesheet_uri = trailingslashit( $stylesheet_dir_uri ) . $stylesheet;
	}
	/* Return the theme stylesheet. */
	return $stylesheet_uri;
}
/**
 * Adds theme/plugin custom images sizes added with add_image_size() to the image uploader/editor.  This 
 * allows users to insert these images within their post content editor.
 *
 */
function hybrid_image_size_names_choose( $sizes ) {
	/* Get all intermediate image sizes. */
	$intermediate_sizes = get_intermediate_image_sizes();
	$add_sizes = array();
	/* Loop through each of the intermediate sizes, adding them to the $add_sizes array. */
	foreach ( $intermediate_sizes as $size )
		$add_sizes[$size] = $size;
	/* Merge the original array, keeping it intact, with the new array of image sizes. */
	$sizes = array_merge( $add_sizes, $sizes );
	/* Return the new sizes plus the old sizes back. */
	return $sizes;
}
/**
 * Loads the correct function for handling attachments.  Checks the attachment mime type to call 
 * correct function. Image attachments are not loaded with this function.  The functionality for them 
 * should be handled by the theme's attachment or image attachment file.
 *
 * Ideally, all attachments would be appropriately handled within their templates. However, this could 
 * lead to messy template files.

 */
function hybrid_attachment() {
	$file = wp_get_attachment_url();
	$mime = get_post_mime_type();
	$mime_type = explode( '/', $mime );
	/* Loop through each mime type. If a function exists for it, call it. Allow users to filter the display. */
	foreach ( $mime_type as $type ) {
		if ( function_exists( "hybrid_{$type}_attachment" ) )
			$attachment = call_user_func( "hybrid_{$type}_attachment", $mime, $file );
		$attachment = apply_atomic( "{$type}_attachment", $attachment );
	}
	echo apply_atomic( 'attachment', $attachment );
}
/**
 * Handles application attachments on their attachment pages.  Uses the <object> tag to embed media 
 * on those pages.
 */
function hybrid_application_attachment( $mime = '', $file = '' ) {
	$embed_defaults = wp_embed_defaults();
	$application = '<object class="text" type="' . esc_attr( $mime ) . '" data="' . esc_url( $file ) . '" width="' . esc_attr( $embed_defaults['width'] ) . '" height="' . esc_attr( $embed_defaults['height'] ) . '">';
	$application .= '<param name="src" value="' . esc_url( $file ) . '" />';
	$application .= '</object>';
	return $application;
}
/**
 * Handles text attachments on their attachment pages.  Uses the <object> element to embed media 
 * in the pages.
 *
 */
function hybrid_text_attachment( $mime = '', $file = '' ) {
	$embed_defaults = wp_embed_defaults();
	$text = '<object class="text" type="' . esc_attr( $mime ) . '" data="' . esc_url( $file ) . '" width="' . esc_attr( $embed_defaults['width'] ) . '" height="' . esc_attr( $embed_defaults['height'] ) . '">';
	$text .= '<param name="src" value="' . esc_url( $file ) . '" />';
	$text .= '</object>';
	return $text;
}
/**
 * Handles audio attachments on their attachment pages.  Puts audio/mpeg and audio/wma files into 
 * an <object> element.
 */
function hybrid_audio_attachment( $mime = '', $file = '' ) {
	$embed_defaults = wp_embed_defaults();
	$audio = '<object type="' . esc_attr( $mime ) . '" class="player audio" data="' . esc_url( $file ) . '" width="' . esc_attr( $embed_defaults['width'] ) . '" height="' . esc_attr( $embed_defaults['height'] ) . '">';
		$audio .= '<param name="src" value="' . esc_url( $file ) . '" />';
		$audio .= '<param name="autostart" value="false" />';
		$audio .= '<param name="controller" value="true" />';
	$audio .= '</object>';
	return $audio;
}
/**
 * Handles video attachments on attachment pages.  Add other video types to the <object> element.
 */
function hybrid_video_attachment( $mime = false, $file = false ) {
	$embed_defaults = wp_embed_defaults();
	if ( $mime == 'video/asf' )
		$mime = 'video/x-ms-wmv';
	$video = '<object type="' . esc_attr( $mime ) . '" class="player video" data="' . esc_url( $file ) . '" width="' . esc_attr( $embed_defaults['width'] ) . '" height="' . esc_attr( $embed_defaults['height'] ) . '">';
		$video .= '<param name="src" value="' . esc_url( $file ) . '" />';
		$video .= '<param name="autoplay" value="false" />';
		$video .= '<param name="allowfullscreen" value="true" />';
		$video .= '<param name="controller" value="true" />';
	$video .= '</object>';
	return $video;
}
/*
Name : loop_pagination
Description : return pagination
*/
function loop_pagination( $args = array() ) {
	global $wp_rewrite, $wp_query;
	/* If there's not more than one page, return nothing. */
	if ( 1 >= $wp_query->max_num_pages )
		return;
	/* Get the current page. */
	$current = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );
	/* Get the max number of pages. */
	$max_num_pages = intval( $wp_query->max_num_pages );
	/* Set up some default arguments for the paginate_links() function. */
	$defaults = array(
		'base'         => add_query_arg( 'paged', '%#%' ),
		'format'       => '',
		'total'        => $max_num_pages,
		'current'      => $current,
		'prev_next'    => true,
		//'prev_text'  => __( '&laquo; Previous' ), // This is the WordPress default.
		//'next_text'  => __( 'Next &raquo;' ), // This is the WordPress default.
		'show_all'     => false,
		'end_size'     => 1,
		'mid_size'     => 1,
		'add_fragment' => '',
		'type'         => 'plain',
		'before_page_number' =>'<strong>',
		'after_page_number' =>'</strong>',
		// Begin loop_pagination() arguments.
		'before'       => '<div class="pagination loop-pagination">',
		'after'        => '</div>',
		'echo'         => true,
	);
	/* Add the $base argument to the array if the user is using permalinks. */
	if ( $wp_rewrite->using_permalinks() && !is_search() )
		$defaults['base'] = user_trailingslashit( trailingslashit( get_pagenum_link() ) . 'page/%#%' );
	/* Allow developers to overwrite the arguments with a filter. */
	$args = apply_filters( 'loop_pagination_args', $args );
	/* Merge the arguments input with the defaults. */
	$args = wp_parse_args( $args, $defaults );
	/* Don't allow the user to set this to an array. */
	if ( 'array' == $args['type'] )
		$args['type'] = 'plain';
	/* Get the paginated links. */
	$page_links = paginate_links( $args );
	/* Remove 'page/1' from the entire output since it's not needed. */
	$page_links = str_replace( array( '&#038;paged=1\'', '/page/1\'', '/page/1/\'' ), '\'', $page_links );
	/* Wrap the paginated links with the $before and $after elements. */
	$page_links = $args['before'] . $page_links . $args['after'];
	/* Allow devs to completely overwrite the output. */
	$page_links = apply_filters( 'loop_pagination', $page_links );
	/* Return the paginated links for use in themes. */
	if ( $args['echo'] )
		echo $page_links;
	else
		return $page_links;
}
/*
Name :supreme_get_post_taxonomies
*/
function supreme_get_post_taxonomies($post) {
    $post_type  = get_post_type($post);
    $taxonomies = get_object_taxonomies($post_type, 'objects');
	$key_ ='';
	foreach($taxonomies as $key=>$var){
		$key_ .= $key.",";	
	}
	return explode(',',$key_);
}
/*
 * Function Name:the_content_limit
 * Return : Display the limited content
 */
if(!function_exists('the_content_limit')){
	function the_content_limit($max_char, $more_link_text = '', $stripteaser = true, $more_file = '') {	
		global $post;
		$more_link_text = ($more_link_text!='')? $more_link_text: __('Read More &raquo;',THEME_DOMAIN);
		$content = get_the_content();
		$content = apply_filters( 'the_content', $content );
		$content = strip_tags($content);
		$content = substr($content, 0, $max_char);
		$content = substr($content, 0, strrpos($content, " "));
		$more_link_text='<a href="'.get_permalink().'">'.$more_link_text.'</a>';
		$content = $content." ".$more_link_text;
		echo $content;	
	}
}
/*
 * Function Name:listing_post_title_before_image
 * Return : Display the post title
 */
add_action('listing_post_title_before_image','listing_post_title_before_image');
if(!function_exists('listing_post_title_before_image')){
function listing_post_title_before_image($instance)
{
	if(!empty($instance['show_title'])) :
		printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute('echo=0'), the_title_attribute('echo=0') );
	endif;
}
}
/* 
 * Function name: featured_get_image
 * Return: pass post image;
 */
if(!function_exists('featured_get_image')){
	function featured_get_image($arg)
	{
	global $post;
	if($arg['format']=='html')
	{ echo $arg['size'];
		$image = directory_bdw_get_images_plugin($post->ID,$arg['size']);	
		$thumb_img = @$image[0]['file'];
		if($thumb_img)
			echo '<img class="img thumbnail " src="'.$thumb_img.'" />';		
	}else
	{
		$image = directory_bdw_get_images_plugin($post->ID,$arg['size']);	
		$thumb_img = @$image[0]['file'];
		echo $thumb_img;
	}	
	}
}
/*
 * Function Name: supreme_get_additional_image_sizes;
 * Return : display all image size
 */
function supreme_get_additional_image_sizes() {
	global $_wp_additional_image_sizes;
	if ( $_wp_additional_image_sizes )
			return $_wp_additional_image_sizes;
	return array();
}

/* return pagination to detail pages */

function supreme_loop_navigation($post){
	if ( is_attachment() ) : ?>
	<div class="loop-nav">
	  <?php previous_post_link( '%link', '<span class="previous"><span class="meta-nav">&larr;</span> ' . __( 'Return to entry', THEME_DOMAIN ) . '</span>' ); ?>
	</div>
	<!-- .loop-nav -->
	<?php elseif ( is_singular( 'post' ) ) : ?>
	<div class="loop-nav">
	  <?php previous_post_link( '%link', '<span class="previous"><span class="meta-nav">&larr;</span> ' . __( 'Previous', THEME_DOMAIN ) . '</span>' ); ?>
	  <?php next_post_link( '%link', '<span class="next">' . __( 'Next', THEME_DOMAIN ) . '  <span class="meta-nav">&rarr;</span></span>' ); ?>
	</div>
	<!-- .loop-nav -->
	<?php elseif ( !is_singular() && current_theme_supports( 'loop-pagination' ) ) :  loop_pagination( array( 'prev_text' => '<strong class="prev page-numbers">'.__( 'Previous', THEME_DOMAIN ).'</strong> ', 'next_text' => ' <strong class="next page-numbers">'.__( 'Next', THEME_DOMAIN ).'</strong>' ) ); ?>
	<?php elseif ( !is_singular() && $nav = get_posts_nav_link( array( 'sep' => '', 'prelabel' => '<span class="previous"><span class="meta-nav">&larr;</span> ' . __( 'Previous', THEME_DOMAIN ) . '</span>', 'nxtlabel' => '<span class="next">' . __( 'Next', THEME_DOMAIN ) . ' &rarr;</span>' ) ) ) : echo "qwe"; ?>
	<div class="loop-nav"> <?php echo $nav; ?> </div>
<!-- .loop-nav -->
<?php endif;
}
/*
Name : supreme_quote_post_content
Description : apply quats to content
*/
function supreme_quote_post_content( $content ) {
	if ( has_post_format( 'quote' ) ) {
		preg_match( '/<blockquote.*?>/', $content, $matches );
		if ( empty( $matches ) )
			$content = "<blockquote>{$content}</blockquote>";
	}
	return $content;
}
/* filter for excerpt length */
if(!function_exists('supreme_excerpt_length')){ 
	function supreme_excerpt_length() { 
		$tmpdata = get_option(supreme_prefix().'_theme_settings'); 
		if($tmpdata['templatic_excerpt_length']){
			return $tmpdata['templatic_excerpt_length'];
		}else{
			return 400;
		}
	}
}
/* filter for excerpt length */
if(!function_exists('slider_excerpt_length')){
	function slider_excerpt_length() {
	global $legnth_content;
		return $legnth_content;
	}
}
/*
Name : string_limit_words
Desc : filter for excerpt length and Read more link filter
*/
function string_limit_words($string, $word_limit)
{
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit)
	array_pop($words);
	return implode(' ', $words).new_excerpt_more();
}
/*
Name : new_excerpt_more
Desc : Read more link filter
*/
if(!function_exists('new_excerpt_more')){
	function new_excerpt_more($more='') {
		global $post;
		$tmpdata = get_option(supreme_prefix().'_theme_settings');
		if(function_exists('icl_t')){
			icl_register_string(THEME_DOMAIN,@$tmpdata['templatic_excerpt_link'],@$tmpdata['templatic_excerpt_link']);
			$link = icl_t(THEME_DOMAIN,@$tmpdata['templatic_excerpt_link'],@$tmpdata['templatic_excerpt_link']);
		}else{
			$link = (isset($tmpdata['templatic_excerpt_link']))? $tmpdata['templatic_excerpt_link'] : '';
		}
		if(isset($tmpdata['templatic_excerpt_link']) && $tmpdata['templatic_excerpt_link']){
			return '... <a class="moretag" href="'. get_permalink($post->ID) . '">'.$link.'</a>';
		}else{
			return '... <a class="moretag" href="'. get_permalink($post->ID) . '">'.__('Read more &raquo;',THEME_DOMAIN).'</a>';
		}
	}
}
if(!function_exists('supreme_is_layout1c')){
	function supreme_is_layout1c(){
		$global_layout = theme_get_settings( 'supreme_global_layout' );
			if ( $global_layout == 'layout_1c' ){
				return true;
			}else{
				return false;
			}
			
	}
}
/*
Name :supreme_havent_gallery
Descriptioon : is detail page content have gallery shortcode or not 
*/
function supreme_havent_gallery() {
    global $post;
    if (isset($post->post_content) && !stripos($post->post_content, '[gallery')) {
       return true;
    }else{
       return false;
    }
    
}

/*
	Post gallery on detail page of "Post".
	It will show only when gallery post format is selected
*/
function supreme_post_gallery($post) {
	global $post;
	$post_images= directory_bdw_get_images_plugin($post->ID,'thumb');
	$post_main_image = directory_bdw_get_images_plugin($post->ID,'large');
	?>
<?php if(count($post_images)>0): ?>
<!--Image Gallery Start -->
	<script type="text/javascript">
		jQuery(window).load(function() {
		  // The slider being synced must be initialized first
		  jQuery('#carousel').flexslider({
			animation: "slide",
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			itemWidth: 100,
			itemMargin: 5,
			asNavFor: '#slider',
			smoothHeight: true,
		  });
		   
		  jQuery('#slider').flexslider({
			animation: "slide",
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			sync: "#carousel",
			smoothHeight: true,
		  });
		});
	//FlexSlider: Default Settings
	</script>
	<div class="post_gallery_container">
	  <div id="slider" class="flexslider clearfix ">
		<div class="slides_container">
		  <ul id = "main_image" class="slides">
			<?php for($im=0;$im<count($post_main_image);$im++): 
												$attachment_id = $post_main_image[$im]['id'];
												//echo "<pre>"; print_r($post_images);
												$alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
												$attach_data = get_post($attachment_id);
												$title = $attach_data->post_title;
										?>
			<li> <a rel="example_group" href="<?php echo $post_main_image[$im]['file'];?>" title="<?php echo $title; ?>"> <img src="<?php echo $post_main_image[$im]["file"];?>" title="<?php echo $title; ?>" alt="<?php echo $alt; ?>" /> </a> </li>
			<?php endfor; ?>
		  </ul>
		</div>
	  </div>
	  <!--Finish image gallery -->
	  <?php if(count($post_images) > 1){ ?>
	  <div id="carousel" class="flexslider">
		<ul class="slides">
		  <?php for($im=0;$im<count($post_images);$im++): 
													$attachment_id = $post_images[$im]['id'];
													//echo "<pre>"; print_r($post_images);
													$alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
													$attach_data = get_post($attachment_id);
													$title = $attach_data->post_title;
											?>
		  <li> <a href="<?php echo $post_images[$im]['file'];?>" title="<?php echo $title; ?>"> <img src="<?php echo $post_images[$im]["file"];?>" height="70" width="70"  title="<?php echo $title; ?>" alt="<?php echo $alt; ?>" /> </a> </li>
		  <?php endfor; ?>
		</ul>
	  </div>
	  <?php } ?>
	</div>
<?php endif; ?>
<?php 
}
/*
	It will return the favicon icon.
*/
function supreme_get_favicon(){
	global $tmpdata;
	if(supreme_get_settings('supreme_favicon_icon')){
		return supreme_get_settings('supreme_favicon_icon');
	}
}
/*
 * Function Name:user_single_post_visit_count
 * Argument: Post id
 */
if(!function_exists('user_single_post_visit_count')):
	function user_single_post_visit_count($pid)
	{
		if(get_post_meta($pid,'viewed_count',true))
		{
			return get_post_meta($pid,'viewed_count',true);
		}else
		{
			return '0';	
		}
	}
endif;
/*
 * Function Name:user_single_post_visit_count_daily
 * Argument: Post id
 */
if(!function_exists('user_single_post_visit_count_daily')):
function user_single_post_visit_count_daily($pid)
{
	if(get_post_meta($pid,'viewed_count_daily',true))
	{
		return get_post_meta($pid,'viewed_count_daily',true);
	}else
	{
		return '0';	
	}
}
endif;
/*
	This function will return the post views 
 */
if(!function_exists('supreme_view_count')):
function supreme_view_count( $content ) {	
	$custom_content='';
	global $post;
	if ( is_single() || is_singular() && !is_page()) 
	{
			$sep =" , ";
			if(user_single_post_visit_count($post->ID) == 1){
				$time = ' '.__("time",THEME_DOMAIN);
			}else{
				$time = ' '.__("times",THEME_DOMAIN);
			}
			
			if(user_single_post_visit_count_daily($post->ID) == 1){
				$visit = ' '.__("Visit",THEME_DOMAIN);
			}else{
				$visit = ' '.__("Visits",THEME_DOMAIN);
			}
			$custom_content.="<p><span class='view_counter'>".__("Visited",THEME_DOMAIN).'<b>'.sprintf(__(' %s',THEME_DOMAIN) ,user_single_post_visit_count($post->ID)).$time.'</b>';
			$custom_content.= $sep.' <b>'.user_single_post_visit_count_daily($post->ID).' '.$visit."</b>".' '.__("today",THEME_DOMAIN)."</span></p>";
			$custom_content .= $content;
			//$content.=$custom_content;
			return $custom_content;
	} 
	return $content;
}
endif;

/* hook before body to add google analytics code */
add_action('before_body_end','supreme_gogole_analytics');
function supreme_gogole_analytics(){
	echo stripslashes(supreme_get_settings('supreme_gogle_analytics_code'));
}

/*
	Secondary navigation menu
*/
function supreme_secondary_navigation(){
	$theme_name = get_option('stylesheet');
	$nav_menu = get_option('theme_mods_'.strtolower($theme_name));
	if(is_active_sidebar('mega_menu')){
		if(function_exists('dynamic_sidebar')){
			dynamic_sidebar('mega_menu'); // jQuery mega menu
		} 
	}elseif(isset($nav_menu['nav_menu_locations'])  && isset($nav_menu['nav_menu_locations']['secondary']) && $nav_menu['nav_menu_locations']['secondary'] != 0){ // show only on desktop ?>
	<div id="nav-secondary" class="nav_bg columns">
	  <?php apply_filters('tmpl_supreme_header_secondary',supreme_header_secondary_navigation()); // Loads the menu-secondary template.?>
	</div>
	<?php 
		//add_action('pre_get_posts', 'home_page_feature_listing');	
		apply_filters('tmpl_after-header',supreme_sidebar_after_header()); // Loads the sidebar-after-header. 
	}else{
		echo '<div id="nav-secondary" class="nav_bg columns"><div id="menu-secondary" class="menu-container clearfix"><nav class="wrap" role="navigation"><div class="menu"><ul id="menu-secondary-items" class="">';
			wp_list_pages('title_li=&depth=0&child_of=0&number=5&show_home=1&sort_column=ID&sort_order=DESC');
		echo '</ul></div>';
		apply_filters('supreme-nav-right',dynamic_sidebar('secondary_navigation_right'));
		echo '</nav></div></div>';
	}
}
/*
	Primary navigation menu
*/
function supreme_primary_navigation(){
	/* $theme_name = get_option('stylesheet');
	$nav_menu = get_option('theme_mods_'.strtolower($theme_name));
	if(isset($nav_menu['nav_menu_locations'])  && $nav_menu['nav_menu_locations']['primary'] != 0){ */
	if ( has_nav_menu( 'primary' ) || isset($_REQUEST['ptype']) ) : 
	do_action( 'before_menu_primary' ); // supreme_before_menu_primary ?>
	<!-- Primary Navigation Menu Start -->
	<div id="menu-primary" class="menu-container">
	  <nav role="navigation" class="wrap">
		<div id="menu-primary-title">
		  <?php _e( 'Menu', THEME_DOMAIN ); ?>
		</div>
		<!-- #menu-primary-title -->
		<?php do_action( 'open_menu_primary' ); // supreme_open_menu_primary 
					wp_nav_menu( array( 'theme_location' => 'primary', 'container_class' => 'menu', 'menu_class' => 'primary_menu clearfix', 'menu_id' => 'menu-primary-items', 'fallback_cb' => '' ) ); 
					do_action( 'close_menu_primary' ); // supreme_close_menu_primary ?>
	  </nav>
	</div>
	<!-- #menu-primary .menu-container -->
<!-- Primary Navigation Menu End -->
<?php do_action( 'after_menu_primary' ); // supreme_after_menu_primary
		endif;
	
}

/* to display slider above main */
apply_filters('supreme_above_main-banner',add_action('before_main','supreme_home_banner_sidebar')); // you can remove this filter from chid theme and add another one to show slider inside main like : add_action ('before-content','supreme_above_main-banner'); 
	
function supreme_home_banner_sidebar(){ 
		if(is_home() || is_front_page())
			dynamic_sidebar('home-page-banner');
}

/*
 Filters 'get_theme_layout' to set layouts for specific installed plugin pages.
*/
function supreme_plugin_layouts( $layout ) {
	if ( current_theme_supports( 'theme-layouts' ) ) {	
		$global_layout = theme_get_settings( 'supreme_global_layout' );
		if ( $layout == 'layout-default' ) {			
				if ( $global_layout == 'layout_1c' )
					$layout = 'layout-1c';
				elseif ( $global_layout == 'layout_2c_l' )
					$layout = 'layout-2c-l';
				elseif ( $global_layout == 'layout_2c_r' )
					$layout = 'layout-2c-r';
				elseif ( $global_layout == 'layout_3c_c' )
					$layout = 'layout-3c-c';
				elseif ( $global_layout == 'layout_3c_l' )
					$layout = 'layout-3c-l';
				elseif ( $global_layout == 'layout_3c_r' )
					$layout = 'layout-3c-r';
				elseif ( $global_layout == 'layout_hl_1c' )
					$layout = 'layout-hl-1c';
				elseif ( $global_layout == 'layout_hl_2c_l' )
					$layout = 'layout-hl-2c-l';
				elseif ( $global_layout == 'layout_hl_2c_r' )
					$layout = 'layout-hl-2c-r';
				elseif ( $global_layout == 'layout_hr_1c' )
					$layout = 'layout-hr-1c';
				elseif ( $global_layout == 'layout_hr_2c_l' )
					$layout = 'layout-hr-2c-l';
				elseif ( $global_layout == 'layout_hr_2c_r' )
					$layout = 'layout-hr-2c-r';
		}		
	}	
	return $layout;
}
/**
 * Filters 'theme_layouts_strings'.
 */
function supreme_theme_layouts( $strings ) {
	/* Set up the layout strings. */
	$strings = array(
		'default' => __( 'Default', ADMINDOMAIN ),
		'1c' => __( 'One Column', ADMINDOMAIN ),
		'2c-l' => __( 'Two Columns, Left', ADMINDOMAIN ),
		'2c-r' => __( 'Two Columns, Right', ADMINDOMAIN )
	);
	return $strings;
}
/*
 * Filters 'get_theme_layout'.
 */
function supreme_layout_default( $layout ) {return 'layout-default';}
function supreme_layout_1c( $layout ) {return 'layout-1c';}
function supreme_layout_2c_l( $layout ) {return 'layout-2c-l';}
function supreme_layout_2c_r( $layout ) {return 'layout-2c-r';}

/*
 * Disables sidebars based on layout choices.
 *
 */
function supreme_disable_sidebars( $sidebars_widgets ) {
	global $wp_query;
	if ( current_theme_supports( 'theme-layouts' ) && !is_admin() ) {
		if ( 'layout-1c' == theme_layouts_get_layout() ) {
			$sidebars_widgets['front-page-sidebar'] = false;
			$sidebars_widgets['primary-sidebar'] = false;
			$sidebars_widgets['post-listing-sidebar'] = false;
			$sidebars_widgets['post-detail-sidebar'] = false;
			$sidebars_widgets['secondary'] = false;
			$sidebars_widgets['contact_page_sidebar'] = false;
			$args=array(
			  '_builtin' => false,
			);
			//Disable all custom post type sidebar start
			$post_types = get_post_types($args);
			foreach($post_types as $post_type){
				$sidebars_widgets["{$post_type}_category_listing_sidebar"] = false;
				$sidebars_widgets["{$post_type}_detail_sidebar"] = false;
				$sidebars_widgets["{$post_type}_tag_listing_sidebar"] = false;
			}
			//Disable all custom post type sidebar end

			$theme_sidebar = '';
			$hide_theme_sidebars = apply_filters('theme_sidebar_hide',$theme_sidebar);
			if($hide_theme_sidebars){
				foreach($hide_theme_sidebars as $hide_theme_sidebar){
					$sidebars_widgets["{$hide_theme_sidebar}"] = false;
				}
			}
			//Filter for hide sidebar from theme end
		}
		elseif ( 'layout-hl-1c' == theme_layouts_get_layout() || 'layout-hr-1c' == theme_layouts_get_layout() ) {
			$sidebars_widgets['front-page-sidebar'] = false;
			$sidebars_widgets['primary-sidebar'] = false;
			$sidebars_widgets['secondary'] = false;
			$sidebars_widgets['after-header'] = false;
			$sidebars_widgets['after-header-2c'] = false;
			$sidebars_widgets['after-header-3c'] = false;
			$sidebars_widgets['after-header-4c'] = false;
			$sidebars_widgets['after-header-5c'] = false;
		}
		elseif ( 'layout-hl-2c-l' == theme_layouts_get_layout() || 'layout-hl-2c-r' == theme_layouts_get_layout() || 'layout-hr-2c-l' == theme_layouts_get_layout() || 'layout-hr-2c-r' == theme_layouts_get_layout() ) {
			$sidebars_widgets['after-header'] = false;
			$sidebars_widgets['after-header-2c'] = false;
			$sidebars_widgets['after-header-3c'] = false;
			$sidebars_widgets['after-header-4c'] = false;
			$sidebars_widgets['after-header-5c'] = false;
		}
		
	}
	return $sidebars_widgets;
}

/*
Name: templatic_posts_where_filter
Desc : WPML compatible query , return the "joins" for wp query
*/
function templatic_posts_where_filter($join)
{
	global $wpdb, $pagenow, $wp_taxonomies;
	$language_where='';
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		$language = ICL_LANGUAGE_CODE;
		$join .= " AND t.language_code='".$language."'";
	}	
	return $join;
}

/*
Name: templatic_widget_wpml_filter
Desc : WPML compatible query , return the "where" for wp query in widgets
*/
function templatic_widget_wpml_filter($where){
	global $wpdb;
	if(is_plugin_active('wpml-string-translation/plugin.php')){
		$language = ICL_LANGUAGE_CODE;
		$where .= " AND t.language_code='".$language."'";
	}
	return $where;
}

/*
Name: check_if_woocommerce_active
Desc: Check if woo commerce plug in is active or not
*/
if(!function_exists('check_if_woocommerce_active')){
	function check_if_woocommerce_active(){
		$plugins = wp_get_active_and_valid_plugins();
		$flag ='false';
		foreach($plugins as $plugins){
			if (strpos($plugins,'woocommerce.php') !== false) {
				$flag = 'true';
				break;
			}else{
				 $flag = 'false';
			}
		}
		return $flag;
	}
}

/* if woocommerce activated add the theme suppoert for woocommerce*/
if(function_exists('check_if_woocommerce_active')){
	$is_woo_active = check_if_woocommerce_active();
	if($is_woo_active == 'true'){
		add_theme_support( 'woocommerce' );
	}
}	
/* Action for post type selection meta box for advanced search page template */
add_action('admin_menu', 'supreme2_ptthemes_taxonomy_meta_box');	
add_action('save_post', 'supreme2_insert_post_type');
/*Add The Metabox for post type selection for archive page template. START*/
if(!function_exists('supreme2_ptthemes_taxonomy_meta_box')){
	function supreme2_ptthemes_taxonomy_meta_box() {
		add_meta_box("supreme2_post_type_meta", "Post type options", "supreme2_post_type_meta", "page", "side", "core");
	}	
}
function remove_post_type_option()
{
	if(get_current_screen()->id == 'page')
	{
		echo '<style type="text/css">
				.metabox-prefs label[for="supreme2_post_type_meta-hide"] { display: none !important; }
			</style>';
	}
}
add_action('admin_head', 'remove_post_type_option', 10);
function supreme2_post_type_meta(){ ?>
<script type="text/javascript">
		jQuery.noConflict(); 
		jQuery(document).ready(function() {
		var page_template = jQuery("#page_template").val();
		if(page_template !='page-templates/advance-search.php'){
			jQuery("#supreme2_post_type_meta").css('display','none');
		}
		jQuery("#page_template").change(function() {
			var src = jQuery(this).val();
				if(jQuery("#page_template").val() =='page-templates/advance-search.php'){
				jQuery("#supreme2_post_type_meta").fadeIn(2000); }else{
				jQuery("#supreme2_post_type_meta").fadeOut(2000);
				}
			});
		});
	</script>
<?php
		$custom_post_types_args = array();  
		$custom_post_types = get_post_types($custom_post_types_args,'objects');   
		global $post;
		$template_post_type = "";
		$template_post_type = get_post_meta($post->ID,'template_post_type',true);
		$template_post_types = get_option('templatic_custom_post');
		$template_post_types = array_keys($template_post_types);
		
		$i=0;
		foreach ($custom_post_types as $content_type) 
		{
			if($content_type->name =='post' || in_array($content_type->name,$template_post_types)){				
								
				
				if($template_post_type == $content_type->name || $i==0){ $c = 'checked=checked';}else{ $c=''; }
				?>
<input type="radio" name="template_post_type1" id="<?php echo $content_type->name?>" value="<?php echo $content_type->name;?>"  <?php echo $c;?> />
<label for="<?php echo $content_type->name?>"> <?php echo ucfirst($content_type->name); ?></label>
<br/>
<?php
				$i++;
			}
		}	
}
function supreme2_insert_post_type(){
	global $globals,$wpdb;
	update_post_meta(@$_POST['post_ID'], 'template_post_type', @$_POST['template_post_type1']);
}
function supreme2_view_counter(){
   $prefix = supreme_prefix();
   $settings = get_option( supreme_prefix()."_theme_settings" ); 
   remove_filter('the_content','view_sharing_buttons');  	
   remove_filter( 'the_content', 'view_count' );
   if(isset($settings['enable_view_counter']) && $settings['enable_view_counter']==1){  
	   	add_filter("before_content", "supreme_get_custom_post_type_template" ) ;
		add_filter( 'the_content', 'supreme_view_count' );
   }
	if(function_exists('check_if_woocommerce_active')){
		$is_woo_active = check_if_woocommerce_active();
	}	
	if($is_woo_active == 'true' && 'product' == get_post_type()){
		add_action('woocommerce_single_product_summary','supreme2_view_sharing_buttons',100);
	}else{
		add_action("after_entry",'supreme2_view_sharing_buttons');
	}
			
	
}
add_action("wp_head", "supreme2_view_counter");
if(!function_exists('supreme2_view_sharing_buttons')){
	function supreme2_view_sharing_buttons($content){
		global $post;	
		if (is_single() && ($post->post_type!='product_variation' )) 
		{
			$post_thumbnail_id = get_post_thumbnail_id( $post->ID ); 
			$post_img = wp_get_attachment_image_src( $post_thumbnail_id, 'thumb' );
			$post_images = $post_img[0];
			$title=urlencode($post->post_title);
			$url=urlencode(get_permalink($post->ID));
			$summary=urlencode(htmlspecialchars($post->post_content));
			$image=directory_bdw_get_images_plugin($post->ID, 'thumb' );
			$image = $image[0]['file'];
			$settings = get_option( supreme_prefix()."_theme_settings" );   	
			$excerpt = get_the_excerpt();
			if( @$settings['facebook_share_detail_page'] || @$settings['google_share_detail_page'] || @$settings['twitter_share_detail_page'] || @$settings['pintrest_detail_page']){
			echo '<div class="share_link">';
				if(@$settings['facebook_share_detail_page'] == 1){
					?>
<a onClick="window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo $title;?>&amp;p[summary]=<?php echo $summary;?>&amp;p[url]=<?php echo $url; ?>&amp;&amp;p[images][0]=<?php echo $image;?>','sharer','toolbar=0,status=0,width=548,height=325');" href="javascript: void(0)" id="facebook_share_button">
<?php _e('Facebook Share.',THEME_DOMAIN); ?>
</a>
<?php
				}
				if(@$settings['google_share_detail_page'] == 1): ?>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
<div class="g-plus" data-action="share" data-annotation="bubble"></div>
<?php endif;
				
				if(@$settings['twitter_share_detail_page'] == 1): ?>
<a href="https://twitter.com/share" class="twitter-share-button" data-lang="en" data-text='<?php echo $excerpt;?>' data-url="<?php echo get_permalink($post->ID); ?>" data-counturl="<?php echo get_permalink($post->ID); ?>">
<?php _e('Tweet',THEME_DOMAIN); ?>
</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<?php endif;
				
				if(@$settings['pintrest_detail_page'] == 1):?>
<!-- Pinterest -->
<div class="pinterest"> <a href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($post->ID)); ?>&media=<?php echo $image; ?>&description=<?php the_title(); ?>" >
  <?php _e("Pin It",THEME_DOMAIN); ?>
  </a>
  <script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>
</div>
<?php endif; 
			echo '</div>';
			}
		}
		return $content;
	}
}
/*
 * Function Name: get_custom_post_type_template
 * add single post view counter
 */
if(!function_exists('supreme_get_custom_post_type_template')){
	function supreme_get_custom_post_type_template($single_template) { 
		global $post;	 
		if(is_single() || is_singular())
			supreme_view_counter_single_post($post->ID);
		
		return $single_template;
	}
}
/*
 * Function Name: view_counter_single_post
 * Argument: post id
 */
if(!function_exists('supreme_view_counter_single_post')){
	function supreme_view_counter_single_post($pid){ 
		if( @$_SERVER['HTTP_REFERER'] == '' || !strstr( @$_SERVER['HTTP_REFERER'], @$_SERVER['REQUEST_URI']))
	{
			$viewed_count = get_post_meta($pid,'viewed_count',true);
			$viewed_count_daily = get_post_meta($pid,'viewed_count_daily',true);
			$daily_date = get_post_meta($pid,'daily_date',true);
		
			update_post_meta($pid,'viewed_count',$viewed_count+1);
		
			if(get_post_meta($pid,'daily_date',true) == date('Y-m-d')){
				update_post_meta($pid,'viewed_count_daily',$viewed_count_daily+1);
			} else {
				update_post_meta($pid,'viewed_count_daily','1');
			}
			update_post_meta($pid,'daily_date',date('Y-m-d'));
		}
	}
}

if(!function_exists('supreme_get_link_url')){
	function supreme_get_link_url() {
		$has_url = get_the_post_format_url();
		return ( $has_url ) ? $has_url : apply_filters( 'the_permalink', get_permalink() );
	}
}
add_action('admin_footer','delete_sample_data');
if(!function_exists('delete_sample_data')){
function delete_sample_data()
{
?>
<script type="text/javascript">
jQuery(document).ready( function(){
	jQuery('.button_delete').click( function() {
		if(confirm(" Delete the dummy data only if you haven't changed the added data (posts, pages, etc). If you have, all those changes will be lost. Deleting dummy data might also cause changes with your widgets.")){
			window.location = "<?php echo home_url()?>/wp-admin/themes.php?dummy=del";
		}else{
			return false;
		}	
	});
});
</script>
<?php } }
add_action('paypal_successfull_return_content','successfull_return_paypal_status',10,4);
function successfull_return_paypal_status()
{
	update_post_meta($_REQUEST['pid'],"status","Approved");
}
//Set Default permalink on theme activation: start
add_action( 'load-themes.php', 'default_permalink_set' );
function default_permalink_set(){
	global $pagenow;
	if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ){ // Test if theme is activate
		//Set default permalink to postname start
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$wp_rewrite->flush_rules();
		if(function_exists('flush_rewrite_rules')){
			flush_rewrite_rules(true);  
		}
		//Set default permalink to postname end
	}
}
//Set Default permalink on theme activation: end
add_action('testimonial_script','widget_testimonial_script',20,3);
function widget_testimonial_script($transition,$fadin,$fadout)
{
	?>
<script type="text/javascript">
		var $testimonials = jQuery.noConflict();
		$testimonials(document).ready(function() {
		  $testimonials('#testimonials')
			.cycle({
				fx: '<?php echo $transition; ?>', // choose your transition type, ex: fade, scrollUp, scrollRight, shuffle
				 timeout: '<?php echo $fadin; ?>',
				 speed:'<?php echo $fadout; ?>',
	
			 });
		});
		</script>
<?php
}
add_action('init','popular_post_widget',10);
//add image size for pospular post widget
function popular_post_widget()
{
	$supreme_thumbnail_height = apply_filters('supreme_thumbnail_height',60);
	$supreme_thumbnail_width =  apply_filters('supreme_thumbnail_width',60);
	add_image_size( 'popular_post-thumbnail', $supreme_thumbnail_height, $supreme_thumbnail_width, true );
	add_image_size( 'mobile-thumbnail', 60, 60, true );
}
add_filter('popular_post_thumb_image','crop_popular_post_thumb_image',10);
function crop_popular_post_thumb_image()
{
	return get_the_image(array('echo' => false, 'size'=> 'popular_post-thumbnail','height' => 60,'width'=>60,'default_image'=> "http://placehold.it/60x60"));
}
if(!function_exists('excerpt')){
	function excerpt($limit = 27) {
		$excerpt = explode(' ', get_the_excerpt(), $limit);
		if (count($excerpt)>=$limit) {
			array_pop($excerpt);
			$excerpt = implode(" ",$excerpt).'...';
		} else {
			$excerpt = implode(" ",$excerpt);
		}	
		$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
		return $excerpt;
	}
}
if(!function_exists('content')){
	function content($limit = 27) {
		$content = explode(' ', get_the_content(), $limit);
		if (count($content)>=$limit) {
			array_pop($content);
			$content = implode(" ",$content).'...';
		} else {
			$content = implode(" ",$content);
		}	
		$content = preg_replace('/\[.+\]/','', $content);
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}
}
//Add action for theme settings menu: Start
	add_action('admin_menu', 'register_theme_settings_menu',9999);
//Add action for theme settings menu: End
/*
Function Name: register_theme_settings_menu
Purpose		 : To create submenu for theme settings in Appearance menu
*/
if(!function_exists('register_theme_settings_menu')){
	function register_theme_settings_menu() {
		add_theme_page(__("Theme Settings",ADMINDOMAIN), __("Theme Settings",ADMINDOMAIN), 'manage_options', 'theme-settings-page', 'theme_settings_page_callback'  );
		add_theme_page(__('Custom CSS Editor',ADMINDOMAIN), __('Custom CSS Editor',ADMINDOMAIN), 'edit_theme_options', 'templatic_custom_css_editor', 'templatic_custom_css_editor_settings');
	}
}
/* to check tevolution plugin is active or not*/
function is_tevolution_active(){
	if(is_plugin_active('Tevolution/templatic.php')){
		return true;
	}else{
		return false;
	}
}
/* to hide description on author page */
function tmpl_donot_display_description(){
	global $post;
	$tmpdata = get_option('templatic_settings');
	if(is_tevolution_active() && (@$tmpdata['listing_hide_excerpt']=='' || !in_array($post->post_type,@$tmpdata['listing_hide_excerpt']))){
			
		return false;
		
	}else{
		return true;
	}
}
/*
Name:get_tmpl_plugin_directory
desc: return the plugin directory path
*/
if(!function_exists('get_tmpl_plugin_directory')){
function get_tmpl_plugin_directory() {
	 return WP_CONTENT_DIR."/plugins/";
}
}
/*
Name: templatic_custom_css_editor_settings
desc: save custom css in an option table
*/
function templatic_custom_css_editor_settings()
{
	$title = __('Custom CSS Editor',ADMINDOMAIN);
	$file = TEMPLATE_DIR."custom.css";?>
	
	
	<?php 
	$theme = 'directory';
	if ( $theme )
		$stylesheet = $theme;
	else
		$stylesheet = get_stylesheet();

	$theme = wp_get_theme( $stylesheet );
	$allowed_files = $theme->get_files( 'php', 1 );
	$has_templates = ! empty( $allowed_files );
	$style_files = $theme->get_files( 'css' );
	$allowed_files['style.css'] = $style_files['style.css'];
	$allowed_files += $style_files;
	
	$relative_file = 'custom.css';
	if(isset($_POST['action']) && $_POST['action'] == 'update')
	{
		
		check_admin_referer( 'edit-theme_' . $file . $stylesheet );
		$newcontent = wp_unslash( $_POST['custom_css_content'] );
		update_option('directory_custom_css',$newcontent);
		$location = 'themes.php?page=templatic_custom_css_editor';
		$location .= '&updated=true';
		wp_redirect( $location );
		exit;
	}
	$content = '';
	if(file_exists($file))
	{
		if ( ! $error && filesize( $file ) > 0 ) {
			$f = fopen($file, 'r');
			$content = fread($f, filesize($file));
	
			if ( '.php' == substr( $file, strrpos( $file, '.' ) ) ) {
				$functions = wp_doc_link_parse( $content );
	
				$docs_select = '<select name="docs-list" id="docs-list">';
				$docs_select .= '<option value="">' . esc_attr__( 'Function Name&hellip;' ) . '</option>';
				foreach ( $functions as $function ) {
					$docs_select .= '<option value="' . esc_attr( urlencode( $function ) ) . '">' . htmlspecialchars( $function ) . '()</option>';
				}
				$docs_select .= '</select>';
			}
	
			$content = esc_textarea( $content );
		}
	}
	?>
    <div class="wrap">
	<h2><?php echo esc_html( $title ); ?></h2>
	 <p> <?php echo sprintf(__('You can customize the theme by entering CSS classes in this section. Enter only the classes you want to overwrite (not the whole style.css file). For details on using custom.css open %s.',ADMINDOMAIN),'<a href="http://templatic.com/docs/using-custom-css-for-theme-customizations/">'.__('this article',ADMINDOMAIN).'</a>');?></p>
   
   <?php if(isset($_GET['updated']) && $_GET['updated'] == 'true'){ ?>
	    <div class="updated" id="message"><p><?php echo __('File edited successfully.',ADMINDOMAIN); ?></p></div>
    <?php } ?>
	<form name="custom_css" id="template" action="" method="post">
	<?php wp_nonce_field( 'edit-theme_' . $file . $stylesheet ); ?>
		<div><textarea cols="70" rows="30" name="custom_css_content" id="custom_css_content" aria-describedby="newcontent-description"><?php echo $content; ?></textarea>
        <input type="hidden" name="action" value="update" />
		<input type="hidden" name="file" value="<?php echo esc_attr( $relative_file ); ?>" />
		<input type="hidden" name="theme" value="<?php echo esc_attr( $theme->get_stylesheet() ); ?>" />
		<input type="hidden" name="scrollto" id="scrollto" value="0" />
		<?php
		if(get_option('directory_custom_css'))
		{
			if ( is_writeable( $file ) ) :
				submit_button( __( 'Update File',ADMINDOMAIN ), 'primary', 'submit', true );
			else : ?>
		<p><em><?php __('You need to make this file writable before you can save your changes. See <a href="http://codex.wordpress.org/Changing_File_Permissions">the Codex</a> for more information in directory root folder.',ADMINDOMAIN); ?></em></p>
		<?php endif; 
		}
		else
		{
			submit_button( __( 'Update File', ADMINDOMAIN ), 'primary', 'submit', true );
		}?>
		</div>
	</form>
    </div>
<?php
}
/*
Name: save_custom_css
desc: save custom css in a file from option
*/
add_action('init','save_custom_css');
function save_custom_css()
{
	/* DOING_AJAX is define then return false for admin ajax*/
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {		
		return ;	
	}
	$file = TEMPLATE_DIR."custom.css";
	$theme = 'directory';
	if ( $theme )
		$stylesheet = $theme;
	else
		$stylesheet = get_stylesheet();

	$theme = wp_get_theme( $stylesheet );
	
	{
		if(!file_exists($file))
		{
			fopen( $file, 'w+' );
		}
	}
	$newcontent = get_option('directory_custom_css');
	if ( is_writeable( $file )) {
		//is_writable() not always reliable, check return value. see comments @ http://uk.php.net/is_writable
		$f = fopen( $file, 'w+' );
		if ( $f !== false ) {
			fwrite( $f, $newcontent );
			fclose( $f );
			$theme->cache_delete();
		}
	}
}

add_action('admin_head', 'tmpl_admin_popupcss', 11); // add css for auto update section in backend

function tmpl_admin_popupcss(){ ?>
	<style>
	.table_tnews{float:right;width:63%}.t_theme{float:left;width:34%;margin-right:3%}.t_theme img{max-width:100%}.pimg img{border:1px solid #ccc}.clearfix{clear:both}ul#templatic-services li{list-style:disc inside}.clearfix:after{clear:both;content:".";display:block;font-size:0;height:0;line-height:0;visibility:hidden}.theme_meta .more a.btn_viewdemo,.theme_meta .more a.btn_viewdetails{margin:0}.table_tnews .news li p{margin-top:0}.templatic-dismiss{background:url(../images/xit.gif) no-repeat scroll 0 2px transparent;position:absolute;right:60px;top:8px;width:0;font-size:13px;line-height:1;padding:0 0 0 10px;text-decoration:none;text-indent:3px}.templatic-dismiss:hover{background-position:-10px 2px}.templatic_autoinstall{position:relative;padding:12px!important},.login .message,div.updated{background:#FFFBE4;border-color:#DFDFDF}.postbox .inside{margin:15px 0!important}.themeunit{margin-bottom:10px}#TB_iframeContent,#TB_window{height:460px!important;margin-top:0!important}#TB_iframeContent body{padding:0!important}body{height:auto}.templatic_login{background:none repeat scroll 0 0 #FFF;border:0!important;margin:0!important;font-size:14px;font-weight:400;padding:15px;padding-top:20px;width:40%}.templatic_login label{color:#777;font-size:14px}.templatic_login form .input,.templatic_login input[type=password],.templatic_login input[type=text]{background:none repeat scroll 0 0 #FBFBFB;border:1px solid #E5E5E5;box-shadow:1px 1px 2px rgba(200,200,200,.2)inset;color:#555;font-size:24px;font-weight:200;line-height:1;margin-bottom:16px;margin-right:6px;margin-top:2px;outline:0 none;padding:10px 8px 6px;width:100%}.templatic_login input[type=submit]{background-color:#21759b;background-image:-webkit-gradient(linear,left top,left bottom,from(#2a95c5),to(#21759b));background-image:-webkit-linear-gradient(top,#2a95c5,#21759b);background-image:-moz-linear-gradient(top,#2a95c5,#21759b);background-image:-ms-linear-gradient(top,#2a95c5,#21759b);background-image:-o-linear-gradient(top,#2a95c5,#21759b);background-image:linear-gradient(to bottom,#2a95c5,#21759b);border-color:#21759b;box-shadow:0 1px 0 rgba(120,200,230,.5)inset;color:#FFF;text-decoration:none;text-shadow:0 1px 0 rgba(0,0,0,.1);height:30px;line-height:28px;padding:0 12px 2px;border-radius:3px;border-style:solid;border-width:1px;cursor:pointer;display:inline-block;font-size:12px;margin-right:10px}.templatic_login p.info{margin-top:0}body{min-width:380px!important}#pblogo{margin-top:10px;text-align:left!important}#TB_window{left:53%!important;top:100px!important}	
	</style>
<?php }


/**
	It will return the all sidebar/widget area of themes 
 */
function supreme_get_sidebars() {
	/* Set up an array of sidebars. */
	global $theme_sidebars,$plugin_sidebars;
	if(empty($theme_sidebars))
	{
		$theme_sidebars = array(''); 
	}
	if(empty($plugin_sidebars))
	{
		$plugin_sidebars = array(''); 
	}
	
	$sidebars = array(
		'header' => array(
			'name' =>	apply_filters('supreme_header_right_title',__( 'Header', ADMINDOMAIN )),
			'description' =>	apply_filters('supreme_header_right_description',__( "The area is located on the right side of your header (between primary and secondary navigation).", ADMINDOMAIN )),
		),
		'secondary_navigation_right' => array(
			'name' =>	__( 'Secondary Navigation', ADMINDOMAIN ),
			'description' =>	__( 'Widgets placed inside this area will appear on the right side of your secondary navigation bar (the one below the logo). The simple "Search" widget works best here.', ADMINDOMAIN ),
		),
		'home-page-banner' => array(
			'name' =>	apply_filters('supreme_home_page_banner_title',__( 'Homepage Banner', ADMINDOMAIN )),
			'description' =>	__( "This area usually displays the big slider or the map. It's located between the header and the Homepage - Main Content area.", ADMINDOMAIN ),
		),	
		
		'home-page-content' => array(
			'name' =>	__( 'Homepage - Main Content', ADMINDOMAIN ),
			'description' =>	apply_filters('supreme_home_page_widget_area_description',__('This area appears alongside the homepage sidebar. It&#39;s located between the homepage slider and subsidiary areas.',ADMINDOMAIN)),
		),	
		'front-page-sidebar' => array(
		'name' =>  __( 'Homepage Sidebar', ADMINDOMAIN ),
		'description' => __( 'The area is located below the homepage slider and above subsidiary areas. It shows alongside Homepage - Main Content area.', ADMINDOMAIN )
		),
		
		
		'post-listing-sidebar' => array(
		'name' =>  __( 'Post Category Page Sidebar', ADMINDOMAIN ),
		'description' => __( 'This sidebar will show on category pages for the built-in WordPress Posts.', ADMINDOMAIN )
		),
		
		'post-detail-sidebar' => array(
		'name' =>  __( 'Post Detail Page Sidebar', ADMINDOMAIN ),
		'description' => __( 'This sidebar will show on detail (single) Post pages.', ADMINDOMAIN )
		),
		
		'after-content' => array(
			'name' =>	__( 'All Pages - Below Content',ADMINDOMAIN ),
			'description' =>	__( "The widget area is located below the main content on all pages. For example, on detail pages you'll find it below the comments and related posts.", ADMINDOMAIN ),
		),
		'before-content' => array(
			'name' =>	__( 'All Pages - Above Content', ADMINDOMAIN ),
			'description' =>	__( "The widget area is located above the main content on all pages. For example, on detail pages you'll find it above the title.", ADMINDOMAIN ),
		),
		'entry' => array(
			'name' =>	__( 'Post Detail Page - Before Description', ADMINDOMAIN ),
			'description' =>	__( 'Display widgets between the title and description on detail Post pages.', ADMINDOMAIN),
		),
		'after-singular' => array(
			'name' =>	__( 'Detail Pages - Below Content',ADMINDOMAIN ),
			'description' =>	__( 'The area shows below the main content on WordPress Posts and Pages.', ADMINDOMAIN ),
		),
		'primary-sidebar' => array(
			'name' => 	__( 'Primary Sidebar',ADMINDOMAIN ),
			'description' => 	__( 'This sidebar will show on pages that do not have a unique sidebar area assigned to them. It&#39;s essentially a backup sidebar..', ADMINDOMAIN )
		),
		
		'subsidiary' => array(
			'name' => 	__( 'Subsidiary - 1 Column',  ADMINDOMAIN ),
			'description' =>	__( 'Displays widgets in a single column on all pages. The area shows below the main content area.', ADMINDOMAIN),
		),
		'subsidiary-2c' => array(
			'name' =>	__( 'Subsidiary - 2 Column', ADMINDOMAIN ),
			'description' =>	__( 'Displays widgets in 2 columns on all pages. The area shows below the main content area.', ADMINDOMAIN),
		),
		
		'subsidiary-3c' => array(
			'name' =>	__( 'Subsidiary - 3 Column',ADMINDOMAIN ),
			'description' =>	__( 'Displays widgets in 3 columns on all pages. The area shows below the main content area.', ADMINDOMAIN),
		),
		'after-header' => array(
			'name' =>	__( 'After Header', ADMINDOMAIN ),
			'description' =>	__( 'A 1-column widget area loaded after the header of the site.', ADMINDOMAIN ),
		),		
		'contact_page_widget' => array(
			'name' =>	__( 'Contact Page - Main Content', ADMINDOMAIN ),
			'description' =>	__( 'The area displays widgets on the contact page. Use the "Contact Us" template to create a contact page.', ADMINDOMAIN ),
		),
		
		'contact_page_sidebar' => array(
			'name' =>	__( 'Contact Page Sidebar', ADMINDOMAIN ),
			'description' =>	__( 'Display widgets inside the Contact page sidebar area.', ADMINDOMAIN ),
		),
		'author-page-sidebar' => array(
			'name' =>	__( 'Author Page Sidebar',ADMINDOMAIN ),
			'description' =>	__( 'This sidebar will show on individual author pages. To visit your author page visit a URL like this one: your-domain.com/author/your-username.',ADMINDOMAIN ),
		),
		'footer' => array(
			'name' =>	__( 'Footer', ADMINDOMAIN ),
			'description' =>	__( 'Displays widgets below the subsidiary area.',ADMINDOMAIN ),
		),
		'home-page-above-content-all-pages' => array(
			'name' =>	__( 'Above Main Content - All Pages', ADMINDOMAIN ),
			'description' =>	__( 'Widgets in this area will display on all pages below menu/map and above content in full width.',ADMINDOMAIN ),
			'before_widget' =>	'',
			'after_widget' =>	'',
		),
		'home-page-above-content' => array(
			'name' =>	__( 'Above Home page content', ADMINDOMAIN ),
			'description' =>	__( 'Widgets in this area will be shown above home page content in full width.',ADMINDOMAIN ),
			'before_widget' =>	'',
			'after_widget' =>	'',
		),
		'home-page-below-content' => array(
			'name' =>	__( 'Below Home page content', ADMINDOMAIN ),
			'description' =>	__( 'Widgets in this area will be shown in full width below home page content.',ADMINDOMAIN ),
			'before_widget' =>	'',
			'after_widget' =>	'',
		),
		'home-page-above-footer' => array(
			'name' =>	__( 'Inside Home page Footer', ADMINDOMAIN ),
			'description' =>	__( 'Widgets in this area will be shown in full width inside the home page footer.',ADMINDOMAIN ),
			'before_widget' =>	'',
			'after_widget' =>	'',
		)
		
	
	);
	
	$sidebars = array_merge($sidebars,$theme_sidebars,$plugin_sidebars);
	/* Return the sidebars. */
	
	return $sidebars;
}
/* Register widget areas. */
add_action( 'widgets_init', 'tmpldir_register_sidebars' );
/*
 Registers the supreme supported sidebars 
 */
function tmpldir_register_sidebars() {
	unregister_widget('WP_Widget_Text');
	/* Get the theme-supported sidebars. */
	$supported_sidebars = get_theme_support( 'supreme-core-sidebars' );
	/* If the theme doesn't add support for any sidebars, return. */
	if ( !is_array( $supported_sidebars[0] ) )
		return;
	/* Get the available core framework sidebars. */
	$core_sidebars = supreme_get_sidebars();
	/* Loop through the supported sidebars. */
	foreach ( $supported_sidebars[0] as $sidebar ) {
		/* Make sure the given sidebar is one of the core sidebars. */
		if ( isset( $core_sidebars[$sidebar] ) ) {
			/* Set up some default sidebar arguments. */
			$defaults = array(
				'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrap widget-inside">',
				'after_widget' => 	'</div></div>',
				'before_title' => 	'<h3 class="widget-title">',
				'after_title' => 	'</h3>'
			);
			/* Parse the sidebar arguments and defaults. */
			$args = wp_parse_args( $core_sidebars[$sidebar], $defaults );
			/* If no 'id' was given, use the $sidebar variable and sanitize it. */
			$args['id'] = ( isset( $args['id'] ) ? sanitize_key( $args['id'] ) : sanitize_key( $sidebar ) );
			/* Register the sidebar. */
			
			register_sidebar($args);
		}
	}
	
	if(is_plugin_active('woocommerce/woocommerce.php')){
		$args = array(
			'name'          => __( 'WooCommerce Sidebar', ADMINDOMAIN ),
			'id'            => 'supreme_woocommerce',
			'description'   => apply_filters('supreme_woo_commerce_sidebar_description',__('This sidebar is specially for woocommerce product pages, whichever widgets you drop here will be shown in woocommerce product pages.',ADMINDOMAIN)),
			'class'         => '',
			'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrap widget-inside">',
			'after_widget'  => '</div></div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>' );
		register_sidebar( $args );
	}
}

add_action('wp_head','templatic_customizer_style');
/* add customiser css in front style.css */
if(!function_exists('templatic_customizer_style')){
	function templatic_customizer_style(){
		
		if(file_exists(get_stylesheet_directory().'/functions/admin-style.php'))
		{
			include_once(get_stylesheet_directory().'/functions/admin-style.php'); // child theme css file
		}
		elseif(file_exists(get_template_directory().'/library/front-style.php')){
			include_once(get_template_directory().'/library/front-style.php');
		}
	}
}

/**
 * SEO and header functions
 */
/* Add <meta> elements to the <head> area. */
add_action( 'wp_head', 'tmpldir_meta_robots', 1 );
add_action( 'wp_head', 'tmpldir_meta_author', 1 );
add_action( 'wp_head', 'tmpldir_meta_copyright', 1 );
add_action( 'wp_head', 'tmpldir_meta_revised', 1 );
add_action( 'wp_head', 'tmpldir_meta_description', 1 );
add_action( 'wp_head', 'tmpldir_meta_keywords', 1 );
/**
 * Sets the default meta robots setting.  If private, don't send meta info to the header.  Runs the 
 * supreme_meta_robots filter hook at the end.
 *
 */
function tmpldir_meta_robots() {
	/* If the blog is set to private, don't show anything. */
	if ( !get_option( 'blog_public' ) )
		return;
	/* Create the HTML for the robots meta tag. */
	$robots = '<meta name="robots" content="index,follow" />' . "\n";
	echo apply_atomic( 'meta_robots', $robots );
}
/**
 * Generates the meta author.  For singular posts, it uses the post author's display name.  For user/author 
 * archives, it uses the user's display name.

 */
function tmpldir_meta_author() {
	/* Set an empty $author variable. */
	$author = '';
	/* Get the queried object. */
	$object = get_queried_object();
	/* If viewing a singular post, get the post author's display name. */
	if ( is_singular() )
		$author = get_the_author_meta( 'display_name', $object->post_author );
	/* If viewing a user/author archive, get the user's display name. */
	elseif ( is_author() )
		$author = get_the_author_meta( 'display_name', get_queried_object_id() );
	/* If an author was found, wrap it in the proper HTML and escape the author name. */
	if ( !empty( $author ) )
		$author = '<meta name="author" content="' . esc_attr( $author ) . '" />' . "\n";
	echo apply_atomic( 'meta_author', $author );
}
/**
 * Add the meta tag for copyright information to the header.  Singular posts display the date the post was 
 * published.  All other pages will show the current year. 
 */
function tmpldir_meta_copyright() {
	/* If viewing a singular post, get the post month and year. */
	if ( is_singular() )
		$date = get_the_time( esc_attr__( 'F Y', THEME_DOMAIN ) );
	/* For all other views, get the current year. */
	else
		$date = date( esc_attr__( 'Y', THEME_DOMAIN ) );
	/* Create the HTML for the copyright meta tag. */
	$copyright = '<meta name="copyright" content="' . sprintf( esc_attr__( 'Copyright (c) %1$s', THEME_DOMAIN ), $date ) . '" />' . "\n";
	echo apply_atomic( 'meta_copyright', $copyright );
}
/**
 * Add the revised meta tag on the singular view of posts.  This shows the last time the post was modified. 
 */
function tmpldir_meta_revised() {
	/* Create an empty $revised variable. */
	$revised = '';
	/* If viewing a singular post, get the last modified date/time to use in the revised meta tag. */
	if ( is_singular() )
		$revised = '<meta name="revised" content="' . get_the_modified_time( esc_attr__( 'l, F jS, Y, g:i a', THEME_DOMAIN ) ) . '" />' . "\n";
	echo apply_atomic( 'meta_revised', $revised );
}
/**
 * Generates the meta description based on either metadata or the description for the object.
 */
function tmpldir_meta_description() {
	/* Set an empty $description variable. */
	$description = '';
	/* If viewing the home/posts page, get the site's description. */
	if ( is_home() ) {
		$description = get_bloginfo( 'description' );
	}
	/* If viewing a singular post. */
	elseif ( is_singular() ) {
		/* Get the meta value for the 'Description' meta key. */
		$description = get_post_meta( get_queried_object_id(), 'Description', true );
		/* If no description was found and viewing the site's front page, use the site's description. */
		if ( empty( $description ) && is_front_page() )
			$description = get_bloginfo( 'description' );
		/* For all other singular views, get the post excerpt. */
		elseif ( empty( $description ) )
			$description = get_post_field( 'post_excerpt', get_queried_object_id() );
	}
	/* If viewing an archive page. */
	elseif ( is_archive() ) {
		/* If viewing a user/author archive. */
		if ( is_author() ) {
			/* Get the meta value for the 'Description' user meta key. */
			$description = get_user_meta( get_query_var( 'author' ), 'Description', true );
			/* If no description was found, get the user's description (biographical info). */
			if ( empty( $description ) )
				$description = get_the_author_meta( 'description', get_query_var( 'author' ) );
		}
		/* If viewing a taxonomy term archive, get the term's description. */
		elseif ( is_category() || is_tag() || is_tax() )
			$description = term_description( '', get_query_var( 'taxonomy' ) );
		/* If viewing a custom post type archive. */
		elseif ( is_post_type_archive() ) {
			/* Get the post type object. */
			$post_type = get_post_type_object( get_query_var( 'post_type' ) );
			/* If a description was set for the post type, use it. */
			if ( isset( $post_type->description ) )
				$description = $post_type->description;
		}
	}
	/* Format the meta description. */
	if ( !empty( $description ) )
		$description = '<meta name="description" content="' . str_replace( array( "\r", "\n", "\t" ), '', esc_attr( strip_tags( $description ) ) ) . '" />' . "\n";
	echo apply_atomic( 'meta_description', $description );
}
/**
 * Generates meta keywords/tags for the site.
 */
function tmpldir_meta_keywords() {
	/* Set an empty $keywords variable. */
	$keywords = '';
	/* If on a singular post and not a preview. */
	if ( is_singular() && !is_preview() ) {
		/* Get the queried post. */
		$post = get_queried_object();
		/* Get the meta value for the 'Keywords' meta key. */
		$keywords = get_post_meta( get_queried_object_id(), 'Keywords', true );
		/* If no keywords were found. */
		if ( empty( $keywords ) ) {
			/* Get all taxonomies for the current post type. */
			$taxonomies = get_object_taxonomies( $post->post_type );
			/* If taxonomies were found for the post type. */
			if ( is_array( $taxonomies ) ) {
				/* Loop through the taxonomies, getting the terms for the current post. */
				foreach ( $taxonomies as $tax ) {
					if ( $terms = get_the_term_list( get_queried_object_id(), $tax, '', ', ', '' ) )
						$keywords[] = $terms;
				}
				/* If keywords were found, join the array into a comma-separated string. */
				if ( !empty( $keywords ) )
					$keywords = join( ', ', $keywords );
			}
		}
	}
	/* If on a user/author archive page, check for user meta. */
	elseif ( is_author() ) {
		/* Get the meta value for the 'Keywords' user meta key. */
		$keywords = get_user_meta( get_query_var( 'author' ), 'Keywords', true );
	}
	/* If we have keywords, format for output. */
	if ( !empty( $keywords ) )
		$keywords = '<meta name="keywords" content="' . esc_attr( strip_tags( $keywords ) ) . '" />' . "\n";
	echo apply_atomic( 'meta_keywords', $keywords );
}

/**
	isplays the author's avatar and biography. This is typically shown on singular view pages only.
**/
function supreme_author_biography_($post){
	global $post;
	$theme_options = get_option(supreme_prefix().'_theme_settings');
	$supreme_author_bio_pages = @$theme_options['supreme_author_bio_pages'];
	 if(is_page() && $supreme_author_bio_pages){?>
	<div class="entry-author-meta">
		<a href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )); ?>" title="<?php echo esc_attr( get_the_author_meta( 'display_name' ) ); ?>" class="avatar-frame">
		<?php  if(get_user_meta(get_the_author_meta( 'ID' ),'profile_photo',true) != ""){
							echo '<img class="avatar" alt="avatar" src="'.get_user_meta(get_the_author_meta( 'ID' ),'profile_photo',true).'" alt="'.esc_attr( get_the_author_meta( 'display_name' ) ).'" title="'.esc_attr( get_the_author_meta( 'display_name' ) ).'" width="60" height="60"/>';
			}else{
				echo get_avatar(get_the_author_meta('ID'), '60', '', ''); 
			}?>
		</a>
		<p class="author-name">
			<?php  do_action('entry-author');  ?>
		</p>
		<p class="author-description">
			<?php the_author_meta('description'); ?>
		</p>
	</div>
	<!-- .entry-author -->
	<?php }	
		$theme_options = get_option(supreme_prefix().'_theme_settings');
		$supreme_author_bio_posts = @$theme_options['supreme_author_bio_posts'];
	if(is_single() && $supreme_author_bio_posts){ ?>
		<div class="entry-author-meta"> <a href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )); ?>" title="<?php echo esc_attr( get_the_author_meta( 'display_name' ) ); ?>" class="avatar-frame"><?php if(get_user_meta(get_the_author_meta( 'ID' ),'profile_photo',true) != ""){
								echo '<img class="avatar" alt="avatar" src="'.get_user_meta(get_the_author_meta( 'ID' ),'profile_photo',true).'" alt="'.esc_attr( get_the_author_meta( 'display_name' ) ).'" title="'.esc_attr( get_the_author_meta( 'display_name' ) ).'" width="60" height="60"/>';
								}else{ echo get_avatar(get_the_author_meta('ID'), '60', '', ''); }?></a>
			<p class="author-name">
				<?php do_action('entry-author'); ?>
			</p>
			<p class="author-description">
				<?php the_author_meta('description'); ?>
			</p>
		</div>
<!-- .entry-author -->
<?php }
}
?>