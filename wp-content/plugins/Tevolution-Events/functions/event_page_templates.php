<?php
/*	Return the Templates for Events post type . 
	- We need to write separately because if template available in child theme then give first priority to child theme template,
	Then check if template located in main theme then call it , else plug-in will call its own template. */
/*
	the function will return the archive page template.
*/

add_filter( "archive_template", "event_get_archive_page_template",13) ;

function event_get_archive_page_template($archive_template)
{
	global $wpdb,$wp_query,$post;	
	
	if(is_archive() && (get_post_type()==CUSTOM_POST_TYPE_EVENT || $wp_query->query_vars['post_type']==CUSTOM_POST_TYPE_EVENT))
	{	
		$template = '/archive-'.CUSTOM_POST_TYPE_EVENT.'.php';
		
		if ( file_exists(STYLESHEETPATH . $template)) {
			
			$archive_template = STYLESHEETPATH . $template;
			
		} else if ( file_exists(TEMPLATEPATH . $template) ) {
			
			$archive_template = TEMPLATEPATH . $template;
			
		}elseif( file_exists(TEVOLUTION_EVENT_DIR . 'templates'.$template)){
			
			$archive_template = TEVOLUTION_EVENT_DIR . 'templates'.$template;			
		}
	}
	
	return $archive_template;
}

/*
	Same Way This function will return the taxonomy/category page template.
*/ 
add_filter( "taxonomy_template", "event_get_taxonomy_page_template",13) ;
function event_get_taxonomy_page_template($taxonomy_template)
{	
	global $wpdb,$wp_query,$post;
	//fetch the current page texonomy
	$current_term = $wp_query->get_queried_object();		
	
	if($current_term->taxonomy == CUSTOM_CATEGORY_TYPE_EVENT )
	{
		$template = '/taxonomy-'.$current_term->taxonomy.'.php';
		
		/* load templates from available events */
		
		if ( file_exists(STYLESHEETPATH . $template)) {
			
			$taxonomy_template = STYLESHEETPATH .$template;
			
		} else if ( file_exists(TEMPLATEPATH . $template) ) {
			
			$taxonomy_template = TEMPLATEPATH . $template;
			
		}else{
			
			$taxonomy_template = TEVOLUTION_EVENT_DIR . 'templates'.$template;
			
		}
	}

     return $taxonomy_template;
}

/*
	Same Way This function will return the taxonomy/tags page template.
*/

function event_get_tag_page_template($tags_template)
{	
	global $wpdb,$wp_query,$post;
	//fetch the current page texonomy
	$current_term = $wp_query->get_queried_object();		
	if($current_term->taxonomy == CUSTOM_TAG_TYPE_EVENT )
	{
		$template = '/taxonomy-'.$current_term->taxonomy.'.php';
		
		/* load templates from available events */
		
		if ( file_exists(STYLESHEETPATH . $template)) {
			
			$tags_template = STYLESHEETPATH .$template;
			
		} else if ( file_exists(TEMPLATEPATH . $template) ) {
			
			$tags_template = TEMPLATEPATH . $template;
			
		}else{
			
			$tags_template = TEVOLUTION_EVENT_DIR . 'templates'.$template;
			
		}
	}
	return $tags_template;
}
add_filter( "taxonomy_template", "event_get_tag_page_template",13) ;
/*
	Event detail page template
*/ 
 
add_filter( "single_template", "event_get_single_template",13) ;
function event_get_single_template($single_template)
{	
	global $wpdb,$wp_query,$post;		
	if(get_post_type()==CUSTOM_POST_TYPE_EVENT)
	{
		$template = '/single-'.CUSTOM_POST_TYPE_EVENT.'.php';
		
		if ( file_exists(STYLESHEETPATH . $template)) {
			
			$single_template = STYLESHEETPATH . $template;			
			
		} else if ( file_exists(TEMPLATEPATH . $template) ) {
			
			$single_template = TEMPLATEPATH . $template;
			
		}else{
			
			$single_template = TEVOLUTION_EVENT_DIR . 'templates'.$template;
			
		}
	}	
	
     return $single_template;
}

/*
 * Search Page template for only tevolution custom post type
 *
 */
add_filter( "search_template",'event_get_search_template',11 );
function event_get_search_template($search_template)
{	
	global $wpdb,$wp_query,$post;		
	$post_type=get_query_var('post_type');
	
	$calendar_event = get_query_var('s');
	
	/* we add this condition because when we get the post type as an array in url ( specially on search page ) , the post type pass blank , so it's not call the taxonomy-search template , instead it call the default tevolution search template. */
    if(is_array($post_type) && count($post_type) == 1)
		{
			$post_type = $post_type[0];
		}
	
	/* fetch the tevolution post type */
	$custom_post_type = tevolution_get_post_type();	
	if($post_type==CUSTOM_POST_TYPE_EVENT  || $wp_query->query_vars['post_type']==CUSTOM_POST_TYPE_EVENT)
	{
					
		if ( file_exists(STYLESHEETPATH . '/event-search.php')) {
			
			$search_template = STYLESHEETPATH . '/event-search.php';			
			
		} else if ( file_exists(TEMPLATEPATH . '/event-search.php') ) {
			
			$search_template = TEMPLATEPATH . '/event-search.php';
			
		}else{
			$search_template = TEVOLUTION_EVENT_DIR. 'templates/event-search.php';
		}
	}	
	
     return $search_template;
}

