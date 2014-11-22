<?php 	
if(is_admin() && isset($_REQUEST['page']) && "templatic_multiple_rating" == $_REQUEST['page']){
if(isset($_REQUEST['submit']) && $_REQUEST['submit'] != '')
{
	
	$rating_product_title = get_option('rating_product_title');
	if(!$rating_product_title)
		$rating_product_title = array();
	update_option('rating_product_title',array_merge($rating_product_title,$_REQUEST['rating_product_title']));
	$post_type = isset($_REQUEST['tab'])?$_REQUEST['tab']:$_REQUEST['tab_post_type'];
	update_option('rating_main_taxonomy'.$post_type,0);
	update_option('main_post_type','main_post_type');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));	
	if(is_plugin_active('woocommerce/woocommerce.php') && $post_type == 'product' ){
			$rate_taxonomies = $taxonomies[1];
	}else{
		$rate_taxonomies = $taxonomies[0];						
	}
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	
	$rating_title_post_type = get_option('rating_title'.$post_type);
	
	$args = array(
	'type'                     => $post_type,
	'orderby'                  => 'name',
	'order'                    => 'ASC',
	'hide_empty'               => 0,
	'taxonomy'                 => $rate_taxonomies,
	'pad_counts'               => false 
	); 
	/*Remove stitepress terms claises filer for display all langauge wise category show  */
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
		global $sitepress;
		remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
	}
	$categories = get_categories( $args );
	$category_rating_title = array();
	
	for($i=0;$i<count($categories);$i++)
	{
		$category_rating_title[$categories[$i]->term_id] = $rating_product_title[$post_type];
	}
	update_option('rating_title'.$post_type,$category_rating_title);	
}
if((isset($_REQUEST['category_submit']) && $_REQUEST['category_submit'] != '') )
{
	update_option('save_catetory',$_REQUEST['category']);
	update_option('rating_title',$_REQUEST['rating_title']);
	$post_type = isset($_REQUEST['tab'])?$_REQUEST['tab']:$_REQUEST['tab_post_type'];
	update_option('rating_main_taxonomy'.$post_type,0);	
	update_option('rating_title'.$post_type,$_REQUEST['rating_title_'.$post_type]);
}
if(isset($_REQUEST['submit_show_average_rating']) && $_REQUEST['submit_show_average_rating'] != '')
{
	update_option('show_average_rating',$_REQUEST['show_average_rating']);
}
/* NAME : FETCH CATEGORIES DROPDOWN
DESCRIPTION : THIS FUNCTION WILL FETCH THE CATEGORY DROPDOWN WHILE ADDING A PRICE PACKAGE OR CUSTOM FIELD */
function get_wp_category_checklist_rating_plugin($post_taxonomy,$pid='',$tab_key='')
{	
	$save_category = get_option('save_catetory');
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$pid = explode(',',$pid);
	global $wpdb;
	$taxonomy = $post_taxonomy;
	$table_prefix = $wpdb->prefix;
	$wpcat_id = NULL;
	$post_type = isset($_REQUEST['tab'])?$_REQUEST['tab']:$tab_key;
	$rating_title_post_type = get_option('rating_title'.$post_type);
	
	/* FETCH PARENT CATEGORY */
	if($taxonomy == "")
	{
		$custom_tax = @array_keys(get_option('templatic_custom_taxonomy'));
		$slugs = @implode(",",$custom_tax);
		$slugs .= ",category";		
		$wpcategories = (array)$wpdb->get_results
						("SELECT * FROM {$table_prefix}terms, {$table_prefix}term_taxonomy
						WHERE {$table_prefix}terms.term_id = {$table_prefix}term_taxonomy.term_id
						AND ({$table_prefix}term_taxonomy.taxonomy in ('" . str_replace(",", "','", $slugs) . "')) and  {$table_prefix}term_taxonomy.parent=0  ORDER BY {$table_prefix}terms.term_id");
	}
	else
	{
		$wpcategories = (array)$wpdb->get_results
						("SELECT * FROM {$table_prefix}terms, {$table_prefix}term_taxonomy
						WHERE {$table_prefix}terms.term_id = {$table_prefix}term_taxonomy.term_id
						AND {$table_prefix}term_taxonomy.taxonomy ='".$taxonomy."' and  {$table_prefix}term_taxonomy.parent=0  ORDER BY {$table_prefix}terms.term_id");
	}	
	$wpcategories = array_values($wpcategories);
	$wpcat2 = NULL;
	if($wpcategories)
	{
		$counter = 0;
		foreach ($wpcategories as $wpcat)
		{ 
			$counter++;
			$termid = $wpcat->term_id;;
			$name = ucfirst($wpcat->name); 
			$termprice = $wpcat->term_price;
			$tparent =  $wpcat->parent; 
			
			?>
			<div class='postbox' id="<?php echo $termid; ?>" ><div title='Click to toggle' class='handlediv' onclick="return show_rating_tag(this.id,'<?php echo $post_type; ?>');" id="<?php echo $termid; ?>"><br></div>
			<span class="row-title"  onclick="return show_rating_tag(this.id,'<?php echo $post_type; ?>');" id="<?php echo $termid; ?>"><h3 class="hndle"><span><?php echo " ".$name;  ?></span></h3></span>
			<div class="inside" style="display:none;" id="tblcat<?php echo $termid; ?>">
			
			
				<table cellpadding="1" cellspacing="0" style="display:none;" id="tblproduct<?php echo $termid; ?>" class="category_rating_table">
					<?php
					if($rating_product_title)
					{
					if((!empty($rating_product_title[$post_type]) && empty($rating_title[$termid]) && empty($rating_title_post_type[$termid][0]) ) || (get_option('rating_main_taxonomy'.$post_type,true) == 1 )  ):					
					
						for($i=0;$i<sizeof($rating_product_title[$post_type]);$i++):?>
							<tr class="rating_move_cursor">
                         	<td><input type="text" size="60" name="rating_title_<?php echo $post_type;?>[<?php echo $termid;?>][<?php echo $i; ?>]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_product_title[$post_type][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/>		
							</td>
                             <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>    
                         </tr>
						<?php endfor;
						endif;
					
					if(!empty($rating_title_post_type[$termid])  ):	
						for($i=0;$i<sizeof($rating_product_title[$post_type]);$i++):
							if(array_key_exists($i,$rating_title_post_type[$termid]))
							{
								?>
									<tr class="rating_move_cursor">
										<td><input type="text" size="60" name="rating_title_<?php echo $post_type;?>[<?php echo $termid;?>][<?php echo $i; ?>]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_product_title[$post_type][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/>		
										</td>
										 <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>    
									</tr>
						<?php 
							}
						endfor;
					endif;
				}
					if(!empty($rating_title[$termid][0]) ):
						for($i=0;$i<sizeof($rating_title[$termid]);$i++):
						?>                              	
                    	<tr class="rating_move_cursor">
                         	<td><input type="text" size="60" name="rating_title[<?php echo $termid;?>][]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_title[$termid][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/>
							</td>
                             <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>
							
                         </tr>
                    	<?php endfor;?>
                    <?php else:?>
                    <tr>
                    	<td><input type="text" size="60" name="rating_title[<?php echo $termid; ?>][]" id="product_title1" value=""  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/></td>
                    </tr>
                    <?php endif;?>
               </table>
			<a style="display:none;" href="javascript:void(0)" style="<?php if(!empty($save_category)) { if(in_array($termid,$save_category[$post_taxonomy])) { echo ""; } } else {  echo "display:none;";  }  ?>" class="link" id="add_<?php echo $termid; ?>" onClick="addproductrow('<?php echo $termid; ?>');"><?php _e('+ Add New',RATING_DOMAIN);?></a>
			<input style="display:none;margin-left:5px;" type="submit" class="button button-primary" id="category_submit_<?php echo $termid; ?>" name="category_submit" value="Save">
			<?php 
			echo "</div></div>";
			if($taxonomy !="")
				{
					$child = get_term_children( $termid, $post_taxonomy );
					$args = array('child_of'	=> $termid,
								'hide_empty'	=> 0,
								'orderby'       => 'id',
								'order'         => 'ASC',
								'taxonomy'		=> $post_taxonomy);
				
			/*Remove stitepress terms claises filer for display all langauge wise category show  */
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				global $sitepress;
				remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
			}
		 $categories = get_categories( $args );
		 foreach($categories as $child_of)
		 { 
			$child_of = $child_of->term_id; 
		 	$p = 0;
			$term = get_term_by( 'id', $child_of,$post_taxonomy);
			$termid = $term->term_id;
			$term_tax_id = $term->term_id;
			$termprice = $term->term_price;
			$name = $term->name;
			if($child_of)
			{
				$catprice = $wpdb->get_row("select * from $wpdb->term_taxonomy tt ,$wpdb->terms t where t.term_id='".$child_of."' and t.term_id = tt.term_id AND tt.taxonomy ='".$taxonomy."'");
				for($i=0;$i<count($catprice);$i++)
				{
					if($catprice->parent)
					{	
						$p++;
						$catprice1 = $wpdb->get_row("select * from $wpdb->term_taxonomy tt ,$wpdb->terms t where t.term_id='".$catprice->parent."' and t.term_id = tt.term_id AND tt.taxonomy ='".$taxonomy."'");
						if($catprice1->parent)
						{
							$i--;
							$catprice = $catprice1;
							continue;
						}
					}
				}
			}
			$p = $p*15;
			
		 ?>
			<div class='postbox' id="<?php echo $termid; ?>" ><div title='Click to toggle' class='handlediv' onclick="return show_rating_tag(this.id,'<?php echo $post_type; ?>');" id="<?php echo $termid; ?>"><br></div>
			<span class="row-title" onclick="return show_rating_tag(this.id,'<?php echo $post_type; ?>');" id="<?php echo $termid; ?>" ><h3 class="hndle"><span><?php echo " ".$name;  ?></span></h3></span>
			<div class="inside" style="display:none;" id="tblcat<?php echo $termid; ?>">
				<table cellpadding="1" cellspacing="0" style="display:none;" id="tblproduct<?php echo $termid; ?>">
					<?php
					if($rating_product_title)
					{
					if((!empty($rating_product_title[$post_type]) && empty($rating_title[$termid]) && empty($rating_title_post_type[$termid][0]) ) || (get_option('rating_main_taxonomy'.$post_type,true) == 1 )  ):					
						for($i=0;$i<sizeof($rating_product_title[$post_type]);$i++):?>
							<tr class="rating_move_cursor">
                         	<td><input type="text" size="60" name="rating_title_<?php echo $post_type;?>[<?php echo $termid;?>][<?php echo $i; ?>]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_product_title[$post_type][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/>		
							</td>
                             <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>    
                         </tr>
						<?php endfor;
						endif;
					if(!empty($rating_title_post_type[$termid])  ):	
						for($i=0;$i<sizeof($rating_product_title[$post_type]);$i++):
							if(array_key_exists($i,$rating_title_post_type[$termid]))
							{
								?>
									<tr class="rating_move_cursor">
										<td><input type="text" size="60" name="rating_title_<?php echo $post_type;?>[<?php echo $termid;?>][<?php echo $i; ?>]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_product_title[$post_type][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/>		
										</td>
										 <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>    
									</tr>
						<?php 
							}
						endfor;
					endif;
					}
					if(!empty($rating_title[$termid][0]) ):
						for($i=0;$i<sizeof($rating_title[$termid]);$i++):
						?>                              	
                    	<tr class="rating_move_cursor">
                         	<td><input type="text" size="60" name="rating_title[<?php echo $termid;?>][]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_title[$termid][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/></td>
                            <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>
                         </tr>
                    	<?php endfor;?>
                    <?php else:?>
                    <tr>
                    	<td><input type="text" size="60" name="rating_title[<?php echo $termid; ?>][]" id="product_title1" value=""  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/></td>
                    </tr>
                    
                    <?php endif;?>
               </table>
			<a style="display:none;" href="javascript:void(0)" style="<?php if(!empty($save_category)) { if(in_array($termid,$save_category[$post_taxonomy])) { echo ""; } } else {  echo "display:none;";  }  ?>" class="link" id="add_<?php echo $termid; ?>" onClick="addproductrow('<?php echo $termid; ?>');"><?php _e('+ Add New',RATING_DOMAIN);?></a>
			<input style="display:none;margin-left:5px;" class="button button-primary" type="submit" id="category_submit_<?php echo $termid; ?>" name="category_submit" value="Save">
		<?php  
		echo '</div></div>';
		}	}
		
		}
	}
	?>
	<?php
}
/* EOF - FETCH CATEGORIES DROPDOWN */
	
		//wp_enqueue_script('jquery-ui-tabs');
		?>
          
		<form name="show_average_rating_form" id="show_average_rating_form" action="" method="post" >
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label ><?php _e('Show average rating',RATING_DOMAIN); ?></label></th>
						<td>
							<label><input type="radio" name="show_average_rating" <?php if(get_option('show_average_rating') == 'hover'){?> checked=checked <?php } ?>value="hover"/><?php _e(' On hover',RATING_DOMAIN); ?></label>&nbsp;
							<label><input type="radio" name="show_average_rating"  <?php if(get_option('show_average_rating') == 'button'){?> checked=checked <?php } ?> value="button"><?php _e(' On click',RATING_DOMAIN); ?></label><p class="tevolution_desc"><?php _e('Selecting "On hover" will show the average ratings when a user hovers over the rating stars. Selecting "On click" will show the average ratings when the button is pressed. ')?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"></th>
						<td>
							<input type="submit" style="margin-left:5px;" class="button button-primary" id='submit_show_average_rating' name="submit_show_average_rating"  value="Save">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<form name="rating_form" id="rating_form" action="" method="post" style="padding-top:10px;">
		
				<?php
				$rating_product_title = get_option('rating_product_title');
				$posttaxonomy =  get_option('templatic_custom_post');
				
				$post_types = get_post_types();
				$i=0;
				$tab_key = '';
				echo "<h2 class='nav-tab-wrapper'>";
				 foreach($posttaxonomy as $key=>$post_types){
						$post_type = $post_types['rewrite']['slug'];
						if($post_type == 'admanager')
							continue;
						if(@$i==0)	
							$tab_key=$post_type;	
						
						$current_tab=isset($_REQUEST['tab'])?$_REQUEST['tab']:$tab_key;	
						$class = ( $post_type == $current_tab) ? ' nav-tab-active' : '';				
						echo "<a class='nav-tab$class' href='?page=templatic_multiple_rating&tab=$post_type'>".ucfirst($post_type)."</a>";	
						@$i++;
					
				}
				$current_tab=isset($_REQUEST['tab'])?$_REQUEST['tab']:'';	
				$class = ( 'post' == $current_tab) ? ' nav-tab-active' : '';				
				echo "<a class='nav-tab$class' href='?page=templatic_multiple_rating&tab=post'>".ucfirst('Post')."</a>";	
				echo "</h2>";
				
				$post_type = isset($_REQUEST['tab'])?$_REQUEST['tab']:$tab_key;
				$PostTypeObject = get_post_type_object($post_type);
				$_PostTypeName = $PostTypeObject->labels->name;
				?>
				<div class="inside_category_rating">
				<div class='metabox-holder'><div class='postbox-container' > <div class="poststuff"><div class='postbox'><div onclick="return show_rating_type('<?php echo $post_type; ?>');"  title='Click to toggle' class='handlediv'><br></div>
					<h3 onclick="return show_rating_type('<?php echo $post_type; ?>');"  class="hndle"><span><?php _e('Global rating option for',RATING_DOMAIN);echo " ".$_PostTypeName;?></span></h3>
					<div class="inside" id="tblpost_type<?php echo $post_type; ?>">
					
					<div style="margin-left:10px;margin-top:10px;" id="post_type_description" class="description"><?php _e('Options added here will be automatically assigned to all categories within this post type.',RATING_DOMAIN); ?></div>
                    <table class="form-table"  id="tblproduct<?php echo $post_type; ?>">
					
                    <?php
					
					if(!empty($rating_product_title[$post_type])):					
						for($i=0;$i<sizeof($rating_product_title[$post_type]);$i++):
				?>                              	
                    	<tr id='<?php echo $post_type."_rating_tab_row"; ?>' class="rating_move_cursor">
                         	<td><input type="text" size="60" name="rating_product_title[<?php echo $post_type; ?>][]" id="product_title<?php echo ($i+1);?>" value="<?php echo $rating_product_title[$post_type][$i]; ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/></td>
                             <td><a class="l_remove" onclick="removeRow(this)" href="javascript:void(0)"><?php _e('Remove',RATING_DOMAIN);?></a></td>
                         </tr>
                    	<?php endfor;?>
                    <?php else:?>
                    <tr  class="rating_move_cursor">
                    	<td><input type="text" size="60" name="rating_product_title[<?php echo $post_type; ?>][]" id="product_title1" value="<?php echo esc_attr(get_post_meta(@$post->ID,'product_title',true) ); ?>"  Placeholder="<?php _e(RATING_TITLE_NOTE,RATING_DOMAIN)?>"/></td>
                    </tr>
                    
                    <?php endif;?>
					</table>
							<a href="javascript:void(0)"  id="add_<?php echo $post_type; ?>" class="link" onClick="addtaxonomyrow('<?php echo $post_type; ?>');"><?php _e('+ Add New',RATING_DOMAIN);?></a>
							<input type="submit" style="margin-left:5px;" class="button button-primary" id='post_type_<?php echo $post_type; ?>' name="submit" onclick="return alert_submit_taxonomy_rating('<?php echo $post_type; ?>')" value="Save">
						<input type="hidden" name="tab_post_type" value="<?php echo $post_type;?>" />
						<input type="hidden" name="main_post_type" value="main_post_type" />
					</form>
					</div>
				</div>
				</div>
			</div>
		</div>
		</div>
		<form name="rating_taxonomy_form" id="rating_taxonomy_form" action="" method="post">
			<table class="form-table">
				<tr>
					<td>
						<?php
						$post_type = isset($_REQUEST['tab'])?$_REQUEST['tab']:$tab_key;	
						echo "<div id='".$post_type."_rating_tab' >";
						$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));	
						$rate_taxonomies = $taxonomies[0];						
						echo '<p><div class="tevo_sub_title">'.__('Category-specific rating options',RATING_DOMAIN).'</div></p>';
						echo '<p class="tevolution_desc">'.__('Use this section to define unique rating options for specific categories.',RATING_DOMAIN).'</p><div class="inside_category_rating"><div class="metabox-holder"><div class="postbox-container" ><div class="poststuff">';					
						 if($post_type == 'post')
						 {
							$tab_key = $post_type;
						 }
						 
						get_wp_category_checklist_rating_plugin($rate_taxonomies,'',$tab_key);
						echo "</div></div></div></div></div>";
						?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="tab_post_type" value="<?php echo $post_type;?>" />
		</form>
		<?php
}
add_action('admin_footer','add_rating_tag_script');
function add_rating_tag_script()
{
	wp_enqueue_script( 'jquery-ui-sortable' );
	?>
	<script>
	function show_rating_tag(term_id,post_type)
	{
		jQuery('#tblcat'+term_id).toggle();
		jQuery('#tblproduct'+term_id).toggle();
		jQuery('#add_'+term_id).toggle();
		jQuery('#category_submit_'+term_id).toggle();
	}
	function show_rating_type(post_type)
	{
		jQuery('#tblproduct'+post_type).toggle();
		jQuery('#tblpost_type'+post_type).toggle();
		jQuery('#add_'+post_type).toggle();
		jQuery('#post_type_'+post_type).toggle();
		jQuery('#post_type_description').toggle();
	}
	function addproductrow(cat_id,post_type)
	{
		var tbl = document.getElementById('tblproduct'+cat_id);
		var lastRow = tbl.rows.length;
		// if there's no header row in the table, then iteration = lastRow + 1
		var iteration = lastRow;
		//var iteration = lastRow + 1;
		var row = tbl.insertRow(lastRow);
		row.className = "rating_move_cursor";
		//  cell 0
		var cell0 = row.insertCell(0);
		var el = document.createElement('input');
		el.type = 'text';
		el.name = 'rating_title['+cat_id+'][]';
		el.size = 60;
		el.id = 'taxonomy_title'+iteration;
		el.placeholder = "<?php echo RATING_TITLE_NOTE; ?>";
		cell0.appendChild(el);
		var cell3 = row.insertCell(1);
		el = document.createElement('div');
		cell3.innerHTML = '<a href="javascript:void(0)" class="l_remove" onclick="removeRow(this)">Remove</a>';
		cell3.appendChild(el);
	}
	function addtaxonomyrow(post_type)
	{
		var tbl = document.getElementById('tblproduct'+post_type);
		var lastRow = tbl.rows.length;
		// if there's no header row in the table, then iteration = lastRow + 1
		var iteration = lastRow;
		//var iteration = lastRow + 1;
		var row = tbl.insertRow(lastRow);
		row.id = post_type+'_rating_tab_row';
		row.className = "rating_move_cursor";
		//  cell 0
		var cell0 = row.insertCell(0);
		var el = document.createElement('input');
		el.type = 'text';
		el.name = 'rating_product_title['+post_type+'][]';
		el.size = 60;
		el.placeholder = "<?php echo RATING_TITLE_NOTE; ?>";
		el.id = 'product_title'+iteration;
		cell0.appendChild(el);
		var cell3 = row.insertCell(1);
		el = document.createElement('div');
		cell3.innerHTML = '<a href="javascript:void(0)" class="l_remove" onclick="removeRow(this)">Remove</a>';
		cell3.appendChild(el);
	}
	function removeRow(theLink)
	{ 
		var theRow = theLink.parentNode.parentNode;
		var theBod = theRow.parentNode;
		theBod.removeChild(theRow);
	}
	function alert_submit_taxonomy_rating(post_type)
	{
		if(confirm('Adding this rating option to '+post_type+' will also add it to all its categories too. Do you want to save? '))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	jQuery.noConflict();
	jQuery(document).ready(function($){
	<?php	$tab =  isset($_REQUEST['tab'])?$_REQUEST['tab']:'post';	?>
			$("#tblproduct<?php echo $tab; ?>").sortable({items: "tr"}); 
			$(".category_rating_table").sortable({items: "tr"});
	});
	</script>
<?php
}
?>