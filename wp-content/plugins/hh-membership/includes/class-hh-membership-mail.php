<?php
/**
 * Created by PhpStorm.
 * User: HuuHien
 * Date: 12/21/2014
 * Time: 11:02 PM
 */
if( !class_exists( 'HH_Membership_Mail' ) ):
    class HH_Membership_Mail{
        function __construct(){

        }
        function send_mail( $mail_to, $subject, $message, $header = '' ){
            add_filter( 'wp_mail_content_type', array( __CLASS__, 'set_html_content_type' ) );
            wp_mail( $mail_to, $subject, $message, $header );
            remove_filter( 'wp_mail_content_type', array( __CLASS__, 'set_html_content_type' ) );
        }
        function set_html_content_type() {
            return 'text/html';
        }
        function replace_message( $array = array(), $message = ''){
            foreach( $array as $k=>$v){
                $message = str_replace( $k, $v, $message );
            }
            return $message;
        }
    }
endif;