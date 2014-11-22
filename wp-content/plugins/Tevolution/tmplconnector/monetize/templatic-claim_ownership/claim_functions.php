<?php
/* 
	This function will add a metabox for latest claims listing in word press dashboard 
*/

function add_claim_dashboard_metabox()
{
	global $wp_meta_boxes,$current_user;
	if(is_super_admin($current_user->ID)) {
		wp_add_dashboard_widget('claim_dashboard_metabox', 'Ownership Claims', 'fetch_claims');
		@$wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'];
	}
}
/* eof - claim dashboard metabox */

/* name : fetch claims
description : this function fetches claims in a metabox displaying on wordpress dashboard */
function fetch_claims()
{
	global $wpdb,$claim_db_table_name; ?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function confirmSubmit(str) {
			var answer = confirm("<?php echo DELETE_CONFIRM_ALERT; ?>");
			if (answer){
				window.location = "<?php echo site_url(); ?>/wp-admin/index.php?poid="+str;
				alert('<?php echo ENTRY_DELETED; ?>');
			}
		}
	function claimer_showdetail(str)
	{	
		if(document.getElementById('comments_'+str).style.display == 'block')	{
			document.getElementById('comments_'+str).style.display = 'none';
		} else {
			document.getElementById('comments_'+str).style.display = '';
		}
	}
	/* ]]> */
	</script>
	<?php /* DISPLAY CLAIM DATA IN TABLE */
	echo "<table class='widefat'>
	<thead>
	<tr>
		<th style='width:30%;'>".__('Claim On',DOMAIN)."</th>
		<th style='width:30%;'>".CLAIMER_TEXT."</th>
		<th>".STATUS."</th>
		<th>".ACTION_TEXT."</th>
	</tr></thead>";
	$claim_post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'claim' LIMIT 0,7");	
	if(count($claim_post_ids) != 0)
	{
		$counter =0;
		foreach ($claim_post_ids as $claim_post_id) :			
			$data = get_post_meta($claim_post_id,'post_claim_data',true);			
			/* FETCH CLAIM DATA */			
			$post_id = $data['post_id'];
			$auth_data= get_userdata($data['author_id']);
			$post_title = $data['post_title'];
			$claimer_name = $data['claimer_name'];
			$name = str_word_count($claimer_name,1);
			$claimer_contact = $data['claimer_contact'];
			$claimer_email=$data['claimer_email'];
			$author_id = $data['author_id'];
			$status = $data['claim_status'];
			$msg = $data['claim_msg'];
			$udata = get_userdata($author_id);
			?>
               <tr>
                    <td>
                    	<?php echo $claim_post_id;?>&nbsp;<a href="<?php echo site_url().'/wp-admin/post.php?post='.$post_id.'&action=edit';?>" title="<?php echo VIEW_CLAIM; ?>"><?php __('By',ADMINDOMAIN); ?><?php echo $post_title?></a>
                    </td>
                    <td><?php echo $claimer_name;?>: <?php echo $claimer_email;?></td>                    
               	<?php if($status == 'approved' && get_post_meta($post_id,'is_verified',true) == 1) :?>
                    	<td id="verified"><?php echo YES_VERIFIED; ?></td>
                    <?php elseif($status == 'declined') : ?>   
                    	<td id="declined"><?php echo DECLINED; ?></td>
                    <?php else : ?>
                    	<td id="unapproved"><?php echo PENDING; ?></td>
                    <?php endif;?>
                    <td>
                    	<a href="#TB_inline?width=600&height=600&inlineId=claimed_win_<?php echo $claim_post_id;?>" id="claimed_w_<?php echo $claim_post_id;?>" class="thickbox" title="<?php echo __('View claim details',ADMINDOMAIN);  ?>"><i class="fa fa-info"></i></a>&nbsp;&nbsp;
						<?php if($status == 'approved' && get_post_meta($post_id,'is_verified',true) == 1){ ?>
							<a href="<?php echo site_url().'/wp-admin/post.php?post='.$post_id.'&action=edit&decline=yes&clid='.$claim_post_id;?>" title="<?php echo DECLINE_CLAIM; ?>"><i class="fa fa-close"></i></a>&nbsp;&nbsp;
						<?php }else{ ?>
							<a href="<?php echo site_url().'/wp-admin/post.php?post='.$post_id.'&action=edit&verified=yes&clid='.$claim_post_id .'&user='.$name[0]?>" title="<?php echo VERIFY_CLAIM; ?>"><i class="fa fa-check-circle"></i></a>&nbsp;&nbsp;
						<?php } ?>
                         <a href="javascript:void(0);" onclick="return confirmSubmit(<?php echo $claim_post_id; ?>);" title="<?php echo DELETE_CLAIM; ?>"><i class="fa fa-minus-circle"></i></a>
                    </td>
               </tr>

			<div id="claimed_win_<?php echo $claim_post_id; ?>" class="clm_cls" style="display:none;width:400px; height:400px;">
				
				<h2><?php echo __('Author Details',ADMINDOMAIN); ?></h2>
				<p class="tev_description">
					<?php echo  "<strong>".__('Name',ADMINDOMAIN)."</strong>"; echo ": ".$auth_data->display_name; ?>
				</p>
				<p class="tev_description">
					<?php echo  "<strong>".__('Email',ADMINDOMAIN)."</strong>"; echo ": ".$auth_data->user_email; ?>
				</p>
				
				
				<h2><?php echo __('Claimer Details',ADMINDOMAIN); ?></h2>
				<p class="tev_description">
					<?php echo "<strong>".__('Name',ADMINDOMAIN)."</strong>"; echo ": ".$data['claimer_name']; ?>
				</p>
				<p class="tev_description">
					<?php echo "<strong>".__('Email',ADMINDOMAIN)."</strong>"; echo ": ".$data['claimer_email']; ?>
				</p>
				<p class="tev_description">
					<?php echo "<strong>".__('Conatct No.',ADMINDOMAIN)."</strong>"; echo ": ".$data['claimer_contact']; ?>
				</p>
				<p class="tev_description">
					<?php echo "<strong>".__('Message',ADMINDOMAIN)."</strong>"; echo ": ".$data['claim_msg']; ?>
				</p>
			</div>
	
		<?php
		$c = $counter ++;
		endforeach;
	}
	else
	{
		echo "<tr><td colspan='6'>".NO_CLAIM_REQUEST."</td></tr>";
	}
	echo "</table>";
}

