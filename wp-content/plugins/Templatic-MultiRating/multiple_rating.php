<?php
/*
Plugin Name: Templatic MultiRating
Plugin URI: http://templatic.com/docs/tevolution-multirating-plugin-guide/
Description: Templatic Multi Rating plugin helps you display and allow your users to rate your posts and listings out of 5 stars with their comments. With this plugin admin has options to display an average rating for all custom posts and listings or to have an individual option for each custom post type.
Version: 1.0.3
Author: Templatic
Author URI: http://templatic.com/
*/
// Plugin version
define( 'MULTIPLE_RATING_VERSION', '1.0.3' );
// Plugin Folder URL
define( 'MULTIPLE_RATING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Plugin Folder Path
define( 'MULTIPLE_RATING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// Plugin Root File
define( 'MULTIPLE_RATING_PLUGIN_FILE', __FILE__ );
//Define domain name
define('RATING_DOMAIN','multiple_rating');
define('TEMPLATIC_RATING_SLUG','Templatic-MultiRating/multiple_rating.php');
define('RATING_PLUGIN_FOLDER_NAME','Templatic-MultiRating');
define('RATING_TITLE_NOTE',__('Option name (e.g. Atmosphere, Quality of service, etc)',RATING_DOMAIN));


$locale = get_locale();
	//get_template_directory().'/languages/'.$locale.'.mo';
		if(file_exists(MULTIPLE_RATING_PLUGIN_DIR.'/languages/'.$locale.'.mo'))
		{
			load_textdomain(RATING_DOMAIN,MULTIPLE_RATING_PLUGIN_DIR.'/languages/'.$locale.'.mo');
		}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(strstr($_SERVER['REQUEST_URI'],'plugins.php')){
	require_once('wp-updates-plugin.php');
	new WPMultipleRatingUpdates( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
}
include(dirname(__FILE__)."/install.php");
register_activation_hook(__FILE__,'add_multiple_rating');
	if(!function_exists('add_multiple_rating')){
	function add_multiple_rating(){
		update_option('templatic_multiple_rating','Active');
		update_option('rating_redirect_on_first_activation','Active');
	}
}
/*
 * Function Name: rating_update_login
 * Return: update templatic rating plugin version after templatic member login
 */
add_action('wp_ajax_templatic_multiRating','rating_update_login');
function rating_update_login()
{ 
	check_ajax_referer( 'MultiRating', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
add_action('admin_init', 'rating_plugin_redirect');
/*
Name : rating_plugin_redirect
Description : Redirect on plugin dashboard after activating plugin
*/
function rating_plugin_redirect()
{
  if (get_option('rating_redirect_on_first_activation') == 'Active')
  {
    update_option('rating_redirect_on_first_activation', 'Deactive');
	if(is_plugin_active('Tevolution/templatic.php')){
		wp_redirect(site_url().'/wp-admin/admin.php?page=templatic_multiple_rating');
	}
	else
	{
		wp_redirect(site_url().'/wp-admin/options-general.php?page=templatic_multiple_rating');
	}
  }
}
/*
Name : rating_plugin_deactivate
Description : Delete the option of redirect.
*/
function rating_plugin_deactivate() { 
	delete_option('rating_redirect_on_first_activation');
}
register_deactivation_hook(__FILE__, 'rating_plugin_deactivate');
/*
Name : comment_has_rating
Description : Add a column to the comment rating if there is an rating for the given comment at backend.
*/
add_filter( 'manage_edit-comments_columns', 'comment_has_rating' );
add_filter( 'manage_comments_custom_column', 'comment_rating' , 22, 2);
function comment_has_rating($columns)
{
	 $columns['comment-rating'] = __( 'Comment Rating', 'comment-rating' );
	 return $columns;
}
function comment_rating( $column_name, $comment_id ) {
global $post;
 if( 'comment-rating' == strtolower( $column_name ) ) {
	 if( 0 != ( $comment_image_data = get_comment_meta( $comment_id, 'comment_rating', true ) ) ) {
		 $html = admin_comment_average_rating($comment_id);
		 echo $html;
	 } // end if
 } // end if/else
} // end comment_rating
/*
Name : admin_comment_average_rating
Description : calculate average rating for particular comment at comment tab at backend.
*/
function admin_comment_average_rating($comment_id)
{
	global $post,$term_rating_label;
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	
	$comment_rating = get_comment_meta( $comment_id, 'comment_rating', true );
	if($comment_rating)
	{
		if(!empty($comment_rating))
		{
			for($i=1;$i<=count($comment_rating);$i++)
			{
				$rating_per_comment += $comment_rating[$i][0];
				$count++;
			}
		}
		$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
		$average_rating = explode(".",$avarage_rating_per_comment);
		if($average_rating[1] >=5)
		{
			$avarage_rating_per_comment = $average_rating[0].".5";
		}
		else
		{
			$avarage_rating_per_comment = $average_rating[0];
		}
	}
	
	$rat1=($avarage_rating_per_comment == "0.5")?' checked="checked" ':'';
	$rat2=($avarage_rating_per_comment == "1.0")?' checked="checked" ':'';
	$rat3=($avarage_rating_per_comment == "1.5")?' checked="checked" ':'';
	$rat4=($avarage_rating_per_comment == "2.0")?' checked="checked" ':'';
	$rat5=($avarage_rating_per_comment == "2.5")?' checked="checked" ':'';
	$rat6=($avarage_rating_per_comment == "3.0")?' checked="checked" ':'';
	$rat7=($avarage_rating_per_comment == "3.5")?' checked="checked" ':'';
	$rat8=($avarage_rating_per_comment == "4.0")?' checked="checked" ':'';
	$rat9=($avarage_rating_per_comment == "4.5")?' checked="checked" ':'';
	$rat10=($avarage_rating_per_comment == "5.0")?' checked="checked" ':'';
	
	
	$rating_html =  '<div class="average_rating clear_rating">
				<div id="rate"> 
					<input class="star {split:2}" '. $rat1 .' type="radio" name="single_rating'.$comment_id.'" value="0.5" disabled="disabled"/>
					<input class="star {split:2}" '. $rat2 .' type="radio" name="single_rating'.$comment_id.'" value="1.0" disabled="disabled"/>
					<input class="star {split:2}" '. $rat3 .' type="radio" name="single_rating'.$comment_id.'" value="1.5" disabled="disabled"/>
					<input class="star {split:2}" '. $rat4 .' type="radio" name="single_rating'.$comment_id.'" value="2.0" disabled="disabled"/>
					<input class="star {split:2}" '. $rat5 .' type="radio" name="single_rating'.$comment_id.'" value="2.5" disabled="disabled"/>
					<input class="star {split:2}" '. $rat6 .' type="radio" name="single_rating'.$comment_id.'" value="3.0" disabled="disabled"/>
					<input class="star {split:2}" '. $rat7 .' type="radio" name="single_rating'.$comment_id.'" value="3.5" disabled="disabled"/>
					<input class="star {split:2}" '. $rat8 .' type="radio" name="single_rating'.$comment_id.'" value="4.0" disabled="disabled"/>
					<input class="star {split:2}" '. $rat9 .' type="radio" name="single_rating'.$comment_id.'" value="4.5" disabled="disabled"/>
					<input class="star {split:2}" '. $rat10 .' type="radio" name="single_rating'.$comment_id.'" value="5.0" disabled="disabled"/>
				</div>
		   </div>';
	return $rating_html;
}
 
 /*
Name : admin_rating_script
Description : include script for rating at backend.
*/
add_action('admin_head','admin_rating_script');
function admin_rating_script()
{
	if((strstr($_SERVER['REQUEST_URI'],'edit-comments.php')))
	{
		echo '<link media="all" type="text/css" href="'.MULTIPLE_RATING_PLUGIN_URL."css/jquery.rating.css".'" rel="stylesheet">';
		wp_enqueue_script( 'rating-scripts', trailingslashit ( MULTIPLE_RATING_PLUGIN_URL ) . 'js/jquery.rating-mini.js', array( 'jquery' ), '20120606', true );
		wp_enqueue_script( 'rating_pack-scripts', trailingslashit ( MULTIPLE_RATING_PLUGIN_URL ) . 'js/jquery.MetaData-mini.js', array( 'jquery' ), '20120606', true );
	}
}
 /*
Name : admin_rating_script
Description : show rating star while submitting an acomment.
*/
function rating_comment_text_after($arg) {
	global $post,$wpdb;
	//$arg['comment_field'] = '';
	$post_type_rating = 0;
	$hide_maintaxonomy_rating = get_option('hide_maintaxonomy_rating');
	$rating_title = get_option('rating_title');
	$rating_title_post_type = get_option('rating_title'.$post->post_type);
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	static $rating_html = '';
	$rating_html .= '<div class="form_comment_rating_wrap" style="display:none">';
	$rating_title_post_type_array = array();
	foreach($terms as $term)
	{
		if(!empty($rating_title_post_type[$term->term_id]))
		{
			foreach($rating_title_post_type[$term->term_id] as $key=>$value)
			{
				if($rating_title_post_type[$term->term_id][$key] != '' && !in_array($key,$rating_title_post_type_array))
				{
					if(function_exists('icl_register_string')){
						icl_register_string(RATING_DOMAIN,$value,$value);
						$value = icl_t(RATING_DOMAIN,$value,$value);
					}
					$rating_html .= '<div id="rate">'.'<span class="rating_desc"> '.$value.'</span> <input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="0.5" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="1.0" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="1.5" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="2.0" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="2.5" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="3.0" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="3.5" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="4.0" />
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="4.5"/>
														<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="5.0" />
										</div>';
					array_push($rating_title_post_type_array,$key);
					$k++;
				}
			}
		}
		
		for($i=0;$i<count(@$rating_title[$term->term_id]);$i++)
		{
			if($rating_title[$term->term_id][$i] != '')
			{
				if(function_exists('icl_register_string')){
					icl_register_string(RATING_DOMAIN,$rating_title[$term->term_id][$i],$rating_title[$term->term_id][$i]);
					$rating_title[$term->term_id][$i] = icl_t(RATING_DOMAIN,$rating_title[$term->term_id][$i],$rating_title[$term->term_id][$i]);
				}
				$rating_html .= '<div id="rate">'.'<span class="rating_desc"> '.$rating_title[$term->term_id][$i].'</span> <input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="0.5" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="1.0" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="1.5" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="2.0" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="2.5" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="3.0" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="3.5" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="4.0" />
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="4.5"/>
													<input class="star {split:2}" type="radio" name="comment_rating['.$k.'][]" value="5.0" />
									</div>';
				$k++;
			}
		}
	}
	$rating_html .= '</div>'; 
	wp_nonce_field( plugin_basename( __FILE__ ), 'comment_rating_display_nonce' );
	$arg['comment_field'] = $rating_html.$arg['comment_field'];
    return $arg;
}
add_filter('comment_form_defaults', 'rating_comment_text_after',100);
 /*
Name : save_plugin_comment_rating
Description : save rating in comment meta table and average of that particular post in post meta.
*/
add_filter( 'wp_insert_comment','save_plugin_comment_rating'  );
function save_plugin_comment_rating($comment_id)
{
	global $post;
	$post_id = $_POST['comment_post_ID'];
	if(!empty($_REQUEST['comment_rating']))
	{
		add_comment_meta( $comment_id, 'comment_rating', $_REQUEST['comment_rating'] );
		$average_rating = get_single_average_rating_count($post_id);
		update_post_meta($post_id,'average_rating',$average_rating);
	}
}
 /*
Name : display_comment_rating
Description : show rating for an individual comment on detail page.
*/
add_filter( 'comments_array', 'display_comment_rating',100);
function display_comment_rating($comments)
{
	global $post;
	$hoverclass = '';
	$mouse_hover = '';
	if( count( $comments ) > 0 ) 
	{
		// Loop through each comment...
		$k = 1;
		foreach( $comments as $comment ) 
		{
			// ...and if the comment has a comment rating...
			if( true == get_comment_meta( $comment->comment_ID, 'comment_rating' ) ) 
			{
				
				$comment_rating = get_comment_meta( $comment->comment_ID, 'comment_rating', true );
				$rating_per_comment = 0;
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
				}
				$avarage_rating_per_comment = round(($rating_per_comment/count($comment_rating)),1);
				$average_rating = explode(".",$avarage_rating_per_comment);
				if($average_rating[1] >=5)
				{
					$avarage_rating_per_comment = $average_rating[0].".5";
				}
				else
				{
					$avarage_rating_per_comment = $average_rating[0];
				}
				
				$rat1=($avarage_rating_per_comment == "0.5")?' checked="checked" ':'';
				$rat2=($avarage_rating_per_comment == "1.0")?' checked="checked" ':'';
				$rat3=($avarage_rating_per_comment == "1.5")?' checked="checked" ':'';
				$rat4=($avarage_rating_per_comment == "2.0")?' checked="checked" ':'';
				$rat5=($avarage_rating_per_comment == "2.5")?' checked="checked" ':'';
				$rat6=($avarage_rating_per_comment == "3.0")?' checked="checked" ':'';
				$rat7=($avarage_rating_per_comment == "3.5")?' checked="checked" ':'';
				$rat8=($avarage_rating_per_comment == "4.0")?' checked="checked" ':'';
				$rat9=($avarage_rating_per_comment == "4.5")?' checked="checked" ':'';
				$rat10=($avarage_rating_per_comment == "5.0")?' checked="checked" ':'';
				
				if(get_option('show_average_rating') == 'button')
				{
					$mouse_hover = 'butoontooltipitem';
				}
				else
				{
					$mouse_hover = 'onmouseover="return show_rating('.$comment->comment_ID.')" onmouseout="return hide_rating('.$comment->comment_ID.')"';
				}
				if(count($comment_rating) > 1)
				{
					$rating_text = __('Average Rating',RATING_DOMAIN);
				}
				else
				{
					$rating_text = __('Rating',RATING_DOMAIN);
				}
				
				$avg_rate = $avarage_rating_per_comment;
				$output = '';
				for($i=1;$i<=5;$i++)
				{
					if($avg_rate < 1 && $avg_rate >0 )
					{
						$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
						$avg_rate = 0;
					}
					elseif($i <= $avg_rate)
					{
						$rate = explode(".",$avg_rate);
						if($rate[0] == $i)
						{
							if($rate[1] > 0)
							{
								$avg_rate = "0".".".$rate[1];
							}
						}
						$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
					}
					else
					{
						$output .= apply_filters('rating_star_normal','<span><i class="fa fa-star rating-off"></i></span>');
					}
				}
				$comment->comment_content .= '<div class="average_rating clear_rating"><div class="average_rating_title">'.$rating_text.'</div><div id="rate" '.$mouse_hover.' > '.$output.'
												</div>';
												if(count($comment_rating) > 1)
												{
												
												if(get_option('show_average_rating') == 'button')
												{
													$comment->comment_content .= '<div class="clear_rating button" onclick="return show_rating('.$comment->comment_ID.')">'.__('Show individual rating',RATING_DOMAIN).'</div>';
													$hoverclass = "butoontooltipitem";
												}
												else
												{
														$hoverclass = "readtooltipitem";
												}
													
												
												$comment->comment_content .= '<div class="'.$hoverclass.'" style="display:none;" id="comment_rating_'.$comment->comment_ID.'">';
											
												$post_type_rating = 0;
												$hide_maintaxonomy_rating = get_option('hide_maintaxonomy_rating');
												$rating_title = get_option('rating_title');
												$rating_product_title = get_option('rating_product_title');
												$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
												$terms = get_the_terms($post->ID,$taxonomies[0]);
												$comment_rating = get_comment_meta( $comment->comment_ID, 'comment_rating', true );
												
												$rating_html = '';
												$l = 1;
												$rating_title_post_type_array = array();
												$rating_title_post_type = get_option('rating_title'.$post->post_type);
												foreach($terms as $term)
												{
													foreach($rating_title_post_type[$term->term_id] as $key=>$value)
													{
														if($rating_title_post_type[$term->term_id][$key] != '' && !in_array($key,$rating_title_post_type_array))
														{
															
															if(function_exists('icl_register_string')){
																icl_register_string(RATING_DOMAIN,$value,$value);
																$value = icl_t(RATING_DOMAIN,$value,$value);
															}
															$avg_rate  = $comment_rating[$l][0];
															$output = '';
															for($i=1;$i<=5;$i++)
															{
																if($avg_rate < 1 && $avg_rate >0 )
																{
																	$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
																	$avg_rate = 0;
																}
																elseif($i <= $avg_rate)
																{
																	$rate = explode(".",$avg_rate);
																	if($rate[0] == $i)
																	{
																		if($rate[1] > 0)
																		{
																			$avg_rate = "0".".".$rate[1];
																		}
																	}
																	$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
																}
																else
																{
																	$output .= apply_filters('rating_star_normal','<span><i class="fa fa-star rating-off"></i></span>');
																}
															}
															$rating_html .= '<div id="rate"><span class="rating_text">'.$value.'</span> '.$output.'
																				</div>';
															array_push($rating_title_post_type_array,$key);
															$k++;
															$l++;
														}
													}
													//if($comment_rating[$l][0])
													{
														for($r=0;$r<count($rating_title[$term->term_id]);$r++)
														{
															
															if($rating_title[$term->term_id][$r] != '')
															{
																
																$output = '';
																
																$avg_rate  = $comment_rating[$l][0];
																for($i=1;$i<=5;$i++)
																{
																	if($avg_rate < 1 && $avg_rate >0 )
																	{
																		$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
																		$avg_rate = 0;
																	}
																	elseif($i <= $avg_rate)
																	{
																		$rate = explode(".",$avg_rate);
																		if($rate[0] == $i)
																		{
																			if($rate[1] > 0)
																			{
																				$avg_rate = "0".".".$rate[1];
																			}
																		}
																		$output .=apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
																	}
																	else
																	{
																		$output .= apply_filters('rating_star_normal','<span><i class="fa fa-star rating-off"></i></span>');
																	}
																}
															if(function_exists('icl_register_string')){
																icl_register_string(RATING_DOMAIN,$rating_title[$term->term_id][$i],$rating_title[$term->term_id][$i]);
																$rating_title[$term->term_id][$i] = icl_t(RATING_DOMAIN,$rating_title[$term->term_id][$i],$rating_title[$term->term_id][$i]);
															}
															
															$rating_html .= '<div id="rate"><span class="rating_text">'.$rating_title[$term->term_id][$r].'</span>'.$output.'</div>';
																$k++;
																$l++;
															}
														}
													}
												}
											}
											$comment->comment_content .= $rating_html.'</div></div>';
			}
		}
	}
	return $comments;
}
 /*
Name : rating_script
Description : include rating script for rating an comment.
*/
add_action('wp_head','rating_script');
function rating_script()
{
	global $post;
	if(is_single() && get_post_type($post->ID) != 'product'){
		echo '<link media="all" type="text/css" href="'.MULTIPLE_RATING_PLUGIN_URL."css/jquery.rating.css".'" rel="stylesheet">';
		wp_enqueue_script( 'rating-scripts', trailingslashit ( MULTIPLE_RATING_PLUGIN_URL ) . 'js/jquery.rating-mini.js', array( 'jquery' ), '20120606', true );
		wp_enqueue_script( 'rating_pack-scripts', trailingslashit ( MULTIPLE_RATING_PLUGIN_URL ) . 'js/jquery.MetaData-mini.js', array( 'jquery' ), '20120606', true );		
	}
}
 /*
Name : post_detail_comment_comment
Description : show average rating above the comment.
*/
add_action('show_comment','post_detail_comment_comment');
function post_detail_comment_comment($comments)
{
	global $post,$term_rating_label_detail;
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	$rating_title_post_type_array = array();
	$rating_title_post_type = get_option('rating_title'.$post->post_type);
	
	foreach($terms as $term)
	{
		if(!empty($rating_title_post_type[$term->term_id]))
		{
			foreach($rating_title_post_type[$term->term_id] as $key=>$value)
			{
				if($rating_title_post_type[$term->term_id][$key] != '' && !in_array($key,$rating_title_post_type_array))
				{
					$term_rating_label_detail[] = $value;
					array_push($rating_title_post_type_array,$key);
				}
			}
		}
		for($i=0;$i<count($rating_title[$term->term_id]);$i++)
		{
			if($rating_title[$term->term_id][$i] != '')
			{
				$term_rating_label_detail[] = $rating_title[$term->term_id][$i];
			}
		}	
	}
	
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post->ID,
	'status'=> 'approve'
	); 
	$comment_rating = "";
	for($r=0;$r<count(get_comments($defaults));$r++)
	{
		$commnet_array = get_comments($defaults);
		$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}
	}
	if(count(get_comments($defaults)) > 0 )
	{
		$rat1=($avarage_rating_per_comment == "0.5")?' checked="checked" ':'';
		$rat2=($avarage_rating_per_comment == "1.0")?' checked="checked" ':'';
		$rat3=($avarage_rating_per_comment == "1.5")?' checked="checked" ':'';
		$rat4=($avarage_rating_per_comment == "2.0")?' checked="checked" ':'';
		$rat5=($avarage_rating_per_comment == "2.5")?' checked="checked" ':'';
		$rat6=($avarage_rating_per_comment == "3.0")?' checked="checked" ':'';
		$rat7=($avarage_rating_per_comment == "3.5")?' checked="checked" ':'';
		$rat8=($avarage_rating_per_comment == "4.0")?' checked="checked" ':'';
		$rat9=($avarage_rating_per_comment == "4.5")?' checked="checked" ':'';
		$rat10=($avarage_rating_per_comment == "5.0")?' checked="checked" ':'';
		if(count(get_comments($defaults)) > 1)
		{
			$review_label = __('reviews',RATING_DOMAIN);
			$rating_text = '';
		}
		else
		{
			$review_label = __('review',RATING_DOMAIN);
			$rating_text = '';
		}
		if(get_option('show_average_rating') == 'button')
		{
			$mouse_hover = '';
		}
		else
		{
			$mouse_hover = 'onmouseover="return show_rating(\'show_rating\')" onmouseout="return hide_rating(\'show_rating\')"';
		}
		
		echo  '<div class="average_rating_wrapper"><div class="average_rating clear_rating">
					<div id="rate" '.$mouse_hover.'>'.' '.$rating_text.' 
						'.get_single_average_rating_image($post->ID).' '.__('out of',RATING_DOMAIN).' '.sprintf('%s %s',count(get_comments($defaults)),$review_label).'</span>
					</div>
			  ';
			  
		$values = array('rating_sum');
		$defaults = array(
		'post_id' => $post->ID,
		'status'=> 'approve'
		);
		if(count(get_comments($defaults)) > 1)
		{
		if(get_option('show_average_rating') == 'button')
		{
			echo '<div class="clear_rating button" onclick="return show_average_rating('.$comment->comment_ID.')">'.__('Show individual rating',RATING_DOMAIN).'</div>';
		}
		else
		{
				$hoverclass1 = "readtooltipitem";
		}
		echo '<div style="display:none;" class="'.$hoverclass1.'" id="comment_rating_show_rating">';
		for($r=0;$r<count(get_comments($defaults));$r++)
		{
			$commnet_array = get_comments($defaults);
			$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
			
			$rating_per_comment = 0;
			$i = 1;
			if(!empty($comment_rating))
			{
				foreach($comment_rating as $key=>$val)
				{	
					$values['rating_sum'][$key] += $comment_rating[$key][0];
					$i++;
				}
			}
		}
		
		$rating_array = $values['rating_sum'];
		if(!empty($rating_array))
		{
			ksort($rating_array);//sort rating by key...
			$label_rating = 0;
			foreach ($rating_array as $key => $val) {
			
			$defaults = array(
			'post_id' => $post->ID,
			'status'=> 'approve'
			);
			
			$avarage_rating_per_comment = round(($val/count(get_comments($defaults))),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
			$output = '';
			$avg_rate  = $avarage_rating_per_comment;
			for($t=1;$t<=5;$t++)
			{
				if($avg_rate < 1 && $avg_rate >0 )
				{
					$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
					$avg_rate = 0;
				}
				elseif($t <= $avg_rate)
				{
					$rate = explode(".",$avg_rate);
					if($rate[0] == $t)
					{
						if($rate[1] > 0)
						{
							$avg_rate = "0".".".$rate[1];
						}
					}
					$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
				}
				else
				{
					$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
				}
			}
			
			$rating_title = get_option('rating_title');
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
			$terms = get_the_terms($post->ID,$taxonomies[0]);
			
			
				if($term_rating_label_detail[$label_rating] != '')
				{
					if(function_exists('icl_register_string')){
						icl_register_string(RATING_DOMAIN,$term_rating_label_detail[$label_rating],$term_rating_label_detail[$label_rating]);
						$term_rating_label_detail[$label_rating] = icl_t(RATING_DOMAIN,$term_rating_label_detail[$label_rating],$term_rating_label_detail[$label_rating]);
					}
					echo  '<div class="individual_average_rating"><div id="rate"><span class="rating_text">'.' '.$term_rating_label_detail[$label_rating].'</span>'.$output.'
												</div></div> ';
				}
				$label_rating++;
			}
		}
		echo '</div>';
	}
	echo '</div></div>';
	}
}
 /*
Name : single_average_rating
Description : show average rating.
*/
function single_average_rating($post_id)
{
	global $post,$term_rating_label;
	$rating_title = get_option('rating_title');
	
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	
	
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post_id,
	'status'=> 'approve'
	); 
	
	for($r=0;$r<count(get_comments($defaults));$r++)
	{
		$commnet_array = get_comments($defaults);
		$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if(@$average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}
	}
	//if(count(get_comments($defaults)) > 0 )
	{
		$rat1=($avarage_rating_per_comment == "0.5")?' checked="checked" ':'';
		$rat2=($avarage_rating_per_comment == "1.0")?' checked="checked" ':'';
		$rat3=($avarage_rating_per_comment == "1.5")?' checked="checked" ':'';
		$rat4=($avarage_rating_per_comment == "2.0")?' checked="checked" ':'';
		$rat5=($avarage_rating_per_comment == "2.5")?' checked="checked" ':'';
		$rat6=($avarage_rating_per_comment == "3.0")?' checked="checked" ':'';
		$rat7=($avarage_rating_per_comment == "3.5")?' checked="checked" ':'';
		$rat8=($avarage_rating_per_comment == "4.0")?' checked="checked" ':'';
		$rat9=($avarage_rating_per_comment == "4.5")?' checked="checked" ':'';
		$rat10=($avarage_rating_per_comment == "5.0")?' checked="checked" ':'';
		
		if(count(get_comments($defaults)) <=1 )
		{
			$review = __('review',RATING_DOMAIN);
		}
		else
		{
			$review = __('reviews',RATING_DOMAIN);
		}
		$rand_no = rand();
		echo  '<div class="average_rating clear_rating">
					<div id="rate"> 
						<input class="star {split:2}" '. $rat1 .' type="radio" name="single_rating'.$rand_no.'" value="0.5" disabled="disabled"/>
						<input class="star {split:2}" '. $rat2 .' type="radio" name="single_rating'.$rand_no.'" value="1.0" disabled="disabled"/>
						<input class="star {split:2}" '. $rat3 .' type="radio" name="single_rating'.$rand_no.'" value="1.5" disabled="disabled"/>
						<input class="star {split:2}" '. $rat4 .' type="radio" name="single_rating'.$rand_no.'" value="2.0" disabled="disabled"/>
						<input class="star {split:2}" '. $rat5 .' type="radio" name="single_rating'.$rand_no.'" value="2.5" disabled="disabled"/>
						<input class="star {split:2}" '. $rat6 .' type="radio" name="single_rating'.$rand_no.'" value="3.0" disabled="disabled"/>
						<input class="star {split:2}" '. $rat7 .' type="radio" name="single_rating'.$rand_no.'" value="3.5" disabled="disabled"/>
						<input class="star {split:2}" '. $rat8 .' type="radio" name="single_rating'.$rand_no.'" value="4.0" disabled="disabled"/>
						<input class="star {split:2}" '. $rat9 .' type="radio" name="single_rating'.$rand_no.'" value="4.5" disabled="disabled"/>
						<input class="star {split:2}" '. $rat10 .' type="radio" name="single_rating'.$rand_no.'" value="5.0" disabled="disabled"/>
					</div>
			   </div>';
			   
		if(is_single()){ 
			echo '<div class="rating_text">';
			echo apply_filters('tev_reviews_text',$post_id,$review);
			echo '</div>';
		}
	}
}
 /*
Name : tev_display_reviews
Description : filter to show review text.
*/
add_filter('tev_reviews_text','tev_display_reviews',10,2);
function tev_display_reviews($post_id,$review){
	$defaults = array(
	'post_id' => $post_id,
	'status'=> 'approve'
	); 
	return sprintf('<a href="#comments"> %s %s</a>',count(get_comments($defaults)), $review);
}
 /*
Name : get_single_average_rating_count
Description : return average count of a comment rating.
*/
function get_single_average_rating_count($post_id){
	
	global $post,$term_rating_label;
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	foreach($terms as $term)
	{
		for($i=0;$i<count(@$rating_title[$term->term_id]);$i++)
		{
			if($rating_title[$term->term_id][$i] != '')
			{
				$term_rating_label[] = $rating_title[$term->term_id][$i];
			}
		}
	}
	
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post_id,
	'status'=> 'approve'
	); 
	
	for($r=0;$r<count(get_comments($defaults));$r++)
	{
		$commnet_array = get_comments($defaults);
		$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}
	}
	return $avarage_rating_per_comment;
}
/*
 * Function name: get_single_average_rating
 * Return: display small rating start in google map popup window
 */
 
