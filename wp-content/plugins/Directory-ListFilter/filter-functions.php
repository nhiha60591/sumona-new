<?php
/*////////////////////////////
 *
 * all functions of plugin
 *
/*///////////////////////////
/*
	Name : add_styles_scripts
    Desc : add css and scripts 
*/

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'filter_activate' )
{
	add_action( 'admin_notices', 'admin_plugin_activation_notice_handler' );
}
/*
 * Name : admin_plugin_activation_notice_handler
  Desc : return the message on successfully plugin activation
 */
function admin_plugin_activation_notice_handler($post) {
	echo '<div class="updated"><p>';_e('List Filter plugin has been activated, plugin has added a new widget to your site named List Filter. You can use this widget in the following widget areas: Listing Category Page Sidebar, Listing Tag Page Sidebar and Primary Sidebar(for search results page).',SF_DOMAIN);echo "</p></div>";
}
	
/*
	Name : tmpl_show_the_field_in
    Desc : Create field for add as filter in add/edit custom field form 
*/
function tmpl_show_the_field_in($post_field_id)
{ /* if field is image uploader then it caan not ne set as a filter */
	$ctype = @get_post_meta($post_field_id,"ctype",true);
	if( $ctype != 'heading_type' && $ctype != 'post_categories' && $ctype != 'image_uploader' && $ctype != 'coupon_uploader' && $ctype != 'oembed_video'  && $ctype != 'upload' && $ctype != 'multicity' )
	{
	?>
	<div id="sf_field">
		<input <?php echo $checked;?> type="checkbox" name="show_as_filter" id="show_as_filter" value="1" <?php if( @get_post_meta($post_field_id,"show_as_filter",true)=='1'){ echo 'checked="checked"';}?>/>&nbsp;<label for="show_as_filter" ><?php echo __('List Filter widget',SF_DOMAIN);?></label><br />
	</div>	
	<?php
	}
}


/*
	Name : tmpl_set_field_order
    Desc : set order for custom field in this widget when custom filed is update
*/
function tmpl_set_field_order($post_id)
{
	if((isset($_REQUEST['page']) && $_REQUEST['page'] == 'custom_fields'))
	{
		update_post_meta($post_id,'filter_sort_order',$_POST['sort_order']);
	}
	
	return $_POST['show_as_filter'] = (isset($_POST['show_as_filter']))? $_POST['show_as_filter'] :0;	
}


/*
Name : tmpl_get_post_id_by_meta_key_and_value
Description : gets the post id from meta-key and value.
*/

