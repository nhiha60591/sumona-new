<?php
class Templatic{
	var $file;
	var $version;
}

/* This class will fetch the all add-ons main file */
class Templatic_connector { 
	
	public function templ_dashboard_extends(){
	
		/* This is the correct way to loop over the directory. */
			
		do_action('templconnector_bundle_box');
			
		
		/* to get t plugins */	
	
	}	
	/* -- Function contains bundles of file which creates the bunch of templatic other plugins list EOF - */
	function templ_extend(){
		$modules_array = array();
		$modules_array = array('templatic-custom_taxonomy','templatic-custom_fields','templatic-registration','templatic-monetization','templatic-claim_ownership');
		require_once(TEMPL_MONETIZE_FOLDER_PATH.'templ_header_section.php' );
		?>
        <p class="tevolution_desc"><?php echo __('The plugins listed below will help you completely transform your website and give it some extra functionalities. Please click the "Details & Purchase" button next to any of them to find out more about the functions they each offer.',ADMINDOMAIN);?></p>
          <?php
		echo '
		<div id="tevolution_bundled" class="metabox-holder wrapper widgets-holder-wrap"><table cellspacing="0" class="wp-list-tev-table postbox fixed pages ">
			<tbody style="background:white; padding:40px;">
			<tr><td>
			';
		/* This is the correct way to loop over the directory. */			
		do_action('tevolution_extend_box');
		/* to get t plugins */			
		echo '</td></tr>
		</tbody></table>
		</div>
		';
	
		require_once(TEMPL_MONETIZE_FOLDER_PATH.'templ_footer_section.php' );
	}
	
	
	/* -- Function contains bundles of file which creates the bunch of paymentgateway plugin lists backend EOF - */
	function templ_payment_gateway(){
		$modules_array = array();
		$modules_array = array('templatic-custom_taxonomy','templatic-custom_fields','templatic-registration','templatic-monetization','templatic-claim_ownership');
		require_once(TEMPL_MONETIZE_FOLDER_PATH.'templ_header_section.php' );
		?>
          <p class="tevolution_desc"><?php echo __('The payment gateways below will help you maximize the earning potential of your site. Offering more payment options to your users will help encourage more people, who perhaps might not find the built-in PayPal suitable, to submit a listing on your directory.',ADMINDOMAIN);?></p>
          <?php
		
		echo '
		<div id="tevolution_bundled" class="metabox-holder wrapper widgets-holder-wrap"><table cellspacing="0" class="wp-list-tev-table postbox fixed pages ">
			<tbody style="background:white; padding:40px;">
			<tr><td>
			';
		/* This is the correct way to loop over the directory. */
		do_action('tevolution_payment_gateway');
		/* to get t plugins */			
		echo '</td></tr>
		</tbody></table>
		</div>
		';
	
		require_once(TEMPL_MONETIZE_FOLDER_PATH.'templ_footer_section.php' );
	}
	
	
	function templ_overview(){
	
		require_once(TEMPL_MONETIZE_FOLDER_PATH.'templ_header_section.php' );
		
		if((isset($_REQUEST['tab']) && $_REQUEST['tab'] =='overview') || !isset($_REQUEST['tab'])){ ?>
		<?php /* do_action('tevolution_details'); action to get server date time and other details */ ?>
		<script type="text/javascript">
			jQuery( document ).ready(function() {
				jQuery('.templatic-dismiss').remove();
			});
		</script>
		<div id="tevolution_dashboard_fullwidth">
		<div id="poststuff">
			<div class="postbox " id="formatdiv">
				<div class="handlediv" title="Click to toggle">
				<br>
				</div>
				<h3 class="hndle">
					<span><?php echo __('Licence key',ADMINDOMAIN); ?></span>
				</h3>
				<div id="licence_fields">
					<form action="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu";?>" name="" method="post">
					<div class="inside">
					<div id="licence_fields">
				
						<div>
						<input type="password" name="licencekey" id="licencekey" value="<?php echo get_option('templatic_licence_key_'); ?>" size="30" max-length="36" PLACEHOLDER="templatic.com purchase code"/>
						<?php do_action('tevolution_error_message'); ?>
						</div>
                        <?php
						$templatic_licence_key = get_option('templatic_licence_key');
						if(strstr($templatic_licence_key,'is_supreme') && get_option('templatic_licence_key_') !='' && !$_POST){
							$verify= __('Verified',ADMINDOMAIN);
						}else{
							$verify=__('Verify',ADMINDOMAIN);
						}
						?>
						<input type="submit" accesskey="p" value="<?php echo $verify;?>" class="button button-primary button-large" id="Verify" name="Verify">
						<p><?php echo __('Enter the license key in order to unlock the plugin and enable automatic updates.',ADMINDOMAIN); ?></p>
					</div>
					</div>
					</form>
				<div class="licence_fields">
				</div>	
				</div>
			</div>
		</div>
		</div>
		<?php } 
		
		tmpl_overview_box();
		require_once(TEMPL_MONETIZE_FOLDER_PATH.'templ_footer_section.php' );
	}
	/*
	Name : bdw_get_images_with_info
	Description :Return the images of post with attachment information
	*/
	function bdw_get_images_with_info($iPostID,$img_size='thumb') 
	{
    $arrImages =& get_children('order=ASC&orderby=menu_order ID&post_type=attachment&post_mime_type=image&post_parent=' . $iPostID );
	$return_arr = array();
	if($arrImages) 
	{		
       foreach($arrImages as $key=>$val)
	   {
	   		$id = $val->ID;
			if($img_size == 'large')
			{
				$img_arr = wp_get_attachment_image_src($id,'full');	// THE FULL SIZE IMAGE INSTEAD
				$imgarr['id'] = $id;
				$imgarr['file'] = $img_arr[0];
				$return_arr[] = $imgarr;
			}
			elseif($img_size == 'medium')
			{
				$img_arr = wp_get_attachment_image_src($id, 'medium'); //THE medium SIZE IMAGE INSTEAD
				$imgarr['id'] = $id;
				$imgarr['file'] = $img_arr[0];
				$return_arr[] = $imgarr;
			}
			elseif($img_size == 'thumb')
			{
				$img_arr = wp_get_attachment_image_src($id, 'thumbnail'); // Get the thumbnail url for the attachment
				$imgarr['id'] = $id;
				$imgarr['file'] = $img_arr[0];
				$return_arr[] = $imgarr;
				
			}
	   }
	  return $return_arr;
	}
	}
}
?>