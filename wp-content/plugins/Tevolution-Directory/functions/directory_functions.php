<?php
add_action('init','directory_init_function');

/* Script for detail page map and cookies js*/
add_action('wp_head','directory_script_style');
function directory_script_style(){
	$custom_post_type = tevolution_get_post_type();	
	if((is_archive() && in_array(get_post_type(),$custom_post_type)  && get_post_type()!='event' && !is_author())|| is_search()){
	 	wp_enqueue_script('directory-cookies-script', TEVOLUTION_DIRECTORY_URL.'js/jquery_cokies.js',array( 'jquery' ),'',false);
	}
	$custom_post_type = tevolution_get_post_type();	
	if((is_single() || is_singular()) && (in_array(get_post_type(),$custom_post_type)  && get_post_type()!='event' )){
		wp_enqueue_script('jquery-ui-tabs');
		?>
        <script type="text/javascript">
			jQuery(function() {
				jQuery('.listing-image a.listing_img').lightBox();
			});	
			jQuery('.tabs').bind('tabsshow', function(event, ui) {			
				if (ui.panel.id == "listing_map") {
					Demo.init();
				}
			});
			jQuery(function(){ var n=jQuery("ul.tabs li a, .tmpl-accordion dd a").attr("href");if(n=="#listing_map"){Demo.init();}})
			
			jQuery(function(){jQuery("ul.tabs li a, .tmpl-accordion dd a").live('click',function(){
				var n=jQuery(this).attr("href");if(n=="#listing_map"){Demo.init();}
			})});
		
		</script>
		<?php
	}
}
/*call plug-in js and css file on admin_head and wp_head action*/
add_action('admin_head','manage_function_script'); 
add_action('wp_enqueue_scripts','manage_function_script',4);
function manage_function_script(){
	global $pagenow,$post,$wp_query;	
	if(is_admin()){
		wp_enqueue_script('function_script',TEVOLUTION_DIRECTORY_URL.'js/function_script.js',array( 'jquery' ),'',false);
		wp_enqueue_script('thickbox');
	}
	/* Directory Plug-in Style Sheet File In Desktop view only  */	
	if ( !tmpl_wp_is_mobile()) {
		wp_enqueue_style('directory_style',TEVOLUTION_DIRECTORY_URL.'css/directory.css');
	}
}

/*
	add the image sizes for addon
*/
function directory_init_function(){
	add_image_size( 'directory-listing-image', 250, 165, true );
	add_image_size( 'directory-single-image', 300, 200, true );	
	// Register widgetized areas
	if ( function_exists('register_sidebar') )
	{
		register_sidebars(1,array('id' => 'after_directory_header', 'name' => __('Listing Category Pages - Below Header','templatic-admin'), 'description' => __('Use this area to show widgets between the secondary navigation bar and main content area on Listing category pages.','templatic-admin'),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));		
			
	}
	remove_filter('the_content','view_sharing_buttons');
	remove_filter( 'the_content', 'view_count' );
	remove_action('tmpl_before_comments','single_post_categories_tags'); 
}

/* It Will Display the Directions map on detail page */

