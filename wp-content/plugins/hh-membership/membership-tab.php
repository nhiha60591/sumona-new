<?php
/*
Plugin Name: Tevolution Membership
Plugin URI: https://github.com/nhiha60591/sumona-new
Description: Membership Package
Version: 1.0.1
Author: Huu Hien
Author URI: https://github.com/nhiha60591/sumona-new
*/
define( '__HHTEXTDOMAIN__', 'hh_membership');

/**
 * Class HH_Membership_Tab
 */
include ( "includes/class-hh-membership-mail.php");
class HH_Membership_Tab{
    function __construct(){
        add_action( 'templatic_monetizations_tabs', array( $this, 'add_tab_link' ), 10, 2 );
        add_action( 'monetization_tabs_content', array( $this, 'membership_panel' ), 10, 2 );
        add_action( 'init', array( $this, 'update_membership_package' ), 10, 2 );
        add_action( 'init', array( $this, 'cancel_membership_package' ), 10, 2 );
        add_action( 'init', array( $this, 'register_post_type' ),1 );
        add_filter( 'template_include', array( $this, 'template_include') );
        add_action( 'wp_enqueue_scripts', array( $this, 'front_script' ) );
        add_action( 'tovolution_confirm_transaction', array( $this, 'confirm_membership_transaction' ), 10, 1 );
        add_action( 'admin_print_scripts', array( $this, 'admin_script' ) );
        add_filter( 'tevolution_package_link', array( $this, 'change_link_membership'), 10, 2 );
        register_activation_hook( __FILE__, array( $this, 'hh_membership_activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'hh_membership_deactivation' ) );
        add_action( 'hh_membership_expired',  array( $this, 'remove_role_membership_expired' ) );
        add_action( 'init',  array( $this, 'hh_add_cap_to_post_type' ), 999 );
        add_shortcode( 'membership', array( $this, 'membership_shortcode') );
        //add_action( 'admin_menu', array( $this, 'hh_admin_menu') );
        add_action( 'author_box_content', array( $this, 'upgrade_page' ) );
        add_action( 'templatic_general_data_email', array( $this, 'hh_templatic_general_membership'), 20 );
        //add_action( 'init', array( $this, 'hh_setting_options') );

        add_action( 'tevolution_submition_success_msg', array( $this, 'hh_send_mail_success_payment') );
    }

    /**
     * Get current action
     *
     * @return bool
     */
    public function current_action() {
        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];

        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return $_REQUEST['action2'];

        return false;
    }

    /**
     * Add Tab Link
     *
     * @param $tab
     * @param $class
     */
    function add_tab_link( $tab, $class ){
        ?>
        <a id="membership_settings" class='nav-tab<?php if($tab == 'membership') echo $class;  ?>' href='?page=monetization&tab=membership'><?php echo __('Membership',ADMINDOMAIN); ?> </a>
        <?php
    }

    /**
     * Add Membership Panel
     */
    function membership_panel(){
        global $membership_element;
        $data = array(
            'package_type',
            'package_amount',
            'validity',
            'validity_per',
            'package_status',
            'recurring',
            'billing_num',
            'billing_per',
            'billing_cycle',
            'first_free_trail_period',
            'post_type_access',
        );
        if( isset( $_REQUEST['msid'] ) ){
            foreach( $data as $key ){
                $membership_element[$key] = get_post_meta( $_REQUEST['msid'], $key, true );
            }
            $postData = get_post( $_REQUEST['msid'] );
            $membership_element['package_name'] = $postData->post_title;
            $membership_element['package_desc'] = $postData->post_content;
        }
        if( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] =='membership' ):
            if( isset( $_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' ){
                do_action( 'hh_update_membership_package' );
            }
            if( isset( $_REQUEST['action'] ) && !empty( $_REQUEST['action'] )){
                switch( $this->current_action() ){
                    case "add_membership":
                        include "add-membership.php";
                        break;
                    case "edit_membership":
                        include "edit-membership.php";
                        break;
                    case "delete_membership":
                    case "delete":
                        include "delete-membership.php";
                        break;
                    default:
                        do_action( 'monetization_membership_action' );
                }
            }else{
                include "list-membership-package.php";
            }
        ?>
        <?php
        endif;
    }

    /**
     * Update Membership Package
     */
    function update_membership_package(){
        if( isset( $_REQUEST['action'] ) && !empty( $_REQUEST['action']) ){
            $msid = !empty( $_REQUEST['msid'] ) ? $_REQUEST['msid'] : 0;
            switch( $_REQUEST['action'] ){
                case 'delete_membership':
                    $this->delete_membership( $msid );
                    break;
                case 'edit_membership':
                case 'add_membership':
                    $this->update_membership( $msid );
                    break;
            }
        }
    }

    /**
     * Delete Membership by ID
     *
     * @param int $msid
     */
    function delete_membership( $msid = 0 ){

    }

    /**
     * Update Membership by ID
     *
     * @param int $msid
     */
    function update_membership( $msid = 0 ){
        if( isset( $_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' ){
            $current_user = wp_get_current_user();
            $data = array(
                'package_type' => $_POST['package_type'],
                'package_amount' => $_POST['package_amount'],
                'validity' => $_POST['validity'],
                'validity_per' => $_POST['validity_per'],
                'package_status' => !empty( $_POST['package_status'] ) ? $_POST['package_status'] : 0,
                'recurring' => !empty( $_POST['recurring'] ) ? $_POST['recurring'] : 0,
                'billing_num' => !empty( $_POST['billing_num'] ) ? $_POST['billing_num'] : 0,
                'billing_per' => !empty( $_POST['billing_per'] ) ? $_POST['billing_per'] : 'D',
                'billing_cycle' => !empty( $_POST['billing_cycle'] ) ? $_POST['billing_cycle'] : 0,
                'first_free_trail_period' => !empty( $_POST['first_free_trail_period'] ) ? $_POST['first_free_trail_period'] : 0,
                'post_type_access' => !empty( $_POST['post_type_access'] ) ? $_POST['post_type_access'] : array(),
            );
            // Create post object
            $my_post = array(
                'post_title'    => $_POST['package_name'],
                'post_content'  => $_POST['package_desc'],
                'post_status'   => 'publish',
                'post_author'   => $current_user->ID,
                'post_type'     => 'membership'
            );
            if( $msid ){
                $my_post['ID'] = $msid;
            }

            // Insert the post into the database
            $post_id = wp_insert_post( $my_post );
            if( absint( $post_id ) && $post_id > 0 ){
                foreach( $data as $key=>$val){
                    if( !empty( $val ) ){
                        update_post_meta( $post_id, $key, $val );
                    }else{
                        delete_post_meta( $post_id, $key );
                    }
                }
            }
            /**
             * Set Package Default
             */
            update_post_meta( $post_id, 'package_select', $post_id );
            $roleslug = "package_".$post_id;
            remove_role($roleslug);
            $caps = array();
            $role = add_role( $roleslug,$_POST['package_name'].' Package Role',$caps);
            foreach($_POST['post_type_access'] as $rows){
                $role->add_cap( 'edit_'.$rows );
                $role->add_cap( 'read_'.$rows );
                $role->add_cap( 'delete_'.$rows );
                $role->add_cap( 'edit_post'.$rows.'s' );
                $role->add_cap( 'edit_others_'.$rows.'s' );
                $role->add_cap( 'publish_'.$rows.'s' );
                $role->add_cap( 'read_private_'.$rows.'s' );
                $role->add_cap( 'delete_'.$rows.'s' );
                $role->add_cap( 'delete_private_'.$rows.'s' );
                $role->add_cap( 'delete_published_'.$rows.'s' );
                $role->add_cap( 'delete_others_'.$rows.'s' );
                $role->add_cap( 'edit_private_'.$rows.'s' );
                $role->add_cap( 'edit_published_'.$rows.'s' );
                $role->add_cap( 'create_'.$rows.'s' );
            }
            $role->add_cap('read');
            $role->add_cap('upload_files');
            wp_redirect( add_query_arg( array('page'=>'monetization', 'tab'=> 'membership' ), admin_url("admin.php") ) );
        }
    }

    /**
     * Register Custom Post Type
     */
    function register_post_type(){
        $labels = array(
            'name'               => _x( 'Memberships', 'post type general name', 'your-plugin-textdomain' ),
            'singular_name'      => _x( 'Membership', 'post type singular name', 'your-plugin-textdomain' ),
            'menu_name'          => _x( 'Memberships', 'admin menu', 'your-plugin-textdomain' ),
            'name_admin_bar'     => _x( 'Membership', 'add new on admin bar', 'your-plugin-textdomain' ),
            'add_new'            => _x( 'Add New', 'Membership', 'your-plugin-textdomain' ),
            'add_new_item'       => __( 'Add New Membership', 'your-plugin-textdomain' ),
            'new_item'           => __( 'New Membership', 'your-plugin-textdomain' ),
            'edit_item'          => __( 'Edit Membership', 'your-plugin-textdomain' ),
            'view_item'          => __( 'View Membership', 'your-plugin-textdomain' ),
            'all_items'          => __( 'All Memberships', 'your-plugin-textdomain' ),
            'search_items'       => __( 'Search Memberships', 'your-plugin-textdomain' ),
            'parent_item_colon'  => __( 'Parent Memberships:', 'your-plugin-textdomain' ),
            'not_found'          => __( 'No Memberships found.', 'your-plugin-textdomain' ),
            'not_found_in_trash' => __( 'No Memberships found in Trash.', 'your-plugin-textdomain' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'membership-package' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
        );

        register_post_type( 'membership', $args );
    }

    /**
     * Template Include
     *
     * @param $template
     * @return string
     */
    function template_include( $template ){
        if( is_singular( 'membership') ){
            $new_template = locate_template( array( 'register-membership.php' ) );
            if ( '' != $new_template ) {
                return $new_template;
            }else{
                return plugin_dir_path(__FILE__)."templates/register-membership.php";
            }
        }
        return $template;
    }

    /**
     * Add Front End Script
     */
    function front_script(){
        wp_enqueue_script( 'hh-membership-ui', plugin_dir_url( __FILE__ )."assets/js/jquery.validate.js", array( 'jquery' ) );
        wp_enqueue_style( 'hh-membership-package', plugin_dir_url( __FILE__ )."assets/css/style.css" );
        wp_enqueue_style('tevolution_tootip',TEMPL_PLUGIN_URL.'stylesheet.css','',false);
        wp_enqueue_style('tevolution_style',TEMPL_PLUGIN_URL.'style.css','',false);
        wp_enqueue_script('tevolution_tootip_core1',TEMPL_PLUGIN_URL.'js/mootools-1.2.1-core.js');
        wp_enqueue_script('tevolution_tootip_core2',TEMPL_PLUGIN_URL.'js/mootools-1.2-more.js');
        wp_enqueue_script('tevolution_tootip',TEMPL_PLUGIN_URL.'js/MooTooltips.js');
        wp_enqueue_script('tevolution_js_hh',TEMPL_PLUGIN_URL.'js/js_hh.js');
    }

    /**
     * Add Back End Script
     */
    function admin_script(){
        wp_register_script( 'hh-admin-membership', plugins_url( '/assets/js/hh-admin-membership.js', __FILE__ ) );
        wp_enqueue_script( 'hh-admin-membership' );
        wp_enqueue_script( 'hh-membership-ui', plugin_dir_url( __FILE__ )."assets/js/jquery.validate.js", array( 'jquery' ) );
        wp_enqueue_script('post');
    }

    /**
     * Get Link Membership
     *
     * @param $package
     * @param $post_id
     * @return string
     */
    function change_link_membership( $package, $post_id ){
        $post = get_post( $post_id );
        if( $post->post_type == 'membership' ){
            $package = ( @$post->post_title)?'<a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=monetization&action=edit_membership&msid='.$post->ID.'&tab=membership">'.$post->post_title.'</a>' :'-';
        }
        return $package;
    }

    /**
     * Confirm Membership Transaction By ID
     *
     * @param $cid
     */
    function confirm_membership_transaction( $cid ){
        global $wpdb;
        $transection_db_table_name=$wpdb->prefix.'transactions';
        $cid = explode(",",$cid);
        $post = get_post( $cid[1] );
        if( $post->post_type == 'membership'){
            $user_id = $wpdb->get_var( "SELECT `user_id` FROM {$transection_db_table_name} WHERE `trans_id` = '{$cid[0]}'");
            $users = new WP_User( $user_id );
            if( !is_super_admin( $user_id ) ){
                $users->set_role( "package_".$cid[1] );
            }
        }
    }
    /**
     * On activation, set a time, frequency and name of an action hook to be scheduled.
     */
    function hh_membership_activation() {
        wp_schedule_event( time(), 'daily', 'hh_membership_expired' );
    }

    /**
     * Remove Membership User When Expired
     */
    function remove_role_membership_expired(){
        $agrs = array(
            'post_type' => 'membership',
            'post_status'   => 'publish',
            'posts_per_page' => -1
        );
        $MembershipPackage = new WP_Query( $agrs );
        if( $MembershipPackage->have_posts() ){
            while( $MembershipPackage->have_posts() ){
                $MembershipPackage->have_posts();
                $userargs = array(
                    'role'         => 'package_'.get_the_ID(),
                );

                $validity = get_post_meta( get_the_ID(), 'validity', true );
                $validity_per = get_post_meta( get_the_ID(), 'validity_per', true );
                $validity_per_text = '';
                switch( $validity_per ){
                    case "D":
                        $validity_per_text = 'day';
                        break;
                    case "M":
                        $validity_per_text = 'month';
                        break;
                    case "Y":
                        $validity_per_text = 'year';
                        break;
                    default :
                        $validity_per_text = 'day';
                        break;
                }
                $users = get_users( $userargs );
                if( sizeof( $users ) > 0 ){
                    foreach( $users as $user ){
                        $user_register = get_user_meta( $user->ID, 'membership_package_register', true );
                        if( strtotime( "+ {$validity} $validity_per_text",  strtotime( $user_register ) ) <= strtotime( date( "Y-m-d" ) ) ){
                            $userData = new WP_User( $user->ID );
                            $userData->set_role( 'subscriber' );
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
    }
    /**
     * On deactivation, remove all functions from the scheduled action hook.
     */
    function hh_membership_deactivation() {
        wp_clear_scheduled_hook( 'hh_membership_expired' );
    }

    /**
     * Add Capability To Tevolution Post Type
     */
    function hh_add_cap_to_post_type() {
        global $wp_post_types;
        $args = get_option('templatic_custom_post');
        if($args):
            $role = get_role('administrator');
            foreach($args as $key=> $_args)
            {
                $capObject = new stdClass();
                $capObject->edit_post = 'edit_'.$key;
                $capObject->read_post = 'read_'.$key;
                $capObject->delete_post = 'delete_'.$key;
                $capObject->edit_posts = 'edit_post'.$key.'s';
                $capObject->edit_others_posts = 'edit_others_'.$key.'s';
                $capObject->publish_posts = 'publish_'.$key.'s';
                $capObject->read_private_posts = 'read_private_'.$key.'s';
                $capObject->read = 'read';
                $capObject->delete_posts = 'delete_'.$key.'s';
                $capObject->delete_private_posts = 'delete_private_'.$key.'s';
                $capObject->delete_published_posts = 'delete_published_'.$key.'s';
                $capObject->delete_others_posts = 'delete_others_'.$key.'s';
                $capObject->edit_private_posts = 'edit_private_'.$key.'s';
                $capObject->edit_published_posts = 'edit_published_'.$key.'s';
                $capObject->create_posts = 'create_'.$key.'s';
                $wp_post_types[$key]->cap = $capObject;

                $role->add_cap( 'edit_'.$key );
                $role->add_cap( 'read_'.$key );
                $role->add_cap( 'delete_'.$key );
                $role->add_cap( 'edit_post'.$key.'s' );
                $role->add_cap( 'edit_others_'.$key.'s' );
                $role->add_cap( 'publish_'.$key.'s' );
                $role->add_cap( 'read_private_'.$key.'s' );
                $role->add_cap( 'delete_'.$key.'s' );
                $role->add_cap( 'delete_private_'.$key.'s' );
                $role->add_cap( 'delete_published_'.$key.'s' );
                $role->add_cap( 'delete_others_'.$key.'s' );
                $role->add_cap( 'edit_private_'.$key.'s' );
                $role->add_cap( 'edit_published_'.$key.'s' );
                $role->add_cap( 'create_'.$key.'s' );
            }
        endif;
    }

    /**
     * Add Membership Short Code
     *
     * @param $atts
     * @return string
     */
    function membership_shortcode( $atts){
        $current_user = wp_get_current_user();
        if( $current_user->ID ) return;
        $atts = shortcode_atts( array(
            'title' => 'Membership Registration',
            'login_caption' => 'If you have already registered online, then please login',
        ), $atts );
        ob_start();
        ?>
        <div class="hh-login-form">
            <h3><?php echo $atts['login_caption']; ?></h3>
            <?php
            $args = array(
                'echo'           => true,
                'redirect'       => site_url( $_SERVER['REQUEST_URI'] ),
                'form_id'        => 'loginform',
                'label_username' => __( 'Username' ),
                'label_password' => __( 'Password' ),
                'label_remember' => __( 'Remember Me' ),
                'label_log_in'   => __( 'Log In' ),
                'id_username'    => 'user_login',
                'id_password'    => 'user_pass',
                'id_remember'    => 'rememberme',
                'id_submit'      => 'wp-submit',
                'remember'       => true,
                'value_username' => NULL,
                'value_remember' => false
            );
            wp_login_form( $args );
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add Admin Menu
     */
    function hh_admin_menu(){
        add_menu_page('Email Options', 'Email Options', 'administrator', 'hh_email_option', array( $this, 'hh_email_option' ) );
        add_submenu_page( 'hh_email_option', 'Email Template', 'Email Template', 'administrator', 'hh_mail_template', array( $this, 'hh_email_template') );
    }

    /**
     * Email Option Page
     */
    function hh_email_option(){
        include "templates/email-options.php";
    }

    /**
     * Email Template Page
     */
    function hh_email_template(){
        include "templates/email-template.php";
    }

    /**
     * Membership Author Page
     */
    function upgrade_page(){
        $current_user = get_current_user_id();
        $var = get_query_var( 'author' );
        if( $var != $current_user ) return;
        if( current_user_can( 'manage_options') ) return;
        $pages = get_pages();
        $permalink = '';
        foreach($pages as $page){
            if(has_shortcode( $page->post_content, 'membership' )){
                $permalink = get_the_permalink($page->ID);
                break;
            }
        }
        if( isset( $_POST['yes']) ){
            self::cancel_membership_package();
            ?>
            <h2>Your membership cancellation request successful.</h2>
            <h2>You are now free user</h2>
            <p>
                We're sorry for going to have let you go. We hope you will come back again for your membership in near future
            </p>
            <p>
                Thanks!
            </p>
            <?php
            return;
        }
        if( isset( $_POST['cancel-membership'] ) ){
            ?>
            <div class="cancel-membership">
                <h1>Are you want to Cancel your current Membership?</h1>
                <span class="">Note:</span>
                <ol>
                    <li>Please not that, if you cancel your membership you will lose view access of premium contents and you will be changed to Free user</li>
                </ol>
                <form name="" action="" method="post">
                    <input type="submit" name="back" value="Back" class="button">
                    <input type="submit" name="yes" value="Yes" class="button">
                </form>
            </div>
            <?php
            return;
        }
        ?>
        <div class="upgrade-box">
            <form name="" action="" method="post">
            <a href="<?php echo add_query_arg( array( 'action' => 'upgrade', 'user_id' => get_current_user_id()), $permalink ); ?>" class="button upgrade">Upgrade/Downgrade Membership</a>
            <?php $memebership = get_user_meta( $current_user, 'membership_package_id', true ); if( !empty( $memebership ) ): ?>
                <input name="cancel-membership" class="button" type="submit" value="Cancel Membership"/>
            <?php endif; ?>
            </form>
        </div>
        <?php
    }

    /**
     * Cancel Membership Package
     */
    function cancel_membership_package(){
        $current_user = get_current_user_id();
        $user = new WP_User( $current_user );
        if ( !current_user_can( 'manage_options' ) ) {
            $user->set_role( 'subscriber' );
        }
        $data = get_option('templatic_settings');
        $HH_Mail = new HH_Membership_Mail();
        $membership_package = get_user_meta( $current_user, 'membership_package_id', true );
        $membership_package = get_post( $membership_package );
        $replace_array = array(
            '[#site_name#]' => home_url(),
            '[#to_name#]' => $user->dislay_name,
            '[#user_login#]' => $user->user_login,
            '[#user_email#]' => $user->user_email,
            '[#old_membership_level#]'=> $membership_package->post_title,
            '[#new_membership_level#]'=> 'Subscriber',
        );
        delete_user_meta( $current_user, 'membership_package_id' );
        $admin_msg = $HH_Mail->replace_message($replace_array, $data['hh_upgrade_user']);
        $user_msg = $HH_Mail->replace_message($replace_array, $data['hh_cancel_to_admin']);
        $HH_Mail->send_mail($user->user_email, $data['hh_upgrade_user_subject'], $user_msg);
        $HH_Mail->send_mail(get_option("admin_email"), $data['hh_cancel_to_admin_subject'], $admin_msg);
    }
    function hh_setting_options(){
        if( isset( $_POST['hh_save_email_template'] ) ){
            do_action( 'before_hh_email_template_save_option' );
            $options = apply_filters( 'hh_email_membership_option', $_POST['hh_email_membership'] );
            update_option( 'hh_email_membership', $options );
            do_action( 'after_hh_email_template_save_option', $options );
        }
    }
    function hh_templatic_general_membership( $column ){
        switch($column)
        {
            case 'email':
                ?>
                <div id="legend_membership_notifications">
                    <?php include( "templates/email-template.php"); ?>
                </div>
                <?php
                break;
        }
    }
    function hh_send_mail_success_payment(){
        global $payable_amount,$wpdb;
        $transaction_tabel = $wpdb->prefix."transactions";
        $user_id = $wpdb->get_var("select user_id from $transaction_tabel order by trans_id DESC limit 1");
        $user_details = get_userdata( $user_id );
        $data = get_option('templatic_settings');
        $old = get_user_meta( $user_id, 'membership_package_id', true );
        $HH_Mail = new HH_Membership_Mail();
        if( !empty( $old ) ){
            $transaction_detail = '';
            $replace_array = array(
                '[#site_name#]' => home_url(),
                '[#to_name#]' => $user_details->display_name,
                '[#payable_amt#]' => $payable_amount,
                '[#transaction_details#]' => $transaction_detail,
            );
            $admin_msg = $HH_Mail->replace_message( $replace_array, $data['hh_upgrade_user']);
            $user_msg = $HH_Mail->replace_message( $replace_array, $data['hh_upgrade_admin']);
            $HH_Mail->send_mail( $user_details->user_email, $data['hh_upgrade_user_subject'], $user_msg );
            $HH_Mail->send_mail( get_option( "admin_email" ), $data['hh_upgrade_admin_subject'], $admin_msg );
            return;
        }
        $transaction_detail = '';
        $replace_array = array(
            '[#site_name#]' => home_url(),
            '[#to_name#]' => $user_details->display_name,
            '[#payable_amt#]' => $payable_amount,
            '[#transaction_details#]' => $transaction_detail,
        );
        $admin_msg = $HH_Mail->replace_message( $replace_array, $data['hh_new_user']);
        $user_msg = $HH_Mail->replace_message( $replace_array, $data['hh_new_admin']);
        $HH_Mail->send_mail( $user_details->user_email, $data['hh_success_payment_user_subject'], $user_msg );
        $HH_Mail->send_mail( get_option( "admin_email" ), $data['hh_success_payment_admin_subject'], $admin_msg );
    }
}
new HH_Membership_Tab();