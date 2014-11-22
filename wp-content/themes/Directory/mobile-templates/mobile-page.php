<?php
/**
 * Page Template
 *
 * This is the default page template.  It is used when a more specific template can't be found to display 
 * singular views of pages.
 */
get_header(); // Loads the header.php template. 
	
do_action( 'before_content' );
?>
<section id="content" class="large-9 small-12 columns">
	<?php do_action( 'open_content' ); 
	do_action( 'templ_after_container_breadcrumb' ); ?>  
	<div class="hfeed">
     <?php apply_filters('tmpl_before-content',supreme_sidebar_before_content() ); // Loads the sidebar-before-content.
		if ( have_posts() ) :
			while ( have_posts() ) : the_post(); 
				do_action( 'before_entry' );  ?>
				<div id="post-<?php the_ID(); ?>" class="<?php supreme_entry_class(); ?>">
      			<?php do_action( 'open_entry' ); 					
					 do_action('entry-title'); ?>
                          <section class="entry-content">
                            <?php do_action('open-post-content');
                                  the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', THEME_DOMAIN ) );
                                  wp_link_pages( array( 'before' => '<p class="page-links">' . __( 'Pages:', THEME_DOMAIN ), 'after' => '</p>' ) );
                                   do_action('entry-edit-link'); 
                                   do_action('close-post-content');
                              ?>
                          </section>
                          <!-- .entry-content -->
					<?php do_action( 'close_entry' );  ?>
    				</div>
					<!-- .hentry -->
					<?php 
					do_action( 'after_entry' ); 
					do_action( 'after_singular' );
					do_action( 'before_comments' ); 
					 
					 // If comments are open or we have at least one comment, load the comments template.
					 if ( supreme_get_settings( 'enable_comments_on_page' )) {
					 	comments_template( '/comments.php', true ); // Loads the comments.php template. 
					 }
					 do_action( 'after_comments' ); // after_comments 
				endwhile; 
			endif; 			?>
	</div>
	<!-- .hfeed -->
  <?php do_action( 'close_content' ); ?>
</section>
<!-- #content -->
<?php do_action( 'after_content' ); 

get_footer(); // Loads the footer.php template. ?>