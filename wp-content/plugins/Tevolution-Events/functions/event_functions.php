<?php
/* Events manager common functions - event_functions.php*/

include_once(TEVOLUTION_EVENT_DIR.'functions/event_category_customfield.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/event_filters.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/event_manager_shortcodes.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/events_manage_tickets.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/event_page_templates.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/event_listing_functions.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/events_attend.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/event_user_attend.php');
include_once(TEVOLUTION_EVENT_DIR.'functions/event_single_functions.php');
add_action( 'init', 'event_manager_image_sizes' );
if(!defined('ADD_FAVOURITE_TEXT'))
	define('ADD_FAVOURITE_TEXT',__('Add to favorites',EDOMAIN));
	
if(!defined('REMOVE_FAVOURITE_TEXT'))
	define('REMOVE_FAVOURITE_TEXT',__('Added',EDOMAIN));

/* Add event css above the directory plugin */
add_action('wp_enqueue_scripts','tmpl_add_eventplugin_css',5); // to call the css on top

function tmpl_add_eventplugin_css(){
	/* Directory Plug-in Style Sheet File In Desktop view only  */	
	if ( !tmpl_wp_is_mobile()) {
		wp_enqueue_style( 'event-manager-style', TEVOLUTION_EVENT_URL."css/event.css" );
	}
}
/*
	Add the different images size for event pages and widgets
*/
function event_manager_image_sizes()
{
	add_image_size( 'event-listing-image', 250, 165, true );
	add_image_size( 'event-single-image', 855, 570, true );	
	
	add_filter('tevolution_login_redirect','event_login_redirect'); // tevolution login redirect link
	remove_filter('the_content','view_sharing_buttons');
	remove_filter( 'the_content', 'view_count' );
	remove_action('tmpl_before_comments','single_post_categories_tags');
	// Register widgetized areas
	if ( function_exists('register_sidebar') )
	{
		register_sidebars(1,array('id' => 'after_ecategory_header', 'name' => __('Event Category Pages - Below Header',EDOMAIN), 'description' => __('Widgets placed here appear on the event category page below the header part.',EDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));		
			
	}
	remove_filter('the_content','view_sharing_buttons');
}
add_action('wp_head', 'event_manager_style',true);  

/* Event Manager Style Sheet On single Page, Listing, Category Page, Tag page And Event Search page */	

function event_manager_style()
{
	global $pagenow,$post,$wp_query;
	if(is_single() && get_post_type()==CUSTOM_POST_TYPE_EVENT && $post->post_parent!=0){		
		echo '<link rel="canonical" href="'.get_permalink($post->post_parent).'" />';
	}
	
}

/*Add action wp_footer for add to event manager  script & style file */

add_action('wp_footer', 'event_manager_script_style'); 
function event_manager_script_style()
{
	global $pagenow,$post,$wp_query;
	if(is_archive() && get_post_type()==CUSTOM_POST_TYPE_EVENT){
		wp_enqueue_script('event-script', TEVOLUTION_EVENT_URL.'js/event_script.js');
	}
	 	 
	 if(((is_single() || is_singular() || is_tax() || is_archive() ) && get_post_type()==CUSTOM_POST_TYPE_EVENT) || ( is_home() || is_front_page())){?>
	 <script type="text/javascript"> 		
		function addTo_AttendEvent(post_id,action,st_date,end_date)
		{		
			<?php 
			global $current_user;
			if($current_user->ID !=''){ 

			?>var data;if(action=="add"){if(st_date=="undefined"||st_date=="")data="action=event_attend&ptype=favorite&action=add&pid="+post_id+"<?php echo @$language;?>";else data="action=event_attend&ptype=favorite&action_type=add&pid="+post_id+"&st_date="+st_date+"&end_date="+end_date+"<?php echo @$language;?>"}else{if(st_date=="undefined"||st_date=="")data="action=event_attend&ptype=favorite&action=removed&pid="+post_id+"<?php echo @$language;?>";else data="action=event_attend&ptype=favorite&action_type=remove&pid="+post_id+"&st_date="+st_date+"&end_date="+end_date+"<?php echo @$language;?>"}
			
				jQuery.ajax({	
				url:ajaxUrl,
				type:'POST',			
				timeout: 20000,
				dataType: 'html',
				data:data,
				
				error: function(){
					alert("Error loading user's attending event.");
				},
				success: function(html){	
				<?php if(isset($_REQUEST['list']) && $_REQUEST['list']=='favourite')
				{ ?> document.getElementById('post_'+post_id).style.display='none';	<?php } ?>
					if(!st_date){ document.getElementById('attend_event_'+post_id).innerHTML=html; }else{ document.getElementById('attend_event_'+post_id+'-'+st_date).innerHTML=html; }
				}
			});
			return false;
			<?php } ?>
		}
	</script>
	<?php
	 }
	 if((is_single() || is_singular()) && get_post_type()==CUSTOM_POST_TYPE_EVENT){ ?>
		<script type="text/javascript">         
			jQuery(function(){jQuery("#image_gallery a").lightBox()});jQuery(".tabs").bind("tabsselect",function(e,t){if(t.panel.id=="locations_map"){google.maps.event.trigger(Demo.map,"resize");Demo.map.setCenter(Demo.map.center)}});jQuery(function(){ var n=jQuery("ul.tabs li a, .tmpl-accordion dd a").attr("href");if(n=="#locations_map"){Demo.init();}})
			jQuery(function(){jQuery("ul.tabs li a, .tmpl-accordion dd a").live('click',function(){
				var n=jQuery(this).attr("href");if(n=="#locations_map"){Demo.init();}
			})});
		</script>
	<?php
	}		
}

/* Add the vent settings link in general settings */
add_filter('templatic_general_settings_tab', 'tmpl_addlink_inmore_setting',14); 

function tmpl_addlink_inmore_setting($tabs){
	$tabs['event_settings'] = __('Event Settings',EDOMAIN);
	return $tabs;
}

/* Add the form of settings data */
add_action('templatic_general_data_event_settings','templatic_event_settngs_data');

function templatic_event_settngs_data(){
	eventmanager_post_page_setting_data();
}

/* Add action to add Event setting page When click on button */
function eventmanager_post_page_setting_data()
{
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br></div>';
	echo '<p class="tev_description">'.__('Add Event Description here',EDOMAIN).'</p>';
	if(isset($_POST['event_manager_setting_submit'])){
		if(!isset($_POST['hide_attending_event']) && $_POST['hide_attending_event'] ==''){
			$_POST['hide_attending_event'] ='No';
		}
		update_option('event_manager_setting',$_POST);
		
		echo '<div id="setting-error-settings_updated" class="updated settings-error">';
		echo '<p>';
		echo '<strong>'.__('Settings saved',EDOMAIN).'</strong>';
		echo '</p>';
		echo '</div>';
	}
	
	do_action('tmpl_before_eventsettings_form');
	$event_manager_setting=get_option('event_manager_setting');
		?>
	<form name="" action="" method="post">
	<table class="form-table">		
		<?php do_action('tmpl_before_default_tab'); ?>
		<tr>
			<th><label><?php echo __('Select default tab',EDOMAIN); ?></label></th>
			<td>
				<div class="element">
					 <div class="input_wrap">
						<?php $templatic_current_tab =  @$event_manager_setting['templatic-current_tab']; ?>
					  <select id="templatic-current_tab" name="templatic-current_tab" style="vertical-align:top;width:200px;" >
						<option value=""><?php  echo __('Please select default tab',EDOMAIN);  ?></option>
						<option value="past" <?php if($templatic_current_tab == 'past' ) { echo "selected=selected";  } ?>><?php echo __('Past',EDOMAIN); ?></option>
                        <option value="current" <?php if($templatic_current_tab == 'current' ) { echo "selected=selected";  } ?>><?php echo __('Current',EDOMAIN); ?></option>
						<option value="upcoming" <?php if($templatic_current_tab == 'upcoming' ) { echo "selected=selected";  } ?>><?php echo __('Upcoming',EDOMAIN); ?></option>
					</select> 
				</div>
				</div>
			   <label for="ilc_tag_class"><p class="description"><?php echo __('The selected tab will show on all event category and tag pages.',EDOMAIN);?></p></label>
			</td>
		</tr>
		<?php do_action('tmpl_after_default_tab'); ?>
        <tr>
			<th><label><?php echo __('Attending events feature',EDOMAIN);?></label></th>
			<td valign="top">
				<?php
					$hide_attending_event = get_option('event_manager_setting');
					if(isset($hide_attending_event['hide_attending_event']) && $hide_attending_event['hide_attending_event'] == 'yes'){$checked='checked="checked"';} else {$checked = '';}
					?>
					<label for="hide_attending_event">
					<input type="checkbox" value="yes" name="hide_attending_event" <?php echo $checked; ?> id="hide_attending_event" />&nbsp;<?php echo __('Enable',EDOMAIN);?></label>
			</td>
		</tr>
		<?php do_action('tmpl_after_attending_event_fld'); ?>
		<tr>
			<th><label><?php echo __('Select the people attending page',EDOMAIN);?></label></th>
			<td>
				<?php $pages = get_pages();?>
				<select id="event_user_attend_list" name="event_user_attend_list">
				<?php
				if($pages) :
					$select_page=$event_manager_setting['event_user_attend_list'];
					foreach ( $pages as $page ) {
						$selected=($select_page==$page->ID)?'selected="selected"':'';
						$option = '<option value="' . $page->ID . '" ' . $selected . '>';
						$option .= $page->post_title;
						$option .= '</option>';
						echo $option;
					}
				else :
					echo '<option>' . __('No pages found', 'edd') . '</option>';
				endif;
				?>
				</select>
                    <p class="description"><?php echo __('This page should have been created while installing the Event Manager plugin. To create it manually go to Pages -> Add New and insert the following shortcode: [event-user-attend-list]',EDOMAIN);?></p>
			</td>
		</tr>
		<?php do_action('tmpl_after_attending_event_user_fld'); ?>
		<tr>
			<th><label><?php echo __('Show Facebook events on author pages',EDOMAIN);?></label></th>
			<td valign="top">
				<?php
					$show_facebook_event = get_option('event_manager_setting');
					if(isset($show_facebook_event['show_facebook_event']) && $show_facebook_event['show_facebook_event'] == 'yes'){$checked='checked="checked"';} else {$checked = '';}
					?>
					<label for="show_facebook_event">
					<input type="checkbox" value="yes" name="show_facebook_event" <?php echo $checked; ?> id="show_facebook_event" />&nbsp;<?php _e('Enable',EDOMAIN);?></label>
					<p class="description"><?php echo __("For details on setting up Facebook events visit the",EDOMAIN);?> <a href="http://templatic.com/docs/tevolution-events-plugin-guide/#facebookevent" target="_blank"><?php echo __("documentation guide",EDOMAIN);?></a></p>
			</td>
		</tr>
		<?php do_action('tmpl_after_facebook_event_fld'); ?>
		<tr>
			<th><label><?php echo __('Hide past events',EDOMAIN);?></label></th>
			<td valign="top">
				<?php
					$hide_past_event = get_option('event_manager_setting');
					if(isset($hide_past_event['hide_past_event']) && $hide_past_event['hide_past_event'] == 'yes'){$checked='checked="checked"';} else {$checked = '';}
					?>
					<label for="hide_past_event">
					<input type="checkbox" value="yes" name="hide_past_event" <?php echo $checked; ?> id="hide_past_event" />&nbsp;<?php _e('Enable',EDOMAIN);?></label>
					<p class="description"><?php echo __("Choosing &quot;Enable&quot; will change the status of your past events from &quot;Published&quot; to &quot;Draft&quot;. The &quot;Past Events&quot; tab on event category pages will be hidden as well.",EDOMAIN);?></p>
			</td>
		</tr>
		<?php do_action('tmpl_after_past_event_fld'); ?>
	</table>	 
	<p class="submit">
	  <input id="submit" class="button button-primary button-hero" type="submit" value="<?php _e('Save Changes',EDOMAIN);?>" name="event_manager_setting_submit">
	</p>
     </form>
	<?php
	do_action('tmpl_after_eventsettings_form');
		
}
/*
	Add the class name in body
 */
add_filter('body_class','event_manager_body_class',11,2);
function event_manager_body_class($classes,$class){
	
	if ( is_front_page() )
		$classes[] = 'tevolution-event-manager event-front-page';
	elseif ( is_home() )
		$classes[] = 'tevolution-event-manager event-home';
	elseif ( is_single() && get_post_type()==CUSTOM_POST_TYPE_EVENT )
		$classes[] = 'tevolution-event-manager event-single-page';
	elseif ( is_page() )
		$classes[] = 'tevolution-event-manager event-page';	
	elseif ( is_tax() )
		$classes[] = 'tevolution-event-manager ecent-taxonomy-page';
	elseif ( is_tag() )
		$classes[] = 'tevolution-event-manager event-tag-page';
	elseif ( is_date() )
		$classes[] = 'tevolution-event-manager event-date-page';
	elseif ( is_author() )
		$classes[] = 'tevolution-event-manager event-author-page';
	elseif ( is_search() )
		$classes[] = 'tevolution-event-manager event-search-page';
	elseif ( is_post_type_archive() )
		$classes[] = 'tevolution-event-manager event-post-type-page';
	elseif((isset($_REQUEST['page']) && $_REQUEST['page'] == "preview")  && isset($_POST['cur_post_type']) && $_POST['cur_post_type']==CUSTOM_POST_TYPE_EVENT){
		$classes[] = 'tevolution-event-manager event-single-page';
	}
		
	return $classes;
}
/*
 * Sorting option of category page - related to events
 */
add_action('taxonomy_sorting_option','event_taxonomy_sorting_option');
function event_taxonomy_sorting_option(){
	$tmpdata = get_option('templatic_settings');	
	?>
      <label for="stdate_low_high"><input type="checkbox" id="stdate_low_high" name="sorting_option[]" value="stdate_low_high" <?php if(!empty($tmpdata['sorting_option']) && in_array('stdate_low_high',$tmpdata['sorting_option'])) echo 'checked';?>/>&nbsp;<?php _e('Start Date Ascending (Only for event post type)',EDOMAIN);?></label><br />
                    <label for="stdate_high_low"><input type="checkbox" id="stdate_high_low" name="sorting_option[]" value="stdate_high_low" <?php if(!empty($tmpdata['sorting_option']) && in_array('stdate_high_low',$tmpdata['sorting_option'])) echo 'checked';?>/>&nbsp;<?php _e('Start Date Descending (Only for event post type)',EDOMAIN);?></label><br />
     <?php
}
/* Return  Login redirect Link
*/
function event_login_redirect($url){
	
	if(isset($_REQUEST['redirect_to']) && $_REQUEST['redirect_to']!=''){
		$url=$_REQUEST['redirect_to']	;
	}
	return $url;
}
/*
 * Function Name: directory_listing_search
 * Return: mile range wise search
 *
 */
add_action('wp_ajax_nopriv_event_search','event_listing_search');
add_action('wp_ajax_event_search','event_listing_search');
function event_listing_search(){
	global $wp_query,$wpdb,$current_cityinfo;
	
	$per_page=get_option('posts_per_page');
	$args=array(
			 'post_type'      => 'event',
			 'posts_per_page' => $per_page,
			 'post_status'    => 'publish',
			 );
	
	event_manager_listing_custom_field();
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		add_filter('posts_where', 'wpml_milewise_search_language',20);
	}
	add_filter( 'posts_where', 'event_listing_search_posts_where', 10, 2 );
	add_filter('posts_where', 'event_manager_posts_where');
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		add_filter('posts_where', 'location_multicity_where',20);
	}
	
	$post_details= new WP_Query($args);	
	
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		remove_filter('posts_where', 'location_multicity_where');
	}
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		remove_filter('posts_where', 'wpml_milewise_search_language');
	}
	
	if ($post_details->have_posts()) :
		while ( $post_details->have_posts() ) : $post_details->the_post();
		
		if(isset($_REQUEST['page_type'])=='archive'){			
			event_archive_search_listing($wp_query);
		}elseif(isset($_REQUEST['page_type'])=='taxonomy'){
			event_taxonomy_search_listing($wp_query);
		}
		endwhile;
		wp_reset_query();
	else:
	?>
    <p class='nodata_msg'><?php _e( 'Apologies, but no results were found for the requested archive.', EDOMAIN ); ?></p>              
    <?php
	endif;
	exit;
}
/*
Function Name : event_archive_search_listing
Description : Event archive page (like search or else ) listing page - fetch entries when AJAX seach used ( in search near by miles in range )
*/
function event_archive_search_listing($wp_query){
	global $post,$wp_query;	
	$wp_query->set('is_ajax_archive',1);
	
	do_action('event_before_post_loop');
	
	$featured=get_post_meta(get_the_ID(),'featured_c',true);
	$classes=($featured=='c')?'featured_c':'';
	?>
    <div class="post <?php echo $classes;?>">  
		<?php do_action('event_before_archive_image');           /*do_action before the post image */?>
          
          <?php do_action('event_archive_page_image');?>  
          
          <?php do_action('event_after_archive_image');           /*do action after the post image */?> 
          <div class="entry"> 
               <!--start post type title -->
               <?php do_action('event_before_post_title');         /* do action for before the post title.*/ ?>
               
               <div class="event-title">
               
               <?php do_action('templ_post_title');                /* do action for display the single post title */?>
               
               </div>
               
               <?php do_action('event_after_post_title');          /* do action for after the post title.*/?>
               <!--end post type title -->
               <?php do_action('event_post_info');                 /*do action for display the post info */ ?>     
               
               
               <!--Start Post Content -->
               <?php do_action('event_before_post_content');       /* do action for before the post content. */ 
			   
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
               ?>
               
               <?php do_action('event_after_post_content');        /* do action for after the post content. */?>
               <!-- End Post Content -->
               
               <!-- Show custom fields where show on listing = yes -->
               <?php do_action('event_listing_custom_field');/*add action for display the listing page custom field */?>
               
               <?php do_action('templ_the_taxonomies');   ?> 
               
                <?php do_action('event_after_taxonomies');?>
          </div>
	</div>
     <?php do_action('event_after_post_loop');
}
/*
	Event category listing page - It will return the html of each event entry when we use any filter on category page ( like miles range and others )
*/
function event_taxonomy_search_listing($wp_query){
	global $post,$wp_query;	
	$wp_query->set('is_ajax_archive',1);
	
	do_action('event_before_post_loop');
	$featured=get_post_meta(get_the_ID(),'featured_c',true);
	$classes=($featured=='c')?'featured_c':'';
	?>
                         
    <div class="post <?php echo $classes;?>" >  
			<?php   /*do_action before the post image */
			do_action('event_before_category_page_image');         
		
			do_action('event_category_page_image');
		  
			/*do action after the post image */
			do_action('event_after_category_page_image'); ?> 
			<div class="entry"> 
               <!--start post type title -->
               <?php	 
				/* do action for before the post title.*/
				do_action('event_before_post_title');         ?>
               
				<div class="event-title">

				   <?php
					/* do action for display the single post title */
					do_action('templ_post_title'); ?>

				</div>
				<?php 
				/* do action for after the post title.*/
				do_action('event_after_post_title');
				
				/*do action for display the post info */
			    do_action('event_post_info');                 ?>     
               
               
				<!--Start Post Content -->
				<?php  /* do action for before the post content. */ 
				do_action('event_before_post_content');      

				do_action('templ_taxonomy_content');	

				/* do action for after the post content. */
				do_action('event_after_post_content');       ?>
				<!-- End Post Content -->
               
               <!-- Show custom fields where show on listing = yes -->
				<?php 
				/*add action for display the listing page custom field */
				do_action('event_listing_custom_field');

				do_action('templ_the_taxonomies');

				do_action('event_after_taxonomies');
				?>
          </div>
    </div>
    <?php do_action('event_after_post_loop');
}

