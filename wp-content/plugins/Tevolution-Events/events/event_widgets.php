<?php
/* 
 Show the meta box the events in edit listing page - to select the events of that places
*/
class directory_related_event_widget extends WP_Widget {
	function directory_related_event_widget() {

		$widget_ops = array('classname' => 'directory_related_event', 'description' => __('Allows visitors to connect their Listings with Events while editing them in the front-end. Use the widget inside the Primary Sidebar area.',EDOMAIN));
		$this->WP_Widget('directory_related_event_widget', __('T &rarr; Add Related Event',EDOMAIN), $widget_ops);
	}
	function widget($args, $instance) {

		extract($args, EXTR_SKIP);
		$title = apply_filters('widget_title', $instance['title']);
		$description = empty($instance['description']) ? '' : apply_filters('widget_description', $instance['description']);
		
		if((isset($_REQUEST['pid']) && $_REQUEST['pid']!= '') && (isset($_REQUEST['action']) && $_REQUEST['action']== 'edit') && get_post_type($_REQUEST['pid']) == 'listing')
		{
			echo $before_widget;
			$submit_post = get_post($_REQUEST['pid']); 
			global $wpdb,$post;			
			if(is_array($_POST['event_for_listing']))
			{
				$event_for_listing =  implode(',',$_POST['event_for_listing']);
				
				$event_listing = explode(",",get_post_meta($post->ID,'event_for_listing',true));
				
				update_post_meta($_REQUEST['pid'],'event_for_listing',$event_for_listing); // booked tickets
			
				echo "<div class='updated'>".__('Related event saved successfully.',EDOMAIN)."</div>";
			
			}
			global $wpdb,$post;
			?>
			<form action="" name="related_event" id="related_event" method="post">
			<?php
			$post_id = $_REQUEST['pid'];
			
			if($title){echo '<h3 class="widget-title">'.$title.'</h3>';}			
			
			$args = array(				
				'order' => 'ASC',
				'orderby' => 'title',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'post_type' => 'event',
				'author'=> $submit_post->post_author,
			);			
			$get_posts = new WP_Query;
			$get_event = $get_posts->query( $args );
			if(get_post_meta($post_id,'event_for_listing',true)):
				$event_for_listing = explode(',',get_post_meta($post_id,'event_for_listing',true));
			else:
				$event_for_listing = '';
			endif;
			echo "<div>";
			
			if(!empty($get_event)){
			if(empty($event_for_listing)){ $default ='selected=selected'; }else{ $default=''; }
				echo "<select name='event_for_listing[]' id='event_for_listing' multiple='multiple' class='clearfix' style='padding:2px;  width:80%;'>";
					
				echo "<option value='0' $default>".__("Select an events",DIR_DOMAIN)."</option>";
				foreach($get_event as $event_d){
					setup_postdata($event_d);
					if(in_array($event_d->ID,$event_for_listing)){ $selected = 'selected=selected'; }else{ $selected='';}
					echo "<option value='".$event_d->ID."' $selected>".$event_d->post_title."</option>";	
				}
				echo "</select>";
			}else{
				_e('Currently no event created by the owner of this list/place.',DIR_DOMAIN);
			}
			echo "</div>";
			if($description)
			{
				echo '<p class="description">'.$description.'</p>';
			}
			?>
				<input type="submit" alt="" class="normal_button main_btn"  value="<?php _e('Save',DIR_DOMAIN);?>"> 
				</form>
			<?php
			echo $after_widget;
		}
	}
	
	function update($new_instance, $old_instance) {

		return $new_instance;
	}
	function form($instance) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '',''=>'') );		
		$title = strip_tags($instance['title']);
		$description = ($instance['description']); ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('description'); ?>"><?php echo __('Description','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo esc_attr($description); ?>" />
			</label>
		</p>
		<?php			
	}
}
/* End directory_related_event_widget widget */