add_action('directory_single_page_map','directory_singlemap_after_post_content');
function directory_singlemap_after_post_content(){
	global $post,$templatic_settings;
	$templatic_settings=get_option('templatic_settings');
	
	if(is_single() && $templatic_settings['direction_map']=='yes'){
		$geo_latitude = get_post_meta(get_the_ID(),'geo_latitude',true);
		$geo_longitude = get_post_meta(get_the_ID(),'geo_longitude',true);
		$address = get_post_meta(get_the_ID(),'address',true);
		$map_type =get_post_meta(get_the_ID(),'map_view',true);
		$zooming_factor =get_post_meta(get_the_ID(),'zooming_factor',true);
		if($address){			
		?>
               <div id="directory_location_map" style="width:100%;">
                    <div class="directory_google_map" id="directory_google_map_id" style="width:100%;"> 
                    <?php include_once (TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/google_map_detail.php');?> 
                    </div>  <!-- google map #end -->
               </div>
		<?php
		}
	
	}
}
/*
	Add class name related to directory addon on every page
 */
add_filter('body_class','directory_body_class',11,2);
function directory_body_class($classes,$class){
	$custom_post_type = apply_filters('directory_post_type_template',tevolution_get_post_type());	
	if ( is_front_page() )
		$classes[] = 'tevolution-directory directory-front-page';
	elseif ( is_home() )
		$classes[] = 'tevolution-directory directory-home';
	elseif ( is_single() && get_post_type()==CUSTOM_POST_TYPE_LISTING || (isset($_REQUEST['page']) && $_REQUEST['page']=='preview'))
		$classes[] = 'tevolution-directory directory-single-page';
	elseif ( is_single() && (in_array(get_post_type(),$custom_post_type)  && get_post_type()!='event' )|| (isset($_REQUEST['page']) && $_REQUEST['page']=='preview'))
		$classes[] = 'tevolution-directory directory-single-page';
	elseif ( is_page() || isset($_REQUEST['page']) )
		$classes[] = 'tevolution-directory directory-page';	
	elseif ( is_tax() )
		$classes[] = 'tevolution-directory directory-taxonomy-page';
	elseif ( is_tag() )
		$classes[] = 'tevolution-directory directory-tag-page';
	elseif ( is_date() )
		$classes[] = 'tevolution-directory directory-date-page';
	elseif ( is_author() )
		$classes[] = 'tevolution-directory directory-author-page';
	elseif ( is_search() )
		$classes[] = 'tevolution-directory directory-search-page';
	elseif ( is_post_type_archive() )
		$classes[] = 'tevolution-directory directory-post-type-page';	
	elseif((isset($_REQUEST['page']) && $_REQUEST['page'] == "preview")  && isset($_POST['cur_post_type']) && $_POST['cur_post_type']==CUSTOM_POST_TYPE_LISTING)
	{
		$classes[] = 'tevolution-directory directory-single-page';
	}	
		
	return $classes;
}

/*
	Add class name on container div
*/
function directory_class(){
	
	echo get_directory_class();		
}
function get_directory_class(){
	global $wpdb,$templatic_settings,$wp_query,$city_id;
	if($templatic_settings['pippoint_effects'] =='click')
	{ 
		$classes[]="wmap_static"; 
	}else{
		$classes[]="wmap_scroll"; 
	}	
	
	$classes = apply_filters( 'get_directory_class', $classes);
	
	if(!empty($classes))
		$classes = join( ' ', $classes );
	return $classes;	
}

/*
	This function will return the results after drag the miles range slider
*/
add_action('wp_ajax_nopriv_listing_search','directory_listing_search');
add_action('wp_ajax_listing_search','directory_listing_search');
function directory_listing_search(){
	global $wp_query,$wpdb,$current_cityinfo;	
	
	$per_page=get_option('posts_per_page');
	if(isset($_REQUEST['term_id']) && $_REQUEST['term_id']!=""){
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => 'listing','public'   => true, '_builtin' => true ));	
		$args=array(
				 'post_type'      => 'listing',
				 'posts_per_page' => $per_page,
				 'post_status'    => 'publish',
				 'tax_query'      => array(
										  array(
											 'taxonomy' => $taxonomies[0],
											 'field'    => 'id',
											 'terms'    => explode(',',$_REQUEST['term_id']),
											 'operator' => 'IN'
										  )
									   ),
				);
		
	}else{
		$args=array(
				 'post_type'      => 'listing',
				 'posts_per_page' => $per_page,
				 'post_status'    => 'publish',
				 );
	}
	
	directory_manager_listing_custom_field();
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		add_filter('posts_where', 'wpml_listing_milewise_search_language');
	}
	
	add_action('pre_get_posts','directot_search_get_posts');
	add_filter( 'posts_where', 'directory_listing_search_posts_where', 10, 2 );
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		add_filter('posts_where', 'location_multicity_where');
	}
	$post_details= new WP_Query($args);	
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		remove_filter('posts_where', 'location_multicity_where');
	}
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		remove_filter('posts_where', 'wpml_listing_milewise_search_language');
	}
	if ($post_details->have_posts()) :
		while ( $post_details->have_posts() ) : $post_details->the_post();
		
		if(isset($_REQUEST['page_type'])=='archive' || isset($_REQUEST['page_type'])=='taxonomy'){		
			/* to display the html same as category and archive pages for listing */
			directory_archive_search_listing($wp_query);
		}
		endwhile;
		wp_reset_query();
	else:
		?>
        <p class='nodata_msg'><?php _e( 'Apologies, but no results were found for the requested archive.', DIR_DOMAIN ); ?></p>    
        <?php
	endif;
	exit;
}
function directot_search_get_posts($wp_query){
	$wp_query->set('is_archive',1);	
	
}

