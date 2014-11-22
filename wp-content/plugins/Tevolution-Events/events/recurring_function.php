<?php
add_action('wp_footer','recurring_event_js');
add_action('admin_head','recurring_event_js');
function recurring_event_js(){
	global $pagenow,$post;	
	if(is_admin() &&( (isset($_REQUEST['post_type']) && $_REQUEST['post_type']=='event') ||  ($pagenow=='post.php' && @$_REQUEST['action']=='edit'))){
		wp_enqueue_script('recurring_js', TEVOLUTION_EVENT_URL.'js/recurring_event.js');
	}	
	
	if((is_page() && (get_post_meta($post->ID,'submit_post_type',true)=='event' && get_post_meta($post->ID,'is_tevolution_submit_form',true)==1)) || (is_single() && get_post_type()=='event' && isset($_REQUEST['action']) && $_REQUEST['action']=='edit')  ){
		wp_enqueue_script('recurring_js', TEVOLUTION_EVENT_URL.'js/recurring_event.js');
	}
}
/** 
Function name : event_rec_option_items
Description : to fetch the recurrence of an event BOF **/
function event_rec_option_items($array, $saved_value) {
	$output = "";
	foreach($array as $key => $item) {
		$selected ='';
		if ($key == $saved_value)
			$selected = "selected='selected'";
		$output .= "<option value='".esc_attr($key)."' $selected >".esc_html($item)."</option>\n";
	}
	echo $output;
}
/** to fetch the recurrence of an event EOF **/
/**
Function name: event_checkbox_items
Description : to fetch the days of an event BOF **/
function event_checkbox_items($name, $array, $saved_values, $horizontal = true) {
	$output = "";
	foreach($array as $key => $item) {
		$checked = "";
		if (in_array($key, $saved_values))		
			 $checked = "checked='checked'";
		$output .=  "<input type='checkbox' name='".esc_attr($name)."' value='".esc_attr($key)."' $checked /> ".esc_html($item)."&nbsp; ";
		if(!$horizontal)
			$output .= "<br/>\n";
	}
	echo $output;
}
/** 
Function name : event_get_hour_format
Description : to fetch the hour format of an event **/
function event_get_hour_format(){
	return get_option('date_format_custom') ? "H:i":"h:i A";
}
/** 
Function name : event_get_days_names
Description : to fetch the days name **/
function event_get_days_names(){
	return array ( 0 => __( 'Sun',EDOMAIN ), 1 => __( 'Mon',EDOMAIN ), 2 => __( 'Tue',EDOMAIN ), 3 => __( 'Wed',EDOMAIN ), 4 => __( 'Thu',EDOMAIN ), 5 => __( 'Fri',EDOMAIN ), 6 => __( 'Sat',EDOMAIN ) );
}
add_action('save_post', 'save_recurring_event',13);
function save_recurring_event($last_postid)
{
	global $post,$wpdb;	
	if(!isset($_REQUEST['event_type'])){
		$last_postid = ($_POST['ID']!='')?$_POST['ID'] : $last_postid;
		update_post_meta($last_postid,'event_type','Regular event');
		$_REQUEST['event_type']='Regular event';
	}
	$event_type = @$_REQUEST['event_type'];
	if(trim(strtolower($event_type)) == trim(strtolower('Recurring event')) && isset($_REQUEST['cur_post_type']) &&  $_REQUEST['cur_post_type'] == 'event')
	{
		
		$st_date = get_post_meta($last_postid,'st_date',true);
		$end_date = get_post_meta($last_postid,'end_date',true);
		if(@$_POST['allow_to_create_rec'])
			$allow_to_create_rec = update_post_meta($last_postid,'allow_to_create_rec',$_POST['allow_to_create_rec']);
		$args =	array( 
						'post_type' => 'event',
						'posts_per_page' => 1	,
						'post_status' => 'recurring',
						'meta_query' => array(
						'relation' => 'AND',
							array(
									'key' => '_event_id',
									'value' => $last_postid,
									'compare' => '=',
									'type'=> 'text'
								),
							)
						);
			$post_query = null;
			$child_data = new WP_Query($args);
			
			$parent_data = get_post($last_postid);
			$parent_post_status = get_post_meta($last_postid,'tmpl_post_status',true);
			$fetch_status = 'recurring';
			/* to delete the old recurrences BOF */
			$args =	array( 
						'post_type' => 'event',
						'posts_per_page' => -1	,
						'post_status' => array($fetch_status),
						'meta_query' => array(
						'relation' => 'AND',
							array(
									'key' => '_event_id',
									'value' => $last_postid,
									'compare' => '=',
									'type'=> 'text'
								),
							)
						);
			$post_query = null;
			$post_query = new WP_Query($args);
			if($post_query){
				while ($post_query->have_posts()) : $post_query->the_post();
						wp_delete_post($post->ID);
				endwhile;
				wp_reset_query();
			}
			
			update_post_meta($last_postid, 'recurrence_occurs', $_REQUEST['recurrence_occurs']);
			update_post_meta($last_postid, 'recurrence_per', $_REQUEST['recurrence_per']);
			update_post_meta($last_postid, 'recurrence_onday', $_REQUEST['recurrence_onday']);
	
			update_post_meta($last_postid, 'recurrence_bydays', implode(',',$_REQUEST['recurrence_bydays']));
			if($_REQUEST['featured_type'])
			{ 
				if($_REQUEST['featured_type']):
					if($_REQUEST['featured_type'] == 'both'):
						 update_post_meta($last_postid, 'featured_c', 'c');
						 update_post_meta($last_postid, 'featured_h', 'h');
						 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
					endif;
					if($_REQUEST['featured_type'] == 'c'):
						 update_post_meta($last_postid, 'featured_c', 'c');
						 update_post_meta($last_postid, 'featured_h', 'n');
						 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
					endif;	 
					if($_REQUEST['featured_type'] == 'h'):
						 update_post_meta($last_postid, 'featured_h', 'h');
						 update_post_meta($last_postid, 'featured_c', 'n');
						 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
					endif;
					if($_REQUEST['featured_type'] == 'none'):
						 update_post_meta($last_postid, 'featured_h', 'n');
						 update_post_meta($last_postid, 'featured_c', 'n');
						 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
					endif;	 
				else:
					 update_post_meta($last_postid, 'featured_type', 'none');
					 update_post_meta($last_postid, 'featured_c', 'n');
					 update_post_meta($last_postid, 'featured_h', 'n');
				endif;
			}
			$st_date = get_post_meta($last_postid,'st_date',true);
			$end_date = get_post_meta($last_postid,'end_date',true);
			
			if( isset($_REQUEST['st_time']) ){
			$count = explode(':',rtrim($_REQUEST['st_time']));
			if( count($count) > 2 ){
				$get_sttime =  date("H:i", strtotime(rtrim($_REQUEST['st_time'])));
			}else{
				$_REQUEST['st_time'] = $_REQUEST['st_time'].":00";
				$get_sttime = date("H:i", strtotime(rtrim($_REQUEST['st_time'])));
			}
			}else{
				$get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
			}
			if( isset($_REQUEST['end_time']) ){
				$count = explode(':',rtrim($_REQUEST['end_time']));
				if( count($count) > 2 ){
					$get_end_time = date("H:i", strtotime(rtrim($_REQUEST['end_time'])));
				}else{
					$_REQUEST['end_time'] = $_REQUEST['end_time'].":00";
					$get_end_time = date("H:i", strtotime(rtrim($_REQUEST['end_time'])));
				}
			}else{
				$get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
			}
			
			$event_start_date = $_REQUEST['st_date'].' '.$get_sttime;
			$event_end_date = $_REQUEST['end_date'].' '.$get_end_time;
			update_post_meta($last_postid, 'set_st_time', trim($event_start_date));
			update_post_meta($last_postid, 'set_end_time', trim($event_end_date));
			update_post_meta($last_postid, 'recurrence_onweekno', $_REQUEST['recurrence_onweekno']);
			update_post_meta($last_postid, 'recurrence_days', $_REQUEST['recurrence_days']);	
			update_post_meta($last_postid, 'monthly_recurrence_byweekno', $_REQUEST['monthly_recurrence_byweekno']);	
			update_post_meta($last_postid, 'recurrence_byday', $_REQUEST['recurrence_byday']);	
			update_post_meta($last_postid, 'st_date', trim($_REQUEST['st_date']));	
			update_post_meta($last_postid, 'end_date', trim($_REQUEST['end_date']));	
			update_post_meta($last_postid, 'st_time', trim($_REQUEST['st_time']));	
			update_post_meta($last_postid, 'end_time', trim($_REQUEST['end_time']));	
			update_post_meta($last_postid, 'address', trim($_REQUEST['address']));			
			templ_save_recurrence_events( $_REQUEST,$last_postid);// to save event recurrences - front end
		
		/* to delete the old recurrences EOF */
	
		$start_date = templ_recurrence_dates($last_postid);
		update_post_meta($last_postid,'recurring_search_date',$start_date);
	
	/*from recurring event to regular event code start here*/
	
	$event_type_regular = get_post_meta($last_postid,'event_type',true);
		if(trim(strtolower($event_type_regular)) == trim(strtolower('Recurring event')) && isset($_REQUEST['cur_post_type']) &&  $_REQUEST['cur_post_type'] == 'event' && trim(strtolower($event_type)) == trim(strtolower('Regular event')))
		{
			
			$st_date = get_post_meta($last_postid,'st_date',true);
			$end_date = get_post_meta($last_postid,'end_date',true);
			
			if( isset($_REQUEST['st_time']) ){
			$count = explode(':',rtrim($_REQUEST['st_time']));
			if( count($count) > 2 ){
				$get_sttime =  date("H:i", strtotime(rtrim($_REQUEST['st_time'])));
			}else{
				$_REQUEST['st_time'] = $_REQUEST['st_time'].":00";
				$get_sttime = date("H:i", strtotime(rtrim($_REQUEST['st_time'])));
			}
			}else{
				$get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
			}
			if( isset($_REQUEST['end_time']) ){
				$count = explode(':',rtrim($_REQUEST['end_time']));
				if( count($count) > 2 ){
					$_REQUEST['end_time'] = $_REQUEST['end_time'].":00";
					$get_end_time = date("H:i", strtotime(rtrim($_REQUEST['end_time'])));
				}else{
					$get_end_time = date("H:i", strtotime(rtrim($_REQUEST['end_time'])));
				}
			}else{
				$get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
			}
			
			$event_start_date = $_REQUEST['st_date'].' '.$get_sttime;
			$event_end_date = $_REQUEST['end_date'].' '.$get_end_time;
			update_post_meta($last_postid, 'set_st_time', $event_start_date);
			update_post_meta($last_postid, 'set_end_time', $event_end_date);	
			
				$parent_data = get_post($last_postid);
				$parent_post_status = get_post_meta($last_postid,'tmpl_post_status',true);
				$fetch_status = 'recurring';
				/* to delete the old recurrences BOF */
				$args =	array( 
							'post_type' => 'event',
							'posts_per_page' => -1	,
							'post_status' => array($fetch_status),
							'meta_query' => array(
							'relation' => 'AND',
								array(
										'key' => '_event_id',
										'value' => $last_postid,
										'compare' => '=',
										'type'=> 'text'
									),
								)
							);
				$post_query = null;
				$post_query = new WP_Query($args);
				if($post_query){
					while ($post_query->have_posts()) : $post_query->the_post();
							wp_delete_post($post->ID);
					endwhile;
					wp_reset_query();
				}
				
				update_post_meta($last_postid, 'recurrence_occurs', $_REQUEST['recurrence_occurs']);
				update_post_meta($last_postid, 'recurrence_per', $_REQUEST['recurrence_per']);
				update_post_meta($last_postid, 'recurrence_onday', $_REQUEST['recurrence_onday']);
		
				update_post_meta($last_postid, 'recurrence_bydays', implode(',',$_REQUEST['recurrence_bydays']));
				if($_REQUEST['featured_type'])
				{ 
					if($_REQUEST['featured_type']):
						if($_REQUEST['featured_type'] == 'both'):
							 update_post_meta($last_postid, 'featured_c', 'c');
							 update_post_meta($last_postid, 'featured_h', 'h');
							 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
						endif;
						if($_REQUEST['featured_type'] == 'c'):
							 update_post_meta($last_postid, 'featured_c', 'c');
							 update_post_meta($last_postid, 'featured_h', 'n');
							 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
						endif;	 
						if($_REQUEST['featured_type'] == 'h'):
							 update_post_meta($last_postid, 'featured_h', 'h');
							 update_post_meta($last_postid, 'featured_c', 'n');
							 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
						endif;
						if($_REQUEST['featured_type'] == 'none'):
							 update_post_meta($last_postid, 'featured_h', 'n');
							 update_post_meta($last_postid, 'featured_c', 'n');
							 update_post_meta($last_postid, 'featured_type', $_REQUEST['featured_type']);
						endif;	 
					else:
						 update_post_meta($last_postid, 'featured_type', 'none');
						 update_post_meta($last_postid, 'featured_c', 'n');
						 update_post_meta($last_postid, 'featured_h', 'n');
					endif;
				}
				update_post_meta($last_postid, 'recurrence_onweekno', $_REQUEST['recurrence_onweekno']);
				update_post_meta($last_postid, 'recurrence_days', $_REQUEST['recurrence_days']);	
				update_post_meta($last_postid, 'monthly_recurrence_byweekno', $_REQUEST['monthly_recurrence_byweekno']);	
				update_post_meta($last_postid, 'recurrence_byday', $_REQUEST['recurrence_byday']);	
				update_post_meta($last_postid, 'st_date', $_REQUEST['st_date']);	
				update_post_meta($last_postid, 'end_date', $_REQUEST['end_date']);	
				update_post_meta($last_postid, 'st_time', $_REQUEST['st_time']);	
				update_post_meta($last_postid, 'end_time', $_REQUEST['end_time']);	
				update_post_meta($last_postid, 'address', $_REQUEST['address']);	
			//	templ_save_recurrence_events( $_REQUEST,$last_postid);// to save event recurrences - front end
			
			/* to delete the old recurrences EOF */
		
			$start_date = templ_recurrence_dates($last_postid);
			update_post_meta($last_postid,'recurring_search_date',$start_date);
	
		}
	}
	/*from recurring event to regular event code end here*/
	$event_type = @$_REQUEST['event_type'];
	if(trim(strtolower($event_type)) == trim(strtolower('Regular event')) && isset($_REQUEST['cur_post_type']) &&  $_REQUEST['cur_post_type'] == 'event')
	{		
	
		
		$st_date = get_post_meta($last_postid,'st_date',true);
		$end_date = get_post_meta($last_postid,'end_date',true);
		$st_date = get_post_meta($last_postid,'st_date',true);
		$end_date = get_post_meta($last_postid,'end_date',true);
		
		if( isset($_REQUEST['st_time']) ){
		$count = explode(':',rtrim($_REQUEST['st_time']));
		if( count($count) > 2 ){
			$get_sttime = rtrim($_REQUEST['st_time']);
		}else{
			$get_sttime = rtrim($_REQUEST['st_time']).':00';
		}
		}else{
			$get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
		}
		if( isset($_REQUEST['end_time']) ){
			$count = explode(':',rtrim($_REQUEST['end_time']));
			if( count($count) > 2 ){
				$get_end_time = rtrim($_REQUEST['end_time']);
			}else{
				$get_end_time = rtrim($_REQUEST['end_time']).':00';
			}
		}else{
			$get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
		}
		$event_start_date = $_REQUEST['st_date'].' '.$get_sttime;
		$event_end_date = $_REQUEST['end_date'].' '.$get_end_time;
		update_post_meta($last_postid, 'set_st_time', $event_start_date);
		update_post_meta($last_postid, 'set_end_time', $event_end_date);	
	}
	
	/* save additional data when submit event from backend */
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='templatic_frontend_edit'){
		$event_type = @$_REQUEST['event_type']=$_REQUEST['event_type'];
		
	}	
	if((strstr($_SERVER['REQUEST_URI'],'wp-admin') && isset($_REQUEST['action'])  && $_REQUEST['action'] == 'editpost') || (isset($_REQUEST['action']) && $_REQUEST['action']=='templatic_frontend_edit') ) 
	{
		$event_type = $_POST['event_type'];
		$pID = $_POST['ID'];
		if( isset($_REQUEST['st_time']) ){
			$admin_count = explode(':',rtrim($_REQUEST['st_time']));
			if( count($admin_count) > 2 ){
				$admin_get_sttime = date("H:i", strtotime(rtrim($_REQUEST['st_time'])));
			}else{
				$_REQUEST['st_time'] = $_REQUEST['st_time'].':00';
				$admin_get_sttime = date("H:i", strtotime(rtrim($_REQUEST['st_time'])));
			}
		}else{
			$admin_get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
		}
		if( isset($_REQUEST['end_time']) ){
			$admin_count = explode(':',rtrim($_REQUEST['end_time']));
			if( count($admin_count) > 2 ){
				$admin_get_end_time = date("H:i", strtotime(rtrim($_REQUEST['end_time'])));
			}else{
				$_REQUEST['end_time'] = $_REQUEST['end_time'].":00";
				$admin_get_end_time = date("H:i", strtotime(rtrim($_REQUEST['end_time'])));
			}
		}else{
			$admin_get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
		}
		
		$admin_event_start_date = $_REQUEST['st_date'].' '.$admin_get_sttime;
		$admin_event_end_date = $_REQUEST['end_date'].' '.$admin_get_end_time;
		
		if(isset($_REQUEST['action']) && $_REQUEST['action']=='templatic_frontend_edit'){
			update_post_meta($pID, 'st_date', $_REQUEST['st_date']);
			update_post_meta($pID, 'end_date', $_REQUEST['end_date']);
			update_post_meta($pID, 'st_time', date('H:i',strtotime(trim($_REQUEST['st_time']))));
			update_post_meta($pID, 'end_time', date('H:i',strtotime(trim($_REQUEST['end_time']))));			
		}
	//	update_post_meta($pID, 'end_time',  $_REQUEST['end_time']);
		update_post_meta($pID, 'set_st_time', $admin_event_start_date);
		update_post_meta($pID, 'set_end_time', $admin_event_end_date);
		
		if(isset($_REQUEST['action']) && $_REQUEST['action']=='templatic_frontend_edit'){
			
			update_post_meta($pID, 'st_date', $_REQUEST['st_date']);
			update_post_meta($pID, 'end_date', $_REQUEST['end_date']);
			update_post_meta($pID, 'st_time', date('H:i',strtotime(trim($_REQUEST['st_time']))));
			update_post_meta($pID, 'end_time', date('H:i',strtotime(trim($_REQUEST['end_time']))));			
		}
		if(get_post_meta($pID,'_event_id',true) != '')
		{
			$where = array( 'ID' => $pID , 'post_type' => 'event');
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'recurring' ), $where );
		}
		if(trim(strtolower($event_type)) == trim(strtolower('Recurring event')) && isset($_POST['post_type']) &&  $_POST['post_type'] == 'event')
		{
			
			update_post_meta($pID, 'recurrence_occurs', $_POST['recurrence_occurs']);
			if(isset($_POST['recurrence_per']) && $_POST['recurrence_per'] !=''){
				update_post_meta($pID, 'recurrence_per', $_POST['recurrence_per']);
			}else{
				update_post_meta($pID, 'recurrence_per', 1);
			}
			update_post_meta($pID, 'recurrence_onday', $_POST['recurrence_onday']);
	
			update_post_meta($pID, 'recurrence_bydays', implode(',',$_POST['recurrence_bydays']));
	
			update_post_meta($pID, 'recurrence_onweekno', $_POST['recurrence_onweekno']);
			update_post_meta($pID, 'allow_to_create_rec', $_POST['allow_to_create_rec']);
			
			if(isset($_POST['recurrence_days']) && $_POST['recurrence_days'] !=''){
				update_post_meta($pID, 'recurrence_days', $_POST['recurrence_days']);	
			}else{
				update_post_meta($pID, 'recurrence_days', 1);	
			}
			
			update_post_meta($pID, 'monthly_recurrence_byweekno', $_POST['monthly_recurrence_byweekno']);	
			update_post_meta($pID, 'recurrence_byday', $_POST['recurrence_byday']);	
		}
		if(trim(strtolower($event_type)) == trim(strtolower('Recurring event')) && isset($_POST['post_type']) &&  $_POST['post_type'] == 'event' )
		{ 
			$start_date = templ_recurrence_dates($pID);
			$post_data = $_POST;
			$parent_data = get_post($pID);
			$st_date = get_post_meta($pID,'st_date',true);
			$end_date = get_post_meta($pID,'end_date',true);
			
				$parent_post_status = get_post_meta($pID,'tmpl_post_status',true);
				
				$fetch_status = 'recurring';
				
				/* to delete the old recurrences BOF */
				$args =	array( 
							'post_type' => 'event',
							'posts_per_page' => -1	,
							'post_status' => array($fetch_status),
							'meta_query' => array(
							'relation' => 'AND',
								array(
										'key' => '_event_id',
										'value' => $pID,
										'compare' => '=',
										'type'=> 'text'
									),
								)
							);
				$post_query = null;
				$post_query = new WP_Query($args);
				if($post_query){
					while ($post_query->have_posts()) : $post_query->the_post();
						 
							wp_delete_post($post->ID);
						 
					endwhile;
					wp_reset_query();
				}
				/* to delete the old recurrences EOF */
				templ_save_recurrence_events($post_data,$pID);// to save event recurrences
			
			update_post_meta($pID,'recurring_search_date',$start_date);
			
		}
		$recurrence_occurs_type = get_post_meta($pID, 'recurrence_occurs',true);		
		if($recurrence_occurs_type != '' && trim(strtolower($event_type)) == trim(strtolower('Regular event')))
		{
			
			update_post_meta($pID, 'recurrence_occurs','');
			$event_type = get_post_meta($pID,'event_type',true);
			$post_type = get_post_type($pID);

				/* to delete the old recurrences BOF */
				$args =	array( 
							'post_type' => 'event',
							'posts_per_page' => -1	,
							'post_status' => array('recurring'),
							'meta_query' => array(
							'relation' => 'AND',
								array(
										'key' => '_event_id',
										'value' => $pID,
										'compare' => '=',
										'type'=> 'text'
									),
								)
							);
				$post_query = null;
				$post_query = new WP_Query($args);				
				if($post_query){
					while ($post_query->have_posts()) : $post_query->the_post();
						wp_delete_post($post->ID);
					endwhile;
					wp_reset_query();
				
				/* to delete the old recurrences EOF */
			}
		}
	}		
	/*save recurring event from backend ends here*/
	
	
	
}
/*
 *Function Name : templ_recurrence_dates
 *Description : return recurrence dates.
 */
