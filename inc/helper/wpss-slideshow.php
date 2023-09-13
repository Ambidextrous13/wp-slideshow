<?php
/**
 * Manages front-end scripts for the slideshow.
 *
 * @package wordpress_slideshow
 * @author t0nystark <https://profiles.wordpress.org/t0nystark/>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates slideshow HTML based on the provided settings.
 *
 * @param object $wpss_instance An instance of the Wpss class.
 * @return string HTML representation of the slideshow.
 */
function wpss_plugin_front_end_html( $wpss_instance ) {
	$wpss_frontend_payload = $wpss_instance->db_slides_fetcher();
	$wpss_slide_is_limited = (int) $wpss_frontend_payload['slide_limit'];
	$wpss_slide_start      = (int) $wpss_frontend_payload['slide_start'];
	$wpss_slide_end        = (int) $wpss_frontend_payload['slide_end'];
	$wpss_slide_order      = $wpss_frontend_payload['slide_order'];
	$wpss_slide_alignment  = (int) $wpss_frontend_payload['alignment'];
	$wpss_slide_is_square  = (int) $wpss_frontend_payload['web_is_sq'];
	$wpss_img_height       = (int) $wpss_frontend_payload['web_height'];
	$wpss_img_width        = (int) $wpss_slide_is_square ? $wpss_img_height : $wpss_frontend_payload['web_width'];
	$frontend_hero_resp    = '';

	if ( $wpss_slide_order ) {
		if ( 1 === $wpss_slide_is_limited ) {
			$wpss_slide_iterator = 1 <= $wpss_slide_start ? $wpss_slide_start : 1;
			$wpss_slide_end      = $wpss_slide_iterator < $wpss_slide_end && $wpss_slide_end <= count( $wpss_slide_order ) ? $wpss_slide_end : count( $wpss_slide_order );
		} else {
			$wpss_slide_iterator = 1;
			$wpss_slide_end      = count( $wpss_slide_order );
		}
		$wpss_img_height = 20 < $wpss_img_height ? $wpss_img_height : 25;
		$wpss_img_width  = 20 < $wpss_img_width ? $wpss_img_width : 25;

		$wpss_align_class = '';
		if ( 0 === $wpss_slide_alignment ) {
			$wpss_align_class = 'wpss-align-left';
		} elseif ( 1 === $wpss_slide_alignment ) {
			$wpss_align_class = 'wpss-align-centre';
		} elseif ( 2 === $wpss_slide_alignment ) {
			$wpss_align_class = 'wpss-align-right';
		}

		$frontend_hero_resp  .= sprintf( '<div id="wpss-slideshow" class="wpss-slideshow %1$s">', $wpss_align_class );
		$frontend_hero_resp  .= '<ul class="pbSliderContainer">';
		$wpss_activation_text = 'active';

		$wpss_slide_counter = 1;
		for ( $wpss_slide_iterator; $wpss_slide_iterator <= $wpss_slide_end; $wpss_slide_iterator++ ) {
			$wpss_attachment_id   = $wpss_slide_order[ $wpss_slide_iterator - 1 ];
			$frontend_hero_resp  .= sprintf( '<li class="pbSlider slide-%1$s %2$s">', $wpss_slide_counter, $wpss_activation_text );
			$frontend_hero_resp  .= sprintf( '<img src="%1$s" height="%2$s" width="%3$s">', esc_url( wp_get_attachment_url( $wpss_attachment_id ) ), $wpss_img_height, $wpss_img_width );
			$frontend_hero_resp  .= '</li>';
			$wpss_activation_text = 'hidden';
			++$wpss_slide_counter;
		}
		$frontend_hero_resp .= '</ul>';
		$frontend_hero_resp .= '</div>';
		return wp_kses(
			$frontend_hero_resp,
			[
				'ul'  => [ 'class' => [] ],
				'li'  => [ 'class' => [] ],
				'img' => [
					'src'    => [],
					'height' => [],
					'width'  => [],
				],
				'div' => [
					'id'    => [
						'wpss-slideshow',
					],
					'class' => [
						'wpss-slideshow',
						'wpss_align_left',
						'wpss_align_centre',
						'wpss_align_right',
					],
				],
			]
		);
	} else {
		return '';
	}
}
