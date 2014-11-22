<?php
/*
name : register_event_post_type
description : Register event taxonomy.
*/

define('CUSTOM_POST_TYPE_EVENT','event');
define('CUSTOM_CATEGORY_TYPE_EVENT','ecategory');
define('CUSTOM_TAG_TYPE_EVENT','etags');
define('CUSTOM_MENU_TITLE',__('Events',EDOMAIN));
define('CUSTOM_MENU_NAME',__('Events',EDOMAIN));
define('CUSTOM_MENU_SIGULAR_NAME',__('Event',EDOMAIN));
define('CUSTOM_MENU_ADD_NEW',__('Add an Event',EDOMAIN));
define('CUSTOM_MENU_ADD_NEW_ITEM',__('Add new Event',EDOMAIN));
define('CUSTOM_MENU_EDIT',__('Edit',EDOMAIN));
define('CUSTOM_MENU_EDIT_ITEM',__('Edit Event',EDOMAIN));
define('CUSTOM_MENU_NEW',__('New Event',EDOMAIN));
define('CUSTOM_MENU_VIEW',__('View Event',EDOMAIN));
define('CUSTOM_MENU_SEARCH',__('Search Event',EDOMAIN));
define('CUSTOM_MENU_NOT_FOUND',__('No Event found',EDOMAIN));
define('CUSTOM_MENU_NOT_FOUND_TRASH',__('No Event found in trash',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_LABEL',__('Event Categories',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_TITLE',__('Event Categories',EDOMAIN));
define('CUSTOM_MENU_EVENT_SIGULAR_CAT',__('Event Category',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_SEARCH',__('Search category',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_POPULAR',__('Popular categories',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_ALL',__('All categories',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_PARENT',__('Parent category',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_PARENT_COL',__('Parent category:',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_EDIT',__('Edit category',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_UPDATE',__('Update category',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_ADDNEW',__('Add new category',EDOMAIN));
define('CUSTOM_MENU_EVENT_CAT_NEW_NAME',__('New category name',EDOMAIN));
define('CUSTOM_MENU_TAG_LABEL_EVENT',__('Event Tags',EDOMAIN));
define('CUSTOM_MENU_TAG_TITLE_EVENT',__('Event Tags',EDOMAIN));
define('CUSTOM_MENU_TAG_SEARCH_EVENT',__('Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_POPULAR_EVENT',__('Popular Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_ALL_EVENT',__('All Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_PARENT_EVENT',__('Parent Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_PARENT_COL_EVENT',__('Parent Event tags:',EDOMAIN));
define('CUSTOM_MENU_TAG_EDIT_EVENT',__('Edit Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_UPDATE_EVENT',__('Update Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_ADD_NEW_EVENT',__('Add new Event tags',EDOMAIN));
define('CUSTOM_MENU_TAG_NEW_ADD_EVENT',__('New Event tag name',EDOMAIN));
define('EVENT_ST_TIME',__('Start Time',EDOMAIN));
define('EVENT_END_TIME',__('End Time',EDOMAIN));
//custom field information title
define('EVENT_CUSTOM_INFORMATION',__('Event Custom Information',EDOMAIN));
define('CUSTOM_INFORMATION',__('Custom Information',EDOMAIN));
define('EVENT_TITLE_HEAD',__('Title',EDOMAIN));
define('EVENT_TYPE_TEXT',__('Event type',EDOMAIN));
define('ADDRESS',__('Address',EDOMAIN));
define('CATGORIES_TEXT',__('Categories',EDOMAIN));
define('TAGS_TEXT_HEAD',__('Tags',EDOMAIN));
/*Attend event msg and text  */
define('ATTEND_EVENT_MSG',__('are you going to attend',EDOMAIN));
define('REMOVE_EVENT_MSG',__('You are going to attend',EDOMAIN));
define('ATTEND_EVENT_TEXT',__('Yes, I am',EDOMAIN));
define('REMOVE_EVENT_TEXT',__('Not attending now.',EDOMAIN));

define('RECURRING_PER',__('Please Select Recurring per',EDOMAIN));
define('RECURRING_DAY_AFTER',__('Please Select Recurring end day after',EDOMAIN));

add_action('init','register_event_post_type',0);

function register_event_post_type()
{/* if doing ajax is set the function return */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {		
		return ;	
	}
	if(is_admin()){
		include(TEVOLUTION_EVENT_DIR.'events/install.php');
	}
}
if(file_exists(TEVOLUTION_EVENT_DIR . 'events/recurring_function.php'))
{
	include_once( TEVOLUTION_EVENT_DIR . 'events/recurring_function.php');
}
if(file_exists(TEVOLUTION_EVENT_DIR . 'events/recurring_html.php'))
{
	include_once( TEVOLUTION_EVENT_DIR . 'events/recurring_html.php');
}
if(file_exists(TEVOLUTION_EVENT_DIR . 'events/calendar_widgets.php'))
{
	include_once( TEVOLUTION_EVENT_DIR . 'events/calendar_widgets.php');
}
if(file_exists(TEVOLUTION_EVENT_DIR . 'events/event_widgets.php'))
{
	include_once( TEVOLUTION_EVENT_DIR . 'events/event_widgets.php');
}
if(file_exists(TEVOLUTION_EVENT_DIR . 'events/page-template_facebookevents.php'))
{
	include_once( TEVOLUTION_EVENT_DIR . 'events/page-template_facebookevents.php');
}
if(function_exists('_iscurlinstalled') && _iscurlinstalled())
{	
	if(file_exists(TEVOLUTION_EVENT_DIR."events/facebook-platform/src/facebook.php") && isset($_REQUEST['fb_event']) && $_REQUEST['fb_event'] == 'facebook_event'){
		include_once (TEVOLUTION_EVENT_DIR.'events/facebook-platform/src/facebook.php');
	}
}
?>