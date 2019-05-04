=== Most Popular Jetpack ===
Contributors: jennettefulda
Donate link: https://www.makeworthymedia.com/plugins/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: jetpack,popular,traffic,stats
Requires at least: 5.0
Tested up to: 5.0
Stable tag: 1.0

Displays the most popular posts as tracked by the Jetpack plugin.

== Description ==

Use the [popular-jetpack] shortcode to display your site's most popular posts as tracked by the Jetpack plugin. You will need to install the Jetpack plugin, connect it to your WordPress.com account, and give it time to track pageviews before the shortcode will display any results. 

Stats are tracked and stored by WordPress.com, which prevents your database from becoming bloated and reduces the strain on your server. Results are stored as a transient in the database with a one-day expiration time.

#### Shortcode Attributes
You can use the following attributes to control what data is displayed:

* **Timespan:** [popular-jetpack timespan='all'] How far back in time to draw results from. Accepted values are 'all', 'year', 'month', 'week', 'day'. Default setting is 'all.' 
* **Limit:** [popular-jetpack limit='10'] The number of posts to display. Default setting is 10. 
* **Post Types:** [popular-jetpack posttypes='post,other'] The type of posts to display, separated by commas. Default setting displays only posts.
* **Cache time in seconds:** [popular-jetpack 'cache' => 86400] The number of seconds to cache the results for. Please do not cache for less than 180 seconds per WordPress.com guidelines. Defaults to one day.
* **Force SSL:** [popular-jetpack ssl='0'] Forces all links to use https:// links instead of http:// This can be helpful if you switched your site to SSL after you started tracking stats. Otherwise, WordPress.com will still include the old http:// links in your data set. Defaults to 0.
* **Exclude Posts:** [popular-jetpack exclude='1,10,5'] The IDs of any posts you wish to exclude from the results, separated by commas. Defaults to nothing.

== Installation ==

1. Upload the folder 'mwm-popular-jetpack' to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it! You can start using the shortcode.

== Frequently Asked Questions ==

= What shortcode do I use to display the results? =

Use the [popular-jetpack] shortcode to display your results. See further instructions in the Description section.

= How do I enable Jetpack to track stats on my site? =

First, you need to download and activate [the Jetpack plugin] (https://wordpress.org/plugins/jetpack/) from the WordPress repository. Then you need to connect it to a WordPress.com account. The plugin should prompt you on how to do that. Once that's done, go to Jetpack -> Settings and click on the "Traffic" tab. Scroll down until you see a button that says "Activate Site Stats" and click it. You'll need to give the plugin a few hours before it will start tracking data.

= How does the plugin access and store the data? =

The plugin uses the WordPress.com API to access stats using https://stats.wordpress.com/csv.php The data is then stored as a transient in your database with a default expiration date of one day.

== Changelog ==

= 1.0 =
* Original version of the plugin.