function get_single_average_rating($post_id){
	
	global $post,$term_rating_label;
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	if(!empty($terms))
	{
		foreach($terms as $term)
		{
			for($i=0;$i<count(@$rating_title[$term->term_id]);$i++)
			{
				if($rating_title[$term->term_id][$i] != '')
				{
					$term_rating_label[] = $rating_title[$term->term_id][$i];
				}
			}
		}
	}
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post_id,
	'status'=> 'approve'
	); 
	
	for($r=0;$r<count(get_comments($defaults));$r++)
	{
		$commnet_array = get_comments($defaults);
		$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}
	}
		
		
	$avg_rate=$avarage_rating_per_comment;
	for($i=1;$i<=5;$i++)
	{
		if($avg_rate < 1 && $avg_rate >0 )
		{ 
			$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
			$avg_rate = 0;
		}
		elseif($i <= $avg_rate)
		{
			$rate = explode(".",$avg_rate);
			if($rate[0] == $i)
			{
				if($rate[1] > 0)
				{
					$avg_rate = "0".".".$rate[1];
				}
			} 
			$output .= apply_filters('rating_star_rated','<i class="rating-on"></i>');
		}
		else 
		{
			$output .= apply_filters('rating_star_normal','<i class="rating-off"></i>');
		}
	}
		
	
	return $output;	
}
/*
 * Function name: get_single_average_rating_image
 * Return: display the normal star image in archive page, category page, tag page and any more
 */
 
