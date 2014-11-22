<?php
/*
 * Function Name: get_the_event_taxonomies
 * Return: product category and tag
 */
function get_the_event_taxonomies()
{
	global $post;
	
	$taxonomy_category = get_the_taxonomies();	
	$taxonomy_category = str_replace(CUSTOM_MENU_EVENT_CAT_TITLE.':',__('Posted In ',EDOMAIN),$taxonomy_category[CUSTOM_CATEGORY_TYPE_EVENT]);		
	$taxonomy_category = substr($taxonomy_category,0,-1);	
	return $taxonomy_category;
}
function get_event_user_category()
{
	
	/* global $post;		
	the_taxonomies(array('before'=>'<p class="bottom_line"><span class="i_category">','sep'=>'</span>&nbsp;&nbsp;<span class="i_tag">','after'=>'</span></p>')); */
	
	global $post,$htmlvar_name;
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID, $taxonomies[0]);
	$sep = ", ";
	$i = 0;
	foreach($terms as $term)
	{
		
		if($i == ( count($terms) - 1))
		{
			$sep = '';
		}
		elseif($i == ( count($terms) - 2))
		{
			$sep = __(' and ',EDOMAIN);
		}
		$term_link = get_term_link( $term, $taxonomies[0] );
		if( is_wp_error( $term_link ) )
			continue;
		$taxonomy_category .= '&nbsp;<a href="' . $term_link . '">' . $term->name . '</a>'.$sep; 
		$i++;
	}
	if(!empty($terms))
	{
		echo '<p class="bottom_line"><span class="i_category">';
		echo sprintf(__('Posted in %s',EDOMAIN),$taxonomy_category);
		echo '</span></p>';
	}
	global $post;
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	
	$tag_terms = get_the_terms($post->ID, $taxonomies[1]);
	$sep = ",";
	$i = 0;
	if($tag_terms){
	foreach($tag_terms as $term)
	{
		
		if($i == ( count($tag_terms) - 1))
		{
			$sep = '';
		}
		elseif($i == ( count($tag_terms) - 2))
		{
			$sep = __(' and ',EDOMAIN);
		}
		$term_link = get_term_link( $term, $taxonomies[1] );
		if( is_wp_error( $term_link ) )
			continue;
		$taxonomy_tag .= '<a href="' . $term_link . '">' . $term->name . '</a>'.$sep; 
		$i++;
	}
	}
	if(!empty($tag_terms) )
	{
		echo '<p class="bottom_line"><span class="i_category">';
		echo sprintf(__('Tagged In %s',EDOMAIN),$taxonomy_tag);
		echo '</span></p>';
	}
}
function get_the_event_tag()
{
	global $post;
	
	$taxonomy_tag = get_the_taxonomies();	
	$taxonomy_tag = str_replace(CUSTOM_MENU_TAG_TITLE_EVENT.':',__('Tagged In ',EDOMAIN), @$taxonomy_tag[CUSTOM_TAG_TYPE_EVENT]);		
	$taxonomy_tag = substr($taxonomy_tag,0,-1);	
	return $taxonomy_tag;
}
add_action('event_single_page_map','event_single_googlemap');// detail page direction map
function event_single_googlemap(){
	global $post;
	$templatic_settings=get_option('templatic_settings');
	if(is_single() && $templatic_settings['direction_map']=='yes'){
		
		$post_id = get_the_ID();
		if(get_post_meta($post_id,'_event_id',true)){
			$post_id=get_post_meta($post_id,'_event_id',true);
		}	
		$geo_latitude = get_post_meta($post_id,'geo_latitude',true);
		$geo_longitude = get_post_meta($post_id,'geo_longitude',true);
		$address = get_post_meta($post_id,'address',true);
		$map_type =get_post_meta($post_id,'map_view',true);
		$zooming_factor =get_post_meta(get_the_ID(),'zooming_factor',true);
		if($address){
		?>
               <div id="event_location_map" style="width:100%;">
                    <div class="event_google_map" id="event_google_map_id" style="width:100%;"> 
                    <?php include_once (TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/google_map_detail.php');?>
                    </div>  <!-- google map #end -->
               </div>
		<?php
		}
	}
}
add_action('event_inside_container_breadcrumb','event_detail_custom_field');
/*
	Function Name: event_inside_container_breadcrumb
	Description : return event detail page custom fields visible or not ( top inside container )
*/
function event_detail_custom_field(){
	if(is_single() && get_post_type()==CUSTOM_POST_TYPE_EVENT){
		global $wpdb,$post,$htmlvar_name,$pos_title;
		
		$cus_post_type = get_post_type();
		$heading_type = event_fetch_heading_post_type(get_post_type());
		
		if(count($heading_type) > 0)
		{
			foreach($heading_type as $key=>$heading)
			{	
				$htmlvar_name[$key] = get_event_single_customfields(get_post_type(),$heading,$key);//custom fields for custom post type..
			}
		}	
		return $htmlvar_name;
	}
}
/*
 * Function name: get_directory_listing_customfields
 * Return: return array for event listing custom fields
 */
function get_event_single_customfields($post_type,$heading='',$heading_key=''){
	global $wpdb,$post,$posttitle;
	$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
	$tmpdata = get_option('templatic_settings');
	if($tmpdata['templatic-category_custom_fields'] == 'No')
	{
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
									'key'     => 'show_on_detail',
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
	}
	else
	{
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
									'key'     => 'show_on_detail',
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
	}
	remove_all_actions('posts_where');		
	$post_query = null;
	remove_action('pre_get_posts','event_manager_pre_get_posts');
	remove_action('pre_get_posts','directory_pre_get_posts',12);
	add_filter('posts_join', 'custom_field_posts_where_filter');
		
	
	$post_query = get_transient( '_tevolution_query_single'.trim($post_type).trim($heading_key).$cur_lang_code );	
	if ( false === $post_query && get_option('tevolution_cache_disable')==1 ) {
		$post_query = new WP_Query($args);
		set_transient( '_tevolution_query_single'.trim($post_type).trim($heading_key).$cur_lang_code, $post_query, 12 * HOUR_IN_SECONDS );
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
			$option_title=get_post_meta($post->ID,'option_title',true);
			$option_values=get_post_meta($post->ID,'option_values',true);
			$default_value=get_post_meta($post->ID,'default_value',true);
			
			$htmlvar_name[$post_name] = array( 'type'=>$ctype,
												'label'=> $post->post_title,
												'style_class'=>$style_class,
												'option_title'=>$option_title,
												'option_values'=>$option_values,
												'default'=>$default_value,
											  );
		endwhile;
		wp_reset_query();
	}
	return $htmlvar_name;
	
}
/*
 * Function name: directory_preview_page_fields_collection
 * Return : display the additional custom field
 */
add_action('event_preview_page_fields_collection','event_preview_page_fields_collection');
function event_preview_page_fields_collection(){
	global $heading_title;
	$session=$_SESSION['custom_fields'];
	$cur_post_type='event';
	$heading_type = event_fetch_heading_post_type(get_post_type());	
	if(count($heading_type) > 0)
	{
		foreach($heading_type as $key=>$heading)
		{	
			$htmlvar_name[$key] = get_event_single_customfields($cur_post_type,$heading,$key);//custom fields for custom post type..
		}
	}
	
	$j=0;
	if(!empty($htmlvar_name)){
		echo '<div class="event_custom_field">';
		foreach($htmlvar_name as $key=>$value){
			$i=0;
			foreach($value as $k=>$val){
				$key = ($key=='basic_inf')? __('Event Information',EDOMAIN): $heading_title[$key];
				if($k!='post_title' && $k!='post_content' && $k!='post_excerpt' && $k!='post_images' && $k!='address' && $k!='end_date' && $k!='st_time' && $k!='end_time' && $k!='event_type' && $k!='reg_fees' && $k!='video' && $k!='map_view' && $k!='st_date' && $k!='phone' && $k!='email' && $k!='website' && $k!='twitter' && $k!='facebook' && $k!='google_plus' && $k!='organizer_name' && $k!='organizer_email' && $k!='organizer_logo' && $k!='organizer_address' && $k!='organizer_contact' && $k!='organizer_website' && $k!='organizer_mobile' && $k!='organizer_desc')
				{
					$field=$session[$k];
					if($val['type'] == 'multicheckbox' && $field!=""):
					if($i==0){echo '<h4 class="custom_field_headding">'.$key.'</h4>';$i++;}?>
					<p class='<?php echo $val['style_class'];?>'><label><?php echo $val['label']; ?></label> : <?php echo implode(",",$field); ?></p>
					<?php endif;
					$field=stripslashes($session[$k]);
					if($val['type'] != 'multicheckbox' && $field!=''):
					if($i==0){echo '<h4 class="custom_field_headding">'.$key.'</h4>';$i++;}?>                              
					<p class='<?php echo $val['style_class'];?>'><label><?php echo $val['label']; ?></label> : <?php echo $field;?></p>
					<?php
					endif;
				
				}// End If condition
				
				$j++;
			}// End second for each
		}// END First foreach
		echo '</div>';
	}
}
/*
 * Add action n after the post title
 */
//add_action('event_after_post_title','add_to_my_calendar',10);
/*
 * Function Name: add_to_my_calendar
 * Return : display the add to my calendar in single page
 */
function add_to_my_calendar()
{
	global $post;
	if(is_single() && get_post_type()==CUSTOM_POST_TYPE_EVENT):
	
	
        $args=array('outlook'=>1,'google_calender'=>1,'yahoo_calender'=>1,'ical_cal'=>1);
        $icalurl = get_event_ical_info($post->ID);	
		add_action('wp_footer','tmpl_show_hide_csl_script');
        ?>
      
        <li class="add_to_my_calendar">
            <div class="calendar">
                <a href="javascript:void(0);" class="calendar_show small_btn"><span><?php _e('Add to my calendar',EDOMAIN);?></span></a>     
                <div id="addtocalendar" class="addtocalendar">
                    <ul>
                    
                    <?php if($args['google_calender']){?><li class="i_google"><a href="<?php echo $icalurl['google']; ?>" target="_blank"><i class="fa fa-google"></i> <?php _e('Google Calendar',EDOMAIN);?> </a> </li><?php }
					
					if($args['outlook']){?><li class="i_calendar"><a href="<?php echo $icalurl['ical']; ?>"><i class="fa fa-calendar"></i> <?php _e('Outlook Calendar',EDOMAIN);?></a> </li><?php }
					
					if($args['ical_cal']){?><li class="i_calendar"><a href="<?php echo $icalurl['ical']; ?>"><i class="fa fa-calendar"></i> <?php _e('iCal Calendar',EDOMAIN);?> </a> </li><?php }
					
					if($args['yahoo_calender']){?><li class="i_yahoo"><a href="<?php echo $icalurl['yahoo']; ?>" target="_blank"><i class="fa fa-yahoo"></i> <?php _e('Yahoo! Calendar',EDOMAIN);?></a> </li><?php }?>
                    </ul>
                </div>
            </div>
        </li>
    <?php
	endif;
}

/* Show hide the "Add to calendar" div on hover*/

function tmpl_show_hide_csl_script(){ ?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".addtocalendar").css('visibility','hidden');
		
			jQuery(".calendar_show").mouseover(function() { jQuery("#addtocalendar").css('visibility','visible'); });
			jQuery(".calendar_show").mouseout(function() { jQuery("#addtocalendar").css('visibility','hidden'); });
			jQuery("#addtocalendar").mouseover(function() { jQuery("#addtocalendar").css('visibility','visible'); });
			jQuery("#addtocalendar").mouseout(function() { jQuery("#addtocalendar").css('visibility','hidden'); });
		});	
	</script>