/* Template for listing preview page */

add_action( 'init', 'event_custom_fields_preview' ,10);

function event_custom_fields_preview()
{
	if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=''){
		$_REQUEST['cur_post_type']=get_post_type($_REQUEST['pid']);
	}
	if((isset($_REQUEST['page']) && $_REQUEST['page'] == "preview")  && isset($_REQUEST['cur_post_type']) && $_REQUEST['cur_post_type']==CUSTOM_POST_TYPE_EVENT)
	{
		
		/* auto detect mobile devices */
		if (tmpl_wp_is_mobile()) {
			$template = '/mobile-single-'.CUSTOM_POST_TYPE_EVENT.'-preview.php';
		}else{
			$template = '/single-'.CUSTOM_POST_TYPE_EVENT.'-preview.php';
		}
		
		if ( file_exists(STYLESHEETPATH . $template)) {
			
			$single_template_preview = STYLESHEETPATH . $template;			
			
		} else if ( file_exists(TEMPLATEPATH . $template) ) {
			
			$single_template_preview = TEMPLATEPATH . $template;
			
		}else{
			
			$single_template_preview = TEVOLUTION_EVENT_DIR . 'templates'.$template;
			
		}		
		include($single_template_preview);
		exit;

	}
}

/* Template for listing detail page */

add_action( 'get_template_part_event-listing','single_event_listing_template_part',12,2);
function single_event_listing_template_part($slug,$name)
{
	
	if ( file_exists(STYLESHEETPATH . "/{$slug}-{$name}.php")) {
			
		$single_template = STYLESHEETPATH . "/{$slug}-{$name}.php";			
			
	}else if(file_exists(TEMPLATEPATH."/{$slug}-{$name}.php"))
	{
		$single_template = TEMPLATEPATH. "/{$slug}-{$name}.php";		
	}else{
		$single_template = TEVOLUTION_EVENT_DIR. "templates/{$slug}-{$name}.php";		
		include_once($single_template);	
	}	
}

add_action('event_before_categories_title','event_manager_listing_custom_field');
add_action('event_before_archive_title','event_manager_listing_custom_field');
add_action('event_after_search_title','event_manager_listing_custom_field');
function event_manager_listing_custom_field(){
	global $wpdb,$post,$htmlvar_name,$pos_title;	
	
	$cus_post_type = (isset($_REQUEST['action']))? CUSTOM_POST_TYPE_EVENT : get_post_type();	
	$heading_type = event_fetch_heading_post_type($cus_post_type);
	
	if(count($heading_type) > 0)
	{
		foreach($heading_type as $key=>$heading)
		{	
			$htmlvar_name[$key] = get_event_listing_customfields($cus_post_type,$heading,$key);//custom fields for custom post type..
		}
	}	
	return $htmlvar_name;
}
/*
	return array for event listing custom fields
 */
function get_event_listing_customfields($post_type,$heading='',$heading_key=''){
	global $wpdb,$post,$posttitle;
	$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
	$args = array( 'post_type' => 'custom_fields',
				'posts_per_page' => -1	,
				'post_status' => array('publish'),
				'meta_query' => array('relation' => 'AND',
								array(
									'key'     => 'post_type_'.$post_type.'',
									'value'   => $post_type,
									'compare' => '=',
									'type'    => 'text'
								),		
								array(
									'key'     => 'is_active',
									'value'   =>  '1',
									'compare' => '='
								),
								array(
									'key'     => 'show_on_listing',
									'value'   =>  '1',
									'compare' => '='
								),
								array(
									'key'     => 'heading_type',
									'value'   =>  array('basic_inf',$heading),
									'compare' => 'IN'
								)
							),
				'meta_key' => 'sort_order',
				'orderby' => 'meta_value',
				'order' => 'ASC'
		);
	
	remove_all_actions('posts_where');		
	$post_query = null;
	remove_action('pre_get_posts','event_manager_pre_get_posts');
	remove_action('pre_get_posts','directory_pre_get_posts',12);
	add_filter('posts_join', 'custom_field_posts_where_filter');
	$post_query = get_transient( '_tevolution_query_taxo'.trim($post_type).trim($heading_key).$cur_lang_code );	
	if ( false === $post_query && get_option('tevolution_cache_disable')==1 ) {
		$post_query = new WP_Query($args);		
		set_transient( '_tevolution_query_taxo'.trim($post_type).trim($heading_key).$cur_lang_code, $post_query, 12 * HOUR_IN_SECONDS );		
	}elseif(get_option('tevolution_cache_disable')==''){
		$post_query = new WP_Query($args);
	}
	remove_filter('posts_join', 'custom_field_posts_where_filter');
	
	$htmlvar_name='';
	if($post_query->have_posts())
	{
		while ($post_query->have_posts()) : $post_query->the_post();
			$ctype = get_post_meta($post->ID,'ctype',true);
			$post_name=get_post_meta($post->ID,'htmlvar_name',true);
			$style_class=get_post_meta($post->ID,'style_class',true);
			
			$htmlvar_name[$post_name] = array( 'type'=>$ctype,
												'label'=> $post_name,
												'style_class'=>$style_class
											  );
			$posttitle[] = $post->post_title;
		endwhile;
		wp_reset_query();
	}	
	return $htmlvar_name;
	
}
/*
 * Function Name: event_fetch_heading_post_type
 *
 */
