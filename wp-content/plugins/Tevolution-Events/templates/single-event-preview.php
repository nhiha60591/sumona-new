<?php
/**
 * Tevolution single custom post type template
 *
**/
get_header(); //Header Portation
global $upload_folder_path;
if(isset($_POST['preview'])){
	
	$_SESSION['custom_fields'] = $_POST; // set custom_fields session	
	if(isset($_POST['category']))
	 {
		$_SESSION['category'] = $_POST['category'];
	 }
}
if(isset($_POST['imgarr']) && $_POST['imgarr']!=""){
	$_SESSION['file_info'] = explode(",",$_POST['imgarr']);	
	$_SESSION["templ_file_info"] = explode(",",$_POST['imgarr']);
}
if(isset($_FILES) && !empty($_FILES) && !strstr($_SERVER['REQUEST_URI'],'/wp-admin/'))
{
	foreach($_FILES as $key => $FILES)
	 {	
	 	if($FILES['name']!=''){
			$_SESSION['upload_file'][$key] = get_file_upload($_FILES[$key]);
		}
	 }
	 
}	
if($_SESSION["file_info"])
{
	foreach($_SESSION["file_info"] as $image_id=>$val)
	{
		 $image_src =  get_template_directory_uri().'/images/tmp/'.$val;
		 break;
	}				
	
}else
{
	/* exucutre when come after go back nad edit */
	$image_src = @$thumb_img_arr[0];
	if($_REQUEST['pid']){
		$large_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'medium');
		$thumb_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'thumb');
	}
	$image_src = $large_img_arr[0];		
}
if($_REQUEST['pid'])
{	/* exicute when comes for edit the post */
	$large_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'medium');
	$thumb_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'thumb');
	$largest_img_arr = bdw_get_images_plugin($_REQUEST['pid'],'large');		
}
$current_user = wp_get_current_user();
$cur_post_id = $_SESSION['custom_fields']['cur_post_id'];
$cur_post_type = get_post_meta($cur_post_id,'submit_post_type',true);
$tmpdata = get_option('templatic_settings');	
$date_formate=get_option('date_format');
$time_formate=get_option('time_format');
$st_date=date($date_formate,strtotime($_SESSION['custom_fields']['st_date']));
$end_date=date($date_formate,strtotime($_SESSION['custom_fields']['end_date']));
$st_time=date($time_formate,strtotime($_SESSION['custom_fields']['st_time']));
$end_time=date($time_formate,strtotime($_SESSION['custom_fields']['end_time']));
$time=$st_time.' '.__('To',EDOMAIN).' '.$end_time;
$reg_fees=$_SESSION['custom_fields']['reg_fees'];
$address=$_SESSION['custom_fields']['address'];
$geo_latitude =$_SESSION['custom_fields']['geo_latitude'];
$geo_longitude = $_SESSION['custom_fields']['geo_longitude'];
$map_type =$_SESSION['custom_fields']['map_view'];
$website=$_SESSION['custom_fields']['website'];
$phone=$_SESSION['custom_fields']['phone'];
$listing_logo=$_SESSION['upload_file']['listing_logo'];
$listing_timing=$_SESSION['custom_fields']['listing_timing'];
$email=$_SESSION['custom_fields']['email'];
$special_offer=$_SESSION['custom_fields']['proprty_feature'];
$video=$_SESSION['custom_fields']['video'];
$facebook=$_SESSION['custom_fields']['facebook'];
$google_plus=$_SESSION['custom_fields']['google_plus'];
$twitter=$_SESSION['custom_fields']['twitter'];
$org_name=$_SESSION['custom_fields']['organizer_name'];
$org_address=$_SESSION['custom_fields']['organizer_address'];
$org_contact=$_SESSION['custom_fields']['organizer_contact'];
$org_mobile=$_SESSION['custom_fields']['organizer_mobile'];
$org_email=$_SESSION['custom_fields']['organizer_email'];
$org_website=$_SESSION['custom_fields']['organizer_website'];
$org_desc=$_SESSION['custom_fields']['organizer_desc'];
$org_logo=$_SESSION['upload_file']['organizer_logo'];
$reg_desc=$_SESSION['custom_fields']['reg_desc'];
if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=''){
	$address=get_post_meta($_REQUEST['pid'],'address',true);
	$st_date=date($date_formate,strtotime(get_post_meta($_REQUEST['pid'],'st_date',true)));
	$end_date=date($date_formate,strtotime(get_post_meta($_REQUEST['pid'],'end_date',true)));
	$st_time=date($time_formate,strtotime(get_post_meta($_REQUEST['pid'],'st_time',true)));
	$end_time=date($time_formate,strtotime(get_post_meta($_REQUEST['pid'],'end_time',true)));
	$time=$st_time.' '.__('To',EDOMAIN).' '.$end_time;
	$reg_fees=get_post_meta($_REQUEST['pid'],'reg_fees',true);
	$post_content = get_post($_REQUEST['pid']);
	$post_content = $post_content->post_content;
	$address=get_post_meta($_REQUEST['pid'],'address',true);
	$geo_latitude =get_post_meta($_REQUEST['pid'],'geo_latitude',true);
	$geo_longitude = get_post_meta($_REQUEST['pid'],'geo_longitude',true);
	$map_type =get_post_meta($_REQUEST['pid'],'map_type',true);
	$website=get_post_meta($_REQUEST['pid'],'website',true);
	$phone=get_post_meta($_REQUEST['pid'],'phone',true);
	$listing_logo=get_post_meta($_REQUEST['pid'],'listing_logo',true);
	$listing_timing=get_post_meta($_REQUEST['pid'],'listing_timing',true);
	$email=get_post_meta($_REQUEST['pid'],'email',true);
	$special_offer=get_post_meta($_REQUEST['pid'],'proprty_feature',true);
	$video=get_post_meta($_REQUEST['pid'],'video',true);
	$facebook=get_post_meta($_REQUEST['pid'],'facebook',true);
	$google_plus=get_post_meta($_REQUEST['pid'],'google_plus',true);
	$twitter=get_post_meta($_REQUEST['pid'],'twitter',true);
	$org_name=get_post_meta($_REQUEST['pid'],'organizer_name',true);
	$org_address=get_post_meta($_REQUEST['pid'],'organizer_address',true);
	$org_contact=get_post_meta($_REQUEST['pid'],'organizer_contact',true);
	$org_mobile=get_post_meta($_REQUEST['pid'],'organizer_mobile',true);
	$org_email=get_post_meta($_REQUEST['pid'],'organizer_email',true);
	$org_website=get_post_meta($_REQUEST['pid'],'organizer_website',true);
	$org_desc=get_post_meta($_REQUEST['pid'],'organizer_desc',true);
	$org_logo=get_post_meta($_REQUEST['pid'],'organizer_logo',true);
	$reg_desc=get_post_meta($_REQUEST['pid'],'reg_desc',true);
}
//contion for captcha inserted properly or not.
$tmpdata = get_option('templatic_settings');
if(isset($tmpdata['user_verification_page']) && $tmpdata['user_verification_page'] != "")
{
	$display = $tmpdata['user_verification_page'];
}
else
{
	$display = "";	
}
$id = $_SESSION['custom_fields']['cur_post_id'];
$permalink = get_permalink( $id );
if( is_plugin_active('wp-recaptcha/wp-recaptcha.php') && $tmpdata['recaptcha'] == 'recaptcha' && in_array('submit',$display) && 'preview' == @$_REQUEST['page'] && 'delete' != $_REQUEST['action'] ){
		require_once( WP_CONTENT_DIR.'/plugins/wp-recaptcha/recaptchalib.php');
		$a = get_option("recaptcha_options");
		$privatekey = $a['private_key'];
						$resp = recaptcha_check_answer ($privatekey,
								getenv("REMOTE_ADDR"),
								$_POST["recaptcha_challenge_field"],
								$_POST["recaptcha_response_field"]);
											
		if (!$resp->is_valid ) {
			if($_REQUEST['pid'] != '')
			 {
				wp_redirect(get_permalink($cur_post_id).'/?ptype=post_event&pid='.$_REQUEST['pid'].'&action=edit&backandedit=1&ecptcha=captch');
			 }
			 else
			 {
				wp_redirect(get_permalink($cur_post_id).'/?ptype=post_event&backandedit=1&ecptcha=captch');	 
			 }
			exit;
		} 
	}
