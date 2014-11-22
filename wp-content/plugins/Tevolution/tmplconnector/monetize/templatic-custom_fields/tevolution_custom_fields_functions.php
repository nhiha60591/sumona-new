<?php
if(!is_admin() && !strstr($_SERVER['REQUEST_URI'],'/wp-admin/' ) || isset($_REQUEST['front']) && $_REQUEST['front']==1){
	add_filter('tiny_mce_plugins','tmpl_tiny_mce_plugins');
	add_filter('mce_buttons','tmpl_mce_buttons');
	add_filter('mce_buttons_2','tmpl_mce_buttons_2');
}
/*
 * This function user for get the tevolution custom fields on wpml language wise post join filter
 */
function custom_field_posts_where_filter($join)
{
	global $wpdb, $pagenow, $wp_taxonomies,$ljoin;
	$language_where='';
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		$language = ICL_LANGUAGE_CODE;
		$join .= " {$ljoin} JOIN {$wpdb->prefix}icl_translations t ON {$wpdb->posts}.ID = t.element_id			
			AND t.element_type IN ('post_custom_fields') JOIN {$wpdb->prefix}icl_languages l ON t.language_code=l.code AND l.active=1 AND t.language_code='".$language."'";
	}	
	return $join;
}


/* 
 * This function user for get the post type wise custom filed from  frontend submit post type
 * only get the show on submit form enable custom fields.
 */
function get_post_custom_fields_templ_plugin($post_types,$category_id='',$taxonomy='',$heading_type='',$remove_post_id='') {	

	global $wpdb,$post,$_wp_additional_image_sizes,$sitepress;
	if(@$_REQUEST['page'] != 'paynow'  && @$_REQUEST['page'] != 'transcation' && $category_id!='')
	{
		$category_id = explode(",",$category_id);
	}	
 	
	remove_all_actions('posts_where');
	$remove_post_id_array = explode(",",$remove_post_id);
	
	/* Get the custom fields on heading type wise */
	if($heading_type){
		$args=array('post_type'      => 'custom_fields',
					'posts_per_page' => -1	,
					'post_status'    => array('publish'),
					'post__not_in'   => $remove_post_id_array,
					'meta_query'     => array('relation' => 'AND',
											array('key' => 'post_type_'.$post_types, 'value' => array('all',$post_types),'compare' => 'IN','type'=> 'text'),											
											array('key' => 'show_on_page','value' =>  array('user_side','both_side'),'compare' => 'IN','type'=> 'text'),											
											array('key' => $post_types.'_heading_type','value' =>  array('basic_inf',htmlspecialchars_decode($heading_type)),'compare' => 'IN'),
											array('key' => 'is_submit_field', 'value' =>  '1','compare' => '='),
										),
					'meta_key'       => $post_types.'_sort_order',
					'orderby'        => 'meta_value_num',
					'meta_value_num' => $post_types.'_sort_order',
					'order'          => 'ASC'
				);		
		if((isset($_REQUEST['pid']) && $_REQUEST['pid']!='' && isset($_REQUEST['action']) && $_REQUEST['action']=='edit') || (isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=='edit')){
			/* Unset is submit field  on edit listing page for display all custom fields post type wise*/			
			unset($args['meta_query'][3]);	
		}
		
	}else{ /*Get the custom fields without heading type wise */
		$args=array('post_type'      => 'custom_fields',
					'posts_per_page' => -1	,
					'post_status'    => array('publish'),
					'post__not_in'   => $remove_post_id_array,
					'meta_query'     => array('relation' => 'AND',
											array('key' => 'post_type_'.$post_types.'','value' => array('all',$post_types),'compare' => 'In','type'=> 'text'),
											array('key' => 'show_on_page','value' =>  array('user_side','both_side'),'compare' => 'IN','type'=> 'text'),
											array('key' => 'is_submit_field', 'value' =>  '1','compare' => '='),
									    ),
					'meta_key'       => $post_types.'_sort_order',
					'orderby'        => 'meta_value_num',
					'meta_value_num' => $post_types.'_sort_order',
					'order'          => 'ASC'
					);
		
		if((isset($_REQUEST['pid']) && $_REQUEST['pid']!='' && isset($_REQUEST['action']) && $_REQUEST['action']=='edit') || (isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=='edit')){
			/* Unset is submit field  on edit listing page for display all custom fields post type wise*/
			unset($args['meta_query'][2]);	
		}		
	}	
	
	/* Get the custom fields category wise if category id not equal to blank */
	if($category_id!=''){
		$args['tax_query']	= array('relation' => 'OR',
									array('taxonomy' => $taxonomy,'field' => 'id','terms' => $category_id,'operator'  => 'IN','include_children' => false),
									array('taxonomy' => 'category','field' => 'id','terms' => 1,'operator'  => 'IN','include_children' => false)
								 );
	}	
	$post_query = null;
	remove_all_actions('posts_orderby');	
	
	/* add posts)join filter for get the custom fields on wpml language wise */
	add_filter('posts_join', 'custom_field_posts_where_filter');	
	$post_meta_info = new WP_Query($args);
	
	$return_arr = array();	
	if($post_meta_info){
		while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
			$is_active=get_post_meta($post->ID,"is_active",true);
			$ctype=get_post_meta($post->ID,"ctype",true);
			
			/*Custom fields loop returns if is active not equal to one or ctype equal to heading type */
			if(is_plugin_active('Tevolution-FieldsMonetization/fields_monetization.php') && (isset($_REQUEST['pakg_id']) && $_REQUEST['pakg_id']!='')){
				$package_select=$_REQUEST['pakg_id'];
				$field_monetiz_custom_fields=get_post_meta($package_select,'custom_fields',true);
				if(!empty($field_monetiz_custom_fields) && !in_array($post->ID, $field_monetiz_custom_fields)){
					continue;
				}
			}
			
			if($is_active!=1 || $ctype=='heading_type'){
				continue;
			}
		
			if(get_post_meta($post->ID,"ctype",true)){
				$options = explode(',',get_post_meta($post->ID,"option_values",true));
			}
			$field_category=get_post_meta($post->ID,"field_category",true);
			$custom_fields = array(
					"id"		          => $post->ID,
					"name"		          => get_post_meta($post->ID,"htmlvar_name",true),
					"label" 	          => $post->post_title,
					"htmlvar_name" 	      => get_post_meta($post->ID,"htmlvar_name",true),
					"default" 	          => get_post_meta($post->ID,"default_value",true),
					"type" 	 	          => get_post_meta($post->ID,"ctype",true),
					"desc"                => $post->post_content,
					"option_title"        => get_post_meta($post->ID,"option_title",true),
					"option_values"       => get_post_meta($post->ID,"option_values",true),
					"is_require"          => get_post_meta($post->ID,"is_require",true),
					"is_active"           => get_post_meta($post->ID,"is_active",true),
					"show_on_listing"     => get_post_meta($post->ID,"show_on_listing",true),
					"show_on_detail"      => get_post_meta($post->ID,"show_on_detail",true),
					"validation_type"     => get_post_meta($post->ID,"validation_type",true),
					"field_require_desc"  => get_post_meta($post->ID,"field_require_desc",true),
					"style_class"         => get_post_meta($post->ID,"style_class",true),
					"extra_parameter"     => get_post_meta($post->ID,"extra_parameter",true),
					"show_in_email"       => get_post_meta($post->ID,"show_in_email",true),
					"range_min"           => get_post_meta($post->ID,"range_min",true),
					"range_max"           => get_post_meta($post->ID,"range_max",true),
					"search_ctype"        => get_post_meta($post->ID,"search_ctype",true),
					"field_category"      => ($field_category)?$field_category: 'all',
					);
			if($options)
			{
				$custom_fields["options"]=$options;
			}
			$return_arr[get_post_meta($post->ID,"htmlvar_name",true)] = $custom_fields;
		endwhile;wp_reset_query();
	}
	/*remove posts_join wpml language filter */
	remove_filter('posts_join', 'custom_field_posts_where_filter');
	
	return $return_arr;
	
}

