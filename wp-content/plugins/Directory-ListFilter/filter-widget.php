<?php
/*////////////////////////////
 *
 * Widget for search filter
 *
 *////////////////////////////
/*
	Name : searchFilters_Widget
    Desc : Search by filters according to custom fields and taxonomies and many other factors
*/
		
class searchFilters_Widget extends WP_Widget {
	
	/**
	 * Register widget with WordPress.
	 */
	
	function __construct() {
		$widget_ops = array('classname' => 'search_filter', 'description' => __('Filter category page entries and search results in real-time. Use custom fields as parameters. Available widget areas: Category page sidebars, Primary sidebar.',SF_DOMAIN) );
		$this->WP_Widget('searchFilters_Widget', __('T &rarr; List Filter',SF_DOMAIN), $widget_ops);
	}
		
	/**
	 * Front-end display of widget.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;

		$title = $instance['title'];
		$cat = $instance['sf_cats'];
		$tags = $instance['sf_tags'];
		$rating = $instance['sf_rating'];
		$search_criteria = $instance['sf_search_criteria'];
		$distance = $instance['sf_distance'];
		$max_range =  ($instance['sf_max_range'] ? $instance['sf_max_range'] : 5000);
		$post_type = (isset($_REQUEST['post_type']) && $_REQUEST['post_type']!= '' && !is_array($_REQUEST['post_type'])) ? $_REQUEST['post_type'] : $instance['sf_post_type'][0];
		$searchin_cc = $instance['sf_search_in_current_city'];
		$event_date = $instance['sf_event_date'];
		$current_post_type = get_post_type(); /* get current page post type */ 
		
		
		if(isset($searchin_cc) && $searchin_cc != '')
			$query_string .= '&search_filter_in_city=1';
	