/*
	This function will return the HTMl  after the filter results on category page ( like miles range )
*/

function directory_archive_search_listing($wp_query){
		
	add_filter( "pre_get_posts", "directot_search_get_posts" );
	global $post,$wp_query;	
	$wp_query->set('is_ajax_archive',1);
	do_action('directory_before_post_loop');
	
	$featured=get_post_meta(get_the_ID(),'featured_c',true);
	$classes=($featured=='c')?'featured_c':'';
	?>
     <div class="post <?php echo $classes;?>">  
        <?php do_action('directory_before_archive_image');           /*do_action before the post image */
				
				do_action('directory_archive_page_image');
				
				do_action('directory_after_archive_image');           /*do action after the post image */?> 
		<div class="entry"> 
               <!--start post type title -->
               <?php do_action('directory_before_post_title');         /* do action for before the post title.*/ ?>
               
				<div class="listing-wrapper">
						<!-- Entry title start -->
						<div class="entry-title">
					   
						<?php do_action('templ_post_title');                /* do action for display the single post title */?>
					   
						</div>
						
						<?php do_action('directory_after_post_title');          /* do action for after the post title.*/?>
					   
						<!-- Entry title end -->
						
						<!-- Entry details start -->
						<div class="entry-details">
						
						<?php  /* Hook to get Entry details - Like address,phone number or any static field  */  
						do_action('listing_post_info');   ?>     
						
						</div>
						<!-- Entry details end -->
				</div>
               
               
				<!--Start Post Content -->
				<?php do_action('directory_before_post_content');       /* do action for before the post content. */ 
				$tmpdata = get_option('templatic_settings');
				if($tmpdata['listing_hide_excerpt']=='' || !in_array(get_post_type(),$tmpdata['listing_hide_excerpt'])){
                    if(function_exists('supreme_prefix')){
                         $theme_settings = get_option(supreme_prefix()."_theme_settings");
                    }else{
                         $theme_settings = get_option("supreme_theme_settings");
                    }
                    if($theme_settings['supreme_archive_display_excerpt']){
                         echo '<div itemprop="description" class="entry-summary">';
                         the_excerpt();
                         echo '</div>';
                    }else{
                         echo '<div itemprop="description" class="entry-content">';
                         the_content(); 
                         echo '</div>';
                    }
				}
               do_action('directory_after_post_content');        /* do action for after the post content. */?>
               <!-- End Post Content -->
               
               <!-- Show custom fields where show on listing = yes -->
               <?php do_action('directory_listing_custom_field');/*add action for display the listing page custom field */?>
               
               <?php do_action('templ_the_taxonomies');   ?>  
               
               <?php do_action('directory_after_taxnomies');?>
        </div>
     </div>
     <?php do_action('directory_after_post_loop');
}