/* deleting the claim on click of delete button of dashboard metabox */
if(isset($_REQUEST['poid']) && @$_REQUEST['poid'] != "")
{
	global $wpdb,$post;
	$vclid = $_REQUEST['poid'];
	wp_delete_post($vclid,true);
}
/* eof - fetch claims in dashboard metabox */
/* name : add metabox in posts
description : this function will add a metabox on add/edit page of every post */
function add_claim_metabox_posts ()
{
	global $post,$wpdb,$post_id;	
	if(isset($_REQUEST['post']) && $_REQUEST['post'] !=''){
			$post_id = $_REQUEST['post'];
	}else{
			$post_id = @$post->ID;
	}
	if(@$post->ID!=''){
		$tmpdata = get_option('templatic_settings');
		$post_type = $tmpdata['claim_post_type_value'];
		if($post_type){
			foreach($post_type as $type) :	
				if($post->ID !=''): 
					
				$post_content = $wpdb->get_row("SELECT post_content FROM $wpdb->posts WHERE $wpdb->posts.post_content = '".$post_id."' and $wpdb->posts.post_type = 'claim'");					
				add_meta_box("claim_post", "Claim post", "fetch_meta_options", $type, "side", "high");
				endif;
			endforeach;
		}
	}
}
/* eof - add metabox in posts */
function add_verified_user_recurring($post_id)
{
	$args =array( 'post_type'      => 'event',
			    'posts_per_page' => -1	,
			    'post_status'    => 'recurring',
			    'post_parent'    =>$post_id,
			    'meta_query'     => array('relation'      => 'AND',
									array('key'     => '_event_id',
										 'value'   => $post_id,
										 'compare' => '=',
										 'type'    => 'text'
										),
							   )
				);
	$post_query = null;
	$post_query = new WP_Query($args);
	$post_title = get_the_title($_REQUEST['clid']);
	$post_content = $post_id;
	$post_author = 1;
	
	if($post_query){
		while ($post_query->have_posts()) : $post_query->the_post();
			 global $post;
			 	$claim_post_type = array( 'post_title'   => $post_title,
									 'post_content' => $post->ID,
									 'post_status'  => 'publish',
									 'post_author'  => 1,
									 'post_type'    => "claim",
									 'post_excerpt' => "approved"
									);
				$las_rec_post_id = wp_insert_post( $claim_post_type ); /* INSERT QUERY */
				add_post_meta($las_rec_post_id,'is_verified',1);
				/* INSERTING CLAIM INFORMATION IN POST META TABLE */
				$data = get_post_meta($_REQUEST['clid'],'post_claim_data',true); /* FETCH CLAIM ID */
				$data['post_id'] = $las_rec_post_id;
				add_post_meta($las_rec_post_id, 'post_claim_data', $data);
			 
		endwhile;
		wp_reset_query();
	}
}
/* name : fetch meta options
description : this function will fetch the claim data in post's metabox */
function fetch_meta_options()
{	
	global $wpdb,$post;
	$claim_status = "";	
	/* VERIFY THE USER */
	if((isset($_REQUEST['verified']) && $_REQUEST['verified'] == 'yes') && (isset($_REQUEST['user']) && $_REQUEST['user']!=''))
	{
		$clid = $_REQUEST['clid'];
		global $current_user,$post_id;  
		$sclid = $_REQUEST['sclid'];
		/* if self verify the claim */
		if(isset($sclid) && $sclid!=''){
			$clid1 = $post_id;
			global $post;
			$title = get_the_title(@$post_id);
			$claim_post_id =$post_id; /* claimed post id */
			$claim_post_type = array(
				 'post_title' => __('Claim for - ',ADMINDOMAN).$title.'',
				 'post_content' => ''.$claim_post_id.'',
				 'post_excerpt' => 'approved',
				 'post_status' => 'publish',
				 'post_author' => 1,
				 'post_type' => "claim",
				);
			 $claim_id = wp_insert_post( $claim_post_type ); /* INSERT QUERY */
			
			$claim_post = array('post_id' => $post_id,
				    'request_uri' => '',
				    'link_url' => $link_url,
				    'claimer_id' => $current_user->ID,
				    'author_id' => $current_user->ID,
				    'post_title' => $title,
				    'claimer_name' => $current_user->display_name,
				    'claimer_email' => $current_user->user_email,
				    'claimer_contact' => '' ,
				    'claim_msg' => '',
				    'claim_status' => 'approved'
				);		
			update_post_meta($claim_id,'post_claim_data',$claim_post); /* UPDATING THE WHOLE CLAIM DATA ARRAY */
			$clid = $claim_id; // claim id
			
	
			add_post_meta($post_id,'is_verified',1);
			update_post_meta($post_id,'is_verified',1);
			
		}
		/* End verify the claim */
		$_REQUEST['user'];
		/* UPDATE CLAIM STATUS WHEN THE ADMIN VERIFIES THE AUTHOR */
		$data = get_post_meta($clid, 'post_claim_data',true);
		$post_id = $data['post_id'];
		$request_uri = $data['request_uri'];
		$link_url = $data['link_url'];
		$claimer_id = $data['claimer_id'];
		$post_title = $data['post_title'];
		$claimer_name = $data['claimer_name'];
		$claimer_email = $data['claimer_email'];
		$claimer_contact = $data['claimer_contact'];
		$author_id = $data['author_id'];
		$claim_status = $data['claim_status'];
		$claim_msg = $data['claim_msg'];
		$claim_post = array('post_id' => $post_id,
				    'request_uri' => $request_uri,
				    'link_url' => $link_url,
				    'claimer_id' => $claimer_id,
				    'author_id' => $author_id,
				    'post_title' => $post_title,
				    'claimer_name' => $claimer_name,
				    'claimer_email' => $claimer_email,
				    'claimer_contact' => $claimer_contact ,
				    'claim_msg' => $claim_msg,
				    'claim_status' => 'approved'
				);		
		update_post_meta($clid,'post_claim_data',$claim_post); /* UPDATING THE WHOLE CLAIM DATA ARRAY */
		
		$event_type = get_post_meta($post_id,'event_type',true);
		if(trim(strtolower($event_type)) == trim(strtolower('Recurring Event')))
		{
			add_verified_user_recurring($post_id);
		}
		
		add_post_meta($post_id,'is_verified',1);
		if(isset($sclid) && $sclid!=''){
			update_post_meta($claim_post_id,'is_verified',1);
		}else{
			update_post_meta($claim_post_id,'is_verified',0);
		}
		$wpdb->update( $wpdb->posts, array('post_excerpt' => 'approved'), array('ID' => $clid));
		if (($claimer_id == '' || $claimer_id == '0' )&& $_REQUEST['user']){
			add_verified_user($clid); /* CALL A FUNCTION TO ADD VERIFIED USER */
		}		
	}
	elseif((isset($_REQUEST['verified']) && $_REQUEST['verified'] == 'no') && (isset($_REQUEST['clid']) && $_REQUEST['clid']))
	{
		$clid = $_REQUEST['clid'];
		$data = get_post_meta($clid, 'post_claim_data',true);
		$post_id = $data['post_id'];
		delete_post_meta($clid, 'post_claim_data');
		wp_delete_post($clid);
		delete_post_meta($post_id,'is_verified',0);
	}
	elseif((isset($_REQUEST['decline']) && $_REQUEST['decline'] == 'yes') && (isset($_REQUEST['clid']) && $_REQUEST['clid']!=''))
	{
		global $current_user;
		$clid = $_REQUEST['clid'];
		$_REQUEST['user'];
		/* update claim status when the admin declines the author */
		$data = get_post_meta($clid, 'post_claim_data',true);
		$post_id = $data['post_id'];
		$request_uri = $data['request_uri'];
		$link_url = $data['link_url'];
		$claimer_id = $data['claimer_id'];
		$post_title = $data['post_title'];
		$claimer_name = $data['claimer_name'];
		$claimer_email = $data['claimer_email'];
		$claimer_contact = $data['claimer_contact'];
		$author_id = $data['author_id'];
		$claim_status = $data['claim_status'];
		$claim_msg = $data['claim_msg'];
		$post = array('post_id' => $post_id,
					  'request_uri' => $request_uri,
					  'link_url' => $link_url,
					  'claimer_id' => $claimer_id,
					  'author_id' => $author_id,
					  'post_title' => $post_title,
					  'claimer_name' => $claimer_name,
					  'claimer_email' => $claimer_email,
					  'claimer_contact' => $claimer_contact ,
					  'claim_msg' => $claim_msg,
					  'claim_status' => 'declined');
		update_post_meta($clid,'post_claim_data',$post); /* UPDATING THE WHOLE CLAIM DATA ARRAY */
		if(isset($sclid) && $sclid!=''){
			update_post_meta($post_id,'is_verified',1);
		}else{
			update_post_meta($post_id,'is_verified',0);
		}
		
		$wpdb->update( $wpdb->posts, array('post_excerpt' => 'declined'), array('ID' => $clid)); 
	}
	/* PRINT THE DATA IN METABOX */	
	$data = get_post_meta(@$clid,'post_claim_data',true);	
	if($data['claim_status'] == 'approved' && get_post_meta($data['post_id'],'is_verified',true) == '1')
	{	
		global $post;
	
		$post_id = $data['post_id'];
		echo "<p>".__('1 user has claimed for this post.',ADMINDOMAIN)."</p>";
		?>
		<!-- <h4><img src="<?php //echo plugin_dir_url( __FILE__ ); ?>images/verified.png" alt="<?php //echo YES_VERIFIED;?>" border="0" align="middle" style="position:relative; top:-4px; margin-right:5px;" /> <?php //echo POST_VERIFIED_TEXT; ?></h4> -->
		
		<a href="<?php echo site_url().'/wp-admin/post.php?post='.$_REQUEST['post'].'&action=edit&verified=no&clid='.$clid;?>" title="<?php echo REMOVE_CLAIM_REQUEST; ?>"><?php echo REMOVE_CLAIM_REQUEST; ?></a>
	<?php 
	}else
	{
		global $current_user;
		$id = @$_REQUEST['clid'];
		$post_claim_id = $wpdb->get_col("SELECT ID from $wpdb->posts WHERE (post_content = '".$_REQUEST['post']."' OR post_content = '".$post->ID."') AND post_status = 'publish' AND (post_excerpt = 'approved' OR post_excerpt = '') and post_type='claim'"); /* FETCH TOTAL NUMBER OF CLAIMS FOR A POST */
		$data = get_post_meta($id,'post_claim_data',true);
		$post_id = $data['post_id'];
		if(count($post_claim_id) == '')
		{ 
			echo "<p>" . NO_CLAIM . "</p>"; ?>
			<a href="<?php echo site_url().'/wp-admin/post.php?post='.$post->ID.'&action=edit&verified=yes&sclid=1&user='.$current_user->ID;?>" title="<?php echo __('Set as verify',ADMINDOMAIN); ?>" class="verify_this">
					<strong><?php echo __('Self Verify',ADMINDOMAIN); ?></strong>
			</a>
			<?php
			
		}
		else
		{
			/* condition to display the count of claims in metabox */
			if(count($post_claim_id) == 1) :
				echo "<p>" . count($post_claim_id). " ".__('user has claimed for this post.',DOMAIN)."</p>";
			else :
				echo "<p>" . count($post_claim_id). " ".__('users have claimed for this post.',DOMAIN)."</p>";
			endif;
			?>
          
            <?php
			foreach($post_claim_id as $key => $val) :
				$data = get_post_meta($val,'post_claim_data',true);
				if($data['claim_status'] == 'pending') :
					$user_data = get_userdata($data['claimer_id']);
					$claim_user = get_post_meta($val,'post_claim_data',true);
					$name = str_word_count($claim_user['claimer_name'],1);?>
					<ul>
						<li>
							<a href="<?php echo site_url().'/wp-admin/post.php?post='.$post->ID.'&action=edit&verified=yes&clid='.$val.'&user='.$name[0];?>" title="<?php echo VERIFY_CLAIM; ?>" class="verify_this">
								<strong><?php echo VERIFY_CLAIM; ?></strong>
							</a> / 
							<a href="<?php echo site_url().'/wp-admin/post.php?post='.$post->ID.'&action=edit&decline=yes&clid='.$val.'&user='.$name[0];?>" title="<?php echo DECLINE_CLAIM; ?>" class="verify_this">
								<strong><?php echo DECLINE_CLAIM; ?></strong>
							</a>
					<?php $current_link = get_author_posts_url(@$user_data->ID);
						if($user_data != '' && $data['claimer_id'] != '0') {?>
							<a href="<?php echo $current_link; ?> "><?php echo $user_data->display_name; ?></a>
					<?php } else { echo $name[0]; }?>
						</li>
					</ul>
                    <?php
					else:
					?>
                      <a href="<?php echo site_url().'/wp-admin/post.php?post='.$_REQUEST['post'].'&action=edit&verified=no&clid='.$val;?>" title="<?php echo REMOVE_CLAIM_REQUEST; ?>"><?php echo REMOVE_CLAIM_REQUEST; ?></a>
                    <?php
					
					endif;// Finish claim status pending if condition 
		endforeach; // finish post claim id foreach
		} 
	}
}
/* eof - fetch meta options */
/* name : add the verified user
description : this funtion will add a user who has been verified for the claimed post */
function add_verified_user($clid)
{
	global $wpdb,$post;
	$data = get_post_meta($clid,'post_claim_data',true);
	get_post_meta($clid,'is_verified',true);
	$user_name = $data['claimer_name'];
	$name = str_word_count($user_name,1);
	$user_email = $data['claimer_email'];
	$get_user_data_by_id = '';
	$user_has_flag = 0;
	$post_id = 0;
	$user_pass = '';
	if( @$_REQUEST['post'] ){
		$post_id = @$_REQUEST['post'];
		$get_post_data = get_post($post_id);
	}
	if(!empty($get_post_data)){
		$post_title = $get_post_data->post_title;
	}
	
	if( $name[0] !="" ){
		$get_current_user = get_user_by( 'login', $name[0] );
		
		if( @$get_current_user->ID > 0 ){
			$get_user_data_by_id = @$get_current_user->ID;
			$user_has_flag = 1;
		}else{
			$user_pass = wp_generate_password(12,false);
			$get_user_data_by_id = wp_create_user( $name[0], $user_pass, $user_email );
			$user_has_flag = 0;
		}
	}
	if ( $get_user_data_by_id )
	{
		$user_info = get_userdata($get_user_data_by_id);
		$user_login = $user_info->user_login;		
		//$user_pass = $user_info->user_pass;
		$post_url_link = '<a href="'.$_REQUEST['link_url1'].'">'.$post_title.'</a>';
		$email_subject = "Claim verified for - ".$post_title;
		$fromEmail = get_option('admin_email');
		$fromEmailName = stripslashes(get_option('blogname'));			
		
		$msg = '<p>Dear '.$user_login.',</p>';
		$msg .= '<p>The Claim for the <a href="'.get_permalink($_REQUEST['post']).'">'.$post_title.'</a> has been verified.</p>';
		if( $user_has_flag == 0 ){
			$msg .= '<p>'.__('You can login with the following credentials :',DOMAIN). '</p>';
			$msg .= '<p>'.__('Username:',DOMAIN).' [#user_login#]</p>';
			$msg .= '<p>'.__('Password:',DOMAIN).' [#user_password#]</p>';
			$msg .= "<p>".__("You can login from [#site_login_url#] or copy this link and paste it to your browser's address bar: ",DOMAIN)."[#site_login_url_link#]</p>";
		}
		$msg .= '<p>'.__('Thanks,',DOMAIN).'<br/> [#site_name#] </p>';
		$client_message =  $msg;
		$subject = $email_subject;
		$yourname_link = $yourname;
		if(function_exists('get_tevolution_login_permalink')){
			$store_login = '<a href="'.get_tevolution_login_permalink().'">'.__('Click Login',DOMAIN).'</a>';
			$store_login_link = get_tevolution_login_permalink();
		}else{
			$store_login='';
			$store_login_link='';
		}
		
		$search_array = array('[#user_name#]','[#user_login#]','[#user_password#]','[#site_name#]','[#site_login_url#]','[#site_login_url_link#]');
		$replace_array = array($user_login,$user_login,$user_pass,$fromEmailName,$store_login,$store_login_link);
		$client_message = str_replace($search_array,$replace_array,$client_message);
		/* CALL A MAIL FUNCTION */
		templ_send_email($fromEmail,$fromEmailName,$user_email,$user_login,$subject,$client_message,$extra='');
	}
	
	/* UPDATING THE CLAIM DATA */
	$user_info = get_userdata($get_user_data_by_id);
	$user_id = $user_info->ID;
	$data = get_post_meta($clid, 'post_claim_data',true);
	$post_id = $data['post_id'];
	$request_uri = $data['request_uri'];
	$link_url = $data['link_url'];
	$claimer_id = $data['claimer_id'];
	$post_title = $data['post_title'];
	$claimer_name = $data['claimer_name'];
	$claimer_email = $data['claimer_email'];
	$claimer_contact = $data['claimer_contact'];
	$author_id = $data['post_author_id'];
	$claim_status = $data['claim_status'];
	$claim_msg = $data['claim_msg'];
	$post = array('post_id' => $post_id,
				  'request_uri' => $request_uri,
				  'link_url' => $link_url,
				  'claimer_id' => $user_id,
				  'author_id' => $user_id,
				  'post_title' => $post_title,
				  'claimer_name' => $claimer_name,
				  'claimer_email' => $claimer_email,
				  'claimer_contact' => $claimer_contact ,
				  'claim_msg' => $claim_msg,
				  'claim_status' => $claim_status);
	update_post_meta($post->ID,'post_claim_data',$post); /* UPDATING THE WHOLE CLAIM DATA ARRAY */
	
	/* UPDATING THE POST TABLE */
	$wpdb->get_results("Update $wpdb->posts set post_author ='".$user_id."' where ID = '".$post_id."' and post_status  = 'publish'");
}
/* eof - add verified user */

