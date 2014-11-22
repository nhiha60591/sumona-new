<?php 
ob_start();
include "more-file/membership-tab.php";
if (defined('WP_DEBUG') and WP_DEBUG == true){
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    error_reporting(0);
}

if(file_exists(trailingslashit ( get_template_directory() ) . 'library/supreme.php'))
	require_once( trailingslashit ( get_template_directory() ) . 'library/supreme.php' ); // contain all classes and core function pf the framework
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
	define( 'THEME_DOMAIN', 'templatic');  //tevolution* deprecated
	define( 'ADMINDOMAIN', 'templatic-admin' ); //tevolution* deprecated
	
define('TEMPLATE_URI',trailingslashit(get_template_directory_uri()));
define('TEMPLATE_DIR',trailingslashit(get_template_directory()));
$theme = new Supreme(); /* Part of the framework. */
$page= (isset($_REQUEST['page']))? $_REQUEST['page']: '';
if(is_admin() && ($pagenow =='themes.php' || $pagenow =='update-core.php' || $pagenow =='post.php' || $pagenow =='edit.php'|| $pagenow =='admin-ajax.php' || trim($page) == trim('tmpl_theme_update')) && file_exists(get_template_directory().'/wp_theme_update.php')){
	require_once('wp_theme_update.php');	
	new WPUpdates_Supreme_Updater( 'http://templatic.com/updates/api/', basename(get_template_directory_uri()) );
}
/*------------------------
  Theme setup function.  This function adds support for theme features and defines the default theme
  actions and filters.
 -----------------------------*/ 
function theme_activation(){
	global $pagenow;	
	if ( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
		$b = array(
				'supreme_logo_url' 					=> get_template_directory_uri()."/images/logo.png",
				'supreme_site_description'			=> 1,
				'customcss'                         => 1,
				'display_publish_date'				=> 1,
				'display_post_terms'				=> 1,
				'supreme_display_noimage'			=> 1,
				'supreme_archive_display_excerpt'	=> 1,
				'templatic_excerpt_length'			=> 27,
				'display_header_text'				=> 1,
				'tmpl_mobile_view'				=> 1,
				'supreme_show_breadcrumb'			=> 1,
				'footer_insert' 					=> '<p class="copyright">&copy; '.date('Y').' <a href="http://templatic.com/demos/directory">Directory 2</a>. &nbsp;Designed by <a href="http://templatic.com" class="footer-logo"><img src="'.get_template_directory_uri().'/library/images/templatic-wordpress-themes.png" alt="WordPress Directory Theme" /></a></p>',
				'theme_activate'                     => 1,
				
			);
		if(function_exists('supreme_prefix'))
			$supreme_prefix=supreme_prefix();
		else
			$supreme_prefix=sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
		
		/*Check theme activate option on theme setting option for if already theme activation setting set then didnt resetting on theme switch */
		$theme_settings=get_option($supreme_prefix.'_theme_settings');
		if(!$theme_settings['theme_activate']){ 
			update_option($supreme_prefix.'_theme_settings',$b);
			}
		

	}
	
}
/*
 * Name: include_auto_install_xml_file
 * Desc: include xml file for auto install.
 * */ 
function include_auto_install_xml_file()
{
	if(isset($_REQUEST['dummy_insert']) && $_REQUEST['dummy_insert']){
		/* check if theme specific auto install file is exists or not */
		$theme_name = strtolower(wp_get_theme());
		if(file_exists(get_template_directory().'/library/functions/'.$theme_name.'_auto_install/auto_install_xml.php')){
			require_once (get_template_directory().'/library/functions/'.$theme_name.'_auto_install/auto_install_xml.php');
		}else{
			if(file_exists(get_template_directory().'/library/functions/auto_install/auto_install_xml.php')){
				require_once (get_template_directory().'/library/functions/auto_install/auto_install_xml.php');
			}
		}	
	}
}

