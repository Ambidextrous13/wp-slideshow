<?php
/**
 * Manages front-end scripts for the admin-side plugin page.
 *
 * @package wordpress_slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

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
<div id="accordion">
	<h3 class="accordion-heading"><?php esc_html_e( 'Settings', 'slideshow' ); ?></h3>
	<div id="settings">
		<div class="preview-size">
			<fieldset>
				<legend>Preview Slide Shape: </legend>
				<label title = "Make preview slide square" for="square">Square(Recommended)</label>
				<input type="radio" name="radio-shape" id="square" value="1" checked>
				
				<label for="rectangle">Rectangle</label>
				<input type="radio" name="radio-shape" id="rectangle" value="0">
			</fieldset>
			
			<fieldset>
				<label for="preview_width">Preview Slide Width</label>
				<input type="text" id="preview_width" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_width" class="slider"></div>
			</fieldset>
			<fieldset id="preview_height_enc" class="dp-none">
				<label for="preview_height">Preview Slide Height</label>
				<input type="text" id="preview_height" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_height" class="slider"></div>
			</fieldset>
		</div>
		<div class="webview-size">
			<fieldset>
				<legend>Actual Slide Shape: </legend>
				<label for="square-wv">Square(Recommended)</label>
				<input type="radio" name="radio-shape-wv" id="square-wv" value="1" checked>
				
				<label for="rectangle-wv">Rectangle</label>
				<input type="radio" name="radio-shape-wv" id="rectangle-wv" value="0">
			</fieldset>
			
			<fieldset>
				<label for="webview_width">Actual Slide Width</label>
				<input type="text" id="webview_width" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_width_wv" class="slider"></div>
			</fieldset>
			<fieldset id="webview_height_enc" class="dp-none">
				<label for="webview_height">Actual Slide Height</label>
				<input type="text" id="webview_height" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider_height_wv" class="slider"></div>
			</fieldset>
		</div>
		<div class="slide-limit">
			<fieldset>
				<legend>Slide Range Selector: </legend>
				<label for="limit">Enable</label>
				<input type="radio" name="radio-slide-limit" id="limit" value="1" checked>

				<label for="no-limit">Disable</label>
				<input type="radio" name="radio-slide-limit" id="no-limit" value="0">
			</fieldset>
			<fieldset id="slide-range-set">
				<label for="slide-range">Slides Range:</label>
				<input type="text" id="slide-range" readonly style="border:0; color:#f6931f; font-weight:bold;">
				<div id="slider-range" class="slider"></div>
			</fieldset>
		</div>
	</div>
	
	<h3 class="accordion-heading"><?php esc_html_e( 'Slides', 'slideshow' ); ?></h3>
	<div>
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