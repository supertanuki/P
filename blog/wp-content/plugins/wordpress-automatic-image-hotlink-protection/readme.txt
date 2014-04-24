=== Plugin Name ===
Contributors: christopherross
Plugin URI: http://thisismyurl.com/downloads/wordpress/plugins/hotlink-protection/
Donate link:  http://thisismyurl.com/downloads/wordpress/plugins/hotlink-protection/
Tags: image, hotlink, protection,images,admin,bandwidth
Requires at least: 2.0.0
Tested up to: 3.2
Stable tag: 2.0.0

The WordPress Automatic Image Hotlink Protection plugin is a single step script designed to stop others from stealing your images. 


== Description ==

The WordPress Automatic Image Hotlink Protection plugin is a single step script designed to stop others from stealing your images. Simply add an .htaccess file to your root folder thereby stopping external web servers from linking directly to your files.

The script automatically tests to to see if your web server is compatible with the script before adding the .htaccess file and setting the appropriate permissions. If deactivated, the script removes the code from your .htaccess file.

* Please Note : This plugin works only with the primary domain, and the www. addition not subdomains.
== Installation ==

To install the plugin, please upload the wordpress hotlink protection folder to your plugins folder and active the plugin. Once you've activated the plugin, visit the admin page at Settings > Hotlink Protection to activate the plugin.

== Updates ==
Updates to the plugin will be posted here, to [Christopher Ross](http://thisismyurl.com/)

== Donations ==
If you would like to donate to help support future development of this tool, please visit [Christopher Ross](http://thisismyurl.com/downloads/wordpress/plugins/hotlink-protection/)

== Frequently Asked Questions == 

= How do I remove this plugin? =

In most cases, the plugin should take care of itself when deactivating but if for any reason your server has problems with the hotlink removal, open your .htaccess file via an FTP client and remove the lines between # Hotlink Protection START # and # Hotlink Protection END # to remove the plugin manually.  

== Changelog ==

= 2.0.0 =

* Removed admin control panel
* Tested with WordPress 3.2
* Optimized code for WordPress 3.2
* Removed excess code

= 1.1.0 =

* Tested for WordPress 3.1
* Added new admin screens

= 1.0.0 =

* rewrote plugin for use with 3.x websites
* moved .htaccess file from uploads folder to root
* added file_exists checks
* tested for clean removal 

= 0.1.6 =

* removed update function

== Upgrade Notice ==

Empty

== Screenshots ==

Empty
