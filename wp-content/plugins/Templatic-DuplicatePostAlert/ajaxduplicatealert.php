<?php
/*
Plugin Name: Templatic-DuplicatePostAlert
Plugin URI: http://templatic.com/
Description: Simply provides ability to alert admin if he enter post title which already exists.
Version: 1.0
Author: Templatic
Author URI: http://templatic.com/
*/
error_reporting(0);
// Plugin version
define( 'AJAX_MANAGER_VERSION', '1.0' );
// Plugin Folder URL
define( 'AJAX_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Plugin Folder Path
define( 'AJAX_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// Plugin Root File
define( 'AJAX_MANAGER_PLUGIN_FILE', __FILE__ );
//Define domain name
define('AJAX_DOMAIN','tmpl_ajaxalert');
define('AJAX_MANAGER_FOLDER_NAME','Templatic-Admin-AjaxDuplicateAlert');
@define('PLUGINS_VERSION','1.0');
@define('PLUGINS_SLUG','Templatic-Admin-AjaxDuplicateAlert/ajaxduplicatealert.php');
define('AJAX_INCLUDE_ERROR',__('System might facing the problem in include ',AJAX_DOMAIN));
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//Custom functions file
if(file_exists(AJAX_MANAGER_PLUGIN_DIR."admin_functions.php")){
	include(AJAX_MANAGER_PLUGIN_DIR."admin_functions.php");
}else{
	_e(AJAX_INCLUDE_ERROR. AJAX_MANAGER_PLUGIN_DIR."admin_functions.php",AJAX_DOMAIN);
}
//Custom functions file
?>