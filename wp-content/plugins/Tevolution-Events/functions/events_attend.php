<?php
/*
 * Function Name: event_user_attend
 * Return: display the event attend 
 */
add_action('event_user_attend','event_user_attend');
function event_user_attend(){
	global $post,$wpdb;
	
	if(is_single() && get_post_type()==CUSTOM_POST_TYPE_EVENT){
		$event_type = get_post_meta($post->ID,'event_type',true);		
		$recurrence_occurs = get_post_meta($post->ID,'recurrence_occurs',true);
		/* Recurring Event  */
		
		if(function_exists('icl_register_string')){
			icl_register_string(EDOMAIN,$recurrence_occurs ,$recurrence_occurs );
			$recurrence_occurs = icl_t(EDOMAIN,$recurrence_occurs ,$recurrence_occurs);
		}
	
		if(trim(strtolower($event_type)) == trim(strtolower('Recurring event')) && !tmpl_is_parent($post))
		{
			?>
            <script type="text/javascript">
			function show_recurring_event(type)
			{
				if(type == 'show')
				{
					document.getElementById("show_recurring").style.display = 'none';
					document.getElementById("hide_recurring").style.display = '';
					document.getElementById("recurring_events").style.display = 'block';
				}
				else if(type == 'hide')
				{
					document.getElementById("show_recurring").style.display = '';
					document.getElementById("hide_recurring").style.display = 'none';
					document.getElementById("recurring_events").style.display = 'none';
				}
				return true;
			}
			</script>
				 <?php  if(isset($_GET['recurrences']) && $_GET['recurrences'] == 1){ 
							$display= "style=display:block;"; 
						}else{ 
							$display= "style=display:none;"; } ?>
              
				<?php if(isset($_GET['recurrences']) && $_GET['recurrences'] == 1){ ?>
					<div id="show_recurring" style="display:none;"  onclick="return show_recurring_event('show');" ><button class="reverse"><?php echo sprintf(__('Show occurrences',EDOMAIN), $recurrence_occurs);  ?></button></div>
					<div id="hide_recurring" onclick="return show_recurring_event('hide');" ><button class="reverse secondary_btn"><?php echo sprintf(__('Hide occurrences',EDOMAIN), $recurrence_occurs);  ?></button></div>
				<?php }else{ ?>
					<div id="show_recurring"  onclick="return show_recurring_event('show');" ><button class="reverse secondary_btn"><?php echo sprintf(__('Show occurrences',EDOMAIN), $recurrence_occurs);  ?></button></div>
					<div id="hide_recurring" style="display:none;" onclick="return show_recurring_event('hide');" ><button class="reverse"><?php echo sprintf(__('Hide occurrences',EDOMAIN), $recurrence_occurs);  ?></button></div>
				<?php } ?>
			  
               <div id="recurring_events"  <?php echo $display; ?> class="recurring_info">
               	<?php 
					$event_manager_setting = get_option('event_manager_setting');
					if($event_manager_setting['hide_attending_event'] == 'yes')
					{
						echo '<div class="recurring_event_class" >'.hide_attend_recurrence_event($post->ID).'</div>';
					}
					else
					{
						echo attend_recurrence_event($post->ID);
					}?>
               </div>
    	<?php
		}// Finish the recurring event if condition
		
		/* Regular Event  */
		$event_manager_setting = get_option('event_manager_setting');
		if(!isset($event_manager_setting['hide_attending_event']) && @$event_manager_setting['hide_attending_event'] != 'yes')
		{
			if(trim(strtolower($event_type)) == trim(strtolower('Regular event'))):
				if( get_option('timezone_string')!="" ){
					date_default_timezone_set(get_option('timezone_string'));
				}
				$today = strtotime(date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))));
				$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',strtotime(get_post_meta($post->ID,'set_end_time',true))));
				if($set_end_time >= $today)
				{
				?>
					   <div class="attending_event clearfix"> 
					   <?php echo attend_event_manager_html($post->post_author,$post->ID); ?>
					   </div>  
				<?php // Finish regular event if condition
				}
			endif;
		}
	}
	wp_reset_query();
	wp_reset_postdata();
	
}
/*
	It Will Display the Box of user going to attend the event or not.
 */
