<?php
/** 
 * Clean up header output
 * @package Alien Ship
 * @since Alien Ship 0.3
 */

if ( ! function_exists( 'alienship_noindex' ) ):
function alienship_noindex() {
  if (get_option('blog_public') === '0') {
    echo '<meta name="robots" content="noindex,nofollow">', "\n";
  }
}
endif;

if ( ! function_exists( 'alienship_head_cleanup' ) ):
function alienship_head_cleanup() {
  remove_action('wp_head', 'feed_links', 2);
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'index_rel_link');
  remove_action('wp_head', 'parent_post_rel_link', 10, 0);
  remove_action('wp_head', 'start_post_rel_link', 10, 0);
  remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
  remove_action('wp_head', 'noindex', 1);
  add_action('wp_head', 'alienship_noindex');
}
add_action('init', 'alienship_head_cleanup');
endif;