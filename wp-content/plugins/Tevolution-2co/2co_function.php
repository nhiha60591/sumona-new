<?php
function templ_add_method_install_2co(){
	update_option('2co_plugin_active', 'true');
	require_once(TEMPL_FILE_PATH_2co .'/includes/install.php');
}
function templ_add_method_deactivate_2co(){	
	delete_option('2co_plugin_active');
	global $wpdb;
	$paymentmethodname = '2co';
	$paymethodinfo = array(
					"name" 		=> '2co',
					"key" 		=> $paymentmethodname,
					"isactive"	=>	'1', // 1->display,0->hide
					"display_order"=>'21',
					"payOpts"	=>	$payOpts,
					);
	$paymentsql = "select * from $wpdb->options where option_name like 'payment_method_".$paymethodinfo['key']."' order by option_id asc";
	$paymentinfo = $wpdb->get_results($paymentsql);
	$wpdb->query("DELETE FROM $wpdb->options where option_name like 'payment_method_".$paymethodinfo['key']."'");
}

add_action('2co_successfull_return_content','successfull_return_2co_content',10,2);
function successfull_return_2co_content($orderid,$content)
{
	echo $content;	
}
/*
 * Add Action '2co_submit_post_details' for display the submited post details
 * Function Name: successfull_return_2co_post_details
 */
