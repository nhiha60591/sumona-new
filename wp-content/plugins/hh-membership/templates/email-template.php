<div class="wrap">
    <h2><?php _e( 'Email Option', __HHTEXTDOMAIN__ ); ?></h2>
    <div id="poststuff">
    <form name="" action="" method="post">
        <?php do_action( 'before_email_template' ); ?>
        <div id="tmpl-settings_basic_inf" class="postbox ">
            <div class="handlediv" title="Click to toggle">
                <br>
            </div>
            <h3 class="hndle"><span><?php _e( "Membership Registration email to user", __HHTEXTDOMAIN__ ) ;?></span></h3>
            <div class="inside">
                <table id="tvolution_fields" style="width:100%" class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="membership">Subject</label></th>
                        <td>
                            <input size="100" class="regular-text pt_input_text" type="text" value="" name="hh_email_membership['new_user']['subject']" id="hh_email" placeholder="">
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="membership">Message</label></th>
                        <td>
                            <?php
                                $content='';
                                $settings =   array(
                                    'wpautop' => false, // use wpautop?
                                    'media_buttons' => false, // show insert/upload button(s)
                                    'textarea_name' => "hh_email_membership['new_user']['subject']", // set the textarea name to something different, square brackets [] can be used here
                                    'textarea_rows' => '7', // rows="..."
                                    'tabindex' => '',
                                    'editor_css' => '<style>.wp-editor-wrap{width:640px;margin-left:0px;}</style>', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                                    'editor_class' => '', // add extra class(es) to the editor textarea
                                    'teeny' => true, // output the minimal editor config used in Press This
                                    'dfw' => true, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                    'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                    'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                );
                                wp_editor( $content, "hh_email_membership", $settings );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php do_action( 'after_email_template' ); ?>
        </form>
    </div>
    </div>