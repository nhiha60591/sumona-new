<?php
add_action('event_before_container_breadcrumb','event_breadcrumb');
add_action('tmpl_device_sorting_option','tmpl_device_archives_sorting_opt');
/*
 * display the bread crumb
 * Function Name:single_post_type_breadcrumb 
 */
function event_breadcrumb()
{
	if ( current_theme_supports( 'breadcrumb-trail' ) && supreme_get_settings('supreme_show_breadcrumb')){
		breadcrumb_trail( array( 'separator' => '&raquo;' ) );
	}
}
/* Event Listing Functions */
add_action('event_subcategory','event_subcategory');/* */
add_action('event_after_subcategory','event_after_subcategory_content');/*Display the event related tabs, listview, grid view icon and also sorting dropdowns*/
add_action('event_category_page_image','event_category_page_image');
add_action('init','event_remove_to_favourites',11);
/* remove add to favourite text comes from tevolution */
function event_remove_to_favourites(){
	global $post;
	remove_action('templ_post_title','tevolution_favourite_html',11,@$post);
}
add_action('event_post_info','templ_after_event_content',13);
add_action('event_before_post_title','event_before_post_title');
/*archive page */
add_action('event_archive_page_image','event_category_page_image');
add_action('event_after_archive_title','event_after_subcategory_content');
add_action('event_after_taxonomies','event_after_taxonomies_content');
/*Search Page  */
add_action('event_before_loop_search','event_after_subcategory_content');
if(isset($_REQUEST['nearby']) && $_REQUEST['nearby'] == 'search')
{
	add_action('event_after_search_title','event_listing_city_name');
}
/*
 * Function Name: event_subcategory
 * Display the parent categories sub child categories list
 */
function event_subcategory(){
	global $wpdb,$wp_query;	
	$term_id = $wp_query->get_queried_object_id();
	$taxonomy_name = CUSTOM_CATEGORY_TYPE_EVENT;
	do_action('tevolution_category_query');
	$featured_catlist_list =  wp_list_categories('title_li=&child_of=' . $term_id .'&echo=0&taxonomy='.$taxonomy_name.'&show_count=0&hide_empty=1&pad_counts=0&show_option_none=&orderby=name&order=ASC');
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		remove_filter( 'terms_clauses','locationwise_change_category_query',10,3 );	
	}
	if(!strstr(@$featured_catlist_list,'No categories')){
		echo '<div id="sub_event_categories">';
		echo '<ul>';
		echo $featured_catlist_list;
		echo '</ul>';
		echo '</div>';
	}
}

/*
 * return event archive link.
 */
if (!function_exists('get_archive_link')){
  function get_archive_link( $post_type ){
    global $wp_post_types;
    $archive_link = false;
    if (isset($wp_post_types[$post_type])){
      $wp_post_type = $wp_post_types[$post_type];
      if ($wp_post_type->publicly_queryable)
        if ($wp_post_type->has_archive && $wp_post_type->has_archive!==true)
          $slug = $wp_post_type->has_archive;
        else if (isset($wp_post_type->rewrite['slug']))
          $slug = $wp_post_type->rewrite['slug'];
        else
          $slug = $post_type;
      $archive_link = get_option( 'siteurl' ) . "/{$slug}/";
    }
    return apply_filters( 'archive_link', $archive_link, $post_type );
  }
}

/*
 * Function Name: event_after_subcategory_content
 */
function event_after_subcategory_content(){
	global $wpdb,$wp_query;
	/* return the events separation tabs upcoming/past/current */	
	apply_filters('tmpl_events_listings_tabs',tmpl_events_listings_tabs_html());
}

