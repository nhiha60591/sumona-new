<?php
$is_edit='';
if(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'){
	$is_edit=1;
}

global $post,$htmlvar_name,$tmpl_flds_varname;
$templatic_settings=get_option('templatic_settings');
$googlemap_setting=get_option('city_googlemap_setting');
$post_id = get_the_ID();
$count_post_id = get_the_ID();
if(get_post_meta($post_id,'_event_id',true)){
	$post_id=get_post_meta($post_id,'_event_id',true);
}
//$templatic_settings['direction_map']=='yes'
$tmpdata = get_option('templatic_settings');	
$special_offer=get_post_meta($post_id,'proprty_feature',true);
$video=get_post_meta($post_id,'video',true);
$facebook=get_post_meta($post_id,'facebook',true);
$google_plus=get_post_meta($post_id,'google_plus',true);
$twitter=get_post_meta($post_id,'twitter',true);
if(function_exists('bdw_get_images_plugin'))
{
	$post_img = bdw_get_images_plugin($post_id,'large');
	$postimg_thumbnail = bdw_get_images_plugin($post_id,'thumbnail');
	$more_listing_img = bdw_get_images_plugin($post_id,'tevolution_thumbnail');
	$thumb_img = @$post_img[0]['file'];
	$attachment_id = @$post_img[0]['id'];
	$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array
	$attach_data = get_post($attachment_id);
	$img_title = $attach_data->post_title;
	$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
	
}
?>
<script type="text/javascript">
jQuery(function() {
	jQuery('#event_image_gallery .event_image a').lightBox();
});
</script>


