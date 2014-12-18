<div class="wrap">
    <h2><?php _e( 'Email Option', __HHTEXTDOMAIN__ ); ?></h2>
    <form name="" action="" method="post">
    <table class="form-table">
        <tbody>
            <?php do_action( 'before_email_option_fields' ); ?>
            <tr>
                <th scope="row">
                    <label for="send_for_admin">Send mail for admin</label>
                </th>
                <td>
                    <fieldset>
                        <label for="send_for_admin">
                            <input name="send_for_admin" type="checkbox" id="send_for_admin" value="1">
                            Send mail for admin when new user register
                        </label>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="send_for_user">Send mail for user</label>
                </th>
                <td>
                    <fieldset>
                        <label for="send_for_user">
                            <input name="send_for_user" type="checkbox" id="send_for_user" value="1">
                            Send mail for user when new register
                        </label>
                    </fieldset>
                </td>
            </tr>

            <?php do_action( 'after_email_option_fields' ); ?>
        </tbody>
    </table>
    </form>
</div>