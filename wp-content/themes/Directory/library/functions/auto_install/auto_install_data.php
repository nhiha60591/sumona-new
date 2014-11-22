<?php 
global $upload_folder_path,$wpdb,$blog_id;
$a = get_option(supreme_prefix().'_theme_settings');
$b = array(
		'supreme_logo_url' 					=> get_template_directory_uri()."/images/logo.png",
		'supreme_site_description'			=> 1,
		'display_publish_date'				=> 1,
		'display_post_terms'				=> 1,
		'supreme_display_noimage'			=> 1,
		'supreme_archive_display_excerpt'	=> 1,
		'templatic_excerpt_length'			=> 27,
		'display_header_text'				=> 1,
		'supreme_show_breadcrumb'			=> 1,
		'tmpl_mobile_view'			=> 1,
		'enable_inquiry_form'				=> 1,
		'footer_insert' 					=> '<p class="copyright">&copy; '.date('Y').' <a href="'.home_url().'">'.get_option('blogname').'</a>.&nbsp;Designed by <a href="http://templatic.com" class="footer-logo"><img src="'.get_template_directory_uri().'/library/images/templatic-wordpress-themes.png" alt="WordPress Directory Theme" /></a></p>'
	);
update_option(supreme_prefix().'_theme_settings',$b);
update_option('posts_per_page',5);
update_option('show_on_front','page');
$args = array(
			'post_type' => 'page',
			'meta_key' => '_wp_page_template',
			'meta_value' => 'page-templates/front-page.php'
			);
$page_query = new WP_Query($args);
$front_page_id = $page_query->post->ID;
update_option('page_on_front',$front_page_id);


$dummy_image_path = get_template_directory_uri().'/images/dummy/';
$post_info = array();
$category_array = array('Blog','News','Facebook','Google','Mobile','Apple');
insert_taxonomy_category($category_array);
function insert_taxonomy_category($category_array){
	global $wpdb;
	for($i=0;$i<count($category_array);$i++)	{
		$parent_catid = 0;
		if(is_array($category_array[$i]))		{
			$cat_name_arr = $category_array[$i];
			for($j=0;$j<count($cat_name_arr);$j++)			{
				$catname = $cat_name_arr[$j];
				if($j>1){
					$catid = $wpdb->get_var("select term_id from $wpdb->terms where name=\"$catname\"");
					if(!$catid)					{
					$last_catid = wp_insert_term( $catname, 'category' );
					}					
				}else				{
					$catid = $wpdb->get_var("select term_id from $wpdb->terms where name=\"$catname\"");
					if(!$catid)
					{
						$last_catid = wp_insert_term( $catname, 'category');
					}
				}
			}
		}else		{
			$catname = $category_array[$i];
			$catid = $wpdb->get_var("select term_id from $wpdb->terms where name=\"$catname\"");
			if(!$catid)
			{
				wp_insert_term( $catname, 'category');
			}
		}
	}
	for($i=0;$i<count($category_array);$i++)	{
		$parent_catid = 0;
		if(is_array($category_array[$i]))		{
			$cat_name_arr = $category_array[$i];
			for($j=0;$j<count($cat_name_arr);$j++)			{
				$catname = $cat_name_arr[$j];
				if($j>0)				{
					$parentcatname = $cat_name_arr[0];
					$parent_catid = $wpdb->get_var("select term_id from $wpdb->terms where name=\"$parentcatname\"");
					$last_catid = $wpdb->get_var("select term_id from $wpdb->terms where name=\"$catname\"");
					wp_update_term( $last_catid, 'category', $args = array('parent'=>$parent_catid) );
				}
			}
			
		}
	}
}

////post end///
//====================================================================================//
////post start 19///
$image_array = array();
$post_meta = array();
$image_array[] = "http://templatic.net/images/Directory/img19.jpg" ;
$post_meta = array(
				   "templ_seo_page_title" =>'Transforming Paper and Plastic into a 3D Interactive Experience',
				   "templ_seo_page_kw" => '',
				   "tl_dummy_content"	=> '1',
				   "templ_seo_page_desc" => '',
				   "country_id" => 226,
				   "zones_id" => 3721,
				   "post_city_id"=>"1",
				);
$post_info[] = array(
					"post_title" =>	'Transforming Paper and Plastic into a 3D Interactive Experience',
					"post_content" =>	'The Google Earth API was used as a foundation for StrataLogica to make use of its sophisticated image rendering logic, satellite imagery and access to built-in tools and navigation controls. As an enterprise scale application, we faced some interesting challenges and gained many insights along the way that we d like to share.

Our first task was to prove we could wrap Nystrom s existing educationally focused maps and globes onto Google Earth while retaining the same high quality resolution delivered in their print products.

Achieving acceptable image resolution resulted in file sizes which were much too large. In addition, we needed to deliver an increased level of map content and granularity of images as the user zoomed into the earth. To address these two issues, we created a custom process that takes an Adobe Illustrator file and outputs Superoverlays in accordance with KML 2.1 standards. Using open source Python frameworks, we created a customized solution that outputs Superoverlays with various levels of content.

Our next challenge was to provide support for authoring and maintaining content, in the browser using the Google Earth plugin. All content is authored and maintained in a content management system CMS in much the same way as any dynamic website. One unique difference is that some of the content elements are geo-referenced coordinates that specify the location of content on earth. In the case of placemark balloons, the geo-referenced coordinates identify hotspots on the Nystrom maps which become clickable when the user turns on a setting. The placemark balloons provide supplementary audio, image, video and descriptive content such as the example shown above for the Appalachian Mountains. 

',
					"post_meta" =>	$post_meta,
					"post_image" =>	$image_array,
					"post_category" =>	array('Blog','Mobile'),
					"post_tags" =>	array('Tags','Sample Tags')

					);
////post end///
//====================================================================================//
////post start 20///
$image_array = array();
$post_meta = array();
$image_array[] = "http://templatic.net/images/Directory/img20.jpg" ;
$post_meta = array(
				   "templ_seo_page_title" =>'Go Mobile with Master Accuracy ',
				   "templ_seo_page_kw" => '',
				   "tl_dummy_content"	=> '1',
				   "templ_seo_page_desc" => '',
				   "country_id" => 226,
				   "zones_id" => 3721,
				   "post_city_id"=>"1"
				);