function attend_event_manager_html($user_id,$post_id)
{
	global $current_user;
	$post = get_post($post_id);
	
	$user_meta_data = get_user_meta($current_user->ID,'user_attend_event',true);
	$profile_photo=get_user_meta($current_user->ID,'profile_photo',true);
	if($profile_photo):
	
		echo '<a href="'.get_bloginfo('url').'/author/' . $current_user->user_nicename . '"><img src="'.$profile_photo.'" width="60" height="60"></a>';
	else:
		echo get_avatar($current_user->user_email,60);
	endif;
	if($user_meta_data && in_array('#'.$post_id.'#',$user_meta_data))
	{
		?>
		<span id="attend_event_<?php echo $post_id;?>" class="fav"  > 
		<span class="span_msg"><?php
		if($current_user->ID){
			echo "<a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a>, ".REMOVE_EVENT_MSG." <strong>".$post->post_title."</strong><br/>";
		}else{
			echo "<a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a> ".REMOVE_EVENT_MSG." <strong>".$post->post_title."</strong><br/>";
		}	
		?>
		<a href="javascript:void(0);" class="addtofav b_review not_attending" onclick="javascript:addTo_AttendEvent('<?php echo $post_id;?>','remove');"><?php echo REMOVE_EVENT_TEXT;?></a>  	
		<span id="attended_persons" class="attended_persons"><?php echo event_atended_persons($post_id); ?></span>
		</span>
		</span>    
		<?php
	}else{
	?>
	<span id="attend_event_<?php echo $post_id;?>" class="fav">
	<span class="span_msg"><?php 
	if($current_user->ID){
		echo "<a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a>, ".ATTEND_EVENT_MSG." <strong>".$post->post_title."</strong>? <br/>";
	}else{
		echo "<a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a> ".ATTEND_EVENT_MSG." <strong>".$post->post_title."</strong>? <br/>";
	}
			if($current_user->ID ==''){
				$data_reveal_id ='data-reveal-id="tmpl_reg_login_container"';
			}else{
				$data_reveal_id ='';
			}
	?>
     	<a href="javascript:void(0);" class="addtofav b_review attending" <?php echo $data_reveal_id; ?> onclick="javascript:addTo_AttendEvent(<?php echo $post_id;?>,'add');"><?php echo ATTEND_EVENT_TEXT;?>&nbsp;</a>
	<span id="attended_persons" class="attended_persons"><?php echo event_atended_persons($post_id); ?></span>
	</span>
	</span>
	<?php } 
}
/*
Function Name " event_atended_persons
args : post id
Description : count how many numbers of users going to attend the event (regular event attenders)
*/
function event_atended_persons($post_id){
	global $wpdb;
	$qry_results = $wpdb->get_results("select * from $wpdb->usermeta where meta_key LIKE '%user_attend_event%' and meta_value LIKE '%#$post_id#%' ");	
	$peoples = count($qry_results);
	
	if($peoples >0){
		$page_id = get_option('event_attending_user_page');
		if(function_exists('icl_object_id')){
			$page_id = icl_object_id($page_id, 'page', false);
		}
		$page_template_url=get_permalink($page_id);		
		if(strstr($page_template_url,'?'))		
			$userlist_url=$page_template_url.'&eid='.$post_id;
		else
			$userlist_url=$page_template_url.'?eid='.$post_id;
		
		if($peoples == 1){
			return $peoples." <a href='".$userlist_url."' target='_blank'>".__('person is attending.',EDOMAIN)." </a>";
		}else{
			return $peoples." <a href='".$userlist_url."' target='_blank'>".__('people are attending.',EDOMAIN)." </a>";			
		}
	}else{
		return __('No one is attending yet.',EDOMAIN);
	}
}
add_action('wp_ajax_nopriv_event_attend','event_attend_user');
add_action('wp_ajax_event_attend','event_attend_user');
function event_attend_user(){	
	if(isset($_REQUEST['ptype']) &&$_REQUEST['ptype'] == 'favorite'){
		if(isset($_REQUEST['action_type']) && $_REQUEST['action_type']=='add')
		{
			if(isset($_REQUEST['st_date']) && $_REQUEST['st_date'] != '' && $_REQUEST['st_date'] != 'undefined' )
				event_add_to_attend($_REQUEST['pid'],$_REQUEST['st_date'],$_REQUEST['end_date']);
			else
				event_add_to_attend($_REQUEST['pid']);
		}else{
			if(isset($_REQUEST['st_date']) && $_REQUEST['st_date'] != '' && $_REQUEST['st_date'] != 'undefined')
				remove_addto_attend_event($_REQUEST['pid'],$_REQUEST['st_date'],$_REQUEST['end_date']);
			else
				remove_addto_attend_event($_REQUEST['pid']);
		}
		
	}
	exit;
}
function event_add_to_attend($post_id,$st_date='',$end_date='')
{
	global $current_user,$post;
	$post = get_post($post_id);
	$user_meta_data = array();
	$user_meta_data = get_user_meta($current_user->ID,'user_attend_event',true);
	$user_meta_data[]= "#".$post_id."#";
	update_user_meta($current_user->ID, 'user_attend_event', $user_meta_data);
	if($st_date)
	{
		$user_meta_start_date = array();
		$user_meta_start_date = get_user_meta($current_user->ID,'user_attend_event_st_date',true);
		$user_meta_start_date[]=$post_id."_".$st_date;
		update_user_meta($current_user->ID, 'user_attend_event_st_date', $user_meta_start_date);
	}
	if($end_date)
	{
		$user_meta_end_date = array();
		$user_meta_end_date = get_user_meta($current_user->ID,'user_attend_event_end_date',true);
		$user_meta_end_date[]=$post_id."_".$end_date;
		update_user_meta($current_user->ID, 'user_attend_event_end_date', $user_meta_end_date);
	}
	
	$user_meta_data = get_user_meta($current_user->ID,'user_attend_event',true);
	$user_attend_event_start_date = get_user_meta($current_user->ID,'user_attend_event_st_date',true);	
	$user_attend_event_end_date = get_user_meta($current_user->ID,'user_attend_event_end_date',true);
	$a .= get_avatar($current_user->user_email,60,60);
	if($current_user->ID ==''){
		$data_reveal_id ='data-reveal-id="tmpl_reg_login_container"';
	}else{
		$data_reveal_id ='';
	}
	if(!$st_date)
	{
		echo '<span class="span_msg"><a href='.get_author_posts_url($current_user->ID).'>'.$current_user->display_name.'</a>, '.REMOVE_EVENT_MSG."<a href=".get_permalink($post->ID)."> <strong>".$post->post_title."</strong></a><br/>".'<a href="javascript:void(0);" class="addtofav b_review not_attending '.$data_reveal_id.'" onclick="javascript:addTo_AttendEvent(\''.$post_id.'\',\'remove\');">'.REMOVE_EVENT_TEXT.'</a><span id="attended_persons" class="attended_persons">'.event_atended_persons($post_id).'</span>'.'</span>';exit;	
		}
	elseif($user_meta_data && in_array("#".$post_id."#",$user_meta_data,true) && in_array($post_id."_".$st_date,$user_attend_event_start_date,true) && in_array($post_id."_".$end_date,$user_attend_event_end_date,true))
	{
		echo '<span class="span_msg"><a href='.get_author_posts_url($current_user->ID).'>'.$current_user->display_name.'</a>, '.REMOVE_EVENT_MSG."<a href=".get_permalink($post->ID)."> <strong>".$post->post_title."</strong></a><br/>".'<a href="javascript:void(0);" class="addtofav b_review not_attending '.$data_reveal_id.'" onclick="javascript:addTo_AttendEvent(\''.$post_id.'\',\'remove\',\''.$st_date.'\',\''.$end_date.'\');">'.REMOVE_EVENT_TEXT.'</a><span id="attended_persons" class="attended_persons">'.attend_recurring_event_persons($post_id,$st_date,$end_date).'</span>'.'</span>';
	}
}
/*
 * Function Name: remove_from_attend_event
 * Return : Remove attend event
 */
