<?php
/**
 * Search Form Template
 *
 * The search form template displays the search form.
 */
?>
<form method="get" class="search-form" action="<?php echo trailingslashit( home_url() ); ?>">
  <div>
    <input class="search-text" type="text" name="s" value="<?php if ( is_search() ) echo esc_attr( get_search_query() ); ?>" onfocus="if (this.placeholder == '<?php _e('Search On This Site..',THEME_DOMAIN); ?>') {this.placeholder = '';}" onblur="if (this.placeholder == '') {this.placeholder = '<?php _e('Search On This Site..',THEME_DOMAIN); ?>';}" placeholder="<?php _e('Search On This Site..',THEME_DOMAIN); ?>" />
    <input class="search-submit button" name="submit" type="submit" value="<?php _e( 'Search', THEME_DOMAIN ); ?>" />
  </div>
</form>
<!-- .search-form -->