$post_info[] = array(
					"post_title" =>	'Go Mobile with Master Accuracy ',
					"post_content" =>	'Given the rise of Mobile Internet users in past few years  it has become quite important to have mobile or lite  version of your blog. Mobile users generally do not like to download relatively heavy blog that are made for normal internet connections and web browsers.

There are number of solutions for bloggers for both WordPress & Blogger platforms and I have personally tried a few of them, but I have not been too satisfied  Either they are too complex to setup or they are not exactly mobile-friendly.Here at trak.in, we had been using custom mobile blogger solution for past couple of months from Mobstac and were quite satisfied with what they had to offer. However, now Mobstac has gone ahead and launched a platform which allows bloggers to create mobile version of their blog. I got trak.in s mobile version up and running on the new platform in under 5 minutes.

',
					"post_meta" =>	$post_meta,
					"post_image" =>	$image_array,
					"post_category" =>	array('Blog','Mobile'),
					"post_tags" =>	array('Tags','Sample Tags')

					);
////post end///
//====================================================================================//
////post start 21///
$image_array = array();
$post_meta = array();
$image_array[] = "http://templatic.net/images/Directory/img21.jpg" ;
$post_meta = array(
				   "templ_seo_page_title" =>'Google Trends Plus SEO Drive Traffic to Your Blog ',
				   "templ_seo_page_kw" => '',
"tl_dummy_content"	=> '1',
				   "templ_seo_page_desc" => '',
				   "country_id" => 226,
				   "zones_id" => 3721,
				   "post_city_id"=>"1"
				);
$post_info[] = array(
					"post_title" =>	'Google Trends Plus SEO Drive Traffic to Your Blog ',
					"post_content" =>	'An organization has its own shuttle bus and provide free transportation for their employees is a very common service in China. Workers in schools, government agencies or private businesses all enjoy some sort of transportation tip free bus, taxi re reimbursement etc. on a monthly basis. However, it is quite rare here in North America. A recent article in New York Times exposed that Google is providing this service to the employees in the San Francisco region. I suppose it may be not as needed as in China since most people work in Google can afford cars but it is still a very thoughtful service due to high traffic in the cities. If you can hop onto a free ride and relax, surf on the Internet and get off right in front of your office, it s kinda nice isn t it Anyway, the article says, Google owns 32 buses, their routes cover over 10 cities and 6 counties around SF area. The buses provide free transportation to over 25% of the employees around 1200 people. They even have their own department of transportation I googled Google Bus, but only could find the Korean version of Google Bus, but I have to say this is a great idea to keep their employees and great advertisement

Beside free transportation, Google is doing the best to keep the employees: free cafeteria, free GYM, free rock climbing facilities, free swimming pool, free car wash, and free spa I need to forward this to my Microsoft friends

',
					"post_meta" =>	$post_meta,
					"post_image" =>	$image_array,
					"post_category" =>	array('Blog','Google'),
					"post_tags" =>	array('Tags','Sample Tags')

					);
////post end///
//====================================================================================//
////post start 22///
$image_array = array();
$post_meta = array();
$image_array[] = "http://templatic.net/images/Directory/img22.jpg" ;
$post_meta = array(
				   "templ_seo_page_title" =>'Best Laptop: Apple MacBook Pro, Popular Science Top 100 Innovations of 2009 ',
				   "templ_seo_page_kw" => '',
"tl_dummy_content"	=> '1',
				   "templ_seo_page_desc" => '',
				   "country_id" => 226,
				   "zones_id" => 3721,
				   "post_city_id"=>"1"
				);
$post_info[] = array(
					"post_title" =>	'Best Laptop: Apple MacBook Pro, Popular Science Top 100 Innovations of 2009 ',
					"post_content" =>	'Popular Science recently named an Apple computer as the best laptop of 2009 in the Best of What s New 2009: The Year s 100 Greatest Innovations  article. What makes Apple laptop the best in the field  Apple s unique, patented technology on battery life, included with every MacBook Pro.Due to Apple s design and technology flat lithium-polymer batteries  MacBook Pros carry more power than similarly sized Windows PC machines with the same processor.

The uniquely efficient FLAT lithium-polymer batteries allows much longer run times without increasing the size or weight of Apple s laptop.

Advanced chemistry and Adaptive Charging in Apple s MacBook Pro notebooks is said to allows the battery to maintain charging capabilities longer. The battery in the MacBook Pro is designed and built with additional Apple technology to last five years or 1000 recharges, multiple times typical PC battery life span

',
					"post_meta" =>	$post_meta,
					"post_image" =>	$image_array,
					"post_category" =>	array('Blog','Apple'),
					"post_tags" =>	array('Tags','Sample Tags')

					);
////post end///
//====================================================================================//
insert_posts($post_info);
function insert_posts($post_info)
{
	global $wpdb,$current_user;
	for($i=0;$i<count($post_info);$i++)
	{
		$post_title = $post_info[$i]['post_title'];
		$post_count = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts where post_title like \"$post_title\" and post_type='post' and post_status in ('publish','draft')");
		if(!$post_count)
		{
			$post_info_arr = array();
			$catids_arr = array();
			$my_post = array();
			$post_info_arr = $post_info[$i];
			if($post_info_arr['post_category'])
			{
				for($c=0;$c<count($post_info_arr['post_category']);$c++)
				{
					$catids_arr[] = get_cat_ID($post_info_arr['post_category'][$c]);
				}
			}else
			{
				$catids_arr[] = 1;
			}
			$my_post['post_title'] = $post_info_arr['post_title'];
			$my_post['post_content'] = $post_info_arr['post_content'];
			if($post_info_arr['post_author'])
			{
				$my_post['post_author'] = $post_info_arr['post_author'];
			}else
			{
				$my_post['post_author'] = 1;
			}
			$my_post['post_status'] = 'publish';
			$my_post['post_category'] = $catids_arr;
			$my_post['tags_input'] = $post_info_arr['post_tags'];
			$last_postid = wp_insert_post( $my_post );
			$post_meta = $post_info_arr['post_meta'];
			$data = array(
				'comment_post_ID' => $last_postid,
				'comment_author' => 'admin',
				'comment_author_email' => get_option('admin_email'),
				'comment_author_url' => 'http://',
				'comment_content' => $post_info_arr['post_title'].'its amazing.',
				'comment_type' => '',
				'comment_parent' => 0,
				'user_id' => $current_user->ID,
				'comment_author_IP' => '127.0.0.1',
				'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
				'comment_date' => $time,
				'comment_approved' => 1,
			);

			wp_insert_comment($data);
			if($post_meta)
			{
				foreach($post_meta as $mkey=>$mval)
				{
					update_post_meta($last_postid, $mkey, $mval);
				}
			}
			
			$post_image = $post_info_arr['post_image'];
			tmpl_directory_upload_image($last_postid,$post_image);
			
		}
	}
}
//=============================PAGES ENTRY START=======================================================//
$post_info = array();
$pages_array = array(array('Archives','Contact Us','Home'));
$page_info_arr = array();
$page_meta = array('_wp_page_template'=>'page-templates/archives.php', 'tl_dummy_content' => 1);
$page_info_arr[] = array('post_title'=>'Archives',
						'post_content'=>'This is Archives page template. Just select it from page templates section and you&rsquo;re good to go.',
						'post_meta'=>$page_meta);
