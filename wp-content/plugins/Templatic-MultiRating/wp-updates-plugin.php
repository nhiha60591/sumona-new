<?php
if( !class_exists('WPMultipleRatingUpdates') ) {
    class WPMultipleRatingUpdates {
		
    	var $api_url;
    
    	var $plugin_path;
    	var $plugin_slug=TEMPLATIC_RATING_SLUG;
    	var $plugin_version=MULTIPLE_RATING_VERSION;
    	function __construct( $api_url, $plugin_path ) {
			$plugin_path = plugin_dir_path( __FILE__ );
			$plugin_file = $plugin_path ."multiple_rating.php";
			$plugin_data = get_plugin_data( $plugin_file, $markup = true, $translate = true );
			$plugin_version = $plugin_data['Version'];
    		$this->api_url = $api_url;
    		$this->plugin_path = $plugin_path;
    		if(strstr($plugin_path, '/')) list ($t1, $t2) = explode('/', $plugin_path); 
    		else $t2 = $plugin_path;    		
    	
    		add_filter( 'pre_set_site_transient_update_plugins', array(&$this, 'rating_check_for_update') );
    		add_filter( 'plugins_api', array(&$this, 'rating_plugin_api_call'), 10, 3 );
		
		if ( is_network_admin() || !is_multisite() ) {			
			add_action('after_plugin_row_'.TEMPLATIC_RATING_SLUG, array(&$this, 'rating_plugin_row') );	  
		}
    	}
	
	
	/*
	 * add action for set the auto update for tevolution plugin
	 * Function Name: tevolution_plugin_row
	 * Return : Display the plugin new version update message
	 */
	function rating_plugin_row()
	{		
		//check the remote version
		 global $plugin_response;	 
	      $remote_version=$plugin_response[TEMPLATIC_RATING_SLUG]['new_version'];
		 if (version_compare($this->plugin_version , $remote_version, '<'))
		 {	
			$new_version = version_compare($this->plugin_version , $remote_version, '<') ? __('There is a new version of Tevolution-rating plugin available ', DOMAIN)  : '';
			  
			  $ajax_url = esc_url( add_query_arg( array( 'slug' => 'Templatic-MultiRating', 'action' => 'templatic_multiRating' , '_ajax_nonce' => wp_create_nonce( 'MultiRating' ), 'TB_iframe' => true ,'width'=>500,'height'=>400), admin_url( 'admin-ajax.php' ) ) );
			  $file='Templatic-MultiRating/multiple_rating.php';
			  $download= wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_' . $file);
			
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">' . $new_version . '<a href="'.$ajax_url.'" class="thickbox" title="'.__('Templatic Multiple Rating Update',RATING_DOMAIN).'">'.__('update now',RATING_DOMAIN).'</a></div></td></tr>';
		
		 }
	}
    
    	function rating_check_for_update( $transient ) {
			
    			global $plugin_response,$wp_version;			
			
			
			if (empty($transient->checked)) return $transient;						
			$request_args = array(
				'slug' => $this->plugin_slug,
				'version' => $transient->checked[$this->plugin_slug]
				);
			$request_string = $this->rating_prepare_request( 'templatic_plugin_update', $request_args );			
			
			$raw_response = wp_remote_post( $this->api_url, $request_string );				
			$response = null;
			if( !is_wp_error($raw_response) && ($raw_response['response']['code'] == 200) )
				$response = json_decode($raw_response['body']);
		
			if( !empty($response) ) {// Feed the update data into WP updater
				$transient->response[$this->plugin_slug] = $response; 
				$plugin_response[$this->plugin_slug] = (array)$response; 
				update_option($this->plugin_slug.'_theme_version',$plugin_response);
			}						
			return $transient;
    	}
    
    	function rating_plugin_api_call( $def, $action, $args ) {
    		if( !isset($args->slug) || $args->slug != $this->plugin_slug ) return $def;
    		
    		$plugin_info = get_site_transient('update_plugins');
    		$request_args = array(
    		    'slug' => $this->plugin_slug,
    			'version' => (isset($plugin_info->checked)) ? $plugin_info->checked[$this->plugin_path] : 0 // Current version
    		);
    		
    		$request_string = $this->rating_prepare_request( $action, $request_args );
    		$raw_response = wp_remote_post( $this->api_url, $request_string );
    		if( is_wp_error($raw_response) ){
				$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.',RATING_DOMAIN).'</p> <p><a href="?" onclick="document.location.reload(); return false;">'.__('Try again',RATING_DOMAIN).'</a>', $raw_response->get_error_message());
				
    		} else {
    			$res = json_decode($raw_response['body']);
    			if ($res === false)
    				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred', RATING_DOMAIN), $raw_response['body']);
    		}    		
    		return $res;
    	}
    
    	function rating_prepare_request( $action, $args ) {
    		global $wp_version;
    		
    		return array(
    			'body' => array(
    				'action' => $action, 
    				'request' => serialize($args),
    				'api-key' => md5(home_url())
    			),
    			'user-agent' => 'WordPress/'. $wp_version .'; '. home_url()
    		);	
    	}
    
    
    }
}
?>