<!-- Tabs Start -->
	<?php
		$post_img = bdw_get_images_plugin($post_id,'thumb');
		$post_images = @$post_img[0]['file'];
		$title=urlencode($post->post_title);
		$url=urlencode(get_permalink($post_id));
		$summary=urlencode(htmlspecialchars($post->post_content));
		$image=$post_images;
		$settings = get_option( "templatic_settings" );
	?>
 
	<ul class="tabs" data-tab role="tablist">
		<?php 
		if($templatic_settings['direction_map']=='yes' || !empty($post_img) || $video):

		if(!empty($post_img)):?>
			<li class="tab-title active" role="presentational"><a href="#event_image_gallery" role="tab" tabindex="0" aria-selected="false" controls="event_image_gallery"><?php _e('Photos',EDOMAIN);?></a></li>
		<?php endif;

		if($templatic_settings['direction_map']=='yes'):?>
			<li class="tab-title" role="presentational"><a href="#locations_map" role="tab" tabindex="1" aria-selected="false" controls="locations_map"><?php _e('Map',EDOMAIN);?></a></li>
		<?php endif;

		if($video!="" && $tmpl_flds_varname['video'] || ($is_edit==1 &&  $tmpl_flds_varname['video'])):?>
			<li class="tab-title" role="presentational"><a href="#event_video" role="tab" tabindex="2" aria-selected="false" controls="event_video"><?php _e('Video',EDOMAIN);?></a></li>
		<?php endif;
		  
		endif;

		do_action('show_listing_event'); ?>
	</ul>
	
	
	
	<div class="tabs-content">
	<?php
	
	if(($video!="" && $tmpl_flds_varname['video']) || ($is_edit==1 && $tmpl_flds_varname['video'])):?>
        <!--Video Code Start -->
        <section role="tabpanel" aria-hidden="false" class="content" id="event_video">
            <?php if($is_edit==1):
				do_action('oembed_video_description');?>        		
                <span id="frontend_edit_video" class="frontend_oembed_video button" ><?php _e('Edit Video',EDOMAIN);?></span>
                <input type="hidden" class="frontend_video" name="frontend_edit_video" value='<?php echo $video;?>' />
            <?php endif;?>
             <div class="frontend_edit_video"><?php             
                    $embed_video= wp_oembed_get( $video);            
                    if($embed_video!=""){
                        echo $embed_video;
                    }else{
                        echo $video;
                    }
            ?></div>
        </section>
        <!--Video code End -->
     <?php endif;
	 
	 if($templatic_settings['direction_map']=='yes'):?>
          <!--Map Section Start -->
			<section role="tabpanel" aria-hidden="false" class="content" id="locations_map">
               <?php do_action('event_single_page_map') ?>
			</section>
          <!--Map Section End -->
     <?php endif;
     
     if(!empty($post_img) && $is_edit==''):?>
          <!--Image gallery Section Start -->
			<section role="tabpanel" aria-hidden="false" class="content active" id="event_image_gallery">
          	<div  id="slider" class="event_image flexslider">
			
					<ul class="slides">
					<?php
						$main_post_img = bdw_get_images_plugin($post_id,'large');
						foreach($main_post_img as $key=>$value):
							$attachment_id = $value['id'];
							$attach_data = get_post($attachment_id);
							$image_attributes = wp_get_attachment_image_src( $attachment_id ,'event-single-image'); // returns an array							
							$img_title = $attach_data->post_title;
							
                    ?>
							<li>
                              	<a itemprop="image" href="<?php echo $image_attributes['0'];?>" title="<?php echo $img_title; ?>"  class="listing_img" >		
		                            <img src="<?php echo $value['file'];?>" />
                                </a>
                            </li>
									
					  <?php endforeach;?>
					</ul>		
             
               </div>
               
			<?php if(!empty($more_listing_img) && count($more_listing_img)>1):?>
               	<div id="event_image_gallery" >
                    <div id="silde_gallery" class="flexslider<?php if(!empty($more_listing_img) && count($more_listing_img)>11) {echo ' slider_padding_class'; }?>">
                         <ul class="more_photos slides">
							<?php 
							foreach($more_listing_img as $key=>$value):
							$attachment_id = $value['id'];
							$attach_data = get_post($attachment_id);
							$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array							
							$img_title = $attach_data->post_title;
							?>
                        		<li>
                              	 <a href="<?php echo $image_attributes['0'];?>" title="<?php echo $img_title; ?>" >		
		                              <img src="<?php echo $value['file'];?>" alt="<?php echo $img_title; ?>"/>
                                    </a>
                              </li>
                                   
							<?php
							endforeach;?>
                         </ul>
                    </div>
                </div>
               <?php endif;?>
          </section>          
          <!--Image gallery Section End -->
    <?php 
		do_action('show_listing_event_detail'); 
	endif;
	
	if($is_edit=="1"):?>
     <!-- Front end edit upload image-->
	<section role="tabpanel" aria-hidden="false" class="content active entry-header-image" id="event_image_gallery">
        <!--editing post images -->
        <div id="slider" class="event_image listing-image flexslider frontend_edit_image flex-viewport">
            <ul class="frontend_edit_images_ul slides">
                <?php
                $post_img = bdw_get_images_plugin($post->ID,'large');
                if(!empty($post_img)):
                foreach($post_img as $key=>$value):
                    echo "<li class='image' data-attachment_id='".basename($value['file'])."' data-attachment_src='".$value['file']."'><img src='".$value['file']."' alt='".$img_title."' /></li>";
                    break;
                endforeach;
                endif;	
                ?>
            </ul>
            <div id="uploadimage" class="upload button secondary_btn clearfix">
                <span><?php _e("Upload Images", EDOMAIN); ?></span>					
            </div>
        </div>
        
        <div id="frontend_images_gallery_container" class="clearfix flex-viewport">
            <ul class="frontend_images_gallery more_photos slides">
                <?php
                if(!empty($post_img)):                				
                foreach($post_img as $key=>$value):
                    echo "<li class='image' data-attachment_id='".basename($value['file'])."' data-attachment_src='".$value['file']."'><img src='".$value['file']."' alt='".$img_title."' /><span>
