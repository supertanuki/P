=== hReview Support for Editor ===
Contributors: andrewescott
Tags: hreview, editor
Requires at least: 2.5
Tested up to: 3.3
Stable tag: trunk

This is a plugin to allow the easy entry of microformat content for reviews (i.e. the hReview microformat) in WordPress pages and posts.

== Description ==

This is a plugin to allow the easy entry of microformat content for reviews (i.e. the hReview microformat) in WordPress pages and posts. It adds a button to the editor that pops up a form with basic review fields, then inserts the contents of the fields back into the editor using markup compatible with hReview.

== Installation ==

1. Unzip the plugin into your wp-content/plugins/ directory. It should automatically create an hreview/ subdirectory.
2. In your WordPress admin page, go to the Plugins section and Activate the "hReview support for editor" plugin.
3. When you go to "Write Post" or "Write Page", click on the star logo to pop-up a simple form.
4. Fill in the fields of the form, particularly the name of the thing you are reviewing.
5. Click Insert and the contents of the fields will be inserted into the page or post you were composing, but marked up using the hReview microformat.

== Frequently Asked Questions ==

= What is a microformat? =

There are different microformats for different types of information. This plugin uses the hReview microformat for review type information. For more information, see [the main microformats site](http://microformats.org).

= Why would I want to use this? =

If your reviews are displayed on your blog using the hReview microformat, it makes it easy for search engines to find and do special things with them. For example, [Google Rich Snippets support the hReview microformat](http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=146645).

== Screenshots ==

1. ![Display of a review on the blog](http://www.aes.id.au/wp-content/uploads/2008/12/review-show.gif) This is an example of what a review looks like (with the Connections theme). 

2. ![Display of creating a review](http://www.aes.id.au/wp-content/uploads/2006/08/review-edit.gif) This is an example of writing a new review using the in-editor form.

== Changelog ==

= 0.9 =
* Fixed support for hReview button in HTML code editor, broken by WP v3.3