/*
	Display edit link on front end when user logged in.
*/
add_action('directory_edit_link','directory_edit_link');
function directory_edit_link() {
	$post_type = get_post_type_object( get_post_type() );
	if ( !current_user_can( $post_type->cap->edit_post, get_the_ID() ) )
		return '';
	
	$args = wp_parse_args( array( 'before' => '', 'after' => ' ' ), @$args );
	echo $args['before'] . '<span class="edit"><a class="post-edit-link" href="' . esc_url( get_edit_post_link( get_the_ID() ) ) . '" title="' . sprintf( esc_attr__( 'Edit %1$s', DIR_DOMAIN ), $post_type->labels->singular_name ) . '">' . __( 'Edit', DIR_DOMAIN ) . '</a></span>' . $args['after'];
}
/*
	Display the after directory header widget
 */
add_action('after_directory_header','after_directory_header');
function after_directory_header(){
	
	if ( is_active_sidebar( 'after_directory_header') ) : ?>
	<div id="category-widget" class="category-widget columns">
		<?php dynamic_sidebar('after_directory_header'); ?>
	</div>
	<?php endif;
}
/* Add add to favourite html for directory theme on listings page  */

function directory_favourite_html($user_id,$post)
{
	global $current_user,$post;
	$add_to_favorite = __('Add to favorites',DIR_DOMAIN);
	$added = __('Added',DIR_DOMAIN);
	if(function_exists('icl_register_string')){
		icl_register_string(DIR_DOMAIN,'directory'.$add_to_favorite,$add_to_favorite);
		$add_to_favorite = icl_t(DIR_DOMAIN,'directory'.$add_to_favorite,$add_to_favorite);
		icl_register_string(DIR_DOMAIN,'directory'.$added,$added);
		$added = icl_t(DIR_DOMAIN,'directory'.$added,$added);
	}
	$post_id = $post->ID;
	
	$user_meta_data = get_user_meta($current_user->ID,'user_favourite_post',true);
	if($post->post_type !='post'){
		do_action('tmpl_after_addtofav_link');
		if($user_meta_data && in_array($post_id,$user_meta_data))
		{
			?>
			<li id="tmplfavorite_<?php echo $post_id;?>" class="fav_<?php echo $post_id;?> fav"  > <a href="javascript:void(0);" class="removefromfav" onclick="javascript:addToFavourite('<?php echo $post_id;?>','remove');"><?php echo $added;?></a></li>    
			<?php
		}else{
		?>
			<li id="tmplfavorite_<?php echo $post_id;?>" class="fav_<?php echo $post_id;?> fav"><a href="javascript:void(0);" class="addtofav"  onclick="javascript:addToFavourite('<?php echo $post_id;?>','add');"><?php echo $add_to_favorite;?></a></li>
		<?php } 
		do_action('tmpl_after_addtofav_link');
	}
}

