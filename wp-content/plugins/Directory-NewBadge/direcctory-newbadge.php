<?php
/*
Plugin Name: Directory - NewBadge
Plugin URI: http://templatic.com/
Description: This add-on allows you to display a badge on your listings for a set period of time, you can set label and color for the badge too. 
Version: 1.0
Author: Templatic
Author URI: http://templatic.com/
*/

define('DNB_DOMAIN','templatic');
$locale = get_locale();
load_textdomain( DNB_DOMAIN, plugin_dir_path( __FILE__ ).'languages/'.$locale.'.mo' );

add_action('admin_enqueue_scripts','diectory_newbadge_admin_head_script',99);
function diectory_newbadge_admin_head_script(){
	wp_enqueue_script('jquery-ui-datetimepicker',plugin_dir_url( __FILE__ ) . '/jquery.datetimepicker.js', array( 'jquery' ), '', false);
	wp_enqueue_style( 'jquery-datetimepicker-style', plugin_dir_url( __FILE__ ) . '/jquery.datetimepicker.css', '', '', 'all' );   
}

/*
 * Function Name: directory_newbadge_meta_box
 */
add_action('admin_init','directory_newbadge_meta_box');
function directory_newbadge_meta_box(){
	$post_types=tevolution_get_post_type();	
	foreach($post_types as $post_type){
		if($post_type!='admanager'){
			add_meta_box( 'directory_newbadge', __( 'Directory New Badge', DNB_DOMAIN ), 'directory_newbadge_meta_box_content', $post_type, 'side','',$post );
		}
	}
}


/*
 * Function Name:directory_newbadge_meta_box_content
 * Return: display meta box 
 */
function directory_newbadge_meta_box_content(){
	global $post;
	$newbadge_title=get_post_meta($post->ID,'newbadge_title',true);
	$newbadge_color=get_post_meta($post->ID,'newbadge_color',true);
	$newbadge_color=($newbadge_color!='')?$newbadge_color:'#';
	$newbadge_end_date=get_post_meta($post->ID,'newbadge_end_date',true);	
	?>
	<ul class="badge_list">
		<li>
        	<label><strong><?php echo _e('Badge Title',DNB_DOMAIN);?></strong></label><span><input type="text" name="newbadge_title" value="<?php echo $newbadge_title?>" /></span>
            <p class="description"><?php _e('This title will appear as new badge on your listings in all the archive pages.',DNB_DOMAIN);?></p>
        </li>
        <li>
        	<label><strong><?php echo _e('Color',DNB_DOMAIN);?></strong></label>
            <span><input type="text" name="newbadge_color" value="<?php echo $newbadge_color;?>"  id="newbadge_color_picker" /></span>
            <img src="<?php echo TEVOLUTION_PAGE_TEMPLATES_URL.'/images/delete_10.png'?>" id="close_newbadge_color_picker" style="display:none"/>
            <div class="farbtastic_color" id="color_newbadge_color_picker"  name="newbadge_color_picker" style="display:none" >
            </div>
            <p class="description"><?php _e('Select color of your new badge.',DNB_DOMAIN);?></p>
		</li>
        <li>
        	<label><strong><?php echo _e('End Date',DNB_DOMAIN);?></strong></label><span><input type="text" name="newbadge_end_date" value="<?php echo $newbadge_end_date?>" id="newbadge_end_date"/></span>
            <p class="description"><?php _e("Select a date since when you don't want the new badge to appear for this listing.",DNB_DOMAIN);?></p>
        </li>
	</ul>
    <script type="text/javascript">
	jQuery(document).ready(function($){
		jQuery("#color_newbadge_color_picker").farbtastic("#newbadge_color_picker");
		jQuery('#newbadge_color_picker').live( 'click focus', function(e) {
			jQuery('[name="newbadge_color_picker"]').css('display', 'block');
			jQuery('#close_newbadge_color_picker').css('display', 'block');
			return false;
		});
		jQuery('#close_newbadge_color_picker').live( 'click focus', function(e) {
			jQuery('[name="newbadge_color_picker"]').css('display', 'none');
			jQuery('#close_newbadge_color_picker').css('display', 'none');
			return false;
		});
		jQuery('#newbadge_end_date').datetimepicker({					 
				 format:'Y-m-d H:i'
				});	
		
	});	
	</script>
    <?php
}


/*
 * Function Name: directory_newbadge_save_post
 * Save Directory NewBadge filed save
 */
add_action('save_post','directory_newbadge_save_post');
function directory_newbadge_save_post($post_id){
	
	$post_types=tevolution_get_post_type();	
	if(in_array($_POST['post_type'],$post_types)){		
		update_post_meta($post_id,'newbadge_title',$_POST['newbadge_title']);
		update_post_meta($post_id,'newbadge_color',$_POST['newbadge_color']);
		update_post_meta($post_id,'newbadge_end_date',$_POST['newbadge_end_date']);			
	}
	

}

add_action('init','directory_newbadge_frontend_init');
function directory_newbadge_frontend_init(){
	global $post;
	$post_types=tevolution_get_post_type();		
	foreach($post_types as $post_type){
		add_action($post_type.'_inside_listing_image','directory_newbadge_image_tag');
	}
	add_action('inside_listing_image','directory_newbadge_image_tag');
	add_action('tmpl_before_category_page_image','directory_newbadge_image_tag');

}

function directory_newbadge_image_tag(){
	global $post;
	$newbadge_title=get_post_meta($post->ID,'newbadge_title',true);
	$newbadge_title=($newbadge_title!="")? $newbadge_title : __('New',DNB_DOMAIN);
	$newbadge_color=get_post_meta($post->ID,'newbadge_color',true);	
	$newbadge_end_date=get_post_meta($post->ID,'newbadge_end_date',true);	
	$current_date=date('Y-m-d H:i');
	
	if(strtotime($newbadge_end_date)> strtotime($current_date)){		
		?>
        <span class="badge-status" style="background:<?php echo $newbadge_color;?>"><?php echo $newbadge_title;?></span>
        <?php
	}	
}
add_action('wp_footer','directory_badge_wp_footer');
function directory_badge_wp_footer(){
	?>
    <style type="text/css">
		.badge-status{background: none repeat scroll 0 0 #ff0000; border-radius:3px; top: 13px; color: #fff; display: inline-block; font-weight: bold; right: 13px; padding: 2px 10px; position:absolute; z-index:1;}
		#loop_listing_archive .post .listing_img .featured_tag, #loop_listing_taxonomy .post .listing_img .featured_tag, #tmpl-search-results.list .hentry .listing_img .featured_tag, .user #content .hentry .listing_img .featured_tag, .hfeed .post .listing_img .featured_tag, .user #content .author_cont div[id*="post"] .listing_img .featured_tag{bottom:12px; top:inherit;}
		#loop_listing_archive .post, #loop_listing_taxonomy .post{position:relative;}
	</style>
    <?php	
}
