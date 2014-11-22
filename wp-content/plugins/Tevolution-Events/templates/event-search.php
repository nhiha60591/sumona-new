<?php
/**
 * Event Search page
 *
**/
get_header(); //Header Portation
$tmpdata = get_option('templatic_settings');
	
	do_action('after_ecategory_header'); 

	/*do action for display the breadcrumb in between header and container. */ 
	do_action('event_before_container_breadcrumb'); 
	
	do_action( 'before_content' );
	?>
<div id="content" class="contentarea large-9 small-12 columns <?php //event_class();?>">
	<?php 
	
	do_action( 'open_content' );
	/*do action for display the breadcrumb  inside the container. */
	
	do_action('event_inside_container_breadcrumb'); 
	
	/* do action for display before categories title */
	do_action('event_before_search_title');
	
	
	global $current_cityinfo;
	if((isset($_REQUEST['radius']) && $_REQUEST['radius']!='') || (isset($_REQUEST['location']) && $_REQUEST['location']!='')){
		if(isset($_REQUEST['radius']) && $_REQUEST['radius']==1){
			$radius_type=(isset($_REQUEST['radius_type']) && $_REQUEST['radius_type']=='kilometer')? 'kilometer': 'mile';
		}
		if(isset($_REQUEST['radius']) && $_REQUEST['radius']!=1 && $_REQUEST['radius']!=""){
			$radius_type=(isset($_REQUEST['radius_type']) && $_REQUEST['radius_type']=='kilometer')? 'kilometers': 'miles';
		}
		$radius=(isset($_REQUEST['location']) && $_REQUEST['location']!='')?  $_REQUEST['radius'].' '.$radius_type.' around "'.$_REQUEST['location'].'"' : $_REQUEST['radius'].' '.$radius_type.' around "'.$current_cityinfo['cityname'].'"';
	}
	?>
     <header class="page-header extra-search-criteria-title">
          <h1 class="page-title"><?php printf( __( 'Search Results for: %s %s', EDOMAIN ), '<span>"' . get_search_query() . '"</span>','<span>' . $radius . '</span>' ); ?></h1>
          <?php do_action('after_search_result_label');  ?>
     </header>
     
	<?php  do_action('event_after_search_title');// do action for display after categories title ?>
     
    
     
     <!--Start loop search page-->   
     <?php do_action('event_before_loop_search');?> 
     
     <div id="loop_event_archive" class="list">
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
			wp_reset_query();
			else:
				if(isset($_REQUEST['etype']) && $_REQUEST['etype']=='current')
					$seach_tab='past or upcoming';
				elseif(isset($_REQUEST['etype']) && $_REQUEST['etype']=='past')
					$seach_tab='current or upcoming';
				elseif(isset($_REQUEST['etype']) && $_REQUEST['etype']=='upcoming')
					$seach_tab='past or cureent';
				?>
				<p class='nodata_msg'><?php echo sprintf(__( 'Sorry! No results were found in %s events for the requested search or its may be in %s events. Try searching with some different keywords', EDOMAIN ),$_REQUEST['etype'],$seach_tab); ?></p>
               
               <?php get_template_part( 'event-listing','search-form' ); ?>
          <?php endif;?>
     </div>
     
      <?php do_action('event_after_loop_search');?>
     
     <?php if($wp_query->max_num_pages !=1):?> 
     <div id="listpagi">
          <div class="pagination pagination-position">
                <?php if(function_exists('pagenavi_plugin')) { pagenavi_plugin(); } ?>
          </div>
     </div>    	 
     <?php endif;
	 
	 do_action( 'close_content' );
	 ?>
      <!--End loop search page -->
</div>
<!--search sidebar -->
<?php 
	do_action( 'after_content' );
	if ( is_active_sidebar( 'primary' )) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar( 'primary' ); ?>		
	</aside>
<?php elseif ( is_active_sidebar( 'primary-sidebar') ) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar('primary-sidebar'); ?>
	</aside>
<?php elseif ( is_active_sidebar( 'ecategory_listing_sidebar') ) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar('ecategory_listing_sidebar'); ?>
	</aside>
<?php endif; ?>
<!--search sidebar -->
<?php get_footer(); ?>
