<?php
/**
 * Tevolution single custom post type Preview Page template
 *
**/
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-tabs');
$cur_post_type = get_post_meta($cur_post_id,'submit_post_type',true);
$tmpdata = get_option('templatic_settings');	
$cur_post_id = $_REQUEST['cur_post_id'];
$tmpdata = get_option('templatic_settings');	
$date_formate=get_option('date_format');
$time_formate=get_option('time_format');
$st_date=date($date_formate,strtotime($_REQUEST['st_date']));
$end_date=date($date_formate,strtotime($_REQUEST['end_date']));
$st_time=date($time_formate,strtotime($_REQUEST['st_time']));
$end_time=date($time_formate,strtotime($_REQUEST['end_time']));
$time=$st_time.' '.__('To',EDOMAIN).' '.$end_time;
$reg_fees=$_REQUEST['reg_fees'];
$address=$_REQUEST['address'];
$geo_latitude =$_REQUEST['geo_latitude'];
$geo_longitude = $_REQUEST['geo_longitude'];
$map_type =$_REQUEST['map_view'];
$website=$_REQUEST['website'];
$phone=$_REQUEST['phone'];
$listing_logo=$_REQUEST['listing_logo'];
$listing_timing=$_REQUEST['listing_timing'];
$email=$_REQUEST['email'];
$special_offer=$_REQUEST['proprty_feature'];
$video=$_REQUEST['video'];
$facebook=$_REQUEST['facebook'];
$google_plus=$_REQUEST['google_plus'];
$twitter=$_REQUEST['twitter'];
$org_name=$_REQUEST['organizer_name'];
$org_address=$_REQUEST['organizer_address'];
$org_contact=$_REQUEST['organizer_contact'];
$org_mobile=$_REQUEST['organizer_mobile'];
$org_email=$_REQUEST['organizer_email'];
$org_website=$_REQUEST['organizer_website'];
$org_desc=$_REQUEST['organizer_desc'];
$org_logo=$_REQUEST['organizer_logo'];
$reg_desc=$_REQUEST['reg_desc'];
$_REQUEST['imgarr'] = explode(",",$_REQUEST['imgarr']);
/* Set curent language in cookie */
if(is_plugin_active('wpml-translation-management/plugin.php')){
	global $sitepress;
	$_COOKIE['_icl_current_language'] = $sitepress->get_current_language();
}
?>
<script id="tmpl-foudation" src="<?php echo TEMPL_PLUGIN_URL; ?>js/foundation.min.js"> </script>
<div id="content" role="main">	
	
     <div class="event type-event event-type-preview hentry" >  
       
         <header class="entry-header">
            <div class="entry-header-title">
                 <h1 itemprop="name" class="entry-title"><?php echo stripslashes($_REQUEST['post_title']); ?></h1>
            
                  <div class="entry-header-custom-wrap">
                      <div class="entry-header-custom-left">
                           <?php if($st_date!='' && $end_date!=''):?>
                                <p class="date"><label><?php _e('Start Date:',EDOMAIN);?></label><span class="event_custom"><?php echo $st_date;?></span></p>
                                <p class="date"><label><?php _e('End Date:',EDOMAIN);?></label><span class="event_custom"><?php echo $end_date;?></span></p>
                           <?php endif;?>
                           
                            <?php if($st_time!='' && $end_time!=''):?>
                                <p class="time"><label><?php _e('Time:',EDOMAIN);?></label><span class="event_custom"><?php echo $time;?></span></p>
                           <?php endif;?>  
                           <?php if($reg_fees!=''):?>
                            <p class="fees"><label><?php _e('Fees:',EDOMAIN);?></label><span class="event_custom"><?php echo $reg_fees;?></span></p>
                           <?php endif;?>                                  
                           <?php do_action('directory_display_custom_fields_preview_default_left'); ?>
                      </div>
                      <div class="entry-header-custom-right">
                        <?php if($address!=""):?>
                            <p class="address"><label><?php _e('Location: ',EDOMAIN);?></label><span class="event_custom"><?php echo $address;?></span></p>
                           <?php endif;?>
                           <?php if($website!=""):?>
                            <p class="website"><label><?php _e('Website:',EDOMAIN);?></label><span class="event_custom"><a href="<?php echo $website;?>"><?php echo $website;?></a></span></p>
                           <?php endif;?>
                           <?php do_action('directory_display_custom_fields_preview_default_right'); ?> 
                      </div>
                      
            	</div>
			</div>
         </header>
        		<!-- listing content-->
               <div class="entry-content">
	          <?php do_action('event_preview_before_post_content'); /*Add Action for after preview post content. */?>
               	
                          <ul class="tabs" data-tab role="tablist">
						      <?php if(!empty($_REQUEST['imgarr'])):?>
                              <li class="tab-title active"><a href="#image_gallery"><?php _e('Photos',EDOMAIN);?></a></li>
                              <?php endif;?>
                              
							  <?php if($address!=''):?>
                              <li class="tab-title "><a href="#event_map"><?php _e('Map',EDOMAIN);?></a></li>
                              <?php endif;?>                              
                              
							  <?php if($video!="" ):?>
                              <li class="tab-title" role="presentational"><a href="#event_video" role="tab" tabindex="2" aria-selected="false" controls="event_video"><?php _e('Video',EDOMAIN);?></a></li>
                              <?php endif;
                              
                              do_action('dir_end_tabs_preview'); ?>
                         </ul>

                        <div class="tabs-content">                     
                    	   <?php if($address!=''):?>
                              <!--Map Section Start -->
                              <section role="tabpanel" aria-hidden="false" class="content " id="event_map">
                                   <div id="event_location_map" style="width:100%;">
                                        <div class="event_google_map" id="event_google_map_id" style="width:100%;"> 
                                        <?php include_once (TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/google_map_detail.php');?> 
                                        </div>  <!-- google map #end -->
                                   </div>
                              </section>
                              <!--Map Section End -->
                         <?php endif; ?>  
                          <section role="tabpanel" aria-hidden="false" class="content active" id="image_gallery">
                           <?php if(!empty($post_img)):?>
                                   <!--Image gallery Section Start -->
                                  
                                        <div class="event_image">
                                        <?php 			
                                        if ( has_post_thumbnail()):
                                             the_post_thumbnail('event-single-image'); 
                                        else:
                                             ?>
                                             <a href="<?php echo $image_attributes[0];?>" class="listing_img">
                                             <?php if($thumb_img):?>
                                                  <img src="<?php echo $thumb_img; ?>"  alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" />                    
                                             <?php endif;?>
                                             </a>	
                                         <?php endif;?>
                                        </div>
                                        
                                        <?php if(!empty($more_listing_img) && count($more_listing_img)>1):?>
                                             <div id="gallery" class="preview_more_images">
                                                  <ul class="more_photos">
                                                  <?php foreach($more_listing_img as $key=>$value):
                                                            $attachment_id = $value['id'];
                                                            $attach_data = get_post($attachment_id);
                                                            $image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array							
                                                            $img_title = $attach_data->post_title;
                                                  ?>
                                                       <li>
                                                             <a href="<?php echo $image_attributes['0'];?>" title="<?php echo $img_title; ?>" alt="<?php echo $img_alt; ?>" >		
                                                                 <img src="<?php echo $value['file'];?>" />
                                                             </a>
                                                       </li>
                                                            
                                                  <?php endforeach;?>
                                                  </ul>
                                             </div>
                                        <?php endif;?>                                        
                                  
          			 <!--Image gallery Section End -->
                    	 <?php endif;?>
						 
                          <?php if(!empty($_REQUEST['imgarr'])):?>
                          	
                              <!--Image gallery Section Start -->
                              <?php	
                          							$thumb_img_counter = 0;
                          							$thumb_img_counter = $thumb_img_counter+count($_REQUEST['imgarr']);
                          							$image_path = get_image_phy_destination_path_plugin();
                          							$tmppath = "/".$upload_folder_path."tmp/";						
                          							foreach($_REQUEST['imgarr'] as $image_id=>$val):
                          								 $thumb_image = get_template_directory_uri().'/images/tmp/'.trim($val);
                          								break;
                          							endforeach;	
                          							
                          							if(isset($_REQUEST['pid']) && $_REQUEST['pid']!="")
                          							{	/* exicute when comes for edit the post */
                          								$large_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'event-single-image');
                          								$thumb_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'thumb');
                          								$largest_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'event-single-thumb');		
                          							}
                          							if(!isset($_REQUEST['pid'])):
                          						 ?>	
                                                         <div class="event_image">
                          							 <?php $f=0; foreach($_REQUEST['imgarr'] as $image_id=>$val):
                                                                       $curry = date("Y");
                                                                       $currm = date("m");
                                                                       $src = get_template_directory().'/images/tmp/'.$val;
                                                                       $img_title = pathinfo($val);
                                                                       
                                                               ?>
                                                                  <?php if($largest_img_arr): ?>
                                                                            <?php  foreach($largest_img_arr as $value):
                                                                                  $name = end(explode("/",$value['file']));
                                                                                   if($val == $name):	
                                                                            ?>
                                                                                 <img src="<?php echo  $value['file'];?>" alt="" width="855" height="440" class="Thumbnail thumbnail large post_imgimglistimg"/>
                                                                            <?php endif;
                                                                                 endforeach;?>
                                                                  <?php else: ?>								
                                                                       <img src="<?php echo $thumb_image;?>" alt="" width="855" height="440" class="Thumbnail thumbnail large post_imgimglistimg"/>
                                                                  <?php endif; ?>    
                                                               <?php if($f==0) break; endforeach;?>
                                                              </div>
                                                      		 <?php endif;?>
                          							 <?php $f=0; foreach($_REQUEST['imgarr'] as $image_id=>$val):
                                                                       $curry = date("Y");
                                                                       $currm = date("m");
                                                                       $src = get_template_directory().'/images/tmp/'.$val;
                                                                       $img_title = pathinfo($val);
                                                              if($f==0) break; endforeach;
                          								if(count(array_filter($_REQUEST['imgarr']))>1):?>					
                          							 <div id="gallery" class="image_title_space preview_more_images">
                          								<ul class="more_photos">
                          								 <?php foreach($_REQUEST['imgarr'] as $image_id=>$val)
                          									{
                          										$curry = date("Y");
                          										$currm = date("m");
                          										$src = get_template_directory().'/images/tmp/'.$val;
                          										$img_title = pathinfo($val);						
                          										if($val):
                          										if(file_exists($src)):
                          												 $thumb_image = get_template_directory_uri().'/images/tmp/'.$val; ?>
                          												 <li><a href="<?php echo $thumb_image;?>" title="<?php echo $img_title['filename']; ?>"><img src="<?php echo $thumb_image;?>" alt="" height="70" width="70" title="<?php echo $img_title['filename'] ?>" /></a></li>
                          										<?php else: ?>
                          											<?php
                          												if($largest_img_arr):
                          												foreach($largest_img_arr as $value):
                          													$name = end(explode("/",$value['file']));									
                          													if($val == $name):?>
                          													<li><a href="<?php echo $value['file']; ?>" title="<?php echo $img_title['filename']; ?>"><img src="<?php echo $value['file'];?>" alt="" height="70" width="70" title="<?php echo $img_title['filename'] ?>" /></a></li>
                          											<?php
                          													endif;
                          												endforeach;
                          												endif;
                          											?>
                          										<?php endif; ?>
                          										
                          										<?php else: ?>
                          										<?php if($thumb_img_arr): ?>
                          											<?php 
                          											$thumb_img_counter = $thumb_img_counter+count($thumb_img_arr);
                          											for($i=0;$i<count($thumb_img_arr);$i++):
                          												$thumb_image = $large_img_arr[$i];
                          												
                          												if(!is_array($thumb_image)):
                          											?>
                          											  <li><a href="<?php echo $thumb_image;?>" title="<?php echo $img_title['filename']; ?>"><img src="<?php echo $thumb_image;?>" alt="" height="70" width="70" title="<?php echo $img_title['filename'] ?>" /></a></li>
                          											  <?php endif;?>
                          										<?php endfor; ?>
                          										<?php endif; ?>	
                          										<?php endif; ?>
                          									<?php
                          									$thumb_img_counter++;
                          									} ?>
                          									
                          									</ul>
                          							 </div>                 
                                   <?php endif;?>
                                   <!-- -->
                              
                              <!--Image gallery Section End -->
                         <?php endif;?>      
                         </section>
                         
                        <?php if($video!=""):?>
                            <!--Video Code Start -->
                            <section role="tabpanel" aria-hidden="false" class="content" id="event_video">
                            <?php             
                            $embed_video= wp_oembed_get( $video);            
                            if($embed_video!=""){
                            echo $embed_video;
                            }else{
                            echo $video;
                            }
                            ?>
                            </section>
                        	<!--Video code End -->
                        <?php endif;?>
                         
                         <?php do_action('listing_end_preview');?>
                    </div>               
               	
               
               <div itemprop="description" class="entry-content">
               	<h2><?php _e('Event Description',EDOMAIN);?></h2>
               	 <?php if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=''){echo stripslashes($post_content);} else{ echo stripslashes($_REQUEST['post_content']); }?>
               </div><!-- end .entry-content -->
               <?php if($org_name!='' || $org_address!='' || $org_contact!='' || $org_mobile!='' || $org_email!='' || $org_website!=''):?>
			<div class="event-organizer">
				<h2><?php _e('Organizers',EDOMAIN);?></h2>
				
				<?php if($org_logo):?>
					<div class="event-organizer-left">
						<img src="<?php echo $org_logo;?>"  />
					</div>
				<?php endif;?>
				
				<div class="event-organizer-right">
				<?php if($org_name!=''):?>
				<p class="org_name"><label><?php _e('Organized by:',EDOMAIN);?></label><span class="label_data"><?php echo $org_name?></span></p>
				<?php endif;?>
				
				<?php if($org_address!=''):?>
				<p class="address"><label><?php _e('Organizers Address:',EDOMAIN);?></label><span class="label_data"><?php echo $org_address?></span></p>
				<?php endif;?>
				
				<?php if($org_contact!=''):?>
				<p class="phone"><label><?php _e('Organizers Contact Info:',EDOMAIN);?></label><span class="label_data"><?php echo $org_contact?></span></p>
				<?php endif;?>
				
				<?php if($org_mobile!=''):?>
				<p class="phone"><label><?php _e('Mobile:',EDOMAIN);?></label><span class="label_data"><?php echo $org_mobile?></span></p>
				<?php endif;?>
				
				<?php if($org_email!=''):?>
				<p class="email"><label><?php _e('Email:',EDOMAIN);?></label><span class="label_data"><?php echo $org_email?></span></p>
				<?php endif;?>
				
				<?php if($org_website!=''):?>
				<p class="website"><label><?php _e('Website:',EDOMAIN);?></label><span class="label_data"><?php echo $org_website?></span></p>
				<?php endif;?>
				</div>
			</div>			
			<?php endif;?>
               
               <?php if($org_desc!=''):?>
                    <div class="abput-event-organizer">
                    <h2><?php _e('About the Organizers',EDOMAIN);?></h2>
                    <?php echo stripslashes($org_desc);?>
                    </div>
               <?php endif;?>
               
               <?php if($reg_desc!=''):?>
               <p class="reg_desc">
               <?php echo stripslashes($reg_desc);?>
               </p>
               <?php endif; ?>			   
			   
			
               <div class="single-social-media">                    
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=xa-4c873bb26489d97f"></script>
                    <?php if($facebook!=""):?>
                          <a href="<?php echo $facebook;?>" id="facebook"><i class="fa fa-facebook"></i> Facebook</a>
                     <?php endif;?>
                     
                     <?php if($twitter!=""):?>
                          <a href="<?php echo $twitter;?>" id="twitter"><i class="fa fa-twitter"></i> Twitter</a>
                     <?php endif;?>
                     
                     <?php if($google_plus!=""):?>
                          <a href="<?php echo $google_plus;?>" id="google_plus"><i class="fa fa-google-plus"></i> Google Plus</a>
                     <?php endif;?>
               </div>
               
        		<?php do_action('preview_after_post_content'); /*Add Action for after preview post content. */?>
	          </div>
               <!--Finish the listing Content -->
     		
               <?php do_action('event_preview_page_fields_collection');?>     
         
     </div>
</div>

<script type="text/javascript"> 
jQuery(function(){jQuery("ul.tabs li a").live('click',function(){
  var n=jQuery(this).attr("href");
  if(n=="#event_map")
  {
    Demo.init();
  }
 })});
</script>