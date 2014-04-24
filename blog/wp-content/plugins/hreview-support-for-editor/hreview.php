<?php 
/*
Plugin Name: hReview Support for Editor
Plugin URI: http://www.aes.id.au/?page_id=28
Description: Allows the right microformat content to be easily added for reviews.
Version: 0.9
Author: Andrew Scott
Author URI: http://www.aes.id.au/
*/ 

/*  Copyright 2007-2011  Andrew Scott

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Find the full URL to the plugin directory and store it
global $hreview_plugin_url;
$hreview_plugin_url = dirname(get_settings('siteurl') . '/wp-content/plugins/' . preg_replace('/^.*wp-content\/plugins\//', '', str_replace('\\', '/', __FILE__)));

// Set up defaults for options
add_option('hreview_ratingby_text', 'Rating by');
add_option('hreview_stars_text', 'stars');

// Set up hooks for the plugin
add_action('admin_footer', 'hreview_plugin_footer');
add_action('wp_head', 'hreview_plugin_head');
add_action('marker_css', 'hreview_plugin_css');
add_action('init', 'hreview_plugin_init');
add_action('admin_menu', 'hreview_plugin_menu');

function hreview_plugin_init() {
  global $wp_version;
  if (get_user_option('rich_editing') == 'true') {
    // Include hooks for TinyMCE plugin
    add_filter('mce_external_plugins', 'hreview_plugin_mce_external_plugins');
    add_filter('mce_buttons_3', 'hreview_plugin_mce_buttons');
    // Quicktags HTML editor hooks in WP 3.3+
    if (version_compare($wp_version, '3.3', '>=')) {
      add_action('admin_footer-post.php', 'hreview_plugin_quicktag_buttons');
      add_action('admin_footer-post-new.php', 'hreview_plugin_quicktag_buttons');
    }
  }
} // End hreview_plugin_init()

function hreview_plugin_menu() {
  if (function_exists('add_options_page')) {
    add_options_page('hReview Options', 'hReview', 8, __FILE__,
      'hreview_plugin_options_page');
  }
} // End hreview_plugin_menu()

function hreview_plugin_options_page() {
?><div class="wrap">
<h2>hReview Support for Editor</h2>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<table class="form-table">
<tr valign="top">
<th scope="row">Rating-by text</th>
<td><input type="text" name="hreview_ratingby_text" value="<?php echo get_option('hreview_ratingby_text'); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row">Stars text</th>
<td><input type="text" name="hreview_stars_text" value="<?php echo get_option('hreview_stars_text'); ?>" /></td>
</tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="hreview_ratingby_text,hreview_stars_text" />
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
</p></form></div>
<?php
} // End hreview_plugin_options_page()

function hreview_plugin_mce_external_plugins($plugins) {
  global $hreview_plugin_url;
  $plugins['hreview_plugin'] = $hreview_plugin_url . '/tinymceplugin/editor_plugin.js';
  return $plugins;
} // End hreview_plugin_mce_external_plugins()

function hreview_plugin_mce_buttons($buttons) {
  array_push($buttons, 'hreview_button');
  return $buttons;
} // End hreview_plugin_mce_buttons()

function hreview_plugin_quicktag_buttons() {
?>
<script type="text/javascript">//<![CDATA[
<?php
  echo "\tQTags.addButton('ed_hreview', 'hReview', edInsertHReviewCode, false, false, 'Add an hReview');\n";
?>
//]]></script>
<?php
} // End hreview_plugin_quicktag_buttons()

function hreview_plugin_footer() {
  global $hreview_plugin_url;
?>
<script type="text/javascript">//<![CDATA[
  var hreview_from_gui;
  function edInsertHReview() {
    tb_show("Add an hReview", "<?php echo $hreview_plugin_url; ?>/hreviewinput.php?TB_iframe=true");
    hreview_from_gui = true; /** Called from TinyMCE **/
  } // End edInsertHReview()

  function edInsertHReviewCode() {
    tb_show("Add an hReview", "<?php echo $hreview_plugin_url; ?>/hreviewinput.php?TB_iframe=true");
    hreview_from_gui = false; /** Called from Quicktags **/
  } // End edInsertHReview()

  if (hreview_qttoolbar = document.getElementById("ed_toolbar")){
    newbutton = document.createElement("input");
    newbutton.type = "button";
    newbutton.id = "ed_hreview";
    newbutton.className = "ed_button";
    newbutton.value = "hReview";
    newbutton.onclick = edInsertHReviewCode;
    hreview_qttoolbar.appendChild(newbutton);
  }

  function edInsertHReviewAbort() {
    tb_remove();
  } // End edInsertHReviewAbort()

  function edInsertHReviewStars(itemRating) {
    var markup = '';
    if ( itemRating ) {
      var i, stars, display_name, itemRatingValue = parseFloat(itemRating);
      display_name = '<?php {
        global $current_user;
	get_currentuserinfo();
          echo $current_user->display_name;
      } ?>';
      markup = '<p class="myrating"><?php echo get_option('hreview_ratingby_text');?>' +
        ' <span class="reviewer">' + display_name + '</span>: ' +
        '<span class="rating">' + itemRating + '</span> <?php echo get_option('hreview_stars_text');?><br />';
      stars = 0;
      for ( i = 1; i <= itemRatingValue; i++ ) {
        stars++;
        markup = markup + '<img class="hreview_image" width="20" height="20" src="<?php echo $hreview_plugin_url;
?>/starfull.gif" alt="*" />';
      } // End for
      i = parseInt(itemRatingValue);
      if ( itemRatingValue - i > 0.1 ) {
        stars++;
        markup = markup + '<img class="hreview_image" width="20" height="20" src="<?php echo $hreview_plugin_url;
?>/starhalf.gif" alt="1/2" />';
      } // End if
      for ( i = stars; i < 5; i++ ) {
        markup = markup + '<img class="hreview_image" width="20" height="20" src="<?php echo $hreview_plugin_url;
?>/starempty.gif" alt="" />';
      } // End for
      markup = markup + '</p>';
    } // End if
    return markup;
  } // End edInsertHReviewStars()

  function edInsertHReviewDone(itemName, itemURL, itemSummary, itemDescription, itemRating) {
    tb_remove();
    var HReviewOutput = '<div class="hreview"><h2 class="item"><span class="fn">' +
      ( itemURL ? '<a class="url" href="' + itemURL + '">' : '' ) +
      itemName +
      ( itemURL ? '</a>' : '') +
      '</span></h2>' +
      ( itemSummary ? '<span class="summary">' + itemSummary + 
        '</span>' : '' ) +
      ( itemDescription ? '<blockquote class="description">' +
        itemDescription + '</blockquote>' : '' ) +
      ( itemRating ? edInsertHReviewStars(itemRating) : '' ) +
      '</div>';
    if (hreview_from_gui)
    {
      tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, HReviewOutput);
      tinyMCE.execCommand('mceCleanup');
    } else
    {
      edInsertContent(edCanvas, HReviewOutput);
    }
  } // End edInsertHReviewDone()
//]]></script>
<?php
} // End hreview_plugin_footer()

function hreview_plugin_head() {
  global $hreview_plugin_url;
  echo '<link rel="stylesheet" type="text/css" media="screen" href="' .
    $hreview_plugin_url . '/hreview.css" />';
} // End hreview_plugin_head()

function hreview_plugin_css() {
  global $hreview_plugin_url;
  echo '@import url( ' . $hreview_plugin_url . '/hreview-editor.css );';
} // End hreview_plugin_css()

?>
