<?php
/*////////////////////////////
 *
 * calender
 *
/*////////////////////////////
require_once(plugin_dir_path( __FILE__ ).'filter-functions.php' );
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
<h3 class="widget-title"><?php _e('Event Date',SF_DOMAIN); ?></h3>
<input type="hidden" name="tmpl_event_date" filterdisplayname="<?php _e('Event Date',SF_DOMAIN);?>" id="tmpl_event_date" />
<div id="sfevent_cal">
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
					<th title="<?php _e('Monday',SF_DOMAIN); ?>" class="days" ><?php _e('Mon',EDOMAIN);?></th>
					<th title="<?php _e('Tuesday',SF_DOMAIN); ?>" class="days" ><?php _e('Tues',EDOMAIN);?></th>
					<th title="<?php _e('Wednesday',SF_DOMAIN); ?>" class="days" ><?php _e('Wed',EDOMAIN);?></th>
					<th title="<?php _e('Thursday',SF_DOMAIN); ?>" class="days" ><?php _e('Thur',EDOMAIN);?></th>
					<th title="<?php _e('Friday',SF_DOMAIN); ?>" class="days" ><?php _e('Fri',EDOMAIN);?></th>
					<th title="<?php _e('Saturday',SF_DOMAIN); ?>" class="days" ><?php _e('Sat',EDOMAIN);?></th>
					<th title="<?php _e('Sunday',SF_DOMAIN); ?>" class="days" ><?php _e('Sun',EDOMAIN);?></th>
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
				/* $date_hover='onmouseover="date_event(\''.$todaydate.'\',\'calendar_tooltip_'.$urlddate.'\','.$urlddate.')"'; */
			
					echo "<span class=\"no_event\" ><a filterdisplayname='".__('Event Date',SF_DOMAIN)."' todaydate=".$todaydate." href=\"javascript:void(0);\">". ($cal_date) . "</a></span>";
				
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
</div>
<?php add_action('wp_footer','tmpl_wp_footer_calendar'); 

function tmpl_wp_footer_calendar(){ ?>
	<script type="text/javascript">
		jQuery('#sfevent_cal .calendar_widget tbody tr td a').live('click',function(){	
			
			jQuery('#sfevent_cal tr td').find('.active_date').removeClass('active_date');
			jQuery('#searchfilterform #tmpl_event_date').val(jQuery(this).attr('todaydate'));
			clearTimeout(typingTimer);
			typingTimer = setTimeout(doneTyping, doneTypingInterval,'tmpl_event_date');
			jQuery(this).addClass('active_date');
		
		});	
	/*
	 *
	 * function : change_calendar
	 * Description : change the months of claender
	*/	
	function change_calendar(mnth,yr)
	{  	
		  document.getElementById("sfevent_cal").innerHTML="<span class='process-overlay'></span><i class='fa fa-circle-o-notch fa-spin'></i>";
		  url = "<?php echo TEVOLUTION_EVENT_URL; ?>events/calendar/ajax_calendar.php"
		  var data = 'action=tmpl_change_calendar&mnth='+mnth+'&yr='+yr;
		  jQuery.ajax({
			   url: ajaxUrl, 
			   type: 'POST',       
			   data: data,    
			   cache: true,
			   success: function (html) { 
					jQuery('#sfevent_cal').html(html);
			   }      
		  });
	} 
	</script>
<?php } ?>