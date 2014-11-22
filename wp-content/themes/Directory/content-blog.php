<?php // supreme_open_entry
	$post_type = get_post_type($post->ID);
	
	do_action( 'open_entry'.$post_type );

	$featured=get_post_meta(get_the_ID(),'featured_c',true);
		$featured=($featured=='c')?'featured_c':'';
		
		if(isset($_REQUEST['sort']) && $_REQUEST['sort'] =='favourites'){
			$post_type_tag = $post->post_type;
		}else{
			$post_type_tag = '';
		}
	
	if ( is_sticky() && is_home() && ! is_paged() ) : ?>
		<div class="featured-post">
		  <?php _e( 'Featured post', THEME_DOMAIN ); ?>
		</div>
	<?php 
	endif;

	/* get the image code - show image if Display imege option is enable from backend - Start */
	if ( has_post_thumbnail() && ! post_password_required() ) : ?>
		<div class="entry-thumbnail">
			<?php the_post_thumbnail(); ?>
		</div>
	<?php endif; 
	/* get the image code - show image if Display image option is enable from back end - Start */		
	?>
	<div class="entry-header post-blog-content">
	  <?php do_action('supreme_before-title_'.$post_type);?>
	  <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', THEME_DOMAIN ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?>
		</a>
	  </h2>
	  <?php  
			do_action('tevolution_blog_title_text');
				if(!is_author()){
					apply_filters('supreme-post-info',supreme_core_post_info($post)); //do not show by line for blog post page for home page.
				}else{
					/* display different meta on author page */
					do_action('tmpl_author_meta');
				}
			
			do_action('supreme_after-title_'.$post_type);			
			
			do_action( 'tmpl-before-entry'.$post_type); // Loads the sidebar-entry
			$theme_options = get_option(supreme_prefix().'_theme_settings');
			$supreme_archive_display_excerpt = $theme_options['supreme_archive_display_excerpt'];
			$templatic_excerpt_link = $theme_options['templatic_excerpt_link'];
			
			/* to hide the excerpt and content from author page */
			if(!is_author()){
				if( $supreme_archive_display_excerpt) { ?>
			
					  <div class="entry-summary">
						<?php the_excerpt($templatic_excerpt_link  );  ?>
						<?php do_action('single_post_custom_fields'); ?>
					  </div>
					  <!-- .entry-summary -->
				
				<?php }else{ 
					if(is_tevolution_active() && tmpl_donot_display_description()){ ?>
					  <?php }else{ ?>
					  <section class="entry-content">
						<?php 
							the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', THEME_DOMAIN ) ); 
							wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', THEME_DOMAIN ), 'after' => '</div>' ) ); 
							do_action('single_post_custom_fields'); ?>
					  </section>
					  <!-- .entry-content -->
				<?php	}
				} 
				/* blog post categories */
				$taxonomies =  supreme_get_post_taxonomies($post);
				$cat_slug = $taxonomies [0];
				$tag_slug = $taxonomies [1];
				$theme_options = get_option(supreme_prefix().'_theme_settings');
				
				supreme_entry_meta(); 			
			
			}else{
			
			}
			do_action('supreme_aftercontent'.$post_type);
			do_action('templ_show_edit_renew_delete_link');
			
			do_action( 'close_entry'.$post_type); // supreme_close_entry ?>
	  <!-- #post -->
	</div>