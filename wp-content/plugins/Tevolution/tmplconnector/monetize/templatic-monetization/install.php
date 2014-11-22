<?php
global $wp_query,$wpdb,$wp_rewrite;
define('TEMPL_MONETIZATION_PATH',TEMPL_MONETIZE_FOLDER_PATH . "templatic-monetization/"); 
/* ACTIVATING PRICE PACKAGES */
if( (isset($_REQUEST['activated']) && $_REQUEST['activated'] == 'monetization') && ($_REQUEST['true'] && $_REQUEST['true'] == 1) || (isset($_REQUEST['activated']) && $_REQUEST['activated']=='true') )
{
	update_option('monetization','Active');
	if(!get_option('currency_symbol'))
		update_option('currency_symbol','$');
	if(!get_option('currency_code'))
		update_option('currency_code','USD');
	if(!get_option('currency_pos'))
		update_option('currency_pos','1');
	if(!get_option('tmpl_price_decimal_sep'))
		update_option('tmpl_price_decimal_sep','.');
	if(!get_option('tmpl_price_num_decimals'))
		update_option('tmpl_price_num_decimals',2);
		
	add_action('admin_init','test_function');
	function test_function(){
		require_once(TEMPL_MONETIZATION_PATH.'add_dummy_packages.php');
	}
}
else if( (isset($_REQUEST['deactivate']) && $_REQUEST['deactivate'] == 'monetization') && (isset($_REQUEST['true']) && $_REQUEST['true'] == 0 ))
{
	delete_option('monetization');
}
/* eof - price packages activation */

/* code to create an admin subpage menu for price packages */

	/* including a language file */
	if(file_exists(TEMPL_MONETIZE_FOLDER_PATH.'templatic-monetization/language.php'))
	{
		include (TEMPL_MONETIZE_FOLDER_PATH . "templatic-monetization/language.php");
	}
	/* INCLUDING A FUNCTIONS FILE */
	if(file_exists(TEMPL_MONETIZE_FOLDER_PATH.'templatic-monetization/price_package_functions.php'))
	{
		include (TEMPL_MONETIZE_FOLDER_PATH . "templatic-monetization/price_package_functions.php");
	}
	
	add_action('templ_add_admin_menu_', 'add_subpage_monetization',13); /* ADD HOOK */
	
	/* script in style for color picker in backend */
	add_action('admin_enqueue_scripts','add_farbtastic_style_script');
	
	if(file_exists(TEMPL_MONETIZATION_PATH."templatic-payment_options/payment_functions.php"))
		include(TEMPL_MONETIZATION_PATH."templatic-payment_options/payment_functions.php");
		
	add_action('admin_head','templ_add_pkg_js');
	add_action('wp_head','templ_add_pkg_js');
	add_filter('set-screen-option', 'package_table_set_option', 10, 3);
	add_action('admin_init','transactions_table_create');
	
	/* EOF - CREATE SUB PAGE MENU */

function add_subpage_monetization()
{
	$page_title = __('Monetization',ADMINDOMAIN); /* DEFINE PAGE TITLE AND MENU TITLE */
	$transcation_title = __('Transactions',ADMINDOMAIN); /* DEFINE PAGE TITLE AND MENU TITLE */
	/* CREATING A SUB PAGE MENU TO TEMPLATIC SYSTEM */

	$hook = add_submenu_page('templatic_system_menu',$page_title,$page_title,'administrator', 'monetization', 'add_monetization');

	add_action( "load-$hook", 'add_screen_options' ); /* CALL A FUNCTION TO ADD SCREEN OPTIONS */
	$hook_transaction = add_submenu_page('templatic_system_menu',$transcation_title,$transcation_title,'administrator', 'transcation', 'add_transcation');
	do_action('templatic_monetizations_menu');// add additional admin menu after  monetization and transcation menu
	add_action( "load-$hook_transaction", 'add_screen_options_transaction' ); /* CALL A FUNCTION TO ADD SCREEN OPTIONS */
	
}

/*
 * Function Name: add_screen_options
 * Return: display the screen option in Monetization menu page 
 */

function add_screen_options()
{
	$option = 'per_page';
	$args = array('label'   => 'Show record per page for monetization',
			    'default' => 10,
			    'option'  => 'package_per_page'
		);
	add_screen_option( $option, $args ); /* ADD SCREEN OPTION */
}

/*
 * Function Name: add_screen_options_transaction
 * return: display the screen option in transaction menu page
 */