/* this function will remove the space and convert upper case latter to lower case */
function tmplCompFld($str){
	$str = strtolower(trim($str));
	return $str;
}

/* 
Name :display_custom_post_field_plugin
description : Returns all custom fields html
*/

function display_custom_post_field_plugin($custom_metaboxes,$session_variable,$post_type,$pkg_id='',$submit_page_id=''){
	global $wpdb;	
	
	foreach($custom_metaboxes as $heading=>$_custom_metaboxes)
	{
		$i = 0;
		$activ = fetch_active_heading($heading);
		/* Display custom fields heading  fields wise */
		add_action('wp_footer','add_file_exists_script');
		if($activ):
			$PostTypeObject = get_post_type_object($post_type);
			$_PostTypeName = $PostTypeObject->labels->name;
			if(function_exists('icl_register_string')){
				icl_register_string(DOMAIN,$_PostTypeName.'submit',$_PostTypeName);
				$_PostTypeName =icl_t(DOMAIN,$_PostTypeName.'submit',$_PostTypeName);
			}			
			$_PostTypeName = $_PostTypeName . ' ' . __('Information',DOMAIN);
			if($heading == '[#taxonomy_name#]' && $_custom_metaboxes)
			{
				$heading='';			
				$heading_desc='';
            }
			else
			{
				if($_custom_metaboxes){
					if(function_exists('icl_register_string')){
						icl_register_string(DOMAIN,$heading,$heading);
					}
					if(function_exists('icl_t')){
						$heading = icl_t(DOMAIN,$heading,$heading);
					}else{
						$heading = sprintf(__("%s",DOMAIN),$heading);
					}					
				}
				$heading_desc=$_custom_metaboxes['basic_inf']['desc'];
			}
			if($_custom_metaboxes && $i == 0 ){
				echo '<div class="sec_title">';
				
					if(tmplCompFld($heading) != tmplCompFld('Label of Field'))
						echo '<h3>'.$heading.'</h3>';
					echo ($heading_desc!='')? '<p>'.$heading_desc.'</p>' : '';
				echo '</div>';
				$i++;
			}
		endif;	
		/* Finish custom field heading display section */
		
		foreach($_custom_metaboxes as $key=>$val) {
			$name = $val['name'];
			$site_title = $val['label'];
			$type = $val['type'];
			$htmlvar_name = $val['htmlvar_name'];			
			$field_category=$val['field_category'];
			//set the post category , post title, post content, post image and post expert replace as per post type
			if($htmlvar_name=="category")
			{
				$site_title=str_replace('Post Category',__('Category',DOMAIN),$site_title);
			}
			if($htmlvar_name=="post_title")
			{
				$site_title=str_replace('Post Title',__('Title',DOMAIN),$site_title);
			}
			if($htmlvar_name=="post_content")
			{
				$site_title=str_replace('Post Content',ucfirst($post_type)." ".__('Description',DOMAIN),$site_title);
			}
			if($htmlvar_name=="post_excerpt")
			{
				$site_title=str_replace('Post Excerpt',ucfirst($post_type)." ".__('description in two lines (will be shown on listing pages)',DOMAIN),$site_title);
			}
			if($htmlvar_name=="post_images")
			{
				$site_title=str_replace('Post Images',__('Images',DOMAIN),$site_title);
			}
			//finish post type wise replace post category, post title, post content, post expert, post images
			$admin_desc = $val['desc'];
			$option_values = $val['option_values'];
			$default_value = $val['default'];
			$style_class = $val['style_class'];
			$extra_parameter = $val['extra_parameter'];
			$field_require_desc = $val['field_require_desc'];
			if(!$extra_parameter){ $extra_parameter ='';}
			/* Is required CHECK BOF */
			$is_required = '';
			$input_type = '';
			if(trim($val['validation_type']) != ''){
				if($val['is_require'] == '1'){
				$is_required = '<span class="required">*</span>';
				}
				
				$is_required_msg = '<span id="'.$name.'_error" class="message_error2"></span>';
			} else {
				$is_required = '';
				$is_required_msg = '';
			}
			/* Is required CHECK EOF */
			$value = "";
			if(@$_REQUEST['pid'])
			{
				$post_info = get_post($_REQUEST['pid']);
				if($name == 'post_title') {
					$value = $post_info->post_title;
				}
				elseif($name == 'post_content') {
					$value = $post_info->post_content;
				}
				elseif($name == 'post_excerpt'){
					$value = $post_info->post_excerpt;
				}
				else {
					$value = get_post_meta($_REQUEST['pid'], $name,true);
				}
			
			}
			
			if(isset($_SESSION[$session_variable]) && !empty($_SESSION[$session_variable]))
			{
				$value = @$_SESSION[$session_variable][$name];
			}elseif(isset($_REQUEST[$name])){
				$value = $_REQUEST[$name];
			}
			$value = apply_filters('SelectBoxSelectedOptions',$value,$name);			

		/* custom fields loop continue when custom field type equal to heading type */
		if($type=='heading_type' || $type=='post_categories'){
			continue;
		}
		do_action('tmpl_custom_fields_'.$name.'_before_wrap');		
		
		$custom_fileds=($type!='post_categories')?'custom_fileds':'';
		?>
		<div class="form_row clearfix <?php echo $custom_fileds.' '.$style_class. ' '.$name;?>">
		   
		<?php
		/* label of custom fields */
		if($type=='text'){
			$labelclass= apply_filters('tmpl_cf_lbl_class_'.$name ,'r_lbl');
		}else{
			$labelclass= apply_filters('tmpl_cf_lbl_class_'.$name ,'');
		}
		
		/*Show label as heading type if the fields heading type is set as "Label of field" */
		if(tmplCompFld($heading) == tmplCompFld('Label of Field')){
			echo '<div class="sec_title">';
					echo '<h3>'.$site_title.$is_required.'</h3>';
			echo '</div>';
		}else{
			$label = "<label class=".$labelclass.">".$site_title.$is_required."</label>";
			if((tmplCompFld($site_title) != tmplCompFld('category')) && (tmplCompFld($site_title) != tmplCompFld('Multi City')))
				echo $label;
		}
			
		   
		/* label of custom fields */
		
		switch ($type) {
			case "text":
				/* input type text - when the fields name is geo latitude and longitude we needs to add extra functions in input field */
				if($name == 'geo_latitude' || $name == 'geo_longitude') {
					$extra_script = apply_filters('tmpl_cf_extra_fields_'.$name,'onblur="changeMap();"');
				} else {
					$extra_script =  apply_filters('tmpl_cf_extra_fields_'.$name,'"');;
				}

				do_action('tmpl_custom_fields_'.$name.'_before'); ?>
				
				<input name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php if(isset($value) && $value!=''){ echo stripslashes($value); } ?>" type="text" class="textfield <?php echo $style_class;?>" <?php echo $extra_parameter; ?> <?php echo $extra_script;?> placeholder="<?php echo @$val['default']; ?>"/>
				
				<?php echo $is_required_msg;
				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after'); 
				do_action('tmpl_text_cutom_fields_settings',$custom_metaboxes,$session_variable,$post_type,$pkg_id,$val['name']);
				break;
			case "date":
				/* Script for date picker */
				
				if(!tmpl_wp_is_mobile()){
					?>     
					<script type="text/javascript">
						jQuery(function(){
							var pickerOpts = {						
								showOn: "both",
								dateFormat: 'yy-mm-dd',
								//buttonImage: "<?php echo TEMPL_PLUGIN_URL;?>css/datepicker/images/cal.png",
								buttonText: '<i class="fa fa-calendar"></i>',
								buttonImageOnly: false,
								monthNames: objectL11tmpl.monthNames,
								monthNamesShort: objectL11tmpl.monthNamesShort,
								dayNames: objectL11tmpl.dayNames,
								dayNamesShort: objectL11tmpl.dayNamesShort,
								dayNamesMin: objectL11tmpl.dayNamesMin,
								isRTL: objectL11tmpl.isRTL,
								onChangeMonthYear: function(year, month, inst) {
								jQuery("#<?php echo $name;?>").blur();
								},
								onSelect: function(dateText, inst) {
								//jQuery("#<?php echo $name;?>").focusin();
								jQuery("#<?php echo $name;?>").blur();
								}
							};	
							jQuery("#<?php echo $name;?>").datepicker(pickerOpts);
						});
					</script>
					<?php 
					$type="text";
				}else{
					$type ="date";
				}	
				do_action('tmpl_custom_fields_'.$name.'_before'); ?>
				
				<input type="<?php echo $type; ?>" name="<?php echo $name;?>" id="<?php echo $name;?>" class="textfield <?php echo $style_class;?>" value="<?php echo esc_attr(stripslashes($value)); ?>" size="25" <?php echo 	$extra_parameter;?> placeholder="<?php echo @$val['default']; ?>"/>
				
				<?php echo $is_required_msg;
				
				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after'); ?>	          
				<?php
				break;
			case "multicheckbox":
				$options = $val['option_values'];				
				$option_titles = $val['option_title'];	
				if(!is_array($value))		{
					if(strstr($value,','))
					{							
						update_post_meta($_REQUEST['pid'],$htmlvar_name,explode(',',$value));
						$value=get_post_meta($_REQUEST['pid'],$htmlvar_name,true);
					}
				}
				if(!isset($_REQUEST['pid']) && !isset($_REQUEST['backandedit']))
				{
					$default_value = explode(",",$val['default']);
				}
	
				if($options)
				{  
					$chkcounter = 0;
					echo '<div class="form_cat_left hr_input_multicheckbox">';
					do_action('tmpl_custom_fields_'.$name.'_before');
					$option_values_arr = explode(',',$options);
					$option_titles_arr = explode(',',$option_titles);
					for($i=0;$i<count($option_values_arr);$i++)
					{
						$chkcounter++;
						$seled='';
						if(isset($_REQUEST['pid']) || isset($_REQUEST['backandedit']))
						  {
							$default_value = $value;
						  }
						if($default_value !=''){
						if(in_array($option_values_arr[$i],$default_value)){ 
						$seled='checked="checked"';} }	
						$option_titles_arr[$i] = (!empty($option_titles_arr[$i])) ? $option_titles_arr[$i] : $option_values_arr[$i];
						echo '

						<div class="form_cat">
							<label>
								<input name="'.$key.'[]"  id="'.$key.'_'.$chkcounter.'" type="checkbox" value="'.$option_values_arr[$i].'" '.$seled.'  '.$extra_parameter.' /> '.$option_titles_arr[$i].'
							</label>
						</div>';
					}
					echo '</div>';
					
					echo $is_required_msg;
					
					if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
					
					do_action('tmpl_custom_fields_'.$name.'_after');
				}
				break;  
			case "texteditor":
				do_action('tmpl_custom_fields_'.$name.'_before');
				$media_buttons = apply_filters('tmpl_media_button_pro',false);
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				
				do_action('tmpl_texteditor_cutom_fields_settings',$custom_metaboxes,$session_variable,$post_type,$pkg_id,$val['name'],$submit_page_id);
				/* Wp editor on submit form */
				$settings =   apply_filters('tmpl_cf_wpeditor_settings',array(
						'wpautop' => false,
						'media_buttons' => $media_buttons,
						'textarea_name' => $name,
						'textarea_rows' => apply_filters('tmpl_wp_editor_rows',get_option('default_post_edit_rows',6)), // rows="..."
						'tabindex' => '',
						'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>',
						'editor_class' => '',
						'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo',
						'teeny' => false,
						'dfw' => false,
						'tinymce' => true,
						'quicktags' => false
					));				
				if(isset($value) && $value != '') 
				{  $content=$value; 
				}else{
					$content= $val['default']; 
				} 				
				wp_editor( stripslashes($content), $name, apply_filters('tmpl_wp_editor_settings',$settings,$name));

				echo $is_required_msg;

				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after');
				do_action('tmpl_text_cutom_fields_settings',$custom_metaboxes,$session_variable,$post_type,$pkg_id,$val['name']);
				break;
		    case "textarea":
				
				do_action('tmpl_custom_fields_'.$name.'_before'); ?>
				
                <textarea name="<?php echo $name;?>" id="<?php echo $name;?>" class="<?php if($style_class != '') { echo $style_class;}?> textarea" <?php echo $extra_parameter;?> placeholder="<?php echo @$val['default']; ?>" rows="3"><?php if(isset($value))echo stripslashes($value);?></textarea>
               	<?php echo $is_required_msg;
				
				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after'); 
				do_action('tmpl_textarea_cutom_fields_settings',$custom_metaboxes,$session_variable,$post_type,$pkg_id,$val['name']);
				break;
				
		    case "radio":
				
				do_action('tmpl_custom_fields_'.$name.'_before'); 
			
				$options = $val['option_values'];
				$option_title = $val['option_title'];
				if($options)
				{ 
					$chkcounter = 0;
					echo '<div class="form_cat_left">';
					$option_values_arr = explode(',',$options);
					$option_titles_arr = explode(',',$option_title);
			
					if($option_title ==''){  $option_titles_arr = $option_values_arr;  }
					
					echo '<ul class="hr_input_radio">';
					for($i=0;$i<count($option_values_arr);$i++)
					{
						$chkcounter++;
						$seled='';
						
						if($default_value == $option_values_arr[$i] || ($i==0 && trim($value)=='')){ $seled='checked="checked"';}
						if (isset($value) && trim($value) == trim($option_values_arr[$i])){ $seled='checked="checked"';}
						$event_type = array("Regular event", "Recurring event");
						
						if($key == 'event_type'):
							if (trim(@$value) == trim($event_type[$i])){ $seled="checked=checked";}
							echo '<li>
									<input name="'.$key.'"  id="'.$key.'_'.$chkcounter.'" type="radio" value="'.$event_type[$i].'" '.$seled.'  '.$extra_parameter.' /> <label for="'.$key.'_'.$chkcounter.'">'.$option_titles_arr[$i].'</label>
								</li>';
						else:
							echo '<li><input name="'.$key.'"  id="'.$key.'_'.$chkcounter.'" type="radio" value="'.$option_values_arr[$i].'" '.$seled.'  '.$extra_parameter.' /> <label for="'.$key.'_'.$chkcounter.'">'.$option_titles_arr[$i].'</label>
								</li>';
						endif;
					}
					echo '</ul>';	
					
					echo '</div>';
				}
				
				echo $is_required_msg;
				
				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after');
				
				break;
		    case "select":
				do_action('tmpl_custom_fields_'.$name.'_before'); ?>
                <select name="<?php echo $name;?>" id="<?php echo $name;?>" class="textfield textfield_x <?php echo $style_class;?>" <?php echo $extra_parameter;?>>
					<option value=""><?php _e("Please Select",DOMAIN);?></option>
					<?php if($option_values){
						//$option_values_arr = explode(',',$option_values);
						$option_title = ($val['option_title']) ? $val['option_title'] : $val['option_values'];
						$option_values_arr = apply_filters('SelectBoxOptions',explode(',',$option_values),$name);
						$option_title_arr = apply_filters('SelectBoxTitles',explode(',',$option_title),$name);
						for($i=0;$i<count($option_values_arr);$i++)
						{
						?>
						<option value="<?php echo $option_values_arr[$i]; ?>" <?php if($value==$option_values_arr[$i]){ echo 'selected="selected"';} else if($default_value==$option_values_arr[$i]){ echo 'selected="selected"';}?>><?php echo $option_title_arr[$i]; ?></option>
						<?php	
						}
					}?>
				</select>
                <?php echo $is_required_msg;
				
				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after');
				break;
		    case "upload": ?>
				<!-- html for image upload for submit form front end -->
				<div class="upload_box <?php echo apply_filters('tmpl_cf_img_uploder_class',''); ?>">
                 <div class="hide_drag_option_ie">
					<p><?php _e('You can drag &amp; drop images from your computer to this box.',DOMAIN); ?></p>
					<p><?php _e('OR',DOMAIN); ?></p>
                 </div>
					<?php 
					echo '<div class="tmpl_single_uploader">';
						do_action('tmpl_custom_fields_'.$name.'_before');
						$wp_upload_dir = wp_upload_dir();?>
						
						<!-- Save the uploaded image path in hidden fields -->
						<input type="hidden" value="<?php echo stripslashes($value); ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="fileupload uploadfilebutton"  placeholder="<?php echo @$val['default']; ?>"/>
						<div id="<?php echo $name; ?>"></div>
						
						<div id="fancy-contact-form">
							<div class="dz-default dz-message" ><span  id="fancy-<?php echo $name; ?>"><span><i class="fa fa-folder"></i>  <?php _e('Upload Image',DOMAIN); ?></span></span></div>
                            <?php
							if(@$_REQUEST['pid']==''){
							?>
								<span  id="image-<?php echo $name; ?>"></span>
                            <?php } ?>
							<input type="hidden" name="submitted" value="1">
						</div>
						<script>
							var image_thumb_src = '<?php echo  $wp_upload_dir['url'];?>/';
							jQuery(document).ready(function(){
								var settings = {
									url: '<?php echo plugin_dir_url( __FILE__ ); ?>single-upload.php',
									dragDrop:true,
									fileName: "<?php echo $name; ?>",
									allowedTypes:"jpeg,jpg,png,gif,doc,pdf,zip",	
									returnType:"json",
									multiple:false,
									showDone:false,
									showAbort:false,
									showProgress:true,
									onSubmit:function(files, xhr)
									{
										//jQuery('.ajax-file-upload-statusbar').html('');
									},
									onSuccess:function(files,data,xhr)
									{
										jQuery('#image-<?php echo $name; ?>').html('');
										if(jQuery('#img_<?php echo $name; ?>').length > 0)
										{
											jQuery('#img_<?php echo $name; ?>').remove();
										}
									    var img = jQuery('<img height="60px" width="60px" id="img_<?php echo $name; ?>">'); //Equivalent: $(document.createElement('img'))
									    data = data+'';
										if(data != 'error'){
											var id_name = data.split('.'); 
											var img_name = '<?php echo bloginfo('template_url')."/images/tmp/"; ?>'+id_name[0]+"."+id_name[1];
											
											img.attr('src', img_name);
											img.appendTo('#image-<?php echo $name; ?>');
										}
										else
										{
											jQuery('#image-<?php echo $name; ?>').html("<?php _e('Image can&rsquo;t be uploaded due to some error.',DOMAIN); ?>");
											jQuery('.ajax-file-upload-statusbar').css('display','none');
											return false;
										}
										jQuery('#image-<?php echo $name; ?>').css('display','');
										jQuery('#<?php echo $name; ?>').val(image_thumb_src+data);
										jQuery('.ajax-file-upload-filename').css('display','none');
										jQuery('.ajax-file-upload-red').css('display','none');
										jQuery('.ajax-file-upload-progress').css('display','none');
									},
									showDelete:true,
									deleteCallback: function(data,pd)
									{
										for(var i=0;i<data.length;i++)
										{
											jQuery.post("<?php echo plugin_dir_url( __FILE__ ); ?>delete_image.php",{op:"delete",name:data[i]},
											function(resp, textStatus, jqXHR)
											{
												//Show Message  
												jQuery('#image-<?php echo $name; ?>').html("<div><?php _e('File Deleted',DOMAIN);?></div>");
												jQuery('#<?php echo $name; ?>').val('');
											});
										 }      
										pd.statusbar.hide(); //You choice to hide/not.

									}
								}
								var uploadObj = jQuery("#fancy-"+'<?php echo $name; ?>').uploadFile(settings);
							});
						</script>
						<?php do_action('tmpl_custom_fields_'.$name.'_after');
						
						/* check the format of uploaded file ( is image ??)*/
						if($_REQUEST['pid'] || $_SESSION['custom_fields'][$name] != '' || $_REQUEST[$name] != ''):
							if($_SESSION['custom_fields'][$name] != '')
							{
								$image = $_SESSION['custom_fields'][$name];
							}
							else
							{
								$image = get_post_meta($_REQUEST['pid'],$name, $single = true);
							}
							if(isset($_REQUEST[$name]) && $_REQUEST[$name] != '')
							{
								$image = $_REQUEST[$name];
							}
							$upload_file=strtolower(substr(strrchr($image,'.'),1));					 
							if($upload_file =='jpg' || $upload_file =='jpeg' || $upload_file =='gif' || $upload_file =='png' || $upload_file =='jpg' ){
									?>
								<p id="image-<?php echo $name; ?>" class="resumback"><img height="60px" width="60px" src="<?php echo $image; ?>" /><span class="ajax-file-upload-red" onclick="delete_image('<?php echo basename($value);?>')"><?php _e('Delete',ADMINDOMAIN); ?></span></p>
						<?php }else{ ?>
								<p class="resumback"><a href="<?php echo get_post_meta($_REQUEST['pid'],$name, $single = true); ?>"><?php echo basename(get_post_meta($_REQUEST['pid'],$name, $single = true)); ?></a></p>
							<?php } 
						endif; 
						echo '</div>';
						if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif; ?>
				<?php echo $is_required_msg;?>
				</div>
			<?php	
				break;
				
		    case "oembed_video": ?>
			
				<?php do_action('tmpl_custom_fields_'.$name.'_before'); ?>
				
				<input name="<?php echo $name;?>" id="<?php echo $name;?>" value='<?php if(isset($value) && $value!=''){ echo stripslashes($value); } ?>' type="text" class="textfield <?php echo $style_class;?>" <?php echo $extra_parameter; ?> <?php echo $extra_script;?> placeholder="<?php echo @$val['default']; ?>"/>
				
				<?php echo $is_required_msg;
				if($admin_desc!=""):?>
					<div class="description"><?php echo $admin_desc; ?></div>
				<?php endif;
				
				do_action('tmpl_custom_fields_'.$name.'_after');
				break;
				
		    case "range_type":
			
				$range_min=$val['range_min'];
				$range_max=$val['range_max'];
				global $validation_info;					
				if($val['is_require']==0 && $range_min!="" && $range_max!='' && $val['search_ctype']=='slider_range'){
					$validation_info[] = array(
						'title'	       => $val['label'],
						'name'	       => $key,
						'espan'	       => $key.'_error',
						'type'	       => $val['type'],
						'text'	       => $val['text'],
						'is_require'	  => 1,
						'validation_type'=> 'digit',
						'search_ctype'=> $val['search_ctype']
					);
				}
				
				do_action('tmpl_custom_fields_'.$name.'_before'); ?> 
				
			    <input name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php if(isset($value) && $value!=''){ echo stripslashes($value); } ?>" type="text" class="textfield <?php echo $style_class;?>" <?php echo $extra_parameter; ?> <?php echo $extra_script;?> placeholder="<?php echo @$val['default']; ?>" min="<?php echo $range_min?>" max="<?php echo $range_max?>"/>
				<?php echo $is_required_msg;
			  
				if($admin_desc!=""):?><div class="description"><?php echo $admin_desc; ?></div><?php endif;
			  
				do_action('tmpl_custom_fields_'.$name.'_after'); 
				
				break;
				
			case "image_uploader":
			
				echo '<div class="upload_box">';
					add_action('wp_footer','callback_on_footer_fn');?>
                    <div class="hide_drag_option_ie">
                        <p><?php _e('You can drag &amp; drop images from your computer to this box.',DOMAIN); ?></p>
                        <p><?php _e('OR',DOMAIN); ?></p>
                    </div>
					<?php
					include (apply_filters('include_image_upload_script',TEMPL_MONETIZE_FOLDER_PATH."templatic-custom_fields/image_uploader.php")); ?>
                    <span class="message_note"><?php echo $admin_desc;?></span>
                    <span class="message_error2" id="post_images_error"></span>
					<span class="safari_error" id="safari_error"></span>
				<?php
				echo "</div>";
				break;
				
		    case "geo_map":
				include_once(TEMPL_MONETIZE_FOLDER_PATH."templatic-custom_fields/location_add_map.php"); 
				
				if($admin_desc == ''): ?>
					<span class="message_note"><?php echo $GET_MAP_MSG;?></span>
				<?php endif; 
				break;
		    default:
				do_action('tevolution_custom_fieldtype',$key,$val,$post_type);
		}
		do_action('tmpl_cutom_fields_settings',$custom_metaboxes,$session_variable,$post_type,$pkg_id,$name);
		/* Switch case end */

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		do_action('show_new_custom_field',$type,$site_title,$is_required);
		
		?>     
		 
		</div>    
		<?php
		do_action('tmpl_custom_fields_'.$name.'_after_wrap');
		}
	}	
}




