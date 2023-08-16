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
		require_once $this->plugin_path . 'inc/helper/admin-frontend.php';
	}

	/**
	 * Saves user-uploaded images to the WordPress directory.
	 *
	 * This function handles the storage of images uploaded by users from the plugin page.
	 * Images are not stored in the plugin's database; instead, they are saved to the WordPress directory. To do so, use the `wpss_enqueue_images` function.
	 * Make sure to implement nonces and other security verifications before using this function.
	 *
	 * @param array $action Default is null. You can specify an action for the `media_handle_upload` function.
	 * @return boolean True if the images are saved successfully; otherwise, false.
	 */
	public function save_the_images( $action = null ) {
		require_once untrailingslashit( ABSPATH ) . '/wp-admin/includes/image.php';
		require_once untrailingslashit( ABSPATH ) . '/wp-admin/includes/file.php';
		require_once untrailingslashit( ABSPATH ) . '/wp-admin/includes/media.php';

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_FILES['files'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$files = $_FILES['files'];

			$attachments_id = [];
			foreach ( $files['name'] as $key => $value ) {
				if ( $files['name'][ $key ] ) {
					$file = [
						'name'     => $files['name'][ $key ],
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ],
					];

					$uploaded_file_type = $files['type'][ $key ];
					if ( preg_match( '/\Aimage\/\S*\z/', $uploaded_file_type, $output_array ) ) {
						$_FILES = [ 'upload' => $file ];

						// phpcs:ignore WordPress.Security.NonceVerification
						foreach ( $_FILES as $file => $array ) {
							$media_id = media_handle_upload(
								$file,
								0,
								[],
								[
									'action'    => $action,
									'test_form' => false,
								]
							);
							if ( is_numeric( $media_id ) ) {
								array_push( $attachments_id, $media_id );
							} else {
								return 'Error';
							}
						}
					}
				}
			}
			return $attachments_id;
		}
	}
}
