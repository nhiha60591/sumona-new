<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title><?php echo __( 'Tevolution Update', TEVOLUTION_STATISTCS_DOMAIN ); ?></title>
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
	$self_url = add_query_arg( array( 'slug' => 'statistics', 'action' => 'statistics' , '_ajax_nonce' => wp_create_nonce( 'statistics' ), 'TB_iframe' => true ), admin_url( 'admin-ajax.php' ) );
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
		  	$warnning_message="Invalid UserName or password. are you using templatic member username and password?";
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
			if(in_array('Tevolution - Statistics Plugin',$product))
			{
				$successfull_login=1;				
				$download_link=$data_product['Tevolution - Statistics Plugin'];
			}else
			{
				$warnning_message="We don't find Tevolution - Statistics Plugin active in your templatic account, you will not be able to update without a license";
			}
		}
	}else{
		if(isset($_POST['templatic_login']) && ($_POST['templatic_username'] =='' || $_POST['templatic_password']=='')){
		$warnning_message="Invalid UserName or password. Please enter templatic member's username and password."; }
	}
	?>
     <body style="padding:40px;">
               
           <div id='pblogo'>
               <img src="<?php echo esc_url( plugins_url( '/images/templatic.png', __FILE__ ) ); ?>" style="margin-right: 50px;" />
		   </div> 
          <div class='wrap directory_login wrap templatic_login'>
           <?php
		if(isset($warnning_message) && $warnning_message!='')
		{?>
			<div class='error'><p><strong><?php _e($warnning_message,DIR_DOMAIN);?></strong></p></div>	
		<?php
          }
		?>
          <?php if(!isset($successfull_login) && $successfull_login!=1):?>
			   
               <p class="info">
			   
			   <?php echo __('Enter your Templatic account credentials to proceed with the update. These are the same details you use for the member area.',TEVOLUTION_STATISTCS_DOMAIN);?></p>
               <form action="<?php echo $self_url;?>" name="" method="post">
                   <style type="text/css">
						.wp-core-ui .button, .wp-core-ui .button-secondary {
							background: none repeat scroll 0 0 #F7F7F7;
							border-color: #CCCCCC;
							box-shadow: 0 1px 0 #FFFFFF inset, 0 1px 0 rgba(0, 0, 0, 0.08);
							color: #555555;
							vertical-align: top;
						}
				   </style> 
                   <table>
					<tr>
					<td><label><?php echo __('User Name', TEVOLUTION_STATISTCS_DOMAIN)?></label></td>
					<td><input type="text" name="templatic_username"  /></td>
					</tr>
					<tr>
                    <td><label><?php echo __('Password', TEVOLUTION_STATISTCS_DOMAIN)?></label></td>
					<td><input type="password" name="templatic_password"  /></td>
					</tr>
					<tr>
					<td><input type="submit" name="templatic_login" value="Sign In" class="button-secondary"/></td>
					<td><a title="Close" id="TB_closeWindowButton" href="#" class="button button-secondary"><?php echo __('Cancel',TEVOLUTION_STATISTCS_DOMAIN); ?></a></td>
					</tr>
				</table>
				
               </form>
               <p>Forgot your password? You can reset it from the <a href="http://templatic.com/members/member">member area login screen.</a></p>
          <?php else:								
				 $file=TEVOLUTION_STATISTCS_SLUG;
		 		 $download= wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_' . $file);
				 echo ' Tevolution-Statistics Plugin <a href="'.$download.'"  target="_parent" class="button-secondary">Update Now</a>';
			 endif;?>
          </div>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
?>
	</body>
</html>
