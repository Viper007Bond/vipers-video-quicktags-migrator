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
		//add_shortcode( 'dailymotion', array( $this, 'shortcode_dailymotion' ) );
		//add_shortcode( 'vimeo', array( $this, 'shortcode_vimeo' ) );
		//add_shortcode( 'veoh', array( $this, 'shortcode_veoh' ) );
		//add_shortcode( 'viddler', array( $this, 'shortcode_viddler' ) );
		//add_shortcode( 'metacafe', array( $this, 'shortcode_metacafe' ) );
		//add_shortcode( 'blip.tv', array( $this, 'shortcode_bliptv' ) );
		//add_shortcode( 'bliptv', array( $this, 'shortcode_bliptv' ) );
		//add_shortcode( 'flickrvideo', array( $this, 'shortcode_flickrvideo' ) );
		//add_shortcode( 'ifilm', array( $this, 'shortcode_ifilm' ) );
		//add_shortcode( 'spike', array( $this, 'shortcode_ifilm' ) );
		//add_shortcode( 'myspace', array( $this, 'shortcode_myspace' ) );

		// These services are dead
		//add_shortcode( 'googlevideo', array( $this, 'shortcode_dead_service' ) );
		//add_shortcode( 'gvideo', array( $this, 'shortcode_dead_service' ) );
		//add_shortcode( 'stage6', array( $this, 'shortcode_dead_service' ) );

		// The rest of these can just be handled by WordPress core directly
		//add_shortcode( 'videofile', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'video', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'avi', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'mpeg', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'wmv', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'flash', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'flv', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		//add_shortcode( 'quicktime', array( $GLOBALS['wp_embed'], 'shortcode' ) );
	}

	/**
	 * A simple checker to see if a string looks like a URL or not.
	 *
	 * This function should NOT be used for security purposes, rather
	 * it's just a quick and dirty way to tell video IDs from video URLs.
	 *
	 * @param string $string The string to check.
	 *
	 * @return bool Whether the string looks like a URL or not.
	 */
	public function is_url( $string ) {
		return (bool) preg_match( '#^https?://#i', $string );
	}

	/**
	 * Parse legacy formatted shortcodes into a more standardized format.
	 *
	 * Way back in the day, before shortcodes existed, WordPress.com created pseudo-shortcodes
	 * that accepted no-name parameters (attributes) instead of between opening and closing tags.
	 * This weird format is still in use to this day.
	 *
	 * Example: [youtube https://www.youtube.com/watch?v=EYs_FckMqow]
	 *
	 * With this format, the URL ends up being stored as $attr[0]. This helper function takes
	 * that value and overwrites $content (where the URL should be), and then returns them both.
	 *
	 * @param string|array $attr    An empty string if there's no attributes in the shortcode, otherwise an array.
	 * @param string       $content The existing shortcode content (between the shortcodes).
	 *
	 * @return array An array of the attributes and content variables.
	 */
	public function handle_no_name_attribute( $attr, $content ) {
		if ( ! is_array( $attr ) || empty( $attr[0] ) ) {
			return array( $attr, $content );
		}

		// Undo some of what wptexturize() did to the value
		$find_and_replace = array(
			'&#215;'  => 'x',
			'&#8211;' => '--',
			'&#8212;' => '---',
			'&#8230;' => '...',
			'&#8220;' => '"',
			'&#8221;' => '"',
			'&#8217;' => "'",
			'&#038;'  => '&',
		);
		$attr[0] = str_replace( array_keys( $find_and_replace ), array_values( $find_and_replace ), $attr[0] );

		// Equals sign between the shortcode tag and value with value inside of quotes
		if ( preg_match( '#=("|\')(.*?)\1#', $attr[0], $match ) ) {
			$content = $match[2];
		}
		// Equals sign between the shortcode tag and value with value unquoted
		elseif ( '=' == substr( $attr[0], 0, 1 ) ) {
			$content = substr( $attr[0], 1 );
		}
		// Normal with a space between the shortcode and the value
		else {
			$content = $attr[0];
		}

		unset( $attr[0] );

		return array( $attr, $content );
	}

	public function shortcode_youtube( $attr, $content, $tag ) {
		list( $attr, $content ) = $this->handle_no_name_attribute( $attr, $content );
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