$page_meta = array( 'tl_dummy_content' => 1,'_wp_page_template'=>'page-templates/contact-us.php');
$page_info_arr[] = array('post_title'=>'Contact Us',
						'post_content'=>'Please complete the form below and we will get back to you as soon as possible.',
						'post_meta'=>$page_meta);
$page_meta = array( 'tl_dummy_content' => 1);
$page_meta = array('_wp_page_template'=>'page-templates/front-page.php','Layout'=>'default'); 
$page_info_arr[] = array('post_title'=>'Home',
						'post_content'=>'',
						'comment_status'=>'closed',
						'post_meta'=> $page_meta);

$page_meta = array('_wp_page_template'=>'page-templates/full-page-map.php','Layout'=>'default'); 
$page_info_arr[] = array('post_title'=>'All In One Map',
						'post_content'=>"[tevolution_listings_map post_type='listing'   zoom_level='5'  latitude='40.46800769694572'  longitude='-101.42762075195316' clustering=1][/tevolution_listings_map]",
						'comment_status'=>'closed',
						'post_meta'=> $page_meta);

$page_meta = array('tl_dummy_content'=>'1','Layout'=>'default'); 
$page_info_arr[] = array('post_title'=>'People',
						'post_content'=>"[tevolution_author_list role='subscriber' users_per_page='8'][/tevolution_author_list]",
						'comment_status'=>'closed',
						'post_meta'=> $page_meta);

set_page_info_autorun($pages_array,$page_info_arr);
//Sidebar widget settings: start
$sidebars_widgets = get_option('sidebars_widgets');  //collect widget informations
$sidebars_widgets = array();
//==============================HEADER WIDGET AREA SETTINGS START=========================//
//Search widget settings start
$directory_search_location = array();
$directory_search_location[1] = array(
					"title"				=>	'',
					"post_type"			=>	array('listing'),
					"miles_search"		=>	0,
					"radius_measure"	=>	'kilometer',
					);
$directory_search_location['_multiwidget'] = '1';
update_option('widget_directory_search_location', $directory_search_location);
$directory_search_location = get_option('widget_directory_search_location');
krsort($directory_search_location);
foreach($directory_search_location as $key1=>$val1)
{
	$directory_search_location_key1 = $key1;
	if(is_int($directory_search_location_key1))
	{
		break;
	}
}
//Search widget settings end
$sidebars_widgets["header"] = array("directory_search_location-{$directory_search_location_key1}");
//==============================HEADER WIDGET AREA SETTINGS END=========================//
//==============================HOME PAGE BANNER WIDGET AREA SETTING START=========================//
//Home page Goole map
$supreme_banner_map = array();
$supreme_banner_map[1] = array(
					"hight"	=>	'500',
					);
$supreme_banner_map['_multiwidget'] = '1';
update_option('widget_googlemap_homepage',$supreme_banner_map);
$supreme_banner_map = get_option('widget_googlemap_homepage');
krsort($supreme_banner_map);
foreach($supreme_banner_map as $key=>$val1)
{
	$supreme_banner_map_key1 = $key;
	if(is_int($supreme_banner_map_key1)){
		break;
	}
}
//Home page banner slider settings end
$sidebars_widgets["home-page-banner"] = array("googlemap_homepage-{$supreme_banner_map_key1}");
//==============================HOME PAGE BANNER WIDGET AREA SETTING END=========================//
//==============================FOOTER WIDGET AREA SETTING START=========================//
//about theme widget settings start
$templatic_text = array();
$templatic_text[1] = array(
				"title"			=>	__("About Directory",THEME_DOMAIN),
				"text"		=>	'Directory is the most feature rich directory <a "title="WordPress Directory Theme" alt="WordPress Directory Theme" href="http://templatic.com">WordPress theme</a> available today. It provides all the tools necessary to run a modern directory website plus loads of  <a href="http://templatic.com/plugins/directory-add-ons/">add-ons</a> made for it. Built-in monetization, unlimited custom fields and categories, custom post types plus Google Maps integration are just some of the features available in this advanced directory theme.<br/><a href="http://templatic.com/forums/viewforum.php?f=119"><strong>Visit the support forum >></strong></a>',
				);
$templatic_text['_multiwidget'] = '1';
update_option('widget_templatic_text',$templatic_text);
$templatic_text = get_option('widget_templatic_text');
krsort($templatic_text);
foreach($templatic_text as $key=>$val)
{
	$templatic_text_key = $key;
	if(is_int($templatic_text_key)){
		break;
	}
}
//Navigation menu widget settings start
//Social Media widget settings start
$social_media = array();
$social_media[1] = array(
				"title"						=>	'Connect With Us',
				"social_description"		=>	'',
				"social_link"				=>	array('http://facebook.com/templatic','http://twitter.com/templatic','http://www.youtube.com/user/templatic','http://templatic.com/','http://templatic.com/','http://templatic.com/'),
				"social_icon"				=>	array('','','','','',''),
				"social_text"				=>	array('<i class="fa fa-facebook"></i>Find us on Facebook','<i class="fa fa-twitter"></i>Follow us on Twitter','<i class="fa fa-youtube"></i>Find us on Youtube','<i class="fa fa-linkedin"></i>Connect with us on LinkedIn','<i class="fa fa-google-plus"></i>Find us on Google+','<i class="fa fa-pinterest"></i>Find us on Pinterest')
				);
$social_media['_multiwidget'] = '1';
update_option('widget_social_media',$social_media);
$social_media = get_option('widget_social_media');
krsort($social_media);
foreach($social_media as $key=>$val)
{
	$social_media_key1 = $key;
	if(is_int($social_media_key1)){
		break;
	}
}
//Social Media widget settings start