/* Display the event featured image or event image */
function event_category_page_image(){
	global $post,$wpdb,$wp_query;
	
	$post_id = get_the_ID();
	if(get_post_meta($post_id,'_event_id',true)){
		$post_id=get_post_meta($post_id,'_event_id',true);
	}
	
	$featured=get_post_meta($post_id,'featured_c',true);
	$featured=($featured=='c')?'featured_c':'';
	if ( has_post_thumbnail()):
		echo '<div class="event_img">';
		do_action('inside_listing_image');
		echo '<a href="'.get_permalink().'">';
		if($featured){echo '<span class="featured_tag">'.__('Featured',EDOMAIN).'</span>';}
		the_post_thumbnail('event-listing-image'); 
		echo '</a></div>';
	else:
		if(function_exists('bdw_get_images_plugin'))
		{
			$post_img = bdw_get_images_plugin($post_id,'event-listing-image');						
			$thumb_img = @$post_img[0]['file'];
			$attachment_id = @$post_img[0]['id'];
			$attach_data = get_post($attachment_id);
			$img_title = $attach_data->post_title;
			$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
		}
		?>
		<div class="event_img"> 
			<?php do_action('inside_listing_image');?>
			<a href="<?php the_permalink();?>">
			<?php if($featured){echo '<span class="featured_tag">'.__('Featured',EDOMAIN).'</span>';}?>
			<?php if($thumb_img):?>
				<img itemprop="image" src="<?php echo $thumb_img; ?>"  alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" />
			<?php else:?>    
				<img itemprop="image" src="<?php echo TEVOLUTION_EVENT_URL; ?>images/noimage-220x150.jpg" alt=""  />
			<?php endif;
			?>
			</a>
			<?php do_action('after_entry_image'); ?>		
		</div>
   <?php endif;
}

/* Display the start date and time before the title */
function event_before_post_title(){
	global $post;	
	$is_archive = get_query_var('is_ajax_archive');
	if(is_archive() || $is_archive == 1 || defined( 'DOING_AJAX' )){
		$st_date=strtotime(get_post_meta($post->ID,'st_date',true));
		?>
		<span class="date">
			<?php echo date_i18n("d",$st_date); ?>
			<span><?php echo date_i18n("M",$st_date); ?></span>
		</span>
		<?php
	}
}