/*
	This will return the edit link onj author page 
*/
add_action('event_edit_link','event_edit_link'); // show edit link on author page
function event_edit_link() {
	$post_type = get_post_type_object( get_post_type() );
	if ( !current_user_can( $post_type->cap->edit_post, get_the_ID() ) )
		return '';
	$args = wp_parse_args( array( 'before' => '', 'after' => ' ' ), @$args );
	echo $args['before'] . '<div class="edit clearfix"><a class="post-edit-link" href="' . esc_url( get_edit_post_link( get_the_ID() ) ) . '" title="' . sprintf( esc_attr__( 'Edit %1$s', EDOMAIN ), $post_type->labels->singular_name ) . '">' . __( 'Edit', EDOMAIN ) . '</a></div>' . $args['after'];
}

/*
	Display the widgets in category page header section - but this will apply only if you are not using tempatic themes, use only addons
 */
add_action('after_ecategory_header','tmpl_after_ecategory_header');
function tmpl_after_ecategory_header(){
	
	if ( is_active_sidebar( 'tmpl_after_ecategory_header') ) : ?>
     <div id="category-map" class="category-map">
          <?php dynamic_sidebar('tmpl_after_ecategory_header'); ?>
     </div>
     <?php endif;
}


add_action('wp_head','event_manager_remove_shortcode_p_tag'); //remove extra generated <p>
/*
	On woo commerce check out page extra <P> rags generated when create it , remove that tags
*/
function event_manager_remove_shortcode_p_tag()
{
	if(is_page() && is_plugin_active('woocommerce/woocommerce.php'))
	{
		global $post;
		if($post->ID == get_option('woocommerce_cart_page_id') || $post->ID == get_option('woocommerce_checkout_page_id') || $post->ID == get_option('woocommerce_pay_page_id') || $post->ID == get_option('woocommerce_thanks_page_id') || $post->ID == get_option('woocommerce_myaccount_page_id') || $post->ID == get_option('woocommerce_edit_address_page_id') || $post->ID == get_option('woocommerce_view_order_page_id') || $post->ID == get_option('woocommerce_change_password_page_id') || $post->ID == get_option('woocommerce_logout_page_id') || $post->ID == get_option('woocommerce_lost_password_page_id') )
		{
			remove_filter( 'the_content', 'wpautop',12 );
		}
	}
}
/* Add add to favourite html for event theme */
function eventmngr_favourite_html($user_id,$post)
{
	global $current_user,$post;
	$post_id = $post->ID;
	$add_to_favorite = __('Add to favorites',EDOMAIN);
	$added = __('Added',EDOMAIN);
	if(function_exists('icl_register_string')){
		icl_register_string(EDOMAIN,'event_manager'.$add_to_favorite,$add_to_favorite);
		$add_to_favorite = icl_t(EDOMAIN,'event_manager'.$add_to_favorite,$add_to_favorite);
		icl_register_string(EDOMAIN,'event_manager'.$added,$added);
		$added = icl_t(EDOMAIN,'event_manager'.$added,$added);
	}
	$user_meta_data = get_user_meta($current_user->ID,'user_favourite_post',true);
	if($post->post_type !='post'){
		if($user_meta_data && in_array($post_id,$user_meta_data))
		{
			?>
		<li id="tmplfavorite_<?php echo $post_id;?>" class="fav_<?php echo $post_id;?> fav"  > <a href="javascript:void(0);" class="removefromfav" onclick="javascript:addToFavourite('<?php echo $post_id;?>','remove');"><?php echo $added;?></a>   </li>    
			<?php
		}else{
		?>
		<li id="tmplfavorite_<?php echo $post_id;?>" class="fav_<?php echo $post_id;?> fav"><a href="javascript:void(0);" class="addtofav"  onclick="javascript:addToFavourite('<?php echo $post_id;?>','add');"><?php echo $add_to_favorite;?></a></li>
		<?php } 
	}
}
add_action('tevolution_author_tab','tmpl_dashboard_attendingevents_tab',20); // to display tab 
/*
	add attending events tab
*/
function tmpl_dashboard_attendingevents_tab(){
	global $current_user,$wp_query;
	$qvar = $wp_query->query_vars;
	$author = $qvar['author'];
	if(isset($author) && $author !='') :
		$curauth = get_userdata($qvar['author']);
	else :
		$curauth = get_userdata(intval($_REQUEST['author']));
	endif;	
	if(isset($_REQUEST['sort']) && $_REQUEST['sort'] =='attending'){
		$class = 'nav-author-post-tab-active active';
		add_action('wp_footer','author_page_attending_event_tab');
	}else{
		$class ='';
	}
	$event_manager_setting = get_option('event_manager_setting');
	if($current_user->ID && !isset($event_manager_setting['hide_attending_event']) && $event_manager_setting['hide_attending_event'] != 'yes' && $curauth->ID == $current_user->ID){
		echo "<li role='presentational' class='tab-title ".$class."'><a class='author_post_tab' href='".esc_url(get_author_posts_url($current_user->ID).'?sort=attending&custom_post=event')."'>".esc_html(__('Attending Events',EDOMAIN))."</a></li>";	
	}
}
/*
Function Name : author_page_attending_event_tab
Description : Add attending events tab on author page
*/
function author_page_attending_event_tab(){
	?>
	<script type="text/javascript">
     jQuery(document).ready(function(){	
         <?php if(isset($_REQUEST['sort']) && $_REQUEST['sort'] =='attending'):?>
          if(jQuery('.author_custom_post_wrapper ul li a').hasClass('nav-author-post-tab-active'))
          {
               jQuery('.author_custom_post_wrapper ul li a').removeClass('nav-author-post-tab-active');
               jQuery('.author_custom_post_wrapper ul li a[href*="sort=attending&custom_post=event"]').attr('href', function() {					    
                        jQuery('.author_custom_post_wrapper ul li a[href*="sort=attending&custom_post=event"]').addClass('nav-author-post-tab-active');					    
               });
          }
     	<?php endif;?>
		
		<?php if(isset($_REQUEST['fb_event']) && $_REQUEST['fb_event'] =='facebook_event'):?>
          if(jQuery('.author_custom_post_wrapper ul li a').hasClass('nav-author-post-tab-active'))
          {
               jQuery('.author_custom_post_wrapper ul li a').removeClass('nav-author-post-tab-active');
               jQuery('.author_custom_post_wrapper ul li a[href*="fb_event=facebook_event"]').attr('href', function() {					    
                        jQuery('.author_custom_post_wrapper ul li a[href*="fb_event=facebook_event"]').addClass('nav-author-post-tab-active');					    
               });
          }
     	<?php endif;?>
     });
     </script>
     <?php
}
$show_facebook_event = get_option('event_manager_setting');
if(isset($show_facebook_event['show_facebook_event']) && $show_facebook_event['show_facebook_event'] == 'yes'){
	add_action('tevolution_author_tab','tmpl_dashboard_facebook_event_tab',21); // to display tab 
}
/*
Function Name : tmpl_dashboard_facebook_event_tab
Description : add facebook events tab on author dashboard
*/
function tmpl_dashboard_facebook_event_tab(){
	global $current_user,$wp_query;
	if(isset($_REQUEST['fb_event']) && $_REQUEST['fb_event'] =='facebook_event'){
		$class = 'nav-author-post-tab-active active';
		add_action('wp_footer','author_page_attending_event_tab');
	}else{
		$class ='';
	}
	
	$qvar = $wp_query->query_vars;

	$author = $qvar['author'];


	if(isset($author) && $author !='') :

		$curauth = get_userdata($qvar['author']);

	else :

		$curauth = get_userdata(intval($_REQUEST['author']));

	endif;
	
	$author_link=get_author_posts_url($curauth->ID);
	if(strpos($author_link, "?"))
		$author_link=get_author_posts_url($curauth->ID)."&";
	else
		$author_link=get_author_posts_url($curauth->ID)."?";
	
	echo "<li role='presentational' class='tab-title ".$class."'><a class='author_post_tab' href='".$author_link."fb_event=facebook_event&custom_post=fb-event'>".esc_html(__('Facebook Events',EDOMAIN))."</a></li>";
	
}
/*
Function Name : fetch_facebook_event_html
Description : return the facebook event html
*/
add_action( 'before_loop_archive','fetch_facebook_event_html' );
function fetch_facebook_event_html()
{
	global $current_user,$curauth,$wp_query;
	$user_id = get_query_var('author');
	$curauth = get_userdata($user_id);
	
	define('SHOW_FACEBOOK_SETTING',__('Add Facebook event',EDOMAIN));
	define('HIDE_FACEBOOK_SETTING',__('Hide Options',EDOMAIN));
	define('FACEBOOK_EVENT_TEXT',__('Facebook Events',EDOMAIN));
	define('NO_FACEBOOK_EVENT',__('Author has not shared any Facebook events yet.',EDOMAIN));
	if(isset($_REQUEST['fb_event']) && $_REQUEST['fb_event']=='facebook_event'):
		if(_iscurlinstalled() && $current_user->ID == $curauth->ID){
			?>
			<div class="setting_tab">
			<button id="hide_fb_fields" class="reverse" style="<?php if(get_user_meta($curauth->ID,'appID',true) != ''){?> display:none !important; <?php } else { ?> <?php } ?>" onclick="return showFacebookSetting('hide_facebook_setting');" >
				<?php echo HIDE_FACEBOOK_SETTING; ?>
			</button>
			<button id="edit_fb_fields" class="reverse" style="<?php if(get_user_meta($curauth->ID,'appID',true) == ''){?> display:none !important; <?php } ?>" onclick="return showFacebookSetting('show_facebook_setting');">
				<?php echo SHOW_FACEBOOK_SETTING; ?>
			</button>
			</div>
			<div id="show_api_fields" <?php if(get_user_meta($curauth->ID,'appID',true)){?> style="display:none;" <?php } ?>>
				<div class="form_row">
					<label for="appid"> <?php _e('AppID',EDOMAIN); ?> : </label>
					<input type="text" name="appid" id="appid" value="<?php echo get_user_meta($curauth->ID,'appID',true); ?>" />
				</div>
				<div class="form_row">
					<label for="secretid"> <?php _e('Secret ID',EDOMAIN); ?> : </label>
					<input type="text" name="secret_id" id="secret_id" value="<?php echo get_user_meta($curauth->ID,'secret',true); ?>" />
				</div>
				<div class="form_row">
					<label for="pageid"><?php _e('Page ID',EDOMAIN); ?> : </label>
					 <input type="text" name="page_id" id="page_id" value="<?php echo get_user_meta($curauth->ID,'pageID',true); ?>"/>
				</div>
				<div class="form_row">
					<input type="submit" name="submit" id="submit" value="<?php _e('Submit',EDOMAIN); ?>"  />
				</div>                        	  	
			</div><?php	
		}
		?>
		<div id="responsecontainer">
		  <?php  $appID = get_user_meta($curauth->ID,'appID',true);
				 $secret_id = get_user_meta($curauth->ID,'secret',true);
				 $page_id = get_user_meta($curauth->ID,'pageID',true);
			if(_iscurlinstalled())
			{
				if($appID)
				{
				 echo facebook_events_template($appID,$secret_id,$page_id); 
				}
				 else { ?>
					<p class="message" ><?php echo NO_FACEBOOK_EVENT;?> </p> <?php
				}
			}else{
				echo '<p class="error">';
			    _e('CURL is not installed on your server, please enbale CURL to use Facebook evenst API.',EDOMAIN);
			    echo '<p>';
			}?>
		</div>                    
	<?php endif;// check request list facebook_event not
}
/* sorting options for attending events */
if(isset($_REQUEST['sort']) && $_REQUEST['sort'] =='attending'){
	global $current_user,$curauth,$wp_query,$paged;
		add_filter('posts_join','event_user_attending_posts_join'); 
		add_filter('posts_where','event_user_attending_list',11); // to apply filter where - filter event
		add_filter('posts_orderby','searching_filter_orderby',11); // to apply filter order by - filter event of order by start date
}

