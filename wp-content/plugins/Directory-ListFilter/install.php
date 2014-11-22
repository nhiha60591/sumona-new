<?php
global $wp_query,$wpdb,$pagenow;

if(isset($_POST['reset_custom_fields']) && (isset($_POST['custom_reset']) && $_POST['custom_reset']==1))
{
	update_option('directory_custom_fields_insert','none');
}

/* get all custom fields */
$post_content = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_type = 'custom_fields' and $wpdb->posts.post_status = 'publish'");
$j=1; 
foreach($post_content as $custom_fields_id)
{
	$ctype = get_post_meta($custom_fields_id->ID ,'ctype',true);
	/* exception of custom field */
	if($ctype != 'heading_type' && $ctype != 'multicity'  && $ctype != 'post_categories' && $ctype != 'image_uploader' && $ctype != 'oembed_video' && $ctype != 'upload'  && $ctype != 'select' && $ctype != 'textarea' && $ctype != 'texteditor')
	{
		/* add custom field as a filter */
		update_post_meta($custom_fields_id->ID,'show_as_filter',1);
		update_post_meta($custom_fields_id->ID,'filter_sort_order',$j);
		
	}
$j++;	
}
?>
