<?php
/*
Plugin Name: Hotlink Protection
Plugin URI: http://thisismyurl.com/downloads/wordpress/plugins/hotlink-protection/
Description: The WP Hotlink Protection plugin is a single step script designed to add an .htaccess file to your WordPress site thereby stopping external web servers from linking directly to your files.
Author: Christopher Ross
Version: 2.0.0
Author URI: http://thisismyurl.com
*/

/*
	/--------------------------------------------------------------------\
	|                                                                    |
	| License: GPL                                                       |
	|                                                                    |
	| Copyright (C) 2011, Christopher Ross			  	    			 |
	| http://thisismyurl.com                                   			 |
	| All rights reserved.                                               |
	|                                                                    |
	| This program is free software; you can redistribute it and/or      |
	| modify it under the terms of the GNU General Public License        |
	| as published by the Free Software Foundation; either version 2     |
	| of the License, or (at your option) any later version.             |
	|                                                                    |
	| This program is distributed in the hope that it will be useful,    |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
	| GNU General Public License for more details.                       |
	|                                                                    |
	| You should have received a copy of the GNU General Public License  |
	| along with this program; if not, write to the                      |
	| Free Software Foundation, Inc.                                     |
	| 51 Franklin Street, Fifth Floor                                    |
	| Boston, MA  02110-1301, USA                                        |   
	|                                                                    |
	\--------------------------------------------------------------------/
*/



// on activate
global $thisismyurl_hotlink_protection_file;
global $thisismyurl_hotlink_protection_file_hlp;


$url = strtolower(get_bloginfo('url'));
$url = str_replace('https://','',$url);
$url = str_replace('http://','',$url);
$url = str_replace('www.','',$url);
$thisismyurl_hotlink_protection_file_hlp = "

# Hotlink Protection START #

RewriteEngine on
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?".$url." [NC]
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?google.com [NC]
RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]

# Hotlink Protection END #

";

$thisismyurl_hotlink_protection_file = ABSPATH.'.htaccess';

// on activate
function timu_wpaihp_active() {
	global $thisismyurl_hotlink_protection_file;
	global $thisismyurl_hotlink_protection_file_hlp;
	
	if (file_exists($thisismyurl_hotlink_protection_file)) {
	  
		$fh = fopen($thisismyurl_hotlink_protection_file, 'r');
		$htaccess = fread($fh, filesize($thisismyurl_hotlink_protection_file));
		fclose($fh);
  	}
  
	$fh = fopen($thisismyurl_hotlink_protection_file, 'w') or die("can't open file");
	fwrite($fh, $htaccess.$thisismyurl_hotlink_protection_file_hlp);
	fclose($fh);
	
}
register_activation_hook( __FILE__, 'timu_wpaihp_active' );


// on deactivate
function timu_wpaihp_deactivate() {
	global $thisismyurl_hotlink_protection_file;
	global $thisismyurl_hotlink_protection_file_hlp;
	
	if (file_exists($thisismyurl_hotlink_protection_file)) {
		
		$fh = fopen($thisismyurl_hotlink_protection_file, 'r');
		$htaccess = fread($fh, filesize($thisismyurl_hotlink_protection_file));
		fclose($fh);

		$htaccess = str_replace($thisismyurl_hotlink_protection_file_hlp,"",$htaccess);

		$fh = fopen($thisismyurl_hotlink_protection_file, 'w') or die("can't open file");
		fwrite($fh, $htaccess);
		fclose($fh);

	}
}
register_deactivation_hook( __FILE__, 'timu_wpaihp_deactivate' );

?>