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
		// To avoid weirdness, bail if the original plugin is still active
		if ( class_exists( 'VipersVideoQuicktags' ) ) {
			if ( current_user_can( 'activate_plugins' ) ) {
				add_action( 'admin_notices', array( $this, 'display_vvq_active_warning' ) );
			}

			return;
		}
	}

	public function display_vvq_active_warning() {
		$vvq_file = 'vipers-video-quicktags/vipers-video-quicktags.php';

		if ( in_array( $vvq_file, get_option( 'active_plugins', array() ) ) ) {
			$deactivate_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'deactivate',
						'plugin' => rawurlencode( $vvq_file ),
					),
					admin_url( 'plugins.php' )
				),
				'deactivate-plugin_' . $vvq_file
			);
		} else {
			$deactivate_url = add_query_arg( 's', rawurlencode( "Viper's Video Quicktags" ), admin_url( 'plugins.php' ) );
		}

		echo '<div class="notice notice-warning"><p>' . sprintf(
				__( "<a href='%s'><strong>Please disable the Viper's Video Quicktags plugin.</strong></a> You have the migrator plugin installed and activated and it better handles all of the functionality of the old plugin.", 'vipers-video-quicktags-migrator' ),
				esc_url( $deactivate_url )
			) . '</p></div>';
	}
}

add_action( 'plugins_loaded', 'VipersVideoQuicktagsMigrator' );

function VipersVideoQuicktagsMigrator() {
	global $VipersVideoQuicktagsMigrator;

	if ( ! isset( $VipersVideoQuicktagsMigrator ) || ! is_a( $VipersVideoQuicktagsMigrator, 'VipersVideoQuicktagsMigrator' ) ) {
		$VipersVideoQuicktagsMigrator = new VipersVideoQuicktagsMigrator();
	}

	return $VipersVideoQuicktagsMigrator;
}