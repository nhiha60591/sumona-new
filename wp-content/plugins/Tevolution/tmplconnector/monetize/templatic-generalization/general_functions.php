<?php	
/*
 * include the generalization css in header
 */
add_action( 'wp_enqueue_scripts', 'tevolution_general_function' );
function tevolution_general_function(){	
	if(is_single()){		
		wp_enqueue_style('general-style', TEMPL_PLUGIN_URL.'tmplconnector/monetize/templatic-generalization/css/style.css' );
		wp_enqueue_script("generalization-basic",TEMPL_PLUGIN_URL.'tmplconnector/monetize/templatic-generalization/js/basic.js','','',true);
	}
}
/*
 *To Include the sent to friend form in footer,It will open after click on sent to friend button
 */
function send_email_to_friend()
{	
	wp_reset_postdata();
	include_once(TEMPL_MONETIZE_FOLDER_PATH."templatic-generalization/popup_frms.php");
}
/*
 * include popup_inquiry_frm.php
 */
function send_inquiry()
{
	wp_reset_postdata();
	include_once(TEMPL_MONETIZE_FOLDER_PATH."templatic-generalization/popup_inquiry_frm.php");	
}
/* start code to add add to favourites on author dash board */
add_action('init','tevolution_author_favourites_tab');
function tevolution_author_favourites_tab(){
	if(current_theme_supports('tevolution_my_favourites')){		
		add_action('tevolution_author_tab','tmpl_dashboard_favourites_tab'); // to display tab 
	}
}
/* code to add add to favourites on author dash board */
function tmpl_dashboard_favourites_tab(){
	global $current_user,$curauth,$wp_query;	
	$qvar = $wp_query->query_vars;
	$author = $qvar['author'];
	if(isset($author) && $author !='') :
		$curauth = get_userdata($qvar['author']);
	else :
		$curauth = get_userdata(intval($_REQUEST['author']));
	endif;	
	if(isset($_REQUEST['sort']) && $_REQUEST['sort'] =='favourites'){
		$class = 'active';
	}else{
		$class ='';
	}
	
	if($current_user->ID == $curauth->ID){
		echo "<li role='presentational' class='tab-title ".$class."'><a class='author_post_tab ' href='".esc_url(get_author_posts_url($current_user->ID).'?sort=favourites&custom_post=all')."'>";
		echo _e('My Favorites',DOMAIN);
		echo "</a></li>";
	}
	
}
/* add filter to fetch favourites post listing on admin dashboard page */
if(isset($_REQUEST['sort']) && $_REQUEST['sort'] =='favourites'){
	global $current_user,$curauth,$wp_query,$sitepress;
	add_filter('posts_join','tevolution_favourites_post_join',12);
	add_filter('posts_where','tevolution_favourites_post',12);
}
/*
*start function to list - favourites post on dashboard 
*/
function tevolution_favourites_post_join($join){

	global $wpdb, $pagenow, $wp_taxonomies,$ljoin,$sitepress;
	$language_where='';	
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		$post_types=get_option('templatic_custom_post');
		$posttype='';		
		foreach($post_types as $key=>$value){
			$posttype.="'post_".$key."',";
		}
		$posttype=substr($posttype,0,-1);
		$language = ICL_LANGUAGE_CODE;
		$join = " {$ljoin} JOIN {$wpdb->prefix}icl_translations t1 ON {$wpdb->posts}.ID = t1.element_id			
			AND t1.element_type IN (".$posttype.") JOIN {$wpdb->prefix}icl_languages l1 ON t1.language_code=l1.code AND l1.active=1 AND t1.language_code='".$language."'";
	}
	return $join;
}

/*
* start function to list - favourites post on dashboard
*/
function tevolution_favourites_post(){
	global $wpdb,$current_user,$curauth,$wp_query;
	
	$where = '';
	$query_var = $wp_query->query_vars;
	$user_id = $query_var['author'];
	$post_ids = get_user_meta($current_user->ID,'user_favourite_post',true);
	$final_ids = '';
	if(!empty($post_ids))
	{
		$post_ids = implode(",",$post_ids);
	}
	else
	{
	 	$post_ids = "''";
	}
	$qvar = $wp_query->query_vars;
	$authname = $qvar['author_name'];
	$curauth = get_userdata($qvar['author']);
	$nicename = $current_user->user_nicename;
	
	if($_REQUEST['sort']=='favourites')	{
		$where .= " AND ($wpdb->posts.ID in ($post_ids))";			
	}else
	{	
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			$language = ICL_LANGUAGE_CODE;
			$where = " AND ($wpdb->posts.post_author = $user_id)  AND t.language_code='".$language."'";
		}else{
			$where = " AND ($wpdb->posts.post_author = $user_id) ";
		}
	}	
	return $where;
}
/*
* to fetch the server date and time
*/
add_action('tevolution_details','tevolution_server_date_time');
function tevolution_server_date_time() {
	
	
	$tev_time_now = date("D dS M, Y h:i a");
	$timezone_now = date("e, (T P)");
	echo "<p id='server-date-time'><strong>".__('Server Date/Time',ADMINDOMAIN).":</strong> $tev_time_now <br/><strong>".__('Time Zone',ADMINDOMAIN).": </strong> $timezone_now</p>";
	
}
/* 
* get tevolution version details
*/
function tevolution_version() {
	
	$plugin_file = get_tmpl_plugin_directory()."Tevolution/templatic.php";
	$plugin_details = get_plugin_data( $plugin_file, $markup = true, $translate = true ); 
	$version = @$plugin_details['Version'];
	echo " <span class='tevolution_version'>".@$version."<span>";
}

/*
* Set Default permalink on theme activation: end
*/
if(isset($_POST['Verify']) && $_POST['Verify'] !=''){
	global $wp_version;
	$arg=array('method' => 'POST',
			 'timeout' => 45,
			 'redirection' => 5,
			 'httpversion' => '1.0',
			 'blocking' => true,
			 'headers' => array(),
			 'body' => array( 'licencekey' => $_POST['licencekey'],'action'=>'licensekey_verification'),
			 'user-agent' => 'WordPress/'. $wp_version .'; '. home_url(),
			 'cookies' => array()
		);
	$warnning_message='';
	$response = wp_remote_get('http://templatic.net/api/verification/index.php',$arg );
	if(!is_wp_error( $response ) ) {
		update_option('templatic_licence_key',$response['body']);
		if(isset($_POST['licencekey']) && $_POST['licencekey'] !=''){ 
			if(strstr($response['body'],'error_message')){
				update_option('templatic_licence_key_','');
							
				add_action('tevolution_error_message','tevolution_error_message_error');
			}else{ 
				update_option('templatic_licence_key_',$_POST['licencekey']);
				add_action('tevolution_error_message','tevolution_error_message_success');
			}
		}else{
			update_option('templatic_licence_key_','');
			add_action('tevolution_error_message','tevolution_error_message_error');
		}
	}else{
		
		update_option('templatic_licence_key','{"error_message":"WP HTTP Error: couldn\'t connect to host."}');
		add_action('tevolution_error_message','tevolution_error_message_host');
	}
	
}else{
	if(!get_option('templatic_licence_key_')){
		add_action('tevolution_error_message','tevolution_error_message_error');
	}
}
/*
* show error message if tevolution licence key is wrong
*/
$templatic_licence_key = get_option('templatic_licence_key');
if(strstr($templatic_licence_key,'is_supreme') && get_option('templatic_licence_key_') !='' && !$_POST){
	add_action('tevolution_error_message','tevolution_key_is_verified');
}
function tevolution_error_message_host(){
	echo "<span>".__("WP HTTP Error: couldn't connect to host.",ADMINDOMAIN)."</span>";
}
function tevolution_key_is_verified(){
	echo "<span class='dashicons dashicons-yes'></span>";
}
/* 
* tevolution licence key error message 
*/
function tevolution_error_message_error($message){
	if(isset($_POST['Verify']) && $_POST['Verify'] !=''){
		echo "<span style='color:red;' >"; 
			$error_message=json_decode(get_option('templatic_licence_key'));				
			if($error_message){
				echo base64_decode($error_message->error_message);
			}
		echo "</span>";
	}
	echo "<p>".__('The key can be obtained from Templatic',ADMINDOMAIN)." <a href='http://templatic.com/members/member'>".__('member area',ADMINDOMAIN)."</a></p>";
}
/* 
* tevolution licence key success message 
*/
function tevolution_error_message_success(){
	echo "<span style='color:green;'>";
	$success_message=json_decode(get_option('templatic_licence_key'));	
	if(!empty($success_message))	
		echo base64_decode(@$success_message->success_message);
	echo "</span>";
}
/*
* show meesaage while licence key is not verified in front end.
*/
add_action('wp_head','tevolution_licence_message');
function tevolution_licence_message(){
	if(!is_admin() && !strstr($_SERVER['REQUEST_URI'],'wp-admin/')){
		$templatic_licence_key = get_option('templatic_licence_key');
		if(strstr($templatic_licence_key,'error_message') || !get_option('templatic_licence_key_')){
			if(!get_option('templatic_licence_key_'))
			{
				echo "<h2>".__('Your copy of Tevolution hasn&#32;t been verified yet. To verify the plugin and unlock the site please <a href="'.admin_url( 'admin.php?page=templatic_system_menu').'" style="color:red;">click here</a> to verify your licence key',DOMAIN)."</h2>";
			}else{
				echo "<h2>".__('You are not allowed to run this site, because of invalid licence key. <a href="'.admin_url( 'admin.php?page=templatic_system_menu').'">click here</a> to verify your valid licence key',DOMAIN)."</h2>";
			}
			die;
		}
	}
}
/*
 * send inquiry mail function 
 */
