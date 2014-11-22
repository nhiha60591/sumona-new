<?php
/*
 * Add The listing custom categories fields for 
 */	 
add_action( 'init', 'event_category_custom_field');
function event_category_custom_field()
{
	global $wpdb;		
	/*
	 * created and edit the listing custom post type category custom field store in term table
	 */
	$tevolution_taxonomy_marker=get_option('tevolution_taxonomy_marker');	
	if($tevolution_taxonomy_marker['ecategory']!='enable'){
		return '';
	}

	add_action('edited_ecategory','event_custom_fields_AlterFields');			
	add_action('created_ecategory','event_custom_fields_AlterFields');	
	add_filter('manage_ecategory_custom_column', 'manage_event_category_columns', 10, 3);
	add_filter('manage_edit-ecategory_columns', 'event_category_columns');	
	
	/*Event tags taxonomy */
	/*add_action('edited_etags','event_custom_fields_AlterFields');
	add_action('created_etags','event_custom_fields_AlterFields');
	add_filter('manage_etags_custom_column', 'manage_event_category_columns', 10, 3);
	add_filter('manage_edit-etags_columns', 'event_category_columns');*/
	/*
	 * created and edit the event custom post type category custom field store in term table
	 */
	
	//if(isset($_GET['taxonomy']) && ($_GET['taxonomy']== 'ecategory' ||$_GET['taxonomy']== 'etags' ))
	if(isset($_GET['taxonomy']) && ($_GET['taxonomy']== 'ecategory'))
	{
		$taxnow=$_GET['taxonomy'];
		add_action($taxnow.'_edit_form_fields','event_custom_fields_EditFields',11);
		add_action($taxnow.'_add_form_fields','event_custom_fields_AddFieldsAction',11);		
	}
}
function event_custom_fields_EditFields($tag)
{
	event_custom_fields_AddFields($tag,'edit');	
}
function event_custom_fields_AddFieldsAction($tag)
{
	event_custom_fields_AddFields($tag,'add');
}
/*
 * Function Name: event_custom_fields_AddFields
 * display custom field in event and listing category page
 */
function event_custom_fields_AddFields($tag,$screen)
{	
	$tax = @$tag->taxonomy;
	wp_enqueue_style('thickbox');
	?>
     	<div class="form-field-category">
		<tr class="form-field form-field-category">
			<th scope="row" valign="top"><label for="cat_icon"><?php _e("Map Marker", EDOMAIN); ?></label></th>
			<td> 
                    <input id="cat_icon" type="text" size="60" name="cat_icon" value="<?php echo ($tag->term_icon)? $tag->term_icon:''; ?>"/>&nbsp;<?php _e('Or',EDOMAIN);?>&nbsp;<a class="button upload_button" title="Add city background image" id="cat_icon" data-editor="cat_upload_icon" href="#">
                    <span class="wp-media-buttons-icon"></span><?php _e('Browse',EDOMAIN);?>	</a>		
                    <p class="description"><?php _e('It will appear on the homepage Google map for events placed in this category. ',EDOMAIN);?></p>
			</td>
		</tr>
          </div>
          <?php if(!is_plugin_active('Tevolution-Directory/directory.php')):?>
		<script type="text/javascript">
		/**
		 *
		 * Upload Option
		 *
		 * Allows window.send_to_editor to function properly using a private post_id
		 * Dependencies: jQuery, Media Upload, Thickbox
		 *
		 */
		(function ($) {
		  uploadOption = {
		    init: function () {
			 var formfield,
				formID,
				btnContent = true;
			 // On Click
			 $('.upload_button').live("click", function () {
			   formfield = $(this).prev('input').attr('id');
			   formID = $(this).attr('rel');
			   tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			   return false;
			 });
				  
			 window.original_send_to_editor = window.send_to_editor;
			 window.send_to_editor = function(html) {
				   if (formfield) {
					itemurl = $(html).attr('href');
					var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;
					var document = /(^.*\.pdf|doc|docx|ppt|pptx|odt*)/gi;
					var audio = /(^.*\.mp3|m4a|ogg|wav*)/gi;
					var video = /(^.*\.mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2*)/gi;
					if (itemurl.match(image)) {
					  btnContent = '<img src="'+itemurl+'" alt="" /><a href="javascript:(void);" class="remove"><?php _e('Remove Image',EDOMAIN); ?></a>';
					} else {
					  btnContent = '<div class="no_image">'+html+'<a href="" class="remove"><?php _e('Remove',EDOMAIN); ?></a></div>';
					}										
					$('#' + formfield).val(itemurl);
					$('#' + formfield).next().next('div').slideDown().html(btnContent);
					tb_remove();
				   } else {
					window.original_send_to_editor(html);
				   }
			 }
		    }
		  };
		  $(document).ready(function () {
		    uploadOption.init()
		  })
		})(jQuery);
		</script>
	<?php
		endif;
}
/*
 * Function Name: event_custom_fields_AlterFields
 * Description : add/ edit listing and event custom taxonomy custom field 
 */
function event_custom_fields_AlterFields($termId)
{
	global $wpdb;
	$term_table=$wpdb->prefix."terms";		
	$cat_icon=$_POST['cat_icon'];		
	//update the service price value in terms table field	<br />
	if(isset($_POST['cat_icon']) && $_POST['cat_icon']!=''){
		$sql="update $term_table set term_icon='".$cat_icon."' where term_id=".$termId;
		$wpdb->query($sql);
	}
}
/*
 * Function Name: event_category_columns
 * Description : manage columns for event and listing custom taxonomy
 */
function event_category_columns($columns)
{
	$columns['icon'] = __('Map Marker',EDOMAIN);	
	if(isset($_GET['taxonomy']) && $_GET['taxonomy']=='ecategory')
		$columns['posts'] = __('Events',EDOMAIN);
	if(isset($_GET['taxonomy']) && $_GET['taxonomy']=='listingcategory')
		$columns['posts'] = __('listings',EDOMAIN);
	return $columns;	
}
/*
 * Function Name: manage_event_category_columns
 * Description : display listing and event custom taxonomy custom field display in category columns
 */
function manage_event_category_columns($out, $column_name, $term_id){
	global $wpdb;
	$term_table=$wpdb->prefix."terms";		
	$sql="select * from $term_table where term_id=".$term_id;
	$term=$wpdb->get_results($sql);	
	
	switch ($column_name) {
		case 'icon':					
				 $out= ($term[0]->term_icon)?'<img src="'.$term[0]->term_icon.'" >' : '<img src="'.apply_filters('tmpl_default_map_icon',TEVOLUTION_EVENT_URL.'images/pin.png').'" >';
			break; 
		default:
			break;
	}
	return $out;	
}
add_filter( 'manage_edit-event_sortable_columns', 'templatic_edit_event_sortable_columns',11 ) ;
function templatic_edit_event_sortable_columns( $columns ) {
	$columns['address'] = 'address';
	$columns['event_type_'] = 'event_type_';	
	return $columns;
}
?>