<a class='delete' title='Delete image' href='#' id='".$value['id']."' ><i class='fa fa-times-circle redcross'></i></a></span></li>";
                endforeach;
                endif;	
                ?>
            </ul>
            <input type="hidden" id="fontend_image_gallery" name="fontend_image_gallery" value="<?php echo esc_attr( substr(@$image_gallery,0,-1) ); ?>" />		
        </div>
        <span id="forntend_status" class="message_error2 clearfix"></span>
        <!--finsh editing post images -->
    </section>
    <?php do_action('show_listing_event_detail'); 
	
	endif;
	
	do_action('listing_extra_details'); ?>	
</div>
<!-- Tabs End -->
<section itemprop="description" class="entry-content event-description">
	<?php 
	do_action('event_before_tab_content');
	do_action('templ_post_single_content');       /*do action for single post content */
	do_action('event_after_tab_content');       /*do action for single post content */ 
	?>
</section><!-- end .entry-content -->
<?php
$org_name=get_post_meta($post_id,'organizer_name',true);
$org_address=get_post_meta($post_id,'organizer_address',true);
$org_contact=get_post_meta($post_id,'organizer_contact',true);
$org_mobile=get_post_meta($post_id,'organizer_mobile',true);
$org_email=get_post_meta($post_id,'organizer_email',true);
$org_website=get_post_meta($post_id,'organizer_website',true);
$org_desc=get_post_meta($post_id,'organizer_desc',true);
$org_logo=get_post_meta($post_id,'organizer_logo',true);
$reg_desc=get_post_meta($post_id,'reg_desc',true);
if($org_name!='' || $org_address!='' || $org_contact!='' || $org_mobile!='' || $org_email!='' || $org_website!=''):
?>
<div class="event-organizer">
	<h2><?php _e('Organizers',EDOMAIN);?></h2>
     
     <?php if($org_logo && $tmpl_flds_varname['organizer_logo']  && ($is_edit=="")):?>
     	<div class="event-organizer-left">
          	<img src="<?php echo $org_logo;?>"  />
          </div>
     <?php elseif($is_edit==1): ?>
     <div class="event-organizer-left" >
        <div style="display:none;" class="frontend_organizer_logo"><?php echo $org_logo?></div>
        <!--input id="fronted_files_listing_logo" class="fronted_files" type="file" multiple="true" accept="image/*" /-->
        <div id="fronted_upload_organizer_logo" class="frontend_uploader button" data-src="<?php echo $org_logo?>">	                 	
            <span><?php echo __( 'Upload ', EDOMAIN ).$tmpl_flds_varname['organizer_logo']['label']; ?></span>						
        </div>
     </div>
     <?php endif;?>
     
     <div class="event-organizer-right">
		<?php if($org_name!='' && $tmpl_flds_varname['organizer_name'] || ($is_edit==1 && $tmpl_flds_varname['organizer_name'])):?>
		<p class="org_name <?php echo $tmpl_flds_varname["organizer_name"]["style_class"]; ?>"><label><?php echo $tmpl_flds_varname["organizer_name"]["label"]; ?>:</label><span class="label_data frontend_organizer_name"  <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo $org_name?></span></p>
     <?php endif;
	 
	 if( @$org_address!='' && @$tmpl_flds_varname['organizer_address'] || ($is_edit==1 &&  @$tmpl_flds_varname['organizer_address'])):?>
		<p class="address <?php echo $tmpl_flds_varname["organizer_address"]["style_class"]; ?>"><label><?php echo $tmpl_flds_varname["organizer_address"]["label"]; ?>:</label><span class="label_data frontend_organizer_address" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo $org_address?></span></p>
     <?php endif;
	 
	if($org_contact!='' && $tmpl_flds_varname['organizer_contact'] || ($is_edit==1 && $tmpl_flds_varname['organizer_contact'])):?>
		<p class="phone <?php echo $tmpl_flds_varname["organizer_contact"]["style_class"]; ?>"><label><?php echo $tmpl_flds_varname["organizer_contact"]["label"]; ?>:</label><span class="label_data frontend_organizer_contact" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo $org_contact?></span></p>
	<?php endif;
	 
	if($org_mobile!='' && $tmpl_flds_varname['organizer_mobile'] || ($is_edit==1 && $tmpl_flds_varname['organizer_mobile'])):?>
		<p class="phone <?php echo $tmpl_flds_varname["organizer_mobile"]["style_class"]; ?>"><label><?php echo $tmpl_flds_varname["organizer_mobile"]["label"]; ?>:</label><span class="label_data frontend_organizer_mobile" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo $org_mobile?></span></p>
	<?php endif;

	if($org_email!='' && $tmpl_flds_varname['organizer_email'] || ($is_edit==1 && $tmpl_flds_varname['organizer_email'])):?>
		<p class="email <?php echo $htmlvar_name["org_info"]["organizer_email"]["style_class"]; ?>"><label><?php  echo $htmlvar_name["org_info"]["organizer_email"]["label"]; ?>:</label><span class="label_data frontend_organizer_email" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo antispambot($org_email)?></span></p>
	<?php endif;?>
     
     <?php if($org_website!='' && $tmpl_flds_varname['organizer_website'] || ($is_edit==1 && $tmpl_flds_varname['organizer_website'])):?>
     <p class="website <?php echo $tmpl_flds_varname["organizer_website"]["style_class"]; ?>"><label><?php echo $tmpl_flds_varname["organizer_website"]["label"]; ?>:</label><span class="label_data frontend_organizer_website" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo $org_website?></span></p>
     <?php endif;?>
     </div>
