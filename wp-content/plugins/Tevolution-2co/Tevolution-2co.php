<?php 
/*
 *Plugin Name: Tevolution 2Checkout
 *Plugin URI: http://templatic.com/payment-gateways/
 *Description: Seamlessly integrate 2Checkout payment gateway with the Tevolution from templatic.com.
 *Version: 1.0.1
 *Author: Templatic
 *Author URI: http://templatic.com
*/
//define the plugin directory path
define('TEMPL_FILE_PATH_2co', plugin_dir_path(__FILE__));
define('DOMAIN', 'templatic');
define( 'TWOCHECKOUT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
add_action('activated_plugin','save_error_2co');
function save_error_2co(){
    update_option('plugin_error',  ob_get_contents());
}
//echo get_option('plugin_error');
require_once ABSPATH.'wp-admin/includes/upgrade.php';

//Create Setting link for plugin: Start
add_filter( 'plugin_action_links_' . TWOCHECKOUT_PLUGIN_BASENAME,'twocheckout_action_links'  );
function twocheckout_action_links($links){

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=monetization&tab=payment_options' ) . '">' . __( 'Settings', DOMAIN ) . '</a>',			
	);
	return array_merge( $plugin_links, $links );
}
//Create Setting link for plugin: End


add_action('admin_init','twocheckout_init_function_callback');
if(!function_exists('twocheckout_init_function_callback')){
	function twocheckout_init_function_callback(){
		if( !isset($_GET['act_plug']) ){
			if(is_plugin_active("Tevolution-2co/Tevolution-2co.php")){
				if(!is_plugin_active("Tevolution/templatic.php")){?>
					<div id="message" class="error">
						<p>
							<?php  _e("You have not activated a base plugin Tevolution! Please activate it to start using Tevolution 2Checkout",DOMAIN);?>
						</p>
					</div>
			<?php
				}
			}
		}
		if(get_option('2co_plugin_active') == 'true'){
			update_option('2co_plugin_active', 'false');
			wp_safe_redirect(admin_url('plugins.php?act_plug=2co'));
		}
		if( isset($_GET['act_plug']) && $_GET['act_plug'] == "2co" ){
			if(is_plugin_active("Tevolution-2co/Tevolution-2co.php")){
				if(!is_plugin_active("Tevolution/templatic.php")){?>
					<div id="message" class="error">
						<p>
							<?php  _e("You have not activated a base plugin Tevolution! Please activate it to start using Tevolution 2Checkout",DOMAIN);?>
						</p>
					</div>
			<?php
				}else{
			?>	
					<div id="message" class="updated">
						<p>
							<?php  _e("Tevolution 2Checkout is active now, <a href='".admin_url( 'admin.php?page=monetization&tab=payment_options' )."'>click here</a> to get started. For detailed information, refer the <a href='http://templatic.com/docs/2co-2checkout/' target='_blank'>users guide</a>",DOMAIN);?>
						</p>
					</div>
			<?php	
				}
			}
		}
	}
}


//call function file
require_once TEMPL_FILE_PATH_2co.'2co_function.php'; 

// Activation and Deactivation hooks for plugin
register_activation_hook(__FILE__,'templ_add_method_install_2co');
register_deactivation_hook(__FILE__,'templ_add_method_deactivate_2co');
?>