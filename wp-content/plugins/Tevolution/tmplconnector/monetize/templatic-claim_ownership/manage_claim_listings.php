<!-- Custom Taxonomies Lists -->
<div class="wrap">
	<?php if(isset($_REQUEST['custom_msg_type']) && $_REQUEST['custom_msg_type'] == 'delete'):?>
	<div class="message updated"><p><?php echo __('Claim deleted successfully',ADMINDOMAIN); ?></p></div>
	<?php endif; ?>    
	<div id="icon-edit" class="icon32 icon32-posts-post"><br/></div>
	<h2>
	<?php echo __("Manage Claims",ADMINDOMAIN); ?>
	</h2
     <?php do_action('tevolution_claim_listing_msg');?>
     
</div><br />
<form name="all_custom_post_types" id="posts-filter" action="<?php echo admin_url("admin.php?page=custom_setup"); ?>" method="post" >
	<?php
	$templ_claimlist_table = new templ_claimlist_table();
	$templ_claimlist_table->prepare_items();
	$templ_claimlist_table->display();
	?>
</form>