<?php
/*
NAME : SUBMIT EVENT CANCELLATION FILE
DESCRIPTION : THIS FILE WILL BE CALLED IF THE USER CANCEL THE PROCESS OF SUBMITTING EVENT.
*/
add_action('wp_head','show_background_color');
function show_background_color()
{
/* Get the background image. */
	$image = get_background_image();
	/* If there's an image, just call the normal WordPress callback. We won't do anything here. */
	if ( !empty( $image ) ) {
		_custom_background_cb();
		return;
	}
	/* Get the background color. */
	$color = get_background_color();
	/* If no background color, return. */
	if ( empty( $color ) )
		return;
	/* Use 'background' instead of 'background-color'. */
	$style = "background: #{$color};";
?>
<style type="text/css">
body.custom-background {
<?php echo trim( $style );
?>
}
</style>
<?php }


global $page_title,$current_user;
$page_title =  __('Payment Cancellation',DOMAIN);

get_header(); ?>
<?php if ( get_option( 'ptthemes_breadcrumbs' ) == 'Yes') {  ?>
<div class="breadcrumb_in"><a href="<?php echo site_url(); ?>"><?php _e('Home'); ?></a> &raquo; <?php echo $page_title; ?></div><?php } ?>

<section id="content" class="large-9 small-12 columns">
	
	
	<div class="post-content">
	<?php if($current_user->ID !=''){ ?>
	<h2><?php  _e('Payment Cancellation',DOMAIN);?></h2>
	<?php 
	
	$tmpdata = get_option('templatic_settings');
	$filecontent = stripslashes(get_option('post_payment_cancel_msg_content'));
	if(!$filecontent)
	{
		$filecontent = PAY_CANCEL_MSG;
	}
	$store_name = '<a href="'.site_url().'">'.get_option('blogname').'</a>';
	$search_array = array('[#site_name#]','[#admin_email#]');
	$replace_array = array($store_name,get_option('admin_email'));
	$filecontent = str_replace($search_array,$replace_array,$filecontent);
	echo $filecontent;
	echo ' <a href="'.site_url().'">';
	_e('Go to Home',DOMAIN);
	echo '</a>';
	$post = get_post($_REQUEST['pid']);
	$user_info = get_userdata( $post->post_author );
	$fromEmail = $user_info->user_email;
	$fromEmailName = $user_info->user_login;
	$to = get_option('admin_email');
	$toname = stripslashes(get_option('blogname'));	
	$subject = $tmpdata['payment_cancelled_subject'];
	if(!$subject)
	{
		$subject = 'Payment Cancelled';
	}
	$payment_cancelled_content=$tmpdata['payment_cancelled_content'];
	if(!$payment_cancelled_content)
	{
		$payment_cancelled_content = '[#post_type#] has been cancelled with transaction id [#transection_id#]';
	}
	
	$payment_cancelled_content=str_replace(array('[#post_type#]','[#transection_id#]'),array(ucfirst(get_post_type($_REQUEST['pid'])),$_REQUEST['trans_id']),$payment_cancelled_content);
	$filecontent1 = $payment_cancelled_content;
	$filecontent2 = $payment_cancelled_content;
	@templ_send_email($fromEmail,$fromEmailName,$to,$toname,$subject,$filecontent1,''); // email to admin
	if($fromEmail != $to)
	{
		@templ_send_email($to,$toname,$fromEmail,$fromEmailName,$subject,$filecontent2,''); // email to client 
	}
	
	}else{
		_e('You are not allowed to access this page.',DOMAIN);	
	}
	?> 
	</div> <!-- content #end -->
</section>
<aside id="sidebar-primary">
<?php get_sidebar(); ?>
</aside>
<?php 



get_footer(); ?>