		/* get taxonomies */
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));	
		
		$custom_fields_onad = get_filter_search_post_fields($post_type,'custom_fields',$post_type);

		if(isset($_REQUEST['post_type']) && $_REQUEST['post_type']!= '' && !is_array($_REQUEST['post_type']))
		{
			$field_arr = array();
			foreach($custom_fields_onad as $fields)
			{
				$field_arr[] = $fields['name'];
			}
			$search_criteria = $field_arr;
		}
		
		
		/* add block with unique id to fetch the filter results on search page */
		if(is_search() && $_REQUEST['nearby'] == 'search')
		{	?>
			<script>
				jQuery('.twp_search_cont').after('<div id="loop_listing_taxonomy" class="list"></div>');
			</script><?php
		}
		elseif(is_search() && $_REQUEST['nearby'] != 'search')
		{
			?>
			<script>
				jQuery('.twp_search_cont').after('<div id="loop_listing_taxonomy" class="list"></div>');
			</script>	
			<?php
		}
		/* End */
		/* if widget is put on wrong place, then show the warning */
		if(!is_tax() && !is_archive() && !is_search() && ($current_post_type != $post_type))
		{
			
		}
		else{

			/* widget start */
			echo $args['before_widget']; 
			$title = apply_filters( 'widget_title', $instance['title'] );
			/* get post type */
			
			$check_list = array('property','classified','event');
			if(in_array($post_type,$check_list))
				$list_id='loop_'.$post_type.'_taxonomy';
			else	
				$list_id='loop_listing_taxonomy';
				
			if(is_tax()){
				
				$page_type='taxonomy';
			}else{
				
				$page_type='archive';
				
			}
			
		
			/* get last quired object */
			$queried_object = get_queried_object();   
			$term_id = $queried_object->term_id;  
			/* query string for ajax request for term id */
			$query_string .='&term_id='.$term_id;  
			if(!is_author()){
			?>
			
			<form method="get" id="searchfilterform" class="tmpl_filter_results" name="searchfilterform" autocomplete="off" action="<?php echo home_url(); ?>/">
			<?php
			/* gets all query string and join them */
			if(isset($_SERVER['QUERY_STRING'])){
				$query_string.='&'.$_SERVER['QUERY_STRING'];
			}
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				
				$TmplajaxUrl = SEARCH_FILTER_PLUGIN_URL.'tmpl_custom_ajax.php?lang='.ICL_LANGUAGE_CODE ;				
			}else{
				$TmplajaxUrl = SEARCH_FILTER_PLUGIN_URL.'tmpl_custom_ajax.php' ;				
			}
			?>
			<input type="hidden" value="" id="sortby" name="sortby" />
			<script>
				var TmplajaxUrl = '<?php echo esc_js($TmplajaxUrl); ?>';
			</script>
			<?php
			
			/* remove filter of advance search result because to get proper custom post on advance search page */
			remove_filter('posts_where', 'advance_search_template_where');

			/* if custom field is post title then show it to top of the filters  */
			
			if(isset($search_criteria) && count($search_criteria) > 0)
			{ ?>
			<h3 class="widget-title"><?php echo $title; ?></h3>
			<?php
				
			}
			/* title filter end */
			
			/* show published date */
			if(isset($event_date) && $event_date == 1 && $post_type == 'event')
			{
				?>
				<div class="filter ver-list-filter fil-scroll">
					<?php
					 /* include file for calenderSearch from current city */
					 include_once(SEARCH_FILTER_FOLDER_PATH.'calender.php'); ?>
				</div>				
				<?php
			}
			/* show published date end */
		
		/* if categories is selected from widget, get the categories of custom fields we selected from widget */

		if(!is_tax())
		{
		
			/* If search from multiple taxonomies then show all categories */
			if(is_search() && is_array($_REQUEST['post_type'])){
				for($i=0; $i <= count($_REQUEST['post_type']); $i++){
					$post_type_ = $_REQUEST['post_type'][$i];
					$taxonomies_ = get_object_taxonomies( (object) array( 'post_type' => $post_type_,'public'   => true, '_builtin' => true ));	
					
					if($post_type_ !=''){
						$args1=array(
						'post_type'                => (isset($post_type_) && $post_type_ != '') ? $post_type_ : 'post', /* post type */
						'child_of'                 => 0,
						'parent'                   => '',
						'orderby'                  => 'name',
						'order'                    => 'ASC',
						'hide_empty'               => 1,
						'hierarchical'             => 1,
						'exclude'                  => '',
						'include'                  => '',
						'number'                   => '',
						'taxonomy'                 => (isset($taxonomies_[0]) && $taxonomies_[0]!='') ? $taxonomies_[0] :'category',   /* texonomy for post type */
						'pad_counts'               => false
						);

						/* get all categories of particular post type selected from backend widget */
						$categories = get_categories($args1);  
						?>
						<!-- category listing --> 
						<div id="tmpl_<?php echo $post_type_; ?>_div" class="filter ver-list-filter fil-scroll filter_class">
							<h3 class="widget-title"><?php _e('Categories',SF_DOMAIN); ?> <i class="fa fa-angle-down" id="cat_up_down"></i></h3>

						<?php 
						$tmpl_post_type = "'".$post_type_."'";
						if(!isset($categories['errors']) && count($categories) > 0){
							?>
							<ul class="sf_cat" id="sf_cat_up_down">
							<?php
							
							foreach($categories as $category) 
							{	
								/* set child wp_categories on transient */
								
								$parent = get_term( $category->term_id, $taxonomies_[0] );

								if($parent->count !=0){
										$checked_cat = '';
										if($parent){
											if(isset($_REQUEST['category']) && $_REQUEST['category'] == $parent->term_id)
												 $checked_cat = 'checked="checked"';
												
												if(is_search()){
													$parents = '<li><label><input '.$checked_cat.' type="checkbox" filterdisplayname="'.__('Categories',SF_DOMAIN).'" filterdispvalue="'.$parent->name.'" class="sf_checkcats" value="'.$parent->term_id.'" name="cats[]" onclick="filter_search_fields_nearby('.$tmpl_post_type.',\'ptype\',\'tmpl_'.$post_type_.'_div\',\'filter_class\')"/>' . $parent->name.'<span>('.$category->count.')</span></label></li>';
												}else{
													$parents = '<li><label><input '.$checked_cat.' type="checkbox" filterdisplayname="'.__('Categories',SF_DOMAIN).'" filterdispvalue="'.$parent->name.'" class="sf_checkcats" value="'.$parent->term_id.'" name="cats[]"/>' . $parent->name.'<span>('.$category->count.')</span></label></li>';
												}
												/* $parents = '<input type="checkbox" value="'.$parent->term_id.'" name="cats[]"/>' . $parent->name ; */
												do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; 
											}
								}
							}
							echo '</ul>';    /* end category listing */
						}
						echo "</div>";
					}
				}
			
			}else{
				$args1=array(
					'post_type'                => (isset($post_type) && $post_type != '') ? $post_type : 'post', /* post type */
					'child_of'                 => 0,
					'parent'                   => '',
					'orderby'                  => 'name',
					'order'                    => 'ASC',
					'hide_empty'               => 1,
					'hierarchical'             => 1,
					'exclude'                  => '',
					'include'                  => '',
					'number'                   => '',
					'taxonomy'                 => (isset($taxonomies[0]) && $taxonomies[0]!='') ? $taxonomies[0] :'category',   /* texonomy for post type */
					'pad_counts'               => false
					);

				/* get all categories of particular post type selected from backend widget */
				$categories = get_categories($args1);  

				?>
				<!-- category listing --> 
				<div class="filter ver-list-filter fil-scroll">
					<label><strong><?php _e('Categories',SF_DOMAIN); ?> <i class="fa fa-angle-down" id="cat_up_down"></i></strong></label>

				<?php 
				if(!isset($categories['errors']) && count($categories) > 0){
					echo '<ul class="sf_cat" id="sf_cat_up_down">';
					    tev_wp_terms_checklist_filter($post->ID, array( 'taxonomy' =>$taxonomies[0]) );
					echo '</ul>';
					
				}else{
					echo '<p>'. __('No Category available.',DIR_DOMAIN).'</p>';
				} 
				echo "</div>";
			}
		?>
		
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var flag = 0;
				jQuery('.fil-scroll .widget-title i').bind('click',function(){
					var id = jQuery(this).attr('id');
					if(flag == 0)
						{
							jQuery('#sf_'+id).slideUp('slow');
							flag = 1;
						}
					else
						{
							jQuery('#sf_'+id).slideDown('slow');
							flag = 0;
						}
				});
			});
		</script>
		<?php
		/* end for categories */
	
		}
		
		
		/* if ratting is selected from widget show rating's checkbox */
			if(isset($rating) && $rating == 1)	
			{ ?>	
				<div class="filter hrz-list-filter fil-scroll">
					<label><strong><?php _e('Star rating',SF_DOMAIN); ?></strong></label>
					<ul>
						<li><input id="sf_sfrate1" class="sf_sfrate" filterdisplayname="<?php _e('Rating',SF_DOMAIN);?>" type="checkbox" name="rate[]" value="1"><label for="sf_sfrate1"><?php _e('1',SF_DOMAIN); ?></label></li>
						<li><input id="sf_sfrate2" class="sf_sfrate" filterdisplayname="<?php _e('Rating',SF_DOMAIN);?>" type="checkbox" name="rate[]" value="2"><label for="sf_sfrate2"><?php _e('2',SF_DOMAIN); ?></label></li>
						<li><input id="sf_sfrate3" class="sf_sfrate" filterdisplayname="<?php _e('Rating',SF_DOMAIN);?>" type="checkbox" name="rate[]" value="3"><label for="sf_sfrate3"><?php _e('3',SF_DOMAIN); ?></label></li>
						<li><input id="sf_sfrate4" class="sf_sfrate" filterdisplayname="<?php _e('Rating',SF_DOMAIN);?>" type="checkbox" name="rate[]" value="4"><label for="sf_sfrate4"><?php _e('4',SF_DOMAIN); ?></label></li>
						<li><input id="sf_sfrate5" class="sf_sfrate" filterdisplayname="<?php _e('Rating',SF_DOMAIN);?>" type="checkbox" name="rate[]" value="5"><label for="sf_sfrate5"><?php _e('5',SF_DOMAIN); ?></label></li>
					</ul>
				</div>
			<?php	
			}
			/* end ratting */			
		
		/* if distance is selected from back end widget then show slider for distance search */
		if(isset($distance) && $distance == 1)
		{
				/* include slider script */
				wp_enqueue_script("jquery-ui-slider");  
				?>
				<div class="sf_search_range">
					<label><strong><?php _e('Search nearby (in miles)',SF_DOMAIN); ?></strong></label>
					<input type="text" filterdisplayname="<?php _e('Mile range',SF_DOMAIN)?>" name="radius" id="sf_radius_range" value="<?php echo $max_range; ?>" style="border:0; font-weight:bold;"  readonly="readonly"/>
				</div>
				 <div id="sf-radius-range"></div> <!-- division for range slider --> 
				  <script type="text/javascript">		    
					jQuery('#sf-radius-range').bind('slidestop', function(event, ui) {				
						var miles_range=jQuery('#sf_radius_range').val();
						/* add the miles range filter above content*/
						clearTimeout(typingTimer);
						typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_radius_range');
					});
					
					/* generates miles range slider */
					jQuery(function(){
						jQuery("#sf-radius-range").slider({
									range:true,
									min:1,
									max:<?php echo $max_range; ?>,
									values:[1,<?php echo $max_range; ?>],
									slide:function(e,t){
										jQuery("#sf_radius_range").val(t.values[0]+" - "+t.values[1])
										}
									});
						jQuery("#sf_radius_range").val(jQuery("#sf-radius-range").slider("values",0)+" - "+jQuery("#sf-radius-range").slider("values",1))			
					});
				</script>
				<?php
		}
		/* distance search end */
			
		/* shows custom fields filter checked from back end widget */
		?>
		<div id="custom_field_part"></div>
		<?php
		if(isset($search_criteria) && count($search_criteria) > 0 && (!is_search() || (is_search() && isset($_REQUEST['post_type']) && count($_REQUEST['post_type']) <=1)))
		{
			$c=0;
			$tc = count($search_criteria);
			foreach($search_criteria as $filters_type)
			{
				$c ++;
				if($tc==$c){
						do_action('tmpl_last_filters_start');
				}
				do_action('tmpl_start_filters_'.$c);
				$custom_field_id = tmpl_get_post_id_by_meta_key_and_value('htmlvar_name', $filters_type); // get custom field id
				$cfield = get_post($custom_field_id);
				$placeholder = $cfield->post_title; /* get field title */
				$custom_field_type= get_post_meta($custom_field_id,'ctype',true); /* get custom field type */
				$search_ctype= get_post_meta($custom_field_id,'search_ctype',true); /* get custom field type */
				if($search_ctype!=''){
					$custom_field_type=$search_ctype;
				}				
				$htmlvar_name = get_post_meta($custom_field_id,'htmlvar_name',true);
				$default_value = get_post_meta($custom_field_id,'default_value',true);
				$range_min = get_post_meta($custom_field_id,'range_min',true);
				$range_max = get_post_meta($custom_field_id,'range_max',true);
				/* Show custom fields categorywise if "Show custom fields categorywise" enable from general settings in tevolution for category page */
				$tmpdata = get_option('templatic_settings');
				if($tmpdata['templatic-category_custom_fields'] == 'Yes' && is_tax())
				{ 
					/* get categories attached to the custom field */
					$custom_field_categories = get_the_terms( $custom_field_id , $taxonomies[0] );

					$custom_field_cat = array();
					if(is_array($custom_field_categories))
					{
						
						foreach($custom_field_categories as $_custom_cat)
						{
							$custom_field_cat[] = $_custom_cat->term_id;
						}
					}
					/* if custom field not contains the id of current category page then do not show on that category page */
					if(!in_array($term_id,$custom_field_cat))
					{
						//continue;
					}
					$custom_field_cat = array();
				}
				?>
                <input type="hidden" name="list_filter_search_custom[<?php echo $htmlvar_name;?>]" value="<?php echo $custom_field_type;?>"  />
                <?php
				if($custom_field_type == 'text'){ /* if custom field type is text then show text box */
					do_action('tmpl_fiters_text_start_'.$htmlvar_name);
				?>
					<div class="filter ver-list-filter fil-scroll">
						<label><strong><?php echo $placeholder; ?></strong></label>
						<div>
							<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php if($htmlvar_name == 'post_title' || $htmlvar_name == 'location') echo 'sf_'.$filters_type; else echo $filters_type; ?>" id="<?php echo $filters_type; ?>">
							<p class="description">
							<?php if($htmlvar_name == 'end_time' || $htmlvar_name == 'st_time' ) 
									{
										_e('Enter event end time. eg. 18:25 (Follows 24 hrs format)',SF_DOMAIN);
									}
							?>
							</p>
						</div>
					</div>					
					<?php	
					do_action('tmpl_fiters_text_end_'.$htmlvar_name);
				}elseif($custom_field_type=='multicity'){
					global $wpdb,$country_table,$zones_table,$multicity_table;
					do_action('tmpl_fiters_multicity_start_'.$htmlvar_name);
					$countryinfo = $wpdb->get_results($wpdb->prepare("SELECT  distinct  c.country_id,c.*  FROM $country_table c,$multicity_table mc where  c.`country_id`=mc.`country_id`  AND c.is_enable=%d group by country_name order by country_name ASC",1));
					if(isset($default_country_id))
						$zoneinfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM $zones_table z, $multicity_table mc where z.zones_id=mc.zones_id AND mc.country_id=%d  order by zone_name ASC",$default_country_id));
					if(isset($default_zone_id)  && isset($default_country_id))
						$cityinfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM $multicity_table where zones_id=$default_zone_id AND country_id=%d order by cityname ASC",$default_country_id));
					?><!-- Country select box -->
					<div class="form_row clearfix">               
					   <select name="country_id" id="country_id">
							<option value=""><?php _e('Select Country',SF_DOMAIN);?></option>
							<?php foreach($countryinfo as $country): $selected=($country->country_id==$default_country_id)? 'selected':'';
							$country_name=$country->country_name;
							 if (function_exists('icl_register_string')) {									
									icl_register_string('location-manager', 'location_country_'.$country->country_id,$country_name);
									$country_name = icl_t('location-manager', 'location_country_'.$country->country_id,$country_name);
							  }
						?>
							<option value="<?php echo $country->country_id?>" <?php echo $selected;?>><?php echo $country_name;?></option>
							<?php endforeach; ?>
					   </select>	  
					</div>
					<!-- State select box -->
					<div class="form_row clearfix">               
					   <select name="zones_id" id="adv_zone">
							<option value=""><?php _e('All Regions',SF_DOMAIN);?></option>
							<?php 
							if($zoneinfo){
							foreach($zoneinfo as $zone): $selected=($zone->zones_id ==$default_zone_id)? 'selected':'';
							$zone_name=$zone->zone_name;
							 if (function_exists('icl_register_string')) {									
									icl_register_string('location-manager', 'location_zone_'.$zone->zones_id,$zone_name);
									$zone_name = icl_t('location-manager', 'location_zone_'.$zone->zones_id,$zone_name);
							  }	
						?>
							<option value="<?php echo $zone->zones_id?>" <?php echo $selected;?>><?php echo $zone_name;?></option>
							<?php endforeach;
							} ?>
					   </select>
					  
					</div>
				  <!-- Cities select box -->
					<div class="form_row clearfix">             
					   <select name="post_city_id" id="adv_city">
							<option value=""><?php _e('All Cities',SF_DOMAIN);?></option>
							<?php
							if($cityinfo){
							foreach($cityinfo as $city): $selected=($city->city_id ==$default_city_id)? 'selected':'';
								$cityname=$city->cityname;
								if (function_exists('icl_register_string')) {									
										icl_register_string('location-manager', 'location_city_'.$city->city_slug,$cityname);
										$cityname = icl_t('location-manager', 'location_city_'.$city->city_slug,$cityname);
								} ?>
								<option value="<?php echo $city->city_id?>" <?php echo $selected;?>><?php echo $cityname;?></option>
							<?php endforeach;
							} ?>
					   </select>
					</div>
					<script>
					var typingTimer;                /* timer identifier*/
					var doneTypingInterval = 1000;  /* time in ms, 1 second */
						/* add the selected filter above content*/
						jQuery('#searchfilterform select').bind('change',function(){
							clearTimeout(typingTimer);
							typingTimer = setTimeout(doneTyping, doneTypingInterval);
						});
					</script>
					  <?php	
				do_action('tmpl_fiters_multicity_end_'.$htmlvar_name);
				/* if field type is select, then show select box  */ 	
				}elseif($custom_field_type == 'select'){ 
				do_action('tmpl_fiters_select_start_'.$htmlvar_name);
				?>
					
					<div class="filter ver-list-filter fil-scroll">
						<label><strong><?php echo $placeholder; ?></strong></label><?php				
						$options = get_post_meta($custom_field_id,'option_values',true);	
						
						$option_titles = get_post_meta($custom_field_id,'option_title',true);
						$ctype = get_post_meta($custom_field_id,'ctype',true);
						if($options)
						{  $chkcounter = 0;
							echo '<div class="form_cat_left">';
							echo '<select filterdisplayname="'.$placeholder.'" id="'.$htmlvar_name.'" name="'.$htmlvar_name.'" name="'.$htmlvar_name.'" class="hr_input_radio">';
							echo '<option value="">';
							_e('Select ',SF_DOMAIN);echo $placeholder;
							echo '</option>';
							$option_values_arr = explode(',',$options);
							$option_title_arr = explode(',',$option_titles);
							$label = explode(',',$option_titles);					
							for($i=0;$i<count($option_values_arr);$i++)
							{
								$chkcounter++;
								$seled='';
								if($default_value == $option_values_arr[$i]){ $seled='selected="selected"';}
								if (isset($value) && trim($value) == trim($option_values_arr[$i])){ $seled='selected="selected"';}
								echo '<option value="'.trim($option_values_arr[$i]).'" '.$seled.'  '.$extra_parameter.' /> '.$option_values_arr[$i].'</option>';
							}
							echo '</select></div>';
						}
						?>
					</div>
					<script>
					var typingTimer;                /* timer identifier*/
					var doneTypingInterval = 1000;  /* time in ms, 1 second */
						jQuery('#searchfilterform select').live('change',function(){
							clearTimeout(typingTimer);
							typingTimer = setTimeout(doneTyping, doneTypingInterval,jQuery(this).attr('id'));
						});
					</script>
				<?php 
				do_action('tmpl_fiters_select_end_'.$htmlvar_name);
			}elseif($custom_field_type == 'date'){ /* if custom field type is date then show date box */
					/* jquery date picker */ 
					do_action('tmpl_fiters_date_start_'.$htmlvar_name);
					?>     
					<div class="filter ver-list-filter fil-scroll">
						<label><strong><?php echo $placeholder; ?></strong></label>
						<script type="text/javascript">
							jQuery(function(){
								var pickerOpts = {						
									showOn: "both",
									dateFormat: 'yy-mm-dd',
									buttonText: '<i class="fa fa-calendar"></i>',
									monthNames: objectL11tmpl.monthNames,
									monthNamesShort: objectL11tmpl.monthNamesShort,
									dayNames: objectL11tmpl.dayNames,
									dayNamesShort: objectL11tmpl.dayNamesShort,
									dayNamesMin: objectL11tmpl.dayNamesMin,
									isRTL: objectL11tmpl.isRTL,
								};	
								jQuery("#<?php echo $filters_type;?>").datepicker(pickerOpts);
							});
						</script>
						<div class="small-12 columns dp">
							<input type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $filters_type;?>" id="<?php echo $filters_type;?>" class="textfield" value="" size="25"  />          
						</div>
					</div>
					<script>
					jQuery('#<?php echo $filters_type;?>').live('change',function(){
						clearTimeout(typingTimer);
						 typingTimer = setTimeout(doneTyping, doneTypingInterval,'<?php echo $filters_type;?>');
					});
					</script>
					<?php
					do_action('tmpl_fiters_date_end_'.$htmlvar_name);
				}
				elseif($custom_field_type =='radio') 
				{ /* if field type is radio then show radio buttons */ 
					do_action('tmpl_fiters_radio_start_'.$htmlvar_name);
				?>
					<div class="filter ver-list-filter fil-scroll">
						<label><strong><?php echo $placeholder; ?></strong></label><?php				
						$options = get_post_meta($custom_field_id,'option_values',true);				
						$option_titles = get_post_meta($custom_field_id,'option_title',true);
						$ctype = get_post_meta($custom_field_id,'ctype',true);
						if($options)
						{  $chkcounter = 0;
							echo '<div class="form_cat_left">';
							echo '<ul class="hr_input_radio">';
							$option_values_arr = explode(',',$options);
							$option_title_arr = explode(',',$option_titles);
							$label = explode(',',$option_titles);					
							for($i=0;$i<count($option_values_arr);$i++)
							{
								$chkcounter++;
								$seled='';
								//if($default_value == $option_values_arr[$i]){ $seled='checked="checked"';}
								//if (isset($value) && trim($value) == trim($option_values_arr[$i])){ $seled='checked="checked"';}
								echo '<li>
									
										<input filterdisplayname="'.$placeholder.'" name="'.$htmlvar_name.'"  id="'.$htmlvar_name.'_'.$chkcounter.'" type="radio" value="'.trim($option_title_arr[$i]).'" '.$seled.'  '.$extra_parameter.' /> 
									<label class="r_lbl" for="'.$htmlvar_name.'_'.$chkcounter.'">'.$option_title_arr[$i].'&nbsp;</label>
								</li>';
							}
							echo '</ul></div>';
						}
						?>
					</div>
					<?php do_action('tmpl_fiters_radio_end_'.$htmlvar_name); ?>
					<!-- When selected custom field set as radio button - The filter results( Which display above results ) should display the radio button title not value -->
					<script>

						jQuery('#searchfilterform input[type="radio"]').bind('click',function(){
						 clearTimeout(typingTimer);
						 typingTimer = setTimeout(searchRadiofield, doneTypingInterval,jQuery(this).attr('name'));
						 function searchRadiofield(id)
						 {
							var miles_range=jQuery('#sf_radius_range').val();
							<?php
							if(is_search())
							{
								
								?>
								var list_id='loop_listing_archive';
								<?php
								
							}
							else
							{
								?>
								var list_id='<?php echo $list_id?>';
								<?php
							}
							?>		
							jQuery('.'+list_id+'_process').remove();
							jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );

							jQuery.ajax({
								url:TmplajaxUrl,
								type:'POST',
								cache: true,			
								data: 'action=search_filter<?php echo $query_string; ?>&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize(),
								success:function(results){
										jQuery('.filter_list_wrap').show();
										jQuery('#selectedFilters').show();
										
										var filter_title = jQuery('#searchfilterform input[name="'+id+'"]').attr('filterdisplayname'); /* get filter label */
										var value = jQuery('#searchfilterform input[name="'+id+'"]').val(); /* get filter value */
										
										if(id != '') /* if filter title is not blank then add a filter to filter listing */
										{
											
											if (jQuery('#selectedFilters #filter-group-'+id).length == 0 )
											{
												jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="RTitle" onclick="delFltrRadio(\''+id+'\')">'+value+'<i class="fa fa-times-circle"></i></a></span></div>');
											}
											else
												{
													jQuery('#filter-group-'+id+' span a').html(value+'<i class="fa fa-times-circle"></i>');
												}
											
											if(jQuery('#searchfilterform #'+id).val() == '')
											{
												jQuery('#selectedFilters #filter-group-'+id).remove(); 
											}
											if(id == 'search_within')	
											{
												if(jQuery('#selectedFilters #'+id).val() == '')
												{
													jQuery('#selectedFilters #filter-group-'+id).remove(); 
												}
											}
									
										}
									else
										{
											jQuery('#selectedFilters #filter-group-'+id).remove();
										}
												
									jQuery('.'+list_id+'_process').remove();
									jQuery('#listpagi').remove();
									jQuery('#'+list_id).html(results);
									//tmpl_check_form_field_values();
									
								}
							});
							/* Ajax request for locate the location on map */
							jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',			
									data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									dataType: 'json',
									success:function(results){						
										tmpl_googlemaplisting_deleteMarkers();
										templ_add_googlemap_markers_onmap(results.markers);
									}
								});
						 }	
						});
							
						function delFltrRadio(id)
						{
							jQuery('#searchfilterform input[name="'+id+'"]').attr('checked', false);
							var miles_range=jQuery('#sf_radius_range').val();
							<?php
							if(is_search())
							{
								
								?>
								var list_id='loop_listing_archive';
								<?php
								
							}
							else
							{
								?>
								var list_id='<?php echo $list_id?>';
								<?php
							}
							?>	
							
							jQuery('.'+list_id+'_process').remove();
							jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );
							
							
							/*jQuery('#searchfilterform input[name="'+id+'"]').val('');*/
							jQuery.ajax({
								url:TmplajaxUrl,
								type:'POST',
								cache:true,
								data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
								success:function(results){						
									jQuery('.'+list_id+'_process').remove();
									jQuery('#listpagi').remove();
									jQuery('#'+list_id).html(results);
									
									jQuery('#filter-group-'+id).remove();
									tmpl_check_form_field_values();
								}
							});
							/* Ajax request for locate the location on map */
							jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',			
									data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									dataType: 'json',
									success:function(results){						
										tmpl_googlemaplisting_deleteMarkers();
										templ_add_googlemap_markers_onmap(results.markers);
									}
								}); 
						}
					</script>
					<!-- End Script --><?php	
				}
				 /* if field type is multi check box then show multi checkbox */
				elseif($custom_field_type=='multicheckbox'){ 
					do_action('tmpl_fiters_mc_start_'.$htmlvar_name);
				?>
					<div class="filter ver-list-filter fil-scroll">
					<label><strong><?php echo $placeholder; ?></strong></label>
					<?php					
						 $options = get_post_meta($custom_field_id,'option_values',true);
						$option_titles = get_post_meta($custom_field_id,'option_title',true);
						 if($options)
						 {  $chkcounter = 0;
							  echo '<div class="form_cat_left hr_input_multicheckbox">';
							  $option_title_arr = explode(',',$option_titles);
							  $option_values_arr = explode(',',$options);
							  for($i=0;$i<count($option_values_arr);$i++)
							  {
								   $chkcounter++;
								   $seled='';
								   //if($default_value == $option_values_arr[$i]){ $seled='checked="checked"';}
								   if (isset($value) && trim($value) == trim($option_values_arr[$i])){ $seled='checked="checked"';}
								   echo '<div class="form_cat">
										
											 <input class="checkbox" filterdispID="'.$htmlvar_name.'" filterdispvalue="'.$option_title_arr[$i].'" filterdisplayname="'.$placeholder.'" name="'.$htmlvar_name.'[]"  id="'.$htmlvar_name.'_'.$chkcounter.'" type="checkbox" value="'.$option_values_arr[$i].'" '.$seled.'  '.$extra_parameter.' /> 
										<label class="r_lbl" for="'.$htmlvar_name.'_'.$chkcounter.'">'.$option_title_arr[$i].'</label>
								   </div>';
							  }
							  echo '</div>';
						 }
						?>
						</div>
					<?php 	do_action('tmpl_fiters_mc_end_'.$htmlvar_name); ?>
						<script>
						jQuery('#searchfilterform input[type="checkbox"]').live('click',function(){
						 if(jQuery(this).attr('class')=='sf_checkcats')
							{
									clearTimeout(typingTimer);
									typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_checkcats');
	
							}
							else if(jQuery(this).attr('class')=='sf_sfrate')
							{
									clearTimeout(typingTimer);
									typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_sfrate');		
							}
							else
							{
								clearTimeout(typingTimer);
								/*typingTimer = setTimeout(searchMulticheckbox, doneTypingInterval,jQuery(this).attr('name'),jQuery(this).attr('filterdisplayname'));*/
								typingTimer = setTimeout(searchMulticheckbox, doneTypingInterval,jQuery(this).attr('filterdispID'),jQuery(this).attr('filterdisplayname'));
							}
							
						 
						 function searchMulticheckbox(id,title)
						 {
							var miles_range=jQuery('#sf_radius_range').val();
							<?php
							if(is_search())
							{
								
								?>
								var list_id='loop_listing_archive';
								<?php
								
							}
							else
							{
								?>
								var list_id='<?php echo $list_id?>';
								<?php
							}
							?>	
							jQuery('.'+list_id+'_process').remove();
							jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );

							jQuery.ajax({
								url:TmplajaxUrl,
								type:'POST',
								cache: true,			
								data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
								success:function(results){
										jQuery('.filter_list_wrap').show();
										jQuery('#selectedFilters').show();
										var atLeastOneIsChecked = jQuery('#searchfilterform input[name="'+id+'[]"]:checked').length; /* get checked reates length */
										if(atLeastOneIsChecked > 0) 
										{
											filter_title = title; /* get title of filter */
											jQuery('#searchfilterform input[name="'+id+'[]"]:checked').each(function() {
											chkId = jQuery(this).val();
										     checkboxName = jQuery(this).attr('filterdispvalue'); 
											if (jQuery('#selectedFilters #filter-group-'+id).length == 0 ) /* creates a block for ratiing of block is not created before */
											{
												jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrCheckbox(\''+chkId+'\',\''+id+'\')">'+checkboxName+'<i class="fa fa-times-circle"></i></a></span></div>');
											}
											else    /* otherwise add a value for created block  */
											{
												if (jQuery('#filter-group-'+id+' span #'+id+'_'+chkId).length == 0 )
												{
													jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrCheckbox(\''+chkId+'\',\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a>');
												}
												else
												{
													//alert('else');
													jQuery('#searchfilterform input[type="'+id+'"]').each(function(){
													var curr_val = jQuery(this).val();
													if(jQuery(this).attr('checked') == 'checked')
													{
														jQuery('#filter-group-'+id+' span a').remove();
														jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrCheckbox(\''+chkId+'\',\''+id+'\')">'+checkboxName+'<i class="fa fa-times-circle"></i></a>');
													}
																													
													});
												}
											}
										});
										}else{
											jQuery('#selectedFilters #filter-group-'+id).remove(); 
										}
												
									jQuery('.'+list_id+'_process').remove();
									jQuery('#listpagi').remove();
									jQuery('#'+list_id).html(results);
									tmpl_check_form_field_values();
								}
							});
							/* Ajax request for locate the location on map */
							jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',			
									data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									dataType: 'json',
									success:function(results){						
										tmpl_googlemaplisting_deleteMarkers();
										templ_add_googlemap_markers_onmap(results.markers);
									}
								});
						 }	
						});
							
						function delFltrCheckbox(val,type)
							{
								
								 jQuery('#searchfilterform input[name="'+type+'[]"]').each(function() {
										if(jQuery(this).val() == val)
										{
											jQuery(this).attr('checked', false); /* uncheck a checkbox of rate according to removal */
										}
								 });
							
							
								
								var miles_range=jQuery('#sf_radius_range').val(); /* get the range */
								<?php
								if(is_search())
								{
									
									?>
									var list_id='loop_listing_archive';
									<?php
									
								}
								else
								{
									?>
									var list_id='<?php echo $list_id?>';
									<?php
								}
								?>
								jQuery('.'+list_id+'_process').remove(); /* remove the element */
								jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );  /* show processing image during ajax request. */
								jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',
									cache:true,								
									data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									success:function(results){						
										jQuery('.'+list_id+'_process').remove(); 
										jQuery('#listpagi').remove();
										jQuery('#'+list_id).html(results);
										if(jQuery('#filter-group-'+type+' .value a').length > 1)
											{
												jQuery('#'+type+'_'+val).remove();
											}
										else
											{
												jQuery('#filter-group-'+type).remove();
											}
									tmpl_check_form_field_values();			
									}
								});
								/* Ajax request for locate the location on map */
								jQuery.ajax({
										url:TmplajaxUrl,
										type:'POST',			
										data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
										dataType: 'json',
										success:function(results){						
											tmpl_googlemaplisting_deleteMarkers();
											templ_add_googlemap_markers_onmap(results.markers);
										}
									});
							}
					</script>
				<?php 
				} /* if custom field type is text editor then show text field */
				elseif($custom_field_type=='min_max_range' ){
				
					$name=$filters_type;
					do_action('tmpl_fiters_mmr_start_'.$name);
				?>
                <h3 class="widget-title"><?php echo $placeholder; ?></h3>
                <input type="text" name="<?php echo $name.'_min';?>" id="<?php echo $name;?>_min" value="" placeholder="<?php echo $placeholder.' ';_e('Min value',SF_DOMAIN);?>" class="min_range" filterdisplayname="<?php echo $placeholder.' ';_e('Min value',SF_DOMAIN);?>"/>
                <input type="text" name="<?php echo $name.'_max';?>" id="<?php echo $name;?>_max" value="" placeholder="<?php echo $placeholder.' ';_e('Max value',SF_DOMAIN);?>" class="max_range" filterdisplayname="<?php echo $placeholder.' ';_e('Max value',SF_DOMAIN);?>"/>
                <input type="hidden" name="<?php echo $name;?>" value='1'/>
                <?php
				do_action('tmpl_fiters_mmr_end_'.$name);
			}elseif($custom_field_type=='slider_range' && $range_min!='' && $range_max!=''){
				$name=$filters_type;
				$min_range=$range_min;
				$max_range=$range_max;
				wp_enqueue_script("jquery-ui-slider");	
				do_action('tmpl_fiters_sliderrange_start_'.$name);
				?>
                <h3 class="widget-title"><?php echo $placeholder; ?></h3>
                <label>
                <input type="text" name="<?php echo $name;?>" id="<?php echo $name;?>_range" value="<?php echo $max_range; ?>" style="border:0; font-weight:bold;"  filterdisplayname="<?php echo $placeholder; ?>" readonly="readonly"/>
                </label>
                <div id="<?php echo $name;?>_range_type" class="clearfix" style="width:90%;"></div>
                <script type="text/javascript">
				jQuery(function(){jQuery("#<?php echo $name?>_range_type").slider({range:true,min:<?php echo $min_range;?>,max:<?php echo $max_range; ?>,values:[<?php echo $min_range;?>,<?php echo $max_range; ?>],slide:function(e,t){jQuery("#<?php echo $name;?>_range").val(t.values[0]+" - "+t.values[1])}});jQuery("#<?php echo $name;?>_range").val(jQuery("#<?php echo $name?>_range_type").slider("values",0)+" - "+jQuery("#<?php echo $name?>_range_type").slider("values",1))})
				jQuery('#<?php echo $name;?>_range_type').bind('slidestop', function(event, ui) {				
						var <?php echo $name;?>=jQuery('#<?php echo $name;?>_range').val();
						/* add the miles range filter above content*/
						clearTimeout(typingTimer);
						typingTimer = setTimeout(doneTyping, doneTypingInterval,'<?php echo $name;?>_range');
					});
				</script>
                <?php
				do_action('tmpl_fiters_sliderrange_end_'.$name);
			}elseif($custom_field_type=='min_max_range_select'){
			
				$title_min_range = explode(',',get_post_meta($custom_field_id,'search_min_option_title',true));
				$value_min_range = explode(',',get_post_meta($custom_field_id,'search_min_option_title',true));
				$title_max_range = explode(',',get_post_meta($custom_field_id,'search_max_option_title',true));
				$value_max_range = explode(',',get_post_meta($custom_field_id,'search_max_option_values',true));				
				$name=$filters_type;
				
				do_action('tmpl_fiters_mmrselect_start_'.$name);
                ?>
                <div class="form_row clearfix">
                <input type="hidden" name="<?php echo $name;?>"  value="1"/>
                <select name="<?php echo $name;?>_min" id="<?php echo $name;?>_min" class="textfield textfield_x <?php echo $style_class;?>" <?php echo $extra_parameter;?>>
                    <option value=""><?php echo sprintf(__('Please Select %s Min value',SF_DOMAIN),$site_title);?></option>
                    <?php if(!empty($value_min_range)){
                    for($i=0;$i<count($value_min_range);$i++){?>
                        <option value="<?php echo $value_min_range[$i]; ?>" <?php if($value==$value_min_range[$i]){ echo 'selected="selected"';} else if($default_value==$value_min_range[$i]){ echo 'selected="selected"';}?>><?php echo ($title_min_range[$i])? $title_min_range[$i]:$value_min_range[$i]; ?></option>
                        <?php	
                        }
                    }?>                
                </select>
                </div>
                
                 <div class="form_row clearfix">
                <select name="<?php echo $name;?>_max" id="<?php echo $name;?>_max" class="textfield textfield_x <?php echo $style_class;?>" <?php echo $extra_parameter;?>>
                    <option value=""><?php echo sprintf(__('Please Select %s Max value',SF_DOMAIN),$site_title);?></option>
                    <?php if(!empty($value_max_range)){
                    for($i=0;$i<count($value_max_range);$i++){?>
                        <option value="<?php echo $value_max_range[$i]; ?>" <?php if($value==$value_max_range[$i]){ echo 'selected="selected"';} else if($default_value==$value_max_range[$i]){ echo 'selected="selected"';}?>><?php echo ($title_max_range[$i])? $title_max_range[$i]:$value_max_range[$i]; ?></option>
                        <?php	
                        }
                    }?>                
                </select>
                <script>
					var typingTimer;                /* timer identifier*/
					var doneTypingInterval = 1000;  /* time in ms, 1 second */
						jQuery('#searchfilterform select').live('change',function(){
							clearTimeout(typingTimer);
							typingTimer = setTimeout(doneTyping, doneTypingInterval,jQuery(this).attr('id'));
						});
					</script>
                </div>
                <?php
				
				do_action('tmpl_fiters_mmrselect_end_'.$name);
			}elseif($custom_field_type == 'texteditor'){	
				do_action('tmpl_fiters_texteditor_start_'.$name);
				?>
						<div class="filter ver-list-filter fil-scroll">
							<label><strong><?php echo $placeholder; ?></strong>
							<div>
								<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $filters_type; ?>" id="<?php echo $filters_type; ?>">
							</div>
						</div>
				<?php 
				do_action('tmpl_fiters_texteditor_end_'.$name);
				}
				 /* if custom field type is textarea then show textarea */
				elseif($custom_field_type=='textarea')
				{ 
					do_action('tmpl_fiters_textarea_start_'.$name);
				?>			
					<div class="filter ver-list-filter fil-scroll">
						<label><strong><?php echo $placeholder; ?></strong></label>
						<div>
								<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $filters_type; ?>" id="<?php echo $filters_type; ?>">
							</div>      
					</div><?php
					do_action('tmpl_fiters_textarea_end_'.$name);
				}elseif($custom_field_type == 'geo_map'){ 
					do_action('tmpl_fiters_geomap_start_'.$name);
					?>
					<div class="filter ver-list-filter fil-scroll">
							<label><strong><?php echo $placeholder; ?></strong></label>
							<div>
								<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $filters_type; ?>" id="<?php echo $filters_type; ?>">
							</div>
					</div>	
					<?php
					do_action('tmpl_fiters_geomap_end_'.$name);
				}
				
				if($tc==$c){
						do_action('tmpl_last_filters_end');
				}
				do_action('tmpl_end_filters_'.$c);
			}							
		}

		echo $args['after_widget'];
	}/* end else */
	/* custom fields filter end */						 
	?>
	</form>
	<?php } ?>
	<script>
		var typingTimer;                /* timer identifier*/
		var doneTypingInterval = 1000;  /* time in ms, 1 second */
		/* add the selected filter above content*/
		jQuery('#searchfilterform input[type="text"]').bind('keyup',function(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(doneTyping, doneTypingInterval,this.id);
		});
		/* start date request */
		jQuery('#st_date').live('change',function(){
			clearTimeout(typingTimer);
			 typingTimer = setTimeout(doneTyping, doneTypingInterval,'st_date');
		});
		
		/* end date request	 */
		jQuery('#end_date').live('change',function(){
			clearTimeout(typingTimer);
			 typingTimer = setTimeout(doneTyping, doneTypingInterval,'end_date');
		});
			
			/* 
			Name :tmpl_check_form_field_values
			description : check if all fields are empty then remove search filter list from above listing
			*/
			
			function tmpl_check_form_field_values()
			{
				jQuery('#searchfilterform input[type="text"]').each(function(){
				
				var id = jQuery(this).attr('id');
				var filter_title = jQuery(this).attr('filterdisplayname');
				var value = jQuery(this).val();
				if(id != '' && filter_title != '') /* if filter title is not blank then add a filter to filter listing */
				{
					
					if (jQuery('#selectedFilters #filter-group-'+id).length == 0 )
					{
						jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="RTitle" onclick="delFltr(\''+id+'\')">'+value+'<i class="fa fa-times-circle"></i></a></span></div>');
					}
					else
						{
							jQuery('#filter-group-'+id+' span a').html(value+'<i class="fa fa-times-circle"></i>');
						}
					
					if(jQuery('#searchfilterform #'+id).val() == '')
					{
						jQuery('#selectedFilters #filter-group-'+id).remove(); 
					}
					if(id == 'search_within')	
					{
						if(jQuery('#selectedFilters #'+id).val() == '')
						{
							jQuery('#selectedFilters #filter-group-'+id).remove(); 
						}
					}
			
				}
				else
					{
						jQuery('#selectedFilters #filter-group-'+id).remove();
				}
				
				});
				
				/* check if textfields are empty */
				var empty_textbox = 0;
				jQuery('#searchfilterform :text').each(function(){ 
					if( jQuery.trim(jQuery(this).val()) == "" ) empty_textbox++ ;
					
				});
				
				/* check if checkboxes are empty */
				var empty_checkbox = 0;
				jQuery('#searchfilterform :checkbox').each(function(){ 
					if( jQuery(this).prop('checked') == false ) empty_checkbox++ ;
					
				});
				
				/* check if selectboxes are empty */
				var empty_selectbox = 0;
				jQuery('#searchfilterform select').each(function(){ 
					if( jQuery(this).val() == "" ) empty_selectbox++ ;
					
				});

				/* if all fields are empty then remove search filter list from above listing */				
				if((empty_textbox == jQuery('#searchfilterform :text').length) && (empty_checkbox == jQuery('#searchfilterform :checkbox').length) && (empty_selectbox == jQuery('#searchfilterform select').length))
				{
					jQuery('.filter_list_wrap').hide(); return false;
				}
			}
		
												
		/* Add google Map marker */
		function templ_add_googlemap_markers_onmap(markers){
			mgr = new MarkerManager( map );	
			if (markers && markers.length > 0) {
				for (var i = 0; i < markers.length; i++) {	
					var details = markers[i];
					var pippoint_effects='click';
					var image = new google.maps.MarkerImage(details.icons);
					var myLatLng = new google.maps.LatLng(details.location[0], details.location[1]);
					
					markers[i] = new google.maps.Marker({ title: details.name, position: myLatLng, icon: image });
					markerArray.push(markers[i]);
					
					tmpl_attachMessage(markers[i], details.message);
					bounds.extend(myLatLng);
					var pinpointElement = document.getElementById( 'pinpoint_'+details.pid );
					if ( pinpointElement ) { 
						if(pippoint_effects=='hover'){
							google.maps.event.addDomListener( pinpointElement, 'mouseover', (function( theMarker ) {								
							 return function() {
								google.maps.event.trigger( theMarker, 'click' );
							 };
						  })(markers[i]) );
						}else if(pippoint_effects=='click'){
							google.maps.event.addDomListener( pinpointElement, 'click', (function( theMarker ) {
							 return function() {
								google.maps.event.trigger( theMarker, 'click' );
							 };
						  })(markers[i]) );
							
						}// Pinpoint click
						
					}// pinpointElement
				}
			}// markers if condition

			google.maps.event.addListener(mgr, 'loaded', function() {
				mgr.addMarkers( markerArray, 0 );
				mgr.refresh();
			});
			
			/* Set marker cluster on google map */
			if(clustering !=1){
				markerClusterer = new MarkerClusterer(map, markers,{maxZoom: 0,gridSize: 40,styles: null,infoOnClick: 1,infoOnClickZoom: 18,});
			}
		}

		// but that message is not within the marker's instance data 
		function tmpl_attachMessage(marker, msg) {
			var myEventListener = google.maps.event.addListener(marker, 'click', function() {						
				 infoBubble.setContent( msg );	
				 infoBubble.open(map, marker);
			});
		}
		/* Delete google Map marker */
		function tmpl_googlemaplisting_deleteMarkers() {
			if (markerArray && markerArray.length > 0){
				for (i in markerArray){
					if (!isNaN(i)){
						markerArray[i].setMap(null);
						infoBubble.close();
					}
				}
				markerArray.length = 0;
			}
			mgr.clearMarkers();
			if(clustering !=1){
				markerClusterer.clearMarkers();
			}
		}
		/* categories request */	
		jQuery('.sf_checkcats').live('click', function(event, ui) {
			clearTimeout(typingTimer);
			typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_checkcats');
		});
		
		/* rating request */	 
		jQuery('.sf_sfrate').live('click', function(event, ui) {
			clearTimeout(typingTimer);
			typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_sfrate');
		});
		
		/* filter with pagination */
		jQuery('#listpagi .page-numbers').live('click',function(e){
				e.preventDefault();
				var page_link = jQuery(this).attr('href');
				if(page_link)
				{
					<?php
					/* get search result page post type */
					if(isset($_REQUEST['search_template']) && $_REQUEST['search_template'] == 1 )
					{
						$search_page_post_type = $_REQUEST['post_type']; 
						if($search_page_post_type != $post_type)
						{
							?>
								alert("<?php _e('Wrong criteria for filter.',SF_DOMAIN); ?>");
								return false;
							<?php 
						}
					}
					?>
						
					var miles_range=jQuery('#sf_radius_range').val();
					var alpha_sort =  jQuery('#alpha_sort').val();
					<?php
					if(is_search())
					{
						
						?>
						var list_id='loop_listing_archive';
						<?php
						
					}
					else
					{
						?>
						var list_id='<?php echo $list_id?>';
						<?php
					}
					if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
						?>						
						if(page_link.indexOf("page/")=='-1'){
							var split_link = page_link.split('&');
							split_link=split_link[1].split('=');
							var page_num = split_link[1];
						}else{
							var split_link = page_link.split('/');
							var page_num = split_link[split_link.length-2];
						}
						<?php
					}else{
						?>
						
						if(page_link.indexOf("page/")=='-1'){
							var split_link = page_link.split('?');
							split_link=split_link[1].split('=');
							var page_num = split_link[1];
						}else{
							var split_link = page_link.split('/');
							var page_num = split_link[split_link.length-2];
						}
						
						<?php
						
					}
					?>
					/* seperates the link by '/' because page number is in the link */
					//var split_link = page_link.split('/');
					/* get page number which is second last element in split link */
					
					
					//var list_id='<?php echo $list_id?>';
						jQuery('.'+list_id+'_process').remove();
						jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );
						jQuery.ajax({
							url:TmplajaxUrl,
							type:'POST',
							cache: true,			
							data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>&page_num='+page_num+'&alpha_sort='+alpha_sort,
							success:function(results){					
								jQuery('.'+list_id+'_process').remove();
								jQuery('#listpagi').remove();
								
								jQuery('#'+list_id).html(results);
								tmpl_check_form_field_values();
							}
						});
						
				}
				else
				{
					return false;
				}
				
			});
		
		jQuery('#directory_sort_order_alphabetical ul li a').live('click',function(e){
				e.preventDefault();
				jQuery(this).parent().parent().find('.active').removeClass('active');
				
				jQuery(this).parent().addClass('active');
				jQuery('#alpha_sort').val(jQuery(this).text());
				var alpha_sort =  jQuery('#alpha_sort').val();
				var miles_range=jQuery('#sf_radius_range').val();
					<?php
					if(is_search())
					{
						
						?>
						var list_id='loop_listing_archive';
						<?php
						
					}
					else
					{
						?>
						var list_id='<?php echo $list_id?>';
						<?php
					}
					?>
					jQuery('.'+list_id+'_process').remove();
					jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );
					jQuery.ajax({
						url:TmplajaxUrl,
						type:'POST',
						cache: true,			
						data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>&alpha_sort='+alpha_sort,
						success:function(results){					
							jQuery('.'+list_id+'_process').remove();
							jQuery('#listpagi').remove();
							jQuery('#'+list_id).html(results);
						}
					});
					/* Ajax request for locate the location on map */
					jQuery.ajax({
							url:TmplajaxUrl,
							type:'POST',			
							data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>&alpha_sort='+alpha_sort,
							dataType: 'json',
							success:function(results){						
								tmpl_googlemaplisting_deleteMarkers();
								templ_add_googlemap_markers_onmap(results.markers);
							}
						});
			
			});
			
