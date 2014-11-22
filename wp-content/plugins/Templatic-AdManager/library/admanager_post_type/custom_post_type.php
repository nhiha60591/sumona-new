<?php
//----------------------------------------------------------------------//
// Initiate the plugin to add custom post type
//----------------------------------------------------------------------//
add_action("init", "admanager_custom_post_type");
add_action("admin_init", "tmpl_addmanager_metabox");
function admanager_custom_post_type(){
	/* Register AD post type*/
	if(isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == CUSTOM_AD_POST_TYPE){
		$icon = AD_MANAGER_PLUGIN_URL.'images/favicon-active.png';
	}else{
		$icon = AD_MANAGER_PLUGIN_URL.'images/favicon-active.png';
	}
	$custom_post_type = CUSTOM_AD_POST_TYPE;

	
	/*Register Listing cutom post type */
	$post_arr_merge[$custom_post_type] = array(	'label' 			=> CUSTOM_MENU_AD_TITLE,
										'labels' 			=> array(	'name' 			=> CUSTOM_MENU_AD_NAME,
																'singular_name' 	=> CUSTOM_MENU_AD_SIGULAR_NAME,
																'add_new' 		=> CUSTOM_MENU_AD_ADD_NEW,
																'add_new_item' 	=> CUSTOM_MENU_AD_ADD_NEW_ITEM,
																'edit' 			=> CUSTOM_MENU_AD_EDIT,
																'edit_item' 		=> CUSTOM_MENU_AD_EDIT_ITEM,
																'new_item' 		=> CUSTOM_MENU_AD_NEW,
																'view_item'		=> CUSTOM_MENU_AD_VIEW,
																'search_items' 	=> CUSTOM_MENU_AD_SEARCH,
																'not_found' 		=> CUSTOM_MENU_AD_NOT_FOUND,
																'not_found_in_trash'=> CUSTOM_MENU_AD_NOT_FOUND_TRASH	),
										'public' 			=> true,
										'can_export'		=> true,
										'show_ui' 		=> true,
										'_builtin' 		=> false,
										'_edit_link' 		=> 'post.php?post=%d',
										'capability_type' 	=> 'post',
										'menu_icon' 		=> TEMPL_PLUGIN_URL.'/images/templatic-logo.png',
										'hierarchical' 	=> false,
										'rewrite' 		=> array("slug" => CUSTOM_AD_POST_TYPE),
										'query_var' 		=> CUSTOM_AD_POST_TYPE,
										'supports' 		=> array( 'title' ) ,
										'show_in_nav_menus'	=> true ,
									);
	$original = get_option('templatic_custom_post');
	if($original)	
		$post_arr_merge = array_merge($original,$post_arr_merge);
		
	ksort($post_arr_merge);
	update_option('templatic_custom_post',$post_arr_merge);
	/*END register listing custom post type */
	
	/*Finish the Register AD post type */	
}
/*
 * add filter for custom post type columns data
 */
add_filter("manage_edit-".CUSTOM_AD_POST_TYPE."_columns", "modify_ad_post_table_row",15);
function modify_ad_post_table_row($columns){
	if(is_plugin_active('wpml-translation-management/plugin.php'))	{
		$country_flag = '';
		$languages = icl_get_languages('skip_missing=0');
		if(!empty($languages)){
			foreach($languages as $l){
				if(!$l['active']) echo '<a href="'.$l['url'].'">';
				if(!$l['active']) $country_flag .= '<img src="'.$l['country_flag_url'].'" height="12" alt="'.$l['language_code'].'" width="18" />'.' ';
				if(!$l['active']) echo '</a>';
			}
		}
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Ad Title', PLUGIN_DOMAIN ),
			'icl_translations' => $country_flag,			
			'ad_type' => __( 'Ad Type',PLUGIN_DOMAIN ),
			'ad_height_width' => __( 'Ad Size',PLUGIN_DOMAIN ),
			'advertise_visited'=> __( 'Advertisement Visited',PLUGIN_DOMAIN )
			);
	}else{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Ad Title',PLUGIN_DOMAIN ),
			'ad_type' => __( 'Ad Type',PLUGIN_DOMAIN ),
			'ad_height_width' => __( 'Ad Size',PLUGIN_DOMAIN ),
			'advertise_visited'=> __( 'Advertisement Visited',PLUGIN_DOMAIN )
			);
	}
	return $columns;
}
/*
 * add action for add the custome post columns in listing custom post type
 */
