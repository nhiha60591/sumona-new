<?php
global $General, $Cart, $payable_amount,$post_title,$last_postid;
define('TWO_CO_MSG',__('Processing for 2CO, Please wait ...',DOMAIN));
$paymentOpts = templatic_get_payment_options($_REQUEST['paymentmethod']);
$merchantid = $paymentOpts['vendorid'];

/*Price Package info */
$price_package_id=get_post_meta($last_postid,'package_select',true);
$package_amount=get_post_meta($price_package_id,'package_amount',true);
$validity=get_post_meta($price_package_id,'validity',true);
$validity_per=get_post_meta($price_package_id,'validity_per',true);
$recurring=get_post_meta($price_package_id,'recurring',true);
$billing_num=get_post_meta($price_package_id,'billing_num',true);
$billing_per=get_post_meta($price_package_id,'billing_per',true);
$billing_cycle=get_post_meta($price_package_id,'billing_cycle',true);
if($merchantid == '')
{
	$merchantid = '1303908';
}
$ipnfilepath = $paymentOpts['ipnfilepath'];
if($ipnfilepath == '')
{
	$ipnfilepath = site_url()."/?ptype=notifyurl&pmethod=2co";
}
$currency_code = templatic_get_currency_type();
$post = get_post($last_postid);
$post_title = $post->post_title;
$post_desc = $post->post_content;
$user_info = get_userdata($post->post_author);
$address1 = get_post_meta($post->post_author,'address');
$address2 = get_post_meta($post->post_author,'area');
$country = get_post_meta($post->post_author,'add_country');
$state = get_post_meta($post->post_author,'add_state');
$city = get_post_meta($post->post_author,'add_city');

if($recurring==1){
	$c=$billing_num;
	if($billing_per=='M'){
		$rec_type=sprintf('%d Month', $c);
		$cycle= 'Month';
	}elseif($billing_per=='D'){
		$rec_type=sprintf('%d Week', $c/7);
		$cycle= 'Week';
	}else{
		$rec_type=sprintf('%d Year', $c);
		$cycle= 'Year';
	}
				
	$c_recurrence=$rec_type;
	//$c_duration='FOREVER';
	$c_duration=$billing_cycle.' '.$cycle;
	$c_startup_fee=$payable_amount-$package_amount;
	$payable_amount=$package_amount;
}

?>

<form method="post" action="https://www.2checkout.com/checkout/purchase" name="frm_payment_method">
<input type="hidden" value="<?php echo $last_postid; ?>" name="li_0_product_id"/>
<input type="hidden" value="<?php echo $post_title; ?>" name="li_0_name"/>
<input type="hidden" value="<?php echo $payable_amount;?>" name="li_0_price"/>
<input type="hidden" value="1" name="id_type"/>
<input type="hidden" value="<?php echo $payable_amount;?>" name="total"/>
<input type="hidden" value="<?php echo $merchantid;?>" name="sid"/>
 <input type='hidden' name='mode' value='2CO' />
<?php if($recurring==1):?>
<input type='hidden' name='li_0_recurrence' value='<?php echo $c_recurrence;?>' />
<input type='hidden' name='li_0_duration' value='<?php echo $c_duration;?>' />
<input type='hidden' name='li_0_startup_fee' value='<?php echo $c_startup_fee;?>' />
<?php endif;?>

<input type="hidden" name="c_tangible" value="N">
<input type='hidden' name='x_receipt_link_url' value='<?php echo site_url()."/?page=2co_success&paydeltype=2co&pid=".$last_postid;?>' />
<input type='hidden' name='x_amount' value='<?php echo $payable_amount;?>' />
<input type='hidden' name='x_login' value='<?php echo $merchantid;?>' />
<input type='hidden' name='x_first_name' value='<?php if($user_info->first_name){ echo $user_info->first_name; }else{ echo $user_info->user_login; } ?>' />
<input type='hidden' name='x_email' value='<?php echo $user_info->user_login;?>' />
<input type="hidden" name="tco_currency" value="<?php echo $currency_code;?>" />
<input type="hidden" name="return_url" value="<?php echo site_url()."/?page=2co_success&paydeltype=2co&pid=".$last_postid; ?>"/>
<input type='hidden' name='x_invoice_num' value='<?php echo $last_postid;?>' />
<?php 
	if( $paymentOpts['twocheckout_mode'] == 1 ){
?>
		<input type='hidden' name='demo' value='Y' />
<?php 
	}
?>

<!--<input type="submit" value="Buy from 2CO" name="purchase" class="submit"/>-->
</form>

 <div class="wrapper" >
		<div class="clearfix container_message">
            	<h1 class="processing_message_head"><?php echo TWO_CO_MSG;?></h1>
            </div>

<script>
setTimeout("document.frm_payment_method.submit()",500); 
</script>