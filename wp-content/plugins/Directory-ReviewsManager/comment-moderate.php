<?php
/**
 * Plugin Name: Directory - Reviews Manager	
 * Plugin URI: http://templatic.com
 * Description: Allows Listing authors to moderate reviews on the listings they have submitted.
 * Version: 1.0
 * Author: Templatic
 * Author URI: http://templatic.com
 */
ob_start();
@define( 'TEVOLUTION_COMMENT_MODERATE_DOMAIN', 'tevolution_comment_moderate');
define( 'TEVOLUTION_COMMENT_MODERATE_VERSION', '1.0' );
define('TEVOLUTION_COMMENT_MODERATE_SLUG','Tevolution-Comment-Moderate/comment-moderate.php');
// Plugin Folder URL
define( 'TEVOLUTION_COMMENT_MODERATE_URL', plugin_dir_url( __FILE__ ) );
// Plugin Folder Path
define( 'TEVOLUTION_COMMENT_MODERATE_DIR', plugin_dir_path( __FILE__ ) );
// Plugin Root File
define( 'TEVOLUTION_COMMENT_MODERATE_FILE', __FILE__ );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
ob_start();
$locale = get_locale();
global $wpdb;
if(file_exists(TEVOLUTION_COMMENT_MODERATE_DIR.'languages/'.$locale.'.mo'))
{
	load_textdomain(TEVOLUTION_COMMENT_MODERATE_DOMAIN,TEVOLUTION_COMMENT_MODERATE_DIR.'languages/'.$locale.'.mo');
}


/*
 * Plugin Deactivation hook
 */
register_deactivation_hook(__FILE__,'comment_moderate_deactivation_hook');
function comment_moderate_deactivation_hook(){

	 delete_option('comment_moderate_redirect_activation');
}

/*
 * Plugin Activation hook
 */
register_activation_hook(__FILE__,'comment_moderate_plugin_activate');
if(!function_exists('comment_moderate_plugin_activate')){
	function comment_moderate_plugin_activate(){	
		global $wpdb;
		update_option('comment_moderate_redirect_activation','Active');
	}
}
add_action('admin_init', 'comment_moderate_plugin_redirect');

/*
Name : comment_moderate_plugin_redirect
Description : Redirect plugin to monetize listing
*/
function comment_moderate_plugin_redirect()
{
	if (get_option('comment_moderate_redirect_activation') == 'Active' && is_plugin_active('Tevolution/templatic.php'))
	{
		update_option('comment_moderate_redirect_activation', 'Deactive');
		wp_redirect(site_url().'/wp-admin/admin.php?page=monetization&activate=comment_moderate');
	}
}

if((isset($_REQUEST['activate']) && $_REQUEST['activate'] == 'comment_moderate'))
{
	add_action('admin_notices','tevolution_comment_moderate_admin_notices');	//action to show notice after plugin activation.
}

/*
Name : tevolution_comment_moderate_admin_notices
Description : message while activate the plugin
*/
function tevolution_comment_moderate_admin_notices()
{
	if(isset($_REQUEST['activate']) && $_REQUEST['activate'] == 'comment_moderate')
	{
		echo '<div class="updated"><p>' . __('Directory - Reviews Manager plugin has been activated. Listing owners will be able to moderate reviews on their listing detail page now. Edit the package in which you want to allow reviews moderation and enable this option "Allow author to moderate comments?". Refer to this <b><a href="http://templatic.com/docs/reviews-manager/">plugin  documentation</a></b> for more detail.',TEVOLUTION_COMMENT_MODERATE_DOMAIN). '</p></div>';
	}
}

if(is_plugin_active('Tevolution/templatic.php'))
{
	include(TEVOLUTION_COMMENT_MODERATE_DIR.'comment_settings.php');	
}else{
	add_action('admin_notices','comment_admin_notices');
}

/*
Name : comment_admin_notices
Description : show notice while tevolution plugin is not activated.
*/
function comment_admin_notices(){
	echo '<div class="error"><p>' . sprintf(__('You have not activated the base plugin %s. Please activate it to use Tevolution-Comment-Moderate plugin.',TEVOLUTION_COMMENT_MODERATE_DOMAIN),'<b>Tevolution</b>'). '</p></div>';
}
/*
 * Function Name: include_comment_moderate_css
 * Return: include comment moderate css
 */
add_action( 'wp_head', 'include_comment_moderate_css' );
function include_comment_moderate_css() {
   /* Register our stylesheet. */
   wp_register_style( 'comment_moderate_css', TEVOLUTION_COMMENT_MODERATE_URL.'css/comment_moderate-style.css' );
   wp_enqueue_style( 'comment_moderate_css' );
}
?>
