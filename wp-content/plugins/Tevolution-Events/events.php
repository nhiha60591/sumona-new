<?php
/*
Plugin Name: Tevolution - Events
Plugin URI: http://templatic.com/docs/tevolution-events-plugin-guide/
Description: Tevolution - Events plugin helps you to add and easily manage events with a special event calendar. You can also set up the price packages and let your users submit events on your site with the help of the Tevolution base plugin.
Version: 2.0.1
Author: Templatic
Author URI: http://templatic.com/
*/
if (defined('WP_DEBUG') and WP_DEBUG == true){ error_reporting(E_ALL ^ E_NOTICE); } else { error_reporting(0); }
ob_start();
// Plugin version
define( 'TEVOLUTION_EVENT_VERSION', '2.0.1' );
define('TEVOLUTION_EVENT_SLUG','Tevolution-Events/events.php');
// Plugin Folder URL
define( 'TEVOLUTION_EVENT_URL', plugin_dir_url( __FILE__ ) );
// Plugin Folder Path
define( 'TEVOLUTION_EVENT_DIR', plugin_dir_path( __FILE__ ) );
// Plugin Root File
define( 'TEVOLUTION_EVENT_FILE', __FILE__ );
//Define domain name
define('EDOMAIN','tevolution_event');
if(!defined('INCLUDE_ERROR'))
	define('INCLUDE_ERROR',__('System might facing the problem in include ',EDOMAIN));
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
ob_start();
global $templatic_settings;
$templatic_settings=get_option('templatic_settings');
if(strstr($_SERVER['REQUEST_URI'],'plugins.php') && !isset($_REQUEST['action'])){
	require_once('wp-updates-plugin.php');
	new WP_Event_Manager_Updates( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
}
if(is_plugin_active('Tevolution/templatic.php'))
{
	/*Include plugin main files.*/
	add_action('init','tmpl_load_plugin_domain');
	
	/* Include the tevolution plugins main file to use the core functionalities of plugin. */
	if(is_plugin_active('Tevolution/templatic.php') && file_exists(WP_PLUGIN_DIR . '/Tevolution/templatic.php')){
		include_once( WP_PLUGIN_DIR . '/Tevolution/templatic.php');
	}else{
		if(function_exists('dd_admin_notices'))
			add_action('admin_notices','dd_admin_notices');
	}
	/* Bundle Box*/
	if(is_admin() && (isset($_REQUEST['page']) && $_REQUEST['page']=='templatic_system_menu')){
		include(TEVOLUTION_EVENT_DIR."bundle_box.php");	
		include(TEVOLUTION_EVENT_DIR."install.php");
	}
	
	add_action('init','set_event_status_draft');
	
	include_once(TEVOLUTION_EVENT_DIR.'events/events.php');
	
	if (function_exists('is_active_addons') && is_active_addons('tevolution_event_manager')){
		if(file_exists(TEVOLUTION_EVENT_DIR.'functions/event_functions.php')){
			include_once(TEVOLUTION_EVENT_DIR.'functions/event_functions.php');
		}
	}
	
}else{
	add_action('admin_notices','event_manager_admin_notices');
}
/* Show the notice if tevolution event manager plugin is not activated. */
function event_manager_admin_notices(){
	echo '<div class="error"><p>' . __('You have not activated the base plugin <b>Tevolution</b>. Please activate it to use Tevolution-Event-manager plugin.',EDOMAIN). '</p></div>';
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),'event_manager_action_links'  );
function event_manager_action_links($links){
	if(!is_plugin_active('Tevolution/templatic.php')){
		return $links;
	}
	
	if (function_exists('is_active_addons') && is_active_addons('tevolution_event_manager')){
		$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=templatic_settings&tab=event_settings' ) . '">' . __( 'Settings', EDOMAIN ) . '</a>',			
			);
	}else{
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=templatic_system_menu' ) . '">' . __( 'Settings', EDOMAIN ) . '</a>',			
		);
	}
	return array_merge( $plugin_links, $links );
}

function tmpl_load_plugin_domain(){
	if(file_exists(TEVOLUTION_EVENT_DIR.'languages/'.get_locale().'.mo')){
		load_textdomain( EDOMAIN,TEVOLUTION_EVENT_DIR.'languages/'.get_locale().'.mo' );
	}
	
}
/*
name : set_event_status_draft
description : set post status to draft
*/
function set_event_status_draft()
{
	global $wpdb;
	$hide_past_event = get_option('event_manager_setting');
	if(isset($hide_past_event['hide_past_event']) && $hide_past_event['hide_past_event'] == 'yes'){
		$today = date_i18n('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')));
		$where = " AND (p.ID in (select $wpdb->postmeta.post_id from $wpdb->postmeta where $wpdb->postmeta.meta_key='set_end_time' and date_format($wpdb->postmeta.meta_value,'%Y-%m-%d %H:%i:%s') < '".$today."')) ";
		$postid_str = $wpdb->get_var("select group_concat(p.ID) from $wpdb->posts p where 1 $where and p.post_status='publish' and p.post_type='event'");
		if($postid_str)
		{
			$listing_ex_status = 'draft';
			$wpdb->query("update $wpdb->posts set post_status=\"$listing_ex_status\" where ID in ($postid_str)");
		}
	}
}
/*
 * Plugin Deactivation hook
 * 
 */
 
