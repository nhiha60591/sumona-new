<?php
/**
 * Event archive page
 *
**/
get_header(); //Header Portation
$tmpdata = get_option('templatic_settings');
?>
<script type="text/javascript">
var category_map = '<?php echo $tmpdata['category_map'];?>';
<?php if($_COOKIE['display_view']=='locations_map' && $tmpdata['category_map']=='yes'):?>
jQuery(function() {			
	jQuery('#listpagi').hide();
});
<?php endif;?>
</script>
<?php do_action('after_ecategory_header'); 

	if(function_exists('tmpl_get_category_list_customfields')){
		$htmlvar_name = tmpl_get_category_list_customfields(get_post_type());
	}else{
		global $htmlvar_name;
	}
	do_action('event_before_container_breadcrumb'); /*do action for display the bradcrumb in between header and container. */ ?>
<div id="content" class="contentarea large-9 small-12 columns <?php //event_class();?>">
	<?php do_action('event_inside_container_breadcrumb'); /*do action for display the bradcrumn  inside the container. */ ?>
     
	<?php do_action('event_before_archive_title');//do action for display before categories title?>              
     <h1 class="page-title">
		<?php echo ucfirst(apply_filters('tevolution_archive_page_title','Event'));?>		
	</h1>    
     
	<?php 
	
	/* archive - custom post type description */
	$archive_description = get_option('templatic_custom_post');
	if($archive_description[get_post_type()]['description'] !=''){
		?>
		 <div class="archive-meta"><?php echo stripslashes($archive_description[get_post_type()]['description']); ?></div>
		<?php 
	}
	
	 do_action('event_after_archive_title');
	
	
	 ?>
     
    
     
     <!--Start loop archive page-->   
     <?php do_action('event_before_loop_archive');?> 
     
     <div id="loop_event_archive" class="search_result_listing <?php if(isset($tmpdata['default_page_view']) && $tmpdata['default_page_view']=="gridview"){echo 'grid';}else{echo 'list';}?>" <?php if( is_plugin_active('Tevolution-Directory/directory.php') && isset($tmpdata['default_page_view']) && $tmpdata['default_page_view']=="mapview"){ echo 'style="display: none;"';}?>>
     	<?php if (have_posts()) : 
				while (have_posts()) : the_post(); ?>	
                     <?php do_action('event_before_post_loop');?>
                    	<div class="post <?php templ_post_class();?>">  
                         	<?php do_action('event_before_category_page_image');           /*do_action before the post image */
                         	
								do_action('event_category_page_image');
                              
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
									
									<?php  /* Hook to get Entry details - Like address,phone number or any static field  */  
									do_action('event_post_info');   ?>     
									
									</div> 
									</div>
								   
								   <!--Start Post Content -->
								   <?php do_action('event_before_post_content');       /* do action for before the post content. */ 
								   
									do_action('templ_taxonomy_content');	
								   
									do_action('event_after_post_content');        /* do action for after the post content. */
								   
									do_action('templ_the_taxonomies');  
								   
									do_action('event_after_taxonomies');?>
								</div>
                            <?php do_action('event_after_post_entry');?>
                         </div>
                         <?php do_action('event_after_post_loop');

						 endwhile; 
						 if($wp_query->max_num_pages !=1):?> 
                         <div id="listpagi">
                              <div class="pagination pagination-position">
                                    <?php if(function_exists('pagenavi_plugin')) { pagenavi_plugin(); } ?>
                              </div>
                         </div>    	 
                        <?php endif;
			wp_reset_query();
			else:?>
          	<p class='nodata_msg'><?php _e( 'Apologies, but no results were found for the requested archive.', EDOMAIN ); ?></p>              
          <?php endif;?>
     </div>
     
      <?php do_action('event_after_loop_archive');?>
      
    
      <!--End loop archive page -->
</div>
<!--archive sidebar -->
<?php if ( is_active_sidebar( 'ecategory_listing_sidebar') ) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar('ecategory_listing_sidebar'); ?>
	</aside>
<?php elseif ( is_active_sidebar( 'primary' )) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar( 'primary' ); ?>		
	</aside>
<?php elseif ( is_active_sidebar( 'primary-sidebar') ) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar('primary-sidebar'); ?>
	</aside>
<?php endif; ?>
<!--archive sidebar -->
<?php get_footer(); ?>