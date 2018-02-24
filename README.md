# Viper's Video Quicktags Migrator

[Viper's Video Quicktags](https://github.com/Viper007Bond/vipers-video-quicktags) was a popular but no longer maintained plugin that I wrote that made it easy to embed videos into your WordPress site. Since the plugin was originally written over 10 years ago, WordPress has added [native embed support](http://codex.wordpress.org/Embeds) which allows you to easily embed videos out of the box. Its functionality is far superior to that of the plugin and most people have switched to using it.

Unfortunately many people have been left with old posts that use the plugin's shortcodes and manually migrating over to the new embed method would be a time consuming and tedious task.

Instead this plugin will take over parsing those shortcodes, making use of the native WordPress functionality instead.