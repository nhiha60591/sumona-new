<?php
/* Event Manager functions - event_manage)tickets.php */
/* add action with woocommerce only */
if(is_plugin_active('woocommerce/woocommerce.php')){
	add_action('admin_init','_event_manager_woocommerce_compatitbility');
	add_action('tmpl_detail_page_custom_fields_collection','woocommerce_event_ticket_field',12);	
}
/*
Function Name : woocommerce_compatitbility
Description : add meta box in add event page if woocommerce is activated
*/
function _event_manager_woocommerce_compatitbility(){
	if(is_admin()){
		add_meta_box( 'woocommerce_templatic_prds', __('Select Events Ticket',EDOMAIN), 'event_ticket_woocommerce_templatic', CUSTOM_POST_TYPE_EVENT, 'side', 'core', '');		
		add_action('save_post','woocommerce_templatic_event_save');
		
	}		
}
function event_ticket_woocommerce_templatic($post_id){
	global $wpdb,$post_id;
	$get_prds = get_posts(array('post_type'=>'product','posts_per_page' => -1));
	$prd_id = get_post_meta($post_id,'templ_event_ticket',true);
	$templ_event_ticket_ids = get_post_meta($post_id,'templ_event_ticket_ids',true);
	
	echo "<div style='margin:0px 0px 15px 10px;'>";
	echo "<select name='event_ticket_for' id='event_ticket_for' class='clearfix' style='padding:2px;  width:80%;'>";
	echo "<option value=''>".__('Select a ticket.',EDOMAIN)."</option>";
	foreach($get_prds as $event_d){
		setup_postdata($event_d);
		if(trim($prd_id) == $event_d->ID){ $selected = 'selected=selected';}else{ $selected='';}
		echo "<option value='".$event_d->ID."' $selected>".$event_d->post_title."</option>";	
	}
	echo "</select>";
	echo "<div class='clearfix'></div><div class='clearfix'></div><br/>";
	$total_tickets = explode(',',$templ_event_ticket_ids);
	$booked_tickets = get_post_meta($post_id,'templ_event_ticket_booked',true);
	if($booked_tickets){ $booked_tickets = explode(',',$booked_tickets);}
	if($templ_event_ticket_ids !=''){ // display generated ticket id 
		$available_tckts = get_post_meta($prd_id,'_stock',true);
		echo "<b>".$available_tckts."</b> "; _e('tickets are available.',EDOMAIN); echo "<br/>";
	}
	echo "</div>";
	echo '<p class="description">'.__('It helps you to connect your product with your event and it will appear on your event detail page. (e.g. Creating event tickets and select it here in your event)<br><strong>Note:</strong> It will appear only when you have WooCommerce plugin activated.',EDOMAIN).'</p>';
}
/*
Function Name : woocommerce_templatic_events_save
Description : save events of tickets 
*/
function woocommerce_templatic_event_save($post_id){
	global $wpdb,$post_id;
	$prd_id =  @$_POST['event_ticket_for'];
	$booked_tickets =  @$_POST['templ_event_ticket_booked'];
	if($booked_tickets){
		$booked_tickets =  implode(',',$_POST['templ_event_ticket_booked']);
	}
	$qty = get_post_meta($prd_id,'_stock',true);
	if($qty !=''){
		for($i=1 ; $i <= $qty; $i++){
			$tickets .= "E".$post_id.$i.",";
		}
	} 
	update_post_meta($post_id,'templ_event_ticket',$prd_id);
	update_post_meta($prd_id,'templ_prd_for_ticket',$post_id);// update product to set the event for the product
	update_post_meta($post_id,'templ_event_ticket_ids',@$tickets); // total tickets generated
	update_post_meta($post_id,'templ_event_ticket_booked',$booked_tickets); // booked tickets
}
/*
	Function Name: woocommerce_event_ticket_field
	Description: Woocommerce event tickets
*/
function woocommerce_event_ticket_field(){
	global $post;	
	$prd_id =  get_post_meta($post->ID,'templ_event_ticket',true);
	$booked_tckt_id =  get_post_meta($post->ID,'templ_event_ticket_booked',true);
	$total_tickets = get_post_meta($prd_id,'_stock',true);
	if(get_post_meta($prd_id,'_stock',true) && is_plugin_active('woocommerce/woocommerce.php')){
		$event_tckt_id = "<a href=".get_permalink($prd_id).">".$total_tickets."</a>";
		echo "<p class='ticket'>";
		echo $event_tckt_id; _e(' tickets are available.',EDOMAIN);
		echo "<a href=".get_permalink($prd_id)." class='bookn_tab button secondary_btn tiny_btn'>".__('Book now',EDOMAIN)."</a>";
		echo "</p>";
	}	
}
?>