function get_single_average_rating_image($post_id){
	
	global $post,$term_rating_label;
	$output = '';
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	if(!empty($terms))
	{
		foreach($terms as $term)
		{
			for($i=0;$i<count(@$rating_title[$term->term_id]);$i++)
			{
				if($rating_title[$term->term_id][$i] != '')
				{
					$term_rating_label[] = $rating_title[$term->term_id][$i];
				}
			}
		}
	}
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post_id,
	'status'=> 'approve'
	); 
	
	for($r=0;$r<count(get_comments($defaults));$r++)
	{
		$commnet_array = get_comments($defaults);
		$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}
	}
		
		
	$avg_rate=$avarage_rating_per_comment;
	for($i=1;$i<=5;$i++)
	{
		if($avg_rate < 1 && $avg_rate >0 )
		{
			$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
			$avg_rate = 0;
		}
		elseif($i <= $avg_rate)
		{
			$rate = explode(".",$avg_rate);
			if($rate[0] == $i)
			{
				if($rate[1] > 0)
				{
					$avg_rate = "0".".".$rate[1];
				}
			}
			$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
		}
		else
		{
			$output .= apply_filters('rating_star_normal','<span><i class="fa fa-star rating-off"></i></span>');
		}
	}
		
	
	return $output;	
}
/*
 * Function name: get_single_comment_rating_image
 * Return: display the normal star image in archive page, category page, tag page and any more
 */
 