if(!function_exists('tmpl_wp_is_mobile')){
	/*
	Check if device is mobile or not. Return true if mobile devie is detected
	*/
	if(function_exists('supreme_prefix')){
		$pref = supreme_prefix();
	}else{
		$pref = sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
	}
	$theme_options = get_option($pref.'_theme_settings');
	$is_mobile_enabled= @$theme_options['tmpl_mobile_view'];
	if($is_mobile_enabled !=0){
		$is_mobile_enabled=1;
	}
	function tmpl_wp_is_mobile(){
		if ( (wp_is_mobile() && $is_mobile_enabled == 1  || (isset($_REQUEST['device']) && $_REQUEST['device']=='mobile')) && (!preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT'])) && !strstr('windows phone',$_SERVER['HTTP_USER_AGENT'])) && $is_mobile_enabled == 1){ /* if not desktop then return true */	
			return true;
		}else{	
			return false;
		}	
	}

}

add_action('admin_init','include_auto_install_xml_file'); 
add_action('admin_init','theme_activation'); 
add_action( 'after_setup_theme', 'supreme_theme_setup',11 );
function supreme_theme_setup() {

	/* Get action/filter hook prefix. */
	$prefix = supreme_prefix(); // Part of the framework, cannot be changed or prefixed.
	/* Begin localization */
	
	
	$locale = get_locale();
	//get_template_directory().'/languages/'.$locale.'.mo';
	
	if(is_admin()){
		
		if(file_exists(get_stylesheet_directory().'/languages/'.$locale.'.mo'))
		{ 
			load_textdomain( ADMINDOMAIN, get_stylesheet_directory().'/languages/admin-'.$locale.'.mo');
		}else{
			load_textdomain( ADMINDOMAIN, get_template_directory().'/languages/admin-'.$locale.'.mo');
		}
	}else{
		if(file_exists(get_stylesheet_directory().'/languages/'.$locale.'.mo'))
		{
			load_textdomain(THEME_DOMAIN, get_stylesheet_directory().'/languages/'.$locale.'.mo');
		}else{
			load_textdomain(THEME_DOMAIN, get_template_directory().'/languages/'.$locale.'.mo');
		}
	}
	/* End localization */
	if(file_exists(get_template_directory().'/library/functions/functions.php')){
		require_once(get_template_directory().'/library/functions/functions.php'); // framework functions file 
	}
	
	
	
	/* Add framework menus. */
	add_theme_support( 'supreme-core-menus', array( // Add core menus.
		'primary',
		'secondary',
		'subsidiary'
		) );
	/* Register additional menus */
	
	/* Add framework sidebars */
	/* add sidebar support in theme , want to remove from child theme as remove theme support from child theme's functions file */
	
	add_theme_support( 'supreme-core-sidebars', array( // Add sidebars or widget areas.
				'header',
				'mega_menu',
				'secondary_navigation_right',
				'home-page-banner',
				'home-page-above-content',
				'home-page-content',
				'home-page-below-content',
				'before-content',
				'entry',
				'after-content',
				'front-page-sidebar',
				'author-page-sidebar',
				'post-listing-sidebar',
				'post-detail-sidebar',
				'primary-sidebar',
				'after-singular',
				'subsidiary',
				'subsidiary-2c',
				'subsidiary-3c',
				'contact_page_widget',
				'contact_page_sidebar',
				'supreme_woocommerce',
				'home-page-above-footer',
				'footer'
				) );
	/* add theme support for menu */
	/* Add framework menus. */
	add_theme_support( 'supreme-core-menus', array( // Add core menus.
				'primary',
				'secondary',
				//'subsidiary',
				'footer',		
	) );
	add_theme_support( 'post-formats', array(
		'aside',
		'audio',
		'gallery',
		'image',
		'link',
		'quote',
		'video'
		) );
	add_post_type_support( 'post', 'post-formats' ); // support post format
	add_post_type_support( 'portfolio', 'post-formats' ); // for portfolio slides option in slider
	add_theme_support( 'supreme_banner_slider' ); // work with home page banner slider
	add_theme_support( 'supreme-show-commentsonlist' ); // to show comments counting on listing
	add_theme_support( 'tmpldir-core-widgets' ); // to support widgets 
	add_theme_support( 'supreme-core-shortcodes' ); // to support short codes
	add_theme_support("home_listing_type_value");
	add_theme_support("taxonomy_sorting");
	add_theme_support("google_map"); // Show gogole map if location manager active
	add_theme_support("tevolution_my_favourites"); // Show my favourites & add to favourites with tevolution
	add_theme_support("tevolution_author_listing"); // show author listing widget with tevolution
	add_theme_support("map_fullwidth_support");
	add_theme_support("slider-post-inslider");
	add_theme_support("slider-post-content");
	
	/* theme support for default page views */
	add_theme_support("tmpl_show_pageviews");
	/* Home page settings to show the different post types listings on home page */
	add_theme_support("theme_home_page");

	add_action('init','remove_home_page_feature_listing_filter');
	/* Add theme support for framework layout extension. */
	add_theme_support( 'theme-layouts', array( // Add theme layout options.
		'1c',
		'2c-l',
		'2c-r',
		) );
	/* Add theme support for other framework extensions */
	
	//add_theme_support( 'custom-header' );
	add_theme_support( 'post-stylesheets' );
	add_theme_support( 'loop-pagination' );
	add_theme_support( 'breadcrumb-trail' );
	add_theme_support( 'supreme-core-theme-settings', array( 'footer' ) );
    if ( !current_user_can('edit_posts') ) {
		//show_admin_bar(false);
	}
	/* Add theme support for WordPress features. */
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'add-listing-to-favourite' );
	add_theme_support("tev_taxonomy_sorting_opt");
	//add_theme_support("tev_taxonomy_excerpt_opt");
	/* Add theme support for WordPress background feature */
	add_theme_support( 'custom-background', array (
		'default-color' => '',
		'default-image' => '',
		'wp-head-callback' => 'supreme_custom_background_callback',
		'admin-head-callback' => '',
		'admin-preview-callback' => ''
	));
	
		
	/* to provide a option of posts per slide */
	add_theme_support('postperslide');	
	/*

	/* Changes tev general settings and options should be stay same in new version */

	
	/* Modify excerpt more */
	add_filter('excerpt_length', 'supreme_excerpt_length',11);
	add_filter('excerpt_more', 'new_excerpt_more');
	/* Wraps <blockquote> around quote posts. */
	add_filter( 'the_content', 'supreme_quote_post_content' );
	add_filter( 'embed_defaults', 'supreme_embed_defaults' ); // Set default widths to use when inserting media files
	add_filter( 'sidebars_widgets', 'supreme_disable_sidebars' );
	/* Add aditional layouts */
	add_filter( 'theme_layouts_strings', 'supreme_theme_layouts' );
	###### ACTIONS ######
	/* Load resources into the theme. */
	add_action( 'wp_enqueue_scripts', 'tmpl_theme_css_scripts' ,20);
	/* Register new image sizes. */
	add_action( 'init', 'supreme_register_image_sizes' );
	
	/* Set theme specific options */
	add_action( 'admin_init', 'tmpl_set_themesettings' );
	/* Assign specific layouts to pages based on set conditions and disable certain sidebars based on layout choices. */
	add_action( 'template_redirect', 'supreme_layouts' );
	
	/* Register additional widget areas. */
	add_action( 'widgets_init', 'tmpldir_register_sidebars', 11 ); // Number 11 indicates custom sidebars should be registered after Hybrid Core Sidebars
	/* WooCommerce Functions. */
	if ( function_exists( 'is_woocommerce' ) ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
	}
	/* Set content width. */
	supreme_set_content_width( 600 );
	/****** Theme related files ******/
	
	/* add action to save tev general settings for theme design */

	if(file_exists(get_stylesheet_directory()."/functions/childtheme-functions.php")){
		include_once(get_stylesheet_directory()."/functions/childtheme-functions.php"); //child theme directory functions file
	}
	if ( get_header_textcolor()=='blank') { ?>
	<style type="text/css">
	#site-title, #site-description {
		text-indent: -99999px;
	}
	</style>
	<?php }
	remove_action("wp_head", "supreme2_view_counter");
	add_filter('tev_gravtar_size','tev_gravtar_size_hook');
	global $pagenow;
	
	if(is_admin() && is_writable(WP_CONTENT_DIR."/plugins") && is_readable(get_template_directory()) && ($pagenow=='themes.php' || $pagenow=='theme-install.php') ){
		$tev_zip = get_template_directory_uri()."/Tevolution.zip";
		$tev_zip_path = get_template_directory()."/Tevolution.zip";
		$dir_zip = get_template_directory_uri()."/Tevolution-Directory.zip";
		$dir_zip_path = get_template_directory()."/Tevolution-Directory.zip";
		$loc_zip = get_template_directory_uri()."/Tevolution-LocationManager.zip";
		$loc_zip_path = get_template_directory()."/Tevolution-LocationManager.zip";
		
		$target_path1 = get_tmpl_plugin_directory()."Tevolution.zip";  // change this to the correct site path
		$target_path2 = get_tmpl_plugin_directory()."Tevolution-Directory.zip";  // change this to the correct site path
		$target_path3 = get_tmpl_plugin_directory()."Tevolution-LocationManager.zip";  // change this to the correct site path
		
		$plug_path1 = "Tevolution/templatic.php";  // change this to the correct site path
		$plug_path2 = "Tevolution-Directory/directory.php";  // change this to the correct site path
		$plug_path3 = "Tevolution-LocationManager/location-manager.php";  // change this to the correct site path		
	
		$on_go = get_option('tev_on_go');
		if(!$on_go){ $on_go =0; }
		if ( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' && $on_go == 0 ) {
		if(file_exists($tev_zip_path))
			zip_copy( $tev_zip, $target_path1, $plug_path1 );
		if(file_exists($dir_zip_path))
			zip_copy( $dir_zip, $target_path2, $plug_path2 );
		if(file_exists($loc_zip_path))
			zip_copy( $loc_zip, $target_path3, $plug_path3, $add_msg =1 );
		}
	}
	/* code to auto extract plugins END*/
	add_filter( 'sidebars_widgets', 'directory_disable_sidebars' );
} 
/* change gravatra size on author page */
function tev_gravtar_size_hook(){
	return 352;
}
/* Show authentication message */
function dir_one_click_install(){
	echo "<div id='ajax-notification' class='updated'><p><span style='color:red;'>AUTHENTICATION REQUIRED:</span>".__('Your server does not allow automated plugin activation so you will have to activate the plugins manually one by one.',ADMINDOMAIN)."</p>  </div>";
}
/* code to auto extract plugins  START*/	 
function zip_copy( $source, $target, $plug_path, $add_msg=0) 
{
	if(!@copy($source,$target))
	{	add_action('admin_notices','dir_one_click_install');
		$errors= error_get_last();
		echo "<span style='color:red;'>".__('COPY ERROR:',ADMINDOMAIN)."</span> ".$errors['type'];
		echo "<br />\n".$errors['message'];
	} else {
			$file = explode('.',$target);
	
			if(file_exists($target)){ 
				$message ="<span style='color:green;'>".__('File copied from remote!',ADMINDOMAIN)."</span><br/>";
				
				$zip = new ZipArchive();
				$x = $zip->open($target);
				
				if ($x === true && file_exists($target)) { 
					$zip->extractTo( get_tmpl_plugin_directory()); // change this to the correct site path
					$zip->close();
	
					
					unlink($target);
					$message = __("Your .zip file was uploaded and unpacked.",ADMINDOMAIN)."<br/>";
				}else{
					
				}
			}
		if($add_msg == 1 && strstr($_SERVER['REQUEST_URI'],'themes.php')){ 
			update_option('tev_on_go',1);
			
			$plug_path2 = "Tevolution-Directory/directory.php";  // change this to the correct site path
			$plug_path3 = "Tevolution-LocationManager/location-manager.php";  // change this to the correct site path
			$plug_path1 = "Tevolution/templatic.php";  // change this to the correct site path
			
			activate_plugin($plug_path2);
			activate_plugin($plug_path3);
			activate_plugin($plug_path1);
			
			$location_post_type[]='post,category,post_tag';
			$location_post_type[]='listing,listingcategory,listingtags';
			$post_types=update_option('location_post_type',$location_post_type);
		}
	}
}
/*
	To update option , is theme is support woocommerce or not 
*/
function tmpl_set_themesettings(){
    
	/* update woo commerce options */
	$currrent_theme_name = wp_get_theme();
	$templatic_woocommerce_themes = get_option('templatic_woocommerce_themes');
	$templatic_woocommerce_ = str_replace(',','',get_option('templatic_woocommerce_themes'));
	if(!strstr(trim($templatic_woocommerce_) ,trim($currrent_theme_name))):
		update_option('templatic_woocommerce_themes',$templatic_woocommerce_themes.",".$currrent_theme_name);
	endif;	

	/* update full width map settings */
	$templatic_settings = get_option('templatic_settings');
	$tmpdata = get_option('templatic_settings');
	$map_settngs = get_option('map_settngs');
	
	
	if((!$tmpdata['google_map_full_width'] || !$tmpdata['direction_map'] || @$map_settngs['google_map_full_width'] =='yes' || @$map_settngs['direction_map'] =='yes') && !$_POST){
		$tmpdata['google_map_full_width'] = 'yes';
		
		if(@$tmpdata['direction_map'] !='No' || @$tmpdata['direction_map'] =='' || @$map_settngs['direction_map'] =='yes'){ 
			$tmpdata['direction_map'] = 'yes';
			$map_data['direction_map'] ='';
			update_option('map_settngs',array_merge( $map_settngs, $map_data ) );
		}
		
		if(!empty($templatic_settings)):
			update_option('templatic_settings',array_merge( $templatic_settings, $tmpdata ));
		else:
			update_option('templatic_settings',$tmpdata );
		endif;
	}
}
/*
	Load theme's styles and scripts
*/
function tmpl_theme_css_scripts() {
	
	/* Load themes style.css*/
	wp_enqueue_style( 'directory-css', get_stylesheet_uri());
	
	if ( !tmpl_wp_is_mobile()) {
		wp_enqueue_style( 'tmp-responsive',  trailingslashit ( get_template_directory_uri() ) . 'css/responsive.css',array('directory-css'));
	}
	
	/* load light box js on detail page and on pages only */	
	if(is_single() || (is_page() && !is_front_page()))
		wp_enqueue_script( 'templatic_colorbox', trailingslashit ( get_template_directory_uri() ) . 'js/jquery.colorbox-min.js', array( 'jquery' ), '20120606', true );

	/* for WooCommerce */
	if(is_search()){
		wp_enqueue_script( 'tmpl_search_view', trailingslashit ( get_template_directory_uri() ) . 'js/search-view.js', array( 'jquery' ), '20120606', true );
	}
	
	/* add mobile css */
	
	/* include parent css in child theme*/
	if( file_exists(get_stylesheet_directory().'/theme-style.css'))
	{
		wp_enqueue_style( 'tmpl_dir_css', trailingslashit ( get_template_directory_uri() ) . 'style.css');
	}
	if(tmpl_wp_is_mobile()){
		wp_enqueue_style( 'tmpl_mobile_view', trailingslashit ( get_template_directory_uri() ) . 'css/search-view.js',array('directory-css'));
		
		if( file_exists(get_stylesheet_directory().'/theme-mobile-style.css'))
		{
			wp_enqueue_style( 'tmpl_child_mobile_view', trailingslashit ( get_stylesheet_directory_uri() ) . 'theme-mobile-style.css');
		}
	}
	else
	{
		if( file_exists(get_stylesheet_directory().'/theme-style.css'))
		{
			wp_enqueue_style( 'tmpl_childtheme_view', trailingslashit ( get_stylesheet_directory_uri() ) . 'theme-style.css');
		}
	}
	
	/* rtl.css */
	$supreme2_theme_settings = get_option(supreme_prefix().'_theme_settings');
	if ( isset($supreme2_theme_settings['rtlcss']) && $supreme2_theme_settings['rtlcss']==1 && !is_admin() ) { 
		/* include right to left css */
		if(file_exists(get_stylesheet_directory().'/theme-style.css')){
			wp_enqueue_style( 'tmpl_rtl_css', get_template_directory_uri().'/rtl.css',array('tmpl_dir_css') );
			if(file_exists(get_stylesheet_directory().'/rtl.css')){
				wp_enqueue_style( 'tmpl_child_rtl_css', get_stylesheet_directory_uri().'/rtl.css',array('tmpl_rtl_css') );
			}
		}else{
			wp_enqueue_style( 'tmpl_rtl_css', get_template_directory_uri().'/rtl.css',array('tmp-responsive') );
		}
	 
	}
	   
	/* mobile view - gallery work only with un minified js*/
	
	if(tmpl_wp_is_mobile()){
		wp_enqueue_script( 'tmpl-scripts', trailingslashit ( get_template_directory_uri() ) . 'js/_supreme.js', array( 'jquery' ), '20120606', true );
	}else{
		wp_enqueue_script( 'tmpl-scripts', trailingslashit ( get_template_directory_uri() ) . 'js/_supreme.min.js', array( 'jquery' ), '20120606', true );
	}
	wp_enqueue_script( 'templatic-faskclick-js', '//cdnjs.cloudflare.com/ajax/libs/fastclick/1.0.0/fastclick.min.js', array( 'jquery' ), '20120606', true );
	
	if( function_exists( 'is_woocommerce') ) {
		wp_dequeue_style( 'woocommerce_frontend_styles' );
	}
}
/**
 * This is a fix for when a user sets a custom background color with no custom background image.  What 
 * happens is the theme's background image hides the user-selected background color.  If a user selects a 
 * background image, we'll just use the WordPress custom background callback.
 * 
 * Thanks to Justin Tadlock for the code.
 *
 * @since 0.1
 * @link http://core.trac.wordpress.org/ticket/16919
 */