/* Fetch heading type custom fields. its display in custom field create or edit section */
function fetch_heading_posts()
{
	global $wpdb,$post;
	remove_all_actions('posts_where');
	$heading_title = array();
	/* Wp query passing argument for fetch is active heading type*/
	$args=array('post_type'      => 'custom_fields',
				'posts_per_page' => -1	,
				'post_status'    => array('publish'),
				'meta_query'     => array('relation' => 'AND',
									   array('key' => 'ctype','value' => 'heading_type','compare' => '=','type'=> 'text'),
									   array('key' => 'is_active','value' => '1','compare' => '=','type'=> 'text')
									),
				'meta_key'       => 'sort_order',
				'orderby'        => 'meta_value_num',
				'meta_value_num' =>'sort_order',
				'order'          => 'ASC'
				);
	$post_query = null;
	add_filter('posts_join', 'custom_field_posts_where_filter');
	$post_query = new WP_Query($args);
	remove_filter('posts_join', 'custom_field_posts_where_filter');
	$post_meta_info = $post_query;
	
	if($post_meta_info){
		while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
			$heading_title[$post->post_name] = $post->post_title;
		endwhile;
	}
	return $heading_title;
}


/* 
Name :display_custom_category_field_plugin
description : Returns category custom fields html on submit page
*/
function display_custom_category_field_plugin($custom_metaboxes,$session_variable,$post_type,$cpost_type='post'){ 
  
	foreach($custom_metaboxes as $key=>$val) { 
		$name = $val['name'];
		$site_title = $val['label'];
		$type = $val['type'];
		$htmlvar_name = $val['htmlvar_name'];
		$admin_desc = $val['desc'];
		$option_values = $val['option_values'];
		$default_value = $val['default'];
		$style_class = $val['style_class'];
		$extra_parameter = $val['extra_parameter'];
		if(!$extra_parameter){ $extra_parameter ='';}
		/* Is required CHECK BOF */
		$is_required = '';
		$input_type = '';
		if($val['is_require'] == '1'){
			$is_required = '<span class="required">*</span>';
			$is_required_msg = '<span id="'.$name.'_error" class="message_error2"></span>';
		} else {
			$is_required = '';
			$is_required_msg = '';
		}
		/* Is required CHECK EOF */
		if(@$_REQUEST['pid'])
		{
			$post_info = get_post($_REQUEST['pid']);
			if($name == 'post_title') {
				$value = $post_info->post_title;
			}else {
				$value = get_post_meta($_REQUEST['pid'], $name,true);
			}
			
		}else if(isset($_SESSION[$session_variable]) && !empty($_SESSION[$session_variable]))
		{
			$value = @$_SESSION[$session_variable][$name];
		}else{
			$value='';
		}
	   
	if($type=='post_categories')
	{ /* fetch catgories on action */	
		wp_reset_query();
		global $post;
		$submit_post_type=get_post_meta($post->ID,'submit_post_type',true);
		$PostTypeObject = get_post_type_object(($submit_post_type!='')?$submit_post_type:$cpost_type);
		$_PostTypeName = $PostTypeObject->labels->name;
		?>
        <div class="form_row clearfix">	  
            <label><?php _e('Select Categories',DOMAIN).$is_required; ?></label>
            <div class="category_label"><?php include(TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/category.php');?></div>
            <?php echo $is_required_msg;?>
            <?php if($admin_desc!=""):?>
            	<div class="description"><?php echo $admin_desc; ?></div>
			<?php else: ?>
            	<span class="message_note msgcat"><?php _e("Select a category for your ",DOMAIN); echo strtolower($_PostTypeName); ?></span>
            <?php endif;
			
			/* check the category wise custom fields are enable or not - load Ajax if cat wise custom fields option is selected */
			
			$templatic_settings = get_option('templatic_settings');
		
			if((!isset($templatic_settings['templatic-category_custom_fields']) && $templatic_settings['templatic-category_custom_fields']=='') || (isset($templatic_settings['templatic-category_custom_fields']) && $templatic_settings['templatic-category_custom_fields']=='No')){
				$category_custom_fields = 0;
			}else{
				$category_custom_fields = 1;
			}
			?>
			<input type="hidden" name="cat_fields" id="cat_fields" value="<?php echo $category_custom_fields; ?>"/>
        </div>    
    <?php 
	}elseif(isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg'] ==1 && $type=='post_categories'){
		wp_reset_query();
		global $post;
		$PostTypeObject = get_post_type_object(get_post_meta($post->ID,'submit_post_type',true));
		$_PostTypeName = $PostTypeObject->labels->name;

		 ?>
        <div class="form_row clearfix">        
            <label><?php echo $_PostTypeName. __('Category',DOMAIN).$is_required; ?></label>
            <div class="category_label"><?php include(TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/category.php');?></div>
            <?php echo $is_required_msg;
			
			if($admin_desc!=""):?>
            	<div class="description"><?php echo $admin_desc; ?></div>
			<?php else:
				$PostTypeObject = get_post_type_object($post_type);
				$_PostTypeName = $PostTypeObject->labels->name;?>
            	<span class="message_note msgcat"><?php _e("n which category you'd like to publish this ",DOMAIN); echo strtolower($_PostTypeName) ."?"; ?></span>
            <?php endif;?>
        </div>    
	<?php }
	do_action('show_additional_custom_field',$type,$site_title,$is_required,$cpost_type);
	}

}

/* This function use for display submit form custom fields category wise using jquery ajax.*/
add_action( 'wp_ajax_nopriv_submit_category_custom_fields','tmpl_get_submit_category_custom_fields');
add_action( 'wp_ajax_submit_category_custom_fields' ,'tmpl_get_submit_category_custom_fields');
function tmpl_get_submit_category_custom_fields(){
	
	$post_type=$_REQUEST['post_type'];
	$all_cat_id=$_REQUEST['category_id'];	
	$pakg_id = $_REQUEST['pakg_id'];
	$submit_page_id = $_REQUEST['submit_page_id'];
	/*Get the taxonomy mane from  post type */
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));
	$taxonomy = $taxonomies[0];
	
	/*fetch heading type from post type */
	$heading_type = fetch_heading_per_post_type($post_type);			
	if(count($heading_type) > 0)
	{
		foreach($heading_type as $_heading_type){
			//custom fields for custom post type..
			$custom_metaboxes[$_heading_type] = get_post_custom_fields_templ_plugin($post_type,$all_cat_id,$taxonomy,$_heading_type);
		}
	}else{
		//custom fields for custom post type..
		$custom_metaboxes[] = get_post_custom_fields_templ_plugin($post_type,$all_cat_id,$taxonomy,'');
	}	
	
	$validation_info=apply_filters('tevolution_submit_from_validation',$validation_info,$custom_metaboxes);
	/* Display custom fields post type wuse */
	//display custom fields html on submit form.
	display_custom_post_field_plugin($custom_metaboxes,'custom_fields',$post_type,$pakg_id,$submit_page_id);
	/*action after custom fields*/
	do_action('action_after_custom_fields',$custom_metaboxes,'custom_fields',$post_type,$pakg_id);
	/* wp_editor load using jquery ajax script  */  
	if (class_exists('_WP_Editors')) {
		_WP_Editors::editor_js();
	}
	die();
}



