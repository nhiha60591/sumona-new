<?php
/**
 * Mobile Footer Template
 *
 * The footer template is generally used on every page of your site. Nearly all other
 * templates call it somewhere near the bottom of the file. It is used mostly as a closing
 * wrapper, which is opened with the header.php file. It also executes key functions needed
 * by the theme, child themes, and plugins. 
 */
 do_action( 'close_main' ); // supreme_close_main 
 ?>
</div>
<!-- .wrap -->
<?php do_action( 'after_wrapper' ); // supreme_after_main ?>
</section>
<!-- #main -->
<?php do_action( 'after_main' ); // supreme_after_main ?>
</div>

   <script type="text/javascript">
jQuery( document ).ready(function() {
	
	//jQuery(window).scrollTop(jQuery('#main').offset().top);
	jQuery('#directorytab').css('display','block');
	jQuery('.sort_options,#mobile_listing_popup_link').hide().delay(1500).fadeIn();


	
		<?php
		if(is_tax() || is_archive()){
		?>
		// views
		if (jQuery('#listview').hasClass('active').toString()) {
		  jQuery('body').removeClass('category-map');
		  jQuery('.view_type_wrap').removeClass('hide');
		}
		else if (jQuery('#locations_map').hasClass('active').toString()) {
		  jQuery('.view_type_wrap').addClass('hide');
		  jQuery('body').addClass('category-map');

		  var windowHeight = jQuery(window).height();
		  var displayHeight = windowHeight - 57;    
		  jQuery('#content').css('height',displayHeight+'px');
		}
	   
		jQuery('#locations_map').click(function() {
		  jQuery('.view_type_wrap').addClass('hide');
		  jQuery('body').addClass('category-map');

		  var windowHeight = jQuery(window).height();
		  var displayHeight = windowHeight - 57;    
		  jQuery('#content').css('height',displayHeight+'px');
		  jQuery(window).scrollTop(jQuery("#container").offset().top);
		});
		
		jQuery('#listview').click(function() {
		  jQuery('.view_type_wrap').removeClass('hide');
		  jQuery('body').removeClass('category-map');
		  jQuery('#content').css('height','auto');
		});
		<?php }
		?>

		
		jQuery('.tmpl-accordion-navigation > a').click(function() {
		  jQuery(window).scrollTop(jQuery(this).offset().top);
		});


		// var doc_height = jQuery('body').delay(10000).height();
		// alert(doc_height);
		// if(doc_height<640) {
		// 	jQuery('body').css('height','100%');
		// 	jQuery('.supreme_wrapper').css('height', '100%').css('height', '-=71px');
		// }
			
});

</script>


<!-- #container -->
<footer class="footer">
    <?php echo apply_atomic_shortcode( 'footer_content', supreme_get_settings( 'footer_insert' ) ); ?>
</footer>


  </div> <!-- inner-wrap start -->
</div> <!-- off-canvas-wrap end -->


<?php do_action( 'close_body' ); // supreme_close_body ?>
</div>

<?php 
	do_action( 'after_footer' ); // supreme_after_footer 
	wp_footer(); // wp_footer 
	do_action('before_body_end',10); ?>
</body>
</html>