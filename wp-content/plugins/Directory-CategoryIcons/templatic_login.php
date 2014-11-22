<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title><?php echo __( 'Directory CategoryIcon Update', CIDOMAIN ); ?></title>
		<?php
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'jquery-tools', plugins_url( '/css/tabs.css', __FILE__ ) );
			wp_admin_css( 'global' );
			wp_admin_css( 'admin' );
			wp_admin_css();
			wp_admin_css( 'colors' );
			do_action('admin_print_styles');
			do_action('admin_print_scripts');
			do_action('admin_head');
			?>
	</head>
     <?php
	global $current_user;
	$self_url = add_query_arg( array( 'slug' => 'directory_categoryicon', 'action' => 'directory_categoryicon' , '_ajax_nonce' => wp_create_nonce( 'directory_categoryicon' ), 'TB_iframe' => true ), admin_url( 'admin-ajax.php' ) );
	if(isset($_POST['templatic_login']) && isset($_POST['templatic_username']) && $_POST['templatic_username']!=''  && isset($_POST['templatic_password']) && $_POST['templatic_password']!='')
	{ 
		$arg=array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'username' => $_POST['templatic_username'], 'password' => $_POST['templatic_password']),
			'cookies' => array()
		    );
		$warnning_message='';
		$response = wp_remote_post('http://templatic.com/members/login_api.php',$arg );	
	
		if( is_wp_error( $response ) ) {
		  	$warnning_message = __("Invalid UserName or password. are you using templatic member username and password?",CIDOMAIN);
		} else { 
		  	$data = json_decode($response['body']);
		}
		/*Return error message */
		if(isset($data->error_message) && $data->error_message!='')
		{
			$warnning_message=$data->error_message;			
		}
		/*Finish error message */
		$data_product = (array)$data->product;
		if(isset($data_product) && is_array($data_product))
		{		
			foreach($data_product as $key=>$val)
			{
				$product[]=$key;
			}						
			if(in_array(CATEGORYICON_PLUGIN_NAME,$product))
			{
				$successfull_login=1;				
				$download_link=$data_product[PLUGIN_NAME];
			}else
			{
				$warnning_message = __("Oops, we have a problem. The information you provided is either incorrect or you don't have",CIDOMAIN).' '.CATEGORYICON_PLUGIN_NAME.''."__('available inside the account. If you think everything should be ok with your account, please",CIDOMAIN)." <a href='http://templatic.com/contact'>".__('contact us.',CIDOMAIN)."</a>";
			}
		}
	}else{
		if(isset($_POST['templatic_login']) && ($_POST['templatic_username'] =='' || $_POST['templatic_password']=='')){
		$warnning_message = __("Invalid UserName or password. Please enter templatic member's username and password.",CIDOMAIN);  }
	}
	?>
     <body style="padding:40px;">
               
           <div id='pblogo'>
               <img src="<?php echo esc_url(TEVOLUTION_PAGE_TEMPLATES_URL.'/tmplconnector/monetize/images/templatic.png' ); ?>" style="margin-right: 50px;" />
		   </div> 
          <div class='wrap templatic_login'>
           <?php
		if(isset($warnning_message) && $warnning_message!='')
		{?>
			<div class='error'><p><strong><?php echo $warnning_message;?></strong></p></div>	
		<?php
          }
		?>
          <?php if(!isset($successfull_login) && $successfull_login!=1):?>
			   
               <p class="info">
			   
			   <?php echo __('Enter your Templatic account credentials to proceed with the update. These are the same details you use for the member area.',CIDOMAIN);?></p>
               <form action="<?php echo $self_url;?>" name="" method="post">
                   <table>
					<tr>
					<td><label><?php echo __('User name: ',CIDOMAIN); ?></label></td>
					<td><input type="text" name="templatic_username"  /></td>
					</tr>
					<tr>
                    <td><label><?php echo __('Password: ',CIDOMAIN); ?></label></td>
					<td><input type="password" name="templatic_password"  /></td>
					</tr>
					<tr>
					<td><input type="submit" name="templatic_login" value="Sign In" class="button-primary"/></td>
					<td><a title="Close" id="TB_closeWindowButton" href="#" class="button-secondary"><?php echo __('Cancel',CIDOMAIN); ?></a></td>
					</tr>
				</table>
				
               </form>
               <p>Forgot your password? You can reset it from the <a href="http://templatic.com/members/member">member area login screen.</a></p>
          <?php else:								
				 $file=CATEGORYICON_SLUG;
		 		 $download= wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_' . $file);
				 echo '<p><b>'.__('Important!',CIDOMAIN).'</b> '.__('Clicking on "Update Now" will overwrite all files. If you customized the',CIDOMAIN).' '.PLUGIN_NAME.' '.__('code in any way please abort the update process and backup now.',CIDOMAIN).'</p><p><a href="https://codex.wordpress.org/WordPress_Backups">'.__('Click here',CIDOMAIN).'</a> '.__('for tips on how to backup your files and database.',CIDOMAIN).'</p><a href="'.$download.'"  target="_parent" class="button button-primary">'.__('Update Now',CIDOMAIN).'</a>';
				 
			 endif;?>
          </div>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
?>
	</body>
</html>