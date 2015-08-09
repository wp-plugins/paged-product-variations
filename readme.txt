=== WooCommerce Paged Product Variations ===
Contributors: omac
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=B38QAQ2DENKEE&lc=US&item_name=Logan%20Graham&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: WooCommerce, product, variations
Requires at least: 4.0.0
Tested up to: 4.2.2
Stable tag: 1.0.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a project committed to making large storefronts using WooCommerce manageable by having the variations in the back-end paginated.

== Description ==

WooCommerce Paged Product Variations is a project committed to making large storefronts using WooCommerce manageable by having the variations in the back-end paginated.

The plugin is designed for the latest version of WooCommerce, however, has backwards-compatibility with < 2.3.x versions.

== Installation ==

1. Upload zip of the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. This is an example of what the plugin looks like in action. The default setting (which can be changed via a filter) is 10 variations per page.

== Frequently Asked Questions ==

= Can I change how many variations show up per page? =

You can adjust the variations per page by using the `paged_variations_posts_per_page` hook.

== Changelog ==

= 1.0.2 =
* Additional < 2.3.x tweaks
* Changed how page numbers are stored
* Added warning for edited data when switching pages
* Better setup WooCommerce defaults (tiptip, downloads, stock)

= 1.0.1 =
* Added Tooltips after pulling in new variations
* Fixed re-loading the variations after saving new attributes
* Fixed number of pages displaying incorrectly

= 1.0 =
* Initial Upload of the plugin.