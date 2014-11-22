<?php
/* fetch html for recurring events */
add_action('tmpl_custom_fields_event_type_after','tmpl_get_recurring');
function tmpl_get_recurring($event_type='Recurring event')
{
	global $post;
	$name_of_day = event_get_days_names();
	$hours_format = event_get_hour_format();
	if(isset($_SESSION['custom_fields']) && $_SESSION['custom_fields'] != '' && isset($_REQUEST['backandedit']) && $_REQUEST['backandedit'] != '')
	{
		$event_type = $_SESSION['custom_fields']['event_type'];
		$recurrence_occurs = $_SESSION['custom_fields']['recurrence_occurs'];
		$recurrence_per = $_SESSION['custom_fields']['recurrence_per'];
		$recurrence_onday = $_SESSION['custom_fields']['recurrence_onday'];
		$recurrence_onweekno = $_SESSION['custom_fields']['recurrence_onweekno'];
		$recurrence_days = $_SESSION['custom_fields']['recurrence_days'];
		$recurrence_byday = $_SESSION['custom_fields']['recurrence_byday'];
		$monthly_recurrence_byweekno = $_SESSION['custom_fields']['monthly_recurrence_byweekno'];
	}
	elseif(strstr($_SERVER['REQUEST_URI'],'wp-admin') && isset($_REQUEST['action']) && isset($_GET['post']))
	{
		$event_type = get_post_meta(@$_GET['post'],'event_type',true);
		$recurrence_occurs = get_post_meta(@$_GET['post'],'recurrence_occurs',true);
		$recurrence_per = get_post_meta(@$_GET['post'],'recurrence_per',true);
		$recurrence_onday = get_post_meta(@$_GET['post'],'recurrence_onday',true);
		$recurrence_onweekno = get_post_meta(@$_GET['post'],'recurrence_onweekno',true);
		$recurrence_days = get_post_meta(@$_GET['post'],'recurrence_days',true);
		$monthly_recurrence_byweekno = get_post_meta(@$_GET['post'],'monthly_recurrence_byweekno',true);
		$recurrence_byday = get_post_meta(@$_GET['post'],'recurrence_bydays',true);
		$recurrence_bydays = get_post_meta(@$_GET['post'],'recurrence_byday',true);
	}
	else
	{
		
		$event_type = get_post_meta(@$_REQUEST['pid'],'event_type',true);
		$recurrence_occurs = get_post_meta(@$_REQUEST['pid'],'recurrence_occurs',true);
		$recurrence_per = get_post_meta(@$_REQUEST['pid'],'recurrence_per',true);
		$recurrence_onday = get_post_meta(@$_REQUEST['pid'],'recurrence_onday',true);
		$recurrence_onweekno = get_post_meta(@$_REQUEST['pid'],'recurrence_onweekno',true);
		$recurrence_days = get_post_meta(@$_REQUEST['pid'],'recurrence_days',true);
		$monthly_recurrence_byweekno = get_post_meta(@$_REQUEST['pid'],'monthly_recurrence_byweekno',true);
		$recurrence_byday = get_post_meta(@$_REQUEST['pid'],'recurrence_bydays',true);
	}
?>
	<div class="form_row clearfix" id="recurring_event" <?php if(trim(strtolower($event_type)) == trim(strtolower('Recurring event'))){  ?>style="display:inline-block;" <?php }else{ ?> style="display:none;zoom:1" <?php } ?>>
	
	
		 <div class="event_repeat clearfix">
			 <div class="form_row clearfix">
			 <label><?php _e('Event will repeat',EDOMAIN); ?></label>
			 <select id="recurrence-occurs" name="recurrence_occurs">
				<?php
					$rec_options = array ("daily" => __ ( 'Daily', EDOMAIN ), "weekly" => __ ( 'Weekly', EDOMAIN ), "monthly" => __ ( 'Monthly', EDOMAIN ), 'yearly' => __('Yearly',EDOMAIN) );
					event_rec_option_items ( $rec_options,$recurrence_occurs); 
				echo @$recurrence_occurs; ?>
			</select>
			
            </div>
            
            
			<label><?php _e ( 'every', EDOMAIN )?></label>
			<input type="text" id="recurrence-per" name='recurrence_per' size='2' value='<?php echo $recurrence_per ; ?>'/>
			<span id="rec-ocr-error" class="error" style="display:none;"><?php _e('It will be better to select regular event for single day.',EDOMAIN); ?></span>
			<span class='rec-span' id="recurrence-perday" <?php if((@$recurrence_occurs =='daily' && @$recurrence_per == 1) || !$recurrence_occurs){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'day', EDOMAIN )?></span>
			<span class='rec-span' id="recurrence-perdays" <?php  if(@$recurrence_occurs =='daily' && @$recurrence_per > 1){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'days', EDOMAIN ) ?></span>
			
			<span class='rec-span' id="recurrence-perweek" <?php if(@$recurrence_occurs =='weekly' && @$recurrence_per == 1){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'week on', EDOMAIN); ?></span>
			<span class='rec-span' id="recurrence-perweeks" <?php if(@$recurrence_occurs =='weekly' && @$recurrence_per > 1){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'weeks on', EDOMAIN); ?></span>
			<span class='rec-span' id="recurrence-permonth" <?php if(@$recurrence_occurs =='monthly' && @$recurrence_per == 1){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'month on the', EDOMAIN )?></span>
			<span class='rec-span' id="recurrence-permonths" <?php if(@$recurrence_occurs =='monthly' && @$recurrence_per > 1){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'months on the', EDOMAIN )?></span>
			
			<span class='rec-span' id="recurrence-peryear" <?php if(@$recurrence_occurs =='yearly' && @$recurrence_per == 1){   ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'year', EDOMAIN )?></span> 
			<span class='rec-span' id="recurrence-peryears" <?php if(@$recurrence_occurs =='yearly' && @$recurrence_per > 1){   ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>><?php _e ( 'years', EDOMAIN ) ?></span>
               <span id="recurrence_per_error" class="message_error2"></span>
		 </div>
						 
		 <div class="form_weekly_days clearfix" id="weekly-days" <?php  if(@$recurrence_occurs =='weekly' || $recurrence_occurs =='weekly'){  ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>>
			<?php
				$saved_bydays =  explode ( ",", $recurrence_byday ); 
				
				event_checkbox_items ( 'recurrence_bydays[]', $name_of_day, $saved_bydays ); 
			?>
		 </div>
		<div class="monthly_opt_container" id="monthly_opt_container" <?php if($recurrence_occurs =='monthly'){ ?>style="display:inline-block;"<?php }else{ ?>style="display:none;"<?php } ?>>
			<select id="monthly-modifier" name="monthly_recurrence_byweekno">
				<?php
					$weeks_options = array ("1" => __ ( 'first', EDOMAIN ), '2' => __ ( 'second', EDOMAIN ), '3' => __ ( 'third', EDOMAIN ), '4' => __ ( 'fourth', EDOMAIN ), '-1' => __ ( 'last', EDOMAIN ) ); 
					event_rec_option_items ( $weeks_options, $monthly_recurrence_byweekno  ); 
				?>
			</select>
			<select id="recurrence-weekday" name="recurrence_byday">
				<?php event_rec_option_items ( $name_of_day, $recurrence_bydays  ); ?>
			</select>
			<?php _e('of each month',EDOMAIN); ?>
			&nbsp;
		</div>
		
        <div class="form_last_days form_row clearfix">
			<label><?php _e('Each event ends after ',EDOMAIN); ?></label>
			<input id="end_days" type="text"  maxlength="8" name="recurrence_days" value="<?php echo $recurrence_days; ?>" />
			<?php _e('day(s)',EDOMAIN); ?>
               <span id="recurrence_days_error" class="message_error2"></span>
		</div>
		<?php global $pagenow; 
		if($pagenow =='post.php' || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'renew') ):
	?>
		<p><span style="color:red;font-weight:bold;"><?php _e('Please note',EDOMAIN);  ?>: </span> <?php _e('Updating these recurring properties will generate new URLs for instances of this recurring event. When that happens external links to those instances will stop working.',EDOMAIN); ?></p>
	<?php endif; ?>
		<?php 
		global $pagenow;
		if($pagenow =='post-new.php' || !isset($_REQUEST['action'])): ?>
		<div style="color:green;"><?php _e('Each occurrence of this recurring event will be created as a separate event.',EDOMAIN); ?></div>
		<?php endif; 
		  if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' || $_REQUEST['pid'] !=''){
					global $post;
					$chk_sel = get_post_meta($post->ID,'allow_to_create_rec',true);
					if($chk_sel == 'yes'){ $checked = "checked=checked";  }else{ $checked = ""; }
				
		?>
		
			
		<?php } ?>
	</div>
<?php
}
?>