function add_screen_options_transaction()
{
	$option = 'per_page';
	$args = array( 'label'   => 'Transaction',
				'default' => 10,
				'option'  => 'transaction_per_page'
		);
	add_screen_option( $option, $args ); /* ADD SCREEN OPTION */
}

/*
	include wordpress farbtastic script and style for choose color picker
 */
function add_farbtastic_style_script()
{
	wp_enqueue_script( 'farbtastic' );
	wp_enqueue_style( 'farbtastic' );
}
/* FUNCTION CALLED ON SUB PAGE MENU HOOK */
function add_monetization()
{
	include(TEMPL_MONETIZATION_PATH."templatic_monetization.php");
}
/* FUNCTION CALLED ON SUB PAGE MENU HOOK */
function add_transcation()
{
	if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'transcation' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
		include(TEMPL_MONETIZATION_PATH."templatic_transaction_detail_report.php");
	elseif(isset($_REQUEST['page']) && $_REQUEST['page'] == 'transcation')
		include(TEMPL_MONETIZATION_PATH."templatic_transaction_report.php");
}
/*
Name :payment_option_plugin_function 
desc : Function to insert file for add/edit/delete options for payment options/gateway settings BOF 
*/
function payment_option_plugin_function(){
	if((isset($_GET['tab']) && $_REQUEST['tab'] == 'payment_options') && (!isset($_GET['payact']) && @$_GET['payact']=='')){
		templ_payment_methods();
	}else if((isset($_GET['tab']) && $_REQUEST['tab'] == 'currency_settings') && (!isset($_GET['payact']) && @$_GET['payact']=='')){
		tmpl_currency_settings();
	}else if((isset($_GET['payact']) && $_GET['payact']=='setting') && (isset($_GET['id']) && $_GET['id'] != '')){
		include (TEMPL_MONETIZATION_PATH."templatic-payment_options/admin_paymethods_add.php");
	}
}
/* Function to insert file for add/edit/delete options for custom fields EOF --**/

/* Function to insert file for add/edit/delete options for custom fields EOF --**/
/*
Name: templ_add_pkg_js
desc : return the script for fetching price packages
*/
function templ_add_pkg_js(){
	global $wp_query,$pagenow,$post;
	// If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object	
	if($post)
		$is_tevolution_submit_form = get_post_meta( @$post->ID, 'is_tevolution_submit_form', true );
		$is_tevolution_upgrade_form = get_post_meta(@$post->ID, 'is_tevolution_upgrade_form', true );
		$is_frontend_submit_form = get_post_meta(@$post->ID, 'is_frontend_submit_form', true );
	if((is_page() &&  ($is_tevolution_upgrade_form==1 || $is_tevolution_submit_form==1 || $is_frontend_submit_form==1)) ||(is_admin() && ($pagenow=='post.php' || $pagenow== 'post-new.php'))){
		include(TEMPL_MONETIZE_FOLDER_PATH.'templatic-monetization/price_package_js.php'); 
	}
	
}

/* this function will filter data according to screen options   */
function package_table_set_option($status, $option, $value)
{
    return $value;
}

/*
 * Function Name: transactions_table_create
 * Create the transactions table
 */