//Newsletter subscribe widget settings start
$supreme_subscriber_widget = array();
$supreme_subscriber_widget[1] = array(
				"title"					=>	__('Get Latest Updates',THEME_DOMAIN),
				"text"					=>	__(' Subscribe to get our latest news',THEME_DOMAIN),
				"newsletter_provider"	=>	'feedburner',
				"feedburner_id"			=>	'',
				"mailchimp_api_key"		=>	'',
				"mailchimp_list_id"		=>	'',
				"feedblitz_list_id"		=>	'',
				"aweber_list_name"		=>	'',
				);						
$supreme_subscriber_widget['_multiwidget'] = '1';
update_option('widget_supreme_subscriber_widget',$supreme_subscriber_widget);
$supreme_subscriber_widget = get_option('widget_supreme_subscriber_widget');
krsort($supreme_subscriber_widget);
foreach($supreme_subscriber_widget as $key=>$val)
{
	$supreme_subscriber_widget_key = $key;
	if(is_int($supreme_subscriber_widget_key))
	{
		break;
	}
}
//Newsletter subscribe widget settings start

$sidebars_widgets["footer"] = array("templatic_text-{$templatic_text_key}","social_media-{$social_media_key1}","supreme_subscriber_widget-{$supreme_subscriber_widget_key}");
//==============================FOOTER WIDGET AREA SETTING END=========================//
//==============================HOME PAGE CONTENT WIDGET AREA SETTING START=========================//
//T → All Category List Home Page widget settings start
$widget_directory_featured_category_list = array();
$widget_directory_featured_category_list[2] = array(
				"title"					=>	__('Browse Listings By Categories',THEME_DOMAIN),
				"post_type"				=>	'listing',
				"category_level"		=>	2,
				"number_of_category"	=>	5,
				);						
$widget_directory_featured_category_list['_multiwidget'] = '1';
update_option('widget_directory_featured_category_list',$widget_directory_featured_category_list);
$widget_directory_featured_category_list = get_option('widget_directory_featured_category_list');
krsort($widget_directory_featured_category_list);
foreach($widget_directory_featured_category_list as $key=>$val)
{
	$widget_directory_featured_category_list_key = $key;
	if(is_int($widget_directory_featured_category_list_key))
	{
		break;
	}
}
//T → All Category List Home Page widget settings end

//Advertisement widget settings start
$supreme_advertisements = array();
$supreme_advertisements[1] = array(
				"title"	=>	'',
				"ads"	=>	'<a href="http://templatic.com"><img src="'.get_template_directory_uri().'/images/adv_728x90.jpg" style="padding-left:60px;"></a>',
				);						
$supreme_advertisements['_multiwidget'] = '1';
update_option('widget_supreme_advertisements',$supreme_advertisements);
$supreme_advertisements = get_option('widget_supreme_advertisements');
krsort($supreme_advertisements);
foreach($supreme_advertisements as $key=>$val)
{
	$supreme_advertisements_key = $key;
	if(is_int($supreme_advertisements_key))
	{
		break;
	}
}
//advertisement widget settings end

//T → Featured Listings For Home Page widget settings start
$directory_featured_homepage_listing = array();
$directory_featured_homepage_listing[1] = array(
				"title"					=>	__("Places Around You",THEME_DOMAIN),
				"text"					=>	__("View All",THEME_DOMAIN),
				"link"					=>	'#',
				"number"				=>	6,
				"view"					=>	'grid',
				"post_type"				=>	'listing',
				"category"				=>	'',
				);						
$directory_featured_homepage_listing['_multiwidget'] = '1';
update_option('widget_directory_featured_homepage_listing',$directory_featured_homepage_listing);
$directory_featured_homepage_listing = get_option('widget_directory_featured_homepage_listing');
krsort($directory_featured_homepage_listing);
foreach($directory_featured_homepage_listing as $key=>$val)
{
	$directory_featured_homepage_listing_key1 = $key;
	if(is_int($directory_featured_homepage_listing_key1))
	{
		break;
	}
}
//T → Featured Listings For Home Page widget settings end

//Advertisement widget settings start
$supreme_advertisements[2] = array(
				"title"	=>	'',
				"ads"	=>	'<a href="http://templatic.com"><img src="'.get_template_directory_uri().'/images/adv_728x90.jpg" style="padding-left:60px;"></a>',
				);						
$supreme_advertisements['_multiwidget'] = '1';
update_option('widget_supreme_advertisements',$supreme_advertisements);
$supreme_advertisements = get_option('widget_supreme_advertisements');
krsort($supreme_advertisements);
foreach($supreme_advertisements as $key=>$val)
{
	$supreme_advertisements_key2 = $key;
	if(is_int($supreme_advertisements_key2))
	{
		break;
	}
}
//advertisement widget settings end

//T → Featured Listings For Home Page widget settings start
$directory_featured_homepage_listing[2] = array(
				"title"					=>	__("Hotels Around You",THEME_DOMAIN),
				"text"					=>	__("View All",THEME_DOMAIN),
				"link"					=>	'#',
				"number"				=>	3,
				"view"					=>	'list',
				"post_type"				=>	'listing',
				"content_limit"			=>	150,
				"category"				=>	'',
				);						
$directory_featured_homepage_listing['_multiwidget'] = '1';
update_option('widget_directory_featured_homepage_listing',$directory_featured_homepage_listing);
$directory_featured_homepage_listing = get_option('widget_directory_featured_homepage_listing');
krsort($directory_featured_homepage_listing);
foreach($directory_featured_homepage_listing as $key=>$val)
{
	$directory_featured_homepage_listing_key2 = $key;
	if(is_int($directory_featured_homepage_listing_key2))
	{
		break;
	}
}
//T → Featured Listings For Home Page widget settings end

//T → Post Listing widget settings start
$supreme_recent_post = array();
$idObj = get_term_by('slug', 'blog', 'category'); 
$id = $idObj->term_id;
$supreme_recent_post[1] = array(
				"title"					=>	__("Latest News",THEME_DOMAIN),
				"post_type"				=>	'post',
				"post_type_taxonomy"	=>	$id,
				"post_number"			=>	3,
				"orderby"				=>	'date',
				"order"					=>	'DESC',
				"show_gravatar"			=>	'',
				"gravatar_size"			=>	'',
				"show_image"			=>	1,
				"image_size"			=>	'thumbnail',
				"show_title"			=>	1,
				"show_content"			=>	'content-limit',
				"content_limit"			=>	450,
				"more_text"				=>	__('[Read More...]',THEME_DOMAIN),
				);						
