<?php
class Main_Connector_Class  {
	var $components;
	var $current_action_response;
	public function __construct() {		
		$this->sections = array(
								'tevolution_bundled' => array(
														'name' => __( 'Core Module', ADMINDOMAIN ), 
														'description' => __( 'Features bundled with Tevolution.', ADMINDOMAIN )
													), 								
								'standalone_plugin' => array(
														'name' => __( 'Extend', ADMINDOMAIN ), 
														'description' => __( 'Plugins developed by Templatic.', ADMINDOMAIN )
													)
								);
		$this->templatic_components = array(
								'tevolution_bundled' => array(
											    array('name'=>__('Bulk Import / Export', ADMINDOMAIN )),
											    array('name'=>__('Claim Post Manager', ADMINDOMAIN )),
											    array('name'=>__('Custom Fields Manager', ADMINDOMAIN )),
											    array('name'=>__('Custom Post Types Manager', ADMINDOMAIN )),
											    array('name'=>__('Security Manager', ADMINDOMAIN)),
											    array('name'=>__('Monetization', ADMINDOMAIN)),
											    array('name'=>__('User registration/Login Management', ADMINDOMAIN)),
											    ),
								//add templatic standalone plugin information
								'standalone_plugin' => @$stand_alone_plugin
								);
			
		$this->closed_components = array();
		
	} // End __construct()
	
	public function get_section_links () {
		$html = '';
		
		$total = 0;
		
		$sections = array(
						'all' => array( 'href' => '#all', 'name' => __( 'All', ADMINDOMAIN ), 'class' => 'current all tab' )
					);
					
		foreach ( $this->sections as $k => $v ) {			
			$total += count( $this->templatic_components[$k] );
			$sections[$k] = array( 'href' => '#' . esc_attr( @$this->config->token . $k ), 'name' => $v['name'], 'class' => 'tab', 'count' => count( $this->templatic_components[$k] ) );			
		}		
		$sections['all']['count'] = $total;		
		$sections = apply_filters( @$this->config->token . '_main_section_links_array', $sections );
		
			
		
		$count = 1;
		foreach ( $sections as $k => $v ) {
			
			$count++;
			if ( $v['count'] > 0 ) 
			{				
				$html .= '<li><a href="' . $v['href'] . '"';
				if ( isset( $v['class'] ) && ( $v['class'] != '' ) ) { $html .= ' class="' . esc_attr( $v['class'] ) . '"'; }
				$html .= '>' . $v['name'] . '</a>';
				$html .= ' <span>(' . $v['count'] . ')</span>';
				if ( $count <= count( $sections ) ) { $html .= ' | '; }
				$html .= '</li>' . "\n";
			}
		}
		
		echo $html;
		do_action( @$this->config->token . '_main_get_section_links' );
	} // End get_section_links()	
		
}

remove_all_actions('templconnector_bundle_box');
add_action('tevolution_setup_steps_box','tevolution_setup_steps_box_display'); // write setup steps here

/* Check that user had created the posttype or not */
function tevolution_templatic_custom_post(){
	if(get_option('templatic_custom_post') !=''){
		return true;
	}else{
		return false;
	}
}
/* end */
/* Check that user had created submition form or not */
function tevoltion_submission_form(){
	global $wp_query;
	$args = array('post_type'=>'page',
				'meta_query' => array(
							array(
							'key' => 'is_tevolution_submit_form',
							'value' => 1,
							'compare' => '==',
							)
					));
	$data = get_posts($args);
	if(!empty($data)){
		return true;
	}else{
		return false;
	}
} 
/* check nav menu craeted or not */
function tevolution_nav_menu(){
	if(has_nav_menu('primary') || has_nav_menu('secondary')){
		return true;
	}else{
		return false;
	}
}
function tevoltion_monetization_status(){
	if(get_option('monetization') =='Active'){
		return true;
	}else{
		return false;
	}
}
/* end */
/*
 * Function Name: tevolution_extend_box
 * Return: display the extend plugins list
 */