</div>
<?php endif;

	if($org_desc!='' && $tmpl_flds_varname['organizer_desc'] || ($is_edit==1 && $tmpl_flds_varname['organizer_desc'])):?>
	<div class="abput-event-organizer <?php echo $$tmpl_flds_varname["organizer_desc"]["style_class"]; ?>">
		<h2><?php echo $tmpl_flds_varname['organizer_desc']['label'];?></h2>
		<div class="frontend_organizer_desc <?php if($is_edit==1):?> editblock <?php endif;?>" >
			<?php echo $org_desc;?>
		</div>
	</div>
<?php endif;

	if(isset($reg_desc) && $reg_desc!='' && @$tmpl_flds_varname['reg_desc'] || ($is_edit==1  && @$tmpl_flds_varname['reg_desc'])):
	
	if($is_edit==1  && @$tmpl_flds_varname['reg_desc']):?>
		<h2><?php echo @$tmpl_flds_varname['reg_desc']['label'];?></h2>
<?php endif;?>
<div class="how_to_reg frontend_reg_desc <?php if($is_edit==1):?> editblock <?php endif;?>" >
<?php
    $reg_desc = apply_filters( 'the_content', $reg_desc );
	$reg_desc = str_replace( ']]>', ']]&gt;', $reg_desc );
	echo $reg_desc;
?>
</div>
<?php endif;


