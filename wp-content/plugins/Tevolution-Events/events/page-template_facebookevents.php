<?php
/*
Template Name: Template - Facebook Events
*/
function directory_facebook_events($atts){
	extract( shortcode_atts( array (
			'post_type'   =>'post',				
			), $atts ) 
		);	
	ob_start();	
	remove_filter( 'the_content', 'wpautop' , 12);
	echo facebook_events_template($atts['facebook_app_id'],$atts['facebook_secret_id'],$atts['facebook_page_id']);
	return ob_get_clean();
}
add_shortcode('directory_facebook_event', 'directory_facebook_events');
?>