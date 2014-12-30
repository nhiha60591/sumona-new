<?php
/**
 * Single Template
 *
 * This is the default post template.  It is used when a more specific template can't be found to display
 * singular views of the 'post' post type.
 */
$current_user = wp_get_current_user();
get_header(); // Loads the header.php template.
do_action( 'before_content' );
do_action( 'templ_before_container_breadcrumb' );  ?>
    <section id="content" class="large-9 small-12 columns">
        <?php do_action( 'open_content' );
        do_action( 'templ_inside_container_breadcrumb' );
        global $post;
        ?>
        <div class="hfeed">
            <?php apply_filters('tmpl_before-content',supreme_sidebar_before_content() ); // Loads the sidebar-before-content.
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    do_action( 'before_entry' );  ?>
                    <div id="post-<?php the_ID(); ?>" class="<?php supreme_entry_class(); ?>">
                        <?php
                            if( isset($_POST['hh_register']) ){
                                $error = new WP_Error();
                                if( !$current_user->ID ):
                                    if( !isset( $_POST['username'] ) || empty( $_POST['username']) ){
                                        $error->add( 'username', '<strong>ERROR: </strong>Please enter a username');
                                    }elseif( username_exists( $_POST['username']) ){
                                        $error->add( 'username', '<strong>ERROR: </strong>This username ready exists');
                                    }
                                    if( !isset( $_POST['password'] ) || empty( $_POST['password']) ){
                                        $error->add( 'password', '<strong>ERROR: </strong>Please provide a password');
                                    }
                                    if( isset( $_POST['password'] ) && isset( $_POST['confirm_password'] ) && $_POST['password'] != $_POST['confirm_password'] ){
                                        $error->add( 'confirm_password', '<strong>ERROR: </strong>Please enter the same password as above');
                                    }
                                    if( !isset( $_POST['user_email'] ) || empty( $_POST['user_email']) ){
                                        $error->add( 'user_email', '<strong>ERROR: </strong>Please enter a valid email address');
                                    }else{
                                        if( email_exists( $_POST['user_email'])) {
                                            $error->add('user_email', '<strong>ERROR: </strong>This email ready exists');
                                        }
                                    }
                                    if( !isset( $_POST['first_name'] ) || empty( $_POST['first_name']) ){
                                        $error->add( 'first_name', '<strong>ERROR: </strong>Please enter your first name');
                                    }
                                    if( !isset( $_POST['last_name'] ) || empty( $_POST['last_name']) ){
                                        $error->add( 'last_name', '<strong>ERROR: </strong>Please enter your last name');
                                    }
                                endif;
                                if ( sizeof( $error->get_error_codes() ) > 0 ){
                                    foreach( $error->get_error_messages() as $mess ){
                                        echo '<div class="error">'.$mess.'</div>';
                                    }
                                }else{
                                    global $post;
                                    if( !$current_user->ID ):
                                        $userdata = array(
                                            'user_pass' => $_POST['confirm_password'],
                                            'user_login' => $_POST['username'],
                                            'first_name' => $_POST['first_name'],
                                            'last_name' => $_POST['last_name'],
                                            'user_email' => $_POST['user_email'],
                                        );
                                        $user_id = wp_insert_user( $userdata ) ;
                                    else:
                                        $user_id = $current_user->ID;
                                    endif;

                                    //On success
                                    if( !is_wp_error($user_id) ) {
                                        $data = get_option('hh_email_membership');
                                        if (!is_array($data)) {
                                            $data = array();
                                        }
                                        //HH_Membership_Mail::send_mail( $userdata['user_email'], $data['new_user']['subject'], $data['new_user']['message']);
                                        //HH_Membership_Mail::send_mail( get_option( "admin_email" ), $data['new_user_admin']['subject'], $data['new_user_admin']['message']);
                                        update_user_meta( $user_id, 'membership_package_id', $post->ID );
                                        update_user_meta( $user_id, 'membership_package_register', date( "Y-m-d") );
                                        $payable_amount = get_post_meta( $post->ID, 'package_amount', true );
                                        $trans_id = insert_transaction_detail($_POST['paymentmethod'],$post->ID,0,1);
                                        insert_update_users_packageperlist(0,$_POST,$trans_id);
                                        payment_menthod_response_url($_POST['paymentmethod'],get_the_ID(),'membership',get_the_ID(),$payable_amount);
                                    }
                                }
                            }
                        ?>
                        <section class="entry-content">
                            <form name="membership_register" id="membership_register" class="membership_register" action="<?php echo add_query_arg( array('action'=>'payment-method'), get_the_permalink()); ?>" method="post" _lpchecked="1">
                                <?php if( !$current_user->ID ): ?>
                                <p class="register_membership">
                                    <label for="username">Username:</label>
                                    <input type="text" name="username" value="" id="username" placeholder="" class="placeholder" style="cursor: auto; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABHklEQVQ4EaVTO26DQBD1ohQWaS2lg9JybZ+AK7hNwx2oIoVf4UPQ0Lj1FdKktevIpel8AKNUkDcWMxpgSaIEaTVv3sx7uztiTdu2s/98DywOw3Dued4Who/M2aIx5lZV1aEsy0+qiwHELyi+Ytl0PQ69SxAxkWIA4RMRTdNsKE59juMcuZd6xIAFeZ6fGCdJ8kY4y7KAuTRNGd7jyEBXsdOPE3a0QGPsniOnnYMO67LgSQN9T41F2QGrQRRFCwyzoIF2qyBuKKbcOgPXdVeY9rMWgNsjf9ccYesJhk3f5dYT1HX9gR0LLQR30TnjkUEcx2uIuS4RnI+aj6sJR0AM8AaumPaM/rRehyWhXqbFAA9kh3/8/NvHxAYGAsZ/il8IalkCLBfNVAAAAABJRU5ErkJggg==); background-attachment: scroll; background-position: 100% 50%; background-repeat: no-repeat;">
                                </p>
                                <p class="register_membership">
                                    <label for="password">Password:</label>
                                    <input type="password" name="password" value="" id="password" placeholder="" class="placeholder" style="cursor: auto; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACIUlEQVQ4EX2TOYhTURSG87IMihDsjGghBhFBmHFDHLWwSqcikk4RRKJgk0KL7C8bMpWpZtIqNkEUl1ZCgs0wOo0SxiLMDApWlgOPrH7/5b2QkYwX7jvn/uc//zl3edZ4PPbNGvF4fC4ajR5VrNvt/mo0Gr1ZPOtfgWw2e9Lv9+chX7cs64CS4Oxg3o9GI7tUKv0Q5o1dAiTfCgQCLwnOkfQOu+oSLyJ2A783HA7vIPLGxX0TgVwud4HKn0nc7Pf7N6vV6oZHkkX8FPG3uMfgXC0Wi2vCg/poUKGGcagQI3k7k8mcp5slcGswGDwpl8tfwGJg3xB6Dvey8vz6oH4C3iXcFYjbwiDeo1KafafkC3NjK7iL5ESFGQEUF7Sg+ifZdDp9GnMF/KGmfBdT2HCwZ7TwtrBPC7rQaav6Iv48rqZwg+F+p8hOMBj0IbxfMdMBrW5pAVGV/ztINByENkU0t5BIJEKRSOQ3Aj+Z57iFs1R5NK3EQS6HQqF1zmQdzpFWq3W42WwOTAf1er1PF2USFlC+qxMvFAr3HcexWX+QX6lUvsKpkTyPSEXJkw6MQ4S38Ljdbi8rmM/nY+CvgNcQqdH6U/xrYK9t244jZv6ByUOSiDdIfgBZ12U6dHEHu9TpdIr8F0OP692CtzaW/a6y3y0Wx5kbFHvGuXzkgf0xhKnPzA4UTyaTB8Ph8AvcHi3fnsrZ7Wore02YViqVOrRXXPhfqP8j6MYlawoAAAAASUVORK5CYII=); background-attachment: scroll; background-position: 100% 50%; background-repeat: no-repeat;">
                                </p>
                                <p class="register_membership">
                                    <label for="confirm_password">Confirm password:</label>
                                    <input type="password" name="confirm_password" value="" id="confirm_password" placeholder="" class="placeholder" style="cursor: auto; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABMUlEQVQ4T32SgVHDMAxF6QZ0AswELRuECcoGhAloJwAmACZomAA6QbMBZYKaDRih/+UkTvHF1d0/x/L317eU2cX5WNrxoUabTRwk5Z6EtjjrtH8RcsyXAlx6FS6FneCVcbIS/oSNgNgQUQDSt/Bj1UvbnHNxIdy4eBTolYQEsOkXKIQzBL0I3NvoIGlzFN6Ftbl71gqRaAT2xJvwKFwL2R1A2Jsql6hEL7JdSlp5Oy7uhE/nukCrxDYIUI1JxGAC5L3Yg7670sGQNGJNwMXpQR+byIjoAx3mCViN8aVNFpjUXGDUozG6srvgPJkYlwnc3Qv+nJEAhNZIfDcCzfoVeA4FcPZhvEFx6ld2IZpaxn9lP6gJUAnbV+aA6uzp0yhqApBoUmdrM+Hm7BMiP2mTawInofVEEf5J2pUAAAAASUVORK5CYII=); background-attachment: scroll; background-position: 100% 50%; background-repeat: no-repeat;">
                                </p>
                                <p class="register_membership">
                                    <label for="user_email">Email:</label>
                                    <input type="text" name="user_email" value="" id="user_email" placeholder="" class="placeholder">
                                </p>
                                <p class="register_membership">
                                    <label for="first_name">First Name:</label>
                                    <input type="text" name="first_name" value="" id="first_name" placeholder="" class="placeholder">
                                </p>
                                <p class="register_membership">
                                    <label for="last_name">Last Name:</label>
                                    <input type="text" name="last_name" value="" id="last_name" placeholder="" class="placeholder">
                                </p>
                                <?php endif; ?>
                                <h3>Payment method</h3>
                                <div class="method">
                                    <?php templatic_payment_option_preview_page(); ?>
                                </div>
                                <input type="submit" name="hh_register" value="Register">
                            </form>
                            <script type="text/javascript">
                                jQuery(document).ready(function($){
                                    $("#membership_register").validate({
                                        rules: {
                                            first_name: "required",
                                            last_name: "required",
                                            username: {
                                                required: true,
                                                minlength: 2
                                            },
                                            password: {
                                                required: true,
                                                minlength: 5
                                            },
                                            confirm_password: {
                                                required: true,
                                                minlength: 5,
                                                equalTo: "#password"
                                            },
                                            user_email: {
                                                required: true,
                                                email: true
                                            }
                                        },
                                        messages: {
                                            first_name: "Please enter your firstname",
                                            last_name: "Please enter your lastname",
                                            username: {
                                                required: "Please enter a username",
                                                minlength: "Your username must consist of at least 2 characters"
                                            },
                                            password: {
                                                required: "Please provide a password",
                                                minlength: "Your password must be at least 5 characters long"
                                            },
                                            confirm_password: {
                                                required: "Please provide a password",
                                                minlength: "Your password must be at least 5 characters long",
                                                equalTo: "Please enter the same password as above"
                                            },
                                            user_email: "Please enter a valid email address"
                                        }
                                    });
                                });
                            </script>
                        </section>
                        <!-- .entry-content -->
                    </div>
                    <!-- .hentry -->
                    <?php
                endwhile;
            endif;
            apply_filters('tmpl_after-content',supreme_sidebar_after_content()); // after-content-sidebar use remove filter to dont display it ?>
        </div>
        <!-- .hfeed -->
        <?php do_action( 'close_content' ); ?>
    </section>
    <!-- #content -->
<?php
do_action( 'after_content' );
apply_filters('supreme-post-detail-sidebar',supreme_post_detail_sidebar());// load the side bar of listing page	
get_footer(); // Loads the footer.php template. ?>