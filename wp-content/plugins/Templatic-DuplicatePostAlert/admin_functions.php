<?php
// Call Ajax and javascript on admin header
add_action('admin_footer','include_admin_script');
/*
*@Function: include_admin_script
*@Filter: admin_head
*@Return: Print css on admin head and do ajax for checking duplicate post entry
*/
if(!function_exists('include_admin_script')){
	function include_admin_script(){
		wp_enqueue_script('jquery');
		global $post;
		$post_type = @$post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$post_type_name = @$post_type_object->labels->name;
		?>
		<style type="text/css">
			.wp-admin #warnmessage,
			#wpbody #wpbody-content .wrap #warnmessage{
				display:none;
			}
		</style>
		<script type="text/javascript">
			 jQuery.noConflict();
			 jQuery(document).ready(function(){
				jQuery("#title").change(function(){
					var title=jQuery("#title").val();
					var post_type = '<?php echo $post_type;?>';
					var post_type_name = '<?php echo $post_type_name;?>';
					jQuery.ajax({
						type:"post",
						url:"<?php echo AJAX_MANAGER_PLUGIN_URL.'ajax_title_check.php';?>",
						data:"title="+title+"&post_type="+post_type,
							success:function(data){
							if(data == 1){
								jQuery('#warnmessage').css({'display':'block'});
								jQuery('#warnmessage').html('<p><?php _e("{$post_type_name} with this title already exists.",AJAX_DOMAIN);?></p>');
								jQuery('#warnmessage').appendTo('#titlediv');
								jQuery('#warnmessage').delay(5000).fadeOut('slow');
							}
						}
					});
	 			});
			 });
		</script>
		<div id="warnmessage" class="updated inside"></div>
	<?php
	
	}
}
// Call Ajax and javascript on front end footer
add_action('wp_footer','include_front_footer_script');
/*
*@Function: include_front_footer_script
*@Filter: wp_footer
*@Return: Print css on front end footer and do ajax for checking duplicate post entry
*/
if(!function_exists('include_front_footer_script')){
	function include_front_footer_script(){
		wp_enqueue_script('jquery');
		global $post;
		$is_tevolution_submit_form = get_post_meta($post->ID,'is_tevolution_submit_form',true);
		$post_type = get_post_meta($post->ID,'submit_post_type',true);
		$post_type_object = get_post_type_object($post_type);
		if(!empty($post_type_object))
			$post_type_name = $post_type_object->labels->name;
		if($is_tevolution_submit_form==1){
		?>
			<style type="text/css">
                    #submit_form #post_title_error p{
                         margin-left: 20px;
                         padding-top: 3px;
                    }
               </style>
               <script type="text/javascript">
                     jQuery.noConflict();
                     jQuery(document).ready(function(){
                         jQuery("#post_title").blur(function(){
                              jQuery('#post_title_error').css({'background':'none','display':'block'});
                         });
                         jQuery("#post_title").blur(function(){
                              var title=jQuery("#post_title").val();
							  var post_type = '<?php echo $post_type;?>';
                              var post_type_name = '<?php echo $post_type_name;?>';
                              jQuery.ajax({
                                   type:"post",
                                   url:"<?php echo AJAX_MANAGER_PLUGIN_URL.'ajax_title_check.php';?>",
                                   data:"title="+title+"&post_type="+post_type,
                                        success:function(data){
                                        if(data == 1){
                                             jQuery('#post_title_error').css({'background':'none'});
                                             jQuery('#post_title_error').css({'color':'red'});
                                             jQuery('#post_title_error').html('<?php _e("{$post_type_name} with this title already exists.",AJAX_DOMAIN);?>');
                                        }
                                   }
                              });
                         });
                     });
               </script>
	<?php
		}
	
	}
}?>