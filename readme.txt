=== FAQ Manager ===
Contributors: norcross
Donate link: http://andrewnorcross.com/donate
Tags: frequently asked questions, FAQ, shortcodes, custom post types
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.331
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Uses custom post types and taxonomies to manage an FAQ section for your site.

== Description ==
Uses custom post types and taxonomies to manage an FAQ section for your site. Includes a set of custom taxonomies to organize, and shortcode options for different display configurations. [See the FAQ section](http://wordpress.org/extend/plugins/wordpress-faq-manager/faq "See the FAQ section")  for complete setup options.

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
	place `[faq]` on a post / page

* For a single FAQ:
	place `[faq faq_id="ID"]` on a post / page

* List all from a single FAQ topic category:
	place `[faq faq_topic="topic-slug"]` on a post / page

* List all from a single FAQ tag:
	place `[faq faq_tag="tag-slug"]` on a post / page

Please note that the shortcode can't handle a query of multiple categories in a single shortcode. However, you can stack them as such:
	...content....
	`[faq faq_topic="topic-slug-one"]`
	`[faq faq_tag="tag-slug-two"]`

The list will show 10 FAQs based on your sorting (if none has been done, it will be in date order).
* To display only 5:
	place `[faq limit="5"]` on a post / page

* To display ALL:
	place `[faq limit="-1"]` on a post / page

* For a list with a title and link to full FAQ:
	place `[faqlist]` on a post / page

* For a list with a group of titles that link to complete content later in page:
	place `[faqcombo]` on a post / page

* For a list of taxonomies (topics or tags) with a link to their respective archive page:
	place `[faqtaxlist type="topics"]` or `[faqtaxlist type="tags"]` on a post / page

* For a list of taxonomies (topics or tags) with their description:
	place `[faqtaxlist type="topics (or tags)" desc="true"]` on a post / page

== Screenshots ==

1. The "Add New FAQ" area
2. Example of collapsed FAQs
3. Example of expanded FAQs

== Changelog ==

= 1.331 =
* added German language support. Props @PowieT

= 1.330 =
* added French language support. Props @straw94

= 1.329 =
* replaced custom function with native admin columns for FAQ taxonomies
* removed 'answers' from FAQ table due to translation issues
* tweaked CSS to include FAQ icon in all related areas.

= 1.328 =
* small bugfix on markup for shortcode combo

= 1.327 =
* added optional 'back to top' link for combo FAQ list

= 1.326 =
* added option to redirect all FAQ content to a single FAQ page

= 1.325 =
* added option to disable content_filter on output (added on 1.324)

= 1.324 =
* applying filters to content output for shortcodes, etc
* beginning internationalization support
* moved widgets into a separate file for organization

= 1.323 =
* minor bugfix for conflicts with certain commerical forms plugins

= 1.322 =
* fixed RSS inclusion bug

= 1.321 =
* fixed IE9 expand / collapse bug
* added version number to CSS and JS files

= 1.32 =
* added optional inclusion of permalink below expanded entries

= 1.31 =
* added ability to change single FAQ slugs

= 1.30 =
* added taxonomy list shortcode
* revamped settings and instructions page
* CSS cleanup

= 1.29 =
* modified expand / collapse to close all other FAQs when one is opened
* added expand / collapse speed option

= 1.283 =
* added standard post classes for taxonomy archives

= 1.282 =
* added standard post classes for themes with narrow CSS

= 1.281 =
* bugfix on plugin page menu links

= 1.28 =
* added optional jQuery smooth scrolling effect for FAQ Combo shortcode
* added links to settings and instructions page on plugin table

= 1.27 =
* fixed bug in Random FAQ widget and and added variable for 'see more' text and number. Props @jupiterwise

= 1.26 =
* added `faqcombo` shortcode to allow for a list of FAQ titles that inner-link to the content on the page

= 1.25 =
* added CSS for printing to auto-expand FAQs (does not affect screen)
* fixed jQuery expand bug that was causing FAQs to be collapsed at all times.

= 1.24 =
* removed the 'public' option (it was misleading)
* added SEO options (noindex, nofollow, and noarchive)

= 1.23 =
* bugfix with URL source of files (for reals this time)
* Search widget to search just FAQs

= 1.22 =
* bugfix with URL source of files
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

= 1.3 =
* All the markup changed from using underscores to dashes, i.e. `<div class="faq_list">` to `<div class="faq-list">`. If you have any custom CSS, you will need to update it.

= 1.2 =
* Note: you MUST re-save your settings based on changed made.

= 1.01 =
* Fixed query for number of FAQs displayed

= 1.01 =
* No fundamental changes

== Potential Enhancements ==
* Got a bug? Something look off? Hit me up.


