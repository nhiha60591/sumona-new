<?php
/*
Plugin Name: Templatic AdManager
Plugin URI: http://templatic.com/docs/templatic-admanager-plugin-guide/
Description:  Templatic AdManager is a powerful banner management system which lets you display ads on your pages, posts and listings.
Version: 1.0.3
Author: Templatic
Author URI: http://templatic.com/
*/
if (defined('WP_DEBUG') and WP_DEBUG == true){ error_reporting(E_ALL ^ E_NOTICE); } else { error_reporting(0); }
/* Plugin version */
define( 'AD_MANAGER_VERSION', '1.0.3' );
/* Plugin Folder URL */
define( 'AD_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/* Plugin Folder Path */
define( 'AD_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
/* Plugin Root File */
define( 'AD_MANAGER_PLUGIN_FILE', __FILE__ );
/* Plugin basename */
define( 'AD_MANAGER_PLUGIN_BASENAME', plugin_basename(__FILE__) );
/* Define domain name */
define('PLUGIN_DOMAIN','temp_admanager');
define('AD_MANAGER_FOLDER_NAME','Templatic-AdManager');
define('PLUGINS_VERSION','1.0');
define('PLUGINS_SLUG','Templatic-AdManager/admanager.php');
define('AD_INCLUDE_ERROR',__('Server might be facing problem in including: ',PLUGIN_DOMAIN));
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
ob_start();
/* Include booking system's main files. */
load_textdomain( PLUGIN_DOMAIN,AD_MANAGER_PLUGIN_DIR.'languages/'.get_locale().'.mo' );
load_plugin_textdomain( PLUGIN_DOMAIN,false, AD_MANAGER_PLUGIN_DIR);
if(strstr($_SERVER['REQUEST_URI'],'plugins.php')){
	require_once('wp-updates-plugin.php');
	new WPAdManagerUpdates( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
}

add_action('admin_init','remove_addmanager_update',20);
function remove_addmanager_update(){
	remove_action( 'after_plugin_row_Templatic-AdManager/admanager.php', 'wp_plugin_update_row' ,10, 2 );
}
/*
 * Include need file
 */	
/*Custom post type language file */
if(file_exists(AD_MANAGER_PLUGIN_DIR."library/admanager_post_type/custom_post_type_lang.php")){
	include(AD_MANAGER_PLUGIN_DIR."library/admanager_post_type/custom_post_type_lang.php");
}else{
	echo "<p>".AD_INCLUDE_ERROR. AD_MANAGER_PLUGIN_DIR."library/admanager_post_type/custom_post_type_lang.php"."</p>";
}
/* Custom post type file */
if(file_exists(AD_MANAGER_PLUGIN_DIR."library/admanager_post_type/custom_post_type.php")){
	include(AD_MANAGER_PLUGIN_DIR."library/admanager_post_type/custom_post_type.php");
}else{
	echo "<p>".AD_INCLUDE_ERROR. AD_MANAGER_PLUGIN_DIR."library/admanager_post_type/custom_post_type.php"."</p>";
}
/* Custom post type file */
/* Hook on plugin activation: start */
register_activation_hook(__FILE__, 'ad_plugin_activate');
register_deactivation_hook(__FILE__, 'ad_plugin_deactivate');
/* Hook on plugin activation: end */
/* Custom functions file */
if(file_exists(AD_MANAGER_PLUGIN_DIR."library/ad_functions.php")){
	include(AD_MANAGER_PLUGIN_DIR."library/ad_functions.php");
}else{
	echo "<p>".AD_INCLUDE_ERROR. AD_MANAGER_PLUGIN_DIR."library/ad_functions.php"."</p>";
}
/* Custom functions file */
/* Widget file */
if(file_exists(AD_MANAGER_PLUGIN_DIR."library/ad_widget.php")){
	include(AD_MANAGER_PLUGIN_DIR."library/ad_widget.php");
}else{
	echo "<p>".AD_INCLUDE_ERROR. AD_MANAGER_PLUGIN_DIR."library/ad_widget.php"."</p>";
}
/* Widget file */
/*
 * Function Name: admanager_update_login
 * Return: update admanager_update_login plugin version after templatic member login
 */
add_action('wp_ajax_admanager','admanager_update_login');
function admanager_update_login()
{
	check_ajax_referer( 'admanager', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
/*
 * Plugin Deactivation hook
 * 
 */
 
register_deactivation_hook(__FILE__,'unregister_admanager_taxonomy');
function unregister_admanager_taxonomy(){
	 $post_type = get_option("templatic_custom_post");
	 
	 unset($post_type['admanager']);
	 update_option("templatic_custom_post",$post_type);
}
?>