/*
	Display the category and tags on category page
*/
add_action('directory_the_taxonomies','directory_post_categories_tags');
function directory_post_categories_tags()
{
	global $post;
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID, $taxonomies[0]);
	$sep = ", ";
	$i = 0;
	foreach($terms as $term)
	{
		
		if($i == ( count($terms) - 1))
		{
			$sep = '';
		}
		elseif($i == ( count($terms) - 2))
		{
			$sep = __(' and ',DIR_DOMAIN);
		}
		$term_link = get_term_link( $term, $taxonomies[0] );
		if( is_wp_error( $term_link ) )
			continue;
		$taxonomy_category .= '<a href="' . $term_link . '">' . $term->name . '</a>'.$sep; 
		$i++;
	}
	if(!empty($terms))
	{
		echo '<p class="bottom_line"><span class="i_category">';
		echo sprintf(__('<span>Posted in</span> %s',DIR_DOMAIN),$taxonomy_category);
		echo '</span></p>';
	}
	global $post;
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	
	$tag_terms = get_the_terms($post->ID, $taxonomies[1]);
	$sep = ",";
	$i = 0;
	if($tag_terms){
	foreach($tag_terms as $term)
	{
		
		if($i == ( count($tag_terms) - 1))
		{
			$sep = '';
		}
		elseif($i == ( count($tag_terms) - 2))
		{
			$sep = __(' and ',DIR_DOMAIN);
		}
		$term_link = get_term_link( $term, $taxonomies[1] );
		if( is_wp_error( $term_link ) )
			continue;
		$taxonomy_tag .= '<a href="' . $term_link . '">' . $term->name . '</a>'.$sep; 
		$i++;
	}
	}
	if(!empty($tag_terms))
	{
		echo '<p class="bottom_line"><span class="i_category">';
		echo sprintf(__('Tagged In %s',DIR_DOMAIN),$taxonomy_tag);
		echo '</span></p>';
	}
}
/* 
	Link of sample listings CSV
*/
add_action('tevolution_listing_sample_csvfile','tevolution_listing_sample_csvfile');
function tevolution_listing_sample_csvfile(){
	?>
     <a href="<?php echo TEVOLUTION_DIRECTORY_URL.'functions/listing_sample.csv';?>"><?php _e('(Sample csv file)',DIR_DOMAIN);?></a>
     <?php	
}
/*
	This function will return the search page map listings
*/
add_action('wp_ajax_nopriv_listing_search_map','directory_listing_search_map');
add_action('wp_ajax_listing_search_map','directory_listing_search_map');
function directory_listing_search_map(){
	global $wp_query,$wpdb,$current_cityinfo;
	
	$per_page=get_option('posts_per_page');
	if(isset($_REQUEST['term_id']) && $_REQUEST['term_id']!=""){
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => 'listing','public'   => true, '_builtin' => true ));	
		$args=array(
				 'post_type'      => 'listing',
				 'posts_per_page' => $per_page,
				 'post_status'    => 'publish',
				 'tax_query'      => array(
										  array(
											 'taxonomy' => $taxonomies[0],
											 'field'    => 'id',
											 'terms'    => explode(',',$_REQUEST['term_id']),
											 'operator' => 'IN'
										  )
									   ),
				);
		
	}else{
		$args=array(
				 'post_type'      => 'listing',
				 'posts_per_page' => $per_page,
				 'post_status'    => 'publish',
				 );
	}
	directory_manager_listing_custom_field();
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		add_filter('posts_where', 'wpml_listing_milewise_search_language');
	}
	
	add_action('pre_get_posts','directot_search_get_posts');
	add_filter( 'posts_where', 'directory_listing_search_posts_where', 10, 2 );
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		add_filter('posts_where', 'location_multicity_where');
	}
	$post_details= new WP_Query($args);
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		remove_filter('posts_where', 'location_multicity_where');
	}
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		remove_filter('posts_where', 'wpml_listing_milewise_search_language');
	}
	$term_icon='';
	if(isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy']!='' && isset($_REQUEST['slug']) && $_REQUEST['slug']!=''){			
		$term=get_term_by( 'slug',$_REQUEST['slug'] , $_REQUEST['taxonomy'] ) ;
		$term_icon=$term->term_icon;
	}
		
	if ($post_details->have_posts()) :
	$pids=array();
		while ( $post_details->have_posts() ) : $post_details->the_post();
			$ID =get_the_ID();				
			$title = get_the_title($ID);
			$plink = get_permalink($ID);
			$lat = get_post_meta($ID,'geo_latitude',true);
			$lng = get_post_meta($ID,'geo_longitude',true);					
			$address = stripcslashes(str_replace($srcharr,$replarr,(get_post_meta($ID,'address',true))));
			$contact = str_replace($srcharr,$replarr,(get_post_meta($ID,'phone',true)));
			$website = get_post_meta($ID,'website',true);
			/*Fetch the image for display in map */
			if ( has_post_thumbnail()){
				$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail');						
				$post_images=$post_img[0];
			}else{
				$post_img = bdw_get_images_plugin($ID,'thumbnail');					
				$post_images = $post_img[0]['file'];
			}
			
			$imageclass='';
			if($post_images)
				$post_image='<div class=map-item-img><img src='.$post_images.' width=150 height=150/></div>';
			else{
				$post_image='';
				$imageclass='no_map_image';
			}
			
			if($term_icon=='')
				$term_icon=apply_filters('tmpl_default_map_icon',TEVOLUTION_DIRECTORY_URL.'images/pin.png');
			
			$image_class=($post_image)?'map-image' :'';
			$comment_count= count(get_comments(array('post_id' => $ID)));
			$review=($comment_count ==1 )? __('review',DIR_DOMAIN):__('reviews',DIR_DOMAIN);	
			
			if(($lat && $lng )&& !in_array($ID,$pids))
			{ 	
				$retstr ='{';
				$retstr .= '"name":"'.$title.'",';
				$retstr .= '"location": ['.$lat.','.$lng.'],';
				$retstr .= '"message":"<div class=\"google-map-info '.$image_class.'\"><div class=map-inner-wrapper><div class=\"map-item-info '.$imageclass.'\">'.$post_image;
				$retstr .= '<h6><a href='.$plink.' class=ptitle><span>'.$title.'</span></a></h6>';							
				if($address){$retstr .= '<p class=address>'.$address.'</p>';}
				if($contact){$retstr .= '<p class=contact>'.$contact.'</p>';}
				if($website){$retstr .= '<p class=website><a href= '.$website.'>'.$website.'</a></p>';}
				if($templatic_settings['templatin_rating']=='yes'){
					$rating=draw_rating_star_plugin(get_post_average_rating(get_the_ID()));
					$retstr .= '<div class=map_rating>'.str_replace('"','',$rating).' <span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
				}else{
					$retstr .= apply_filters('show_map_multi_rating',get_the_ID(),$plink,$comment_count,$review);
				}
				$retstr .= '</div></div></div>';
				$retstr .= '",';
				$retstr .= '"icons":"'.$term_icon.'",';
				$retstr .= '"pid":"'.$ID.'"';
				$retstr .= '}';
				$content_data[] = $retstr;
				$j++;
			}	
			
			$pids[]=$ID;
		endwhile;
		wp_reset_query();	
		
	endif;
	if($content_data)	
		$cat_content_info[]= implode(',',$content_data);
				
	if($cat_content_info)
	{
		$catinfo_arr= '{"markers":['.implode(',',$content_data)."]}";
	}else
	{
		$catinfo_arr= '{"markers":[]}';
	}
	echo $catinfo_arr;	
	exit;
}