function supreme_custom_background_callback() {
	/* Get the background image. */
	$image = get_background_image();
	/* If there's an image, just call the normal WordPress callback. We won't do anything here. */
	if ( !empty( $image ) ) {
		_custom_background_cb();
		return;
	}
	/* Get the background color. */
	$color = get_background_color();
	/* If no background color, return. */
	if ( empty( $color ) )
		return;
	/* Use 'background' instead of 'background-color'. */
	$style = "background: #{$color};";
?>
<style type="text/css">
body.custom-background {
<?php echo trim( $style );
?>
}
</style>
<?php
}

/*
Name : supreme_register_image_sizes
Description : Registers additional image size 'supreme-thumbnail'.
*/
function supreme_register_image_sizes() {
	add_image_size( 'slider-thumbnail', '350', '350', true );
}
/*
 Name : supreme_embed_defaults
 Description : Overwrites the default widths for embeds.  This is especially useful for making sure videos properly expand the full width on video pages. 
*/
function supreme_embed_defaults( $args ) {
	$args['width'] = 600;
	if ( current_theme_supports( 'theme-layouts' ) ) {
		$layout = theme_layouts_get_layout();
		if ( 'layout-3c-l' == $layout || 'layout-3c-r' == $layout || 'layout-3c-c' == $layout || 'layout-hl-2c-l' == $layout || 'layout-hl-2c-r' == $layout || 'layout-hr-2c-l' == $layout || 'layout-hr-2c-r' == $layout )
		
			$args['width'] = 280;
			
		elseif ( 'layout-1c' == $layout )
		
			$args['width'] = 920;
	}
	return $args;
}
add_action('admin_init','supreme_wpup_changes',20);
function supreme_wpup_changes(){
	 remove_action( 'after_theme_row_supreme', 'wp_theme_update_row' ,10, 2 );
}
if(!function_exists('customAdmin')){
	function customAdmin() {
		
		/* auto install for theme */
		$theme_name = strtolower(wp_get_theme());
		if(strstr($_SERVER['REQUEST_URI'],'themes.php') || (isset($_REQUEST['page']) && $_REQUEST['page']=='templatic_system_menu') || defined( 'DOING_AJAX' )  ){
			/* Check if theme specific auto install is available or not */
			if(file_exists(get_stylesheet_directory().'/functions/'.$theme_name.'_auto_install/auto_install.php')){
				include_once(get_stylesheet_directory().'/functions/'.$theme_name.'_auto_install/auto_install.php');
			}elseif(file_exists(get_template_directory().'/library/functions/'.$theme_name.'_auto_install/auto_install.php')){
				include_once(get_template_directory().'/library/functions/'.$theme_name.'_auto_install/auto_install.php');
			}else{
				/* if theme specific auto install is not exists then take the default auto install */
				if(file_exists(get_stylesheet_directory().'/functions/auto_install/auto_install.php')){
					include_once(get_stylesheet_directory().'/functions/auto_install/auto_install.php');
				}elseif(file_exists(get_template_directory().'/library/functions/auto_install/auto_install.php')){
					include_once(get_template_directory().'/library/functions/auto_install/auto_install.php');
				}
			}
		}
	}
}
add_action('admin_init', 'customAdmin', 11); // add admin-style.css

