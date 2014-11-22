<?php
/*
name : register custom place post type 
description : Register place taxonomy.
*/
if(!defined('CUSTOM_POST_TYPE_LISTING'))
	@define('CUSTOM_POST_TYPE_LISTING','listing');
define('CUSTOM_CATEGORY_TYPE_LISTING','listingcategory');
define('CUSTOM_TAG_TYPE_LISTING','listingtags');
define('CUSTOM_MENU_TITLE_LISTING',__('Listings','templatic-admin'));
define('CUSTOM_MENU_NAME_LISTING',__('Listings','templatic-admin'));
define('CUSTOM_MENU_SIGULAR_NAME_LISTING',__('Listing','templatic-admin'));
define('CUSTOM_MENU_ADD_NEW_LISTING',__('Add Listing','templatic-admin'));
define('CUSTOM_MENU_ADD_NEW_ITEM_LISTING',__('Add new listing','templatic-admin'));
define('CUSTOM_MENU_EDIT_LISTING',__('Edit','templatic-admin'));
define('CUSTOM_MENU_EDIT_ITEM_LISTING',__('Edit listing','templatic-admin'));
define('CUSTOM_MENU_NEW_LISTING',__('New listing','templatic-admin'));
define('CUSTOM_MENU_VIEW_LISTING',__('View listing','templatic-admin'));
define('CUSTOM_MENU_SEARCH_LISTING',__('Search listing','templatic-admin'));
define('CUSTOM_MENU_NOT_FOUND_LISTING',__('No listing found','templatic-admin'));
define('CUSTOM_MENU_NOT_FOUND_TRASH_LISTING',__('No listing found in trash','templatic-admin'));
define('CUSTOM_MENU_CAT_LABEL_LISTING',__('Listing Categories','templatic-admin'));
define('CUSTOM_MENU_CAT_TITLE_LISTING',__('Listing Categories','templatic-admin'));
define('CUSTOM_MENU_SIGULAR_CAT_LISTING',__('Category','templatic-admin'));
define('CUSTOM_MENU_CAT_SEARCH_LISTING',__('Search category','templatic-admin'));
define('CUSTOM_MENU_CAT_POPULAR_LISTING',__('Popular categories','templatic-admin'));
define('CUSTOM_MENU_CAT_ALL_LISTING',__('All categories','templatic-admin'));
define('CUSTOM_MENU_CAT_PARENT_LISTING',__('Parent category','templatic-admin'));
define('CUSTOM_MENU_CAT_PARENT_COL_LISTING',__('Parent category:','templatic-admin'));
define('CUSTOM_MENU_CAT_EDIT_LISTING',__('Edit category','templatic-admin'));
define('CUSTOM_MENU_CAT_UPDATE_LISTING',__('Update category','templatic-admin'));
define('CUSTOM_MENU_CAT_ADDNEW_LISTING',__('Add new category','templatic-admin'));
define('CUSTOM_MENU_CAT_NEW_NAME_LISTING',__('New category name','templatic-admin'));
define('CUSTOM_MENU_TAG_LABEL_LISTING',__('Listing Tags','templatic-admin'));
define('CUSTOM_MENU_TAG_TITLE_LISTING',__('Listing Tags','templatic-admin'));
define('CUSTOM_MENU_TAG_NAME_LISTING',__('Listing Tags','templatic-admin'));
define('CUSTOM_MENU_TAG_SEARCH_LISTING',__('Listing Tags','templatic-admin'));
define('CUSTOM_MENU_TAG_POPULAR_LISTING',__('Popular listing tags','templatic-admin'));
define('CUSTOM_MENU_TAG_ALL_LISTING',__('All listing tags','templatic-admin'));
define('CUSTOM_MENU_TAG_PARENT_LISTING',__('Parent listing tags','templatic-admin'));
define('CUSTOM_MENU_TAG_PARENT_COL_LISTING',__('Parent listing tags:','templatic-admin'));
define('CUSTOM_MENU_TAG_EDIT_LISTING',__('Edit listing tags','templatic-admin'));
define('CUSTOM_MENU_TAG_UPDATE_LISTING',__('Update listing tags','templatic-admin'));
define('CUSTOM_MENU_TAG_ADD_NEW_LISTING',__('Add new listing tags','templatic-admin'));
define('CUSTOM_MENU_TAG_NEW_ADD_LISTING',__('New listing tag name','templatic-admin'));

add_action('init','register_place_post_type',0);
function register_place_post_type()
{
	/* if doing ajax is set the function return */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {		
		return ;	
	}
	
	if(is_admin()){	
		include(TEVOLUTION_DIRECTORY_DIR.'listing/install.php');	
	}
	
}
?>