//This function would remove the favourite property earlier
function remove_addto_attend_event($post_id,$st_date='',$end_date='')
{
	global $current_user;
	$user_meta_data = array();
	$post= get_post($post_id);
	$user_meta_data = get_user_meta($current_user->ID,'user_attend_event',true);
	if(in_array("#".$post_id."#",$user_meta_data))
	{
		$i = 0;
		$user_new_data = array();
		foreach($user_meta_data as $key => $value)
		{
			
			if("#".$post_id."#" == $value && $i == 0)
			{
				$value= '';
				$i++;
			}else{
				$user_new_data[] = $value;
			}
		}	
		$user_meta_data	= $user_new_data;
	}
	update_user_meta($current_user->ID, 'user_attend_event', $user_meta_data);
	
	$user_attend_event_st_date = array();
	$user_attend_event_st_date = get_user_meta($current_user->ID,'user_attend_event_st_date',true);
	
	if($st_date)
	{
	if(in_array($post_id."_".$st_date,$user_attend_event_st_date))
	{
		$user_new_data = array();
		foreach($user_attend_event_st_date as $key => $value)
		{
			if($post_id."_".$st_date == $value)
			{
				$value= '';
			}else{
				$user_new_data[] = $value;
			}
		}
		$user_attend_event_st_date	= $user_new_data;
	}
	update_user_meta($current_user->ID, 'user_attend_event_st_date', $user_attend_event_st_date);
	
	$user_attend_event_end_date = array();
	$user_attend_event_end_date = get_user_meta($current_user->ID,'user_attend_event_end_date',true);
	if(in_array($post_id."_".$end_date,$user_attend_event_end_date))
	{
		$user_new_data = array();
		foreach($user_attend_event_end_date as $key => $value)
		{
			if($post_id."_".$end_date == $value)
			{
				$value= '';
			}else{
				$user_new_data[] = $value;
			}
		}	
		$user_attend_event_end_date	= $user_new_data;
	}
	update_user_meta($current_user->ID, 'user_attend_event_end_date', $user_attend_event_end_date);
	}
	
	if($current_user->ID ==''){
		$data_reveal_id ='data-reveal-id="tmpl_reg_login_container"';
	}else{
		$data_reveal_id ='';
	}
	
	if(!$st_date)
	{
		echo '<span class="span_msg"><a href='.get_author_posts_url($current_user->ID).'>'.$current_user->display_name.'</a>, '.ATTEND_EVENT_MSG.'<a href='.get_permalink($post->ID).'> <strong>'.$post->post_title.'</strong></a>?<br/> <a class="addtofav b_review attending" href="javascript:void(0);"  '.$data_reveal_id.' onclick="javascript:addTo_AttendEvent(\''.$post_id.'\',\'add\');">'.ATTEND_EVENT_TEXT." ".'</a> <span id="attended_persons" class="attended_persons">'.event_atended_persons($post_id).'</span></span>';exit;
	}
	else
	{
		if($current_user->ID ==''){
			$data_reveal_id ='data-reveal-id="tmpl_reg_login_container"';
		}else{
			$data_reveal_id ='';
		}
		echo '<span class="span_msg"><a href='.get_author_posts_url($current_user->ID).'>'.$current_user->display_name.'</a>, '.ATTEND_EVENT_MSG.'<a href='.get_permalink($post->ID).'> <strong>'.$post->post_title.'</strong></a>?<br/> <a class="addtofav b_review attending" href="javascript:void(0);" '.$data_reveal_id.'  onclick="javascript:addTo_AttendEvent(\''.$post_id.'\',\'add\',\''.$st_date.'\',\''.$end_date.'\');">'.ATTEND_EVENT_TEXT." ".'</a><span id="attended_persons" class="attended_persons">'.				attend_recurring_event_persons($post_id,$st_date,$end_date).'</span></span>';
	}
}
/*
Function Name : attend_recurring_event_persons
Description : list all recurring dates on detail page (recurring event attenders)
*/
function attend_recurring_event_persons($post_id,$st_date,$end_date){
	global $wpdb;	
	if(isset($post_id) && isset($st_date))
		$qry_results = $wpdb->get_results("select * from $wpdb->usermeta where meta_key LIKE '%user_attend_event_st_date%' and meta_value LIKE '%$post_id"._."$st_date%'");	
		$peoples = count($qry_results);
	
	if($peoples >0){
		$page_template_url=get_permalink(get_option('event_attending_user_page'));		
		if(strstr($page_template_url,'?'))		
			$userlist_url=$page_template_url.'&eid='.$post_id;
		else
			$userlist_url=$page_template_url.'?eid='.$post_id;
			
			if($peoples == 1){
				return $peoples." <a href='".$userlist_url."' target='_blank'>".__('person is attending.',EDOMAIN)." </a>";
			}else{
				return $peoples." <a href='".$userlist_url."' target='_blank'>".__('peoples are attending.',EDOMAIN)." </a>";
			}
	}else{
		return __('No one is attending yet.',EDOMAIN);
	}	
}
/*
 *Function Name : attend_recurrence_event
 *Description : start of function for recurrence event.
 */
