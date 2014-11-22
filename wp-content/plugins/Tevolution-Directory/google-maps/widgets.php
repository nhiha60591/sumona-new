<?php
/* Widgets - widgets.php */
/*
 * Common widgets for all tevolution add ons
 */
if (!defined('DIR_DOMAIN')) 
	@define( 'DIR_DOMAIN', 'templatic');
add_action('widgets_init','tmpl_plugin_reg_widgets');
function tmpl_plugin_reg_widgets()
{	
	register_widget('directory_neighborhood');	
	register_widget('directory_featured_category_list');
	register_widget('directory_mile_range_widget');
}
/* End of location wise search widget */

/*   
	Name : directory_neighborhood
	Desc: neighborhood posts Widget (particular category) 
*/
class directory_neighborhood extends WP_Widget {
	function directory_neighborhood() {
	//Constructor
		$widget_ops = array('classname' => 'widget In the neighborhood', 'description' => __('Display posts that are in the vicinity of the post that is currently displayed. Use in detail page sidebar areas.',DIR_DOMAIN) );
		$this->WP_Widget('directory_neighborhood', __('T &rarr; In The Neighborhood',DIR_DOMAIN), $widget_ops);
	}
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		global $miles,$wpdb,$post,$single_post,$wp_query,$current_cityinfo;
		global $current_post,$post_number;
 		$current_post = $post->ID;	
		$title = empty($instance['title']) ? __("Nearest Listing",DIR_DOMAIN) : apply_filters('widget_title', $instance['title']);
		$post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
		$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
		$radius = empty($instance['radius']) ? '0' : apply_filters('widget_radius', $instance['radius']);
		$closer_factor = empty($instance['closer_factor']) ? 0 : apply_filters('widget_closer_factor', $instance['closer_factor']);
		$radius_measure= empty($instance['radius_measure']) ? '0' : apply_filters('widget_radius_measure', $instance['radius_measure']);		
		
		
		//get the current post details
		$current_post_details=get_post($post->ID);
		echo $before_widget;
		?>          
		<div class="neighborhood_widget">
		<?php
          echo '<h3 class="widget-title">'.$title.'</h3>';
			$miles=(strtolower($radius_measure) == strtolower('Kilometer'))? $radius / 0.621: $radius;
				
