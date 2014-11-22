<?php
//mimic the actuall admin-ajax
if (!isset( $_POST['action']))
    die('-1');

define( 'DOING_AJAX', true );
if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', true );
}

//make sure you update this line 
//to the relative location of the wp-load.php
require_once('../../../wp-load.php'); 

//Typical headers
header('Content-Type: text/html');
send_nosniff_header();

//Disable caching
//header('Cache-Control: no-cache');
//header('Pragma: no-cache');
$expires = 60 * 15;        // 15 minutes
header('Pragma: public');
header('Cache-Control: maxage=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

$action = esc_attr(trim($_POST['action']));

//A bit of security
$allowed_actions = array(
    'filter_searchable_fields_front_end',
    'search_filter',
    'search_filter_map',
    'filter_searchable_fields'
);

if(in_array($action, $allowed_actions)){
    if(is_user_logged_in())
        do_action('wp_ajax_'.$action);
    else
        do_action('wp_ajax_nopriv_'.$action);
}else{
    die('-1');
}
?>
