<form id="event_searchform" action="<?php echo home_url(); ?>" method="get" role="search">
     <div>
          <label class="screen-reader-text" for="s"><?php _e('Search for',EDOMAIN)?>:</label>
          <input id="s" type="text" name="s" value="">
          <input type="hidden" name="post_type" value="event" />
          <input id="searchsubmit" type="submit" value="<?php _e("Search",EDOMAIN);?>">
     </div>
</form>