<?php
/** Ad Widget: start */
if(!class_exists('tmpl_ad_widget')){
	class tmpl_ad_widget extends WP_Widget {
		function tmpl_ad_widget() {
		/* Constructor */
			$widget_ops = array('classname' => 'widget tmpl_ad_widget', 'description' => __('Display an ad you created using the Ad Manager add-on.',PLUGIN_DOMAIN) );		
			$this->WP_Widget('tmpl_ad_widget', __('T &rarr; Display Ad',PLUGIN_DOMAIN), $widget_ops);
		}
		function widget($args, $instance) {
			/* prints the widget */
			extract($args, EXTR_SKIP);		
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']); 					
			$ad_todisplay = empty($instance['ad_todisplay']) ? '' : apply_filters('widget_ad_todisplay', $instance['ad_todisplay']); 					
			
			
			
			echo $before_widget;
			
			if(!empty($ad_todisplay)):
			
			if(is_single())
			{
				global $post,$wpdb;
				$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
				$taxonomies = $taxonomies[0];
				$mycustomcategories = get_the_terms( $post->ID, $taxonomies );
				foreach( $mycustomcategories as $term ){
					$categories  .= $term->term_ad_ids.",";
				}
				/* ad id in that category */
				$categories = explode(",",$categories);
				$post_ad_ids = array_values(array_filter($categories));
				
				/* show only that ad which is selected from widget */
				$ad_todisplay = array_values(array_intersect($post_ad_ids, $ad_todisplay));
			}
			
			/* Get necessary details for displaying advertisement */
			global $wpdb,$wp_query,$post;
			//fetch the current page taxonomy
			if(!empty($ad_todisplay)):
			 	$rand_ad_id = array_rand($ad_todisplay,1);
			else:
				$rand_ad_id = '';
			endif;
			$post_ad = $ad_todisplay[$rand_ad_id];
			$post_ad_id = $ad_todisplay[$rand_ad_id];
			$location_post_type = '';
			$location_post_type = ','.implode(',',get_option('location_post_type'));
			remove_all_actions('posts_where');
			if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && strpos($location_post_type,',admanager') !== false)
			{
				$args1 = array('post__in'=>array($post_ad_id),'post_type'=>'admanager','orderby'=>'rand');
			}
			else
			{
				$args1 = array('post__in'=>array($post_ad_id),'post_type'=>'admanager','orderby'=>'rand','suppress_filters' => true);
				
			}
			global $wp_query;
			
			remove_all_actions('posts_where');
			add_filter('posts_where', 'location_multicity_where');
			$post_data  = new WP_Query( $args1 );
			remove_filter('posts_where', 'location_multicity_where');
			$add_post_id = '';
			foreach($post_data as $add_data)
			{
				if(get_post_meta($add_data->ID, 'ad_type', true) != '')
				{
					$post_admanager_ids = $add_data->ID;
					break;
				}
			}
			$post_ad_type = @get_post_meta($post_admanager_ids, 'ad_type', true);
			$post_ad_html = @get_post_meta($post_admanager_ids, 'ad_html_code', true);
			$ad_image_url = @get_post_meta($post_admanager_ids, 'ad_image_url', true);
			$ad_image_title = @get_post_meta($post_admanager_ids, 'ad_image_title', true);
			$ad_image_link_url = @get_post_meta($post_admanager_ids, 'ad_image_link_url', true);
			if($ad_image_link_url == '')
			{
				$ad_image_link_url = 'javascript:void(0);';
			}
			else
			{
				$target = 'target="_blank"';
			}
			if( @get_post_meta($post_admanager_ids, 'ad_height_width', true)){
				$ad_height_width = explode("x",get_post_meta($post_admanager_ids, 'ad_height_width', true));
			}
			$width = @$ad_height_width[0];
			$height = @$ad_height_width[1];
			$data = '';
			
			if(is_category() || is_tax())
			{
				$current_term = $wp_query->get_queried_object();
				if(in_array($post_admanager_ids,explode(",",$current_term->term_ad_ids)))
				{
					if ( $title <> "" ) { 
						echo $before_title;
						echo $title;
						echo $after_title;
					}
					
					if( "image" == $post_ad_type){ /* If image type display image ad */
						if( $ad_image_url !="" ){
							$data = '<div class="plugin_ad" style="width:'.$width.'px;height:'.$height.'px">';
							$data_img = '<img src="'.$ad_image_url.'" title="'.$ad_image_title.'"  style="width:'.$width.'px;height:'.$height.'px" />';
							$data .= '<a onclick="return redirect_image_link('.$post_admanager_ids.')" class="ad ad-'.sanitize_title($ad_image_title).'" href="'.$ad_image_link_url.'" '.$target.' title="'.$ad_image_title.'">'.$data_img.'</a>';
							$data .= '</div>';
							echo $data;
						}
					}else{  /* If image type display html ad */
						if( $post_ad_html !="" ){
							$data = '<div class="plugin_ad">';
							$data .= $post_ad_html;
							$data .= '</div>';
							echo $data;
						}
					}
				}
			}
			elseif(is_single())
			{

				if(!empty($post_admanager_ids))
				{
					if ( $title <> "" ) { 
						echo $before_title;
						echo $title;
						echo $after_title;
					}
					if( "image" == $post_ad_type){ /* If image type disply image ad */
						if( $ad_image_url !="" ){
							$data = '<div class="plugin_ad" style="width:'.$width.'px;height:'.$height.'px">';
							$data_img = '<img src="'.$ad_image_url.'" title="'.$ad_image_title.'"  style="width:'.$width.'px;height:'.$height.'px" />';
							$data .= '<a onclick="return redirect_image_link('.$post_admanager_ids.')" class="ad ad-'.sanitize_title($ad_image_title).'" href="'.$ad_image_link_url.'" '.$target.' title="'.$ad_image_title.'">'.$data_img.'</a>';
							$data .= '</div>';
							echo $data;
						}
					}else{  /* If image type display html ad */
						if( $post_ad_html !="" ){
							$data = '<div class="plugin_ad">';
							$data .= $post_ad_html;
							$data .= '</div>';
							echo $data;
						}
					}
				}
			}
			else
			{
				if ( $title <> "" ) { 
					echo $before_title;
					echo $title;
					echo $after_title;
				}
				
				if( "image" == $post_ad_type){ /* If image type display image ad */
					if( $ad_image_url !="" ){
						$data = '<div class="plugin_ad" style="width:'.$width.'px;height:'.$height.'px">';
						$data_img = '<img src="'.$ad_image_url.'" title="'.$ad_image_title.'"  style="width:'.$width.'px;height:'.$height.'px" />';
						$data .= '<a onclick="return redirect_image_link('.$post_admanager_ids.')" class="ad ad-'.sanitize_title($ad_image_title).'" href="'.$ad_image_link_url.'" '.$target.' title="'.$ad_image_title.'">'.$data_img.'</a>';
						$data .= '</div>';
						echo $data;
					}
				}else{  /* If image type display html ad */
					if( $post_ad_html !="" ){
						$data = '<div class="plugin_ad">';
						$data .= $post_ad_html;
						$data .= '</div>';
						echo $data;
					}
				}
			}
			
			endif;
			/* Display Ad: end */
			echo $after_widget;
		}
		function update($new_instance, $old_instance) {
			/* save the widget */
 			return $new_instance;
		}
		function form($instance) {
		
 		/* widgetform in backend */
		global $sitepress;
		$instance = wp_parse_args( (array)$instance, array('ad_todisplay' => '', 'title' => ''));
		$title = $instance['title'];
		$widgets_ad_ids = $instance['ad_todisplay'];
		/* Get all created advertisements */
		$args=array(
				  'post_type'      => CUSTOM_AD_POST_TYPE,
				  'posts_per_page' => -1,
				  'post_status'	   => 'publish',	
			 );
		remove_all_filters('posts_where');	 
		$ad_query1 = new WP_Query($args);
		?>
			  <p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',PLUGIN_DOMAIN);?>
				   <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
				</label>
			  </p>
			   <p>
                 <label for="<?php echo $this->get_field_id('ad_todisplay'); ?>"><?php echo __('Select Ads which you want to display:',PLUGIN_DOMAIN);?></label>
                    <ul id="users">
					<?php
					if($ad_query1->have_posts()){
						$i =0;
						while ($ad_query1->have_posts()) : $ad_query1->the_post();global $post;
							$chekd = (isset($post->ID) && !empty($widgets_ad_ids) && in_array($post->ID,$widgets_ad_ids)) ? 'checked="checked"' : '';
							echo '<li><label for="'.$this->get_field_id('ad_todisplay').$i.'"><input type="checkbox" name="'.$this->get_field_name('ad_todisplay').'[]" id="'.$this->get_field_id('ad_todisplay').$i.'" '.$chekd.' value="'.$post->ID.'" class="checkbox"/> '.$post->post_title.'</label></li>';
							$i++;
						endwhile;
						wp_reset_query();
					}else{
						echo '<li>'.__("No advertisement created yet.",PLUGIN_DOMAIN).'</li>';
					}	
					?>
					</ul>
               </p>
			   <p><small><?php echo __('Ads will display randomly on page refresh',PLUGIN_DOMAIN);?></small></p>
          <?php
		}
	}
	add_action( 'widgets_init', create_function('', 'return register_widget("tmpl_ad_widget");') );
}
/* Ad Widget: end */
?>