function get_single_comment_rating_image($comment_id){
	
	global $post,$term_rating_label;
	$output = '';
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	if(!empty($terms))
	{
		foreach($terms as $term)
		{
			for($i=0;$i<count(@$rating_title[$term->term_id]);$i++)
			{
				if($rating_title[$term->term_id][$i] != '')
				{
					$term_rating_label[] = $rating_title[$term->term_id][$i];
				}
			}
		}
	}
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post_id,
	'status'=> 'approve'
	); 

	
		$comment_rating = get_comment_meta( $comment_id, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}

		
		
	$avg_rate =$avarage_rating_per_comment;
	if($avg_rate > 0)
	{
		for($i=1;$i<=5;$i++)
		{
			if($avg_rate < 1 && $avg_rate >0 )
			{
					$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
				$avg_rate = 0;
			}
			elseif($i <= $avg_rate)
			{
				$rate = explode(".",$avg_rate);
				if($rate[0] == $i)
				{
					if($rate[1] > 0)
					{
						$avg_rate = "0".".".$rate[1];
					}
				}
				$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
			}
			else
			{
				$output .= apply_filters('rating_star_normal','<span><i class="fa fa-star rating-off"></i></span>');
			}
		}
	}
	
	return $output;	
}
/*
 * Function name: get_single_average_rating_image
 * Return: display the normal star image in archive page, category page, tag page and any more
 */
 
