<?php
/* Add frontend moderation options */
add_filter( 'comment_text',  'comment_frontend_settings'  );
/* Approve comment if user is set out of moderation queue */
add_filter( 'pre_comment_approved', 'comment_can_moderate','99', 2 );
/* Bring back children of deleted comments */
add_action( 'transition_comment_status','transition_comment_status' , 100, 3 );
	
/*
* moderate comment status to approve,spam or trash.
*/
function comment_can_moderate($approved , $commentdata)
{
	global $current_user,$wpdb;
	$author = $commentdata['comment_author'];
	$email = $commentdata['comment_author_email'];

	$comment_post_author_id = get_post($commentdata['comment_post_ID']);
	$allow_moderat_comment = '';

	$package_select = get_post_meta($commentdata['comment_post_ID'],'package_select',true); // check whether the post is withing the moderate price package.
	if(get_post_meta($package_select,'can_author_mederate',true))
	{
	 $allow_moderat_comment = 'allow';
	}
	

	if(@$allow_moderat_comment == 'allow' && stripos(trim(get_option('blacklist_keys')),$commentdata['comment_author_IP'])===FALSE)
	{
		$ok_to_comment = 0;
		// Comment whitelisting:
		if(get_user_meta($comment_post_author_id->post_author,'user_comment_whitelist',true))
		{
			if ( 'trackback' != $comment_type && 'pingback' != $comment_type && $author != '' && $email != '' ) {
				// expected_slashed ($author, $email)
				$ok_to_comment = $wpdb->get_var("SELECT comment_approved FROM $wpdb->comments WHERE comment_author = '$author' AND comment_author_email = '$email' and comment_approved = '1' LIMIT 1");
			}
		}
		if((get_user_meta($current_user->ID,'tevolution_comment_moderated',true) || $ok_to_comment ) && !get_user_meta($comment_post_author_id->post_author,'user_comment_moderation',true) )
		{
			return  1;
		}else
		{
			return 0;
		}
	}
	elseif(@$allow_moderat_comment == 'allow' && stripos(trim(get_option('blacklist_keys')),$commentdata['comment_author_IP'])!==false)
	{
		return 0;
	}
	else
	{
		return $approved;
	}
}

/*
* mail the post author as per wordpress settings.
*/
add_action("wp_insert_comment", "mail_user_comment",99,2);
function mail_user_comment($comment_id, $comment_object)
{
	$comment_post_author_id = get_post($comment_id);
	
	if(get_user_meta($comment_post_author_id->post_author,'user_comments_notify',true))
	{
		wp_notify_postauthor( $comment_id ); // mail to post author.function of wordpress
	}
	if(get_user_meta($comment_post_author_id->post_author,'user_moderation_notify',true) && !$comment_object->comment_approved)
	{
		wp_notify_postauthor( $comment_id );	// mail to post author.function of wordpress when comment is held for moderation.
	}
}

/*
*Displays frontend moderation options.
*/
function comment_frontend_settings ($content) {
		if( is_admin() ) {
			return $content;
		}
		
	global  $user_ID, $comment, $post, $current_user;
	$user_info = get_userdata($comment->user_id);
	$allow_moderat_comment = '';
		
	$package_select = get_post_meta($post->ID,'package_select',true);
	if(get_post_meta($package_select,'can_author_mederate',true))
	{
	 $allow_moderat_comment = 'allow';
	}
	if(@$allow_moderat_comment == 'allow') {
	if( $post->post_author == $current_user->ID ) { 
	  $child = comment_has_child($comment->comment_ID, $comment->comment_post_ID);
	  /*  Container   */
		$out = '<p class="tevolution_comment-frontend">';
		/* Approve comment */
		if($comment->comment_approved == '0') {
		$out .= '<span id="comment-'.$comment->comment_ID.'-approve">'.get_comment_approve($comment).' | </span>';
	  }
				/*  Delete comment  */
				$out .= get_comment_delete($comment).' | ';
				/*  Delete thread   */
				if($child>0) {
					$out .= get_comment_delete_thread($comment).' | ';
				}
				/*  If IP isn't banned  */
				if(stripos(trim(get_option('blacklist_keys')),$comment->comment_author_IP)===FALSE) {
						/*  Delete and ban  */
						$out .= get_comment_delete_ban($comment);//.' | ';
						/*  Delete thread and ban   */
						if($child>0)
								$out .= ' | '.get_comment_delete_thread_ban($comment);
				} else {
						$out .= 'IP '.$comment->comment_author_IP.' ';
						$out .= __('already banned!', TEVOLUTION_COMMENT_MODERATE_DOMAIN );
				}
				
				$out .= '</p>';
				$out .= '<span id="tevolution-comment-'.$comment->comment_ID.'"></span>';

				return $content . $out;	
			}
		}
	return $content;
}
/*
* check whether comment has reply or not.
*/
function comment_has_child($id, $postid) {
	global $wp_query;
	
	if ($wp_query->comments != NULL ) {
	  foreach( $wp_query->comments AS $comment ) {
		if( $comment->comment_parent == $id ) {
		  return true; 
		}
	  }
	}
	return false;
	
} 

