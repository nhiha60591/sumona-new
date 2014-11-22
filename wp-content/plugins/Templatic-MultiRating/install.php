<?php
include_once(MULTIPLE_RATING_PLUGIN_DIR.'/templatic_rating_system_main.php');
 /*
Name : add_multi_rating_system_admin_menu
Description : allow admin to add rating title from backend.
*/
if(function_exists('is_active_rating_addons') && is_active_rating_addons('templatic_multiple_rating')){
	add_action('admin_menu', 'add_multi_rating_system_admin_menu');
	if(!function_exists('add_multi_rating_system_admin_menu')){
	function add_multi_rating_system_admin_menu(){
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'templatic_multiple_rating'){
			$templatic_rating_system_icon = MULTIPLE_RATING_PLUGIN_URL.'/images/favicon-active.png';
		}else{
			$templatic_rating_system_icon = MULTIPLE_RATING_PLUGIN_URL.'/images/favicon-active.png';
		}
		if(!is_plugin_active('Tevolution/templatic.php')){
			add_submenu_page('options-general.php', 'Multi Rating','Multi Rating','administrator', 'templatic_multiple_rating', 'templatic_multiple_rating_settings', $templatic_rating_system_icon, 80);
		}
		
	}
	}
}
/*
Name :add_multi_rating_tevolution_admin_menu
Description : add multi rating menu to tevolution menu
*/
add_action('templ_add_admin_menu_', 'add_multi_rating_tevolution_admin_menu', 30);
function add_multi_rating_tevolution_admin_menu(){
	$menu_title2 = __('Multi Rating', RATING_DOMAIN);
	global $location_settings_option;
	
	$location_settings_option=add_submenu_page('templatic_system_menu', $menu_title2, $menu_title2,'administrator', 'templatic_multiple_rating', 'templatic_multiple_rating_settings');	
	
}
/*
Name : is_active_addons
Description : return each addons is activated or not
*/
function is_active_rating_addons($key)
{
	$act_key = get_option($key);
	if ($act_key != '')
	{
		return true;
	}
}
/*
Name : remove_tevolution_rating
Description : remove the rating option from tevolution.
*/
add_action('admin_init','remove_tevolution_rating');
function remove_tevolution_rating()
{
	if(is_plugin_active('Tevolution/templatic.php')){
		$tmpdata = get_option('templatic_settings');
		$tmpdata['templatin_rating'] = '';
		update_option('templatic_settings',$tmpdata);
		remove_action('templatic_general_setting_data','rating_setting_data',12);
	}
}
?>