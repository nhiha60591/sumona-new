<?php
/**
 * Event single custom post type template
 *
**/
get_header(); //Header Portation
$is_edit='';
if(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'){
	$is_edit=1;
}
/* to get the common/context custom fields display by default with current post type */
if(function_exists('tmpl_single_page_default_custom_field')){
	$tmpl_flds_varname = tmpl_single_page_default_custom_field(CUSTOM_POST_TYPE_EVENT);
}

$phone = get_post_meta($post->ID,'phone',true);
$website = get_post_meta($post->ID,'website',true);
$email = get_post_meta($post->ID,'email',true);
if(function_exists('bdw_get_images_plugin'))
{
	$post_img = bdw_get_images_plugin(get_the_ID(),'directory-single-image');
	$postimg_thumbnail = bdw_get_images_plugin(get_the_ID(),'thumbnail');
	$more_listing_img = bdw_get_images_plugin(get_the_ID(),'tevolution_thumbnail');
	$thumb_img = @$post_img[0]['file'];
	$attachment_id = @$post_img[0]['id'];
	$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large');
	$attach_data = get_post($attachment_id);
	$img_title = $attach_data->post_title;
	$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
}
?>
<!-- start content part-->
<div id="content" class="large-9 small-12 columns" role="main">

	
     
	<?php 
		if(function_exists('supreme_sidebar_before_content'))
			apply_filters('tmpl_before-content',supreme_sidebar_before_content() ); // Loads the sidebar-before-content.?>
     
	<?php while ( have_posts() ) : the_post(); ?>
     	<?php do_action('event_before_post_loop');?>
          <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>  
          	<?php do_action('event_before_post_title');         /* do action for before the post title.*/ ?>
          
          	<!--start post type title -->
    			<header class="entry-header">
               	<section class="entry-header-title">
                	<div class="entry-info">
                    <h1 itemprop="name" class="entry-title <?php if($is_edit==1):?>frontend-entry-title <?php endif;?>" <?php if($is_edit==1):?> contenteditable="true"<?php endif;?>>
                     	<?php 
                     		do_action('before_title_h1');
								the_title(); 
							do_action('after_title_h1');
                    	?>
                  	</h1>
                    <?php					
					$tmpdata = get_option('templatic_settings');
					if($tmpdata['templatin_rating']=='yes'):
						$total=get_post_total_rating(get_the_ID());
						$total=($total=='')? 0: $total;
						$review_text=($total==1)? '<a id="reviews_show" href="#comments">'.__('Review',EDOMAIN).'</a>': '<a href="#comments" id="reviews_show">'.$total.' '.__('Reviews',EDOMAIN).'</a>';
					?>
                         	<div class="event_rating">
							<div class="event_rating_row"><span class="single_rating"> <?php echo draw_rating_star_plugin(get_post_average_rating(get_the_ID()));?> <span><?php echo $review_text?></span></span></div>
                              </div>
					<?php endif;
					do_action('event_display_rating',get_the_ID());	?>
                    </div>
                    <div class="entry-links-mobile mobile">
                        <ul>        		
                            <?php if(@$tmpl_flds_varname['phone'] || ($is_edit==1 && $tmpl_flds_varname['phone'])):?>
                            <li class="phone <?php echo $tmpl_flds_varname['phone']['style_class']; ?>">
                                <a href="tel:<?php echo $phone ?>" class="entry-phone frontend_phone listing_custom" <?php if($is_edit==1):?>contenteditable="true" <?php endif;?>><i class="fa fa-phone"></i> <?php echo 'Call';?></a>
                            </li>
                            <?php endif; 
                            
                          
                            if(@$email!="" && @$tmpl_flds_varname['email'] || ($is_edit==1 && @$tmpl_flds_varname['email'])):?>
                            <li class="email  <?php echo $tmpl_flds_varname['email']['style_class']; ?>">
                                <a class="entry-email frontend_email listing_custom" href="mailto:<?php echo $email; ?>" <?php if($is_edit==1):?>contenteditable="true"<?php endif;?>><i class="fa fa-envelope-o"></i><?php echo 'Email';?></a>
                            <?php endif; ?>
                            </li>
                            <?php 
                            if( @$tmpl_flds_varname['website'] && @$website || ($is_edit==1 && $tmpl_flds_varname['website']))
                            {	if(!strstr($website,'http')) $website = 'http://'.$website; ?>	
                            <li class="website <?php echo $tmpl_flds_varname['website']['style_class']; ?>">
                                <a target="_blank" id="website" class="frontend_website <?php if($is_edit==1):?>frontend_link<?php endif; ?>" href="<?php echo $website;?>" ><span><i class="fa fa-globe"></i><?php echo $tmpl_flds_varname['website']['label']; ?></span></a>
                            </li>
                            <?php
                            }	
							
							  /* Add to fav link for mobile*/
                            if(current_theme_supports('tevolution_my_favourites') && ($post->post_status == 'publish' )){
                                global $current_user;
                                $user_id = $current_user->ID;
                                do_action('tmpl_before_addtofav');
                                $link.= tmpl_detailpage_favourite_html($user_id,@$post);
                                echo $link;
                                
                            } 
                    
							?>
                        </ul>
                    </div>
					<article class="entry-header-custom-wrap">
						<?php do_action('event_date_display',get_the_ID());?>
						<?php do_action('directory_display_custom_fields_default'); ?>
					</article>
                </section>
            </header>
               
			<?php do_action('event_after_post_title');          /* do action for after the post title.*/

					do_action('event_user_attend');          /* do action for after the post title.*/?>              
               
               <div class="claim-post-wraper">
			<ul>
					<?php
					 if(!isset($link)){ $link=''; } 
					 if(current_theme_supports('tevolution_my_favourites') && ($post->post_status == 'publish' || $post->post_status == 'recurring' )){
						global $current_user;
						$user_id = $current_user->ID;
						//$link.= eventmngr_favourite_html($user_id,$post);
					}
					 ?>                        
                    </ul>
               </div>
     		  <!--end post type title -->
            
                 <!--Code start for single captcha -->
                 <?php 
                      $tmpdata = get_option('templatic_settings');
                      $display = (isset($tmpdata['user_verification_page']))?$tmpdata['user_verification_page']:array();
                      $captcha_set = array();
                      $captcha_dis = '';
                      if(count($display) > 0)
                       {
                           foreach($display as $_display)
                            {
                                if($_display == 'claim' || $_display == 'emaitofrd')
                                 { 
                                    $captcha_set[] = $_display;
                                    $captcha_dis = $_display;
                                 }
                            }
                       }
                     $recaptcha = get_option("recaptcha_options");
				   global $current_user;
				 ?>
				   
				<div id="myrecap" style="display:none;"><?php if($recaptcha['show_in_comments']!= 1 || $current_user->ID != ''){ templ_captcha_integrate($captcha_dis); }?></div> 
				<input type="hidden" id="owner_frm" name="owner_frm" value=""  />
				<div id="claim_ship"></div>
				<script type="text/javascript">
				var RECAPTCHA_COMMENT = '';
					<?php
					
					if($recaptcha['show_in_comments']!= 1 || $current_user->ID != ''){ ?>
						jQuery('#owner_frm').val(jQuery('#myrecap').html());
				<?php 	} else{ ?> RECAPTCHA_COMMENT = <?php echo $recaptcha['show_in_comments']; ?>; <?php } ?>
				</script>
            
            <!--Code end for single captcha -->
			
			<!-- Image gallery -->
			<section id="listing_gallery">
			<!-- Image Gallery Div --> 	
			<?php if($thumb_img && $is_edit==''):?>		
			<div id="directory_detail_img" class="entry-header-image">
			
			<?php do_action('directory_before_post_image');
			if($is_edit==""):?>
			<div id="slider" class="listing-image flexslider frontend_edit_image">    
				<ul class="slides">
					<?php
						if(!empty($post_img)):
						foreach($post_img as $key=>$value):
						$attachment_id = $value['id'];
						$attach_data = get_post($attachment_id);
						$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array							
						$img_title = $attach_data->post_title;
						$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);	?>
						<li>
							<a href="<?php echo $image_attributes['0'];?>" title="<?php echo $img_title; ?>" >		
								<img src="<?php echo $value['file'];?>" alt="<?php echo $img_title; ?>"/>
							</a>
						</li>
						<?php 
						endforeach;
					endif;?>
				</ul>
			</div>
			<?php endif;
			
			do_action('directory_after_post_image');?>
			</div><!-- .entry-header-image -->
			<?php endif;
			
			if($is_edit=="1"):?>
			<!-- Front end edit upload image-->
			<div id="directory_detail_img" class="entry-header-image">
			<!--editing post images -->
			<div id="slider" class="listing-image flexslider frontend_edit_image flex-viewport">
			<ul class="frontend_edit_images_ul">
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
			<span><?php _e("Upload Images", DIR_DOMAIN); ?></span>					
			</div>
			</div>
			
			<div id="frontend_images_gallery_container" class="clearfix flex-viewport">
			<ul class="frontend_images_gallery more_photos slides">
			<?php
			if(!empty($post_img)):                				
			foreach($post_img as $key=>$value):
			echo "<li class='image' data-attachment_id='".basename($value['file'])."' data-attachment_src='".$value['file']."'><img src='".$value['file']."' alt='".$img_title."' /><span>
			<a class='delete' title='Delete image' href='#' id='".$value['id']."' >Delete</a></span></li>";
			endforeach;
			endif;	
			?>
			</ul>
			<input type="hidden" id="fontend_image_gallery" name="fontend_image_gallery" value="<?php echo esc_attr( substr(@$image_gallery,0,-1) ); ?>" />		
			</div>
			<span id="forntend_status" class="message_error2 clearfix"></span>
			<!--finish editing post images -->
			</div>
			<?php endif;?>
			</section>
			
            	<!-- event content-->
               <section class="entry-content">
               <?php 
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
					$more_listing_img = bdw_get_images_plugin($post_id,'event-single-thumb');
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
					
					<!--Event Share Link Coding Start -->
					<?php tevolution_socialpost_link($post);
					?>
					<!--Event Share Link Coding End -->
				 
					<dl class="tmpl-accordion" data-accordion>

								<dd class="tmpl-accordion-navigation active"><a href="#overview"><?php _e('Event Description',EDOMAIN);?></a></a>
									<div id="overview" class="content active">
										 <?php 
										 do_action('event_before_post_content');       /* do action for before the post content. */ 
			   
				
										 do_action('templ_post_single_content');       /*do action for single post content */
										 
										 do_action('event_after_post_content');       /* do action for before the post content. */ 
			   
				
										 ?>
									</div><!-- end .entry-content -->

								</dd>
								<?php 

								if($templatic_settings['direction_map']=='yes'):?>									
									<dd class="tmpl-accordion-navigation"><a href="#locations_event_map"><?php _e('Map',EDOMAIN);?></a>
										<div id="locations_event_map" class="content">
											<?php if($templatic_settings['direction_map']=='yes'):?>
												  <!--Map Section Start -->
												   <?php echo do_action('event_single_page_map'); ?>
												  <!--Map Section End -->
											 <?php endif; ?>
										</div>
									</dd>
								<?php endif;
								  
								  if(($special_offer!="" && $tmpl_flds_varname['proprty_feature'] ) || ($is_edit==1 && $tmpl_flds_varname['proprty_feature']) ):?>
										<dd class="tmpl-accordion-navigation"><a href="#proprty_feature"><?php _e('Special Offer',EDOMAIN);?></a>
                                           
                                           <div id="proprty_feature" class="content">
                                            <?php
                                                $special_offer = apply_filters( 'the_content', $special_offer );
                                                $special_offer = str_replace( ']]>', ']]&gt;', $special_offer );
                                                echo $special_offer;
                                            ?>
                                            </div>
                                            <!--Special Offer End -->
                                            </div>
										</dd>	
                                        <?php endif; 
										
										
										if($video!="" && $tmpl_flds_varname['video'] || ($is_edit==1 &&  $tmpl_flds_varname['video'])):?>
											<dd class="tmpl-accordion-navigation"><a href="#event_video"><?php _e('Video',EDOMAIN);?></a>
											<!--Video Code Start -->
													 <div id="event_video" class="content"><?php             
															$embed_video= wp_oembed_get( $video);            
															if($embed_video!=""){
																echo $embed_video;
															}else{
																echo $video;
															}
													?></div>
											<!--Video code End -->
											</dd>
										 <?php endif; ?>


										<?php do_action('show_listing_event');
								
								do_action('dir_end_mobile_tabs');
								
								/* organize informations */
								$org_name=get_post_meta($post_id,'organizer_name',true);
								$org_address=get_post_meta($post_id,'organizer_address',true);
								$org_contact=get_post_meta($post_id,'organizer_contact',true);
								$org_mobile=get_post_meta($post_id,'organizer_mobile',true);
								$org_email=get_post_meta($post_id,'organizer_email',true);
								$org_website=get_post_meta($post_id,'organizer_website',true);
								$org_desc=get_post_meta($post_id,'organizer_desc',true);
								$org_logo=get_post_meta($post_id,'organizer_logo',true);
								$reg_desc=get_post_meta($post_id,'reg_desc',true);
									
								if($org_name!='' || $org_address!='' || $org_contact!='' || $org_mobile!='' || $org_email!='' || $org_website!='' || $org_desc!=''):
								?>
                                
                                
                                <dd class="tmpl-accordion-navigation">
                                	<a href="#organizers"><?php _e('Organizers',EDOMAIN);?></a>
                                	
									<div class="event-organizer content" id="organizers">										 
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
											<p class="email <?php echo $tmpl_flds_varname["organizer_email"]["style_class"]; ?>"><label><?php  echo $tmpl_flds_varname["organizer_email"]["label"]; ?>:</label><span class="label_data frontend_organizer_email" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo antispambot($org_email)?></span></p>
										<?php endif;?>
										 
										 <?php if($org_website!='' && $tmpl_flds_varname['organizer_website'] || ($is_edit==1 && $tmpl_flds_varname['organizer_website'])):?>
										 <p class="website <?php echo $tmpl_flds_varname["organizer_website"]["style_class"]; ?>"><label><?php echo $tmpl_flds_varname["organizer_website"]["label"]; ?>:</label><span class="label_data frontend_organizer_website" <?php if($is_edit==1):?> contenteditable="true" <?php endif;?>><?php echo $org_website?></span></p>
										 <?php endif;?>
										 
										 <p class="org_description">
											<label><?php echo $tmpl_flds_varname["organizer_desc"]["label"] ?></label>
											<span class="label_data frontend_organizer_website"><?php echo strip_tags($org_desc); ?></span>
										 </p>
										 </div>
										 
									</div>
									
                                </dd>
                                <?php endif; 
								
								
								/* Event registration description */		
				
								if(isset($reg_desc) && $reg_desc!='' && @$tmpl_flds_varname['reg_desc'] || ($is_edit==1  && @$tmpl_flds_varname['reg_desc'])): ?>
								<dd class="tmpl-accordion-navigation">
									<a href="#reg_desc"><?php echo $tmpl_flds_varname['reg_desc']['label'];?></a>
                                	
									<div class="content" id="reg_desc">
										<?php
											$reg_desc = apply_filters( 'the_content', $reg_desc );
											$reg_desc = str_replace( ']]>', ']]&gt;', $reg_desc );
											echo $reg_desc;
										?>
									</div>
								</dd>
								<?php endif;
								
								
								/* add action for display before the post comments. */
								do_action('tmpl_before_comments');  

								comments_template( get_template_directory().'/mobile-templates/comments.php', true ); // Loads the comments.php template. 

								/*Add action for display after the post comments. */
								do_action('tmpl_after_comments'); 								
								
								?>
                                
					</dl>

					
					
					<?php do_action('show_listing_event_detail'); 
					
					do_action('listing_extra_details'); 

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
								if($i==0 && $field!='' ){echo '<h2 class="custom_field_headding">'.$heading_key.'</h2>';$i++;}
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
						<p class='<?php echo $k;?>'><label><?php echo $val['label']; ?>:&nbsp; </label> <span <?php if($is_edit==1):?>id="frontend_multicheckbox_<?php echo $k;?>" <?php endif;?> class="multicheckbox"><?php echo substr($checkbox_value,0,-1);?></span></p>

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
						<p class='<?php echo $k;?>'><label><?php echo $val['label']; ?>:&nbsp; </label><span <?php if($is_edit==1):?>id="frontend_radio_<?php echo $k;?>" <?php endif;?>><?php echo $rado_value;?></span></p>
						<?php
										}
									}
								elseif($val['type']=='oembed_video' && ($field || $is_edit==1)):?>
									<p class='<?php echo $val['style_class'];?>'><label><?php echo $val['label']; ?>:&nbsp;</label>
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
										<p class="<?php echo $val['style_class'];?>"><label><?php echo $val['label']; ?>: </label>
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
											<label><?php echo $val['label']; ?>:&nbsp;</label>
											
											<span <?php if($is_edit==1):?>id="frontend_<?php echo $val['type'].'_'.$k;?>" contenteditable="true" class="frontend_<?php echo $k;?>" <?php endif;?>>
												<?php echo date(get_option('date_format'),strtotime($field));?>
											</span>
											
										</p>
									
									<?php }else{
										?>
										<p class='<?php echo $val['style_class'];?>'>
											<label><?php echo $val['label']; ?>:&nbsp;</label>
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
               </section>
               <!--Finish the listing Content -->
              
     			
     		<!--Custom field collection do action -->
     		<?php do_action('event_custom_fields_collection');

			wp_reset_query();
			
			do_action('tmpl_single_post_pagination'); /* add action for display the next previous pagination */ 
			
			do_action( 'after_entry' );
            global $post;
            ?>
               
     		</div>
		   <?php do_action('event_after_post_loop');
		   
	endwhile; // end of the loop. ?>

	
    
</div><!-- #content -->
<!-- end  content part-->
<?php get_footer(); ?>