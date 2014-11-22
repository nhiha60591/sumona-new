<?php
/*
 * Add action to display the Tevolution-Events plugin in extend tab on Tevolution dashboard.
 */
add_action('templconnector_bundle_box','add_tevolution_event_manager_bundle_box');
function add_tevolution_event_manager_bundle_box()
{
	$activated = @$_REQUEST['activated'];
	$deactivate = @$_REQUEST['deactivate'];
	if(function_exists('templatic_module_activationmsg'))
	{
		if($activated)		
			templatic_module_activationmsg('tevolution_event_manager','Tevolution Event manager','',$mod_message='',$realted_mod =''); 
		else
			templatic_module_activationmsg('tevolution_event_manager','Tevolution Event manager','',$mod_message='',$realted_mod =''); 	
	}
?>
    <div id="templatic_tevolution_event_manager" class="widget_div">
        <div title="Click to toggle" class="handlediv"></div>
            <h3 class="hndle"><span><?php _e('Tevolution Event Manager',EDOMAIN); ?></span></h3>
        <div class="inside">
        	  <img class="dashboard_img" src="<?php echo TEVOLUTION_EVENT_URL?>images/icon-event-manager.png" />
            <?php
            _e('An event management module that will let you add, sort and manage your events easily and effectively. By management, we mean you can do excerpt settings, decide the publishing status on event addition, fire e-mail notification to user on event expiry, sort the listings as per the locations and their near by locations too. Show and share your listings using your social accounts from your website itself and many more.',EDOMAIN);
            ?>
            <div class="clearfixb"></div>          
            <?php if(!is_active_addons('tevolution_event_manager')) :?>
            <div id="publishing-action">
                <a href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&activated=tevolution_event_manager&true=1";?>" class="templatic-tooltip button-primary"><?php _e('Activate &rarr;',EDOMAIN); ?></a>
            </div>
            <?php  endif;?>
    <?php  if (is_active_addons('tevolution_event_manager')) : ?>
            <div class="settings_style">
            <a href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&deactivate=tevolution_event_manager&true=0";?>" class="deactive_lnk"><?php _e('Deactivate ',EDOMAIN); ?></a>|
            <a class="templatic-tooltip set_lnk" href="<?php echo site_url()."/wp-admin/admin.php?page=directory_settings";?>"><?php _e('Settings',EDOMAIN); ?></a>            
            </div>
    <?php endif; ?>
        </div>
    </div>
<?php
}
?>