function get_single_page_average_rating_image($post_id){
	
	global $post,$single_term_rating_label_detail;
	$rating_title = get_option('rating_title');
	$rating_product_title = get_option('rating_product_title');
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
	$terms = get_the_terms($post->ID,$taxonomies[0]);
	$k = 1;
	$rating_title_post_type_array = array();
	$rating_title_post_type = get_option('rating_title'.$post->post_type);
	
	foreach($terms as $term)
	{
		if(!empty($rating_title_post_type[$term->term_id]))
		{
			foreach($rating_title_post_type[$term->term_id] as $key=>$value)
			{
				if($rating_title_post_type[$term->term_id][$key] != '' && !in_array($key,$rating_title_post_type_array))
				{
					$single_term_rating_label_detail[] = $value;
					array_push($rating_title_post_type_array,$key);
				}
			}
		}
		for($i=0;$i<count($rating_title[$term->term_id]);$i++)
		{
			if($rating_title[$term->term_id][$i] != '')
			{
				$single_term_rating_label_detail[] = $rating_title[$term->term_id][$i];
			}
		}	
	}
	
	$avarage_rating_per_comment = 0;
	$rating_per_comment = 0;
	$count = 0;
	$defaults = array(
	'post_id' => $post->ID,
	'status'=> 'approve'
	); 
	$comment_rating = "";
	for($r=0;$r<count(get_comments($defaults));$r++)
	{
		$commnet_array = get_comments($defaults);
		$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
		if($comment_rating)
		{
			if(!empty($comment_rating))
			{
				for($i=1;$i<=count($comment_rating);$i++)
				{
					$rating_per_comment += $comment_rating[$i][0];
					$count++;
				}
			}
			$avarage_rating_per_comment = round(($rating_per_comment/$count),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
		}
	}
	if(count(get_comments($defaults)) > 0 )
	{
		$rat1=($avarage_rating_per_comment == "0.5")?' checked="checked" ':'';
		$rat2=($avarage_rating_per_comment == "1.0")?' checked="checked" ':'';
		$rat3=($avarage_rating_per_comment == "1.5")?' checked="checked" ':'';
		$rat4=($avarage_rating_per_comment == "2.0")?' checked="checked" ':'';
		$rat5=($avarage_rating_per_comment == "2.5")?' checked="checked" ':'';
		$rat6=($avarage_rating_per_comment == "3.0")?' checked="checked" ':'';
		$rat7=($avarage_rating_per_comment == "3.5")?' checked="checked" ':'';
		$rat8=($avarage_rating_per_comment == "4.0")?' checked="checked" ':'';
		$rat9=($avarage_rating_per_comment == "4.5")?' checked="checked" ':'';
		$rat10=($avarage_rating_per_comment == "5.0")?' checked="checked" ':'';
		if(count(get_comments($defaults)) > 1)
		{
			$review_label = '<a id="reviews_show" href="#comments">'.__('reviews',RATING_DOMAIN).'</a>';
			$rating_text = '';
		}
		else
		{
			$review_label = '<a id="reviews_show" href="#comments">'.__('review',RATING_DOMAIN).'</a>';
			$rating_text = '';
		}
		
		$mouse_hover = 'onmouseover="return show_single_rating(\'show_rating\')" onmouseout="return hide_single_rating(\'show_rating\')"';
				
		echo  '<div class="average_rating_wrapper"><div class="average_rating clear_rating">
					<div id="rate" '.$mouse_hover.'>'.' '.$rating_text.' 
						'.get_single_average_rating_image($post->ID).' '.__('out of',RATING_DOMAIN).' '.sprintf('%s %s',count(get_comments($defaults)),$review_label).'</span>
					</div>
			  ';
		$values = array('rating_sum');
		$defaults = array(
		'post_id' => $post->ID,
		'status'=> 'approve'
		);
		if(count($comment_rating) > 1)
		{
		
			$hoverclass1 = "readtooltipitem";
		
		echo '<div style="display:none;" class="'.$hoverclass1.'" id="single_comment_rating_show_rating">';
		for($r=0;$r<count(get_comments($defaults));$r++)
		{
			$commnet_array = get_comments($defaults);
			$comment_rating = get_comment_meta( $commnet_array[$r]->comment_ID, 'comment_rating', true );
			
			$rating_per_comment = 0;
			$i = 1;
			if(!empty($comment_rating))
			{
				foreach($comment_rating as $key=>$val)
				{	
					$values['rating_sum'][$key] += $comment_rating[$key][0];
					$i++;
				}
			}
		}
		
		$rating_array = $values['rating_sum'];
		if(!empty($rating_array))
		{
			ksort($rating_array);//sort rating by key...
			$label_rating = 0;
			foreach ($rating_array as $key => $val) {
			
			$defaults = array(
			'post_id' => $post->ID,
			'status'=> 'approve'
			);
			
			$avarage_rating_per_comment = round(($val/count(get_comments($defaults))),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
			$output = '';
			$avg_rate  = $avarage_rating_per_comment;
			for($t=1;$t<=5;$t++)
			{
				if($avg_rate < 1 && $avg_rate >0 )
				{
					$output .= apply_filters('rating_star_rated_half','<span class="fa-stack"><i class="fa fa-star rating-off  fa-stack-1x"></i><i class="fa fa-star-half rating-half-on  fa-stack-1x"></i></span>');
					$avg_rate = 0;
				}
				elseif($t <= $avg_rate)
				{
					$rate = explode(".",$avg_rate);
					if($rate[0] == $t)
					{
						if($rate[1] > 0)
						{
							$avg_rate = "0".".".$rate[1];
						}
					}
					$output .= apply_filters('rating_star_rated','<span><i class="fa fa-star rating-on"></i></span>');
				}
				else
				{
					$output .= apply_filters('rating_star_normal','<span><i class="fa fa-star rating-off"></i></span>');
				}
			}
			
			$rating_title = get_option('rating_title');
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
			$terms = get_the_terms($post->ID,$taxonomies[0]);
			
			
				if($single_term_rating_label_detail[$label_rating] != '')
				{
					if(function_exists('icl_register_string')){
						icl_register_string(RATING_DOMAIN,$single_term_rating_label_detail[$label_rating],$single_term_rating_label_detail[$label_rating]);
						$single_term_rating_label_detail[$label_rating] = icl_t(RATING_DOMAIN,$single_term_rating_label_detail[$label_rating],$single_term_rating_label_detail[$label_rating]);
					}
					echo  '<div class="individual_average_rating"><div id="rate"><span class="rating_text">'.' '.$single_term_rating_label_detail[$label_rating].'</span>'.$output.'
												</div></div> ';
				}
				$label_rating++;
			}
		}
		echo '</div>';
	}
	echo '</div></div>';
	}
}
 /*
Name : show_comment_rating_single
Description : show rating on single page after page gets loaded.
*/
add_action('wp_footer','show_comment_rating_single',20);
function show_comment_rating_single()
{
	if(is_single())
	{?>
		<script>
		jQuery(window).load(function () {
			jQuery('.form_comment_rating_wrap').css('display','block');
		});
		</script>
	<?php
	}
}
/*action to fetch rating for comments wise*/
add_action('display_multi_rating','tmpl_rating_html');
function tmpl_rating_html($comment_id)
{
	?>
        <div class="event_rating">
          <div class="event_rating_row"><span class="single_rating"> <?php echo get_single_comment_rating_image($comment_id);?> </span></div>
        </div>
    <?php
}
/*action to fetch rating for listing page*/
add_action('show_multi_rating','tmpl_show_multi_rating');
function tmpl_show_multi_rating()
{
	global $post;
	?>
        <div class="listing_rating">
            <div class="directory_rating_row"><span class="single_rating"> <?php echo get_single_average_rating_image($post->ID);?> </span></div>
        </div>	
	<?php
}
/*action to fetch rating for detail page*/
add_action('show_single_multi_rating','tmpl_show_single_multi_rating');
function tmpl_show_single_multi_rating($post_id)
{
	global $post;
	?>
        <div class="listing_rating">
            <div class="directory_rating_row"><span class="single_rating"> <?php echo get_single_page_average_rating_image($post->ID);?> </span></div>
        </div>	
	<?php
}
/*fitler to fetch rating for map or ajax related page*/
add_filter('show_map_multi_rating','tmpl_show_map_multi_rating',10,4);
function tmpl_show_map_multi_rating($post_id,$plink,$comment_count,$review)
{
	$rating = get_single_average_rating($post_id);
	$result = '';
	$result = '<div class=map_rating>'.stripcslashes(str_replace('"','',$rating)).'<span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
	return $result;
}
/*change in structure of comment box*/
function display_muli_rating_star($comment)
{
		global $post;
		$html = '';
		if( true == get_comment_meta( $comment->comment_ID, 'comment_rating' ) ) 
		{
			
			$comment_rating = get_comment_meta( $comment->comment_ID, 'comment_rating', true );
			$rating_per_comment = 0;
			for($i=1;$i<=count($comment_rating);$i++)
			{
				$rating_per_comment += $comment_rating[$i][0];
			}
			$avarage_rating_per_comment = round(($rating_per_comment/count($comment_rating)),1);
			$average_rating = explode(".",$avarage_rating_per_comment);
			if($average_rating[1] >=5)
			{
				$avarage_rating_per_comment = $average_rating[0].".5";
			}
			else
			{
				$avarage_rating_per_comment = $average_rating[0];
			}
			
			$rat1=($avarage_rating_per_comment == "0.5")?' checked="checked" ':'';
			$rat2=($avarage_rating_per_comment == "1.0")?' checked="checked" ':'';
			$rat3=($avarage_rating_per_comment == "1.5")?' checked="checked" ':'';
			$rat4=($avarage_rating_per_comment == "2.0")?' checked="checked" ':'';
			$rat5=($avarage_rating_per_comment == "2.5")?' checked="checked" ':'';
			$rat6=($avarage_rating_per_comment == "3.0")?' checked="checked" ':'';
			$rat7=($avarage_rating_per_comment == "3.5")?' checked="checked" ':'';
			$rat8=($avarage_rating_per_comment == "4.0")?' checked="checked" ':'';
			$rat9=($avarage_rating_per_comment == "4.5")?' checked="checked" ':'';
			$rat10=($avarage_rating_per_comment == "5.0")?' checked="checked" ':'';
			
			if(get_option('show_average_rating') == 'button')
			{
				$mouse_hover = 'butoontooltipitem';
			}
			else
			{
				$mouse_hover = 'onmouseover="return show_rating('.$comment->comment_ID.')" onmouseout="return hide_rating('.$comment->comment_ID.')"';
			}
			if(count($comment_rating) > 1)
			{
				$rating_text = __('Average Rating',RATING_DOMAIN);
			}
			else
			{
				$rating_text = __('Rating',RATING_DOMAIN);
			}
			
			$avg_rate = $avarage_rating_per_comment;
			$output = '';
			for($i=1;$i<=5;$i++)
			{
				if($avg_rate < 1 && $avg_rate >0 )
				{
					$output .='<img src='. apply_filters('rating_star_rated_half',MULTIPLE_RATING_PLUGIN_URL."images/star_rated_half.png").' >';
					$avg_rate = 0;
				}
				elseif($i <= $avg_rate)
				{
					$rate = explode(".",$avg_rate);
					if($rate[0] == $i)
					{
						if($rate[1] > 0)
						{
							$avg_rate = "0".".".$rate[1];
						}
					}
					$output .='<img src="'. apply_filters('rating_star_rated',MULTIPLE_RATING_PLUGIN_URL.'images/star_rated.png').'">';
				}
				else
				{
					$output .='<img src="'. apply_filters('rating_star_normal',MULTIPLE_RATING_PLUGIN_URL.'images/star_normal.png').'">';
				}
			}
			$html .= '<div class="average_rating clear_rating"><div class="average_rating_title">'.$rating_text.'</div><div id="rate" '.$mouse_hover.' > '.$output.'
											</div>';
											if(count($comment_rating) > 1)
											{
											
											if(get_option('show_average_rating') == 'button')
											{
												$html .= '<div class="clear_rating button" onclick="return show_rating('.$comment->comment_ID.')">'.__('Show individual rating',RATING_DOMAIN).'</div>';
												$hoverclass = "butoontooltipitem";
											}
											else
											{
													$hoverclass = "readtooltipitem";
											}
												
											
											$html .= '<div class="'.$hoverclass.'" style="display:none;" id="comment_rating_'.$comment->comment_ID.'">';
										
											$post_type_rating = 0;
											$hide_maintaxonomy_rating = get_option('hide_maintaxonomy_rating');
											$rating_title = get_option('rating_title');
											$rating_product_title = get_option('rating_product_title');
											$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post->post_type,'public'   => true, '_builtin' => true ));	
											$terms = get_the_terms($post->ID,$taxonomies[0]);
											$comment_rating = get_comment_meta( $comment->comment_ID, 'comment_rating', true );
											
											$rating_html = '';
											$l = 1;
											$rating_title_post_type_array = array();
											$rating_title_post_type = get_option('rating_title'.$post->post_type);
											foreach($terms as $term)
											{
												foreach($rating_title_post_type[$term->term_id] as $key=>$value)
												{
													if($rating_title_post_type[$term->term_id][$key] != '' && !in_array($key,$rating_title_post_type_array))
													{
														
														if(function_exists('icl_register_string')){
															icl_register_string(RATING_DOMAIN,$value,$value);
															$value = icl_t(RATING_DOMAIN,$value,$value);
														}
														$avg_rate  = $comment_rating[$l][0];
														$output = '';
														for($i=1;$i<=5;$i++)
														{
															if($avg_rate < 1 && $avg_rate >0 )
															{
																$output .='<img src='. apply_filters('rating_star_rated_half',MULTIPLE_RATING_PLUGIN_URL."images/star_rated_half.png").' >';
																$avg_rate = 0;
															}
															elseif($i <= $avg_rate)
															{
																$rate = explode(".",$avg_rate);
																if($rate[0] == $i)
																{
																	if($rate[1] > 0)
																	{
																		$avg_rate = "0".".".$rate[1];
																	}
																}
																$output .='<img src="'. apply_filters('rating_star_rated',MULTIPLE_RATING_PLUGIN_URL.'images/star_rated.png').'">';
															}
															else
															{
																$output .='<img src="'. apply_filters('rating_star_normal',MULTIPLE_RATING_PLUGIN_URL.'images/star_normal.png').'">';
															}
														}
														$rating_html .= '<div id="rate"><span class="rating_text">'.$value.'</span> '.$output.'
																			</div>';
														array_push($rating_title_post_type_array,$key);
														$k++;
														$l++;
													}
												}
												//if($comment_rating[$l][0])
												{
													for($r=0;$r<count($rating_title[$term->term_id]);$r++)
													{
														
														if($rating_title[$term->term_id][$r] != '')
														{
															
															$output = '';
															
															$avg_rate  = $comment_rating[$l][0];
															for($i=1;$i<=5;$i++)
															{
																if($avg_rate < 1 && $avg_rate >0 )
																{
																	$output .='<img src='. apply_filters('rating_star_rated_half',MULTIPLE_RATING_PLUGIN_URL."images/star_rated_half.png").' >';
																	$avg_rate = 0;
																}
																elseif($i <= $avg_rate)
																{
																	$rate = explode(".",$avg_rate);
																	if($rate[0] == $i)
																	{
																		if($rate[1] > 0)
																		{
																			$avg_rate = "0".".".$rate[1];
																		}
																	}
																	$output .='<img src="'. apply_filters('rating_star_rated',MULTIPLE_RATING_PLUGIN_URL.'images/star_rated.png').'">';
																}
																else
																{
																	$output .='<img src="'. apply_filters('rating_star_normal',MULTIPLE_RATING_PLUGIN_URL.'images/star_normal.png').'">';
																}
															}
														if(function_exists('icl_register_string')){
															icl_register_string(RATING_DOMAIN,$rating_title[$term->term_id][$i],$rating_title[$term->term_id][$i]);
															$rating_title[$term->term_id][$i] = icl_t(RATING_DOMAIN,$rating_title[$term->term_id][$i],$rating_title[$term->term_id][$i]);
														}
														
														$rating_html .= '<div id="rate"><span class="rating_text">'.$rating_title[$term->term_id][$r].'</span>'.$output.'</div>';
															$k++;
															$l++;
														}
													}
												}
											}
										}
										$html .= $rating_html.'</div></div>';
		}
		echo $html;
	}
?>