<?php
require( "../../../../wp-load.php");
global $wpdb,$country_table,$zones_table,$multicity_table;
$my_post_type = explode(",",$_REQUEST['post_type']);
$catid = $_REQUEST['mcatid'];
$term_icon = $_REQUEST['term_icon'];
$cprice = $_REQUEST['cprice'];
//$my_post_type = explode(",",$_REQUEST['post_type']);
$categories='';
if(isset($_REQUEST['city_id']) && $_REQUEST['city_id']!=''){
	$cityinfo = $wpdb->get_results($wpdb->prepare("select categories from $multicity_table where city_id =%d",$_REQUEST['city_id'] ));
	$categories=$cityinfo[0]->categories;
}
for($c=0 ; $c < count($my_post_type) ; $c ++){
	if($my_post_type[$c] !=''){
		if($c ==0){
		get_location_category_checklist($my_post_type[$c],$categories,$_REQUEST['mod'],'select_all');
		}else{
		get_location_category_checklist($my_post_type[$c],$categories,$_REQUEST['mod'],'');
		}
	}
}
?>