/*
	add page view options in general settings
*/
add_action('tmpl_other_page_view_option','dir_page_view_options');
function dir_page_view_options(){
	$get_plug_data = get_option('templatic_settings');
	$googlemap_setting=get_option('city_googlemap_setting'); ?>
	  &nbsp;&nbsp;
    <label for="default_page_view3">
      <input type="radio" id="default_page_view3" name="default_page_view" value="mapview" <?php if( @$get_plug_data['default_page_view']== "" || @$get_plug_data['default_page_view']=='mapview') echo "checked=checked";?> />
      <?php echo __('Map',THEME_DOMAIN); ?>
    </label>
    <?php 
}
/* This function use for direcyory plugin localization sligs  */
add_action('admin_init','directory_localization_slugs');
function directory_localization_slugs(){
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		if(!get_option('directory_localization'))
		{
			$found='';
			$files_to_search =  TEVOLUTION_DIRECTORY_DIR.'directory.php';
			$old = 'define\(\ \'DIR_DOMAIN';
			$old1 = 'define\(\ \'ADMINDOMAIN';
			$new ="\tdefine( 'DIR_DOMAIN', 'tevolution'); ";
			$new1 ="\tdefine( 'ADMINDOMAIN', 'tevolution' );";
			if(function_exists('tevolution_replace_line'))
			{
				tevolution_replace_line($old,$new,$files_to_search);
				tevolution_replace_line($old1,$new1,$files_to_search);
			}
			update_option('directory_localization','1');
		}	
		if(get_option('directory_localization') == 1)
		{
			add_action('admin_notices','directory_text_domain_message');
			//add_Action('localization_filter','directory_text_domain_message');
		}
	
		if(isset($_REQUEST['ch_domain']) && $_REQUEST['ch_domain'] =='directorydomain'){
			$found='';
				$files_to_search =  TEVOLUTION_DIRECTORY_DIR.'directory.php';
				$old = 'define\(\ \'DIR_DOMAIN';
				$old1 = 'define\(\ \'templatic-admin';
				$new ="\tdefine( 'DIR_DOMAIN', 'templatic'); ";
				if(function_exists('tevolution_replace_line'))
				{
					tevolution_replace_line($old,$new,$files_to_search);
					tevolution_replace_line($old1,$new1,$files_to_search);
				}
				update_option('directory_localization',2);
		}
	}
}