/*
* text to approve commnet on post detail page.
*/
function get_comment_approve($comment) {
	return '<a href="#" onclick="tevolution_comment_approve('.$comment->comment_ID.'); return false">' . __('Approve',TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
}


/*
* text to delete commnet on post detail page.
*/
function get_comment_delete($comment) {
	return '<a href="#" onclick="tevolution_comment_delete('.$comment->comment_ID.'); return false">' . __('Delete', TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
}

/*
* text to delete commnet and banned ip on post detail page.
*/
function get_comment_delete_ban($comment) {
	return '<a href="#" onclick="tevolution_comment_delete_ban('.$comment->comment_ID.',\''.$comment->comment_author_IP.'\'); return false">' . __('Delete & Ban IP', TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
}


/*
* text to delete whole thread on post detail page.
*/
function get_comment_delete_thread($comment) {
	return '<a href="#" onclick="tevolution_comment_delete_thread('.$comment->comment_ID.'); return false">' . __('Delete Thread', TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
}


/*
* text to delete whole thread and banned ip on post detail page.
*/
function get_comment_delete_thread_ban($comment) {
	return '<a href="#" onclick="tevolution_comment_delete_thread_ban('.$comment->comment_ID.',\''.$comment->comment_author_IP.'\'); return false">' . __('Delete Thread & Ban IP',TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
}

/*
* text to delete whole thread and banned ip on post detail page.
*/
function get_comment_moderated($user_ID, $frontend = true) {
	if($frontend)
		$frontend2 = 'true';
	else
		$frontend2 = 'false';
		
	$out = '<a href="#" class="commenter-'.$user_ID.'-moderated" onclick="tevolution_comment_moderated('.$user_ID.', '. $frontend2 .'); return false">'; 
	if(!get_user_meta($user_ID,'tevolution_comment_moderated'))
		if($frontend)
			$out .= __('Allow user to comment without moderation',TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
		else
			$out .= __('Moderated', TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
	else
		if($frontend)
			$out .= __('Moderate future comments by this user', TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
		else
			$out .= __('Unmoderated', TEVOLUTION_COMMENT_MODERATE_DOMAIN) . '</a>';
	return  $out;
} 


add_action( 'wp_ajax_tevolution_comment_approve', 'tevolution_comment_approve');
add_action( 'wp_ajax_tevolution_comment_delete', 'tevolution_comment_delete');
add_action( 'wp_ajax_tevolution_comment_moderated', 'tevolution_comment_moderated');

/*
* fetch pending comment when post author is logged inn.
*/
add_filter('comments_array','display_pending_comment_post_author'); 
function display_pending_comment_post_author($comments){
	global $user_ID,$wpdb,$post;
	
    $comemnts=NULL;
    /*modify this part to get only user comments*/
    if ( $user_ID) {
              $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1' OR  comment_approved = '0'  )  ORDER BY comment_date_gmt", $post->ID, $user_ID));
        } else if ( empty($comment_author) ) {
                $comments = get_comments( array('post_id' => $post->ID, 'status' => 'approve', 'order' => 'ASC') );
       } else {
                $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND ( comment_approved = '1' OR ( comment_author = %s AND comment_author_email = %s AND comment_approved = '0' ) ) ORDER BY comment_date_gmt", $post->ID, wp_specialchars_decode($comment_author,ENT_QUOTES), $comment_author_email));

    }
    return $comments;
}


/*
* approve the comment.
*/
function tevolution_comment_approve() {
	if(!wp_set_comment_status( $_REQUEST['id'], 'approve' ))
		die('db error');
}
	
/*
* delete and banned ip for particular comment.
*/
function tevolution_comment_delete() {
	global $wpdb;

	if(isset($_REQUEST['ip']) && stripos(trim(get_option('blacklist_keys')),$_REQUEST['ip'])===FALSE) {
		
		$objComment = get_comment( $_REQUEST['id'] );
		$commentStatus = $objComment->comment_approved;
		$blacklist_keys = trim(stripslashes(get_option('blacklist_keys')));      
		$blacklist_keys_update = $blacklist_keys."\n".$_REQUEST['ip'];
		update_option('blacklist_keys', $blacklist_keys_update);

		$wpdb->update( 'wp_comments', array( 'comment_approved' => 'spam' ), array( 'comment_ID' => intval($_REQUEST['id']) ) );
		do_action('transition_comment_status','spam','unapproved', $objComment );
		$wpdb->update( 'wp_comments', array( 'comment_approved' => $commentStatus ), array( 'comment_ID' => intval($_REQUEST['id']) ) );
	}

	if (isset($_REQUEST['thread'])) {
		if($_REQUEST['thread'] == 'yes') {
			tevolution_comment_delete_recursive($_REQUEST['id']);
		} 
	}
	else {
	if(!wp_delete_comment($_REQUEST['id']))
		die('db error');
	}       

}

/*
* particular user can post without moderate the comment or not.
*/
function tevolution_comment_moderated() {
	if(get_user_meta($_REQUEST['id'],'tevolution_comment_moderated')) {
	   if(!delete_user_meta($_REQUEST['id'],'tevolution_comment_moderated'))
			die('meta error');
		echo 'user moderated';
	}
	else {
		if(!update_user_meta($_REQUEST['id'],'tevolution_comment_moderated','no'))
			die('meta error');
		echo 'user non-moderated';
	}
}

/*
* delete the reply of main comment.
*/
function tevolution_comment_delete_recursive($id) {
	global  $wpdb;  
	echo ' '.$id.' ';
	$comments = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE `comment_parent` ='{$id}'",ARRAY_A);
	if(strlen($wpdb->last_error)>0)
		die('db error');
	if(!wp_delete_comment($id))
		die('db error');             
	/*  If there are no more children */
	if(count($comments)==0)
		return;
	foreach($comments AS $comment) {
		tevolution_comment_delete_recursive($comment['comment_ID']);
	}
}


/*
* set the status for banned comment.
*/
function transition_comment_status( $new_status, $old_status, $comment ) {
  global $wpdb;
  
  if( $old_status == 'trash' && $new_status != 'spam' ) { //  restoring comment
	  $children = get_comment_meta( $comment->comment_ID, 'children', true );
	  if( $children && is_array( $children ) ) {
		$children = implode( ',', $children );
		$wpdb->query( "UPDATE $wpdb->comments SET comment_parent = '{$comment->comment_ID}' WHERE comment_ID IN ({$children}) " );
	  }
	  delete_comment_meta( $comment->comment_ID, 'children' );
  }
  
  if( $new_status == 'trash' ) {  //  trashing comment
	if( function_exists( 'update_comment_meta' ) ) {  //  store children in meta
	  $children = $wpdb->get_col( "SELECT comment_ID FROM $wpdb->comments WHERE comment_parent = '{$comment->comment_ID}' " );
	  if( $children ) {
		update_comment_meta( $comment->comment_ID, 'children', $children );
	  }
	} //  assign new parents
	
	$wpdb->query( "UPDATE $wpdb->comments SET comment_parent = '{$comment->comment_parent}' WHERE comment_parent = '{$comment->comment_ID}' " );

  }
}

/*
* call the ajax function to perform approve,delete or banned the ip.
*/
add_action('wp_footer','comment_moderate_script');
function comment_moderate_script()
{
	?>
		<script>
			/*  approve/disapprove comment  */
			function tevolution_comment_approve(id) { 
				jQuery("#comment-"+id+"-approve").text( ' | '); 
				jQuery.ajax({
					type: 'POST',
					url: ajaxUrl,
					data: {"action": "tevolution_comment_approve", 'id': id},
					success: function(data){
						jQuery("#comment-body-"+id).children(":first").text('');
						jQuery("#comment-"+id+"-approve").remove();
						jQuery("#comment-"+id+"-unapproved").removeClass("tc_highlight");
					}
				});
				return false;  
			}

			/*  delete comment  */
			function tevolution_comment_delete(id) {
					jQuery.ajax({
						type: 'POST',
						url: ajaxUrl,
						data: {"action": "tevolution_comment_delete", 'id': id},
						success: function(data){
							if(data.search(/db error/)==-1) {
								var item = jQuery("[id^='comment'][id$='"+id+"']");
								item.slideUp();
							} 
						}
					});
					return false;
				
			}

			/*  delete comment and ban ip */
			function tevolution_comment_delete_ban(id,ip) {
					jQuery.ajax({
						type: 'POST',
						url: ajaxUrl,
						data: {"action": "tevolution_comment_delete", 'id': id, 'ip': ip},
						success: function(data){
							if(data.search(/db error/)==-1) {
								var item = jQuery("[id^='comment'][id$='"+id+"']");
								item.slideUp();
							} 
						}
					});
					return false;
			}

			/*  delete thread */
			function tevolution_comment_delete_thread(id) {
					jQuery.ajax({
						type: 'POST',
						url: ajaxUrl,
						data: {"action": "tevolution_comment_delete", 'id': id, 'thread': 'yes'},
						success: function(data){
							if(data.search(/db error/)==-1) {
								var posts = data.split(" ");
								var i = 0;
								while (i < posts.length) {
									if(posts[i]!='') {
										var item = jQuery("[id^='comment'][id$='"+posts[i]+"']");
										item.slideUp();
									}
									i+=1;
								}
							} 
						}
					});
					return false;
			}

			/*  delete thread and ban */
			function tevolution_comment_delete_thread_ban(id, ip) {
				
					jQuery.ajax({
						type: 'POST',
						url: ajaxUrl,
						data: {"action": "tevolution_comment_delete", 'id': id, 'ip': ip, 'thread': 'yes'},
						success: function(data){
							if(data.search(/db error/)==-1) {
								var posts = data.split(" ");
								var i = 0;
								while (i < posts.length) {
									if(posts[i]!='') {
										var item = jQuery("[id^='comment'][id$='"+posts[i]+"']");
										item.slideUp();
									}
									i+=1;
								}
							}
						}
					});
					return false;
			}

			/*  manage user moderation  */
			function tevolution_comment_moderated(id, frontend) {
				jQuery.ajax({
						type: 'POST',
						url: ajaxUrl,
						data: {"action": "tevolution_comment_moderated", 'id': id},
						success: function(data){
							if(data.search(/user non-moderated/)!=-1)
								if(frontend)
									jQuery(".commenter-"+id+"-moderated").text('<?php _e('Moderate future comments by this user',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?>');
								else
									jQuery(".commenter-"+id+"-moderated").text('<?php _e('Unmoderated',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?>');
							else if (data.search(/user moderated/)!=-1)
								if(frontend)
									jQuery(".commenter-"+id+"-moderated").text('<?php _e('Allow user to comment without moderation',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?>');
								else
									jQuery(".commenter-"+id+"-moderated").text('<?php _e('Moderated',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?>');
								else
									jQuery(".commenter-"+id+"-moderated").text('<?php _e('Error',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?>');
						}
					});
					return false;
			}

		</script>
	<?php
}
	
/* start code to add comment moderate settings on author dash board */
add_action('init','tevolution_moderate_comment_author_tab');
function tevolution_moderate_comment_author_tab(){
	add_action('tevolution_author_tab','tmpl_dashboard_moderate_comment_tab'); // to display tab 
}
/*
* comment moderate settings on author dash board .
*/
function tmpl_dashboard_moderate_comment_tab(){
	global $current_user,$curauth,$wp_query,$post;	
	$qvar = $wp_query->query_vars;
	$author = $qvar['author'];
	if(isset($author) && $author !='') :
		$curauth = get_userdata($qvar['author']);
	else :
		$curauth = get_userdata(intval($_REQUEST['author']));
	endif;	
	if(isset($_REQUEST['moderate_comment']) && $_REQUEST['moderate_comment'] =='moderate_comment_settigns'){
		$class = 'nav-author-post-tab-active';
	}else{
		$class ='';
	}
	$custom_post_type = tevolution_get_post_type(); //fetch tevolution post type.
	$allow_moderat_comment = '';
	$args = array(
		'author'        =>  $current_user->ID,
		'orderby'       =>  'post_date',
		'order'         =>  'ASC',
		'posts_per_page' => -1,
		'post_status'	=> 'publish',
		'post_type'		=> $custom_post_type
		);
		$the_query = new WP_Query( $args );

		// The Loop
		$i = 0;
		if ( $the_query->have_posts() ) {
			while ($the_query->have_posts()) : $the_query->the_post();
				 if(get_post_meta($post->ID,'package_select',true) != '')
				 {
					 $package_select = get_post_meta($post->ID,'package_select',true);
					 if(get_post_meta($package_select,'can_author_mederate',true))
					 {
						 $allow_moderat_comment = 'allow';
						 break;
					 }
				 }
			endwhile;
		} 
		wp_reset_postdata();
	if($current_user->ID == $curauth->ID && @$allow_moderat_comment == 'allow'){
		echo "<li><a class='author_post_tab ".$class."' href='".esc_url(get_author_posts_url($current_user->ID).'?moderate_comment=moderate_comment_settigns&custom_post=all')."'>";
		_e(esc_html('Comment Settings'),DOMAIN);
		echo "</a></li>";
	}
	
}

if(isset($_REQUEST['moderate_comment']) && $_REQUEST['moderate_comment'] == 'moderate_comment_settigns'){
	add_action( 'before_loop_archive','tmpl_dashboard_comment_moderate_settigns_tab',21); // to display tab 
}
/*
* html to moderate comment on user dashboard page .
*/
function tmpl_dashboard_comment_moderate_settigns_tab()
{
	global $current_user,$curauth,$wp_query,$wpdb,$post;
	$user_id = get_query_var('author');
	$curauth = get_userdata($user_id);
	
	if(isset($_REQUEST['moderate_comment']) && $_REQUEST['moderate_comment']=='moderate_comment_settigns'):
		if($current_user->ID == $curauth->ID){
			
			if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true')
			{
				update_user_meta($current_user->ID,'user_comments_notify',$_REQUEST['user_comments_notify']);
				update_user_meta($current_user->ID,'user_moderation_notify',$_REQUEST['user_moderation_notify']);
				update_user_meta($current_user->ID,'user_comment_moderation',$_REQUEST['user_comment_moderation']);
				update_user_meta($current_user->ID,'user_comment_whitelist',$_REQUEST['user_comment_whitelist']);
			?>
				<div class="updated settings-error" id="setting-error-settings_updated"> 
					<p>
						<strong><?php _e('Settings saved.',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?></strong>
					</p>
				</div>
			<?php } ?>
			<form name="moderate_comment" action="<?php echo get_author_posts_url( $current_user->ID); ?>?moderate_comment=moderate_comment_settigns&custom_post=all&settings-updated=true" method="post">
            	<ul class="moderate_comment-checklist">
                	<li>
                    	<div class="form-label">
                        	<span><?php _e('E-mail me whenever',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?></span>
                        </div>
                        <div class="form-control">
                        	<div class="form-group">
                            	<input type="checkbox" <?php if(get_user_meta($current_user->ID,'user_comments_notify',true)){ ?> checked="checked" <?php } ?> value="1" id="user_comments_notify" name="user_comments_notify">
                                <label for="user_comments_notify"><?php _e('Anyone posts a comment',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?> </label>
                            </div>
                            <div class="form-group">
                            	<input type="checkbox" <?php if(get_user_meta($current_user->ID,'user_moderation_notify',true)){ ?>checked="checked"<?php } ?> value="1" id="user_moderation_notify" name="user_moderation_notify">
                                <label for="user_moderation_notify"><?php _e('A comment is held for moderation',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?></label>
                            </div>
                        </div>
                    </li>
                    <li>
                    	<div class="form-label">
                        	<span><?php _e('Before a comment appears',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?></span>
                        </div>
                        <div class="form-control">
                        	<div class="form-group">
                            	<input type="checkbox" <?php if(get_user_meta($current_user->ID,'user_comment_moderation',true)){ ?>checked="checked"<?php } ?> value="1" id="user_comment_moderation" name="user_comment_moderation">
                                <label for="user_comment_moderation"><?php _e('Comment must be manually approved',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?></label>
                            </div>
                            <div class="form-group">
                            	<input type="checkbox" <?php if(get_user_meta($current_user->ID,'user_comment_whitelist',true)){ ?>checked="checked"<?php } ?> value="1" id="user_comment_whitelist" name="user_comment_whitelist">
                                <label for="user_comment_whitelist"><?php _e('Comment author must have a previously approved comment',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?></label>
                            </div>
                        </div>
                    </li>
                    <li>
                    	<div class="form-label">&nbsp;</div>
                        <div class="form-control">
                        	<input type="submit" name="submit" id="submit" value="<?php _e('Submit',TEVOLUTION_COMMENT_MODERATE_DOMAIN); ?>"  />
                        </div>
                    </li>
                </ul>
			</form>
			<?php
			add_action('wp_footer','tmpl_remove_extra_content');/*remove extra div from author page on this settings page*/
		}
	endif;
}

/*
* added html for user can comment  in price package.
*/
add_action('add_new_row_pricepackages','comment_moderate_row_pricepackages');
function comment_moderate_row_pricepackages($id)
{
	?>
		<tr>
			<th valign="top">
				<label for="can_author_mederate" class="form-textfield-label"><?php echo CAN_AUTHOR_MODERATE; ?></label>
			</th>
			<td>
				<label for="can_author_mederate"><input type="checkbox" name="can_author_mederate" id="can_author_mederate" value="1" <?php if(isset($id) && $id != '' && get_post_meta($id, 'can_author_mederate', true) == 1){ echo 'checked=checked'; } ?> "/>&nbsp;
				<?php echo YES; ?></label><br/>
				<p class="description"><?php echo THOUGHTFUL_COMMENT_STATUS_DESC; ?>.</p>
			</td>
		</tr>
	<?php
}

/*
* save the option that price package can moderate or not.
*/
add_action('save_post_monetization_package','save_comment_moderate_option');
function save_comment_moderate_option($package_id)
{
	update_post_meta($package_id,'can_author_mederate',$_REQUEST['can_author_mederate']);
}
/*
* remove loop error div from author page of comment settings tab.
*/
function tmpl_remove_extra_content()
{
	?>
		<script>
			jQuery(document).ready(function(){
				jQuery('ul.looperror').remove();
			});
		</script>
	<?php
}

/*
* show comment to post author that he can approce particular comment or not
*/
add_filter('comment_approved_filter','tmpl_comment_approved_filter');
function tmpl_comment_approved_filter()
{
	global $post, $comment,$current_user;
	$comment_post_author_id = get_post($post->ID);
	$allow_moderat_comment = '';
	$package_select = get_post_meta($comment_post_author_id->ID,'package_select',true);
	$comment_moderate = get_post_meta($package_select,'can_author_mederate',true);
	if ( '0' == $comment->comment_approved && $comment_post_author_id->post_author != $current_user->ID && @$comment_moderate) :
		return 1;
	else:
		return 0;
	endif;
}
?>