add_action('tevolution_extend_box','tevolution_extend_box');
function tevolution_extend_box(){
	
	$buttontext =  __('Details & Purchase',ADMINDOMAIN); 
	$activate =  __('Activate',ADMINDOMAIN);
	$deactivatetext =  __('Deactivate',ADMINDOMAIN);

	// Add stand alone plugin list in transient
	delete_transient('_tevolution_standalone_plugin');
	if ( false === ( $response = get_transient( '_tevolution_standalone_plugin') ) ) {
		$response = wp_remote_get( 'http://templatic.net/api/templatic-standalone-plugin.xml', array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true		
		    )
		);	
		set_transient( '_tevolution_standalone_plugin', $response, 12 * HOUR_IN_SECONDS );				
	}
	if( is_wp_error( $response ) ) {		 
			echo '<div id="standalone_plugin_error" class="metabox-holder wrapper widgets-holder-wrap">';
			printf(__('<strong>templatic.com connect Error</strong>: %s',ADMINDOMAIN), $response->get_error_message());		
		} else {
		  $data = $response['body'];
		}	
	
		if($data){
			$doc = new DOMDocument();
			@$doc->loadXML($data);
			$sourceNode = $doc->getElementsByTagName("templatic-standalone-plugin");
		} 
		
		if($sourceNode){
			
			foreach($sourceNode as $source)
			{
				$plugin_type = $source->getElementsByTagName("plugin-type");
							
				$plugin_name = $source->getElementsByTagName("plugin-name");
				$name = $plugin_name->item(0)->nodeValue; 
				
				$plugin_folder = $source->getElementsByTagName("plugin-folder");
				$pluginfolder = $plugin_folder->item(0)->nodeValue; 
				
				$plugin_image = $source->getElementsByTagName("plugin-image");
				$image = $plugin_image->item(0)->nodeValue; 
				
				$plugin_description = $source->getElementsByTagName("plugin-description");
				$short_description = $plugin_description->item(0)->nodeValue; 
				
				$plugin_path = $source->getElementsByTagName("plugin-path");
				$filepath = $plugin_path->item(0)->nodeValue; 
				
				$plugin_download_url = $source->getElementsByTagName("plugin-download-url");
				$donwload_url = $plugin_download_url->item(0)->nodeValue; 
				
				$plugin_argument = $source->getElementsByTagName("plugin-argument");
				$add_query_arg = $plugin_argument->item(0)->nodeValue; 
				
				$plugin_price = $source->getElementsByTagName("plugin-price");
				$price = $plugin_price->item(0)->nodeValue; 
				
				$plugin_type = $source->getElementsByTagName("plugin-type");
				$type = $plugin_type->item(0)->nodeValue; 
				
				if(strstr($type,',')){
					$type = explode(',',$type);
				}else{
					$type = array($type);
				}
				$filename= get_tmpl_plugin_directory().$filepath;
				
				$theme = wp_get_theme();
				$parent_theme = $theme['Template'];
				
				if (function_exists('icl_register_string')) {			   		
					/*Plugin Description */
					icl_register_string(ADMINDOMAIN, 'plugin_description_'.$name,$short_description);
					$short_description = icl_t(ADMINDOMAIN, 'plugin_description_'.$name,$short_description);
					
					icl_register_string(ADMINDOMAIN, 'plugin_name_'.$name,$name);
					$name = icl_t(ADMINDOMAIN, 'plugin_name_'.$name,$name);
				}
				
				/* come only if directory theme start*/
				
				if(($parent_theme =='Directory' && in_array('Directory',$type)) || is_plugin_active('Tevolution-Directory/directory.php')){ 
					if(!file_exists($filename))
					{
					
					?>
					<div id="templatic_<?php echo str_replace('-','',$name);?>" class="widget_div">
						
						  <div class="inside">
							  <div class="t_module_desc"> 
								<h3 class="hndle">
								  <div class="t_dashboard_icon">
										<img class="dashboard_img" src="<?php echo $image;?>" />
								  </div>
								<span><?php echo $name; ?></span>
								</h3>
								<p class="mod_desc"><?php echo $short_description;?></p>
							  </div>
							  <div id="publishing-action" class="settings_style">
								<a href="<?php echo $donwload_url;?>" class="button-primary" target="_blank"><?php echo $buttontext; ?></a>
                                        <p class="plugin_price"><?php echo $price;?></p>
							  </div>
						  </div>
					</div>
					<?php	
					
				}else if(is_plugin_active($filepath) || !is_plugin_active($filepath))
				{
					// delete payment gateway plugin
					if((isset($_REQUEST['deactivate']) && $_REQUEST['deactivate']!='') && (isset($_REQUEST['plugin']) && $_REQUEST['plugin']!="")){
						delete_option($_REQUEST['deactivate']);
						$current_plugin = get_option( 'active_plugins' );
						foreach($current_plugin as $key=>$current){
								if($current==$_REQUEST['plugin']){
									unset($current_plugin[$key]);
								}
						}						
						sort( $current_plugin );		  
						update_option( 'active_plugins', $current_plugin );		  						
						
					}
					
					?>
                         <div id="templatic_<?php echo str_replace('-','',$name);?>" class="widget_div">
						
						  
							<div class="inside">
							<div class="t_module_desc"> 
								<h3 class="hndle">
									<div class="t_dashboard_icon">
										<img class="dashboard_img" src="<?php echo $image;?>" />
									</div>
									<span><?php echo $name; ?></span>
								</h3>
								<p class="mod_desc"><?php echo $short_description;?></p>
							</div>
                            <div id="publishing-action" class="settings_style">    
                            	<?php 						
						if(!get_option($add_query_arg)):?>                               	
                              	<a href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&tab=extend&activated=$add_query_arg&plugin=".$filepath."&true=1";?>" class="button-primary"><i class="fa fa-check"></i><?php echo $activate; ?> &rarr;</a>
                              <?php else:?>                                   
                                 <a class="button" href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&tab=extend&deactivate=$add_query_arg&plugin=".$filepath."&true=0";?>">
                                   <i class="fa fa-times"></i><?php echo $deactivatetext; ?> &rarr;</a>
                              <?php endif;?>
                             </div>
						  </div>
					</div>
				<?php
				}}else{
					if($parent_theme !='Directory' && in_array('Other',$type)){
						if(!file_exists($filename))
						{
						?>
						<div id="templatic_<?php echo str_replace('-','',$name);?>" class="widget_div">
							
							  <div class="inside">
								  <div class="t_module_desc"> 
									<h3 class="hndle">
										<div class="t_dashboard_icon">
											<img class="dashboard_img" src="<?php echo $image;?>" />
										</div>
										<span><?php echo $name; ?></span>
									</h3>
									<p class="mod_desc"><?php echo $short_description;?></p>
								  </div>
								  <div id="publishing-action" class="settings_style">
									<a href="<?php echo $donwload_url;?>" class="button-primary" target="_blank"><?php echo $buttontext; ?></a>
											<p class="plugin_price"><?php echo $price;?></p>
								  </div>
							  </div>
						</div>
						<?php	
						
					}else if(is_plugin_active($filepath) || !is_plugin_active($filepath))
					{
						// delete payment gateway plugin
						if((isset($_REQUEST['deactivate']) && $_REQUEST['deactivate']!='') && (isset($_REQUEST['plugin']) && $_REQUEST['plugin']!="")){
							delete_option($_REQUEST['deactivate']);
							$current_plugin = get_option( 'active_plugins' );
							foreach($current_plugin as $key=>$current){
									if($current==$_REQUEST['plugin']){
										unset($current_plugin[$key]);
									}
							}						
							sort( $current_plugin );		  
							update_option( 'active_plugins', $current_plugin );		  						
							
						} ?>
							 <div id="templatic_<?php echo str_replace('-','',$name);?>" class="widget_div">
								
								<div class="inside">
									<div class="t_module_desc"> 
										<h3 class="hndle">
											<div class="t_dashboard_icon">
												<img class="dashboard_img" src="<?php echo $image;?>" />
											</div>
										<span><?php echo $name; ?></span>
										</h3>
										<p class="mod_desc"><?php echo $short_description;?></p>
									</div>
									<div id="publishing-action" class="settings_style">    
										<?php if(!get_option($add_query_arg)):?>                               	
										<a href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&tab=extend&activated=$add_query_arg&plugin=".$filepath."&true=1";?>" class="button-primary"><i class="fa fa-check"></i><?php echo $activate; ?> &rarr;</a>
										<?php else:?>                                   
										<a class="button" href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&tab=extend&deactivate=$add_query_arg&plugin=".$filepath."&true=0";?>">
										   <i class="fa fa-times"></i><?php echo $deactivatetext; ?> &rarr;
										</a>
										<?php endif;?>
									 </div>
							    </div>
						</div>
					<?php
					}
				}
				}/* come only if directory theme end*/
			
					if((isset($_REQUEST['activated']) && $_REQUEST['activated']!="") &&(isset($_REQUEST['plugin']) && $_REQUEST['plugin']!=""))
					{
						$current = get_option( 'active_plugins' );
						$plugin = plugin_basename( trim($_REQUEST['plugin'] ) );	
						if ( !in_array( $plugin, $current ) ) {
						   $current[] = $plugin;
						   sort( $current );		  
						   update_option( 'active_plugins', $current );		  
						}
						update_option($_REQUEST['activated'],'Active');
						if($i==0):
						?>
                       	<script type="text/javascript">
							window.location='<?php echo "?page=templatic_system_menu&tab=extend&activated=".$_REQUEST['activated']."&true=1";?>';
						</script>                              
						<?php endif;
					}
			}
		}
}
/*
 * Function Name: tevolution_payment_gateway
 * Return: display the payment gatway plugin list
 */
