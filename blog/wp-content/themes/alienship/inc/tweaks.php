<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Alien Ship
 * @since Alien Ship 0.1
 */


/*  @since Alien Ship 0.3
 * We don't need to self-close these tags in html5: <img>, <input> */
if ( ! function_exists( 'alienship_remove_self_closing_tags' ) ):
function alienship_remove_self_closing_tags($input) {
  return str_replace(' />', '>', $input);
}
add_filter('get_avatar', 'alienship_remove_self_closing_tags');
add_filter('comment_id_fields', 'alienship_remove_self_closing_tags');
add_filter('post_thumbnail_html', 'alienship_remove_self_closing_tags');
endif;


/* Pretty search URL. Changes /?s=foo to /search/foo. http://txfx.net/wordpress-plugins/nice-search/
 * @since Alien Ship 0.3 */
if ( ! function_exists( 'alienship_nice_search_redirect' ) ):
function alienship_nice_search_redirect() {
  if ( is_search() && get_option('permalink_structure') != '' && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) === false && strpos( $_SERVER['REQUEST_URI'], '/search/' ) === false ) {
    wp_redirect( home_url( '/search/' . str_replace( array( ' ', '%20' ),  array( '+', '+' ), get_query_var( 's' ) ) ) );
    exit();
  }
}
add_action( 'template_redirect', 'alienship_nice_search_redirect' );
endif;


if ( ! function_exists( 'alienship_search_query' ) ):
function alienship_search_query($escaped = true) {
  $query = apply_filters('alienship_search_query', get_query_var('s'));
  if ($escaped) {
      $query = esc_attr($query);
  }
  return urldecode($query);
}
add_filter('get_search_query', 'alienship_search_query');
endif;



/* 
 * Custom image, link, and title on login/register page
 * ----------------------------------------------------
 * @since Alien Ship .50
 */

/* Custom permalink for login page image */
if ( ! function_exists( 'alienship_custom_login_image_link' ) ) {
  function alienship_custom_login_image_link() {
    return get_site_url();
  }
  add_filter('login_headerurl', 'alienship_custom_login_image_link');
}

/* Custom title for login page image */
if ( ! function_exists( 'alienship_custom_login_image_title' ) ) {
  function alienship_custom_login_image_title() {
    return esc_attr(get_bloginfo('name'));
  }
  add_filter('login_headertitle', 'alienship_custom_login_image_title');
}

/* Custom login logo for wp-admin screen */
if ( of_get_option( 'alienship_custom_login_image' ) ) {
  if ( ! function_exists( 'alienship_custom_login_image' ) ) {
    function alienship_custom_login_image() {
      echo '<style type="text/css">
      .login h1 a { background-image:url( "'.of_get_option( 'alienship_custom_login_image_file' ).'" ) !important; }
      </style>';
    }
  }
add_action('login_head', 'alienship_custom_login_image');
}


/* Option to disable admin bar display on front end for all users.
 * @since Alien Ship .50 
 */
if ( of_get_option( 'alienship_disable_admin_bar' ) ) {
  add_filter('show_admin_bar', '__return_false');
}


/* Remove WordPress logo and dropdown menu from admin bar
 * @since Alien Ship .50
 */
if ( ! function_exists( 'alienship_remove_admin_bar_logo') ) {
  function alienship_remove_admin_bar_logo() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
  }
}
add_action('wp_before_admin_bar_render', 'alienship_remove_admin_bar_logo', 0);


/* Change default length of excerpt 
function alienship_custom_excerpt_length( $length ) {
        return 20;
}
add_filter( 'excerpt_length', 'alienship_custom_excerpt_length', 999 );

*/

/**
 * Adds custom classes to the array of body classes.
 *
 * @since Alien Ship 0.1
 */
/* function alienship_body_classes( $classes ) {
	// If this isn't a post or a page we'll add the convenient .indexed class
	if ( ! is_singular() ) {
		$classes[] = 'indexed';
	}
	// Adds a class of group-blog to blogs with more than 1 published author
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
add_filter( 'body_class', 'alienship_body_classes' );
*/



/**
 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages
 *
 * @since Alien Ship 0.1
 */
/* function alienship_enhanced_image_navigation( $url, $id ) {
	if ( ! is_attachment() && ! wp_attachment_is_image( $id ) )
		return $url;

	$image = get_post( $id );
	if ( ! empty( $image->post_parent ) && $image->post_parent != $id )
		$url .= '#main';

	return $url;
}
add_filter( 'attachment_link', 'alienship_enhanced_image_navigation', 10, 2 ); */