add_action('widgets_init','event_plugin_widget');
function event_plugin_widget(){
	register_widget('directory_related_event_widget');
	register_widget('event_listing_widget');
}

/* Event listing search widget */

class event_listing_widget extends WP_Widget {
	function event_listing_widget() {
		$widget_ops = array('classname' => 'widget Event Listing', 'description' => __('Listing of current and upcoming events.',EDOMAIN) );		
		$this->WP_Widget('event_listing_widget', __('T &rarr; Events Listing',EDOMAIN), $widget_ops);
	}
	function widget($args, $instance) {

		global $wp_locale;
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? __("Event Listing",EDOMAIN) : apply_filters('widget_title', $instance['title']);
		$view = empty($instance['view']) ? 'list' : apply_filters('widget_view', $instance['view']);
		$category = empty($instance['category']) ? '' : apply_filters('widget_category', $instance['category']);
		$number = empty($instance['number']) ? '5' : apply_filters('widget_number', $instance['number']);
		$sorting = empty($instance['event_sorting']) ? 'Latest Published' : apply_filters('widget_event_sorting', $instance['event_sorting']);
		$my_post_type = 'event';
		global $post,$wpdb;
		$post_widget_count = 1;
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => 'event','public'   => true, '_builtin' => true ));
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			if(@$category!=""){
				foreach($category as $cat){	
					$category_ID =  get_term_by( 'id',$cat,  $taxonomies[0] );	
					$category_id.=$category_ID->term_id.',';
				}
				$category=explode(',',substr($category_id,0,-1));
			}
		}
		/* Check for existing user if user add category slug by comman seprator */
		$field='id';		
		if($category!='' && (!is_array($category) || strstr($category,','))){
			$category=explode(',',$category);
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				if(@$category!=""){
					foreach($category as $cat){						
						$category_ID =  get_term_by( 'slug',$cat,  $taxonomies[0] );						
						$category_slug.=$category_ID->slug.',';
					}
					$category=explode(',',substr($category_slug,0,-1));
				}
			}
			$field='slug';
		}
		
		global $htmlvar_name;
		$heading_type = tmpl_fetch_heading_post_type($my_post_type);
		if(count($heading_type) > 0)
		{
			foreach($heading_type as $key=>$heading)
			{	
				if(function_exists('get_directory_listing_customfields'))
					$htmlvar_name[$key] = get_directory_listing_customfields($my_post_type,$heading,$key);//custom fields for custom post type..
			}
		}
		if ( $sorting == 'Random' )
		{
			
			$orderby = 'rand';
			$order = '';
			$meta_key = '';
		}
		elseif ( $sorting == 'Alphabetical' )
		{
			$orderby = 'title';
			$order = 'ASC';
			$meta_key = '';
		}elseif($sorting =='s_date' || $sorting ==''){
			$meta_key = 'st_date';
			$orderby = 'meta_value';
			$order = 'ASC';
		}
		elseif($sorting =='Latest Published')
		{
			$meta_key = '';
			$orderby = '';
			$order = '';
		}
		if($category!=""){
			$args=array(
					'post_type' => $my_post_type,
					'posts_per_page' => $number,				
					'post_status' => 'publish',				
					'tax_query' => array(
								array(
									'taxonomy' => $taxonomies[0],
									'field' => $field,
									'terms' => $category,
								)
						),
					'meta_key' => $meta_key,
					'orderby' => $orderby,
					'order'=>$order		
			);
		}else{
			
				$args=array(
					'post_type' => $my_post_type,
					'post_status' => 'publish',				
					'posts_per_page' => $number,
					'meta_key' => $meta_key,
					'orderby' => $orderby,
					'order'=>$order
					);
			
		}
		$my_query = null;
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			$flg=0;
			$location_post_type=implode(',',get_option('location_post_type'));
			if(isset($my_post_type) && $my_post_type!=''){
				if (strpos($location_post_type,','.$my_post_type) !== false) {
				   $flg=1;
				}
			}
			if($flg==1){
				add_filter('posts_where', 'location_multicity_where');
			}
		}
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			add_filter('posts_where', 'wpml_listing_milewise_search_language');
		}
		
		add_filter('posts_where', 'hide_past_event');
		
		$my_query = new WP_Query($args);
		remove_filter('posts_where', 'hide_past_event');
	
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			add_filter('posts_where', 'location_multicity_where');
		}
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			remove_filter('posts_where', 'wpml_listing_milewise_search_language');
		}
		global $htmlvar_name;
		$heading_type = tmpl_fetch_heading_post_type($my_post_type);
		if(count($heading_type) > 0)
		{
			foreach($heading_type as $key=>$heading)
			{	
				if(function_exists('get_directory_listing_customfields'))
					$htmlvar_name[$key] = get_directory_listing_customfields($my_post_type,$heading,$key);//custom fields for custom post type..
			}
		}
		
		?>
        <div id="widget_loop_<?php echo $my_post_type?>" class="widget widget_loop_taxonomy listing_event_widget widget_loop_<?php echo $my_post_type?>">			
          <?php if( $my_query->have_posts()): ?>
			<?php if($title){?><h3 class="widget-title"><span><?php echo $title;?></span><?php if($link){?><a href="<?php echo $link;?>" class="more" ><?php echo $text; ?></a><?php }?></h3> <?php }?>
			<!-- widget_loop_taxonomy_wrap START -->
			<div id="loop_listing_taxonomy" class="widget_loop_taxonomy_wrap  <?php echo $view; ?>">
          	<?php while($my_query->have_posts()) : $my_query->the_post();?> 
				<!-- inside loop div start -->
               	<div id="<?php echo $my_post_type.'_'.get_the_ID(); ?>" <?php if((get_post_meta($post->ID,'featured_h',true) == 'h')){ post_class('post featured_post');} else { post_class('post');}?>>
               	 
				<?php   $post_id=get_the_ID();
						do_action('event_featured_widget_listing_image',$post_id,$my_post_type);
						?>
							  <!-- End fp_image-->
                              <!-- start fp_entry -->
                              <div class="entry">
								<?php 
								
								do_action('home_featured_before_title');
								$post_type= $post->post_type; 							
								do_action('event_before_title_'.$post_type);
								echo 
								'<div class="event-wrapper">';
								   do_action('show_event_featured_homepage_listing');
								  
								   echo '<div class="entry-details">';
								   do_action('event_lsiting_after_title_'.$post_type,$instance);	
								   echo '</div>
								</div>';
								do_action('event_taxonomy_content',$instance);
								do_action('event_featured_after_title',$instance);
								do_action('templ_the_taxonomies');
                                   
								echo "<div class='rev_pin'><ul>";								
								if(current_theme_supports('tevolution_my_favourites') && function_exists('tevolution_favourite_html')){
									echo '<li class="favourite">';
									tevolution_favourite_html();	
									echo '</li>';
								}
								echo '<li>';
									do_action('directory_the_comment');   
								echo '</li>
								</ul></div>'; ?>
                              </div> <!-- End fp_entry -->
				</div> <!-- inside loop div end -->     
            <?php endwhile; wp_reset_query();?>
			</div>
			<!-- widget_loop_taxonomy_wrap eND -->
			<?php endif; ?>
			</div> <!-- widget_loop_taxonomy -->
          <?php
	
	}
	function update($new_instance, $old_instance) {
		return $new_instance;
	}
	function form($instance) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 't1' => '', 't2' => '', 't3' => '',  'img1' => '', 'desc1' => '' ) );		
		$title = (strip_tags($instance['title'])) ? strip_tags($instance['title']) : __("Event Listing",EDOMAIN);
		$category = $instance['category'];
		$view = strip_tags($instance['view']);
		$number = strip_tags($instance['number']);
		$read_more = strip_tags($instance['read_more']);
		$sorting = strip_tags($instance['event_sorting']);
		
		/* check for existing user category slug comma separator */
		if(!is_array($category) || strstr($category,',')!=''){			
			$category=explode(',',$category);
		}
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo TITLE_TEXT; ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php echo __('Number of posts','templatic-admin');?>:
               	<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr($number); ?>" />
        </p>        
        <p>
			<label style="vertical-align:top;" for="<?php echo $this->get_field_id('category'); ?>"><?php echo __('Categories:','templatic-admin');?></label>
            <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>[]" class="<?php echo $this->get_field_id('category'); ?> widefat" multiple="multiple">
            	<option value=""><?php echo __('Select Category','templatic-admin');?></option>
				<?php 
				
				$taxonomies = get_object_taxonomies( (object) array( 'post_type' => 'event','public'   => true, '_builtin' => true ));
                $terms = get_terms( $taxonomies[0], 'orderby=name&hide_empty=1' );
                foreach( $terms as $term ){
               	 	$term_value = $term->term_id;	
					$selected=(in_array($term_value,$category) || in_array($term->slug,$category)) ?"selected":'';
					?>
                    <option value="<?php echo $term_value ?>" <?php echo $selected?>> <?php echo  esc_attr( $term->name ); ?> </option>
                <?php	}
				?>
			
            </select>
            
		</p>
		<p>
               <label for="<?php echo $this->get_field_id('view'); ?>"><?php echo __('View','templatic-admin')?>:
               <select id="<?php echo $this->get_field_id('view'); ?>" name="<?php echo $this->get_field_name('view'); ?>">
                         <option value="list" <?php if($view == 'list'){ echo 'selected="selected"';}?>><?php echo __('List view','templatic-admin');?></option>
                         <option value="grid" <?php if($view == 'grid'){ echo 'selected="selected"';}?>><?php echo __('Grid view','templatic-admin');?></option>
               </select>
               </label>
          </p>
		  <p><?php echo __('Grid View is not available for sidebar area. It is available for Home page content area only.','templatic-admin');?></p>
		 <p>
             <label for="<?php echo $this->get_field_id('content_limit'); ?>"><?php echo __('Limit content to', 'templatic-admin'); ?>: </label> <input type="text" id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('content_limit'); ?>" value="<?php echo esc_attr(intval($instance['content_limit'])); ?>" size="3" /> <?php echo __('characters', 'templatic-admin'); ?>
          </p>
		  <p>
          	<label for="<?php echo $this->get_field_id('read_more'); ?>"><?php echo __('Read More Text','templatic-admin');?>: 
              		<input class="widefat" id="<?php echo $this->get_field_id('read_more'); ?>" name="<?php echo $this->get_field_name('read_more'); ?>" type="text" value="<?php echo esc_attr($read_more); ?>" />
               </label>
          </p>
          <p>
		  <label for="<?php echo $this->get_field_id('event_sorting'); ?>"><?php echo __('Sort Event', 'templatic-admin'); ?>:
		  <select name="<?php echo $this->get_field_name('event_sorting'); ?>" id="<?php echo $this->get_field_id('event_sorting'); ?>">
			<option  <?php if ($sorting == 's_date' || $sorting=='' ) { echo 'selected=selected'; } ?> value="s_date"><?php _e('As Per Start Date','templatic'); ?></option>
			<option value="Latest Published" <?php if ($sorting == 'Latest Published') { echo 'selected=selected'; } ?>><?php _e('Latest Published','templatic'); ?></option>
			<option <?php if ($sorting == 'Random') { echo 'selected=selected'; } ?> value="Random"><?php _e('Random','templatic'); ?></option>
			<option <?php if ($sorting == 'Alphabetical') { echo 'selected=selected'; } ?> value="Alphabetical"><?php _e('Alphabetical','templatic'); ?></option>
			
		  </select>
		  </label>
		</p>
<?php
	}
}
?>