$supreme_recent_post['_multiwidget'] = '1';
update_option('widget_supreme_recent_post',$supreme_recent_post);
$supreme_recent_post = get_option('widget_supreme_recent_post');
krsort($supreme_recent_post);
foreach($supreme_recent_post as $key=>$val)
{
	$supreme_recent_post_key1 = $key;
	if(is_int($supreme_recent_post_key1))
	{
		break;
	}
}
//T → Post Listing widget settings end

$sidebars_widgets["home-page-content"] = array("directory_featured_category_list-{$widget_directory_featured_category_list_key}","supreme_advertisements-{$supreme_advertisements_key}","directory_featured_homepage_listing-{$directory_featured_homepage_listing_key1}","supreme_advertisements-{$supreme_advertisements_key2}","directory_featured_homepage_listing-{$directory_featured_homepage_listing_key2}","supreme_recent_post-{$supreme_recent_post_key1}");
//==============================HOME PAGE CONTENT WIDGET AREA SETTING END=========================//
//==============================FRONT PAGE SIDEBAR WIDGET AREA SETTING START=========================//
//Advertisement widget settings start
$supreme_advertisements[3] = array(
				"title"	=>	'',
				"ads"	=>	'<a href="http://templatic.com"><img align="middle" src="'.get_template_directory_uri().'/images/adv_300x250.jpg"></a>',
				);						
$supreme_advertisements['_multiwidget'] = '1';
update_option('widget_supreme_advertisements',$supreme_advertisements);
$supreme_advertisements = get_option('widget_supreme_advertisements');
krsort($supreme_advertisements);
foreach($supreme_advertisements as $key=>$val)
{
	$supreme_advertisements_key3 = $key;
	if(is_int($supreme_advertisements_key3))
	{
		break;
	}
}
//advertisement widget settings end

//Recent Review widget settings start
$widget_comment = array();
$widget_comment[1] = array(
				"title"		=>	'Recent Reviews',
				"post_type"	=>	'listing',
				"count"		=>	5,
				);						
$widget_comment['_multiwidget'] = '1';
update_option('widget_widget_comment',$widget_comment);
$widget_comment = get_option('widget_widget_comment');
krsort($widget_comment);
foreach($widget_comment as $key=>$val)
{
	$widget_comment_key = $key;
	if(is_int($widget_comment_key))
	{
		break;
	}
}
//Recent Review widget settings end
//about theme widget settings start
$templatic_text[2] = array(
				"title"		=>	__("Featured Video",THEME_DOMAIN),
				"text"		=>	'<iframe width="300" height="300" frameborder="0" allowfullscreen="" src="//www.youtube.com/embed/dYtARna2u0o"></iframe>',
				);						
$templatic_text['_multiwidget'] = '1';
update_option('widget_templatic_text',$templatic_text);
$templatic_text = get_option('widget_templatic_text');
krsort($templatic_text);
foreach($templatic_text as $key=>$val)
{
	$templatic_text_key2 = $key;
	if(is_int($templatic_text_key2))
	{
		break;
	}
}
//about theme widget settings end

//T → Popular Posts Widget settings start
$templatic_popular_post_technews = array();
$templatic_popular_post_technews[1] = array(
					"title"					=>	__('Popular Listings',THEME_DOMAIN),
					"post_type"				=>	'listing',
					"number"				=>	5,
					"slide"					=>	5,
					"popular_per"			=>	'comments',
					"pagination_position"	=>	0,
					);
$templatic_popular_post_technews['_multiwidget'] = '1';
update_option('widget_templatic_popular_post_technews',$templatic_popular_post_technews);
$templatic_popular_post_technews = get_option('widget_templatic_popular_post_technews');
krsort($templatic_popular_post_technews);
foreach($templatic_popular_post_technews as $key1=>$val1)
{
	$templatic_popular_post_technews_key1 = $key1;
	if(is_int($templatic_popular_post_technews_key1))
	{
		break;
	}
}
//T → Popular Posts Widget settings end

$sidebars_widgets["front-page-sidebar"] = array("supreme_advertisements-{$supreme_advertisements_key3}", "widget_comment-{$widget_comment_key}", "templatic_text-{$templatic_text_key2}", "templatic_popular_post_technews-{$templatic_popular_post_technews_key1}");
//==============================FRONT PAGE SIDEBAR WIDGET AREA SETTING END=========================//
//==============================LISTING SIDEBAR WIDGET AREA SETTING START=========================//

//Search widget settings start
$directory_search_location[2] = array(
					"title"				=>	__('Search Nearby Listings',THEME_DOMAIN),
					"post_type"			=>	array('listing'),
					"miles_search"		=>	0,
					"radius_measure"	=>	'miles',
					);						
$directory_search_location['_multiwidget'] = '1';
update_option('widget_directory_search_location', $directory_search_location);
$directory_search_location = get_option('widget_directory_search_location');
krsort($directory_search_location);
foreach($directory_search_location as $key1=>$val1)
{
	$directory_search_location_key2 = $key1;
	if(is_int($directory_search_location_key2))
	{
		break;
	}
}
//Search widget settings end

//T → Search Near By Miles Range widget settings start
$directory_mile_range_widget = array();
$directory_mile_range_widget[1] = array(
					"title"				=>	__("Filter Listings By Miles",THEME_DOMAIN),
					"max_range"			=>	500,
					"post_type"			=>	'listing',
					);						
$directory_mile_range_widget['_multiwidget'] = '1';
update_option('widget_directory_mile_range_widget',$directory_mile_range_widget);
$directory_mile_range_widget = get_option('widget_directory_mile_range_widget');
krsort($directory_mile_range_widget);
foreach($directory_mile_range_widget as $key1=>$val1)
{
	$directory_mile_range_widget_key = $key1;
	if(is_int($directory_mile_range_widget_key))
	{
		break;
	}
}
//T → Search Near By Miles Range widget settings end 

//Browse by category widget settings start
$templatic_browse_by_categories = array();
$templatic_browse_by_categories[1] = array(
					"title"				=>	__('Browse Listings By Category',THEME_DOMAIN),
					"post_type"			=>	'listing',
					"categories_count"	=>	1,
					);						