/* alphabaticle list for sorting */
var alphabts = '';
alphabts += '<div id="directory_sort_order_alphabetical" class="sort_order_alphabetical">';
alphabts += '<input type="hidden" name="alpha_sort" id="alpha_sort" />';
alphabts += '<ul>';
	alphabts += '<li class="active"><a href="javascript:void(0)"><?php _e('All',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('A',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('B',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('C',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('D',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('E',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('F',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('G',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('H',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('I',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('J',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('K',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('L',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('M',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('N',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('O',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('P',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('Q',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('R',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('S',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('T',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('U',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('V',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('W',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('X',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('Y',SF_DOMAIN);?></a></li>';
	alphabts += '<li><a href="javascript:void(0)"><?php _e('Z',SF_DOMAIN);?></a></li>';
alphabts += '</ul>';
alphabts += '</div>';	


/* Search within results */
jQuery('#search_within').bind('keyup', function(event, ui) {				
	clearTimeout(typingTimer);
	typingTimer = setTimeout(doneTyping, doneTypingInterval,'search_within');	
});
	
/* Sorting listings by clicking on sorting options from above listings */
/* remove previous event */	
jQuery('#directory_sortby').attr('onchange','').unbind('change');	
jQuery('#directory_sortby').live('change',function (e){
	e.preventDefault();						
	var sort_by = jQuery(this).val();
	if( !sort_by ){ return false; }
	/* set sorting value to the hidden field in to filter form */
	jQuery('#searchfilterform #sortby').val(sort_by);
	
	<?php
	if(is_search())
	{
		
		?>
		var list_id='loop_listing_archive';
		<?php
		
	}
	else
	{
		?>
		var list_id='<?php echo $list_id?>';
		<?php
	}
	?>	
	/* if sort by alphabets then show alphabets */
	if(sort_by == 'alphabetical')
	{

		if(jQuery('#content #directory_sort_order_alphabetical').length == 0)
			jQuery('#'+list_id).before(alphabts);

		return false;	
	}
	else
	{
		if(jQuery('#content #directory_sort_order_alphabetical').length > 0)
			jQuery('#directory_sort_order_alphabetical').remove();
	}

	var miles_range=jQuery('#sf_radius_range').val();	
	
	/* remove process block */
	jQuery('.'+list_id+'_process').remove();
	
	/* show process image */
	jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );

	jQuery.ajax({
		url:TmplajaxUrl,
		type:'POST',
		cache: true,			
		data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
		success:function(results){	
			jQuery('.'+list_id+'_process').remove();
			jQuery('#listpagi').remove();
			jQuery('#'+list_id).html(results);
		}
	});
	/* Ajax request for locate the location on map */
	jQuery.ajax({
		url:TmplajaxUrl,
		type:'POST',			
		data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
		dataType: 'json',
		success:function(results){						
			tmpl_googlemaplisting_deleteMarkers();
			templ_add_googlemap_markers_onmap(results.markers);
		}
	});
});
			
			/* 
			Name :doneTyping
			description : do filter after every request
			parameter : 'id' - execute filter above result listing according this id 
			*/
			function doneTyping(id)
			{
				<?php
				/* get search result page post type */
				if(isset($_REQUEST['search_template']) && $_REQUEST['search_template'] == 1 )
				{
					$search_page_post_type = $_REQUEST['post_type']; 
					if($search_page_post_type != $post_type)
					{
						?>
							alert("<?php _e('Wrong criteria for filter.',SF_DOMAIN); ?>");
							return false;
						<?php 
					}
				}
			?>
				
			var miles_range=jQuery('#sf_radius_range').val();
			var alpha_sort =  jQuery('#alpha_sort').val();
			<?php
			if(is_search())
			{
				
				?>
				var list_id='loop_listing_archive';
				<?php
				
			}
			else
			{
				?>
				var list_id='<?php echo $list_id?>';
				<?php
			}
			?>
			var sort_by = jQuery('#directory_sortby').val(); /* sort by value */
			var search_within = jQuery('#search_within').val(); /* search within value	*/
			jQuery('.'+list_id+'_process').remove();
			jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i>" );			
			jQuery.ajax({
				url:TmplajaxUrl,
				type:'POST',		
				data: 'action=search_filter<?php echo $query_string; ?>&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'&search_within='+search_within+'&alpha_sort='+alpha_sort,
				cache: true,
				success:function(results){
					jQuery('.'+list_id+'_process').remove();
					<?php
					if(is_search())
					{
						?>
							jQuery('#tmpl-search-results').remove();
						<?php
					}
					?>
					jQuery('.pagination').remove();
					jQuery('#listpagi').remove();
					jQuery('#'+list_id).html(results);
					
					if(id == 'search_within')
					{
					   var filter_title = jQuery('#'+id).attr('filterdisplayname'); /* get filter label */
					   var value = jQuery('#'+id).val(); /* get filter value */
					}
					else
					{
						var filter_title = jQuery('#searchfilterform #'+id).attr('filterdisplayname'); /* get filter label */
						var value = jQuery('#searchfilterform #'+id).val(); /* get filter value */
					}
					
					
					if(id == 'sf_sfrate') /* if rate is checked then add filter to filter listing */
					{
						var atLeastOneIsChecked = jQuery('#searchfilterform input[name="rate[]"]:checked').length; /* get checked reates length */
						if(atLeastOneIsChecked > 0) 
						{
							 filter_title = jQuery('#searchfilterform .sf_sfrate').attr('filterdisplayname'); /* get title of filter */
							 jQuery('#searchfilterform input[name="rate[]"]:checked').each(function() {
									  chkId = jQuery(this).val();
									  
									  if (jQuery('#selectedFilters #filter-group-'+id).length == 0 ) /* creates a block for ratiing of block is not created before */
										{
											jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a></span></div>');
										}
										else    /* otherwise add a value to created block  */
										{
											if (jQuery('#filter-group-'+id+' span #'+id+'_'+chkId).length == 0 )
											{
												jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a>');
											}
											else
											{
												jQuery('#searchfilterform input[name="rate[]"]').each(function(){
														var curr_val = jQuery(this).val();
														if(jQuery(this).attr('checked') == 'checked')
														{
															jQuery('#filter-group-'+id+' span a').remove();
															jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a>');
														}
																													
													});
											}
										}
									  
									});
									
						}
						else{
							
								jQuery('#selectedFilters #filter-group-'+id).remove(); 
							
						}
					}
					else if(id == 'sf_checkcats')  /* if category is removed from  filter listing */
					{
						var atLeastOneIsChecked = jQuery('#searchfilterform input[name="cats[]"]:checked').length;
						if(atLeastOneIsChecked > 0)
						{
							filter_title = jQuery('#searchfilterform .sf_checkcats').attr('filterdisplayname');
							 jQuery('#searchfilterform input[name="cats[]"]:checked').each(function() {
									  chkId = jQuery(this).val(); /* get category id */
									  catname = jQuery(this).attr('filterdispvalue'); /* get category sname */
									  if (jQuery('#selectedFilters #filter-group-'+id).length == 0 ) /* creates a block for ratiing of block is not created before */
										{
											jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+catname+'<i class="fa fa-times-circle"></i></a></span></div>');
										}
										else  /* otherwise add a value to created block  */
										{
											
											if (jQuery('#filter-group-'+id+' span #'+id+'_'+chkId).length == 0 )
											{
												jQuery('#filter-group-'+id+' span').append('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+catname+'<i class="fa fa-times-circle"></i></a>');
											}
											else
											{
												jQuery('#searchfilterform input[name="cats[]"]').each(function(){
														var curr_val = jQuery(this).val();
														if(jQuery(this).attr('checked') == 'checked')
														{
															jQuery('#filter-group-'+id+' span a').remove();
															jQuery('#filter-group-'+id+' span').append('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+catname+'<i class="fa fa-times-circle"></i></a>');
														}																
													});
											}		
										}
									});
						}
						else{
							
								jQuery('#selectedFilters #filter-group-'+id).remove(); 
							
						}
					}
					else
					{
					if(id != '') /* if filter title is not blank then add a filter to filter listing */
						{
							
							if (jQuery('#selectedFilters #filter-group-'+id).length == 0 )
							{
								jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="RTitle" onclick="delFltr(\''+id+'\')">'+value+'<i class="fa fa-times-circle"></i></a></span></div>');
							}
							else
								{
									jQuery('#filter-group-'+id+' span a').html(value+'<i class="fa fa-times-circle"></i>');
								}
							
							if(jQuery('#searchfilterform #'+id).val() == '')
							{
								jQuery('#selectedFilters #filter-group-'+id).remove(); 
							}
							if(id == 'search_within')	
							{
								if(jQuery('#selectedFilters #'+id).val() == '')
								{
									jQuery('#selectedFilters #filter-group-'+id).remove(); 
								}
							}
					
						}
					else
						{
							jQuery('#selectedFilters #filter-group-'+id).remove();
						}
					}
				jQuery('.filter_list_wrap').show();
				jQuery('#selectedFilters').show();
				tmpl_check_form_field_values();	
				}
			});
			
			/* Ajax request for locate the location on map */
			jQuery.ajax({
					url:TmplajaxUrl,
					type:'POST',			
					data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'&search_within='+search_within+'&alpha_sort='+alpha_sort+'<?php echo $query_string; ?>',
					dataType: 'json',
					success:function(results){						
						tmpl_googlemaplisting_deleteMarkers();
						templ_add_googlemap_markers_onmap(results.markers);
					}
				});

			}
		
			
			
			
			/* 
			Name :delFltr
			description : removal of filter from filter listing and show result according to it
			parameter : 'str' - removal according to this argument
			*/
			function delFltr(str)
			{
			
				var miles_range=jQuery('#sf_radius_range').val();
				var list_id='<?php echo $list_id?>';	
				
				jQuery('.'+list_id+'_process').remove();
				jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );
				
		
				jQuery('#searchfilterform #'+str).val('');
				jQuery.ajax({
					url:TmplajaxUrl,
					type:'POST',
					cache:true,
					data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
					success:function(results){						
						jQuery('.'+list_id+'_process').remove();
						jQuery('#listpagi').remove();
						jQuery('#'+list_id).html(results);
						jQuery('#filter-group-'+str).remove();
					}
				});
				
				/* Ajax request for locate the location on map */
				jQuery.ajax({
						url:TmplajaxUrl,
						type:'POST',			
						data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
						dataType: 'json',
						success:function(results){						
							tmpl_googlemaplisting_deleteMarkers();
							templ_add_googlemap_markers_onmap(results.markers);
						}
					});
			}
			
			/* 
			Name :delFltrChkbox
			description : removal of filter from filter listong and show result according to it
			parameter : 'val' - value of removal, 'type' - type of removal
			*/
			function delFltrChkbox(val,type)
			{
				if(type == 'sf_sfrate')
				{
				 jQuery('#searchfilterform input[name="rate[]"]').each(function() {
						if(jQuery(this).val() == val)
						{
							jQuery(this).attr('checked', false); /* uncheck a checkbox of rate according to removal */
						}
				 });
				}
				if(type == 'sf_checkcats')
				{
				 jQuery('#searchfilterform input[name="cats[]"]').each(function() {
						if(jQuery(this).val() == val)
						{
							jQuery(this).attr('checked', false); /* uncheck a checkbox of categories according to removal */
						}
				 });
				}
				
				var miles_range=jQuery('#sf_radius_range').val(); /* get the range */
				var list_id='<?php echo $list_id?>';	 /* get the page id */
				jQuery('.'+list_id+'_process').remove(); /* remove the element */
				jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );  /* show processing image during ajax request. */
				jQuery.ajax({
					url:TmplajaxUrl,
					type:'POST',
					cache:true,								
					data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
					success:function(results){						
						jQuery('.'+list_id+'_process').remove(); 
						jQuery('#listpagi').remove();
						jQuery('#'+list_id).html(results);
						if(jQuery('#filter-group-'+type+' .value a').length > 1)
							{
								jQuery('#'+type+'_'+val).remove();
							}
						else
							{
								jQuery('#filter-group-'+type).remove();
							}
					}
				});
				/* Ajax request for locate the location on map */
				jQuery.ajax({
						url:TmplajaxUrl,
						type:'POST',			
						data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
						dataType: 'json',
						success:function(results){						
							tmpl_googlemaplisting_deleteMarkers();
							templ_add_googlemap_markers_onmap(results.markers);
						}
					});
			}
			
			/* clear all filters */
			jQuery('#clear_filter').live('click',function(){
				
				jQuery('#tmpl_event_date').val('');
				jQuery('#search_from').val('');
				jQuery('#searchfilterform').each (function(){
				 this.reset();				  /* resets tha search filter form	*/				   
				}); 
				var miles_range=jQuery('#sf_radius_range').val();
				<?php
				if(is_search())
				{
					
					?>
					var list_id='loop_listing_archive';
					<?php
					
				}
				else
				{
					?>
					var list_id='<?php echo $list_id?>';
					<?php
				}
				?>	
				jQuery('.'+list_id+'_process').remove();
				jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );
				jQuery.ajax({
					url:TmplajaxUrl,
					type:'POST',	
					cache:true,
					data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
					success:function(results){
						jQuery('#selectedFilters').hide();
						jQuery('.filter_list_wrap').hide();						
						jQuery('.'+list_id+'_process').remove();
						jQuery('#listpagi').remove();
						jQuery('#'+list_id).html(results);
						jQuery('#selectedFilters .flit-opt-cols').remove(); /* remove all filters from filter listing  */
					}
				});
				/* Ajax request for locate the location on map */
				jQuery.ajax({
						url:TmplajaxUrl,
						type:'POST',			
						data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
						dataType: 'json',
						success:function(results){						
							tmpl_googlemaplisting_deleteMarkers();
							templ_add_googlemap_markers_onmap(results.markers);
						}
					});
				
				});
		
		
				function filter_search_fields_nearby(fid,fname,fields_div,fclass){
						var list_id = 'custom_field_part';
						
						var cat_length = jQuery('#'+fields_div).find('input[name="cats[]"]:checked').length;
						
						if(cat_length ==0){
							jQuery('.'+fclass).show();
						}else{
							jQuery('.'+fclass).hide();
						}
						
						jQuery('#'+fields_div).show();
						jQuery('#search_from').val(fid);
						jQuery('.'+list_id+'_process').remove();
						jQuery('#'+list_id ).prepend( "<p class='custom_field_part_process' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );	
						jQuery.ajax({
						url:TmplajaxUrl,
						type:'POST',
						data:'action=filter_searchable_fields_front_end&post_types='+fid+'&fid='+fname,
						cache:true,
						success:function(results) {
							jQuery('.custom_field_part_process').remove();
							jQuery('#custom_field_part').html(results);
						}
						});
				}
		</script>
		
		<?php
				
		add_action('admin_footer','filter_searchable_scripts'); /* get fields for search according to custom post type */
		wp_reset_query();
		//echo $args['after_widget'];  /* end of widget */
	}
	
	
	/**
	 * Sanitize widget form values as they are saved.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		return $new_instance;
	}

	/**
	 * Back-end widget form.
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Search By Filters',SF_DOMAIN) ,'post_type' => 'post') );		
		$title = strip_tags($instance['title']);
		$cat = $instance['sf_cats'];
		$tags = $instance['sf_tags'];
		$rating = $instance['sf_rating'];
		$distance = $instance['sf_distance'];
		$search_criteria = $instance['sf_search_criteria'];
		$post_type = $instance['sf_post_type'];
		$max_range = $instance['sf_max_range'];
		$search_cc = $instance['sf_search_in_current_city'];
		$event_date = $instance['sf_event_date'];
		if(empty($search_criteria)){ $search_criteria =array(); }
		if(empty($search_in_city)){ $search_in_city =array(); }	
		wp_enqueue_script('abc_shop_categories_widget_js',  array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), '', true);
		wp_enqueue_style('abc_shop_categories_widget_css', plugin_dir_url(__FILE__) . 'css/jquery-ui-1.8.21.custom.css');
		
		?>
		<script>
		jQuery(document).ready(function() {
		jQuery('#<?php echo $this->get_field_id('sf_search_criteria'); ?>').sortable({
			items:'p',
			cursor:'move',
			axis:'y',
			//handle: 'td',
			scrollSensitivity:40,
			helper:function(e,ui){
				ui.children().each(function(){
					jQuery(this).width(jQuery(this).width());
				});
				ui.css('left', '0');
				return ui;
			},
			start:function(event,ui){
				ui.item.css('background-color','#f6f6f6');
			},
			update: function(event, ui){
				//var custom_sort_order = jQuery("#post_custom_fields :input").serialize();
				var custom_sort_order = jQuery('#<?php echo $this->get_field_id('sf_search_criteria'); ?> :input').serialize();	
				jQuery.ajax({
						 url: ajaxurl,
						 type: 'POST',
						 data:'action=tmpl_filter_custom_field_sortorder&custom_sort_order=' + custom_sort_order,		 
						 success:function(result){
						 }
					 });	
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
			}
		});
		});
		/* fields sorting */

		</script>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:',SF_DOMAIN ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('sf_post_type');?>" ><?php echo __('Select Post Type',SF_DOMAIN);?>:    </label>	
			<select  id="<?php echo $this->get_field_id('sf_post_type');?>" name="<?php echo $this->get_field_name('sf_post_type'); ?>[]" class="widefat" onchange="filter_search_fields(this.id,'<?php echo $this->get_field_name('sf_search_criteria'); ?>','<?php echo $this->get_field_id('sf_search_criteria'); ?>','<?php echo $instance; ?>','<?php echo $this->get_field_id('sf_event_date'); ?>1');">        	
				<?php
                    $all_post_types = apply_filters('templatic_custom_posttype',get_option("templatic_custom_post"));
                    
                     /* $all_post_types = get_post_types(array('public'   => true,'_builtin' => false)); */
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if(is_plugin_active('Tevolution/templatic.php'))
                    {  
						if(empty($post_type)){ $post_type = array('post','listing');  }
					}
					else
						{
							if(empty($post_type)){ $post_type = array('post','post');  }
						}
					
                    foreach($all_post_types as $key=>$post_types){
						if($key !=''){
					?>
						<!--<option value="<?php echo $key;?>" <?php if(is_array($post_type) && in_array($key,@$post_type))echo "selected";?>><?php echo esc_attr($post_types);?></option>-->
						<option value="<?php echo $key;?>" <?php if(is_array($post_type) && in_array($key,@$post_type))echo "selected";?>><?php echo esc_attr($post_types['label']);?></option>
					<?php
                    } }
                    ?>	
			</select>
		</p>

		<p> <p id='search_process_<?php echo $this->get_field_name('sf_search_criteria'); ?>' style='display:none;'><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p><label for="<?php echo $this->get_field_id('sf_search_criteria'); ?>"><?php echo __('Enable Filters for',SF_DOMAIN);?>: </label> </p>
		<?php  
			if(in_array('event',$post_type)){
				$style = "";
			}else{
				$style="style=display:none;";
			}
		?>
		 <p id ='<?php echo $this->get_field_id('sf_event_date'); ?>1' <?php echo $style; ?>>
			<label for="<?php echo $this->get_field_id('sf_event_date'); ?>">
					<input id="<?php echo $this->get_field_id('sf_event_date'); ?>" name="<?php echo $this->get_field_name('sf_event_date'); ?>" type="checkbox" value="1" <?php if(isset($event_date) && $event_date == 1){ ?>checked=checked<?php } ?>style="width:10px;"  /><b><?php echo __('Event calender',SF_DOMAIN);?></b>             
            </label>
        </p> 
		
		<p>      
               <?php 
               
				$tmpl_settings = get_option('templatic_settings');
				
				$rattings = $tmpl_settings['templatin_rating'];
                if((isset($rattings) && $rattings == 'yes') || is_plugin_active('Templatic-MultiRating/multiple_rating.php')):     /* if ratting is enable then show this option */
               ?>
				<p> <label for="<?php echo $this->get_field_id('sf_rating'); ?>">
					<input id="<?php echo $this->get_field_id('sf_rating'); ?>" name="<?php echo $this->get_field_name('sf_rating'); ?>" type="checkbox" value="1" <?php if(isset($rating) && $rating == 1){ ?>checked=checked<?php } ?>style="width:10px;"  /><b><?php echo __('Ratings',SF_DOMAIN);?></b>
					</label></p>
			   <?php endif; ?>
			   
			<p> <label for="<?php echo $this->get_field_id('sf_distance'); ?>">
					<input onclick="show_hide_max_range(this.id)" id="<?php echo $this->get_field_id('sf_distance'); ?>" name="<?php echo $this->get_field_name('sf_distance'); ?>" type="checkbox" value="1" <?php if(isset($distance) && $distance == 1){ ?>checked=checked<?php } ?>style="width:10px;"  /><b><?php echo __('Distance',SF_DOMAIN);?></b>
				</label></p>	
            
         <p id="sf_<?php echo $this->get_field_id('sf_distance'); ?>" style="display:none">
			<label for="<?php echo $this->get_field_id( 'sf_max_range' ); ?>"><?php _e( 'Max Range:',SF_DOMAIN ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'sf_max_range' ); ?>" name="<?php echo $this->get_field_name( 'sf_max_range' ); ?>" type="text" value="<?php echo esc_attr( $max_range ); ?>">
		 </p>
		<?php if(is_plugin_active('Tevolution-LocationManager/location-manager.php')) { ?>
		 <p> 
			<label for="<?php echo $this->get_field_id('sf_search_in_current_city'); ?>">
					<input id="<?php echo $this->get_field_id('sf_search_in_current_city'); ?>" name="<?php echo $this->get_field_name('sf_search_in_current_city'); ?>" type="checkbox" value="1" <?php if(isset($search_cc) && $search_cc == 1){ ?>checked=checked<?php } ?>style="width:10px;"  /><b><?php echo __('Limit search to the current city',SF_DOMAIN);?></b>
			</label>
		</p> 
		<?php } ?>
				<?php /* Get other cutom fields */ 
				echo '<p><label>'. __('Other Custom Fields',SF_DOMAIN).': </label></p>';
				?>
		 		<div class="searchable_fields" id="<?php echo $this->get_field_id('sf_search_criteria'); ?>">
				<?php 
				$post_types = $post_type;
				$custom_fileds_ = array();
				for($i=0; $i <= count($post_types); $i++){
					if($post_types[$i] !=''){
						/* gets the custom fields */
						$default_custom_metaboxes = array_filter(get_filter_search_post_fields($post_types[$i],'custom_fields',$post_types));
						foreach($default_custom_metaboxes as $key=>$val) {
										$name = $val['name'];
										$site_title = $val['label'];
										$type = $val['type'];
										$htmlvar_name = $val['htmlvar_name'];
										if(in_array($htmlvar_name,$search_criteria)){
											$checked = "1";
										}else{
											$checked = "";
										}
										$custom_fileds_[$htmlvar_name] = array(
													 'fid'=>$val['ID'],
													 'ID'=>$this->get_field_id("sf_search_criteria_".$val['htmlvar_name']),
													 'name'=>$this->get_field_name('sf_search_criteria'),
													 'htmlvar_name'=>$htmlvar_name,
													 'site_title' => $val['label'],
													 'checked' =>$checked);
													}
												}
				} 
				
				$custom_fileds_se  = array_filter($custom_fileds_);
				if(count($custom_fileds_se) == 0)
				{
					echo '<p><a href="'.site_url().'/wp-admin/admin.php?page=custom_setup&ctab=custom_fields&action=addnew#show_as_filter">'.__('Click Here',SF_DOMAIN).'</a>'.__(' to set your custom field as \'show as filter\' to add in filter',SF_DOMAIN).'</p>';
				}
				$c = 1;
				
				/* add fields which are not allow as filter fields */
				$not_allowto_filter = apply_filters('tmpl_notto_filter',array('post_coupons',' '));
				if(!empty($custom_fileds_se)){
					foreach($custom_fileds_se as $key=>$val) {
						$name = $val['name'];
						$site_title = $val['site_title'];
						
						$htmlvar_name = $val['htmlvar_name'];
						
						if($site_title ==''){
							$site_title = $htmlvar_name."( ".__('Var Name',SF_DOMAIN)." )";
						}
						
						$fid = $val['fid'];
						$id = $val['ID'];
						$checked = $val['checked'];
						if($checked ==1){ $checked = "checked=checked"; }else{ $checked = ''; }
								if(($htmlvar_name == 'st_time' || $htmlvar_name == 'end_time') && ($post_types[0] != 'event'))
								{
									continue;
								}	
						if(!in_array($htmlvar_name, $not_allowto_filter)){
							echo "<p id=\"$htmlvar_name\"><label for=".$id."><input id=".$id." name='".$name."[]' type='checkbox' value='".$htmlvar_name."' ".$checked."/><b>".$site_title	."</b></label><input type='hidden' name='custom_sort_order[]' value='" . esc_attr( $fid ) . "'/></p>";
						}
						$c++;
					}
					
				} ?>
			</div>	
			<?php echo '<p>'. __('Use drag and drop to edit the order in which custom filters are shown inside the widget.',SF_DOMAIN).'</p>'; 

			?>
			<!-- search in  city -->			
            
            <script> /* for maximum range field show hide as per distance field is checked or not */
			
				function show_hide_max_range(id){
					
					if(jQuery('#'+id).is(':checked'))
						{
							jQuery('#sf_'+id).show();
						}
					else
						{
							jQuery('#sf_'+id).hide();
						}
					
				}
				
				jQuery(document).ready(function(){
					
					if(jQuery("#<?php echo $this->get_field_id('sf_distance'); ?>").is(':checked'))
						{
							//jQuery(".widget-content #max_range").show();
							jQuery('#sf_<?php echo $this->get_field_id('sf_distance'); ?>').show();
						}
					else
						{
							//jQuery(".widget-content #max_range").hide();
							jQuery('#sf_<?php echo $this->get_field_id('sf_distance'); ?>').hide();
						}
					
					
                                         
                    /* if event date is selected then don't show start date and end date in widget  */
					if(jQuery("#<?php echo $this->get_field_id('sf_event_date'); ?>").is(':checked'))
						{
							jQuery("#st_date").hide();
							jQuery("#end_date").hide();
						}
					else
						{
							jQuery("#st_date").show();
							jQuery("#end_date").show();
						}
						
					jQuery("#<?php echo $this->get_field_id('sf_event_date'); ?>").click(function(){
						
					if(jQuery("#<?php echo $this->get_field_id('sf_event_date'); ?>").is(':checked'))
						{
							jQuery("#st_date").hide();
							jQuery("#end_date").hide();
						}
					else
						{
							jQuery("#st_date").show();
							jQuery("#end_date").show();
						}	
					});
					/* end for show start date and end date */		
	
					
				});
				
			</script>
		 	
		<?php 
		add_action('admin_footer','filter_searchable_scripts'); /* get fields for search according to custom post type */
	}


}