/*
 * Change the localization slug if use wpml plugin
 */
function directory_text_domain_message(){
	
	$url = admin_url('index.php?ch_domain=domain');
	$message = "<div id=\"error\" class=\"updated\">\n";
	$message .= '<p>'.__('Same changes we do in directory plugin.',ADMINDOMAIN).'<a href="'.admin_url('index.php?ch_domain=directorydomain').'"> '.__('Click here',ADMINDOMAIN).' </a> '.__('to change it with new one.',ADMINDOMAIN).'</p>';
	$message .= "</div>";
	echo $message;
}


/* Remove the listing post type from back end custom fields section - because we want to show the listing post type first on custom fields filter */

/* pass blank post type when tmpl_get_posttype() function is used to get post types */
add_filter('tmpl_custom_fields_filter','tmpl_custom_fields_filter_return');
add_action('tmpl_custom_fields_post_type','tmpl_custom_fields_post_type_return');

/* pass blank post type when get_option('tevolution_custom_post_type'); is used to get post types */
add_filter('tevolution_custom_post_type','tevolution_custom_post_type_return');
add_action('tmpl_before_author_page_posttype_tab','tmpl_before_author_page_posttype_tab_return');

function tmpl_custom_fields_filter_return($post_type){
	if(($key = array_search(CUSTOM_POST_TYPE_LISTING, $post_type)) !== false) {
		unset($post_type[$key]);
	}
	return $post_type;
}

function tevolution_custom_post_type_return($post_types){
	
	unset($post_types[CUSTOM_POST_TYPE_LISTING]);
	
	return $post_types;
}

/* Add the listing tab FIRST in Manage custom fields section bacikend */
function tmpl_custom_fields_post_type_return(){
	global $wp_query;		
	/* get the submit form page using post type wise */
	$args=array('s'=>'submit_form','post_type'=>'page','posts_per_page'=>-1,
				'meta_query'     => array('relation' => 'AND',
						   array('key' => 'submit_post_type','value' => CUSTOM_POST_TYPE_LISTING,'compare' => '='),
						   array('key' => 'is_tevolution_submit_form','value' => '1','compare' => '=')
						),
				);
	$post_query = new WP_Query($args);
	
	$obj = get_post_type_object( CUSTOM_POST_TYPE_LISTING);			
	$submit_link='';
	if($post_query->have_posts()){
		while ($post_query->have_posts()) { $post_query->the_post();
			$submit_link='<a href="'.get_permalink().'" target="_blank" class="view_frm_link"><small>'.__(' View Form',ADMINDOMAIN).'</small></a>';
		}
	}
	if((isset($_REQUEST['post_type_fields']) && $_REQUEST['post_type_fields']=='listing') || $_REQUEST['post_type_fields']==''){ $class="current"; }else{ $class=""; }
	?>
	<li><a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=custom_setup&amp;ctab=custom_fields&amp;post_type_fields=<?php echo CUSTOM_POST_TYPE_LISTING; ?>" class="<?php echo $class; ?>"><?php echo $obj->labels->singular_name; ?></a>(<?php echo $submit_link; ?>) </li>
<?php
}

