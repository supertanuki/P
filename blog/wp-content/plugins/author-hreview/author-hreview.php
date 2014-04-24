<?php
	/*
	Plugin Name: Author hReview
    Plugin URI: http://authorhreview.com
    Description: Add support for hReview and AggregateRating based on schema.org.
    Version: 0.0.9.4
    Author: Hesham Zebida
    Author URI: http://www.famousbloggers.net
    Last Version update : 15 December 2012
    */
	
	// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}
	
    $thesisreviewspost_plugin_url = trailingslashit ( WP_PLUGIN_URL . '/' . dirname ( plugin_basename ( __FILE__ ) ) );
    $thesisreviewpost_widget_show = false;
	$pluginname = 'Author hReview';
	$plugin_version = '0.0.9.4';
	$ta_post_cpt = 'post';
	$shortname = "awesome";
	$rating = '';
	$ID = '';
	$ta_post_review_rating = '';
	$review_author = '';
	$ta_post_review_name = '';
	$wpar_chk_rating_after_post_display ='';
	$ta_mytheme_meta_box_post_nonce ='';
			
	// set up plugin actions
    //add_action( 'admin_init', 'ta_requires_wordpress_version' );			// check WP version 3.0+
	add_action( 'admin_init', 'ta_thesisreviews_admin_init' );				// to register admin styles and scripts
	add_action('wp_enqueue_scripts', 'arwp_stylesheet');					// load css
	
	//requires
	require_once ('include/review_meta_box.php');							// load meta box
	require_once ('include/admin/options.php');								// load admin options
	require_once ('include/review_template.php');							// load review template
	require_once ('include/review_widget.php');								// load widget functions
	require_once ('include/review_column_preview.php');						// load column preview
	require_once ('include/misc/news.php');									// load blog news
	
	// ------------------------------------------------------------------------
	// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
	// ------------------------------------------------------------------------
	function ta_requires_wordpress_version() {
		global $wp_version;
		$plugin = plugin_basename( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__, false );

		if ( version_compare($wp_version, "3.0", "<" ) ) {
			if( is_plugin_active($plugin) ) {
				deactivate_plugins( $plugin );
				wp_die( "'".$plugin_data['Name']."' requires WordPress 3.0 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
			}
		}
	}

	// add admin init
	function ta_thesisreviews_admin_init() {
		global $thesisreviewspost_plugin_url;
		$file_dir=get_bloginfo('template_directory');
		// *** add scripts here to admin page if required
		$file_dir=get_bloginfo('template_directory');
		//wp_enqueue_script("thesis_awesome_color_Script", $file_dir."/lib/scripts/jscolor/jscolor.js", false, "1.0");
		wp_enqueue_style ('arwp_review_style', $thesisreviewspost_plugin_url."/style/admin_style.css");
	}
	/**
	*load our css
	* to the head
	*/
	function arwp_stylesheet() {
        $myStyleUrl = plugins_url('style/style.css', __FILE__); // Respects SSL, Style.css is relative to the current file
        $myStyleFile = WP_PLUGIN_DIR . '/author-hreview/style/style.css';
        
		global $thesisreviews_widget_show;
		global $post;
		
		
		if ( file_exists($myStyleFile) ) {

            	wp_register_style('myStyleSheets', $myStyleUrl);
            	wp_enqueue_style( 'myStyleSheets');
        }
    }
?>