/* for get custom fields */
add_action('wp_ajax_filter_searchable_fields','filter_searchable_fields');
add_action('wp_ajax_filter_searchable_fields_front_end','filter_searchable_fields_front_end');
add_action('wp_ajax_nopriv_filter_searchable_fields_front_end','filter_searchable_fields_front_end');


/* 
Name :filter_searchable_fields_front_end
description : Returns custom fields on front end as we checked "Show as a Filter" in custom fields
*/
function filter_searchable_fields_front_end($post_types)
{
	$post_types = $_REQUEST['post_types'];
	$fname = $_REQUEST['fid'];
	$post_types = explode(',',$_REQUEST['post_types']);
	$fields = '';
	$custom_fileds = array();
	for($i=0; $i < count($post_types); $i++){
		
		if($post_types[$i] !=''){
			$default_custom_metaboxes = get_filter_search_post_fields($post_types[$i],'custom_fields',$post_types); 
			
			foreach($default_custom_metaboxes as $key=>$val) {
							$name = $val['name'];
							$site_title = $val['label'];
							$type = $val['type'];
							$htmlvar_name = $val['htmlvar_name'];
							
							$custom_fileds[$htmlvar_name] = array('name'=>$val['htmlvar_name'],
													 'ID'=>"sf_search_criteria_".$htmlvar_name,	
													 'site_title' => $val['label'],
													 'fid'=> $fname ,
													);									
			}
		}
	}
	if(!empty($custom_fileds)){	
		$custom_fileds1 = array_filter($custom_fileds);

        $list_id='loop_listing_taxonomy'; 
		$page_type='archive';

	/* get last quried object */
	$queried_object = get_queried_object();   
	
	$term_id = $queried_object->term_id;  
	/* query string for ajax request for term id */
	$query_string .='&term_id='.$term_id; 	
	
	$c=1;
	
	foreach($custom_fileds1 as $key=>$val) {
		$htmlvar_name = $val['name'];
		$site_title = $val['site_title'];
		$ID = $val['fid'];
		$id = $val['ID'];
		
		$custom_field_id = tmpl_get_post_id_by_meta_key_and_value('htmlvar_name', $htmlvar_name); // get custom field id
				$cfield = get_post($custom_field_id);
				$placeholder = $cfield->post_title; /* get field title */
				$custom_field_type= get_post_meta($custom_field_id,'ctype',true); /* get custom field type */
				$htmlvar_name = get_post_meta($custom_field_id,'htmlvar_name',true);
				$default_value = get_post_meta($custom_field_id,'default_value',true);
							
/* if categories is selected from widget, get the categories of custom fiels we selected from widget */
			
		
	}
			
			if($post_types[0] == 'event')
			{
				?>
				<div class="filter ver-list-filter fil-scroll">
					<?php
					 /* include file for calenderSearch from current city */
					 include_once(SEARCH_FILTER_FOLDER_PATH.'calender.php'); ?>
				</div>				
				<?php
			}
	
	foreach($custom_fileds1 as $key=>$val) {
		$htmlvar_name = $val['name'];
		$site_title = $val['site_title'];
		$ID = $val['fid'];
		$id = $val['ID'];
		//$htmlvar_name = $val['htmlvar_name'];
		if($checked ==1){ $checked = "checked=checked"; }else{ $checked = ''; }
		
		
		if(($htmlvar_name == 'st_time' || $htmlvar_name == 'end_time') && ($post_types[0] != 'event'))
		{
			continue;
		}

			$custom_field_id = tmpl_get_post_id_by_meta_key_and_value('htmlvar_name', $htmlvar_name); // get custom field id
			$cfield = get_post($custom_field_id);
			$placeholder = $cfield->post_title; /* get field title */
			$custom_field_type= get_post_meta($custom_field_id,'ctype',true); /* get custom field type */
			$search_ctype= get_post_meta($custom_field_id,'search_ctype',true); /* get custom field type */
			if($search_ctype!=''){
				$custom_field_type=$search_ctype;
			}		
			$htmlvar_name = get_post_meta($custom_field_id,'htmlvar_name',true);
			$default_value = get_post_meta($custom_field_id,'default_value',true);
			$range_min = get_post_meta($custom_field_id,'range_min',true);
			$range_max = get_post_meta($custom_field_id,'range_max',true);
						
			/* if categories is selected from widget, get the categories of custom fiels we selected from widget */
			?>
            <input type="hidden" name="list_filter_search_custom[<?php echo $htmlvar_name;?>]" value="<?php echo $custom_field_type;?>"  />
            <?php
			if($custom_field_type == 'text' && $htmlvar_name != 'post_title') /* if custom field type is text then show text box */
			{
				?>
				<div class="filter ver-list-filter fil-scroll">
					<label><strong><?php echo $placeholder; ?></strong></label>
					<div>
						<?php 
							if($htmlvar_name == 'post_title' )
							{
								?>
								<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="sf_<?php echo $htmlvar_name; ?>" id="sf_<?php echo $htmlvar_name; ?>">
								<?php
							}
							else
							{
								?>
								<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $htmlvar_name; ?>" id="<?php echo $htmlvar_name; ?>">
								<?php
							} 
						?>
						
						<p class="description">
						<?php if($htmlvar_name == 'end_time' || $htmlvar_name == 'st_time' ) 
								{
									_e('Enter event end time. eg. 18:25 (Follows 24 hrs format)',SF_DOMAIN);
								}
						?>
						</p>
					</div>
				</div>
				<script>
					var typingTimer;                /* timer identifier*/
					var doneTypingInterval = 1000;  /* time in ms, 1 second */
						
						jQuery('#searchfilterform input[type="text"]').bind('keyup',function(){
							
								clearTimeout(typingTimer);
								typingTimer = setTimeout(doneTyping, doneTypingInterval,this.id);
								 
							});
				</script>
				<?php	
			}
			elseif($custom_field_type=='multicity'){
			global $wpdb,$country_table,$zones_table,$multicity_table;

			$countryinfo = $wpdb->get_results($wpdb->prepare("SELECT  distinct  c.country_id,c.*  FROM $country_table c,$multicity_table mc where  c.`country_id`=mc.`country_id`  AND c.is_enable=%d group by country_name order by country_name ASC",1));
			if(isset($default_country_id))
				$zoneinfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM $zones_table z, $multicity_table mc where z.zones_id=mc.zones_id AND mc.country_id=%d  order by zone_name ASC",$default_country_id));
			if(isset($default_zone_id)  && isset($default_country_id))
				$cityinfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM $multicity_table where zones_id=$default_zone_id AND country_id=%d order by cityname ASC",$default_country_id)); ?>
						  <div class="form_row clearfix">               
							   <select name="country_id" id="country_id">
									<option value=""><?php _e('Select Country',SF_DOMAIN);?></option>
									<?php foreach($countryinfo as $country): $selected=($country->country_id==$default_country_id)? 'selected':'';
									$country_name=$country->country_name;
									 if (function_exists('icl_register_string')) {									
											icl_register_string('location-manager', 'location_country_'.$country->country_id,$country_name);
											$country_name = icl_t('location-manager', 'location_country_'.$country->country_id,$country_name);
									  }
								?>
									<option value="<?php echo $country->country_id?>" <?php echo $selected;?>><?php echo $country_name;?></option>
									<?php endforeach; ?>
							   </select>
							  
						  </div>
						   <div class="form_row clearfix">               
							   <select name="zones_id" id="adv_zone">
									<option value=""><?php _e('All Regions',SF_DOMAIN);?></option>
									<?php 
									if($zoneinfo){
									foreach($zoneinfo as $zone): $selected=($zone->zones_id ==$default_zone_id)? 'selected':'';
									$zone_name=$zone->zone_name;
									 if (function_exists('icl_register_string')) {									
											icl_register_string('location-manager', 'location_zone_'.$zone->zones_id,$zone_name);
											$zone_name = icl_t('location-manager', 'location_zone_'.$zone->zones_id,$zone_name);
									  }	
								?>
									<option value="<?php echo $zone->zones_id?>" <?php echo $selected;?>><?php echo $zone_name;?></option>
									<?php endforeach;
									} ?>
							   </select>
							  
						  </div>
						   <div class="form_row clearfix">             
							   <select name="post_city_id" id="adv_city">
									<option value=""><?php _e('All Cities',SF_DOMAIN);?></option>
									<?php
									if($cityinfo){
									foreach($cityinfo as $city): $selected=($city->city_id ==$default_city_id)? 'selected':'';
										$cityname=$city->cityname;
										if (function_exists('icl_register_string')) {									
												icl_register_string('location-manager', 'location_city_'.$city->city_slug,$cityname);
												$cityname = icl_t('location-manager', 'location_city_'.$city->city_slug,$cityname);
										} ?>
										<option value="<?php echo $city->city_id?>" <?php echo $selected;?>><?php echo $cityname;?></option>
									<?php endforeach;
									} ?>
							   </select>
							  
						  </div>
						<script>
							var typingTimer;                /* timer identifier*/
							var doneTypingInterval = 1000;  /* time in ms, 1 second */
								
								jQuery('#searchfilterform select').bind('change',function(){
									
										clearTimeout(typingTimer);
										typingTimer = setTimeout(doneTyping, doneTypingInterval);
										 
									});
						</script><?php		
					}elseif($custom_field_type =='radio') 
				{ /* if field type is radio then show radio buttons */ ?>
					<div class="filter ver-list-filter fil-scroll">
						<label><strong><?php echo $placeholder; ?></strong></label><?php				
						$options = get_post_meta($custom_field_id,'option_values',true);				
						$option_titles = get_post_meta($custom_field_id,'option_title',true);
						$ctype = get_post_meta($custom_field_id,'ctype',true);
						if($options)
						{  $chkcounter = 0;
							echo '<div class="form_cat_left">';
							echo '<ul class="hr_input_radio">';
							$option_values_arr = explode(',',$options);
							$label = explode(',',$option_titles);					
							for($i=0;$i<count($option_values_arr);$i++)
							{
								$chkcounter++;
								$seled='';
								if($default_value == $option_values_arr[$i]){ $seled='checked="checked"';}
								if (isset($value) && trim($value) == trim($option_values_arr[$i])){ $seled='checked="checked"';}
								echo '<li>
									
										<input filterdisplayname="'.$placeholder.'" name="'.$htmlvar_name.'"  id="'.$htmlvar_name.'_'.$chkcounter.'" type="radio" value="'.trim($option_values_arr[$i]).'" '.$seled.'  '.$extra_parameter.' /> 
									<label class="r_lbl" for="'.$htmlvar_name.'_'.$chkcounter.'">'.$label[$i].'&nbsp;</label>
								</li>';
							}
							echo '</ul></div>';
						}
						?>
					</div>
					<!-- When selected custom field set as radio button - The filter results( Which display above results ) should display the radio button title not value -->
					<script>
						jQuery('#searchfilterform input[type="radio"]').live('click',function(){
						<?php if($current_post_type != $post_type && !is_search())
							{ ?>alert('You place the widget in wrong/unsupported area.'); return false; <?php } ?>
						 clearTimeout(typingTimer);
						 typingTimer = setTimeout(searchRadiofield, doneTypingInterval,jQuery(this).attr('name'));
						 function searchRadiofield(id)
						 {
							var miles_range=jQuery('#sf_radius_range').val();
							<?php
							if(is_search())
							{
								
								?>
								var list_id='loop_listing_archive';
								<?php
								
							}
							else
							{
								?>
								var list_id='<?php echo $list_id?>';
								<?php
							}
							?>	
							jQuery('.'+list_id+'_process').remove();
							jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );

							jQuery.ajax({
								url:TmplajaxUrl,
								type:'POST',
								cache: true,			
								data: 'action=search_filter<?php echo $query_string; ?>&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize(),
								success:function(results){
                                                                                <?php
										if(is_search())
										{
											?>
												jQuery('.hfeed article').remove();
											<?php
										}
										?>   
										jQuery('.filter_list_wrap').show();
										jQuery('#selectedFilters').show();
										var atLeastOneIsChecked = jQuery('#searchfilterform input[name="'+id+'"]:checked').length; /* get checked reates length */
										if(atLeastOneIsChecked > 0) 
										{
											filter_title = jQuery('#searchfilterform input[name="'+id+'"]').attr('filterdisplayname'); /* get title of filter */
											jQuery('#searchfilterform input[name="'+id+'"]:checked').each(function() {
											chkId = jQuery(this).val();
										 
											if (jQuery('#selectedFilters #filter-group-'+id).length == 0 ) /* creates a block for ratiing of block is not created before */
											{
												jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrRadio(\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a></span></div>');
											}
											else    /* otherwise add a value for created block  */
											{
												if (jQuery('#filter-group-'+id+' span #'+id+'_'+chkId).length == 0 )
												{
													jQuery('#filter-group-'+id+' span').html('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrRadio(\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a>');
												}
												else
												{
													jQuery('#searchfilterform input[name="rate[]"]').each(function(){
													var curr_val = jQuery(this).val();
													if(jQuery(this).attr('checked') == 'checked')
													{
														jQuery('#filter-group-'+id+' span a').remove();
														jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrChkbox('+chkId+',\''+id+'\')">'+chkId+'<i class="fa fa-times-circle"></i></a>');
													}
																													
													});
												}
											}
										});
										}else{
											jQuery('#selectedFilters #filter-group-'+id).remove(); 
										}
												
									jQuery('.'+list_id+'_process').remove();
									jQuery('#listpagi').remove();
									jQuery('#'+list_id).html(results);
									
								}
							});
							/* Ajax request for locate the location on map */
							jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',			
									data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									dataType: 'json',
									success:function(results){						
										tmpl_googlemaplisting_deleteMarkers();
										templ_add_googlemap_markers_onmap(results.markers);
									}
								});
						 }	
						});
							
						function delFltrRadio(id)
						{
							jQuery('#searchfilterform input[name="'+id+'"]').attr('checked', false);
							var miles_range=jQuery('#sf_radius_range').val();
							<?php
							if(is_search())
							{
								
								?>
								var list_id='loop_listing_archive';
								<?php
								
							}
							else
							{
								?>
								var list_id='<?php echo $list_id?>';
								<?php
							}
							?>	
							
							jQuery('.'+list_id+'_process').remove();
							jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );
							
							
							/*jQuery('#searchfilterform input[name="'+id+'"]').val('');*/
							jQuery.ajax({
								url:TmplajaxUrl,
								type:'POST',
								cache:true,
								data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
								success:function(results){						
									jQuery('.'+list_id+'_process').remove();
									jQuery('#listpagi').remove();
									jQuery('#'+list_id).html(results);
									
									jQuery('#filter-group-'+id).remove();
								}
							});
							/* Ajax request for locate the location on map */
							jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',			
									data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									dataType: 'json',
									success:function(results){						
										tmpl_googlemaplisting_deleteMarkers();
										templ_add_googlemap_markers_onmap(results.markers);
									}
								}); 
						}
					</script>
					<!-- End Script --><?php	
				}
					 /* if field type is multicheckbox then show multicheckbox */
					elseif($custom_field_type=='multicheckbox'){ 
						?>
							 <div class="filter ver-list-filter fil-scroll">
								<label><strong><?php echo $placeholder; ?></strong></label>
							<?php					
								 $options = get_post_meta($custom_field_id,'option_values',true);
								$option_titles = get_post_meta($custom_field_id,'option_title',true);
								 if($options)
								 {  $chkcounter = 0;
									  echo '<div class="form_cat_left hr_input_multicheckbox">';
									  $option_values_arr = explode(',',$options);
									  $option_title_arr = explode(',',$option_titles);
									  for($i=0;$i<count($option_values_arr);$i++)
									  {
										   $chkcounter++;
										   $seled='';
										   if($default_value == $option_values_arr[$i]){ $seled='checked="checked"';}
										   if (isset($value) && trim($value) == trim($option_values_arr[$i])){ $seled='checked="checked"';}
										   echo '<div class="form_cat">
												
													 <input name="'.$htmlvar_name.'[]"  id="'.$htmlvar_name.'_'.$chkcounter.'" type="checkbox" value="'.$option_values_arr[$i].'" '.$seled.'  '.$extra_parameter.' filterdispvalue="'.$option_title_arr[$i].'" /> 
												<label class="r_lbl" for="'.$htmlvar_name.'_'.$chkcounter.'">'.$option_title_arr[$i].'</label>
										   </div>';
									  }
									  echo '</div>';
								 }
								?>
								</div>
								<script>
						jQuery('#searchfilterform input[type="checkbox"]').live('click',function(){
						 if(jQuery(this).attr('class')=='sf_checkcats')
							{
									clearTimeout(typingTimer);
									typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_checkcats');
	
							}
							else if(jQuery(this).attr('class')=='sf_sfrate')
							{
									clearTimeout(typingTimer);
									typingTimer = setTimeout(doneTyping, doneTypingInterval,'sf_sfrate');		
							}
							else
							{
								clearTimeout(typingTimer);
								typingTimer = setTimeout(searchMulticheckbox, doneTypingInterval,'<?php echo $htmlvar_name; ?>','<?php echo $placeholder; ?>');
							}
							
						 function searchMulticheckbox(id,title)
						 {
							 
							var miles_range=jQuery('#sf_radius_range').val();
							var list_id='<?php echo $list_id?>';	
							jQuery('.'+list_id+'_process').remove();
							jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );

							jQuery.ajax({
								url:TmplajaxUrl,
								type:'POST',
								cache: true,			
								data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
								success:function(results){
										jQuery('.filter_list_wrap').show();
										jQuery('#selectedFilters').show();
										var atLeastOneIsChecked = jQuery('#searchfilterform input[name="'+id+'[]"]:checked').length; /* get checked reates length */
										if(atLeastOneIsChecked > 0) 
										{
											filter_title = title; /* get title of filter */
											jQuery('#searchfilterform input[name="'+id+'[]"]:checked').each(function() {
											chkId = jQuery(this).val();
										    checkboxName = jQuery(this).attr('filterdispvalue'); 
											if (jQuery('#selectedFilters #filter-group-'+id).length == 0 ) /* creates a block for ratiing of block is not created before */
											{
												jQuery('#selectedFilters').prepend('<div id="filter-group-'+id+'" class="flit-opt-cols"><div class="filter-lable">'+filter_title+':</div><span class="value"><a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrCheckbox(\''+chkId+'\',\''+id+'\')">'+checkboxName+'<i class="fa fa-times-circle"></i></a></span></div>');
											}
											else    /* otherwise add a value for created block  */
											{
												if (jQuery('#filter-group-'+id+' span #'+id+'_'+chkId).length == 0 )
												{
													jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrCheckbox(\''+chkId+'\',\''+id+'\')">'+checkboxName+'<i class="fa fa-times-circle"></i></a>');
												}
												else
												{
													//alert('else');
													jQuery('#searchfilterform input[name="'+id+'[]"]').each(function(){
													var curr_val = jQuery(this).val();
													if(jQuery(this).attr('checked') == 'checked')
													{
														jQuery('#filter-group-'+id+' span a').remove();
														jQuery('#filter-group-'+id+' span').prepend('<a  delfltrname="filter_title" id="'+id+'_'+chkId+'" onclick="delFltrCheckbox(\''+chkId+'\',\''+id+'\')">'+checkboxName+'<i class="fa fa-times-circle"></i></a>');
													}
																													
													});
												}
											}
										});
										}else{
											jQuery('#selectedFilters #filter-group-'+id).remove(); 
										}
												
									jQuery('.'+list_id+'_process').remove();
									jQuery('#listpagi').remove();
									jQuery('#'+list_id).html(results);
								}
							});
							
							/* Ajax request for locate the location on map */
							jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',			
									data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									dataType: 'json',
									success:function(results){						
										tmpl_googlemaplisting_deleteMarkers();
										templ_add_googlemap_markers_onmap(results.markers);
									}
								});
							
						 }	
						});
							
						function delFltrCheckbox(val,type)
							{
								
								 jQuery('#searchfilterform input[name="'+type+'[]"]').each(function() {
										if(jQuery(this).val() == val)
										{
											jQuery(this).attr('checked', false); /* uncheck a checkbox of rate according to removal */
										}
								 });
								
							
								
								var miles_range=jQuery('#sf_radius_range').val(); /* get the range */
								<?php
								if(is_search())
								{
									
									?>
									var list_id='loop_listing_archive';
									<?php
									
								}
								else
								{
									?>
									var list_id='<?php echo $list_id?>';
									<?php
								}
								?>
								jQuery('.'+list_id+'_process').remove(); /* remove the element */
								jQuery('#'+list_id ).prepend( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><span class='process-overlay'></span><i class='fa fa-2x fa-circle-o-notch fa-spin'></i></p>" );  /* show processing image during ajax request. */
								jQuery.ajax({
									url:TmplajaxUrl,
									type:'POST',
									cache:true,								
									data: 'action=search_filter&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
									success:function(results){						
										jQuery('.'+list_id+'_process').remove();
										jQuery('#listpagi').remove(); 
										jQuery('#'+list_id).html(results);
										if(jQuery('#filter-group-'+type+' .value a').length > 1)
											{
												jQuery('#'+type+'_'+val).remove();
											}
										else
											{
												jQuery('#filter-group-'+type).remove();
											}
									}
								});
								/* Ajax request for locate the location on map */
								jQuery.ajax({
										url:TmplajaxUrl,
										type:'POST',			
										data:'action=search_filter_map&posttype=<?php echo $post_type;?>&page_type=<?php echo $page_type?>&'+jQuery("#searchfilterform").serialize()+'<?php echo $query_string; ?>',
										dataType: 'json',
										success:function(results){						
											tmpl_googlemaplisting_deleteMarkers();
											templ_add_googlemap_markers_onmap(results.markers);
										}
									});
							}
					</script>
								<?php 
					}
					 /* if custom field type is texteditor then show text field */
			   elseif($custom_field_type=='min_max_range' ){
					$name=$filters_type;
				?>
                    <h3 class="widget-title"><?php echo $placeholder; ?></h3>
                    <input type="text" name="<?php echo $name.'_min';?>" id="<?php echo $name;?>_min" value="" placeholder="<?php echo $placeholder.' ';_e('Min value',DOMAIN);?>" class="min_range" filterdisplayname="<?php echo $placeholder.' ';_e('Min value',DOMAIN);?>"/>
                    <input type="text" name="<?php echo $name.'_max';?>" id="<?php echo $name;?>_max" value="" placeholder="<?php echo $placeholder.' ';_e('Max value',DOMAIN);?>" class="max_range" filterdisplayname="<?php echo $placeholder.' ';_e('Max value',DOMAIN);?>"/>
                    <input type="hidden" name="<?php echo $name;?>" />
                    <?php
                }elseif($custom_field_type=='slider_range' && $range_min!='' && $range_max!=''){
                    $name=$filters_type;
                    $min_range=$range_min;
                    $max_range=$range_max;
                    wp_enqueue_script("jquery-ui-slider");	
                    ?>
                    <h3 class="widget-title"><?php echo $placeholder; ?></h3>
                    <label>
                    <input type="text" name="<?php echo $name;?>" id="<?php echo $name;?>_range" value="<?php echo $max_range; ?>" style="border:0; font-weight:bold;"  readonly="readonly" filterdisplayname="<?php echo $placeholder?>"/>
                    </label>
                    <div id="<?php echo $name;?>_range_type" class="clearfix" style="width:90%;"></div>
                    <script type="text/javascript">
                    jQuery(function(){jQuery("#<?php echo $name?>_range_type").slider({range:true,min:<?php echo $min_range;?>,max:<?php echo $max_range; ?>,values:[<?php echo $min_range;?>,<?php echo $max_range; ?>],slide:function(e,t){jQuery("#<?php echo $name;?>_range").val(t.values[0]+" - "+t.values[1])}});jQuery("#<?php echo $name;?>_range").val(jQuery("#<?php echo $name?>_range_type").slider("values",0)+" - "+jQuery("#<?php echo $name?>_range_type").slider("values",1))})
					jQuery('#<?php echo $name;?>_range_type').bind('slidestop', function(event, ui) {				
						var <?php echo $name;?>=jQuery('#<?php echo $name;?>_range').val();
						/* add the miles range filter above content*/
						clearTimeout(typingTimer);
						typingTimer = setTimeout(doneTyping, doneTypingInterval,'<?php echo $name;?>_range');
					});
                    </script>
                <?php
				}elseif($custom_field_type=='min_max_range_select'){
				$title_min_range = explode(',',get_post_meta($custom_field_id,'search_min_option_title',true));
				$value_min_range = explode(',',get_post_meta($custom_field_id,'search_min_option_title',true));
				$title_max_range = explode(',',get_post_meta($custom_field_id,'search_max_option_title',true));
				$value_max_range = explode(',',get_post_meta($custom_field_id,'search_max_option_values',true));				
				$name=$filters_type;
                ?>
                <div class="form_row clearfix">
                <input type="hidden" name="<?php echo $name;?>"  value="1"/>
                <select name="<?php echo $name;?>_min" id="<?php echo $name;?>_min" class="textfield textfield_x <?php echo $style_class;?>" <?php echo $extra_parameter;?>>
                    <option value=""><?php echo sprintf(__('Please Select %s Min value',SF_DOMAIN),$site_title);?></option>
                    <?php if(!empty($value_min_range)){
                    for($i=0;$i<count($value_min_range);$i++){?>
                        <option value="<?php echo $value_min_range[$i]; ?>" <?php if($value==$value_min_range[$i]){ echo 'selected="selected"';} else if($default_value==$value_min_range[$i]){ echo 'selected="selected"';}?>><?php echo ($title_min_range[$i])? $title_min_range[$i]:$value_min_range[$i]; ?></option>
                        <?php	
                        }
                    }?>                
                </select>
                </div>
                
                 <div class="form_row clearfix">
                <select name="<?php echo $name;?>_max" id="<?php echo $name;?>_max" class="textfield textfield_x <?php echo $style_class;?>" <?php echo $extra_parameter;?>>
                    <option value=""><?php echo sprintf(__('Please Select %s Max value',SF_DOMAIN),$site_title);?></option>
                    <?php if(!empty($value_max_range)){
                    for($i=0;$i<count($value_max_range);$i++){?>
                        <option value="<?php echo $value_max_range[$i]; ?>" <?php if($value==$value_max_range[$i]){ echo 'selected="selected"';} else if($default_value==$value_max_range[$i]){ echo 'selected="selected"';}?>><?php echo ($title_max_range[$i])? $title_max_range[$i]:$value_max_range[$i]; ?></option>
                        <?php	
                        }
                    }?>                
                </select>
                <script>
					var typingTimer;                /* timer identifier*/
					var doneTypingInterval = 1000;  /* time in ms, 1 second */
						jQuery('#searchfilterform select').live('change',function(){
							clearTimeout(typingTimer);
							typingTimer = setTimeout(doneTyping, doneTypingInterval,jQuery(this).attr('id'));
						});
					</script>
                </div>
                <?php
				
				
			}elseif($custom_field_type == 'texteditor')
					{	
						?>
							<div class="filter ver-list-filter fil-scroll">
								<label><strong><?php echo $placeholder; ?></strong></label>
								<div>
									<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $htmlvar_name; ?>" id="<?php echo $htmlvar_name; ?>">
								</div>
						</div>
					<?php
					}
					 /* if custom field type is textarea then show textarea */
					elseif($custom_field_type=='textarea')
					{ 
						?>			
						<div class="filter ver-list-filter fil-scroll">
							<label><strong><?php echo $placeholder; ?></strong></label>
							<div>
									<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $htmlvar_name; ?>" id="<?php echo $htmlvar_name; ?>">
								</div>      
						</div>	
						<?php
					}
					elseif($custom_field_type == 'geo_map')	
					{
						?>
						<div class="filter ver-list-filter fil-scroll">
							<label><strong><?php echo $placeholder; ?></strong></label>
							<div>
								<input  value="" type="text" filterdisplayname="<?php echo $placeholder;?>" name="<?php echo $htmlvar_name; ?>" id="<?php echo $htmlvar_name; ?>">
							</div>
						</div>
						<script>
							var typingTimer;                /* timer identifier*/
							var doneTypingInterval = 1000;  /* time in ms, 1 second */
								
							jQuery('#searchfilterform input[type="text"]').bind('keyup',function(){
									clearTimeout(typingTimer);
									typingTimer = setTimeout(doneTyping, doneTypingInterval,this.id);	 
							});
						</script>		
						<?php
					}		
			$c++;
		}
	}
	exit;
}

