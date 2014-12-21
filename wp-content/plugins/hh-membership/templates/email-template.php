<div class="wrap">
    <?php
        $data = get_option( 'hh_email_membership' );
        if( !is_array( $data ) ){
            $data = array();
        }
    ?>
    <h2><?php _e( 'Email Option', __HHTEXTDOMAIN__ ); ?></h2>
    <div id="poststuff">
    <form name="" action="" method="post">
        <?php do_action( 'before_email_template' ); ?>
        <div id="hh_new_to_user" class="postbox ">
            <div class="handlediv" title="Click to toggle"><br></div>
            <h3 class="hndle"><span><?php _e( "Membership Registration email to user", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['new_user']['subject']; ?>" name="hh_email_membership[new_user][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                                $content=$data['new_user']['message'];
                                $settings =   array(
                                    'wpautop' => false, // use wpautop?
                                    'media_buttons' => false, // show insert/upload button(s)
                                    'textarea_name' => "hh_email_membership[new_user][message]", // set the textarea name to something different, square brackets [] can be used here
                                    'textarea_rows' => '7', // rows="..."
                                    'tabindex' => '',
                                    'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                    'editor_class' => '', // add extra class(es) to the editor textarea
                                    'teeny' => true, // output the minimal editor config used in Press This
                                    'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                    'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                    'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                );
                                wp_editor( $content, "hh_new_to_user_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_new_to_admin" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Membership Registration email to admin", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['new_user_admin']['subject']; ?>" name="hh_email_membership[new_user_admin][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['new_user_admin']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[new_user_admin][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_new_to_admin_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_successful_payment_user" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Successful membership payment notification to user", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['successful_payment_user']['subject']; ?>" name="hh_email_membership[successful_payment_user][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['successful_payment_user']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[successful_payment_user][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_successful_payment_user_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_successful_payment_admin" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Successful membership payment notification to admin", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['successful_payment_admin']['subject']; ?>" name="hh_email_membership[successful_payment_admin][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['successful_payment_admin']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[successful_payment_admin][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_successful_payment_admin_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_upgrade_downgrade_user" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Membership upgrade/downgrade notification to user", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['upgrade_downgrade_user']['subject']; ?>" name="hh_email_membership[upgrade_downgrade_user][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['upgrade_downgrade_user']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[upgrade_downgrade_user][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_upgrade_downgrade_user_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_upgrade_downgrade_admin" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Membership upgrade/downgrade notification to admin", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['upgrade_downgrade_admin']['subject']; ?>" name="hh_email_membership[upgrade_downgrade_admin][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['upgrade_downgrade_admin']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[upgrade_downgrade_admin][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_upgrade_downgrade_admin_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_cancel_to_user" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Membership Cancel notification to user", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['cancel_to_user']['subject']; ?>" name="hh_email_membership[cancel_to_user][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['cancel_to_user']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[cancel_to_user][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_cancel_to_user_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="hh_cancel_to_admin" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Membership Cancel notification to admin", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="<?php echo $data['cancel_to_admin']['subject']; ?>" name="hh_email_membership[cancel_to_admin][subject]" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                            $content=$data['cancel_to_admin']['message'];
                            $settings =   array(
                                'wpautop' => false, // use wpautop?
                                'media_buttons' => false, // show insert/upload button(s)
                                'textarea_name' => "hh_email_membership[cancel_to_admin][message]", // set the textarea name to something different, square brackets [] can be used here
                                'textarea_rows' => '7', // rows="..."
                                'tabindex' => '',
                                'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                'editor_class' => '', // add extra class(es) to the editor textarea
                                'teeny' => true, // output the minimal editor config used in Press This
                                'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                            );
                            wp_editor( $content, "hh_cancel_to_admin_editor", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php do_action( 'after_email_template' ); ?>
        <?php submit_button('Save Change', 'primary', 'hh_save_email_template') ?>
        </form>
    </div>
    </div>