function event_fetch_heading_post_type($post_type){
	
	global $wpdb,$post,$heading_title;
	$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
	remove_all_actions('posts_where');
	remove_action('pre_get_posts','event_manager_pre_get_posts');
	remove_action('pre_get_posts','directory_pre_get_posts',12);
	remove_action('pre_get_posts','location_pre_get_posts',12);
	add_filter('posts_join', 'custom_field_posts_where_filter');
	$heading_title = array();
	$args=
	array( 
	'post_type' => 'custom_fields',
	'posts_per_page' => -1	,
	'post_status' => array('publish'),
	'meta_query' => array(
		'relation' => 'AND',
		array(
			'key' => 'ctype',
			'value' => 'heading_type',
			'compare' => '=',
			'type'=> 'text'
		),
		array(
			'key' => 'post_type',
			'value' => $post_type,
			'compare' => 'LIKE',
			'type'=> 'text'
		)
		
	),
	'meta_key' => 'sort_order',	
	'orderby' => 'meta_value_num',
	'meta_value_num'=>'sort_order',
	'order' => 'ASC'
	);
	$post_query = null;
	remove_all_actions('posts_orderby');	
	
	$post_query = get_transient( '_tevolution_query_heading'.trim($post_type).$cur_lang_code );
	if ( false === $post_query && get_option('tevolution_cache_disable')==1) {
		$post_query = new WP_Query($args);
		set_transient( '_tevolution_query_heading'.trim($post_type).$cur_lang_code, $post_query, 12 * HOUR_IN_SECONDS );
	}elseif(get_option('tevolution_cache_disable')==''){
		$post_query = new WP_Query($args);
	}
	$post_meta_info = $post_query;
	
	if($post_meta_info){
		while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
		$otherargs=
		array( 
		'post_type' => 'custom_fields',
		'posts_per_page' => -1	,
		'post_status' => array('publish'),
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'is_active',
				'value' => '1',
				'compare' => '=',
				'type'=> 'text'
			),
			array(
				'key' => 'heading_type',
				'value' => $post->post_title,
				'compare' => '=',
				'type'=> 'text'
			)
		));
		
		
		
		$other_post_query = null;
		$htmlvar_name=get_post_meta(get_the_ID(),'htmlvar_name',true);
		$other_post_query = get_transient( '_tevolution_query_heading'.trim($post_type).trim($htmlvar_name).$cur_lang_code );
		if ( false === $other_post_query && get_option('tevolution_cache_disable')==1 ) {
			$other_post_query = new WP_Query($otherargs);
			set_transient( '_tevolution_query_heading'.trim($post_type).trim($htmlvar_name).$cur_lang_code, $other_post_query, 12 * HOUR_IN_SECONDS );
		}elseif(get_option('tevolution_cache_disable')==''){
			$other_post_query = new WP_Query($otherargs);
		}
		if(count( @$other_post_query->post) > 0)
		  {
			$heading_title[$htmlvar_name] = $post->post_title;
		  }
		endwhile;
		wp_reset_query();
	}
	remove_filter('posts_join', 'custom_field_posts_where_filter');
	return $heading_title;
}

/* get template for ajax response for list filter. First it will call theme's template. If it is not found, then it will call this pligin's "templates/content-event.php" and if both not found, then it will call Directory plugin's "templates/content-listing.php" */

add_action('tmpl_get_template_event','tmpl_ajax_event_template_part');
if(!function_exists('tmpl_ajax_event_template_part'))
{
	function tmpl_ajax_event_template_part($posttype)
	{
		if(file_exists(STYLESHEETPATH . '/content-'.$posttype.'.php'))
			{
				$ajax_template = STYLESHEETPATH . '/content-'.$posttype.'.php';
			}	
		else	
			{
				if(file_exists(TEVOLUTION_EVENT_DIR . 'templates/content-'.$posttype.'.php'))
					$ajax_template = TEVOLUTION_EVENT_DIR . 'templates/content-'.$posttype.'.php';
				else
					$ajax_template = TEVOLUTION_DIRECTORY_DIR . 'templates/content-listing.php';
			}	
		return include($ajax_template);
	}
}
?>