function templ_save_recurrence_events($post_data,$pID)
{
	global $wpdb,$current_user;
	
	$post_id = $pID;
	$start_date = strtotime(get_post_meta($post_id,'st_date',true));
	$end_date = strtotime(get_post_meta($post_id,'end_date',true));
	$tmpl_end_date = strtotime(get_post_meta($post_id,'end_date',true));
    $recurrence_occurs = get_post_meta($post_id,'recurrence_occurs',true);//recurrence type
	$recurrence_per = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
	$current_date = date('Y-m-d');
	$recurrence_days = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$recurrence_list = "";	
	if($recurrence_occurs == 'daily' )
	{
		
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		for($i=0;$i<=($days_between);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			if(($i%$recurrence_per) == 0 )
			{
				$j = $i+$recurrence_days;
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i day");
				if($recurrence_days==0):
						$recurrence_days=0;
				endif;
				
				$st_date2 = strtotime(date("Y-m-d", $st_date1) );
				$st_date = date('Y-m-d',$st_date2);
				if($recurrence_days ==1):
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
				else:
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
				endif;
				if($tmpl_end_date < strtotime($end_date)){
					$end_date = date("Y-m-d", $tmpl_end_date);
				}
				templ_update_rec_data($post_data,$post_id,$st_date,$end_date);
			}
			else
			{
				continue;
			}
		}
	}
	if($recurrence_occurs == 'weekly' )
	{ 
		$recurrence_interval = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$l = 0;
		$count_recurrence = 0;
		$current_week = 0;
		$recurrence_list .= "<ul>";
		
		if(strstr(get_post_meta($post_id,'recurrence_bydays',true),","))
			$recurrence_byday = explode(',',get_post_meta($post_id,'recurrence_byday',true));	//on which day
		else
			$recurrence_byday = get_post_meta($post_id,'recurrence_byday',true);	//on which day
		$start_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) );
		$end_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'end_date',true))) );
		
		//sort out week one, get starting days and then days that match time span of event (i.e. remove past events in week 1)
		$weekdays = explode(",", get_post_meta($post_id,'recurrence_bydays',true));
		$matching_days = array(); 
		$aDay = 86400;  // a day in seconds
		$aWeek = $aDay * 7;
			$start_of_week = get_option('start_of_week'); //Start of week depends on WordPress
			//first, get the start of this week as timestamp
			$event_start_day = date('w', $start_date);
			$offset = 0;
			if( $event_start_day > $start_of_week ){
				$offset = $event_start_day - $start_of_week; //x days backwards
			}elseif( $event_start_day < $start_of_week ){
				$offset = $start_of_week;
			}
			$start_week_date = $start_date - ( ($event_start_day - $start_of_week) * $aDay );
			//then get the timestamps of weekdays during this first week, regardless if within event range
			$start_weekday_dates = array(); //Days in week 1 where there would events, regardless of event date range
			for($i = 0; $i < 7; $i++){
				$weekday_date = $start_week_date+($aDay*$i); //the date of the weekday we're currently checking
				$weekday_day = date('w',$weekday_date); //the day of the week we're checking, taking into account wp start of week setting
				if( in_array( $weekday_day, $weekdays) ){
					$start_weekday_dates[] = $weekday_date; //it's in our starting week day, so add it
				}
			}
	
			//for each day of eventful days in week 1, add 7 days * weekly intervals
			foreach ($start_weekday_dates as $weekday_date){
				//Loop weeks by interval until we reach or surpass end date
				while($weekday_date <= $end_date){
					if( $weekday_date >= $start_date && $weekday_date <= $end_date ){
						$matching_days[] = $weekday_date;
					}					
					$weekday_date = $weekday_date + strtotime("+$recurrence_interval week", date("Y-m-d",$weekday_date));
				}
			}//done!
			sort($matching_days);
			$tmd = count($matching_days);
			for($z=0;$z<count($matching_days);$z++)
			{
				$st_date1 = $matching_days[$z];
				if($z <= ($tmd-1)){
					if($recurrence_days==0):
						$recurrence_days=0;
				
					endif;
						
				
					$st_date2 = strtotime(date("Y-m-d", $matching_days[$z]));
					$st_date = date('Y-m-d',$st_date2);
					if($recurrence_days ==1):
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
					else:
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
				
					endif;
					if($tmpl_end_date < strtotime($end_date)){
						$end_date = date("Y-m-d", $tmpl_end_date);
					}
					templ_update_rec_data($post_data,$post_id,$st_date,$end_date);
				
				}
			}
	}
	if($recurrence_occurs == 'monthly' )
	{
		$recurrence_interval = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$recurrence_byweekno = get_post_meta($post_id,'monthly_recurrence_byweekno',true);	//on which day
		$l = 0;
		$month_week = 0;
		$count_recurrence = 0;
		$current_month = 0;
		$recurrence_list .= "<ul>";
		
			if(strstr(get_post_meta($post_id,'recurrence_bydays',true),","))
				$recurrence_byday = explode(',',get_post_meta($post_id,'recurrence_byday',true));	//on which day
			else
				$recurrence_byday = get_post_meta($post_id,'recurrence_byday',true);	//on which day
			$start_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) );
			$end_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'end_date',true))) );
		
		$matching_days = array(); 
		$aDay = 86400;  // a day in seconds
		$aWeek = $aDay * 7;		 
		$current_arr = getdate($start_date);
		$end_arr = getdate($end_date);
		$end_month_date = strtotime( date('Y-m-t', $end_date) ); //End date on last day of month
		$current_date = strtotime( date('Y-m-1', $start_date) ); //Start date on first day of month
		while( $current_date <= $end_month_date ){
			 $last_day_of_month = date('t', $current_date);
			//Now find which day we're talking about
			$current_week_day = date('w',$current_date);
			$matching_month_days = array();
			//Loop through days of this years month and save matching days to temp array
			for($day = 1; $day <= $last_day_of_month; $day++){
				if((int) $current_week_day == $recurrence_byday){
					$matching_month_days[] = $day;
				}
				$current_week_day = ($current_week_day < 6) ? $current_week_day+1 : 0;							
			}
			//Now grab from the array the x day of the month
			$matching_day = ($recurrence_byweekno > 0) ? $matching_month_days[$recurrence_byweekno-1] : array_pop($matching_month_days);
			$matching_date = strtotime(date('Y-m',$current_date).'-'.$matching_day);
			if($matching_date >= $start_date && $matching_date <= $end_date){
				$matching_days[] = $matching_date;
			}
			//add the number of days in this month to make start of next month
			$current_arr['mon'] += $recurrence_interval;
			if($current_arr['mon'] > 12){
				//FIXME this won't work if interval is more than 12
				$current_arr['mon'] = $current_arr['mon'] - 12;
				$current_arr['year']++;
			}
			$current_date = strtotime("{$current_arr['year']}-{$current_arr['mon']}-1"); 
			
		}
		sort($matching_days);
		$tmd = count($matching_days);
		for($z=0;$z<count($matching_days);$z++)
		{
			$class= ($z%2) ? "odd" : "even";
			$st_date1 = $matching_days[$z];
			date("Y-m-d", $matching_days[$z]);
			if($z <= ($tmd-1)){
				if($recurrence_days==0)
					$recurrence_days=1;
			
				$st_date2 = strtotime(date("Y-m-d", $matching_days[$z]) );
				$st_date = date("Y-m-d", $st_date2);
					if($recurrence_days ==1):
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
					else:
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
					
					endif;
				if($tmpl_end_date < strtotime($end_date)){
					$end_date = date('Y-m-d',strtotime(date("Y-m-d", $tmpl_end_date)));
				}
				templ_update_rec_data($post_data,$post_id,$st_date,$end_date);
			}
		}
	}
	if($recurrence_occurs == 'yearly' )
	{
		$date1 = get_post_meta($post_id,'st_date',true);
		$date2 = get_post_meta($post_id,'end_date',true);
		$st_startdate1 = explode("-",$date1);
		$st_year = $st_startdate1[0];
		$st_month = $st_startdate1[1];
		$st_day = $st_startdate1[2];
		$st_date1 = mktime(0, 0, 0, $st_month, $st_day, $st_year);
		$st_date__month = (int)date('n', $st_date1); //get the current month of start date.
		$diff = abs(strtotime($date2) - strtotime($date1));
		$years_between = floor($diff / (365*60*60*24));
		$recurrence_list .= "<ul>";
		for($i=0;$i<($years_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			$startdate = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i year");
			$startdate1 = explode("-",date('Y-m-d',$startdate));
			$year = $startdate1[0];
			$month = $startdate1[1];
			$day = $startdate1[2];
			$date2 = mktime(0, 0, 0, $month, $day, $year);
			$month = (int)date('n', $date2); //get the current month.
			
			if($month == $st_date__month  && $i%$recurrence_per == 0)
			{				
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))). " +$i year");
				if($recurrence_days==0)
					$recurrence_days=1;
				
				$st_date2 = strtotime(date("Y-m-d", $st_date1));
				$st_date = date("Y-m-d", $st_date2);
				if($recurrence_days ==1):
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
				else:
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
						
				endif;
				if($tmpl_end_date < strtotime($end_date)){
					$end_date = date("Y-m-d", $tmpl_end_date);
				}
				templ_update_rec_data($post_data,$post_id,$st_date,$end_date);
			}
			else
			{
				continue;
			}
		}
	}
}
/*
Function Name : templ_update_rec_data
Description : it's update other recurrences while update the events
*/	
function templ_update_rec_data($post_data,$post_id,$st_date,$end_date){
	
	global $wpdb,$post;
	remove_action('save_post','save_recurring_event',13);
	$recurring_update = $_REQUEST['recurring_update'];
	$parent_data = get_post($post_id);
	if(!strstr($_SERVER['REQUEST_URI'],'wp-admin'))
		update_post_meta($post_id,'tmpl_post_status',$parent_data->post_status);
	
	$parent_post_status = get_post_meta($parent_data->ID,'tmpl_post_status',true);
	$p_status = $parent_data->post_status;
	if($parent_post_status =='draft' && $p_status == 'draft'){
		$child_status = 'pending';
	}else{
		$child_status = 'recurring';
	}
	if(isset($recurring_update) && $recurring_update != '')
	{
		$post_details = array('post_title' => $post_data->post_title,
					'post_content' => $post_data->post_content,
					'post_status' => $child_status,
					'post_type' => 'event',
					'post_name' => str_replace(' ','-',$post_data->post_title)."-".$st_date,
					'post_parent' => $post_id,
				  );
	}
	else
	{
		$post_details = array('post_title' => str_replace('<br>','',$post_data['post_title']),
					'post_content' => $post_data['post_content'],
					'post_status' => $child_status,
					'post_type' => 'event',
					'post_name' => str_replace(' ','-',$post_data['post_title'])."-".$st_date,
					'post_parent' => $post_id,
				  );
	}
	$alive_days = get_post_meta($post_id,'alive_days',true);
	$last_rec_post_id = wp_insert_post($post_details); // insert recurrences of events 
	$tl_dummy_content = get_post_meta($post_id,'tl_dummy_content',true);
	
	
	
	$where = array( 'post_parent' => $post_id , 'post_type' => 'event' );
	$wpdb->update( $wpdb->posts, array( 'post_status' => $child_status ), $where );
	

	
	if(isset($recurring_update) && $recurring_update != '')
		tmpl_set_my_categories($last_rec_post_id,$post_id); // assign category of parent post
	if((isset($_REQUEST['tax_input']['ecategory']) && $_REQUEST['tax_input']['ecategory']!='') || $_REQUEST['category'] !='' || $_SESSION['category'])
	{	
		tmpl_set_my_categories($last_rec_post_id,$post_id); // assign category of parent post
	}
	if((isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') || (isset($_REQUEST['post_id']) && $_REQUEST['post_id']!='' && $_REQUEST['action']=='templatic_frontend_edit'))
		tmpl_set_my_categories($last_rec_post_id,$post_id); 
	if(isset($tl_dummy_content) && $tl_dummy_content != '')
	{
		tmpl_set_my_categories($last_rec_post_id,$post_id); 
		update_post_meta($last_rec_post_id,'tl_dummy_content',1);
	}
	$st_time = get_post_meta($post_id,'st_time',true);
	$end_time = get_post_meta($post_id,'end_time',true);
	$post_city_id = get_post_meta($post_id,'post_city_id',true);
	$address = get_post_meta($post_id,'address',true);
	$geo_latitude = get_post_meta($post_id,'geo_latitude',true);
	$geo_longitude = get_post_meta($post_id,'geo_longitude',true);
	
	if( isset($st_time) ){
	$count = explode(':',$st_time);
	if( count($count) > 2 ){
		$get_sttime = $st_time;
	}else{
		$get_sttime = $st_time.':00';
	}
	}else{
		$get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
	}
	if( isset($end_time) ){
		$count = explode(':',$end_time);
		if( count($count) > 2 ){
			$get_end_time = $end_time;
		}else{
			$get_end_time = $end_time.':00';
		}
	}else{
		$get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
	}
	
	$event_start_date = $st_date.' '.$get_sttime;
	$event_end_date = $end_date.' '.$get_end_time;
	
	
	$featured_type = get_post_meta($post_id,'featured_type',true);
	$featured_h = get_post_meta($post_id,'featured_h',true);
	$featured_c = get_post_meta($post_id,'featured_c',true);
	/* add parent post value with different date and time */
	update_post_meta($last_rec_post_id,'event_type','Regular event'); 
	update_post_meta($last_rec_post_id,'end_date',$end_date); 
	update_post_meta($last_rec_post_id,'st_date',$st_date);
	update_post_meta($last_rec_post_id,'st_time',$st_time);
	update_post_meta($last_rec_post_id,'end_time',$end_time);
	update_post_meta($last_rec_post_id,'post_city_id',$post_city_id);
	update_post_meta($last_rec_post_id,'_event_id',$post_id); 
	update_post_meta($last_rec_post_id,'address',$address); 
	update_post_meta($last_rec_post_id,'geo_latitude',$geo_latitude); 
	update_post_meta($last_rec_post_id,'geo_longitude',$geo_longitude); 
	update_post_meta($last_rec_post_id,'alive_days',$alive_days); 
	update_post_meta($last_rec_post_id,'featured_type',$featured_type); 
	update_post_meta($last_rec_post_id,'featured_h',$featured_h); 
	update_post_meta($last_rec_post_id,'featured_c',$featured_c); 
	update_post_meta($last_rec_post_id, 'set_st_time', $event_start_date);
	update_post_meta($last_rec_post_id, 'set_end_time', $event_end_date);
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		if(function_exists('wpml_insert_templ_post'))
		{
			global $wpdb,$sitepress;
			$icl_table = $wpdb->prefix."icl_translations";
			$current_lang_code= ICL_LANGUAGE_CODE;
			$element_type = "post_event";
			$default_languages = ICL_LANGUAGE_CODE;
			$default_language = $sitepress->get_default_language();
			$trid =  1 + $wpdb->get_var($wpdb->prepare("select trid from $icl_table order by trid desc LIMIT 0,1"));
			$insert_tr = " INSERT INTO $icl_table (`translation_id` ,`element_type` ,`element_id` ,`trid` ,`language_code` ,`source_language_code`)VALUES ( '' , '".$element_type."', $last_rec_post_id, $trid , '".$current_lang_code."', 'NULL')";
			$wpdb->query($insert_tr);	
		}
	}
}
/*
Function Name : tmpl_set_my_categories
Description : set the categories of recurrence events 
*/
function tmpl_set_my_categories($last_rec_post_id,$post_id=''){
	$cat_1 = "";
	$recurring_update = $_REQUEST['recurring_update'];
	$tl_dummy_content = get_post_meta($post_id,'tl_dummy_content',true);	
	if(strstr($_SERVER['REQUEST_URI'],'wp-admin') && !isset($recurring_update) && $recurring_update == ''  && $tl_dummy_content == '' && $_REQUEST['action']!='templatic_frontend_edit'){		
		$cats = $_REQUEST['tax_input']['ecategory']; 
		$tags = $_REQUEST['tax_input']['etags']; 
		$tags = explode(',',$tags);
	}else if((isset($recurring_update) && $recurring_update != '') || (isset($_REQUEST['action']) && $_REQUEST['action']=='templatic_frontend_edit'))
	{

		$terms = wp_get_post_terms( $post_id, 'ecategory' );
		$terms_tag = wp_get_post_terms( $post_id, 'etags' );
		
		$cat_count = count($terms);
		$sep =",";
		
			for($c=0; $c < $cat_count ; $c++){
				
				if(($cat_count - 1)  == $c)
					$sep = "";
				$cat_1 .= $terms[$c]->term_id.$sep;
			
			}
		
		$sep =",";
		$term_count = count($terms_tag);
		{
			for($c=0; $c < $term_count ; $c++){
			
				if(($term_count - 1)  == $c)
					$sep = "";
				$tag_1 .= $terms_tag[$c]->name.$sep;
			
			}
			
		}
		$cats = explode(',',$cat_1);
		$tags = explode(',',$tag_1);
	}
	elseif((isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') || (isset($tl_dummy_content) && $tl_dummy_content != ''))
	{		
		$terms = wp_get_post_terms( $post_id, 'ecategory' );
		$terms_tag = wp_get_post_terms( $post_id, 'etags' );
		
		$cat_count = count($terms);
		$sep =",";
		
			for($c=0; $c < $cat_count ; $c++){
				
				if(($cat_count - 1)  == $c)
					$sep = "";
				$cat_1 .= $terms[$c]->term_id.$sep;
			
			}
		
		$sep =",";
		$term_count = count($terms_tag);
		{
			for($c=0; $c < $term_count ; $c++){
			
				if(($term_count - 1)  == $c)
					$sep = "";
				$tag_1 .= $terms_tag[$c]->name.$sep;
			
			}
			
		}
		$cats = explode(',',$cat_1);
		$tags = explode(',',$tag_1);
	}
	else{		
		if($_SESSION['category']){
			$cats = $_SESSION['category']; 
		}else{
			$cats = $_REQUEST['category']; 
		}
		
		$tags = $_REQUEST['e_tags']; 
		$sep =",";
		for($c=0; $c < count($cats) ; $c++){
			$cat_0 = explode(',',$cats[$c]);
			if((count($cats) - 1)  == $c)
				$sep = "";
			$cat_1 .= $cat_0[0].$sep;
			
		}
		$cats = explode(',',$cat_1);
	
	}
	wp_set_post_terms( $last_rec_post_id, $cats,'ecategory' ,false);
	wp_set_post_terms( $last_rec_post_id, $tags,'etags' ,false);
}
/*
 *Function Name : templ_recurrence_dates
 *Description : return recurrence dates.
 */
function templ_recurrence_dates($post_id)
{
	global $wpdb,$current_user,$post;
	$start_date = strtotime(get_post_meta($post_id,'st_date',true));
	$end_date = strtotime(get_post_meta($post_id,'end_date',true));
	$recurrence_occurs = get_post_meta($post_id,'recurrence_occurs',true);//recurrence type
	$recurrence_per = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
	$current_date = date('Y-m-d');
	$recurrence_days = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$recurrence_list = "";
	$st_date = "";
	if($recurrence_occurs == 'daily' )
	{
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$recurrence_list .= "<ul>";
		for($i=0;$i<($days_between);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			if(($i%$recurrence_per) == 0 )
			{
				$j = $i+$recurrence_days;
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i day");
				if($recurrence_days==0)
					$recurrence_days=1;
				for($rd=0;$rd<$recurrence_days;$rd++)
				{
					$st_date2 = strtotime(date("Y-m-d", $st_date1) . " +$rd day");
					$st_date .= date_i18n("Y-m-d", $st_date2).",";
				}
			}
			else
			{
				continue;
			}
		}
	}
	if($recurrence_occurs == 'weekly' )
	{
		$recurrence_interval = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$l = 0;
		$count_recurrence = 0;
		$current_week = 0;
		$recurrence_list .= "<ul>";
		
		if(strstr(get_post_meta($post_id,'recurrence_bydays',true),","))
			$recurrence_byday = explode(',',get_post_meta($post_id,'recurrence_byday',true));	//on which day
		else
			$recurrence_byday = get_post_meta($post_id,'recurrence_byday',true);	//on which day
		$start_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) );
		$end_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'end_date',true))) );
		
		//sort out week one, get starting days and then days that match time span of event (i.e. remove past events in week 1)
		$weekdays = explode(",", get_post_meta($post_id,'recurrence_bydays',true));
		$matching_days = array(); 
		$aDay = 86400;  // a day in seconds
		$aWeek = $aDay * 7;
		$start_of_week = get_option('start_of_week'); //Start of week depends on WordPress
		//first, get the start of this week as timestamp
		$event_start_day = date('w', $start_date);
		$offset = 0;
		if( $event_start_day > $start_of_week ){
			$offset = $event_start_day - $start_of_week; //x days backwards
		}elseif( $event_start_day < $start_of_week ){
			$offset = $start_of_week;
		}
		$start_week_date = $start_date - ( ($event_start_day - $start_of_week) * $aDay );
		//then get the timestamps of weekdays during this first week, regardless if within event range
		$start_weekday_dates = array(); //Days in week 1 where there would events, regardless of event date range
		for($i = 0; $i < 7; $i++){
			$weekday_date = $start_week_date+($aDay*$i); //the date of the weekday we're currently checking
			$weekday_day = date('w',$weekday_date); //the day of the week we're checking, taking into account wp start of week setting
			if( in_array( $weekday_day, $weekdays) ){
				$start_weekday_dates[] = $weekday_date; //it's in our starting week day, so add it
			}
		}
		
		//for each day of eventful days in week 1, add 7 days * weekly intervals
		foreach ($start_weekday_dates as $weekday_date){
			//Loop weeks by interval until we reach or surpass end date
			while($weekday_date <= $end_date){
				if( $weekday_date >= $start_date && $weekday_date <= $end_date ){
					$matching_days[] = $weekday_date;
				}	
							
				$weekday_date = $weekday_date + strtotime("+$recurrence_interval week", date("Y-m-d",$weekday_date));
			}
		}//done!
		sort($matching_days);
		$tmd = count($matching_days);
		for($z=0;$z<count($matching_days);$z++)
		{
			$class= ($z%2) ? "odd" : "even";
			$st_date1 = $matching_days[$z];
			if($z <= ($tmd-1)){
				if($recurrence_days==0)
					$recurrence_days=1;
				for($rd=0;$rd<$recurrence_days;$rd++)
				{
					$st_date1 = strtotime(date("Y-m-d", $matching_days[$z]) . " +$rd day");
					$st_date .= date_i18n('Y-m-d', $st_date1).",";
				}
			}
		}
	}
	
	if($recurrence_occurs == 'monthly' )
	{
		$recurrence_interval = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$recurrence_byweekno = get_post_meta($post_id,'monthly_recurrence_byweekno',true);	//on which day
		$l = 0;
		$month_week = 0;
		$count_recurrence = 0;
		$current_month = 0;
		$recurrence_list .= "<ul>";
		
			if(strstr(get_post_meta($post_id,'recurrence_bydays',true),","))
				$recurrence_byday = explode(',',get_post_meta($post_id,'recurrence_byday',true));	//on which day
			else
				$recurrence_byday = get_post_meta($post_id,'recurrence_byday',true);	//on which day
			$start_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) );
			$end_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'end_date',true))) );
		
		$matching_days = array(); 
		$aDay = 86400;  // a day in seconds
		$aWeek = $aDay * 7;		 
		$current_arr = getdate($start_date);
		$end_arr = getdate($end_date);
		$end_month_date = strtotime( date('Y-m-t', $end_date) ); //End date on last day of month
		$current_date = strtotime( date('Y-m-1', $start_date) ); //Start date on first day of month
		while( $current_date <= $end_month_date ){
			 $last_day_of_month = date('t', $current_date);
			//Now find which day we're talking about
			$current_week_day = date('w',$current_date);
			$matching_month_days = array();
			//Loop through days of this years month and save matching days to temp array
			for($day = 1; $day <= $last_day_of_month; $day++){
				if((int) $current_week_day == $recurrence_byday){
					$matching_month_days[] = $day;
				}
				$current_week_day = ($current_week_day < 6) ? $current_week_day+1 : 0;							
			}
			//Now grab from the array the x day of the month
			$matching_day = ($recurrence_byweekno > 0) ? $matching_month_days[$recurrence_byweekno-1] : array_pop($matching_month_days);
			$matching_date = strtotime(date('Y-m',$current_date).'-'.$matching_day);
			if($matching_date >= $start_date && $matching_date <= $end_date){
				$matching_days[] = $matching_date;
			}
			//add the number of days in this month to make start of next month
			$current_arr['mon'] += $recurrence_interval;
			if($current_arr['mon'] > 12){
				//FIXME this won't work if interval is more than 12
				$current_arr['mon'] = $current_arr['mon'] - 12;
				$current_arr['year']++;
			}
			$current_date = strtotime("{$current_arr['year']}-{$current_arr['mon']}-1"); 
			
		}
		sort($matching_days);
		$tmd = count($matching_days);
		 for($z=0;$z<count($matching_days);$z++)
		{
			$class= ($z%2) ? "odd" : "even";
			$st_date1 = $matching_days[$z];
			date("Y-m-d", $matching_days[$z]);
			if($z <= ($tmd-1)){
				if($recurrence_days==0)
					$recurrence_days=1;
				for($rd=0;$rd<$recurrence_days;$rd++)
				{
					$st_date2 = strtotime(date("Y-m-d", $matching_days[$z]) . " +$rd day");
					$st_date .= date_i18n("Y-m-d", $st_date2).",";
				}
			}
		}
	}
	if($recurrence_occurs == 'yearly' )
	{
		$date1 = get_post_meta($post_id,'st_date',true);
		$date2 = get_post_meta($post_id,'end_date',true);
		$st_startdate1 = explode("-",$date1);
		$st_year = $st_startdate1[0];
		$st_month = $st_startdate1[1];
		$st_day = $st_startdate1[2];
		$st_date1 = mktime(0, 0, 0, $st_month, $st_day, $st_year);
		$st_date__month = (int)date('n', $st_date1); //get the current month of start date.
		$diff = abs(strtotime($date2) - strtotime($date1));
		$years_between = floor($diff / (365*60*60*24));
		$recurrence_list .= "<ul>";
		for($i=0;$i<($years_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			$startdate = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i year");
			$startdate1 = explode("-",date('Y-m-d',$startdate));
			$year = $startdate1[0];
			$month = $startdate1[1];
			$day = $startdate1[2];
			$date2 = mktime(0, 0, 0, $month, $day, $year);
			$month = (int)date('n', $date2); //get the current month.
			
			if($month == $st_date__month  && $i%$recurrence_per == 0)
			{				
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))). " +$i year");
				if($recurrence_days==0)
					$recurrence_days=1;
				for($rd=0;$rd<$recurrence_days;$rd++)
				{
					$st_date2 = strtotime(date("Y-m-d", $st_date1) . " +$rd day");
					$st_date .= date_i18n('Y-m-d', $st_date2).",";
				}
			}
			else
			{
				continue;
			}
		}
	}
	return $st_date;
}
add_filter('post_row_actions', 'tmpl_qe_download_link', 10, 2);
function tmpl_qe_download_link($actions, $post) {
	$post_status = trim(strtolower('Recurring'));
	if(trim(strtolower($post->post_status)) == $post_status  && $post->post_type =='event'){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
		$plugin = "woocommerce/woocommerce.php";
		$url = get_edit_post_link( $post->ID );
		if(is_plugin_active($plugin)){
			$actions['edit'] = "<a href='".$url."'>".__('Manage tickets',EDOMAIN)."</a>";
		}else{
			unset($actions['edit'],$actions['trash']);
			$actions['status'] = "<strong>".__('Recurrence',EDOMAIN)."</strong>";
		}
		
		unset($actions['inline hide-if-no-js'],$actions['trash']);
		
	}
	$showrecurrence_url = admin_url("edit.php?post_type=event&recurrence=1&post_id=".$post->ID);
	$event_type = trim(strtolower(get_post_meta($post->ID,'event_type',true)));
	if(trim(strtolower('Recurring event')) == $event_type  && $post->post_type =='event'){
		$actions['status'] = "<a href='".$showrecurrence_url."'>".__('Show Recurrence',EDOMAIN)."</a>";
	}
    return $actions; 
}
/*
FUNCTION NAME : FETCH DATA FOR EVENT POST TYPE
DESCRIPTION : FETCH EVENT CATEGORIES, TAGS, ADDRESS ETC FIELD TO DISPLAY THEM IN EVENTS PAGE - BACK END
*/
add_action( 'manage_event_posts_custom_column', 'templatic_manage_event_columns', 10, 2 );
function templatic_manage_event_columns( $column, $post_id )
{
//echo '<link href="'.get_template_directory_uri().'/monetize/admin.css" rel="stylesheet" type="text/css" />';
	global $post;
	switch( $column ) {
	
		case 'post_category' :
			/* Get the post_category for the post. */
			$templ_events = get_the_terms($post_id,CUSTOM_CATEGORY_TYPE_EVENT);
			if (is_array($templ_events)) {
				foreach($templ_events as $key => $templ_event) {
					$edit_link = home_url()."/wp-admin/edit.php?".CUSTOM_CATEGORY_TYPE_EVENT."=".$templ_event->slug."&post_type=".CUSTOM_POST_TYPE_EVENT;
					$templ_events[$key] = '<a href="'.$edit_link.'">' . $templ_event->name . '</a>';
					}
				echo implode(' , ',$templ_events);
			}else {
				_e( 'Uncategorized',EDOMAIN);
			}
			break;
		case 'post_tags' :
			/* Get the post_tags for the post. */
			$templ_event_tags = get_the_terms($post_id,CUSTOM_TAG_TYPE_EVENT);
			if (is_array($templ_event_tags)) {
				foreach($templ_event_tags as $key => $templ_event_tag) {
					$edit_link = home_url()."/wp-admin/edit.php?".CUSTOM_TAG_TYPE_EVENT."=".$templ_event_tag->slug."&post_type=".CUSTOM_POST_TYPE_EVENT;
					$templ_event_tags[$key] = '<a href="'.$edit_link.'">' . $templ_event_tag->name . '</a>';
				}
				echo implode(' , ',$templ_event_tags);
			}
			break;
		case 'geo_address' :
			/* Get the address for the post. */
			$geo_address = get_post_meta( $post_id, 'address', true );
				if($geo_address != ''){
					$geo_address = $geo_address;
				} else {
					$geo_address = ' ';
				}
				echo $geo_address;
			break;
		case 'event_data' :
				/* Get the start_timing for the post. */
				$time_formate=get_option('time_format');
				$st_date = get_post_meta( $post_id, 'st_date', true );
				$end_date = get_post_meta( $post_id, 'end_date', true );	
				if($st_date != ''){
					$st_date = $st_date.' '.date($time_formate,strtotime(get_post_meta($post_id,'st_time',true)));
				} else {
					$st_date = ' ';
				}
				if($end_date != ''){
					$end_date = $end_date.' '.date($time_formate,strtotime(get_post_meta($post_id,'end_time',true)));
				} else {
					$end_date = ' ';
				}
				echo __('Date: ').$st_date. ' - '.$end_date;
			break;		
		case 'event_type_' :
			/* Get the event_type for the post. */
				$event_type = trim(get_post_meta( $post_id, 'event_type', true ));
				if(strtolower($event_type) == trim(strtolower('Recurring event'))){				
					$e_type = "<span style='color:green;'>".__('Recurring event',EDOMAIN)."</span>";
				} else {
					 $e_type = __('Regular event',EDOMAIN);;
				}
				if($post->post_parent !=0){
					$post_parent = get_post($post->post_parent );
					$e_type = __('Recurrence of ',EDOMAIN)."<a href='".get_edit_post_link($post->post_parent)."'>".$post_parent->post_title."</a>";
				}
				echo $e_type;
			break;
		
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}
/* EOF - FETCH DATA IN BACK END */
				
/*
FUNCTION NAME : FUNCTION TO DISPLAY EVENT POST TYPE IN BACK END
DESCRIPTION : THIS FUNCTION ADDS COLUMNS IN EVENT POST TYPE IN BACK END
*/
add_filter('tevolution_manage_edit-event_columns', 'templatic_edit_event_columns',15 ) ;
function templatic_edit_event_columns( $columns )
{ 
	
	$columns['event_data'] = __('Event Data',EDOMAIN);
	$columns['event_type_'] = __('Event Type',EDOMAIN);	
	return $columns;
}
/* END OF FUNCTION */
/*
 * ADMIN COLUMN - SORTING - MAKE HEADERS SORTABLE
 * https://gist.github.com/906872
 */
add_filter("manage_edit-event_sortable_columns", 'event_sort',11);
function event_sort($columns) {
	$custom = array(
		'event_type' 	=> 'event_type'
	);
	return wp_parse_args($custom, $columns);
}
function recurring_event_custom_post_status(){
	global $typenow;
	if(isset($_REQUEST['recurrence']) && $_REQUEST['recurrence'] != '' && $typenow == CUSTOM_POST_TYPE_EVENT)
	{
		register_post_status( 'recurring', array(
		'label'                     => _x( 'Recurring', 'event' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Recurring <span class="count">(%s)</span>', 'Recurring <span class="count">(%s)</span>' ),
		) );
		add_action('pre_get_posts', 'custom_recurring_event_filter',11);
	}
}
add_action( 'admin_init', 'recurring_event_custom_post_status' );
function custom_recurring_event_filter( &$query )
{
	add_filter('posts_where', 'recurring_event_filter_where');
}
function recurring_event_filter_where($where)
{
	global $post,$wpdb;
	$where .= " AND ($wpdb->posts.ID = ".$_REQUEST['post_id']." or $wpdb->posts.post_parent = ".$_REQUEST['post_id']." ) ";
	return $where;
}
/*
Function Name : delete_recurring_event
Description : to delete recurring data from front end.
*/
if(!strstr($_SERVER['REQUEST_URI'],'wp-admin') )
	add_action('delete_post', 'delete_recurring_event'); // to delete the post of old recurrences
function delete_recurring_event()
{
	global $wpdb,$post,$post_id;
	$event_type = trim(strtolower(get_post_meta($_REQUEST['pid'],'event_type',true)));
	if($event_type == trim(strtolower('Recurring event')) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'delete')
	{
		/* to delete the old recurrences BOF */
		$args =	array( 
					'post_type' => 'event',
					'posts_per_page' => -1	,
					'post_status' => array('recurring'),
					'meta_query' => array(
					'relation' => 'AND',
						array(
								'key' => '_event_id',
								'value' => $_REQUEST['pid'],
								'compare' => '=',
								'type'=> 'text'
							),
						)
					);
		$post_query = null;
		$post_query = new WP_Query($args);
		if($post_query){
			while ($post_query->have_posts()) : $post_query->the_post();
				wp_delete_post($post->ID);
			endwhile;wp_reset_query();
		}
	}
	remove_action('delete_post', 'delete_recurring_event');
	/* to delete the old recurrences EOF */
}
/*
Function Name : delete_recurring_event
Description : to delete recurring data from front end.
*/
if(strstr($_SERVER['REQUEST_URI'],'wp-admin') )
{
	add_action('trash_event', 'delete_admin_recurring_event',1,1); // to delete the post of old recurrences
}
function delete_admin_recurring_event($post_id)
{
	global $wpdb,$post,$post_id;
	if(!is_array($_REQUEST['post']))
	{
		$_REQUEST['post'] = array($_REQUEST['post']);
	}
	 for($i=0;$i<count($_REQUEST['post']);$i++)
	 {
	 	$event_type = trim(strtolower(get_post_meta($_REQUEST['post'][$i],'event_type',true)));
		if($event_type == trim(strtolower('Recurring event')))
		{
			/* to delete the old recurrences BOF */
			$args =	array( 
						'post_type' => 'event',
						'posts_per_page' => -1	,
						'post_status' => array('recurring'),
						'meta_query' => array(
						'relation' => 'AND',
							array(
			 						'key' => '_event_id',
									'value' =>$_REQUEST['post'][$i],
									'compare' => '=',
									'type'=> 'text'
								),
							)
						);
			$post_query = null;
			$post_query = new WP_Query($args);
			if($post_query){
				while ($post_query->have_posts()) : $post_query->the_post();
					wp_delete_post($post->ID);
				endwhile;wp_reset_query();
			}
		}
	}
	remove_action('delete_post', 'delete_recurring_event');
	/* to delete the old recurrences EOF */
}
add_action( 'admin_menu', 'tmpl_remove_meta_boxes' );
function tmpl_remove_meta_boxes($post_id)
{ 	
	//remove custom setting metabox in staff custom post type.
	global $post;
	if(isset($_REQUEST['post']) && $_REQUEST['post'] != '')
	{
		$post_edit = $_REQUEST['post'];
		$post = get_post($post_edit);
		if($post_edit !='' && $post->post_type =='event' && $post->post_parent != 0){
			global $post;
			$post_edit = $_REQUEST['post'];
			$post = get_post($post_edit);
			$post_status = trim(strtolower('Recurring'));
			if(trim(strtolower($post->post_status)) == $post_status && $_REQUEST['action'] =='edit'){
				remove_meta_box('ptthemes-settings', 'event', 'normal');
				remove_meta_box('trackbacksdiv', 'event', 'normal');
				remove_meta_box('slugdiv', 'event', 'normal');
				remove_meta_box('revisionsdiv', 'event', 'normal');
				remove_meta_box('authordiv', 'event', 'normal');
				remove_meta_box('ecategorydiv', 'event', 'normal');
				remove_meta_box('tagsdiv-etags', 'event', 'normal');
				remove_meta_box('tagsdiv', 'event', 'normal');
				remove_meta_box('postimagediv', 'event', 'normal');
				remove_meta_box('post-stylesheets', 'event', 'normal');
			
				add_action('admin_init', 'remove_all_media_buttons');
			}
		}
	}
}
/* remove add media button for recurring post for events */
function remove_all_media_buttons()
{
    remove_all_actions('media_buttons');	
	add_meta_box('tmpl_recurring_dates','Event is on','tmpl_recurring_on','event','side','high');
}
/*
Function Name :tmpl_recurring_on
Description : show recurring dates
*/
function tmpl_recurring_on($post){
	global $post;
	echo "<p class='error'>";
		_e('This event is the recurrence of the event.',EDOMAIN);
	echo "</p>";
	$st_date = get_post_meta($post->ID,'st_date',true);
	$end_date =  get_post_meta($post->ID,'end_date',true);
	$st_time =  get_post_meta($post->ID,'st_time',true);
	$end_time =  get_post_meta($post->ID,'end_time',true);
	$address =  get_post_meta($post->ID,'address',true);
	if($st_date){
		echo "<p>";
		_e('Start date',EDOMAIN); echo ": <b>". $st_date." ".$st_time."</b>"; 
		echo "</p>";
	}
	if($end_date){
		echo "<p>";
			_e('End date',EDOMAIN); echo ": <b>".$end_date." ".$end_time."</b>";
		echo "</p>";
	}
	if($address){
		echo "<p>";
			_e('Address',EDOMAIN); echo ": <b>".$address."</b>";
		echo "</p>";
	}
}
// Display notification messages for recurring ande its recurrence event.
	function admin_notice_handler($post) {
		global $post;
		$errors = __('Modification in this event will be applied to all other occurrences of this recurring event.',EDOMAIN);
	
		if(isset($_REQUEST['post']) && $_REQUEST['post'] != '') {
			
			$post_type = get_post_type($_REQUEST['post']);
			
			$event_type = get_post_meta($_REQUEST['post'],'event_type',true);
			
			$_event_id = get_post_meta($_REQUEST['post'],'_event_id',true);
			
			if($post_type == 'event' && trim(strtolower($event_type)) == trim(strtolower('Recurring Event')) )
			{
	
				echo '<div class="error"><p>' . $errors .'<a href="'.site_url().'/wp-admin/edit.php?post_type=event&recurrence=1&post_id='.$post->ID.'" style="text-decoration:none; color:#21759B;" >&nbsp;'.__('Show Recurrence',EDOMAIN). '</a> <br/><b>'.__('NOTE:',EDOMAIN).'</b> '.__('Moving this event to trash and restoring it again, will only restore the main event and not its recurrence. To add recurrence again, select the &quot;Recurring event&quot; option field and add the recurrence dates as per your need.',EDOMAIN).'</p></div>';
				
			}	
			elseif($post_type == 'event' && trim(strtolower($event_type)) == trim(strtolower('Regular event'))  && $_event_id == $post->post_parent)
			{
				$errors = __('As this is an occurrence, you can edit only the description of this event. To edit other fields go to main event of',EDOMAIN).' <a target="_blank" href="'.site_url("/wp-admin/post.php?post=".$post->post_parent."&action=edit").'">'.__('this',EDOMAIN).'</a> '.__('occurrence .',EDOMAIN);
				echo '<div class="error"><p>' . $errors . '</p></div>';
				
			}	
		}   
	
	}
	function recurring_notices($post) {
		global $post;
		$errors = __('Modification in this event will be applied to all other occurrences of this recurring event.',EDOMAIN);
	
		if(isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
			
			$post_type = get_post_type($_REQUEST['pid']);
			
			$event_type = get_post_meta($_REQUEST['pid'],'event_type',true);
			
			if($post_type == 'event' && trim(strtolower($event_type)) == trim(strtolower('Recurring Event')) )
			{
	
				echo '<div class="error"><p>' . $errors . '</p></div>';
				
			}	
		}   
	}
	add_action( 'admin_notices', 'admin_notice_handler' );
	add_action( 'submit_form_before_content', 'recurring_notices' );
/**/
/*
Function Name : tmpl_is_parent
Description : return true if post have parent post
*/
function tmpl_is_parent($post){
	if($post->post_parent){
		return true;
	}else{
		return false;
	}
}
/*	
Function Name : get_formated_date
Description : get default date format -- */
function get_formated_date($date)
{
	return mysql2date(get_option('date_format'), $date);
}
/*	
Function Name : get_formated_date
Description : get default time format -- */
function get_formated_time($time)
{
	return mysql2date(get_option('time_format'), $time, $translate=true);
}
/*
 * Function Name : _iscurlinstalled
 * Description : Returns true/false , check CURL is enable or not
 */
function _iscurlinstalled() {
	if  (in_array  ('curl', get_loaded_extensions())) {
		return true;
	}
	else{
		return false;
	}
}
/*
 * Function Name : facebook_events
 * Description : Returns facebook events for page template
*/
function facebook_events_template($appID,$secret,$pageID)
{
	global $post;		
	/*$appID = get_post_meta($post->ID,'facebook_app_id',true);
	$secret = get_post_meta($post->ID,'facebook_secret_id',true);
	$pageID = get_post_meta($post->ID,'facebook_page_id',true);			
	*/
	$config = array(
		'appId' => $appID,
		'secret' => $secret,
	  );
	if(_iscurlinstalled())
	{
	 if(class_exists('Facebook')){
	 $facebook = new Facebook($config);
	 $user_id = $facebook->getUser();
	
		if($appID) 
		{
		  /*  We have a user ID, so probably a logged in user.
		   If not, we'll get an exception, which we handle below. */
		  try {
		  
		/* just a heading once it creates an event */
			$fql    =   "SELECT eid,name, pic, start_time, end_time, location, description 
				FROM event WHERE eid IN ( SELECT eid FROM event_member WHERE uid = $pageID ) 
				ORDER BY start_time asc";			
				$param  =   array(
				'method'    => 'fql.query',
				'query'     => $fql,
				'callback'  => '');
				$fqlResult   =   $facebook->api($param);
	
	if(count($fqlResult)<0){?>
		 <p class="message" ><?php echo NO_FACEBOOK_EVENT;?> </p> 
	<?php
	}
	else
	{
		?>
        <div class="fb_event_wrapper">
        <?php
	/* looping through retrieved data */
	$start_date = '';
	$end_date = '';
	$start_time = '';
	$end_time = '';
	foreach( $fqlResult as $keys => $values ){
		/* see here for the date format I used
		The pattern string I used 'l, F d, Y g:i a'
		will output something like this: July 30, 2015 6:30 pm */
		/* getting 'start' and 'end' date,
		'l, F d, Y' pattern string will give us
		something like: Thursday, July 30, 2015 */
		//echo $values['start_time'];
		$datetime = new DateTime($values['start_time']);
		//$start_date = date_i18n( get_option("date_format"), $values['start_time'] );
		$start_date = $datetime->format( get_option("date_format"));
		if( @$values['end_time']!="" ){
			$datetime = new DateTime($values['end_time']);
			$end_date = $datetime->format( get_option("date_format"));
		}
		/* getting 'start' and 'end' time
		'g:i a' will give us something
		like 6:30 pm */
		$datetime = new DateTime($values['start_time']);
		$start_time = $datetime->format( get_option("time_format"));
		if( @$values['end_time']!="" ){
			$datetime = new DateTime($values['end_time']);
			$end_time = $datetime->format( get_option("time_format"));
		}
		//printing the data
		$link = "http://www.facebook.com/events/".$values['eid'];
	   echo "<div class='facebook_event  clearfix'>";
			echo "<a class='event_img'><img  src={$values['pic']} /></a>";
	   		echo "<div class='fb_content'>";
			echo "<h3><a href='".$link."'>{$values['name']}</a></h3>";
			echo "<p class='fb_event_info'> ";
			echo "<span class='label'>".__('Start date',EDOMAIN).":</span><span class='fb_info'>{$start_date}"."</span><br/>";
			if( @$values['end_time']!="" ){
				echo "<span class='label'>".__('End date',EDOMAIN).":</span><span class='fb_info'>{$end_date}"."</span><br/>";
			}	
			echo "<span class='label'>".__('Time',EDOMAIN).":</span><span class='fb_info'>{$start_time}"."</span>";
			
			if( @$values['end_time']!="" ){
				echo " - {$end_time}"."<br/>";
			}else{echo "<br/>";}
			if($values['location']){
			echo "<span class='label'>".__('Location',EDOMAIN).":</span><span class='fb_info'>" . $values['location'] . "</span><br/>";
			}
			if($values['description']){
			echo "<span class='label'>".__('More Info',EDOMAIN).":</span><span class='fb_info'>" . $values['description'] . "</span>";
			}
			echo "</p>";
			echo "</div>";
		echo "</div>";
	}?>
    </div>
    <?php
	}
	?>	
	<script type='text/javascript'>
	//just to add some hover effects
	jQuery(document).ready(function(){
	jQuery('.event').hover(
		function () {
			jQuery(this).css('background-color', '#CFF');
		}, 
		function () {
			jQuery(this).css('background-color', '#E3E3E3');
		}
	);
	});</script>
	<?php
			/* SQL queries return the results in an array, so we have to get the user's name from the first element in the array. */
		   
		  } catch(FacebookApiException $e) {
			/* If the user is logged out, you can have a user ID even though the access token is invalid.In this case, we'll get an exception, so we'll just ask the user to login again here. */
			$login_url = $facebook->getLoginUrl(); 
			_e('Looks like you have entered invalid keys.',EDOMAIN);

		  }   
		}
		}else{
			_e('Facebook Plugin not installed.',EDOMAIN);
		}
	}else
	{
		echo '<p class="error">';
		_e('CURL is not installed on your server, please enbale CURL to use Facebook evenst API.',EDOMAIN);
		echo '</p>';
	}
}
function event_remove_tevolution_message()
{
	remove_action('tevolution_transaction_msg','tevolution_transaction_msg_fn');
	remove_action('tevolution_transaction_mail','tevolution_transaction_mail_fn');
	add_action('tevolution_transaction_msg','event_transaction_msg_fn');
	add_action('tevolution_transaction_mail','event_transaction_mail_fn');
}
function event_transaction_msg_fn()
{
	
	if(count($_REQUEST['cf'])>0)
	{
		for($i=0;$i<count($_REQUEST['cf']);$i++)
		{
			$cf = explode(",",$_REQUEST['cf'][$i]);
			$orderId = $cf[0];
			if(isset($_REQUEST['action']) && $_REQUEST['action'] !='')
			{
				global $wpdb,$transection_db_table_name;
				$transection_db_table_name = $wpdb->prefix . "transactions";
				
				$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
				$orderinfo = $wpdb->get_row($ordersql);
			
				$pid = $orderinfo->post_id;
				$event_type = get_post_meta($pid,'event_type',true);
				
				$payment_type = $orderinfo->payment_method;
				$payment_date =  date_i18n(get_option('date_format'),strtotime($orderinfo->payment_date));
				$trans_status = $wpdb->query("update $transection_db_table_name SET status = '".$_REQUEST['ostatus']."' where trans_id = '".$orderId."'");
				$user_detail = get_userdata($orderinfo->user_id); // get user details 
				$user_email = $user_detail->user_email;
				$user_login = $user_detail->display_name;
				$my_post['ID'] = $pid;
				
				if(isset($_REQUEST['action']) && $_REQUEST['action']== 'confirm')
				{
					$payment_status = APPROVED_TEXT;
					$status = 'publish';
					if(trim(strtolower($event_type)) == strtolower('Recurring event'))
					{
						global $wpdb;
						$where = array( 'post_parent' => $pid , 'post_type' => 'event');
						$wpdb->update( $wpdb->posts, array( 'post_status' => 'recurring' ), $where );
					}
				}
				elseif(isset($_REQUEST['action']) && $_REQUEST['action']== 'pending')
				{
					$payment_status = PENDING_MONI;
					$status = 'draft';
					if(trim(strtolower($event_type)) == strtolower('Recurring event'))
					{
						global $wpdb;
						$where = array( 'post_parent' => $pid , 'post_type' => 'event');
						$wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), $where );
					}
				}
				elseif(isset($_REQUEST['action']) && $_REQUEST['action']== 'cancel')
				{
					$payment_status = PENDING_MONI;
					$status = 'draft';
					if(trim(strtolower($event_type)) == strtolower('Recurring event'))
					{
						global $wpdb;
						$where = array( 'post_parent' => $pid , 'post_type' => 'event');
						$wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), $where );
					}
				}
				$my_post['post_status'] = $status;
				wp_update_post( $my_post );
				
				$to = get_site_emailId_plugin();
				$productinfosql = "select ID,post_title,guid,post_author from $wpdb->posts where ID = $pid";
				$productinfo = get_post($pid);
				$post_name = $productinfo->post_title;
				$transaction_details="";
				$transaction_details .= "-------------------------------------------------- <br/>\r\n";
				$transaction_details .= __("Payment Details for Listing $post_name",EDOMAIN)." <br/>\r\n";
				$transaction_details .= "-------------------------------------------------- <br/>\r\n";
				$transaction_details .= __("Status:",EDOMAIN)." $payment_status <br/>\r\n";
				$transaction_details .= __("Type:",EDOMAIN)."   $payment_type <br/>\r\n";
				$transaction_details .= __("Date:",EDOMAIN)."   $payment_date <br/>\r\n";
				$transaction_details .= "-------------------------------------------------- <br/>\r\n";
				
				$subject = get_option('post_payment_success_admin_email_subject');
				if(!$subject)
				{
					$subject = __("Payment Success Confirmation Email","templatic");
				}
				$content = get_option('payment_success_email_content_to_admin');
				if(!$content)
				{
					$content = "<p>".__('Dear',EDOMAIN)." [#to_name#],</p><p>[#transaction_details#]</p><br><p>".__('We hope you enjoy. Thanks!',EDOMAIN)."</p><p>[#site_name#]</p>";
				}
				$store_name = get_option('blogname');
				$fromEmail = get_option('admin_email');
				$fromEmailName = stripslashes(get_option('blogname'));	
				$search_array = array('[#to_name#]','[#transaction_details#]','[#site_name#]');
				$replace_array = array($fromEmail,$transaction_details,$store_name);
				$filecontent = str_replace($search_array,$replace_array,$content);
				@templ_send_email($fromEmail,$fromEmailName,$to,$user_login,$subject,$filecontent,''); // email to admin
				// post details
					$post_link = site_url().'/?ptype=preview&alook=1&pid='.$pid;
					$post_title = '<a href="'.$post_link.'">'.stripslashes($productinfo->post_title).'</a>'; 
					$aid = $productinfo->post_author;
					$userInfo = get_userdata($aid);
					$to_name = $userInfo->user_nicename;
					$to_email = $userInfo->user_email;
					$user_email = $userInfo->user_email;
				
				$transaction_details ="";
				$transaction_details .= __("Information Submitted URL",EDOMAIN)." <br/>\r\n";
				$transaction_details .= "-------------------------------------------------- <br/>\r\n";
				$transaction_details .= "  $post_title <br/>\r\n";
				
				$subject = get_option('payment_success_email_subject_to_client');
				if(!$subject)
				{
					$subject = __("Payment Success Confirmation Email",EDOMAIN);
				}
				$content = get_option('payment_success_email_content_to_client');
				if(!$content)
				{
					$content = "<p>".__('Dear',EDOMAIN)." [#to_name#],</p><p>[#transaction_details#]</p><br><p>".__('We hope you enjoy. Thanks!',EDOMAIN)."</p><p>[#site_name#]</p>";
				}
				$store_name = get_option('blogname');
				$search_array = array('[#to_name#]','[#transaction_details#]','[#site_name#]');
				$replace_array = array($to_name,$transaction_details,$store_name);
				$content = str_replace($search_array,$replace_array,$content);
				//@mail($user_email,$subject,$content,$headers);// email to client
				templ_send_email($fromEmail,$fromEmailName,$user_email,$user_login,$subject,$content,$extra='');
			}
		}
	}
}
/*
Function Name : event_transaction_mail_fn
Description : to fire the transaction mail
*/
function event_transaction_mail_fn()
{
	if(isset($_REQUEST['submit']) && $_REQUEST['submit'] !='')
	{
	$orderId = $_REQUEST['trans_id'];
		global $wpdb,$transection_db_table_name;
		$transection_db_table_name = $wpdb->prefix . "transactions";
		
		$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
		$orderinfo = $wpdb->get_row($ordersql);
	
		$pid = $orderinfo->post_id;
		$event_type = get_post_meta($pid,'event_type',true);
		$payment_type = $orderinfo->payment_method;
		$payment_date =  date_i18n(get_option('date_format'),strtotime($orderinfo->payment_date));
		$trans_status = $wpdb->query("update $transection_db_table_name SET status = '".$_REQUEST['ostatus']."' where trans_id = '".$orderId."'");
		$user_detail = get_userdata($orderinfo->user_id); // get user details 
		$user_email = $user_detail->user_email;
		$user_login = $user_detail->display_name;
		$my_post['ID'] = $pid;
		if(isset($_REQUEST['ostatus']) && $_REQUEST['ostatus']== 1)
			$status = 'publish';
		else
			$status = 'draft';
		$my_post['post_status'] = $status;
		wp_update_post( $my_post );
		
		if(isset($_REQUEST['ostatus']) && $_REQUEST['ostatus']== 1)
		{
			$payment_status = APPROVED_TEXT;
			if(trim(strtolower($event_type)) == strtolower('Recurring event'))
			{
				global $wpdb;
				$where = array( 'post_parent' => $pid , 'post_type' => 'event' );
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'recurring' ), $where );
			}
		}
		elseif(isset($_REQUEST['ostatus']) && $_REQUEST['ostatus']== 2)
		{
			$payment_status = ORDER_CANCEL_TEXT;
			if(trim(strtolower($event_type)) == strtolower('Recurring event'))
			{
				global $wpdb;
				$where = array( 'post_parent' => $pid , 'post_type' => 'event' );
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), $where );
			}
		}
		elseif(isset($_REQUEST['ostatus']) && $_REQUEST['ostatus']== 0)
		{
			$payment_status = PENDING_MONI;
			if(trim(strtolower($event_type)) == strtolower('Recurring event'))
			{
				global $wpdb;
				$where = array( 'post_parent' => $pid , 'post_type' => 'event');
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), $where );
			}
		}
		$to = get_site_emailId_plugin();
		$productinfosql = "select ID,post_title,guid,post_author from $wpdb->posts where ID = $pid";
		$productinfo = get_post($pid);
	   $post_name = $productinfo->post_title;
		$transaction_details="";
		$transaction_details .= "-------------------------------------------------- <br/>\r\n";
			$transaction_details .= __("Payment Details for Listing $post_name",EDOMAIN)." <br/>\r\n";
			$transaction_details .= "-------------------------------------------------- <br/>\r\n";
			$transaction_details .= __("Status:",EDOMAIN)." $payment_status <br/>\r\n";
			$transaction_details .= __("Type:",EDOMAIN)."   $payment_type <br/>\r\n";
			$transaction_details .= __("Date:",EDOMAIN)."   $payment_date <br/>\r\n";
			$transaction_details .= "-------------------------------------------------- <br/>\r\n";
			$subject = get_option('post_payment_success_admin_email_subject');
			if(!$subject)
			{
				$subject = __("Payment Success Confirmation Email","templatic");
			}
			$content = get_option('payment_success_email_content_to_admin');
			if(!$content)
			{
				$content = "<p>".__('Dear',EDOMAIN)." [#to_name#],</p><p>[#transaction_details#]</p><br><p>".__('We hope you enjoy. Thanks!',EDOMAIN)."</p><p>[#site_name#]</p>";
			}
			$store_name = get_option('blogname');
			$fromEmail = get_option('admin_email');
			$fromEmailName = stripslashes(get_option('blogname'));	
			$search_array = array('[#to_name#]','[#transaction_details#]','[#site_name#]');
			$replace_array = array($fromEmail,$transaction_details,$store_name);
			$filecontent = str_replace($search_array,$replace_array,$content);
			@templ_send_email($fromEmail,$fromEmailName,$to,$user_login,$subject,$filecontent,''); // email to admin
			// post details
				$post_link = site_url().'/?ptype=preview&alook=1&pid='.$pid;
				$post_title = '<a href="'.$post_link.'">'.stripslashes($productinfo->post_title).'</a>'; 
				$aid = $productinfo->post_author;
				$userInfo = get_userdata($aid);
				$to_name = $userInfo->user_nicename;
				$to_email = $userInfo->user_email;
				$user_email = $userInfo->user_email;
			
			$transaction_details ="";
			$transaction_details .= __("Information Submitted URL",EDOMAIN)." <br/>\r\n";
			$transaction_details .= "-------------------------------------------------- <br/>\r\n";
			$transaction_details .= "  $post_title <br/>\r\n";
			
			$subject = get_option('payment_success_email_subject_to_client');
			if(!$subject)
			{
				$subject = __("Payment Success Confirmation Email",EDOMAIN);
			}
			$content = get_option('payment_success_email_content_to_client');
			if(!$content)
			{
				$content = "<p>".__('Dear',EDOMAIN)." [#to_name#],</p><p>[#transaction_details#]</p><br><p>".__('We hope you enjoy. Thanks!',EDOMAIN)."</p><p>[#site_name#]</p>";
			}
			$store_name = get_option('blogname');
			$search_array = array('[#to_name#]','[#transaction_details#]','[#site_name#]');
			$replace_array = array($to_name,$transaction_details,$store_name);
			$content = str_replace($search_array,$replace_array,$content);
			//@mail($user_email,$subject,$content,$headers);// email to client
			templ_send_email($fromEmail,$fromEmailName,$user_email,$user_login,$subject,$content,$extra='');
	}
}
if( (isset($_REQUEST['cf']) && $_REQUEST['cf']!="") || (isset($_REQUEST['update_transaction_status']) && $_REQUEST['update_transaction_status']!="") ){
	
	if(isset($_REQUEST['cf']) && $_REQUEST['cf'] !='')
	{
		$cf = explode(",",$_REQUEST['cf'][0]);
		if(isset($cf[1]) && $cf[1] !='')
			$PostType = get_post_type($cf[1]);
	}
	if(isset($_REQUEST['update_transaction_status']) && $_REQUEST['update_transaction_status'] !='')
	{
		$PostType = get_post_type($_REQUEST['update_transaction_status']);
	}
	if(isset($PostType) && $PostType!="" && ($PostType=='event')){
		add_action('init','event_remove_tevolution_message');
	}
}