			add_filter('posts_where','directory_nearby_filter');
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				add_filter('posts_where', 'wpml_listing_milewise_search_language');
			}
			$args = array(
				'post__not_in'        => array($current_post) ,
				'post_status'         => 'publish',
				'post_type'           => $post_type,
				'posts_per_page'      => $post_number,				
				'ignore_sticky_posts' => 1,
				'orderby'             => 'rand'
			);
			if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
			{
				add_filter('posts_where', 'location_multicity_where');
			}
			$wp_query_near = new WP_Query($args);			
			if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
			{
				remove_filter('posts_where', 'location_multicity_where');
			}
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				remove_filter('posts_where', 'wpml_listing_milewise_search_language');
			}
			if($wp_query_near->have_posts()):
				echo '<ul class="nearby_distance">';
				while($wp_query_near->have_posts())
				{
					$wp_query_near->the_post();
					echo '<li class="nearby clearfix">';
					
					if ( has_post_thumbnail()){
						$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'tevolution_thumbnail');						
						$post_images=$post_img[0];
					}else{
						$post_img = bdw_get_images_plugin(get_the_ID(),'tevolution_thumbnail');					
						$post_images = $post_img[0]['file'];
					}
					$image=($post_images)?$post_images : TEVOLUTION_DIRECTORY_URL.'images/no-image.png';
					?>
                         <div class='nearby_image'>
                         <a href="<?php echo get_permalink($post->post_id); ?>">
                         	<img src="<?php echo $image?>" alt="<?php echo get_the_title($post->post_id); ?>" title="<?php echo get_the_title($post->post_id); ?>" class="thumb" />
                         </a>
                         </div>
                         <div class='nearby_content'>
                         	<h4><a href="<?php echo get_permalink($post->post_id); ?>"><?php the_title(); ?></a></h4>
						<p class="address"><?php $address = get_post_meta(get_the_ID(),'address',true); echo $address; ?></p>
                         </div>
					<?php
					echo '</li>';
                         
				}
				echo '</ul>';
			else:
          		_e('Sorry! There is no near by results found',DIR_DOMAIN);
			endif;
			remove_filter('posts_where','nearby_filter'); 
			wp_reset_query();
		?>         
        </div>
		<?php
		echo $after_widget;
	}
	function update($new_instance, $old_instance) {
		//save the widget		
		return $new_instance;
	}
	function form($instance) {
		//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => __("Nearest Listing",DIR_DOMAIN), 'post_type' => 'listing', 'post_number' => 5, 'closer_factor'=>2 ) );
			$title = strip_tags($instance['title']);
			$post_type = strip_tags($instance['post_type']);
			$post_number = strip_tags($instance['post_number']);
			$post_link = strip_tags($instance['post_link']);
			$closer_factor = strip_tags($instance['closer_factor']);
			
			$distance_factor = strip_tags($instance['radius']);
			$radius_measure=strip_tags($instance['radius_measure']);
		?>
          <script type="text/javascript">										
			function select_show_list(id,div_def,div_custom)
			{
				var checked=id.checked;
				jQuery('#'+div_def).slideToggle('slow');
				jQuery('#'+div_custom).slideToggle('slow');
			}			
		</script>
          <p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>
               <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
               </label>
          </p>
          <p>
               <label for="<?php echo $this->get_field_id('post_type');?>" ><?php echo __('Select Post:',DIR_DOMAIN);?>     </label>	
               <select  id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" class="widefat">        	
				<?php
                    $all_post_types = get_option("templatic_custom_post");
                    foreach($all_post_types as $key=>$post_types){
					?>
					<option value="<?php echo $key;?>" <?php if($key== $post_type)echo "selected";?>><?php echo esc_attr($post_types['label']);?></option>
					<?php
                    }
                    ?>	
               </select>
          </p>
          
          <p>
               <label for="<?php echo $this->get_field_id('post_number'); ?>"><?php echo __('Number of posts',DIR_DOMAIN);?>
               <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
               </label>
          </p> 
       
			<p>            
			<label for="<?php echo $this->get_field_id('radius'); ?>"><?php echo __('Select Distance',DIR_DOMAIN);?>
			<select id="<?php echo $this->get_field_id('radius'); ?>" name="<?php echo $this->get_field_name('radius'); ?>">
				<option value="1" <?php if(esc_attr($distance_factor)=='1'){ echo 'selected="selected"';} ?>><?php echo __('1',DIR_DOMAIN); ?></option>
				<option value="5" <?php if(esc_attr($distance_factor)=='5'){ echo 'selected="selected"';} ?>><?php echo __('5',DIR_DOMAIN); ?></option>
				<option value="10" <?php if(esc_attr($distance_factor)=='10'){ echo 'selected="selected"';} ?>><?php echo __('10',DIR_DOMAIN); ?></option>
				<option value="100" <?php if(esc_attr($distance_factor)=='100'){ echo 'selected="selected"';} ?>><?php echo __('100',DIR_DOMAIN); ?></option>
				<option value="1000" <?php if(esc_attr($distance_factor)=='1000'){ echo 'selected="selected"';} ?>><?php echo __('1000',DIR_DOMAIN); ?></option>
				<option value="5000" <?php if(esc_attr($distance_factor)=='5000'){ echo 'selected="selected"';} ?>><?php echo __('5000',DIR_DOMAIN); ?></option>      
			</select>
			</label>             
			</p> 
          <p>            
               <label for="<?php echo $this->get_field_id('radius_measure'); ?>"><?php echo __('Display By',DIR_DOMAIN);?>
               <select id="<?php echo $this->get_field_id('radius_measure'); ?>" name="<?php echo $this->get_field_name('radius_measure'); ?>">
                    <option value="kilometer" <?php if(esc_attr($radius_measure)=='kilometer'){ echo 'selected="selected"';} ?>><?php echo __('Kilometers',DIR_DOMAIN); ?></option>
                    <option value="miles" <?php if(esc_attr($radius_measure)=='miles'){ echo 'selected="selected"';} ?>><?php echo __('Miles',DIR_DOMAIN); ?></option>                    
               </select>
               </label>             
		</p> 
		<?php
	}
}
/* End of directory_neighborhood*/

