<?php
/**
 * Shortcode creation
 **/
add_shortcode('event_attend_user_list', 'event_attend_user_list');
function event_attend_user_list($atts){
	extract( shortcode_atts( array (
				'post_type'   =>CUSTOM_POST_TYPE_EVENT,				
				), $atts ) 
			);
	ob_start();
	global $wpdb,$post,$current_user;
	/* set the submit post type on submit form page */
	if(get_option('event_attending_user_page')==""){		
		update_option('event_attending_user_page',$post->ID);
	}
	if(isset($_REQUEST['eid']) && $_REQUEST['eid']!=""):							
		$event_id=$_REQUEST['eid'];
		$qry_results = $wpdb->get_results("select user_id from $wpdb->usermeta where meta_key LIKE '%user_attend_event%' and meta_value LIKE '%#$event_id#%' ");	
		$date_formate=get_option('date_format');
		$time_formate=get_option('time_format');
		$st_date=date($date_formate,strtotime(get_post_meta($event_id,'st_date',true)));
		$end_date=date($date_formate,strtotime(get_post_meta($event_id,'end_date',true)));
				
		$st_time=date($time_formate,strtotime(get_post_meta($event_id,'st_time',true)));
		$end_time=date($time_formate,strtotime(get_post_meta($event_id,'end_time',true)));
		$time=$st_time.' '.__('To',EDOMAIN).' '.$end_time;
		$address=get_post_meta($event_id,'address',true);
		/*use the content variable to fetch content from the page.*/
		$content_post = get_post($event_id);
		$content = $content_post->post_content;
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		
		
		
		$user_attend ='<h4 class="page-title entry-title"><a href="'.get_permalink($event_id).'" >'.get_the_title($event_id).'</a></h4>';
		$user_attend.= '<br/><strong>'.__('From',EDOMAIN).":</strong> <span>".$st_date."</span><br/><strong>".__('To',EDOMAIN).":</strong> <span>".$end_date."</span>";
		$user_attend.='<br/><strong>'.__('Time:',EDOMAIN).'</strong>&nbsp;'.$time;	
		$user_attend.='<br/><strong>'.__('Address:',EDOMAIN).'</strong>&nbsp;'.$address;	
		//$user_attend .='<div>'.__('People attending this event',EDOMAIN).'</div>';
		$user_attend.='<ul class="attending_user_list">';
		foreach($qry_results as $res)
		{			
			$user = get_userdata($res->user_id);
			$profile_photo=get_user_meta($res->user_id,'profile_photo',true);
			
			$user_attend.='<li>';
			if($profile_photo)
				$user_attend.='<div class="user_gravater"><a href="'.get_bloginfo('url').'/author/' . $user->user_nicename . '"><img src="'.$profile_photo.'" width="100" height="100"></a></div>';
			else
				$user_attend.='<div class="user_gravater"><a href="'.get_bloginfo('url').'/author/' . $user->user_nicename . '">'.str_replace("alt=''",'',get_avatar($user->user_email, '100')).'</a></div>';
			$user_attend.='<div class="user_info"><span><strong>'.__('Name:',EDOMAIN).'</strong> <a href="'.get_author_posts_url($user->ID).'">'.$user->display_name.'</a><br />';
				
			$user_attend.='</div>';
			$user_attend.='</li>';
		
		}
		$user_attend.='</ul>';	
		echo $user_attend;
	else:// request eid if condition
		echo "<div class='error'>"; _e('Sorry, invalid request! Head over to your desired event&acute;s detail page and click the link that says "people attending" to see the total list of people attending that particular event.',EDOMAIN);
		echo "</div>";
	endif;
	return ob_get_clean();
}
?>