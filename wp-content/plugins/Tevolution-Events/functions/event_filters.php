<?php
add_action('init','event_get_posts');
function event_get_posts(){
	if(!is_admin()){
			if(isset($_REQUEST['search_template']) && $_REQUEST['search_template'] ==1){
				add_action('pre_get_posts','event_manager_pre_get_posts',12);
				add_action('pre_get_posts', 'advance_search_template_function',11);
			}else{
			add_action('pre_get_posts','event_manager_pre_get_posts');
			}
	}
}
function event_manager_pre_get_posts($query){
	global $wp_query,$wpdb;	
	if(is_single() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CUSTOM_POST_TYPE_EVENT){
		register_post_status( 'recurring', array(
		'label'                     => _x( 'Recurring', 'event' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Recurring <span class="count">(%s)</span>', 'Recurring <span class="count">(%s)</span>' ),
		) );
	}
	
	if(!is_author() && !is_search() && is_tax()){
		$current_term = $wp_query->get_queried_object();	
	}
	if((!is_search() && !is_author())&& isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CUSTOM_POST_TYPE_EVENT && is_archive() || (is_archive() && (isset($current_term->taxonomy) && ($current_term->taxonomy == CUSTOM_CATEGORY_TYPE_EVENT || $current_term->taxonomy == CUSTOM_TAG_TYPE_EVENT)))){
		if( isset($current_term) && ($current_term->taxonomy == CUSTOM_CATEGORY_TYPE_EVENT || $current_term->taxonomy == CUSTOM_TAG_TYPE_EVENT) ||  (is_archive() && !get_query_var('cat') && $query->query_vars['post_type'] == CUSTOM_POST_TYPE_EVENT))
		{
			$query->set('post_status', array('publish','inherit'));			
			add_filter('posts_where', 'event_manager_posts_where');
			add_filter('posts_orderby', 'event_manager_filter_orderby');
		}
	 }
	
	if(is_search() && isset($_REQUEST['t']) && $_REQUEST['t']=='event')
	{	
		add_filter('posts_orderby', 'searching_filter_orderby');
		add_filter('posts_where', 'event_searching_filter_where');
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			add_filter('posts_where', 'wpml_milewise_search_language');
		}
	}else
	{
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			remove_filter('posts_where', 'wpml_milewise_search_language');
		}
	}
	
	if(is_search() && (isset($_REQUEST['post_type']) && $_REQUEST['post_type']==CUSTOM_POST_TYPE_EVENT)){
		$query->set('post_status', array('publish','inherit'));
		add_filter('posts_where', 'event_manager_posts_where');
		add_filter('posts_orderby', 'event_manager_filter_orderby');
		
	}
	if(is_author() && (isset($_GET['sort']) && $_GET['sort'] == 'attending'))
	{
		$query->set('post_status', array('publish','inherit','recurring'));	
	}
	if(isset($_REQUEST['search_template']) && ($_REQUEST['post_type']==CUSTOM_POST_TYPE_EVENT || (is_array($_REQUEST['post_type']) && in_array(CUSTOM_POST_TYPE_EVENT,$_REQUEST['post_type'])))){
		add_filter('posts_orderby', 'event_manager_filter_orderby');
	}
	if(is_search() && (isset($_REQUEST['search_template']) && $_REQUEST['search_template']==1) && (isset($_REQUEST['post_type']) && $_REQUEST['post_type']=='event')){
		register_post_status( 'recurring', array(
		'label'                     => _x( 'Recurring', 'event' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Recurring <span class="count">(%s)</span>', 'Recurring <span class="count">(%s)</span>' ),
		) );		
		$query->set('post_status', array('publish','inherit','recurring'));
	}
	
}
function event_manager_posts_where($where){
	global $wpdb,$wp_query;	
	
	if( get_option('timezone_string')!="" ){
		date_default_timezone_set(get_option('timezone_string'));
	}	
	remove_action('templ_after_post_content','event_custom_fields'); // remove thi because custom fields comes and concat with current where condition	
	$event_manager_setting=get_option('event_manager_setting');
	$templatic_current_tab = isset($event_manager_setting['templatic-current_tab'])? $event_manager_setting['templatic-current_tab']:'';
	if(!isset($_REQUEST['etype']))			
	{	
		$_REQUEST['etype']=($templatic_current_tab == '')?'current':$templatic_current_tab;
		$to_day = date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')));
	}
	
	if(isset($_REQUEST['sortby']) && $_REQUEST['sortby']!=''){
		$where .= "  AND $wpdb->posts.post_title like '".$_REQUEST['sortby']."%'";
	}
	
	if(isset($_REQUEST['etype']) && $_REQUEST['etype']=='upcoming')
	{				
		$today = date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')));
		$where .= " AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='set_st_time' and date_format($wpdb->postmeta.meta_value,'%Y-%m-%d %H:%i:%s') >'".$today."')) ";
	}			
	elseif(isset($_REQUEST['etype']) && $_REQUEST['etype']=='past')
	{				
		$today = date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')));
		$where .= " AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='set_end_time' and date_format($wpdb->postmeta.meta_value,'%Y-%m-%d %H:%i:%s') < '".$today."')) ";
	}elseif($_REQUEST['etype']=='current')
	{
		$today = date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')));
		$where .= "  AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='set_st_time' and date_format($wpdb->postmeta.meta_value,'%Y-%m-%d %H:%i:%s') <='".$today."')) AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='set_end_time' and date_format($wpdb->postmeta.meta_value,'%Y-%m-%d %H:%i:%s') >= '".$today."')) ";
	}
	
	//$where .= "  AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='event_type' and $wpdb->postmeta.meta_value = 'Regular event'))";
	return $where;
}
/*
 * Function Name: directory_category_filter_orderby
 * Return: display the category page listing by ordering wise.
 */
