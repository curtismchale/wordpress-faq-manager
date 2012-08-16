=== FAQ Manager ===
Contributors: norcross
Donate link: http://andrewnorcross.com/donate
Tags: frequently asked questions, FAQ, shortcodes, custom post types
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 1.22

Uses custom post types and taxonomies to manage an FAQ section for your site.

== Description ==
Uses custom post types and taxonomies to manage an FAQ section for your site. Includes a set of custom tags and taxonomies to organize, and shortcode for display options.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the 'wordpress-faq-manager' folder to the `/wp-content/plugins/` directory or install via the WP admin panel
1. Activate the plugin through the 'Plugins' menu in WordPress
1. That's it.

== Frequently Asked Questions ==

= What does this do? =

It uses the custom post type feature to create a dedicated FAQ section in your WordPress site, including categories and tags exclusive to them.

= How Do I Use It? =

Each FAQ acts like a "post". You can assign your own categories (called topics) or tags and organize as you see fit. You can also use shortcodes to place them on any page as follows:

* For the complete list: 
	place [faq] on a post / page
	
* For a single FAQ: 
	place [faq faq_id="ID"] on a post / page
	
* List all from a single FAQ topic category: 
	place [faq faq_topic="topic-slug"] on a post / page
	
* List all from a single FAQ tag: 
	place [faq faq_tag="tag-slug"] on a post / page
	
Please note that the shortcode can't handle a query of multiple categories in a single shortcode. However, you can stack them as such:
	...content....
	[faq faq_topic="topic-slug-one"]
	[faq faq_tag="tag-slug-two"]

The list will show 10 FAQs based on your sorting (if none has been done, it will be in date order). 
* To display only 5: 
	place [faq limit="5"] on a post / page

* To display ALL: 
	place [faq limit="-1"] on a post / page


== Screenshots ==

1. Screenshot of the "Add New FAQ" area

== Changelog ==

= 1.22 =
* added a user permissions filter `faq-cap` to all related admin menu pages. [See the Codex](http://codex.wordpress.org/Plugin_API/Filter_Reference/user_has_cap "See the Codex") on `user_has_cap` filter to adjust. 

= 1.21 =
* updated FAQ sort page to match user permissions of settings page

= 1.2 =
* MAJOR code cleanup
* converted code base to OOP
* serialized settings storage in DB
* consolidated widgets

= 1.14 =
* Added fallbacks if user doesn't save settings

= 1.13 =
* Removed version number for script and CSS enqueues for better cache setup
* New icon

= 1.12 =
* Code cleanup for 3.3

= 1.11 =
* Added FAQ slug as title anchor
* Optional H type selector (H1, H2) for better theme compatibility

= 1.1 =
* Included optional jQuery AJAX pagination (thanks to @JohnPBloch and @DanDenney for the help)

= 1.043 =
* Slight markup change (switching a span class to a div)
* Cleaned up function to include optional jQuery collapse

= 1.042 =
* Restored the single FAQ title on the "Random FAQ" widget

= 1.041 =
* bug fix where markup in post editor screen would break layout on admin panel.

= 1.04 =
* added 3 additional widgets
* code cleanup via suggestions by @Yoast
* Included wpautop function to display line breaks / lists / etc.

= 1.03 =
* included optional jQuery collapse / expand
* added second shortcode option [faqlist]
* added instructions page within FAQ submenu


= 1.02 =
* added option to control number of FAQs displayed via shortcode. See the How To section of the readme for more info

= 1.01 =
* Fixed path for CPT icon
* Updated user documentation

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.2 =
* Note: you MUST re-save your settings based on changed made.

= 1.01 =
* Fixed query for number of FAQs displayed

= 1.01 =
* No fundamental changes

== Potential Enhancements ==
* Got a bug? Something look off? Hit me up.


