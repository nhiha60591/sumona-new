<?php
$data = get_option('hh_email_membership');
if (!is_array($data)) {
    $data = array();
}
$list_membership_mail = array(
    array(
        'title' => '',
        'name' => '',
    ),
);
?>
<div class="tevo_sub_title" style="padding-top: 10px;">Membership Notification Settings</div>
<table  class="widefat post email-wide-table email-settings">
    <thead>
    <tr>
        <th class="first-th">
            <label for="notification_title" class="form-textfield-label"><?php echo __('Notification Title',ADMINDOMAIN); ?></label>
        </th>

        <th class="last-th">
            <label for="msg_desc" class="form-textfield-label"><?php echo __('Actions',ADMINDOMAIN); ?></label>
        </th>
    </tr>
    </thead>
    <tbody>
    <tr class="post-submission-not alternate">
        <td><label class="form-textfield-label">Successful post submission message</label></td>

        <td><a href="javascript:void(0);" onclick="open_quick_edit('post-submission-not','edit-post-submission-not')">Quick Edit</a>
            |
            <a href="javascript:void(0);" onclick="reset_to_default('','post_added_success_msg_content','post-submission-not');">Reset</a>
            <span class="spinner" style="margin:0 18px 0;"></span>
            <span class="qucik_reset">Data reset</span>
        </td>
    </tr>
    <tr class="edit-post-submission-not" style="display:none">
        <td width="100%" colspan="2">
            <h4 class="edit-sub-title">Quick Edit</h4>
            <table width="98%" align="left" class="tab-sub-table">
                <tbody><tr>
                    <td style="line-height:10px">
                        <label class="form-textfield-label sub-title">Message</label>
                    </td>
                    <td width="90%" style="line-height:10px">

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="buttons">
                            <div class="inline_update">
                                <a class="button-primary save  quick_save" href="javascript:void(0);" accesskey="s">Save Changes</a>
                                <a class="button-secondary cancel " href="javascript:void(0);" onclick="open_quick_edit('edit-post-submission-not','post-submission-not')" accesskey="c">Cancel</a>
                                <span class="save_error" style="display:none"></span><span class="spinner"></span>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody></table>
        </td>
    </tr>
    </tbody>
</table>