function hide_past_event($where)
{
	global $wpdb;
	$today = date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')));
	$where .= " AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='set_end_time' and date_format($wpdb->postmeta.meta_value,'%Y-%m-%d %H:%i:%s') >= '".$today."')) ";
	return $where;
}

/*
 * Function Name: directory_category_filter_orderby
 * Return: display the category page listing by ordering wise.
 */
function event_manager_filter_orderby($orderby){
	global $wpdb,$wp_query;		
	if (isset($_REQUEST['event_sortby']) && ($_REQUEST['event_sortby'] == 'title_asc' || $_REQUEST['event_sortby'] == 'alphabetical') )
	{
		$orderby= "$wpdb->posts.post_title ASC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'title_desc' )
	{
		$orderby = "$wpdb->posts.post_title DESC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'date_asc' )
	{
		$orderby = "$wpdb->posts.post_date ASC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'date_desc' )
	{
		$orderby = "$wpdb->posts.post_date DESC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'stdate_low_high' )
	{
		$orderby = " (SELECT $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key like \"st_date\") ASC";		
	}
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'stdate_high_low' )
	{
		$orderby = " (select $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id = $wpdb->posts.ID and $wpdb->postmeta.meta_key like \"st_date\") DESC";
	}elseif(isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'random' )
	{
		$orderby = " (select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC,rand()";
	}elseif(isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'reviews' )
	{
		$orderby = 'DESC';
		$orderby = " $wpdb->posts.comment_count $orderby,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
		
	}
	elseif(isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'rating' )
	{
		$orderby = " (select $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id = $wpdb->posts.ID and $wpdb->postmeta.meta_key like \"average_rating\") DESC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	else
	{
		$orderby = " (SELECT $wpdb->postmeta.meta_value from $wpdb->postmeta where ($wpdb->posts.ID = $wpdb->postmeta.post_id) AND $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC, $wpdb->posts.post_date DESC";
	}
	return $orderby;
}
function search_sort_by_posts_where($where){
	global $wpdb,$wp_query;	
	if(isset($_REQUEST['sortby']) && $_REQUEST['sortby']!=''){
		$where .= "  AND $wpdb->posts.post_title like '".$_REQUEST['sortby']."%'";
	}
	return $where;
}
/*
 * Function Name: directory_listing_search_posts_where 
 * Return: find near my listing from current city
 */
function event_listing_search_posts_where($where){
	global $wpdb,$wp_query,$post,$current_cityinfo;		
	
	$lat =$current_cityinfo['lat'];
	$long = $current_cityinfo['lng'];	
	
	$miles_range=explode('-',$_REQUEST['miles_range']);
	$to_miles = trim($miles_range[0]);
	$miles = trim($miles_range[1]);
	$tbl_postcodes = $wpdb->prefix . "postcodes";		
	
	
	$where .= " AND ($wpdb->posts.ID in (SELECT post_id FROM  $tbl_postcodes WHERE truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) <= ".$miles." AND truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) >= ".$to_miles." ORDER BY truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) ASC))";	
	
	
	return $where;
}
function searching_filter_orderby($orderby) {
	global $wpdb;
	//$orderby = "  (select $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key like \"is_featured\") desc,$wpdb->posts.post_title ";
	$orderby = "  (select $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key like \"st_date\") ASC ";
	return $orderby;	
}
function event_searching_filter_where($where) {
	global $wpdb;
	$skw = trim($_REQUEST['skw']);
	$scat = trim($_REQUEST['scat']);
	$saddress = trim($_REQUEST['saddress']);
	$sdate = trim($_REQUEST['sdate']);	
	
	$where = '';
	$where = " AND $wpdb->posts.post_type in ('event') AND ($wpdb->posts.post_status = 'publish') ";
	if($skw)
	{
		$where .= " AND (($wpdb->posts.post_title LIKE \"%$skw%\") OR ($wpdb->posts.post_content LIKE \"%$skw%\")) ";
	}
	if($sdate)
	{
		//$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key like 'st_date' and pm.meta_value <= \"$sdate\" and pm.post_id in ((select pm2.post_id from $wpdb->postmeta pm2 where pm2.meta_key like 'end_date' and pm2.meta_value>=\"$sdate\"))) ";
		$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key like 'st_date' and pm.meta_value = \"$sdate\") ";
	}
	if($scat>0)
	{
		$where .= " AND  $wpdb->posts.ID in (select $wpdb->term_relationships.object_id from $wpdb->term_relationships join $wpdb->term_taxonomy on $wpdb->term_taxonomy.term_taxonomy_id=$wpdb->term_relationships.term_taxonomy_id and $wpdb->term_taxonomy.term_id=\"$scat\" ) ";
	}
	if($saddress)
	{
		$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key like 'address' and pm.meta_value like \"%$saddress%\") ";
	}
		
		$post_meta_info = $wpdb->get_results("SELECT $wpdb->posts.* FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) 
		INNER JOIN $wpdb->postmeta AS mt1 ON ($wpdb->posts.ID = mt1.post_id) INNER JOIN $wpdb->postmeta AS mt2 ON ($wpdb->posts.ID = mt2.post_id) WHERE 1=1 AND 
		$wpdb->posts.post_type = 'custom_fields' AND (($wpdb->posts.post_status = 'publish')) AND ($wpdb->postmeta.meta_key = 'sort_order' AND 
			(mt1.meta_key = 'is_search' AND CAST(mt1.meta_value AS CHAR) = '1') AND (mt2.meta_key = 'post_type' AND CAST(mt2.meta_value AS CHAR) LIKE '%event%') ) 
GROUP BY $wpdb->posts.ID ORDER BY $wpdb->postmeta.meta_value+0 ASC");
		$return_arr = array();
		 if($post_meta_info){
			
			foreach($post_meta_info as $post_meta_info_obj){	
				if($post_meta_info_obj->ctype){
					$options = explode(',',$post_meta_info_obj->option_values);
				}
				$custom_fields = array(
						"name"		=> get_post_meta($post_meta_info_obj->ID,"htmlvar_name",true),
						"label" 	=> $post_meta_info_obj->post_title,
						"htmlvar_name" 	=> get_post_meta($post_meta_info_obj->ID,"htmlvar_name",true),
						"default" 	=> get_post_meta($post_meta_info_obj->ID,"default_value",true),
						"type" 		=> get_post_meta($post_meta_info_obj->ID,"ctype",true),
						"desc"      => $post_meta_info_obj->post_content,
						"option_values" => get_post_meta($post_meta_info_obj->ID,"option_values",true),
						"is_require"  => get_post_meta($post_meta_info_obj->ID,"is_require",true),
						"is_active"  => get_post_meta($post_meta_info_obj->ID,"is_active",true),
						"show_on_listing"  => get_post_meta($post_meta_info_obj->ID,"show_on_listing",true),
						"show_on_detail"  => get_post_meta($post_meta_info_obj->ID,"show_on_detail",true),
						"validation_type"  => get_post_meta($post_meta_info_obj->ID,"validation_type",true),
						"style_class"  => get_post_meta($post_meta_info_obj->ID,"style_class",true),
						"extra_parameter"  => get_post_meta($post_meta_info_obj->ID,"extra_parameter",true),
						);
				if($options)
				{
					$custom_fields["options"]=$options;
				}
				$return_arr[get_post_meta($post_meta_info_obj->ID,"htmlvar_name",true)] = $custom_fields;
			}
		}
		$custom_metaboxes = $return_arr;	
	foreach($custom_metaboxes as $key=>$val) {
	$name = $key;
		if($_REQUEST[$name]){
			$value = $_REQUEST[$name];
			if($name == 'event_desc'){
				$where .= " AND ($wpdb->posts.post_content like \"%$value%\" )";
			} else if($name == 'event_name'){
				$where .= " AND ($wpdb->posts.post_title like \"%$value%\" )";
			}elseif($name=='st_date'){
				$where .= " AND";
				if(isset($_REQUEST['st_date']) && $_REQUEST['st_date']!='' && isset($_REQUEST['end_date']) && $_REQUEST['end_date']!=''){
					$where .= " (";
				}
				
				$where .= " ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='st_date' and ($wpdb->postmeta.meta_value BETWEEN  '".$_REQUEST['st_date']."' AND '".$_REQUEST['end_date']."' ))) ";
			}elseif($name=='end_date'){
				$where .= " OR ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='end_date' and ($wpdb->postmeta.meta_value BETWEEN  '".$_REQUEST['st_date']."' AND '".$_REQUEST['end_date']."' ))) ";
					
				if(isset($_REQUEST['st_date']) && $_REQUEST['st_date']!='' && isset($_REQUEST['end_date']) && $_REQUEST['end_date']!=''){
					$where .= " )";
				}
			}else {
				$where .= " AND ($wpdb->posts.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='$name' and ($wpdb->postmeta.meta_value like \"%$value%\" ))) ";
			}
		}
	}
	return $where;
}
function wpml_milewise_search_language($where)
{
	$language = ICL_LANGUAGE_CODE;
	$where .= " and t.language_code='".$language."'";
	return $where;
}
?>
