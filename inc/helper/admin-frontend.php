<?php
/**
 * Manages front-end scripts for the admin-side plugin page.
 *
 * @package wordpress_slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_formats = false;
global $global_wpss_class_instance;
if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	if ( ! isset( $_POST['dont_copy_the_nonce'] )
		|| ! wp_verify_nonce( $_POST['dont_copy_the_nonce'], 'action_camera_light' )
		|| ! current_user_can( 'edit_posts' )
		) {
		$error_ = new WP_Error( '001', 'invalid token' );
		wp_send_json_error(
			$error_,
			400
		);
	} else {
		$wpss_uploads = $global_wpss_class_instance->save_the_images();
		if ( is_array( $wpss_uploads ) ) {
			$global_wpss_class_instance->wpss_enqueue_images( $wpss_uploads );
		} else {
			$show_formats = true;
		}
	}
}

$wpss_settings = $global_wpss_class_instance->db_slides_fetcher( true );
$wpss_slides   = $wpss_settings['slide_order'];
if ( ! is_array( $wpss_slides ) ) {
	$wpss_slides = [];
}
?>

<div id="wpss-loading">
	<div class="lds-spinner">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
</div>

<div class="wpss-alerts dp-none" id="wpss-main-alert">
	<p id="wpss-main-alert-text"></p>
</div>
<div id="accordion" class="dp-none">
	<h3 class="accordion-heading"><?php esc_html_e( 'Settings', 'slideshow' ); ?></h3>
	<div id="wpss-settings">
		<div class="preview-size setting">
			<fieldset>
				<legend><?php esc_html_e( 'Slide Shape(Preview): ', 'slideshow' ); ?></legend>
				<div class="radios">
					<label title=<?php esc_attr_e( 'Make preview slide square', 'slideshow' ); ?> for="square"><?php esc_html_e( 'Square', 'slideshow' ); ?></label>
					<input type="radio" name="radio-shape" id="square" value="1" <?php '1' === $wpss_settings['prev_is_sq'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>
					<label for="rectangle"><?php esc_html_e( 'Rectangle', 'slideshow' ); ?></label>
					<input type="radio" name="radio-shape" id="rectangle" value="0"  <?php '0' === $wpss_settings['prev_is_sq'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>
				</div>
			</fieldset>

			<fieldset>
				<div class="slider-pack">
					<label for="preview-width"><?php esc_html_e( 'Slide Width', 'slideshow' ); ?></label>
					<input type="text" id="preview-width" readonly style="border:0; color:#f6931f; font-weight:bold;">
					<div id="slider-width" class="slider"></div>
				</div>
			</fieldset>
			<fieldset id="preview-height-enc" class="dp-none">
				<div class="slider-pack">
					<label for="preview-height"><?php esc_html_e( 'Slide Height', 'slideshow' ); ?></label>
					<input type="text" id="preview-height" readonly style="border:0; color:#f6931f; font-weight:bold;">
					<div id="slider-height" class="slider"></div>
				</div>
			</fieldset>
		</div>
		<div class="webview-size setting">
			<fieldset>
				<legend><?php esc_html_e( 'Slide Shape(Webview): ', 'slideshow' ); ?></legend>
				<div class="radios">
					<label for="square-wv"><?php esc_html_e( 'Square', 'slideshow' ); ?></label>
					<input type="radio" name="radio-shape-wv" id="square-wv" value="1" <?php '1' === $wpss_settings['web_is_sq'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>
					<label for="rectangle-wv"><?php esc_html_e( 'Rectangle', 'slideshow' ); ?></label>
					<input type="radio" name="radio-shape-wv" id="rectangle-wv" value="0" <?php '0' === $wpss_settings['web_is_sq'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>
				</div>
			</fieldset>

			<fieldset>
				<div class="slider-pack">
					<label for="webview-width"><?php esc_html_e( 'Slide Width', 'slideshow' ); ?></label>
					<input type="text" id="webview-width" readonly style="border:0; color:#f6931f; font-weight:bold;">
					<div id="slider-width-wv" class="slider"></div>
				</div>
			</fieldset>
			<fieldset id="webview-height-enc" class="dp-none">
				<div class="slider-pack">
					<label for="webview-height"><?php esc_html_e( 'Slide Height', 'slideshow' ); ?></label>
					<input type="text" id="webview-height" readonly style="border:0; color:#f6931f; font-weight:bold;">
					<div id="slider-height-wv" class="slider"></div>
				</div>
			</fieldset>
		</div>
		<div class="slide-limit setting">
			<fieldset>
				<legend><?php esc_html_e( 'Slide Range Selector', 'slideshow' ); ?></legend>
				<div class="radios">
					<label for="limit"><?php esc_html_e( 'Enable', 'slideshow' ); ?></label>
					<input type="radio" name="radio-slide-limit" id="limit" value="1" <?php '1' === $wpss_settings['slide_limit'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>

					<label for="no-limit"><?php esc_html_e( 'Disable', 'slideshow' ); ?></label>
					<input type="radio" name="radio-slide-limit" id="no-limit" value="0" <?php '0' === $wpss_settings['slide_limit'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>
				</div>
			</fieldset>
			<fieldset id="slide-range-set">
				<div class="slide-range-info">
					<label for="slide-range-start"><?php esc_html_e( 'Slide Start:', 'slideshow' ); ?></label>
					<input type="text" id="slide-range-start" readonly style="border:0; color:#f6931f; font-weight:bold;">
					<label for="slide-range-end"><?php esc_html_e( 'Slide End:', 'slideshow' ); ?></label>
					<input type="text" id="slide-range-end" readonly style="border:0; color:#f6931f; font-weight:bold;">
				</div>
				<div id="slider-range" class="slider"></div>
			</fieldset>
		</div>
		<div class="alignment-settings setting">
			<legend><?php esc_html_e( 'Position of Slideshow', 'slideshow' ); ?></legend>
			<fieldset class="slide-alignment">
				<label for="left-align-btn"><?php esc_html_e( 'Left', 'slideshow' ); ?></label>
				<input type="radio" name="radio-slide-alignment" id="left-align-btn" value="0" <?php '0' === $wpss_settings['alignment'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>

				<label for="centre-align-btn"><?php esc_html_e( 'Centre', 'slideshow' ); ?></label>
				<input type="radio" name="radio-slide-alignment" id="centre-align-btn" value="1" <?php '1' === $wpss_settings['alignment'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>

				<label for="right-align-btn"><?php esc_html_e( 'Right', 'slideshow' ); ?></label>
				<input type="radio" name="radio-slide-alignment" id="right-align-btn" value="2" <?php '2' === $wpss_settings['alignment'] ? esc_attr_e( 'checked', 'slideshow' ) : ''; ?>>
			</fieldset>
		</div>
		<div class="submit-settings setting">
			<legend><?php esc_html_e( 'Save', 'slideshow' ); ?></legend>
			<fieldset class="slide-alignment">
				<button id="save-settings" class="sbt-setting-btn"><?php esc_html_e( 'Save', 'slideshow' ); ?></button>
				<button id="reset-settings" class="sbt-setting-btn"><?php esc_html_e( 'Reset', 'slideshow' ); ?></button>
			</fieldset>
		</div>
	</div>

	<h3 class="accordion-heading"><?php esc_html_e( 'Slides', 'slideshow' ); ?></h3>
	<div>
		<?php if ( $show_formats ) : ?>
		<div class="wpss-alerts-red">
			<p> <?php esc_html_e( 'Please ensure that the only supported image formats are JPEG, JPG, PNG and GIF', 'slideshow' ); ?></p>
		</div>
		<?php endif; ?>

		<form method="POST" action="" enctype="multipart/form-data">
			<label for="upload"><?php echo esc_html( _n( 'Add slides', 'Add more slides', count( $wpss_slides ) + 1, 'slideshow' ) ); ?></label>
			<input type="file" name="files[]" id="wpss-files" accept="image/*" multiple>
			<?php wp_nonce_field( 'action_camera_light', 'dont_copy_the_nonce' ); ?>
			<button id="upload" type="submit" class="submit-btn" name="upload"><?php esc_html_e( 'Upload', 'slideshow' ); ?></button>
		</form>
		<div id="sortable" class="slides-container">
			<?php
			foreach ( $wpss_slides as $attachment_id ) {
				printf( '<div data="%1$s" class="ui-state-default img-holder">', esc_attr( $attachment_id ) );
				printf( '<div class="slide-delete"></div>' );
				printf( '<img class="slide-img" src="%s" >', esc_url( wp_get_attachment_url( $attachment_id ) ) );
				printf( '</div>' );
			}
			?>
		</div>
		<?php if ( count( $wpss_slides ) ) : ?>
			<button type="button" class="submit-ajax" id="wpss-rearrange"><?php esc_html_e( 'Confirm', 'slideshow' ); ?></button>
			<button type="button" class="reset" id="wpss-reset"><?php esc_html_e( 'Reset', 'slideshow' ); ?></button>
		<?php endif; ?>
		<div id="delete-dialogue" class="dp-none">
				<div class="delete-caution">
					<div class="caution-logo"></div>
					<p class="caution-text"><?php esc_html_e( 'Caution', 'slideshow' ); ?></p>
				</div>
				<p class="delete-maintext"><?php esc_html_e( 'Confirm Delete', 'slideshow' ); ?></p>
				<div id="slide-delete-buttons">
					<button id="slide-cancel-delete" class="slide-delete-button"><?php esc_html_e( 'Cancel', 'slideshow' ); ?></button>
				</div>
			</div>
	</div>
</div>

