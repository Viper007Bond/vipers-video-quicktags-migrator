<?php /*

**************************************************************************

Plugin Name:  Viper's Video Quicktags Migrator
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/vipers-video-quicktags/
Version:      1.0.0-alpha
Description:  Parses legacy shortcodes from the retired Viper's Video Quicktags plugin using the embed functionality that's built directly into WordPress itself.
Author:       Alex Mills (Viper007Bond)
Author URI:   http://www.viper007bond.com/

**************************************************************************/

class VipersVideoQuicktagsMigrator {
	function __construct() {

	}
}

add_action( 'plugins_loaded', 'VipersVideoQuicktagsMigrator' );

function VipersVideoQuicktagsMigrator() {
	global $VipersVideoQuicktagsMigrator;

	if ( ! isset( $VipersVideoQuicktagsMigrator ) || is_a( $VipersVideoQuicktagsMigrator, 'VipersVideoQuicktagsMigrator' ) ) {
		$VipersVideoQuicktagsMigrator = new VipersVideoQuicktagsMigrator();
	}

	return $VipersVideoQuicktagsMigrator;
}