/* Display address and other event fields on category/archive  pages */
function templ_after_event_content($htmlvar_name = ''){
	global $post;

	if(!empty($htmlvar_name))
		$htmlvar_name = $htmlvar_name;
	else
		global $htmlvar_name;	
	
	$address=get_post_meta($post->ID,'address',true);
	$phone=get_post_meta($post->ID,'phone',true);

	$starttime=get_post_meta($post->ID,'st_time',true);
	$endtime=get_post_meta($post->ID,'end_time',true);
	
	$date_formate=get_option('date_format');
	$time_formate=get_option('time_format');
	$st_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'st_date',true)));
	$end_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'end_date',true)));
	
	$date='<span itemprop="startDate" content="'.date('Y-m-d',strtotime($st_date)).'T'.$starttime.'">'.$st_date.'</span> '. __('To',EDOMAIN).' <span itemprop="endDate" content="'.date('Y-m-d',strtotime($end_date)).'T'.$endtime.'">'.$end_date."</span>";
	
	if($starttime!='')
		$st_time=date_i18n($time_formate,strtotime($starttime));
	if($endtime!='')
		$end_time=date_i18n($time_formate,strtotime($endtime));	
	do_action('tevolution_title_text'); 
	$is_archive = get_query_var('is_ajax_archive');	
	$is_related = get_query_var('is_related');	
	if((is_archive() || $is_archive == 1 || $is_related ==1 || defined( 'DOING_AJAX' ) || @$_GET['post_type'] == CUSTOM_POST_TYPE_EVENT || (is_array($_GET['post_type']) && in_array(CUSTOM_POST_TYPE_EVENT,@$_GET['post_type']))) && ($post->post_type ==CUSTOM_POST_TYPE_EVENT || @$_GET['post_type'] == CUSTOM_POST_TYPE_EVENT )){ 
		
		if(( @$_GET['post_type'] == CUSTOM_POST_TYPE_EVENT  || @$_GET['t'] == CUSTOM_POST_TYPE_EVENT) && is_search()){
			echo ($address)? '<p class="address">'.$address.'</p>' : '';
		  
			echo ($st_date && $end_date)? '<p class="event_date"><span>'.$date.'</span></p>' : '';		
			echo ($st_time && $end_time)? '<p class="time"><span>'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>' : '';		
		
		}else{
			echo ( @$htmlvar_name['address'] || @$htmlvar_name['basic_inf']['address'])? '<p class="address" itemprop="address">'.$address.'</p>' : '';
			echo ( (@$htmlvar_name['st_date'] && $htmlvar_name['end_date']) || (@$htmlvar_name['basic_inf']['st_date'] &&  @$htmlvar_name['basic_inf']['end_date']))? '<p class="event_date"><span>'.$date.'</span></p>' : '';		
			echo ((@$htmlvar_name['st_time'] && $htmlvar_name['end_time']) || (@$htmlvar_name['basic_inf']['st_time'] &&  @$htmlvar_name['basic_inf']['end_time']) &&($starttime!='' && $endtime!=''))? '<p class="time"><span>'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>' : '';	
			echo ( (@$htmlvar_name['phone'] || @$htmlvar_name['contact_info']['phone']) && $phone != '')? '<p class="phone">'.$phone.'</p>' : '';			
		}
		
		
		if( @$htmlvar_name['twitter']  || @$htmlvar_name['facebook'] || @$htmlvar_name['google_plus'])
		{
			$twitter=get_post_meta($post->ID,'twitter',true);
			$facebook=get_post_meta($post->ID,'facebook',true);
			$google_plus=get_post_meta($post->ID,'google_plus',true);
			echo "<div class='social_wrapper'>";
				
			if($twitter != '' && $htmlvar_name['twitter'] || @$htmlvar_name['contact_info']['twitter'])
			{
			?>
				<a class='twitter' href="<?php echo $twitter;?>"><label><?php _e('twitter',EDOMAIN); ?></label></a>
			<?php
			}
			if($facebook != '' && $htmlvar_name['facebook'] || @$htmlvar_name['contact_info']['facebook'])
			{
			?>
				<a class='facebook' href="<?php echo $facebook;?>"><label><?php _e('facebook',EDOMAIN); ?></label></a>
			<?php
			}
			if($google_plus != '' && $htmlvar_name['google_plus'] || @$htmlvar_name['contact_info']['google_plus'])
			{
			?>
				<a class='google_plus' href="<?php echo $google_plus;?>"><label><?php _e('Google+',EDOMAIN); ?></label></a>
			<?php
			}
			echo "</div>";
		}
		$j=0;
		if(!empty($htmlvar_name)){
			foreach($htmlvar_name as $key=>$value){
				$i=0;
				if(isset($value) && $value!=''){

					$key = ($key=='basic_inf')?__('Event Information',EDOMAIN): $key;	
					if($key!='post_title' && $key!='post_content' && $key!='post_excerpt' && $key!='post_images' && $key!='st_time' && $key!='end_date' && $key!='st_date' && $key!='end_time' && $key!='address' && $key != 'twitter' && $key != 'facebook' && $key != 'google_plus' && $key != 'phone' )
					{	
						//if($i==0){echo '<h4 class="custom_field_headding">'.$key.'</h4>';}
						$field= get_post_meta($post->ID,$key,true);
						if($value['type'] == 'multicheckbox' && $field!=""): ?>
						<p class='<?php echo $value['style_class'];?>'><label><?php echo $value['label']; ?></label>: <?php echo implode(",",$field); ?></p>
						<?php endif;
						if($value['type'] != 'multicheckbox' && $field!=''):
							?>                              
								<p class='<?php echo $value['style_class'];?>'><label><?php echo $value['label']; ?></label>: <?php echo $field;?></p>
							<?php
						endif;
					}
					$i++;
					$j++;
					
				}				
			}
		}
		
	}//END archive condition 
	
	
}//END templ_after_event_content

/* Display single ratings on detail page */