/* 
 * Display submit preview on popup model window 
 * Load preview page template as per post type subit page
 */
add_action( 'wp_ajax_nopriv_tevolution_submit_from_preview','tmpl_get_tevolution_submit_from_preview');
add_action( 'wp_ajax_tevolution_submit_from_preview' ,'tmpl_get_tevolution_submit_from_preview');
function tmpl_get_tevolution_submit_from_preview(){
	
	$post_type=$_REQUEST['submit_post_type'];	
	/* Do action for add additional post type preview page display hook */	
	
	do_action('before_tevolution_submit_'.$post_type.'_preview');
	
	get_template_part( 'tevolution-single', $post_type.'-preview'); 
	
	do_action('after_tevolution_submit_'.$post_type.'_preview');
	
	die();
}

/* 
 * Display price package information while submission listing after selecting price package.
 */
add_action( 'wp_ajax_nopriv_tmpl_tevolution_submit_from_package_info','tmpl_tevolution_submit_from_package_info');
add_action( 'wp_ajax_tmpl_tevolution_submit_from_package_info' ,'tmpl_tevolution_submit_from_package_info');
function tmpl_tevolution_submit_from_package_info(){
	$package_array = get_post($_REQUEST['pkg_id']);
	$result = '';
	$result .='<span class="label label-default">'.ucfirst($package_array->post_title);
	if(get_post_meta($_REQUEST['pkg_id'],'package_amount',true) > 0 && isset($_REQUEST['pkg_subscribed']) && $_REQUEST['pkg_subscribed'] ==0) 
	{ 
		$result .= __(' Package with price ',DOMAIN);
		$result .= get_option('currency_symbol').get_post_meta($_REQUEST['pkg_id'],'package_amount',true); 
	}
	$result .='</span>';
	echo $result;exit;
}
/* 
 * Display category as per price package.
 */
