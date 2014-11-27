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
                switch( $_REQUEST['action'] ){
                    case "add_membership":
                        include "add-membership.php";
                        break;
                    case "edit_membership":
                        include "edit-membership.php";
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
}
new HH_Membership_Tab();