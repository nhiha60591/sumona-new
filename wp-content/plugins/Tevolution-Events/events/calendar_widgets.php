<?php
/*
* events calendar widget file
*/
function include_widget()
{
/* EVENTS CALENDAR WIDGET STARTS */
	class my_event_calender_widget extends WP_Widget {
		function my_event_calender_widget() {
		/*Default Constructor*/
		$widget_ops = array('classname' => 'event_listing_calendar', 'description' => __('Display a calendar that showcases events available on your site. Events are shown after hovering over a specific date in the calendar. Works best in sidebar areas.',EDOMAIN) );		
		$this->WP_Widget('event_calendar', __('T &rarr; Events Calendar',EDOMAIN), $widget_ops);
		}
		function widget($args, $instance) {
			global $post;
			extract($args, EXTR_SKIP);
			$title = empty($instance['title']) ? __("Event Listing Calendar") : apply_filters('widget_title', $instance['title']);
			
			add_action('wp_footer','event_calendar_widget_script');
			global $current_cityinfo;
	
			echo $before_widget;
			echo ($title)? '<h3 class="widget-title">'.$title.'</h3>':'';
			/*show calendar widget html*/
			get_event_manager_calendar();
			echo $after_widget;
		}
		function update($new_instance, $old_instance) {
			return $new_instance;
		}
		function form($instance) {
			/*widgetform in backend */
			$instance = wp_parse_args( (array) $instance, array( 'title' => '') );		
			$title = (strip_tags($instance['title'])) ? strip_tags($instance['title']) : __("Event Listing Calendar",EDOMAIN);
			?>
			<p>
                    <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title',EDOMAIN); ?>:
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
                    </label>
			</p>
		<?php
		}
	}
	register_widget('my_event_calender_widget');
}
add_action('widgets_init','include_widget');
/*Event Calendar */
if(!function_exists('get_event_manager_calendar'))
{
	function get_event_manager_calendar()
	{	
		/*show static calendar html at the time of page load*/
	    echo '<div class="calendar widget-calendar" id="eventcal" align="center">';
		static_event_calendar();
	    echo '</div>';
	}
}
function get_calendar_month_name($number){	
	$month = date_i18n("M", mktime(0, 0, 0, $number, 10));
	return  $month;
}
function calendar_week_mod_end($num) {
	$base = 7;
	return ($num - $base*floor($num/$base));
}
/*
 * ajax to display event on calendar widget 
 */