$templatic_browse_by_categories['_multiwidget'] = '1';
update_option('widget_templatic_browse_by_categories',$templatic_browse_by_categories);
$templatic_browse_by_categories = get_option('widget_templatic_browse_by_categories');
krsort($templatic_browse_by_categories);
foreach($templatic_browse_by_categories as $key1=>$val1)
{
	$templatic_browse_by_categories_key1 = $key1;
	if(is_int($templatic_browse_by_categories_key1))
	{
		break;
	}
}
//Browse by category widget settings end
$sidebars_widgets["listingcategory_listing_sidebar"] = array("directory_search_location-{$directory_search_location_key2}","directory_mile_range_widget-{$directory_mile_range_widget_key}", "templatic_browse_by_categories-{$templatic_browse_by_categories_key1}");
//==============================LISTING SIDEBAR WIDGET AREA SETTING END=========================//
//==============================LISTING DETAIL SIDEBAR WIDGET AREA SETTING END=========================//
//T → In the neighborhood widget settings start
$directory_neighborhood = array();
$directory_neighborhood[1] = array(
					"title"					=>	__('Nearest Listings',THEME_DOMAIN),
					"post_type"				=>	'listing',
					"post_number"			=>	4,
					"content_limit"			=>	34,
					"show_list"				=>	0,
					"closer_factor"			=>	0,
					"radius"				=>	5000,
					"radius_measure"		=>	'miles',
					);						
$directory_neighborhood['_multiwidget'] = '1';
update_option('widget_directory_neighborhood',$directory_neighborhood);
$directory_neighborhood = get_option('widget_directory_neighborhood');
krsort($directory_neighborhood);
foreach($directory_neighborhood as $key1=>$val1)
{
	$directory_neighborhood_key1 = $key1;
	if(is_int($directory_neighborhood_key1))
	{
		break;
	}
}
//T → In the neighborhood widget settings end

//Search widget settings start
$directory_search_location[3] = array(
					"title"				=>	__('Search Nearby Listings',THEME_DOMAIN),
					"post_type"			=>	array('listing'),
					"miles_search"		=>	0,
					"radius_measure"	=>	'miles',
					);						
$directory_search_location['_multiwidget'] = '1';
update_option('widget_directory_search_location', $directory_search_location);
$directory_search_location = get_option('widget_directory_search_location');
krsort($directory_search_location);
foreach($directory_search_location as $key1=>$val1)
{
	$directory_search_location_key3 = $key1;
	if(is_int($directory_search_location_key3))
	{
		break;
	}
}
//Search widget settings end

//Browse by category widget settings start
$templatic_browse_by_categories[2] = array(
					"title"				=>	__('Browse Listings By Categories',THEME_DOMAIN),
					"post_type"			=>	'listing',
					"categories_count"	=>	1,
					);						
$templatic_browse_by_categories['_multiwidget'] = '1';
update_option('widget_templatic_browse_by_categories',$templatic_browse_by_categories);
$templatic_browse_by_categories = get_option('widget_templatic_browse_by_categories');
krsort($templatic_browse_by_categories);
foreach($templatic_browse_by_categories as $key1=>$val1)
{
	$templatic_browse_by_categories_key2 = $key1;
	if(is_int($templatic_browse_by_categories_key2))
	{
		break;
	}
}
//Browse by category widget settings end

//Advertisement widget settings start
$supreme_advertisements[4] = array(
				"title"	=>	'',
				"ads"	=>	'<a href="http://templatic.com"><img align="middle" src="'.get_template_directory_uri().'/images/adv_300x250.jpg"></a>',
				);						
$supreme_advertisements['_multiwidget'] = '1';
update_option('widget_supreme_advertisements',$supreme_advertisements);
$supreme_advertisements = get_option('widget_supreme_advertisements');
krsort($supreme_advertisements);
foreach($supreme_advertisements as $key=>$val)
{
	$supreme_advertisements_key4 = $key;
	if(is_int($supreme_advertisements_key4))
	{
		break;
	}
}
//advertisement widget settings end

$sidebars_widgets["listing_detail_sidebar"] = array("directory_neighborhood-{$directory_neighborhood_key1}", "directory_search_location-{$directory_search_location_key3}", "templatic_browse_by_categories-{$templatic_browse_by_categories_key2}", "supreme_advertisements-{$supreme_advertisements_key4}");
//==============================LISTING DETAIL SIDEBAR WIDGET AREA SETTING END=========================//

//POST DETAIL PAGE SIDEBAR WIDGET START
//=============================================
//about theme widget settings start
$templatic_text[3] = array(
				"title"		=>	__("About the author",THEME_DOMAIN),
				"text"		=>	"<img src='http://templatic.com/demos/dirchild/video/wp-content/uploads/2013/09/20130903093522_profile7.png' height=90 width=90 style='float:left; margin:0 10px 10px 0'>
<h4><strong>Allen Rechard</strong></h4>
Use the 'Text' widget in the 'Post Detail Page Sidebar' to make any information you wish to display in this sidebar area.",
				);						
$templatic_text['_multiwidget'] = '1';
update_option('widget_templatic_text',$templatic_text);
$templatic_text = get_option('widget_templatic_text');
krsort($templatic_text);
foreach($templatic_text as $key=>$val)
{
	$templatic_text_key3 = $key;
	if(is_int($templatic_text_key3))
	{
		break;
	}
}
//about theme widget settings end

//Social Media widget settings start
$social_media[2] = array(
				"title"						=>	'Connect With Us',
				"social_description"		=>	'',
				"social_link"				=>	array('http://facebook.com/templatic','http://twitter.com/templatic','http://www.youtube.com/user/templatic','http://templatic.com/','http://templatic.com/','http://templatic.com/'),
				"social_icon"				=>	array('','','','','',''),
				"social_text"				=>	array('<i class="fa fa-facebook"></i>Find us on Facebook','<i class="fa fa-twitter"></i>Follow us on Twitter','<i class="fa fa-youtube"></i>Find us on Youtube','<i class="fa fa-linkedin"></i>Connect with us on LinkedIn','<i class="fa fa-google-plus"></i>Find us on Google+','<i class="fa fa-pinterest"></i>Find us on Pinterest')
				);