<?php
}
/*
 * Function Name:get_event_ical_info
 * Return : show the display add to calendar 
 */
function get_event_ical_info($post_id) {	
	require_once(TEVOLUTION_EVENT_DIR.'functions/ical/iCalcreator.class.php');
	$cal_post = get_post($post_id);
	if ($cal_post) {
		$location = get_post_meta($post_id,'address',true);
		$start_year = date('Y',strtotime(get_post_meta($post_id,'st_date',true)));
		$start_month = date('m',strtotime(get_post_meta($post_id,'st_date',true)));
		$start_day = date('d',strtotime(get_post_meta($post_id,'st_date',true)));
		
		$end_year = date('Y',strtotime(get_post_meta($post_id,'end_date',true)));
		$end_month = date('m',strtotime(get_post_meta($post_id,'end_date',true)));
		$end_day = date('d',strtotime(get_post_meta($post_id,'end_date',true)));
		
		$start_time = get_post_meta($post_id,'st_time',true);
		$end_time = get_post_meta($post_id,'end_time',true);
		if (($start_time != '') && ($start_time != ':')) { $event_start_time = explode(":",$start_time); }
		if (($end_time != '') && ($end_time != ':')) { $event_end_time = explode(":",$end_time); }
		
		$post_title = get_the_title($post_id);
		$v = new vcalendar();                          
		$e = new vevent();  
		$e->setProperty( 'categories' , 'Events' );                   
		
		if (isset( $event_start_time)) { @$e->setProperty( 'dtstart' 	,  @$start_year, @$start_month, @$start_day, @$event_start_time[0], @$event_start_time[1], 00 ); } else { $e->setProperty( 'dtstart' ,  $start_year, $start_month, $start_day ); } // YY MM dd hh mm ss
		if (isset($event_end_time)) { @$e->setProperty( 'dtend'   	,  $end_year, $end_month, $end_day, $event_end_time[0], $event_end_time[1], 00 );  } else { $e->setProperty( 'dtend' , $end_year, $end_month, $end_day );  } // YY MM dd hh mm ss
		$e->setProperty( 'description' 	, strip_tags($cal_post->post_excerpt) ); 
		if (isset($location)) { $e->setProperty( 'location'	, $location ); } 
		$e->setProperty( 'summary'	, $post_title );                 
		$v->addComponent( $e );
		
		$templateurl = get_template_directory_uri().'/cache/';
		$home = home_url();
		$dir = str_replace($home,'',$templateurl);
		$dir = str_replace('/wp-content/','wp-content/',$dir);
		
		$v->setConfig( 'directory', $dir ); 
		$v->setConfig( 'filename', 'event-'.$post_id.'.ics' ); 
		$v->saveCalendar(); 
		$output['ical'] = $templateurl.'event-'.$post_id.'.ics';
		
		////GOOGLE URL//
		$google_url = "http://www.google.com/calendar/event?action=TEMPLATE";
		$google_url .= "&amp;text=".urlencode($post_title);
		if (isset($event_start_time) && isset($event_end_time)) { 
			$google_url .= "&amp;dates=".@$start_year.@$start_month.@$start_day."T".str_replace('.','',@$event_start_time[0]).str_replace('.','',@$event_start_time[1])."00/".@$end_year.@$end_month.@$end_day."T".str_replace('.','',@$event_end_time[0]).str_replace('.','',@$event_end_time[1])."00"; 
		} else { 
			$google_url .= "&amp;dates=".$start_year.$start_month.$start_day."/".$end_year.$end_month.$end_day; 
		}
		
		$google_url .= "&amp;sprop=website:".$home;
		$google_url .= "&amp;details=".strip_tags($cal_post->post_excerpt);
		if (isset($location)) { $google_url .= "&amp;location=".$location; } else { $google_url .= "&amp;location=Unknown"; }
		$google_url .= "&amp;trp=true";
		$output['google'] = $google_url;
		////YAHOO URL///
		$yahoo_url = "http://calendar.yahoo.com/?v=60&amp;view=d&amp;type=20";
		$yahoo_url .= "&amp;title=".str_replace(' ','+',$post_title);
		if (isset($event_start_time)) 
		{ 
			$yahoo_url .= "&amp;st=".@$start_year.@$start_month.@$start_day."T".@$event_start_time[0].@$event_start_time[1]."00";
			$yahoo_url .= "&et=".$end_year.$end_month.$end_day."T".$event_end_time[0].$event_end_time[1]."00";   
		}
		else
		{ 
			$yahoo_url .= "&amp;st=".$start_year.$start_month.$start_day;
		}
		if(isset($event_end_time))
		{
			//$yahoo_url .= "&dur=".$event_start_time[0].$event_start_time[1];
		}
		$yahoo_url .= "&amp;desc=".__('For+details,+link+').get_permalink($post_id).' - '.str_replace(' ','+',strip_tags($cal_post->post_excerpt));
		$yahoo_url .= "&amp;in_loc=".str_replace(' ','+',$location);
		$output['yahoo'] = $yahoo_url;
	}
	return $output;
}