add_action('wp_ajax_nopriv_event_calendar','event_calendar_widget');
add_action('wp_ajax_event_calendar','event_calendar_widget');
function event_calendar_widget(){
	global $post,$wpdb,$current_cityinfo;	
	/* display calendar fetching all event */
	$monthNames = Array(__("January",EDOMAIN), __("February",EDOMAIN), __("March",EDOMAIN), __("April",EDOMAIN), __("May",EDOMAIN), __("June",EDOMAIN), __("July",EDOMAIN), __("August",EDOMAIN), __("September",EDOMAIN), __("October",EDOMAIN), __("November",EDOMAIN), __("December",EDOMAIN));
	global $todaydate;
	$cMonth = (isset($_REQUEST['mnth']) && $_REQUEST['mnth']!='')?$_REQUEST["mnth"]: date("n");
	$cYear = (isset($_REQUEST['yr']) && $_REQUEST['yr']!='')?$_REQUEST["yr"]: date("Y");
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	/*fetch the calendar shortcode id to get it work with wpml*/
	$page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts where post_content like '%[calendar_event]%' and post_type='page' and post_status='publish' limit 0,1");
	if(function_exists('icl_object_id')){
		$page_id = icl_object_id($page_id, 'page', false);
	}
	
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	/*set next and prvious month link*/
	$mainlink = $_SERVER['REQUEST_URI'];
	if(strstr($_SERVER['REQUEST_URI'],'?mnth') && strstr($_SERVER['REQUEST_URI'],'&yr'))
	{
		$replacestr = "?mnth=".$_REQUEST['mnth'].'&yr='.$_REQUEST['yr'];
		$mainlink = str_replace($replacestr,'',$mainlink);
	}elseif(strstr($_SERVER['REQUEST_URI'],'&mnth') && strstr($_SERVER['REQUEST_URI'],'&yr'))
	{
		$replacestr = "&mnth=".$_REQUEST['mnth'].'&yr='.$_REQUEST['yr'];
		$mainlink = str_replace($replacestr,'',$mainlink);
	}
	if(strstr($_SERVER['REQUEST_URI'],'?') && (!strstr($_SERVER['REQUEST_URI'],'?mnth')))
	{
		$pre_link = $mainlink."&mnth=". $prev_month . "&yr=" . $prev_year."#event_cal";
		$next_link = $mainlink."&mnth=". $next_month . "&yr=" . $next_year."#event_cal";
	}else
	{
		$pre_link = $mainlink."?mnth=". $prev_month . "&yr=" . $prev_year."#event_cal";	
		$next_link = $mainlink."?mnth=". $next_month . "&yr=" . $next_year."#event_cal";
	}
	?>
	<table id="wp-calendar" width="100%" class="calendar">
		
		<caption><?php echo $monthNames[$cMonth-1].' '.$cYear; ?></caption>
				
		<tr>
		<td style="padding:0px; border:none;">
		<table width="100%" border="0" cellpadding="2" cellspacing="2"  class="calendar_widget" style="padding:0px; margin:0px; border:none;">
		
		<thead>
			<th title="<?php _e('Monday',EDOMAIN); ?>" class="days" ><?php _e('Mon',EDOMAIN);?></th>
			<th title="<?php _e('Tuesday',EDOMAIN); ?>" class="days" ><?php _e('Tues',EDOMAIN);?></th>
			<th title="<?php _e('Wednesday',EDOMAIN); ?>" class="days" ><?php _e('Wed',EDOMAIN);?></th>
			<th title="<?php _e('Thursday',EDOMAIN); ?>" class="days" ><?php _e('Thur',EDOMAIN);?></th>
			<th title="<?php _e('Friday',EDOMAIN); ?>" class="days" ><?php _e('Fri',EDOMAIN);?></th>
			<th title="<?php _e('Saturday',EDOMAIN); ?>" class="days" ><?php _e('Sat',EDOMAIN);?></th>
			<th  title="<?php _e('Sunday',EDOMAIN); ?>" class="days" ><?php _e('Sun',EDOMAIN);?></th>
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
		/*register post status recurring to fetch events recurring post type*/
		register_post_status( 'recurring' );
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

				$urlddate = "$cYear$cMonth_date$calday";				
				$thelink = get_permalink($page_id)."?cal_date=$urlddate";

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
				
				$posts_per_page=get_option('posts_per_page');
				/*query to fetch events to show on calendar widget*/
                $args=
				array( 'post_type' => 'event',
				'posts_per_page' => 5	,
				'post_status' => array('recurring','publish','private'),
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'st_date',
						'value' => $todaydate,
						'compare' => '<=',
						'type' => 'DATE'
					),
					array(
						'key' => 'end_date',
						'value' => $todaydate,
						'compare' => '>=',
						'type' => 'DATE'
					)
				)
				);
					$location_post_type = get_option('location_post_type');
					/*if location manager plugin is activated and events post type is selected in manage location than fetch events city wise*/
					if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && is_array('event',$location_post_type))
					{
						global $cityid;
						$cityid = $_REQUEST['city_id'];
						add_filter('posts_where', 'location_city_filter');
					}
					$my_query1 = null;
					$my_query1 = new WP_Query($args);		

					if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && is_array('event',$location_post_type))
					{
						remove_filter('posts_where', 'location_city_filter');
					}
					$post_info = '';
					global $posts;
					$c =0;
					if( $my_query1->have_posts() )
					{ 
						$post_info .='<span id="calendar_tooltip_'.$urlddate.'" class="calendar_tooltip" '.$style.'><span class="shape"></span>';						
						$post_info .='</span>';
					}
					echo "<td class='date_n' >";
					$date_hover='onmouseover="date_event(\''.$todaydate.'\',\'calendar_tooltip_'.$urlddate.'\','.$urlddate.')"';
					if($my_query1->have_posts())
					{	
						$flg = 0;
						$temp_calendar_date='';
						while ($my_query1->have_posts()) : $my_query1->the_post();
							/* separate out recurring events with regular events */
							$is_recurring = get_post_meta($post->ID,'event_type',true);
							
							if(strtolower(trim($is_recurring)) == strtolower(trim('Regular event'))){
								
								$calendar_date= "<a class=\"more_events\" href=\"$thelink\" >". ($cal_date) . "</a>";
							}else{ 
								$flg=1;							
								$calendar_date="<span class=\"no_event\" >". ($cal_date) . "</span>";
							}						
							
							if($cal_date!=$tmp_date)
							{							
								if($flg==1)						
								{
									$flg=0;
									$p=1;
									$temp_calendar_date=$calendar_date;								
								}
								else	
								{
									$p=0;
									$temp_calendar_date=$calendar_date;
									$tmp_date=$cal_date;
								}
							}	
						endwhile;	
						if($p==1)
							echo $temp_calendar_date;
						else
							echo '<div class="event_calendar_wrap"><div '.$date_hover.'>'.$temp_calendar_date."</div>".$post_info.'</div>';
						
					}else
					{	
							echo "<span class=\"no_event\" >". ($cal_date) . "</span>";
					}
					echo "</td>\n";
			}
			if(($i % 7) == 0 ) echo "</tr>\n";
		}
		$pad = 7 - calendar_week_mod_end(date('w', strtotime($todaydate)));
		if ( $pad != 0 && $pad != 7 )
			echo "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
		?>
		</tr>
		</tbody>
               <tfoot>
               <tr>
                    <td id="prev" colspan="3">
                         <a href="javascript:void(0);" onclick="change_calendar(<?php echo $prev_month; ?>,<?php echo $prev_year; ?>)"> &laquo; <?php echo get_calendar_month_name($prev_month); ?></a>
                    </td>
                    <td class="pad">&nbsp;</td>
                    <td class="pad" id="next" colspan="3">
                         <a href="javascript:void(0);"  onclick="change_calendar(<?php echo $next_month; ?>,<?php echo $next_year; ?>)"> <?php echo get_calendar_month_name($next_month); ?> &raquo;</a>
                    </td>
               </tr>
               </tfoot>
		</table>
		</td>
	</tr>
	</table>
	<?php
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='event_calendar'){
		
		exit;
	}
}
/*
 * display event on calendar widget ajax
 */
