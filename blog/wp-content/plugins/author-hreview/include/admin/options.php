<?php
/*
	Options page
*/
// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}
// ------------------------------------------------------------------------
// PLUGIN PREFIX:                                                          
// ------------------------------------------------------------------------
// A PREFIX IS USED TO AVOID CONFLICTS WITH EXISTING PLUGIN FUNCTION NAMES.
// WHEN CREATING A NEW PLUGIN, CHANGE THE PREFIX AND USE YOUR TEXT EDITORS 
// SEARCH/REPLACE FUNCTION TO RENAME THEM ALL QUICKLY.
// ------------------------------------------------------------------------

// 'wpar_' prefix is derived from [wp]wordpress [author]ptions [r]reviews

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
//register_activation_hook(__FILE__, 'wpar_add_defaults');
//register_uninstall_hook(__FILE__, 'wpar_delete_plugin_options');
add_action( 'admin_init', 'wpar_init' );
add_action( 'admin_menu', 'wpar_add_options_page' );
add_filter( 'plugin_action_links', 'wpar_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'wpar_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function wpar_delete_plugin_options() {
	delete_option('wpar_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'wpar_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function wpar_add_defaults() {
	$tmp = get_option('wpar_options');
    if(($tmp['wpar_chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('wpar_options'); // so we don't have to reset all the 'off' checkboxes too! ( I don't think this is needed but leave for now)
		$arr = array(	"wpar_chk_rating_home_display" => "1",
						"wpar_chk_rating_box_hide" => "",
						"wpar_chk_rating_after_post_display" => "1",
						"wpar_box_width" => "300",
						"wpar_drp_box_align" => "right",
						"wpar_drp_button_color" => "orange",
						"wpar_chk_default_options_db" => ""
		);
		update_option('wpar_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'wpar_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function wpar_init(){
	register_setting( 'wpar_plugin_options', 'wpar_options', 'wpar_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'wpar_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function wpar_add_options_page() {
	
	// add main menu	
	add_menu_page(
							__( 'Author hReview' ),
							__( 'Author hReview' ),
							'administrator',
							'authorhreview',
							'wpar_render_form',
							plugin_dir_url( 'author-hreview/images/stars_admin.png' , __FILE__ ).'stars_admin.png',
							49
						);
		
	// set second menu item title
	add_submenu_page('authorhreview','Settings','Settings','manage_options','authorhreview','wpar_render_form');
}

	// screen icon function, can be called by wpar_screen_icon();
	function wpar_screen_icon () {
		echo '<div class="icon32 wpar_icon32" id=""></div>';
	}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

	// Render the Plugin options form
	function wpar_render_form() {
		echo '<div class="wrap">';
		wpar_screen_icon();
		echo '<h2>Author hReview Settings</h2>';
		echo '<p>Get more control over Google SERP.</p>';
		
		echo '<div class="updated" style="overflow: hidden;"><p class="alignleft">Do you know that Author hReview plugin has a <b>Pro Version</b>? Enjoy high class support, more control over your reviews, half rating, templates, shortcodes, widgets, and more! <b>You save over 30%</b>. <a href="http://authorhreview.com/" target="_blank">Click here for more info</a>.</p> <p class="alignright"></p></div>';

		echo '<form method="post" action="options.php">';
		settings_fields('wpar_plugin_options');
		$options = get_option('wpar_options');
		echo '<div class="metabox-holder">';
		require_once ('settings_social.php');		// load scial settings
		require_once ('settings_general.php');		// load general settings
        echo '<div style="clear:both;"></div>';
		echo '</div>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '</form>';
	}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpar_validate_options($input) {
	 // strip html from textboxes
	$input['wpar_box_width'] =  wp_filter_nohtml_kses($input['wpar_box_width']); // Sanitize textarea input (strip html tags, and escape characters)
	//$input['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
	return $input;
}

// Display a Settings link on the main Plugins page
function wpar_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$posk_links = '<a href="'.get_admin_url().'options-general.php?page=author-hreview.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $posk_links );
	}

	return $links;
}

// Extra links for the plugin page
function wpar_admin_links() { ?>
	
        <div class="wpar_admin_link">
            <ul>
				<li><p>
                	<a href="http://wordpress.org/extend/plugins/author-hreview/" target="_blank"><p>Rate the plugin 5★ on WordPress.org</p></a></p>
				</li>
                <li class="wpar_blog"><p>
                	<a href="http://authorhreview.com/" title="Author hReview" target="_blank"><p>Blog about it and link to the plugin site</p></a></p>
				</li>
                <li class="wpar_paypal"><p>
                		<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=44ALNG28WPN76" title="Author hReview" target="_blank">
                    		Please, consider making a donation. Thanks!
						</a>
					</p>
				</li>
			</ul>              
 		</div>
<?php
}
?>