/* 
Name :filter_searchable_fields
description : Returns custom fields as we checked "Show as a Filter" in custom fields
*/
function filter_searchable_fields($post_types)
{
	$post_types = $_REQUEST['post_types'];
	$fname = $_REQUEST['fid'];
	$post_types = explode(',',$_REQUEST['post_types']);
	$fields = '';
	$custom_fileds = array();
	for($i=0; $i < count($post_types); $i++){
		
		if($post_types[$i] !=''){
			$default_custom_metaboxes = get_filter_search_post_fields($post_types[$i],'custom_fields',$post_types); 
			
			foreach($default_custom_metaboxes as $key=>$val) {
							$name = $val['name'];
							$site_title = $val['label'];
							$type = $val['type'];
							$htmlvar_name = $val['htmlvar_name'];
							
							
							$custom_fileds[$htmlvar_name] = array('name'=>$val['htmlvar_name'],
													 'ID'=>"sf_search_criteria_".$htmlvar_name,	
													 'site_title' => $val['label'],
													 'fid'=> $fname ,
													);									
			}
		}
	}
	if(!empty($custom_fileds)){	
		$custom_fileds1 = array_filter($custom_fileds);
	
	
	if(count($custom_fileds1) == 0)
	{
		echo '<p><a href="'.site_url().'/wp-admin/admin.php?page=custom_setup&ctab=custom_fields&action=addnew#show_as_filter">'.__('Click Here',SF_DOMAIN).'</a>'.__(' to set your custom field as \'show as filter\' to add in filter',SF_DOMAIN).'</p>';
	}
	$c=1;
	$not_allowto_filter = apply_filters('tmpl_notto_filter',array('post_coupons',' '));
	foreach($custom_fileds1 as $key=>$val) {
		$htmlvar_name = $val['name'];
		$site_title = $val['site_title'];
		$ID = $val['fid'];
		$id = $val['ID'];
		
		
		if($site_title ==''){
			$site_title = $htmlvar_name."( ".__('Var Name',SF_DOMAIN)." )";
		}
		
						
		if($checked ==1){ $checked = "checked=checked"; }else{ $checked = ''; }
		
		if(($htmlvar_name == 'st_time' || $htmlvar_name == 'end_time') && ($post_types[0] != 'event'))
		{
			continue;
		}		
		if(!in_array($htmlvar_name, $not_allowto_filter)){
			$fields .='<p><label for="'.$id.$htmlvar_name.'"><input '.$checked.' id="'.$id.$htmlvar_name.'" name="'.$fname.'[]" type="checkbox" value="'.$htmlvar_name.'" /><b>'.$site_title.'</b></label></p>';
		}
		$c++;
		}
	}
	echo $fields;
	exit;
}