add_action('event_display_rating','event_display_rating');
function event_display_rating($post_id){
	
	/*action to show rating*/
	do_action('show_single_multi_rating');
}
/*
	display rating views, and other content
*/
function event_after_taxonomies_content(){	
	global $post,$htmlvar_name,$templatic_settings;	
	$is_archive = get_query_var('is_ajax_archive');
	
	if((is_archive() || $is_archive == 1 || defined( 'DOING_AJAX' )) && $post->post_type ==CUSTOM_POST_TYPE_EVENT){			
		$googlemap_setting=get_option('city_googlemap_setting');	
		echo '<div class="rev_pin">';
		echo '<ul>';
		$post_id=get_the_ID();
		$comment_count= count(get_comments(array('post_id' => $post_id)));
		$review=($comment_count <=1 )? __('review',EDOMAIN):__('reviews',EDOMAIN);
				$review=apply_filters('tev_review_text',$review);
	
		if(current_theme_supports('tevolution_my_favourites') ):?> 
               <li class="favourite"><?php tevolution_favourite_html();?></li>
          <?php endif;
		if(get_option('default_comment_status')=='open' || $post->comment_status =='open'){   
			?>               
			<li class="review"> <?php echo '<a id="reviews_show" href="'.get_permalink($post_id).'#comments">'.$comment_count.' '.$review.'</a>';?></li>
			<?php
		}
		if($templatic_settings['category_googlemap_widget'] != 'yes' && $templatic_settings['pippoint_oncategory'] ==1):?> 
          	<li class='pinpoint'><a id="pinpoint_<?php echo $post_id;?>" class="ping" href="#map_canvas"><?php _e('Pinpoint',EDOMAIN);?></a></li>               
		<?php endif;
		
		echo '</ul>';
		echo '</div>';
	}
}
add_action('event_calendar_after_taxnomies','event_calendar_after_taxonomies_content');
/*
	display rating views, and other content
*/
function event_calendar_after_taxonomies_content(){	
	global $post,$htmlvar_name;	
	$is_archive = get_query_var('is_ajax_archive');
	
	if((is_archive() || $is_archive == 1) && $post->post_type ==CUSTOM_POST_TYPE_EVENT){			
		$googlemap_setting=get_option('city_googlemap_setting');	
		echo '<div class="rev_pin">';
		echo '<ul>';
		$post_id=get_the_ID();
		$comment_count= count(get_comments(array('post_id' => $post_id)));
		$review=($comment_count <=1 )? __('review',EDOMAIN):__('reviews',EDOMAIN);
		$review=apply_filters('tev_review_text',$review);
		?>
          <?php if(current_theme_supports('tevolution_my_favourites') ):?> 
               <li class="favourite"><?php tevolution_favourite_html();?></li>
          <?php endif;
		if(get_option('default_comment_status')=='open' || $post->comment_status =='open'){   
			?>               
			<li class="review"> <?php echo '<a id="reviews_show" href="'.get_permalink($post_id).'#comments">'.$comment_count.' '.$review.'</a>';?></li>
			<?php
		}
		
		echo '</ul>';
		echo '</div>';
	}
}
function event_listing_city_name()
{
	global $post;
	echo sprintf(__('Here are the results found for the %s you are looking for, matching to your specified criteria',EDOMAIN),$post->post_type); 
}

/*
	return the upcoming, past and current events tabs in listings page 
*/

/* this action will display the tabs on home page if latest post option is selected form settings->reading settings  */

add_action('supreme_before_article_list','tmpl_events_listings_tabs_html');
add_action('tmpl_device_event_tabs','tmpl_events_listings_tabs_html');

