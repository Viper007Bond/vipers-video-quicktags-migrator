<?php /*

**************************************************************************

Plugin Name:  Viper's Video Quicktags Migrator
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/vipers-video-quicktags/
Version:      0.2016.03.22-alpha
Description:  Parses legacy shortcodes from the retired Viper's Video Quicktags plugin using the embed functionality that's built directly into WordPress itself.
Author:       Alex Mills (Viper007Bond)
Author URI:   http://www.viper007bond.com/

**************************************************************************/

class VipersVideoQuicktagsMigrator {
	/**
	 * VipersVideoQuicktagsMigrator constructor.
	 */
	function __construct() {
		// To avoid weirdness, bail if the original plugin is still active
		if ( class_exists( 'VipersVideoQuicktags' ) ) {
			if ( current_user_can( 'activate_plugins' ) ) {
				add_action( 'admin_notices', array( $this, 'display_vvq_active_warning' ) );
			}

			return;
		}

		$this->add_shortcodes_and_embed_handlers();
	}

	/**
	 * If the original Viper's Video Quicktags plugin is active, this function gets called in order
	 * to output a warning message in the admin area. Included in the message is a link that will
	 * deactivate the other plugin, or at least take the user to the plugin page.
	 */
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
		}
		else {
			$deactivate_url = add_query_arg( 's', rawurlencode( "Viper's Video Quicktags" ), admin_url( 'plugins.php' ) );
		}

		echo '<div class="notice notice-warning"><p>' . sprintf(
				__( "<a href='%s'><strong>Please disable the Viper's Video Quicktags plugin.</strong></a> You have the migrator plugin installed and activated and it better handles all of the functionality of the old plugin.", 'vipers-video-quicktags-migrator' ),
				esc_url( $deactivate_url )
			) . '</p></div>';
	}

	/**
	 * Registers all of the shortcodes that this plugin will handle.
	 *
	 * Additionally Metacafe isn't supported by WordPress core but it was easy to add custom
	 * support for it, so it's callback handler is also registered here.
	 */
	public function add_shortcodes_and_embed_handlers() {
		// These ones need special handling, such as allowing a video ID instead of a full URL.
		add_shortcode( 'youtube', array( $this, 'shortcode_youtube' ) );
		add_shortcode( 'dailymotion', array( $this, 'shortcode_dailymotion' ) );
		add_shortcode( 'vimeo', array( $this, 'shortcode_vimeo' ) );
		add_shortcode( 'metacafe', array( $this, 'shortcode_metacafe' ) );
		wp_embed_register_handler( 'metacafe', '#https?://(www\.)?metacafe\.com/watch/([\d-]+)#i', array( $this, 'embed_handler_metacafe' ) );

		// These can just be handled by WordPress core directly.
		// They'll either embed or they'll end up as a clickable link.
		add_shortcode( 'flickrvideo', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'videofile', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'video', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'avi', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'mpeg', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'wmv', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'flash', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'flv', array( $GLOBALS['wp_embed'], 'shortcode' ) );
		add_shortcode( 'quicktime', array( $GLOBALS['wp_embed'], 'shortcode' ) );

		// These services are dead or no longer supported by this plugin due to unpopularity.
		// If a full video URL was passed to the shortcode, then it'll end up as a clickable link.
		add_shortcode( 'googlevideo', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'gvideo', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'stage6', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'veoh', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'viddler', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'blip.tv', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'bliptv', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'ifilm', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'spike', array( $this, 'shortcode_dead_service' ) );
		add_shortcode( 'myspace', array( $this, 'shortcode_dead_service' ) );
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
	 * that value and overwrites $url (where the URL should be), and then returns them both.
	 *
	 * @param string|array $attr An empty string if there's no attributes in the shortcode, otherwise an array.
	 * @param string       $url  The existing shortcode content (between the shortcodes), which should be the URL.
	 *
	 * @return array An array of the attributes and content variables.
	 */
	public function handle_no_name_attribute( $attr, $url ) {
		if ( ! is_array( $attr ) || empty( $attr[0] ) ) {
			return array( $attr, $url );
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
			$url = $match[2];
		}
		// Equals sign between the shortcode tag and value with value unquoted
		elseif ( '=' == substr( $attr[0], 0, 1 ) ) {
			$url = substr( $attr[0], 1 );
		}
		// Normal with a space between the shortcode and the value
		else {
			$url = $attr[0];
		}

		unset( $attr[0] );

		return array( $attr, $url );
	}

	/**
	 * YouTube embeds. The actual embed is handled directly by WordPress core.
	 *
	 * @param array|string $attr Shortcode attributes. Optional.
	 * @param string       $url  The URL attempting to be embedded.
	 * @param string       $tag  The shortcode tag being used. This will be "youtube".
	 *
	 * @return string|false The embed HTML on success, otherwise the original URL.
	 *                      `$GLOBALS['wp_embed']->maybe_make_link()` can return false on failure.
	 */
	public function shortcode_youtube( $attr, $url, $tag ) {
		list( $attr, $url ) = $this->handle_no_name_attribute( $attr, $url );

		// Convert plain video IDs into URLs
		if ( ! $this->is_url( $url ) ) {
			$url = 'https://www.youtube.com/watch?v=' . $url;
		}

		return $GLOBALS['wp_embed']->shortcode( $attr, $url, $tag );
	}

	/**
	 * Dailymotion embeds. The actual embed is handled directly by WordPress core.
	 *
	 * @param array|string $attr Shortcode attributes. Optional.
	 * @param string       $url  The URL attempting to be embedded.
	 * @param string       $tag  The shortcode tag being used. This will be "dailymotion".
	 *
	 * @return string|false The embed HTML on success, otherwise the original URL.
	 *                      `$GLOBALS['wp_embed']->maybe_make_link()` can return false on failure.
	 */
	public function shortcode_dailymotion( $attr, $url, $tag ) {
		list( $attr, $url ) = $this->handle_no_name_attribute( $attr, $url );

		// [dailymotion id=x3x9u8d]
		if ( ! empty( $attr['id'] ) ) {
			$url = $attr['id'];
		}

		// Convert plain video IDs into URLs
		if ( ! $this->is_url( $url ) ) {
			$url = 'http://www.dailymotion.com/video/' . $url;
		}

		return $GLOBALS['wp_embed']->shortcode( $attr, $url, $tag );
	}

	/**
	 * Vimeo embeds. The actual embed is handled directly by WordPress core.
	 *
	 * @param array|string $attr Shortcode attributes. Optional.
	 * @param string       $url  The URL attempting to be embedded.
	 * @param string       $tag  The shortcode tag being used. This will be "vimeo".
	 *
	 * @return string|false The embed HTML on success, otherwise the original URL.
	 *                      `$GLOBALS['wp_embed']->maybe_make_link()` can return false on failure.
	 */
	public function shortcode_vimeo( $attr, $url, $tag ) {
		list( $attr, $url ) = $this->handle_no_name_attribute( $attr, $url );

		// Convert plain video IDs into URLs
		if ( ! $this->is_url( $url ) ) {
			$url = 'https://vimeo.com/' . $url;
		}

		return $GLOBALS['wp_embed']->shortcode( $attr, $url, $tag );
	}

	/**
	 * Metacafe embeds. The actual embed is handled by a separate callback function.
	 *
	 * @param array|string $attr Shortcode attributes. Optional.
	 * @param string       $url  The URL attempting to be embedded.
	 * @param string       $tag  The shortcode tag being used. This will be "metacafe".
	 *
	 * @return string|false The embed HTML on success, otherwise the original URL.
	 *                      `$GLOBALS['wp_embed']->maybe_make_link()` can return false on failure.
	 */
	public function shortcode_metacafe( $attr, $url, $tag ) {
		list( $attr, $url ) = $this->handle_no_name_attribute( $attr, $url );

		// Convert plain video IDs into URLs
		if ( ! $this->is_url( $url ) ) {
			$url = 'http://www.metacafe.com/watch/' . $url . '/'; // The trailing slash is important
		}

		// The embed will be handled by a callback registered using wp_embed_register_handler()
		return $GLOBALS['wp_embed']->shortcode( $attr, $url, $tag );
	}

	/**
	 * Metacafe video embed handler callback.
	 *
	 * @param array  $matches The RegEx matches from the provided regex when calling wp_embed_register_handler().
	 * @param array  $attr    Embed attributes.
	 * @param string $url     The original URL that was matched by the regex.
	 * @param array  $rawattr The original unmodified attributes.
	 *
	 * @return string The embed HTML.
	 */
	public function embed_handler_metacafe( $matches, $attr, $url, $rawattr ) {
		return '<iframe width="' . esc_attr( $attr['width'] ) . '" height="' . esc_attr( $attr['height'] ) . '" src="' . esc_url( 'http://www.metacafe.com/embed/' . $matches[2] . '/' ) . '" frameborder="0" allowfullscreen></iframe>';
	}

	/**
	 * Handles the embeds from services that have shut down or just are no longer supported by this plugin.
	 *
	 * If just a video ID was used, then an error message is shown.
	 * If a full URL was used, then it's handed off to WordPress core as a last-ditch attempt at embedding.
	 *
	 * @param array|string $attr Shortcode attributes. Optional.
	 * @param string       $url  The URL attempting to be embedded.
	 * @param string       $tag  The shortcode tag being used.
	 *
	 * @return string|false The embed HTML on success, otherwise the original URL.
	 *                      `$GLOBALS['wp_embed']->maybe_make_link()` can return false on failure.
	 */
	public function shortcode_dead_service( $attr, $url, $tag ) {
		list( $attr, $url ) = $this->handle_no_name_attribute( $attr, $url );

		// Return a plain message for non-URL embeds as there's nothing that can be done with them.
		if ( ! $this->is_url( $url ) ) {
			return apply_filters( 'vvq_dead_service_message', '<em>' . __( 'A video used to be embedded here but the service that it was hosted on has shut down.' ) . '</em>' );
		}

		// Otherwise let WordPress core handle it, likely resulting in a plain, clickable link.
		// This lets visitors click through if they want, but also other plugins to provide embed support somehow.
		return $GLOBALS['wp_embed']->shortcode( $attr, $url, $tag );
	}
}

/**
 * Spins up an instance of the plugin's class if one doesn't already exist, then returns it.
 *
 * @return VipersVideoQuicktagsMigrator The instance of this class.
 */
function VipersVideoQuicktagsMigrator() {
	global $VipersVideoQuicktagsMigrator;

	if ( ! isset( $VipersVideoQuicktagsMigrator ) || ! is_a( $VipersVideoQuicktagsMigrator, 'VipersVideoQuicktagsMigrator' ) ) {
		$VipersVideoQuicktagsMigrator = new VipersVideoQuicktagsMigrator();
	}

	return $VipersVideoQuicktagsMigrator;
}

add_action( 'plugins_loaded', 'VipersVideoQuicktagsMigrator' );