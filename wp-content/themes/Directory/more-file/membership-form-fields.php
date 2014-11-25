<form name="" action="" method="post" enctype="multipart/form-data">
    <table class="form-table">
        <tbody>
            <tr>
                <th><label for="package_type"><?php _e( "Package type", ADMINDOMAIN ); ?></label></th>
                <td><label><input type="radio" id="package_type" name="package_type" checked value="3"><?php _e( "Membership", ADMINDOMAIN ); ?></label></td>
            </tr>

            <tr>
                <th><label for="title"><?php _e( "Membership Title", ADMINDOMAIN ); ?></label></th>
                <td><input type="text" class="regular-text" id="title" name="package_name" value="<?php echo @$membership_element['package_name']; ?>"></td>
            </tr>

            <tr>
                <th valign="top">
                    <label for="desc" class="form-textfield-label">Membership Description</label>
                </th>
                <td>
                    <textarea name="package_desc" cols="50" rows="5" id="desc"><?php echo @$membership_element['package_desc']; ?></textarea><br><p class="description">In a few words, describe what this packages offers.</p>
                </td>
            </tr>

            <tr class="" id="package_price">
                <th valign="top">
                    <label for="package_amount" class="form-textfield-label">Amount <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" name="package_amount" id="package_amount" value="<?php echo @$membership_element['package_amount']; ?>">
                    <br><p class="description">This is the price which will be the cost to submit on this package. Do not enter thousand separators. Use the dot (.) as the decimal separator (if necessary). <strong>Tip</strong>: Enter 0 to make the package free.</p>
                </td>
            </tr>

            <tr class="" id="billing_period">
                <th valign="top">
                    <label for="billing_period" class="form-textfield-label">Membership Duration <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" class="billing_num" name="validity" id="validity" value="<?php echo @$membership_element['validity']; ?>">
                    <select name="validity_per" id="validity_per" class="textfield billing_per">
                        <option value="D" <?php selected( 'D', @$membership_element['validity_per']); ?>>Days</option>
                        <option value="M" <?php selected( 'M', @$membership_element['validity_per']); ?>>Months</option>
                        <option value="Y" <?php selected( 'Y', @$membership_element['validity_per']); ?>>Years</option>
                    </select><br>
                    <p class="description">Enter the duration in number of days, months or years for this package.</p>
                </td>
            </tr>

            <tr class="">
                <th valign="top">
                    <label for="package_status" class="form-textfield-label">Enable Package</label>
                </th>
                <td>
                    <input type="checkbox" name="package_status" id="package_status" <?php checked( 1, @$membership_element['package_status'] ); ?> value="1" checked="checked">
                    &nbsp;<label for="package_status">Yes</label><br>
                </td>
            </tr>

            <tr>
                <th valign="top">
                    <label for="is_recurring" class="form-textfield-label" style="width:100px;">Recurring package</label>
                </th>
                <td>
                    <label><input type="checkbox" name="recurring" id="recurring" value="1" <?php checked( 1, @$membership_element['recurring'] ); ?> onclick="rec_div_show(this.id)">&nbsp; Yes</label>
                    <br>
                    <p class="description">If "Yes" is selected, Listing owners will be billed automatically as soon as the price package's billing period expires. <b>Recurring packages should with PayPal, 2CO, Skrill, Google wallet</b></p>
                </td>
            </tr>

            <tr id="rec_tr" style="display: none;">
                <th valign="top">
                    <label for="recurring_billing" class="form-textfield-label">Billing Period for Recurring package</label>
                </th>
                <td>
                    <span class="option_label">Charge users every </span>
                    <input type="text" class="textfield billing_num" name="billing_num" id="billing_num" value="<?php echo @$membership_element['billing_num']; ?>">
                    <select name="billing_per" id="billing_per" class="textfield billing_per">
                        <option value="D" <?php selected( 'D', @$membership_element['billing_per']); ?>>Days</option>
                        <option value="M" <?php selected( 'M', @$membership_element['billing_per']); ?>>Months</option>
                        <option value="Y" <?php selected( 'Y', @$membership_element['billing_per']); ?>>Years</option>
                    </select><br>
                    <p class="description">Time between each billing.</p>
                </td>
            </tr>

            <tr id="rec_tr1" style="display: none;">
                <th valign="top">
                    <label for="billing_cycle" class="form-textfield-label">Number of cycles</label>
                </th>
                <td>
                    <input type="text" class="textfield" name="billing_cycle" id="billing_cycle" value="<?php echo @$membership_element['billing_cycle']; ?>"><br><p class="description">The number of times members will be billed, i.e. the number of times the process will be repeated.</p>
                </td>
            </tr>

            <tr id="rec_tr2" style="display: none;">
                <th valign="top"><label class="form-textfield-label">Free trial period</label></th>
                <td>
                    <div class="input-switch">
                        <input id="first_free_trail_period" type="checkbox" name="first_free_trail_period" <?php checked( 1, @$membership_element['first_free_trail_period'] ); ?> value="1">
                        <label for="first_free_trail_period">&nbsp;Enable</label>
                    </div>
                    <p class="description">With this enabled the first period of the subscription will be free. For the second period the user will be charged the amount you specified above. This only works with PayPal. </p>
                </td>
            </tr>
        </tbody>
    </table>
    <?php submit_button('Save Change'); ?>
</form>