add_action('wp_ajax_nopriv_datewise_event','event_calendar_widget_datewise');
add_action('wp_ajax_datewise_event','event_calendar_widget_datewise');
function event_calendar_widget_datewise(){
	global $post,$wpdb,$current_cityinfo;
	$cMonth = (isset($_REQUEST['mnth']) && $_REQUEST['mnth']!='')?$_REQUEST["mnth"]: date("n");
	$cYear = (isset($_REQUEST['yr']) && $_REQUEST['yr']!='')?$_REQUEST["yr"]: date("Y");
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	
	$todaydate=$_REQUEST['date'];
	$urlddate=$_REQUEST['urlddate'];
	$page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts where post_content like '%[calendar_event]%' and post_type='page' and post_status='publish' limit 0,1");
	if(function_exists('icl_object_id')){
		$page_id = icl_object_id($page_id, 'page', false);
	}
	/*set link of calendar as per wpml*/
	$thelink = get_permalink($page_id)."?cal_date=$urlddate";
	/*register post status recurring*/
	register_post_status( 'recurring' );
	/*query to fetch events to show on calendar widget*/
	$posts_per_page=get_option('posts_per_page');
		$args=
		array( 'post_type' => 'event',
			'posts_per_page' => 5,
			'post_status' => array('recurring','publish'),
			'meta_key' => 'st_date',
			'orderby' => 'meta_value',
			'order' => 'ASC',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'st_date',
					'value' => $todaydate,
					'compare' => '<=',
					'type' => 'DATE'
				),
				array(
					'key' => 'end_date',
					'value' => $todaydate,
					'compare' => '>=',
					'type' => 'DATE'
				),
				array(
					'key' => 'event_type',
					'value' => 'Regular event',
					'compare' => '=',
					'type'=> 'text'
				),				
			)
		);
	$location_post_type = get_option('location_post_type');
	if(is_array($location_post_type) && count($location_post_type) >1){
		$location_post_type = implode(',',$location_post_type);
	}else{
		$location_post_type = $location_post_type[0];
	}
	/*if location manager plugin is activated and events post type is selected in manage location than fetch events city wise*/
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && is_array('event',$location_post_type))
	{
		global $cityid;
		$cityid = $_REQUEST['city_id'];
		add_filter('posts_where', 'location_city_filter');
	}
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php')  && strstr($location_post_type,'event,'))
	{
		add_filter('posts_where', 'location_multicity_where');
	}
	
	$my_query1 = null;
	$my_query1 = new WP_Query($args);
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && is_array('event',$location_post_type))
	{
		remove_filter('posts_where', 'location_multicity_where');
	}
	$post_info = '';
	global $posts;
	$c =0;
	if( $my_query1->have_posts() )
	{
		while ($my_query1->have_posts()) : $my_query1->the_post();
					
			/* separate out recurring events with regular events */
			$is_recurring = get_post_meta($post->ID,'event_type',true);
			if(tmpl_is_parent($post)){
				$recurrence_occurs = get_post_meta($post->post_parent,'recurrence_occurs',true);
				$rec_date = templ_recurrence_dates($post->post_parent);
			}else{
				$recurrence_occurs = get_post_meta($post->post_parent,'recurrence_occurs',true);
				$rec_date = templ_recurrence_dates($post->post_parent);
			}							
			if(strstr($rec_date,',')){ $rec_dates = explode(',',$rec_date); }else{ $rec_dates = $rec_date; }
			/*html for recurring event in pop up*/
			if(tmpl_is_parent($post)){ 												
				$post_info .=' 
				<a class="event_title" href="'.get_permalink($post->ID).'">'.$post->post_title.'</a><small>'.
				'<span class="wid_event_list"><b class="label">'.__('Location :',EDOMAIN).'</b><b class="label_info">'.get_post_meta($post->ID,'address',true) .'</b></span>'.
				'<span class="wid_event_list"><b class="label">'.__('Start Date :',EDOMAIN).'</b><b class="label_info">'.get_formated_date(get_post_meta($post->ID,'st_date',true)).' '.get_formated_time(get_post_meta($post->post_parent,'st_time',true)) .'</b></span>'. 
				'<span class="wid_event_list"><b class="label">'.__('End Date :',EDOMAIN).'</b><b class="label_info">'.get_formated_date(get_post_meta($post->ID,'end_date',true)).' '.get_formated_time(get_post_meta($post->post_parent,'end_time',true)) .'</b></span></small>';
			}else{
				/*html for regular event in pop up*/
				if(strtolower($is_recurring) == strtolower('Regular event')){
					$post_info .=' 
					<a class="event_title" href="'.get_permalink($post->ID).'">'.$post->post_title.'</a><small>'.
					'<span class="wid_event_list"><b class="label">'.__('Location :',EDOMAIN).'</b><b class="label_info">'.get_post_meta($post->ID,'address',true) .'</b></span>'.
					'<span class="wid_event_list"><b class="label">'.__('Start Date :',EDOMAIN).'</b><b class="label_info">'.get_formated_date(get_post_meta($post->ID,'st_date',true)).' '.get_formated_time(get_post_meta($post->ID,'st_time',true)) .'</b></span>'. 
					'<span class="wid_event_list"><b class="label">'.__('End Date :',EDOMAIN).'</b><b class="label_info">'.get_formated_date(get_post_meta($post->ID,'end_date',true)).' '.get_formated_time(get_post_meta($post->ID,'end_time',true)) .'</b></span></small>';
				}
			}
			
		endwhile;
		if($my_query1->found_posts>5)
			$post_info .= "<a class=\"more_events\" href=\"$thelink\" >". __('View more',EDOMAIN) . "</a>";
	}else{
		$post_info .=__('No Event in this date',EDOMAIN);
	}
	echo $post_info;
	exit;
}
/*
 * add the event calendar script in wp_footer
 */