/*
 * Function Name: event_user_attending_posts_join
 * Return: display attending event list in autor page using wpml
 */
function event_user_attending_posts_join($join){
	global $wpdb, $pagenow, $wp_taxonomies,$ljoin;
	$language_where='';	
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		$language = ICL_LANGUAGE_CODE;
		$join .= " {$ljoin} JOIN {$wpdb->prefix}icl_translations t1 ON {$wpdb->posts}.ID = t1.element_id			
			AND t1.element_type IN ('post_event') JOIN {$wpdb->prefix}icl_languages l1 ON t1.language_code=l1.code AND l1.active=1 AND t1.language_code='".$language."'";
	}
	return $join;
}

add_action('wp_ajax_nopriv_save_facebook_event_api','save_facebook_event_api');// save facebook events settings from author page
add_action('wp_ajax_save_facebook_event_api','save_facebook_event_api');
/* Function Name : save_facebook_event_api
   Description : function to save facebook events settings from author page */
function save_facebook_event_api()
{
	global $wpdb,$current_user;
	if(isset($_REQUEST['appid']) && $_REQUEST['appid'] != '')
	{
		update_user_meta($current_user->ID,'appID',$_REQUEST['appid']);
		update_user_meta($current_user->ID,'secret',$_REQUEST['secret_id']);
		update_user_meta($current_user->ID,'pageID',$_REQUEST['page_id']);
	}
	echo facebook_events_template($_REQUEST['appid'],$_REQUEST['secret_id'],$_REQUEST['page_id']);exit;
}
remove_action('for_comments','single_post_comment');
add_action('for_comments','event_post_comment');
/* call comment template on recurring event detail page */
function event_post_comment()
{
	global $post;
	 
	if($post->post_status =='publish' || $post->post_status =='recurring'){
?>
		<?php comments_template(); ?>
<?php
	}
}
add_filter('supreme_post_images','tmpl_supreme_post_images');

/* 
	Function to show the images in recurring event 
*/
function tmpl_supreme_post_images($post){
	global $post;
	$is_parent = $post->post_parent;	
	if(function_exists('bdw_get_images_plugin')){
		if($is_parent != 0){
			$post_img = bdw_get_images_plugin($is_parent,'thumb');					
			$post_image = @$post_img[0]['file'];
		}else{
			$post_img = bdw_get_images_plugin($post->ID,'thumb');					
			$post_image = @$post_img[0]['file'];					
		}
		if(!$post_image)
		{
			$post_image = TEVOLUTION_EVENT_URL.'images/noimage-220x150.jpg';
		}
		return $post_image;
	}
}
add_action('close_entryevent','show_event_favourite_html'); // show favourites on author page
/* function to show the html of event page*/
function show_event_favourite_html(){
	global $post,$current_user;

	echo '<div class="rev_pin">';
		echo '<ul>';
	if(function_exists('eventmngr_favourite_html'))
		{
			$user_id = get_query_var('author');
			eventmngr_favourite_html($user_id,$post);
		}
		$post_id=$post->ID;
		$comment_count= count(get_comments(array('post_id' => $post_id)));
		$review=($comment_count <=1 )? __('review',EDOMAIN):__('reviews',EDOMAIN);
		$review= apply_filters('tev_review_text',$review);
		?>
			<li class="review"> <?php echo '<a id="reviews_show" href="'.get_permalink($post_id).'#comments">'.$comment_count.' '.$review.'</a>';?></li>
		<?php
		echo '</ul>';
	echo '</div>';
		
}

add_action('tevolution_title_text','tevolution_title_text_display',11);
/*
	Display the "recurrences" tags beside the title to show recurring event pop up
*/
function tevolution_title_text_display($post){
	global $post;
	$post_tevolution_title = $post;
	if($post->post_type =='event' && get_post_meta($post->ID,'event_type',true) =='Recurring event'){ 
		$text = __('recurrences',EDOMAIN);
		$post_id = $post->ID;
		$text = "<a data-reveal-id='tmpl_recurrence_$post_id' class='recurrence_text' id='recurrence_$post_id' href='javascript:void(0);'>".$text."</a>";
		echo $text;
		
		add_action('wp_footer','tmpl_get_recurrencies');
		wp_reset_postdata();
	}
	$post = $post_tevolution_title;
}