add_filter('tmpl_slider_image_count','tmpl_event_slider_image_count');
function tmpl_event_slider_image_count($count){
	if(get_post_type()=='event'){
		$count='11';
	}
	
	return $count;
}

/* Display related listings on event detail page */

add_action('tmpl_related_events','tmpl_get_related_events');


if(!function_exists('tmpl_get_related_events')){
	function tmpl_get_related_events(){
		
		global $post,$htmlvar_name,$wpdb,$wp_query;
		/* get all the custom fields which select as " Show field on listing page" from back end */
		
		if(function_exists('tmpl_get_category_list_customfields')){
			$htmlvar_name = tmpl_get_category_list_customfields(CUSTOM_POST_TYPE_EVENT);
		}else{
			global $htmlvar_name;
		}
		$wp_query->set('is_related',1);
		$related_posts = tmpl_get_related_posts_query();
			if(!empty($related_posts)){
			
			echo "<h2>"; _e('Related Events',EDOMAIN); echo "</h2>";
			echo "<div id='loop_listing_taxonomy' class='grid'>";
			while($related_posts->have_posts()){  global $post;
				$related_posts->the_post();
				do_action('event_before_post_loop');?>
				<div class="post <?php templ_post_class();?>">  
					<?php do_action('event_before_category_page_image');           /*do_action before the post image */
						do_action('tmpl_before_category_page_image');           /*do_action before the post image */
					
						do_action('event_category_page_image');
					  
						do_action('event_after_category_page_image');           /*do action after the post image */
						do_action('event_before_post_entry'); ?>
						<div class="entry"> 
							<!--start post type title -->
							<?php do_action('event_before_post_title');         /* do action for before the post title.*/ ?>
							<div class="event-wrapper">
							<div class="event-title">
						   
								<?php do_action('templ_post_title');                /* do action for display the single post title */
								 ?>
						   
							</div>
							<?php do_action('event_after_post_title');          /* do action for after the post title.*/?>
							<!--end post type title -->
							<!-- Entry details start -->
							<div class="entry-details">
							
							<?php  /* Hook to get Entry details - Like address,phone number or any static field  */  
							do_action('event_post_info');   ?>     
							
							</div> 
							</div>
						   
						   <!--Start Post Content -->
						   <?php do_action('event_before_post_content');       /* do action for before the post content. */ 
						   
							do_action('templ_taxonomy_content');	
						   
							do_action('event_after_post_content');        /* do action for after the post content. */
						   
							do_action('templ_the_taxonomies');  
						   
							do_action('event_after_taxonomies');?>
						</div>
					<?php do_action('event_after_post_entry');?>
				 </div>
				<?php do_action('event_after_post_loop');
			}
			echo "</div>";
			wp_reset_query();
		}
		
	}
}
?>