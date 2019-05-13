<?php
/**
 * Plugin Name: Headless Forms
 * Plugin URI: https://formfunfunction.com/
 * Description: A simple form-builder useful for headless WordPress configurations.
 * Author: Jamie Morgan-Ward <jamie@formfunfunction.com>
 * Author URI: https://formfunfunction.com/
 * Text Domain: wp-headless-forms
 * Version: 0.0.2-dev
 *
 * @package WPHeadlessForms
 */

defined( 'ABSPATH' ) || exit;

define( 'WPHF_PLUGIN', __FILE__ );
define( 'WPHF_PLUGIN_DIR', untrailingslashit( dirname( WPHF_PLUGIN ) ) );

// Require Emogrifier.
if ( ! class_exists( 'Emogrifier' ) ) {
	require_once WPHF_PLUGIN_DIR . '/includes/lib/emogrifier/src/Emogrifier.php';
}

if ( ! class_exists( 'WPHeadlessForms' ) ) {
	include_once WPHF_PLUGIN_DIR . '/includes/class-wpheadlessforms.php';
}

/**
 * Initializes the main plugin instance.
 *
 * @since 0.0.1
 */
add_action( 'plugins_loaded', array( WPHeadlessForms::get_instance(), 'init' ) );

/**
 * Plugin activation hook
 *
 * @since 0.0.1
 */
register_activation_hook( WPHF_PLUGIN, array( 'WPHeadlessForms', 'install' ) );


/**
 * Plugin deactivation hook
 *
 * @since 0.0.1
 */
register_deactivation_hook( WPHF_PLUGIN, array( 'WPHeadlessForms', 'deactivate' ) );
