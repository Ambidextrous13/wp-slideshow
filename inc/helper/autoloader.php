<?php
/**
 * Helper file: Autoloader.
 *
 * @package wp-slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

namespace WPSS\Inc\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader that converts the namespace of the file into the actual file location and imports it.
 *
 * @param string $path Namespace of the file or class.
 * @return void
 */
function autoloader( $path = '' ) {
	$main_namespace = 'WPSS';
	if ( empty( $path ) || strpos( $path, '\\' ) === false || strpos( $path, $main_namespace ) !== 0 ) {
		return;
	}

	$path = str_replace( '_', '-', $path );
	$path = explode( '\\', $path );

	$location    = [];
	$location[0] = untrailingslashit( WPSS_PLUGIN_PATH );
	$location[1] = strtolower( $path[1] );
	$location[2] = strtolower( $path[2] );

	switch ( $location[2] ) {
		case 'classes':
			$location[3] = 'class-' . strtolower( $path[3] );
			break;
		default:
			$location[3] = strtolower( $path[3] );
			break;
	}
	$resource_locator = implode( '/', $location );

	require_once $resource_locator . '.php';
}

spl_autoload_register( '\WPSS\Inc\Helpers\autoloader' );
