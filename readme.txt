=== Viper's Video Quicktags Migrator ===
Contributors: Viper007Bond
Requires at least: 2.9
Tested up to: 4.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A replacement for the dead Viper's Video Quicktags plugin using native WordPress embed functionality to take over handling the video embedding.

== Description ==

*This plugin is written by the same author as Viper's Video Quicktags. You only need this plugin if you were a previous user of it.*

Viper's Video Quicktags was a popular but no longer maintained plugin that I wrote that made it easy to embed videos into your WordPress site. Since the plugin was originally written over 10 years ago, WordPress has added [native embed support](http://codex.wordpress.org/Embeds) which allows you to easily embed videos out of the box. Its functionality is far superior to that of the plugin and most people have switched to using it.

Unfortunately many people have been left with old posts that use the plugin's shortcodes and manually migrating over to the new embed method would be a time consuming and tedious task.

Instead this plugin will take over parsing those shortcodes, making use of the native WordPress functionality instead.

Note: This plugin does not add any buttons to the editor like VVQ did as you no longer should need them. Just paste the video URL on its own line, [as documented in the Codex](http://codex.wordpress.org/Embeds).

*Bug reports (but not support requests) and pull requests are welcomed [via GitHub](https://github.com/Viper007Bond/vipers-video-quicktags-migrator). Please use [the WordPress forums](https://wordpress.org/support/plugin/vipers-video-quicktags-migrator) for support.*

== Installation ==

1. Visit your WordPress admin area and navigate to Plugins → Add New.
2. Search for this plugin and install and activate it.
3. Deactivate Viper's Video Quicktags and then delete it. You won't need it.

== Frequently Asked Questions ==

= Where did the buttons in the editor go? =

You don't need them anymore. Just paste the video URL on its own line in your post. See the [WordPress Codex](http://codex.wordpress.org/Embeds) for further details.

= How do I control the default embed size? This plugin doesn't have a settings page! =

Make sure your theme is telling WordPress how wide it's content area is. See [the Content Width Codex article](https://codex.wordpress.org/Content_Width) for further details.

= Why can't this plugin do [...] that Viper's Video Quicktags plugin used to do? =

This plugin's primary goal is to make the old shortcodes work. The new embed functionality works fine out of the box, and if you want to customize those embeds, you can do so separately from this plugin.

Plus many things are just no longer possible, for example customizing the colors of the YouTube player.

= Why can't I make YouTube videos autoplay anymore? =

Previously Viper's Video Quicktags would construct the embed HTML entirely by itself. This allowed more flexibility but unfortunately this also meant it got out of date with the recommended embed HTML. For example YouTube switched to HTML5 instead of Flash and my plugin never made that switch. It also meant that embeds would often have black borders around them since the embed size wouldn't match the video proportions.

Instead WordPress directly asks YouTube itself for the embed HTML for every embed it does. This means that the embed is pixel perfect and always up to date. Unfortunately YouTube doesn't support autoplay using this method as far as I can tell.

Autoplay is lame anyway! :)

== Changelog ==

= 1.2.0 =
* Don't take over WordPress's `[video]` shortcode.
* Alias the `[flv]` and `[wmv]` shortcodes to WordPress's `[video]` shortcode since it can handle those file types out of the box.

= 1.1.0 =
* Jetpack plugin compatibility. Should fix broken embeds if you have the "Shortcode Embeds" module enabled.

= 1.0.0 =
* Initial release.