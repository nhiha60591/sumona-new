<?php
if (!class_exists('monetization')) {
class monetization
{
	/* NAME : INSERT PACKAGE DATA
	DESCRIPTION : THIS FUNCTION INSERTS PACKAGE DATA INTO POSTMETA TABLE CREATING A POST WITH POST TYPE PACKAGE */
	function insert_package_data($post_details)
	{
		global $last_postid,$wpdb;
		$package_name = $post_details['package_name'];
		$package_desc = $post_details['package_desc'];
		$package_type = $post_details['package_type'];
		$package_post_type = $post_details['package_post_type'];
		$package_post_type = implode(',',$post_details['package_post_type']);		
		
		$custom_taxonomy = get_option('templatic_custom_taxonomy',true);
		$custm_category_type = array_keys($custom_taxonomy);
		$post_category = array('category');
		$package_taxonomy_type = array_merge($custm_category_type,$post_category);
		
	
		$package_categories = implode(',',$post_details['category']);
		$package_post = array(
			'post_title' 	=> $package_name,
			'post_content'  => $package_desc,
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => 'monetization_package' );			
		/* CREATING A POST OBJECT AND INSERT THE POST INTO THE DATABAE */
		if($_REQUEST['package_id'])
		{
			$package_id = $_REQUEST['package_id'];
			$package_post['ID'] = $_REQUEST['package_id'];
			$last_postid = wp_insert_post( $package_post );
			if (function_exists('icl_register_string')) {									
				icl_register_string('tevolution-price', 'package-name'.$last_postid,$package_name);
				icl_register_string('tevolution-price', 'package-desc'.$last_postid,$package_desc);			
			}
			/* Finish the place geo_latitude and geo_longitude in postcodes table*/
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				if(function_exists('wpml_insert_templ_post'))
					wpml_insert_templ_post($last_postid,'monetization_package'); /* insert post in language */
			}		
			
				foreach($package_taxonomy_type as $key=> $_tax)
				{
					wp_delete_object_term_relationships( $last_postid, $_tax ); 
					foreach($_POST['category'] as $category)
					 {	
						global $wpdb;					
						$taxonomy = get_term_by('id',$category,$_tax);
						if($taxonomy ){
							wp_set_post_terms($last_postid,$category,$_tax,true); 
						}
					 }
				}

		
			$msg_type = 'edit';
		}
		else
		{
			$last_postid = wp_insert_post( $package_post );
			if (function_exists('icl_register_string')) {									
				icl_register_string('tevolution-price', 'package-name'.$last_postid,$package_name);
				icl_register_string('tevolution-price', 'package-desc'.$last_postid,$package_desc);			
			}
			/* Finish the place geo_latitude and geo_longitude in postcodes table*/
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				if(function_exists('wpml_insert_templ_post'))
					wpml_insert_templ_post($last_postid,'monetization_package'); /* insert post in language */
			}
			if($package_post_type == 'all')
			{
				foreach($package_taxonomy_type as $key=> $_tax)
				{
					foreach($_POST['category'] as $category)
					{
						$package_taxonomy_type=$wpdb->get_var("select taxonomy from $wpdb->term_taxonomy where term_id=".$category);
						wp_set_post_terms($last_postid,$category,$_tax,true);
					}
				}
			}
			else
			{
				foreach($_POST['category'] as $category)
				{
					$package_taxonomy_type=$wpdb->get_var("select taxonomy from $wpdb->term_taxonomy where term_id=".$category);
					wp_set_post_terms($last_postid,$category,$package_taxonomy_type,true);
				}
			}
			$msg_type = 'add';
		}
		/* INSERT THE PACKAGE DATA INTO THE POSTMETA TABLE */
		$show_package = $post_details['show_package'];
		$package_amount = $post_details['package_amount'];
		$package_validity = $post_details['validity'];
		$package_validity_per = $post_details['validity_per'];
		$package_status = $post_details['package_status'];
		$package_is_recurring = ($post_details['recurring'] ==1) ? 1 : 0;
		$package_billing_num = $post_details['billing_num'];
		$package_billing_per = $post_details['billing_per'];
		$package_billing_cycle = $post_details['billing_cycle'];
		
		$subscription_as_pay_post = $post_details['subscription_as_pay_post'];		
		$is_home_page_featured = $post_details['is_home_page_featured'];
		$is_category_page_featured = $post_details['is_category_page_featured'];
		$package_is_home_featured = $post_details['is_home_featured'];
		$package_is_category_featured = $post_details['is_category_featured'];
		$package_feature_amount = $post_details['feature_amount'];
		$package_feature_cat_amount = $post_details['feature_cat_amount'];
		$package_home_page_feature_alive_days = $post_details['home_page_alive_days'];
		$package_cat_page_feature_alive_days = $post_details['cat_page_alive_days'];
		$subscription_days_free_trail = $post_details['subscription_days_free_trail'];
		$days_for_no_post = $post_details['days_for_no_post'];
		
		$limit_no_post = $post_details['limit_no_post'];
		$first_free_trail_period = $post_details['first_free_trail_period'];
		$custom = array('package_type' => $package_type,
						'package_post_type' => $package_post_type,
						'subscription_as_pay_post'=>$subscription_as_pay_post,
						'category' => $package_categories,
						'show_package' => $show_package,
						'package_amount' => $package_amount,
						'validity' => $package_validity,
						'validity_per' => $package_validity_per,
						'package_status' => $package_status,
						'recurring' => $package_is_recurring,
						'billing_num' => $package_billing_num,
						'billing_per' => $package_billing_per,
						'billing_cycle' => $package_billing_cycle,
						'first_free_trail_period' => $first_free_trail_period,
						'is_home_page_featured' => $is_home_page_featured,
						'is_category_page_featured' => $is_category_page_featured,
						'is_home_featured' => $package_is_home_featured,
						'is_category_featured' => $package_is_category_featured,
						'feature_amount' => $package_feature_amount,
						'feature_cat_amount' => $package_feature_cat_amount,
						'limit_no_post'=>$limit_no_post,
						'home_page_alive_days'=>$package_home_page_feature_alive_days,
						'cat_page_alive_days'=> $package_cat_page_feature_alive_days,
						'subscription_days_free_trail'=>$subscription_days_free_trail,
						'days_for_no_post'=>$days_for_no_post
						);
		$custom=apply_filters('insert_package_data',$custom);
		foreach($custom as $key=>$val)
		{				
			update_post_meta($last_postid, $key, $val);
		}
		if(is_plugin_active('thoughtful-comments/fv-thoughtful-comments.php')){
			update_post_meta($last_postid, 'can_author_mederate', $post_details['can_author_mederate']);
			update_post_meta($last_postid, 'comment_mederation_amount', $post_details['comment_mederation_amount']);
		}		
		do_action('save_price_package');
		$url = site_url().'/wp-admin/admin.php?page=monetization';
		echo '<form action="'.$url.'" method="get" id="frm_edit_package" name="frm_edit_package">
					<input type="hidden" value="monetization" name="page"><input type="hidden" value="success" name="package_msg"><input type="hidden" value="'.$msg_type.'" name="package_msg_type">
					<input type="hidden" value="packages" name="tab">
			  </form>
			  <script>document.frm_edit_package.submit();</script>';
			  exit;
	}
	/* EOF - INSERT PACKAGE DATA INTO THE DATABASE */
	/* EOF - DELETE PACKAGE DATA */
	/*
	Name :fetch_monetization_packages_back_end
	Description : To display the feature details of the price packages  in backed
	*/
	function fetch_monetization_packages_back_end($pkg_id,$div_id,$post_type,$taxonomy_slug,$post_cat)
	{
		global $post,$wpdb,$current_user;
		$edit_id = $post->ID;
		echo "<input type='hidden' id='submit_post_type' name='submit_post_type' value='".$_REQUEST['post_type']."'>";
		echo "<input type='hidden' id='cur_post_type' name='cur_post_type' value='".$_REQUEST['post_type']."'>";
		echo "<input type='hidden' id='submit_page_id' name='submit_page_id' value='".$post->ID."'>";
		echo "<input type='hidden' id='total_price' name='total_price' >";
		global $wpdb, $wp_query,$post,$packages_post;
		$packages_post=$post;
		if(!is_plugin_active( 'Tevolution-FieldsMonetization/fields_monetization.php')) {
				if($div_id=='ajax_packages_checkbox'){
					$post_cat='1,'.$post_cat;
					$pargs = array('post_type' => 'monetization_package',
							'posts_per_page' => -1,
							'post_status' => array('publish'),
							'tax_query' => array('relation' => 'OR', array('taxonomy' => $taxonomy_slug,'field' => 'id','terms' => explode(',',$post_cat),'operator'  => 'IN'),array('taxonomy' => 'category','field' => 'id','terms' => 1,'operator'  => 'IN') ),
							'meta_query' => array('relation' => 'AND',
											  array('key' => 'package_post_type',
												   'value' => $post_type,'all',
												   'compare' => 'LIKE'
												   ,'type'=> 'text'),
											  array('key' => 'package_status',
												   'value' =>  '1',
												   'compare' => '=')
									),
							'orderby' => 'menu_order',
							'order' => 'ASC'
						);
				}elseif($post_cat!=''){						
						$pargs = array('post_type' => 'monetization_package',
							'posts_per_page' => -1,
							'post_status' => array('publish'),
							'tax_query' => array('relation' => 'OR', array('taxonomy' => $taxonomy_slug,'field' => 'id','terms' => explode(',',$post_cat),'operator'  => 'IN'),array('taxonomy' => 'category','field' => 'id','terms' => 1,'operator'  => 'IN') ),
							'meta_query' => array('relation' => 'AND',
											  array('key' => 'package_post_type',
												   'value' => $post_type,'all',
												   'compare' => 'LIKE'
												   ,'type'=> 'text'),
											  array('key' => 'package_status',
												   'value' =>  '1',
												   'compare' => '=')
									),
							'orderby' => 'menu_order',
							'order' => 'ASC'
						);
				}else{
					$pargs = array('post_type' => 'monetization_package',
							'posts_per_page' => -1,
							'post_status' => array('publish'),
							
							'meta_query' => array('relation' => 'AND',
											  array('key' => 'package_post_type',
												   'value' => $post_type,'all',
												   'compare' => 'LIKE'
												   ,'type'=> 'text'),
											  array('key' => 'package_status',
												   'value' =>  '1',
												   'compare' => '=')
									),
							'orderby' => 'menu_order',
							'order' => 'ASC'
						);
				}
			}
			else
			{
				$pargs = array('post_type' => 'monetization_package',
					'posts_per_page' => -1,
					'post_status' => array('publish'),
 				     'meta_query' => array('relation' => 'AND',
									  array('key' => 'package_post_type',
										   'value' => $post_type,'all',
										   'compare' => 'LIKE'
										   ,'type'=> 'text'),
									  array('key' => 'package_status',
										   'value' =>  '1',
										   'compare' => '=')
							),
				     'orderby' => 'menu_order',
			          'order' => 'ASC'
				);
			}
			wp_reset_query();
			$package_query = null;			
			$package_query = new WP_Query($pargs);		
			if($pkg_id !=''){
				$selected_pkg = $pkg_id;
			}
			?>
             <input type="hidden" name="package_free_submission" id="package_free_submission">
             <input type="hidden" name="package_select" id="pkg_id" value="<?php echo get_post_meta($post->ID,'package_select',true); ?>">
			<input type="hidden" name="pkg_type" id="pkg_type">
            <div id="plan" class="content step-plan active clearfix">
            <?php
			while($package_query->have_posts())
			{ 
				$package_query->the_post();				
				$package_type = get_post_meta(get_the_ID(),'package_type',true);
				$package_post_type = get_post_meta(get_the_ID(),'package_post_type',true);
				$package_categories = get_post_meta(get_the_ID(),'category',true);
				$show_package = get_post_meta(get_the_ID(),'show_package',true);
				$package_amount = get_post_meta(get_the_ID(),'package_amount',true);
				$recurring = get_post_meta(@$post_id,'recurring',true);
				if($package_type == 2 && $recurring == 1){
					$package_validity = get_post_meta(get_the_ID(),'billing_num',true);
					$package_validity_per =get_post_meta(get_the_ID(),'billing_per',true);
				}else{
					$package_validity = get_post_meta(get_the_ID(),'validity',true);
					$package_validity_per = get_post_meta(get_the_ID(),'validity_per',true);
				}
				$package_status = get_post_meta(get_the_ID(),'package_status',true);
				$recurring = get_post_meta(get_the_ID(),'recurring',true);
				$billing_num = get_post_meta(get_the_ID(),'billing_num',true);
				$billing_per = get_post_meta(get_the_ID(),'billing_per',true);
				$billing_cycle = get_post_meta(get_the_ID(),'billing_cycle',true);
				$is_featured = get_post_meta(get_the_ID(),'is_featured',true);
				$feature_amount_home = get_post_meta(get_the_ID(),'feature_amount',true);
				$feature_cat_amount = get_post_meta(get_the_ID(),'feature_cat_amount',true);  
				$featured_h = get_post_meta(get_the_ID(),'home_featured_type',true); 
				$featured_c = get_post_meta(get_the_ID(),'featured_type',true);
				$package_is_recurring = get_post_meta(get_the_ID(),'recurring',true);
				$package_billing_num = get_post_meta(get_the_ID(),'billing_num',true);
				$package_billing_per =get_post_meta(get_the_ID(),'billing_per',true);
				$package_billing_cycle =get_post_meta(get_the_ID(),'billing_cycle',true);
				
					if(isset($category_id)){ $catid = $category_id; }else{ $catid =''; }
					if(isset($cat_array) && $cat_array != "")
					{
						$catid = $cat_array;
					}
					else
					{
						if(isset($_REQUEST['category'])){
						$catid = $_REQUEST['category'];
						}else{ $catid =''; }
					}
					tmpl_display_package_html($post,$post_type);
					?>
					
				<!-- DISPLAY THE PACKAGE IN FRONT END -->	
					
		<?php 
			}
			?>
            </div>
            <?php
			global $monetization;
				if(class_exists('monetization')){
					if(isset($edit_id) && $edit_id !='' )
					{
						if(get_post_meta($edit_id,'package_select',true)){
							$packg_id = get_post_meta($edit_id,'package_select',true);
						}
						else{
							$packg_id = get_user_meta($current_user->ID,$post_type.'_package_select',true);
						}
						echo '<div id="show_featured_option">';
							$monetization->tmpl_fetch_price_package_featured_option($current_user->ID,$post_type,$edit_id,$packg_id,$is_user_select_subscription_pkg);
						echo '</div>';
					}
					else
					{
					?>
						<div style="display:none;" id="show_featured_option">
							<input type="checkbox" value="" id="featured_h" name="featured_h">
							<input type="checkbox" value="" id="featured_c" name="featured_c">
						</div>
					<?php
					}
				}
		wp_reset_query();
		wp_reset_postdata();
		$post=$packages_post;			
	}
	/*
	Name :fetch_package_feature_details_backend
	Description : To display the feature details of the price packages 
	*/
	function fetch_package_feature_details_backend($edit_id='',$png_id='',$all_cat_id){	
		/* set feature price when Go back and edit */
		if(isset($edit_id) && $edit_id !=''){
			$price_select =  get_post_meta($edit_id,'package_select',true); /* selected package */
			$is_featured = get_post_meta($price_select,'is_featured',true); // package is featured or not 
			if($is_featured ==1){
				$featured_h = get_post_meta($price_select,'feature_amount',true); //
				$featured_c = get_post_meta($price_select,'feature_cat_amount',true); //
				$is_featured_h = get_post_meta($edit_id,'featured_h',true); //
				$is_featured_c = get_post_meta($edit_id,'featured_c',true); //
				$featured_type = get_post_meta($edit_id,'featured_type',true); //
			}		
		}else{
			$featured_h =0;
			$featured_c =0;
		}	
		?>
			<!-- FETCH FEATURED POST PRICES IN BACK END -->
            <?php global $post; 
		  	$post_type = (get_post_meta($post->ID,'template_post_type',true)!="")? get_post_meta($post->ID,'template_post_type',true):get_post_meta($post->ID,'submit_post_type',true); ?>
			<div class="form_row clearfix is_backend_featured" id="show_featured_option">
				<label><strong><?php _e('Would you like to make this ',ADMINDOMAIN).$post_type; _e('featured?',ADMINDOMAIN); ?></strong></label>
				<div class="feature_label">
					<label><input type="checkbox" name="featured_h" id="featured_h" value="<?php echo $featured_h; ?>" onclick="featured_list(this.id)" <?php if(@$is_featured_h !="" && $is_featured_h =="h"){ echo "checked=checked"; } ?>/><?php _e(FEATURED_H,ADMINDOMAIN); ?> <span id="ftrhome"><?php if(isset($featured_h) && $featured_h !=""){ echo "(".fetch_currency_with_position($featured_h).")"; }else{ echo "(".fetch_currency_with_position('0').")"; } ?></span></label>
					<label><input type="checkbox" name="featured_c" id="featured_c" value="0" onclick="featured_list(this.id)" <?php if(@$is_featured_c !="" && $is_featured_c =="c"){ echo "checked=checked"; } ?>/><?php _e(FEATURED_C,ADMINDOMAIN); ?><span id="ftrcat"><?php if(isset($featured_c) && $featured_c !=""){ echo "(".fetch_currency_with_position($featured_c).")"; }else{ echo "(".fetch_currency_with_position('0').")"; } ?></span></label>
					<?php
						if(is_plugin_active('thoughtful-comments/fv-thoughtful-comments.php')){
							$author_moderate = get_post_meta($edit_id,'author_moderate',true);
							$comment_mederation_amount = get_post_meta($price_select,'comment_mederation_amount',true); //
						?>
							<label><input type="checkbox" name="author_can_moderate_comment" id="author_can_moderate_comment" value="0" onclick="featured_list(this.id)" <?php if(@$author_moderate !="" && $author_moderate =="1"){ echo "checked=checked"; } ?>/><?php _e(MODERATE_COMMENT,DOMAIN); ?><span id="ftrcomnt"><?php if(isset($author_moderate) && $author_moderate =="1"){ echo "(".fetch_currency_with_position($comment_mederation_amount).")"; }else{ echo "(".fetch_currency_with_position('0').")"; } ?></span></label>
							<input type="hidden" name="author_moderate" id="author_moderate" value="0"/>
						<?php	
						}
					?>
					<input type="hidden" name="featured_type" id="featured_type" value="<?php echo ($featured_type)? $featured_type : 'none'?>"/>
					<span id='process' style='display:none;'><i class="fa fa-circle-o-notch fa-spin"></i></span>
					
				</div>
				<?php				
					$msg_note = sprintf(__("An additional amount will be charged to make this %s featured. You have the option to feature your %s on home page or category page or both.",ADMINDOMAIN),$post_type,$post_type);
					if(function_exists('icl_register_string')){
						icl_register_string(ADMINDOMAIN,$msg_note,$msg_note);
					}
					
					if(function_exists('icl_t')){
						$msg_note1 = icl_t(ADMINDOMAIN,$msg_note,$msg_note);
					}else{
						$msg_note1 = __($msg_note,ADMINDOMAIN); 
					}
				?>
				<span class="message_note"><?php _e($msg_note1,ADMINDOMAIN);?></span>
				<span id="category_span" class="message_error2"></span>
			</div>
			<!-- END - FETCH FEATURED POST PRICE -->
                    <span id="cat_price" style="display:none;"></span>
                    <span id="pkg_price" style="display:none;"></span>
                    <span id="feture_price" style="display:none;"></span>
                    <span id="result_price" style="display:none;">                              
			
			</div>
	<?php
	}
	/* NAME : FETCH PACKAGE IN FRONT END
	DESCRIPTION : THIS FUNCTION WILL FETCH ALL THE PACKAGES IN FRONT END */
	function fetch_monetization_packages_front_end($pkg_id,$div_id,$post_type,$taxonomy_slug,$post_cat)
	{
		global $wpdb,$post;
		$post_fcategories = explode(',',$post_cat);

		/* FETCH ALL THE POSTS WITH POST TYPE PACKAGE */
		if($div_id != 'ajax_packages_checkbox'){ $class ='form_row_pkg clearfix'; }
		$package_ids = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'monetization_package' AND post_status = 'publish'");
			if($div_id !='all_packages'){ /* this query will execute only for category wise packages */
				$pargs = array('post_type' => 'monetization_package','posts_per_page' => -1	,'post_status' => array('publish'),
					  'meta_query' => array('relation' => 'AND',array('key' => 'package_post_type','value' => $post_type,'compare' => 'LIKE','type'=> 'text'),array('key' => 'show_package','value' =>  array(''),'compare' => 'IN','type'=> 'text'),array('key' => 'package_status','value' =>  '1','compare' => '=')),
					  'tax_query' => array( array('taxonomy' => $taxonomy_slug,'field' => 'id','terms' => $post_fcategories,'include_children'=>false,'operator'  => 'IN') ),
				'orderby' => 'menu_order',
				'order' => 'ASC'
				);
			}else{ /* this query will execute for all package need to show even no category selected */
				$pargs = array('post_type' => 'monetization_package','posts_per_page' => -1	,'post_status' => array('publish'),
					  'meta_query' => array('relation' => 'AND',array('key' => 'package_post_type','value' =>$post_type,'compare' => 'LIKE','type'=> 'text'),array('key' => 'package_status','value' =>  '1','compare' => '=')),
				'orderby' => 'menu_order',
				'order' => 'ASC'
				);
			}
			
			wp_reset_query();
			$package_query = null;
			
			/* do action for add any query or filter before wp_query */
			do_action('price_package_before_query');
			
			$package_query = new WP_Query($pargs);

			/* do action for add any query or filter after wp_query */
			do_action('price_package_after_query');
			
			if($div_id =='all_packages'){
			/* display this fields only when no deiv ID argument pass from funnction, so the intention is to display this fields only once */
			if(isset($_REQUEST['backandedit']) && $_REQUEST['backandedit'] !=''){
				$cat_price = $_SESSION['custom_fields']['all_cat_price'];
				}else{ $cat_price =''; }
				if(isset($_REQUEST['category']) && $_REQUEST['category'] != ""){
					$cats_of =  count($_REQUEST['category']); 
				}
				else
				{ $cats_of = "";}
			 ?>
			<input type="hidden" name="all_cat" id="all_cat" value="0"/>
            <?php 
			$tmpdata = get_option('templatic_settings');
			if(isset($tmpdata['templatic-category_type']) && $tmpdata['templatic-category_type'] == 'select'):
			?>	
			<input type="hidden" name="all_cat_price" id="all_cat_price" value="<?php if(isset($_REQUEST['category']) && $_REQUEST['category'] !=""){ if(is_array($_REQUEST['category']) && $cats_of >0){ $cat = explode(",",$_REQUEST['category'][0]); echo $cat[1]; }else{ echo $_REQUEST['category'];  }  }else{ if(isset($cat_price) && $cat_price !=''){ echo $cat_price; }else{ echo "0"; } }  ?>"/>
            <?php else: ?>
            <input type="hidden" name="all_cat_price" id="all_cat_price" value="<?php if(isset($_REQUEST['category']) && $_REQUEST['category'] !=""){ echo $this->templ_fetch_category_price(@$_REQUEST['category']);  }else{ if(isset($cat_price) && $cat_price !=''){ echo $cat_price; }else{ echo "0"; } }  ?>"/>
            <?php endif;
			} ?>
			
			<div id="<?php echo $div_id; ?>" class="<?php echo $class; ?>">
			<?php
			
			if( $package_query->have_posts() && (!isset($_REQUEST['action']) && @$_REQUEST['action'] !='edit'))
			{
				?>
                <input type="hidden" name="pkg_id" id="pkg_id">
                <input type="hidden" name="pkg_type" id="pkg_type">
                <input type="hidden" name="package_free_submission" id="package_free_submission">
                <input type="hidden" name="upgrade" id="upgrade">
                <div class="clearfix" id="plan" >
                <?php
				if($div_id =='all_packages'){ ?>
					<div class="sec_title"><h3 id="package_data"><?php _e('Select a Package',DOMAIN); ?></h3></div>
					<span class="message_error2" id="all_packages_error"></span>
				<?php }		
			/* FETCH ALL THE PACKAGE DATA FROM POST META TABLE */
			$selected_pkg = $_SESSION['custom_fields']['package_select'];
			if($pkg_id !=''){
				$selected_pkg = $pkg_id;
			}		
			while($package_query->have_posts())
			{
				$package_query->the_post();
				if(isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg']==1 && $pkg_id==$post->ID){
					continue;
				}
				$disply_price_package=apply_filters('tevolution_price_package_loop_frontend','1',$post,$post_type);
				if($disply_price_package==''){
					continue;	
				}
				
				tmpl_display_package_html($post,$post_type);	
			} 
			echo '</div>';
		} ?>    		 
    </div>        
        
	<?php
	}
	/*
	Name : templ_fetch_category_price
	Desc : calculate pricing as per category selection
	*/
	function templ_fetch_category_price($category_id){
		if(isset($category_id))
			foreach($category_id as $_category_arr)
			{
			$category[] = explode(",",$_category_arr);
			}
		if(isset($category))
			foreach($category as $_category){
				$arr_category[] = $_category[0];
				$arr_category_price[] = $_category[1];
			}
			
		return $cat_price = @array_sum($arr_category_price);	
	}
	
	/*
	Name : templ_get_selected_category_id
	Desc : get selected category ID
	*/
	function templ_get_selected_category_id($category_id){
		if(isset($category_id))
			foreach($category_id as $_category_arr)
			{
				$category[] = explode(",",$_category_arr);
			}
		if(isset($category))
			foreach($category as $_category){
				$arr_category[] = $_category[0];
				$arr_category_price[] = $_category[1];
			}
			
		return $cat_array = $arr_category;	
	}
	/*
	Name : templ_total_selected_cats_price
	Desc : get selected category ID
	*/
	function templ_total_selected_cats_price($category_id){
		global $wpdb;
		if(!empty($category_id)){
			$cat_price = $wpdb->get_var("select sum(t.term_price) from $wpdb->terms t ,$wpdb->term_taxonomy tt where t.term_id = tt.term_taxonomy_id and tt.term_taxonomy_id in($category_id)");
			return $cat_price;	
		}
	}
	
	
	
	/*
	Name :fetch_package_feature_details
	Description : To display the feature details of the price packages 
	*/
	function fetch_package_feature_details($edit_id='',$png_id='',$all_cat_id){}
	/* EOF - FETCH featured DATA */
	/*
	Name : templ_get_price_info
	Argument : pkg_id - selected price package id, total price for listing going to submit.
	Desc : return selected price package information.
	*/
	function templ_get_price_info($pkg_id='',$price='')
	{ 
		global $wpdb,$recurring,$billing_num,$billing_per,$billing_cycle;
		
		
		if($pkg_id !="")
		{
			$subsql = " and p.ID =\"$pkg_id\"";	
		}
		
		wp_reset_query();
		$post = get_post($pkg_id); 
		
		if($post)
		{
			$info = array();
			$recurring = get_post_meta($post->ID,'recurring',true);
			if($recurring ==1){
			$validity = get_post_meta($post->ID,'billing_num',true);
			$vper = get_post_meta($post->ID,'billing_per',true);
			}else{
			$vper = get_post_meta($post->ID,'validity_per',true);
			$validity = get_post_meta($post->ID,'validity',true);
			}
			$cats = get_post_meta($post->ID,'category',true);
			$is_featured = get_post_meta($post->ID,'is_featured',true);
			
			$billing_num = get_post_meta($post->ID,'billing_num',true);
			$billing_per = get_post_meta($post->ID,'billing_per',true);
			$billing_cycle = get_post_meta($post->ID,'billing_cycle',true);
			if(($validity != "" || $validity != 0))
			{
				if($vper == 'M')
				{
					$tvalidity = $validity*30 ;
				}else if($vper == 'Y'){
					$tvalidity = $validity*365 ;
				}else{
					$tvalidity = $validity ;
				}
			}
			$info['title'] = $post->post_title;
			$info['package_type']=get_post_meta($post->ID,'package_type',true);
			$info['price'] = get_post_meta($post->ID,'package_amount',true);
			$info['days'] = @$tvalidity;
			$info['alive_days'] = @$tvalidity;
			$info['cat'] = $cats;
			$info['subscription_as_pay_post'] = get_post_meta($post->ID,'subscription_as_pay_post',true);
			$info['is_featured'] = $is_featured;
			
			/*Get the price package featured option */
			$info['is_home_page_featured'] = get_post_meta($post->ID,'is_home_page_featured',true);
			$info['is_category_page_featured'] = get_post_meta($post->ID,'is_category_page_featured',true);
			$info['feature_amount'] = get_post_meta($post->ID,'feature_amount',true);
			$info['feature_cat_amount'] = get_post_meta($post->ID,'feature_cat_amount',true);
			
			$info['is_home_featured'] = get_post_meta($post->ID,'is_home_featured',true);
			$info['is_category_featured'] =get_post_meta($post->ID,'is_category_featured',true);
			/*End get the price package featured option */
			$info['title_desc'] =$post->post_content;
			$info['is_recurring'] =$recurring;
			if($recurring == '1') {
				$info['billing_num'] = $billing_num;
				$info['billing_per'] = $billing_per;
				$info['billing_cycle'] = $billing_cycle;
			}
			$price_info[] = $info;
		}
		return @$price_info;
	}
	
	/*
	Name : templ_set_price_info
	Desc : set the price information of listing
	*/
	function templ_set_price_info($last_postid,$pid,$payable_amount,$alive_days,$payment_method,$coupon,$featured_type){
		$monetize_settings = array();
		$monetize_settings['paid_amount'] = $payable_amount;
		if($pid !='' && $alive_days ==""){
			$monetize_settings['alive_days'] = 'Unlimited'; }
		$monetize_settings['alive_days'] = $alive_days;
		$monetize_settings['paymentmethod'] = $payment_method;
		$monetize_settings['coupon_code'] = $coupon;
				$monetize_settings["paid_amount"] = $payable_amount;
		$monetize_settings["coupon_code"] = $coupon;
		if(!$featured_type){
			  $monetize_settings['featured_type'] = 'none';
			  $monetize_settings['featured_c'] = 'n';
			  $monetize_settings['featured_h'] = 'n';
		}
		if($featured_type == 'c'){
			 $monetize_settings['featured_h'] = 'n';
			 $monetize_settings['featured_c'] = 'c';
		}
		if($featured_type == 'h')
		 {
			 $monetize_settings['featured_c'] = 'n';
			 $monetize_settings['featured_h'] = 'h';
		 }
 		if($featured_type == 'both')
		 {
			 $monetize_settings['featured_c'] = 'c';
			 $monetize_settings['featured_h'] = 'h';
		 }
 		if($featured_type == 'none')
		 {
			 $monetize_settings['featured_c'] = 'n';
			 $monetize_settings['featured_h'] = 'n';
		 }
		foreach($monetize_settings as $key=>$val)
		{
				update_post_meta($last_postid, $key, $val);
		}
	
	}
	
	/*
	Name : templ_total_price
	Args : taxonomy name
	Desc : return the total price of selected categories
	*/
	function templ_total_price($taxonomy){
		$args = array('hierarchical' => true ,'hide_empty' => 0, 'orderby' => 'term_group');
		$terms = get_terms($taxonomy, $args);
		$total_price=0;
		foreach($terms as $term){
				$total_price += $term->term_price;
			
		}
		return $total_price;
	}
	/*
	Name : templ_get_featured_type
	Args : $cur_user_id = current user id
	Desc : return the user last post featured type
	*/	
	function templ_get_featured_type($cur_user_id , $post_type){
		global $wpdb;
		//package_select - package id of last post in database
		
		$user_last_post = $wpdb->get_row("select * from $wpdb->posts p where p.post_type='".$post_type."' and p.post_author = '".$cur_user_id."' order by p.ID DESC LIMIT 0,1");
		$user_last_post_id = @$user_last_post->ID; /* last inserted post */		
		$featured_type=get_post_meta($user_last_post_id,'featured_type',true);
		return $featured_type;
	}
	
	
	/*
	Name : templ_get_packagetype
	Args : $cur_user_id = current user id
	Desc : return the package type of current user
	*/	
	function templ_get_packagetype($cur_user_id , $post_type){
		global $wpdb;
		//package_select - package id of last post in database
		//fetch only user publish user last post

		$user_last_post = $wpdb->get_row("select * from $wpdb->posts p where p.post_type = '".$post_type."' and p.post_author = '".$cur_user_id."' and post_status IN ('publish','trash') order by p.ID DESC LIMIT 0,1");
		$user_last_post_id = @$user_last_post->ID; /* last inserted post */
		
		$selected_pkg = get_post_meta($user_last_post_id,'package_select',true);/* selected package id to fetch package type*/
		
		$package_type = get_post_meta($selected_pkg,'package_type',true); /* 1- Single Submission, 2- Subscription */
		if(!$package_type){ $package_type =1; }
		return $package_type;
	}	
	
	/*
	Name : templ_get_packagetype_last_postid
	Args : $cur_user_id = current user id
	Desc : return the last post id
	*/	
	function templ_get_packagetype_last_postid($cur_user_id , $post_type){
		global $wpdb;
		//package_select - package id of last post in database
		
		$user_last_post = $wpdb->get_row("select * from $wpdb->posts p where p.post_type NOT IN('attachment','inherit','nav-menu','page','post') and p.post_author = '".$cur_user_id."' and post_status IN ('publish','draft','trash') order by p.ID DESC LIMIT 0,1");
		$user_last_post_id = @$user_last_post->ID; /* last inserted post */
		
		return $user_last_post_id;
	}	
	
	
	/*
	Name : templ_get_packaget_post_status
	Args : $cur_user_id = current user id
	Desc : return the post status of current user
	*/	
	function templ_get_packaget_post_status($cur_user_id , $post_type,$package_id){
		global $wpdb;
		//package_select - package id of last post in database
		
		$user_last_post = $wpdb->get_row("select * from $wpdb->posts p where p.post_type NOT IN('attachment','inherit','nav-menu','page','post') and p.post_author = '".$cur_user_id."' and (p.post_status='publish' or p.post_status='draft') order by p.ID DESC LIMIT 0,1");
		if($user_last_post){
			$post_status = $user_last_post->post_status;
		}else{
			$post_status = fetch_posts_default_status();
		}
		return $post_status;
	}
	/*
	Name : templ_days_for_packagetype
	Args : $cur_user_id = current user id
	Desc : fetch the details of package type user selected when come to submit the listing
	*/
	function templ_days_for_packagetype($cur_user_id , $post_type){
		global $wpdb;		
		$package_type = $this->templ_get_packagetype($cur_user_id , $post_type); /* 1- pay per posy, 2- Subscription */
		if($package_type == 2){
			if($cur_user_id){ 
	
			$adays = $wpdb->get_row("select * from $wpdb->posts p where p.post_type NOT IN('attachment','inherit','nav-menu','page','post') and p.post_author = '".$cur_user_id."' order by p.ID DESC LIMIT 0,1");
			if($adays->ID){ 
			$alive_day = get_post_meta($adays->ID,'alive_days',true);
			$publish_date =  strtotime($adays->post_date);
			$publish_date =  date('Y-m-d',$publish_date);
			$curdate = date('Y-m-d');
			
			$days = templ_number_of_days($publish_date,$curdate);
			if(($days == @$alive_days && $days < @$alive_days) || $days ==0){ $alive_days = $alive_day - $days; }else{ $alive_days =0; }
			return $alive_days;
			}
		}}
	}
	
	/*
	Name : templ_days_for_user_packagetype
	Args : $cur_user_id = current user id
	Desc : fetch the details of package type user selected when come to submit the listing
	*/
	function templ_days_for_user_packagetype($cur_user_id , $post_type){
		global $wpdb;		
		$package_id = get_user_meta($cur_user_id ,'package_select',true); 
		$package_type = get_post_meta($package_id,'package_type',true);/* 1- pay per posy, 2- Subscription */		
		if($package_type == 2){
			if($cur_user_id){ 	
				$adays = $wpdb->get_row("select * from $wpdb->posts p where p.post_type NOT IN('attachment','inherit','nav-menu','page','post') and p.post_author = '".$cur_user_id."' order by p.ID DESC LIMIT 0,1");
				if($adays->ID){ 
					$alive_day = get_post_meta($adays->ID,'alive_days',true);
					$publish_date =  strtotime($adays->post_date);
					$publish_date =  date('Y-m-d',$publish_date);
					$curdate = date('Y-m-d');
					
					$days = templ_number_of_days($publish_date,$curdate);
					if(($days == $alive_days && $days < $alive_days) || $days ==0){ $alive_days = $alive_day - $days; }else{ $alive_days =0; }
					return $alive_days;
				}
			}
		}
	}
	/*
	* fetch the details of package type user selected when come to submit the listing
	*/
	function templ_free_alive_days_for_user_packagetype($cur_user_id='' , $post_type='',$package_id=''){
		global $wpdb;		
		$package_type = get_post_meta($package_id,'package_type',true);/* 1- pay per posy, 2- Subscription */		
		if($package_type == 2){
			if($cur_user_id){
				$adays = $wpdb->get_row("select * from $wpdb->posts p where p.post_type NOT IN('attachment','inherit','nav-menu','page','post') and p.post_author = '".$cur_user_id."' order by p.ID ASC LIMIT 0,1");
				if($adays->ID){ 
					$alive_day = get_post_meta($package_id,'subscription_days_free_trail',true);
					$publish_date =  strtotime($adays->post_date);
					$publish_date =  date('Y-m-d',$publish_date);
					$curdate = date('Y-m-d');
					
					$days = templ_number_of_days($publish_date,$curdate);
					if(( $days < $alive_day) || $days ==0){ $alive_days = $alive_day - $days; }else{ $alive_days =0; }
					return $alive_days;
				}
			}
		}
	}
	/*
	Name : is_user_have_alivedays
	Args : $cur_user_id = current user id
	Desc : fetch the details of package type user selected when come to submit the listing
	*/
	function is_user_have_alivedays($cur_user_id , $post_type){
		global $wpdb;
		
		$package_type = $this->templ_get_packagetype($cur_user_id , $post_type); /* 1- pay per posy, 2- Subscription */
		if($package_type == 2){
			if($cur_user_id){ 
			$adays = $wpdb->get_row("select * from $wpdb->posts p where p.post_type NOT IN('attachment','inherit','nav-menu','page','post') and p.post_author = '".$cur_user_id."' order by p.ID DESC LIMIT 0,1");
			if($adays->ID){
			$alive_day = get_post_meta($adays->ID,'alive_days',true);
			$publish_date =  strtotime($adays->post_date);
			$publish_date =  date('Y-m-d',$publish_date);
			$curdate = date('Y-m-d');
			
			$days = templ_number_of_days($publish_date,$curdate,30);
			//echo $alive_day."=".$days;
				if($alive_day > $days && $days == 0){
					return false;
				}else{
					return true;
				}
			}else{
				return true;
			}
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	/*
	Name : is_user_have_alivedays
	Args : $cur_user_id = current user id
	Desc : fetch the details of package type user selected when come to submit the listing
	*/
	function is_package_have_alivedays($cur_user_id , $post_type,$package_id=''){
		global $wpdb;
		$package_type = get_post_meta($package_id,'package_type',true); /* 1- pay per posy, 2- Subscription */
		if($package_type == 2){
			if($cur_user_id){ 
				global $post,$wp_query;
				/*query to fetch all the enabled price package*/
				$args=array('post_type'      	=> $post_type,
							'posts_per_page' 	=> 1	,
							'post_status'    	=> array('publish'),
							'author'			=> $cur_user_id,
							'order'				=> 'ASC'
							);
				
				$post_query = null;
				$post_query = new WP_Query($args);	
				$post_meta_info = $post_query;
				if($post_meta_info->found_posts <= 0){
					return true;
				}
				elseif($post_meta_info->posts[0]->ID){
					$package_select = get_post_meta($post_meta_info->posts[0]->ID,'package_select',true);
					$alive_day = (get_post_meta($package_select,'days_for_no_post',true))?get_post_meta($package_select,'days_for_no_post',true):get_post_meta($post_meta_info->posts[0]->ID,'alive_days',true);
					$publish_date =  strtotime($post_meta_info->posts[0]->post_date);
					$publish_date =  date('Y-m-d',$publish_date);
					$curdate = date('Y-m-d');
					$days = templ_number_of_days($publish_date,$curdate,30);

					if($alive_day > $days ){
						return true;
					}else{
						return false;
					}
				}else{
					return true;
				}
				}else{
					return true;
				}
			}else{
				return true;
			}
	}
	
	/*
	* fetch the details of package type user selected when come to submit the listing
	*/
	function is_user_package_have_alivedays($cur_user_id='' , $post_type='',$package_id=''){
		global $wpdb,$monetization,$current_user;
		$users_packageperlist=$wpdb->prefix.'users_packageperlist';
		$package_type = get_post_meta($package_id,'package_type',true);
		$sql="SELECT * FROM $users_packageperlist WHERE user_id=".$current_user->ID." AND 	package_id=".@$_POST['package_select']." AND status=1";
		if($package_type == 2){
			if($cur_user_id){ 
			$adays = $wpdb->get_row("SELECT * FROM $users_packageperlist WHERE user_id=".$cur_user_id." AND package_id=".$package_id." AND status=1 ");
			$listing_price_info = $monetization->templ_get_price_info($package_id);
			/* Package Alive days */
			$alive_days=$listing_price_info[0]['alive_days'];
			if($adays->ID){
			$alive_day = (get_post_meta($adays->package_id,'days_for_no_post',true))?get_post_meta($adays->package_id,'days_for_no_post',true):get_post_meta($adays->package_id,'alive_days',true);
			$publish_date =  strtotime($adays->date);
			$publish_date =  date('Y-m-d',$publish_date);
			$curdate = date('Y-m-d');
			
			$days = templ_number_of_days($publish_date,$curdate,30);
			if($alive_days > $days && $days == 0){
					return false;
				}else{
					return true;
				}
			}else{
				return true;
			}
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	
	
	function tmpl_get_payable_amount($pkg_id=0,$featured_type=0,$scats='')
	{
		global $wpdb;
		
		$package_price = get_post_meta($pkg_id,'package_amount',true);
		if(isset($featured_type) && $featured_type == 'h')
		{
			$feature_amount = get_post_meta($pkg_id,'feature_amount',true);
		}elseif(isset($featured_type) && $featured_type == 'c')
		{
			$feature_amount = get_post_meta($pkg_id,'feature_cat_amount',true);
		}elseif(isset($featured_type) && $featured_type == 'both')
		{
			$feature_amount = get_post_meta($pkg_id,'feature_cat_amount',true)+get_post_meta($pkg_id,'feature_amount',true);
		}
		
		for($i=0;$i<count($scats);$i++)
		{
			$cat_price = explode(",",$scats[$i]);
			$category_price = get_term( $cat_price[0], $_POST['cur_post_taxonomy']);
			$final_cat_price += $category_price->term_price; 
		}
		return $package_price+$feature_amount+$final_cat_price;
	}
	/*
	 * fetch the price package for submit form. 
	 */
	function tmpl_fetch_price_package($user_id='',$post_type='',$page_id='')
	{
		global $post,$wp_query,$current_user,$wpdb;
		$transaction_tabel = $wpdb->prefix . "transactions";
		/*query to fetch all the enabled price package*/
		$args=array('post_type' => 'monetization_package',
					'posts_per_page' => -1	,
					'post_status' => array('publish'),
					'meta_query' => array('relation' => 'AND',
									  array('key' => 'package_status',
											'value' =>  '1',
											'compare' => '='
											),
				  					  array('key' => 'package_post_type',
											'value' =>  $post_type,
											'compare' => 'LIKE'
											)
								),
				    'orderby' => 'menu_order',
				    'order' => 'ASC'
			);

		/*Check user submited price package subscription */
		$package_id=get_user_meta($current_user->ID,'package_selected',true);// get the user selected price package id
		if(!$package_id)
			$package_id=get_user_meta($current_user->ID,$post_type.'_package_select',true);// get the user selected price package id
		$user_limit_post=get_user_meta($current_user->ID,$post_type.'_list_of_post',true); //get the user wise limit post count on price package select
		if($user_limit_post==''){
			$user_limit_post='0';
		}
		if(isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg']==1)
		{
			$pkg_id = get_post_meta($_REQUEST['pid'],'package_select',true);
		}
		
		
		$package_post_type = explode(",",get_post_meta($package_id,'package_post_type',true));
	
		$package_limit_post=get_post_meta($package_id,'limit_no_post',true);// get the price package limit number of post
		$user_have_pkg = get_post_meta($package_id,'package_type',true); 
		$user_last_postid = $this->templ_get_packagetype_last_postid($current_user->ID,$post_type); /* User last post id*/
		$user_have_days = $this->templ_days_for_packagetype($current_user->ID,$post_type); /* return alive days(numbers) of last selected package  */
		$is_user_have_alivedays = $this->is_package_have_alivedays($current_user->ID,$post_type,$package_id); /* return user have an alive days or not true/false */
		
		$is_user_package_have_alivedays = $this->is_user_package_have_alivedays($current_user->ID,$post_type,$package_id); /* return user have an alive days or not true/false */
		$subscription_days_free_trail = (get_user_meta($current_user->ID,'package_free_submission',true))?get_user_meta($current_user->ID,'package_free_submission',true):0;
		
		$package_avlie_days = $this->templ_free_alive_days_for_user_packagetype($current_user->ID,$post_type,$package_id);
		$price_pacage_alive_days = (get_post_meta($package_id,'subscription_days_free_trail',true))?get_post_meta($package_id,'subscription_days_free_trail',true):0;// get the price package limit number of post
		//check last user post package type check
		if($current_user->ID )// check user wise post per  Subscription limit number post post 
		{		
			/*Only get the pay per subscription package id from postmeta */
			$package_id_sql= "SELECT post_id from {$wpdb->prefix}postmeta where meta_key='package_type' AND meta_value=2";
			/*Get the user last transaction  */
			$transaction_status = $wpdb->get_results("SELECT status,package_id FROM $transaction_tabel where payforpackage=1 AND user_id=".$current_user->ID." AND package_id in(".$package_id_sql.") order by trans_id DESC LIMIT 1");
			$trans_status=@$transaction_status[0]->status;
			$trans_package_id=@$transaction_status[0]->package_id;
			$post_types = explode(',',get_post_meta($package_id,'package_post_type',true)); 
			if(in_array($post_type,$post_types)): $is_posttype_inpkg=1; else: $is_posttype_inpkg=0; endif; // check is this taxonomy included in package or not
		}
		$paypersubscription=0;
		$data_price = '';
		$listing_price_info = $this->templ_get_price_info($package_id);
		$subscription_alive_days = $listing_price_info[0]['alive_days'];
		/*alive days calculation of particualr price package*/
		$cal_pakg_alive_days = (get_post_meta($package_id,'days_for_no_post',true))?get_post_meta($package_id,'days_for_no_post',true):$subscription_alive_days;
		if($cal_pakg_alive_days > 0 )
		{
			$current_date = strtotime(date('Y-m-d h:i:s'));	
			$postid_str = $wpdb->get_results("select p.ID,t.payment_date,t.post_id from $wpdb->posts p,$transaction_tabel t where t.user_id=".$current_user->ID." AND (t.package_type is NULL OR t.package_type=0) group by t.trans_id order by t.trans_id ASC LIMIT 0,1");
			if(count($postid_str) > 0)
			{
				foreach($postid_str as $post_day)
				{
					$days_for_no_post = $current_date -  strtotime($post_day->payment_date);
					$days_for_no_post = floor($days_for_no_post/86400);
					$days_left = $cal_pakg_alive_days - $days_for_no_post;
				}
			}
			else
			{
				$days_left = $cal_pakg_alive_days;
			}
		}
		/*package alive days calculation finish*/
		if($current_user->ID && ($package_limit_post > $user_limit_post && $is_user_have_alivedays == 1 && $package_limit_post!=$user_limit_post && $user_limit_post!='' && $trans_status==1) &&  $subscription_days_free_trail >0 && $subscription_days_free_trail>=$package_avlie_days &&  in_array($post_type,$package_post_type) && get_user_meta($current_user->ID,'upgrade',true) != 'upgrade' && get_user_meta($current_user->ID,'package_free_submission_completed',true) != 'completed' && $days_left >=0 ){
			/*user purchase pay per subscription then show per pay post pirce package if user will be go through Single Submission wise */
			$args['meta_query'][2]=array('key' => 'package_type','value' =>  array(1,2),'compare' => 'NOT IN');
			$paypersubscription=1;
			$data_price = 1;
		}
		else if($current_user->ID && ($package_limit_post > $user_limit_post && $package_limit_post!=$user_limit_post && $user_limit_post !=''  && $trans_status==1) && in_array($post_type,$package_post_type)  && get_user_meta($current_user->ID,'package_free_submission_completed',true) != 'completed' && $days_left >=0){
			/*user purchase pay per subscription then show per pay post pirce package if user will be go through Single Submission wise */
			$args['meta_query'][2]=array('key' => 'package_type','value' =>  1,'compare' => '=');
			$paypersubscription=1;
		}
		else if($current_user->ID && ($package_limit_post > $user_limit_post && $package_limit_post!=$user_limit_post && $user_limit_post !='' && $subscription_days_free_trail >0 && ( $subscription_days_free_trail<=$price_pacage_alive_days || $price_pacage_alive_days ==0)   && $trans_status==1) && in_array($post_type,$package_post_type)  && get_user_meta($current_user->ID,'package_free_submission_completed',true) == 'completed' && $days_left >=0){
			/*user purchase pay per subscription then show per pay post pirce package if user will be go through Single Submission wise */
			$args['meta_query'][2]=array('key' => 'package_type','value' =>  1,'compare' => '=');
			$paypersubscription=1;
		}
			/* Finish user submitted price package subscription*/
		
		
		$post_query = null;
		$post_query = new WP_Query($args);	
		$post_meta_info = $post_query;	
		$i = 0;
		/*loop to fetch the pay per listing package*/
		if($post_meta_info->have_posts() || $paypersubscription==1){
			$is_single_price_package = $this->tmpl_fetch_is_single_price_package($current_user->ID,$post_type,$post->ID);
			if($data_price == 1)
			{?>
				 <input type="hidden" name="upgrade_price" id="upgrade_price" value="<?php echo get_post_meta($package_id,'package_amount',true); ?>">
              <?php
			}
			
			/** when user comes with shortlink provide in package - the first tab of select packages should not be display */
			if(isset($_REQUEST['pkg_id']) && $_REQUEST['pkg_id'] !=''){
				$firs_tab_class=" ";
			}else{
				$firs_tab_class=" active ";
			}
		?>
			<input type="hidden" name="pkg_id" id="pkg_id">
			<input type="hidden" name="pkg_type" id="pkg_type">
            <input type="hidden" name="package_free_submission" id="package_free_submission">
            <input type="hidden" name="upgrade" id="upgrade">
             <input type="hidden" name="completed" id="completed" value="<?php echo get_user_meta($current_user->ID,'package_free_submission_completed',true); ?>">
			<div id="step-plan" <?php if(is_numeric($is_single_price_package)) { ?> style="display:none;" <?php } ?>class="accordion-navigation step-wrapper step-plan current"><a class="step-heading active" href="#"><span>1</span><span><?php printf(__('Select your %s package',DOMAIN),$post_type); ?></span><span><i class="fa fa-caret-down"></i><i class="fa fa-caret-right"></i></span></a>
			<div id="plan" class="content step-plan <?php echo $firs_tab_class; ?> clearfix">
				<div id="packagesblock-wrap" class="block">
                	<?php 
					/*Display purchases pay per subscription package info */
					if($paypersubscription==1):
					?>
                    <div class="packageblock clearifx">
                        <ul data-price="0" data-id="<?php echo $package_id; ?>"  <?php if(get_user_meta($current_user->ID,'upgrade',true) != 'upgrade') { ?> data-free="<?php echo get_post_meta($package_id,'subscription_days_free_trail',true); ?>"  <?php } ?> data-subscribed='1' data-type="2"  data-post="1" class="packagelistitems">
                        <li>
                        <div class="col-md-3 col-sm-6">
                            <div class="panel panel-default text-center">
                            	<div class="panel-heading">
	                                <h3><?php echo get_the_title($package_id); ?></h3>
                                </div>
                                <div class="panel-desc">
	                                 <div class="panel-body">    
                                     <?php _e('You have already subscribed to this package. ',DOMAIN);?>
                                    <p><?php 
									echo sprintf(__('This package allows you to add %d listings, you have already added %d. You still have %d listings left in your package.',DOMAIN),$package_limit_post,$user_limit_post,$package_limit_post-$user_limit_post);?></p>
                                     <?php
									 		if(get_user_meta($current_user->ID,'package_free_submission',true) > 0 && !isset($_REQUEST['page']) && !isset($_REQUEST['pmethod']) && get_user_meta($current_user->ID,'package_free_submission_completed',true) != 'completed')
										   	{
											   ?>
												<p class="margin_right panel-type price package_type"><?php echo '<label>'; _e('Number of free submissions: ',DOMAIN);echo '</label>'; echo '<span>'; echo get_user_meta($current_user->ID,'package_free_submission',true);_e(' Submitted '); echo  (get_post_meta($package_id,'subscription_days_free_trail',true) - get_user_meta($current_user->ID,'package_free_submission',true)); _e(' Left.'); echo '</span>'; ?> </p>
												<?php
										   	}
									 		/*condition to check submit listing within following days*/
									 		if(get_post_meta($package_id,'days_for_no_post',true) > 0)
										   	{
												$current_date = strtotime(date('Y-m-d h:i:s'));	
												$postid_str = $wpdb->get_results("select p.ID,t.payment_date,t.post_id from $wpdb->posts p,$transaction_tabel t where t.user_id=".$current_user->ID." AND (t.package_type is NULL OR t.package_type=0) group by t.trans_id order by t.trans_id ASC LIMIT 0,1");
												if(count($postid_str) > 0)
												{
													foreach($postid_str as $post_day)
													{
														$days_for_no_post = $current_date -  strtotime($post_day->payment_date);
														$days_for_no_post = floor($days_for_no_post/86400);
														$days_left = get_post_meta($package_id,'days_for_no_post',true) - $days_for_no_post;
													}
												}
												else
												{
													$days_left = get_post_meta($package_id,'days_for_no_post',true);
												}
											   ?>
												<p class="margin_right panel-type price package_type"><?php echo '<label>'; _e('Submit listing within following days: ',DOMAIN);echo '</label>'; echo '<span>'; echo $days_left; echo '</span>'; ?> </p>
											   <?php
										   }?>
                                    </div> <!-- panel-body -->
                                    <?php 
									if(in_array($post_type,$package_post_type) && get_user_meta($current_user->ID,'upgrade',true) != 'upgrade' && $subscription_days_free_trail >0  && $is_user_have_alivedays == 1)
									{?>
                                        <div class="upgrade-button">
                                          <!--  <a data-id="<?php echo $package_id; ?>" data-upgrade="upgrade"  class="btn btn-lg btn-primary button select-plan"><?php _e('Upgrade',DOMAIN); ?></a> -->
                                        </div>
                                    <?php } ?>
                                    <div class="pkg-button">
                                       <a data-id="<?php echo $package_id; ?>"  class="btn button button-primary button-large select-plan"><?php _e('Select',DOMAIN); ?></a>                                             
                                    </div> <!-- list-group -->
                                </div><!-- panel-desc -->
                            </div> <!-- panel panel-default -->         
                            <!-- package description -->
                        </div><!-- packages block div closed here -->
                        </li>
                        </ul>
                    </div><!-- package block div closed here -->
                    <?php
					
					endif;
					
					while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
						/*check whether the price package is pay per listing*/
							$i++;
							if(isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg']==1 && $pkg_id==$post->ID){
								continue;
							}
							$disply_price_package=apply_filters('tevolution_price_package_loop_frontend','1',$post,$post_type);
							if($disply_price_package==''){
								continue;	
							}
							
							tmpl_display_package_html($post,$post_type);
							
					endwhile;
					?>
				</div> <!-- End #packageblock-wrap -->
			</div> <!-- End #panel1 -->
			</div>
		<?php
		}

	}
	/* fetch featured option for particular price package selected while submitting listing */
	function tmpl_fetch_price_package_featured_option($user_id='',$post_type='',$post_id='',$pkg_id='',$is_user_select_subscription_pkg='')
	{
		$package_selected = get_post($pkg_id);
		if(isset($_REQUEST['pid']) && $_REQUEST['pid']!="" && !is_admin()){
			$edit_id = $_REQUEST['pid'];
		}
		elseif(is_admin())
		{
			$edit_id = $post_id;
		}
		if(is_plugin_active('thoughtful-comments/fv-thoughtful-comments.php')){
			$author_can_moderate_comment = get_post_meta($pkg_id,'can_author_mederate',true);
		}
			$num_decimals   = absint( get_option( 'tmpl_price_num_decimals' ) );
			$num_decimals 	= ($num_decimals!='')?$num_decimals:'0';
			$decimal_sep    = wp_specialchars_decode( stripslashes( get_option( 'tmpl_price_decimal_sep' ) ), ENT_QUOTES );
			$decimal_sep 	= ($decimal_sep!='')?$decimal_sep:'.';
			$thousands_sep  = wp_specialchars_decode( stripslashes( get_option( 'tmpl_price_thousand_sep' ) ), ENT_QUOTES );
			$thousands_sep 	= ($thousands_sep!='')?$thousands_sep:',';
			$currency = get_option('currency_symbol');
			$position = get_option('currency_pos');
			
			$package_amount = apply_filters( 'formatted_tmpl_price', number_format( get_post_meta($pkg_id,'package_amount',true), $num_decimals, $decimal_sep, $thousands_sep ), $amount, $num_decimals, $decimal_sep, $thousands_sep );
			global  $wpdb,$current_user;
			global  $wpdb;
			 ?>
			<script>
				var currency = '<?php echo get_option('currency_symbol'); ?>';
				var position = '<?php echo get_option('currency_pos'); ?>';
				var num_decimals    = '<?php echo $num_decimals; ?>';
				var decimal_sep     = '<?php echo $decimal_sep ?>';
				var thousands_sep   = '<?php echo $thousands_sep; ?>';
				<?php if(((isset($edit_id) && $edit_id !='' && (isset($_REQUEST['renew']))) || (!isset($edit_id) && $is_user_select_subscription_pkg == 0) || (isset($_REQUEST['backandedit']) && $_REQUEST['backandedit'] == 1) || (isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg'] == 1)) && (function_exists('is_price_package') && is_price_package($current_user->ID,$post_type,$post_id) > 0) )
					{ ?>
						var pkg_price = parseFloat(<?php echo get_post_meta($pkg_id,'package_amount',true); ?>);
						var edit = 0;
				<?php }
					else
					{
					?>
						var pkg_price = parseFloat(0);
						var edit = 1;
				<?php
					}
					?>
			</script>
            <?php
			$featured_h = (get_post_meta($pkg_id,'feature_amount',true))?get_post_meta($pkg_id,'feature_amount',true):0;  /*home page featured amount*/
			$featured_c = (get_post_meta($pkg_id,'feature_cat_amount',true))?get_post_meta($pkg_id,'feature_cat_amount',true):0; /*category page featured amount*/
			
			$is_home_featured = get_post_meta($pkg_id,'is_home_featured',true);/*is price package amount includes the home page featured amount*/
			$is_category_featured = get_post_meta($pkg_id,'is_category_featured',true); /*is price package amount includes the category page featured amount*/
			
			$is_home_page_featured = get_post_meta($pkg_id,'is_home_page_featured',true);/*is price package includes the home page featured */
			$is_category_page_featured = get_post_meta($pkg_id,'is_category_page_featured',true); /*is price package includes the category page featured*/	
			
			$package_alive_days = $this->templ_free_alive_days_for_user_packagetype($current_user->ID,$post_type,$pkg_id);
			$subscription_days_free_trail = get_post_meta($pkg_id,'subscription_days_free_trail',true);
			
			$edit_is_home_page_featured = get_post_meta($edit_id,'featured_h',true);
			$edit_is_cat_page_featured = get_post_meta($edit_id,'featured_c',true);
			
			if((@$is_home_page_featured || @$is_category_page_featured) && (!$is_home_featured || !$is_category_featured))
			{
			?>
				<div class="form_row clearfix" id="is_featured">
					<label><strong><?php _e('Would you like to make this ',DOMAIN).$post_type; _e('featured?',DOMAIN); ?></strong></label>
					<div class="feature_label">
						<?php
						if(!@$is_home_featured && $is_home_page_featured)
						{?>
							<label><input type="checkbox" <?php if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $edit_is_home_page_featured == 'h' ){?>  checked="checked"  <?php if(!is_admin()){ ?>disabled="disabled" <?php } } elseif( $_SESSION['custom_fields']['featured_h']  != '') { ?>  checked="checked" <?php } ?> name="featured_h" id="featured_h" value="<?php echo apply_filters( 'formatted_tmpl_price', number_format( $featured_h, $num_decimals, $decimal_sep, $thousands_sep ), $amount, $num_decimals, $decimal_sep, $thousands_sep ); ?>" /><?php _e('Yes &sbquo; feature this listing on homepage.',DOMAIN); ?> <span id="ftrhome"><?php if(isset($featured_h) && $featured_h !=""){ echo "(".display_amount_with_currency_plugin($featured_h).")"; } ?></span></label>
					<?php }
						if(!@$is_category_featured && $is_category_page_featured){?>
							<label><input type="checkbox" <?php if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $edit_is_cat_page_featured == 'c' ){?>  checked="checked"  <?php if(!is_admin()){ ?>disabled="disabled" <?php }  } elseif( $_SESSION['custom_fields']['featured_c']  != '') { ?>  checked="checked" <?php } ?> name="featured_c" id="featured_c" value="<?php echo apply_filters( 'formatted_tmpl_price', number_format( $featured_c, $num_decimals, $decimal_sep, $thousands_sep ), $amount, $num_decimals, $decimal_sep, $thousands_sep ); ?>" /><?php _e('Yes &sbquo; feature this listing on category page.',DOMAIN); ?><span id="ftrcat"><?php if(isset($featured_c) && $featured_c !=""){ echo "(".display_amount_with_currency_plugin($featured_c).")"; } ?></span></label>
					<?php }
						if(is_plugin_active('thoughtful-comments/fv-thoughtful-comments.php')){
							
							if($pkg_id)
							{
								$comment_mederation_amount = get_post_meta($pkg_id,'comment_mederation_amount',true);
							}
							if(!$comment_mederation_amount){ $comment_mederation_amount =0; }
							{
							?>
								<label><input type="checkbox" name="author_can_moderate_comment" id="author_can_moderate_comment" value="<?php echo $comment_mederation_amount; ?>" onclick="featured_list(this.id)" <?php if(@$_SESSION['custom_fields']['author_can_moderate_comment'] !=""){ echo "checked=checked"; } ?>/><?php echo ' ';_e(MODERATE_COMMENT,DOMAIN); ?><span id="ftrcomnt"><?php if(isset($author_can_moderate_comment) && $author_can_moderate_comment !=""){ echo "(".fetch_currency_with_position($comment_mederation_amount).")"; }else{ echo "(".fetch_currency_with_position('0').")"; } ?></span></label>
								<input type="hidden" name="author_moderate" id="author_moderate" value="0"/>
							<?php	
							}
						}
						?>
						<input type="hidden" name="featured_type" id="featured_type" value="<?php echo ($featured_type)? $featured_type : 'none'?>"/>
						<span id='process' style='display:none;'><i class="fa fa-circle-o-notch fa-spin"></i></span>
					
					</div>
				</div>
			<?php }
			$cat_price=0;
			if(isset($_SESSION['custom_fields']) && $_SESSION['custom_fields']['category']!=''){
				
				foreach($_SESSION['custom_fields']['category'] as $category){
					$category_price = explode(',',$category);
					$cat_price+=$category_price[1];	
				}				
				$package_price=get_post_meta($_SESSION['custom_fields']['pkg_id'],'package_amount',true);				
			}
			if(isset($_REQUEST['backandedit']) && $_REQUEST['backandedit']==1){
				$cat_price = $_SESSION['custom_fields']['all_cat_price'];
				$package_price=get_post_meta($_REQUEST['pkg_id'],'package_amount',true);				
			}
			if(@$_REQUEST['front_end'] == 1 || @$_REQUEST['backandedit'] == 1 || (function_exists('is_price_package') && is_price_package($current_user->ID,$post_type,$post_id) <= 0))
			{
			?>
			<div id="price_package_price_list" class="form_row clearfix" style="display:none;">
            	<div class="form_cat">
	               <span class="total_charges"><b><?php _e('Total Charges:',DOMAIN); echo ' '; ?></b></span>
					<span id="before_cat_price_id"  <?php if($cat_price<=0):?> style="display:none;"<?php endif;?> ><?php if($position == '1'){ echo $currency; }else if($position == '2'){ echo $currency.'&nbsp;';}?></span>
					<span id="cat_price" <?php if($cat_price<=0):?>style="display:none;"<?php endif;?> ><?php echo apply_filters( 'formatted_tmpl_price', number_format( $cat_price, $num_decimals, $decimal_sep, $thousands_sep ), $amount, $num_decimals, $decimal_sep, $thousands_sep );?></span>
					<span id="cat_price_id" style="display:none;"><?php if($position == '3'){ echo $currency; }else if($position != 1 && $position != 2 && $position !=3){ echo '&nbsp;'.$currency; } ?>		</span>		
					<span id="cat_price_add" style="display:none;"><?php echo '+'; ?> </span>	
                    <?php if((isset($edit_id) && $edit_id !='' && (isset($_REQUEST['renew']))) || (!isset($edit_id) && $is_user_select_subscription_pkg == 0) || isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg']==1  || isset($_REQUEST['backandedit']) && $_REQUEST['backandedit']==1 )
						 { ?>
					<span id="pakg_add" <?php if($package_price<=0):?>style="display:none;"<?php endif;?>><?php echo '+';?> 	</span>	
					
					<span id="before_pkg_price_id" <?php if(@$package_amount <=0){ ?>style="display:none;" <?php } ?>><?php if($position == '1'){ echo $currency; }else if($position == '2'){ echo $currency.'&nbsp;'; } ?></span>
					<span id="pkg_price" <?php if(@$package_amount <=0){?> style="display:none;" <?php } ?> ><?php if(isset($package_amount) && $package_amount !=""){ echo $package_amount; } else{ echo "0";}?></span>
					<span id="pkg_price_id" <?php if(@$package_amount <=0){ ?>style="display:none;" <?php } ?> ><?php if($position == '3'){ echo $currency; }else if($position != 1 && $position != 2 && $position !=3){ echo '&nbsp;'.$currency; } ?>	</span>	
					<span id="pakg_price_add" style="display:none;" ><?php echo '+'; ?> </span>	
                    <?php } ?>
                    
					
					
					<span id="before_feture_price_id" style="display:none;"><?php if($position == '1'){ echo $currency; }else if($position == '2'){ echo $currency.'&nbsp;'; } ?></span>
					<span id="feture_price" style="display:none;"><?php if($fprice !=""){ echo $fprice ; }else{ echo "0"; }?></span>
					<span id="feture_price_id" style="display:none;"><?php if($position == '3'){ echo $currency; }else if($position != 1 && $position != 2 && $position !=3){ echo '&nbsp;'.$currency; } ?></span>	
					
					<span id="cat_price_total_price" style="display:none;"><?php echo "<span id='result_price_equ'>=</span>"; ?>
					<?php if($position == '1'){ echo '<span id="currency_before_result_price">'.$currency.'</span>'; }else if($position == '2'){ echo '<span id="currency_before_space_result_price">'.$currency.'&nbsp;</span>'; } ?>
					<span id="result_price"><?php if($total_price != ""){ echo $total_price; }else if($catid != ""){  echo $catprice->term_price; }else{ echo "0";} ?></span>
					<?php if($position == '3'){ echo '<span id="currency_after_result_price">'.$currency.'</span>'; }else if($position != 1 && $position != 2 && $position !=3){ echo '<span id="currency_after_space_result_price">&nbsp;'.$currency."</span>"; } ?></span>
					
					
				</div>
				<span class="message_note"> </span>
				<span id="category_span" class="message_error2"></span>
			<!-- END - FETCH TOTAL PRICE -->
			</div>
			<?php
			}
	}
	
	
	function tmpl_fetch_is_single_price_package($user_id='',$post_type='',$post_id='')
	{
		global $post,$wp_query;
		/*query to fetch all the enabled price package*/
		$args=array('post_type'      => 'monetization_package',
					'posts_per_page' => -1	,
					'post_status'    => array('publish'),
					'meta_query'     => array('relation' => 'AND',
										  array('key' => 'package_status','value' =>  '1','compare' => '='),
										  array('key' => 'package_post_type','value' =>  $post_type,'compare' => 'LIKE')
									)
					);
		
		$post_query = null;
		$post_query = new WP_Query($args);	
		$post_meta_info = $post_query;	
		
		/*return the different value for price package have more than one price package , single price package and none of them.*/
		if($post_meta_info->found_posts > 1){
			return 'show_price_package';			
		}elseif($post_meta_info->found_posts == 1){
			if(get_post_meta($post_meta_info->posts[0]->ID,'package_type',true) == 1){
				return $post_meta_info->posts[0]->ID;
			}else{
				return 'show_price_package';
			}
		}else{
			return false;
		}
	}
} /* class end */
}
if(!isset($monetization))
{
	$monetization = new monetization();
}
/*
NAme : recent_transactions_dashboard_widgets
Desc : admin dashboard transaction widgte setup
*/
function recent_transactions_dashboard_widgets() {
	global $current_user;
	if(is_super_admin($current_user->ID)) {
		wp_add_dashboard_widget('recent_transactions_dashboard_widgets', RECENT_TRANSACTION_TEXT, 'recent_transactions_dashboard_widget');
		
		global $wp_meta_boxes;
	
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	
		@$example_widget_backup = array('recent_transactions_dashboard_widgets' => $normal_dashboard['recent_transactions_dashboard_widgets']);
		unset($normal_dashboard['recent_transactions_dashboard_widgets']);
	
		$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);
	
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}
}
add_action('wp_dashboard_setup', 'recent_transactions_dashboard_widgets' );
/*
NAme : recent_transactions_dashboard_widget
Desc : admin dashboard transaction widgte display
*/
function recent_transactions_dashboard_widget(){
	global $wpdb,$monetization;
	
	?>
<script type="text/javascript">
var chkTransstatus = null;
function change_poststatus(str)
{ 
	  if (str=="")
		  {
		  jQuery("#p_status_"+tid).html("");
		  return false;
		  }
		  
	/* Ajax request for locate change transaction status */
		chkTransstatus = jQuery.ajax({
			url:ajaxUrl,
			type:'POST',
			async: true,
			data:'action=tmpl_ajax_update_status&post_id='+str,
			beforeSend : function(){
				if(chkTransstatus != null){
					chkTransstatus.abort();
				}
			},
			success:function(results){				
				jQuery("#p_status_"+str).html(results);
			}
		});  	

}
</script>
<?php
	$tmpdata = get_option('templatic_settings');
	if(isset($tmpdata['trans_post_type_value']) && count($tmpdata['trans_post_type_value']) > 0)
	{
		$post_args = array('post_status'=>'draft,publish','post_type' => $tmpdata['trans_post_type_value'],'order'=>'DESC','numberposts'=> 7 );
		$recent_posts = get_posts( $post_args );
		$no_alive_days = get_option('no_alive_days');
	if($recent_posts){
		
		$transactions = $wpdb->prefix."transactions";
		echo '<table class="widefat"  width="100%" >
			<thead>	';
		$th='	<tr>
				<th valign="top" align="left" style="width: 50%;">'.__('Transactions',DOMAIN).'</th>
				<th valign="top" align="left">'.__('With',DOMAIN).'</th>
				<th valign="top" align="left">'.__('Exp.',DOMAIN).'</th>
				<th valign="top" align="left">'.__('Status',DOMAIN).'</th>';
		$th .=	'</tr>';   
		echo $th;
		foreach($recent_posts as $posts) {			
			
			$color_taxonomy = 'trans_post_type_colour_'.$posts->post_type;			
			
			$featured_text = '';
			//Check for featured posts: start
			$featured_type = get_post_meta($posts->ID,'featured_type',true);
			if( 'h' == $featured_type ){
				$featured_text = '<div>'.__("Home",DOMAIN).'</div>';
			}elseif( 'c' == $featured_type ){
				$featured_text = '<div>'.__("Category",DOMAIN).'</div>';
			}elseif( 'both' == $featured_type ){
				$featured_text = '<div>'.__("Home, Category",DOMAIN).'</div>';
			}else{
				$featured_text = '';
			}
			
			$price_amount=(get_post_meta($posts->ID,'total_price',true)) ? fetch_currency_with_position(get_post_meta($posts->ID,'total_price',true)): fetch_currency_with_position('0');
		
			
			
			$sql="select * from $transactions where post_id=".$posts->ID." AND (package_type is NULL OR package_type=0)";
			$tran_info = $wpdb->get_results($sql);
			
			$transaction_price_pkg = $monetization->templ_get_price_info($tran_info[0]->package_id,'');
			$publish_date =  date_i18n('Y-m-d',strtotime($tran_info[0]->payment_date));
			$alive_days = $transaction_price_pkg[0]['alive_days'];
			$expired_date = date_i18n(get_option("date_format"),strtotime($publish_date. "+$alive_days day"));
			
			if(isset($tmpdata[$color_taxonomy]) && $tmpdata[$color_taxonomy]!= '') { $color_taxonomy_value = $tmpdata[$color_taxonomy]; } 
			echo '<tr>
				<td valign="top" align="left" ><a href="'.admin_url().'admin.php?page=transcation&action=edit&trans_id='.$posts->ID.'">'.$posts->ID.'</a>&nbsp; <a href="'.site_url().'/wp-admin/post.php?post='.$posts->ID.'&action=edit">'.$posts->post_title.'</a>&nbsp;<div class="transaction_meta">'.__('On',ADMINDOMAIN)."&nbsp;".date_i18n(get_option("date_format"),strtotime($tran_info[0]->payment_date)).'&nbsp;'.__('with',ADMINDOMAIN)." ".get_the_title($tran_info[0]->package_id).'&nbsp;'.__('with amt.',ADMINDOMAIN).'<span style="color:green;">'.$price_amount.'</span></div></td>';
			//echo '<td valign="top" style="color:'.$color_taxonomy_value.'" align="left">'.ucfirst($posts->post_type).'</td>';	
			echo '<td valign="top" align="left">'.$tran_info[0]->payment_method.'</td>';	
			echo '<td valign="top" align="left">'.$expired_date.'</td>';
				if($no_alive_days !='1')
				{
					echo 	'<td valign="top" align="left">';
					if(get_post_meta($posts->ID,'alive_days',true)) { echo get_post_meta($posts->ID,'alive_days',true);} else { echo '0';} echo '</td>';
				}
			if(get_post_status($posts->ID) =='draft'){
			echo '<td valign="top" align="left" id="p_status_'.$posts->ID.'"><a href="javascript:void(0);" onclick="change_poststatus('.$posts->ID.')"  style="color:#E66F00">'.PENDING.'</a></td>';
			}else if(get_post_status($posts->ID) =='publish'){
			echo '<td valign="top" align="left" style="color:green" id="p_status_'.$posts->ID.'">'.APPROVED_TEXT.'</td>';
            }
			
			
			echo '</tr>';
			
			} 
		echo '</thead>	</table>';
		
		echo  '<p><a href="'.admin_url( 'admin.php?page=transcation' ).'">View More Transactions</a></p>';
		} else {
			echo __('No recent transaction available.',ADMINDOMAIN);
		}
	}
	else {
		echo '<p style="margin:0 0 10px">'.sprintf(__('No transaction type selected from  <a href="%s" >transaction settings</a>.',ADMINDOMAIN),admin_url( 'admin.php?page=transcation' )).'</p>';
	}
}
/* DELETE THE PACKAGE DATA */
if( (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' && (isset($_REQUEST['package_id']) && $_REQUEST['package_id'] != '')))
{
	global $wpdb,$post;
	$id = $_REQUEST['package_id'];
	/* DELETING THE PACKAGE ON CLICK OF DELETE BUTTON OF DASHBOARD METABOX */
	delete_post_meta($id, 'package_type');
	delete_post_meta($id, 'package_post_type');
	delete_post_meta($id, 'category');
	delete_post_meta($id, 'show_package');
	delete_post_meta($id, 'package_amount');
	delete_post_meta($id, 'validity');
	delete_post_meta($id, 'validity_per');
	delete_post_meta($id, 'package_status');
	delete_post_meta($id, 'recurring');
	delete_post_meta($id, 'billing_num');
	delete_post_meta($id, 'billing_per');
	delete_post_meta($id, 'billing_cycle');
	delete_post_meta($id, 'is_featured');
	delete_post_meta($id, 'feature_amount');
	delete_post_meta($id, 'feature_cat_amount');
	wp_delete_post($id);
	$url = site_url().'/wp-admin/admin.php?page=monetization';
	echo '<form action="'.$url.'" method="get" id="frm_package" name="frm_package">
	<input type="hidden" value="monetization" name="page"><input type="hidden" value="delete" name="package_msg">
	<input type="hidden" value="packages" name="tab">
	</form>
	<script>document.frm_package.submit();</script>
	';exit;	
}
/* 
name : tmpl_get_transaction_status
description : FUNCTION TO FETCH TRANSACTIONS */
function tmpl_get_transaction_status($tid,$pid){
	global $wpdb,$transection_db_table_name;
	$transection_db_table_name = $wpdb->prefix.'transactions'; 
	$trans_status = $wpdb->get_var("select status from $transection_db_table_name t where trans_id = '".$tid."' order by t.trans_id DESC");
	$result = '';
	if($trans_status == 0){
		$result = '<a id="p_status_'.$tid.'" onclick="change_transstatus('.$tid.','.$pid.')" style="color:#E66F00; font-weight:normal;"  href="javascript:void(0);">'.__('Pending',DOMAIN).'</a>';
	}else if($trans_status == 1){
		$result = '<span style="color:green; font-weight:normal;">'.__('Approved',DOMAIN).'</span>';
	}
	else if($trans_status == 2){
		$result = '<span style="color:red; font-weight:normal;">'.__('Cancel',DOMAIN).'</span>';
	}
	return apply_filters('tmpl_get_transaction_status',$result,$tid,$pid);
}
//END OF FUNCTION
/*
name : fetch_payment_description
description : fetch payment option
*/
function fetch_payment_description($pid)
{
	global $wpdb;
	$transection_db_table_name = $wpdb->prefix.'transactions';
	$transsql_select = "select * from $transection_db_table_name where post_id = ". $pid." AND (package_type is NULL OR package_type=0) ORDER BY trans_id DESC LIMIT 1";
	$transsql_result = $wpdb->get_row($transsql_select);
	
	$payment_options = get_option('payment_method_'.$transsql_result->payment_method);
	$payment_method_name = $payment_options['name'];
	if($transsql_result->status){
		$status = "Approved";
	}else{
		$status = "Pending";
	}
	echo "<li><p class='submit_info_label'>". __('Amount',DOMAIN) .": </p> <p class='submit_info_detail'> ".fetch_currency_with_position( @$transsql_result->payable_amt )."</p></li>";
	if($transsql_result->payment_method!="")
	{
		if(function_exists('icl_register_string')){
			icl_register_string(DOMAIN,$payment_method_name,$payment_method_name);
		}
		
		if(function_exists('icl_t')){
			$payment_method_name = icl_t(DOMAIN,$payment_method_name,$payment_method_name);
		}else{
			$payment_method_name = __($payment_method_name,DOMAIN); 
		}
		echo "<li><p class='submit_info_label'>". __('Payment Method',DOMAIN) .": </p> <p class='submit_info_detail'> ".@$payment_method_name."</p></li>";
	}
	if(function_exists('icl_register_string')){
			icl_register_string(DOMAIN,$status,$status);
		}
		
		if(function_exists('icl_t')){
			$status = icl_t(DOMAIN,$status,$status);
		}else{
			$status = __($status,DOMAIN); 
		}
	echo "<li><p class='submit_info_label'>". __('Status',DOMAIN) .": </p> <p class='submit_info_detail'> ".$status."</p></li>";
}
/*
name : insert_transaction_detail
description : insert transaction detail in transaction table.
*/
function insert_transaction_detail($paymentmethod='',$last_postid,$is_upgrade=0,$is_package=0,$is_featured_h=0,$is_featured_c=0,$is_category=0)
{
		/* Transaction Report */
		global $wpdb,$payable_amount,$current_user;
		if($payable_amount==''){
			$payable_amount='0';
		}
		if($is_upgrade == 1)
		{
			$post_details=get_post_meta($last_postid,'upgrade_data',true);
			$package_select=$post_details['package_select'];
		}else
		{
			$package_select=get_post_meta($last_postid,'package_select',true);
		}
		
		$package_select=($package_select)?$package_select:$_POST['pkg_id'];

		$post_author  = $wpdb->get_row("select * from $wpdb->posts where ID = '".$last_postid."'") ;
		$post_title  = $post_author->post_title ;
		$post_author  = ($post_author->post_author)? $post_author->post_author : $current_user->ID  ;
		$uinfo = get_userdata($post_author);
		$user_fname = $uinfo->display_name;
		$user_email = $uinfo->user_email;
		$user_billing_name = $uinfo->display_name;
		
		$billing_Address = '';
		if($paymentmethod == "")
		 {
			$paymentmethod = "-"; 
		 }
		global $transection_db_table_name;
		if(is_admin()){
			$status =1;
		}else{
			if(get_post_status( $last_postid ) == 'publish' && $payable_amount <=0)
				$status = 1;
			else
				$status = 0;
		}
		$transection_db_table_name=$wpdb->prefix.'transactions';
		$transaction_insert = 'INSERT INTO '.$transection_db_table_name.' set 
			post_id="'.$last_postid.'",
			user_id = "'.$post_author.'",
			post_title ="'.$post_title.'",
			payment_method="'.$paymentmethod.'",
			payable_amt='.$payable_amount.',
			payment_date="'.date("Y-m-d H:i:s").'",
			paypal_transection_id="",
			status="'.$status.'",
			user_name="'.$user_fname.'",
			pay_email="'.$user_email.'",
			billing_name="'.$user_billing_name.'",
			billing_add="'.$billing_Address.'",
			package_id="'.$package_select.'",
			payforpackage="'.$is_package.'",
			payforfeatured_h="'.$is_featured_h.'",
			payforfeatured_c="'.$is_featured_c.'",
			payforcategory="'.$is_category.'"';		
		

		$wpdb->query($transaction_insert);
		return $wpdb->insert_id;
		/* End Transaction Report */
}
/*
name : get_payment_method
description : fetch payment method name.
*/
function get_payment_method($method)
{
	global $wpdb;
	$paymentsql = "select * from $wpdb->options where option_name like 'payment_method_$method'";
	$paymentinfo = $wpdb->get_results($paymentsql);
	if($paymentinfo)
	{
		foreach($paymentinfo as $paymentinfoObj)
		{
			$paymentInfo = unserialize($paymentinfoObj->option_value);
			return $paymentInfo['name'];
		}
	}
}
/*
name : get_order_detailinfo_tableformat
description : fetch order information as a table format.
*/
function get_order_detailinfo_transaction_report($orderId,$isshow_paydetail=0)
{
	global $wpdb,$prd_db_table_name,$transection_db_table_name;
	$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
	$orderinfo = $wpdb->get_results($ordersql);
	$orderinfo = $orderinfo[0];
	$post_id = $orderinfo->post_id;
	$package_select_id = get_post_meta($post_id,'package_select',true);
	$package_select_name = get_the_title($package_select_id);
	$coupon_code = get_post_meta($post_id,'coupon_code',true);	
	$alive_days = get_post_meta($post_id,'alive_days',true);	
	$message = '';
	if($isshow_paydetail)
	{
		
		$message .= '<style>.address_info {width:400px;}</style>';
	}
	$message .='
			
					<div class="order_info">
						<p> <span class="span"> '. __('Transaction ID',DOMAIN).' </span> : <span class="trans_strong">'.$orderinfo->trans_id.'  </span></p> 
						<p><span class="span"> '. __('Transaction Date',DOMAIN).' </span> : <span class="trans_strong">'.date_i18n(get_option('date_format').' '.get_option('time_format'),strtotime($orderinfo->payment_date)).'</span> </p>';
								if(!$alive_days)
								{
									$publishdate 	= get_post($post_id);
									$publish_date 	= strtotime($publishdate->post_date);
									$publish_date 	= date_i18n('Y-m-d',$publish_date);
									$expired_date 	= date_i18n(get_option("date_format"),strtotime($publish_date. "+$alive_days day"));
									$time_formate=get_option('time_format');
									$end_time = '';
									if(get_post_meta($post_id,'st_time',true))
									{
										$end_time = ' '.date($time_formate,strtotime(get_post_meta($post_id,'st_time',true)));
									}
									$message .='<div class="checkout_address" >
													<div class="address_info address_info2 fr">
														<p> <span class="span"> '. __('Expiry Date',DOMAIN).' </span> :  <span class="trans_strong">'.$expired_date.$end_time.'</span>  </p>
													</div>
												</div>';
								}
							$message .='<p><span class="span">'. __('Transaction Status',DOMAIN) .'</span>  : <span class="trans_strong">'. tmpl_get_transaction_status($orderinfo->trans_id,$orderinfo->post_id).'</span> </p>
					</div> <!--order_info -->
					<div class="checkout_address" >
						<div class="address_info address_info2 fr">
							<p> <span class="span"> '. __('Payment Method',DOMAIN).' </span> : <span class="trans_strong">'.get_payment_method($orderinfo->payment_method).'</span>  </p>
						</div>
					</div>
				';
				if($coupon_code)
				{
				$message .='<tr>
						<td align="left" valign="top" colspan="2">
							<div class="checkout_address" >
								<div class="address_info address_info2 fr">
									<h3> '. __('Coupon Code',DOMAIN).'  </h3>									
									<div class="address_row"><span class="trans_strong">'.$coupon_code.'</span>  </div>
								</div>
							</div><!-- checkout Address -->
						 </td>
					</tr>';
				}
				
			
		return $message;
}
/*
name : get_order_detailinfo_tableformat
description : fetch order information as a table format.
*/
function get_order_detailinfo_price_package($orderId,$isshow_paydetail=0)
{
	global $wpdb,$prd_db_table_name,$transection_db_table_name;
	$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
	$orderinfo = $wpdb->get_results($ordersql);
	$orderinfo = $orderinfo[0];

	$package_select_id = $orderinfo->package_id;

	$is_category_featured = get_post_meta($package_select_id,'is_category_featured',true);
	$feature_cat_amount = get_post_meta($package_select_id,'feature_cat_amount',true);

	$is_home_featured = get_post_meta($package_select_id,'is_home_featured',true);
	$feature_amount = get_post_meta($package_select_id,'feature_amount',true);
	
	$package_type = get_post_meta($package_select_id,'package_type',true);
	$message = '';
	$recurring = get_post_meta($package_select_id,'recurring',true);
	$package_select_name = get_the_title($package_select_id);
	
	$message .='<div class="checkout_address" >
					<div class="address_info address_info2 fr">
						<p> <span class="span"> '. __('Package',DOMAIN).' </span> : <span class="trans_strong">'.$package_select_name.'</span>  </p>
					</div>
				</div>';
	if($package_type ){
		if($package_type ==1){
			$message .='<div class="order_info">
						<p> <span class="span"> '. __('Package Type',DOMAIN).' </span> : <span class="trans_strong">'.__('Single Submission',DOMAIN).'</span> </p>
					</div>';
		}else{
			$message .='<div class="order_info">
						<p> <span class="span"> '. __('Package Type',DOMAIN).' </span> : <span class="trans_strong">'.__('Subscription',DOMAIN).'</span> </p>
					</div>';
		}
	}
	if($recurring)
	{
		$message .='<div class="order_info">
						<p> <span class="span"> '. __('Recurring',DOMAIN).' </span> : <span class="trans_strong">'.__('Yes',DOMAIN).'</span> </p>
					</div>
					<div class="order_info">
						<p> <span class="span"> '. __('Recurring Price',DOMAIN).' </span> : <span class="trans_strong">'.fetch_currency_with_position(get_post_meta($post_id,'paid_amount',true)).'  </span></p>
					</div>
					';
	}
	/* package have home page featured or not */
	if($is_home_featured)
	{
		$message .='<div class="order_info">
						<p> <span class="span"> '. __('Featured for home page',DOMAIN).' </span> : <img src="'.TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/images/icon-yes.png" /> </p>
					</div>';
	}elseif($feature_amount !=''){
		$message .='<div class="order_info">
						<p> <span class="span"> '. __('Featured for home page',DOMAIN).' </span> : '.display_amount_with_currency_plugin($feature_amount).' </p>
					</div>';
	}
	
	/* package have category page featured or not */
	if($is_category_featured)
	{
		$message .='<div class="order_info">
						<p> <span class="span"> '. __('Featured for category page',DOMAIN).' </span> : <img src="'.TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/images/icon-yes.png" /> </p>
					</div>';
	}elseif($feature_cat_amount !=''){
		$message .='<div class="order_info">
						<p> <span class="span"> '. __('Featured for category page',DOMAIN).' </span> : '.display_amount_with_currency_plugin($feature_cat_amount).' </p>
					</div>';
	}
	
	if(is_plugin_active('thoughtful-comments/fv-thoughtful-comments.php'))
	{
		if(get_post_meta($post_id,'author_moderate',true) == 1)
		{
			$message .='<div class="order_info">
						<p> <span class="span"> '. __('User caon moderate comment',DOMAIN).' </span> : <img src="'.TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/images/icon-yes.png" /> </p>
					</div>';
		}
	}
	return $message;
}
/*
name : get_order_detailinfo_tableformat
description : fetch order information as a table format.
*/
function get_order_detailinfo_tableformat($orderId,$isshow_paydetail=0)
{
	global $wpdb,$prd_db_table_name,$transection_db_table_name;
	$transection_mng_table = $wpdb->prefix."users_packageperlist";
	$trans_details = "select subscriber_id from $transection_mng_table where trans_id=\"$orderId\"";
	
	
	$subscriber_details = $wpdb->get_var($trans_details);
	$subscriber_id = $subscriber_details;
	if(@$subscriber_id != '')
	{
		$ordersql = "select * from $transection_mng_table where subscriber_id=\"$subscriber_id\"";
	}
	else
	{
		$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
	}
	$orderinfo = $wpdb->get_results($ordersql);
	$post_id = $orderinfo[0]->post_id;
	$post_type = get_post($post_id);
	$package_select_id = get_post_meta($post_id,'package_select',true);
	$package_select_name = get_the_title($package_select_id);
	$coupon_code = get_post_meta($post_id,'coupon_code',true);	
	$message = '';
	if($isshow_paydetail)
	{
		
		$message .= '<style>.address_info {width:400px;}</style>';
	}

		$message .='<table width="100%" class="table widefat post" ><thead>
			<tr>
				<th width="5%" align="left" class="title" > '. __('Image',DOMAIN).'</th>
				<th width="25%" align="left" class="title" >'. __('Title',DOMAIN).'</th>
				<th width="20%" align="left" class="title" > '. __('Submitted by',DOMAIN).'</th>
				<th width="15%" align="left" class="title" > '. __('Payment Method',DOMAIN).'</th>
				<th width="10%" align="left" class="title" > '. __('For Category',DOMAIN).'</th>
				<th width="10%" align="left" class="title" > '. __('Featured On Home Page',DOMAIN).'</th>
				<th width="10%" align="left" class="title" > '. __('Featured On Category Page',DOMAIN).'</th>
				<th width="15%" align="left" class="title" >'. __('Total Price',DOMAIN).'</th>
			</tr></thead>';
		
		$c=0;
		foreach($orderinfo as $oi){
			$c++;
			if($oi->post_id !=0){
				$product_image_arr = bdw_get_images_plugin($oi->post_id,'thumb');
				$product_image = @$product_image_arr[0]['file'];
				if(!$product_image ){
					$product_image  = TEVOLUTION_PAGE_TEMPLATES_URL ."tmplconnector/monetize/images/no-image.png";
				}
				$post = get_post($oi->post_id);
				$trans_id = $oi->trans_id;

				$trans_details = $wpdb->get_row("select * from $transection_db_table_name where trans_id=\"$trans_id\"");
				if($trans_details->payforcategory ==1){
					$pfc = '<img src="'.TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/images/icon-yes.png" />';
				}else{
					$pfc = "-";
				}
				/* pay for home */
				if($trans_details->payforfeatured_h ==1){
					$pffh = '<img src="'.TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/images/icon-yes.png" />';
				}else{
					$pffh = "-";
				}
				
				/* pay for featured on category */
				if($trans_details->payforfeatured_c ==1){
					$pffc = '<img src="'.TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/images/icon-yes.png" />';
				}else{
					$pffc = "-";
				}
				if($c%2 == 0 || $c==0){ $class="alternate"; }else{ $class=""; }
				
				$message .= '<tr class="'.$class.'">
								<td class="row1"><a href="'.get_permalink($post->ID).'"><img src="'.$product_image.'" width=60 height=60 /></a></td>
								<td class="row1" ><a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></td>
								<td class="row1 tprice"  align="left">'.$trans_details->user_name.'</td>
								<td class="row1 tprice"  align="left">'.$trans_details->payment_method.'</td>
								<td class="row1 tprice"  align="left">'.$pfc.'</td>
								<td class="row1 tprice"  align="left">'.$pffh.'</td>
								<td class="row1 tprice"  align="left">'.$pffc.'</td>
								<td class="row1 tprice"  align="left">'.fetch_currency_with_position($trans_details->payable_amt,2).'</td>
							</tr>';
			}
		}
		$message .='</table>';
	if($post_id  !='' || $post_id  !=0){
		return $message;
	}else{
		return '';
	}
}
function get_order_user_info($orderId,$isshow_paydetail=0)
{
	global $wpdb,$prd_db_table_name,$transection_db_table_name;
	$ordersql = "select * from $transection_db_table_name where trans_id=\"$orderId\"";
	$orderinfo = $wpdb->get_results($ordersql);
	$orderinfo = $orderinfo[0];
	$post_id = $orderinfo->post_id;
	$message = '';
	$message .='
				<div class="trans_avatar">
					<div class="order_info">
						<p> <span class="span"> '. get_avatar($orderinfo->pay_email, 75 ).'  </p>
					</div>
				</div>
				<div class="trans_user_info">
					<div class="order_info">
						<p> <span class="span"> '. __('Username',DOMAIN).' </span> : <span class="trans_strong">'.$orderinfo->user_name.'</span> </p>
					</div>
					<div class="order_info">
						<p> <span class="span"> '. __('User Email',DOMAIN).' </span> : <span class="trans_strong">'.$orderinfo->pay_email.'</span> </p>
					</div>
				</div>';
	return $message;
}
add_action('admin_init','post_price_package');
function post_price_package($post){
	global $post,$post_type,$post_id;
	if(!$post && isset($_GET['post']) && $_GET['post']!=''){ $post = get_post($_GET['post']); }
	
	if($post){
		$post_type = $post->post_type;
		$post_id = $post->ID; }
	$package_select=get_post_meta(@$post_id,'package_select',true);
	if($package_select!='' && $package_select!=0 && $post_type!='page'){
		add_meta_box("package_details", "Package Details", "price_package_meta_box",$post_type, "side", "high");
	}
}

function price_package_meta_box(){
	global $post;
	$package_id=get_post_meta($post->ID,'package_select',true);
	$alive_days=get_post_meta($post->ID,'alive_days',true);
	$featured_c=(get_post_meta($post->ID,'featured_c',true)=='c')? ''.__('Yes',DOMAIN) : ''.__('No',DOMAIN);	
	$featured_h=(get_post_meta($post->ID,'featured_h',true)=='h')? ''.__('Yes',DOMAIN) : ''.__('No',DOMAIN);	
	if(function_exists('fetch_currency_with_position'))
	{
		$paid_amount=fetch_currency_with_position(get_post_meta($post->ID,'paid_amount',true));
	}
	
	$package_name=get_the_title($package_id);
	?>
     <p><label><?php echo __('Package Name: ',ADMINDOMAIN);?></label><strong><?php echo $package_name;?></strong></p>
     <p><label><?php echo __('Total Amount: ',ADMINDOMAIN);?></label><strong><?php echo $paid_amount;?></strong></p>
     <p><label><?php echo __('Alive Days: ',ADMINDOMAIN);?></label><strong><?php echo $alive_days;?></strong></p>
     <p><label><?php echo __('Featured for home page? : ',ADMINDOMAIN);?></label><strong><?php echo $featured_h;?></strong></p>
     <p><label><?php echo __('Featured for category page? : ',ADMINDOMAIN);?></label><strong><?php echo $featured_c;?></strong></p>
    <?php
}
/*
 * Function Name: tevolution_price_package_order
 * Return: sort ordering of price package
 */
add_action('wp_ajax_price_package_order','tevolution_price_package_order');
function tevolution_price_package_order(){
	
	$user_id = get_current_user_id();	
	if(isset($_REQUEST['paging_input']) && @$_REQUEST['paging_input']!=0 && @$_REQUEST['paging_input']!=1){
		$package_per_page=get_user_meta($user_id,'package_per_page',true);
		$j = @$_REQUEST['paging_input']*$package_per_page+1;
		$test='';
		$i=$package_per_page;		
		for($j; $j >= count($_REQUEST['price_package_order']);$j--){
			if($_REQUEST['price_package_order'][$i]!=''){				
				wp_update_post(array('ID'=> @$_REQUEST['price_package_order'][$i],'menu_order'	=> $j,));
			}
			$i--;	
		}
	}else{
		$j=1;
		for($i=0;$i<count($_REQUEST['price_package_order']);$i++){
			wp_update_post(array('ID'=> @$_REQUEST['price_package_order'][$i],'menu_order'	=> $j,));
			$j++;
		}
	}
	exit;
}
/*
 * Function Name: get_transaction_detail
 * Return: get the transcation id 
 */
function get_transaction_detail($payment,$post_id){
	global $wpdb,$transection_db_table_name;
	$transection_db_table_name=$wpdb->prefix.'transactions';
	$trans_id = $wpdb->get_var("select trans_id from $transection_db_table_name where post_id = '".$post_id."' AND (package_type is NULL OR package_type=0)");
	return $trans_id;
}
/*
 * function that change the old price package is_featured option to is_home_featured and is_category_featured.
 */
add_action('admin_init','tmpl_change_is_featured_option');
function tmpl_change_is_featured_option()
{
	/*check whether price package is update or not*/
	if(get_option('update_price_package') != 'updated')
	{
		global $post,$wp_query,$monetization,$wpdb;
			$args=
			array( 
			'post_type' => 'monetization_package',
			'posts_per_page' => -1	,
			'post_status' => array('publish'),
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'is_featured',
					'value' =>  '1',
					'compare' => '='
					)
				)
			);
		$post_query = null;
		$post_query = new WP_Query($args);		
		$post_meta_info = $post_query;	
		$monetization = new monetization();	
		if($post_meta_info){
			while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
				$listing_price_info = $monetization->templ_get_price_info($post->ID);
				update_post_meta($post->ID,'is_home_page_featured',1);
				update_post_meta($post->ID,'is_category_page_featured',1);
				update_post_meta($post->ID,'home_page_alive_days',$listing_price_info[0]['alive_days']);
				update_post_meta($post->ID,'cat_page_alive_days',$listing_price_info[0]['alive_days']);
			endwhile;
		}
		update_option('update_price_package','updated');/*tmp variable set to check whether price package is update or not*/
	}
}
/*
 * ajax function to fetch the category for post type while add or editing price package
 */
add_action('wp_ajax_ajax_categories_dropdown','ajax_categories_dropdown');	
function ajax_categories_dropdown()
{
	
	$my_post_type = explode(",",$_REQUEST['post_type']);
	$result = '';
	$category_li = '';
	$result .= '<ul class="categorychecklist form_cat" data-wp-lists="list:listingcategory" id="category_checklist"><li>
		<input type="checkbox" name="selectall" id="selectall" class="checkbox" onclick="displaychk_frm();" />
		<label for="selectall">&nbsp;'. __('Select All',DOMAIN) .'</label>
	</li>';

	$pkg_id = $_REQUEST['package_id'];
	$scats = $_REQUEST['scats'];
	$pid = explode(',',$scats);
	
	/*tmpl_remove_terms_clauses filter use for remove wpml language filter in taxonomy terms clauses */
	$remove_terms_clauses=apply_filters('tmpl_remove_terms_clauses',array('monetization'));
	/*Remove stitepress terms claises filer for display all langauge wise category show  */
	if((isset($_REQUEST['page']) && in_array($_REQUEST['page'],$remove_terms_clauses)  ) && is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		global $sitepress;
		remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
	}
	
	if($_REQUEST['post_type'] == 'all' || $_REQUEST['post_type'] == 'all,')
	{
		$custom_post_types_args = array();
		
		$custom_post_types = get_option("templatic_custom_post");
		$result .= tmpl_get_wp_category_checklist_monetize_plugin($pkg_id, array( 'taxonomy' =>'category','popular_cats' => true,'selected_cats'=>$pid ) );
		foreach ($custom_post_types as $content_type=>$content_type_label) {
			$taxonomy = $content_type_label['slugs'][0];
			
			$result .= "<li><label style='font-weight:bold;'>".$content_type_label['taxonomies'][0]."</label></li>";
			$result .= tmpl_get_wp_category_checklist_monetize_plugin($pkg_id, array( 'taxonomy' =>$taxonomy,'popular_cats' => true,'selected_cats'=>$pid ) );
		}
	}
	else
	{
		
		$my_post_type = explode(",",substr($_REQUEST['post_type'],0,-1));	
		foreach($my_post_type as $_my_post_type)
		{
			if($_my_post_type!='all'){
				$taxonomy = get_taxonomy( $_my_post_type );
				$result .= "<li><label style='font-weight:bold;'>".$taxonomy->labels->name."</label></li>";
				$result .= tmpl_get_wp_category_checklist_monetize_plugin($pkg_id, array( 'taxonomy' =>$_my_post_type,'popular_cats' => true,'selected_cats'=>$pid ) );
			}
		}
	}
	$result .= '</ul>';
	echo $result;exit;
}

/**
 * Output an unordered list of checkbox <input> elements labelled
 * with term names. Taxonomy independent version of wp_category_checklist().
 *
 * @since 3.0.0
 *
 * @param int $post_id
 * @param array $args
 
Display the categories check box like wordpress - wp-admin/includes/meta-boxes.php
 */
function tmpl_get_wp_category_checklist_monetize_plugin($post_id = 0, $args = array()) {
	global  $cat_array;
	$category_result = '';
 	$defaults = array(
		'descendants_and_self' => 0,
		'selected_cats' => false,
		'popular_cats' => false,
		'walker' => null,
		'taxonomy' => 'category',
		'checked_ontop' => true
	);
	if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=""){
		$place_cat_arr = $cat_array;
		$post_id = $post_id;
	}

	$args = apply_filters( 'wp_terms_checklist_args', $args, $post_id );
	$template_post_type = get_post_meta($post->ID,'submit_post_type',true);
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );

	if ( empty($walker) || !is_a($walker, 'Walker') )
		$walker = new Tev_Walker_Category_Checklist_Backend;

	$descendants_and_self = (int) $descendants_and_self;

	$args = array('taxonomy' => $taxonomy);

	$tax = get_taxonomy($taxonomy);
	$args['disabled'] = !current_user_can($tax->cap->assign_terms);
	
	if ( is_array( $selected_cats ) )
		$args['selected_cats'] = $selected_cats;
	elseif ( $post_id )
		$args['selected_cats'] = wp_get_object_terms($post_id, $taxonomy, array_merge($args, array('fields' => 'ids')));
	else
		$args['selected_cats'] = array();

	if ( is_array( $popular_cats ) )
		$args['popular_cats'] = $popular_cats;
	else
		$args['popular_cats'] = get_terms( $taxonomy, array( 'get' => 'all', 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'hierarchical' => false ) );

	if ( $descendants_and_self ) {
		$categories = (array) get_terms($taxonomy, array( 'child_of' => $descendants_and_self, 'hierarchical' => 0, 'hide_empty' => 0 ) );
		$self = get_term( $descendants_and_self, $taxonomy );
		array_unshift( $categories, $self );
	} else {
		$categories = (array) get_terms($taxonomy, array('get' => 'all'));
	}

	if ( $checked_ontop ) {
		// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
		$checked_categories = array();
		$keys = array_keys( $categories );
		$c=0;
		foreach( $keys as $k ) {
			if ( in_array( $categories[$k]->term_id, $args['selected_cats'] ) ) {
				$checked_categories[] = $categories[$k];
				unset( $categories[$k] );
			}
		}

		// Put checked cats on top
		$category_result .= call_user_func_array(array(&$walker, 'walk'), array($checked_categories, 0, $args));
	}
	// Then the rest of them

	$category_result .= call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
	if(empty($categories) && empty($checked_categories)){

			$category_result .= '<span style="font-size:12px;float:left;color:red;">'.sprintf(__('You have not created any category for %s post type. So, this listing will be submited as uncategorized.',DOMAIN),$template_post_type).'</span>';
	}
	return $category_result;
}

/* function returns the number of days the listing will be published in particualr price package */
function tmpl_show_package_period($post_id)
{
	global $wpdb,$current_user;
	/*check price package is recurring or not*/
	$recurring = get_post_meta($post_id,'recurring',true);
	if($recurring ==1){
		$validity = get_post_meta($post_id,'billing_num',true);
		$vper = get_post_meta($post_id,'billing_per',true);
	}else{
		$vper = get_post_meta($post_id,'validity_per',true);
		$validity = get_post_meta($post_id,'validity',true);
	}
	
	if(($validity != "" || $validity != 0))
	{
		if($vper == 'M')
		{
			$tvalidity = $validity*30 ;
		}else if($vper == 'Y'){
			$tvalidity = $validity*365 ;
		}else{
			$tvalidity = $validity ;
		}
	}
	do_action('tmpl_before_success_price_package',$post_id);
	
	
	if(get_post_meta($post_id,'package_type',true) == 2)
	{ ?>
		<p class="panel-type price package_type"><?php echo '<label><b>'; _e('Package Type: ',DOMAIN); echo '</b></label>'; echo ' <span>'; _e('Subscription',DOMAIN); echo '</span>'; ?> </p>
        <p class="panel-type price package_type"><?php echo '<label>'; _e('Listing duration: ',DOMAIN); echo '</label>'; echo ' <span>';  echo $tvalidity; _e(" days",DOMAIN); echo '</span>'; ?> </p>
		<p class="margin_right panel-type price package_type"><?php echo '<label>'; _e('Number of listings included in the package: ',DOMAIN);echo '</label>'; echo '<span>'; echo get_post_meta($post_id,'limit_no_post',true); echo '</span>'; ?> </p>
       <?php  if(get_post_meta($post_id,'days_for_no_post',true) > 0)
	   {
			?>
           	<p class="margin_right panel-type price package_type"><?php echo '<label>'; _e('Listings can be submitted within: ',DOMAIN);echo '</label>'; echo '<span>'; echo get_post_meta($post_id,'days_for_no_post',true); echo '</span>'; ?> </p>
           <?php
	   }
	   if(get_post_meta($current_user->ID,'package_free_submission',true) > 0 && !isset($_REQUEST['page']) && !isset($_REQUEST['pmethod']) && get_user_meta($current_user->ID,'package_free_submission_completed',true) != 'completed' )
	   {
		   ?>
           	<p class="margin_right panel-type price package_type"><?php echo '<label>'; _e('Number of free submissions: ',DOMAIN);echo '</label>'; echo '<span>'; echo get_post_meta($current_user->ID,'package_free_submission',true);_e(' Submitted '); echo  get_post_meta($post_id,'subscription_days_free_trail',true); _e(' Left.'); echo '</span>'; ?> </p>
            <?php
	   }
	} 
	elseif(get_post_meta($post_id,'package_type',true) == 1)
	{ 	?>
		<p class="margin_right panel-type price package_type"><?php echo '<label>'; _e('Package Type: ',DOMAIN); echo '</label>'; echo '<span>'; _e('Single Submission',DOMAIN); echo '</span>'; ?> </p>
        <p class="panel-type price package_type"><?php echo '<label>'; _e('Listing duration: ',DOMAIN); echo '</label>'; echo ' <span>';  echo $tvalidity; _e(" day('s)",DOMAIN); echo '</span>'; ?> </p>
       <?php
	}
	$package_billing_num = get_post_meta($post_id,'billing_num',true);
	$package_billing_per =get_post_meta($post_id,'billing_per',true);
	$package_billing_cycle =get_post_meta($post_id,'billing_cycle',true);
	$first_free_trail_period =get_post_meta($post_id,'first_free_trail_period',true);
	$days_for_no_post =get_post_meta($post_id,'subscription_days_free_trail',true);
	if(get_post_meta($post_id,'recurring',true)=='1')
	{
	echo '<p class=""><label>'; _e('Recurring period',DOMAIN); echo ':&nbsp;</label><span>'.get_post_meta($post_id,'billing_num',true) ."&nbsp;";
	if($package_billing_per == 'D')
	{
		if($package_billing_num==1){ _e('Day',DOMAIN); }else{ _e('Days',DOMAIN); }
	}
	elseif($package_billing_per == 'M')
	{
		if($package_billing_num==1){ _e('Month',DOMAIN); }else{ _e("Months",DOMAIN); }
	}
	else
	{
		if($package_billing_num==1){ _e('Year',DOMAIN); }else{ _e('Years',DOMAIN); }
	}
	echo "</p>";
	echo '<p class=""><label>'; _e('Number of cycles',DOMAIN); echo ':&nbsp;</label><span>'.$package_billing_cycle."&nbsp;<p>";
	}
	if($first_free_trail_period==1 && !isset($_REQUEST['page']) && !isset($_REQUEST['pmethod'])){ 
	?>
	<p><?php _e('This price package will offer free trial period or plan to bill the first instalment of a recurring payment only for PayPal payment gateway.',DOMAIN);?></p>
	<?php }
	if($days_for_no_post>0 && !isset($_REQUEST['page']) && !isset($_REQUEST['pmethod']) && get_user_meta($current_user->ID,'package_free_submission_completed',true) != 'completed' ){ 
	?>
	<p class="margin_right"><?php echo '<label>'; _e('Number of free submissions: ',DOMAIN);echo '</label>'; echo '<span>'; echo $days_for_no_post; echo '</span>'; ?></p>
	<?php }
	
	do_action('tmpl_after_success_price_package',$post_id);
}

/* function returns the html for featured option that is included by default in price package */
function tmpl_show_package_included_featured_option($post_id)
{
	/* package is featured or not */
	$is_featured = get_post_meta($post_id,'is_featured',true); 
	if(is_plugin_active('thoughtful-comments/fv-thoughtful-comments.php')){
		$author_can_moderate_comment = get_post_meta($post_id,'can_author_mederate',true);
	}
	
		/*home page featured amount*/
		$featured_h = get_post_meta($post_id,'feature_amount',true); 
		/*category page featured amount*/
		$featured_c = get_post_meta($post_id,'feature_cat_amount',true); 
		/*is price package amount includes the home page featured amount*/
		$is_home_featured = get_post_meta($post_id,'is_home_featured',true);
		$is_home_featured_checked = '';
		$is_home_featured_disabled = '';
		$is_home_featured_text = (get_post_meta($post_id,'is_home_featured',true))?__('Homepage',DOMAIN):__('Yes &sbquo; feature this listing on homepage',DOMAIN);
		
		/*is price package amount includes the category page featured amount*/
		$is_category_featured = get_post_meta($post_id,'is_category_featured',true); 
	
		$is_category_featured_text = (get_post_meta($post_id,'is_category_featured',true))?__('Category page',DOMAIN):__('Yes &sbquo; feature this listing on categorypage',DOMAIN);
		if($is_home_featured || $is_category_featured)
		{
	 ?>
            <p><label><strong><?php _e('Listings submitted with this package will be automatically featured on: ',DOMAIN);?></strong></label>
            <?php
            if($is_home_featured && $is_category_featured){
            ?>
            	<span><?php _e('Homepage and Category page',DOMAIN); ?> </span>
            <?php
            }
            
            if($is_home_featured && !$is_category_featured)
            { ?>
           		<span><?php echo $is_home_featured_text; ?> </span>
            <?php
            }
            if($is_category_featured && !$is_home_featured)
            { ?>
            	<span><?php echo $is_category_featured_text; ?></span>
            <?php } ?>
            </p>
            <span id='process' style='display:none;'><i class="fa fa-circle-o-notch fa-spin"></i></span>
                        
	<?php
	}
}
/* include js for price calculation while listing submission*/
add_action('wp_enqueue_scripts', 'calculate_price_package');
function calculate_price_package() {
	global $pagenow,$post;
	if (@$pagenow == 'post.php' || @$pagenow == 'post-new.php' || @get_post_meta($post->ID,'is_tevolution_submit_form',true) == 1 || @get_post_meta($post->ID,'is_tevolution_upgrade_form',true) == 1) {
        wp_register_script('calculate_package_price', TEVOLUTION_PAGE_TEMPLATES_URL.'tmplconnector/monetize/templatic-monetization/js/calculate_package_price.js','','',true);
        wp_enqueue_script('calculate_package_price');
    }  
}
/* 
 * Display category as per price package.
 */
add_action( 'wp_ajax_nopriv_tmpl_tevolution_select_pay_per_subscription_price_package','tmpl_tevolution_select_pay_per_subscription_price_package');
add_action( 'wp_ajax_tmpl_tevolution_select_pay_per_subscription_price_package' ,'tmpl_tevolution_select_pay_per_subscription_price_package');
function tmpl_tevolution_select_pay_per_subscription_price_package(){
	global $current_user,$post,$monetization;
	$post_type = $_REQUEST['submit_post_type'];
	$result = '';
	$result .= $monetization->tmpl_fetch_is_single_price_package($current_user->ID,$post_type,$post->ID);/*fetch the price package*/
	echo $result;exit;
}

/*
 * Insert/update user package per list 
 * This function save users package information per post submited
 */
function insert_update_users_packageperlist($post_id,$_post,$trans_id){
	global $wpdb,$current_user,$monetization;
	if($_post['package_select']==''){
		return;	
	}
	$users_packageperlist=$wpdb->prefix.'users_packageperlist';
	$subscriber_id=rand().strtotime(date('Y-m-d'));
	$listing_price_info = $monetization->templ_get_price_info($_post['pkg_id']);
	
	if($listing_price_info[0]['package_type']==2){
		/*Get the active selected package id user wise  */
		$sql="SELECT * FROM $users_packageperlist WHERE user_id=".$current_user->ID." AND 	package_id=".$_post['package_select']." AND status=1";		
		$results=$wpdb->get_results($sql);
		
		/*Get the existing user subscriber id */
		if($results[0]->subscriber_id!= '')
			$subscriber_id=$results[0]->subscriber_id;
			
		$packageperlist_insert = "INSERT INTO ".$users_packageperlist." set 
			user_id = ".$current_user->ID.",
			post_id =".$post_id.",
			package_id =".$_post['package_select'].",
			trans_id=".$trans_id.",
			subscriber_id='".$subscriber_id."',
			date='".date("Y-m-d")."',
			status=1";
	}
	else
	{
		$packageperlist_insert = "INSERT INTO ".$users_packageperlist." set 
			user_id = ".$current_user->ID.",
			post_id =".$post_id.",
			package_id =".$_post['package_select'].",
			trans_id=".$trans_id.",
			subscriber_id='".$subscriber_id."',
			date='".date("Y-m-d")."',
			status=1";
	}
	$wpdb->query($packageperlist_insert);	
}

/*
 * On an early action hook, check if the hook is scheduled - if not, schedule it.
 */
add_action( 'init', 'tevolution_daily_schedule_expire_featured_option' );

function tevolution_daily_schedule_expire_featured_option() {	
	if ( ! wp_next_scheduled( 'daily_schedule_featured_option' ) ) {		
		wp_schedule_event( time(), 'daily', 'daily_schedule_featured_option');
	}
}
add_action( 'daily_schedule_featured_option', 'do_daily_schedule_featured_option' );
/*
 * check whether the listing expire the home or category page featured option
 */
if(!function_exists('do_daily_schedule_featured_option'))
{
	function do_daily_schedule_featured_option()
	{
		$post_type = tevolution_get_post_type();
		global $post,$wp_query,$monetization;
		/*query to fetch all the post with tevolution post type*/
			$args=
			array( 
			'post_type' => $post_type,
			'posts_per_page' => -1	,
			'post_status' => array('publish'),
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'featured_c',
					'value' =>  'c',
					'compare' => 'LIKE'
					),
				array(
					'key' => 'featured_h',
					'value' =>  'h',
					'compare' => 'LIKE'
					)
				)
			);
		$post_query = null;
		$post_query = new WP_Query($args);		
		$post_meta_info = $post_query;	
		if($post_meta_info){
			while ($post_meta_info->have_posts()) : $post_meta_info->the_post();
				/*select the post package id*/
				$package_select = get_post_meta($post->ID,'package_select',true);
				/*select the post date*/
				$post_date = strtotime($post->post_date);
				/*set the currnet date*/
				$current_date = strtotime(date_i18n('Y-m-d G:i:s'));
				/*get the difference between of current date and package alive date*/
				$day_diff = floor(($current_date - $post_date) / (60 * 60 * 24));
				/*if the difference between of current date and package alive date is greater than home page alive days of tha price package for that particular post */
				$home_page_alive_days = get_post_meta($package_select,'home_page_alive_days',true);
				if($day_diff > $home_page_alive_days && $home_page_alive_days !='')
				{
					/*set home page featured option*/
					if(get_post_meta($post->ID,'featured_h',true) != '')
						update_post_meta($post->ID,'featured_h','');
					/*set featured_type option to category page featured if cat page featured alive days is not expired*/
					if(get_post_meta($post->ID,'featured_c',true) == 'c')
					{
						update_post_meta($post->ID,'featured_type','c');
					}
					else
					{
						update_post_meta($post->ID,'featured_type','');
					}
				}
				/*if the difference between of current date and package alive date is greater than category page alive days of tha price package for that particular post */
				$cat_page_alive_days = get_post_meta($package_select,'cat_page_alive_days',true);
				if($day_diff > $cat_page_alive_days && $cat_page_alive_days != '')
				{
					/*set category page featured option*/
					if(get_post_meta($post->ID,'featured_c',true) != '')
						update_post_meta($post->ID,'featured_c','');
					/*set featured_type option to home page featured if home page featured alive days is not expired*/
					if(get_post_meta($post->ID,'featured_h',true) == 'h')
					{
						update_post_meta($post->ID,'featured_type','h');
					}
					else
					{
						update_post_meta($post->ID,'featured_type','');
					}
				}
			endwhile;
			wp_reset_query();
			wp_reset_postdata();
		}
	}
}
/*
* display price package htnl on submit form page.
*/
function tmpl_display_package_html($post,$post_type='')
{
	global $current_user,$transaction_table,$wpdb;
	$transaction_table = $wpdb->prefix . "transactions";
	/*Check user submited price package subscription */
	$package_id=get_user_meta($current_user->ID,'package_selected',true);// get the user selected price package id
	if(!$package_id)
		$package_id=get_user_meta($current_user->ID,$post_type.'_package_select',true);// get the user selected price package id
	$user_limit_post=get_user_meta($current_user->ID,$post_type.'_list_of_post',true); //get the user wise limit post count on price package select
	if($user_limit_post==''){
		$user_limit_post='0';
	}
	if(isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg']==1)
	{
		$pkg_id = get_post_meta($_REQUEST['pid'],'package_select',true);
	}
	
	
	$package_post_type = explode(",",get_post_meta($package_id,'package_post_type',true));
	$user_package = get_user_meta($current_user->ID,$post_type.'_package_select',true);
	/*check selected package */
	if($user_package){
		$transaction_status = $wpdb->get_results("SELECT status,package_id FROM $transaction_table where payforpackage=1 AND user_id=".$current_user->ID." AND package_id =".$user_package." order by trans_id DESC LIMIT 1");
	}
			$package_disable_class = '';
			$trans_status=@$transaction_status[0]->status;
			$trans_package_id=@$transaction_status[0]->package_id;
			if(count($transaction_status)!=0 && $trans_status==0 &&  $post->ID == $trans_package_id && get_post_meta($post->ID,'package_type',true) == 2 ){
				$package_disable_class = 'overlay_opacity';
			}
			$class = '';
			if(is_admin())
			{
				$package_select = get_post_meta($_REQUEST['post'],'package_select',true);
				if($post->ID == $package_select)
				{
					$class = 'selected';
				}
			}
			
			if (function_exists('icl_register_string')) {	
				icl_register_string('tevolution-price', 'package-name'.$package_desc,$post->post_title);			
				$post->post_title = icl_t('tevolution-price', 'package-name'.$post->ID,$post->post_title);
				$post->post_content = icl_t('tevolution-price', 'package-desc'.$post->ID,$post->post_content);
		  	}	
	?>
  		<div class="packageblock clearifx <?php echo $package_disable_class; ?>">
            <ul data-price="<?php echo get_post_meta($post->ID,'package_amount',true); ?>"  <?php if(get_user_meta($current_user->ID,'upgrade',true) != 'upgrade' && get_post_meta($post->ID,'subscription_days_free_trail',true) >  get_post_meta($current_user->ID,'package_free_submission',true) ) { ?> data-free="<?php echo get_post_meta($post->ID,'subscription_days_free_trail',true); ?>" <?php } ?> data-subscribed='0' data-id="<?php echo $post->ID; ?>" data-type="<?php echo get_post_meta($post->ID,'package_type',true); ?>" <?php if(get_post_meta($post->ID,'subscription_as_pay_post',true)) { ?> data-post="<?php echo get_post_meta($post->ID,'subscription_as_pay_post',true); ?>" <?php } ?> class="packagelistitems <?php echo $class; ?>" >
                <li>
                    <div class="col-md-3 col-sm-6">
                		<div class="panel panel-default text-center">
                            <div class="panel-heading">
                                <h3><?php echo $post->post_title; ?></h3>
                            </div>
                             <?php
							 if(count($transaction_status)!=0 && $trans_status==0 &&  $post->ID == $trans_package_id && get_post_meta($post->ID,'package_type',true) == 2 ){
								if($current_user->ID )// check user wise post per  Subscription limit number post post 
								{		
									/*Only get the pay per subscription package id from postmeta */
									$package_id_sql= "SELECT post_id from {$wpdb->prefix}postmeta where meta_key='package_type' AND meta_value=2";
									/*Get the user last transaction  */
									$transaction_status = $wpdb->get_results("SELECT status,package_id FROM $transaction_table where payforpackage=1 AND user_id=".$current_user->ID." AND package_id in(".$package_id_sql.") order by trans_id DESC LIMIT 1");
									$trans_status=$transaction_status[0]->status;
									$trans_package_id=$transaction_status[0]->package_id;
									if(count($transaction_status)!=0 && $trans_status==0 && in_array($post_type,$package_post_type) ){				
										$admin_email=get_option('admin_email');
										echo sprintf(__('You have subscribed to this package but your transaction is not approved yet. Please %s contact%s the administrator of the site for more details.',DOMAIN),'<a style="position:relative;z-index:1;color:#0165bd;" href="mailto:'.$admin_email.'">','</a>');
									}
									
									$post_types = explode(',',get_post_meta($package_id,'package_post_type',true)); 
									if(in_array($post_type,$post_types)): $is_posttype_inpkg=1; else: $is_posttype_inpkg=0; endif; // check is this taxonomy included in package or not
								}
							 }
							?>
                  			<div class="panel-desc">
                        		<div class="panel-body">
                                    <span class="panel-title price"><?php  echo "<b>"; _e('Price: ',DOMAIN); echo "</b>".display_amount_with_currency_plugin(get_post_meta($post->ID,'package_amount',true)); ?></span> 
                                    <span class="days">
                                        <?php 
                                            /*show particular price package period or days*/
                                            tmpl_show_package_period($post->ID);
                                        ?>
                                    </span>
                                     <?php
                                        /*show particular price package includes fetured options*/
                                        echo tmpl_show_package_included_featured_option($post->ID);
                                    ?>
                            <!-- package description -->
                                    <div class="moreinfo">
                                        <?php  echo $post->post_content; ?>
                                    </div> 
                                </div> <!-- panel-body -->
                                <div class="pkg-button">
                                    <a data-id="<?php echo $post->ID; ?>"  class="btn btn-lg btn-primary button select-plan"><?php _e('Select',DOMAIN); ?></a>
                                </div> <!-- list-group -->
                    		</div><!-- panel-desc -->
               			</div> <!-- panel panel-default -->         
                	<!-- package description -->
            		</div><!-- packages block div closed here -->
                </li>
            </ul>
        </div>  
    <?php
}
/*return the count of price package.to check whether price package is enable or not.*/
function is_price_package($user_ID,$post_type,$post_isID)
{
	global $post,$wp_query,$current_user,$wpdb;
	$transaction_tabel = $wpdb->prefix . "transactions";
	/*query to fetch all the enabled price package*/
	$args=array('post_type' => 'monetization_package',
				'posts_per_page' => -1	,
				'post_status' => array('publish'),
				'meta_query' => array('relation' => 'AND',
								  array('key' => 'package_status',
										'value' =>  '1',
										'compare' => '='
										),
								  array('key' => 'package_post_type',
										'value' =>  $post_type,
										'compare' => 'LIKE'
										)
							),
				'orderby' => 'menu_order',
				'order' => 'ASC'
		);
	$post_query = null;
	$post_query = new WP_Query($args);	
	return count($post_query->posts);
}
?>