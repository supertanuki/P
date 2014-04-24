<?php
/*
Plugin Name: Internals Links
Plugin URI: http://www.itnetwork.fr
Description: Internals Links dans TinyMCE.
Version: 1
Author: RH
*/

function internals_links($initArray){
   $initArray['external_link_list_url'] = get_option('siteurl') . '/wp-content/plugins/internals-links/link-list.php';
   return $initArray;
} 

add_filter('tiny_mce_before_init', 'internals_links');

?>