$social_media['_multiwidget'] = '1';
update_option('widget_social_media',$social_media);
$social_media = get_option('widget_social_media');
krsort($social_media);
foreach($social_media as $key=>$val)
{
	$social_media_key2 = $key;
	if(is_int($social_media_key2)){
		break;
	}
}
//Social Media widget settings start
//Newsletter subscribe widget settings start
$supreme_subscriber_widget[2] = array(
				"title"					=>	__('Subscribe To Newsletter',THEME_DOMAIN),
				"text"					=>	__('Subscribe to get latest news from site',THEME_DOMAIN),
				"newsletter_provider"	=>	'feedburner',
				"feedburner_id"			=>	'templatic',
				"mailchimp_api_key"		=>	'',
				"mailchimp_list_id"		=>	'',
				"feedblitz_list_id"		=>	'',
				"aweber_list_name"		=>	'',
				);						
$supreme_subscriber_widget['_multiwidget'] = '1';
update_option('widget_supreme_subscriber_widget',$supreme_subscriber_widget);
$supreme_subscriber_widget = get_option('widget_supreme_subscriber_widget');
krsort($supreme_subscriber_widget);
foreach($supreme_subscriber_widget as $key=>$val)
{
	$supreme_subscriber_widget_key = $key;
	if(is_int($supreme_subscriber_widget_key))
	{
		break;
	}
}
//Newsletter subscribe widget settings start
//Browse by category widget settings start
$templatic_browse_by_categories[3] = array(
					"title"				=>	__('Categories',THEME_DOMAIN),
					"post_type"			=>	'post',
					"categories_count"	=>	1,
					);						
$templatic_browse_by_categories['_multiwidget'] = '1';
update_option('widget_templatic_browse_by_categories',$templatic_browse_by_categories);
$templatic_browse_by_categories = get_option('widget_templatic_browse_by_categories');
krsort($templatic_browse_by_categories);
foreach($templatic_browse_by_categories as $key1=>$val1)
{
	$templatic_browse_by_categories_key3 = $key1;
	if(is_int($templatic_browse_by_categories_key3))
	{
		break;
	}
}
$sidebars_widgets["post-detail-sidebar"] = array("templatic_text-{$templatic_text_key3}","social_media-{$social_media_key2}","supreme_subscriber_widget-{$supreme_subscriber_widget_key}","templatic_browse_by_categories-{$templatic_browse_by_categories_key3}");
//POST DETAIL PAGE SIDEBAR WIDGET END
//=============================================
//POST LISTING PAGE SIDEBAR WIDGET START
//=============================================
//about theme widget settings start
$templatic_text[4] = array(
				"title"		=>	__("About the author",THEME_DOMAIN),
				"text"		=>	"<img src='http://templatic.com/demos/dirchild/video/wp-content/uploads/2013/09/20130903093522_profile7.png' height=90 width=90 style='float:left; margin:0 10px 10px 0'>
<h4><strong>Allen Rechard</strong></h4>
Use the 'Text' widget in the 'Post Category Page Sidebar' to make any information you wish to display in this sidebar area.",
				);						
$templatic_text['_multiwidget'] = '1';
update_option('widget_templatic_text',$templatic_text);
$templatic_text = get_option('widget_templatic_text');
krsort($templatic_text);
foreach($templatic_text as $key=>$val)
{
	$templatic_text_key4 = $key;
	if(is_int($templatic_text_key4))
	{
		break;
	}
}
//about theme widget settings end

//Social Media widget settings start
$social_media[3] = array(
				"title"						=>	'Connect With Us',
				"social_description"		=>	'',
				"social_link"				=>	array('http://facebook.com/templatic','http://twitter.com/templatic','http://www.youtube.com/user/templatic','http://templatic.com/','http://templatic.com/','http://templatic.com/'),
				"social_icon"				=>	array('','','','','',''),
				"social_text"				=>	array('<i class="fa fa-facebook"></i>Find us on Facebook','<i class="fa fa-twitter"></i>Follow us on Twitter','<i class="fa fa-youtube"></i>Find us on Youtube','<i class="fa fa-linkedin"></i>Connect with us on LinkedIn','<i class="fa fa-google-plus"></i>Find us on Google+','<i class="fa fa-pinterest"></i>Find us on Pinterest')
				);
$social_media['_multiwidget'] = '1';
update_option('widget_social_media',$social_media);
$social_media = get_option('widget_social_media');
krsort($social_media);
foreach($social_media as $key=>$val)
{
	$social_media_key3 = $key;
	if(is_int($social_media_key3)){
		break;
	}
}
//Social Media widget settings start
//Newsletter subscribe widget settings start
$supreme_subscriber_widget[3] = array(
				"title"					=>	__('Subscribe To Newsletter',THEME_DOMAIN),
				"text"					=>	__('Subscribe to get latest news from site',THEME_DOMAIN),
				"newsletter_provider"	=>	'feedburner',
				"feedburner_id"			=>	'templatic',
				"mailchimp_api_key"		=>	'',
				"mailchimp_list_id"		=>	'',
				"feedblitz_list_id"		=>	'',
				"aweber_list_name"		=>	'',
				);						
$supreme_subscriber_widget['_multiwidget'] = '1';
update_option('widget_supreme_subscriber_widget',$supreme_subscriber_widget);
$supreme_subscriber_widget = get_option('widget_supreme_subscriber_widget');
krsort($supreme_subscriber_widget);
foreach($supreme_subscriber_widget as $key=>$val)
{
	$supreme_subscriber_widget_key = $key;
	if(is_int($supreme_subscriber_widget_key))
	{
		break;
	}
}
//Newsletter subscribe widget settings start
//Browse by category widget settings start
$templatic_browse_by_categories[4] = array(
					"title"				=>	__('Categories',THEME_DOMAIN),
					"post_type"			=>	'post',
					"categories_count"	=>	1,
					);						
$templatic_browse_by_categories['_multiwidget'] = '1';
update_option('widget_templatic_browse_by_categories',$templatic_browse_by_categories);
$templatic_browse_by_categories = get_option('widget_templatic_browse_by_categories');
krsort($templatic_browse_by_categories);
foreach($templatic_browse_by_categories as $key1=>$val1)
{
	$templatic_browse_by_categories_key4 = $key1;
	if(is_int($templatic_browse_by_categories_key4))
	{
		break;
	}
}
//Browse by category widget settings end
$sidebars_widgets["post-listing-sidebar"] = array("templatic_text-{$templatic_text_key4}","social_media-{$social_media_key3}","supreme_subscriber_widget-{$supreme_subscriber_widget_key}","templatic_browse_by_categories-{$templatic_browse_by_categories_key4}");
//POST LISTING PAGE SIDEBAR WIDGET END
//=============================================
//PRIMARY SIDEBAR WIDGET START
//=============================================

