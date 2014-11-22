<?php
/*
Plugin Name: Tevolution - Directory
Plugin URI: http://templatic.com/docs/directory-plugin-guide/
Description: Tevolution - Directory plugin is specially built to turn your site into a powerful listings directory for any niche. To be used with Tevolution, this plugin is loaded with a bundle of features like listing submissions, power search, unlimited categories, custom fields and subscription or per listing payment packages. .
Version: 2.0.1
Author: Templatic
Author URI: http://templatic.com/
*/
ob_start();
// Plugin version
	define( 'DIR_DOMAIN', 'templatic');  //tevolution* deprecated
	if(!defined('ADMINDOMAIN'))
		define( 'ADMINDOMAIN', 'tevolution' ); //tevolution* deprecated
 //tevolution* deprecated
 //tevolution* deprecated
define( 'TEVOLUTION_DIRECTORY_VERSION', '2.0.1' );
define('TEVOLUTION_DIRECTORY_SLUG','Tevolution-Directory/directory.php');
// Plugin Folder URL
define( 'TEVOLUTION_DIRECTORY_URL', plugin_dir_url( __FILE__ ) );
// Plugin Folder Path
define( 'TEVOLUTION_DIRECTORY_DIR', plugin_dir_path( __FILE__ ) );
// Plugin Root File
define( 'TEVOLUTION_DIRECTORY_FILE', __FILE__ );
//Define domain name

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
ob_start();
global $templatic_settings;
$templatic_settings=get_option('templatic_settings');
if(strstr($_SERVER['REQUEST_URI'],'plugins.php')){
	require_once('wp-updates-plugin.php');
	new WPDirectoryUpdates( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
}
/*
Name: is_cdlocalization
Desc: check is it codestyling localization or not
*/
if(!function_exists('is_cdlocalization')){
	function is_cdlocalization(){
		if(is_plugin_active('codestyling-localization/codestyling-localization.php')){
			return true;
		}else{
			return false;
		}
	}
}
if(is_plugin_active('Tevolution/templatic.php'))
{
	//Include plugin main files.
	
	$locale = get_locale();
	if(is_cdlocalization()){ 
		   
		   if(is_admin()){
				load_textdomain( 'dirtemplatic',TEVOLUTION_DIRECTORY_DIR.'languages/templatic-admin-'.$locale.'.mo' );
				load_textdomain( 'templatic-admin',TEVOLUTION_DIRECTORY_DIR.'languages/templatic-admin-'.$locale.'.mo' );
		   }else{
				load_textdomain( DIR_DOMAIN,TEVOLUTION_DIRECTORY_DIR.'languages/dirtemplatic-'.$locale.'.mo' );
		   }
	}else{
		if(is_admin()){
			load_textdomain( 'templatic-admin', TEVOLUTION_DIRECTORY_DIR.'languages/templatic-admin-'.$locale.'.mo' );
		}else{
			load_textdomain( DIR_DOMAIN, TEVOLUTION_DIRECTORY_DIR.'languages/dirtemplatic-'.$locale.'.mo' );
		}
	}
	//Include the tevolution plugins main file to use the core functionalities of plugin.
	if(is_plugin_active('Tevolution/templatic.php') && file_exists(WP_PLUGIN_DIR . '/Tevolution/templatic.php')){
		include_once( WP_PLUGIN_DIR . '/Tevolution/templatic.php');
	}else{
		add_action('admin_notices','directory_admin_notices');
	}
	
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
	
	/* Bundle Box*/
	if(is_admin() && (isset($_REQUEST['page']) && $_REQUEST['page']=='templatic_system_menu')){
		include(TEVOLUTION_DIRECTORY_DIR."bundle_box.php");	
		include(TEVOLUTION_DIRECTORY_DIR."install.php");
	}
	include(TEVOLUTION_DIRECTORY_DIR.'listing/listing.php');	
	
	
	if (function_exists('is_active_addons') && is_active_addons('tevolution_directory')){	
		include(TEVOLUTION_DIRECTORY_DIR.'functions/manage_function.php');		
	}
	
	
}else{
	add_action('admin_notices','directory_admin_notices');
}
/* This function display admin notices for activate Tevolution plugin activate */
function directory_admin_notices(){
	echo '<div class="error"><p>' . sprintf(__('You have not activated the base plugin %s. Please activate it to use Tevolution-Directory plugin.','templatic-admin'),'<b>Tevolution</b>'). '</p></div>';
}

/* Plugin action link filter call for display settings link in plugins page before deactivate plugin link */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),'directory_action_links'  );
function directory_action_links($links){
	if(!is_plugin_active('Tevolution/templatic.php')){
		return $links;
	}
	if (function_exists('is_active_addons') && is_active_addons('tevolution_directory')){
		$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=templatic_settings' ) . '">' . __( 'Settings', 'templatic-admin' ) . '</a>',				
		);
	}else{
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=templatic_system_menu' ) . '">' . __( 'Settings', 'templatic-admin' ) . '</a>',
		);
	}
	return array_merge( $plugin_links, $links );
}
/*
 * Plugin Deactivation hook
 */
register_deactivation_hook(__FILE__,'unregister_directory_taxonomy');
function unregister_directory_taxonomy(){
	 $post_type = get_option("templatic_custom_post");
	 $taxonomy = get_option("templatic_custom_taxonomy");
	 $tag = get_option("templatic_custom_tags");
	 $taxonomy_slug = $post_type['listing']['slugs'][0];
	 $tag_slug = $post_type['listing']['slugs'][1];
	 
	 unset($post_type['listing']);
	 unset($taxonomy[$taxonomy_slug]);
	 unset($tag[$tag_slug]);
	 update_option("templatic_custom_post",$post_type);
	 update_option("templatic_custom_taxonomy",$taxonomy);
	 update_option("templatic_custom_tags",$tag);
	 unlink(get_template_directory()."/taxonomy-".$taxonomy_slug.".php");
	 unlink(get_template_directory()."/taxonomy-".$tag_slug.".php");
	 unlink(get_template_directory()."/single-listing.php");
}

/*Plugin activation hook for for set default value */
register_activation_hook(__FILE__,'directory_plugin_activate');
if(!function_exists('directory_plugin_activate')){
	function directory_plugin_activate(){	
		global $wpdb;
		/* Alter term_icon field in terms table if not exists */
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_icon'");
		if('term_icon' != $field_check)	{
			$wpdb->query("ALTER TABLE $wpdb->terms ADD term_icon varchar(255) NOT NULL DEFAULT ''");
		}		
	}
}

/*
 *update directory_update_login plugin version after templatic member login
 */
add_action('wp_ajax_directory','directory_update_login');
function directory_update_login()
{
	check_ajax_referer( 'directory', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
/* remove wp autoupdates */
add_action('admin_init','directory_wpup_changes',20);
function directory_wpup_changes(){
		remove_action( 'after_plugin_row_Tevolution-Directory/directory.php', 'wp_plugin_update_row' ,10, 2 );

		$tmplsettings = get_option('templatic_settings');
		$map_settings = get_option('maps_setting');
		if(@$tmplsettings['category_googlemap_widget'] !='no' && @$map_settings['category_googlemap_widget'] =='yes'){ 
			$tmpldata['category_googlemap_widget']='yes';
			update_option('templatic_settings',array_merge($tmplsettings,$tmpldata));
		}
}
if(!defined('INCLUDE_ERROR'))
	define('INCLUDE_ERROR',__('System might facing the problem in include ',DIR_DOMAIN));
?>