/*
 * Function Name: event_recurring_submit_form_validation
 * Return: add recurring event validation script
 */
add_action("wp_head",'event_recurring_submit_form_validation');
function event_recurring_submit_form_validation(){
	global $post;
	
	if(get_post_meta($post->ID,'submit_post_type',true) ==CUSTOM_POST_TYPE_EVENT && get_post_meta($post->ID,'is_tevolution_submit_form',true)==1){		
		add_filter('submit_form_validation','recurring_event_submit_form_validation');
		?>
          <script type="text/javascript">
		jQuery(document).ready(function(){ 
		    jQuery("input[name=recurrence_per]").blur(function() 
		    { 		    	
			 if (!jQuery("input[name=recurrence_per]").val())
			 {
				jQuery("#recurrence_per_error").html("<?php _e(RECURRING_PER,EDOMAIN);?>");
			 }
			else
			{
				jQuery("#recurrence_per_error").html("");
			}
		    }); 
		 
		    jQuery("input[name=recurrence_days]").blur(function() 
		    { 
				 if (!jQuery("input[name=recurrence_days]").val())
				 {
					jQuery("#recurrence_days_error").html("<?php _e(RECURRING_DAY_AFTER,EDOMAIN);?>");
					
				 }
				else
				{
					jQuery("#recurrence_days_error").html("");
				}
		    }); 
		}); 
		</script>
		<?php
	}
}


