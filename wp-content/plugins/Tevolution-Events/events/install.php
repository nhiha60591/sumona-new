<?php 
/*
* Register event post type
*/
/* Define variables for Event post type*/
global $wpdb,$pagenow;

$event_post_type=0;
$custom_post_type = CUSTOM_POST_TYPE_EVENT;
$custom_cat_type = CUSTOM_CATEGORY_TYPE_EVENT;
$custom_tag_type = CUSTOM_TAG_TYPE_EVENT;
$custom_post_types_args = array();
if(function_exists('tevolution_get_post_type'))
{
	$post_type_array = tevolution_get_post_type();
}
else
{
	$post_type_array = get_post_types($custom_post_types_args,'objects');
}
/*reset events custom fields from tevolution custom fields section*/
if(isset($_POST['reset_custom_fields']) && (isset($_POST['custom_reset']) && $_POST['custom_reset']==1))
{
	update_option('event_custom_fields_insert','none');
}elseif(isset($_REQUEST['post_type_fields']) && $_REQUEST['post_type_fields'] =='event'){ 
	update_option('event_custom_fields_insert','none');
}
/*
 * This message will appear when user delete the event taxonomy , but event plugin is activated
 */
function tevolution_event_taxonomy_msg(){
	echo '<div id="message" class="error below-h2">';
	echo '<form action="" method="post">';
	echo "<p class='tevolution_desc'>".__('You have no event post type now but your event-manager is in active status so you can generate event post type again. ',EDOMAIN);
	echo '<input type="submit" name="event_post_type" value="'.__('Generate Event Taxonomy',EDOMAIN).'" class="button-primary">';
	echo '</p>';
	echo '<form>';
	echo '</div>';
}
/*set variable to insert event post type*/
if((isset($_REQUEST['page']) && $_REQUEST['page']=='custom_setup') && (isset($_POST['event_post_type'])) || (isset($_REQUEST['activated']) && $_REQUEST['activated']=='tevolution_event_manager')){	
	$event_post_type=1;
}
/*register events taxonomy*/
if($event_post_type==1 || (!in_array('event',$post_type_array) && get_option('tevolution_event_manager') !='Active')) 
{
	update_option('tevolution_event_manager','Active');
	
	$post_arr_merge[$custom_post_type] = 
				array(	'label' 			=> CUSTOM_MENU_SIGULAR_NAME,
						'labels' 			=> array(	
												'name' 				 =>  CUSTOM_MENU_SIGULAR_NAME,
												'singular_name'  	 =>  CUSTOM_MENU_SIGULAR_NAME,
												'menu_name'  	 =>  CUSTOM_MENU_TITLE,
												'add_new' 			 =>  CUSTOM_MENU_ADD_NEW,
												'add_new_item' 		 =>  CUSTOM_MENU_ADD_NEW_ITEM,
												'edit' 				 =>  CUSTOM_MENU_EDIT,
												'edit_item' 		 =>  CUSTOM_MENU_EDIT_ITEM,
												'new_item' 			 =>  CUSTOM_MENU_NEW,
												'view_item'			 =>  CUSTOM_MENU_VIEW,
												'search_items' 		 =>  CUSTOM_MENU_SEARCH,
												'not_found' 		 =>  CUSTOM_MENU_NOT_FOUND,
												'not_found_in_trash' =>  CUSTOM_MENU_NOT_FOUND_TRASH	),
						'public' 			=> true,
						'has_archive'       => true,
						'can_export'		=> true,
						'show_ui' 			=> true, /* SHOW UI IN ADMIN PANEL */
						'_builtin' 			=> false, /* IT IS A CUSTOM POST TYPE NOT BUILT IN */
						'_edit_link' 		=> 'post.php?post=%d',
						'capability_type' 	=> 'post',
						
						'menu_icon' 		=> TEMPL_PLUGIN_URL.'/images/templatic-logo.png',
						'hierarchical' 		=> false,
						'rewrite' 			=> array("slug" => "$custom_post_type"), /* PERMALINKS TO EVENT POST TYPE */
						'query_var' 		=> "$custom_post_type", /* THIS GOES TO WPQUERY SCHEMA */
						'supports' 			=> array(	'title', 'author','excerpt','thumbnail','comments','editor','trackbacks','custom-fields','revisions') ,
						'show_in_nav_menus'	=> true ,
						'slugs'				=> array("$custom_cat_type","$custom_tag_type"),
						'taxonomies'		=> array(CUSTOM_MENU_EVENT_SIGULAR_CAT,CUSTOM_MENU_TAG_LABEL_EVENT)
					
				);
				$original = get_option('templatic_custom_post');
				if($original)
				{
					$post_arr_merge = array_merge($original,$post_arr_merge);
				}
				
				ksort($post_arr_merge);
				update_option('templatic_custom_post',$post_arr_merge);
/* EOF - REGISTER EVENT POST TYPE */
				
/* REGISTER CUSTOM TAXONOMY FOR POST TYPE EVENT */
$original = array();
	$taxonomy_arr_merge[$custom_cat_type] = 
				 
				array (	"hierarchical" 	=> true, 
						"label" 		=> CUSTOM_MENU_EVENT_CAT_LABEL, 
						"post_type"		=> $custom_post_type,
						"post_slug"		=> $custom_post_type,
						'labels' 		=> array(	
												'name' 				=>  CUSTOM_MENU_EVENT_CAT_TITLE,
												'singular_name' 	=>  $custom_cat_type,
												'search_items' 		=>  CUSTOM_MENU_EVENT_CAT_SEARCH,
												'popular_items' 	=>  CUSTOM_MENU_EVENT_CAT_SEARCH,
												'all_items' 		=>  CUSTOM_MENU_EVENT_CAT_ALL,
												'parent_item' 		=>  CUSTOM_MENU_EVENT_CAT_PARENT,
												'parent_item_colon' =>  CUSTOM_MENU_EVENT_CAT_PARENT_COL,
												'edit_item' 		=>  CUSTOM_MENU_EVENT_CAT_EDIT,
												'update_item'		=>  CUSTOM_MENU_EVENT_CAT_UPDATE,
												'add_new_item' 		=>  CUSTOM_MENU_EVENT_CAT_ADDNEW,
												'new_item_name' 	=>  CUSTOM_MENU_EVENT_CAT_NEW_NAME,	), 
						'public' 		=> true,
						'show_ui' 		=> true,
						'rewrite' 		 => array("slug" => "$custom_cat_type"), /* PERMALINKS TO EVENT POST TYPE */
				);
				$original = get_option('templatic_custom_taxonomy');
				if($original)
				{
					$taxonomy_arr_merge = array_merge($original,$taxonomy_arr_merge);
				}
				//register_taxonomy($custom_cat_type,array($custom_post_type),$taxonomy_arr_merge[$custom_cat_type]);
				ksort($taxonomy_arr_merge);
				update_option('templatic_custom_taxonomy',$taxonomy_arr_merge);

				$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');
				if(empty($tevolution_taxonomy_marker)){
					update_option('tevolution_taxonomy_marker',array($custom_cat_type=>'enable'));
				}else{
					update_option('tevolution_taxonomy_marker',array_merge($tevolution_taxonomy_marker,array($custom_cat_type=>'enable')));
				}
/*EOF - REGISTER CUSTOM TAXONOMY FOR POST TYPE EVENT */
	/* REGISTER TAG FOR POST TYPE EVENT */
	$tag_arr_merge = array();
	$tag_arr_merge[$custom_tag_type] =
				array(	"hierarchical" 	=> false, 
						"label" 		=> CUSTOM_MENU_TAG_LABEL_EVENT, 
						"post_type"		=> $custom_post_type,
						"post_slug"		=> $custom_post_type,
						'labels' 		=> array(	
												'name' 				=>  CUSTOM_MENU_TAG_TITLE_EVENT,
												'singular_name' 	=>  $custom_tag_type,
												'search_items' 		=>  CUSTOM_MENU_TAG_SEARCH_EVENT,
												'popular_items' 	=>  CUSTOM_MENU_TAG_POPULAR_EVENT,
												'all_items' 		=>  CUSTOM_MENU_TAG_ALL_EVENT,
												'parent_item' 		=>  CUSTOM_MENU_TAG_PARENT_EVENT,
												'parent_item_colon' =>  CUSTOM_MENU_TAG_PARENT_COL_EVENT,
												'edit_item' 		=>  CUSTOM_MENU_TAG_EDIT_EVENT,
												'update_item'		=>  CUSTOM_MENU_TAG_UPDATE_EVENT,
												'add_new_item' 		=>  CUSTOM_MENU_TAG_ADD_NEW_EVENT,
												'new_item_name' 	=>  CUSTOM_MENU_TAG_NEW_ADD_EVENT,	),  
						'public' 		=> true,
						'show_ui' 		=> true,
						'rewrite' 		 => array("slug" => "$custom_tag_type"), /* PERMALINKS TO EVENT POST TYPE */
				);
				$original = get_option('templatic_custom_tags');
				if($original)
				{
					$tag_arr_merge = array_merge($original,$tag_arr_merge);
				}
				ksort($tag_arr_merge);
				update_option('templatic_custom_tags',$tag_arr_merge);
	
}
/*
 * display event taxonomy generate when event taxonomy not exists 
 */
