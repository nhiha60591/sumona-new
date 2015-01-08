<?php
/**
 * Created by PhpStorm.
 * User: HuuHien
 * Date: 12/21/2014
 * Time: 11:02 PM
 */
    class HH_Membership_Mail{
        function __construct(){

        }
        public function send_mail( $mail_to, $subject, $message, $header = '' ){
            add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
            $message = apply_filters( 'hh_replace_message_mail', $message );
            wp_mail( $mail_to, $subject, $message, $header );
            remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
        }
        public function set_html_content_type() {
            return 'text/html';
        }
        public function replace_message( $array = array(), $message = ''){
            foreach( $array as $k=>$v){
                $message = str_replace( $k, $v, $message );
            }
            return $message;
        }
    }