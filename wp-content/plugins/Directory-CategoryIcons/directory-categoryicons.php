<?php
/*
Plugin Name: Directory - Category Icons
Plugin URI: http://templatic.com/docs/category-icons/
Description: The Category Icons plugin allows you to associate <a href="http://fortawesome.github.io/Font-Awesome/icons/">fontawesome</a> icons or custom images with your categories. The icon will be displayed in all category lists (i.e. category widgets). 
Version: 1.0
Author: Templatic
Author URI: http://templatic.com/
*/

@define( 'CIDOMAIN', 'category_icon');  
define('CATEGORYICON_VERSION','1.0');
@define('CATEGORYICON_PLUGIN_NAME','Directory - CategoryIcons Plugin');
define('CATEGORYICON_SLUG','Directory-CategoryIcons/directory-categoryicons.php');

$locale = get_locale();
load_textdomain( CIDOMAIN, plugin_dir_path( __FILE__ ).'languages/'.$locale.'.mo' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/* for auto updates */
if(strstr($_SERVER['REQUEST_URI'],'plugins.php')){
	require_once('wp-updates-plugin.php');
	new WPUpdatesDirectoryCategoryIconUpdater( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
}
/* plugin activation hook */
register_activation_hook(__FILE__,'category_icon_plugin_activate');
function category_icon_plugin_activate(){
	global $wpdb;

	$category_icon = $wpdb->get_var("SHOW COLUMNS FROM $wpdb->terms LIKE 'category_icon'");
	if('category_icon' != $field_check)	{
		$wpdb->query("ALTER TABLE $wpdb->terms ADD category_icon varchar(255) NOT NULL DEFAULT ''");
	}
}

add_action('admin_init','tmpl_tevolution_category_icon');


function tmpl_tevolution_category_icon(){

	$taxnow = sanitize_key($_REQUEST['taxonomy']);

	add_action($taxnow.'_edit_form_fields','tmpl_category_customicon_EditFields');
	add_action($taxnow.'_add_form_fields','tmpl_category_customicon_AddFieldsAction');

	add_action('edited_'.$taxnow,'tmpl_category_customicon_AlterFields');

	add_action('created_'.$taxnow,'tmpl_category_customicon_AlterFields');

	add_filter('manage_'.$taxnow.'_custom_column', 'tmpl_manage_category_icon_columns', 10, 3);
	add_filter('manage_edit-'.$taxnow.'_columns', 'tmpl_category_icon_columns');
	
	remove_action( 'after_plugin_row_Directory-CategoryIcons/directory-categoryicons.php', 'wp_plugin_update_row' ,10, 2 );
}


/*
 * Function Name: tmpl_category_customicon_EditFields
 * display catgeory icon upload and text field on edit time
 */
function tmpl_category_customicon_EditFields($tag){
	tmpl_category_customicon_AddFields($tag,'edit');
}


/*
 * Function Name: tmpl_category_customicon_AddFieldsAction
 * display catgeory icon upload and text field on new add time
 */
function tmpl_category_customicon_AddFieldsAction($tag){
	tmpl_category_customicon_AddFields($tag,'add');
}


/*
 * Function Name: tmpl_category_customicon_AddFields
 * display catgeory icon upload and text field on new add or edit time
 */
function tmpl_category_customicon_AddFields($tag,$screen){
	$tax = @$tag->taxonomy;	
	?>
     	<div class="form-field-category">
		<tr class="form-field form-field-category">
			<th scope="row" valign="top"><label for="category_icon"><?php echo __("Category Icon", CIDOMAIN); ?></label></th>
			<td> 
                    <input id="category_icon" type="text" size="60" name="category_icon" value='<?php echo (@$tag->category_icon)? @$tag->category_icon:''; ?>'/>	
                    <?php echo __('Or',CIDOMAIN);?>
                     <a data-id="category_icon" id="Category Icon" type="submit" class="upload_file_button button"><?php  echo __('Browse',ADMINDOMAIN);?></a>
                    <p class="description"><?php 
                    $fontawesome=esc_html('<i class="fa fa-car"></i>');
                    	echo sprintf(__('To display a',CIDOMAIN).' <a href="%s" title="'.__('fontawesome',CIDOMAIN).'">'.__('fontawesome',CIDOMAIN).'</a> '.__('icon (recommended) enter the following code:(e.g.%s). Alternatively you can upload an image (30x20 pixels) by using the Browse button.',CIDOMAIN),'http://fortawesome.github.io/Font-Awesome/icons/',$fontawesome);?></p>    
			</td>
		</tr>
		</div>
	<?php
}


/*
 * Function Name: tmpl_category_icon_columns
 * manage category icon columns 
 */
function tmpl_category_icon_columns($columns){

	$columns['category_icon'] = __('Icon',CIDOMAIN);	

	return $columns;
}


/*
 * Function Name: tmpl_manage_category_icon_columns
 * display category icon in category columns
 */
function tmpl_manage_category_icon_columns($out, $column_name, $term_id){

	global $wpdb;
	$term_table=$wpdb->prefix."terms";		
	$sql="select category_icon from $term_table where term_id=".$term_id;
	$term=$wpdb->get_results($sql);	
	if($term[0]->category_icon ) { $icon = $term[0]->category_icon; }else{ $icon = ''; }
	switch ($column_name) {
		
		case 'category_icon':
				$allowed =  tmpl_category_icon_image_allowed();
				$filename = $icon;
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				if(!in_array($ext,$allowed) ) {
				    $out=$icon;
				}else{
				 $out= '<img src="'.$icon.'"  width="30" height="20">';
				}
			break; 
		default:
			break;
	}
	return $out;
}



/*
 * Function Name: tmpl_category_customicon_AlterFields
 * update category icon in terms table category icon fields
 */
function tmpl_category_customicon_AlterFields($termId){
	global $wpdb;
	$term_table=$wpdb->prefix."terms";		
	$category_icon=$_POST['category_icon'];	
	//update the service price value in terms table field	
	if(isset($_POST['category_icon'])){
		$sql="update $term_table set category_icon='".$category_icon."' where term_id=".$termId;
		$wpdb->query($sql);
	}
}



/*
 * Function Name: tmpl_category_icon_list_cats
 * display category icon or font awesome before category name
 */

add_filter('list_cats','tmpl_category_icon_list_cats',10,2);
function tmpl_category_icon_list_cats($cat_name, $category=''){

	if($category->category_icon!=""){
		$allowed =  tmpl_category_icon_image_allowed();
		$filename = $category->category_icon;
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if(!in_array($ext,$allowed) ) {
		    $category_icon=$category->category_icon."&nbsp;";
		}else{
		 $category_icon= '<img src="'.$category->category_icon.'"  width="30" height="20" title="'.$category->name.'">&nbsp;';
		}

	}
	return $category_icon.$cat_name;
}


/*
 * Function Name: tmpl_category_icon_image_allowed
 * return category icon image type allowed
 */
function tmpl_category_icon_image_allowed(){

	$allowed_type=array('gif','png' ,'jpg','jpeg');
	
	return apply_filters('tmpl_category_icon_image_allowed',$allowed_type);	
}

/*
 * Function Name: tmpl_catgeory_icon_style
 * Return: remove list style when category icon plugin activate
 */
add_action('wp_head','tmpl_catgeory_icon_style');
function tmpl_catgeory_icon_style(){
	?>
	<style type="text/css">
	body .sidebar .widget ul.categories li, 
	body .sidebar .widget ul.browse_by_category li{
		list-style:none;
	}
	.cat-item a img {
		height: 16px;
		vertical-align: middle;
		width: 16px;
		padding-right: 4px;
	}
	.cat-item i {
		height: 16px;
		padding-right: 4px;
		text-align: center;
		width: 16px;
	}
	.cat-item:before{ content:none!important;}
	.cat-item{ padding-left:0!important;}
	</style>
	<?php	
}

/*
 * Function Name: categoryicon_manage_function_script
 * enqueue script and style for thickbox 
 */
add_action('admin_head','categoryicon_manage_function_script'); // to call the script in bottom
function categoryicon_manage_function_script(){
	wp_enqueue_script('function_script',plugin_dir_url( __FILE__ ).'function_script.js',array( 'jquery' ),'',false);
	wp_enqueue_script('thickbox');	
	wp_enqueue_style('thickbox'); 
}

/*
 * Function Name: directory_categoryicon_update_login
 * Return: update directory filter plugin version after templatic member login
 */
add_action('wp_ajax_directory_categoryicon','directory_categoryicon_update_login');
function directory_categoryicon_update_login()
{
	check_ajax_referer( 'directory_categoryicon', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
?>
