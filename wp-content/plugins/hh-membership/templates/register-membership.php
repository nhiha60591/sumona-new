<?php
/**
 * Single Template
 *
 * This is the default post template.  It is used when a more specific template can't be found to display
 * singular views of the 'post' post type.
 */
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
                                $payable_amount = get_post_meta( get_the_ID(), 'payable_amount', true );
                                payment_menthod_response_url($_POST[''],get_the_ID(),'aa',get_the_ID(),$payable_amount);
                            }
                        ?>
                        <section class="entry-content">
                            <form name="membership_register" id="submit_form" action="<?php echo add_query_arg( array('action'=>'payment-method'), get_the_permalink()); ?>" method="post" _lpchecked="1">
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
                                <h3>Payment method</h3>
                                <div class="method">
                                    <?php templatic_payment_option_preview_page(); ?>
                                </div>
                                <input type="submit" name="hh_register" value="Register">
                            </form>
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