function attend_recurrence_event($post_id)
{

	
	global $wpdb,$current_user,$post;
	$start_date = strtotime(get_post_meta($post_id,'st_date',true));
	$end_date = strtotime(get_post_meta($post_id,'end_date',true));
	$recurrence_occurs = get_post_meta($post_id,'recurrence_occurs',true);//recurrence type
	$recurrence_per = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
	$current_date = date('Y-m-d');
	$recurrence_days = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$post_title = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$recurrence_list = "";
	//_e('This is a ',EDOMAIN);echo $recurrence_occurs;_e(' Event.',EDOMAIN);	
	if( get_option('timezone_string')!="" ){
		date_default_timezone_set(get_option('timezone_string'));
	}
	$today = strtotime(date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))));
                                 /*
					fetch child recurring events of parent recurring event
				*/
				
				$args=
				array( 
				'post_type' => 'event',
				'posts_per_page' => -1	,
				'post_status' => array('recurring'),
				'post_parent' => $post_id,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'event_type',
						'value' => 'Regular event',
						'compare' => '=',
						'type'=> 'text'
					)
				),
				'meta_key' => 'st_date',
				'orderby' => 'meta_value_num',
				'meta_value_num'=>'sort_order',
				'order' => 'ASC'
				);
				$post_query = null;
				$post_query = new WP_Query($args);
	if($recurrence_occurs == 'daily' )
	{
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$recurrence_list .= "<ul>";
		$z=0;
		$is_recurring_event = 0;
		for($i=0;$i<($days_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			if(($i%$recurrence_per) == 0 )
			{
				$j = ($i+$recurrence_days-1);
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i day");
				$st_date = date_i18n(get_option("date_format"), $st_date1);
				$end_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$j day");
				$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
				$end_date_time = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true).' '.get_post_meta($post_id,'end_time',true))) . " +$j day");
				$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
				
				if($end_date1 >  $post_end_date)
					$end_date1 = $post_end_date;
				$end_date = date_i18n(get_option("date_format"), $end_date1);
				$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
				$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
				
									
				if($post_query){
					global $post;
					$post = $post_query->posts[$z];
				}			
				//$set_end_time =  strtotime(date_i18n('Y-m-d H:i:s',strtotime(get_post_meta($post->ID,'set_end_time',true))));
				if($today <= $set_end_time)
				{
						$is_recurring_event = 1;
						$recurrence_list .= "<li class=$class>";
						$recurrence_list .= "<div class='date_info'>
						<p>
							  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
									  <strong>".__("To",EDOMAIN)." </strong>   $end_date.$end_time <br/>
						</p>
									</div>";
							$recurrence_list .= "<div class='attending_event'> ";
							
							$recurrence_list .= event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option("date_format"), $st_date1),date_i18n(get_option("date_format"),$end_date1));
							$recurrence_list .= "	<div class='clearfix'></div>
							
							</div>  ";
					
					$recurrence_list .= "</li>";
				}
			}
			else
			{
				continue;
			}
			$z++;
		}
		if($is_recurring_event == 0)
		{
			_e('All recurrences of this event are ended. Please follow other events to attend or you can check it out later when we have upcoming recurrences for it.',EDOMAIN);
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
		$is_recurring_event = 0;
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
			//first, get the start of this week as time stamp
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
				$weekday_day1 = date('Y-m-d',$weekday_date); //the day of the week we're checking, taking into account wp start of week setting		
				
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
					//$weekday_date = $weekday_date + strtotime("+$recurrence_interval week", date("Y-m-d",$weekday_date));
					$weekday_date= strtotime("+$recurrence_interval week", $weekday_date);
				}
			}//done!
			sort($matching_days);
			for($z=0;$z<(count($matching_days));$z++)
			{
				$class= ($z%2) ? "odd" : "even";
				$st_date1 = $matching_days[$z];
				$st_date = date("Y-m-d", $st_date1);
				$st_end_date = date("Y-m-d", $matching_days[$z]);
				$week_recurrence_days = $recurrence_days - 1;
				$end_date1 = strtotime(date("Y-m-d", strtotime($st_end_date)) . " +$week_recurrence_days day");
				$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
				if($end_date1 >  $post_end_date)
					$end_date1 = $post_end_date;
				$end_date = date_i18n(get_option('date_format'), $end_date1);
				$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
				$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
				
					if($post_query->have_posts()){
						global $post;
						$post = $post_query->posts[$z];
					}
				$end_date_time = strtotime(date("Y-m-d", strtotime($st_date.' '.$end_time)) . " +$recurrence_days day");
				
				if($today <= $end_date_time)
				{
					$is_recurring_event = 1;
					if($recurrence_days >=2)
					{
						$on = __("From",EDOMAIN);
					}else
					{
						$on = __("On",EDOMAIN);
					}
					$recurrence_list .= "<li class=$class>";
					$recurrence_list .= "<div class='date_info'>
						<p>
							   <strong>".$on."</strong>   $st_date $st_time";
							  if($recurrence_days >=2)
							  {
									$recurrence_list .= "  <strong>".__("To",EDOMAIN)." </strong>   $end_date $end_time <br/>";
							  }
						$recurrence_list .= "</p>
							</div>";				
					$recurrence_list .= "<div class='attending_event'> ";
					$recurrence_list .= event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option('date_format'), $st_date1),date_i18n(get_option('date_format'),$end_date1));
					$recurrence_list .= "	<div class='clearfix'></div>
				   </div>  ";
					 
					$recurrence_list .= "</li>";
				}
			}
			if($is_recurring_event == 0)
			{
				_e('All recurrences of this event are ended. Please follow other events to attend or you can check it out later when we have upcoming recurrences for it.',EDOMAIN);
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
		$is_recurring_event = 0;
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
		for($z=0;$z<count($matching_days);$z++)
		{ 
			$class= ($z%2) ? "odd" : "even";
			$st_date1 = $matching_days[$z];
			$st_date = date_i18n(get_option('date_format'), $matching_days[$z]);
			$st_end_date = date("Y-m-d", $matching_days[$z]);
			$end_date1 = strtotime(date("Y-m-d", strtotime($st_end_date)) . " +$recurrence_days day");
			$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
			if($end_date1 >  $post_end_date)
				$end_date1 = $post_end_date;
			$end_date = date_i18n(get_option('date_format'), $end_date1);
			$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
			$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
			
					if($post_query->have_posts()){
						global $post;
						$post = $post_query->posts[$z];
					}
				$end_date_time = strtotime(date("Y-m-d", strtotime($st_date.' '.$end_time)) . " +$recurrence_days day");
				$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
				
				if($today <= $set_end_time)
				{	
					$is_recurring_event = 1;
					$recurrence_list .= "<li class=$class>";
					$recurrence_list .= "<div class='date_info'>
					<p>
						  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
								  <strong>".__("To",EDOMAIN)." </strong>   $end_date $end_time <br/>
					</p>
									</div>";							
					$recurrence_list .= "<div class='attending_event'> ";
					$recurrence_list .= event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option("date_format"), $st_date1),date_i18n(get_option("date_format"),$end_date1));
					$recurrence_list .= "	<div class='clearfix'></div>
					</div>  ";						
					$recurrence_list .= "</li>";
				}
		}
		if($is_recurring_event == 0)
		{
			_e('All recurrences have come to an end. You can check it out later when we have upcoming recurrences for it.',EDOMAIN);
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
		$is_recurring_event = 0;
		$recurrence_list .= "<ul>";		
		for($i=0;$i<($years_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			$startdate = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i year");
			$end_date_time = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true).' '.get_post_meta($post_id,'end_time',true))) . " +$i year");
			$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
			$startdate1 = explode("-",date('Y-m-d',$startdate));
			$year = $startdate1[0];
			$month = $startdate1[1];
			$day = $startdate1[2];
			$date2 = mktime(0, 0, 0, $month, $day, $year);
			$month = (int)date('n', $date2); //get the current month.
			if($month == $st_date__month  && $i%$recurrence_per == 0)
			{
				$st_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))). " +$i year");
				$st_date = date_i18n(get_option('date_format'), $st_date);
				
				$end_date = $date2 = mktime(0, 0, 0, $month, $day+$recurrence_days, $year);
				$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
				if($end_date >  $post_end_date)
					$end_date = $post_end_date;
				$end_date = date_i18n(get_option('date_format'), $end_date);
				$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
				$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
				
				
					if($post_query->have_posts()){
						global $post;
						$post = $post_query->posts[$i];
					}
				
				if($today <= $set_end_time)
				{
					$is_recurring_event = 1;
					$recurrence_list .= "<li class=$class>";
					$recurrence_list .= "<div class='date_info'>
					<p>
						  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
								  <strong>".__("To",EDOMAIN)."</strong>   $end_date $end_time <br/>
					</p>
									</div>";
								
					$recurrence_list .= "<div class='attending_event'> ";
					$recurrence_list .= event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option('date_format'), $st_date1),date_i18n(get_option('date_format'),$end_date1));
					$recurrence_list .= "	<div class='clearfix'></div>
					</div>  ";
							 
					$recurrence_list .= "</li>";
				}
			}
			else
			{
				continue;
			}
		}
		if($is_recurring_event == 0)
		{
			_e('All recurrences of this event are ended. Please follow other events to attend or you can check it out later when we have upcoming recurrences for it.',EDOMAIN);
		}
	}
	return $recurrence_list;
}
/*
 *Function Name : hide_attend_recurrence_event
 *Description : start of function for recurrence event.
 */
