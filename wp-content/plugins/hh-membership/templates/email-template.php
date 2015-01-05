<?php
//$data = get_option('hh_email_membership');
$data = get_option('templatic_settings');
$list_membership_mail = array(
    array(
        'title' => 'Membership Registration email to user',
        'name' => 'hh_new_user',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Membership Registration email to admin',
        'name' => 'hh_new_user_admin',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Successful membership payment notification to user',
        'name' => 'hh_success_payment_user',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Successful membership payment notification to admin',
        'name' => 'hh_success_payment_admin',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Membership upgrade/downgrade notification to user',
        'name' => 'hh_upgrade_user',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Membership upgrade/downgrade notification to admin',
        'name' => 'hh_upgrade_admin',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Membership Cancel notification to user',
        'name' => 'hh_cancel_to_user',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
    ),
    array(
        'title' => 'Membership Cancel notification to admin',
        'name' => 'hh_cancel_to_admin',
        'subject' => '',
        'content' => '<p>Hey [#to_name#],</p><p>[#frnd_comments#]</p><p>Link: [#post_title#]</p><p>Cheers<br/>[#your_name#]</p>'
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
    <?php $i=1; foreach( $list_membership_mail as $mail ): if( $i%2 != 0) $class = ' alternate'; else $class = ' ';  ?>
        <?php
            if($data[$mail['name'].'_subject'] != ""){
                $subject = stripslashes($data[$mail['name'].'_subject']);
            }else{
                $subject = $data[$mail['subject']];
            }

            if($data[$mail['name']] != ""){
                $content = stripslashes($data[$mail['name']]);
            }else{
                $content = $data[$mail['content']];
            }
        ?>
        <tr class="<?php echo $mail['name']; ?>-class<?php echo $class; ?>">
            <td><label class="form-textfield-label"><?php echo $mail['title']; ?></label></td>
            <td>
                <a href="javascript:void(0);" onclick="open_quick_edit('<?php echo $mail['name']; ?>-class','edit-<?php echo $mail['name']; ?>-class')">Quick Edit</a>|
                <a href="javascript:void(0);" onclick="reset_to_default('','','<?php echo $mail['name']; ?>-class');">Reset</a>
                <span class="spinner" style="margin:0 18px 0;"></span>
                <span class="qucik_reset">Data reset</span>
            </td>
        </tr>
        <tr class="edit-<?php echo $mail['name']; ?>-class" style="display:none">
            <td width="100%" colspan="2">
                <h4 class="edit-sub-title">Quick Edit</h4>
                <table width="98%" align="left" class="tab-sub-table">
                    <tbody>
                        <tr>
                            <td style="line-height:10px"><label class="form-textfield-label sub-title">Subject</label></td>
                            <td><input type="text" name="<?php echo $mail['name']; ?>_subject" value="<?php echo $data[$mail['name'].'_subject']; ?>" /></td>
                        </tr>
                        <tr>
                           <td style="line-height:10px">
                            <label class="form-textfield-label sub-title">Message</label>
                            </td>
                            <td width="90%" style="line-height:10px">
                                <?php
                                $settings =   array(
                                    'wpautop' => false, // use wpautop?
                                    'media_buttons' => false, // show insert/upload button(s)
                                    'textarea_name' => $mail['name'], // set the textarea name to something different, square brackets [] can be used here
                                    'textarea_rows' => '7', // rows="..."
                                    'tabindex' => '1',
                                    'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                    'editor_class' => '', // add extra class(es) to the editor textarea
                                    'teeny' => true, // output the minimal editor config used in Press This
                                    'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                    'tinymce' => false, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                    'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                );
                                // default settings
                                wp_editor( $content, $mail['name'], $settings);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="buttons">
                                    <div class="inline_update">
                                        <a class="button-primary save quick_save" href="javascript:void(0);" accesskey="s">Save Changes</a>
                                        <a class="button-secondary cancel " href="javascript:void(0);" onclick="open_quick_edit('edit-<?php echo $mail['name']; ?>-class','<?php echo $mail['name']; ?>-class')" accesskey="c">Cancel</a>
                                        <span class="save_error" style="display:none"></span><span class="spinner"></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php $i++; endforeach; ?>
    </tbody>
</table>