if(file_exists(get_template_directory_uri().'are-you-a-human/areyouahuman.php') && is_plugin_active('are-you-a-human/areyouahuman.php') && $tmpdata['recaptcha'] == 'playthru'  && in_array('submit',$display) && 'preview' == @$_REQUEST['page'] && 'delete' != $_REQUEST['action'] )
{
	require_once( get_template_directory_uri().'are-you-a-human/areyouahuman.php');
	require_once(get_template_directory_uri().'are-you-a-human/includes/ayah.php');
	$ayah = new AYAH();
	$score = $ayah->scoreResult();
	if(!$score)
	{
		wp_redirect(get_permalink($cur_post_id).'/?ptype=post_event&backandedit=1&invalid=playthru');
		exit;
	}
}
if(function_exists('bdw_get_images_plugin') && (isset($_REQUEST['pid']) && $_REQUEST['pid']!=''))
{
	$post_img = bdw_get_images_plugin($_REQUEST['pid'],'event-single-image');
	$postimg_thumbnail = bdw_get_images_plugin($_REQUEST['pid'],'thumbnail');
	$more_listing_img = bdw_get_images_plugin($_REQUEST['pid'],'event-single-thumb');
	$thumb_img = $post_img[0]['file'];
	$attachment_id = $post_img[0]['id'];
	$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array
	$attach_data = get_post($attachment_id);
	$img_title = $attach_data->post_title;
	$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
	
}
wp_enqueue_script('jquery-ui-tabs');
?>
<link rel='stylesheet' id='event_style-css'  href='<?php echo TEVOLUTION_EVENT_URL?>css/event.css?ver=3.5.2' type='text/css' media='all' />		
<?php do_action('fetch_event_preview_field');?>
<!-- start content part-->
<div id="content" class="large-9 small-12 columns" role="main">	
	
     <div id="post-<?php the_ID(); ?>" class="event type-event event-type-preview hentry" >  
     
		<?php include (TEMPL_MONETIZE_FOLDER_PATH . "templatic-custom_fields/submit_preview_buttons.php"); /* fetch publish options and button options */?> 
          
         <header class="entry-header">
                    <section class="entry-header-title">
                         <h1 itemprop="name" class="entry-title"><?php echo stripslashes($_SESSION['custom_fields']['post_title']); ?></h1>
					
                          <article class="entry-header-custom-wrap">
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
                              
                         </article>
                    </section>
               </header>
        		<!-- listing content-->
               <section class="entry-content">
	          <?php do_action('event_preview_before_post_content'); /*Add Action for after preview post content. */?>
               	<div id="tabs">
                    	 <ul>
						<?php if($address!=''):?>
                              <li><a href="#locations_map"><?php _e('Map',EDOMAIN);?></a></li>
                              <?php endif;?>
                              
                              <?php if(!empty($_SESSION['file_info']) || !empty($post_img)):?>
                              <li><a href="#image_gallery"><?php _e('Photos',EDOMAIN);?></a></li>
                              <?php endif;?>
                              <?php do_action('dir_end_tabs_preview'); ?>
                         </ul>                    
                    	 <?php if($address!=''):?>
                              <!--Map Section Start -->
                              <div id="locations_map">
                                   <div id="event_location_map" style="width:100%;">
                                        <div class="event_google_map" id="event_google_map_id" style="width:100%;"> 
                                        <?php include_once (TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/google_map_detail.php');?> 
                                        </div>  <!-- google map #end -->
                                   </div>
                              </div>
                              <!--Map Section End -->
                         <?php endif; ?>  
                          <div id="image_gallery">
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
						 
                          <?php if(!empty($_SESSION["file_info"])):?>
                          	
                              <!--Image gallery Section Start -->
                              <?php	
							$thumb_img_counter = 0;
							$thumb_img_counter = $thumb_img_counter+count($_SESSION["file_info"]);
							$image_path = get_image_phy_destination_path_plugin();
							$tmppath = "/".$upload_folder_path."tmp/";						
							foreach($_SESSION["file_info"] as $image_id=>$val):
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
							 <?php $f=0; foreach($_SESSION["file_info"] as $image_id=>$val):
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
							 <?php $f=0; foreach($_SESSION["file_info"] as $image_id=>$val):
                                             $curry = date("Y");
                                             $currm = date("m");
                                             $src = get_template_directory().'/images/tmp/'.$val;
                                             $img_title = pathinfo($val);
                                    if($f==0) break; endforeach;
								if(count(array_filter($_SESSION["file_info"]))>1):?>					
							 <div id="gallery" class="image_title_space preview_more_images">
								<ul class="more_photos">
								 <?php foreach($_SESSION["file_info"] as $image_id=>$val)
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
                         </div>
                         <?php do_action('listing_end_preview');?>
                    </div>               
               	
               
               <div itemprop="description" class="entry-content">
               	<h2><?php _e('Event Description',EDOMAIN);?></h2>
               	 <?php if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=''){echo stripslashes($post_content);} else{ echo stripslashes($_SESSION['custom_fields']['post_content']); }?>
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
               <?php endif;?>
               
               
               <?php if($video!=""):?>
               <!--Video Code Start -->
               <div id="event_video">
                    <?php echo str_replace('\"','',$video);?>
               </div>
               <!--Video code End -->
               <?php endif;?>
			
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
	          </section>
               <!--Finish the listing Content -->
     		
               <?php do_action('event_preview_page_fields_collection');?>
     
          <div id="back-top" class="get_direction clearfix">
               <a href="#top" class="button getdir" style=""><?php _e('Back to Top',EDOMAIN);?></a>
          </div>
     
     </div>
</div>
<!--End content part -->
<!--single post type sidebar -->
<?php if ( is_active_sidebar('listing_detail_sidebar' ) ) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar( 'listing_detail_sidebar' ); ?>		
	</aside>
	<?php
elseif ( is_active_sidebar( 'primary-sidebar') ) : ?>
	<aside id="sidebar-primary" class="sidebar large-3 small-12 columns">
		<?php dynamic_sidebar('primary-sidebar'); ?>
	</aside>
<?php endif; ?>
<!--end single post type sidebar -->
<script type="text/javascript">
jQuery(function() {
	jQuery( "#tabs" ).tabs();
});
jQuery(function() {
	jQuery('#image_gallery a').lightBox();
});
jQuery('#tabs').bind('tabsshow', function(event, ui) {	
    if (ui.panel.id == "listing_map") {	    
		google.maps.event.trigger(Demo.map, 'resize');
		Demo.map.setCenter(Demo.map.center); // be sure to reset the map center as well
		Demo.init();
    }
});
</script>
<!-- end  content part-->
<?php get_footer(); ?>