add_action('wp_ajax_tevolution_send_inquiry_form','tevolution_send_inquiry_form');
add_action('wp_ajax_nopriv_tevolution_send_inquiry_form','tevolution_send_inquiry_form');
function tevolution_send_inquiry_form(){
	global $wpdb;
	$post = array();
	if( @$_REQUEST['postid'] ){
		$post = get_post($_REQUEST['postid']);
	}
	if(isset($_REQUEST['your_iemail']) && $_REQUEST['your_iemail'] != "")
	{	
		/* CODE TO CHECK WP-RECAPTCHA */
		$tmpdata = get_option('templatic_settings');
		$display = $tmpdata['user_verification_page'];
		if(file_exists(get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php') && is_plugin_active('wp-recaptcha/wp-recaptcha.php') && in_array('sendinquiry',$display))
		{
			require_once( get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php');
			$a = get_option("recaptcha_options");
			$privatekey = $a['private_key'];
			$resp = recaptcha_check_answer ($privatekey,getenv("REMOTE_ADDR"),$_REQUEST["recaptcha_challenge_field"],$_REQUEST["recaptcha_response_field"]);						
								
			if ($resp->is_valid =="")
			{
				echo '1';
				exit;
			}
		}
		/* END OF CODE - CHECK WP-RECAPTCHA */	
		$yourname = $_REQUEST['full_name'];
		$youremail = $_REQUEST['your_iemail'];
		$contact_num = $_REQUEST['contact_number'];
		$frnd_subject = $_REQUEST['inq_subject'];
		$frnd_comments = $_REQUEST['inq_msg'];
		$post_id = $_REQUEST['listing_id'];	
		$to_email = (get_post_meta($post->ID,'email',true)!="")? get_post_meta($post->ID,'email',true): get_the_author_meta( 'user_email', $post->post_author )  ;
		$userdata = get_userdata($post->post_author);
		$to_name = $userdata->data->display_name;
		if($post_id != "")
		{
			$productinfosql = "select ID,post_title from $wpdb->posts where ID ='".$post_id."'";
			$productinfo = $wpdb->get_results($productinfosql);
			foreach($productinfo as $productinfoObj)
			{
				$post_title = stripslashes($productinfoObj->post_title); 
			}
		}
		/*Inquiry EMAIL START*/
		global $General;
		global $upload_folder_path;
		$store_name = '<a href="'.site_url().'">'.get_option('blogname').'</a>';
		$tmpdata = get_option('templatic_settings');	;
		$email_subject = stripslashes($tmpdata['send_inquirey_email_sub']);
		$email_content = stripslashes($tmpdata['send_inquirey_email_description']);	
		
		
		if($email_content == "" && $email_subject=="")
		{
			$message1 =  __('[SUBJECT-STR]You might be interested in [SUBJECT-END]
			<p>Dear [#to_name#],</p>
			<p>[#frnd_comments#]</p>
			<p>Link : <b>[#post_title#]</b> </p>
			<p>Contact number : [#contact#]</p>
			<p>From, [#your_name#]</p>
			<p>Sent from -[#$post_url_link#]</p></p>',DOMAIN);
			$filecontent_arr1 = explode('[SUBJECT-STR]',$message1);
			$filecontent_arr2 = explode('[SUBJECT-END]',$filecontent_arr1[1]);
			$subject = $filecontent_arr2[0];
			if($subject == '')
			{
				$subject = $frnd_subject;
			}
			$client_message = $filecontent_arr2[1];
		} else {
			$client_message = $email_content;
		}
		$subject = stripslashes($frnd_subject);
	
		$post_url_link = '<a href="'.$_REQUEST['link_url'].'">'.$post_title.'</a>';
		/*customer email*/
		$yourname_link = __('<b><a href="'.get_option('siteurl').'">'.get_option('blogname').'</a></b>.',DOMAIN);
		$search_array = array('[#to_name#]','[#frnd_subject#]','[#post_title#]','[#frnd_comments#]','[#your_name#]','[#$post_url_link#]','[#contact#]');
		$replace_array = array($to_name,$frnd_subject,$post_url_link,$frnd_comments,$yourname,$yourname_link,$contact_num);
		$client_message = str_replace($search_array,$replace_array,$client_message,$contact_num); 
		
		templ_send_email($youremail,$yourname,$to_email,$to_name,$subject,stripslashes($client_message),$extra='');///To clidne email
		/*Inquiry EMAIL END*/
		$post = "";
		if(get_option('siteurl').'/' == $_REQUEST['request_uri']){
				_e('Email sent successfully',DOMAIN);
				exit;
		} else {
				_e('Email sent successfully',DOMAIN);
				exit;
		}
		
	}
}
/*
 * send to friend email function 
 */
add_action('wp_ajax_tevolution_send_friendto_form','tevolution_send_friendto_form');
add_action('wp_ajax_nopriv_tevolution_send_friendto_form','tevolution_send_friendto_form');
function tevolution_send_friendto_form(){
	
	global $wpdb,$General,$upload_folder_path,$post;
	$postdata = array();
	if( @$_REQUEST['post_id']!="" ){
		$postdata = get_post($_REQUEST['post_id']);
	}
	if( @$_REQUEST['yourname'] )
	{
		/* CODE TO CHECK WP-RECAPTCHA */
		$tmpdata = get_option('templatic_settings');
		$display = $tmpdata['user_verification_page'];
		if(file_exists(get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php') && is_plugin_active('wp-recaptcha/wp-recaptcha.php') && in_array('emaitofrd',$display))
		{
			require_once( get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php');
			$a = get_option("recaptcha_options");
			$privatekey = $a['private_key'];
			$resp = recaptcha_check_answer ($privatekey,getenv("REMOTE_ADDR"),$_REQUEST["recaptcha_challenge_field"],$_REQUEST["recaptcha_response_field"]);						
				
			if ($resp->is_valid=="")
			{
				echo '1';
				exit;					
			}				
		}

		
		/* END OF CODE - CHECK WP-RECAPTCHA */	
		$yourname = $_REQUEST['yourname'];
		$youremail = $_REQUEST['youremail'];
		$frnd_subject = $_REQUEST['frnd_subject'];
		$frnd_comments = $_REQUEST['frnd_comments'];
		$to_friend_email = $_REQUEST['to_friend_email'];
		$to_name = $_REQUEST['to_name_friend'];
		/*Inquiry EMAIL START*/
		global $General,$wpdb;
		global $upload_folder_path;
		$post_title = stripslashes($postdata->post_title);
		$tmpdata = get_option('templatic_settings');	;
		$email_subject =$tmpdata['mail_friend_sub'];
		$email_content =$tmpdata['mail_friend_description'];
		
		
		if($email_content == "" && $email_subject=="")
		{
			$message1 =  __('[SUBJECT-STR]You might be interested in [SUBJECT-END]
			<p>Dear [#to_name#],</p>
			<p>[#frnd_comments#]</p>
			<p>Link : <b>[#post_title#]</b> </p>
			<p>From, [#your_name#]</p>',DOMAIN);
			$filecontent_arr1 = explode('[SUBJECT-STR]',$message1);
			$filecontent_arr2 = explode('[SUBJECT-END]',$filecontent_arr1[1]);
			$subject = $filecontent_arr2[0];
			if($subject == '')
			{
				$subject = $frnd_subject;
			}
			$client_message = $filecontent_arr2[1];
		}else
		{
			$client_message = $email_content;
		}
		$subject = $frnd_subject;
		$post_url_link = '<a href="'.$_REQUEST['link_url'].'">'.$post_title.'</a>';
		/*customer email*/
		
		$search_array = array('[#to_name#]','[#post_title#]','[#frnd_comments#]','[#your_name#]','[#post_url_link#]');
		$replace_array = array($to_name,$post_url_link,nl2br($frnd_comments),$yourname,$post_url_link);
		$client_message = str_replace($search_array,$replace_array,$client_message);	
		templ_send_email($youremail,$yourname,$to_friend_email,$to_name,$subject,stripslashes($client_message),$extra='');///To clidne email
		
		/*Inquiry EMAIL END*/
		_e('Email sent successfully',DOMAIN);
		exit;
	}
		
}
/*
* pop up box for licensekey on admin side.
*/
add_action('admin_init','tevolution_licensekey_popupbox');
function tevolution_licensekey_popupbox(){
	global $pagenow;	
	if($pagenow=='themes.php' || ($pagenow=='admin.php' && isset($_REQUEST['page']) && $_REQUEST['page']=='templatic_system_menu')){
		$templatic_licence_key=get_option('templatic_licence_key_');
		if(($pagenow=='themes.php' &&  $templatic_licence_key=='') || $templatic_licence_key==''){
			?>
			<div id="boxes" class="licensekey_boxes">
				<div style="top:0px; left: 551.5px; display: none;" id="dialog" class="window">
                    	<span class="close"><a href="#" class="close"><span class="dashicons dashicons-no close-btn"></span></a></span>
					<h2><?php echo __('Licence key',ADMINDOMAIN); ?></h2>
                         <form action="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu";?>" name="" method="post">
                         <div class="inside">
                         <p><?php echo __('Enter the license key in order to unlock the plugin and enable automatic updates.',ADMINDOMAIN); ?></p>
						 <div id="licence_fields">
                                   <input type="password" name="licencekey" id="licencekey" value="<?php echo get_option('templatic_licence_key_'); ?>" size="30" max-length="36" PLACEHOLDER="templatic.com purchase code"/>
                                   <input type="submit" accesskey="p" value="<?php echo __('Verify',ADMINDOMAIN);?>" class="button button-primary button-large" id="Verify" name="Verify">
                                   <?php do_action('tevolution_error_message'); ?>
						</div>
                         </div>
                         </form>
				</div>
				<!-- Mask to cover the whole screen -->
				<div style="width: 1478px; height: 602px; display: none; opacity: 0.8;" id="mask"></div>
			</div>
			<?php
		}
	}
}

/*
* return the plugin directory path
*/
if(!function_exists('get_tmpl_plugin_directory')){
function get_tmpl_plugin_directory() {
	 return WP_CONTENT_DIR."/plugins/";
}
}


/*
* Add add to favourite html for detail page 
*/
if(!function_exists('tmpl_detailpage_favourite_html')){
function tmpl_detailpage_favourite_html($user_id,$post)
{
	global $current_user,$post;
	$add_to_favorite = __('Add to favorites',DOMAIN);
	$added = __('Added',DOMAIN);
	if(function_exists('icl_register_string')){
		icl_register_string(DOMAIN,'directory'.$add_to_favorite,$add_to_favorite);
		$add_to_favorite = icl_t(DOMAIN,'directory'.$add_to_favorite,$add_to_favorite);
		icl_register_string(DOMAIN,'directory'.$added,$added);
		$added = icl_t(DOMAIN,'directory'.$added,$added);
	}
	$post_id = $post->ID;
	
	$user_meta_data = get_user_meta($current_user->ID,'user_favourite_post',true);
	if($post->post_type !='post'){
		if($user_meta_data && in_array($post_id,$user_meta_data))
		{
			
			?>
			<li id="tmplfavorite_<?php echo $post_id;?>" class="fav_<?php echo $post_id;?> fav"  >
				<?php do_action('tmpl_before_rfav'); ?>
                
				<a href="javascript:void(0);" class="removefromfav" data-id='<?php echo $post_id; ?>'  onclick="javascript:addToFavourite('<?php echo $post_id;?>','remove');"><i class="fa fa-heart"></i><?php echo $added;?>
				</a>
				<?php do_action('tmpl_after_rfav'); ?>
			</li>    
			<?php
		}else{
			if($current_user->ID ==''){
				$data_reveal_id ='data-reveal-id="tmpl_reg_login_container"';
			}else{
				$data_reveal_id ='';
			}
		?>
		<li id="tmplfavorite_<?php echo $post_id;?>" class="fav_<?php echo $post_id;?> fav">
			<?php do_action('tmpl_before_addfav'); ?>
			<a href="javascript:void(0);" <?php echo $data_reveal_id; ?> class="addtofav" data-id='<?php echo $post_id; ?>'   onclick="javascript:addToFavourite('<?php echo $post_id;?>','add');"><i class="fa fa-heart-o"></i><?php echo $add_to_favorite;?></a>
			<?php do_action('tmpl_before_addfav'); ?>
		</li>
		<?php } 
	}
}
}

/* 
* add action for send to friend and send inquiry email - specially in tevolution templates 
*/
add_action('templ_after_post_content','tevolution_dir_popupfrms');
if(!function_exists('tevolution_dir_popupfrms')){
	function tevolution_dir_popupfrms($post){
		global $current_user,$post;
		$tmpdata = get_option('templatic_settings');	
		$link='';	
		
		/* Claim ownership link */
		if(is_single())
		{
			if(isset($tmpdata['claim_post_type_value'])&& @in_array($post->post_type,$tmpdata['claim_post_type_value']) && function_exists('tmpl_claim_ownership') && @$post->post_author!=@$current_user->ID)
			{
				/*
					We add filter here so if you are creating a child theme and don't want to show here, then just remove from child theme.
					e.g. add_filter('tmpl_allow_claimlink_inlist',0);
				*/
				$allow_claim = apply_filters('tmpl_allow_claimlink_inlist',1);
				do_action('tmpl_before_claim');
				if($allow_claim && get_post_meta($post->ID,'is_verified',true) !=1){
					echo '<li class="claim_ownership">';
					echo	do_shortcode('[claim_ownership]');
					echo '</li>';
				}
			}
			
			if(isset($tmpdata['send_to_frnd'])&& $tmpdata['send_to_frnd']=='send_to_frnd' && function_exists('send_email_to_friend'))
			{
				/*
					We add filter here so if you are creating a child theme and don't want to show here, then just remove from child theme.
					e.g. add_filter('tmpl_sent_to_frd_link','');
				*/
				do_action('tmpl_before_send_tofrd');
				$send_to_frnd=	apply_filters('tmpl_sent_to_frd_link','<a class="small_btn tmpl_mail_friend" data-reveal-id="tmpl_send_to_frd" href="javascript:void(0);" id="send_friend_id"  title="Mail to a friend" >'. __('Send to friend',DOMAIN).'</a>');				
				
				add_action('wp_footer','send_email_to_friend',10);
				echo "<li>".$send_to_frnd.'</li>';
			}
				
			/* sent inquiry link*/
			
			if(isset($tmpdata['send_inquiry'])&& $tmpdata['send_inquiry']=='send_inquiry' && function_exists('send_inquiry'))
			{		
				/*
					We add filter here so if you are creating a child theme and don't want to show here, then just remove from child theme.
					e.g. add_filter('tmpl_send_inquiry_link','');
				*/
				do_action('tmpl_before_send_inquiry');
				$send_inquiry=	apply_filters('tmpl_send_inquiry_link','<a class="small_btn tmpl_mail_friend" data-reveal-id="tmpl_send_inquiry"  href="javascript:void(0)" title="Send Inquiry" id="send_inquiry_id" >'.__('Send inquiry',DOMAIN).'</a>');
				add_action('wp_footer','send_inquiry');		
				echo '<li class="send_inquiry">'.$send_inquiry.'</li>';
			} 
		
			/* Add to favourites */
			if(current_theme_supports('tevolution_my_favourites') && ($post->post_status == 'publish' )){
				global $current_user;
				$user_id = $current_user->ID;
				do_action('tmpl_before_addtofav');
				$link.= apply_filters('tmpl_add_to_favlink',tmpl_detailpage_favourite_html($user_id,@$post));
				echo $link;
				
			}
		}
	}
}
/*
* return the social media links of current post
*/
if(!function_exists('tevolution_socialpost_link')){
	function tevolution_socialpost_link($post){
		global $htmlvar_name;
		$is_edit='';
		if(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'){
			$is_edit=1;
		}
		$facebook=get_post_meta($post->ID,'facebook',true);
		$facebook_show = apply_filters('tmpl_fb_share_link',1);
		$google_plus=get_post_meta($post->ID,'google_plus',true);
		$google_plus_show = apply_filters('tmpl_google_plus_share_link',1);
		$twitter=get_post_meta($post->ID,'twitter',true);
		$twitter_show=apply_filters('tmpl_twitter_share_link',1);
		echo '<div class="share_link">';
		do_action('tmpl_after_social_share_link');
		if($facebook!="" && $facebook_show && @$htmlvar_name['contact_info']['facebook'] || ($is_edit==1 && @$htmlvar_name['contact_info']['facebook'])):
		if(!strstr($facebook,'http'))
			$facebook = 'http://'.$facebook;
		?>
	 	<span><a id="facebook" class="frontend_facebook <?php if($is_edit==1):?>frontend_link <?php endif;?>" href="<?php echo $facebook;?>"><i class="fa fa-facebook"></i> Facebook</a></span>
		<?php endif;
	 
		if($twitter!="" && @$htmlvar_name['contact_info']['twitter'] && $twitter_show ==1 || ($is_edit==1 && @$htmlvar_name['contact_info']['twitter'])):
	 	if(!strstr($twitter,'http'))
			$twitter = 'http://'.$twitter;
	 	?>
	 	<span><a id="twitter" class="frontend_twitter <?php if($is_edit==1):?>frontend_link <?php endif;?>" href="<?php echo $twitter;?>"><i class="fa fa-twitter"></i> Twitter</a></span>
		<?php endif;?>

		<?php if($google_plus!="" && @$htmlvar_name['contact_info']['google_plus'] && $google_plus_show ==1 || ($is_edit==1 && @$htmlvar_name['contact_info']['google_plus'])):
	 	if(!strstr($google_plus,'http'))
			$google_plus = 'http://'.$google_plus;
		?>
		<span><a id="google_plus" class="frontend_google_plus <?php if($is_edit==1):?>frontend_link <?php endif;?>" href="<?php echo $google_plus;?>"><i class="fa fa-google-plus"></i> Google Plus</a></span>
		<?php endif;
		do_action('tmpl_after_social_share_link');
	echo '</div>';
	}
}
/*
* Social media share link
*/
if(!function_exists('tevolution_socialmedia_sharelink')){
function tevolution_socialmedia_sharelink($post){
	$tmpdata = get_option('templatic_settings');	
	$title=($post->post_title);
	$post_img = bdw_get_images_plugin($post->ID,'thumb');
	$post_images = @$post_img[0]['file'];
	$url=(get_permalink($post->ID));
	$image=$post_images;
	if(@$tmpdata['google_share_detail_page'] == 'yes' || @$tmpdata['twitter_share_detail_page'] == 'yes' || @$tmpdata['pintrest_detail_page']=='yes')
	{?>
	<ul class='social-media-share'>
	<?php if($tmpdata['facebook_share_detail_page'] == 'yes') { ?>
		<li><div class="facebook_share" data-url="<?php echo $url; ?>" data-text="<?php echo $title; ?>"></div></li>
	<?php }
	
	if($tmpdata['twitter_share_detail_page'] == 'yes'): ?>
		<li><div class="twitter_share"  data-url="<?php echo $url; ?>" data-text="<?php echo $title; ?>"></div></li> 
	<?php endif; 
	if($tmpdata['google_share_detail_page'] == 'yes'): ?>
	    <li><div class="googleplus_share" href="javascript:void(0);"  data-url="<?php echo $url; ?>" data-text="<?php echo $title; ?>"></div></li>
	<?php endif;

	if(@$tmpdata['pintrest_detail_page']=='yes'):?>
		<li><div class="pinit_share" data-href="http://pinterest.com/pin/create/button/?url=<?php urlencode(the_permalink()); ?>" data-media="<?php echo $post_images; ?>" data-description="<?php the_title(); ?> - <?php the_permalink(); ?>"></div></li>
	<?php endif; ?>   
	</ul>
	
<?php 	
		
		} 
	}
}

/*
* check whether file is writable or not.
*/
function is_writeable_file($path) {

	/* PHP's is_writable does not work with Win32 NTFS */
	/* recursively return a temporary file path */
	if ($path{strlen($path)-1}=='/') 
		return is_writeable_file($path.uniqid(mt_rand()).'.tmp');
	else if (is_dir($path))
		return is_writeable_file($path.'/'.uniqid(mt_rand()).'.tmp');
	/* check tmp file for read/write capabilities */
	$rm = file_exists($path);
	$f = @fopen($path, 'a');
	if ($f===false)
		return false;
	fclose($f);
	if (!$rm)
		unlink($path);
	return true;
}
/*
* tevolution replace domains to old to new new to old
*/
function tevolution_replace_line($old, $new, $my_file) {
	if ( @is_file( $my_file ) == false ) {
		return false;
	}
	if (!is_writeable_file($my_file)) {
		echo "Error: file $my_file is not writable.\n";
		return false;
	}
	

	$found = false;
	$lines = file($my_file);
	foreach( (array)$lines as $line ) {
	 	if ( preg_match("/$old/", $line)) {
			$found = true;
			break;
		}
	}
	if ($found) {
		$fd = fopen($my_file, 'w');
		foreach( (array)$lines as $line ) {
			if ( !preg_match("/$old/", $line))
				fputs($fd, $line);
			else {
				fputs($fd, "$new //tevolution* deprecated\n");
			}
		}
		fclose($fd);
		return true;
	}
	$fd = fopen($my_file, 'w');
	$done = false;
	foreach( (array)$lines as $line ) {
		if ( $done || !preg_match('/^(if\ \(\ \!\ )?define|\$|\?>/', $line) ) {
			fputs($fd, $line);
		} else {
			fputs($fd, "$new //tevolution* deprecated\n");
			fputs($fd, $line);
			$done = true;
		}
	}
	fclose($fd);
	return true;
}
/* 
* Change the localization slug if use wpml plugin
*/

add_action('admin_init','tevolution_localization_slugs');
function tevolution_localization_slugs(){

	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		if(!get_option('tevolution_localization'))
		{
			$found='';
			$files_to_search =  TEVOLUTION_PAGE_TEMPLATES_DIR.'templatic.php';
			$old = 'define\(\ \'DOMAIN';
			$old1 = 'define\(\ \'ADMINDOMAIN';
			$new ="\tdefine( 'DOMAIN', 'tevolution'); ";
			$new1 ="\tdefine( 'ADMINDOMAIN', 'tevolution' );";
			tevolution_replace_line($old,$new,$files_to_search);
			tevolution_replace_line($old1,$new1,$files_to_search);
			update_option('tevolution_localization','1');
		}	
		if(get_option('tevolution_localization') == 1)
		{
			add_action('admin_notices','tevolution_text_domain_message');
		}
	
		if(isset($_REQUEST['ch_domain']) && $_REQUEST['ch_domain'] =='domain'){
			$found='';
				$files_to_search =  TEVOLUTION_PAGE_TEMPLATES_DIR.'templatic.php';
				$old = 'define\(\ \'DOMAIN';
				$old1 = 'define\(\ \'ADMINDOMAIN';
				$new ="\tdefine( 'DOMAIN', 'templatic'); ";
				$new1 ="\tdefine( 'ADMINDOMAIN', 'templatic-admin' );";
				tevolution_replace_line($old,$new,$files_to_search);
				tevolution_replace_line($old1,$new1,$files_to_search);
				update_option('tevolution_localization',2);
		}
	}
}

/* 
* Change the localization slug if use wpml plugin
*/

function tevolution_text_domain_message(){
	$url = admin_url('index.php?ch_domain=domain');
	$message = "<div id=\"error\" class=\"updated\">\n";
	$message .= '<p>'.__('We see that you have WPML activated so we have kept the old text domain for you(its changed in the new version!) If you want to change the domain to a new one',ADMINDOMAIN).' <a href="'.admin_url('index.php?ch_domain=domain').'"> '.__('Click here',ADMINDOMAIN).' </a>. <br> <strong>'.__('Note',ADMINDOMAIN).':</strong>'.__('Unfortunately this will wipe out your translations and you will have to translate again! to continue with new localization text domain',ADMINDOMAIN).'</p>';
	$message .= do_action('localization_filter');
	$message .= "</div>";
	echo $message;
}

add_action('tevolution_subcategory','tevolution_subcategory'); // show post subcategories on category pages
/*
 *  display the sub categories in tevolution created post types
 */
 
if(!function_exists('tevolution_subcategory')){
	function tevolution_subcategory(){
		global $wpdb,$wp_query;	
		$current_term = $wp_query->get_queried_object();	
		
		$term_id = $wp_query->get_queried_object_id();
		$taxonomy_name = $current_term ->taxonomy;	
		do_action('tevolution_category_query');
		$featured_catlist_list =  wp_list_categories('title_li=&child_of=' . $term_id .'&echo=0&taxonomy='.$taxonomy_name.'&show_count=0&hide_empty=1&pad_counts=0&show_option_none=&orderby=name&order=ASC');
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			remove_filter( 'terms_clauses','locationwise_change_category_query',10,3 );	
		}
		if(!strstr(@$featured_catlist_list,'No categories'))
		{
			echo '<div id="tev_sub_categories">';
			echo '<ul>';
			echo $featured_catlist_list;
			echo '</ul>';
			echo '</div>';
		}
	}
}


/*
* search filters for all type of searches LIKE search by address OR near by search OR advance search
*/
add_action('init','tmpl_search_filters');
function tmpl_search_filters(){
	if(file_exists(TEMPL_MONETIZE_FOLDER_PATH.'templatic-generalization/search_filters.php')){
		include(TEMPL_MONETIZE_FOLDER_PATH.'templatic-generalization/search_filters.php');
	}
}


/*
* add alternative script to default wordpress theme
*/
add_action('init','add_alternative_files');
function add_alternative_files()
{
	if(!function_exists('tmpl_theme_css_scripts'))
	{
		wp_enqueue_script( 'tmpl-slider-js', trailingslashit ( TEMPL_PLUGIN_URL ) . 'js/jquery.flexslider.js', array( 'jquery' ), '20120606', true );
	}
}
/*
* fetch all the users for back end drop down list.
*/
add_filter('wp_dropdown_users', 'tmpl_theme_post_author_override');
function tmpl_theme_post_author_override($output) { 
	global $post; // return if this isn't the theme author override dropdown 
	if (!preg_match('/post_author_override/', $output)) return $output; // return if we've already replaced the list (end recursion) 
	if (preg_match ('/post_author_override_replaced/', $output)) return $output; // replacement call to wp_dropdown_users
	$output = wp_dropdown_users(array( 'echo' => 0, 'name' => 'post_author_override_replaced', 'selected' => empty($post->ID) ? $user_ID : $post->post_author, 'include_selected' => true )); // put the original name back 
	$output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output); return $output;
}


/*
* Print option for display view for listing page.(list,grid)
*/
add_action('admin_init','tmpl_default_view_settings');

function tmpl_default_view_settings(){
	/* DOING_AJAX is define then return false for admin ajax*/
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {		
		return ;	
	}
	/* Show default view option only if theme suppoted different views for theme */	
	if(current_theme_supports('tmpl_show_pageviews')){
		add_action('before_listing_page_setting','directory_before_listing_page_setting_callback');
		if(!function_exists('directory_before_listing_page_setting_callback')){ 
			function directory_before_listing_page_setting_callback(){ 
				$get_plug_data = get_option('templatic_settings');
				$googlemap_setting=get_option('city_googlemap_setting');
			?>
			<tr>
			  <th><label>
				  <?php echo __('Default page view',ADMINDOMAIN); ?>
				</label></th>
			  <td><label for="default_page_view1">
				  <input type="radio" id="default_page_view1" name="default_page_view" value="gridview" <?php if( @$get_plug_data['default_page_view']=='gridview') echo "checked=checked";?> />
				  <?php echo __('Grid',ADMINDOMAIN); ?>
				</label>
				&nbsp;&nbsp;
				<label for="default_page_view2">
				  <input type="radio" id="default_page_view2" name="default_page_view" value="listview" <?php if( @$get_plug_data['default_page_view']== "" || $get_plug_data['default_page_view']=='listview') echo "checked=checked";?> />
				  <?php echo __('List',ADMINDOMAIN); ?>
				</label>
				<?php do_action('tmpl_other_page_view_option'); ?>
			   </td>
			</tr>
			<?php
			}
		}
	}
	global $wpdb;
	$field_check = $wpdb->get_var("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_price'");
	if('term_price' != $field_check){
		$wpdb->query("ALTER TABLE $wpdb->terms ADD term_price varchar(100) NOT NULL DEFAULT '0'");
	}
}
/* 
* show home page display option with different post type.
*/
add_action('tmpl_start_general_settings','tmpl_start_generalsettings_options');
function tmpl_start_generalsettings_options(){ 
		do_action('tev_before_homepage_settings');
		$tmpdata =get_option('templatic_settings');
		/* show if current theme support - home page display with different post types OR not */
		if(current_theme_supports('theme_home_page') && get_option('show_on_front') =='posts'){
		?>
		<table class="tmpl-general-settings form-table" id="home_page_settings">
		<tr id="home_page_settings">
				<th colspan="2"><div class="tevo_sub_title"><?php echo __('Home page settings',ADMINDOMAIN); ?></div>
				</th>
		</tr> 
		<tr>
		<th><label><?php echo __('Homepage displays',ADMINDOMAIN); ?> </label></th>
			<td>
			<?php 
			$posttaxonomy = get_option("templatic_custom_post");
			if(!empty($posttaxonomy))
			{
				foreach($posttaxonomy as $key=>$_posttaxonomy):
					if($key == 'admanager')
						continue;
					?>
					<div class="element">
						<label for="home_listing_type_value_<?php echo $key; ?>"><input type="checkbox" name="home_listing_type_value[]" id="home_listing_type_value_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if(@$tmpdata['home_listing_type_value'] && in_array($key,$tmpdata['home_listing_type_value'])) { echo "checked=checked";  } ?>>&nbsp;<?php echo __($_posttaxonomy['label'],ADMINDOMAIN); ?></label>
					</div>
				<?php endforeach;  }
			else
			{
				$url = '<a target=\"_blank\" href='.admin_url("admin.php?page=custom_setup&ctab=custom_setup&action=add_taxonomy").'>';
				$url .= __('here',ADMINDOMAIN);
				$url .= '</a>'; 
				 echo __('Please create a custom post type from ',ADMINDOMAIN);
				 echo $url;
			}
			 do_action('templ_post_type_description');?>  <p class="description"><?php echo sprintf(__('For this option to work you must select set the "Front page displays" option within %s to "Your latest posts".',ADMINDOMAIN),'<a href="'.admin_url().'options-reading.php" target= "_blank">WordPress reading settings</a>');?></p>           
			</td>
		</tr>	
		<?php 
			$ordervalue = @$tmpdata['tev_front_page_order'];
			if($ordervalue ==''){ $ordervalue ='ddesc'; }
		?>
		<tr>
			<th><label><?php echo __('Sorting options for home page',ADMINDOMAIN); ?> </label></th>
			<td>
				<?php $orders = array('dasc'=>'Publish Date Ascending','ddesc'=>'Publish Date Descending','random'=>'Random','asc'=>'Title Ascending','desc'=>'Title Descending'); ?>
				<select name="tev_front_page_order" id="tev_front_page_order">
					<?php foreach($orders as $key=>$value){ ?>
							<option value="<?php echo $key; ?>" <?php if($key == @$ordervalue) { echo "selected=selected";  } ?> ><?php echo $value; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<p class="submit" style="clear: both;">
			
					<input type="submit" value="<?php _e('Save All Settings',ADMINDOMAIN); ?>" class="button button-primary button-hero" name="Submit">
				</p>
			</td>
		</tr>
		<?php }
		
		do_action('tev_after_homepage_settings');	?>
		</table>
		<?php
}


/* 
* add or remove posts from favourite 
*/
add_action('wp_ajax_tmpl_add_to_favourites','tmpl_add_to_favourites');
add_action('wp_ajax_nopriv_tmpl_add_to_favourites','tmpl_add_to_favourites');

/* add or remove post to your favourites */
/* previously this code was in - Tevolution\tmplconnector\monetize\templatic-generalization\ajax_event.php */

function tmpl_add_to_favourites()
{
	define( 'DOING_AJAX', true );
	require(ABSPATH."wp-load.php");
	if(isset($_REQUEST['ptype']) &&$_REQUEST['ptype'] == 'favorite'){
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global  $sitepress;
			$sitepress->switch_lang($_REQUEST['language']);
		}
		/* add to favoirites */
		if(isset($_REQUEST['action1']) && $_REQUEST['action1']=='add')	{
			if(isset($_REQUEST['st_date']) && $_REQUEST['st_date'] != '' && $_REQUEST['st_date'] != 'undefined' )
			{
				if(isset($_REQUEST['language']) && $_REQUEST['language'] != '')
				{
					add_to_favorite($_REQUEST['pid'],$_REQUEST['language']);exit;
				}
				else
				{
					add_to_favorite($_REQUEST['pid']);exit;
				}
			}
			else
			{
				if(isset($_REQUEST['language']) && $_REQUEST['language'] != '')
				{
					add_to_favorite($_REQUEST['pid'],$_REQUEST['language']);exit;
				}
				else
				{
					add_to_favorite($_REQUEST['pid']);exit;
				}
			}
		}
		/*  remove from favoirites */
		else{
			if(isset($_REQUEST['st_date']) && $_REQUEST['st_date'] != '' && $_REQUEST['st_date'] != 'undefined')
			{
				if(isset($_REQUEST['language']) && $_REQUEST['language'] != '')
				{
					remove_from_favorite($_REQUEST['pid'],$_REQUEST['language']);exit;
				}
				else
				{
					remove_from_favorite($_REQUEST['pid']);exit;
				}
			}
			else
			{
				if(isset($_REQUEST['language']) && $_REQUEST['language'] != '')
				{
					remove_from_favorite($_REQUEST['pid'],$_REQUEST['language']);exit;
				}
				else
				{
					remove_from_favorite($_REQUEST['pid']);exit;
				}
			}
		}
	}
}


/*
* Display the images in mobile view - to get the different images when mobile view is loaded
*/
function tmpl_mobile_archive_image($image_size='thumbnail'){
	global $post,$wpdb,$wp_query;
	
	$post_id = get_the_ID();
	if(get_post_meta($post_id,'_event_id',true)){
		$post_id=get_post_meta($post_id,'_event_id',true);
	}
	
	$featured=get_post_meta($post_id,'featured_c',true);
	$featured=($featured=='c')?'featured_c':'';
	 if ( has_post_thumbnail()):
		echo '<div class="event_img">';
		do_action('inside_listing_image');
		echo '<a href="'.get_permalink().'">';
		if($featured){echo '<span class="featured_tag">'.__('Featured',EDOMAIN).'</span>';}
		the_post_thumbnail($image_size); 
		echo '</a></div>';
	else:
		if(function_exists('bdw_get_images_plugin'))
		{
			$post_img = bdw_get_images_plugin($post_id,$image_size);						
			$thumb_img = @$post_img[0]['file'];
			$attachment_id = @$post_img[0]['id'];
			$attach_data = get_post($attachment_id);
			$img_title = $attach_data->post_title;
			$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
		}
		?>
		<div class="event_img"> 
			<?php do_action('inside_listing_image');?>
			<a href="<?php the_permalink();?>">
			<?php if($featured){echo '<span class="featured_tag">'.__('Featured',EDOMAIN).'</span>';}?>
			<?php if($thumb_img):?>
				<img itemprop="image" src="<?php echo $thumb_img; ?>"  alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" />
			<?php else:?>    
				<img itemprop="image" src="<?php echo TEMPL_PLUGIN_URL; ?>images/no-image.png" alt=""  />
			<?php endif;?>
			</a>	
		</div>
   <?php endif;
}

/* Check is user submitted post in passed post type in argument */

function tmpl_get_user_post_inposttype($post_type){
	global $post,$wp_query,$wpdb,$curauth;
	$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')):'';
	$posts = new $wp_query('author='.$curauth->ID.'&post_type='.$post_type);
	if($posts->have_posts()){
		return true;
	}else{
		return false;
	}
}

/* -------------------- Mobile view code --------------------*/
/*
	Check if device is mobile or not. Return true if mobile devie is detected
*/


/**
	Function located in wp-includes/vars.php, but sometimes it shows undefined
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @return bool true|false
 */
 
if(!function_exists('twp_is_mobile')){
	function twp_is_mobile() {
		static $is_mobile;

		if ( isset($is_mobile) )
			return $is_mobile;

		if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
			$is_mobile = false;
		} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
				$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return $is_mobile;
	}
}
if(!function_exists('tmpl_wp_is_mobile')){
	/*
	Check if device is mobile or not. Return true if mobile devie is detected
	*/
	function tmpl_wp_is_mobile(){
		if(function_exists('supreme_prefix')){  
			$pref = supreme_prefix();
		}else{
			$pref = sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
		}
		
		$theme_options = get_option($pref.'_theme_settings');
		
		$is_mobile_enabled= @$theme_options['tmpl_mobile_view'];
		if($is_mobile_enabled !=0 || $is_mobile_enabled ==''){
			$is_mobile_enabled=1;
		}

		
			if($is_mobile_enabled==1){
				if ( (twp_is_mobile() || (isset($_REQUEST['device']) && $_REQUEST['device']=='mobile')) && (!preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT'])) && !strstr('windows phone',$_SERVER['HTTP_USER_AGENT']))){ /* if not desktop then return true */	
					return true;
				}else{
					return false;
				}	
			}else{
				return false;
			}
	}
}


if ( function_exists('tmpl_wp_is_mobile') && tmpl_wp_is_mobile() ){
		
	/*
	* Same Way This function will return the taxonomy/category page template.
	*/ 
	add_filter( "archive_template", "tmpl_get_mob_archive_template",99) ;
	add_filter( "taxonomy_template", "tmpl_get_mob_archive_template",99) ; 
	add_filter( "single_template", "tmpl_get_mob_single_template",99) ;
	add_filter( "search_template", "tmpl_get_mob_archive_template",99) ;
	add_filter( "page_template", "tmpl_get_mob_page_template",99);
	add_filter( "author_template", "tmpl_get_mob_author_template",99);
	add_filter( "comments_template", "tmpl_plugin_comment_template",99);
	add_action( 'init', 'tmpl_mob_preview_template' ,10);
	add_filter('body_class', 'tmpl_body_class_for_mobile');
	add_action('author_box', 'tmpl_author_mobiledashboard');
}else{
	/* add the author box on author dashboard */
	add_action('author_box', 'tmpl_author_dashboard');
}
/* add class in  body when theme load in mobile */
function tmpl_body_class_for_mobile($classes) {
        $id = get_current_blog_id();
        $slug = strtolower(str_replace(' ', '-', trim(get_bloginfo('name'))));
        $classes[] = $slug;
        $classes[] = 'mobile-view';
        return $classes;
}

/* return add ons name from post type */

function tmpl_addon_name(){
	
	global $addons_posttype,$tmpl_addons_posttype;
	if(empty($tmpl_addons_posttype)){
		$tmpl_addons_posttype = array();
	}
	/* array of all templatic tevolution add-ons */
	$addons_posttype = apply_filters('tmpl_addon_of_posttypes',array('listing'=>'Directory','event'=>'Events','property'=>'RealEstate','classified'=>'Classifieds'));
	
	return $addons_posttype = array_merge($addons_posttype,$tmpl_addons_posttype);
}
	
/* return the template in mobile view for archive,category and tags pages */
function tmpl_get_mob_archive_template(){
	/* auto detect mobile devices */
	$addons_posttype =tmpl_addon_name();
	
	/* Different template for mobile view */
	if (tmpl_wp_is_mobile()) {
		$template = '/mobile-'.get_post_type().'.php';
	}
	
	
	/* check if mobile template available in child theme else call from related plugin */
	if ( file_exists(STYLESHEETPATH .$template)) {
			
		$mob_template = STYLESHEETPATH .$template;			
		
	}else if ( file_exists(TEMPLATEPATH .$template) ) {
		
		$mob_template = TEMPLATEPATH . $template;
		
	}else{
		if(file_exists( WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.$template))
			$mob_template = WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.$template;
		else{
			$exclude_post_type = array('event','listing','property');
			if(!in_array(get_post_type(),$exclude_post_type))
				$mob_template = WP_PLUGIN_DIR.'/Tevolution-Directory/templates/mobile-listing.php';
		}		
	}
	if(!is_category()){
		return $mob_template;
	}
}

/* Detail page template for mobile view */

function tmpl_get_mob_single_template(){
	
	$addons_posttype =tmpl_addon_name();
	
		/* Different template for mobile view */
	if (function_exists('tmpl_wp_is_mobile') && tmpl_wp_is_mobile() ){
		$template = '/mobile-single-'.get_post_type().'.php';
	}
	
	/* check if mobile template available in child theme else call from related plugin */
	if ( file_exists(STYLESHEETPATH .$template)) {
			
		$mob_template = STYLESHEETPATH .$template;			
		
	}else if ( file_exists(TEMPLATEPATH .$template) ) {
		
		$mob_template = TEMPLATEPATH . $template;
		
	}else{
		if(file_exists( WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.$template))
			$mob_template = WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.$template;
	}

	return $mob_template;
}

/* show different pages  in mobile  */

function tmpl_get_mob_page_template($page_template){
	global $post;
	$template= "/mobile-templates/mobile-front-page.php";
	if(is_front_page() || is_home()){
		if ( file_exists(STYLESHEETPATH .$template)) {
			
			$page_template = STYLESHEETPATH .$template;			
		
		}else{
			if( file_exists(TEMPLATEPATH .$template))
			$page_template = TEMPLATEPATH . $template;
		
		}
	}elseif(is_page()){ /* if page is load in mobile then call this template. This will call 'mobile-page.php' from theme's root */
		$template = '/mobile-templates/mobile-page.php';
		if( file_exists(get_template_directory() . $template))
			$page_template = get_template_directory() . $template;
	}
	return $page_template;
}
/*
	Preview page template for mobile view 
*/
function tmpl_mob_preview_template(){

	$addons_posttype =tmpl_addon_name();
	
	/* Different template for mobile view */ 
	$template = '/mobile-single-'.get_post_type().'-preview.php';
	
	if((isset($_REQUEST['page']) && $_REQUEST['page'] == "preview")  && isset($_REQUEST['cur_post_type']) && in_array($_REQUEST['cur_post_type'],$custom_post_type)  && $_REQUEST['cur_post_type']!='event')
	{
		
		
		if ( file_exists(STYLESHEETPATH . $template)) {
			
			$single_template_preview = STYLESHEETPATH . $template;			
			
		} else if ( file_exists(TEMPLATEPATH . $template) ) {
			
			$single_template_preview = TEMPLATEPATH . $template;
			
		}else{
			
			if(file_exists( WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.$template))
			$single_template_preview = WP_PLUGIN_DIR.'/Tevolution-'.$addons_posttype[get_post_type()].'/templates'.$template;
			
		}		
		include($single_template_preview);
		exit;
	}
}

/* Comment template for mobile view */
function tmpl_plugin_comment_template(){
	global $post;
	$template= "/mobile-templates/mobile-comments.php";
	
	if( file_exists(get_template_directory() . $template))
		$comment_template = get_template_directory() . $template;
		
	return $comment_template ;
	
}

/* get the author page template for mobile view */
function tmpl_get_mob_author_template($author_template){
	$template= "/mobile-templates/mobile-author.php";
	
		if( file_exists(get_template_directory() . $template))
			$author_template = get_template_directory() . $template;
	
	return $author_template;
}

/* add additional script to custom pages */
add_action('wp_head','tmpl_add_compatibility_scripts');
if(!function_exists('tmpl_add_compatibility_scripts'))
{
	function tmpl_add_compatibility_scripts()
	{
		if((isset($_REQUEST['page']) && ($_REQUEST['page'] == 'preview' || $_REQUEST['page'] == 'success')) && is_plugin_active('sitepress-multilingual-cms/sitepress.php'))
		{
			icl_lang_sel_nav_css($show = true);
		}
	}
}

/* Activate add on when run the auto install */
function tmpl_overview_box()
{
global $wpdb;
	$wp_user_roles_arr = get_option($wpdb->prefix.'user_roles');
	global $wpdb;
	
		$post_counts = $wpdb->get_var("select count(post_id) from $wpdb->postmeta where (meta_key='pt_dummy_content' || meta_key='tl_dummy_content') and meta_value=1");
		
		/* help links */
		$menu_msg1 .= "<ul><li><a href='".site_url("/wp-admin/user-new.php")."'>".__('Add listing agents',ADMINDOMAIN)."</a></li>";
		$menu_msg1 .= "<li><a href='".site_url("/wp-admin/admin.php?page=monetization&action=add_package&tab=packages")."'>".__("Set pricing options",ADMINDOMAIN)."</a></li>";
		$menu_msg1 .= "<li><a href='".site_url("/wp-admin/admin.php?page=monetization&tab=payment_options")."'>".__('Setup payment types',ADMINDOMAIN)."</a></li></ul>";

		$menu_msg2 = "<ul><li><a href='".site_url("/wp-admin/admin.php?page=templatic_settings#listing_page_settings")."'>".__('Setup category page',ADMINDOMAIN)."</a> and <a href='".site_url("/wp-admin/admin.php?page=templatic_settings#detail_page_settings")."'>".__('detail page',ADMINDOMAIN)."</a></li>";
		$menu_msg2 .= "<li><a href='".site_url("/wp-admin/admin.php?page=templatic_settings#registration_page_setup")."'>".__('Setup registration',ADMINDOMAIN)."</a> and <a href='".site_url("/wp-admin/admin.php?page=templatic_settings#submit_page_settings")."'>".__('submission page',ADMINDOMAIN)."</a></li>";
		
		$menu_msg2 .= "<li><a href='".site_url("/wp-admin/admin.php?page=templatic_settings&tab=email")."'>".__('Manage and customize emails',ADMINDOMAIN)."</a></li></ul>";
		
		$menu_msg3 = "<ul><li><a href='".site_url("/wp-admin/widgets.php")."'>Manage Widgets </a>,  <a href='".site_url("/wp-admin/customize.php")."'>".__('Add your logo',ADMINDOMAIN)." </a></li>";
		$menu_msg3 .= "<li><a href='".site_url("/wp-admin/customize.php")."'>".__('Change site colors',ADMINDOMAIN)." </a></li>";
		$menu_msg3 .= "<li><a href='".site_url("/wp-admin/nav-menus.php?action=edit")."'>".__('Manage menu navigation',ADMINDOMAIN)."</a></li>";
		
		$my_theme = wp_get_theme();
		$theme_name = $my_theme->get( 'Name' );
		$version = $my_theme->get( 'Version' );
		$dummydata_title .= '<h3 class="twp-act-msg">'.sprintf (__('Thank you. %s is now activated.',ADMINDOMAIN),'Tevolution').'</h3>';
		
		/* theme message */	
		$dummy_theme_message .='<div class="tmpl-wp-desc">The Tevolution is ideal for creating and monetizing an online sites. To help you setup the theme, please refer to its <a href="http://templatic.com/docs/tevolution-guide/">Installation Guide</a> to help you better understand the theme&#39;s functions. To help you get started, we have outlined a few recommended steps below to get you going. Should you need any assistance please also visit the Tevolution <a href="http://templatic.com/docs/submit-a-ticket/">helpdesk</a>. </div>';
		
		/* guilde and support link */	
		
		$dummy_nstallation_link  = '<div class="tmpl-ai-btm-links clearfix"><ul><li>Need Help?</li><li><a href="http://templatic.com/docs/tevolution-guide/">Installation Guide</a></li><li><a href="http://templatic.com/docs/submit-a-ticket/">HelpDesk</a></li></ul><p><a href="http://templatic.com">Team Templatic</a> at your service</p></div>';
		if($post_counts>0){
			$theme_name = get_option('stylesheet');
			
			$dummy_data_msg='';
			$dummy_data_msg = $dummydata_title;
			
			$dummy_data_msg .= $dummy_theme_message;
			
			$dummy_data_msg .='<div class="wrapper_templatic_auto_install_col3"><div class="templatic_auto_install_col3"><h4>'.__('Next Steps',ADMINDOMAIN).'</h4>'.$menu_msg1.'</div>';
			$dummy_data_msg .='<div class="templatic_auto_install_col3"><h4>'.__('Advance Options',ADMINDOMAIN).'</h4>'.$menu_msg2.'</div>';
			$dummy_data_msg .='<div class="templatic_auto_install_col3"><h4>'.__('Customize Your Website',ADMINDOMAIN).'</h4>'.$menu_msg3.'</div></div>';
			$dummy_data_msg .='<div class="ref-tev-msg">'.__('Please refer to &quot;Tevolution&quot; and other sections on the left side menu for more of the advanced options.',ADMINDOMAIN).'</div>';
			$dummy_data_msg .= $dummy_nstallation_link;
			
		}else{
			$theme_name = get_option('stylesheet');
			$dummy_data_msg='';
			$dummy_data_msg = $dummydata_title;
			
			
			$dummy_data_msg .= $dummy_theme_message;
			
			$dummy_data_msg .='<div class="wrapper_templatic_auto_install_col3"><div class="templatic_auto_install_col3"><h4>'.__('Next Steps',ADMINDOMAIN).'</h4>'.$menu_msg1.'</div>';
			$dummy_data_msg .='<div class="templatic_auto_install_col3"><h4>'.__('Advance Options',ADMINDOMAIN).'</h4>'.$menu_msg2.'</div>';
			$dummy_data_msg .='<div class="templatic_auto_install_col3"><h4>'.__('Customize Your Website',ADMINDOMAIN).'</h4>'.$menu_msg3.'</div></div>';
			$dummy_data_msg .='<div class="ref-tev-msg">'.__('Please refer to &quot;Tevolution&quot; and other sections on the left side menu for more of the advanced options.',ADMINDOMAIN).'</div>';
			$dummy_data_msg .= $dummy_nstallation_link;
		}
		
		if(isset($_REQUEST['dummy_insert']) && $_REQUEST['dummy_insert']){
			$theme_name = str_replace(' ','',strtolower(wp_get_theme()));
			require_once (get_template_directory().'/library/functions/auto_install/auto_install_data.php');
			
			$args = array(
						'post_type' => 'page',
						'meta_key' => '_wp_page_template',
						'meta_value' => 'page-templates/front-page.php'
						);
			$page_query = new WP_Query($args);
			$front_page_id = $page_query->post->ID;
			update_option('page_on_front',$front_page_id);
			
			/*BEING Cretae primary menu */
			$nav_menus=wp_get_nav_menus( array('orderby' => 'name') );
			$navmenu=array();
			if(!$nav_menus){
				foreach($nav_menus as $menus){
					$navmenu[]=$menus->slug;	
				}
				/*Primary menu */
				if(!in_array('primary',$navmenu)){
					$primary_post_info[] = array('post_title'=>'Home','post_id'   =>$front_page_id,'_menu_item_type'=>'post_type','_menu_item_object'=>'page','menu_item_parent'=>0);
					/*Get submit listing page id */
					$submit_listing_id = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'submit-listing' and $wpdb->posts.post_type = 'page'");
					$primary_post_info[] = array('post_title'=>'','post_id'   =>$submit_listing_id->ID,'_menu_item_type'=>'post_type','_menu_item_object'=>'page','menu_item_parent'=>0);
					/*Insert primary menu */	
					wp_insert_name_menu_auto_install($primary_post_info,'primary');					
					
				}// end primary nav menu if condition
				/*Secondary menu */
				if(!in_array('secondary',$navmenu)){
					/*Home Page */
					$secondary_post_info[] = array('post_title'=>'Home','post_id'   =>$front_page_id,'_menu_item_type'=>'post_type','_menu_item_object'=>'page','menu_item_parent'=>0);
					
					/*Get the  listing category list */
					$args = array( 'taxonomy' =>'listingcategory','orderby'=> 'id','order' => 'ASC', );
					$terms = get_terms('listingcategory', $args);
					if($terms){
						$i=0;
						foreach($terms as $term){
							$menu_item_parent=($i!=0)?'1':'0';
							$secondary_post_info[] = array('post_title'=>'','post_content'=>$term->description,'post_id' =>$term->term_id,'_menu_item_type'=>'taxonomy','_menu_item_object'=>'listingcategory','menu_item_parent'=>$menu_item_parent);
							$i++;
						}
					}
					/*finish listingcategory menu */
					/*Get people page id */
					$people_id = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'people' and $wpdb->posts.post_type = 'page'");
					$secondary_post_info[] = array('post_title'=>'','post_id'   =>$people_id->ID,'_menu_item_type'=>'post_type','_menu_item_object'=>'page','menu_item_parent'=>0);
					/*Get all in one map page id */
					$all_in_one_map_id = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'all-in-one-map' and $wpdb->posts.post_type = 'page'");
					$secondary_post_info[] = array('post_title'=>'','post_id'   =>$all_in_one_map_id->ID,'_menu_item_type'=>'post_type','_menu_item_object'=>'page','menu_item_parent'=>0);					
					
					/*Blog menu */
					$args = array( 'taxonomy' =>'category','orderby'=> 'id','order' => 'ASC','exclude'=>array('1'));
					$terms = get_terms('category', $args);
					if($terms){
						$i=0;
						foreach($terms as $term){
							$menu_item_parent=($i!=0)?'1':'0';
							$secondary_post_info[] = array('post_title'=>'','post_content'=>$term->description,'post_id' =>$term->term_id,'_menu_item_type'=>'taxonomy','_menu_item_object'=>'category','menu_item_parent'=>$menu_item_parent);
							$i++;
						}
					}
					/*finish blog menu */
					
					/*Get contact us page id */
					$contact_us_id = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_name = 'contact-us' and $wpdb->posts.post_type = 'page'");
					$secondary_post_info[] = array('post_title'=>'','post_id'   =>$contact_us_id->ID,'_menu_item_type'=>'post_type','_menu_item_object'=>'page','menu_item_parent'=>0);					
					/*Insert secondary menu */	
					wp_insert_name_menu_auto_install($secondary_post_info,'secondary');
				}// end secondary nav menu if condition
			}

			/*END primary menu */
			
			wp_redirect(admin_url().'themes.php?x=y');
		}
		if(isset($_REQUEST['dummy']) && $_REQUEST['dummy']=='del'){
			tmpl_delete_dummy_data();
			wp_redirect(admin_url().'themes.php');
		}
		
		define('THEME_ACTIVE_MESSAGE','<div id="ajax-notification" class="welcome-panel tmpl-welcome-panel"><div class="welcome-panel-content">'.$dummy_data_msg.'<span id="ajax-notification-nonce" class="hidden">' . wp_create_nonce( 'ajax-notification-nonce' ) . '</span><a href="javascript:;" id="dismiss-ajax-notification" class="templatic-dismiss" style="float:right">Dismiss</a></div></div>');
		echo THEME_ACTIVE_MESSAGE;
}

?>