add_action( 'wp_ajax_nopriv_tmpl_tevolution_submit_from_category','tmpl_tevolution_submit_from_category');
add_action( 'wp_ajax_tmpl_tevolution_submit_from_category' ,'tmpl_tevolution_submit_from_category');
function tmpl_tevolution_submit_from_category(){
	global $include_cat_array;
	
	$post_type=$_REQUEST['submit_post_type'];
	/*get the post type taxonomy */
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));
	$taxonomy = $taxonomies[0];
	if(isset($_REQUEST['package_select']) && $_REQUEST['package_selec'] != '')
	{
		$_REQUEST['pkg_id'] = $_REQUEST['package_select'];
	}
	$pkg_id=$_REQUEST['pkg_id'];
	/*Set the display category list on submit page  */
	$include_cat_array=get_post_meta($pkg_id,'category',true);
	if($include_cat_array!=''){
		$include_cat_array=explode(',',$include_cat_array);
	}
	//echo $post_type."==".$taxonomy."==".$pkg_id."==".$cat_array;
	$default_custom_metaboxes = get_post_fields_templ_plugin($post_type,$all_cat_id,$taxonomy);//custom fields for all category.
	$category_custom_metaboxes['category']=$default_custom_metaboxes['category'];
	/* Display post type category box */
	
	/*Unset action for get the category from package wise on submit form */
	unset($_REQUEST['action']);	
	/* Display Categort as per price package wise on submit form page */
	display_custom_category_field_plugin($category_custom_metaboxes,'custom_fields','post',$post_type);//displaty  post category html.	
	
	die();
}


