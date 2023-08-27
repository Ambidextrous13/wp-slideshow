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
	 * The master database name for the plugin.
	 *
	 * @var string
	 */
	public $table_name = '';

	/**
	 * Contains the valid keys for the WPSS Data Table.
	 *
	 * @var array
	 */
	public const TABLE_KEYS = [
		'slide_order',
		'slide_start',
		'slide_end',
		'slide_limit',
		'prev_height',
		'prev_width',
		'prev_is_sq',
		'prev_h_max',
		'prev_w_max',
		'web_height',
		'web_width',
		'web_is_sq',
		'web_h_max',
		'web_w_max',
		'alignment',
	];

	/**
	 * Stores the keys of the WPSS Data Table required for the slideshow on web pages.
	 *
	 * @var array
	 */
	public const FRONT_END_KEYS = [
		'alignment',
		'web_is_sq',
		'slide_end',
		'slide_order',
		'slide_start',
		'web_height',
		'web_width',
		'slide_limit',
	];
	
	/**
	 * Initializes the class.
	 */
	public function __construct() {
		global $wpdb;
		$this->plugin_path = dirname( __DIR__, 2 ) . '/';
		$this->table_name  = $wpdb->prefix . 'wpss';
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

	/**
	 * Registers the slides in the database by appending them to the current slide master.
	 *
	 * This function registers the slides into the database by appending them to the existing slide master.
	 * You can provide an array of attachment IDs of uploaded images, which can be obtained using the `save_the_images` function.
	 *
	 * @param array $uploads An array of attachment IDs of uploaded images obtained from the `save_the_images` function.
	 * @return boolean True if the operation succeeds; otherwise, false.
	 */
	public function wpss_enqueue_images( $uploads ) {
		$db_data = $this->db_slides_fetcher();
		$slides  = $db_data['slide_order'];
		$d_len   = count( $slides );
		foreach ( $uploads as $sr => $attachment_id ) {
			$slides[ $d_len + $sr ] = $attachment_id;
		}
		if ( ! $this->db_inserter( $slides, [ 'slide_end' => $db_data['slide_end'] ] ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Inserts a row of data into the database. Note: Validation and sanitization must be performed before using this function.
	 *
	 * @param array $insert_array An array of data containing slide order numbers as keys and attachment IDs as values.
	 * @param array $settings Additional settings provided via this array. Refer to the example array below:
	 * [
	 *    'slide_start' => 1,
	 *    'slide_end'   => -1,
	 *    'slide_limit' => 0,
	 *    'prev_height' => 180,
	 *    'prev_width'  => 180,
	 *    'prev_is_sq'  => 1,
	 *    'prev_h_max'  => 250,
	 *    'prev_w_max'  => 250,
	 *    'web_height'  => 180,
	 *    'web_width'   => 180,
	 *    'web_is_sq'   => 1,
	 *    'web_h_max'   => 1080,
	 *    'web_w_max'   => 1920,
	 * ].
	 * @return boolean True if the operation succeeds; otherwise, false.
	 */
	public function db_inserter( $insert_array, $settings = [] ) {
		if ( is_array( $insert_array ) && ! empty( $insert_array ) ) {
			foreach ( $insert_array as $sr => $id ) {
				if ( gettype( 'string' !== $id ) ) {
					$insert_array[ $sr ] = strval( $id );
				}
			}
			$settings['slide_order'] = $insert_array;
		}

		if ( $this->key_value_verifier( $settings ) ) {
			global $wpdb;
			if ( isset( $settings['slide_order'] ) && isset( $settings['slide_end'] ) ) {
				$total_slides = count( $settings['slide_order'] );
				if ( $total_slides < $settings['slide_end'] ) {
					$settings['slide_end'] = $total_slides;
				}
				$settings['slide_order'] = wp_json_encode( $settings['slide_order'] );
			} else {
				unset( $settings['slide_order'] );
			}
			foreach ( $settings as $key => $value ) {
				$data  = [
					'wpss_value' => $value,
				];
				$where = [
					'wpss_key' => $key,
				];

				$data_format = [ '%s' ];
				$wher_format = [ '%s' ];
				$wpdb->update( $this->table_name, $data, $where, $data_format, $wher_format );
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Retrieves the latest slideshow data from the database.
	 *
	 * @param boolean $is_admin_panel If the request is made for the admin panel.
	 * @return array An array containing sorted attachment IDs according to user-arranged slides if $is_admin_panel is false.
	 *              If $is_admin_panel is true, it returns all key-value pairs.
	 */
	public function db_slides_fetcher( $is_admin_panel = false ) {
		$keys = [];
		if ( $is_admin_panel ) {
			$keys = self::TABLE_KEYS;
		} else {
			$keys = self::FRONT_END_KEYS;
		}
		global $wpdb;
		$data = [];
		foreach ( $keys as $key ) {
			$temp = $wpdb->get_row(
				$wpdb->prepare(
					//phpcs:ignore
					"SELECT * FROM $this->table_name WHERE wpss_key = %s;", 
					$key,
				),
				'ARRAY_N'
			);

			$data[ $temp[0] ] = $temp[1];
		}
		if ( isset( $data['slide_order'] ) ) {
			$data['slide_order'] = json_decode( $data['slide_order'], true );
		}
		return $data;
	}

	/**
	 * Handles Ajax verification and data sending for admin-side requests.
	 *
	 * This function manages Ajax verification and data transmission for requests made on the admin side.
	 * Action: wpss_plugin_settings_fetcher.
	 *
	 * @return void
	 */
	public function fetch_settings() {
		if ( ! check_ajax_referer( 'pointBreak', 'ajaxNonce', false ) ) {
			echo wp_json_encode( 
				[ 
					'alert_string' => 'Invalid Security Token',
					'succeed'      => false
				]
			);
			wp_die( '0', 400 );
		}

		$data = $this->db_slides_fetcher( true );

		$data['slide_order'] = wp_json_encode( $data['slide_order'] );
		echo wp_json_encode( $data );
		wp_die();
	}

	/**
	 * Handles Ajax calls for saving slide settings.
	 *
	 * This function serves as an Ajax call handler for saving the settings of the slides.
	 * Action: wpss_plugin_settings_setter.
	 *
	 * @return void
	 */
	public function settings_saver() {
		if ( ! check_ajax_referer( 'pointBreak', 'ajaxNonce', false ) ) {
			echo wp_json_encode(
				[ 
					'alert_string' => 'Invalid Security Token',
					'succeed'      => false
				] 
			);
			wp_die( '0', 400 );
		}

		$rec = $_POST['wpss_settings'];
		if ( is_array( $rec ) && ! empty( $rec ) ) {
			if ( $this->key_value_verifier( $rec ) ) {
				$this->db_inserter( null, $rec );
				echo wp_json_encode(
					[ 
						'alert_string' => 'Saved!',
						'succeed'      => true
					]
				);
				wp_die( '0', 200 );
			}
			echo wp_json_encode(
				[ 
					'alert_string' => 'Verifier failed',
					'succeed'      => false
				]
			);
			wp_die( '0', 400 );
		}
		echo wp_json_encode(
			[ 
				'alert_string' => 'Invalid Data Given',
				'succeed'      => false
			]
		);
		wp_die( '0', 400 );
	}

	/**
	 * Handles Ajax calls for saving slide changes.
	 *
	 * This function serves as an Ajax call handler for saving changes in the slides, including rearrangement, deletion, and insertion.
	 * Actions: wpss_plugin_slide_rearrange.
	 *
	 * @return void
	 */
	public function slides_rearrange() {
		if ( ! check_ajax_referer( 'pointBreak', 'ajaxNonce', false ) ) {
			echo wp_json_encode( 
				[ 
					'alert_string' => 'Invalid Security Token',
					'succeed'      => false
				]
			);
			wp_die( '0', 400 );
		}

		$rec_array = $_POST['slideOrder'];
		if ( is_array( $rec_array ) && ! empty( $rec_array ) ) {
			// '-1' slide ID remover.
			if ( '-1' === $rec_array[ count( $rec_array ) - 1 ] ) {
				unset( $rec_array[ count( $rec_array ) - 1 ] );
			} else {
				$rec_array = array_flip( $rec_array );
				if ( isset( $rec_array['-1'] ) ) {
					unset( $rec_array['-1'] );
				}
				$rec_array = array_flip( $rec_array );
			}

			if ( ! $this->key_value_verifier( $rec_array ) ) {
				echo wp_json_encode(
					[ 
						'alert_string' => 'Invalid Data feed',
						'succeed'      => false
					]
				);
				wp_die( '0', 400 );
			}

			$table_data = $this->db_slides_fetcher();
			if ( $this->wpss_garbage_collector( $rec_array, $table_data['slide_order'] ) ) {
				$this->db_inserter( $rec_array, [ 'slide_end' => $table_data['slide_end'] ] );
				echo wp_json_encode(
					[ 
						'alert_string' => 'Saved!',
						'succeed'      => true 
					] 
				);
				wp_die( '0', 200 );
			}
			echo wp_json_encode(
				[ 
					'alert_string' => 'Garbage Collector failed',
					'succeed'      => false
				] 
			);
			wp_die( '0', 400 );
		}
		echo wp_json_encode(
			[ 
				'alert_string' => 'Invalid Data Given',
				'succeed'      => false
			] 
		);
		wp_die( '0', 400 );
	}

	/**
	 * Validates data before insertion.
	 *
	 * This function validates data before inserting it into the database.
	 * Please ensure that slide order is in array form, not JSON.
	 *
	 * @param array $pairs An array of key-value pairs to be inserted into the database.
	 * @return boolean `true` if no false or malicious data is found; otherwise, `false`.
	 */
	public function key_value_verifier( $pairs ) {
		if ( ! is_array( $pairs ) ) {
			return false;
		}
		// Verifier Yet to Program.
		return true;
	}

	/**
	 * Garbage Collector: Deletes images from the Media library for space efficiency.
	 *
	 * This function acts as a garbage collector and deletes specific images from the Media library to improve space efficiency.
	 *
	 * @param array $new_array An array of IDs that need to be saved; remaining IDs will be deleted.
	 * @param array $table_array An array of IDs that are saved in the database.
	 * @return boolean `true` if the operation succeeds; otherwise, `false`.
	 */
	public function wpss_garbage_collector( $new_array, $table_array ) {
		// yet to implement
		return true;
	}

}
