=== Drafts Dropdown ===
Contributors: alexkingorg, crowdfavorite, syberspace
Tags: draft, drafts, post, admin, access, shortcut
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: 3.0.0

Adds a Drafts tab to the admin bar so that you can quickly access your draft blog posts.

== Description ==

Wish you had quicker access to your draft posts and pages? Tired of having to click Edit / Drafts to get there? Problem solved - the Drafts Dropdown plugin gives you links to all of your drafts on *every* screen through a handy tab.

== Installation ==

1. Download the plugin archive and expand it (you've likely already done this).
2. Put the 'drafts-dropdown.php' file into your wp-content/plugins/ directory.
3. Go to the Plugins page in your WordPress Administration area and click 'Activate' for Drafts Dropdown.

== Frequently Asked Questions ==

= Does Drafts Dropdown require the use of the Admin Bar? =

Yes.

= Does this work on versions of WordPress prior to 3.8? =

Perhaps - however it has not been tested.

== Screenshots ==

1. The Drafts tab as added to the admin area.
2. Here are the draft posts - w00T!

== Changelog ==
= 3.0.0=
* Complete code overhaul
* AJAX overhaul ( now does only send metadata, not full html )
* Fixed slideDown followed by slideUp ( now only does slideDown )
* Restyled Columns ( number of columns can now be adjusted in css file )
* Moved script and style to seperate files ( allows for caching with plugins like W3 Total Cache)

= 2.0.2 =
* Enable drawer on post add/edit screen.

= 2.0.1 =
* Keep drawer from scrolling when open.

= 2.0 =
* Now attaches to the Admin Bar
* Available on front-end of site
* Drafts are loaded in via AJAX to improve performance
* Updated styling

= 1.0 =
* First public release.

== Developers ==

This plugin is now actively developed in GitHub. Fork it and contribute:

https://github.com/crowdfavorite/wp-drafts-dropdown
