<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/22/14
 * Time: 10:48 AM
 */
class HH_Membership_Tab{
    function __construct(){
        add_action( 'templatic_monetizations_tabs', array( $this, 'add_tab_link' ), 10, 2 );
        add_action( 'monetization_tabs_content', array( $this, 'membership_panel' ), 10, 2 );
    }
    function add_tab_link( $tab, $class ){
        ?>
        <a id="membership_settings" class='nav-tab<?php if($tab == 'membership') echo $class;  ?>' href='?page=monetization&tab=membership'><?php echo __('Membership',ADMINDOMAIN); ?> </a>
        <?php
    }
    function membership_panel(){
        global $membership_element;
        if( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] =='membership' ):
            if( isset( $_REQUEST['METHOD']) && $_REQUEST['POST']){
                global $monetization;

                $monetization->insert_package_data($_POST);
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
}
new HH_Membership_Tab();