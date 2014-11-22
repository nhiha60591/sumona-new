<?php 
get_header(); // display header 
global $upload_folder_path;

if(isset($_POST['upgrade'])){
	
	$_SESSION['custom_fields'] = $_POST; // set upgrade_post session	
	$_SESSION['upgrade_post'] = $_POST; // set upgrade_post session	
	if(isset($_POST['category']))
	 {
		$_SESSION['category'] = $_POST['category'];
	 }
}
$current_user = wp_get_current_user();
$cur_post_id = $_SESSION['upgrade_post']['cur_post_id'];
$cur_post_type = get_post_meta($cur_post_id,'submit_post_type',true);

//contion for captcha inserted properly or not.
$tmpdata = get_option('templatic_settings');
if(isset($tmpdata['user_verification_page']) && $tmpdata['user_verification_page'] != "")
{
	$display = $tmpdata['user_verification_page'];
}
else
{
	$display = "";	
}
$id = $_SESSION['upgrade_post']['cur_post_id'];
$permalink = get_permalink( $id );
if( is_plugin_active('wp-recaptcha/wp-recaptcha.php')  && in_array('submit',$display) && 'preview' == @$_REQUEST['page'] && 'delete' != $_REQUEST['action'] ){
		require_once( get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php');
		$a = get_option("recaptcha_options");
		$privatekey = $a['private_key'];
						$resp = recaptcha_check_answer ($privatekey,
								getenv("REMOTE_ADDR"),
								$_POST["recaptcha_challenge_field"],
								$_POST["recaptcha_response_field"]);
											
		if (!$resp->is_valid ) {
			if($_REQUEST['pid'] != '')
			 {
				wp_redirect(get_permalink($cur_post_id).'/?ptype=post_event&pid='.$_REQUEST['pid'].'&action=edit&backandedit=1&ecptcha=captch');
			 }
			 else
			 {
				wp_redirect(get_permalink($cur_post_id).'/?ptype=post_event&backandedit=1&ecptcha=captch');	 
			 }
			exit;
		} 
}


do_action('templ_before_container_breadcrumb'); /*do action for display the bradcrumb in between header and container. */
?>
<!-- start content part-->
<div id="content" role="main" class="large-9 small-12 columns">
	<h1><?php _e('Select payment options',DOMAIN); ?></h1>
	<?php include (TEMPL_MONETIZE_FOLDER_PATH . "templatic-custom_fields/submit_preview_buttons.php"); /* fetch publish options and button options */?>

</div>
<!--End content part -->
<aside class="sidebar large-3 small-12 columns" id="sidebar-primary">
<?php dynamic_sidebar($cur_post_type.'_detail_sidebar');?>
</aside>
<?php get_footer(); ?>