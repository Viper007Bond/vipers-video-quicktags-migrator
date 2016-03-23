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

		$this->add_shortcodes();
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

	public function add_shortcodes() {
		// These ones need special handling, such as allowing a video ID instead of a full URL
		add_shortcode( 'youtube', array( $this, 'shortcode_youtube' ) );
		add_shortcode( 'dailymotion', array( $this, 'shortcode_dailymotion' ) );
		add_shortcode( 'vimeo', array( $this, 'shortcode_vimeo' ) );
		add_shortcode( 'veoh', array( $this, 'shortcode_veoh' ) );
		add_shortcode( 'viddler', array( $this, 'shortcode_viddler' ) );
		add_shortcode( 'metacafe', array( $this, 'shortcode_metacafe' ) );
		add_shortcode( 'blip.tv', array( $this, 'shortcode_bliptv' ) );
		add_shortcode( 'bliptv', array( $this, 'shortcode_bliptv' ) );
		add_shortcode( 'flickrvideo', array( $this, 'shortcode_flickrvideo' ) );
		add_shortcode( 'ifilm', array( $this, 'shortcode_ifilm' ) );
		add_shortcode( 'spike', array( $this, 'shortcode_ifilm' ) );
		add_shortcode( 'myspace', array( $this, 'shortcode_myspace' ) );

		// These services are dead
		add_shortcode( 'googlevideo', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'gvideo', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'stage6', array( $this, 'shortcode_dead_service' ) );

		// The rest of these can just be handled by WordPress core directly
		add_shortcode( 'videofile', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'video', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'avi', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'mpeg', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'wmv', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'flash', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'flv', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'quicktime', array( $GLOBALS['wp_embed'], 'shortcode' ) );
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