add_action('manage_'.CUSTOM_AD_POST_TYPE.'_posts_custom_column','manage_ad_custom_columns_data');
function manage_ad_custom_columns_data($column){
	global $post,$wpdb;	
	$date_format = ( get_option('date_format') ) ? get_option('date_format') : "Y-m-d";
	switch ($column){
		case 'ad_type':
				echo get_post_meta($post->ID,'ad_type',true);
			break;
		case 'ad_height_width':
				echo get_post_meta($post->ID,'ad_height_width',true);
			break;
		case 'advertise_visited':
				echo get_post_meta($post->ID,'add_visited',true);
			break;
	}
}

function tmpl_addmanager_metabox(){
	/* Insert Locations Info heading into posts */
	global $wpdb;
 	 $locations_info = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'locations_info' and $wpdb->posts.post_type = 'custom_fields'"); 	 
	
	 if(count($locations_info) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Locations & Map',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'locations_info',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'post_type'=> $post_type_arr,
			'post_type_listing'=> CUSTOM_AD_POST_TYPE,
			'ctype'=>'heading_type',
			'htmlvar_name'=>'locations_info',
			'field_category' =>'all',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'sort_order' => '4',
			'listing_sort_order' => 4,
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'is_delete' => '0'
			);
		
		$post_id = wp_insert_post( $my_post );
		wp_set_post_terms($post_id,'1','category',true);
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global $sitepress;
			$current_lang_code= ICL_LANGUAGE_CODE;
			$default_language = $sitepress->get_default_language();	
			/* Insert wpml  icl_translations table*/
			$sitepress->set_element_language_details($post_id, $el_type='post_custom_fields', $post_id, $current_lang_code, $default_language );
			if(function_exists('wpml_insert_templ_post'))
				wpml_insert_templ_post($post_id,'custom_fields'); /* insert post in language */
		}
		//wp_set_post_terms($post_id,'1','category',true);
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	 }else{
		$post_type=get_post_meta($locations_info->ID, 'post_type',true );
		
		if(!strstr($post_type,CUSTOM_AD_POST_TYPE))
			update_post_meta($locations_info->ID, 'post_type',$post_type.','.CUSTOM_AD_POST_TYPE );
				
		update_post_meta($locations_info->ID, 'is_submit_field',1);					
		update_post_meta($locations_info->ID, 'post_type_'.CUSTOM_AD_POST_TYPE,CUSTOM_AD_POST_TYPE );					
		update_post_meta($locations_info->ID, 'post_type_'.CUSTOM_AD_POST_TYPE,CUSTOM_AD_POST_TYPE );
		if(get_post_meta($locations_info->ID,CUSTOM_AD_POST_TYPE.'_sort_order',true)){
			update_post_meta($locations_info->ID, CUSTOM_AD_POST_TYPE.'_sort_order',get_post_meta($locations_info->ID,CUSTOM_AD_POST_TYPE.'_sort_order',true) );
		}else{
			update_post_meta($locations_info->ID, CUSTOM_AD_POST_TYPE.'_sort_order',4 );
		}
		update_post_meta($locations_info->ID, CUSTOM_AD_POST_TYPE.'_sort_order',4 );
	 }
	 if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		/* Insert Post excerpt into posts */
		global $wpdb;
		$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_city_id' and $wpdb->posts.post_type = 'custom_fields'");
		if(count($post_content) != 0)
		{
			$post_type=get_post_meta($post_content->ID, 'post_type',true );
			if(!strstr($post_type,'admanager'))
				update_post_meta($post_content->ID, 'post_type',$post_type.',admanager' );
	
			update_post_meta($post_content->ID, 'post_type_admanager','admanager' );
			update_post_meta($post_category->ID, 'admanager_sort_order',2);
			update_post_meta($post_content->ID, CUSTOM_AD_POST_TYPE.'_heading_type','Locations & Map' );
		}
	}
}	


?>