/*
	this function posts the data of the claim form, creates a post and saves data in postmeta
*/
function insert_claim_ownership_data($post_details)
{
	global $wpdb,$General,$upload_folder_path,$post;
	if(@$_POST['claimer_name'])
	{
		/* CODE TO CHECK WP-RECAPTCHA */
		$tmpdata = get_option('templatic_settings');
		$display = $tmpdata['user_verification_page'];
	
		if(file_exists(get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php') && is_plugin_active('wp-recaptcha/wp-recaptcha.php') && in_array('claim',$display))
		{
			require_once( get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php');
			$a = get_option("recaptcha_options");
			$privatekey = $a['private_key'];
			$resp = recaptcha_check_answer ($privatekey,
						getenv("REMOTE_ADDR"),
						$post_details["recaptcha_challenge_field"],
						$post_details["recaptcha_response_field"]);
								
			if ($resp->is_valid )
			{
				echo "<script>alert("._e('Your claim for this post has been sent successfully.',DOMAIN).");</script>";
			}
			else
			{
				echo "<script>alert(".__('Invalid captcha. Please try again.',DOMAIN).")</script>";
				return false;	
			}	 
		}

		/* END OF CODE - CHECK WP-RECAPTCHA */
		
		/* POST CLAIM FORM VALUES */
		$yourname = $post_details['claimer_name'];
		$youremail = $post_details['claimer_email'];
		$your_number = $post_details['claimer_contact'];
		$c_number = $post_details['claimer_contact'];
		$message = $post_details['claim_msg'];
		$claim_post_id = $post_details['post_id'];
		$post_title = $post_details['post_title'];
		$user_id = $current_user->ID;
		$author_id = $post_details['author_id'];
		if($claim_post_id != "")
		{
			$sql = "select ID,post_title from $wpdb->posts where ID ='".$claim_post_id."'";
			$postinfo = $wpdb->get_results($sql);
			foreach($postinfo as $postinfoObj)
			{
				$post_title = $postinfoObj->post_title;
			}
		}
		
		$user_ip = $_SERVER["REMOTE_ADDR"];
		
		/* INSERTING CLAIM POST TYPE IN POST TABLE */
		$id = get_the_title($post->ID);
		$claim_post_type = array(
			 'post_title' => __('Claim for - ',ADMINDOMAN).$id.'',
			 'post_content' => ''.$claim_post_id.'',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_type' => "claim",
			);
		$post_id = wp_insert_post( $claim_post_type ); /* INSERT QUERY */
		/* INSERTING CLAIM INFORMATION IN POST META TABLE */
		add_post_meta($post_id, 'post_claim_data', $post_details);
		/* END OF CODE - INSERT VALUES */
		$q = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = 1");
		$to_email = get_option('admin_email');
		$to_name = $q->user_login;
		$site_name = '<a href="'.site_url().'">'.get_option('blogname').'</a>';
		
		$tmpdata = get_option('templatic_settings');
		$email_subject = $tmpdata['claim_ownership_subject'];
		$claim = $tmpdata['claim_ownership_content'];
		
		if(@$email_subject == '' )
		{
			$email_subject = __("New Claim Submitted",ADMINDOMAIN);
		}
		
		$subject_search_array = array('[#post_id#]');
		$subject_replace_array = array($post_id);
		if(@$claim=='')
		{
			$claim =  __('<p>Dear admin,</p><p>[#claim_name#] has submitted a claim for the post below.</p><p>[#message#]</p><p>Link: [#post_title#]</p><p>From:  [#your_name#]</p><p>Email: [#claim_email#]<p>Phone Number: [#your_number#]</p>',ADMINDOMAIN);
		}
		$filecontent_arr1 = $claim;
		$filecontent_arr2 = $filecontent_arr1;
		$client_message = $filecontent_arr2;
		$subject = $email_subject;
		$post_url_link = '<a href="'.$_REQUEST['link_url'].'">'.$post_title.'</a>';
		$yourname_link = __($yourname,DOMAIN);
		$search_array = array('[#to_name#]','[#post_title#]','[#message#]','[#your_name#]','[#your_number#]','[#post_url_link#]');
		$replace_array = array($to_name,$post_title,$message,$yourname_link,$your_number,$post_url_link);
		$client_message = str_replace($search_array,$replace_array,$client_message);		
	
		/* CALL A MAIL FUNCTION */
		templ_send_email($youremail,$yourname,$to_email,$to_name,$subject,$client_message,$extra='');

	}
}
/*
 * Claimed listing - this function will return the listing is claimed or not 
 *
 */

add_shortcode('claim_ownership','tmpl_claim_ownership');
function tmpl_claim_ownership(){
	global $post,$wpdb;
	if(is_single() || is_page() && !is_page_template('page-template_form.php') ) :
		insert_claim_ownership_data($_POST);
		
		if(get_post_meta($post->ID,'is_verified',true) == 1)
		{ 
		?>
			
		<?php
		}else
		{ 
			$current_ip = $_SERVER["REMOTE_ADDR"]; /* FETCH CURRENT USER IP ADDRESS */												
			$post_claim_id = $wpdb->get_col("SELECT ID from $wpdb->posts WHERE (post_content = '".$post->ID."') AND post_status = 'publish' AND (post_excerpt = 'approved' OR post_excerpt = '') AND post_type='claim' limit 0,1");
			if(count($post_claim_id) > 0 )
			{
				foreach($post_claim_id as $key=>$val)
				{
					$data = get_post_meta($val,'post_claim_data',true); /* FETCH CLAIM ID */									
					if( $post->ID == $data['post_id'] )
					{
						$user_ip = $data['claimer_ip']; /* FETCH IP ADDRESS OF CLAIMED POST */
						if($current_ip == $user_ip && $user_ip != '')
						{ ?>
							<p class="claimed"><?php _e(ALREADY_CLAIMED,DOMAIN); ?></p>
						<?php 
						}else{?>					
						<a href="javascript:void(0)" id="trigger_id" title="<?php _e('Claim For This',DOMAIN); echo " ".ucfirst($post->post_type);?>" data-reveal-id="tmpl_claim_listing" class="i_claim c_sendtofriend" ><?php _e('Claim Ownership',DOMAIN);;?></a>
						<?php
							add_action('wp_footer','tevolution_claim_form'); // action for footer to include claim listing form   ?>
						<?php }
					}
				}
			}else{ 
				add_action('wp_footer','tevolution_claim_form'); // action for footer to include claim listing form  
				?>			
				<a href="javascript:void(0)" id="trigger_id" title="<?php _e('Claim For This Listing',DOMAIN); ?>" data-reveal-id="tmpl_claim_listing" class="i_claim c_sendtofriend"><?php _e('Claim Ownership',DOMAIN);;?></a>
				<?php
			}
		}
	endif;
}
/*
	Include the claim ownership form in footer
*/
function tevolution_claim_form(){
	include_once (TEMPL_MONETIZE_FOLDER_PATH . "templatic-claim_ownership/popup_claim_form.php");
}
add_action('wp_ajax_tevolution_claimowner_ship','tevolution_claimowner_ship');
add_action('wp_ajax_nopriv_tevolution_claimowner_ship','tevolution_claimowner_ship');
function tevolution_claimowner_ship(){
	
	global $wpdb,$General,$upload_folder_path,$post;
	if(@$_REQUEST['claimer_name'])
	{
		/* code to check wp-recaptcha */
		$tmpdata = get_option('templatic_settings');
		$display = $tmpdata['user_verification_page'];
		
		if(file_exists(get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php') && is_plugin_active('wp-recaptcha/wp-recaptcha.php') && in_array('claim',$display))
		{
			require_once( get_tmpl_plugin_directory().'wp-recaptcha/recaptchalib.php');
			$a = get_option("recaptcha_options");
			$privatekey = $a['private_key'];
			$resp = recaptcha_check_answer ($privatekey,
						getenv("REMOTE_ADDR"),
						$_REQUEST["recaptcha_challenge_field"],
						$_REQUEST["recaptcha_response_field"]);
								
			if (!$resp->is_valid )
			{
				echo '1';
				exit;
				echo "<script>alert(".__('Invalid captcha. Please try again.',DOMAIN).");</script>";
				return false;	
			}	 
		}
		
		/* end of code - check wp-recaptcha */
		
		/* POST CLAIM FORM VALUES */
		$yourname = $_REQUEST['claimer_name'];
		$youremail = $_REQUEST['claimer_email'];
		$your_number = $_REQUEST['claimer_contact'];
		$c_number = $_REQUEST['claimer_contact'];
		$message = stripslashes($_REQUEST['claim_msg']);
		$claim_post_id = $_REQUEST['post_id'];
		$post_title = $_REQUEST['post_title'];
		$user_id = $current_user->ID;
		$author_id = $_REQUEST['author_id'];
		if($claim_post_id != "")
		{
			$sql = "select ID,post_title from $wpdb->posts where ID ='".$claim_post_id."'";
			$postinfo = $wpdb->get_results($sql);
			foreach($postinfo as $postinfoObj)
			{
				$post_title = $postinfoObj->post_title;
			}
		}
		
		$user_ip = $_SERVER["REMOTE_ADDR"];
		
		/* inserting claim post type in post table */
		$id = get_the_title($post->ID);
		$claim_post_type = array(
			 'post_title' => __('Claim for - ',ADMINDOMAN).$id.'',
			 'post_content' => ''.$claim_post_id.'',
			 'post_status' => 'publish',
			 'post_author' => 1,
			 'post_type' => "claim",
			);
		$post_id = wp_insert_post( $claim_post_type ); /* INSERT QUERY */
		
		/* inserting claim information in post meta table */
		add_post_meta($post_id, 'post_claim_data', $_REQUEST);
		/* end of code - insert values */
		$q = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = 1");
		$to_email = get_option('admin_email');
		$to_name = $q->user_login;
		$tmpdata = get_option('templatic_settings');
		$email_subject =  @stripslashes($tmpdata['claim_ownership_subject']);
		$email_content =  @stripslashes($tmpdata['claim_ownership_content']);
		$site_name = '<a href="'.site_url().'">'.get_option('blogname').'</a>';
		if(@$email_content == '')
		{
			$email_subject = __("New Claim Submitted",ADMINDOMAIN);
		}
		if(@$email_content == '')
		{
				$email_content = __('<p>Dear admin,</p><br/><p> [#claim_name#] has claimed for this post</p><p>[#message#]</p><p>Link: [#post_title#]</p><p>From: [#your_name#]</p><p>Email: [#claim_email#]<p>Phone Number: [#your_number#]</p>',ADMINDOMAIN);		
		}
		$post_url_link = '<a href="'.get_permalink($claim_post_id).'">'.$post_title.'</a>';
		$subject_search_array = array('[#post_title#]');
		$subject_replace_array = array($post_title);
		$email_subject = str_replace($subject_search_array,$subject_replace_array,$email_subject);
		$subject = $email_subject;
		
		$yourname_link = __($yourname,DOMAIN);
		$search_array = array('[#claim_name#]','[#to_name#]','[#post_title#]','[#message#]','[#your_name#]','[#your_number#]','[#post_url_link#]','[#claim_email#]');
		$replace_array = array($yourname,$to_name,$post_url_link,$message,$yourname_link,$your_number,$post_url_link,$youremail);
		$client_message = str_replace($search_array,$replace_array,$email_content);
		
		/* call a mail function */
		_e('Your claim for this post has been sent successfully.',DOMAIN);
		templ_send_email(get_option('admin_email'),$yourname,$to_email,$to_name,$subject,$client_message,$extra='');
		exit;
			
		
	}
	
}


/*************************** LOAD THE BASE CLASS *******************************

 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class templ_claimlist_table extends WP_List_Table
{
	/*
		fetch all the data and store them in an array 
		Call a function that will return all the data in an array and we will assign that result to a variable $_posttaxonomy. FIRST OF ALL WE WILL FETCH DATA FROM POST META TABLE STORE THEM IN AN ARRAY $_posttaxonomy 
	*/
	function fetch_claimpost_data( $_posttaxonomy,$claim_post_id)
	{ 
		$clid  = $claim_post_id;
		$author_id  = $_posttaxonomy['author_id'];
		$auth_data = get_userdata($author_id);
		$post_id  = $_posttaxonomy['post_id'];
	 	$title  = $_posttaxonomy['post_title'];
		$claimant = $_posttaxonomy['claimer_name'];
		$claim_date = $_posttaxonomy['poublish_date'];
		$status = $_posttaxonomy['claim_status'];
		$action ='';
		global $wpdb;
		$data = get_post_meta($claim_post_id,'post_claim_data',true);		
		/* FETCH CLAIM DATA */			
		$post_id = $data['post_id'];
		$post_title = $data['post_title'];
		$claimer_name = $data['claimer_name'];
		$name = str_word_count($claimer_name,1);
	
		$edit_url = get_permalink($_posttaxonomy['post_id']);
	
		if($status == 'approved' && get_post_meta($post_id,'is_verified',true) == 1) :
			$status = YES_VERIFIED; 
		elseif($status == 'declined') :
			$status = DECLINED;
		else : 
			$status = PENDING;
		endif;
		
		global $wpdb,$claim_db_table_name; 
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		function confirmSubmit(str) {
				var answer = confirm("<?php echo DELETE_CONFIRM_ALERT; ?>");
				if (answer){
					window.location = "<?php echo site_url(); ?>/wp-admin/index.php?poid="+str;
					alert('<?php echo ENTRY_DELETED; ?>');
				}
			}
		
		/* ]]> */
		</script>
		
		<?php
		$action = '';
		$action .='<a href="#TB_inline?width=600&height=600&inlineId=claimed_details_'.$clid.'" id="claimed_'.$clid.'" class="thickbox" title="'.__('View claim details',ADMINDOMAIN).'"><i class="fa fa-info"></i></a> &nbsp;&nbsp;';
		if($status == 'Verified' && get_post_meta($post_id,'is_verified',true) == 1){ 
			$action .='<a href='.site_url().'/wp-admin/post.php?post='.$post_id.'&action=edit&decline=yes&clid='.$clid.' title='.DECLINE_CLAIM.'><i class="fa fa-close"></i></a>&nbsp;&nbsp;';
		}else{
			$action .='<a href='.site_url().'/wp-admin/post.php?post='.$post_id.'&action=edit&verified=yes&clid='.$clid.'&user='.$name[0].' title='.VERIFY_CLAIM.'><i class="fa fa-check-circle"></i></a>&nbsp;&nbsp;';
		} 
		$action .= '<a href="javascript:void(0);confirmSubmit('.$clid.');"><i class="fa fa-minus-circle"></i></a>';
		$claim_post_data = get_post($clid);
		$claim_post_date = $claim_post_data->post_date;
		$meta_data = array(
			'post_id'	=> $post_id,
			'post_title'	=> '<strong><a href="'.$edit_url.'">'.$title.'</a></strong>',
			'claimant' 	=> $claimant,
			'claim_date' => date_i18n( get_option( 'date_format' ), strtotime( $claim_post_date ) ),
			'status' 	=> $status,
			'action' 	=> $action
			);?>
			<div id="claimed_details_<?php echo $clid; ?>" style="display:none;width:400px; height:400px;">
				<h2><?php echo __('Author Details',ADMINDOMAIN); ?></h2>
				<p class="tev_description">
					<?php echo  "<strong>".__('Name',ADMINDOMAIN)."</strong>"; echo ": ".$auth_data->display_name; ?>
				</p>
				<p class="tev_description">
					<?php echo  "<strong>".__('Email',ADMINDOMAIN)."</strong>"; echo ": ".$auth_data->user_email; ?>
				</p>
				
				
				<h2><?php echo __('Claimer Details',ADMINDOMAIN); ?></h2>
				<p class="tev_description">
					<?php echo "<strong>".__('Name',ADMINDOMAIN)."</strong>"; echo ": ".$_posttaxonomy['claimer_name']; ?>
				</p>
				<p class="tev_description">
					<?php echo "<strong>".__('Email',ADMINDOMAIN)."</strong>"; echo ": ".$_posttaxonomy['claimer_email']; ?>
				</p>
				<p class="tev_description">
					<?php echo "<strong>".__('Conatct No.',ADMINDOMAIN)."</strong>"; echo ": ".$_posttaxonomy['claimer_contact']; ?>
				</p>
				<p class="tev_description">
					<?php echo "<strong>".__('Message',ADMINDOMAIN)."</strong>"; echo ": ".$_posttaxonomy['claim_msg']; ?>
				</p>
			</div>
		<?php
		return $meta_data;
	}
	
	/* fetch taxonomy data */
	function taxonomy_data()
	{
		global $post,$wpdb;
		$claim_posts =array();
		$claim_post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'claim'");	
		if(count($claim_post_ids) != 0)
		{
			$counter =0;
			foreach ($claim_post_ids as $claim_post_id){
				$data = get_post_meta($claim_post_id,'post_claim_data',true);			
				$claim_posts[] = $this->fetch_claimpost_data($data,$claim_post_id);
			}
		}		
		
		return $claim_posts;
	}
	/* eof - fetch taxonomy data */
	
	/* define the columns for the table */
	function get_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'post_id' => __('ID',ADMINDOMAIN),
			'post_title' => __('Claim On',ADMINDOMAIN),
			'claimant' => __('Claimant',ADMINDOMAIN),
			'claim_date' => __('Date',ADMINDOMAIN),
			'status' => __('Status',ADMINDOMAIN),
			'action' => __('Action',ADMINDOMAIN)
			);
		return $columns;
	}
	
	function process_bulk_action()
	{ 
		/* Detect when a bulk action is being triggered... */
		if('delete' === $this->current_action() )
		{
			$_SESSION['custom_msg_type'] = 'delete';
			$post_type = get_option("templatic_custom_post");
			$taxonomy = get_option("templatic_custom_taxonomy");
			$tag = get_option("templatic_custom_tags");
			foreach($_REQUEST['checkbox'] as $tax_post_type)
			{
				 $taxonomy_slug = $post_type[$tax_post_type]['slugs'][0];
				 $tag_slug = $post_type[$tax_post_type]['slugs'][1];
				 
				 unset($post_type[$tax_post_type]);
				 unset($taxonomy[$taxonomy_slug]);
				 unset($tag[$tag_slug]);
				 update_option("templatic_custom_post",$post_type);
				 update_option("templatic_custom_taxonomy",$taxonomy);
				 update_option("templatic_custom_tags",$tag);
				 if(file_exists(get_template_directory()."/taxonomy-".$taxonomy_slug.".php"))
					unlink(get_template_directory()."/taxonomy-".$taxonomy_slug.".php");
				 if(file_exists(get_template_directory()."/taxonomy-".$tag_slug.".php"))
					unlink(get_template_directory()."/taxonomy-".$tag_slug.".php");
				 if(file_exists(get_template_directory()."/single-".$post_type.".php"))
					unlink(get_template_directory()."/single-".$post_type.".php");
			}	 
			 wp_redirect(admin_url("admin.php?page=custom_taxonomy"));
			 $_SESSION['custom_msg_type'] = 'delete';
			 exit;
		}
	}
    
	function prepare_items()
	{
		$per_page = $this->get_items_per_page('clistings_per_page', 10);
		$columns = $this->get_columns(); /* call function to get the columns */
        $hidden = array();
		$sortable = array();
        $sortable = $this->get_sortable_columns(); /* get the sortable columns */
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action(); /* function to process the bulk actions */
		$data = $this->taxonomy_data(); /* retirive the package data */
		
		/* function that sorts the columns */
		function usort_reorder($a,$b)
		{
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
		if(is_array($data))
	        usort( $data, 'usort_reorder');
		
		$current_page = $this->get_pagenum(); /* get the pagination */
		$total_items = count($data); /* calculate the total items */
		if(is_array($data))
			$this->found_data = array_slice($data,(($current_page-1)*$per_page),$per_page); /* trim data for pagination*/
		$this->items = $this->found_data; 
		/* assign sorted data to items to be used elsewhere in class */
		/* register pagination options */
		
		$this->set_pagination_args( array(
            'total_items' => $total_items,      //WE have to calculate the total number of items
            'per_page'    => $per_page         //WE have to determine how many items to show on a page
        ) );
	}
	
	/* To avoid the need to create a method for each column there is column_default that will process any column for which no special method is defined */
	function column_default( $item, $column_name )
	{
		switch( $column_name )
		{
			case 'cb':
			case 'post_id':
			case 'post_title':
			case 'claimant':
			case 'claim_date':
			case 'status':
			case 'action':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
	
	/* define the columns to be sorted */
	
	function get_sortable_columns()
	{
		$sortable_columns = array(
			'title' => array('title',true)
			);
		return $sortable_columns;
	}
	
	/* define the links displaying below the title */
	
	function column_title($item)
	{
		return '';
	}
	
	/* define the bulk actions */
	
	function get_bulk_actions()
	{
		$actions = array(
			'delete' => 'Delete permanently'
			);
		return $actions;
	}
	
	/* check box to select all the taxonomies */
	
	function column_cb($item)
	{ 
		return sprintf(
			'<input type="checkbox" name="checkbox[]" id="checkbox[]" value="%s" />', $item['ID']
			);
	}
}
/* Add verified budge on detail page */

add_action('after_title_h1','tmpl_afetr_title_returns');

function tmpl_afetr_title_returns(){
	global $post;
	if(get_post_meta($post->ID,'is_verified',true) == 1){
	?>
		<span  data-tooltip aria-haspopup="true" data-options="disable_for_touch:true" class="fa-stack has-tip tip-right" title="Verified"><i class="fa fa-certificate fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span>
	<?php } 
}
?>