// Returns the portion of haystack which goes until the last occurrence of needle
if(!function_exists('reverse_strrchr')){
	function reverse_strrchr($haystack, $needle, $trail) {
		return strrpos($haystack, $needle) ? substr($haystack, 0, strrpos($haystack, $needle) + $trail) : false;
	}
}
	
/*
Name : check_if_woocommerce_active
Desc : check if woocommerce is active or not 
*/
if(!function_exists('check_if_woocommerce_active')){
	function check_if_woocommerce_active(){
		$plugins = wp_get_active_and_valid_plugins();
		$flag ='';
		foreach($plugins as $plugins){
			if (strpos($plugins,'woocommerce.php') !== false) {
				$flag = 'true';
				break;
			}else{
				 $flag = 'false';
			}
		}
		return $flag;
	}
}
/* add theme support of woocommerce */
if(function_exists('check_if_woocommerce_active')){
	$is_woo_active = check_if_woocommerce_active();
	if($is_woo_active == 'true'){
		add_theme_support( 'woocommerce' );
	}
}


/* add_action('supreme_aftercontentevent');
add_action('supreme_aftercontentlisting'); */
function remove_home_page_feature_listing_filter()
{
	$show_on_front=get_option('show_on_front');
	if($show_on_front=='page'){
		remove_filter('pre_get_posts', 'home_page_feature_listing');
	}
}
add_filter('slider_image_thumb','slider_thumbnail');
function slider_thumbnail()
{
	return 'slider-thumbnail';
}	
/*
	To change the comment field title.
*/
add_filter( 'comment_form_defaults', 'comment_form_defaults_comment_title',11 );
function comment_form_defaults_comment_title( $arg ) {
	$arg['comment_field'] = '<p class="comment-form-comment"><label for="comment">'.__('Review',THEME_DOMAIN).'</label> <textarea aria-required="true" rows="8" cols="45" name="comment" id="comment"></textarea></p>';
	return $arg;
}
/*
	To fetch fields after comment box.
*/
add_filter( 'comment_form_defaults', 'comment_form_defaults',100 );
function comment_form_defaults( $arg ) {
	global $post,$current_user;
	if(!$current_user->ID)
	{
		$fields = $arg['fields'];
		$arg['fields'] = '';
		$arg['comment_field'] .= '<div class="comment_column2">'.$fields['author'].$fields['email'].$fields['url'].'</div>';
	}
	if($post->post_type != 'post')
		$arg['label_submit'] = __('Post Review',THEME_DOMAIN);
	return $arg;
}
add_action( 'init', 'directory_register_image_sizes' );
function directory_register_image_sizes(){	
	add_image_size( 'thumbnail', 250, 165, true );
	if(get_option('thumbnail_size_w')!=250)
		update_option('thumbnail_size_w',250);
	if(get_option('thumbnail_size_h')!=165)
		update_option('thumbnail_size_h',165);
	
}
/**
 * Disables sidebars based on layout choices.
 *
 * @since 0.1
 */
