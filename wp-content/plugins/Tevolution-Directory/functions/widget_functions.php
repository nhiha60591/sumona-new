<?php
/* Widgets - widget_functions.php */
/*
 * Home page display google map as per current city related post type data on map
 */
 
add_action('widgets_init','directory_plugin_widgets_init');
function directory_plugin_widgets_init()
{	
	register_widget('directory_featured_homepage_listing');
}

/*
	Widget of show the featured listing on home page
*/
class directory_featured_homepage_listing extends WP_Widget {
	
	function directory_featured_homepage_listing() {
		//Constructor
		global $thumb_url;
		$widget_ops = array('classname' => 'special', 'description' =>__('Showcase posts from any post type, including those created by you. Featured posts are displayed at the top. Works best in the Homepage - Main Content area.','templatic-admin')) ;
		$this->WP_Widget('directory_featured_homepage_listing',__('T &rarr; Homepage Display Posts','templatic-admin'), $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget		
		
		global $current_cityinfo,$htmlvar_name;
		extract($args, EXTR_SKIP);		
		echo $before_widget;
		$widget_title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$category = empty($instance['category']) ? '' : apply_filters('widget_category', $instance['category']);
		$number = empty($instance['number']) ? '5' : apply_filters('widget_number', $instance['number']);
		$my_post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
		$link = empty($instance['link']) ? '#' : apply_filters('widget_link', $instance['link']);
		$text = empty($instance['text']) ? '' : apply_filters('widget_text', $instance['text']);
		$view = empty($instance['view']) ? 'list' : apply_filters('widget_view', $instance['view']);
		$sorting_options = empty($instance['sorting_options']) ? '' : apply_filters('widget_sorting_options', $instance['sorting_options']);
		$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
		
		global $post,$wpdb,$wp_query;
		$post_widget_count = 1;
		
		$cus_post_type = empty($instance['post_type']) ? 'listing' : $instance['post_type'];
		$post_type = $post->post_type;
		
		/* get all the custom fields which select as " Show field on listing page" from back end */		
		if(function_exists('tmpl_get_category_list_customfields')){
			$htmlvar_name = tmpl_get_category_list_customfields($cus_post_type);
		}else{
			global $htmlvar_name;
		}
	
		remove_filter('pre_get_posts', 'home_page_feature_listing');
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $my_post_type,'public'   => true, '_builtin' => true ));
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			if(@$category!=""){
				foreach($category as $cat){	
					$category_ID =  get_term_by( 'id',$cat,  $taxonomies[0] );	
					$category_id.=$category_ID->term_id.',';
				}
				$category=explode(',',substr($category_id,0,-1));
			}
		}
		/* Check for existing user if user add category slug by common seprator */
		$field='id';	
	
		if($category!='' && !is_array($category)){
			$category=explode(',',$category);
			$category=array_map('trim',$category);
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
		
								
		if($sorting_options =='random'){
			/* for random sorting */
			$order_arg = array('orderby' => 'rand');
		}elseif($sorting_options =='total_price'){
			/* Fetch the listing first which paid more */
			$order_arg = array('meta_key' => 'paid_amount',	
						'orderby' => 'meta_value_num',
						'meta_value_num'=>'paid_amount',
						'order' => 'DESC');
		}elseif($sorting_options =='date'){
			/* Fetch the listings order by date */
			$order_arg = array('orderby' => 'date','order' => 'DESC');
		}elseif($sorting_options =='alphabetical'){
			/* Fetch the listing as per property price low to high */
			$order_arg = array('orderby' => 'post_title','order'=>'ASC');
		}elseif($sorting_options =='reviews'){
			/* Fetch the listing as per property price high to low */
			$order_arg = array('orderby' => 'comment_count','order'=>'DESC');
		}elseif($sorting_options =='featured'){
			/* Fetch only featured listing */
			$order_arg = array('meta_key' => 'featured_h',	
						'orderby' => 'meta_value',
						'order' => 'ASC');
		}else{
			/* Fetch the order by featured on home page listings first */
			$order_arg = array('meta_key' => 'featured_type',	
						'orderby' => 'meta_value',
						'order' => 'ASC');
		}
		
		$order_arg = apply_filters('tmpl_homepage_sorting_options',$order_arg);
		
		if(!empty($category)){
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
			);
			$args= array_merge($args,$order_arg);
		}else{
			
				$args=array(
					'post_type' => $my_post_type,
					'post_status' => 'publish',				
					'posts_per_page' => $number,
					);
			
			$args= array_merge($args,$order_arg);
		}		
		$my_query = null;
		
		remove_filter('posts_orderby', 'home_page_feature_listing_orderby');
		
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			$flg=0;
			$location_post_type = ','.implode(',',get_option('location_post_type'));
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
		
		if($taxonomies[0] !='' && (is_array($category) && count($category) > 0)){
			$tax_trans_slug = str_replace(',','-',implode(',',$category));
		}else{
			$tax_trans_slug ='na';
		}
		
		$replace= array(' ');
		$replace_with =  array('_');
		$w_title= str_replace($replace,$replace_with,strtolower($widget_title));
		/* Add query to transient */
		if($sorting_options !='random'){
				if (get_option('tevolution_cache_disable')==1  && false === ( $my_query = get_transient( 'home_page_featured_'.trim($my_post_type).'_'.$sorting_options.$current_cityinfo['city_id'].$cur_lang_code.$w_title.$number.$tax_trans_slug  ) )  ) { 
					$my_query = new WP_Query($args);		
					set_transient( 'home_page_featured_'.trim($my_post_type).'_'.$sorting_options.$current_cityinfo['city_id'].$cur_lang_code.$w_title.$number.$tax_trans_slug, $my_query, 12 * HOUR_IN_SECONDS );		
				}elseif(get_option('tevolution_cache_disable')==''){ 
					$my_query = new WP_Query($args);
				}
		}else{
			$my_query = new WP_Query($args);
		}		
	
		remove_filter('posts_join', 'custom_field_posts_where_filter');
	
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			add_filter('posts_where', 'location_multicity_where');
		}
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			remove_filter('posts_where', 'wpml_listing_milewise_search_language');
		}
		global $htmlvar_name,$wp_query;
		$wp_query->set('is_ajax_archive',1);
		$wp_query->set('is_related',1);
		
		?>
        <div id="widget_loop_<?php echo $my_post_type?>" class="widget widget_loop_taxonomy widget_loop_<?php echo $my_post_type?>">			
          <?php if( $my_query->have_posts()): ?>
			<?php if($widget_title){?><h3 class="widget-title"><span><?php echo $widget_title;?></span><?php if($link){?><a href="<?php echo $link;?>" class="more" ><?php 
			/*  for translation for widget text  */	
				if(function_exists('icl_register_string')){
						icl_register_string(DIR_DOMAIN,'directory'.$text,$text);
						$text = icl_t(DIR_DOMAIN,'directory'.$text,$text);
					
				}
	
			echo $text; ?></a><?php }?></h3> <?php }?>
			<!-- widget_loop_taxonomy_wrap START -->
			<section id="loop_listing_taxonomy" class="widget_loop_taxonomy_wrap  <?php echo $view?>">
          	<?php while($my_query->have_posts()) : $my_query->the_post(); global $post;
					$addons_posttype =tmpl_addon_name();
					
		
					if(function_exists('tmpl_wp_is_mobile') && tmpl_wp_is_mobile()){
						/* this content will load in mobile only */
						include(WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.'/entry-mobile-'.$post->post_type.'.php');
					
					}else{
					?> 
					<!-- inside loop div start -->
				
					<article id="<?php echo $my_post_type.'_'.get_the_ID(); ?>" <?php if((get_post_meta($post->ID,'featured_h',true) == 'h')){ post_class('post featured_post ');} else { post_class('post large-4 medium-4 small-6 xsmall-12 columns');}?>>  
					<?php 
					/* Hook to display before image */	
					do_action('directory_before_category_page_image');
						
					/* Hook to Display Listing Image  */
					do_action('directory_category_page_image');
					 
					/* Hook to Display After Image  */						 
					do_action('directory_after_category_page_image'); 
					   
					/* Before Entry Div  */	
					do_action('directory_before_post_entry'); ?> 
					
					<!-- Entry Start -->
					<div class="entry"> 
							
						<?php  /* do action for before the post title.*/ 
						do_action('directory_before_post_title');  
						do_action('templ_before_title_'.$my_post_type);       ?>
						<div class="<?php echo $my_post_type; ?>-wrapper">
							<!-- Entry title start -->
							<div class="entry-title">
						   
							<?php do_action('templ_post_title');                /* do action for display the single post title */?>
						   
							</div>
							
							<?php do_action('directory_after_post_title');          /* do action for after the post title.*/?>
						   
							<!-- Entry title end -->
							
							<!-- Entry details start -->
							<div class="entry-details">
							
							<?php /* Hook to get Entry details - Like address,phone number or any static field  */ 
							
							//do_action($my_post_type.'_post_info');
							do_action($post->post_type.'_post_info'); ?>
							
							</div>
							<!-- Entry details end -->
						</div>
						<!--Start Post Content -->
						<?php /* Hook for before post content . */ 
					   
						do_action('directory_before_post_content'); 
						
						/* Hook to display post content . */ 
						do_action('templ_taxonomy_content');
					   
						/* Hook for after the post content. */
						do_action('directory_after_post_content'); 
						?>
						<!-- End Post Content -->
					   <?php 
					    /* Hook for before listing categories     */
						do_action('directory_before_taxonomies');
					    
						/* Display listing categories     */
					    do_action('templ_the_taxonomies'); 

						/* Hook to display the listing comments, add to favorite and pinpoint   */						
						do_action('directory_after_taxonomies');?>
					</div>
					<!-- Entry End -->
					<?php do_action('directory_after_post_entry');?>
				</article>    
            <?php }
			
			endwhile; wp_reset_query();?>
			</section>
			<!-- widget_loop_taxonomy_wrap eND -->
			<?php endif; ?>
			</div> <!-- widget_loop_taxonomy -->
          <?php
		
	 	echo $after_widget;
	}
	
	
	function update($new_instance, $old_instance) {
		//save the widget
		global $wpdb;
		$wpdb->query($wpdb->prepare( "delete from $wpdb->options where option_name LIKE %s",'%home_page_featured_%'));
		return $new_instance;
	}
	function form($instance) {
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => __("Featured Listing",'templatic-admin'), 'category' => '', 'number' => 5 , 'post_type' => 'listing' , 'link' => '#', 'text' => __("View All",'templatic-admin'), 'view' => 'list','read_more' => '' ) );
		$title = strip_tags($instance['title']);
		$category = $instance['category'];
		$number = strip_tags($instance['number']);
		$my_post_type = strip_tags($instance['post_type']);
		$link = strip_tags($instance['link']);
		$text = strip_tags($instance['text']);
		$view = strip_tags($instance['view']);
		$read_more = strip_tags($instance['read_more']);
		$sort_opt = (!empty($instance['sorting_options']))? $instance['sorting_options'] :'featured_listing';
		$rand = rand();
		/* check for existing user category slug commna seprator */

		if(!is_array($category) || strstr($category_array,',')){
			$category=explode(',',$category);
			/*trim array value */
			$category=array_map('trim',$category);
		}
		
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>: 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
          </p>
		<p>
			<label for="<?php echo $this->get_field_id('text'); ?>"><?php echo __('View All Text','templatic-admin');?>: 
				<input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo esc_attr($text); ?>" />
			</label>
          </p>
		<p>
			<label for="<?php echo $this->get_field_id('link'); ?>"><?php echo __('View All Link URL: (ex.http://templatic.com/events)','templatic-admin');?> 
				<input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr($link); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php echo __('Number of posts','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr($number); ?>" />
			</label>
		</p>
		<p>
		   <label for="<?php echo $this->get_field_id('view'); ?>"><?php echo __('View','templatic-admin')?>:
		   <select id="<?php echo $this->get_field_id('view'); ?>" onchange="show_content_limit(this.value,<?php echo $rand; ?>)"  name="<?php echo $this->get_field_name('view'); ?>">
					 <option value="list" <?php if($view == 'list'){ echo 'selected="selected"';}?>><?php echo __('List view','templatic-admin');?></option>
					 <option value="grid" <?php if($view == 'grid'){ echo 'selected="selected"';}?>><?php echo __('Grid view','templatic-admin');?></option>
		   </select>
		   </label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php echo __('Post Type','templatic-admin')?>:
			<select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" class="widget_post_type widefat" onchange="display_post_type_category(this,'<?php echo $this->get_field_id('category');?>','<?php echo implode(',',$category);?>')">
			 <?php
				$all_post_types = apply_filters('tmpl_allow_fields_posttype',get_option("templatic_custom_post"));
				foreach($all_post_types as $key=>$post_type){?>
					<option value="<?php echo $key;?>" <?php if($key== $my_post_type)echo "selected";?>><?php echo esc_attr($post_type['label']);?></option>
			<?php }?>	
			</select>
			</label>
		</p>
		<p id="show_content_limit_<?php echo $rand; ?>" <?php if(@$view == 'grid'){?> style="display:none;"<?php }?> >
			<label for="<?php echo $this->get_field_id('content_limit'); ?>"><?php echo __('Limit content to', 'templatic-admin'); ?>: </label> <input type="text" id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('content_limit'); ?>" value="<?php echo esc_attr(intval($instance['content_limit'])); ?>" size="3" /> <?php echo __('characters', 'templatic-admin'); ?>
		</p>
		<p>
			<label style="vertical-align:top;" for="<?php echo $this->get_field_id('category'); ?>"><?php echo __('Categories:','templatic-admin');?></label>
            <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>[]" class="<?php echo $this->get_field_id('category'); ?> widefat" multiple="multiple">
            	<option value=""><?php echo __('Select Category','templatic-admin');?></option>
				<?php 
				if($my_post_type!=''){
				$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $my_post_type,'public'   => true, '_builtin' => true ));
                $terms = get_terms( $taxonomies[0], 'orderby=name&hide_empty=0' );
                foreach( $terms as $term ){
               	 	$term_value = $term->term_id;	
					$selected=(in_array($term_value,$category) || in_array($term->slug,$category)) ?"selected":'';
					?>
                    <option value="<?php echo $term_value ?>" <?php echo $selected?>> <?php echo esc_attr( $term->name ); ?> </option>
                <?php	}
				}?>
			
            </select>
            
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('sorting_options'); ?>"><?php echo __('Sorting Options',ADMINDOMAIN)?>:
			<select id="<?php echo $this->get_field_id('sorting_options'); ?>" name="<?php echo $this->get_field_name('sorting_options'); ?>">
			 <?php
				$sorting_options = apply_filters('tmpl_homepage_sorting_options',array('alphabetical'=>__('Alphabetical',ADMINDOMAIN),'random'=>__('Random',ADMINDOMAIN),'date'=>__('Date',ADMINDOMAIN),'total_price'=>__('Higher Paid First',ADMINDOMAIN),'featured_listing'=>__('Featured Listing',ADMINDOMAIN)));
				if(get_option('default_comment_status') =='open'){
					$sorting_options = array_merge($sorting_options,array('reviews'=>'Reviews/Comments'));
				}
				foreach($sorting_options as $key=>$value){
				if($sort_opt == $key){ $sel ="selected=selected"; }else{ $sel =''; }
				?>
					<option value="<?php echo $key;?>" <?php echo $sel;?>><?php echo $value;?></option>
			<?php }?>	
			</select>
			</label>
		</p>
		<script>
			function show_content_limit(val,rand_var)
			{
				if(val == 'list'){
					document.getElementById("show_content_limit_"+rand_var).style.display = '';
				}else{
					document.getElementById("show_content_limit_"+rand_var).style.display = 'none';
				}
			}
			function display_post_type_category(post_type,category_id,cat_val){				
				jQuery.ajax({
					url:ajaxUrl,
					type:'POST',
					async: true,
					data:'action=callwidget_post_type_category&post_type='+jQuery(post_type).val()+'&cat_val='+cat_val,						
					success:function(results){						
						jQuery('#'+category_id).html(results);
					},
				});
				
			}
		</script>
		<?php
	}
}
/* End directory_featured_homepage_listing widget */

add_action( 'wp_ajax_callwidget_post_type_category','wp_ajax_callwidget_post_type_category');
function wp_ajax_callwidget_post_type_category(){
	

	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $_REQUEST['post_type'],'public'   => true, '_builtin' => true ));
	$terms = get_terms( $taxonomies[0], 'orderby=name&hide_empty=1' );
	$select_option='<option value="">'. __('Select Category','templatic-admin').'</option>';
	$category=explode(',',$_REQUEST['cat_val']);
	foreach( $terms as $term ){
		$term_value = $term->term_id;	
		$selected=(in_array($term_value,$category) || in_array($term->slug,$category)) ? "selected":'';
		
		$select_option.='<option value="'.$term_value.'" '.$selected.'>'. esc_attr( $term->name ).' </option>';
	}
	echo $select_option;
	exit;
}


?>