global $htmlvar_name,$heading_title;
$j=0;
if(!empty($htmlvar_name)){
	echo '<div class="listing_custom_field event_custom_field">';
	foreach($htmlvar_name as $key=>$value){
		$i=0;
		foreach($value as $k=>$val){			
			$heading_key = ($key=='basic_inf')? __('Event Information',EDOMAIN): @$heading_title[$key];			
			if($k!='post_title' && $k!='post_content'  && $k!='post_excerpt' && $k!='post_images' && $k!='category' && $k!='address' && $k!='end_date' && $k!='st_time' && $k!='end_time' && $k!='event_type' && $k!='reg_fees' && $k!='video' && $k!='map_view' && $k!='st_date' && $k!='reg_desc' && $k!='phone' && $k!='website' && $k!='twitter' && $k!='facebook' && $k!='google_plus' && $k!='organizer_name' && $k!='organizer_email' && $k!='organizer_logo' && $k!='organizer_address' && $k!='organizer_contact' && $k!='organizer_website' && $k!='organizer_mobile' && $k!='organizer_desc' && $k!='post_city_id')
			{	
				//display heading type
				$field= get_post_meta(get_the_ID(),$k,true);
				if($i==0 && $field!='' && $key != 'field_label'){echo '<h2 class="custom_field_headding">'.$heading_key.'</h2>';$i++;}
				if($field!='' && $key == 'field_label'){echo '<h2 class="custom_field_headding">'.$val['label'].'</h2>';$i++;}
				if($val['type'] == 'multicheckbox' &&  ($field!="" || $is_edit==1)):
				$checkbox_value = '';				
					$option_values = explode(",",$val['option_values']);				
					$option_titles = explode(",",$val['option_title']);
					for($i=0;$i<count($option_values);$i++){
						if(in_array($option_values[$i],$field)){
							if($option_titles[$i]!=""){
								$checkbox_value .= $option_titles[$i].',';
							}else{
								$checkbox_value .= $option_values[$i].',';
							}
						}
					}
				?>
     	<p class='<?php echo $k;?>'><?php if($key != 'field_label') { ?><label><?php echo $val['label']; ?>:&nbsp; </label> <?php } ?><span <?php if($is_edit==1):?>id="frontend_multicheckbox_<?php echo $k;?>" <?php endif;?> class="multicheckbox"><?php echo substr($checkbox_value,0,-1);?></span></p>

		<?php 
				elseif($val['type']=='radio' && ($field || $is_edit==1)):
					$option_values = explode(",",$val['option_values']);				
					$option_titles = explode(",",$val['option_title']);
					for($i=0;$i<count($option_values);$i++){
						if($field == $option_values[$i]){
							if($option_titles[$i]!=""){
								$rado_value = $option_titles[$i];
							}else{
								$rado_value = $option_values[$i];
							}
						?>
		<p class='<?php echo $k;?>'><?php if($key != 'field_label') { ?><label><?php echo $val['label']; ?>:&nbsp; </label><?php } ?><span <?php if($is_edit==1):?>id="frontend_radio_<?php echo $k;?>" <?php endif;?>><?php echo $rado_value;?></span></p>
		<?php
						}
					}
				elseif($val['type']=='oembed_video' && ($field || $is_edit==1)):?>
					<p class='<?php echo $val['style_class'];?>'><?php if($key != 'field_label') { ?><label><?php echo $val['label']; ?>:&nbsp;</label><?php } ?>
				 		<?php if($is_edit==1):?>					
                        <span id="frontend_edit_<?php echo $k;?>" class="frontend_oembed_video button" ><?php _e('Edit Video',EDOMAIN);?></span>
                        <input type="hidden" class="frontend_<?php echo $k;?>" name="frontend_edit_<?php echo $k;?>" value='<?php echo $field;?>' />
                        <?php endif;?>
					<span class="frontend_edit_<?php echo $k;?>"><?php             
					$embed_video= wp_oembed_get( $field);            
					if($embed_video!=""){
						echo $embed_video;
					}else{
						echo $field;
					}
					?></span></p>
				<?php	
				endif;
				if($val['type']  == 'upload' || ($is_edit==1 && $val['type']  == 'upload'))
				{
					 $upload_file=strtolower(substr(strrchr($field,'.'),1));					 
					 if($is_edit==1):?>
	                    <p class="<?php echo $val['style_class'];?>"><?php if($key != 'field_label') { ?><label><?php echo $val['label']; ?>: </label><?php } ?>
                            <span class="entry-header-<?php echo $k;?> span_uploader" >
                            <span style="display:none;" class="frontend_<?php echo $k;?>"><?php echo $field?></span>                            
                            <span id="fronted_upload_<?php echo $k;?>" class="frontend_uploader button"  data-src="<?php echo $field?>">	                 	
                            	<span><?php echo __( 'Upload File', EDOMAIN ); ?></span>
                            </span>
                            </span>
                        </p>
					<?php elseif($upload_file=='jpg' || $upload_file=='jpeg' || $upload_file=='gif' || $upload_file=='png' || $upload_file=='jpg' ):?>
                    	<p class="<?php echo $val['style_class'];?>"><img src="<?php echo $field; ?>" /></p>
                    <?php else:?>
                    	<p class="<?php echo $val['style_class'];?>"><label><?php echo $val['label']; ?>: </label><a href="<?php echo $field; ?>" target="_blank"><?php echo basename($field); ?></a></p>
                    <?php endif;
				}
				if(($val['type'] != 'multicheckbox' && $val['type'] != 'radio' && $val['type']  != 'upload' && $val['type'] !='oembed_video') && ($field!='' || $is_edit==1)):	

					if($val['type'] =='date'){ ?>
						<p class='<?php echo $val['style_class'];?>'>
							<?php if($key != 'field_label') { ?><label><?php echo $val['label']; ?>:&nbsp;</label><?php } ?>
							
							<span <?php if($is_edit==1):?>id="frontend_<?php echo $val['type'].'_'.$k;?>" contenteditable="true" class="frontend_<?php echo $k;?>" <?php endif;?>>
								<?php echo date(get_option('date_format'),strtotime($field));?>
							</span>
							
						</p>
					
					<?php }else{
						?>
						<p class='<?php echo $val['style_class'];?>'>
							<?php if($key != 'field_label') { ?><label><?php echo $val['label']; ?>:&nbsp;</label><?php } ?>
							<?php if($val['type']=='texteditor'):?>
								<span <?php if($is_edit==1):?>id="frontend_<?php echo $val['type'].'_'.$k;?>" class="frontend_<?php echo $k; if($val['type']=='texteditor'){ echo ' editblock';} ?>" <?php endif;?>>
									<?php echo $field;?>
								</span>
							<?php else:?>
							<span <?php if($is_edit==1):?>id="frontend_<?php echo $val['type'].'_'.$k;?>" contenteditable="true" class="frontend_<?php echo $k;?>" <?php endif;?>>
								<?php echo $field;?>
							</span>
							<?php endif;?>
						</p>
				<?php }
				endif;
			}// End If condition
			
			$j++;
		}// End second foreach
	}// END First foreach
	echo '</div>';
}
?>