register_deactivation_hook(__FILE__,'unregister_event_taxonomy');
function unregister_event_taxonomy(){
	 $post_type = get_option("templatic_custom_post");
	 $taxonomy = get_option("templatic_custom_taxonomy");
	 $tag = get_option("templatic_custom_tags");
	 $taxonomy_slug = $post_type['event']['slugs'][0];
	 $tag_slug = $post_type['event']['slugs'][1];
	 
	 unset($post_type['event']);
	 unset($taxonomy[$taxonomy_slug]);
	 unset($tag[$tag_slug]);
	 update_option("templatic_custom_post",$post_type);
	 update_option("templatic_custom_taxonomy",$taxonomy);
	 update_option("templatic_custom_tags",$tag);
	 unlink(get_template_directory()."/taxonomy-".$taxonomy_slug.".php");
	 unlink(get_template_directory()."/taxonomy-".$tag_slug.".php");
	 unlink(get_template_directory()."/single-event.php");
	 
	  delete_option('event_manager_redirect_activation');
}
register_activation_hook(__FILE__,'event_manager_plugin_activate');
if(!function_exists('event_manager_plugin_activate')){
	function event_manager_plugin_activate(){
		global $wpdb;
		
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_icon'");
		if('term_icon' != $field_check)	{
			$wpdb->query("ALTER TABLE $wpdb->terms ADD term_icon varchar(255) NOT NULL DEFAULT ''");
		}
		update_option('event_manager_redirect_activation','Active');
	}
}

/*
 * Update directory_update_login plugin version after templatic member login
 */
add_action('wp_ajax_event-manager','event_manager_update_login');
function event_manager_update_login()
{
	check_ajax_referer( 'event-manager', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
/* remove wp autoupdates */
add_action('admin_init','event_manager_wpup_changes',20);
function event_manager_wpup_changes(){
	 remove_action( 'after_plugin_row_Tevolution-Events/events.php', 'wp_plugin_update_row' ,10, 2 );
	 
	 /* auto set the event manager settings */
	$event_manager_setting = get_option('event_manager_setting');
	if(@$event_manager_setting['hide_attending_event'] !='No' || @$event_manager_setting['hide_attending_event']==''){
		$event_set_data['hide_attending_event'] ='yes';
		if(is_array($event_manager_setting)){
			update_option('event_manager_setting',array_merge($event_set_data,$event_manager_setting));
		}else{
			update_option('event_manager_setting',$event_set_data);
		}
	}
}
/* add shortcode */

// init process for registering our button
if(isset($_REQUEST['post']) && $_REQUEST['post'] !=''){
	$post = get_post($_REQUEST['post']);
	$post_type = $post->post_type;
}else{
	$post_type = @$_REQUEST['post_type'];
}
 if(isset($post_type) && $post_type == 'page'){ 
	 add_action('init', 'events_tevolution_shortcode_button_init');
 }
 function events_tevolution_shortcode_button_init() {
      global $pagenow;
	 
      //Abort early if the user will never see TinyMCE
      if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
           return;
	  remove_filter("mce_external_plugins", "tevolution_register_tinymce_plugin"); 
      // Add a callback to add our button to the TinyMCE toolbar
      remove_filter('mce_buttons', 'tevolution_add_tinymce_shortcode_button');
      //Add a callback to regiser our tinymce plugin   
      add_filter("mce_external_plugins", "events_tevolution_register_tinymce_plugin"); 
      // Add a callback to add our button to the TinyMCE toolbar
      add_filter('mce_buttons', 'event_tevolution_add_tinymce_shortcode_button');
	
}
//This callback registers our plug-in
function events_tevolution_register_tinymce_plugin($plugin_array) {
    $plugin_array['tevolution_shortcodes'] = plugin_dir_url( __FILE__ ).'js/shortcodes.js';
    return $plugin_array;
}
//This callback adds our button to the toolbar
function event_tevolution_add_tinymce_shortcode_button($buttons) {
      //Add the button ID to the $button array
	array_push($buttons, "tevolution_shortcodes");  
    return $buttons;
}
?>