/* 
 * Display category as per price package.
 */
add_action( 'wp_ajax_nopriv_tmpl_tevolution_submit_from_package_featured_option','tmpl_tevolution_submit_from_package_featured_option');
add_action( 'wp_ajax_tmpl_tevolution_submit_from_package_featured_option' ,'tmpl_tevolution_submit_from_package_featured_option');
function tmpl_tevolution_submit_from_package_featured_option(){
	global $current_user,$post,$monetization;
	if(isset($_REQUEST['package_select']) && $_REQUEST['package_select'] != '')
	{
		$_REQUEST['pkg_id'] = $_REQUEST['package_select'];
	}
	$post_type = $_REQUEST['submit_post_type'];
	$result = '';
	$result .= $monetization->tmpl_fetch_price_package_featured_option($current_user->ID,$post_type,$post->ID,$_REQUEST['pkg_id'],$_REQUEST['pkg_subscribed']);/*fetch the price package*/
	echo $result;exit;
}

/* get the link of submit form . which post type pass in argument */

if(!function_exists('tmpl_get_submitfrm_link')){
	function tmpl_get_submitfrm_link($post_type){
		global $current_user,$wp_query,$curauth;
		//$curauth =  $wp_query->get_queried_object();

		if($current_user->ID == $curauth->ID)
		{
		/* query to get the submit form link, it will check the "submit_post_type" meta key is available with the value of pot type pass in arg*/
		$args=array('post_type'=>'page','posts_per_page'=>-1,
				'meta_query'     => array('relation' => 'AND',
						   array('key' => 'submit_post_type','value' => $post_type,'compare' => '=='),
						   array('key' => 'is_tevolution_submit_form','value' => '1','compare' => '==')
						),
				);
		$post_query = new WP_Query($args);
		
		$submit_link='';
		if($post_query->have_posts()){
			while ($post_query->have_posts()) { $post_query->the_post();
				$submit_link = __(' Head over to the ',THEME_DOMAIN);
				$submit_link .='<a href="'.get_permalink().'" target="_blank">'.__('Submit ',DOMAIN)." ".ucfirst($post_type)." ".__('Form',DOMAIN).'</a>';	
				$submit_link .= __( ' to add one.', THEME_DOMAIN );
			}
		}
		return $submit_link;
		}
	}
}
function add_file_exists_script()
{
	?>
    <script>
	function doesFileExist(urlToFile)
	{
		var xhr = new XMLHttpRequest();
		xhr.open('HEAD', urlToFile, false);
		xhr.send();
		 
		if (xhr.status == "404") {
			return false;
		} else {
			return true;
		}
	}
	</script>
    <?php
}