<!--Event Social Media Coding Start -->
<?php if(function_exists('tevolution_socialmedia_sharelink')) 
		tevolution_socialmedia_sharelink($post); ?>

<!--Event Social Media Coding End -->

<?php if(isset($tmpdata['templatic_view_counter']) && $tmpdata['templatic_view_counter']=='Yes')
{
	if(function_exists('view_counter_single_post')){
		view_counter_single_post($count_post_id);
	}
	
	$post_visit_count=(get_post_meta($count_post_id,'viewed_count',true))? get_post_meta($count_post_id,'viewed_count',true): '0';
	$post_visit_daily_count=(get_post_meta($count_post_id,'viewed_count_daily',true))? get_post_meta($count_post_id,'viewed_count_daily',true): '0';
	
	
	echo "<div class='view_counter'>";
	$custom_content="<p>".sprintf(__('Visited %s times',EDOMAIN) ,$post_visit_count);
	$custom_content.= ', '.$post_visit_daily_count.__(" Visits today",EDOMAIN)."</p>";
	echo $custom_content;
	echo '</div>';
	
}
?>
<div class="post-meta" itemprop="eventType">                              
	<?php 
	if( @$tmpl_flds_varname['category'] && @$tmpl_flds_varname['category'])
	{
		echo get_the_event_taxonomies();
	}?>
     <?php echo get_the_event_tag();?>
</div>