if(!function_exists('directory_content_limit')){
	function directory_content_limit($max_char, $more_link_text = '', $stripteaser = true, $more_file = '') {	
		global $post;	
		
		$content = get_the_content();
		$content = strip_tags($content);
		$content = substr($content, 0, $max_char);
		$content = substr($content, 0, strrpos($content, " "));
		$more_link_text='<a href="'.get_permalink().'">'.$more_link_text.'</a>';
		$content = $content." ".$more_link_text;
		echo $content;	
	}
}
/*
 * Class Name: directory_featured_category_list
 * Return: display all the category list on home page
 */
class directory_featured_category_list extends WP_Widget {
		function directory_featured_category_list() {
		//Constructor
			$widget_ops = array('classname' => 'all_category_list_widget', 'description' => __('Shows a list of all categories and their sub-categories. Works best in main content and subsidiary areas.','templatic-admin') );		
			$this->WP_Widget('directory_featured_category_list', __('T &rarr; All Categories List','templatic-admin'), $widget_ops);
		}
		function widget($args, $instance) 
		{
		// prints the widget
			global $current_cityinfo;
			extract($args, EXTR_SKIP);
			$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
			$category_level = empty($instance['category_level']) ? '1' : apply_filters('widget_category_level', $instance['category_level']);
			$number_of_category = ($instance['number_of_category'] =='') ? '6' : apply_filters('widget_number_of_category', $instance['number_of_category']);
			$hide_empty_cat = ($instance['hide_empty_cat'] =='') ? '0' : apply_filters('widget_hide_empty_cat', $instance['hide_empty_cat']);
			
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));
			
			$args5=array(
					'orderby'    => 'name',
					'taxonomy'   => $taxonomies[0],
					'order'      => 'ASC',
					'parent'     => '0',
					'show_count' => 0,
					'hide_empty' => 0,
					'pad_counts' => true,					
				);
			
			echo $before_widget;
			
			/* set wp_categories on transient */
			if (get_option('tevolution_cache_disable')==1  && false === ( $categories = get_transient( '_tevolution_query_catwidget'.$post_type.$cur_lang_code) )) {
				$categories=get_categories($args5);
				set_transient( '_tevolution_query_catwidget'.$post_type.$cur_lang_code, $categories, 12 * HOUR_IN_SECONDS );
			}elseif(get_option('tevolution_cache_disable')==''){
				$categories=get_categories($args5);
			}
			 
			if($title){echo '<h3 class="widget-title">'.$title.'</h3>'; } ?>
			<section class="category_list_wrap row">
            <?php 
			if(!isset($categories['errors'])){
				foreach($categories as $category) 
				{	
					/* set child wp_categories on transient */
					
					$transient_name=(!empty($current_cityinfo))? $current_cityinfo['city_slug']: '';					
					if (get_option('tevolution_cache_disable')==1  && false === ( $featured_catlist_list = get_transient( '_tevolution_query_catwidget'.$category->term_id.$post_type.$transient_name.$cur_lang_code) ) ) { 
						do_action('tevolution_category_query');						
						$featured_catlist_list =  wp_list_categories('title_li=&child_of=' . $category->term_id .'&echo=0&depth='.$category_level.'&number='.$number_of_category.'&taxonomy='.$taxonomies[0].'&show_count=1&hide_empty='.$hide_empty_cat.'&pad_counts=0&show_option_none=');
						set_transient( '_tevolution_query_catwidget'.$category->term_id.$post_type.$transient_name.$cur_lang_code, $featured_catlist_list, 12 * HOUR_IN_SECONDS );				
					}elseif(get_option('tevolution_cache_disable')==''){
						do_action('tevolution_category_query');
						$featured_catlist_list =  wp_list_categories('title_li=&child_of=' . $category->term_id .'&echo=0&depth='.$category_level.'&number='.$number_of_category.'&taxonomy='.$taxonomies[0].'&show_count=1&hide_empty='.$hide_empty_cat.'&pad_counts=0&show_option_none=');
					}
					if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
					{
						remove_filter( 'terms_clauses','locationwise_change_category_query',10,3 );	
					}
					$parent = get_term( $category->term_id, $taxonomies[0] );
					if($hide_empty_cat ==1 ){
					if($parent->count !=0 || $featured_catlist_list != ""){
					?>	
                        <article class="category_list large-4 medium-4 small-6 xsmall-12 columns">
							<?php 
							if($parent){
									$parents = '<a href="' . get_term_link( $parent, $taxonomies[0] ) . '" title="' . esc_attr( $parent->name ) . '">' . apply_filters( 'list_cats',$parent->name ,$parent). '</a>';
									if($hide_empty_cat == 1){
										if($parent->count !=0){ ?>
										<h3><?php do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; ?></h3>                         
									<?php } 
									}else{ ?>
										<h3><?php do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; ?></h3>                         
									<?php }

								if( @$featured_catlist_list != "" ){
									if($number_of_category !=0){ 
									if($parent->count ==0){
										?>
										<h3><?php do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; ?></h3>
											<?php  } ?>
										<ul>
											<?php echo $featured_catlist_list; ?>
											<li class="view">
												<a href="<?php echo get_term_link($parent, $taxonomies[0]);?>">
													<?php _e('View all &raquo;',DIR_DOMAIN)?>
												</a> 
											</li>                                        
										</ul>
						<?php 	
									}
								}
								}
						?>
                         </article>   
					<?php }
				 }else{ ?>
					<article class="category_list large-4 medium-4 small-6 xsmall-12 columns">
							<?php 
							if($parent && $taxonomies[0] ){
									$parents = '<a href="' . get_term_link( $parent, $taxonomies[0] ) . '" title="' . esc_attr( $parent->name ) . '">' . apply_filters( 'list_cats',$parent->name ,$parent) . '</a>';
									?>
										<h3><?php 
													include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
													if(is_plugin_active('Tevolution-CategoryIcon/tevolution-categoryicon.php'))
													{
														if(@$parent->category_icon=='')
														{
															do_action('show_categoty_map_icon',$term_icon);
														}
													}
													else
													{
														do_action('show_categoty_map_icon',$parent->term_icon);
													}echo $parents; ?></h3>                         
								<?php

								if( @$featured_catlist_list != "" ){
									if($number_of_category !=0){ ?>
										<ul>
											<?php echo $featured_catlist_list; ?>
											<li class="view">
												<a href="<?php echo get_term_link($parent, $taxonomies[0]);?>">
													<?php _e('View all &raquo;',DIR_DOMAIN)?>
												</a> 
											</li>                                        
										</ul>
						<?php 	
									}
								}
								}
						?>
                    </article>   
				<?php }
				}
			}else{
				echo '<p>'. __('Invalid Category.',DIR_DOMAIN).'</p>';
			} ?>
             </section>
             <?php echo $after_widget;
		}
		function update($new_instance, $old_instance) {
			//save the widget	
			global $wpdb;
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name like '%s'",'%_tevolution_query_catwidget%' ));
			return $new_instance;
		}
		function form($instance) {
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'category_level' => '1','number_of_category' => '5') );		
			$title = strip_tags($instance['title']);
			$my_post_type = ($instance['post_type']) ? $instance['post_type'] : 'listing';
			$category_level = ($instance['category_level']);
			$number_of_category = ($instance['number_of_category']);
			$hide_empty_cat = ($instance['hide_empty_cat']);
			?>
               <p>
                    <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:','templatic-admin');?> 
                         <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
                    </label>
               </p>
				<p>
               	<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php echo __('Post Type:','templatic-admin')?>
                    <select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
                          <?php
						$all_post_types = apply_filters('tmpl_allow_fields_posttype',get_option("templatic_custom_post"));
						foreach($all_post_types as $key=>$post_type){?>
							<option value="<?php echo $key;?>" <?php if($key== $my_post_type)echo "selected";?>><?php echo esc_attr($post_type['label']);?></option>
					<?php }?>	
                    </select>
                    </label>
               </p> 
				<p>
                    <label for="<?php  echo $this->get_field_id('category_level'); ?>"><?php echo __('Category Level','templatic-admin');?>: 
                         <select id="<?php  echo $this->get_field_id('category_level'); ?>" name="<?php echo $this->get_field_name('category_level'); ?>">
                         <?php
                         for($i=1;$i<=10;$i++)
                         {?>
                         	<option value="<?php echo $i;?>" <?php if(esc_attr($category_level)==$i){?> selected="selected" <?php } ?>><?php echo $i;?></option>
                         <?php
                         }?>
                         </select>
                    </label>
               </p> 
			 <p>
               	<label for="<?php  echo $this->get_field_id('number_of_category'); ?>"><?php echo __('Number of child categories','templatic-admin');?>: <input class="widefat" id="<?php  echo $this->get_field_id('number_of_category'); ?>" name="<?php echo $this->get_field_name('number_of_category'); ?>" type="text" value="<?php echo esc_attr($number_of_category); ?>" />
                    </label>
               </p> 
               <?php if(!is_plugin_active('Tevolution-LocationManager/location-manager.php')){ ?>   
			   	<p>
               		<label for="<?php  echo $this->get_field_id('hide_empty_cat'); ?>"><input class="widefat" id="<?php  echo $this->get_field_id('hide_empty_cat'); ?>" name="<?php echo $this->get_field_name('hide_empty_cat'); ?>" type="checkbox" value="1" <?php if(@$hide_empty_cat ==1){ echo "checked=checked"; }?>/>
                	<?php echo __('Hide empty categories','templatic-admin');?></label>
                </p>
               <?php } ?>
		
		<?php
		}
}
/*
directory_mile_range_widget : Miles wise searching widget 
*/
class directory_mile_range_widget extends WP_Widget {
	function directory_mile_range_widget() {
		//Constructor
		$widget_ops = array('classname' => 'search_miles_range', 'description' => __('Search through nearby posts by setting a range. Use in category page sidebar areas.','templatic-admin') );
		$this->WP_Widget('directory_mile_range_widget', __('T &rarr; Search by Miles Range','templatic-admin'), $widget_ops);
	}
	function widget($args, $instance) {
		// prints the widget
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? 'Search Near By Miles Range' : apply_filters('widget_title', $instance['title']);		
		//$post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
		$post_type= get_post_type();
		$miles_search = empty($instance['miles_search']) ? '' : apply_filters('widget_miles_search', $instance['miles_search']);
		$max_range = empty($instance['max_range']) ? '' : apply_filters('widget_max_range', $instance['max_range']);
		$radius_measure = empty($instance['radius_measure']) ? 'miles' : apply_filters('widget_radius_measure', $instance['radius_measure']);
		echo $before_widget;
		$search_txt=sprintf(__('Find a %s',DIR_DOMAIN),$post_type);
		wp_enqueue_script("jquery-ui-slider");
		echo '<div class="search_nearby_widget">';
		if($title){echo '<h3 class="widget-title">'.$title.'</h3>';}
		global $wpdb,$wp_query;		
		
		if(is_tax()){
			$list_id='loop_'.$post_type.'_taxonomy';
			$page_type='taxonomy';
		}else{
			$list_id='loop_'.$post_type.'_archive';
			$page_type='archive';
		}
		
		
		$queried_object = get_queried_object();
		$term_id = $queried_object->term_id;
		$query_string='&term_id='.$term_id;
		
		?>
		<form method="get" id="searchform" action="<?php echo home_url(); ?>/">
          	<input type="hidden"  class="miles_range_post_type" name="post_type" value="<?php echo $post_type;?>" />
            <div class="search_range">
            	<input type="text" name="range_address" id="range_address" value="" class="range_address location placeholder" placeholder="<?php _e('Enter your address',DOMAIN);?>"/>
            <?php if($radius_measure=="miles"):?>
                <label><?php _e('Mile range:',DIR_DOMAIN); ?></label>
            <?php else:?>
            	<label><?php _e('Kilometer range:',DIR_DOMAIN); ?></label>
            <?php endif;?>            	
                <input type="text" name="radius" id="radius_range" value="<?php echo $max_range; ?>" style="border:0; font-weight:bold;"  readonly="readonly"/>
            </div>              
            <div id="radius-range"></div>
              <script type="text/javascript">
			  var slider_ajax=map_ajax=null;
				jQuery('#radius-range').bind('slidestop', function(event, ui) {				
				var miles_range=jQuery('#radius_range').val();
				var range_address=jQuery('#range_address').val();
				var list_id='<?php echo $list_id?>';	
				jQuery('.'+list_id+'_process').remove();
				jQuery('#'+list_id ).addClass( "loading_results" );
				<?php
				if(isset($_SERVER['QUERY_STRING'])){
					$query_string.='&'.$_SERVER['QUERY_STRING'];
				}
				?>				
				slider_ajax = jQuery.ajax({
					url:ajaxUrl,
					type:'POST',
					beforeSend : function(){
							if(slider_ajax != null){
								slider_ajax.abort();
							}
						},
					data:'action=<?php echo $post_type."_search";?>&posttype=<?php echo $post_type;?>&range_address='+range_address+'&miles_range='+miles_range+'&defaul_range=<?php echo '1-'.$max_range;?>&page_type=<?php echo $page_type.$query_string;?>&radius_measure=<?php echo $radius_measure;?>',
					success:function(results){
						jQuery('.'+list_id+'_process').remove();
						jQuery('#'+list_id).html(results);
						jQuery('#listpagi').remove();
						jQuery('#'+list_id ).removeClass( "loading_results" );
					}
				});
				
				map_ajax=jQuery.ajax({
					url:ajaxUrl,
					type:'POST',
					beforeSend : function(){
							if(map_ajax != null){
								map_ajax.abort();
							}
						},
					dataType: 'json',
					data:'action=<?php echo $post_type."_search_map";?>&posttype=<?php echo $post_type;?>&range_address='+range_address+'&miles_range='+miles_range+'&defaul_range=<?php echo '1-'.$max_range;?>&page_type=<?php echo $page_type.$query_string;?>&radius_measure=<?php echo $radius_measure;?>',
					success:function(results){
						googlemaplisting_deleteMarkers();
						markers=results.markers;
						templ_add_googlemap_markers(markers);
					}
				});
			});
			/* Click event on range address */
			jQuery('#range_address').live('keypress', function(e){				
				if(e.which==13){
					jQuery('#radius-range').trigger('slidestop');
				}
			});
			jQuery(function(){jQuery("#radius-range").slider({range:true,min:1,max:<?php echo $max_range; ?>,values:[1,<?php echo $max_range; ?>],slide:function(e,t){jQuery("#radius_range").val(t.values[0]+" - "+t.values[1])}});jQuery("#radius_range").val(jQuery("#radius-range").slider("values",0)+" - "+jQuery("#radius-range").slider("values",1))})
		    </script>
            
          </form>		
		<?php
		echo '</div>';
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		return $new_instance;
	}
	function form($instance) {
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Search Nearby Miles Range', 'max_range' => 500, 'post_type' => 'listing' ) );		
		$title = strip_tags(@$instance['title']);
		$post_type = strip_tags(@$instance['post_type']);
		$max_range = strip_tags(@$instance['max_range']);
		$miles_search=strip_tags(@$instance['miles_search']);
		$radius_measure=strip_tags(@$instance['radius_measure']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>            
			<label for="<?php echo $this->get_field_id('radius_measure'); ?>"><?php echo __('Search By','templatic-admin');?>
			<select id="<?php echo $this->get_field_id('radius_measure'); ?>" name="<?php echo $this->get_field_name('radius_measure'); ?>">
				<option value="miles" <?php if(esc_attr($radius_measure)=='miles'){ echo 'selected="selected"';} ?>><?php echo __('Miles','templatic-admin'); ?></option>
				<option value="kilometer" <?php if(esc_attr($radius_measure)=='kilometer'){ echo 'selected="selected"';} ?>><?php echo __('Kilometers','templatic-admin'); ?></option>
			</select>
			</label>             
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('max_range'); ?>"><?php echo __('Max Range','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('max_range'); ?>" name="<?php echo $this->get_field_name('max_range'); ?>" type="text" value="<?php echo esc_attr($max_range); ?>" />
			</label>
		</p>		
		<?php			
	}
}
/* End directory_mile_range_widget */
/*
	Name : slider_search_option	
	Desc : Add the JS Of sliding search(miles wise searching) in footer
*/
function slider_search_option(){	
	?><script type="text/javascript">	  
		jQuery(function(){jQuery("#radius-range").slider({range:true,min:1,max:500,values:[1,500],slide:function(e,t){jQuery("#radius_range").val(t.values[0]+" - "+t.values[1])}});jQuery("#radius_range").val(jQuery("#radius-range").slider("values",0)+" - "+jQuery("#radius-range").slider("values",1))})
	   </script>
     <?php	
} ?>