if(!function_exists('tmpl_get_post_id_by_meta_key_and_value')){
	function tmpl_get_post_id_by_meta_key_and_value($key, $value) {
              
		global $wpdb;

		 if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			   $language = ICL_LANGUAGE_CODE;
			   $join .= " {$ljoin} JOIN {$wpdb->prefix}icl_translations t ON {$wpdb->prefix}postmeta.post_id = t.element_id			
				AND t.element_type IN ('post_custom_fields') JOIN {$wpdb->prefix}icl_languages l ON t.language_code=l.code AND l.active=1 AND t.language_code='".$language."'";
			   }else{
						$join ='';
				   }
  
	$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` $join WHERE meta_key='$key' AND meta_value='$value'");


              	
	if (is_array($meta) && !empty($meta) && isset($meta[0])) {
		$meta = $meta[0];
	}		
	if (is_object($meta)) {
		return $meta->post_id;
	}
	else {
		return false;
	}
               
                
	}
}

function add_selected_filters()
{
	echo '<div class="filter_list_wrap clearfix">';
	echo '<div id="selectedFilters" class="filter-options clearfix">';
		echo '<div class="flit-opt-cols1">';
			echo '<a class="clear-filter-link" id="clear_filter" href="javascript:void(0)">';
			_e('Clear All Filters',SF_DOMAIN);
			echo '</a>';
		echo '</div>';
	echo '</div>';
	echo '<div class="search-widin-result">';
		echo '<input type="text" placeholder="'.__('Search within results',SF_DOMAIN).'" filterdisplayname="'.__('Search within results',SF_DOMAIN).'" value="" name="search_within" id="search_within">';
	echo '</div>';
	echo '</div>';
	//echo '<div id="selectedFilters" class="filter-options"></div>';
}

/* search with widget fields */
add_action('wp_ajax_search_filter','tmpl_advance_search_filter');
add_action('wp_ajax_nopriv_search_filter','tmpl_advance_search_filter');

/* search in map with widget fields */
add_action('wp_ajax_nopriv_search_filter_map','tmpl_search_filter_map');
add_action('wp_ajax_search_filter_map','tmpl_search_filter_map');

/*
 * function: tmpl_search_filter_map
 * Description: get location on map during filtering
 */
function tmpl_search_filter_map(){

	global $wp_query,$wpdb,$current_cityinfo,$wp_query;
	$pids = array();
	if(isset($_REQUEST['sr_post_type']) && $_REQUEST['sr_post_type'] != '')
		$posttype = explode(',',$_REQUEST['sr_post_type']);
	elseif(isset($_REQUEST['post_type']) && is_array($_REQUEST['post_type']) && count($_REQUEST['post_type']) > 0 && $_REQUEST['search_from'] == '')
		$posttype = $_REQUEST['post_type'];
	else	
		$posttype = (isset($_REQUEST['search_from']) && $_REQUEST['search_from'] != '') ? $_REQUEST['search_from'] : $_REQUEST['posttype'];/* gets the post type */
		
		
	// get page type		
		$list_id='loop_'.$post_type.'_taxonomy';
		$page_type= 'taxonomy';
	
			
	$queried_object = get_queried_object();   /* currently-queried object */
	$term_id = $queried_object->term_id;  

	
	//$wp_query->set('post_type', array($posttype));
	$per_page=get_option('posts_per_page'); /* post per page */
	
	 /* get page number. if there is no page number then set page number to 1 */
	$paged = (isset($_REQUEST['page_num']) && $_REQUEST['page_num'] != '') ? $_REQUEST['page_num'] : 1;
	
	if(isset($_REQUEST['term_id']) && $_REQUEST['term_id']!=""){  /* if term id exist then show result according to term */
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $posttype ,'public'   => true, '_builtin' => true ));	
		$args=array(
				 'post_type'      => $posttype,
				 'posts_per_page' => $per_page,
				 'post_status'    => 'publish',
				 'paged' 		  => $paged,
				 'tax_query'      => array(
										  array(
											 'taxonomy' => $taxonomies[0],
											 'field'    => 'id',
											 'terms'    => explode(',',$_REQUEST['term_id']),
											 'operator' => 'IN'
										  )
									   ),
				);
		
	}else{      /* else show all result */
		$args=array(
				 'post_type'      => $posttype,
				 'posts_per_page' => $per_page,
				 'paged' 		  => $paged,
				 'post_status'    => 'publish',
				 );
	}
	
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		add_filter('posts_join','tevolution_favourites_post_join',12);
		/* if search from current city is enabled from widget then search from current city, otherwise all city result will be shown */
		if(isset($_REQUEST['search_filter_in_city']) && $_REQUEST['search_filter_in_city'] == 1)
			add_filter('posts_where', 'tmpl_location_multicity_where');
	}
	
	/* When search using search widget/template the filters apply from following hook */ 
	add_filter( 'posts_where' , 'tmpl_search_filter_where'); 
	/* filter for order by  */
	add_filter('posts_orderby', 'tmpl_search_filter_orderby');

	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		add_filter('posts_where', 'tmpl_search_language');
	}
	
	if(isset($_REQUEST['radius_type']) && $_REQUEST['radius_type']== strtolower('Kilometer')){
		add_filter('posts_where', 'tmpl_search_filter_nearby');
	}
	/* get all fields values from filter form and check if it is empty or not */
	$all_values = array();
	foreach($_REQUEST as $key=>$value)
	{
		if($key != 'action' && $key != 'posttype' && $key != 'page_type' && $key != 'radius' && $key != 'search_within' && $key != 'term_id' && $key != 'page_num' && $key != 'category' && $key != 'etype' && $key != 'articleauthor' && $key != 'radius' && $key != 'mkey' && $key != 'relation' && $key != 't' && $key != 'lang' && $key != 'ctype' && $key != 'submit' && $key != 'exactyes' && $key != 'page_num' && $key != 's' && $key != 'post_type'  && $key != 'radius_type' && $key != 'nearby' && $key != 'sortby' && $key != 'alpha_sort' && $key != 'ptype' && $key != 'sr_post_type')
		{
			
			if(!empty($value))
			{
				/*echo $key.'=>'.$value.'<br/>';*/
				$all_values[] = $value;
			}
		}
	}
	
	
	/* if all fields are empty then remove filter */
	if(count($all_values) == 0 && !isset($_REQUEST['nearby']))
	{

		remove_filter( 'posts_where' , 'tmpl_search_filter_where'); 
		//remove_filter('posts_orderby', 'tmpl_search_filter_orderby');
	}
	/* end for remove filter */	
	
	$post_details = new WP_Query($args);	
	$post_details->request;

	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		remove_filter('posts_where', 'tmpl_location_multicity_where');
		remove_filter('posts_join','tevolution_favourites_post_join',12);
	}
		
	if ($post_details->have_posts()) :
		while ( $post_details->have_posts() ) : $post_details->the_post();
			$ID =get_the_ID();				
			$title = get_the_title($ID);
			$plink = get_permalink($ID);
			$lat = get_post_meta($ID,'geo_latitude',true);
			$lng = get_post_meta($ID,'geo_longitude',true);					
			$address = stripcslashes(str_replace($srcharr,$replarr,(get_post_meta($ID,'address',true))));
			$contact = str_replace($srcharr,$replarr,(get_post_meta($ID,'phone',true)));
			$website = get_post_meta($ID,'website',true);
			/*Fetch the image for display in map */
			if ( has_post_thumbnail()){
				$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail');						
				$post_images=$post_img[0];
			}else{
				$post_img = bdw_get_images_plugin($ID,'thumbnail');					
				$post_images = $post_img[0]['file'];
			}
			
			$imageclass='';
			if($post_images)
				$post_image='<div class=map-item-img><img src='.$post_images.' width=150 height=150/></div>';
			else{
				$post_image='';
				$imageclass='no_map_image';
			}
			
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $posttype,'public'   => true, '_builtin' => true ));
			$cat_args = array(
						'taxonomy'=>$taxonomies[0],
						'orderby' => 'name', 				
						'hierarchical' => 'true',
						'title_li'=>''
					);	
			$r = wp_parse_args( $cat_args);	
			$catname_arr=get_categories( $r );
			foreach($catname_arr as $cat)
			{
				
				if(isset($_REQUEST['term_id']) && $_REQUEST['term_id']!="")
				{
					
					if($_REQUEST['term_id'] == $cat->term_id)
					{

						if($cat->term_icon != '')
							$term_icon=$cat->term_icon;
						else
							$term_icon=TEVOLUTION_PAGE_TEMPLATES_URL.'images/pin.png';	
								
					}
				}
				else
					{
					    if($cat->term_icon)
							$term_icon=$cat->term_icon;
						else
							$term_icon=TEVOLUTION_PAGE_TEMPLATES_URL.'images/pin.png';	
					}
				
				
			}
			
			if($_REQUEST['term_id']=="" && empty($_REQUEST['cats']))
			 {
				$post_categories = get_the_terms( $ID ,$taxonomies[0]); 
					foreach($post_categories as $post_category){
						if($post_category->term_icon ){
							$term_icon=$post_category->term_icon;
							//break;
							}else{
						$term_icon=TEVOLUTION_LOCATION_URL.'images/pin.png';
						}
					}
				}
			if(count($_REQUEST['cats']) > 0 )
			{
				$post_categories = get_the_terms( $ID ,$taxonomies[0]); 
				foreach($post_categories as $post_category){
					//echo $post_category->term_icon."=".$post_category->term_id;
					if($post_category->term_icon && in_array($post_category->term_id,$_REQUEST['cats'])){
					$term_icon=$post_category->term_icon;
					break;
					}else{
					$term_icon=TEVOLUTION_LOCATION_URL.'images/pin.png';
					}
				}
			}

			
			$image_class=($post_image)?'map-image' :'';
			$comment_count= count(get_comments(array('post_id' => $ID)));
			$review=($comment_count ==1 )? __('review',SF_DOMAIN):__('reviews',SF_DOMAIN);	
			
			if(($lat && $lng )&& !in_array($ID,$pids))
			{ 	
				$retstr ='{';
				$retstr .= '"name":"'.$title.'",';
				$retstr .= '"location": ['.$lat.','.$lng.'],';
				$retstr .= '"message":"<div class=\"google-map-info '.$image_class.'\"><div class=map-inner-wrapper><div class=\"map-item-info '.$imageclass.'\">'.$post_image;
				$retstr .= '<h6><a href='.$plink.' class=ptitle><span>'.$title.'</span></a></h6>';							
				if($address){$retstr .= '<p class=address>'.$address.'</p>';}
				if($contact){$retstr .= '<p class=contact>'.$contact.'</p>';}
				if($website){$retstr .= '<p class=website><a href= '.$website.'>'.$website.'</a></p>';}
				if($templatic_settings['templatin_rating']=='yes'){
					$rating=draw_rating_star_plugin(get_post_average_rating(get_the_ID()));
					$retstr .= '<div class=map_rating>'.str_replace('"','',$rating).' <span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
				}elseif(is_plugin_active('Templatic-MultiRating/multiple_rating.php') && function_exists('single_average_rating')){
					$rating=get_single_average_rating(get_the_ID());
					$retstr .= '<div class=map_rating>'.stripcslashes(str_replace('"','',$rating)).'<span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
				}
				$retstr .= '</div></div></div>';
				$retstr .= '",';
				$retstr .= '"icons":"'.$term_icon.'",';
				$retstr .= '"pid":"'.$ID.'"';
				$retstr .= '}';
				$content_data[] = $retstr;
				$j++;
			}	
			
			$pids[]=$ID;
		endwhile;
		wp_reset_query();	
		
	endif;
	if($content_data)	
		$catinfo_arr= '{"markers":['.implode(',',$content_data)."]}";
				
	if($content_data)
	{
		echo $catinfo_arr;
	}else
	{
		echo '{"markers":[]}';
	}
	exit;
}


/*
Name : advance_search_filter
Description : gets the filter result.
*/
function tmpl_advance_search_filter()
{
 
	global $wp_query,$wpdb,$current_cityinfo,$wp_query;
	
	if(isset($_REQUEST['post_type']) && is_array($_REQUEST['post_type']) && count($_REQUEST['post_type']) > 0 && $_REQUEST['search_from'] == '')
		$posttype = $_REQUEST['post_type'];
	else	
		$posttype = (isset($_REQUEST['search_from']) && $_REQUEST['search_from'] != '') ? $_REQUEST['search_from'] : $_REQUEST['posttype'];/* gets the post type */
		
		
	// get page type		
	if(is_tax()){
		$list_id='loop_'.$post_type.'_taxonomy';
		$page_type= 'taxonomy';
	}
	else{
		if($post_type == CUSTOM_POST_TYPE_EVENT){
			$list_id='loop_'.$post_type.'_archive';
			$page_type= 'archive';
		}else{
			$list_id='loop_'.$post_type.'_taxonomy';
			$page_type= 'archive';
		}
	}
			
	$queried_object = get_queried_object();   /* currently-queried object */
	$term_id = $queried_object->term_id;  
	/* Get heading type to display the custom fields as per selected section.  */
	if(function_exists('tmpl_fetch_heading_post_type')){
	
		$heading_type = tmpl_fetch_heading_post_type($post_type);
	}
	
	/* get all the custom fields which select as " Show field on listing page" from back end */
	
	if(function_exists('tmpl_get_category_list_customfields')){
		$posttype = $_REQUEST['posttype'];
		$htmlvar_name = tmpl_get_category_list_customfields($posttype);
	}else{
		global $htmlvar_name;
	}

	//$wp_query->set('post_type', array($posttype));
	$per_page=get_option('posts_per_page'); /* post per page */
	
	 /* get page number. if there is no page number then set page number to 1 */
	$paged = (isset($_REQUEST['page_num']) && $_REQUEST['page_num'] != '') ? $_REQUEST['page_num'] : 1;
	
	if(isset($_REQUEST['term_id']) && $_REQUEST['term_id']!=""){  /* if term id exist then show result according to term */
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $posttype ,'public'   => true, '_builtin' => true ));	
		$args=array(
				 'post_type'      => $posttype,
				 'posts_per_page' => $per_page,
				 'post_status'    => 'publish',
				 'paged' 		  => $paged,
				 'tax_query'      => array(
										  array(
											 'taxonomy' => $taxonomies[0],
											 'field'    => 'id',
											 'terms'    => explode(',',$_REQUEST['term_id']),
											 'operator' => 'IN'
										  )
									   ),
				);
		
	}else{      /* else show all result */
		$args=array(
				 'post_type'      => $posttype,
				 'posts_per_page' => $per_page,
				 'paged' 		  => $paged,
				 'post_status'    => 'publish',
				 );
	}
	/* to the custom fields which are selected as show on category page - yes*/
	if(function_exists('directory_manager_listing_custom_field'))
		directory_manager_listing_custom_field();
	
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		add_filter('posts_join','tevolution_favourites_post_join',12);
		/* if search from current city is enabled from widget then search from current city, otherwise all city result will be shown */
		if(isset($_REQUEST['search_filter_in_city']) && $_REQUEST['search_filter_in_city'] == 1)
			add_filter('posts_where', 'tmpl_location_multicity_where');
	}
	
	/* When search using search widget/template the filters apply from following hook */ 
	add_filter( 'posts_where' , 'tmpl_search_filter_where'); 
	/* filter for order by  */
	add_filter('posts_orderby', 'tmpl_search_filter_orderby',13);

	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		add_filter('posts_where', 'tmpl_search_language');
	}
	
		
	if(isset($_REQUEST['radius_type']) && $_REQUEST['radius_type']== strtolower('Kilometer')){
		
		add_filter('posts_where', 'directory_search_filter_nearby');
	}
	
	/* get all fields values from filter form and check if it is empty or not */
	$all_values = array();
	foreach($_REQUEST as $key=>$value)
	{
		if($key != 'action' && $key != 'posttype' && $key != 'page_type' && $key != 'radius' && $key != 'search_within' && $key != 'term_id' && $key != 'page_num' && $key != 'category' && $key != 'etype' && $key != 'articleauthor' && $key != 'radius' && $key != 'mkey' && $key != 'relation' && $key != 't' && $key != 'lang' && $key != 'ctype' && $key != 'submit' && $key != 'exactyes' && $key != 'page_num' && $key != 's' && $key != 'post_type'  && $key != 'radius_type' && $key != 'nearby' && $key != 'ptype' && $key != 'search_template' && $key != 'search_custom'  && $key != 'cats' && $key != 'sr_post_type')
		{
			
			if(!empty($value))
			{
				/*echo $key.'=>'.$value.'<br/>';*/
				$all_values[] = $value;
			}
		}
	}
	
	//print_r($all_values);
	/* if all fields are empty then remove filter */
	if(count($all_values) == 0 && !isset($_REQUEST['nearby']))
	{
		remove_all_filters('posts_where'); /* made query default when no field is seleted in widget */
		remove_filter( 'posts_where' , 'tmpl_search_filter_where'); 
		remove_filter('posts_orderby', 'tmpl_search_filter_orderby');
	}
	/* end for remove filter */	
	
	$post_details = new WP_Query($args);	
	$post_details->request;
	
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		remove_filter('posts_where', 'tmpl_location_multicity_where');
		remove_filter('posts_join','tevolution_favourites_post_join',12);
	}
	
	$exclud_array = array('event','property','classified');
	
	if(!in_array($posttype,$exclud_array))
	{
		$posttype = 'listing';
	}
	$wp_query->set('is_ajax_archive',1);
	if ($post_details->have_posts()){
		while ( $post_details->have_posts() ) : $post_details->the_post(); global $post; 
			
			/* 
			   loads template part for search result - loads template if it is available in theme otherwise it loads the template from perticuler plugins.
			   And template name should be "content-{your-posttype}.php"
			*/
			
			
			if(function_exists('tmpl_wp_is_mobile') && tmpl_wp_is_mobile()){
				if (locate_template('entry-mobile-' . $posttype . '.php') != ''){
					get_template_part('entry-mobile', $posttype);
				}else{
					do_action('get_template_part_tevolution-search','entry-mobile',$posttype,$htmlvar_name);
				}
			}else{
				if (locate_template('entry-' . $posttype . '.php') != ''){
					get_template_part('entry', $posttype);
				}else{
					do_action('get_template_part_tevolution-search','entry',$posttype,$htmlvar_name);
				}
			}
			
		endwhile;
		wp_reset_query();
	}else{ /* No results found */ ?>
        <p class='nodata_msg'><?php _e( 'Apologies, but no results were found for the requested archive.', SF_DOMAIN ); ?></p> <?php
		/* No results found */
	}
	
	if($post_details->max_num_pages !=1):
	?>
     <div id="listpagi">
          <div class="pagination pagination-position">
                <?php 
            
                $big = 999999999; // need an unlikely integer
                
					echo paginate_links( array(
						'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format' => '/page/%#%',
						'current' => max( 1, $paged ),
						'total' => $post_details->max_num_pages,
						'before_page_number' => '<strong>',
						'after_page_number' => '</strong>',
						'prev_text'    => '<strong>'.__('Previous',SF_DOMAIN).'</strong>',
						'next_text'    => '<strong>'.__('Next',SF_DOMAIN).'</strong>',
						'type'         => 'plain',
					) );
                ?>
          </div>
     </div>
     <?php endif;
	
	exit;

}

/*
 * Function Name: tmpl_search_language
 * Return: display the search result by language
 */
function tmpl_search_language($where)
{
	$language = ICL_LANGUAGE_CODE;
	$where .= " and t1.language_code='".$language."'";
	return $where;
}

/*
 * Function Name: search_filter_where
 * Return: display the search result by filters
 */
function tmpl_search_filter_where($where)
{
	global $wpdb;	

	/*  get the requested post type  */
	if(isset($_REQUEST['sr_post_type']) && $_REQUEST['sr_post_type'] != '')
		$post_type = str_replace(",","','",$_REQUEST['sr_post_type']);
	elseif(count($_REQUEST['post_type']) > 1)
		$post_type = str_replace(",","','",implode(',',$_REQUEST['post_type']));
	else
		$post_type = (isset($_REQUEST['search_from']) && $_REQUEST['search_from'] != '') ? $_REQUEST['search_from'] : $_REQUEST['posttype'];
	
	$keyword = (isset($_REQUEST['s']) && $_REQUEST['s'] != '') ? $_REQUEST['s'] : '';
	
	$all_srch_filter_arr = array();

	/* search query for any custom fields */
	if(!empty($_POST)){
		
		$fquery = array();
		$qry = "select p.ID from $wpdb->postmeta pm ,$wpdb->posts p where p.ID = pm.post_id AND  p.post_status = 'publish' AND p.post_type IN ('$post_type')";
				
		foreach($_POST as  $key=>$value){			
			if(!empty($value))
			{
			if($key != 'cats' && $key != 'category' && $key != 'scat' & $key != 'etype' && $key != 'min_price' && $key != 'max_price'  && $key != 'search' && $key != 'articleauthor' && $key != 'rate' && $key != 'post_city_id' && $key != 'action' && $key != 'posttype'  && $key != 'page_type' && $key != 'term_id' && $key != 'search_within' && $key != 'sortby' && $key != 'tmpl_event_date' && $key != 'search_filter_in_city' && $key != 'radius' && $key != 'miles_range' && $key != 'mkey' && $key != 'relation' && $key != 'post_title' && $key != 'sf_post_title' && $key != 's' && $key != 't' && $key != 'lang' && $key != 'post_type' && $key != 'post_excerpt' && $key != 'post_content' && $key != 'ctype' && $key != 'alpha_sort'  && $key != 'search_custom' && $key != 'search_template' && $key != 'search_from' && $key != 'submit' && $key != 'nearby' && $key != '' && $key != 'radius_type'  && $key != 'location' && $key != 'exactyes' && $key != 'page_num' && $key != 'ptype' && $key != 'paged' && $key != 'sr_post_type' && !strstr($key,'_min') && !strstr($key,'_max') && $key!='list_filter_search_custom' && $key != 'slug' && $key != 'taxonomy' && $key != 'device' && $key != 'directory_sortby')
				{
					/* if value comes from multi checkbox then show from serialized data */

					
					if(is_array($value))
					{
						$count = 1;
						$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key'";
						foreach($value as $val)
						{
						    if($count == 1)
						       $operator = 'AND';
						    else
						       $operator = 'OR';   
							
							 $qry .= " $operator (pm.meta_value LIKE \"%$val%\" ) ";	
							
						$count++;	
						}
						$qry .= " ))";
					}elseif($_REQUEST['list_filter_search_custom'][$key]=='slider_range' || $_REQUEST['list_filter_search_custom'][$key]=='min_max_range' || $_REQUEST['list_filter_search_custom'][$key]=='min_max_range_select'){
												if($_REQUEST['list_filter_search_custom'][$key]=='min_max_range' || $_REQUEST['list_filter_search_custom'][$key]=='min_max_range_select'  ){							
							$min_value=trim($_REQUEST[$key.'_min']);
							$max_value=trim($_REQUEST[$key.'_max']);
						}else{
							$key_value = explode('-',$_REQUEST[$key]);
							$min_value=trim($key_value[0]);
							$max_value=trim($key_value[1]);
						}
						if($min_value!='' && $max_value!='' && strtolower($min_value)!='any' && strtolower($max_value)!='any'){
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and (pm.meta_value >= $min_value and  pm.meta_value <= $max_value))) ";
						}elseif($min_value!='' && strtolower($min_value)!='any'){
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and pm.meta_value >= $min_value )) ";
						}elseif($max_value!='' && strtolower($max_value)!='any'){
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and pm.meta_value <= $max_value)) ";
						}
						
						//$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and (pm.meta_value like \"%$value%\" )))";
					}
					else
					{
						/* of start date and end date both are selected */
						if($_REQUEST['end_date']!="" && $_REQUEST['st_date'] != "" && $key == 'st_date')
						{
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='st_date' and (pm.meta_value <=  '".$_REQUEST['st_date']."' ))) ";
				
						}
						elseif($_REQUEST['end_date']!="" && $_REQUEST['st_date'] != "" && $key == 'end_date')  
						{
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='end_date' and (pm.meta_value >= '".$_REQUEST['end_date']."' ))) ";
						}
						
						/* if only start date is selected */
						elseif($_REQUEST['end_date']=="" && $_REQUEST['st_date'] != "" && $key == 'st_date')  
						{
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='st_date' and (pm.meta_value <=  '".$_REQUEST['st_date']."' ))) ";
				
						}
						
						/* if only end date is selected */
						elseif($_REQUEST['end_date']!="" && $_REQUEST['st_date'] == "" && $key == 'end_date')
						{
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='end_date' and (pm.meta_value >= '".$_REQUEST['end_date']."' ))) ";
						}
						
						elseif($key != 'st_date' && $key != 'end_date' && $key != 'map_view')
						{
						    if($key == 'sf_location')
								$key = 'location';
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and (pm.meta_value like \"%$value%\" )))";
							$key = '';							
						}
						else
						{
							$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and (pm.meta_value = \"$value\" )))";	
						}
					}
					
				}	
			}elseif($_REQUEST['list_filter_search_custom'][$key]=='min_max_range'){				
				$min_value=trim($_REQUEST[$key.'_min']);
				$max_value=trim($_REQUEST[$key.'_max']);
				if($min_value!='' && $max_value!=''){
					$qry .= " AND ( p.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='$key' and (pm.meta_value >= $min_value and  pm.meta_value <= $max_value))) ";
				}				
			}
		}		
	
	$qry .= " GROUP BY p.ID";
	$fquery = $wpdb->get_col($qry);
	$all_srch_filter_arr = $fquery;		

	}
	
	/* filter by post title and return array of post id results */
	if(isset($_REQUEST['sf_post_title']) && $_REQUEST['sf_post_title'] != '')
	{
		$ptitle = $_REQUEST['sf_post_title'];
		$search_post_title_arr = $wpdb->get_col("SELECT $wpdb->posts.ID FROM  $wpdb->posts WHERE $wpdb->posts.post_title LIKE '%$ptitle%' ");
		 /* get the resulted id */
		 
			$all_srch_filter_arr = array_intersect($search_post_title_arr,$all_srch_filter_arr);

	}
	
	
	/* filter by post address */
	if(isset($_REQUEST['location']) && $_REQUEST['location'] != '')
	{
		$location=$_REQUEST['location'];
		$location_arr = $wpdb->get_col("select pm.post_id from $wpdb->postmeta pm where pm.meta_key like 'address' and pm.meta_value LIKE '%$location%' ");
		 /* get the resulted id */
	
			$all_srch_filter_arr = array_intersect($location_arr,$all_srch_filter_arr);
	

	}	
	/* filter by post content and return array of post id results */		
	if(isset($_REQUEST['post_content']) && $_REQUEST['post_content'] != '')
	{
		$pcontent=$_REQUEST['post_content'];
		$search_post_content_arr = $wpdb->get_col("SELECT $wpdb->posts.ID FROM  $wpdb->posts WHERE $wpdb->posts.post_content LIKE '%$pcontent%'");
		 /* get the resulted id */

			$all_srch_filter_arr = array_intersect($search_post_content_arr,$all_srch_filter_arr);
	

	}
	/* filter by post excerpt and return array of post id results */			
	if(isset($_REQUEST['post_excerpt']) && $_REQUEST['post_excerpt'] != '')
	{
		$pexcerpt = $_REQUEST['post_excerpt'];
		$search_post_excerpt_arr = $wpdb->get_col("SELECT $wpdb->posts.ID FROM  $wpdb->posts WHERE $wpdb->posts.post_excerpt LIKE '%$pexcerpt%' ");
		 /* get the resulted id */

			$all_srch_filter_arr = array_intersect($search_post_excerpt_arr,$all_srch_filter_arr);
		
	}
	/* filter by post categories and return array of post id results */
	if((isset($_REQUEST['cats']) && count($_REQUEST['cats']) > 0)  || (isset($_REQUEST['term_id']) && $_REQUEST['term_id'] != '')) 
	{
		if(count($_REQUEST['cats']) > 0)
			{
				$scat = implode(',',$_REQUEST['cats']);
			}
		else
		{	
			$where = '';
			$scat1 = $_REQUEST['term_id'];
			$taxonomy_names = get_object_taxonomies( $post_type );
				$subcategories = get_categories('&child_of='.$scat1.'&hide_empty=0&type='.$post_type.'&taxonomy='.$taxonomy_names[0]); 
				foreach ($subcategories as $subcategory) {
				  $subscat[] = $subcategory->term_id;
				}
			if( count($subscat)> 0)	
				$scat = implode(',',$subscat).','.$scat1;	
			else
				$scat = $scat1;	
		}
		
		if(strstr($post_type,'event'))
			$cats = $wpdb->get_col("select tr.object_id from $wpdb->terms c,$wpdb->term_taxonomy tt,$wpdb->term_relationships tr,$wpdb->posts p where c.term_id=tt.term_id and c.term_id IN ($scat) and  tt.term_taxonomy_id=tr.term_taxonomy_id and tr.object_id=p.ID and (p.post_status = 'publish' OR p.post_status = 'recurring') and p.post_type IN ('".$post_type."') group by  p.ID");
		else
			$cats = $wpdb->get_col("select tr.object_id from $wpdb->terms c,$wpdb->term_taxonomy tt,$wpdb->term_relationships tr,$wpdb->posts p where c.term_id=tt.term_id and c.term_id IN ($scat) and  tt.term_taxonomy_id=tr.term_taxonomy_id and tr.object_id=p.ID and p.post_status = 'publish' and p.post_type IN ('".$post_type."') group by  p.ID");
		
		
		
		 /* get the resulted id */
		$all_srch_filter_arr = array_intersect($cats,$all_srch_filter_arr);	
	
	}

	/* filter by rate - returns array of results between selected rates  */
	if(isset($_REQUEST['rate']) &&  count($_REQUEST['rate']) > 0)
	{
		$rate = str_replace(",","','",implode(',',$_REQUEST['rate']));
		$rate_arr = $wpdb->get_col("select pm.post_id from $wpdb->postmeta pm where pm.meta_key='average_rating' and pm.meta_value IN ('$rate') ");

		 /* get the resulted id */
		$all_srch_filter_arr = array_intersect($rate_arr,$all_srch_filter_arr);


	}

	/* Search with in results and returns array of results */
	if(isset($_REQUEST['search_within']) && $_REQUEST['search_within'] != '' && $_REQUEST['search_within'] != 'undefined' && $_REQUEST['search_within'] != 'Search within result')
	{
		$search_within = $_REQUEST['search_within'];
		$search_within_arr = $wpdb->get_col("SELECT $wpdb->posts.ID FROM  $wpdb->posts WHERE $wpdb->posts.post_title LIKE '%$search_within%' ");
		/* get the resulted id */

		$all_srch_filter_arr = array_intersect($search_within_arr,$all_srch_filter_arr);

	}
	/* Search with search keyword */
	if(isset($keyword) && $keyword != '')
	{
		$search_within_key = $wpdb->get_col("SELECT $wpdb->posts.ID FROM  $wpdb->posts WHERE $wpdb->posts.post_title LIKE '%$keyword%' OR $wpdb->posts.post_content LIKE '%$keyword%'");
		 /* get the resulted id */
		 $all_srch_filter_arr = array_intersect($search_within_key,$all_srch_filter_arr);


	}
	/* Search by published event date and returns array of results */
	if(isset($_REQUEST['tmpl_event_date']) && $_REQUEST['tmpl_event_date'] != '')
	{

		$event_date = date_i18n('Y-m-d',strtotime($_REQUEST['tmpl_event_date']));
		$events_date_arr = $wpdb->get_col("SELECT pm.post_id FROM  $wpdb->postmeta pm WHERE pm.meta_key='st_date' AND date_format(pm.meta_value, '%Y-%m-%d' ) <= '$event_date' AND (pm.post_id in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='end_date' and date_format(pm.meta_value,'%Y-%m-%d') >= '$event_date'))");
		$all_srch_filter_arr = array_intersect($events_date_arr,$all_srch_filter_arr);

	}

	/* Search by miles range - when distance by filtering is selected */
	if(isset($_REQUEST['miles_range']) && $_REQUEST['miles_range'] != '')
	{
		global $current_cityinfo;	
		$ip  = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		$url = "http://freegeoip.net/json/$ip";
		$data=wp_remote_get( $url, array( 'timeout' => 120, 'httpversion' => '1.1' ) );
		if ($data) {
			$location = json_decode($data['body']);				
			$lat = $location->latitude;
			$long = $location->longitude;
		}else{
			$lat =$current_cityinfo['lat'];
			$long = $current_cityinfo['lng'];
		}
		$miles_range=explode('-',$_REQUEST['miles_range']);
		$to_miles = trim($miles_range[0]);
		$miles = trim($miles_range[1]);
		if($miles ==''){ $miles = '1000'; }
		$tbl_postcodes = $wpdb->prefix . "postcodes";		
		
		if($lat !='' && $long !=''){

			 $miles_range_arr = $wpdb->get_col("SELECT post_id FROM  $tbl_postcodes WHERE truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) <= ".$miles." AND truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) >= ".$to_miles." ORDER BY truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1)");
			 /* get the resulted id */
			 
				$all_srch_filter_arr = array_intersect($miles_range_arr,$all_srch_filter_arr);

		}
	}

	/* If post type is event, then search by tabs like current, upcoming and past */
	if($post_type == 'event')
		{
			$event_manager_setting=get_option('event_manager_setting');/* get the event settings */
			/* gets the current tab */
			$templatic_current_tab = isset($event_manager_setting['templatic-current_tab'])? $event_manager_setting['templatic-current_tab']:'';
			if(!isset($_REQUEST['etype']))			
			{	
				
				$_REQUEST['etype']=($templatic_current_tab == '')?'current':$templatic_current_tab;
				$to_day = date_i18n('Y-m-d',strtotime(date('Y-m-d'))); /* get today's date */
			}
			/* search in upcoming event */
			if(isset($_REQUEST['etype']) && $_REQUEST['etype']=='upcoming')
			{				
				$today = date_i18n('Y-m-d',strtotime(date('Y-m-d')));
				$upcomming_events = $wpdb->get_col("SELECT pm.post_id FROM  $wpdb->postmeta pm WHERE pm.meta_key='st_date' AND date_format(pm.meta_value, '%Y-%m-%d' ) > '$today' ");
				/* get the resulted id */
			        $all_srch_filter_arr = array_intersect($upcomming_events,$all_srch_filter_arr);

			}
			/* search in past event */			
			elseif(isset($_REQUEST['etype']) && $_REQUEST['etype']=='past')
			{			
				$today = date_i18n('Y-m-d',strtotime(date('Y-m-d')));
				$past_events = $wpdb->get_col("SELECT pm.post_id FROM  $wpdb->postmeta pm WHERE pm.meta_key='end_date' AND date_format(pm.meta_value, '%Y-%m-%d' ) < '$today' ");
				/* get the resulted id */
			    $all_srch_filter_arr = array_intersect($past_events,$all_srch_filter_arr);

			}
			/* search in current event */
			elseif(isset($_REQUEST['etype']) && $_REQUEST['etype']=='current')
			{
				$today = date_i18n('Y-m-d',strtotime(date('Y-m-d')));

				$current_events = $wpdb->get_col("SELECT pm.post_id FROM  $wpdb->postmeta pm WHERE pm.meta_key='st_date' AND date_format(pm.meta_value, '%Y-%m-%d' ) <= '$today' AND (pm.post_id in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='end_date' and date_format(pm.meta_value,'%Y-%m-%d') >= '$today'))");
				/* get the resulted id */
				$all_srch_filter_arr = array_intersect($current_events,$all_srch_filter_arr);

			}else{

                $today = date_i18n('Y-m-d',strtotime(date('Y-m-d')));

				$current_events = $wpdb->get_col("SELECT pm.post_id FROM  $wpdb->postmeta pm WHERE pm.meta_key='st_date' AND date_format(pm.meta_value, '%Y-%m-%d' ) <= '$today' AND (pm.post_id in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key='end_date' and date_format(pm.meta_value,'%Y-%m-%d') >= '$today'))");
				/* get the resulted id */
				$all_srch_filter_arr = array_intersect($current_events,$all_srch_filter_arr);
            }
			
		}


	/* sort by alphabets */
	if(isset($_REQUEST['alpha_sort']) && $_REQUEST['alpha_sort'] != '' && $_REQUEST['alpha_sort'] != 'undefined' && $_REQUEST['alpha_sort'] != 'All')
	{
			$where .= "  AND $wpdb->posts.post_title like '".$_REQUEST['alpha_sort']."%'";
	}


	/* arrange keys of resulted array */
	$final_srch_arr = array_values($all_srch_filter_arr);
	$final_srch_ids = implode(',',$final_srch_arr);

	/*  Execute where only if array is not empty */
	if(!empty($final_srch_ids))
		$where .= " AND ($wpdb->posts.ID in ($final_srch_ids))";
	else
		$where .= " AND ($wpdb->posts.ID in ('0'))";	/* if empty then set result post id to zero */
				
	/* if Search from current city is enable then filter within a default city */	
	if(isset($_REQUEST['search_filter_in_city']) && $_REQUEST['search_filter_in_city'] == 1 && !empty($final_srch_ids)){
		$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and FIND_IN_SET( ".$_SESSION['post_city_id'].", pm.meta_value ))";	
	}
	
	$all_srch_filter_arr = array();	/* clear the array */
	
	return $where;
}

/*
 * Function Name: search_filter_orderby
 * Return: display the search result by ordering wise.
 */
function tmpl_search_filter_orderby($orderby){
	global $wpdb,$wp_query;		
	 /* sort by title or alphabetical asc  */
	if (isset($_REQUEST['sortby']) && ($_REQUEST['sortby'] == 'title_asc' || $_REQUEST['sortby'] == 'alphabetical' || $_REQUEST['event_sortby'] == 'title_asc'))
	{
		$orderby= "$wpdb->posts.post_title ASC";
	}
	/* sort by title desc */
	elseif ((isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'title_desc') || $_REQUEST['event_sortby'] == 'title_desc' ) 
	{
		$orderby = "$wpdb->posts.post_title DESC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	/* sort by published date asc */
	elseif ((isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'date_asc') || $_REQUEST['event_sortby'] == 'date_asc' ) 
	{
		$orderby = "$wpdb->posts.post_date ASC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	/* sort by published date desc */
	elseif (isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'date_desc' )  
	{
		$orderby = "$wpdb->posts.post_date DESC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	/* sort by random */
	elseif(isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'random' ) 
	{
		$orderby = "(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC,rand()";
	}
	/* sort by reviews */
	elseif(isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'reviews' ) 
	{
		$orderby = 'DESC';
		$orderby = " comment_count $orderby,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	/* sort by rating */
	elseif(isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'rating' ) 
	{
		$orderby = " (select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id = $wpdb->posts.ID and $wpdb->postmeta.meta_key like \"average_rating\") DESC,(select distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id=$wpdb->posts.ID and $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC";
	}
	/* sort by start date ascending */
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'stdate_low_high' )
	{
		$orderby = " (SELECT $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key like \"st_date\") ASC";		
	}
	/* sort by start date descending */
	elseif (isset($_REQUEST['event_sortby']) && $_REQUEST['event_sortby'] == 'stdate_high_low' )
	{
		$orderby = " (select $wpdb->postmeta.meta_value from $wpdb->postmeta where $wpdb->postmeta.post_id = $wpdb->posts.ID and $wpdb->postmeta.meta_key like \"st_date\") DESC";
	}
	/* other wise sort by post  published date */
	else
	{
		$orderby = " (SELECT distinct $wpdb->postmeta.meta_value from $wpdb->postmeta where ($wpdb->posts.ID = $wpdb->postmeta.post_id) AND $wpdb->postmeta.meta_key = 'featured_c' AND $wpdb->postmeta.meta_value = 'c') DESC, $wpdb->posts.post_date DESC";
	}

	return $orderby;
}

add_action('wp_ajax_nopriv_tmpl_change_calendar','tmpl_change_calendar');
add_action('wp_ajax_tmpl_change_calendar','tmpl_change_calendar');
/*
 *
 * function : tmpl_change_calendar
 * Description : change the months of claender
*/
function tmpl_change_calendar(){
	global $post,$wpdb,$current_cityinfo;	
	/* display calendar fetching all event */
	$monthNames = Array(__("January",SF_DOMAIN), __("February",SF_DOMAIN), __("March",SF_DOMAIN), __("April",SF_DOMAIN), __("May",SF_DOMAIN), __("June",SF_DOMAIN), __("July",SF_DOMAIN), __("August",SF_DOMAIN), __("September",SF_DOMAIN), __("October",SF_DOMAIN), __("November",SF_DOMAIN), __("December",SF_DOMAIN));
	global $todaydate;
	$cMonth = (isset($_REQUEST['mnth']) && $_REQUEST['mnth']!='')?$_REQUEST["mnth"]: date("n");
	$cYear = (isset($_REQUEST['yr']) && $_REQUEST['yr']!='')?$_REQUEST["yr"]: date("Y");
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	
	?>
	<table id="wp-calendar" width="100%" class="calendar">
		
	<caption>
		<div class="prev_month nav_btn">	
			<a href="javascript:void(0);" onclick="change_calendar(<?php echo $prev_month; ?>,<?php echo $prev_year; ?>)"> &laquo; <?php echo tmpl_calendar_month_name($prev_month); ?></a>
		</div>	
			<?php echo $monthNames[$cMonth-1].' '.$cYear; ?>
		<div class="next_month nav_btn">		
			<a href="javascript:void(0);"  onclick="change_calendar(<?php echo $next_month; ?>,<?php echo $next_year; ?>)"> <?php echo tmpl_calendar_month_name($next_month); ?> &raquo;</a>
		</div>	
	</caption>
				
		<tr>
		<td style="padding:0px; border:none;">
		<table width="100%" border="0" cellpadding="2" cellspacing="2"  class="calendar_widget" style="padding:0px; margin:0px; border:none;">
		
		<thead>
			<th title="<?php _e('Monday',SF_DOMAIN); ?>" class="days" ><?php _e('Mon',SF_DOMAIN);?></th>
			<th title="<?php _e('Tuesday',SF_DOMAIN); ?>" class="days" ><?php _e('Tues',SF_DOMAIN);?></th>
			<th title="<?php _e('Wednesday',SF_DOMAIN); ?>" class="days" ><?php _e('Wed',SF_DOMAIN);?></th>
			<th title="<?php _e('Thursday',SF_DOMAIN); ?>" class="days" ><?php _e('Thur',SF_DOMAIN);?></th>
			<th title="<?php _e('Friday',SF_DOMAIN); ?>" class="days" ><?php _e('Fri',SF_DOMAIN);?></th>
			<th title="<?php _e('Saturday',SF_DOMAIN); ?>" class="days" ><?php _e('Sat',SF_DOMAIN);?></th>
			<th  title="<?php _e('Sunday',SF_DOMAIN); ?>" class="days" ><?php _e('Sun',SF_DOMAIN);?></th>
		</thead> 
		<?php
		$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
		$maxday = date("t",$timestamp);
		$thismonth = getdate ($timestamp);
		$startday = $thismonth['wday'];
		
	
		$tmp_date = "";
		if(@$_GET['m'])
		{
			$m = $_GET['m'];	
			$py=substr($m,0,4);
			$pm=substr($m,4,2);
			$pd=substr($m,6,2);
			$monthstdate = "$cYear-$cMonth-01";
			$monthenddate = "$cYear-$cMonth-$maxday";
			
		} ?>
		<tbody>
		<?php
		global $wpdb;
		$sun_start_date = 0;
		if($startday == 0 )
		{
			$count = 7;
		}else
		{
			$count = 0;
		}
		for ($i=1; $i<($maxday+$startday+$count); $i++) {
	
			if($startday == 0 )
			{
				$add_sun_start_date = 7;
				$sun_start_date++;
			}else
			{
				$add_sun_start_date = $startday;
				$sun_start_date = $i;
			}
			if(($sun_start_date % 7) == 1 ) echo "<tr>\n";
			if($sun_start_date < $add_sun_start_date){
				echo "<td class='date_n'></td>\n";
			}
			else 
			{
				$cal_date = $i - $add_sun_start_date + 1;
				$calday = $cal_date;
				if(strlen($cal_date)==1)
				{
					$calday="0".$cal_date;
				}
				$the_cal_date = $cal_date;
				$cMonth_date = $cMonth;
				
				
				if(strlen($the_cal_date)==1){$the_cal_date = '0'.$the_cal_date;}
				if(strlen($cMonth_date)==1){$cMonth_date = '0'.$cMonth_date;}
				//global $post,$wpdb;
				$urlddate = "$cYear$cMonth_date$calday";				
				$thelink = get_permalink($page_id)."?cal_date=$urlddate";
				//$thelink = home_url()."/?s=Calender-Event&amp;m=$urlddate";
				
				$todaydate = "$cYear-$cMonth_date-$the_cal_date";
				$date_num=date('N',strtotime($todaydate))."<br>";
				/* Set the style left as per par calendar */
				$style='';
				if($date_num==3)
					$style='style="left:-44px"';
				if($date_num==4)
					$style='style="left:-87px"';
				if($date_num==5)
					$style='style="left:-129px"';
				if($date_num==6)
					$style='style="left:-161px"';
				if($date_num==7)
					$style='style="left:-195px"';
				/* Finish the set the style left as per calendar*/
	
					
					echo "<td class='date_n1' >";
						
							echo "<span class=\"no_event\" ><a filterdisplayname='".__('Event Date',SF_DOMAIN)."' todaydate=".$todaydate." href=\"javascript:void(0)\">". ($cal_date) . "</a></span>";
				
					echo "</td>\n";
			}
			if(($i % 7) == 0 ) echo "</tr>\n";
		}
		$pad = 7 - tmpl_calendar_week_mod_end(date('w', strtotime($todaydate)));
		if ( $pad != 0 && $pad != 7 )
			echo "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
		?>
		</tr>
		</tbody>
               
		</table>
		</td>
	</tr>
	</table>
	<?php
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='tmpl_change_calendar'){
		
		exit;
	}
}


function tmpl_calendar_week_mod_end($num) {
	$base = 7;
	return ($num - $base*floor($num/$base));
}
function tmpl_calendar_month_name($number){	
	$month = date_i18n("M", mktime(0, 0, 0, $number, 10));
	return  $month;
}
function tmpl_location_multicity_where($where){
	
	global $wpdb,$country_table,$zones_table,$multicity_table,$city_log_table,$current_cityinfo,$wp_query;	
	$city_slug=get_option('location_multicity_slug');	
	$multi_city=($city_slug)? $city_slug : 'city';
	/* latest post -page start */
	if(is_home() ){	// in home page city could not be save we should refresh the page
	
		if(strstr($_SERVER['REQUEST_URI'],'/'.$multi_city.'/')){
			$current_city = explode('/'.$multi_city.'/',$_SERVER['REQUEST_URI']);	
	
			if(strstr($current_city[1],'/')){
				$current_city = explode('/',$current_city[1]);
				$current_city = str_replace('/','',$current_city[0]);
			}else{
				$current_city = str_replace('/','',$current_city[1]);
			}
			$wp_query->set('city',$current_city);				
		}
		$multicity_table = $wpdb->prefix . "multicity";	
		if($wpdb->get_var("SHOW TABLES LIKE '$multicity_table'") == $multicity_table) {
			if(strstr($_SERVER['REQUEST_URI'],'/'.$multi_city.'/')){			
				$sql="SELECT * FROM $multicity_table where city_slug='".get_query_var('city')."'";
			}elseif(isset($_SESSION['post_city_id']) && $_SESSION['post_city_id']!=''){
				if(get_query_var('city')!='')
					$sql=$wpdb->prepare("SELECT * FROM $multicity_table where city_slug=%s",get_query_var('city'));
				else
					$sql=$wpdb->prepare("SELECT * FROM $multicity_table where city_id=%d",$_SESSION['post_city_id']);
				
			}else{			
				$sql="SELECT * FROM $multicity_table where is_default=1";		
			}
		}
		$default_city = $wpdb->get_results($sql);
		$default_city_id=$default_city[0]->city_id;
		
		$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and FIND_IN_SET( ".$default_city_id.", pm.meta_value ))";
	/* latest post -page end */
	}else{ 
		if($current_cityinfo['city_id']!=''){		
			//$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and pm.meta_value like'%".$current_cityinfo['city_id']."%' )";
			$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and FIND_IN_SET( ".$current_cityinfo['city_id'].", pm.meta_value ))";
		} 
		if(isset($_REQUEST['radius']) && $_REQUEST['radius'] !=''){
		
		}
	}
	
	return $where;
}
/*
 * Function Name: tmpl_search_filter_nearby
 * Return: search widget for fetch the near by post through address
 */
function tmpl_search_filter_nearby($where){
	global $wpdb,$wp_query,$current_cityinfo;
	$search = str_replace(' ','',$_REQUEST['location']);
	if(is_ssl()){ $http = "https://"; }else{ $http ="http://"; }
	$geocode = file_get_contents($http.'maps.google.com/maps/api/geocode/json?address='.$search.'&sensor=false');
	$output= json_decode($geocode);
	if(isset($output->results[0]->geometry->location->lat))
		$lat = $output->results[0]->geometry->location->lat;
	if(isset($output->results[0]->geometry->location->lng))
		$long = $output->results[0]->geometry->location->lng;
	
	$miles = @$_REQUEST['radius'];
	$saddress = @$_REQUEST['location'];
	
	if(isset($_REQUEST['radius_type']) && $_REQUEST['radius_type']== strtolower('Kilometer')){
		$miles = @$_REQUEST['radius'] / 0.621;
	}else{
		$miles = @$_REQUEST['radius'];	
	}
	$tbl_postcodes = $wpdb->prefix . "postcodes";
	if($saddress &&  !isset($_REQUEST['radius'])){
		$where .= " AND ($wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key like 'address' and pm.meta_value like \"%$saddress%\") )";
	}elseif($saddress &&  (isset($_REQUEST['radius']) && $_REQUEST['radius']=='')){
		$where .= " AND ($wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key like 'address' and pm.meta_value like \"%$saddress%\") )";
	}
	
	
	if($saddress=='' && !empty($current_cityinfo)){		
		$lat=$current_cityinfo['lat'];
		$long=$current_cityinfo['lng'];
	}
	if(!empty($_REQUEST['post_type']) )
	{
		$post_type1='';
		$post_type = implode(",",$_REQUEST['post_type']);
		$post_type_array = explode(",",$post_type);
		$sep = ",";
		for($i=0;$i<count($post_type_array);$i++)
		{
			if($i == (count($post_type_array) - 1))
			{
				$sep = "";
			}
			if(isset($post_type_array[$i]))
			$post_type1 .= "'".$post_type_array[$i]."'".$sep;
		}
	}
	
	if($lat!='' && $long!='' && (isset($_REQUEST['radius']) && $_REQUEST['radius']!='')){
		if (function_exists('icl_register_string')) {
			if($lat !='' && $long !=''){
				$where .= " AND ($wpdb->posts.ID in (SELECT post_id FROM  $tbl_postcodes WHERE $tbl_postcodes.post_type in (".$post_type1.")  AND truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) <= ".$miles." ORDER BY truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) ASC))";
			}
		}
		else
		{
			if($lat !='' && $long !=''){
				$where .= " AND ($wpdb->posts.ID in (SELECT post_id FROM  $tbl_postcodes WHERE $tbl_postcodes.post_type in (".$post_type1.") AND truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) <= ".$miles." ORDER BY truncate((degrees(acos( sin(radians(`latitude`)) * sin( radians('".$lat."')) + cos(radians(`latitude`)) * cos( radians('".$lat."')) * cos( radians(`longitude` - '".$long."') ) ) ) * 69.09),1) ASC))";
			}
		}
	}else{
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and FIND_IN_SET( ".$current_cityinfo['city_id'].", pm.meta_value ))";
		}
	}
	//echo $where;
	return $where;
}

/**
 * Output an unordered list of checkbox <input> elements labelled
 * with term names. Taxonomy independent version of wp_category_checklist().
 *
 * @since 3.0.0
 *
 * @param int $post_id
 * @param array $args
 
Display the categories check box like wordpress - wp-admin/includes/meta-boxes.php
 */
function tev_wp_terms_checklist_filter($post_id = 0, $args = array()) {

	global  $cat_array;
 	$defaults = array(
		'descendants_and_self' => 0,
		'selected_cats' => false,
		'popular_cats' => false,
		'walker' => null,
		'taxonomy' => 'category',
		'checked_ontop' => true
	);

	if(isset($_REQUEST['backandedit']) != '' || (isset($_REQUEST['pid']) && $_REQUEST['pid']!="") ){
		$place_cat_arr = $cat_array;
		$post_id = $_REQUEST['pid'];
	}
	else
	{
		if(!empty($cat_array)){
			for($i=0; $i < count($cat_array); $i++){
				$place_cat_arr[] = @$cat_array[$i]->term_taxonomy_id;
			}
		}
	}
	$args = apply_filters( 'wp_terms_checklist_args', $args, $post_id );
	$template_post_type = get_post_meta($post->ID,'submit_post_type',true);
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );

	if ( empty($walker) || !is_a($walker, 'Walker') )
		$walker = new Tev_Walker_Category_Checklist_filter;

	$descendants_and_self = (int) $descendants_and_self;

	$args = array('taxonomy' => $taxonomy);

	$tax = get_taxonomy($taxonomy);
	$args['disabled'] = !current_user_can($tax->cap->assign_terms);

	if ( is_array( $selected_cats ) )
		$args['selected_cats'] = $selected_cats;
	elseif ( $post_id && (!isset($_REQUEST['upgpkg']) && !isset($_REQUEST['renew'])) )
		$args['selected_cats'] = wp_get_object_terms($post_id, $taxonomy, array_merge($args, array('fields' => 'ids')));
	else
		$args['selected_cats'] = array();

	if ( is_array( $popular_cats ) )
		$args['popular_cats'] = $popular_cats;
	else
		$args['popular_cats'] = get_terms( $taxonomy, array( 'get' => 'all', 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'hierarchical' => false ) );

	if ( $descendants_and_self ) {
		$categories = (array) get_terms($taxonomy, array( 'child_of' => $descendants_and_self, 'hierarchical' => 0, 'hide_empty' => 0 ) );
		$self = get_term( $descendants_and_self, $taxonomy );
		array_unshift( $categories, $self );
	} else {
		$categories = (array) get_terms($taxonomy, array('get' => 'all'));
	}

	if ( $checked_ontop ) {
		// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
		$checked_categories = array();
		$keys = array_keys( $categories );
		$c=0;
		foreach( $keys as $k ) {
			if ( in_array( $categories[$k]->term_id, $args['selected_cats'] ) ) {
				$checked_categories[] = $categories[$k];
				unset( $categories[$k] );
			}
		}

		// Put checked cats on top
		echo call_user_func_array(array(&$walker, 'walk'), array($checked_categories, 0, $args));
	}
	// Then the rest of them

	echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
	if(empty($categories) && empty($checked_categories)){

			echo '<span style="font-size:12px; color:red;">'.sprintf(__('You have not created any category for %s post type.So, this listing will be submited as uncategorized.',DOMAIN),$template_post_type).'</span>';
	}
}

/**
 * Walker to output an unordered list of category checkbox <input> elements.
 *
 * @see Walker
 * @see wp_category_checklist()
 * @see wp_terms_checklist()
 * @since 2.5.1
 */
class Tev_Walker_Category_Checklist_filter extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
    var $selected_cats = array();
	
	
	/**
	 * Starts the list before the elements are added.
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);
		if ( empty($taxonomy) )
			$taxonomy = 'category';

		if ( $taxonomy == 'category' )
			$name = 'post_category';
		else
			$name = 'tax_input['.$taxonomy.']';
		
		$selected = array();
		if(!empty($_SESSION['category']) && @$_REQUEST['backandedit'] ==1){
			$cats = $_SESSION['category'];
			foreach($cats as $key=>$value){
					$cat = explode(',',$value);
					$selected[] .= $cat[0];
			}
			$selected_cats = $selected;
		}
		if($category->term_price !='' &&  $category->term_price!='0' ){$cprice = "&nbsp;(".fetch_currency_with_position($category->term_price).")"; }else{ $cprice =''; }
	/*	$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';*/
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'>" . '<label class="selectit"><input value="' . $category->term_id .'" filterdispvalue="'.$category->name.'" filterdisplayname="'.__('Categories',SF_DOMAIN).'" class="sf_checkcats" type="checkbox" name="cats[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . $cprice.'</label>';
	}

	/**
	 * Ends the element output, if needed.
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}

/* End of file */
?>