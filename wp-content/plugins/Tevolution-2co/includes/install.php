<?php
$paymentmethodname = '2co';
if(strtolower($_REQUEST['install'])==strtolower($paymentmethodname))
{
	$payOpts = array();
	$paymethodinfo = array();
	$payOpts = array();
	//	supported input types text,checkbox and radio for radio button options use extra parameter "options" eg.( "options" =>	array('Male','Female')) if you leave type 
	//	parameter blank then we automaticaly consider input type text.
	$payOpts[] = array(
					"title"			=>	"Use 2Checkout in test mode?",
					"fieldname"		=>	"twocheckout_mode",
					"type"			=>  'checkbox',
					"value"			=>	"1",
					"description"	=>	__('Check this if you want to use 2Checkout in test mode.')
					);
	$payOpts[] = array(
					"title"			=>	"Seller ID",
					"fieldname"		=>	"vendorid",
					"type"			=>  'text',
					"value"			=>	"1303908",
					"description"	=>	__('Enter Seller ID Example')." : 1303908"
					);
	$paymethodinfo = array(
						"name" 		=> '2co (2CheckOut)',
						"key" 		=> $paymentmethodname,
						"isactive"	=>	'1', // 1->display,0->hide
						"display_order"=>'5',
						"payOpts"	=>	$payOpts,
						);
	update_option("payment_method_$paymentmethodname", $paymethodinfo );
	$install_message = __("Payment Method integrated successfully");
	$option_id = $wpdb->get_var("select option_id from $wpdb->options where option_name like \"payment_method_$paymentmethodname\"");
	wp_redirect("admin.php?page=monetization&tab=payment_options");
}elseif($_REQUEST['uninstall']==$paymentmethodname)
{
	delete_option("payment_method_$paymentmethodname");
	$install_message = __("Payment Method deleted successfully");
}
?>