/* 
Name :get_filter_search_post_fields
description : filter fields as we checked "Show as a Filter" in custom fields
*/
function get_filter_search_post_fields($post_types,$category_id='',$taxonomy='') {
	global $wpdb,$post,$sitepress;
	$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
		remove_all_actions('posts_where');
		$args=
		array( 
		'post_type' => 'custom_fields',
		'posts_per_page' => -1	,
		'post_status' => array('publish'),
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'post_type_'.$post_types,
				'value' => array('all',$post_types),
				'compare' => 'In',
				'type'=> 'text'
			),
			array(
				'key' => 'show_as_filter',
				'value' =>  '1',
				'compare' => '='
			),
			
			array(
				'key' => 'is_active',
				'value' =>  '1',
				'compare' => '='
			)
		),
		 
		'orderby' => 'meta_value_num',
		'order' => 'ASC'
		);
	add_filter('posts_join', 'custom_field_posts_where_filter');
	$post_query = null;	
	
	$post_query = new WP_Query($args);	
	$post_meta_info = $post_query;
	
	wp_reset_postdata();
	$return_arr = array();
	if($post_meta_info){
		while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
			if(get_post_meta($post->ID,"ctype",true)){
				$options = explode(',',get_post_meta($post->ID,"option_values",true));
			}
			$custom_fields = array(
					"ID"		=>$post->ID,
					"name"		=> get_post_meta($post->ID,"htmlvar_name",true),
					"label" 	=> $post->post_title,
					"htmlvar_name" 	=> get_post_meta($post->ID,"htmlvar_name",true),
					"default" 	=> get_post_meta($post->ID,"default_value",true),
					"type" 		=> get_post_meta($post->ID,"ctype",true),
					"desc"      => $post->post_content,
					"option_values" => get_post_meta($post->ID,"option_values",true),
					"option_title" => explode(',',get_post_meta($post->ID,"option_title",true)),
					"is_require"  => get_post_meta($post->ID,"is_require",true),
					"is_active"  => get_post_meta($post->ID,"is_active",true),
					"show_on_listing"  => get_post_meta($post->ID,"show_on_listing",true),
					"show_on_detail"  => get_post_meta($post->ID,"show_on_detail",true),
					"validation_type"  => get_post_meta($post->ID,"validation_type",true),
					"style_class"  => get_post_meta($post->ID,"style_class",true),
					"extra_parameter"  => get_post_meta($post->ID,"extra_parameter",true),
					);
			if($options)
			{
				$custom_fields["options"]=$options;
			}
			$return_arr[get_post_meta($post->ID,"htmlvar_name",true)] = $custom_fields;
		endwhile;
	}
	remove_filter('posts_join', 'custom_field_posts_where_filter');	
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		add_filter('posts_where', array($sitepress,'posts_where_filter'));	
	}
	
	return $return_arr;
}