function tmpl_get_recurrencies($post){ global $post; 

	$post_id = $post->ID;
	?>

	<div class="reveal-modal tmpl_login_frm_data clearfix" id="tmpl_recurrence_<?php echo $post->ID; ?>" style="display:none" data-reveal>
		<h3><?php _e("Recurrence for",EDOMAIN); echo ' ';_e(stripslashes($post->post_title),EDOMAIN); ?></h3>
		<a class="modal_close" href="javascript:;"></a>
		<div>
		<?php
			$event_manager_setting = get_option('event_manager_setting');
			if($event_manager_setting['hide_attending_event'] == 'yes')
			{
				echo '<div class="recurring_event_class" >'.hide_attend_recurrence_event($post->ID).'</div>';
			}
			else
			{
				echo attend_recurrence_event($post->ID);
			} ?>
		</div>
	</div>
	<script type="text/javascript">
		if(jQuery("#tmpl_recurrence_<?php echo $post_id; ?>")){
			jQuery("#tmpl_recurrence_<?php echo $post_id; ?> .modal_close").click(function(){
				jQuery('#tmpl_recurrence_<?php echo $post_id; ?>').attr('style','');
				jQuery('.reveal-modal-bg').css('display','none');
				jQuery('#tmpl_recurrence_<?php echo $post_id; ?>').removeClass('open'); });
		
			jQuery(".reveal-modal-bg").click(function(){ 
			jQuery('.reveal-modal').attr('style',''); 
			jQuery('.reveal-modal-bg').css('display','none');
			jQuery('.eveal-modal').removeClass('open'); });
		}
	</script>

<?php
}
/*
	Return the list of events after click on date in calendar 
*/
function event_calendar_list($atts)
{
	global $wp_query,$wpdb,$paged,$post,$current_cityinfo;
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$cal_date = @$_REQUEST['cal_date'];
	if(isset($_REQUEST['cal_date']) &&  $_REQUEST['cal_date'] !='')
	{
		$py = substr($cal_date,0,4);
		$pm = substr($cal_date,4,2);
		$pd = substr($cal_date,6,2);
		$the_req_date = "$py-$pm-$pd";
	}
	else
	{
		$the_req_date = date('Y-m-d');
	}
	
	$posts_per_page=get_option('posts_per_page');
	register_post_status( 'recurring' );
	$args=
		array( 'post_type' => 'event',
			'posts_per_page' => $posts_per_page,
			'paged'=>$paged,
			'post_status' => array('recurring','publish'),
			'meta_key' => 'st_date',
			'orderby' => 'meta_value',
			'order' => 'ASC',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'st_date',
					'value' => $the_req_date,
					'compare' => '<=',
					'type' => 'DATE'
				),
				array(
					'key' => 'end_date',
					'value' => $the_req_date,
					'compare' => '>=',
					'type' => 'DATE'
				),
				array(
					'key' => 'event_type',
					'value' => 'Regular event',
					'compare' => '=',
					'type'=> 'text'
				)				
			)
		);
	$location_post_type = get_option('location_post_type');
	
	if(is_array($location_post_type) && count($location_post_type) >1){
		$location_post_type = implode(',',$location_post_type);
	}else{
		$location_post_type = $location_post_type[0];
	}
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php')  && strstr($location_post_type,'event,'))
	{
		add_filter('posts_where', 'location_multicity_where');
	}
	
	$my_query1 = null;
	$my_query1 = new WP_Query($args);
	$wp_query = $my_query1;
	
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php')  && strstr($location_post_type,'event,'))
	{
		remove_filter('posts_where', 'location_multicity_where');
	} ?>
	<div id="loop_event_archive" class="list">
     	<?php if( $my_query1->have_posts() ) : 
					while ($my_query1->have_posts()) : $my_query1->the_post(); ?>	
                    	<div class="post <?php templ_post_class();?>">  
                         	<?php 
							do_action('event_before_search_image');           /*do_action before the post image */
							
							do_action('event_archive_page_image');
							
							do_action('event_after_search_image');           /*do action after the post image */
							
							do_action('event_before_post_entry');?>
                         	<div class="entry"> 
                                   <!--start post type title -->
                                   <?php do_action('event_before_post_title');         /* do action for before the post title.*/ ?>
                                   
                                   <div class="event-title">
                                   
									<?php 
										do_action('templ_post_title');                /* do action for display the single post title */
									
										do_action('event_calendar_content');	?>
                                   </div>
                                   <?php do_action('event_calendar_after_content');
								   
								   do_action('event_after_post_title');          /* do action for after the post title.*/?>
                                   <!--end post type title -->
                                   <?php do_action('event_post_info');                 /*do action for display the post info */ ?>     
                                   
                                   
                                   <!--Start Post Content -->
                                   <?php do_action('event_before_post_content');       /* do action for before the post content. */ 
								   
									echo '<div itemprop="description" class="entry-summary">';
									the_excerpt();
									echo '</div>';
									
									
									do_action('event_after_post_content');        /* do action for after the post content. */?>
                                   <!-- End Post Content -->
                                   
                                   <!-- Show custom fields where show on listing = yes -->
                                   <?php 
								   
									do_action('event_listing_custom_field');/*add action for display the listing page custom field */
								   
									do_action('templ_the_taxonomies');
									
									do_action('event_calendar_after_taxnomies');?>
				   		</div>
                              <?php do_action('event_after_post_entry');?>
                        </div>
          	<?php endwhile; 
			else: ?>
			<p class='nodata_msg'><?php  _e( 'Sorry! No results were found for particular date search.',EDOMAIN); ?></p>
              
          <?php endif;?>
     
     <div id="listpagi">
          <div class="pagination pagination-position">
                <?php if(function_exists('pagenavi_plugin')) { pagenavi_plugin(); } ?>
          </div>
     </div>    	 
    <?php wp_reset_query(); ?>
      <!--End loop search page -->
</div>
	<?php
}
add_shortcode('calendar_event', 'event_calendar_list');
add_action('event_calendar_content','event_calendar_content');
/*
Function Name : event_calendar_content
Description : get content of calendar
*/
function event_calendar_content()
{
	global $wpdb,$post;
	$address=get_post_meta($post->ID,'address',true);
	$phone=get_post_meta($post->ID,'phone',true);	
	$date_formate=get_option('date_format');
	$time_formate=get_option('time_format');
	$st_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'st_date',true)));
	$end_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'end_date',true)));
	
	$date=$st_date.' '. __('To',EDOMAIN).' '.$end_date;
	
	$st_time=date_i18n($time_formate,strtotime(get_post_meta($post->ID,'st_time',true)));
	$end_time=date_i18n($time_formate,strtotime(get_post_meta($post->ID,'end_time',true)));	
	if($address){
		echo '<p class="address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.$address.'</p>';
	}
	if($date)
	{
		echo '<p class="event_date">'.'&nbsp;<span>'.$date.'</span></p>';
	}
	if($st_time || $end_time)
	{
		echo '<p class="time">'.'&nbsp;<span>'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>';
	}
}

/*
	Add Listing tab on detail page - to show the place of event in event detail page
*/
add_action('show_listing_event','show_listing_event');
function show_listing_event()
{
	global $post,$wpdb,$event_listing;
	$sql = "select $wpdb->posts.ID,$wpdb->posts.post_title from $wpdb->posts where  $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='event_for_listing' and FIND_IN_SET( ". $post->ID.", pm.meta_value ))";
	$event_listing = $wpdb->get_results($sql);
	if(count($event_listing)>0)
	{?>
		<li class="tab-title" role="presentational"><a href="#listing_event" role="tab" tabindex="4" aria-selected="false" controls="listing_event"><?php _e('Venue',EDOMAIN);?></a></li>
	<?php
	}
	
}
/*
	Add Listing list (display list when click on list ) on detail page - to show the place of event in event detail page
*/
add_action('show_listing_event_detail','show_listing_event_detail');
function show_listing_event_detail()
{
	global $event_listing;
	if(count($event_listing)>0)
	{?>
		<section role="tabpanel" aria-hidden="false" class="content" id="listing_event">
		<?php 
		   for($el=0; $el <= count($event_listing); $el++) { 
			if($event_listing[$el] !=0){ 
			$event_detail = get_post($event_listing[$el]->ID);
			if($event_detail->post_status =='publish')
			{
			?>
		<div class="listed_events clearfix"> 
			<?php $event_detail = get_post($event_listing[$el]);
			
			$post_image = bdw_get_images_plugin($event_detail->ID,'tevolution_thumbnail');
			$post_image=($post_image[0]['file'])? $post_image[0]['file'] :str_replace('/functions/','/',plugin_dir_url( __FILE__ )).'images/noimage-150x150.jpg';
			$e_id = $event_detail->ID;
			$e_title = $event_detail->post_title;
			if(get_post_meta($e_id,'address',true) !='' && get_post_meta($e_id,'address',true) !='' ){
				$address = get_post_meta($e_id,'address',true); } ?>
			<a class="event_img" href="<?php echo get_permalink($e_id); ?>"><img src="<?php echo $post_image; ?>" width="60" height="60" alt="<?php echo $e_title; ?>"/></a>
			<div class="event_detail">
				<a class="event_title" href="<?php echo get_permalink($e_id); ?>"><strong><?php echo $e_title; ?></strong></a><br/>
				<?php if($address){echo sprintf(__('Address: %s',EDOMAIN),$address);}?>
			</div>
		</div>  
		<?php } }
		} ?>
		</section>
	<?php
	}
}
/*
	Display Event informations below title 
*/
if( function_exists('tmpl_wp_is_mobile') && tmpl_wp_is_mobile()){
	/* in mobile devices */
	add_action('event_date_display','tmpl_event_date_display_mobile');
}else{
	/* in desktop */
	add_action('event_date_display','event_date_display');
}
function event_date_display()
{
	global $post,$htmlvar_name,$tmpl_flds_varname;
	$contenteditable= $is_edit='';
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'){
		$is_edit=1;
		$contenteditable='contenteditable="true"';
	}

	$htmlvar_name_date=$tmpl_flds_varname;	
	$date_formate=get_option('date_format');
	$time_formate=get_option('time_format');
	$starttime=get_post_meta($post->ID,'st_time',true);
	$endtime=get_post_meta($post->ID,'end_time',true);	
	$st_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'st_date',true)));
	$end_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'end_date',true)));
	if($starttime!='')
		$st_time=date_i18n($time_formate,strtotime($starttime));
	if($endtime!='')
		$end_time=date_i18n($time_formate,strtotime($endtime));
		
	$reg_fees= get_post_meta($post->ID,'reg_fees',true);
	
	$address=get_post_meta($post->ID,'address',true);
	$website=get_post_meta($post->ID,'website',true);
	$phone=get_post_meta($post->ID,'phone',true); 
	$email=get_post_meta($post->ID,'email',true);
	$prd_id =  get_post_meta($post->ID,'templ_event_ticket',true);	
	echo '<div class="entry-header-custom-left">';
		echo ($htmlvar_name_date['st_date'] || ($is_edit==1 && $htmlvar_name_date['st_date']))? '<p  itemprop="startDate" content="'.date('Y-m-d',strtotime($st_date)).'T'.$starttime.'" class="date '. $htmlvar_name_date["basic_inf"]["st_date"]["style_class"].'"><label>'.$htmlvar_name_date['st_date']['label'].'</strong>:&nbsp;</label><span id="frontend_date_st_date" class="frontend_st_date frontend_datepicker" '.$contenteditable.'>'.$st_date.'</span></p>' : '';		
		echo ($htmlvar_name_date['end_date'] || ($is_edit==1 && $htmlvar_name_date['end_date']))? '<p itemprop="endDate" content="'.date('Y-m-d',strtotime($end_date)).'T'.$endtime.'"  class="date '. $htmlvar_name_date["basic_inf"]["end_date"]["style_class"].'"><label>'.$htmlvar_name_date['end_date']['label'].':&nbsp;</label><span id="frontend_date_end_date" class="frontend_end_date frontend_datepicker" '.$contenteditable.'>'.$end_date.'</span></p>' : '';	
		if($is_edit==1 && $htmlvar_name_date['st_time'] && $htmlvar_name_date['end_time']){
			echo '<p class="time"><label>'.__('From:',EDOMAIN).'&nbsp;</label><span><span class="event_custom frontend_st_time" '.$contenteditable.'>'.$st_time.'</span> '.__('To',EDOMAIN).' <span class="event_custom frontend_end_time" '.$contenteditable.'>'.$end_time.'</span></span></p>';
		}else{
			echo (($htmlvar_name_date['st_time'] && $htmlvar_name_date['end_time'] &&($starttime!='' && $endtime!='') ))? '<p class="time"><label>'.__('From:',EDOMAIN).'&nbsp;</label><span class="event_custom">'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>' : '';
		}
		
		echo ($htmlvar_name_date['reg_fees'] && $reg_fees || (is_edit==1 && $htmlvar_name_date['reg_fees']))? '<p class="fees '. $htmlvar_name_date["reg_fees"]["style_class"].'"><label>'.$htmlvar_name_date['reg_fees']['label'].':&nbsp;</label><span class="frontend_reg_fees" '.$contenteditable.'>'.$reg_fees.'</span></p>' : '';
		do_action('directory_display_custom_fields_default_left');					
	echo '</div>';
	
	if($htmlvar_name_date['address'] || $htmlvar_name_date['contact_info']['phone'] || $htmlvar_name_date['contact_info']['website'] || $prd_id != ''):
	echo '<div itemprop="location" class="entry-header-custom-right">';
		echo (@$htmlvar_name_date['address'] || ($is_edit==1 && $htmlvar_name_date['address']))? '<p  itemprop="address" class="address '. @$htmlvar_name_date["address"]["style_class"].'"><label>'.$htmlvar_name_date["address"]["label"].':&nbsp;</label><span id="frontend_address" class="frontend_address" '.$contenteditable.'>'.$address.'</span></p>' : '';
		echo (@$htmlvar_name_date['phone'] && $phone || ($is_edit==1 && $htmlvar_name_date['phone']))? '<p class="phone '. @$htmlvar_name_date["phone"]["style_class"].'"><label>'.@$htmlvar_name_date["phone"]["label"].':&nbsp;</label><span class="frontend_phone" '.contenteditable.'>'.$phone.'</span></p>' : '';
		if( @$htmlvar_name_date['website'] && @$website || ($is_edit==1 && $htmlvar_name_date['website']))
		{?>	
			 <p class="website <?php echo $htmlvar_name_date["website"]["style_class"]; ?>"><a href="<?php echo $website;?>" id="website" class="frontend_website <?php if($is_edit==1):?>frontend_link<?php endif; ?>"><?php echo $htmlvar_name_date["website"]["label"];?></a></p>
		<?php
		}

		$booked_tckt_id =  get_post_meta($post->ID,'templ_event_ticket_booked',true);
		$total_tickets = get_post_meta($prd_id,'_stock',true);
		if(get_post_meta($prd_id,'_stock',true) && is_plugin_active('woocommerce/woocommerce.php'))
		{
			$event_tckt_id = "<a href=".get_permalink($prd_id).">".$total_tickets."</a>";
			echo "<p class='ticket'>";
			echo $event_tckt_id.' '; _e('tickets are available.',EDOMAIN);
			echo "<a href=".get_permalink($prd_id)." class='bookn_tab button secondary_btn tiny_btn'>".__('Book now',EDOMAIN)."</a>";
			echo "</p>";
		}
		do_action('directory_display_custom_fields_default_right');
	echo '</div>';
	endif;
}