function transactions_table_create(){
	global $wpdb,$pagenow;	
	/*transaction table BOF*/
	if($pagenow=='index.php' || $pagenow=='plugins.php' || (isset($_REQUEST['page']) && ($_REQUEST['page']=='templatic_system_menu' || $_REQUEST['page']=='transcation' || $_REQUEST['page']=='monetization'))){
		
		$transection_db_table_name = $wpdb->prefix . "transactions";
		if($wpdb->get_var("SHOW TABLES LIKE \"$transection_db_table_name\"") != $transection_db_table_name)
		{
			$transaction_table = 'CREATE TABLE IF NOT EXISTS `'.$transection_db_table_name.'` (
			`trans_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`post_id` bigint(20) NOT NULL,
			`post_title` varchar(255) NOT NULL,
			`status` int(2) NOT NULL,
			`payment_method` varchar(255) NOT NULL,
			`payable_amt` float(25,5) NOT NULL,
			`payment_date` datetime NOT NULL,
			`paypal_transection_id` varchar(255) NOT NULL,
			`user_name` varchar(255) NOT NULL,
			`pay_email` varchar(255) NOT NULL,
			`billing_name` varchar(255) NOT NULL,
			`billing_add` text NOT NULL,
			`package_id` int(10) NOT NULL DEFAULT 0,
			`package_type` VARCHAR(255) NULL DEFAULT NULL,
			`payforpackage`  int(2) NOT NULL DEFAULT 0,
			`payforfeatured_h`  int(2) NOT NULL DEFAULT 0,
			`payforfeatured_c`  int(2) NOT NULL DEFAULT 0,
			`payforcategory`  int(2) NOT NULL DEFAULT 0,
			PRIMARY KEY (`trans_id`)
			)DEFAULT CHARSET=utf8';
			$wpdb->query($transaction_table);	
		}
		
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $transection_db_table_name LIKE 'package_id'");		
		if('package_id' != $field_check){
			$wpdb->query("ALTER TABLE $transection_db_table_name ADD package_id int(10) NOT NULL DEFAULT '0'");
		}
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $transection_db_table_name LIKE 'package_type'");		
		if('package_type' != $field_check){
			$wpdb->query("ALTER TABLE $transection_db_table_name ADD package_type VARCHAR(255) NULL DEFAULT NULL");
		}
		
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $transection_db_table_name LIKE 'payforpackage'");		
		if('payforpackage' != $field_check){
			$wpdb->query("ALTER TABLE $transection_db_table_name ADD payforpackage int(2) NOT NULL DEFAULT '0'");
		}
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $transection_db_table_name LIKE 'payforfeatured_h'");		
		if('payforfeatured_h' != $field_check){
			$wpdb->query("ALTER TABLE $transection_db_table_name ADD payforfeatured_h int(2) NOT NULL DEFAULT '0'");
		}
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $transection_db_table_name LIKE 'payforfeatured_c'");		
		if('payforfeatured_c' != $field_check){
			$wpdb->query("ALTER TABLE $transection_db_table_name ADD payforfeatured_c int(2) NOT NULL DEFAULT '0'");
		}
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $transection_db_table_name LIKE 'payforcategory'");		
		if('payforcategory' != $field_check){
			$wpdb->query("ALTER TABLE $transection_db_table_name ADD payforcategory int(2) NOT NULL DEFAULT '0'");
		}
		/*transaction table EOF*/
		
		
		$users_packageperlist_table_name = $wpdb->prefix . "users_packageperlist";
		if($wpdb->get_var("SHOW TABLES LIKE \"$users_packageperlist_table_name\"") != $users_packageperlist_table_name)
		{
			$users_packageperlist_table = 'CREATE TABLE IF NOT EXISTS `'.$users_packageperlist_table_name.'` (
			`ID` int(20) NOT NULL AUTO_INCREMENT,
			`user_id` int(20) NOT NULL,
			`post_id` int(20) NOT NULL,
			`package_id` int(10) NOT NULL DEFAULT 0,
			`trans_id` int(10) NOT NULL DEFAULT 0,
			`subscriber_id` varchar(255) NOT NULL DEFAULT 0,
			`date` date NOT NULL,
			`status` int(2) NOT NULL,
			PRIMARY KEY (`ID`)
			)DEFAULT CHARSET=utf8';
			$wpdb->query($users_packageperlist_table);	
		}
		
	}
}


/* Changed Transaction status */
add_action('wp_ajax_tmpl_ajax_update_status','tmpl_ajax_update_status');
add_action('wp_ajax_nopriv_tmpl_ajax_update_status','tmpl_ajax_update_status');

/* change transaction status from backend - this code was in - Tevolution\tmplconnector\monetize\templatic-monetization\ajax_update_status.php */
function tmpl_ajax_update_status()
{
	require(ABSPATH."wp-load.php");
	/* set transaction order approve from pending. */
	if($_REQUEST['post_id'] !=""){
		$my_post['ID'] = $_REQUEST['post_id'];
		$my_post['post_status'] = 'publish';
		wp_update_post( $my_post );
	}
	if($_REQUEST['post_id'] !=""){
		global $wpdb,$transection_db_table_name;
		$transection_db_table_name = $wpdb->prefix . "transactions";
		$pid = $_REQUEST['post_id'];
		$trans_status = $wpdb->query("update $transection_db_table_name SET status = 1 where post_id = '".$pid."'");
	}
	$result = '';
	if(isset($_REQUEST['trans_id']) && $_REQUEST['trans_id']!='')
		$result = "<span style='color:green; font-weight:normal;'>".APPROVED_TEXT."</span>";
	else
		$result = "<span style='color:green;'>".APPROVED_TEXT."</span>";
	echo $result;exit;
}


?>
