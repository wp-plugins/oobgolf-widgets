=== oobgolf Widgets ===
Contributors: tlaqua
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4630379
Tags: widget,golf,oobgolf
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 1.0.7

oobgolf Widgets is a plugin that allows you to display various bits of information from your oobgolf.com profile on your blog.

== Description ==
oobgolf Widgets is a plugin that allows you to display various bits of information from your oobgolf.com profile on your blog.

**Current Widgets**

* oobgolf Rounds: Displays recent rounds on your blog
* oobgolf Development: Displays your development chart on your blog

== Installation ==
1. Download the installation zip file and unzip leaving the directory structure in tact.
1. Upload the newly unzipped 'oobgolf-widgets' folder to the `/wp-content/plugins/` folder
1. Activate the oobgolf Widgets plugin from the 'Plugins' menu.
1. Add the widget(s) to your sidebar from the 'Widgets' design page.
1. Enter your oobgolf username and password in the widget control box and customize appearance to fit your theme

**Folder Permissions**

1. Ensure both the Cache and tmp folders are writable after uploading the files

**Requirements**

1. PHP GD Library
2. openssl support (for api requests)
1. allow_url_fopen must be set to On in your PHP configuration (usually php.ini)

== Frequently Asked Questions ==

= Where do I request new oobgolf widgets? =

Just post a comment to (http://timlaqua.com/wordpress-plugins/wordpress-plugin-oobgolf-widgets/).

I will try to add suggested widgets in upcoming releases.

== Screenshots ==
1. oobgolf Rounds Widget
2. oobgolf Rounds Widget Scorecard detail
3. oobgolf Development Widget

== Change Log ==

* 1.0.7 Initial new score posting to Twitter (crude atm), added caching to oobgolf Rounds widget
* 1.0.6 Documentation Update
* 1.0.5 Added Legend Location option to dev chart
* 1.0.4 Moved user/pass options to admin page, added rounds to show option, added some minimal error handling
* 1.0.3 Fixed old option propagation
* 1.0.2 Commented out option clear on deactivation
* 1.0.1 Fixed ImageMap purge logic
* 1.0.0 First Release