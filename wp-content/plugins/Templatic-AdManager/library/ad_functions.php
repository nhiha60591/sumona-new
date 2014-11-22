<?php
/*
	It includes all function of ad manager plugin such as ad manager page.


 Create column for ad location and ad id in terms table: Start */
if(!function_exists('ad_plugin_activate')){
	function ad_plugin_activate(){
		global $wpdb;
		update_option('admanager_activate', 'true');
		$field_check = $wpdb->get_var("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_ad_ids'");
		if('term_ad_ids' != $field_check)
		{
			$wpdb->query("ALTER TABLE $wpdb->terms ADD term_ad_ids varchar(256)");
		}
	}
}
/* Create column for ad location and ad id in terms table: End */
/* Delete plugins related data from database: Start */
if(!function_exists('ad_plugin_deactivate')){
	function ad_plugin_deactivate()	{
		global $wpdb;
		delete_option('admanager_activate_field');
		delete_option('admanager_activate');
		$wpdb->query("ALTER TABLE $wpdb->terms DROP COLUMN term_ad_ids");
		
	}
}
/* Delete plugins related data from database: End */
add_action('admin_init', 'admanager_plugin_redirect');
/*
Name : admanager_plugin_redirect
Description : Redirect on plugin dashboard after activating plugin
*/
function admanager_plugin_redirect(){
	global $pagenow;
	if($pagenow=='plugins.php'){
		if(get_option('admanager_activate') == 'true'){
			update_option('admanager_activate', 'false');
			wp_redirect(admin_url('post-new.php?post_type=admanager'));
		}
	}
}
/* Remove View link from ad list: start */
add_filter( 'post_row_actions', 'remove_view_link', 10, 1 );
function remove_view_link( $actions ){
	$post_type = 'admanager';
	if( get_post_type() === $post_type )
	unset( $actions['view'] );
	return $actions;
}
/* Remove View link from appointments list: end */
/* Create Setting link for plugin: Start */
add_filter( 'plugin_action_links_' . AD_MANAGER_PLUGIN_BASENAME,'admanager_action_links'  );
if(!function_exists('admanager_action_links'))
{
	function admanager_action_links($links)
	{
		$plugin_links = array(
			'<a href="' . admin_url( 'post-new.php?post_type=admanager' ) . '">' . __( 'Settings', DOMAIN ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
}
/* Create Setting link for plugin: End */
add_action('init','plugin_add_location');
if(!function_exists('plugin_add_location'))
{
	function plugin_add_location()
	{
		/* Create Location Array for Ad display for post detail page: start */
		/* Format: do_action's action	=> title of location */
		global $ad_display_location;
		$flag                     = 0;
		$category_flag            = 0;
		$default_display_location = array();
		$supreme_theme_location = array(
			'posts_pages'		=> array(
				"open_content"     => "Before Title",
				"entry-title"      => "After Title",
				"open-post-content"=> "Before Content",
				"after_entry"      => "After Content",
				"before_comments"  => "Before Comments",
				"after_comments"   => "After Comments",
			)
		);
		if(class_exists('Supreme'))
		{
			$flag = 1;
		}
		elseif( get_option('template') == "supreme" )
		{
			$flag = 1;
		}
		else
		{
			$flag = 0;
		}
		if($flag == 0)
		{
			$default_display_location = array(
				'default'			=> array(
					"theme_before_post"=>	"Before Content",
					"theme_after_post" =>	"After Content",
				)
			);
		}
		if($flag == 1)
		{
			$default_display_location = array_merge($default_display_location,$supreme_theme_location);
		}
		

		/*fetch Tevolution custom post type.*/
		$templatic_custom_post = get_option('templatic_custom_post');
	
		/*the following loop if for managing the adds on category page and detail apge area */
		$tevolution_post_type=array();
		if($templatic_custom_post){
			foreach($templatic_custom_post as $key=>$value){
				if( $key == "admanager")
				{
						continue;
				}
				else
				{					

					if($key == 'listing')
					{
						$type = 'directory';
					}
					else
					{
						$type = $key;
					}
					/*array for showing adds on category pages which will be available on category page*/
					$add_manager_category_locations[$key] = array(
														$type."_before_categories_title"	=>	__('Above',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Category Title',PLUGIN_DOMAIN),
														$type."_after_categories_title"	=>	__('Below',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Category Title',PLUGIN_DOMAIN),
														$type."_before_categories_description"	=>	__('Above',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Category Description',PLUGIN_DOMAIN),
														$type."_after_categories_description"	=>	__('Below',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Category Description',PLUGIN_DOMAIN),
														$type."_before_loop_taxonomy"	=>	__('Above',PLUGIN_DOMAIN).' '.$value['label'].' '.__('on Category Page',PLUGIN_DOMAIN),
														$type."_after_loop_taxonomy"	=>	__('Below',PLUGIN_DOMAIN).' '.$value['label'].' '.__('on Category Page',PLUGIN_DOMAIN),
										);
					/*array for showing adds on category pages which will be available on detail page*/
					$add_manager_locations[$key] = array(
														$type."_before_post_title"	=>	__('Above',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Detail Title',PLUGIN_DOMAIN),
														$type."_after_post_title"	=>	__('Below',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Detail Title',PLUGIN_DOMAIN),
														$type."_before_post_content"	=>	__('Above',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Detail Description',PLUGIN_DOMAIN),
														$type."_after_post_content"	=>	__('Below',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Detail Description',PLUGIN_DOMAIN),
														$type."_before_post_loop"	=>	__('Above',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Detail Page',PLUGIN_DOMAIN),
														$type."_after_post_loop"	=>	__('Below',PLUGIN_DOMAIN).' '.$value['label'].' '.__('Detail Page',PLUGIN_DOMAIN)
										);
				}
			}
		}


		$other_locations = apply_filters('add_theme_detail_locations',$add_manager_locations);
		if( !empty($other_locations) )
		{
			$default_display_location = array_merge($default_display_location,$other_locations);
		}
		$ad_display_location = $default_display_location;
		/**
		*Create Location Array for Ad display for post detail page: end
		* Create Location Array for Ad display for category page: start
		* Format: do_action's action	=> title of location
		*/
		global $ad_display_location_category;
		$default_location_category = array();
		$supreme_location_category = array(
			'blog_category'	=>	array(
				"open_content"       => __("Above Title",PLUGIN_DOMAIN),
				"before_entry"       => __("Above each post",PLUGIN_DOMAIN),
				"after_entry"        => __("Below each post",PLUGIN_DOMAIN),
				"before_loop_archive"=> __("Above all posts",PLUGIN_DOMAIN),
				"after_loop_archive" => __("Below all posts",PLUGIN_DOMAIN),
			)
		);
		if(class_exists('Supreme'))
		{
			$category_flag = 1;
		}
		elseif( get_option('template') == "supreme" )
		{
			$category_flag = 1;
		}
		else
		{
			$category_flag = 0;
		}
		if($category_flag == 0)
		{
			$default_location_category = array(
				'default'			=> array(
					"theme_before_post"=>	"Before Content",
					"theme_after_post" =>	"After Content",
				)
			);
		}
		if($category_flag == 1)
		{
			$default_location_category = array_merge($default_location_category,$supreme_location_category);
		}
		/*added the array to display adds for category page of tevolution post type*/
		$other_category_locations = apply_filters('add_theme_category_locations',$add_manager_category_locations);
		if( !empty($other_category_locations) )
		{
			$default_location_category = array_merge($default_location_category,$other_category_locations);
		}
		$ad_display_location_category = $default_location_category;
	}
}
/* Create Location Array for Ad display for category page: end */
/* Filter to change the updated, deleted message of admanager: Start */
add_filter('post_updated_messages', 'admanager_updated_messages');
if(!function_exists('admanager_updated_messages'))
{
	function admanager_updated_messages( $messages )
	{
		$messages['admanager'] = array(
			0 => '',
			1=> sprintf( __('Advertisement updated.',PLUGIN_DOMAIN).' <a href="%s">'.__('View Advertisement',PLUGIN_DOMAIN).' </a>', esc_url( get_permalink(@$post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Advertisement updated.'),
			5=> isset($_GET['revision']) ? sprintf( __('Advertisement restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Advertisement published.',PLUGIN_DOMAIN).' <a href="%s">'.__('View Advertisement',PLUGIN_DOMAIN).' </a>', esc_url( get_permalink(@$post_ID) ) ),
			7 => __('Advertisement saved.'),
			8 => sprintf( __('Advertisement submitted.',PLUGIN_DOMAIN).' <a target="_blank" href="%s">'.__('Preview Advertisement',PLUGIN_DOMAIN).' </a>', esc_url( add_query_arg( 'preview', 'true', get_permalink(@$post_ID) ) ) ),
			9 => sprintf( __('Advertisement scheduled for:',PLUGIN_DOMAIN).' <strong>%1$s</strong>. <a target="_blank" href="%2$s">'.__('Preview Advertisement',PLUGIN_DOMAIN).'</a>',
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( @$post->post_date ) ), esc_url( get_permalink(@$post_ID) )),
			10=> sprintf( __('Advertisement draft updated.',PLUGIN_DOMAIN).' <a target="_blank" href="%s">'.__('Preview Advertisement',PLUGIN_DOMAIN).' </a>', esc_url( add_query_arg( 'preview', 'true', get_permalink(@$post_ID) ) ) ),
		);
		return $messages;
	}
}
/* Filter to change the updated, deleted message of admanager: End */
/* Fucntion to call any script or style or action on admin head: start */
add_action('admin_head','admin_head_function_callback');
if(!function_exists('admin_head_function_callback'))
{
	function admin_head_function_callback()
	{
		global $post;
		if( "admanager" == @$post->post_type )
		{
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ()
				{
					jQuery(".selectall").click(function ()
						{
							jQuery(".selectedId").prop("checked", isChecked("selectall"));
						});
					jQuery(".locationselectall").click(function ()
						{
							jQuery(".locationselectedId").prop("checked", isChecked("locationselectall"));
						});
				});
			function isChecked(checkboxId)
			{
				var id = "." + checkboxId;
				return jQuery(id).is(":checked");
			}
			function resetSelectAll(all_id,selected_id)
			{
				var all_id = "." + all_id;
				var selected_id = "." + selected_id;
				if (jQuery(selected_id).length == jQuery(selected_id+":checked").length)
				{
					jQuery(all_id).attr("checked", "checked");
				} else
				{
					jQuery(all_id).removeAttr("checked");
				}
			}
		</script>
		<?php
		$screen = get_current_screen();
		if( ( isset($_REQUEST['post_type']) && $_REQUEST['post_type']=='admanager' ) || "admanager" == $screen->id ){
			wp_enqueue_script('plugin-scripts', AD_MANAGER_PLUGIN_URL.'js/plugin_script.js');
		}
		echo '<style type="text/css">
		.wrap .ad_form input.ad_class,.wrap .ad_form select.ad_class{
		width:300px;
		}
		.wrap .ad_form img.ui-datepicker-trigger{
		vertical-align:bottom;
		}
		.wrap .ad_form td.ad_tbl{
		width:200px;
		vertical-align:text-top;
		}
		.category_display_location{
		max-height: 200px;
		max-width: 300px;
		overflow-x: hidden;
		overflow-y: auto;
		}
		.category_display_location li{
		margin:0 auto;
		}
		table.ad_form .ad_categories option{
		font-size: 13px;
		height: 15px;
		}
		#TB_window #TB_title:last-of-type{
		display:none
		}
		.category_display_location li.saperate{
		border-bottom: 1px solid #DFDFDF;
		float: left;
		width: 98%;
		}
		table.ad_form .ad_categories optgroup {
		font-style:normal;
		}
		</style>';
		/* Admin Custom Post Page Specific CSS */
		global $post_type;
		if($post_type == 'admanager')
		{
			echo '<style type="text/css">#view-post-btn,.ab-top-menu #wp-admin-bar-view,.below-h2 a{display: none;}</style>';
		}
	}
}
/* Fucntion to call any script or style or action on admin head: end */
/* Add metabox for Ad: start */
add_action('save_post','save_ad_settings');
add_action('add_meta_boxes','admanager_meta_box',12);
if(!function_exists('admanager_meta_box'))
{
	function admanager_meta_box()
	{
		remove_meta_box('theme-layouts-post-meta-box', 'admanager','side');
		remove_meta_box('supreme-core-post-seo', 'admanager', 'normal');
		$post_types = get_post_types();
		/* Add Metabox for "Ad" post type */
		add_meta_box('admanager_meta_box_settings', __('Advertisement Settings',PLUGIN_DOMAIN), 'admanager_meta_box_settings', CUSTOM_AD_POST_TYPE, 'normal', 'high');
		add_meta_box('admanager_meta_box_visited', __('Advertisement Visited',PLUGIN_DOMAIN), 'admanager_meta_box_visited', CUSTOM_AD_POST_TYPE, 'side', 'high');
		foreach( $post_types as $post_type )
		{
			if( $post_type != "page" && $post_type != "attachment" && $post_type != "revision" && $post_type != "nav_menu_item" && $post_type != "admanager")
			{
				/* Add Metabox for "post" post type */
				add_meta_box('post_meta_box_settings', __('Advertisement Settings',PLUGIN_DOMAIN), 'post_meta_box_settings', $post_type, 'normal', 'high');
			}
		}
		/* Add Metabox for "page" post type */
		add_meta_box('other_meta_box_settings', __('Ad Settings',PLUGIN_DOMAIN), 'other_meta_box_settings', 'page', 'normal', 'high');
		
	}
}
/*
Name : admanager_meta_box_visited
Description : to show the number of times the advertisement visited.
*/
function admanager_meta_box_visited()
{
	if(@get_post_meta($_GET['post'],'add_visited',true))
	{
		$add_visited = @get_post_meta($_GET['post'],'add_visited',true);
	}
	else{
		$add_visited = 0;
	}
	echo __('Number of ad clicks: ',PLUGIN_DOMAIN).$add_visited;
}
add_action( 'init', 'tmpl_remove_post_type_support', 12); /* to remove the layout boxes from cuxtom post type */
function tmpl_remove_post_type_support()
{
	remove_post_type_support('admanager','theme-layouts');
	add_action( 'add_meta_boxes', 'ad_remove_theme_layout_meta_box',11 );
}
function ad_remove_theme_layout_meta_box()
{
	global $post;
	$post_type = get_post_type();
	if($post_type == CUSTOM_AD_POST_TYPE)
	{
		remove_meta_box('ptthemes-settings',$post_type,'normal');
		remove_meta_box('wpseo_meta',$post_type,'normal');
	}
}
/* Add metabox for Ad: end */
/* Metabox Option fields for Ad: start */
if(!function_exists('admanager_meta_box_settings'))
{
	function admanager_meta_box_settings()
	{
		global $post,$wpdb, $ad_display_location,$ad_display_location_category;
		$ad_height_width = explode("x",get_post_meta( $post->ID,'ad_height_width',true ));
		$ad_width = @$ad_height_width[0];
		$ad_height= @$ad_height_width[1];
		?>
		<div class="row">
			<table class="form-table ad_form">
				<tr>
					<td class="ad_tbl">
						<label for="ad_type">
							<?php _e('Advertisement Type',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<select name="ad_type" id="ad_type" class="">
							<option value="image" <?php selected( get_post_meta( $post->ID,'ad_type',true ), 'image' ); ?>>
								<?php _e('Image Type',PLUGIN_DOMAIN);?>
							</option>
							<option value="html" <?php selected( get_post_meta( $post->ID,'ad_type',true ), 'html' );?>>
								<?php _e('Code Type',PLUGIN_DOMAIN);?>
							</option>
						</select>
						<input type="hidden" name="ad_nonce" id="ad_nonce" value="<?php echo wp_create_nonce(basename(__FILE__));?>"/>
						<br/>
						<p class="description">
							<?php _e("Select 'Image Type' to provide an URL of an image or upload one. Select 'Code Type' to for example display an AdSense type of banner",PLUGIN_DOMAIN); ?>
						</p>
					</td>
				</tr>
				<tr class="ad_type_html" style="<?php
					 if( 'html' == get_post_meta( $post->ID,'ad_type',true ) )
					{ ?> display:table-row;<?php }
					else
					{ ?> display:none;<?php }?>">
					<td class="ad_tbl">
						<label for="ad_html_code">
							<?php _e("Enter HTML code",PLUGIN_DOMAIN); ?>
						</label>
					</td>
					<td>
						<textarea name="ad_html_code" rows="7" cols="50" id="ad_html_code">
							<?php echo get_post_meta( $post->ID,'ad_html_code',true );?>
						</textarea>
						<br/>
						<p class="description">
							<?php _e("Copy and paste your HTML advertisement code here."); ?>
						</p>
					</td>
				</tr>
				<tr class="ad_type_image" style="<?php
					 if( 'image' == get_post_meta( $post->ID,'ad_type',true ) || get_post_meta( $post->ID,'ad_type',true ) == "" )
					{ ?> display:table-row;<?php }
					else
					{ ?> display:none;<?php }?>">
					<td class="ad_tbl">
						<label for="ad_image_title">
							<?php _e("Title",PLUGIN_DOMAIN); ?>
						</label>
					</td>
					<td>
						<input type="text" name="ad_image_title" id="ad_image_title" class="ad_class"  value="<?php echo get_post_meta( $post->ID,'ad_image_title',true );?>" PLACEHOLDER="<?php _e("Advertisement title (optional) ",PLUGIN_DOMAIN);?>" />
						<br/>
						<p class="description">
							<?php _e("Entered titles above will appear on mouseovers on the image in the frontend.",PLUGIN_DOMAIN); ?>
						</p>
					</td>
				</tr>
				<tr class="ad_type_image" style="<?php
					 if( 'image' == get_post_meta( $post->ID,'ad_type',true ) || get_post_meta( $post->ID,'ad_type',true ) == ""  )
					{ ?> display:table-row;<?php }
					else
					{ ?> display:none;<?php }?>">
					<td class="ad_tbl">
						<label for="ad_image_url">
							<?php _e("Advertisement Image",PLUGIN_DOMAIN); ?>
						</label>
					</td>
					<td>
						<input id="ad_image_url" type="text" class="ad_class" name="ad_image_url" value="<?php echo get_post_meta( $post->ID,'ad_image_url',true );?>" PLACEHOLDER="<?php _e("Enter image URL",PLUGIN_DOMAIN);?>" />
						<?php _e('Or',PLUGIN_DOMAIN);?>
						<a data-id="ad_image_url" id="Image URL" type="submit" class="upload_file_button button"><?php  echo __('Upload',PLUGIN_DOMAIN);?></a>   
					</td>
				</tr>
				<tr class="ad_type_image" style="<?php
					 if( 'image' == get_post_meta( $post->ID,'ad_type',true ) || get_post_meta( $post->ID,'ad_type',true ) == ""  )
					{ ?> display:table-row;<?php }
					else
					{ ?> display:none;<?php }?>">
					<td class="ad_tbl">
						<label for="ad_image_link_url">
							<?php _e("Destination Url",PLUGIN_DOMAIN); ?>
						</label>
					</td>
					<td>
						<input type="text" name="ad_image_link_url" id="ad_image_link_url" class="ad_class"  value="<?php echo get_post_meta( $post->ID,'ad_image_link_url',true );?>" PLACEHOLDER="<?php _e("Link of image Advertisement",PLUGIN_DOMAIN);?>" />
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<strong>
							<?php _e("Advertisement Display Settings",PLUGIN_DOMAIN);?>
						</strong>
					</td>
				</tr>
				<tr>
					<td class="ad_tbl">
						<label for="ad_size_width">
							<?php _e('Advertisement Size',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<input type="text" name="ad_size_width" id="ad_size_width" value="<?php echo @$ad_width; ?>"  size="10" PLACEHOLDER="<?php _e("Width",PLUGIN_DOMAIN);?>" /> x <input type="text" name="ad_size_height" id="ad_size_height" value="<?php echo @$ad_height; ?>" size="10" PLACEHOLDER="<?php _e("Height",PLUGIN_DOMAIN);?>" />
						<small>
							<?php _e("(pixels)",PLUGIN_DOMAIN);?>
						</small>
					</td>
				</tr>
				<tr>
					<td class="ad_tbl">
						<label for="ad_categories">
							<?php _e('Select Display Categories',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<ul class="category_display_location wp-tab-panel">
							<?php
							$post_types    = get_post_types();
							$ad_categories = array();
							if( @get_post_meta($post->ID,'ad_categories',true) )
							{
								$ad_categories = json_decode(get_post_meta($post->ID,'ad_categories',true));
							}
							?>
							<li>
								<label>
									<input type="checkbox" name="ad_categories[]" class="selectall" value="not,0" <?php echo (in_array(0,$ad_categories)) ? 'checked' : '';?> />&nbsp;<?php _e("Select All",PLUGIN_DOMAIN);?>
								</label>
							</li>
							<?php
							foreach($post_types as $post_type)
							{
								if($post_type != 'page' && $post_type != "attachment" && $post_type != "revision" && $post_type != "nav_menu_item" && $post_type != "admanager" && $post_type != "product_variation" && $post_type != "shop_order" && $post_type != "shop_coupon")
								{
									$taxonomies = get_object_taxonomies( (object) array('post_type'=> $post_type,'public'   => true,'_builtin' => true ));
									if(is_plugin_active('woocommerce/woocommerce.php') && $post_type == 'product')
									{
										$taxonomies[0] = $taxonomies[1];
									}
									$PostTypeObject   = get_post_type_object($post_type);
									$PostTypeName     = $PostTypeObject->labels->menu_name;
									$post_type_title  = ucfirst($PostTypeName);
									$WPListCategories = get_terms($taxonomies[0]);
									if(!empty($WPListCategories) && empty($WPListCategories->errors))
									{
										echo '<li class="saperate"><label><strong>'.$post_type_title.'</strong></label></li>';
										foreach($WPListCategories as $categories)
										{
											?>
											<li>
												<label>
													<input type="checkbox" name="ad_categories[]" onclick="resetSelectAll('selectall','selectedId');" class="selectedId" value="<?php echo $post_type.','.$categories->term_id; ?>" <?php echo (in_array($categories->term_id,$ad_categories)) ? 'checked' : '';?> />&nbsp;<?php echo $categories->name;?>
												</label>
											</li>
											<?php
										}
									}
								}
							}
							?>
						</ul>
						<p class="description">
							<?php _e("Select the post or custom post categories to display this advertisement banner.",PLUGIN_DOMAIN); ?>
						</p>
					</td>
				</tr>
				<tr>
					<td class="ad_tbl">
						<label for="category_ad_location">
							<?php _e('Advertisement Display Location',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<ul class="category_display_location wp-tab-panel">
							<?php
							if(!empty($ad_display_location_category))
							{
								$category_ad_location = array();
								if( @get_post_meta($post->ID,'category_ad_location',true))
								{
									$category_ad_location = json_decode(get_post_meta($post->ID,'category_ad_location',true));
								}
								?>
								<li>
									<label>
										<input type="checkbox" name="category_ad_location[]" class="locationselectall" value="not,all" <?php echo (in_array('all',$category_ad_location)) ? 'checked' : '';?> />&nbsp;<?php _e("Select All",PLUGIN_DOMAIN);?>
									</label>
								</li>
								<?php
								foreach($ad_display_location_category as $posttype => $actions)
								{
									if( "blog_category" == $posttype )
									{
										echo '<li class="saperate"><label><strong>'.__("Post").'</strong></label></li>';
										foreach($actions as $key => $value)
										{
											?>
											<li>
												<label>
													<input type="checkbox" name="category_ad_location[]" class="locationselectedId"  onclick="resetSelectAll('locationselectall','locationselectedId');"  value="<?php echo 'post,'.$key;?>" <?php echo (in_array($key,$category_ad_location) ) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}
									else
									{
										$object = get_post_type_object($posttype);
										$name   = $object->labels->menu_name;
										$title  = ucfirst($name);
										if(!empty($actions))
										{
											echo '<li class="saperate"><label><strong>'.$title.'</strong></label></li>';
											foreach($actions as $key => $value)
											{
												?>
												<li>
													<label><input type="checkbox" name="category_ad_location[]" onclick="resetSelectAll('locationselectall','locationselectedId');" class="locationselectedId" value="<?php echo $posttype.','.$key;?>" <?php echo (in_array($key,$category_ad_location)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?><label>
												</li>
												<?php
											}
										}
									}
									?>
									<?php
								}
							}else{
								echo '<li style="line-height:40px">'.__("Display location not available",PLUGIN_DOMAIN).'</li>';
							}
							?>
						</ul>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}
}
/* Metabox Option fields for Ad: end */
/* Metabox Option fields for Ad: start */
if(!function_exists('other_meta_box_settings'))
{
	function other_meta_box_settings()
	{
		global $post,$wpdb, $ad_display_location,$page_post;
		$page_post        = $post;
		$postid           = $post->ID;
		$current_type     = $post->post_type;
		$selected_post_ad = json_decode(get_post_meta($post->ID,'selected_post_ad',true));
		$selected_post_ad_location = json_decode(get_post_meta($post->ID,'selected_post_ad_location',true));
		$args = array(
			'post_type'     => CUSTOM_AD_POST_TYPE,
			'posts_per_page'=> - 1,
			'post_status'   => 'publish'
		);
		$ad_query = new WP_Query($args);
		?>
		<div class="row">
			<table class="form-table ad_form">
				<input type="hidden" name="ad_nonce" value="<?php echo wp_create_nonce(basename(__FILE__));?>"/>
				<tr>
					<td class="ad_tbl">
						<label for="selected_post_ad">
							<?php _e('Advertisements',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<ul class="category_display_location wp-tab-panel">
							<?php
							if($ad_query->have_posts()){
								while($ad_query->have_posts()) : $ad_query->the_post();global $post;
								?>
									<li>
										<label>
											<input type="checkbox" name="selected_post_ad[]" class="selected_post_ad_ID" value="<?php echo $post->ID;?>" <?php echo (is_array($selected_post_ad) && in_array($post->ID,$selected_post_ad)) ? 'checked' : '';?> />&nbsp;<?php echo $post->post_title;?> &nbsp; <?php echo (get_post_meta($post->ID,'ad_height_width',true)) ? '( '.get_post_meta($post->ID,'ad_height_width',true) .' )' : "";?>
										</label>
									</li>
							<?php	
								endwhile;
								wp_reset_query();
								wp_reset_postdata();
							}else{
							?>
								<li style="line-height:40px">
									<label>
										<?php _e("No advertisement available",PLUGIN_DOMAIN);?>
									</label>
								</li>
							<?php
							}
							?>
						</ul>
						<p class="description">
							<?php _e("Select which Ad you want to display on this post.",PLUGIN_DOMAIN); ?>
						</p>
					</td>
				</tr>
				<tr>
					<td class="ad_tbl">
						<label for="selected_post_ad_location">
							<?php _e('Display Location',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<ul class="category_display_location wp-tab-panel">
						<?php if(!empty($ad_display_location)){ 
								foreach($ad_display_location as $posttype => $actions){
									if( "default" == $posttype ){
										echo '<li class="saperate"><label><strong>'.__("Default Locations").'</strong></label></li>';
										foreach($actions as $key => $value){
											?>
											<li>
												<label>
													<input type="checkbox" name="selected_post_ad_location[]" class="selected_post_location_Id" value="<?php echo $key;?>" <?php echo (in_array($key,$selected_post_ad_location)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}elseif( "posts_pages" == $posttype && "page" == $current_type ){
										echo '<li class="saperate"><label><strong>'.__("Page Locations").'</strong></label></li>';
										foreach($actions as $key => $value){
											?>
											<li>
												<label>
													<input type="checkbox" name="selected_post_ad_location[]" class="selected_post_location_Id" value="<?php echo $key;?>" <?php echo (is_array($selected_post_ad) && in_array($key,$selected_post_ad)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}elseif( $posttype == $current_type ){
										$object = get_post_type_object($posttype);
										$name   = $object->labels->name;
										$title  = ucfirst($name).' '.__("Locations",PLUGIN_DOMAIN);
										echo '<li class="saperate"><label><strong>'.$title.'</strong></label></li>';
										foreach($actions as $key => $value){
											?>
											<li>
												<label>
													<input type="checkbox" name="selected_post_ad_location[]" class="selected_post_location_Id" value="<?php echo $key;?>" <?php echo (in_array($key,$selected_post_ad_location)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}
								}
							}
						?>
						</ul>
						<p class="description">
							<?php _e("Select location where you want to display Ad.",PLUGIN_DOMAIN); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		<?php
		$post = $page_post;
	}
}
/* Metabox Option fields for Ad: end */
/* Metabox Option fields for Ad: start */
if(!function_exists('post_meta_box_settings')){
	function post_meta_box_settings(){
		wp_reset_query();
		wp_reset_postdata();
		global $post,$wpdb, $ad_display_location,$add_post;
		$add_post     = $post;
		$postid       = $post->ID;
		$current_type = $post->post_type;
		$args         = array(
			'post_type'     => CUSTOM_AD_POST_TYPE,
			'posts_per_page'=> - 1,
			'post_status'   => 'publish'
		);
		$ad_query = new WP_Query($args);
		global $post;
		$post_type = $post->post_type;
		if( $post_type == "" ){
			$post_type = ($_REQUEST['post_type']) ? @$_REQUEST['post_type'] : 'post';
		}
		if( $post_type == "post" ){
			$taxonomy = "category";
		}else{
			$taxonomies = get_object_taxonomies( (object) array('post_type'=> $post_type,'public'   => true,'_builtin' => true ));
			if( is_plugin_active('woocommerce/woocommerce.php') && "product" == $post_type )
			{
				$taxonomies[0] = $taxonomies[1];
			}
			$taxonomy = $taxonomies[0];
		}
		if( "post" == $post->post_type ){
			$categories = get_the_category();
		}else{
			$categories = get_the_terms( $post->ID, $taxonomy );
		}
		$selected_post_ad = json_decode(get_post_meta($post->ID,'selected_post_ad',true));
		$selected_post_ad_location = json_decode(get_post_meta($post->ID,'selected_post_ad_location',true));
		?>
		<div class="row">
			<table class="form-table ad_form">
				<input type="hidden" name="ad_nonce" value="<?php echo wp_create_nonce(basename(__FILE__));?>"/>
				<?php
				if( @$_REQUEST['action'] == "edit" ){
					?>
					<tr class="default_ad">
						<td class="ad_tbl">
							<label for="selected_post_cat_ad">
								<?php _e('Select Category Ad',PLUGIN_DOMAIN);?>
							</label>
						</td>
						<td>
							<select name="selected_post_cat_ad" id="selected_post_cat_ad" class="ad_class">
								<option value="0">
									<?php _e('Please Select',PLUGIN_DOMAIN);?>
								</option>
								<?php
								foreach( $categories as $categories )
								{
									?>
									<option value="<?php echo $categories->term_id;?>" <?php selected( get_post_meta( $postid,'selected_post_cat_ad',true ), $categories->term_id ); ?>>
										<?php echo $categories->name;?>
									</option>
									<?php
								}
								?>
							</select>
							<br/>
							<p class="description">
								<?php _e("Select which Category's Ad you want to display on this post."); ?>
							</p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<b>
								<?php _e("OR",PLUGIN_DOMAIN);?>
							</b>
						</td>
					</tr>
					<?php
				} ?>
				<tr class="custom_ad">
					<td class="ad_tbl">
						<label for="selected_post_ad">
							<?php _e('Advertisements',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<ul class="category_display_location wp-tab-panel">
							<?php
							if($ad_query->have_posts()){
								while($ad_query->have_posts()) : $ad_query->the_post();global $post;
								?>
									<li>
										<label>
										<input type="checkbox" name="selected_post_ad[]" class="selected_post_ad_ID" value="<?php echo $post->ID;?>" <?php echo (isset($post->ID) && !empty($selected_post_ad) && in_array($post->ID,$selected_post_ad)) ? 'checked' : '';?> />&nbsp;<?php echo $post->post_title;?> &nbsp; <?php echo (get_post_meta($post->ID,'ad_height_width',true)) ? '( '.get_post_meta($post->ID,'ad_height_width',true) .' )' : "";?>
										</label>
									</li>
							<?php	
								endwhile;
								wp_reset_query();
								wp_reset_postdata();
							}else{
							?>
								<li style="line-height:40px">
									<label>
										<?php _e("No advertisement available",PLUGIN_DOMAIN);?>
									</label>
								</li>
							<?php
							}
							?>
						</ul>
						<p class="description">
							<?php _e("Select which Ad you want to display on this post."); ?>
						</p>
					</td>
				</tr>
				<tr class="custom_ad">
					<td class="ad_tbl">
						<label for="selected_post_ad_location">
							<?php _e('Display Location',PLUGIN_DOMAIN);?>
						</label>
					</td>
					<td>
						<ul class="category_display_location wp-tab-panel">
						<?php if(!empty($ad_display_location)){ 
								foreach($ad_display_location as $posttype => $actions){
									if( "default" == $posttype ){
										echo '<li class="saperate"><label><strong>'.__("Default Locations").'</strong></label></li>';
										foreach($actions as $key => $value){
											?>
											<li>
												<label>
													<input type="checkbox" name="selected_post_ad_location[]" class="selected_post_location_Id" value="<?php echo $key;?>" <?php echo (in_array($key,$selected_post_ad_location)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}elseif( "posts_pages" == $posttype && "post" == $current_type ){
										echo '<li class="saperate"><label><strong>'.__("Post Locations").'</strong></label></li>';
										foreach($actions as $key => $value){
											?>
											<li>
												<label>
													<input type="checkbox" name="selected_post_ad_location[]" class="selected_post_location_Id" value="<?php echo $key;?>" <?php echo (isset($key) && !empty($selected_post_ad_location) && in_array($key,$selected_post_ad_location)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}elseif( $posttype == $current_type ){
										$object = get_post_type_object($posttype);
										$name   = $object->labels->name;
										$title  = ucfirst($name).' '.__("Locations",PLUGIN_DOMAIN);
										echo '<li class="saperate"><label><strong>'.$title.'</strong></label></li>';
										foreach($actions as $key => $value){
											?>
											<li>
												<label>
													<input type="checkbox" name="selected_post_ad_location[]" class="selected_post_location_Id" value="<?php echo $key;?>" <?php echo (isset($key) && !empty($selected_post_ad_location) && in_array($key,$selected_post_ad_location)) ? 'checked' : '';?>/>&nbsp;<?php _e($value,PLUGIN_DOMAIN); ?>
												</label>
											</li>
											<?php
										}
									}
								}
							}
						?>
						</ul>
						<p class="description">
							<?php _e("Select location where you want to display your Ad."); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		<?php
		$post = $add_post;
	}
}
/* Metabox Option fields for Ad: end */
/* Save Ad settings and meta: start */
if(!function_exists('save_ad_settings'))
{
	function save_ad_settings($post_id)
	{
		global $post, $wpdb;
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
		/* because save_post can be triggered at other times */
		if( !wp_verify_nonce( @$_POST['ad_nonce'], basename(__FILE__) ) )
		return $post_id;
		/* Check permissions */
		if( 'page' == @$_POST['post_type'] )
		{
			if( !current_user_can( 'edit_page', $post_id ) )
			return;
		}
		else
		{
			if( !current_user_can( 'edit_post', $post_id ) )
			return;
		}
		if( CUSTOM_AD_POST_TYPE == $_REQUEST['post_type'] )
		{
			/* Insert advertisement id in term table: start */
			global $wpdb;
			$term_table = $wpdb->prefix."terms";
			if( !empty($_REQUEST['ad_categories']) ){
				/* remove advertisement ids from term table if selected categories is less then saved category : start */
				$get_cat_ids = json_decode(get_post_meta($_REQUEST['post_ID'],'ad_categories',true));
				$AdCategoryIds = array();
				foreach( $_REQUEST['ad_categories'] as $AdCategories ){
					$ExplodedCatIds = explode(",",$AdCategories);
					$AdCategoryIds[] = $ExplodedCatIds[1];
				}
				if( count($get_cat_ids) > count($AdCategoryIds) ){
					$different = array_diff($get_cat_ids,$AdCategoryIds);
					if( count($different) > 0 ){
						foreach($different as $different){
							$get_adids = $wpdb->get_var("select term_ad_ids from $term_table where term_id=".$different);
							$exp_ad_ids= explode(",",$get_adids);
							if( count( $exp_ad_ids > 0 ) ){
								$deleted_ad_ids = '';
								$trimed_ad_ids  = '';
								foreach( $exp_ad_ids as $exp_ad_ids ){
									if( $exp_ad_ids != $_REQUEST['post_ID'] ){
										$deleted_ad_ids .= $exp_ad_ids.',';
									}
								}
								$trimed_ad_ids = rtrim($deleted_ad_ids,",");
								$sql3 = $wpdb->query("update $term_table set term_ad_ids='".$trimed_ad_ids."' where term_id=".$different);
							}
						}
					}/* remove advertisement ids from term table if selected categories is less then saved category : end */
				}else{
					$get_ids     = '';
					$term_ad_ids = '';
					for($term = 0;$term < count($AdCategoryIds);$term++){
						$get_ids = $wpdb->get_var("select term_ad_ids from $term_table where term_id=".$AdCategoryIds[$term]);
						if( $get_ids != '' ){
							if($get_ids == ""){
								$term_ad_ids = $_REQUEST['post_ID'];
							}elseif(strpos($get_ids, $_REQUEST['post_ID'])){
								$term_ad_ids = $get_ids;
							}elseif($get_ids == $_REQUEST['post_ID']){
								$term_ad_ids = $get_ids;
							}else{
								$term_ad_ids = $get_ids.','.$_REQUEST['post_ID'];
							}
						}else{
							$term_ad_ids = $_REQUEST['post_ID'];
						}
						$sql3 = $wpdb->query("update $term_table set term_ad_ids='".$term_ad_ids."' where term_id=".$AdCategoryIds[$term]);
					}
				}/* Insert advertisement ids from term table : end */
			}else{
				/* Check if advertisement is edit then delete advertisement id from terms id: start */
				if( strtolower( $_REQUEST['save'] ) == strtolower( 'Update' ) ){
					$get_all_terms = $wpdb->get_col("select term_id from $term_table WHERE term_ad_ids!=''");
					if( count( $get_all_terms ) > 0 ){
						foreach( $get_all_terms as $get_all_terms ){
							$get_ids = $wpdb->get_var("select term_ad_ids from $term_table where term_id=".$get_all_terms);
							$exp_ad_ids= explode(",",$get_ids);
							if( count( $exp_ad_ids > 0 ) ){
								$deleted_ad_ids = '';
								$trimed_ad_ids  = '';
								foreach( $exp_ad_ids as $exp_ad_ids ){
									if( $exp_ad_ids != $_REQUEST['post_ID'] ){
										$deleted_ad_ids .= $exp_ad_ids.',';
									}
								}
								$trimed_ad_ids = rtrim($deleted_ad_ids,",");
								$sql3 = $wpdb->query("update $term_table set term_ad_ids='".$trimed_ad_ids."' where term_id=".$get_all_terms);
							}
						}
					}
				}
			}
			/* Prepare category wise location array: start */
			if(!empty($_REQUEST['ad_categories'])){
				$adv_id_location = array();
				foreach( $_REQUEST['ad_categories'] as $ad_categories ){
					if(!empty($_REQUEST['category_ad_location'])){
						$a = array();
						foreach( $_REQUEST['category_ad_location'] as $category_ad_location ){
							$expl_cat_post_type = explode(",",$ad_categories);
							$expl_loc_post_type = explode(",",$category_ad_location);
							if( $expl_cat_post_type[0] == $expl_loc_post_type[0] ){
								$a[$expl_cat_post_type[1]][] = $expl_loc_post_type[1];
							}
						}
						$adv_id_location[] = $a;
					}	
				}
			}
			/* Prepare category wise location array: end */			
			/* Extract advertisement category value and prepare it for save: start */
			$AdCategoryIds = array();
			if( !empty( $_REQUEST['ad_categories'] ) ){
				foreach( $_REQUEST['ad_categories'] as $AdCategories ){
					$ExplodedCatIds = explode(",",$AdCategories);
					$AdCategoryIds[] = $ExplodedCatIds[1];
				}
			}
			/* Extract category value and prepare it for save: end */
			
			/* Extract advertisement location value and prepare it for save: start */
			$AdCategoryLocations = array();
			if( !empty( $_REQUEST['category_ad_location'] ) ){
				foreach( $_REQUEST['category_ad_location'] as $AdCatLocations ){
					$ExplodedCatLocations = explode(",",$AdCatLocations);
					$AdCategoryLocations[] = $ExplodedCatLocations[1];
				}
			}
			/* Extract advertisement location value and prepare it for save: end */
			
			/* Set assign post value to variable: start */
			$ad_type              = @$_REQUEST['ad_type'];
			$ad_html_code         = @$_REQUEST['ad_html_code'];
			$ad_image_title       = @$_REQUEST['ad_image_title'];
			$ad_image_url         = @$_REQUEST['ad_image_url'];
			$ad_image_link_url    = @$_REQUEST['ad_image_link_url'];
			$ad_height_width      = @$_REQUEST['ad_size_width'] .'x'. @$_REQUEST['ad_size_height'];
			$ad_categories        = ($AdCategoryIds) ? json_encode($AdCategoryIds) : '';
			$category_ad_location = ($AdCategoryLocations) ? json_encode($AdCategoryLocations) : '';
			$ad_height_width      = ( $ad_height_width == "x" || $ad_height_width == "" ) ? "" : $ad_height_width;
			$adv_id_location        = ($adv_id_location) ? $adv_id_location : '';
			/* Set assign post value to variable: end */
			
			/* Prepare advertisement data in array to save them:  start */
			$ad_datas             = array(
				"ad_type"              =>	@$_REQUEST['ad_type'],
				"ad_html_code"         =>	@$ad_html_code,
				"ad_image_title"       =>	@$ad_image_title,
				"ad_image_url"         =>	@$ad_image_url,
				"ad_image_link_url"    =>	@$ad_image_link_url,
				"is_ad_sticky"         =>	@$_REQUEST['is_ad_sticky'],
				"ad_height_width"      =>	@$ad_height_width,
				"ad_categories"        =>	@$ad_categories,
				"category_ad_location" =>	@$category_ad_location,
				"adv_id_location"	   =>	@$adv_id_location,
			);
			/* Prepare advertisement data in array to save them:  start */
			
			/* Save advertisement data in database: start */
			if( count($ad_datas) > 0 ){
				foreach( $ad_datas as $data_key => $data_value ){
					update_post_meta( $_REQUEST['post_ID'], $data_key ,$data_value );
				}
			}
			/* Save advertisement data in database: end */
		}else{
			$selected_post_cat_ad      = ( $_REQUEST['selected_post_cat_ad'] ) ? $_REQUEST['selected_post_cat_ad']  : '';
			$selected_post_ad          = ( $_REQUEST['selected_post_ad'] ) ? json_encode(  $_REQUEST['selected_post_ad'] ) : '';
			$selected_post_ad_location = ( $_REQUEST['selected_post_ad_location'] ) ? json_encode(  $_REQUEST['selected_post_ad_location'] ) : '';
			$postdatas                 = array(
				"selected_post_cat_ad"     =>	$selected_post_cat_ad,
				"selected_post_ad"         =>	$selected_post_ad,
				"selected_post_ad_location"=>	$selected_post_ad_location,
			);
			if( count($postdatas) > 0 )
			{
				foreach( $postdatas as $postdatas_key => $postdatas_value )
				{
					update_post_meta( $_REQUEST['post_ID'], $postdatas_key ,$postdatas_value );
				}
			}
		}
	}
}
/* Save Ad settings and meta: end */
/**
* @Function: pre_display_ad
* @Filter: wp_head
* @Return: Prepare advertisement to display on selected location
*		   and call on that location action	
*/

add_action('wp_head','pre_display_ad');
if(!function_exists('pre_display_ad')){
	function pre_display_ad(){
		global $post, $ad_display_location, $wp_query, $ad_display_location_category,$wpdb;
		if( is_page() || is_single() || is_singular() ){
			/* Get all categories of current psot */
			$post_type = $post->post_type;
			if( $post_type == "" ){
				$post_type = ($_REQUEST['post_type']) ? @$_REQUEST['post_type'] : 'post';
			}
			if( $post_type == "post" ){
				$taxonomy = "category";
			}else{
				$taxonomies = get_object_taxonomies( (object) array('post_type'=> $post_type,'public'   => true,'_builtin' => true ));
				if( is_plugin_active('woocommerce/woocommerce.php') && "product" == $post_type )
				{
					$taxonomies[0] = $taxonomies[1];
				}
				if(!empty($taxonomies)){
					$taxonomy = $taxonomies[0];
				}
			}
			if(isset($taxonomy)){
				$categories = get_the_terms( $post->ID, $taxonomy );
			}
			/* Get all categories of current post */
			if(!empty($categories)){
				$c = 1;
				foreach($categories as $categories1){
					if($c == 1){
						$termid = $categories1->term_id;
					}
					$c++;
				}
			}
			/* Check for advertisement ids and its locations and call action on particular selected theme locations */
			if( count( @$categories ) > 0 ){
				$ad_ids = json_decode(get_post_meta( $post->ID, 'selected_post_ad', true ));
				if( !empty($ad_ids) ){
					foreach( $ad_ids as $ad_ids){
						if( "publish" == get_post_status( $ad_ids ) ){
							$current_display_location = json_decode(get_post_meta( $post->ID, 'selected_post_ad_location', true ));
							if( !empty($current_display_location) ){
								foreach($ad_display_location as $value => $title){
									if( ( $value == "posts_pages" || "default" == $value ) && ( "post" == get_post_type() || "page" == get_post_type() )){
										for($l = 0;$l < count($current_display_location);$l++){
											if(array_key_exists($current_display_location[$l], $title)){
												if( 'theme_before_post' == $current_display_location[$l] || 'theme_after_post' == $current_display_location[$l]  ){
													add_action('the_content','display_add');
												}else{
													add_action($current_display_location[$l],'display_add');
												}
											}
										}
									}elseif($value == get_post_type()){
										unset($current_display_location['null']);
										for($l = 0;$l < count($current_display_location);$l++){
											if(array_key_exists($current_display_location[$l], $title)){
												if( 'theme_before_post' == $current_display_location[$l] || 'theme_after_post' == $current_display_location[$l]  ){
													add_action('the_content','display_add');
												}else{
													add_action($current_display_location[$l],'display_add');
												}
											}
										}
									}
								}
							}
						}
					}
				}else{
					if( get_post_meta( $post->ID, 'selected_post_cat_ad', true ) != 0 ){
						$term_id = get_post_meta( $post->ID, 'selected_post_cat_ad', true );
						$term_tbl = $wpdb->prefix."terms";
						if($term_id){
							$term_ad_ids = $wpdb->get_var("select term_ad_ids from $term_tbl where term_id=$term_id");
						}
						global $post,$wp_query;
						$category_id     = $termid;
						$category_ad_ids = $term_ad_ids;
						if( @$category_ad_ids != "" || @$category_ad_ids != "," ){
							foreach($ad_display_location_category as $value => $title){
								if($value == 'blog_category' && "post" == get_post_type()){
									$ad_ids = explode(",",$category_ad_ids);
									foreach( $ad_ids as $ad_ids ){
										if( "publish" == get_post_status( $ad_ids ) ){
											$adv_locations = get_post_meta( $ad_ids, 'adv_id_location', true );
											foreach( $adv_locations as $adv_locations ){
												foreach( $adv_locations as $loc_key => $loc_value ){
													if( $loc_key == $category_id ){
														foreach( $loc_value as $loc_value ){
															if(array_key_exists($loc_value, $title)){
																add_action($loc_value,'display_add');
															}
														}
													}
												}
											}
										}
									}
								}elseif($value == get_post_type()){
									$ad_ids = explode(",",$category_ad_ids);
									foreach( $ad_ids as $ad_ids ){
										if( "publish" == get_post_status( $ad_ids ) ){
											$adv_locations = get_post_meta( $ad_ids, 'adv_id_location', true );
											if(!empty($adv_locations)){
											foreach( $adv_locations as $adv_locations ){
												foreach( $adv_locations as $loc_key => $loc_value ){
													if( $loc_key == $category_id ){
														foreach( $loc_value as $loc_value ){
															if(array_key_exists($loc_value, $title)){
																add_action($loc_value,'display_add');
															}
														}
													}
												}
											}
											}
										}
									}
								}
							}
						}
					}else{
						$term_tbl = $wpdb->prefix."terms";
						if($termid){
							$term_ad_ids = $wpdb->get_var("select term_ad_ids from $term_tbl where term_id=".$termid);
						}
						global $post,$wp_query;
						$category_id     = $termid;
						$category_ad_ids = $term_ad_ids;
						if( @$category_ad_ids != "" || @$category_ad_ids != "," ){
							foreach($ad_display_location_category as $value => $title){
								if($value == 'blog_category' && "post" == get_post_type()){
									$ad_ids = explode(",",$category_ad_ids);
									foreach( $ad_ids as $ad_ids ){
										if( "publish" == get_post_status( $ad_ids ) ){
											$adv_locations = get_post_meta( $ad_ids, 'adv_id_location', true );
											if(!empty($adv_locations)){
											foreach( $adv_locations as $adv_locations ){
												foreach( $adv_locations as $loc_key => $loc_value ){
													if( $loc_key == $category_id ){
														foreach( $loc_value as $loc_value ){
															if(array_key_exists($loc_value, $title)){
																add_action($loc_value,'display_add');
															}
														}
													}
												}
											}
											}
										}
									}
								}elseif($value == get_post_type()){
									$ad_ids = explode(",",$category_ad_ids);
									foreach( $ad_ids as $ad_ids ){
										if( "publish" == get_post_status( $ad_ids ) ){
											$adv_locations = get_post_meta( $ad_ids, 'adv_id_location', true );
											if(!empty($adv_locations))
											{
												foreach( $adv_locations as $adv_locations ){
													foreach( $adv_locations as $loc_key => $loc_value ){
														if( $loc_key == $category_id ){
															foreach( $loc_value as $loc_value ){
																if(array_key_exists($loc_value, $title)){
																	add_action($loc_value,'display_add');
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}else{
				$ad_ids = json_decode(get_post_meta( $post->ID, 'selected_post_ad', true ));
				if( !empty($ad_ids) ){
					foreach( $ad_ids as $ad_ids){
						if( "publish" == get_post_status( $ad_ids ) ){
							$current_display_location = json_decode(get_post_meta( $post->ID, 'selected_post_ad_location', true ));
							if( !empty($current_display_location) ){
								foreach($ad_display_location as $value => $title){
									if( ( $value == "posts_pages" || "default" == $value ) && ( "post" == get_post_type() || "page" == get_post_type() )){
										for($l = 0;$l < count($current_display_location);$l++){
											if(array_key_exists($current_display_location[$l], $title)){
												if( 'theme_before_post' == $current_display_location[$l] || 'theme_after_post' == $current_display_location[$l]  ){
													add_action('the_content','display_add');
												}else{
													add_action($current_display_location[$l],'display_add');
												}
											}
										}
									}elseif($value == get_post_type()){
										unset($current_display_location['null']);
										for($l = 0;$l < count($current_display_location);$l++){
											if(array_key_exists($current_display_location[$l], $title)){
												if( 'theme_before_post' == $current_display_location[$l] || 'theme_after_post' == $current_display_location[$l]  ){
													add_action('the_content','display_add');
												}else{
													add_action($current_display_location[$l],'display_add');
												}
											}
										}
									}
								}
							}
						}
					}	
				}
			}
		}elseif( is_category() || is_tax() ){
			$current_term = $wp_query->get_queried_object();
			global $post,$wp_query;
			$category_id     = $current_term->term_id;
			$category_ad_ids = $current_term->term_ad_ids;
			if( @$category_ad_ids != "" || @$category_ad_ids != "," ){
				foreach($ad_display_location_category as $value => $title){					
					if($value == 'blog_category' && "post" == get_post_type()){
						$ad_ids = explode(",",$category_ad_ids);
						foreach( $ad_ids as $ad_ids ){
							if( "publish" == get_post_status( $ad_ids ) ){
								$adv_locations = get_post_meta( $ad_ids, 'adv_id_location', true );
								if(!empty($adv_locations)){
								foreach( $adv_locations as $adv_locations ){
									foreach( $adv_locations as $loc_key => $loc_value ){
										if( $loc_key == $category_id ){
											foreach( $loc_value as $loc_value ){
												if(array_key_exists($loc_value, $title)){												
													add_action($loc_value,'display_add');
												}
											}
										}
									}
								}
								}
							}
						}
					}elseif($value == get_post_type()){
						$ad_ids = explode(",",$category_ad_ids);
						foreach( $ad_ids as $ad_ids ){
							
							$location_post_type = '';
							$location_post_type = ','.implode(',',get_option('location_post_type'));							
							if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && strpos($location_post_type,',admanager') !== false)
							{
								global $current_cityinfo;
								$post_city_id=explode(',',get_post_meta( $ad_ids, 'post_city_id', true ));
								if(!in_array($current_cityinfo['city_id'],$post_city_id) )
								{
									continue;
								}
							}
							
							if( "publish" == get_post_status( $ad_ids ) ){
								$adv_locations = get_post_meta( $ad_ids, 'adv_id_location', true );
					
								if(!empty($adv_locations)){
								foreach( $adv_locations as $adv_locations ){
									foreach( $adv_locations as $loc_key => $loc_value ){
										if( $loc_key == $category_id ){
											foreach( $loc_value as $loc_value ){
												if(array_key_exists($loc_value, $title)){							
													add_action($loc_value,'display_add');
												}
											}
										}
									}
								}
								}
							}
						}
					}
				}
			}
		}
	}
}
/**
* @Function: display_add
* @Filter: Selected advertisement theme locations
* @Return: Display advertisement on defined theme location
*/
if(!function_exists('display_add')){
	function display_add(){
		global $post,$wp_query,$wpdb;
		$current_post_id      = $post->ID;
		$current_post_content = $post->post_content;
		/* Get location of current post id */
		$current_ad_location  = json_decode(get_post_meta( $current_post_id, 'selected_post_ad_location', true ));
		if( is_category() || is_tax() ){ /* Check if archive page */
			$current_term    = $wp_query->get_queried_object();
			$category_id     = $current_term->term_id;
			$category_ad_ids = explode(",",$current_term->term_ad_ids);
			$add_array = array();
			foreach( $category_ad_ids as $category_ad_id){ 
				if( "publish" == get_post_status( $category_ad_id ) ){
					$add_array[] = $category_ad_id;
				}
			}
			$rand_ad_id = array_rand($add_array,1);
			$post_ad_ids= $add_array; /* Get random advertisement id */			
		}elseif( is_page() || is_single() || is_singular() ){ /* Check if detail page */
			$post_type = $post->post_type;
			if( $post_type == "" ){
				$post_type = ($_REQUEST['post_type']) ? @$_REQUEST['post_type'] : 'post';
			}
			/* Get all categories of current post */
			if( $post_type == "post" ){
				$taxonomy = "category";
			}else{
				$taxonomies = get_object_taxonomies( (object) array('post_type'=> $post_type,'public'   => true,'_builtin' => true ));
				if( is_plugin_active('woocommerce/woocommerce.php') && "product" == $post_type )
				{
					$taxonomies[0] = $taxonomies[1];
				}
				$taxonomy = $taxonomies[0];
			}
			$categories = get_the_terms( $post->ID, $taxonomy );
			if(!empty($categories)){
				$c = 1;
				foreach($categories as $categories2){
					if($c == 1){
						$termid = $categories2->term_id;
					}
					$c++;
				}
			}
			if( count($categories) > 0 ){ /* Check if more then one categories  */
				$ad_ids = json_decode(get_post_meta( $post->ID, 'selected_post_ad', true ));
				if( !empty($ad_ids) ){
					$post_ad_ids = json_decode(get_post_meta( $post->ID, 'selected_post_ad', true ));
					foreach( $post_ad_ids as $cm_ad_id){
						if( "publish" == get_post_status( $cm_ad_id ) ){
							$rand_ad_id = array_rand($post_ad_ids,1);
							$post_ad_ids= $post_ad_ids;
							break;
						}
					}
				}else{
					if( get_post_meta( $post->ID, 'selected_post_cat_ad', true ) != 0 ){
						$term_id = get_post_meta( $post->ID, 'selected_post_cat_ad', true );
						$term_tbl = $wpdb->prefix."terms";
						if($term_id){
							$term_ad_ids = $wpdb->get_var("select term_ad_ids from $term_tbl where term_id=$term_id");
						}
						$ad_id = explode(",",$term_ad_ids);
						$add_array = array();
						foreach( $ad_id as $c_ad_id){ 
							if( "publish" == get_post_status( $c_ad_id ) ){
								$add_array[] = $c_ad_id;
							}
						}
						if( !empty($add_array) ){
							$rand_ad_id = array_rand($add_array,1);
							$post_ad_ids= $add_array;
						}
					}else{
						$term_tbl = $wpdb->prefix."terms";
						if($termid){
							$term_ad_ids = $wpdb->get_var("select term_ad_ids from $term_tbl where term_id=".$termid);
						}
						$ad_id = explode(",",$term_ad_ids);
						$add_array = array();
						foreach( $ad_id as $c_ad_id){ 
							if( "publish" == get_post_status( $c_ad_id ) ){
								$add_array[] = $c_ad_id;
							}
						}
						if( !empty($add_array) ){
							$rand_ad_id = array_rand($add_array,1);
							$post_ad_ids= $add_array;
						}
					}
				}
			}else{
				$ad_ids = json_decode(get_post_meta( $post->ID, 'selected_post_ad', true ));
				if( !empty($ad_ids) ){
					$post_ad_ids = json_decode(get_post_meta( $post->ID, 'selected_post_ad', true ));
					foreach( $post_ad_ids as $cm_ad_id){
						if( "publish" == get_post_status( $cm_ad_id ) ){
							$rand_ad_id = array_rand($post_ad_ids,1);
							$post_ad_ids= $post_ad_ids;
						}
					}
				}
			}
		}
		$location_post_type = '';
		$location_post_type = ",".implode(',',get_option('location_post_type'));
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && strpos($location_post_type,',admanager') !== false)
		{
			$args = array('post__in'=>$post_ad_ids,'post_type'=>'admanager','orderby'=>'rand','suppress_filters' => false);
		}
		else
		{
			$args = array('post__in'=>$post_ad_ids,'post_type'=>'admanager','orderby'=>'rand','suppress_filters' => true);
			
		}
		add_filter('posts_where', 'location_multicity_where');
		$post_data    = get_posts($args);
		remove_filter('posts_where', 'location_multicity_where');
		$add_post_id = '';
		foreach($post_data as $add_data)
		{
			if(get_post_meta($add_data->ID, 'ad_type', true) != '')
			{
				$add_post_id = $add_data->ID;
				break;
			}
		}
		$post_ad_type = get_post_meta($add_post_id, 'ad_type', true);
		$post_ad_html = get_post_meta($add_post_id, 'ad_html_code', true);
		$ad_image_url = get_post_meta($add_post_id, 'ad_image_url', true);
		$ad_image_title = get_post_meta($add_post_id, 'ad_image_title', true);
		$ad_image_link_url = get_post_meta($add_post_id, 'ad_image_link_url', true);
		$is_sticky_ad = (get_post_meta($add_post_id, 'is_ad_sticky', true)) ? "plugin_ad_sticky" : "";
		$ad_height_width = explode("x",get_post_meta($add_post_id, 'ad_height_width', true));
		$width = @$ad_height_width[0];
		$height= @$ad_height_width[1];
		$data  = '';
		if($ad_image_link_url == '')
		{
			$ad_image_link_url = 'javascript:void(0);';
		}
		else
		{
			$target = 'target="_blank"';
		}
		if( "image" == $post_ad_type){
			if( $ad_image_url != "" ){
				echo '<style type="text/css">.plugin_ad img{width:'.$width.'px; height:'.$height.'px}</style>';
				$data     = '<div class="plugin_ad '.$is_sticky_ad.'">';
				$data_img = '<img src="'.$ad_image_url.'" title="'.$ad_image_title.'"  style="width:'.$width.'px;height:'.$height.'px" />';
				$data .= '<a onclick="return redirect_image_link('.$add_post_id.')" class="ad ad-'.sanitize_title($ad_image_title).'" href="'.$ad_image_link_url.'" '.$target.' title="'.$ad_image_title.'">'.$data_img.'</a>';
				$data .= '</div>';
				if( is_page() || is_single() || is_singular() ){
					if(count($current_ad_location)>1){
						if( in_array( 'theme_before_post',$current_ad_location ) && in_array( 'theme_after_post',$current_ad_location ) ){
							return $data.$current_post_content.$data;
						}elseif( in_array('theme_before_post',$current_ad_location) ){
							return $data.$current_post_content;
						}elseif( in_array('theme_after_post',$current_ad_location) ){
							return $current_post_content.$data;
						}else{
							echo $data;
						}
					}else{
						if( in_array('theme_before_post',$current_ad_location) ){
							return $data.$current_post_content;
						}elseif( in_array('theme_after_post',$current_ad_location) ){
							return $current_post_content.$data;
						}else{
							echo $data;
						}
					}
				}else{
					echo $data;
				}
			}
		}else{
			if( $post_ad_html != "" ){
				echo '<style type="text/css">.plugin_ad img{width:'.$width.'px; height:'.$height.'px}</style>';
				$data = '<div class="plugin_ad '.$is_sticky_ad.'">';
				$data .= $post_ad_html;
				$data .= '</div>';
				if( is_page() || is_single() || is_singular() ){
					if(count($current_ad_location)>1){
						if( in_array( 'theme_before_post',$current_ad_location ) && in_array( 'theme_after_post',$current_ad_location ) ){
							return $data.$current_post_content.$data;
						}elseif( in_array('theme_before_post',$current_ad_location) ){
							return $data.$current_post_content;
						}elseif( in_array('theme_after_post',$current_ad_location) ){
							return $current_post_content.$data;
						}else{
							echo $data;
						}
					}else{ 
						if( in_array('theme_before_post',$current_ad_location) ){
							return $data.$current_post_content;
						}elseif( in_array('theme_after_post',$current_ad_location) ){
							return $current_post_content.$data;
						}else{
							echo $data;
						}
					}
				}else{
					echo $data;
				}
			}
		}
	}
}
/* Display Ad: end */

/*
 * Function Name:tevolution_category_ads_locations
 * Return: set the tevolution taxonomy page ad displat action list
 */
add_filter('add_theme_category_locations','tevolution_category_ads_locations',0);
function tevolution_category_ads_locations($tevolution_category_locations){
	
	$templatic_custom_post=apply_filters('tmpl_allow_monetize_posttype',get_option('templatic_custom_post'));
	if(!empty($templatic_custom_post)){
		foreach($templatic_custom_post as $type =>$value){
		
		if(function_exists('tevolution_get_post_type'))
			$tev_post_types = apply_filters('tmpl_cat_ads_posttypes',tevolution_get_post_type());
		
		if(in_array($type,$tev_post_types)){
			continue;	
		}
		$tevolution_category_locations[$type]=array("templ_before_categories_title"	   => 'Above '.ucfirst($type).' Category Title',
											"templ_after_categories_title"	   => 'Below '.ucfirst($type).' Category Title',
											"templ_before_categories_description" => 'Above '.ucfirst($type).' Category Description',
											"templ_after_categories_description"  => 'Below '.ucfirst($type).' Category Description'
										);
		}
	}
	$tevolution_category_locations['post']=array("templ_before_categories_title"	   => 'Above Post Category Title');
	return $tevolution_category_locations;
}
/*
Name : redirect_image_link
Description : function to call a ajax function of increment the advertisement visited.
*/
add_action('wp_footer','redirect_image_link');
function redirect_image_link()
{
	?>
	<script type="text/javascript">
		function redirect_image_link(add_id)
		{
			jQuery.ajax({
				url:ajaxUrl,
				type:'POST',
				data:'action=update_add_click&add_id='+add_id,
				success:function(results) {
					
				}
			});
		}
	</script>
	<?php
}
/*
Name : update_add_click
Description : increment the count the particualr advertisement is visited.
*/
add_action('wp_ajax_update_add_click','update_add_click');
add_action('wp_ajax_nopriv_update_add_click','update_add_click');		
function update_add_click()
{
	$add_visited = @get_post_meta($_POST['add_id'],'add_visited',true)+1;
	update_post_meta($_POST['add_id'],'add_visited',$add_visited);exit;
}

global $pagenow,$wpdb;
if((isset($_REQUEST['page']) && (($_REQUEST['page']=='custom_fields' || $_REQUEST['page']=='location_settings')) || $pagenow=='themes.php' || $pagenow=='plugins.php' )  ) 
{ 
	 /* Insert Post heading type into posts */
	 $locations_info = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'locations_info' and $wpdb->posts.post_type = 'custom_fields'");
 	 if(count($locations_info) != 0)
	 {
		 $post_type=get_post_meta($locations_info->ID, 'post_type',true );
		 	if(!strstr($post_type,'admanager'))
				update_post_meta($locations_info->ID, 'post_type',$post_type.',admanager' );
				
			
			update_post_meta($locations_info->ID, 'admanager_sort_order','1' );
			update_post_meta($locations_info->ID, 'post_type_admanager','admanager' );
	 }
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
	{
		/* Insert Post excerpt into posts */
		$post_content = $wpdb->get_row("SELECT post_title,ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'post_city_id' and $wpdb->posts.post_type = 'custom_fields'");
		if(count($post_content) != 0)
		{
			$post_type=get_post_meta($post_content->ID, 'post_type',true );
			if(!strstr($post_type,'admanager'))
				update_post_meta($post_content->ID, 'post_type',$post_type.',admanager' );
					
			update_post_meta($post_content->ID, 'post_type_admanager','admanager' );
		}
	}
	 
	
}
/*
Name : remove_extra_meta_box
Description : remove extra meta boxes from add admanager section.
*/
add_action('admin_init','remove_extra_meta_box',99);
function remove_extra_meta_box()
{
	remove_meta_box( 'ptthemes-settings-image-gallery', 'admanager', 'side' );
	remove_meta_box( 'block_ip', 'admanager', 'side' );
	remove_meta_box( 'commentstatusdiv', 'admanager', 'normal' );
	$location_post_type = '';
	$location_post_type = ",".implode(',',get_option('location_post_type'));
	if(is_plugin_active('Tevolution-LocationManager/location-manager.php') && strpos($location_post_type,',admanager') === false)
	{
		remove_meta_box( 'ptthemes-settingsbasic_inf', 'admanager', 'normal' );
	}
}
/*
Name : tevolution_custom_post_type
Description : remove admanger post type from author page tab.
*/
add_filter('tevolution_custom_post_type','tevolution_custom_post_type');
function tevolution_custom_post_type()
{
	$posttaxonomy = get_option("templatic_custom_post");
	if(array_key_exists('admanager',$posttaxonomy))
	{
		unset($posttaxonomy['admanager']);
	}
	return $posttaxonomy;
}

/* action to remove the ad manager post type from the function we use to get all tevolution post type.*/
add_filter('tmpl_allow_monetize_posttype','tmpl_remove_adpost_type');
add_filter('tmpl_allow_fields_posttype','tmpl_remove_adpost_type');
add_filter('tevolution_custom_post_type_list','tmpl_remove_adpost_type');
add_filter('tmpl_allow_pkg_posttype','tmpl_remove_adpost_type');
add_filter('templatic_custom_posttype','tmpl_remove_adpost_type');
add_filter('tevolution_get_post_type','tevolution_get_post_type_remove');
add_filter('tmpl_allow_postmetas_posttype','tevolution_get_meta_box_remove');

function tmpl_remove_adpost_type($post_types){
	
	//if(in_array('admanager', $post_types)) 
	{
		unset($post_types['admanager']);
	}
	return $post_types;
}

/* if location manager not activated don't pass ad manager post type to display meat box in add/edit add section */
function tevolution_get_meta_box_remove($post_types){
	if(!is_plugin_active('Tevolution-LocationManager/location-manager.php')){
			//unset($post_types['admanager']);
	}
	return $post_types;
}
/* remove the ad manager post type from custom fields section - filters */
function tevolution_get_post_type_remove($post_type){
	
	if(($key = array_search('admanager', $post_type)) !== false) {
		unset($post_type[$key]);
	}
	return $post_type;
}
?>
