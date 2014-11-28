<?php
$msid = $_REQUEST['msid'];
if( is_array( $msid ) ){
    foreach($msid as $post_id){
        wp_delete_post($post_id);
    }
}else{
    wp_delete_post($msid);
}
echo "Delete membership successfully. ";
$link = add_query_arg( array('page'=>'monetization','tab'=>'membership'),  admin_url( "admin.php") );
echo "Click <a href=\"{$link}\">here</a> to back list membership package";