function directory_disable_sidebars( $sidebars_widgets ) {	
	
	global $wpdb,$wp_query,$post;
	//fetch the current page taxonomy
	if(is_tax() || is_category())
		$current_term = $wp_query->get_queried_object();	
	
	
	if ( current_theme_supports( 'theme-layouts' ) && !is_admin() ) {
	
		if ( 'layout-1c' == theme_layouts_get_layout() ) {
				
				$taxonomy=get_query_var( 'taxonomy' );
				if(is_tax()){
					$sidebars_widgets[$taxonomy.'_listing_sidebar'] = false;
					$sidebars_widgets[$taxonomy.'_tag_listing_sidebar'] = false;
				}
				if(is_single()){
					$sidebars_widgets[get_post_type().'_detail_sidebar'] = false;
				}
				if(is_page())
				{
					$post_type=get_post_meta($post->ID,'submit_post_type',true);
					if($post_type!='')
					{
						$sidebars_widgets['add_'.$post_type.'_submit_sidebar'] = false;
					}
				}
				if(is_home())
				{
					$sidebars_widgets['front_sidebar'] = false;
				}
			
			
		}
	}
	return $sidebars_widgets;
}
add_filter('tev_review_text','review_text_hook',11); // filter to remove space.
/* fun to remove review text */
	function review_text_hook($review){
		$review ="&nbsp;";
		return $review;
	}
	
  /*
	include font awesome css.
    */
   add_action( 'wp_enqueue_scripts', 'theme_css_on_init' ); // include fonts awesome
   
   function theme_css_on_init() {
       /* Register our style sheet. */
	   if(is_ssl()){ $http = "https://"; }else{ $http ="http://"; }
	   
       wp_register_style( 'fontawesomecss', $http.'maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
	   wp_enqueue_style( 'fontawesomecss' );
	   $theme = wp_get_theme();
	  
   }
    /* Remove comment icon from wp-adminbar */
   function remove_comments(){
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
	}

add_action( 'wp_before_admin_bar_render', 'remove_comments' );
//add_action('before_content','directory_theme_breadcrumb',11); // add breadcrumb before content

add_action('close-post-content','post_close_post_content');
function post_close_post_content(){
	global $post;
	if(is_single() && get_post_type()=='post' && function_exists('tevolution_socialmedia_sharelink')){
		tevolution_socialmedia_sharelink($post);	
	}
}

add_filter( 'attachment_link', 'attachment_link', 20, 2 );
add_action( 'wp_footer', 'templatic_wp_head' );

function templatic_wp_head() {
	
	/*include admin_style.css for color customizer for backend.*/
	if(isset($_REQUEST['wp_customize']) && $_REQUEST['wp_customize']=='on')
	{
		wp_enqueue_style( 'admin_style', trailingslashit ( get_template_directory_uri())."css/admin_style.css" );
	}
	if(!is_home() && !is_front_page() && !is_search() && !is_archive() && !is_plugin_active('Templatic-Shortcodes/templatic_shortcodes.php') && !is_404() && !isset($_REQUEST['page']))
	{
	?>
	<script type="text/javascript">
	// <![CDATA[
		var $shorcode_gallery_popup = jQuery.noConflict();
		$shorcode_gallery_popup(document).ready(function($){
			$shorcode_gallery_popup(".gallery").each(function(index, obj){
				var galleryid = Math.floor(Math.random()*10000);
				$shorcode_gallery_popup(obj).find("a").colorbox({rel:galleryid, maxWidth:"95%", maxHeight:"95%"});
			});
			$shorcode_gallery_popup("a.lightbox").colorbox({maxWidth:"95%", maxHeight:"95%"});
		});
	// ]]>
	</script>
	<?php
	}

}


/* Include foundation js start */

/* 	Add foundation basic .js
	Here with different function because we needs to add in footer with no js conflicts,
	there should not same other script load from plug-in */
add_action( 'wp_footer', 'tmpl_foundation_script',99 );

/* add script in footer*/
if(!function_exists('tmpl_foundation_script')){
	function tmpl_foundation_script(){ ?>
	<script id="tmpl-foundation" src="<?php echo get_template_directory_uri(); ?>/js/foundation.min.js"> </script>	
<?php
	}
}

/* Include foundation js end */

function attachment_link( $link, $id ) {
	/* The light box doesn't function inside feeds obviously, so don't modify anything */
	if ( is_feed() || is_admin() )
		return $link;

	$post = get_post( $id );

	if ( 'image/' == substr( $post->post_mime_type, 0, 6 ) )
		return wp_get_attachment_url( $id );
	else
		return $link;
}
/* Show the Renew/Edit button on author page */
add_action('tmpl_before_entry_end','tmpl_author_page_editrenew_link');

function tmpl_author_page_editrenew_link(){
	if(is_author() && (!isset($_REQUEST['sort']))){
		do_action('templ_show_edit_renew_delete_link');	
	}
}
/*filter to remove slider while submit a listing*/
add_filter('intermediate_image_sizes','unset_slider_thumnail_size');
function unset_slider_thumnail_size($image_sizes)
{
	foreach ($image_sizes as $key => $value){
		if ($value == 'slider-thumbnail') {
			unset($image_sizes[$key]);
		}
	}
	return $image_sizes;
}
/*generate thumbnail for slider*/
add_action('generate_slider_thumbnail','generate_slider_thumbnail');
function generate_slider_thumbnail($post)
{
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$post_id = $post->ID;
	$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => 'attachment' , 'post_parent' => $post_id); 
	$attachments = get_posts( $args );
	if ($attachments) {
		foreach ( $attachments as $attach_post ) {
			$file = get_attached_file( $attach_post->ID );
			wp_update_attachment_metadata( $attach_post->ID, wp_generate_attachment_metadata( $attach_post->ID, $file ) );
		}
	}	
}
add_action('tmpl_open_main','tmpl_open_main_area_widget');
/* to show the widget area on front page only */
function tmpl_open_main_area_widget(){ 
	if(is_active_sidebar('home-page-above-content-all-pages')){ ?>
		 <div class="home_page_full_content">
          	<?php dynamic_sidebar('home-page-above-content-all-pages'); ?>
         </div>
	<?php }
}

