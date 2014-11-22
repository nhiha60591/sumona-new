<?php 
global $post;
do_action('event_before_post_loop');?>
	<article class="post  <?php templ_post_class();?>">  
		<?php do_action('event_before_category_page_image');           /*do_action before the post image */
		
			echo tmpl_mobile_archive_image('mobile-thumbnail');
		  
			do_action('event_after_category_page_image');           /*do action after the post image */
			do_action('event_before_post_entry'); ?>
			<div class="entry"> 
				<!--start post type title -->
				
				<div class="event-wrapper">
				<?php do_action('event_before_post_title');         /* do action for before the post title.*/ ?>
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