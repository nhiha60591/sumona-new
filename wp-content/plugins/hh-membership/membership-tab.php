<?php
/*
Plugin Name: Tevolution Membership
Plugin URI: https://github.com/nhiha60591/sumona-new
Description: Membership Package
Version: 1.0.1
Author: Huu Hien
Author URI: https://github.com/nhiha60591/sumona-new
*/
class HH_Membership_Tab{
    function __construct(){
        add_action( 'templatic_monetizations_tabs', array( $this, 'add_tab_link' ), 10, 2 );
        add_action( 'monetization_tabs_content', array( $this, 'membership_panel' ), 10, 2 );
        add_action( 'init', array( $this, 'update_membership_package' ), 10, 2 );
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
    }
    public function current_action() {
        if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
            return $_REQUEST['action'];

        if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
            return $_REQUEST['action2'];

        return false;
    }
    function add_tab_link( $tab, $class ){
        ?>
        <a id="membership_settings" class='nav-tab<?php if($tab == 'membership') echo $class;  ?>' href='?page=monetization&tab=membership'><?php echo __('Membership',ADMINDOMAIN); ?> </a>
        <?php
    }
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
    function delete_membership( $msid = 0 ){

    }
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
    function template_include( $template ){
        if( is_singular( 'membership') ){
            $new_template = locate_template( array( 'archive-program.php' ) );
            if ( '' != $new_template ) {
                return $new_template;
            }else{
                return plugin_dir_path(__FILE__)."templates/register-membership.php";
            }
        }
        return $template;
    }
    function front_script(){
        wp_enqueue_script( 'hh-membership-ui', plugin_dir_url( __FILE__ )."assets/js/jquery.validate.js", array( 'jquery' ) );
    }
    function admin_script(){
        wp_register_script( 'hh-admin-membership', plugins_url( '/assets/js/hh-admin-membership.js', __FILE__ ) );
        wp_enqueue_script( 'hh-admin-membership' );
        wp_enqueue_script( 'hh-membership-ui', plugin_dir_url( __FILE__ )."assets/js/jquery.validate.js", array( 'jquery' ) );
    }
    function change_link_membership( $package, $post_id ){
        $post = get_post( $post_id );
        if( $post->post_type == 'membership' ){
            $package = ( @$post->post_title)?'<a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=monetization&action=edit_membership&msid='.$post->ID.'&tab=membership">'.$post->post_title.'</a>' :'-';
        }
        return $package;
    }
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
}
new HH_Membership_Tab();