//About Us widget settings start
$templatic_aboust_us[3] = array(
					"title"				=>	__('Become An Agent',THEME_DOMAIN),
					"about_us"			=>	__('You can become an agent by submitting a listing on our site. <a href="http://templatic.com/">List your business</a> on our site and get access to thousands of visitors we get everyday. You will be able to reach out to <strong>more people and that means more business</strong>.',THEME_DOMAIN),
					);						
$templatic_aboust_us['_multiwidget'] = '1';
update_option('widget_templatic_aboust_us',$templatic_aboust_us);
$templatic_aboust_us = get_option('widget_templatic_aboust_us');
krsort($templatic_aboust_us);
foreach($templatic_aboust_us as $key1=>$val1)
{
	$templatic_aboust_us_key3 = $key1;
	if(is_int($templatic_aboust_us_key3))
	{
		break;
	}
}
//About Us widget settings end

//Login widget settings start
$widget_login = array();
$widget_login[1] = array(
					"title"				=>	__('Dashboard',THEME_DOMAIN),
					"hierarchical"		=>	1,
					);						
$widget_login['_multiwidget'] = '1';
update_option('widget_widget_login',$widget_login);
$widget_login = get_option('widget_widget_login');
krsort($widget_login);
foreach($widget_login as $key1=>$val1)
{
	$widget_login_key1 = $key1;
	if(is_int($widget_login_key1))
	{
		break;
	}
}
//Login widget settings end

//Author widget settings start
$tevolution_author_listing = array();
$tevolution_author_listing[1] = array(
					"title"		=>	__('Top Authors',THEME_DOMAIN),
					"role"		=>	'subscriber',
					"no_user"	=>	5,
					);						
$tevolution_author_listing['_multiwidget'] = '1';
update_option('widget_tevolution_author_listing',$tevolution_author_listing);
$tevolution_author_listing = get_option('widget_tevolution_author_listing');
krsort($tevolution_author_listing);
foreach($tevolution_author_listing as $key1=>$val1)
{
	$tevolution_author_listing_key1 = $key1;
	if(is_int($tevolution_author_listing_key1))
	{
		break;
	}
}
//Login widget settings end
$sidebars_widgets["primary-sidebar"] = array("templatic_aboust_us-{$templatic_aboust_us_key3}", "widget_login-{$widget_login_key1}","tevolution_author_listing-{$tevolution_author_listing_key1}");

//PRIMARY SIDEBAR WIDGET END
//=============================================
//CONTACT PAGE WIDGET AREA START
//=========================================
//Google map widget settings start
$templatic_google_map = array();
$templatic_google_map[1] = array(
					"title"			=>	'Find us on map',
					"address"		=>	'230 Vine Street And locations throughout Old City, Philadelphia, PA 19106',
					"map_height"	=>	400,
					);						
$templatic_google_map['_multiwidget'] = '1';
update_option('widget_templatic_google_map',$templatic_google_map);
$templatic_google_map = get_option('widget_templatic_google_map');
krsort($templatic_google_map);
foreach($templatic_google_map as $key1=>$val1)
{
	$templatic_google_map_key = $key1;
	if(is_int($templatic_google_map_key))
	{
		break;
	}
}

$supreme_contact_widget = array();
$supreme_contact_widget[1] = array(
					"title"			=>	'Contact Us',
					"address"		=>	'230 Vine Street And locations throughout Old City, Philadelphia, PA 19106',
					"map_height"	=>	400,
					);						
$supreme_contact_widget['_multiwidget'] = '1';
update_option('widget_supreme_contact_widget',$supreme_contact_widget);
$supreme_contact_widget = get_option('widget_supreme_contact_widget');
krsort($supreme_contact_widget);
foreach($supreme_contact_widget as $key1=>$val1)
{
	$supreme_contact_widget_key = $key1;
	if(is_int($supreme_contact_widget_key))
	{
		break;
	}
}
//Google map widget settings end
$sidebars_widgets["contact_page_widget"] = array("templatic_google_map-{$templatic_google_map_key}","supreme_contact_widget-{$supreme_contact_widget_key}");
//Facebook fan widget settings start
$supreme_facebook = array();
$supreme_facebook[1] = array(
					"facebook_page_url"		=>	'https://www.facebook.com/templatic',
					"width"					=>	300,
					"show_faces"			=>	1,
					"show_stream"			=>	1,
					"show_header"			=>	1,
					);						
$supreme_facebook['_multiwidget'] = '1';
update_option('widget_supreme_facebook',$supreme_facebook);
$supreme_facebook = get_option('widget_supreme_facebook');
krsort($supreme_facebook);
foreach($supreme_facebook as $key1=>$val1)
{
	$supreme_facebook_key1 = $key1;
	if(is_int($supreme_facebook_key1))
	{
		break;
	}
}
//Facebook fan widget settings end
$sidebars_widgets["contact_page_sidebar"] = array("supreme_facebook-{$supreme_facebook_key1}");
//CONTACT PAGE WIDGET AREA END

/*BEING Below header Listing category widget*/
/*Catgeory map widget */
$category_map = array();
$category_map[1] = array("height"=>	'500');						
$category_map['_multiwidget'] = '1';
update_option('widget_category_googlemap',$category_map);
$category_map = get_option('widget_category_googlemap');
krsort($category_map);
foreach($category_map as $key1=>$val1)
{
	$category_map_key1 = $key1;
	if(is_int($category_map_key1)){
		break;
	}
}
$sidebars_widgets["after_directory_header"] = array("category_googlemap-{$category_map_key1}");
/*END Below header Listing category widget*/
//=========================================
update_option('sidebars_widgets',$sidebars_widgets);  //save widget informations 

/*
 * Function Name: Tmpl_builder_upload_image
 * upload property image from outside server
 */
function tmpl_directory_upload_image($post_id,$post_image){
	if($post_image)
	{
		for($m=0;$m<count($post_image);$m++){
			
	        $title = basename($post_image[$m]);
			
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	        // next, download the URL of the image
	        $upload = media_sideload_image($post_image[$m], $post_id, $title);
		}
	}

}

?>