add_action('2co_submit_post_details','successfull_return_2co_post_details');
function successfull_return_2co_post_details($post_id)
{
	$_REQUEST['pid']=(isset($_REQUEST['pid']) && $_REQUEST['pid']!='')?$_REQUEST['pid']:$post_id;
	?>
     <!-- Short Detail of post -->
	<div class="title-container">
		<h1><?php echo POST_DETAIL;?></h1>
	</div>
    <div class="submited_info">
	<?php
	global $wpdb,$post;
	remove_all_actions('posts_where');
	$cus_post_type = get_post_type($_REQUEST['pid']);
	$args = 
	array( 'post_type' => 'custom_fields',
	'posts_per_page' => -1	,
	'post_status' => array('publish'),
	'meta_query' => array(
	   'relation' => 'AND',
		array(
			'key' => 'post_type_'.$cus_post_type.'',
			'value' => $cus_post_type,
			'compare' => '=',
			'type'=> 'text'
		),
		array(
			'key' => 'show_on_page',
			'value' =>  array('user_side','both_side'),
			'compare' => 'IN'
		),
		array(
			'key' => 'is_active',
			'value' =>  '1',
			'compare' => '='
		),
		array(
			'key' => 'show_on_success',
			'value' =>  '1',
			'compare' => '='
		)
	),
		'meta_key' => 'sort_order',
		'orderby' => 'meta_value',
		'order' => 'ASC'
	);
	$post_query = null;
	$post_meta_info = new WP_Query($args);
	$suc_post = get_post($_REQUEST['pid']);
		if($post_meta_info)
		  {
			echo "<div class='grid02 rc_rightcol'>";
			echo "<ul class='list'>";
			echo "<li><p>Post Title : </p> <p> ".$suc_post->post_title."</p></li>";
			while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
				
				if(get_post_meta($_REQUEST['pid'],$post->post_name,true))
					  {
						if(get_post_meta($post->ID,"ctype",true) == 'multicheckbox')
						  {
							foreach(get_post_meta($_REQUEST['pid'],$post->post_name,true) as $value)
							 {
								$_value .= $value.",";
							 }
							 echo "<li><p>".$post->post_title." : </p> <p> ".substr($_value,0,-1)."</p></li>";
						  }
						else
						 {
							 $custom_field=get_post_meta($_REQUEST['pid'],$post->post_name,true);
							 if(substr($custom_field, -4 ) == '.jpg' || substr($custom_field, -4 ) == '.png' || substr($custom_field, -4 ) == '.gif' || substr($custom_field, -4 ) == '.JPG' 
											|| substr($custom_field, -4 ) == '.PNG' || substr($custom_field, -4 ) == '.GIF'){
								  echo "<li><p>".$post->post_title." : </p> <p> <img src='".$custom_field."' /></p></li>";
							 }							 
							 else
							 echo "<li><p>".$post->post_title." : </p> <p> ".get_post_meta($_REQUEST['pid'],$post->post_name,true)."</p></li>";
						 }
					  }
					if($post->post_name == 'post_content')
					 {
						$suc_post_con = $suc_post->post_content;
					 }
					if($post->post_name == 'post_excerpt')
					 {
						$suc_post_excerpt = $suc_post->post_excerpt;
					 }

					if(get_post_meta($post->ID,"ctype",true) == 'geo_map')
					 {
						$add_str = get_post_meta($_REQUEST['pid'],'address',true);
						$geo_latitude = get_post_meta($_REQUEST['pid'],'geo_latitude',true);
						$geo_longitude = get_post_meta($_REQUEST['pid'],'geo_longitude',true);
						$map_view = get_post_meta($_REQUEST['pid'],'map_view',true);
					 }
  
			endwhile;
			if(is_active_addons('monetization')){
				fetch_payment_description($_REQUEST['pid']);
			}
		  }		 
		
		?>
		</div>
		<?php if(isset($suc_post_con)): ?>
		    <div class="row">
			  <div class="twelve columns">
				  <div class="title_space">
					 <div class="title-container">
						<h1 class="title_green"><span><?php _e('Post Description');?></span></h1>
						<div class="clearfix"></div>
					 </div>
					 <p><?php echo nl2br($suc_post_con); ?></p>
				  </div>
			   </div>
		    </div>
		<?php endif; ?>
		
		<?php if(isset($suc_post_excerpt)): ?>
			 <div class="row">
				<div class="twelve columns">
					<div class="title_space">
						<div class="title-container">
							<h1 class="title_green"><span><?php _e('Post Excerpt');?></span></h1>
							<div class="clearfix"></div>
						</div>
						<p><?php echo nl2br($suc_post_excerpt); ?></p>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
		<?php
		if(@$add_str)
		{
		?>
			<div class="row">
				<div class="title_space">
					<div class="title-container">
						<h1 class="title_green"><span><?php _e('Map'); ?></span></h1>
						<div class="clearfix"></div>
					</div>
					<p><strong><?php _e('Location : '); echo $add_str;?></strong></p>
				</div>
				<div id="gmap" class="graybox img-pad">
					<?php if($geo_longitude &&  $geo_latitude):
							$pimgarr = bdw_get_images_plugin($_REQUEST['pid'],'thumb',1);
							$pimg = $pimgarr[0];
							if(!$pimg):
								$pimg = plugin_dir_url( __FILE__ )."images/img_not_available.png";
							endif;	
							$title = $suc_post->post_title;
							$address = $add_str;
							require_once (TEMPL_MONETIZE_FOLDER_PATH . 'templatic-custom_fields/preview_map.php');
							$retstr ="";
							$retstr .= "<div class=\"forrent\"><img src=\"$pimg\" width=\"192\" height=\"134\" alt=\"\" />";
							$retstr .= "<h6><a href=\"\" class=\"ptitle\" style=\"color:#444444;font-size:14px;\"><span>$title</span></a></h6>";
							if($address){$retstr .= "<span style=\"font-size:10px;\">$address</span>";}
							$retstr .= "</div>";
							preview_address_google_map_plugin($geo_latitude,$geo_longitude,$retstr,$map_view);
						  else:
					?>
							<iframe src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=<?php echo $add_str;?>&amp;ie=UTF8&amp;z=14&amp;iwloc=A&amp;output=embed" height="358" width="100%" scrolling="no" frameborder="0" ></iframe>
					<?php endif; ?>
				</div>
			</div>
		<?php } ?>
		
		
		<!-- End Short Detail of post -->
     <?php
}
?>