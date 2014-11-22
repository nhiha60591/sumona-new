<?php
add_shortcode( 'event-user-attend-list', 'event_user_attend_list' );
function event_user_attend_list(){
	global $wpdb,$templatic_settings,$wp_query,$city_id,$current_cityinfo,$short_code_city_id;
	
	ob_start();
	if ( is_user_logged_in() )
	{
		$user_id=get_current_user_id();
		$posts_per_page=get_option('posts_per_page');
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		remove_action('pre_get_posts','tevolution_author_post');
		add_filter('posts_where', 'event_manager_posts_where');
		$args=array(
				'post_type'   =>CUSTOM_POST_TYPE_EVENT,
				'author'      =>$user_id,
				'post_status' => 'publish',
				'paged'       =>$paged,
				'order_by'    =>'date',
				'order'       => 'DESC'
			);
		add_filter('posts_where','event_user_attending_list'); /* add filter to show events as per event types */
		$post_query = new WP_Query($args);
		$wp_query=$post_query;
		remove_filter('posts_where','event_user_attending_list');	
		
		/* event type link setup start */
			$upcoming = $permalink."?etype=upcoming";
			$current= $permalink."?etype=current";
			$past= $permalink."?etype=past";
		
		$post_type=get_post_type_object( get_post_type());
		if(false===strpos($permalink,'?')){
			$url_glue = '?etype='.$_REQUEST['etype'].'&amp;';
		}else{
			$url_glue = 'etype='.$_REQUEST['etype'].'&amp;';	
		}
		
		$_REQUEST['etype']=!isset($_REQUEST['etype'])?'current':$_REQUEST['etype'];
		
		$upcoming_active=(isset($_REQUEST['etype']) && $_REQUEST['etype'] =='upcoming')?'active':'';
		$current_active=(isset($_REQUEST['etype']) && $_REQUEST['etype'] =='current')?'active':'';
		$past_active=(isset($_REQUEST['etype']) && $_REQUEST['etype'] =='past')?'active':'';		
		
		/* event type link set-up end */
		
		?>
          <div id="loop_event_atteding_list" class="list">
			<ul class="tabs">
				<li class="tab-title <?php echo $past_active;?>" role="presentational"><a href="<?php echo $past;?>" role="tab" tabindex="1" aria-selected="false"><?php _e('Past Events',EDOMAIN);?></a></li>			
				<li class="tab-title <?php echo $current_active;?>" role="presentational"><a href="<?php echo $current; ?>" role="tab" tabindex="2" aria-selected="false"><?php _e('Current Events',EDOMAIN);?></a></li>
				<li class="tab-title <?php echo $upcoming_active;?>" role="presentational"><a href="<?php echo $upcoming; ?>" role="tab" tabindex="2" aria-selected="false"><?php _e('Upcoming Events',EDOMAIN);?></a></li>
			</ul>  	  
        <?php
		if($post_query->have_posts())
		{
			while ($post_query->have_posts()): $post_query->the_post(); ?>
			<div class="post <?php templ_post_class();?>">
               	<?php
				 $post_id = get_the_ID();
				if(get_post_meta($post_id,'_event_id',true)){
					$post_id=get_post_meta($post_id,'_event_id',true);
				}
				$stdate=strtotime(get_post_meta(get_the_ID(),'st_date',true));
				$address=get_post_meta(get_the_ID(),'address',true);
				$listing_timing=get_post_meta(get_the_ID(),'listing_timing',true);
				$phone=get_post_meta(get_the_ID(),'phone',true);
				
				$date_formate=get_option('date_format');
				$time_formate=get_option('time_format');
				$st_date=date_i18n($date_formate,strtotime(get_post_meta(get_the_ID(),'st_date',true)));
				$end_date=date_i18n($date_formate,strtotime(get_post_meta(get_the_ID(),'end_date',true)));
				
				$date=$st_date.' '. __('To',EDOMAIN).' '.$end_date;
				$st_time=date_i18n($time_formate,strtotime(get_post_meta(get_the_ID(),'st_time',true)));
				$end_time=date_i18n($time_formate,strtotime(get_post_meta(get_the_ID(),'end_time',true)));
				
				if ( has_post_thumbnail()):
					echo '<a href="'.get_permalink().'" class="event_img">';					
					the_post_thumbnail('event-listing-image'); 
					echo '</a>';
				else:
					if(function_exists('bdw_get_images_plugin'))
					{
						$post_img = bdw_get_images_plugin($post_id,'event-listing-image');						
						$thumb_img = $post_img[0]['file'];
						$attachment_id = $post_img[0]['id'];
						$attach_data = get_post($attachment_id);
						$img_title = $attach_data->post_title;
						$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
					}
					?>
				    <a href="<?php the_permalink();?>" class="event_img">
				  <?php if($featured){echo '<span class="featured_tag">'.__('Featured',EDOMAIN).'</span>';}?>
				    <?php if($thumb_img):?>
					    <img src="<?php echo $thumb_img; ?>"  alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" />
				    <?php else:?>    
						<img src="<?php echo TEVOLUTION_EVENT_URL; ?>images/noimage-220x150.jpg" alt=""  />
				    <?php endif;?>
				    </a>	
			     <?php endif;   ?>           	
                  	<div class="entry">
                    	<div class="date">
						<?php echo date_i18n("d",$stdate); ?>
                              <span><?php echo date_i18n("M",$stdate); ?></span>
                         </div>
                         <div class="event-title">
                    		<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', EDOMAIN ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                              
                              <?php
						echo '<div class="event_rating">';
						$post_id=get_the_ID();
						$tmpdata = get_option('templatic_settings');
						if(is_archive() && $tmpdata['templatin_rating']=='yes'):?>
							<div class="directory_rating_row"><span class="single_rating"> <?php echo draw_rating_star_plugin(get_post_average_rating($post_id));?> </span></div>
						<?php endif;
						echo '</div>';
						
						echo ($address)? '<p class="address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.$address.'</p>' : '';
						echo ($st_date && $end_date)? '<p class="event_date">'.'&nbsp;<span>'.$date.'</span></p>' : '';		
						echo ($st_time && $end_time)? '<p class="time">'.'&nbsp;<span>'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>' : '';		
						?>
                         </div>
                         <div class="entry-summary">
					<?php the_excerpt(); ?>
                         </div><!-- .entry-summary -->
                        <?php echo get_event_user_category();
						echo '<div class="rev_pin">';
						echo '<ul>';
						$post_id=get_the_ID();
						$comment_count= count(get_comments(array('post_id' => $post_id)));
						$review=($comment_count <=1 )? __('review',EDOMAIN):__('reviews',EDOMAIN);
								$review=apply_filters('tev_review_text',$review);
						?>
						  <?php if(current_theme_supports('tevolution_my_favourites') ):?> 
							   <li class="favourite"><?php tevolution_favourite_html();?></li>
						  <?php endif;?>               
						<li class="review"> <?php echo '<a href="'.get_permalink($post_id).'#comments">'.$comment_count.' '.$review.'</a>';?></li>
						<?php
						
						echo '</ul>';
						echo '</div>';
						?>
                    </div>               
			</div>
			<?php endwhile;
			
		}else{
			_e( 'Apologies, no one is attending any events yet!', EDOMAIN );
		}
		
		?>
          </div>
          <div id="listpagi">
               <div class="pagination pagination-position">
                    <?php if(function_exists('pagenavi_plugin')) { pagenavi_plugin(); } ?>
               </div>
          </div>
          <?php
		wp_reset_query();
		wp_reset_postdata();
	}else{
		$login_url=get_tevolution_login_permalink();
		_e('For viewing event attending listing please login for',EDOMAIN);echo ' <a href="'.$login_url.'">';_e('here',EDOMAIN);echo ' </a>';	 
	}
	return ob_get_clean();
}
/*
Function Name : event_user_attending_list
Description: Get the event attended users list
*/
function event_user_attending_list($where){
	register_post_status( 'recurring' );
	//remove_all_actions('posts_where');
	global $wpdb,$current_user,$curauth,$wp_query,$paged;
	$user_id=get_current_user_id();
	$post_ids = get_user_meta($user_id,'user_attend_event',true);	
	$final_ids = '';
	
	$qvar = $wp_query->query_vars;
	$authname = $qvar['author_name'];
	$curauth = get_userdata($qvar['author']);
	$nicename = $current_user->user_nicename;
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		$language = ICL_LANGUAGE_CODE;
		$language_where=" AND t.language_code='".$language."'";
	}
	if($post_ids)
	{
		foreach($post_ids as $key=>$value)
		{
		  if($value != '')
		    {
			 $final_ids .= str_replace('#','',$value).',';
		    }
		}
		$post_ids = substr($final_ids,0,-1);
		$where = " AND ($wpdb->posts.ID in ($post_ids))  ".$language_where;
	}
	

	return $where;
}
/* end */
?>