/* return the listing tab first in author page */

function tmpl_before_author_page_posttype_tab_return(){
	global $current_user,$wp_query,$curauth,$wpdb;

	if(!isset($_REQUEST['custom_post']) && $_REQUEST['custom_post']==''){
		$_REQUEST['custom_post'] = CUSTOM_POST_TYPE_LISTING;		
	}	
	
	/* get current author informations - specially when logged out */
	$qvar = $wp_query->query_vars;
	$authname = $qvar['author_name'];
	$qvar = $wp_query->query_vars;

	$author = $qvar['author'];


	if(isset($author) && $author !='') :

		$curauth = get_userdata($qvar['author']);

	else :

		$curauth = get_userdata(intval($_REQUEST['author']));

	endif;

	$author_link=apply_filters('templ_login_widget_dashboardlink_filter',get_author_posts_url($curauth->ID));
	if(strpos($author_link, "?"))
		$author_link=apply_filters('templ_login_widget_dashboardlink_filter',get_author_posts_url($curauth->ID))."&";
	else
		$author_link=apply_filters('templ_login_widget_dashboardlink_filter',get_author_posts_url($curauth->ID))."?";
	
	
	$obj = get_post_type_object( CUSTOM_POST_TYPE_LISTING);
	$active_tab=(isset($_REQUEST['custom_post']) && CUSTOM_POST_TYPE_LISTING == $_REQUEST['custom_post']) ?'active':''; ?>
	<li class="tab-title <?php echo $active_tab;?>" role="presentational"><a href="<?php echo $author_link;?>custom_post=<?php  echo CUSTOM_POST_TYPE_LISTING;?>" ><?php  echo $obj->labels->singular_name; ?></a></li>           
	<?php	
}

/* Display the listing post type first on home page map */
add_action('tmpl_before_map_post_type','tmpl_homepage_map_add_listing');
function tmpl_homepage_map_add_listing($post_info){
	
	global $city_category_id;
	if(in_array(CUSTOM_POST_TYPE_LISTING,$post_info)){		
		
		/* To Display the listing post type first on home page map */
		$tevolution_all_post= get_option('templatic_custom_post');	
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => CUSTOM_POST_TYPE_LISTING,'public'   => true, '_builtin' => true ));	
		?>
		<div class="mw_cat_title">
			<label><input type="checkbox" data-category="<?php echo str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING).'categories';?>" onclick="newgooglemap_initialize(this,'');"  value="<?php echo str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING);?>"  <?php if(!empty($_POST['posttype']) && !in_array(str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING) ,$_POST['posttype'])):?> <?php else:?> checked="checked" <?php endif;?> class="<?php echo str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING).'custom_categories';?>" id="<?php echo str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING).'custom_categories';?>" name="posttype[]"> <?php echo ($tevolution_all_post[CUSTOM_POST_TYPE_LISTING]['label'])? $tevolution_all_post[CUSTOM_POST_TYPE_LISTING]['label']: ucfirst(CUSTOM_POST_TYPE_LISTING);?></label><span id='<?php echo CUSTOM_POST_TYPE_LISTING.'_toggle';?>' class="toggle_post_type toggleon" onclick="custom_post_type_taxonomy('<?php echo CUSTOM_POST_TYPE_LISTING.'categories';?>',this)"></span>
		</div>
	
		 <div class="custom_categories <?php echo str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING).'custom_categories';?>" id="<?php echo str_replace("&",'&amp;',CUSTOM_POST_TYPE_LISTING).'categories';?>" >
			 <?php homepage_map_wp_terms_checklist(0, array( 'taxonomy' =>$taxonomies[0],'post_type'=>CUSTOM_POST_TYPE_LISTING,'selected_cats'=>$city_category_id) );?>
		 </div>
		<?php
	}
}
?>