/*
Name: filter_searchable_scripts
Desc: To include search by address widget script in footer back end.
*/
function filter_searchable_scripts(){ 

	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){				
		$TmplajaxUrl = SEARCH_FILTER_PLUGIN_URL.'tmpl_custom_ajax.php?lang='.ICL_LANGUAGE_CODE ;				
	}else{
		$TmplajaxUrl = SEARCH_FILTER_PLUGIN_URL.'tmpl_custom_ajax.php' ;				
	}
?>
	<script type="text/javascript">	

	function filter_search_fields(fid,fname,fields_div,search_criteria,sfid){ 
		var TmplajaxUrl = '<?php echo esc_js($TmplajaxUrl); ?>';
		document.getElementById('search_process_'+fname).style.display = '';
		if(document.getElementById('process_search'))
			document.getElementById('process_search').style.display="block";	
		var post_types = jQuery('#'+fid).val(); 
		if(post_types =='event'){
			document.getElementById(sfid).style.display="block";	
		}else{
			document.getElementById(sfid).style.display="none";	
		}
		jQuery.ajax({
		url:TmplajaxUrl,
		type:'POST',
		cache:true,
		data:'action=filter_searchable_fields&post_types=' + post_types+'&fid='+fname+'&criteria='+search_criteria,
		success:function(results) {
			if(document.getElementById('process_search'))
				document.getElementById('process_search').style.display="none";		
			document.getElementById('search_process_'+fname).style.display = 'none';
			document.getElementById(fields_div).innerHTML=results;		
		}
		});
	}	
	</script>
<?php }	
/* End of file */
add_action('wp_ajax_tmpl_filter_custom_field_sortorder','tmpl_filter_custom_field_sortorder');
function tmpl_filter_custom_field_sortorder(){
	$user_id = get_current_user_id();
	$j=1;
	global $wpdb;

	for($i=0; $i<count($_REQUEST['custom_sort_order']);$i++){ echo $_REQUEST['custom_sort_order'][$i];
		update_post_meta($_REQUEST['custom_sort_order'][$i],'filter_sort_order',$j);		
		$j++;
	}
	exit;
}
?>