/*
	Display Event informations below title in mobile devices
*/

function tmpl_event_date_display_mobile()
{
	global $post,$htmlvar_name,$tmpl_flds_varname;
	$contenteditable= $is_edit='';
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'){
		$is_edit=1;
		$contenteditable='contenteditable="true"';
	}

	$htmlvar_name_date=$tmpl_flds_varname;	
	$date_formate=get_option('date_format');
	$time_formate=get_option('time_format');
	$starttime=get_post_meta($post->ID,'st_time',true);
	$endtime=get_post_meta($post->ID,'end_time',true);	
	$st_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'st_date',true)));
	$end_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'end_date',true)));
	if($starttime!='')
		$st_time=date_i18n($time_formate,strtotime($starttime));
	if($endtime!='')
		$end_time=date_i18n($time_formate,strtotime($endtime));
		
	$reg_fees= get_post_meta($post->ID,'reg_fees',true);
	
	$address=get_post_meta($post->ID,'address',true);
	$website=get_post_meta($post->ID,'website',true);
	$phone=get_post_meta($post->ID,'phone',true); 
	$email=get_post_meta($post->ID,'email',true);
	$prd_id =  get_post_meta($post->ID,'templ_event_ticket',true);	
	echo '<div class="entry-header-custom-left">';
		echo ($htmlvar_name_date['st_date'] || ($is_edit==1 && $htmlvar_name_date['st_date']))? '<p  itemprop="startDate" content="'.date('Y-m-d',strtotime($st_date)).'T'.$starttime.'" class="date '. $htmlvar_name_date["basic_inf"]["st_date"]["style_class"].'"><label>'.$htmlvar_name_date['st_date']['label'].'</strong>:&nbsp;</label><span id="frontend_date_st_date" class="frontend_st_date frontend_datepicker" '.$contenteditable.'>'.$st_date.'</span></p>' : '';		
		echo ($htmlvar_name_date['end_date'] || ($is_edit==1 && $htmlvar_name_date['end_date']))? '<p itemprop="endDate" content="'.date('Y-m-d',strtotime($end_date)).'T'.$endtime.'"  class="date '. $htmlvar_name_date["basic_inf"]["end_date"]["style_class"].'"><label>'.$htmlvar_name_date['end_date']['label'].':&nbsp;</label><span id="frontend_date_end_date" class="frontend_end_date frontend_datepicker" '.$contenteditable.'>'.$end_date.'</span></p>' : '';	
		if($is_edit==1 && $htmlvar_name_date['st_time'] && $htmlvar_name_date['end_time']){
			echo '<p class="time"><label>'.__('From:',EDOMAIN).'&nbsp;</label><span><span class="event_custom frontend_st_time" '.$contenteditable.'>'.$st_time.'</span> '.__('To',EDOMAIN).' <span class="event_custom frontend_end_time" '.$contenteditable.'>'.$end_time.'</span></span></p>';
		}else{
			echo (($htmlvar_name_date['st_time'] && $htmlvar_name_date['end_time'] &&($starttime!='' && $endtime!='') ))? '<p class="time"><label>'.__('From:',EDOMAIN).'&nbsp;</label><span class="event_custom">'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>' : '';
		}
		
		echo ($htmlvar_name_date['reg_fees'] && $reg_fees || (is_edit==1 && $htmlvar_name_date['reg_fees']))? '<p class="fees '. $htmlvar_name_date["reg_fees"]["style_class"].'"><label>'.$htmlvar_name_date['reg_fees']['label'].':&nbsp;</label><span class="frontend_reg_fees" '.$contenteditable.'>'.$reg_fees.'</span></p>' : '';
		do_action('directory_display_custom_fields_default_left');					
	echo '</div>';
	
	if($htmlvar_name_date['address'] || $htmlvar_name_date['contact_info']['phone'] || $htmlvar_name_date['contact_info']['website'] || $prd_id != ''):
	echo '<div itemprop="location" class="entry-header-custom-right">';
		echo (@$htmlvar_name_date['address'] || ($is_edit==1 && $htmlvar_name_date['address']))? '<p  itemprop="address" class="address '. @$htmlvar_name_date["address"]["style_class"].'"><label>'.$htmlvar_name_date["address"]["label"].':&nbsp;</label><span id="frontend_address" class="frontend_address" '.$contenteditable.'>'.$address.'</span></p>' : '';
		
	
		$booked_tckt_id =  get_post_meta($post->ID,'templ_event_ticket_booked',true);
		$total_tickets = get_post_meta($prd_id,'_stock',true);
		if(get_post_meta($prd_id,'_stock',true) && is_plugin_active('woocommerce/woocommerce.php'))
		{
			$event_tckt_id = "<a href=".get_permalink($prd_id).">".$total_tickets."</a>";
			echo "<p class='ticket'>";
			echo $event_tckt_id.' '; _e('tickets are available.',EDOMAIN);
			echo "<a href=".get_permalink($prd_id)." class='bookn_tab button secondary_btn tiny_btn'>".__('Book now',EDOMAIN)."</a>";
			echo "</p>";
		}
		do_action('directory_display_custom_fields_default_right');
	echo '</div>';
	endif;
}
/* 
	Display the event sample csv file
*/
add_action('tevolution_event_sample_csvfile','tevolution_event_sample_csvfile');
function tevolution_event_sample_csvfile(){
	?>
     <a href="<?php echo TEVOLUTION_EVENT_URL.'functions/event_sample.csv';?>"><?php _e('(Sample csv file)',EDOMAIN);?></a>
     <?php	
}
/*
	filter for export to CSV
*/
add_action('init','event_tevolution_export_csv');
function event_tevolution_export_csv()
{ 
	//if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'bulk_upload')
	{
		add_filter('tevolution_export_csv','event_manager_tevolution_export_csv',10,2);
	}
}
/*
	Merge the Events fields in CSV while export the CSV
*/
function event_manager_tevolution_export_csv($custom_fields,$post_type)
{
	
	if($post_type=='event'){
		$custom_fields['recurrence_occurs']=array('name'=>'recurrence_occurs','ctype'=>'text');
		$custom_fields['recurrence_per']=array('name'=>'recurrence_per','ctype'=>'text');
		$custom_fields['recurrence_days']=array('name'=>'recurrence_days','ctype'=>'text');
		$custom_fields['recurrence_bydays']=array('name'=>'recurrence_bydays','ctype'=>'text');
		$custom_fields['monthly_recurrence_byweekno']=array('name'=>'monthly_recurrence_byweekno','ctype'=>'text');
		$custom_fields['recurrence_byday']=array('name'=>'recurrence_byday','ctype'=>'text');
		$custom_fields['set_st_time']=array('name'=>'set_st_time','ctype'=>'text');
		$custom_fields['set_end_time']=array('name'=>'set_end_time','ctype'=>'text');
	}
	return $custom_fields;
}
/*
	While import - save the recurring events data
*/

