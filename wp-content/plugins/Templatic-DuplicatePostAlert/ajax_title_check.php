<?php 
//Include default wordpress file to make ajax call
$file = dirname(__FILE__);
$file = substr($file,0,stripos($file, "wp-content"));
require($file . "/wp-load.php");
//Assigned value to variable
$my_post_type = $_REQUEST['post_type'];
$my_post_title = $_REQUEST['title'];
//Check for entry availability
global $wpdb;
$post_table = $wpdb->prefix.'posts';
$title_query = $wpdb->get_var("select post_title from $post_table where post_type='$my_post_type' and post_title='$my_post_title' and post_status='publish'");
//Retrun true/false
if( $title_query !="" ){
	echo 1;
}else{
	echo 0;
}?>