/* above home page content widget area */
add_action('tmpl_open_wrap','tmpl_open_wrap_widget');

function tmpl_open_wrap_widget(){
	if(is_front_page() && is_active_sidebar('home-page-above-content')){ ?>
		 <div class="home_page_full_content columns">
          	<?php dynamic_sidebar('home-page-above-content'); ?>
         </div>
	<?php }
}
/*set contact us widget in widget area.*/
add_action('init','add_contact_us_widget');
function add_contact_us_widget()
{
	if(get_option('set_contact_widget') != 'save_contact_us_widget')
	{
		$sidebars_widgets = get_option('sidebars_widgets');
		$supreme_contact_widget = array();
		$supreme_contact_widget[1] = array(
							"title"			=>	'Send us a message',
							"address"		=>	'230 Vine Street And locations throughout Old City, Philadelphia, PA 19106',
							"map_height"	=>	400,
							);						
		$supreme_contact_widget['_multiwidget'] = '1';
		update_option('widget_supreme_contact_widget',$supreme_contact_widget);
		$supreme_contact_widget = get_option('widget_supreme_contact_widget');
		krsort($supreme_contact_widget);
		foreach($supreme_contact_widget as $key1=>$val1)
		{
			$supreme_contact_widget_key = $key1;
			if(is_int($supreme_contact_widget_key))
			{
				break;
			}
		}
		$sidebars_widgets["contact_page_widget"] = array_merge(array($sidebars_widgets["contact_page_widget"]),array("supreme_contact_widget-{$supreme_contact_widget_key}"));
		update_option('sidebars_widgets',$sidebars_widgets);  //save widget informations 
		update_option('set_contact_widget','save_contact_us_widget');
	}
}
/**
	Function located in wp-includes/vars.php, but sometimes it shows undefined
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @return bool true|false
 */
 
if(!function_exists('twp_is_mobile')){
	function twp_is_mobile() {
		static $is_mobile;

		if ( isset($is_mobile) )
			return $is_mobile;

		if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
			$is_mobile = false;
		} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
				$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return $is_mobile;
	}
}
if(!function_exists('tmpl_wp_is_mobile')){
	/*
	Check if device is mobile or not. Return true if mobile devie is detected
	*/
	function tmpl_wp_is_mobile(){
		if(function_exists('supreme_prefix')){  
			$pref = supreme_prefix();
		}else{
			$pref = sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
		}
		
		$theme_options = get_option($pref.'_theme_settings');
		
		$is_mobile_enabled= @$theme_options['tmpl_mobile_view'];
		if($is_mobile_enabled !=0 || $is_mobile_enabled ==''){
			$is_mobile_enabled=1;
		}

		
			if($is_mobile_enabled==1){
				if ( (twp_is_mobile() || (isset($_REQUEST['device']) && $_REQUEST['device']=='mobile')) && (!preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT'])) && !strstr('windows phone',$_SERVER['HTTP_USER_AGENT']))){ /* if not desktop then return true */	
					return true;
				}else{
					return false;
				}	
			}else{
				return false;
			}
	}
}

?>