function save_bulk_upload_recurring_event($last_postid)
{ 
	
	if(isset($_FILES['csv_import']['tmp_name']) && $_FILES['csv_import']['tmp_name']!="")
	{
		global $session_count;
		
		if(strtolower(trim($_SESSION['data'][$session_count]['event_type'])) == strtolower(trim('Recurring event')))
		{
			update_post_meta($last_postid, 'set_st_time', trim($_SESSION['data'][$session_count]['set_st_time']));
			update_post_meta($last_postid, 'set_end_time',  trim($_SESSION['data'][$session_count]['set_end_time']));
			update_post_meta($last_postid, 'recurrence_onweekno', $_SESSION['data'][$session_count]['recurrence_onweekno']);
			update_post_meta($last_postid, 'recurrence_days', $_SESSION['data'][$session_count]['recurrence_days']);	
			update_post_meta($last_postid, 'monthly_recurrence_byweekno', $_SESSION['data'][$session_count]['monthly_recurrence_byweekno']);	
			update_post_meta($last_postid, 'recurrence_byday', $_SESSION['data'][$session_count]['recurrence_byday']);	
			update_post_meta($last_postid, 'recurrence_per', $_SESSION['data'][$session_count]['recurrence_per']);	
			update_post_meta($last_postid, 'recurrence_bydays', $_SESSION['data'][$session_count]['recurrence_bydays']);	
			update_post_meta($last_postid, 'st_date', $_SESSION['data'][$session_count]['st_date']);	
			update_post_meta($last_postid, 'end_date', $_SESSION['data'][$session_count]['end_date']);	
			update_post_meta($last_postid, 'st_time', $_SESSION['data'][$session_count]['st_time']);	
			update_post_meta($last_postid, 'end_time', $_SESSION['data'][$session_count]['end_time']);	
			update_post_meta($last_postid, 'address', $_SESSION['data'][$session_count]['address']);
			update_post_meta($last_postid, 'recurrence_occurs', $_SESSION['data'][$session_count]['recurrence_occurs']);
			bulk_upload_save_recurrence_events( $_SESSION['data'][$session_count],$last_postid);
		}
		if(strtolower(trim($_SESSION['data'][$session_count]['event_type'])) == strtolower(trim('Regular event')))
		{
			$st_date = $_SESSION['data'][$session_count]['st_date'];	
			$end_date =  $_SESSION['data'][$session_count]['end_date'];	
			$st_time = $_SESSION['data'][$session_count]['st_time'];
			$end_time = $_SESSION['data'][$session_count]['end_time'];
			
			if( isset($st_time) ){
			$count = explode(':',$st_time);
			if( count($count) > 2 ){
				$get_sttime = $st_time;
			}else{
				$get_sttime = $st_time.':00';
			}
			}else{
				$get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
			}
			if( isset($end_time) ){
				$count = explode(':',$end_time);
				if( count($count) > 2 ){
					$get_end_time = $end_time;
				}else{
					$get_end_time = $end_time.':00';
				}
			}else{
				$get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
			}
			
			$event_start_date = $st_date.' '.$get_sttime;
			$event_end_date = $end_date.' '.$get_end_time;
			update_post_meta($last_postid, 'set_st_time', $event_start_date);
			update_post_meta($last_postid, 'set_end_time', $event_end_date);
		}
	}
}
/*
	It's update other recurrences while update the events
*/	
function bulk_upload_update_rec_data($post_data,$post_id,$st_date,$end_date){
	
	global $wpdb,$post,$session_count;
	$recurring_update = $_REQUEST['recurring_update'];
	$parent_data = get_post($post_id);
	
	if(!strstr($_SERVER['REQUEST_URI'],'wp-admin'))
		update_post_meta($post_id,'tmpl_post_status',$parent_data->post_status);
	
	$parent_post_status = get_post_meta($parent_data->ID,'tmpl_post_status',true);
	$p_status = $parent_data->post_status;
	if($parent_post_status =='draft' && $p_status == 'draft'){
		$child_status = 'pending';
	}else{
		$child_status = 'recurring';
	}
	
		$post_details = array('post_title' => $post_data['templatic_post_title'],
					'post_content' => $post_data['templatic_post_content'],
					'post_status' => $child_status,
					'post_type' => 'event',
					'post_name' => str_replace(' ','-',$post_data['templatic_post_title'])."-".$st_date,
					'post_parent' => $post_id,
				  );
	
	$alive_days = get_post_meta($post_id,'alive_days',true);
	$last_rec_post_id = wp_insert_post($post_details); // insert recurrences of events 
	$tl_dummy_content = get_post_meta($post_id,'tl_dummy_content',true);
	
	$where = array( 'post_parent' => $post_id , 'post_type' => 'event' );
	$wpdb->update( $wpdb->posts, array( 'post_status' => $child_status ), $where );
	
	
	if($_SESSION['data'][$session_count]['templatic_post_category']!="")
	{
		$category_name=explode(',',$_SESSION['data'][$session_count]['templatic_post_category']);
		wp_set_object_terms($last_rec_post_id,$category_name,'ecategory');
		wp_set_object_terms($post_id,$category_name,'ecategory');
	}
	if($_SESSION['data'][$session_count]['templatic_post_tags']!="")
	{
		wp_set_post_terms($last_rec_post_id,$_SESSION['data'][$session_count]['templatic_post_tags'],'etags' );
		wp_set_post_terms($post_id,$_SESSION['data'][$session_count]['templatic_post_tags'],'etags' );
	}
	
	
	$st_time = get_post_meta($post_id,'st_time',true);
	$end_time = get_post_meta($post_id,'end_time',true);
	$post_city_id = $_SESSION['post_city_id'];
	$address = get_post_meta($post_id,'address',true);
	$geo_latitude = get_post_meta($post_id,'geo_latitude',true);
	$geo_longitude = get_post_meta($post_id,'geo_longitude',true);
	
	if( isset($st_time) ){
	$count = explode(':',$st_time);
	if( count($count) > 2 ){
		$get_sttime = $st_time;
	}else{
		$get_sttime = $st_time.':00';
	}
	}else{
		$get_sttime = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
	}
	if( isset($end_time) ){
		$count = explode(':',$end_time);
		if( count($count) > 2 ){
			$get_end_time = $end_time;
		}else{
			$get_end_time = $end_time.':00';
		}
	}else{
		$get_end_time = date_i18n('H:i:s',current_time( 'timestamp', 1 ) );
	}
	
	$event_start_date = $st_date.' '.$get_sttime;
	$event_end_date = $end_date.' '.$get_end_time;
	
	$featured_type = get_post_meta($post_id,'featured_type',true);
	$featured_h = get_post_meta($post_id,'featured_h',true);
	$featured_c = get_post_meta($post_id,'featured_c',true);
	/* add parent post valy with different date and time */
	update_post_meta($last_rec_post_id,'event_type','Regular event'); 
	update_post_meta($last_rec_post_id,'end_date',$end_date); 
	update_post_meta($last_rec_post_id,'st_date',$st_date);
	update_post_meta($last_rec_post_id,'st_time',$st_time);
	update_post_meta($last_rec_post_id,'end_time',$end_time);
	update_post_meta($last_rec_post_id,'post_city_id',$post_city_id);
	update_post_meta($last_rec_post_id,'_event_id',$post_id); 
	update_post_meta($last_rec_post_id,'address',$address); 
	update_post_meta($last_rec_post_id,'geo_latitude',$_SESSION['data'][$session_count]['geo_latitude']); 
	update_post_meta($last_rec_post_id,'geo_longitude',$_SESSION['data'][$session_count]['geo_longitude']); 
	update_post_meta($last_rec_post_id,'alive_days',$_SESSION['data'][$session_count]['alive_days']); 
	update_post_meta($last_rec_post_id,'featured_type',$_SESSION['data'][$session_count]['featured_type']); 
	update_post_meta($last_rec_post_id,'featured_h',$_SESSION['data'][$session_count]['featured_h']); 
	update_post_meta($last_rec_post_id,'featured_c',$_SESSION['data'][$session_count]['featured_c']); 
	update_post_meta($last_rec_post_id, 'set_st_time', trim($event_start_date));
	update_post_meta($last_rec_post_id, 'set_end_time', trim($event_end_date));
}
/*
	Return the recurrences of the post id pass in function
*/
function bulk_upload_save_recurrence_events($post_data,$pID)
{
	
	global $wpdb,$current_user;
	$post_id = $pID;
	
	$start_date = strtotime(get_post_meta($post_id,'st_date',true));
	$end_date = strtotime(get_post_meta($post_id,'end_date',true));
	$tmpl_end_date = strtotime(get_post_meta($post_id,'end_date',true));
    $recurrence_occurs = get_post_meta($post_id,'recurrence_occurs',true);//reoccurence type
	
	$recurrence_per = get_post_meta($post_id,'recurrence_per',true);//no. of occurence.
	$current_date = date('Y-m-d');
	$recurrence_days = get_post_meta($post_id,'recurrence_days',true);	//on which day
	$recurrence_list = "";	
	
	if($recurrence_occurs == 'daily' )
	{
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		for($i=0;$i<($days_between);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			if(($i%$recurrence_per) == 0 )
			{
				$j = $i+$recurrence_days;
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i day");
				if($recurrence_days==0):
						$recurrence_days=0;
				endif;
				
				$st_date2 = strtotime(date("Y-m-d", $st_date1) );
				$st_date = date('Y-m-d',$st_date2);
				if($recurrence_days ==1):
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
				else:
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
				endif;
				if($tmpl_end_date < strtotime($end_date)){
					$end_date = date("Y-m-d", $tmpl_end_date);
				}
				bulk_upload_update_rec_data($post_data,$post_id,$st_date,$end_date);
			}
			else
			{
				continue;
			}
		}
	}
	if($recurrence_occurs == 'weekly' )
	{ 
		$recurrence_interval = get_post_meta($post_id,'recurrence_per',true);//no. of occurence.
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$l = 0;
		$count_recurrence = 0;
		$current_week = 0;
		$recurrence_list .= "<ul>";
		
		if(strstr(get_post_meta($post_id,'recurrence_bydays',true),","))
			$recurrence_byday = explode(',',get_post_meta($post_id,'recurrence_byday',true));	//on which day
		else
			$recurrence_byday = get_post_meta($post_id,'recurrence_byday',true);	//on which day
		$start_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) );
		$end_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'end_date',true))) );
		
		//sort out week one, get starting days and then days that match time span of event (i.e. remove past events in week 1)
		$weekdays = explode(",", get_post_meta($post_id,'recurrence_bydays',true));
		$matching_days = array(); 
		$aDay = 86400;  // a day in seconds
		$aWeek = $aDay * 7;
			$start_of_week = get_option('start_of_week'); //Start of week depends on WordPress
			//first, get the start of this week as timestamp
			$event_start_day = date('w', $start_date);
			$offset = 0;
			if( $event_start_day > $start_of_week ){
				$offset = $event_start_day - $start_of_week; //x days backwards
			}elseif( $event_start_day < $start_of_week ){
				$offset = $start_of_week;
			}
			$start_week_date = $start_date - ( ($event_start_day - $start_of_week) * $aDay );
			//then get the timestamps of weekdays during this first week, regardless if within event range
			$start_weekday_dates = array(); //Days in week 1 where there would events, regardless of event date range
			for($i = 0; $i < 7; $i++){
				$weekday_date = $start_week_date+($aDay*$i); //the date of the weekday we're currently checking
				$weekday_day = date('w',$weekday_date); //the day of the week we're checking, taking into account wp start of week setting
				if( in_array( $weekday_day, $weekdays) ){
					$start_weekday_dates[] = $weekday_date; //it's in our starting week day, so add it
				}
			}
	
			//for each day of eventful days in week 1, add 7 days * weekly intervals
			foreach ($start_weekday_dates as $weekday_date){
				//Loop weeks by interval until we reach or surpass end date
				while($weekday_date <= $end_date){
					if( $weekday_date >= $start_date && $weekday_date <= $end_date ){
						$matching_days[] = $weekday_date;
					}					
					$weekday_date = $weekday_date + strtotime("+$recurrence_interval week", date("Y-m-d",$weekday_date));
				}
			}//done!
			sort($matching_days);
			$tmd = count($matching_days);
			for($z=0;$z<count($matching_days);$z++)
			{
				$st_date1 = $matching_days[$z];
				if($z <= ($tmd-1)){
					if($recurrence_days==0):
						$recurrence_days=0;
				
					endif;
						
				
					$st_date2 = strtotime(date("Y-m-d", $matching_days[$z]));
					$st_date = date('Y-m-d',$st_date2);
					if($recurrence_days ==1):
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
					else:
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
				
					endif;
					if($tmpl_end_date < strtotime($end_date)){
						$end_date = date("Y-m-d", $tmpl_end_date);
					}
					bulk_upload_update_rec_data($post_data,$post_id,$st_date,$end_date);
				
				}
			}
	}
	if($recurrence_occurs == 'monthly' )
	{
		$recurrence_interval = get_post_meta($post_id,'recurrence_per',true);//no. of occurence.
		$days_between = ceil(abs($end_date - $start_date) / 86400);
		$recurrence_byweekno = get_post_meta($post_id,'monthly_recurrence_byweekno',true);	//on which day
		$l = 0;
		$month_week = 0;
		$count_recurrence = 0;
		$current_month = 0;
		$recurrence_list .= "<ul>";
		
			if(strstr(get_post_meta($post_id,'recurrence_bydays',true),","))
				$recurrence_byday = explode(',',get_post_meta($post_id,'recurrence_byday',true));	//on which day
			else
				$recurrence_byday = get_post_meta($post_id,'recurrence_byday',true);	//on which day
			$start_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) );
			$end_date = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'end_date',true))) );
		
		$matching_days = array(); 
		$aDay = 86400;  // a day in seconds
		$aWeek = $aDay * 7;		 
		$current_arr = getdate($start_date);
		$end_arr = getdate($end_date);
		$end_month_date = strtotime( date('Y-m-t', $end_date) ); //End date on last day of month
		$current_date = strtotime( date('Y-m-1', $start_date) ); //Start date on first day of month
		while( $current_date <= $end_month_date ){
			 $last_day_of_month = date('t', $current_date);
			//Now find which day we're talking about
			$current_week_day = date('w',$current_date);
			$matching_month_days = array();
			//Loop through days of this years month and save matching days to temp array
			for($day = 1; $day <= $last_day_of_month; $day++){
				if((int) $current_week_day == $recurrence_byday){
					$matching_month_days[] = $day;
				}
				$current_week_day = ($current_week_day < 6) ? $current_week_day+1 : 0;							
			}
			//Now grab from the array the x day of the month
			$matching_day = ($recurrence_byweekno > 0) ? $matching_month_days[$recurrence_byweekno-1] : array_pop($matching_month_days);
			$matching_date = strtotime(date('Y-m',$current_date).'-'.$matching_day);
			if($matching_date >= $start_date && $matching_date <= $end_date){
				$matching_days[] = $matching_date;
			}
			//add the number of days in this month to make start of next month
			$current_arr['mon'] += $recurrence_interval;
			if($current_arr['mon'] > 12){
				//FIXME this won't work if interval is more than 12
				$current_arr['mon'] = $current_arr['mon'] - 12;
				$current_arr['year']++;
			}
			$current_date = strtotime("{$current_arr['year']}-{$current_arr['mon']}-1"); 
			
		}
		sort($matching_days);
		$tmd = count($matching_days);
		for($z=0;$z<count($matching_days);$z++)
		{
			$class= ($z%2) ? "odd" : "even";
			$st_date1 = $matching_days[$z];
			date("Y-m-d", $matching_days[$z]);
			if($z <= ($tmd-1)){
				if($recurrence_days==0)
					$recurrence_days=1;
			
				$st_date2 = strtotime(date("Y-m-d", $matching_days[$z]) );
				$st_date = date("Y-m-d", $st_date2);
					if($recurrence_days ==1):
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
					else:
						$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
					
					endif;
				if($tmpl_end_date < strtotime($end_date)){
					$end_date = date('Y-m-d',strtotime(date("Y-m-d", $tmpl_end_date)));
				}
				bulk_upload_update_rec_data($post_data,$post_id,$st_date,$end_date);
			}
		}
	}
	if($recurrence_occurs == 'yearly' )
	{
		$date1 = get_post_meta($post_id,'st_date',true);
		$date2 = get_post_meta($post_id,'end_date',true);
		$st_startdate1 = explode("-",$date1);
		$st_year = $st_startdate1[0];
		$st_month = $st_startdate1[1];
		$st_day = $st_startdate1[2];
		$st_date1 = mktime(0, 0, 0, $st_month, $st_day, $st_year);
		$st_date__month = (int)date('n', $st_date1); //get the current month of start date.
		$diff = abs(strtotime($date2) - strtotime($date1));
		$years_between = floor($diff / (365*60*60*24));
		$recurrence_list .= "<ul>";
		for($i=0;$i<($years_between+1);$i++)
		{
			$class= ($i%2) ? "odd" : "even";
			$startdate = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))) . " +$i year");
			$startdate1 = explode("-",date('Y-m-d',$startdate));
			$year = $startdate1[0];
			$month = $startdate1[1];
			$day = $startdate1[2];
			$date2 = mktime(0, 0, 0, $month, $day, $year);
			$month = (int)date('n', $date2); //get the current month.
			
			if($month == $st_date__month  && $i%$recurrence_per == 0)
			{				
				$st_date1 = strtotime(date("Y-m-d", strtotime(get_post_meta($post_id,'st_date',true))). " +$i year");
				if($recurrence_days==0)
					$recurrence_days=1;
				
				$st_date2 = strtotime(date("Y-m-d", $st_date1));
				$st_date = date("Y-m-d", $st_date2);
				if($recurrence_days ==1):
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date))));
				else:
					$end_date =  date('Y-m-d',strtotime(date("Y-m-d", strtotime($st_date)) . " +".($recurrence_days-1)." day"));
						
				endif;
				if($tmpl_end_date < strtotime($end_date)){
					$end_date = date("Y-m-d", $tmpl_end_date);
				}
				bulk_upload_update_rec_data($post_data,$post_id,$st_date,$end_date);
			}
			else
			{
				continue;
			}
		}
	}
}
/*
	Miles range wise search, Event search map
*/
add_action('wp_ajax_nopriv_event_search_map','event_googlemap_search_map');
add_action('wp_ajax_event_search_map','event_googlemap_search_map');
function event_googlemap_search_map(){
	global $wp_query,$wpdb,$current_cityinfo;
	
	$per_page=get_option('posts_per_page');
	$args=array(
			 'post_type'      => 'event',
			 'posts_per_page' => $per_page,
			 'post_status'    => 'publish',
			 );
	
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
			
			if($cat->term_icon)
				$term_icon=$cat->term_icon;
			else
				$term_icon=apply_filters('tmpl_default_map_icon',TEVOLUTION_EVENT_URL.'images/pin.png');
			
			$image_class=($post_image)?'map-image' :'';
			$comment_count= count(get_comments(array('post_id' => $ID)));
			$review=($comment_count ==1 )? __('review',EDOMAIN):__('reviews',EDOMAIN);	
			
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
		echo '[{"totalcount":"'.$j.'",'.substr(implode(',',$cat_content_info),1).']';
	}else
	{
		echo '[{"totalcount":"0"}]';
	}
	exit;
}