$post_type_arra=get_option('templatic_custom_post',@$post_arr_merge);
if(!array_key_exists('event',$post_type_arra)){	
	add_action('tevolution_custom_taxonomy_msg','tevolution_event_taxonomy_msg');
}
if((isset($_REQUEST['ctab']) && ((($_REQUEST['ctab']=='custom_fields' && (isset($_POST['reset_custom_fields']) && $_REQUEST['post_type_fields'] == CUSTOM_POST_TYPE_EVENT)) || $_REQUEST['page']=='templatic_system_menu')) || $pagenow=='themes.php' || $pagenow=='plugins.php' ) && (!in_array('listing',$post_type_array) && get_option('event_custom_fields_insert')!='inserted') || (isset($_REQUEST['activated']) && $_REQUEST['activated']=='tevolution_event_manager')) 
{ 
	update_option('event_custom_fields_insert','inserted');
	/*Reset tevolution Custom Fields */
	if(isset($_POST['reset_custom_fields']) && ((isset($_POST['custom_reset']) && $_POST['custom_reset']==1) || (isset($_REQUEST['posttype_fld_reset']) && $_REQUEST['posttype_fld_reset'] !='')))
	{
		if(isset($_REQUEST['posttype_fld_reset']) && $_REQUEST['posttype_fld_reset'] =='event'){  
			$args=array('post_type'      => 'custom_fields',
			  'posts_per_page' => -1	,
			  'post_status'    => array('publish'),
			  'meta_key'       => 'post_type_'.$custom_post_type,
			  'meta_value'     => $custom_post_type,
			  'order'          => 'ASC'
			);
		}elseif(isset($_REQUEST['custom_reset']) && $_REQUEST['custom_reset'] !=''){
		/* Reset the event custom fields - when click on reset ALL custom fields */
			$args=array('post_type'    => 'custom_fields',
			  'posts_per_page' => -1	,
			  'post_status'    => array('publish'),
			  'order'          => 'ASC'
			);
		}
		$custom_field = new WP_Query($args);
		//echo $custom_field->request; exit;
		$array_default_flds = array('post_title','post_content','category','post_images','post_excerpt','basic_inf');
		if($custom_field):
			while ($custom_field->have_posts()) : 
				global $post;
				$custom_field->the_post();
				if(!in_array($post->post_name,$array_default_flds )){
					wp_delete_post( get_the_ID(), true);
				}
			endwhile;
		endif;
	}
	/*Finish the reset all custom fields */
	/* Here You have to pass "$exclude_post_types" same variable in other plugins as well.	*/
		$exclude_post_type = apply_filters('reset_exclude_post_types',array());
		$cus_pos_type = get_option("templatic_custom_post");
		$post_type_arr='post,';
		$heading_post_type_arr='post,';
		if($cus_pos_type && count($cus_pos_type) > 0)
		{
			foreach($cus_pos_type as $key=> $_cus_pos_type)
			{
				if(!empty($exclude_post_type)){
					if(!in_array($key,$exclude_post_type)){
						$post_type_arr .= $key.",";
					}
				}else{
					$post_type_arr .= $key.",";
				}
				$heading_post_type_arr .= $key.",";
			}
		}
		$post_type_arr = substr($post_type_arr,0,-1);
		$heading_post_type_arr = substr($heading_post_type_arr,0,-1);
	
	 /* Insert Post heading type into posts */
	 $taxonomy_name = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_title = '[#taxonomy_name#]' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($taxonomy_name) != 0)
	 {
		$post_type=get_post_meta($taxonomy_name->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($taxonomy_name->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($taxonomy_name->ID, 'post_type_event','event' );
		update_post_meta($taxonomy_name->ID, 'taxonomy_type_ecategory','ecategory' );
		
		if(get_post_meta($taxonomy_name->ID,'event_sort_order',true)){
			update_post_meta($taxonomy_name->ID, 'event_sort_order',get_post_meta($taxonomy_name->ID,'event_sort_order',true) );
		}else{
			update_post_meta($taxonomy_name->ID, 'event_sort_order',1 );
		}
	 
	 }
	
	 /* Insert Post Category into posts */
	 $post_category = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'category' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_category) != 0)
	 {
		$post_type=get_post_meta($post_category->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_category->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_category->ID, 'post_type_event','event' );
		update_post_meta($post_category->ID, 'taxonomy_type_ecategory','ecategory' );
	
		
		if(get_post_meta($post_category->ID,'event_sort_order',true)){
			update_post_meta($post_category->ID, 'event_sort_order',get_post_meta($post_category->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_category->ID, 'event_sort_order',2 );
		}
		
		if(get_post_meta($post_category->ID,'event_heading_type',true)){
			update_post_meta($post_category->ID, 'event_heading_type',get_post_meta($post_category->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_category->ID, 'event_heading_type','[#taxonomy_name#]' );
		}
	 }
	
	 /* Insert Post title into posts */
	 $post_title = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_title' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_title) != 0)
	 {
		$post_type=get_post_meta($post_title->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_title->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_title->ID, 'post_type_event','event' );
		update_post_meta($post_title->ID, 'taxonomy_type_ecategory','ecategory' );
		update_post_meta($post_title->ID, 'show_in_event_search','1' );
		
		if(get_post_meta($post_title->ID,'event_sort_order',true)){
			update_post_meta($post_title->ID, 'event_sort_order',get_post_meta($post_title->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_title->ID, 'event_sort_order',3 );
		}
		
		if(get_post_meta($post_title->ID,'event_heading_type',true)){
			update_post_meta($post_title->ID, 'event_heading_type',get_post_meta($post_title->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_title->ID, 'event_heading_type','[#taxonomy_name#]' );
		} 
	 }
	

	/* Insert Locations Info heading into posts - Heading type - 4  */
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
			'post_type_event'=> $custom_post_type,
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
			'event_sort_order' => '4',
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	}else{ 
			$post_type=get_post_meta($locations_info->ID, 'post_type',true );
			
		    if(!strstr($post_type,'event'))
				update_post_meta($locations_info->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($locations_info->ID, 'post_type_event','event' );					
			update_post_meta($locations_info->ID, 'taxonomy_type_ecategory','ecategory' );
			
			if(get_post_meta($locations_info->ID,'event_sort_order',true)){
				update_post_meta($locations_info->ID, 'event_sort_order',get_post_meta($locations_info->ID,'event_sort_order',true) );
			}else{
				update_post_meta($locations_info->ID, 'event_sort_order',4 );
			}
	}
	
	
	/* Insert multiplicity selection field  into posts */

	$post_city_id = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_city_id' and $wpdb->posts.post_type = 'custom_fields'");
	 if(count($post_city_id) == 0)
	 {
			$my_post = array(
				 'post_title' => 'Multi City',
				 'post_content' => '',
				 'post_status' => 'publish',
				 'post_author' => 1,
				 'post_name' => 'post_city_id',
				 'post_type' => "custom_fields",
				);
			$post_meta = array(
				'heading_type' => 'Locations & Map',
				'event_heading_type'=>'Locations & Map',
				'ctype'=>'multicity',
				'post_type'=> $post_type_arr,
				'post_type_property'=> $custom_post_type,
				'htmlvar_name'=>'post_city_id',
				'field_category' =>'all',
				'sort_order' => '5',
				'event_sort_order' => '5',
				'is_active' => '1',
				'is_submit_field' => '1',
				'is_require' => '0',
				'show_on_page' => 'both_side',
				'show_in_column' => '0',
				'show_on_listing' => '0',
				'is_edit' => 'true',
				'show_on_detail' => '0',
				'is_search'=>'1',
				'show_in_email'  =>'1',
				'is_delete' => '0'
				);
			$post_id = wp_insert_post( $my_post );
			wp_set_post_terms($post_id,'1','category',true);
			foreach($post_meta as $key=> $_post_meta)
			{
				add_post_meta($post_id, $key, $_post_meta);
			}
			
			$post_types=get_option('templatic_custom_post');
			$posttype='post,';
			foreach($post_types as $key=>$val){
				$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $key,'public'   => true, '_builtin' => true ));	
				$posttype.=$key.',';
				update_post_meta($post_id, 'post_type_'.$key,$key );
				update_post_meta($post_id, 'taxonomy_type_'.$taxonomies[0],$taxonomies[0] );
				update_post_meta($post_id, $key.'_sort_order',get_post_meta($post_id, $key.'_sort_order',true));
			}
			update_post_meta($post_id, 'post_type_post','post' );
			update_post_meta($post_id, 'taxonomy_type_category','category');
			update_post_meta($post_id, 'post_type',substr($posttype,0,-1));
			update_post_meta($post_id, 'show_on_detail','1' );
			
			update_post_meta($post_id, 'post_type_event','event' );
			$post_type=get_post_meta($post_id, 'post_type',true );
				if(!strstr($post_type,'event'))
					update_post_meta($post_id, 'post_type',$post_type.',event' );
			update_post_meta($post_id, 'taxonomy_type_ecategory','ecategory' );
			update_post_meta($post_id, 'show_on_detail','1' );
			update_post_meta($post_id, 'show_in_event_search','1' );
	}else{
		
		 update_post_meta($post_city_id->ID, 'show_on_detail','1' );
		 
		 $post_type=get_post_meta($post_city_id->ID, 'post_type',true );
			if(!strstr($post_type,'event'))
				update_post_meta($post_city_id->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_city_id->ID, 'post_type_event','event' );
			update_post_meta($post_city_id->ID, 'taxonomy_type_ecategory','ecategory' );
			update_post_meta($post_city_id->ID, 'show_on_detail','1' );
			update_post_meta($post_city_id->ID, 'show_in_event_search','1' );
			if(get_post_meta($post_city_id->ID,'event_sort_order',true)){
				update_post_meta($post_city_id->ID, 'event_sort_order',get_post_meta($post_city_id->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_city_id->ID, 'event_sort_order',8 );
			}
			
			if(get_post_meta($post_content->ID,'event_heading_type',true) && get_post_meta($post_content->ID,'event_heading_type',true) !='[#taxonomy_name#]'){
				update_post_meta($post_city_id->ID, 'event_heading_type',get_post_meta($post_city_id->ID,'event_heading_type',true) );
			}else{
				update_post_meta($post_city_id->ID, 'event_heading_type','Locations & Map' );
			}
		 
	}

	/* Insert Address fields into locations and map tab  */

	$post_address = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'address' and $wpdb->posts.post_type = 'custom_fields'");
 	if(count($post_address) == 0)
	{
		$my_post = array(
			 'post_title' => 'Location',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'address',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Locations & Map',
			'event_heading_type'=>'Locations & Map',
			'post_type'=> $post_type_arr,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'geo_map',
			'htmlvar_name'=>'address',
			'field_category' =>'all',
			'is_require' => '1',
			'sort_order' => '6',
			'event_sort_order' => '6',
			'is_active' => '1',
			'is_submit_field' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '1',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'show_on_success' => '1',
			'is_search'=>'1',
			'show_in_email'  =>'1',
			'field_require_desc' => 'Please Enter Address',
			'validation_type' => 'require',
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	}else{ 
		  $post_type=get_post_meta($post_address->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($post_address->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_address->ID, 'post_type_event','event' );
			update_post_meta($post_address->ID, 'taxonomy_type_ecategory','ecategory' );
			update_post_meta($post_address->ID, 'is_search','1' );
			update_post_meta($post_address->ID, 'show_in_event_search','1' );
			
			if(get_post_meta($post_address->ID,'event_sort_order',true)){
				update_post_meta($post_address->ID, 'event_sort_order',get_post_meta($post_address->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_address->ID, 'event_sort_order',6 );
			}
			update_post_meta($post_address->ID, 'event_sort_order',6 );
			if(get_post_meta($post_content->ID,'event_heading_type',true) && get_post_meta($post_content->ID,'event_heading_type',true) !='[#taxonomy_name#]'){
				update_post_meta($post_address->ID, 'event_heading_type',get_post_meta($post_address->ID,'event_heading_type',true) );
			}else{
				update_post_meta($post_address->ID, 'event_heading_type','Locations & Map');
			}
		
	}	

	 
	/* Insert Post Google Map View into posts */
	$post_map_view = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'map_view' and $wpdb->posts.post_type = 'custom_fields'");
 	if(count($post_map_view) == 0)
	{
		$my_post = array(
			 'post_title' => '',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'map_view',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Locations & Map',
			'event_heading_type'=>'Locations & Map',
			'post_type'=> $post_type_arr,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'radio',
			'htmlvar_name'=>'map_view',
			'field_category' =>'all',
			'sort_order' => '7',
			'event_sort_order' => '7',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'0',
			'show_on_success' => '0',
			'option_title'=>'Road Map,Terrain Map,Satellite Map,Street View',
			'option_values' => 'Road Map,Terrain Map,Satellite Map,Street Map',
			'default_value' =>'Road Map',
			
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
	}else{
		$post_type=get_post_meta($post_map_view->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($post_map_view->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_map_view->ID, 'post_type_event','event' );
			update_post_meta($post_map_view->ID, 'taxonomy_type_ecategory','ecategory' );
			if(get_post_meta($post_map_view->ID,'event_sort_order',true)){
				update_post_meta($post_map_view->ID, 'event_sort_order',get_post_meta($post_map_view->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_map_view->ID, 'event_sort_order',7 );
			}
			
			if(get_post_meta($post_content->ID,'event_heading_type',true) && get_post_meta($post_content->ID,'event_heading_type',true) !='[#taxonomy_name#]'){
				update_post_meta($post_map_view->ID, 'event_heading_type',get_post_meta($post_map_view->ID,'event_heading_type',true) );
			}else{
				update_post_meta($post_map_view->ID, 'event_heading_type','Locations & Map');
			}
	}	
	
	
	/* Heading type - Event Informations */
		 
	$event_info = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'event_info' and $wpdb->posts.post_type = 'custom_fields'"); 	 

	if(count($event_info) == 0){
		$my_post = array(
			 'post_title' => 'Event Information',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'event_info',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'heading_type',
			'htmlvar_name'=>'event_info',
			'field_category' =>'all',
			'sort_order' => '8',
			'event_sort_order' => '8',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'is_delete' => '0'
			);
		wp_set_post_terms($post_id,'1','category',true);
		$post_id = wp_insert_post( $my_post );
		
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global $sitepress;
			$current_lang_code= ICL_LANGUAGE_CODE;
			$default_language = $sitepress->get_default_language();	
			/* Insert wpml  icl_translations table*/
			$sitepress->set_element_language_details($post_id, $el_type='post_custom_fields',$post_id, $current_lang_code, $default_language );
			if(function_exists('wpml_insert_templ_post'))
				wpml_insert_templ_post($post_id,'custom_fields'); /* insert post in language */
		}
		
		//wp_set_post_terms($post_id,'1','category',true);
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	}else{
		$post_type=get_post_meta($event_info->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($event_info->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($event_info->ID, 'post_type_event','event' );
			update_post_meta($event_info->ID, 'taxonomy_type_ecategory','ecategory' );
			if(get_post_meta($event_info->ID,'event_sort_order',true) ){
				update_post_meta($event_info->ID, 'event_sort_order',get_post_meta($event_info->ID,'event_sort_order',true) );
			}else{
				update_post_meta($event_info->ID, 'event_sort_order',8);
			}
	}

	 
	 /* Insert Consider this event as into posts */
	 $post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'event_type' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Consider this event as',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'event_type',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'radio',
			'htmlvar_name'=>'event_type',
			'field_category' =>'all',
			'sort_order' => '9',
			'event_sort_order' => '9',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'is_search'=>'0',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'show_in_email'  =>'1',
			'option_title' => 'Regular event, Recurring event',
			'option_values' => 'Regular event, Recurring event',
			'field_require_desc' => 'Please Select Event Type',
			'validation_type' => 'require'
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	
	}else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_content->ID, 'post_type_event','event' );
			update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
			
			if(get_post_meta($post_content->ID,'event_sort_order',true)){
				update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_content->ID, 'event_sort_order',9);
			}
			
			if(!get_post_meta($post_content->ID,'event_heading_type',true) || get_post_meta($post_content->ID,'event_heading_type',true) =='[#taxonomy_name#]' ){
				update_post_meta($post_content->ID, 'event_heading_type','Event Information');

			}else{
				update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
			}
	}	
	 
	 /* Insert Post Event Start Date into posts */
	 $post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'st_date' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Start Date',
			 'post_content' => 'Enter Event Start Date. eg.: 2013-09-05',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'st_date',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'date',
			'htmlvar_name'=>'st_date',
			'field_category' =>'all',
			'is_require' => '1',
			'sort_order' => '10',
			'event_sort_order' => '10',
			'is_active' => '1',
			'is_submit_field' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '1',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'show_on_success' => '1',
			'is_search'=>'1',
			'show_in_email'  =>'1',
			'field_require_desc' => 'Please Enter Start Date',
			'validation_type' => 'require'
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	 }else{
			$post_type=get_post_meta($post_content->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_content->ID, 'post_type_event','event' );
			update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
			
			if(get_post_meta($post_content->ID,'event_sort_order',true)){
				update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_content->ID, 'event_sort_order',10 );
			}
			if(get_post_meta($post_content->ID,'event_heading_type',true)){
				update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
			}else{
				update_post_meta($post_content->ID, 'event_heading_type','Event Information' );
			}
	 }	
	 
	 /* Insert Post Event End Date into posts */
	 $post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'end_date' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'End Date',
			 'post_content' => 'Enter Event End Date. eg.: 2013-09-05',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'end_date',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'date',
			'htmlvar_name'=>'end_date',
			'field_category' =>'all',
			'sort_order' => '11',
			'event_sort_order' => '11',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '1',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'1',
			'show_in_email'  =>'1',
			'field_require_desc' => 'Please enter the End date that occurs after the Start date.',
			'validation_type' => 'require',
			'show_on_success' => '1'
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	 }else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_content->ID, 'post_type_event','event' );
			update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
			
			if(get_post_meta($post_content->ID,'event_sort_order',true)){
				update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_content->ID, 'event_sort_order',11);
			}
			if(get_post_meta($post_content->ID,'event_heading_type',true)){
				update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
			}else{
				update_post_meta($post_content->ID, 'event_heading_type','Event Information' );
			}
	 }	
	 
	 /* Insert Post Start Time into posts */
	 $post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'st_time' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Start Time',
			 'post_content' => 'Enter event start time. eg. 16:25 (Follows 24 hrs format)',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'st_time',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'st_time',
			'field_category' =>'all',
			'is_require' => '1',
			'sort_order' => '12',
			'event_sort_order' => '12',
			'is_active' => '1',
			'is_submit_field' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '1',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'show_on_success' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'field_require_desc' => 'Please Enter Start Time',
			'validation_type' => 'require'
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	 }else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',12);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Event Information');
		}
	 }	

	 
	/* Insert End Time into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'end_time' and $wpdb->posts.post_type = 'custom_fields'");
 	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'End Time',
			 'post_content' => 'Enter event end time. eg. 18:25 (Follows 24 hrs format)',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'end_time',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'end_time',
			'field_category' =>'all',
			'is_require' => '1',
			'sort_order' => '13',
			'event_sort_order' => '13',
			'is_active' => '1',
			'is_submit_field' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '1',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'show_on_success' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'field_require_desc' => 'Please Enter End time',
			'validation_type' => 'require',
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
	
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
	 }else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',13);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Event Information');
		}
	}	
	
	/* Insert Post excerpt into posts */
	$post_excerpt = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_excerpt' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_excerpt) != 0)
	{
		$post_type=get_post_meta($post_excerpt->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_excerpt->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_excerpt->ID, 'post_type_event','event' );
		update_post_meta($post_excerpt->ID, 'taxonomy_type_ecategory','ecategory' );
		update_post_meta($post_excerpt->ID, 'is_submit_field','0' ); 
		
		if(get_post_meta($post_excerpt->ID,'event_sort_order',true)){
			update_post_meta($post_excerpt->ID, 'event_sort_order',get_post_meta($post_mls_no->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_excerpt->ID, 'event_sort_order',14);
		}
		
		if(get_post_meta($post_excerpt->ID,'event_heading_type',true)){
			update_post_meta($post_excerpt->ID, 'event_heading_type',get_post_meta($post_mls_no->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_excerpt->ID, 'event_heading_type','Event Information');
		}	
	 
	}
	
	/* Insert Post content into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_content' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) != 0)
	{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
		update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
			
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );

		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_mls_no->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',15);
		}

		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_mls_no->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Event Information');
		}	
	 
	}
	
	/* Registration fees */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'reg_fees' and $wpdb->posts.post_type = 'custom_fields'");
 	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Registration Fees',
			 'post_content' => 'Enter Registration Fees, in USD eg.: $50',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'reg_fees',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'reg_fees',
			'field_category' =>'all',
			'sort_order' => '16',
			'event_sort_order' => '16',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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

		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	}else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',16);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Event Information');
		}
	
	}
	
	
	/* Insert How to Register into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'reg_desc' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'How to Register?',
			 'post_content' => 'Short description for registration process',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'reg_desc',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'Event Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'texteditor',
			'htmlvar_name'=>'reg_desc',
			'field_category' =>'all',
			'sort_order' => '17',
			'event_sort_order' => '17',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	 }else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',17);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Event Information');
		}
	 }	

	 
	/* Insert Tag Keyword */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_tags' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Tag Keyword',
			 'post_content' => 'Tags are short keywords, with no space within. Up to 40 characters only.',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'post_tags',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Event Information',
			'event_heading_type'=>'[#taxonomy_name#]',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'post_tags',
			'field_category' =>'all',
			'sort_order' => '18',
			'event_sort_order' => '18',
			'is_active' => '0',
			'is_require' => '0',
			'show_on_page' => 'user_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'false',
			'show_on_detail' => '1',
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
		foreach($post_meta as $key=> $_post_meta)
		{
			add_post_meta($post_id, $key, $_post_meta);
		}
 	}else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',18);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Event Information');
		}
	}

	
	/* Insert Post Label of field heading type into posts */
	$field_label = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'field_label' and $wpdb->posts.post_type = 'custom_fields'"); 	 

	if(count($field_label) == 0)
	{
		$my_post = array(
		 'post_title' => 'Label of Field',
		 'post_content' => '',
		 'post_status' => 'publish',
		 'post_author' => 1,
		 'post_name' => 'field_label',
		 'post_type' => "custom_fields",
		);
		$post_meta = array(
		'post_type'=> $custom_post_type,
		'post_type_event'=> $custom_post_type,
		'ctype'=>'heading_type',
		'htmlvar_name'=>'field_label',
		'field_category' =>'all',
		'sort_order' => '19',
		'event_sort_order' => '19',
		'is_active' => '1',
		'is_submit_field' => '0',
		'is_require' => '0',
		'show_on_page' => 'both_side',
		'show_in_column' => '0',
		'show_on_listing' => '0',
		'is_edit' => 'true',
		'show_on_detail' => '1',
		'is_search'=>'0',
		'show_in_email'  =>'1',
		'is_delete' => '0'
		);
		wp_set_post_terms($post_id,'1','category',true);
		$post_id = wp_insert_post( $my_post );

		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global $sitepress;
			$current_lang_code= ICL_LANGUAGE_CODE;
			$default_language = $sitepress->get_default_language();	
			/* Insert wpml  icl_translations table*/
			$sitepress->set_element_language_details($post_id, $el_type='post_custom_fields',$post_id, $current_lang_code, $default_language );
			if(function_exists('wpml_insert_templ_post'))
				wpml_insert_templ_post($post_id,'custom_fields'); /* insert post in language */
		}

		//wp_set_post_terms($post_id,'1','category',true);
		foreach($post_meta as $key=> $_post_meta)
		{
			add_post_meta($post_id, $key, $_post_meta);
		}
 	}else{
		$post_type=get_post_meta($field_label->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
		update_post_meta($field_label->ID, 'post_type',$post_type.',event' );
			
		update_post_meta($field_label->ID, 'post_type_event','event' );
		update_post_meta($field_label->ID, 'taxonomy_type_ecategory','ecategory' );

		if(get_post_meta($field_label->ID,'event_sort_order',true)){
			update_post_meta($field_label->ID, 'event_sort_order',get_post_meta($field_label->ID,'event_sort_order',true) );
		}else{
			update_post_meta($field_label->ID, 'event_sort_order',19);
		}
	}
	
	 
	 /*Insert post image */
	 $post_images = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_images' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_images) != 0)
	 {
		$post_type=get_post_meta($post_images->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
		update_post_meta($post_images->ID, 'post_type',$post_type.',event' );
			
		update_post_meta($post_images->ID, 'post_type_event','event' );
		update_post_meta($post_images->ID, 'taxonomy_type_ecategory','ecategory' );

		if(get_post_meta($post_images->ID,'event_sort_order',true)){
			update_post_meta($post_images->ID, 'event_sort_order',get_post_meta($post_images->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_images->ID, 'event_sort_order',19);
		}

		if(get_post_meta($post_images->ID,'event_heading_type',true)){
			update_post_meta($post_images->ID, 'event_heading_type',get_post_meta($post_images->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_images->ID, 'event_heading_type','Label of Field');
		}	
		update_post_meta($post_images->ID, 'event_sort_order',19);
		update_post_meta($post_images->ID, 'event_heading_type','Label of Field');
	}	
	
	/* Insert Contact Info heading into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'contact_info' and $wpdb->posts.post_type = 'custom_fields'"); 	 

	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Contact Information',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'contact_info',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'heading_type',
			'htmlvar_name'=>'contact_info',
			'field_category' =>'all',
			'sort_order' => '16',
			'event_sort_order' => '16',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
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
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',16);
		}
	
	}		
	
	/* Insert Phone into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'phone' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Phone',
			 'post_content' => 'You can enter phone number,cell phone number etc.',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'phone',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Contact Information',
			'event_heading_type'=>'Contact Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'phone',
			'field_category' =>'all',
			'sort_order' => '17',
			'event_sort_order' => '17',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
			);
		$post_id = wp_insert_post( $my_post );
		wp_set_post_terms($post_id,'1','category',true);
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global $sitepress;
			$current_lang_code= ICL_LANGUAGE_CODE;
			$default_language = $sitepress->get_default_language();	
			/* Insert wpml  icl_translations table*/
			$sitepress->set_element_language_details($post_id, $el_type='post_custom_fields',$post_id, $current_lang_code, $default_language );
			if(function_exists('wpml_insert_templ_post'))
				wpml_insert_templ_post($post_id,'custom_fields'); /* insert post in language */
		}
		//wp_set_post_terms($post_id,'1','category',true);
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	}else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',17);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Contact Information');
		}
	}	
	 
	/* Insert Email into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'email' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Email',
			 'post_content' => 'Enter your email address.',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'email',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Contact Information',
			'event_heading_type'=>'Contact Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'email',
			'field_category' =>'all',
			'sort_order' => '18',
			'event_sort_order' => '18',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '1',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'validation_type' => 'email',
			'show_on_success' => '1',
			'field_require_desc' => 'Please provide your email address',
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
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',18);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Contact Information');
		}
	}	
	 
	/* Insert Website into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'website' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Website',
			 'post_content' => 'Enter website URL. eg.: http://myplace.com',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'website',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Contact Information',
			'event_heading_type'=>'Contact Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'website',
			'field_category' =>'all',
			'sort_order' => '19',
			'event_sort_order' => '19',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',19);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Contact Information');
		}
	}	
	 
	/* Insert Twitter into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'twitter' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Twitter',
			 'post_content' => 'Enter twitter URL. eg.: http://twitter.com/myplace',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'twitter',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Contact Information',
			'event_heading_type'=>'Contact Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'twitter',
			'field_category' =>'all',
			'sort_order' => '20',
			'event_sort_order' => '20',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',20);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Contact Information');
		}
	 }	
	 
	 /* Insert Facebook into posts */
	 $post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'facebook' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Facebook',
			 'post_content' => 'Enter facebook URL. eg.: http://facebook.com/myplace',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'facebook',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Contact Information',
			'event_heading_type'=>'Contact Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'facebook',
			'field_category' =>'all',
			'sort_order' => '21',
			'event_sort_order' => '21',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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
		foreach($post_meta as $key=> $_post_meta)
		{
			add_post_meta($post_id, $key, $_post_meta);
		}
 		
	 }else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',21);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Contact Information');
		}
	 }	
	 
	/* Insert Goggle Plus into posts */
	$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'google_plus' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Google+ ',
			 'post_content' => 'Enter Goolge+ URL. eg.: https://plus.google.com/',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'google_plus',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Contact Information',
			'event_heading_type'=>'Contact Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'google_plus',
			'field_category' =>'all',
			'sort_order' => '22',
			'event_sort_order' => '22',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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
		foreach($post_meta as $key=> $_post_meta)
		{
			add_post_meta($post_id, $key, $_post_meta);
		}
 		
	}else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',22);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Contact Information');
		}
	}	
	 /* Insert Organizer Information Post into posts */
 	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'org_info' and $wpdb->posts.post_type = 'custom_fields'"); 	 
	
	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Organizer Information',
			 'post_content' => '',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'org_info',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'heading_type',
			'htmlvar_name'=>'org_info',
			'field_category' =>'all',
			'sort_order' => '23',
			'event_sort_order' => '23',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
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
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	}else{
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',23);
		}
			
	}	
	
	/* Insert Organizer Name into posts */
	$organizer_name = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_name' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($organizer_name) == 0)
	{
		$my_post = array(
			 'post_title' => 'Organizer Name',
			 'post_content' => 'Who is organizing this event? mention the name.',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_name',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'organizer_name',
			'field_category' =>'all',
			'sort_order' => '24',
			'event_sort_order' => '24',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_delete' => '0',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1'
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	}else{
		$post_type=get_post_meta($organizer_name->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($organizer_name->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($organizer_name->ID, 'post_type_event','event' );
		update_post_meta($organizer_name->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($organizer_name->ID,'event_sort_order',true)){
			update_post_meta($organizer_name->ID, 'event_sort_order',get_post_meta($organizer_name->ID,'event_sort_order',true) );
		}else{
			update_post_meta($organizer_name->ID, 'event_sort_order',24);
		}
			
		if(get_post_meta($organizer_name->ID,'event_heading_type',true)){
			update_post_meta($organizer_name->ID, 'event_heading_type',get_post_meta($organizer_name->ID,'event_heading_type',true) );
		}else{
			update_post_meta($organizer_name->ID, 'event_heading_type','Organizer Information');
		}
	}
	 /* Insert Organizer Email into posts */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_email' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Organizer Email',
			 'post_content' => 'Mention the email Id of an organizer',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_email',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'organizer_email',
			'field_category' =>'all',
			'sort_order' => '25',
			'event_sort_order' => '25',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'show_on_success' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'validation_type' => 'email',
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	}else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',25);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	/* Insert organizer Logo into posts */
	$post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_logo' and $wpdb->posts.post_type = 'custom_fields'");
	if(count($post_content) == 0)
	{
		$my_post = array(
			 'post_title' => 'Select Logo',
			 'post_content' => "Upload organizer's company logo (if any)",
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_logo',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'upload',
			'htmlvar_name'=>'organizer_logo',
			'field_category' =>'all',
			'sort_order' => '26',
			'event_sort_order' => '26',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1',
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 	
	}else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',26);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	 /* Insert Organizer Address into posts */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_address' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Organizer Address',
			 'post_content' => "Specify organizer's contact address",
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_address',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'organizer_address',
			'field_category' =>'all',
			'sort_order' => '27',
			'event_sort_order' => '27',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1',
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
		
	}else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',27);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	 /* Insert Organizer Contact Info. into posts */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_contact' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Organizer Contact Info.',
			 'post_content' => 'Add any additional contact information. (e.g. Fax no., mail Id etc)',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_contact',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'organizer_contact',
			'field_category' =>'all',
			'sort_order' => '28',
			'event_sort_order' => '28',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1',
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
 		
	 }else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',28);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	 /* Insert Organizer Website into posts */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_website' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Organizer Website',
			 'post_content' => 'Specify the website of an organizer (If any).',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_website',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'organizer_website',
			'field_category' =>'all',
			'sort_order' => '29',
			'event_sort_order' => '29',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1',
			'is_delete' => '0'
			);
		$post_id = wp_insert_post( $my_post );
		wp_set_post_terms($post_id,'1','category',true);
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global $sitepress;
			$current_lang_code= ICL_LANGUAGE_CODE;
			$default_language = $sitepress->get_default_language();	
			/* Insert wpml  icl_translations table*/
			$sitepress->set_element_language_details($post_id, $el_type='post_custom_fields',$post_id, $current_lang_code, $default_language );
			if(function_exists('wpml_insert_templ_post'))
				wpml_insert_templ_post($post_id,'custom_fields'); /* insert post in language */
		}
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
	 }else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',29);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	 /* Insert Organizer Mobile into posts */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_mobile' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Organizer Mobile',
			 'post_content' => 'Specify mobile number of an organizer',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_mobile',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'text',
			'htmlvar_name'=>'organizer_mobile',
			'field_category' =>'all',
			'sort_order' => '30',
			'event_sort_order' => '30',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1',
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
	 }else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',30);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	 /* Insert Organizer Description into posts */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'organizer_desc' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'About the Organizers',
			 'post_content' => 'Write a short note about the organizer. It will appear on the detail page of this event by default on your site.',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'organizer_desc',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Organizer Information',
			'event_heading_type'=>'Organizer Information',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'texteditor',
			'htmlvar_name'=>'organizer_desc',
			'field_category' =>'all',
			'sort_order' => '31',
			'event_sort_order' => '31',
			'is_active' => '1',
			'is_submit_field' => '0',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
			'is_search'=>'0',
			'show_in_email'  =>'1',
			'show_on_success' => '1',
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
		
		foreach($post_meta as $key=> $_post_meta)
		 {
			add_post_meta($post_id, $key, $_post_meta);
		 }
	}else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',31);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Organizer Information');
		}	 
	}
	 /*Insert post image */
	 $post_images = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_images' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_images) != 0)
	 {
		 $post_type=get_post_meta($post_images->ID, 'post_type',true );
		 	if(!strstr($post_type,'event'))
				update_post_meta($post_images->ID, 'post_type',$post_type.',event' );
					
			update_post_meta($post_images->ID, 'is_submit_field',1);
			update_post_meta($post_images->ID, 'post_type_event','event' );
			update_post_meta($post_images->ID, 'taxonomy_type_ecategory','ecategory' );
			update_post_meta($post_content->ID, 'is_submit_field',1);
			if(get_post_meta($post_images->ID,'event_sort_order',true) ){
				update_post_meta($post_images->ID, 'event_sort_order',get_post_meta($post_images->ID,'event_sort_order',true) );
			}else{
				update_post_meta($post_images->ID, 'event_sort_order',18);
			}
			if(get_post_meta($post_images->ID,'event_heading_type',true)){
				update_post_meta($post_images->ID, 'event_heading_type',get_post_meta($post_images->ID,'event_heading_type',true) );
			}else{
				update_post_meta($post_images->ID, 'event_heading_type','Label of Field');
			}
			update_post_meta($post_images->ID, 'event_heading_type','Label of Field'); 
		 
	 }
	 /* Insert Video into posts */
	 $post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'video' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Video',
			 'post_content' => 'Add video code here',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'video',
			 'post_type' => "custom_fields",
			);
		$post_meta = array(
			'heading_type' => 'Label of Field',
			'event_heading_type'=>'Label of Field',
			'post_type'=> $custom_post_type,
			'post_type_event'=> $custom_post_type,
			'ctype'=>'oembed_video',
			'htmlvar_name'=>'video',
			'field_category' =>'all',
			'sort_order' => '32',
			'event_sort_order' => '32',
			'is_active' => '1',
			'is_submit_field' => '1',
			'is_require' => '0',
			'show_on_page' => 'both_side',
			'show_in_column' => '0',
			'show_on_listing' => '0',
			'is_edit' => 'true',
			'show_on_detail' => '1',
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
		
		foreach($post_meta as $key=> $_post_meta)
		{
			add_post_meta($post_id, $key, $_post_meta);
		}
 	}else{
	 
		$post_type=get_post_meta($post_content->ID, 'post_type',true );
		if(!strstr($post_type,'event'))
			update_post_meta($post_content->ID, 'post_type',$post_type.',event' );
				
		update_post_meta($post_content->ID, 'post_type_event','event' );
		update_post_meta($post_content->ID, 'ctype','oembed_video' );
		update_post_meta($post_content->ID, 'is_submit_field',1);
		update_post_meta($post_content->ID, 'taxonomy_type_ecategory','ecategory' );
		if(get_post_meta($post_content->ID,'event_sort_order',true)){
			update_post_meta($post_content->ID, 'event_sort_order',get_post_meta($post_content->ID,'event_sort_order',true) );
		}else{
			update_post_meta($post_content->ID, 'event_sort_order',32);
		}
			
		if(get_post_meta($post_content->ID,'event_heading_type',true)){
			update_post_meta($post_content->ID, 'event_heading_type',get_post_meta($post_content->ID,'event_heading_type',true) );
		}else{
			update_post_meta($post_content->ID, 'event_heading_type','Label of Field');
		}	 
	}
	
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'user-attending-event' and $wpdb->posts.post_type = 'page'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'People attending this event',
			 'post_content' => '[event_attend_user_list]',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'user-attending-event',
			 'post_type' => "page",
			 'comment_status' => 'closed'
			);		
		$post_id = wp_insert_post( $my_post );		
		add_post_meta($post_id, '_wp_page_template', 'default');
		update_option('event_attending_user_page',$post_id);		
 	}
	 
	 /*Set the event attending page */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'event-attending-list' and $wpdb->posts.post_type = 'page'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Event Attending List',
			 'post_content' => '[event-user-attend-list]',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_name' => 'event-attending-list',
			 'post_type' => "page",
			);
		$post_meta = array(
			'_wp_page_template' => 'default',
			'_edit_last'        => '1',
			
			);
		$post_id = wp_insert_post( $my_post );
		
		foreach($post_meta as $key=> $_post_meta)
		{
			add_post_meta($post_id, $key, rtrim($_post_meta));
		}
		$templatic_setting=get_option('templatic_settings');
		update_option('templatic_settings',array_merge($templatic_setting,array('event_user_attend_list'=>$post_id)));
	 }
	 
	 /*Set the Submit Event page */
	 $post_content = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'submit-event' and $wpdb->posts.post_type = 'page'");
 	 if(count($post_content) == 0)
	 {
		$my_post = array(
			 'post_title' => 'Submit Event',
			 'post_content' => "[submit_form post_type='event']",
			 'post_status' => 'publish',
			 'comment_status' => 'closed',
			 'post_author' => 1,
			 'post_name' => 'submit-event',
			 'post_type' => "page",
			);		
		$post_id = wp_insert_post( $my_post );	
		update_post_meta($post_id, '_wp_page_template','default' );
		update_post_meta($post_id, 'submit_post_type','event' );
		update_post_meta($post_id, 'is_tevolution_submit_form','1' );
	 }
	
	$page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts where post_name like 'event-calendar' and post_type='page' and post_status='publish'");
	if(!$page_id){
		$sql = "insert into ".$wpdb->posts." set post_author=1, post_title='Event Calendar',comment_status='closed',ping_status='closed',post_content='[calendar_event]', post_name='event-calendar',post_type='page'";
		$wpdb->query($sql);
		$last_post_id = $wpdb->get_var("SELECT max(ID) FROM $wpdb->posts");
		$guid = home_url()."/?p=$last_post_id";
		$guid_sql = "update $wpdb->posts set guid=\"$guid\" where ID=\"$last_post_id\"";
		$wpdb->query($guid_sql);
	}
	 
}
if(is_admin()){
	$results = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type='monetization_package'");
	if(count($results)!=0 && !get_option('update_event_price')){
		foreach($results as $res){			
			$package_post_type=get_post_meta($res->ID,'package_post_type',true);
			$package_post_type.=$custom_post_type.',';
			update_post_meta($res->ID,'package_post_type',substr($package_post_type,0,-1));
			
		}
		update_option('update_event_price',1);
	}
}
?>