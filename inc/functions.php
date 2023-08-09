<?php
/**
 * Contains essential functions used by the plugin.
 *
 * @package wordpress_slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

use WPSS\Inc\Classes\Wpss;

define( 'WPSS_PLUGIN_PATH', dirname( __DIR__ ) . '/' );
define( 'WPSS_PLUGIN_SRC_URL', plugin_dir_url( WPSS_PLUGIN_PATH . 'assets/src/css/' ) );

/**
 * Autoloader for class files.
 */
require_once WPSS_PLUGIN_PATH . 'inc/helper/autoloader.php';
$global_wpss_class_instance = new Wpss();

add_action( 'admin_enqueue_scripts', 'wpss_assets_enqueuer_admin' );

/**
 * Handles the plugin's admin menu assets enqueueing process.
 *
 * @return void
 */
function wpss_assets_enqueuer_admin() {
	// phpcs:ignore
	if ( isset( $_GET['page'] ) && 'wpss-plugin' === $_GET['page'] ) { // just checking if current page is wpss plugin's page.
		wp_enqueue_script( 'wpss-j-ui', WPSS_PLUGIN_SRC_URL . 'js/jquery-ui.min.js', [ 'jquery' ], '1.0', false );
		wp_enqueue_script( 'wpss-main', WPSS_PLUGIN_SRC_URL . 'js/wpss-js.js', [ 'wpss-j-ui' ], filemtime( WPSS_PLUGIN_PATH . 'assets/src/js/wpss-js.js' ), false );

		wp_enqueue_style( 'wpss-j-ui', WPSS_PLUGIN_SRC_URL . 'css/jquery-ui.min.css', [], '1.0' );
		wp_enqueue_style( 'wpss-j-st', WPSS_PLUGIN_SRC_URL . 'css/jquery-ui.structure.min.css', [ 'wpss-j-ui' ], '1.0' );
		wp_enqueue_style( 'wpss-j-tm', WPSS_PLUGIN_SRC_URL . 'css/jquery-ui.theme.min.css', [ 'wpss-j-st' ], '1.0' );
		wp_enqueue_style( 'wpss', WPSS_PLUGIN_SRC_URL . 'css/wpss-admin-end-style.css', [ 'wpss-j-tm' ], filemtime( WPSS_PLUGIN_PATH . 'assets/src/css/wpss-admin-end-style.css' ) );
	}
}