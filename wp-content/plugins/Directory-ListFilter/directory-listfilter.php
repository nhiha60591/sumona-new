<?php
/*
Plugin Name: Directory - List Filter
Plugin URI: http://templatic.com/docs/list-filter/
Description: Filter category pages and search results with a AJAX-based filter widget. Works with your custom fields as well!
Version: 1.0.2
Author: Templatic
Author URI: http://templatic.com/
*/



define('FILTERS_FOLDER_NAME','Tevolution');
define('T_FILTERS_VERSION','1.0.2');
@define('FILTERS_PLUGIN_NAME','Directory - List Filter Plugin');
define('T_FILTERS_SLUG','Directory-ListFilter/directory-listfilter.php');

/* define Absolute folder path for the plugin */
define('SEARCH_FILTER_FOLDER_PATH', plugin_dir_path( __FILE__ ));


// Plugin Folder URL
define( 'SEARCH_FILTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//Define domain
define('SF_DOMAIN','search_filter');



include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	

if(is_plugin_active('Tevolution/templatic.php') && file_exists(WP_PLUGIN_DIR . '/Tevolution/templatic.php')){
	include_once( WP_PLUGIN_DIR . '/Tevolution/templatic.php');
	load_textdomain( 'search_filter',SEARCH_FILTER_FOLDER_PATH.'languages/'.get_locale().'.mo' );
	/* widget and finctions files */
	require_once(plugin_dir_path( __FILE__ ).'filter-functions.php' );
}else{
	add_action('admin_notices','sf_admin_notices');
}
	
/* for auto updates */
if(strstr($_SERVER['REQUEST_URI'],'plugins.php')){
	require_once('wp-updates-plugin.php');
	new WPUpdatesDirectoryFilterUpdater( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
}
/*
Name : sf_admin_notices
Description : warning message if Tevolution is not installed
*/	
function sf_admin_notices(){
		echo '<div class="error"><p>' . sprintf(__('You have not activated the base plugin %s. Please activate it to use Tevolution Filters plugin.','templatic-admin'),'<b>Tevolution</b>'). '</p></div>';
}

/*
 * Function Name:strip_array_indices
 * Front side add css and javascript file in side html head tag 
 */
if(!function_exists('strip_array_indices')){
	function strip_array_indices( $ArrayToStrip ) {
		if(!empty($ArrayToStrip)){
			foreach( $ArrayToStrip as $objArrayItem) {
				$NewArray[] =  $objArrayItem;
			}
		}
		return( $NewArray );
	}
}
//include plugin files
require_once(plugin_dir_path( __FILE__ ).'filter-widget.php' );	

function tmpl_search_filter_activate() {
	
	update_option('tmpl_search_filter_activation','Active');
	require_once(plugin_dir_path( __FILE__ ).'install.php' );

}
register_activation_hook( __FILE__, 'tmpl_search_filter_activate');

/* for styling and scripting */
add_action('wp_enqueue_scripts', 'sf_styles_scripts');
add_action('admin_head', 'sf_styles_scripts',13);
function sf_styles_scripts()
{
	global $wp_locale;
	/* add a css for plugin */
	wp_enqueue_style( 'searchwidget-style',  SEARCH_FILTER_PLUGIN_URL.'css/style.css' );
	if(is_plugin_active('Tevolution/templatic.php') && file_exists(WP_PLUGIN_DIR . '/Tevolution/templatic.php')){	
		wp_enqueue_script('jquery-ui-datepicker');
			 /*localize our js */
			$aryArgs = array(
				'monthNames'        => strip_array_indices( $wp_locale->month ),
				'monthNamesShort'   => strip_array_indices( $wp_locale->month_abbrev ),
				'monthStatus'       => __( 'Show a different month', SF_DOMAIN ),
				'dayNames'          => strip_array_indices( $wp_locale->weekday ),
				'dayNamesShort'     => strip_array_indices( $wp_locale->weekday_abbrev ),
				'dayNamesMin'       => strip_array_indices( $wp_locale->weekday_initial ),
				// is Right to left language? default is false
				'isRTL'             => @$wp_locale->is_rtl,
			);
		 
			/* Pass the array to the enqueued JS */
		wp_localize_script( 'jquery-ui-datepicker', 'objectL11tmpl', $aryArgs );
	}
	if(is_admin()){	
	?>
	<script>
		jQuery(document).ready(function(){
				/* show the field according to field type on custom field form */
				jQuery('#tax_name #ctype').change(function(){		
						var ctype = jQuery(this).val();
						if(ctype == 'image_uploader' || ctype == 'coupon_uploader' || ctype == 'upload' || ctype == 'oembed_video' || ctype == 'heading_type' || ctype == 'post_categories' || ctype == 'select' || ctype == 'multicity')
						{
							jQuery('#form_table #sf_field').hide();
						}
						else
							{
								jQuery('#form_table #sf_field').show();
							}
					});
			
			});
	</script>	
	<?php
	}	?>
	<style>
		.searchable_fields p{
			cursor: move;
		}
	</style>
	<?php
}

// for create new filed in 
add_action('tmpl_extra_show_in_field','tmpl_show_the_field_in');

// redirection after plugin activation
add_action('admin_init', 'tmpl_search_filter_redirect');
/*
Name : search_filter_redirect
Description : Redirect on plugin coupon settings
*/
function tmpl_search_filter_redirect()
{
	if (get_option('tmpl_search_filter_activation') == 'Active' && is_plugin_active('Tevolution/templatic.php'))
	{
		update_option('tmpl_search_filter_activation', 'Deactive');
		wp_redirect(site_url().'/wp-admin/widgets.php?action=filter_activate');
	}
	
	remove_action( 'after_plugin_row_Directory-ListFilter/directory-listfilter.php', 'wp_plugin_update_row' ,10, 2 );
}

/* save sort order for custom field when update custom fields */
add_action('save_post','tmpl_set_field_order');

/* register widget */
add_action( 'widgets_init', 'register_search_filters' );
/*
Name : register_search_filters
Description : register a widget for search filter
*/
function register_search_filters() {
    register_widget( 'searchFilters_Widget' );
}

/* put filter listing below category title */
add_action('directory_subcategory','add_selected_filters',1);

/* put filter listing below listing archive title */
add_action('directory_after_archive_title','add_selected_filters',1);

/* put filter listing below event archive title */
add_action('event_after_archive_title','add_selected_filters',1);

/* put filter listing below event category title */
add_action('event_after_subcategory','add_selected_filters',1);

/* put filter listing on search listing page */
add_action('directory_after_search_title','add_selected_filters',1);

/* put filter listing on search page */
add_action('directory_after_search_title','add_selected_filters',1);

/* put filter event on search page */
add_action('event_after_search_title','add_selected_filters',1);

/* put filter listing on property pages */
add_action('property_after_subcategory','add_selected_filters',1);



/* for change month */
add_action('wp_ajax_tmpl_change_calendar','tmpl_change_calendar');
add_action('wp_ajax_nopriv_tmpl_change_calendar','tmpl_change_calendar');

//add_action('directory_after_post_loop','tmpl_paginat');
/* End of file */
/*
 * Function Name: directory_filter_update_login
 * Return: update directory filter plugin version after templatic member login
 */
add_action('wp_ajax_directory_filter','directory_filter_update_login');
function directory_filter_update_login()
{
	check_ajax_referer( 'directory_filter', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
?>