function tmpl_events_listings_tabs_html(){ ?>
	
		<?php 
		global $wp_query;
		$current_term = $wp_query->get_queried_object();
		$templatic_settings=get_option('templatic_settings');
		$googlemap_setting=get_option('city_googlemap_setting');
		
		/* get and set the permalink on different pages for tabs */
		
		if(!is_tax() && is_archive() && !is_search())
		{	
			$permalink = get_post_type_archive_link('event');
			$permalink=str_replace('&event_sortby=alphabetical&sortby='.$_REQUEST['sortby'],'',$permalink);
		}elseif(is_search()){
			$search_query_str=str_replace('&event_sortby=alphabetical&sortby='.$_REQUEST['sortby'],'',$_SERVER['QUERY_STRING']);
			$permalink=get_bloginfo('siteurl')."?".$search_query_str;
		}else{
			if(!is_home()){
				$permalink =  get_term_link( $current_term->slug, $current_term->taxonomy );
				if(isset($_REQUEST['sortby']) && $_REQUEST['sortby'] !='')
				$permalink=str_replace('&event_sortby=alphabetical&sortby='.$_REQUEST['sortby'],'',$permalink);
			}
		}
		
		if(strstr($permalink,'?'))
		{
			$upcoming= $permalink."&amp;etype=upcoming";
			$current= $permalink."&amp;etype=current";
			$past= $permalink."&amp;etype=past";
		}else
		{ 
			$upcoming = $permalink."?etype=upcoming";
			$current= $permalink."?etype=current";
			$past= $permalink."?etype=past";
		}
		$_REQUEST['etype']=!isset($_REQUEST['etype'])?'current':$_REQUEST['etype'];
		$upcoming_active=(isset($_REQUEST['etype']) && $_REQUEST['etype'] =='upcoming')?'active':'';
		$current_active=(isset($_REQUEST['etype']) && $_REQUEST['etype'] =='current')?'active':'';
		$past_active=(isset($_REQUEST['etype']) && $_REQUEST['etype'] =='past')?'active':'';
			
		if((is_home() && count($templatic_settings['home_listing_type_value']) ==1 && $templatic_settings['home_listing_type_value'][0] =='event') || is_search() || is_tax() || is_archive()){
			$hide_past_event = get_option('event_manager_setting'); ?>
			<ul class="tabs">
				<?php
				if(@$hide_past_event['hide_past_event'] != 'yes'){ ?>
				
					<li class="tab-title <?php echo $past_active;?>" role="presentational"><a href="<?php echo $past;?>" role="tab" tabindex="1" aria-selected="false"><?php _e('Past Events',DIR_DOMAIN);?></a></li>
				
				<?php } ?>
					<li class="tab-title <?php echo $current_active;?>" role="presentational"><a href="<?php echo $current; ?>" role="tab" tabindex="2" aria-selected="false"><?php _e('Current Events',DIR_DOMAIN);?></a></li>
					<li class="tab-title <?php echo $upcoming_active;?>" role="presentational"><a href="<?php echo $upcoming; ?>" role="tab" tabindex="3" aria-selected="false"><?php _e('Upcoming Events',DIR_DOMAIN);?></a></li>
				<?php do_action('event_manager_extra_tabs');  ?>
			</ul>  
			<?php

		}else{
			//if($templatic_settings['home_listing_type_value'])

		}
		?>
	
<?php
}

/* sorting options for category page and archive page */

add_action('tmpl_after_sortby_date_desc','tmpl_sorting_for_event');
function tmpl_sorting_for_event(){
	global $wpdb,$wp_query,$sort_post_type;
	
	if(!is_search()){
		$post_type = (get_post_type()!='')? get_post_type() : get_query_var('post_type');
		$sort_post_type = apply_filters('tmpl_tev_sorting_for_'.$post_type,$post_type);
		
	}else{
		/* on search page what happens if user search with multiple post types */
		if(isset($_REQUEST['post_type'])){
			if(is_array($_REQUEST['post_type']) && count($_REQUEST['post_type'])==1){
				$sort_post_type= $_REQUEST['post_type'][0];
			}else{
				$sort_post_type= $_REQUEST['post_type'];
			}
		}
			if(!$cur_post_type){
				$sort_post_type='directory';
			}
	}
	
	$templatic_settings=get_option('templatic_settings');
	$sel_sort_by = $_REQUEST[$sort_post_type.'_sortby'];
	$sel_class = 'selected=selected';
	if(get_post_type()=='event'):
		if(!empty($templatic_settings['sorting_option']) && in_array('stdate_low_high',$templatic_settings['sorting_option'])):?>
			<option value="stdate_low_high" <?php if($sel_sort_by =='stdate_low_high'){ echo $sel_class; } ?>><?php _e('Start Date low to high',DIR_DOMAIN);?></option>
		<?php endif;
		
		if(!empty($templatic_settings['sorting_option']) && in_array('stdate_high_low',$templatic_settings['sorting_option'])):?>
			<option value="stdate_high_low" <?php if($sel_sort_by =='stdate_high_low'){ echo $sel_class; } ?> ><?php _e('Start Date high to low',DIR_DOMAIN);?></option>
		<?php endif;
	endif;
}
?>