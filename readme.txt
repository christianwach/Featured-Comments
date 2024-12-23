=== Featured Comments ===
Contributors:      needle, mordauk, utkarsh
Donate link:       https://www.paypal.me/interactivist
Tags:              featured comments, feature comments
Requires PHP:      7.4
Requires at least: 4.9
Tested up to:      6.7
Stable tag:        2.0.2a
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Add a "featured" or "buried" CSS class and meta value to selected comments.

== Description ==

Lets the admin add "featured" or "buried" CSS class and meta value to selected comments. Handy to highlight comments that add value to your post.

Also includes a widget for showing recently featured comments.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload 'feature-comments' directory to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

All the options will be automatically added to the edit comments table, and single comment edit screen

== Screenshots ==

1. Comment Edit Table
2. Single Comment Edit
3. Class added to comment, as seen on the frontend (screenshot shows source viewed in Firebug)

== Changelog ==

= 2.0.1 =

* Improve string handling
* Move markup to template files

= 2.0.0 =

* Refactored plugin to conform with PHPCS
* Fixed class typo from "burry" to "bury"

= 1.2.6 =

* Fixed a bug that caused the Feature / Bury links to not work on pages where comments were loaded through ajax
* Updated add_comment_meta() to update_comment_meta() to ensure we are not adding duplicate values

= 1.2.5 =

* Fixed a minor security vulnerability that could allow someone to trick an admin into featuring or burying a comment

= 1.2.4 =

* Fixed a bug with the Featured Comments widget and not being able to properly set the number of comments to show

= 1.2.3 =

* Fixed a fatal error in the Featured Comments widget
* Fixed an undefined index error in the Featured Comments widget
* Fixed a missing comment ID parameter to the get_comment_author_link()

= 1.2.2 =

* Improved capability check when processing ajax requests

= 1.2.1 =

* Re-added Buried checkbox to the edit comment screen
* Added a file-modified-time version number to the JS to ensure file is not cached between updates
* Added a div.feature-burry-comments wrapper to the Feature | Bury links added to comments

= 1.2 =

* Development taken over by [Pippin Williamson](http://pippinsplugins.com)
* NOTE: no longer compatible with WordPress versions less than 3.5
* Replaced deprecated functions with up-to-date versions
* Added new Featured Comments widget
* Updated plugin class to a singleton

= 1.1.1 =
* Fixed bug, which showed feature/bury links to all users, instead of users with 'moderate_comments' capability.

= 1.1 =
* Major update
* Anyone with 'moderate_comments' capability is now able to feature/bury comments both from the frontend and backend
* Added support for featuring comments using ajax.
* The edit comments section now highlights featured comments, and reduces the opacity of buried comments.
* Fixed some E_NOTICE's

= 1.0.3 =
* Fixed a bug introduced in the last update

= 1.0.2 =
* Refactored source code

= 1.0.1 =
* Added missing screenshot files

= 1.0 =
* First version


== Upgrade Notice ==

= 1.2.1 =

* Re-added Buried checkbox to the edit comment screen
* Added a file-modified-time version number to the JS to ensure file is not cached between updates
* Added a div.feature-burry-comments wrapper to the Feature | Bury links added to comments

= 1.2 =

* Development taken over by [Pippin Williamson](http://pippinsplugins.com)
* NOTE: no longer compatible with WordPress versions less than 3.5
* Replaced deprecated functions with up-to-date versions
* Added new Featured Comments widget
* Updated plugin class to a singleton

= 1.1.1 =
* Fixed bug, which showed feature/bury links to all users, instead of users with 'moderate_comments' capability.

= 1.1 =
* Major update
* Anyone with 'moderate_comments' capability is now able to feature/bury comments both from the frontend and backend
* Added support for featuring comments using ajax.
* The edit comments section now highlights featured comments, and reduces the opacity of buried comments.
* Fixed some E_NOTICE's

= 1.0.3 =
* Fixed a bug introduced in the last update

= 1.0.2 =
* Refactored source code

= 1.0.1 =
* Added missing screenshot files

= 1.0 =
* First version