function hide_attend_recurrence_event($post_id)
{
	
	global $wpdb,$current_user,$post;
	$start_date = strtotime(get_post_meta($post_id,'st_date',true));
	$end_date = strtotime(get_post_meta($post_id,'end_date',true));
	$recurrence_occurs = get_post_meta($post_id,'recurrence_occurs',true);//recurrence type
	$recurrence_per = get_post_meta($post_id,'recurrence_per',true);//no. of occurrences.
	$current_date = date('Y-m-d');
	$recurrence_days = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$post_title = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$recurrence_list = "";

	if( get_option('timezone_string')!="" ){
		date_default_timezone_set(get_option('timezone_string'));
	}
	$today = strtotime(date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))));
                               /*
					fetch child recurring events of parent recurring event
				*/
				
				$args=
				array( 
				'post_type' => 'event',
				'posts_per_page' => -1	,
				'post_status' => array('recurring'),
				'post_parent' => $post_id,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'event_type',
						'value' => 'Regular event',
						'compare' => '=',
						'type'=> 'text'
					)
				),
				'meta_key' => 'st_date',
				'orderby' => 'meta_value_num',
				'meta_value_num'=>'sort_order',
				'order' => 'ASC'
				);
				$post_query = null;
				$post_query = new WP_Query($args);
	if($recurrence_occurs == 'daily' )
	{
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$recurrence_list .= "<ul>";
		$z=0;
		$is_recurring_event = 0;
		for($i=0;$i<($days_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			if(($i%$recurrence_per) == 0 )
			{
				$j = ($i+$recurrence_days-1);
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i day");
				$st_date = date_i18n(get_option("date_format"), $st_date1);
				$end_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$j day");
				$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
				$end_date_time = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true).' '.get_post_meta($post_id,'end_time',true))) . " +$j day");
				$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
				
				if($end_date1 >  $post_end_date)
					$end_date1 = $post_end_date;
				$end_date = date_i18n(get_option("date_format"), $end_date1);
				$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
				$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
				
									
				if($post_query){
					global $post;
					$post = $post_query->posts[$z];
				}			
				//$set_end_time =  strtotime(date_i18n('Y-m-d H:i:s',strtotime(get_post_meta($post->ID,'set_end_time',true))));
				if($today <= $set_end_time)
				{
						$is_recurring_event = 1;
						$recurrence_list .= "<li class=$class>";
						$recurrence_list .= "<div class='date_info'>
						<p>
							  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
									  <strong>".__("To",EDOMAIN)." </strong>   $end_date.$end_time <br/>
						</p>
									</div>";
							
							$recurrence_list .= hide_event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option("date_format"), $st_date1),date_i18n(get_option("date_format"),$end_date1));
							$recurrence_list .= "	<div class='clearfix'></div>";
					
					$recurrence_list .= "</li>";
				}
			}
			else
			{
				continue;
			}
			$z++;
		}
		if($is_recurring_event == 0)
		{
			_e('All recurrences of this event are ended. Please follow other events to attend or you can check it out later when we have upcoming recurrences for it.',EDOMAIN);
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
		$is_recurring_event = 0;
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
			//first, get the start of this week as time stamp
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
				$weekday_day1 = date('Y-m-d',$weekday_date); //the day of the week we're checking, taking into account wp start of week setting		
				
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
					//$weekday_date = $weekday_date + strtotime("+$recurrence_interval week", date("Y-m-d",$weekday_date));
					$weekday_date= strtotime("+$recurrence_interval week", $weekday_date);
				}
			}//done!
			sort($matching_days);
			for($z=0;$z<(count($matching_days));$z++)
			{
				$class= ($z%2) ? "odd" : "even";
				$st_date1 = $matching_days[$z];
				$st_date = date_i18n(get_option('date_format'), $st_date1);
				$st_end_date = date("Y-m-d", $matching_days[$z]);
				$end_date1 = strtotime(date("Y-m-d", strtotime($st_end_date)) . " +$recurrence_days day");
				$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
				if($end_date1 >  $post_end_date)
					$end_date1 = $post_end_date;
				$end_date = date_i18n(get_option('date_format'), $end_date1);
				$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
				$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
				
					if($post_query->have_posts()){
						global $post;
						$post = $post_query->posts[$z];
					}
				$end_date_time = strtotime(date("Y-m-d", strtotime($st_date.' '.$end_time)) . " +$recurrence_days day");
				$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
				
				if($today <= $set_end_time)
				{
					$is_recurring_event = 1;
					$recurrence_list .= "<li class=$class>";
					$recurrence_list .= "<div class='date_info'>
						<p>
							  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
									  <strong>".__("To",EDOMAIN)." </strong>   $end_date $end_time <br/>
						</p>
							</div>";				
					
					$recurrence_list .= hide_event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option('date_format'), $st_date1),date_i18n(get_option('date_format'),$end_date1));
					$recurrence_list .= "	<div class='clearfix'></div>";
					 
					$recurrence_list .= "</li>";
				}
			}
			if($is_recurring_event == 0)
			{
				_e('All recurrences of this event are ended. Please follow other events to attend or you can check it out later when we have upcoming recurrences for it.',EDOMAIN);
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
		$is_recurring_event = 0;
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
		for($z=0;$z<count($matching_days);$z++)
		{ 
			$class= ($z%2) ? "odd" : "even";
			$st_date1 = $matching_days[$z];
			$st_date = date_i18n(get_option('date_format'), $matching_days[$z]);
			$st_end_date = date("Y-m-d", $matching_days[$z]);
			$end_date1 = strtotime(date("Y-m-d", strtotime($st_end_date)) . " +$recurrence_days day");
			$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
			if($end_date1 >  $post_end_date)
				$end_date1 = $post_end_date;
			$end_date = date_i18n(get_option('date_format'), $end_date1);
			$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
			$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
			
					if($post_query->have_posts()){
						global $post;
						$post = $post_query->posts[$z];
					}
				$end_date_time = strtotime(date("Y-m-d", strtotime($st_date.' '.$end_time)) . " +$recurrence_days day");
				$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
				
				if($today <= $set_end_time)
				{	
					$is_recurring_event = 1;
					$recurrence_list .= "<li class=$class>";
					$recurrence_list .= "<div class='date_info'>
					<p>
						  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
								  <strong>".__("To",EDOMAIN)." </strong>   $end_date $end_time <br/>
					</p>
									</div>";							
					
					$recurrence_list .= hide_event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option("date_format"), $st_date1),date_i18n(get_option("date_format"),$end_date1));
					$recurrence_list .= "	<div class='clearfix'></div>";						
					$recurrence_list .= "</li>";
				}
		}
		if($is_recurring_event == 0)
		{
			_e('All recurrences have come to an end. You can check it out later when we have upcoming recurrences for it.',EDOMAIN);
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
		$is_recurring_event = 0;
		$recurrence_list .= "<ul>";		
		for($i=0;$i<($years_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			$startdate = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i year");
			$end_date_time = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true).' '.get_post_meta($post_id,'end_time',true))) . " +$i year");
			$set_end_time = strtotime(date_i18n('Y-m-d H:i:s',$end_date_time));
			$startdate1 = explode("-",date('Y-m-d',$startdate));
			$year = $startdate1[0];
			$month = $startdate1[1];
			$day = $startdate1[2];
			$date2 = mktime(0, 0, 0, $month, $day, $year);
			$month = (int)date('n', $date2); //get the current month.
			if($month == $st_date__month  && $i%$recurrence_per == 0)
			{
				$st_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))). " +$i year");
				$st_date = date_i18n(get_option('date_format'), $st_date);
				
				$end_date = $date2 = mktime(0, 0, 0, $month, $day+$recurrence_days, $year);
				$post_end_date  = strtotime(get_post_meta($post_id,'end_date',true));
				if($end_date >  $post_end_date)
					$end_date = $post_end_date;
				$end_date = date_i18n(get_option('date_format'), $end_date);
				$st_time = get_formated_time(get_post_meta($post_id,'st_time',true));
				$end_time = get_formated_time(get_post_meta($post_id,'end_time',true));
				
				
					if($post_query->have_posts()){
						global $post;
						$post = $post_query->posts[$i];
					}
				
				if($today <= $set_end_time)
				{
					$is_recurring_event = 1;
					$recurrence_list .= "<li class=$class>";
					$recurrence_list .= "<div class='date_info'>
					<p>
						  <strong>".__("From",EDOMAIN)."</strong>   $st_date $st_time
								  <strong>".__("To",EDOMAIN)."</strong>   $end_date $end_time <br/>
					</p>
									</div>";
								
					
					$recurrence_list .= hide_event_attend_recurring_html($post->post_author,$post->ID,date_i18n(get_option('date_format'), $st_date1),date_i18n(get_option('date_format'),$end_date1));
					$recurrence_list .= "	<div class='clearfix'></div>";
							 
					$recurrence_list .= "</li>";
				}
			}
			else
			{
				continue;
			}
		}
		if($is_recurring_event == 0)
		{
			_e('All recurrences of this event are ended. Please follow other events to attend or you can check it out later when we have upcoming recurrences for it.',EDOMAIN);
		}
	}
	return $recurrence_list;
}
/*
Function Name : event_attend_recurring_html
Description : list all recurring dates on detail page
*/
function hide_event_attend_recurring_html($user_id,$post_id,$st_date,$end_date)
{
	global $current_user,$post;
	$a = "";
	$post = get_post($post_id);
	$user_meta_data = get_user_meta($current_user->ID,'user_attend_event',true);
	$user_attend_event_start_date = get_user_meta($current_user->ID,'user_attend_event_st_date',true);
	$user_attend_event_end_date = get_user_meta($current_user->ID,'user_attend_event_end_date',true);
	$event_manager_setting = get_option('event_manager_setting');
	if($event_manager_setting['hide_attending_event'] == 'yes')
	{
		$a= '';
			
		$a .= "<div class='recurring_event_title'> ";
		$a .= "<a href=".get_permalink($post->ID)."><strong>".$post->post_title."</strong></a><br/>";
		$a .= "	<div class='clearfix'></div></div>  ";
		
	}
	
	return $a;
}
/*
Function Name : event_attend_recurring_html
Description : list all recurring dates on detail page
*/
function event_attend_recurring_html($user_id,$post_id,$st_date,$end_date)
{
	global $current_user,$post;
	$a = "";
	$post = get_post($post_id);
	$user_meta_data = get_user_meta($current_user->ID,'user_attend_event',true);
	$user_attend_event_start_date = get_user_meta($current_user->ID,'user_attend_event_st_date',true);
	$user_attend_event_end_date = get_user_meta($current_user->ID,'user_attend_event_end_date',true);
	$a .= get_avatar($current_user->user_email,60);
	
	if($user_meta_data && in_array("#".$post_id."#",$user_meta_data) && in_array($post_id."_".$st_date,$user_attend_event_start_date) && in_array($post_id."_".$end_date,$user_attend_event_end_date))
	{
		if($current_user->ID){
		$a.="<span id='attend_event_$post_id-$st_date' class='fav' > 
		<span class='span_msg'><a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a>, ".REMOVE_EVENT_MSG." <a href=".get_permalink($post->ID)."><strong>".$post->post_title."</strong></a><br/><a href='javascript:void(0)' class='addtofav b_review not_attending'onclick='javascript:addTo_AttendEvent(".$post_id.",\"remove\",\"".$st_date."\",\"".$end_date."\")'>".REMOVE_EVENT_TEXT."</a>   
		<span id='attend_persons_$post_id-$st_date' class='attended_persons'>".attend_recurring_event_persons($post->ID,$st_date,$end_date)."</span>
		</span>		
		</span>";	
		}else{
		$a.="<span id='attend_event_$post_id-$st_date' class='fav' > 
		<span class='span_msg'><a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a> ".REMOVE_EVENT_MSG."<a href=".get_permalink($post->ID)."><strong>".$post->post_title."</strong></a><br/><a href='javascript:void(0)' class='addtofav b_review not_attending'  onclick='javascript:addTo_AttendEvent(".$post_id.",\"remove\",\"".$st_date."\",\"".$end_date."\")'>".REMOVE_EVENT_TEXT."</a>   
		<span id='attend_persons_$post_id-$st_date' class='attended_persons'>".attend_recurring_event_persons($post->ID,$st_date,$end_date)."</span>
		</span>		
		</span>";	
		}
	}else{
		if($current_user->ID){
		$a.="<span id='attend_event_$post_id-$st_date' class='fav'>
		<span class='span_msg'>"."<a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a>, ".ATTEND_EVENT_MSG." <a href=".get_permalink($post->ID)."><strong>".$post->post_title."</strong></a>? <br/>
		<a href='javascript:void(0)' class='addtofav b_review attending'  data-reveal-id='tmpl_reg_login_container'onclick='javascript:addTo_AttendEvent(".$post_id.",\"add\",\"".$st_date."\",\"".$end_date."\")'>".ATTEND_EVENT_TEXT." "."</a>
		<span id='attend_persons_$post_id-$st_date' class='attended_persons'>".attend_recurring_event_persons($post->ID,$st_date,$end_date)."</span>
		</span>
		</span>";
		}else{
			if($current_user->ID ==''){
				$data_reveal_id ='data-reveal-id="tmpl_reg_login_container"';
			}else{
				$data_reveal_id ='';
			}
			$a.="<span id='attend_event_$post_id-$st_date' class='fav'>
		<span class='span_msg'>"."<a href='".get_author_posts_url($current_user->ID)."'>".$current_user->display_name."</a> ".ATTEND_EVENT_MSG." <a href=".get_permalink($post->ID)."><strong>".$post->post_title."</strong></a>? <br/>
		<a href='javascript:void(0)' class='addtofav b_review attending' '".$data_reveal_id."' onclick='javascript:addTo_AttendEvent(".$post_id.",\"add\",\"".$st_date."\",\"".$end_date."\")'>".ATTEND_EVENT_TEXT." "."</a>
		<span id='attend_persons_$post_id-$st_date' class='attended_persons'>".attend_recurring_event_persons($post->ID,$st_date,$end_date)."</span>
		</span>
		</span>";
		}
	} 
	return $a;
}
?>