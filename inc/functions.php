<?php
/**
 * Contains essential functions used by the plugin.
 *
 * @package wordpress_slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPSS\Inc\Classes\Wpss;

define( 'WPSS_PLUGIN_PATH', dirname( __DIR__ ) . '/' );
define( 'WPSS_PLUGIN_SRC_URL', plugin_dir_url( WPSS_PLUGIN_PATH . 'assets/src/css/' ) );

/**
 * Autoloader for class files.
 */
require_once WPSS_PLUGIN_PATH . 'inc/helper/autoloader.php';

register_activation_hook( WPSS_PLUGIN_PATH . 'wp-slideshow.php', 'create_the_wpss_plugin_data_table' );

$global_wpss_class_instance = new Wpss();

/**
 * Creates a database table upon activation of the plugin.
 *
 * @return void
 */
function create_the_wpss_plugin_data_table() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$table_name = $wpdb->prefix . 'wpss';

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
		$sql = "CREATE TABLE $table_name (
		wpss_key VARCHAR(255) NOT NULL,
		wpss_value TEXT NOT NULL,
		PRIMARY KEY  (wpss_key)
		) $charset_collate;";

		require_once untrailingslashit( ABSPATH ) . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$default_data = [
			'slide_order' => [],
			'slide_start' => '1',
			'slide_end'   => '-1',
			'slide_limit' => '0',
			'prev_height' => '180',
			'prev_width'  => '180',
			'prev_is_sq'  => '1',
			'prev_h_max'  => '250',
			'prev_w_max'  => '250',
			'web_height'  => '180',
			'web_width'   => '180',
			'web_is_sq'   => '1',
			'web_h_max'   => '1080',
			'web_w_max'   => '1920',
			'alignment'   => '0',
		];
		foreach ( $default_data as $key => $value ) {
			$storable = $value;
			if ( is_array( $value ) ) {
				$storable = wp_json_encode( $value );
			}
			$data  = [
				'wpss_key'   => $key,
				'wpss_value' => $storable,
			];
			$metas = [ '%s', '%s' ];
			$wpdb->insert( $table_name, $data, $metas );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'wpss_assets_enqueuer_admin' );
add_action( 'wp_enqueue_scripts', 'wpss_front_end_assets_enqueuer' );
add_action( 'wp_ajax_wpss_plugin_settings_fetcher', [ $global_wpss_class_instance, 'fetch_settings' ] );
add_action( 'wp_ajax_wpss_plugin_settings_setter', [ $global_wpss_class_instance, 'settings_saver' ] );
add_action( 'wp_ajax_wpss_plugin_slide_rearrange', [ $global_wpss_class_instance, 'slides_rearrange' ] );

/**
 * Adds slideshow.
 */
add_shortcode( 'WPSS_SlideShow', [ $global_wpss_class_instance, 'frontend_hero' ] );

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

		wp_localize_script(
			'wpss-main',
			'ajaxData',
			[
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce' => wp_create_nonce( 'pointBreak' ),
			]
		);
	}
}

/**
 * Handles the plugin's front end assets enqueueing process.
 *
 * @return void
 */
function wpss_front_end_assets_enqueuer() {
	wp_enqueue_script( 'wpss-slider', WPSS_PLUGIN_SRC_URL . 'js/PBslider.js', [ 'jquery' ], '1.0', true );
	wp_enqueue_style( 'wpss-slider', WPSS_PLUGIN_SRC_URL . 'css/wpss-front-end-style.css', [], '1.0' );
}