/* Function to add the tabs on front page - when t evolution event post type set for home page*/

add_action('init','add_home_page_event_filter');
function add_home_page_event_filter()
{
	add_filter('pre_get_posts', 'event_home_page_feature_listing');
}
/*
	added event filter for home page.
*/
function event_home_page_feature_listing( &$query)
{
	$tmpdata = get_option('templatic_settings');
	$tev_home_type= $tmpdata['home_listing_type_value'];
	if(count($tev_home_type) == 1 && !empty($tev_home_type) && in_array('event',$tev_home_type) && get_option('show_on_front') =='posts' && is_home()){
		add_filter('posts_where', 'event_manager_posts_where',11); // filter to sort as per event 
	}
}

/* Define the custom box */
add_action( 'add_meta_boxes', 'listing_add_custom_box' );
/* Do something with the data entered */
add_action( 'save_post', 'listevent_save_postdata' );
/* Adds a box to the main column on the Post and Page edit screens */
function listing_add_custom_box() {
    $screens = array( 'listing' );
    if(is_plugin_active('Tevolution-Events/events.php')){
	    foreach ($screens as $screen) {
		   add_meta_box('listevent_sectionid', __( 'Events linked to this listing', EDOMAIN ),'listevent_custom_boxes', $screen,'side');
	    }
    }
}
/*
	it will list down the events enter by each user
*/
function listevent_custom_boxes(){
	global $wpdb,$post_id,$post;
	
	$event_for_listing=(get_post_meta($post_id,'event_for_listing',true))?explode(',',get_post_meta($post_id,'event_for_listing',true)):'';
	
	$post_type_name='event';
	$per_page = 50;
	$pagenum = isset( $_REQUEST[$post_type_name . '-tab'] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
	$offset = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;
	$args = array(
		'offset' => $offset,
		'order' => 'ASC',
		'orderby' => 'title',
		'posts_per_page' => $per_page,
		'post_type' => 'event',
		'suppress_filters' => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);
	
	if ( isset( $post_type['args']->_default_query ) )
		$args = array_merge($args, (array) $post_type['args']->_default_query );
	
	$get_posts = new WP_Query;
	$posts = $get_posts->query( $args );
	
	
	$post_type_object = get_post_type_object($post_type_name);

	$num_pages = $get_posts->max_num_pages;	
	$page_links = paginate_links( array(
		'base' => add_query_arg(
			array(
				$post_type_name . '-tab' => 'all',
				'paged' => '%#%',
				'item-type' => 'post_type',
				'item-object' => $post_type_name,
			)
		),
		'format' => '',		
		'prev_text' => '',
		'next_text' => '',
		'total' => $num_pages,
		'current' => $pagenum
	));	
	?>
    <input type="hidden" name="event_for_listing" id="listing_event_link" value="<?php echo get_post_meta($post_id,'event_for_listing',true);?>"/>
	<div id="posttype-event-listing" class="posttypediv">		
		<div id="listing-event-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-pagelinks add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
			<ul id="listing_event_checklist" data-wp-lists="list:<?php echo $post_type_name?>" class="categorychecklist form-no-clear">
				<?php
				foreach($posts as $event_d){	
					$event_id= $event_d->ID; 
					if(isset($_REQUEST['lang']) && $_REQUEST['lang'] !=''){
							$event_id = icl_object_id($event_d->ID, $event_d->post_type, false, 'en'); 	
					}else{
							$event_id= $event_d->ID;
					}	
					if(!empty($event_for_listing) && in_array($event_id,$event_for_listing) && $event_id !=''){ $checked = 'checked=checked'; }else{ $checked='';}
					echo '<li><input '.$checked.' id="event-'.$event_d->ID.'" type="checkbox" name="event_for_listing_[]" onclick="get_event_id(this,'.$pagenum.');" class="menu-item-checkbox" value="'.$event_d->ID.'"> <label for="event-'.$event_d->ID.'">'.$event_d->post_title.'</label></li>';					
				}
				?>
			</ul>
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-pagelinks add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
		</div><!-- /.tabs-panel -->

	</div><!-- /.posttypediv -->
     <script type="text/javascript">
	jQuery('#listing-event-all .add-pagelinks a').live("click",function(){
		  var pagged=jQuery(this).text()		  
		  //post code
		  <?php $postid='&post_id='.$post_id?>
		  jQuery.ajax({
				url:ajaxurl,
				type:'POST',
				data:'action=listing_event_link&posttype=event&paged='+pagged+'<?php echo $postid?>',
				success:function(results){					
					jQuery('#posttype-event-listing').html(results);
					var event_for_listing=jQuery('#listing_event_link').val();
					var separated = event_for_listing.split(",");
					for(var i=0;i<separated.length;i++){
						jQuery('#event-'+separated[i]).prop('checked', true);	
					}
				}
			});
		  return false;
	})
	var event_id='';
	function get_event_id(str,pagenum){
		var event_for_listing=jQuery('#listing_event_link').val();
		var eventid='';		
		if(jQuery('#'+str.id).attr('checked')=='checked'){
			event_for_listing+=str.value+',';
			jQuery('#listing_event_link').val(event_for_listing);
			
		}else{
			var event_listing='';
			var separated = event_for_listing.split(",");
			for(var i=0;i<separated.length;i++){				
				if(separated[i]!=str.value && separated[i]!=''){
					event_listing+=separated[i]+',';					
				}
			}
			jQuery('#listing_event_link').val(event_listing);
		}
		
	}
	
	</script>
	<?php
	
	echo '<p class="description">'.__('This connects your event listings to this listing and will show the selected events on the listing detail page.<br><strong>Note:</strong> Feature is only available with the &lsquo;Event Manager&rsquo; plugin.',DIR_DOMAIN).'</p>';
	
}

/*
	It will display the pagination in back end listed events in - Add/Edit listing section "Event Linked to List"
*/ 

add_action('wp_ajax_listing_event_link','listing_event_link');
function listing_event_link(){
	global $wpdb,$post_id,$post;	
	$event_for_listing=(isset($_REQUEST['post_id']) && get_post_meta($_REQUEST['post_id'],'event_for_listing',true))?explode(',',get_post_meta($_REQUEST['post_id'],'event_for_listing',true)):'';
	
	$post_type_name='event';
	$per_page = 50;
	$pagenum = (isset( $_REQUEST['paged'] ) )? absint( $_REQUEST['paged'] ) : 1;
	$offset = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;
	$args = array(
		'offset' => $offset,
		'order' => 'ASC',
		'orderby' => 'title',
		'posts_per_page' => $per_page,
		'post_type' => 'event',
		'suppress_filters' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);	
	if ( isset( $post_type['args']->_default_query ) )
		$args = array_merge($args, (array) $post_type['args']->_default_query );
	
	$get_posts = new WP_Query;
	$posts = $get_posts->query( $args );
	$post_type_object = get_post_type_object($post_type_name);

	$num_pages = $get_posts->max_num_pages;	
	$page_links = paginate_links( array(
		'base' => add_query_arg(
			array(
				$post_type_name . '-tab' => 'all',
				'paged' => '%#%',
				'item-type' => 'post_type',
				'item-object' => $post_type_name,
			)
		),
		'format' => '',		
		'prev_text' => '',
		'next_text' => '',
		'total' => $num_pages,
		'current' => $pagenum
	));	
	?>	
     <div id="listing-event-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
          <?php if ( ! empty( $page_links ) ) : ?>
               <div class="add-pagelinks add-menu-item-pagelinks">
                    <?php echo $page_links; ?>
               </div>
          <?php endif; ?>
          <ul id="<?php echo $post_type_name; ?>checklist" data-wp-lists="list:<?php echo $post_type_name?>" class="categorychecklist form-no-clear">
               <?php
               foreach($posts as $event_d){
                    if(!empty($event_for_listing) && in_array($event_d->ID,$event_for_listing)){ $checked = 'checked=checked'; }else{ $checked='';}
                    echo '<li><input '.$checked.' id="event-'.$event_d->ID.'" type="checkbox" name="event_for_listing_[]"  onclick="get_event_id(this,'.$pagenum.')" class="menu-item-checkbox" value="'.$event_d->ID.'"> <label for="event-'.$event_d->ID.'">'.$event_d->post_title.'</label></li>';					
               }
               ?>
          </ul>
          <?php if ( ! empty( $page_links ) ) : ?>
               <div class="add-pagelinks add-menu-item-pagelinks">
                    <?php echo $page_links; ?>
               </div>
          <?php endif; ?>
     </div><!-- /.tabs-panel -->
     <?php
	exit;
}

/* This function will return the  list of events detail available on particular listing/place */

function tmpl_get_events_list($event_for_list){

	/* args - to fetch the current and upcoming event for detail page listing */
	 $args = array( 'posts_per_page'   => -1,
			'offset'           => 0,
			'post_status'         => array('publish','private'),
			'orderby'          => 'meta_value',
			'order'            => 'ASC',
			'include'          => $event_for_list,
			'exclude'          => '',
			'post_type'        => 'event',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'meta_query'      => array('relation' => 'OR',
								array('key'     => 'st_date',
								'value'   => date('Y-m-d'),
								'compare' => '<='),
								array('key'     => 'end_date',
								'value'   => date('Y-m-d'),
								'compare' => '>=')),
			'suppress_filters' => true );
	return $events_list = get_posts($args); 
}

/* save the event */
function listevent_save_postdata(){
	global $wpdb,$post_id,$post;
	if(isset($_POST['event_for_listing']) && $_POST['event_for_listing']!='')
	{
		update_post_meta($post_id,'event_for_listing',trim($_POST['event_for_listing'])); // booked tickets
	}
	/* if no event is selected then make meta value blank */
	else
		{
			update_post_meta($post_id,'event_for_listing',''); // booked tickets
		}
}
/*
	fetch images on home page listing widget.
*/
add_action('event_featured_widget_listing_image','event_featured_widget_listing_image',10,2);
function event_featured_widget_listing_image($post_id,$my_post_type)
{
	global $post;
	if(get_post_meta($post_id,'_event_id',true)){ $post_id=get_post_meta($post_id,'_event_id',true); }
	  if ( has_post_thumbnail()){
		   $post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), apply_filters('directory_featured_image_size','directory-listing-image'));						
		   $post_images= @$post_img[0];
	  }else{
		   $post_img = bdw_get_images_plugin($post_id,apply_filters('directory_featured_image_size','directory-listing-image'));					
		   $post_images = @$post_img[0]['file'];
	  }
	  $image=($post_images)?$post_images : TEVOLUTION_EVENT_URL.'images/noimage-220x150.jpg';
 $featured=get_post_meta($post_id,'featured_h',true);
