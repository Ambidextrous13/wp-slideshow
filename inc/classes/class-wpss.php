<?php
/**
 * Class file: Main class file of the plugin.
 *
 * @package wp-slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

namespace WPSS\Inc\Classes;

/**
 * The main class responsible for managing both the admin-side and user-side aspects of the slideshow functionality.
 */
class Wpss {

	/**
	 * Stores the root directory path of the plugin.
	 *
	 * @var string
	 */
	public $plugin_path = '';

	/**
	 * Initializes the class.
	 */
	public function __construct() {
		$this->plugin_path = dirname( __DIR__, 2 ) . '/';
		$this->init_hooks();
	}

	/**
	 * Sets up hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_menu', [ $this, 'menu_registrar' ] );
	}

	/**
	 * Registers the WPSS page in the Admin Panel.
	 *
	 * @return void
	 */
	public function menu_registrar() {
		add_menu_page(
			__( 'Slide Show', 'slideshow' ),
			__( 'Slide Show', 'slideshow' ),
			'edit_posts',
			'wpss-plugin',
			[ $this, 'admin_front_end' ],
			plugins_url( 'wp-slideshow/assets/src/images/wpss.svg' )
		);
	}

	/**
	 * Renders the HTML for the admin page.
	 *
	 * @return void
	 */
	public function admin_front_end() {
		echo $this->plugin_path . 'inc/helper/admin-frontend.php';
		require_once $this->plugin_path . 'inc/helper/admin-frontend.php';
	}
}
