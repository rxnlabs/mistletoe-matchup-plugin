<?php
/**
 * Plugin Name:       Mistletoe Matchup Fantasy Draft
 * Plugin URI:        https://rxnlabs.com/mistletoe-matchup-fantasy-draft
 * Description:       A joyful fantasy draft game for made for TV holiday movies. Create leagues, draft holiday movie moments, and compete with friends for festive glory!.
 * Version:           1.0.0
 * Author:            RXN Labs
 * Author URI:        https://rxnlabs.com
 * Text Domain:       mistletoe-matchup-fantasy-draft
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package RXNLabs\MistletoeMatchupFantasyDraft
 */

namespace RXNLabs\MistletoeMatchupFantasyDraft;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) || ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

// add functionality for custom plugin
function cshp_live_reload_plugin_support( $plugin_paths ) {
	return array_merge( $plugin_paths, [
		'insert-plugin-folder-name' => [
			'watch_css_path' => [ 'build', 'build/css'],
			'watch_js_path' => 'build/js'
       ]
    ] );
}
add_filter( 'cshp_lr_plugin_paths', 'cshp_live_reload_plugin_support' );

/**
 * Plugin bootstrap.
 */
final class Plugin {
	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		// Autoload classes.
		if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			require_once __DIR__ . '/vendor/autoload.php';
		}

		// Initialize core logic.
		add_action( 'plugins_loaded', array( __CLASS__, 'load' ) );
	}

	/**
	 * Load plugin components.
	 */
	public static function load() {
		// Initialize core logic or service providers here.
	}
}

// Initialize plugin.
Plugin::init();
