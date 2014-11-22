<?php
 /*
Name : templatic_multiple_rating_settings
Description : include file for add a rating title for different taxonomy.
*/
function templatic_multiple_rating_settings(){
	global $wpdb;
?>
    <div class="wrap">
     <div class="icon32" id="icon-options-general"><br></div>
      <h2><?php _e('Multi Rating Settings', RATING_DOMAIN); ?></h2>
    <?php	
	
	include_once("templatic_rating_setting.php");
	echo "</div>";
}
 /*
Name : attach_rating_backend_stylesheets_scripts
Description : include css file for backend styling.
*/
add_action('admin_head','attach_rating_backend_stylesheets_scripts');
if(!function_exists('attach_rating_backend_stylesheets_scripts')){
	function attach_rating_backend_stylesheets_scripts(){
		echo '<link media="all" type="text/css" href="'.MULTIPLE_RATING_PLUGIN_URL."css/admin_rating_css.css".'" rel="stylesheet">';
	}
}
?>