$tmpdata = get_option('templatic_settings');
	  ?>
	  <!-- start fp_image -->
	 
		<a class="listing_img" href="<?php echo get_permalink($post->post_id); ?>">
		<?php if($featured=='h'){echo '<span class="featured_tag">'.__('Featured',EDOMAIN).'</span>';} ?>
		   <img src="<?php echo $image;?>" alt="<?php echo get_the_title($post->post_id); ?>" title="<?php echo get_the_title($post->post_id); ?>" class="thumb"/></a>
	 
      <?php
}

/* Title for T -> Events Listing widget */
add_action('show_event_featured_homepage_listing','show_event_featured_homepage_listing');
function show_event_featured_homepage_listing()
{
	global $post;
	?>	<div class="entry-title">
			<h2 class="entry-title" itemprop="name"><a href="<?php echo get_permalink($post->post_id); ?>"><?php the_title(); ?></a><?php do_action('tevolution_title_text',$post);  ?></h2> 
		</div>
    <?php
}

/* Event featured after title - Display the contact informations like fb,goggle and twitter icons on event listings widget */
add_action('event_featured_after_title','event_featured_after_title');
function event_featured_after_title($instance)
{
	
	global $post,$htmlvar_name,$posttitle,$wp_query;
	$my_post_type = empty($instance['post_type']) ? 'listing' : $instance['post_type'];
	
	
	$is_archive = get_query_var('is_ajax_archive');
	
	$post_id=get_the_ID();
	$post_id=get_the_ID();
	$tmpdata = get_option('templatic_settings');
	
		
	if(!empty($htmlvar_name['contact_info']) && (isset($htmlvar_name['contact_info']['twitter'])  || isset($htmlvar_name['contact_info']['facebook']) || isset($htmlvar_name['contact_info']['google_plus'])))
	{
		$twitter=get_post_meta($post->ID,'twitter',true);
		$facebook=get_post_meta($post->ID,'facebook',true);
		$google_plus=get_post_meta($post->ID,'google_plus',true);
		echo "<div class='social_wrapper'>";
			
		if($twitter != '' && $htmlvar_name['contact_info']['twitter'])
		{
		?>
			<a class='twitter' href="<?php echo $twitter;?>"><label><?php _e('twitter',EDOMAIN); ?></label></a>
		<?php
		}
		if($facebook != '' && $htmlvar_name['contact_info']['facebook'])
		{
		?>
			<a class='facebook' href="<?php echo $facebook;?>"><label><?php _e('facebook',EDOMAIN); ?></label></a>
		<?php
		}
		if($google_plus != '' && $htmlvar_name['contact_info']['google_plus'])
		{
		?>
			<a class='google_plus' href="<?php echo $google_plus;?>"><label><?php _e('Google+',DIR_DOMAIN); ?></label></a>
		<?php
		}
		echo "</div>";
	}
		$j=0;
		if(!empty($htmlvar_name)){		
		foreach($htmlvar_name as $key=>$value){
			$i=0;
			if(!empty($value)){
				foreach($value as $k=>$val){
				
					$key = ($key=='basic_inf')?'Listing Information': $key;					
					if($k!='post_title'   && $k!='post_excerpt' && $k!='post_images' && $k!='st_time' && $k!='end_date' && $k!='st_date' && $k!='end_time' && $k!='address' && $k!='phone' && $k != 'twitter' && $k != 'facebook' && $k != 'google_plus' && $k != 'listing_timing')
					{						
						//if($i==0){echo '<h4 class="custom_field_headding">'.$key.'</h4>';}
						$field= get_post_meta($post->ID,$k,true);
						if($val['type'] == 'multicheckbox' && $field!=""): ?>
						<p class='<?php echo $k;?>'><label><?php echo $val['label']; ?></label>: <?php echo implode(",",$field); ?></p>
						<?php endif;
						if($val['type'] != 'multicheckbox' && $field!=''):
							?><p class='<?php echo $k;?>'><label><?php echo $val['label']; ?></label>: <?php echo $field;?></p><?php							
						endif;
					}
					$i++;
					$j++;
				}// Foreach
			}// value if condition
		}//foreach loop
	}//htmlvar_name if condition
	
	$j=0;

}

/* Display the content on event widget " Events Listing" */

add_action('event_taxonomy_content','event_taxonomy_category_content');
if(!function_exists('event_taxonomy_category_content')){
	function event_taxonomy_category_content($instance) {	
		global $post;	
		$tmpdata = get_option('templatic_settings');	
		$read_more = strip_tags($instance['read_more']);
		$max_char = ($instance['content_limit']!='')? $instance['content_limit']: 100;
		$more_link_text =($instance['read_more']!='')? $instance['read_more']: __('Read More',EDOMAIN);
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
	Fetch rating and custom fields on event listing widget.
*/
add_action('event_lsiting_after_title_event','event_lsiting_after_title_event');
function event_lsiting_after_title_event(){
	global $post,$htmlvar_name;
		$address=get_post_meta($post->ID,'address',true);
		$listing_timing=get_post_meta($post->ID,'listing_timing',true);
		$phone=get_post_meta($post->ID,'phone',true);
		
		$date_formate=get_option('date_format');
		$time_formate=get_option('time_format');
		$st_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'st_date',true)));
		$end_date=date_i18n($date_formate,strtotime(get_post_meta($post->ID,'end_date',true)));
		
		$date=$st_date.' '. __('To',EDOMAIN).' '.$end_date;
		
		$st_time=date_i18n($time_formate,strtotime(get_post_meta($post->ID,'st_time',true)));
		$end_time=date_i18n($time_formate,strtotime(get_post_meta($post->ID,'end_time',true)));	
		
		echo '<div class="author_rating">';
		$post_id=get_the_ID();
		$tmpdata = get_option('templatic_settings');
		$tmpdata = get_option('templatic_settings');
		do_action('show_multi_rating');
		if($tmpdata['templatin_rating']=='yes'):?>
			<div class="directory_rating_row"><span class="single_rating"> <?php echo draw_rating_star_plugin(get_post_average_rating($post_id));?> </span></div>
		<?php endif;
		echo "</div>";
		do_action('before_custom_fields_event');
		echo (@$phone && @$htmlvar_name['contact_info']['phone'])? '<p class="phone">'.$phone.'</p>' : '';
		echo (@$address && @$htmlvar_name['basic_inf']['address'])? '<p class="address" >'.$address.'</p>' : '';
		echo (@$st_date && @$end_date)? '<p class="event_date"><span>'.$date.'</span></p>' : '';		
		echo (@$st_time && @$end_time)? '<p class="time"><span>'.$st_time.' '.__('To',EDOMAIN).' '.$end_time.'</span></p>' : '';	
		do_action('after_custom_fields_event');
}
/*
	Display the event start date before title on events category and archive pages
*/
add_action('event_before_title_event','event_before_title_event');
add_action('templ_before_title_event','supreme_before_title_event');
function event_before_title_event(){
	global $post;	
	$st_date=strtotime(get_post_meta($post->ID,'st_date',true));
		?>
	<span class="date"> <?php echo date_i18n("d",$st_date); ?> <span><?php echo date_i18n("M",$st_date); ?></span> </span>
	<?php
}
/*
	Display the event start date before title on author and other pages - the pages which come from theme.
 */
add_action('supreme_before-title_event','supreme_before_title_event');
if(!function_exists('supreme_before_title_event(')){
	function supreme_before_title_event(){
		global $post;	
		if($post->ID !='' && (is_author() || is_home() || is_front_page() || is_search())){
			$st_date=strtotime(get_post_meta($post->ID,'st_date',true));
			?>
		<span class="date"> <?php echo date_i18n("d",$st_date); ?> <span><?php echo date_i18n("M",$st_date); ?></span> </span>
	<?php
		}	
	}
}

/*
	Display the primary sidebar on submission form
 */
add_filter( 'sidebars_widgets', 'tevolution_event_calendar_pagedisable_sidebars' );
function tevolution_event_calendar_pagedisable_sidebars( $sidebars_widgets ) {	
	
	global $wpdb,$wp_query,$post;
	if (!is_admin() && is_page()) {
		wp_reset_query();
		wp_reset_postdata();				
		if(strstr($post->post_content,'[calendar_event]')!==false && !empty($sidebars_widgets['ecategory_listing_sidebar'])){
			/*remove primary side bar on calendar event page  */
			$sidebars_widgets['primary'] = false;
			$sidebars_widgets['primary-sidebar'] = false;
		}		
	}
	return $sidebars_widgets;
}

/*
	Sidebar for event calendar page , means the page wgicg contain "[calendar_event]" shortcode
*/
add_action( 'get_sidebar', 'tevolution_event_calendar_page_sidebar');
function tevolution_event_calendar_page_sidebar($name)
{	
	global $post;	
	if(($name=='primary' || $name=='') && is_page()){
		$calendar_page_id=$post->ID;
		if(strstr($post->post_content,'[calendar_event]')!==false){
			echo '<aside class="sidebar large-3 small-12 columns" id="sidebar-primary">';
			dynamic_sidebar( 'ecategory_listing_sidebar' );
			echo '</aside>';
		}		
	}
}
/*slider option change the default*/
add_filter('tmpl_detail_slider_options','tmpl_event_detail_slider_options');
function tmpl_event_detail_slider_options()
{
	$slider_options = (array('animation'=>'slide','slideshow'=>'false','direction'=>'horizontal','slideshowSpeed'=>7000,'animationLoop'=>'true','startAt'=> 0,'smoothHeight'=> 'true','easing'=> "swing",'pauseOnHover'=> 'true','video'=> 'true','controlNav'=> 'false','directionNav'=> 'false','prevText'=> '','nextText'=> '','animationLoop'=>'true','itemWidth'=>'60','itemMargin'=>'20')
						);
	return $slider_options;
}


add_action('wp_footer','tmpl_show_fb_events');
/*
	Script to show hide the face book events form on author page
*/
function tmpl_show_fb_events()
{
	if(isset($_REQUEST['fb_event']) && $_REQUEST['fb_event']=='facebook_event')
	{
	?>
	<script>
	function showFacebookSetting(e){if(e=="show_facebook_setting"){document.getElementById("hide_fb_fields").style.display="";document.getElementById("edit_fb_fields").style.display="none";document.getElementById("show_api_fields").style.display=""}else if(e=="hide_facebook_setting"){document.getElementById("hide_fb_fields").style.display="none";document.getElementById("edit_fb_fields").style.display="";document.getElementById("show_api_fields").style.display="none"}return true}jQuery("#submit").click(function(){var e="";var t="";if(jQuery("#appid")){t=jQuery("#appid").val()}var n="";if(jQuery("#secret_id")){n=jQuery("#secret_id").val()}var r="";if(jQuery("#page_id")){r=jQuery("#page_id").val()}jQuery.ajax({url:ajaxUrl,type:"POST",data:"action=save_facebook_event_api&appid="+t+"&secret_id="+n+"&page_id="+r,success:function(e){if(e&&e!=0){jQuery("#responsecontainer").html(e)}}})})
	</script>
<?php
	}
}
?>