function event_calendar_widget_script(){
	global $current_cityinfo;
?>
	<script language='javascript'>
	var city_id = '';
	<?php
	$location_post_type = get_option('location_post_type');	
	/*if location manager plugin is activated and events post type is selected in manage location than get the current city id*/
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && is_array('event',$location_post_type))
	{
		?>
			city_id = '<?php echo $current_cityinfo['city_id']; ?>';
		<?php
	}
	?>
	/*function to show events on calendar while change of month*/
     function change_calendar(mnth,yr)
     {  	
          document.getElementById("eventcal").innerHTML="<i class='fa fa-spinner fa-spin fa-3x'></i>";
          url = "<?php echo TEVOLUTION_EVENT_URL; ?>events/calendar/ajax_calendar.php"
          var data = 'action=event_calendar&city_id='+city_id+'&mnth='+mnth+'&yr='+yr;
          jQuery.ajax({
               url: ajaxUrl, 
               type: 'POST',       
               data: data,    
               cache: false,
               success: function (html) { 
                    jQuery('#eventcal').html(html);
               }      
          });
     } 
	 /*show events on calendar while page load*/
     function show_calendar()
     {  		
          document.getElementById("eventcal").innerHTML="<i class='fa fa-spinner fa-spin fa-3x'></i>";
          url = "<?php echo TEVOLUTION_EVENT_URL; ?>events/calendar/ajax_calendar.php"
          var data = 'action=event_calendar&city_id='+city_id+'&page=' + document.location.hash.replace(/^.*#/, '');
          jQuery.ajax({
               url: ajaxUrl, 
               type: 'POST',       
               data: data,    
               cache: false,
               success: function (html) { 
                    jQuery('#eventcal').html(html);
               }      
          });
     }
	/*show events on calendar while we mouse hover on date of calendar*/
	function date_event(date1,id,urlddate){
		
	     document.getElementById(id).innerHTML="<i class='fa fa-spinner fa-spin fa-3x'></i>";		
          url = "<?php echo TEVOLUTION_EVENT_URL; ?>events/calendar/ajax_calendar.php";
          var data = 'action=datewise_event&city_id='+city_id+'&date=' +date1+'&urlddate='+urlddate;
          jQuery.ajax({
               url: ajaxUrl, 
               type: 'POST',       
               data: data,    
               cache: false,
               success: function (html) { 
                    jQuery('#'+id).html(html);				
               }      
          });
		
	}
	jQuery(window).load( function( response, status ){
    	show_calendar(); 
	});
     </script>
<?php
}
/*
* show static calendar at the time of page load
*/
function static_event_calendar(){
	global $post,$wpdb,$current_cityinfo;	
	/* display calendar fetching all event */
	$monthNames = Array(__("January",EDOMAIN), __("February",EDOMAIN), __("March",EDOMAIN), __("April",EDOMAIN), __("May",EDOMAIN), __("June",EDOMAIN), __("July",EDOMAIN), __("August",EDOMAIN), __("September",EDOMAIN), __("October",EDOMAIN), __("November",EDOMAIN), __("December",EDOMAIN));
	global $todaydate;
	$cMonth = (isset($_REQUEST['mnth']) && $_REQUEST['mnth']!='')?$_REQUEST["mnth"]: date("n");
	$cYear = (isset($_REQUEST['yr']) && $_REQUEST['yr']!='')?$_REQUEST["yr"]: date("Y");
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	/*set link of calendar as per wpml*/
	$page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts where post_content like '%[calendar_event]%' and post_type='page' and post_status='publish' limit 0,1");
	if(function_exists('icl_object_id')){
		$page_id = icl_object_id($page_id, 'page', false);
	}
	
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	/*set month wise link*/
	$mainlink = $_SERVER['REQUEST_URI'];
	if(strstr($_SERVER['REQUEST_URI'],'?mnth') && strstr($_SERVER['REQUEST_URI'],'&yr'))
	{
		$replacestr = "?mnth=".$_REQUEST['mnth'].'&yr='.$_REQUEST['yr'];
		$mainlink = str_replace($replacestr,'',$mainlink);
	}elseif(strstr($_SERVER['REQUEST_URI'],'&mnth') && strstr($_SERVER['REQUEST_URI'],'&yr'))
	{
		$replacestr = "&mnth=".$_REQUEST['mnth'].'&yr='.$_REQUEST['yr'];
		$mainlink = str_replace($replacestr,'',$mainlink);
	}
	if(strstr($_SERVER['REQUEST_URI'],'?') && (!strstr($_SERVER['REQUEST_URI'],'?mnth')))
	{
		$pre_link = $mainlink."&mnth=". $prev_month . "&yr=" . $prev_year."#event_cal";
		$next_link = $mainlink."&mnth=". $next_month . "&yr=" . $next_year."#event_cal";
	}else
	{
		$pre_link = $mainlink."?mnth=". $prev_month . "&yr=" . $prev_year."#event_cal";	
		$next_link = $mainlink."?mnth=". $next_month . "&yr=" . $next_year."#event_cal";
	}
	?>
	<table id="wp-calendar" width="100%" class="calendar">
		
		<caption><?php echo $monthNames[$cMonth-1].' '.$cYear; ?></caption>
				
		<tr>
		<td style="padding:0px; border:none;">
		<table width="100%" border="0" cellpadding="2" cellspacing="2"  class="calendar_widget" style="padding:0px; margin:0px; border:none;">
		
		<thead>
			<th title="<?php _e('Monday',EDOMAIN); ?>" class="days" ><?php _e('Mon',EDOMAIN);?></th>
			<th title="<?php _e('Tuesday',EDOMAIN); ?>" class="days" ><?php _e('Tues',EDOMAIN);?></th>
			<th title="<?php _e('Wednesday',EDOMAIN); ?>" class="days" ><?php _e('Wed',EDOMAIN);?></th>
			<th title="<?php _e('Thursday',EDOMAIN); ?>" class="days" ><?php _e('Thur',EDOMAIN);?></th>
			<th title="<?php _e('Friday',EDOMAIN); ?>" class="days" ><?php _e('Fri',EDOMAIN);?></th>
			<th title="<?php _e('Saturday',EDOMAIN); ?>" class="days" ><?php _e('Sat',EDOMAIN);?></th>
			<th  title="<?php _e('Sunday',EDOMAIN); ?>" class="days" ><?php _e('Sun',EDOMAIN);?></th>
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
		register_post_status( 'recurring' );
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
				$urlddate = "$cYear$cMonth_date$calday";				
				$thelink = get_permalink($page_id)."?cal_date=$urlddate";
				
				$todaydate = "$cYear-$cMonth_date-$the_cal_date";
				$date_num=date('N',strtotime($todaydate))."<br>";
		
					echo "<td class='date_n' >";					
					echo "<span class=\"no_event\" >". ($cal_date) . "</span>";					
					echo "</td>\n";
			}
			if(($i % 7) == 0 ) echo "</tr>\n";
		}
		$pad = 7 - calendar_week_mod_end(date('w', strtotime($todaydate)));
		if ( $pad != 0 && $pad != 7 )
			echo "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
		?>
		</tr>
		</tbody>
               <tfoot>
               <tr>
                    <td id="prev" colspan="3">
                         <a href="javascript:void(0);" onclick="change_calendar(<?php echo $prev_month; ?>,<?php echo $prev_year; ?>)"> &laquo; <?php echo get_calendar_month_name($prev_month); ?></a>
                    </td>
                    <td class="pad">&nbsp;</td>
                    <td class="pad" id="next" colspan="3">
                         <a href="javascript:void(0);"  onclick="change_calendar(<?php echo $next_month; ?>,<?php echo $next_year; ?>)"> <?php echo get_calendar_month_name($next_month); ?> &raquo;</a>
                    </td>
               </tr>
               </tfoot>
		</table>
		</td>
	</tr>
	</table>
	<?php
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='event_calendar'){
		
		exit;
	}
	
}
?>
