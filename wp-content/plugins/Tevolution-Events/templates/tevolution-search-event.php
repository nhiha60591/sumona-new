<?php
global $post,$wp_query,$htmlvar_name;
$wp_query->set('is_ajax_archive',1);
do_action('event_before_post_loop');
$featured=get_post_meta($post->ID,'featured_c',true);
$classes=($featured=='c')?'featured_c':'';
?>				 
<div class="post <?php echo $classes;?>"> 
<?php do_action('event_before_category_page_image');           /*do_action before the post image */?>
                    
<?php do_action('event_category_page_image');?>  
	  
<?php do_action('event_after_category_page_image');           /*do action after the post image */?> 
	  
<?php do_action('event_before_post_entry');?>
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
        do_action($post->post_type.'_post_info');   ?>     
        
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