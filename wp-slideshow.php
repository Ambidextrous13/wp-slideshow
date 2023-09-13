<?php
/**
 *  Slideshow maker plugin.
 *
 * @package wp-slideshow
 * @author  t0nystark <https://profiles.wordpress.org/t0nystark/>
 *
 * @license GPLv3 <https://www.gnu.org/licenses/gpl-3.0.html>
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Slideshow
 * Description:       Easy way to introduce slideshow on your webpage.
 * Version:           0.1.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            t0nystark
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       slideshow
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/functions.php';
