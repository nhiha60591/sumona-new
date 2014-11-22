<?php
/**
 * Template Name: Mobile Front Template
 *
 * This is the home template.  Technically, it is the "posts page" template.  It is used when a visitor is on the 
 * page assigned to show a site's latest blog posts.
 *
 * @package supreme
 * @subpackage Template
 */
get_header(); // Loads the header.php template. ?>

<section id="content" class="large-9 small-12 columns">
  
    <div class="hfeed">
		<?php dynamic_sidebar( 'before-content' ); // Loads the supreme_sidebar_before_content();  template. ?>
			  <div ID="tmpl-search-results" class="list">
				<?php 
				dynamic_sidebar('home-page-content'); 
				 ?>
			  </div>
			<?php 
			dynamic_sidebar( 'after-content' ); // Loads the sidebar-after-content.php template. ?>
     </div>
  	<!-- .hfeed -->
	<?php 
  	do_action( 'close_content' );
	apply_filters('supreme_custom_front_loop_navigation',supreme_loop_navigation($post)); // Loads the loop-navigation .
	?>
</section>
<!-- #content -->
<?php 
do_action( 'after_content' );
get_footer(); // Loads the footer.php template. ?>