add_action( 'wp_ajax_nopriv_submit_form_recaptcha_validation','tmpl_submit_form_recaptcha_validation');
add_action( 'wp_ajax_submit_form_recaptcha_validation' ,'tmpl_submit_form_recaptcha_validation');
function tmpl_submit_form_recaptcha_validation(){	

	$tmpdata = get_option('templatic_settings');
	$display =(isset($tmpdata['user_verification_page']) && $tmpdata['user_verification_page'] != "")? $tmpdata['user_verification_page']:"";
	
	if( is_plugin_active('wp-recaptcha/wp-recaptcha.php') && in_array('submit',$display) ){
		require_once( get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php');
		$a = get_option("recaptcha_options");
		$privatekey = $a['private_key'];
		if($_REQUEST["recaptcha_response_field"]!="")
		{
			$resp = recaptcha_check_answer ($privatekey,getenv("REMOTE_ADDR"),$_REQUEST["recaptcha_challenge_field"],$_REQUEST["recaptcha_response_field"]);												
			if (!$resp->is_valid ) {				
				$send_data['recaptcha_error']=$a['incorrect_response_error'];				
			}else{
				$send_data['recaptcha_error']=true;
			}
		}else{
			$send_data['recaptcha_error']=$a['no_response_error'];
		}
		
		echo $send_data['recaptcha_error'];
	}else{
		echo true;	
	}
	exit;
}
/* display payment options only when monetization is activated */
add_action('action_before_html','show_payemnt_gateway_error');
function show_payemnt_gateway_error()
{
	 ?>
	<span style="color:red;font-weight:bold;display:block;" id="payment_errors"><?php 
		if(isset($_REQUEST['paypalerror']) && $_REQUEST['paypalerror']=='yes'){
			echo $_SESSION['paypal_errors'];
		}
		if(isset($_REQUEST['eway_error']) && $_REQUEST['eway_error']=='yes'){
			echo $_SESSION['display_message'];
		}
		if(isset($_REQUEST['stripeerror']) && $_REQUEST['stripeerror']=='yes'){
			echo $_SESSION['stripe_errors'];
		}
		if(isset($_REQUEST['psigateerror']) && $_REQUEST['psigateerror']=='yes'){
			echo $_SESSION['psigate_errors'];
		}
		if(isset($_REQUEST['braintreeerror']) && $_REQUEST['braintreeerror']=='yes'){
			echo $_SESSION['braintree_errors'];
		}
		if(isset($_REQUEST['inspire_commerceerror']) && $_REQUEST['inspire_commerceerror']=='yes'){
			echo $_SESSION['inspire_commerce_errors'];
		}
	?></span>
    <?php
}
?>