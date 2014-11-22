<?php 
global $post;

$htmlvar_name = $htmlvar_name;

$featured=get_post_meta($post->ID,'featured_c',true);
$classes =($featured=='c')?'featured_c':'';
?>
<article class="post  <?php echo $classes;?>">  
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
									do_action('event_post_info',$htmlvar_name);   ?>     
									
									</div> 
									</div>
								   
								   <!--Start Post Content -->
								   <?php do_action('event_before_post_content');       /* do action for before the post content. */ 
								   
									do_action('templ_taxonomy_content');	
								   
									do_action('event_after_post_content');        /* do action for after the post content. */
								   
									do_action('event_after_taxonomies');?>
								</div>
                            <?php do_action('event_after_post_entry');?>
                         </article>
                        <?php do_action('event_after_post_loop');?>