add_action('tevolution_payment_gateway','tevolution_payment_gateway');
function tevolution_payment_gateway(){
	$buttontext =  __('Details & Purchase',ADMINDOMAIN); 
	$activate =  __('Activate',ADMINDOMAIN);
	$deactivatetext =  __('Deactivate',ADMINDOMAIN);
	// Add payment gateway list in transient
	if ( false === ( $response = get_transient( '_tevolution_payment_gateways') ) ) {
		$response = wp_remote_get( 'http://templatic.net/api/templatic-paymentgateways-plugin.xml', array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent' => 'WordPress/'. @$wp_version .'; '. home_url(),
			'cookies' => array()	
		    )
		);
		
		set_transient( '_tevolution_payment_gateways', $response, 12 * HOUR_IN_SECONDS );				
	}
	//finish payment gateway listing in transient
	if( is_wp_error( $response ) ) {		 
		echo '<div id="standalone_plugin_error" class="metabox-holder wrapper widgets-holder-wrap">';
		printf(__('<strong>templatic.com connect Error</strong>: %s',ADMINDOMAIN), $response->get_error_message());		
	} else {
		$data = $response['body'];
	}
		
	if($data){
		$doc = new DOMDocument();
		@$doc->loadXML($data);
		$sourceNode = $doc->getElementsByTagName("templatic-standalone-plugin");
	}
	if($sourceNode){
		foreach($sourceNode as $source)
		{
			$plugin_name = $source->getElementsByTagName("plugin-name");
			$name = $plugin_name->item(0)->nodeValue; 
			
			$plugin_folder = $source->getElementsByTagName("plugin-folder");
			$pluginfolder = $plugin_folder->item(0)->nodeValue; 
			
			$plugin_image = $source->getElementsByTagName("plugin-image");
			$image = $plugin_image->item(0)->nodeValue; 
			
			$plugin_description = $source->getElementsByTagName("plugin-description");
			$short_description = $plugin_description->item(0)->nodeValue; 
			
			$plugin_path = $source->getElementsByTagName("plugin-path");
			$filepath = $plugin_path->item(0)->nodeValue; 
			
			$plugin_download_url = $source->getElementsByTagName("plugin-download-url");
			$donwload_url = $plugin_download_url->item(0)->nodeValue; 
			
			$plugin_argument = $source->getElementsByTagName("plugin-argument");
			$add_query_arg = $plugin_argument->item(0)->nodeValue; 
			
			$plugin_price = $source->getElementsByTagName("plugin-price");
			$price = $plugin_price->item(0)->nodeValue; 
			
			$filename= get_tmpl_plugin_directory().$filepath;
			
			if (function_exists('icl_register_string')) {			   		
					/*Plugin Description */
					icl_register_string(ADMINDOMAIN, 'plugin_description_'.$name,$short_description);
					$short_description = icl_t(ADMINDOMAIN, 'plugin_description_'.$name,$short_description);
					
					icl_register_string(ADMINDOMAIN, 'plugin_name_'.$name,$name);
					$name = icl_t(ADMINDOMAIN, 'plugin_name_'.$name,$name);
			}
			
			if(!file_exists($filename))
			{
				?>
				<div id="templatic_<?php echo str_replace('-','',$name);?>" class="widget_div">
					 
					  <div class="inside">
						  <div class="t_module_desc"> 
							<h3 class="hndle">
								<div class="t_dashboard_icon">
									<img class="dashboard_img" src="<?php echo $image;?>" />
								</div>
								<span><?php echo $name; ?></span>
							</h3>
							<p class="mod_desc"><?php echo $short_description;?></p>
						  </div>
						  <div id="publishing-action" class="settings_style">
							<a href="<?php echo $donwload_url;?>" class="button-primary" target="_blank"><?php echo $buttontext; ?></a>
							<p class="plugin_price"><?php echo $price;?></p>
						  </div>
					  </div>
				</div>
				<?php	
				
			}else if(is_plugin_active($filepath) || !is_plugin_active($filepath))
			{
				// delete payment gateway plugin
				if((isset($_REQUEST['deactivate']) && $_REQUEST['deactivate']!='') && (isset($_REQUEST['plugin']) && $_REQUEST['plugin']!="")){
					delete_option($_REQUEST['deactivate']);
					$current_plugin = get_option( 'active_plugins' );
					foreach($current_plugin as $key=>$current){
							if($current==$_REQUEST['plugin']){
								unset($current_plugin[$key]);
							}
					}						
					sort( $current_plugin );		  
					update_option( 'active_plugins', $current_plugin );
				}
				?>
				<div id="templatic_<?php echo str_replace('-','',$name);?>" class="widget_div">
					 
					<div class="inside">
						<div class="t_module_desc"> 
							
							<h3 class="hndle">
								<div class="t_dashboard_icon">
									<img class="dashboard_img" src="<?php echo $image;?>" />
								</div>
								<span><?php echo $name; ?></span>
							</h3>
							<p class="mod_desc"><?php echo $short_description;?></p>
						</div>
					   <div id="publishing-action" class="settings_style">    
							<?php 						
							if(!is_plugin_active($filepath)):?>                               	
								<a href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&tab=payment-gateways&activated=$add_query_arg&plugin=".$filepath."&true=1";?>" class="button-primary"><i class="fa fa-check"></i><?php echo $activate; ?> &rarr;</a>
							<?php else:?>                                   
								<a class="button" href="<?php echo site_url()."/wp-admin/admin.php?page=templatic_system_menu&tab=payment-gateways&deactivate=$add_query_arg&plugin=".$filepath."&true=0";?>">
									<i class="fa fa-times"></i>
									<?php echo $deactivatetext; ?> &rarr;
								</a>
							<?php endif;?>
						</div>
					</div>
				</div>
				<?php
				if((isset($_REQUEST['activated']) && $_REQUEST['activated']!="") && (isset($_REQUEST['plugin']) && $_REQUEST['plugin']!=""))
				{
					$current = get_option( 'active_plugins' );
					$plugin = plugin_basename( trim($_REQUEST['plugin'] ) );	
					if ( !in_array( $plugin, $current ) ) {
					   $current[] = $plugin;
					   sort( $current );		  
					   update_option( 'active_plugins', $current );		  
					}
					update_option($_REQUEST['activated'],'Active');
					if($i==0):
					?>
					<script type="text/javascript">
						window.location='<?php echo "?page=templatic_system_menu&tab=payment-gateways&activated=".$_REQUEST['activated']."&true=1";?>';
					</script>                              
					<?php endif;
				}
			}
		}
	}
}
?>