/*
 * Function Name: recurring_event_submit_form_validation
 * Return: add recurring event validation script
 */
function recurring_event_submit_form_validation($js_code){
	
	$js_code.='
			var event_type = jQuery("input[name=event_type]");
			if(event_type.attr("type") =="radio" && jQuery("input[name=event_type]:checked").val()=="Recurring event")
			{
				var re_flg=0;
				if (!jQuery("input[name=recurrence_per]").val())
				 {
					jQuery("#recurrence_per_error").html("'.__(RECURRING_PER,EDOMAIN).'");
					re_flg=1;
					
				 }
				else
				{
					jQuery("#recurrence_per_error").html("");
					re_flg=0;
				}
				if (!jQuery("input[name=recurrence_days]").val())
				 {
					jQuery("#recurrence_days_error").html("'.__(RECURRING_DAY_AFTER,EDOMAIN).'");
					re_flg=1;
				 }
				else
				{
					jQuery("#recurrence_days_error").html("");
					re_flg=0;
				}
				
				if(re_flg==1){
					return false;	
				}
			}';
			
	return $js_code;
}
/*validation at backend while end date is smaller than start date.*/
add_action('admin_footer','end_date_validation');
function end_date_validation(){
	global $pagenow,$post;	
	if(($pagenow == 'post-new.php' || $pagenow == 'post.php') && $post->post_type == 'event'){
		?>
        	<script>
				jQuery(document).ready(function(){
					jQuery("#end_date").blur(function(){
						if(jQuery('#enddate_error_msg').length)
						{
							jQuery('#enddate_error_msg').remove();
						}
						if(jQuery("#end_date").val() < jQuery("#st_date").val() || jQuery("#end_date").val() == "")
						{
							jQuery('#end_date').next('.ui-datepicker-trigger').next('p').after("<span id='enddate_error_msg' class='error'><?php echo __('Please enter the End date that occurs after the Start date.',EDOMAIN); ?><span>");
						}
					});
				});
			</script>
        <?php
	}
}
?>
