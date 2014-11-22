<?php
/**
 * Event Category taxonomy page
 *
**/
get_header(); //Header Portation
$tmpdata = get_option('templatic_settings');

	do_action('after_ecategory_header');
	global $htmlvar_name;
	if(function_exists('tmpl_get_category_list_customfields')){
		$htmlvar_name = tmpl_get_category_list_customfields(CUSTOM_POST_TYPE_EVENT);
	}else{
		global $htmlvar_name;
	}
	 /*do action for display the breadcrumb in between header and container. */
	do_action('event_before_container_breadcrumb'); ?>
    
    <div class="mobile-listing-header">
    	<?php 
			if(tmpl_wp_is_mobile()  &&  is_active_sidebar(get_post_type().'_above_mobile_listing')) {
				echo '<a href="#" data-reveal-id="mobile_listing_popup" id="mobile_listing_popup_link">Popup</a>';
				echo "<div id='mobile_listing_popup' class='header-widget-wrap reveal-modal mobile_data_reveal' data-reveal>";
					echo '<a class="close-reveal-modal">?</a>';
		        	dynamic_sidebar( get_post_type().'_above_mobile_listing' );
		        echo "</div>";	
			}
			
		?>
		<?php 
			if(is_archive()){ ?>
			<h1 class="loop-title"><?php echo CUSTOM_POST_TYPE_EVENT; ?></h1>
		<?php }else{ ?>
			<h1 class="loop-title"><?php single_cat_title(); ?></h1>
		<?php } 
		
		do_action('tmpl_device_sorting_option'); /* shows sorting options and listin views and map view tabs */
		
		?>
		<?php 
		if(tmpl_wp_is_mobile())
	        dynamic_sidebar( get_post_type().'_above_mobile_listing' );
		?>
	</div>
	
    
<div id="content" class="contentarea large-9 small-12 columns <?php //event_class();?>">
	<?php 
	/*do action for display the bradcrumn  inside the container. */ 
	do_action('event_inside_container_breadcrumb'); 
	/* do action for display before categories title */
	do_action('event_before_categories_title'); ?>
     <!--Start loop taxonomy page-->
     <?php do_action('event_before_loop_taxonomy');?>
     
    <div class="view_type_wrap">
        <?php 
		do_action('event_subcategory');
		?>
    </div>
	<?php do_action('tmpl_device_event_tabs'); /* shows tabs  */?>       
      
     <section id="loop_event_taxonomy" class="search_result_listing list" <?php if( is_plugin_active('Tevolution-Directory/directory.php') && isset($tmpdata['default_page_view']) && $tmpdata['default_page_view']=="mapview"){ echo 'style="display: none;"';}?>>
     	<?php if (have_posts()) : 
				while (have_posts()) : the_post(); ?>	
					<?php do_action('event_before_post_loop');?>
					<article class="post  <?php templ_post_class();?>">  
						<?php do_action('event_before_category_page_image');           /*do_action before the post image */
						
							echo tmpl_mobile_archive_image('mobile-thumbnail');
						  
							do_action('event_after_category_page_image');           /*do action after the post image */
							do_action('event_before_post_entry'); ?>
							<div class="entry"> 
								<!--start post type title -->
								<?php do_action('event_before_post_title');         /* do action for before the post title.*/ ?>
								<div class="event-wrapper">
								<div class="event-title">
							   
									<?php do_action('templ_post_title');                /* do action for display the single post title */
									 ?>
							   
								</div>
								<?php do_action('event_after_post_title');          /* do action for after the post title.*/?>
								<!--end post type title -->
								<!-- Entry details start -->
								<div class="entry-details">
                                	<?php do_action('tmpl_before_maddress'); ?>
                                    <p class="address"><?php echo get_post_meta($post->ID,'address',true); ?></p>
                                    <?php do_action('tmpl_after_maddress'); ?>								
								</div> 
								</div>
							   
							   <!--Start Post Content -->
							   <?php do_action('event_before_post_content');       /* do action for before the post content. */ 
							   ?>
							</div>
						<?php do_action('event_after_post_entry');?>
					 </article>
                        <?php do_action('event_after_post_loop');?>
          	<?php endwhile;
			wp_reset_query(); 
			else:?>
          	<p class='nodata_msg'><?php _e( 'Apologies, but no results were found for the requested archive.', EDOMAIN ); ?></p>              
          <?php endif;
		  if($wp_query->max_num_pages !=1):?>
             <div id="listpagi">
                  <div class="pagination pagination-position">
                        <?php if(function_exists('pagenavi_plugin')) { pagenavi_plugin(); } ?>
                  </div>
             </div>
         <?php endif; ?>
     </section>
     <?php do_action('event_after_loop_taxonomy');
			/* after-content-sidebar use remove filter to don't display it */
			if(function_exists('supreme_sidebar_after_content'))
				apply_filters('tmpl_after-content',supreme_sidebar_after_content()); 

			?>
      <!--End loop taxonomy page -->
</div>
<?php get_footer(); ?>