( function ( $ ) {
	$( document ).ready( function () {

		// Locals
		const settings = {
			slide_start: null,
			slide_end: null,
			slide_limit: null,
			prev_height: null,
			prev_width: null,
			prev_is_sq: null,
			web_height: null,
			web_width: null,
			web_is_sq: null,
			alignment: null,
		};

		$( '.img-holder' ).css( 'height', 120 );
		$( '.img-holder' ).css( 'width', 120 );

		$( 'input[type="radio"]' ).checkboxradio();
		$( document ).tooltip();

		$( "#upload" ).button({
			icon: "ui-icon-arrowthickstop-1-n",
			iconPosition: "end"	
		});

		$( "#sortable" ).sortable({
			revert: true,
			tolerance: "pointer",
			cursor: "grab"
		});

		$( "#sortable" ).disableSelection();

		$( '#upload' ).button( {
			icon: 'ui-icon-arrowthickstop-1-n',
			iconPosition: 'end',
		} );


		// Ajax Calls

		// eslint-disable-next-line no-undef
		let ajaxUrl;
		// eslint-disable-next-line no-undef
		let ajaxNonce;

		// eslint-disable-next-line no-undef
		if ( ajaxData ) {
			// eslint-disable-next-line no-undef
			ajaxUrl = ajaxData.ajaxUrl;
			// eslint-disable-next-line no-undef
			ajaxNonce = ajaxData.ajaxNonce;
		}

		/**
		 * Makes ajax calls.
		 * @param {object}           data            : data to be sent via ajax call.
		 * @param {callableFunction} successCallback : function, executed on successful ajax call.
		 * @param {callableFunction} failureCallback : function, executed on failed ajax call.
		 * @param {boolean}          async           : true for asynchronous request.
		 */
		function doAjax( data, successCallback = null, failureCallback = null, async = true ) {
			loaderPlease();
			$.ajax( {
				url: ajaxUrl,
				type: 'post',
				data: data,
				success: ( response ) => {
					if ( successCallback ) {
						successCallback( response );
					} else {
						wpssAlert( response, true );
					}
					noMoreLoader();
				},
				error: ( response ) => {
					if ( failureCallback ) {
						failureCallback( response );
					} else {
						if ( response.responseJSON ) {
							wpssAlert( response.responseJSON, false );
						} else {
							wpssAlert( response, false );
						}
						setTimeout( () => {
							location.reload();
						}, 2600 );
					}
					noMoreLoader();
				},
				async: async,
			 } );
		}

		let ajaxOP;
		const ajaxPayload = {
			action: 'wpss_plugin_settings_fetcher',
			ajaxNonce: ajaxNonce,
		};

		/**
		 * Callback function executed after a successful AJAX request.
		 *
		 * @param {JSON} response JSON object containing AJAX data.
		 */
		const ajaxSuccessCallback = function ( response ) {
			wpssAlert( response );
			ajaxOP = JSON.parse( response );
			slidesOrder = JSON.parse( ajaxOP.slide_order );

			ajaxOP.slide_end = -1 === parseInt( ajaxOP.slide_end ) ? slidesOrder.length : ajaxOP.slide_end;
			slidesOrder.push( '-1' );
			$( '#preview_width' ).val( ajaxOP.prev_width + 'px' );
			$( '#preview_height' ).val( ajaxOP.prev_height + 'px' );
			$( '#webview_width' ).val( ajaxOP.web_width + 'px' );
			$( '#webview_height' ).val( ajaxOP.web_height + 'px' );
			$( '#slide-range-start' ).val( ajaxOP.slide_start );
			$( '#slide-range-end' ).val( ajaxOP.slide_end );
			ajaxOP.prev_height = '1' === ajaxOP.prev_is_sq ? ajaxOP.prev_width : ajaxOP.prev_height;
			$( '.img-holder' ).css( 'height', ajaxOP.prev_height );
			$( '.img-holder' ).css( 'width', ajaxOP.prev_width );

			settings.slide_limit = ajaxOP.slide_limit;
			settings.prev_is_sq = ajaxOP.prev_is_sq;
			settings.web_is_sq = ajaxOP.web_is_sq;
			settings.alignment = ajaxOP.alignment;
			settings.slide_start = ajaxOP.slide_start;
			settings.slide_end = ajaxOP.slide_end;
			settings.prev_height = ajaxOP.prev_height;
			settings.prev_width = ajaxOP.prev_width;
			settings.web_height = ajaxOP.web_height;
			settings.web_width = ajaxOP.web_width;
			if ( $( '#accordion' ).hasClass( 'dp-none' ) ) {
				$( '#accordion' ).removeClass( 'dp-none' );
			}
		};
		doAjax( ajaxPayload, ajaxSuccessCallback, null, false );


		// Settings Saver.
		$( '#save-settings' ).on( 'click', function() {
			const settingSaverPayload = {
				action: 'wpss_plugin_settings_setter',
				ajaxNonce: ajaxNonce,
				wpss_settings: settings,
			};
			doAjax( settingSaverPayload );
		} );


		// rearrange submit button
		$( '#wpss-rearrange' ).on( 'click', function() {
			const wpssSlideRearrange = {
				action: 'wpss_plugin_slide_rearrange',
				ajaxNonce: ajaxNonce,
				slideOrder: slidesOrder,
			};

			doAjax( wpssSlideRearrange );
		} );

		// End of Ajax

		// disable upload button
		$( '#upload' ).prop( 'disabled', true ).attr( 'title', 'Please select file(s) to upload' );
		$( '#wpss-files' ).on( 'change', ( event ) => {
			const target = event.target;
			if ( target.files?.length ) {
				$( '#upload' ).prop( 'disabled', false ).removeAttr( 'title' );
			} else {
				$( '#upload' ).prop( 'disabled', true ).attr( 'title', 'Please select file(s) to upload' );
			}
		} );
		
		$( "#sortable" ).on( "sortupdate", function( event, ui ) {
			console.log(
				$( "#sortable" ).sortable(
					"serialize",
					{ 
						key: "sort",
						attribute: "data"
					}
				)
			);
		} );

		$( "#accordion" ).accordion({
			heightStyle: "content",
			collapsible: true,
			active: 1,
			icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
		});
		var max_width = $( window ).width();
		var max_height = $( window ).height();

		$( "#slider_width" ).slider({
			range: "min",
			value: 50,
			min: 1,
			max: max_width/3,
			slide: function( event, ui ) {
				$( '.img-holder' ).css( 'width', ui.value );
				$( "#preview_width" ).val( ui.value + 'px' );
			},
		});

		$( "#preview_width" ).val( $( "#slider_width" ).slider( "value" ) + 'px' );
		
		$preview_shape = '';

		$( '#settings' ).on( 'change', function() {
			$preview_shape = $('input[name=radio-shape]:checked', '#settings').val();
			if ( $preview_shape == '0'  ) {
				$( '#preview_height_enc' ).removeClass( 'dp-none' );
				$( "#slider_height" ).slider({
					range: "min",
					value: 50,
					min: 1,
					max: max_height/5,
					change: function( event, ui ) {
						$( '.img-holder' ).css( 'height', ui.value );
					},
					slide: function( event, ui ) {
						$( "#preview_height" ).val( ui.value + 'px' );
					}
				});
				$( "#preview_height" ).val( $( "#slider_height" ).slider( "value" ) + 'px' );
			} else {
				if( ! $( '#preview_height_enc' ).hasClass( 'dp-none' ) ){
					$( '#preview_height_enc' ).addClass( 'dp-none' );
				}
			}
		} );

		/**
		 * Creates sliders.
		 * @param {string}         selector CSS selector to select an HTML element for slider.
		 * @param {string|boolean} range    'min', 'max' or true. A min range goes from the slider min to one handle. A max range goes from one handle to the slider max. true for both open end.
		 * @param {int}            value    Determines the value of the slider.
		 * @param {int}            max      The maximum value of the slider. Default: 100.
		 * @param {int}            min      The minimum value of the slider.
		 * @param {callback}       slide    Triggered after the user slides a handle, if the value has changed.
		 * @param {callback}       change   Triggered on every mouse move during slide.
		 */
		function sliderBuilder( selector, range, value, max, min = 0, slide = null, change = null ) {
			const args = {
				range: range,
				max: parseInt( max ),
				min: parseInt( min ),
			};
			if ( Array.isArray( value ) ) {
				args.values = value;
			} else {
				args.value = value;
			}
			if ( null !== slide ) {
				args.slide = ( event, ui ) => {
					slide( ui );
				};
			}
			if ( null !== change ) {
				args.change = ( event, ui ) => {
					change( ui );
				};
			} else {
				args.change = ( event ) => {
					$( '#wpss-settings' ).trigger( 'change', [ event ] );
				};
			}
			$( selector ).slider( args );
		}

		$( "#slider_width_wv" ).slider({
			range: "min",
			value: 50,
			min: 1,
			max: max_width/3,
			change: function( event, ui ) {
				// db save
			},
			slide: function( event, ui ) {
				$( "#webview_width" ).val( ui.value + 'px' );
			}
		});

		$( "#webview_width" ).val( $( "#slider_width" ).slider( "value" ) + 'px' );
		
		var webview_shape = '';

		$( '#settings' ).on( 'change', function() {
			webview_shape = $('input[name=radio-shape-wv]:checked', '#settings').val();
			if ( webview_shape == '0'  ) {
				$( '#webview_height_enc' ).removeClass( 'dp-none' );
				$( "#slider_height_wv" ).slider({
					range: "min",
					value: 50,
					min: 1,
					max: max_height/5,
					change: function( event, ui ) {
						// db save
					},
					slide: function( event, ui ) {
						$( "#webview_height" ).val( ui.value + 'px' );
					}
				});
				$( "#webview_height" ).val( $( "#slider_height_wv" ).slider( "value" ) + 'px' );
			} else {
				if( ! $( '#webview_height_enc' ).hasClass( 'dp-none' ) ){
					$( '#webview_height_enc' ).addClass( 'dp-none' );
				}
			}
		} );

		$( "#slider-range" ).slider({
			range: true,
			min: 0,
			max: 500,
			values: [ 75, 300 ],
			slide: function( event, ui ) {
				$( "#slide-range" ).val( "Start: " + ui.values[ 0 ] + " - Stop: " + ui.values[ 1 ] );
			}
		});
		$( "#slide-range" ).val( "Start: " + $( "#slider-range" ).slider( "values", 0 ) +" - Stop: " + $( "#slider-range" ).slider( "values", 1 ) );

		var range_enable =  '';
		$( '#settings' ).on( 'change', function() {
			range_enable = $('input[name=radio-slide-limit]:checked', '#settings').val();
			if ( range_enable == '1' ) {
				if( $( '#slide-range-set' ).hasClass( 'dp-none' ) ){
					$( '#slide-range-set' ).removeClass( 'dp-none' );
				}
			}else if ( range_enable == '0' ) {
				if( ! $( '#slide-range-set' ).hasClass( 'dp-none' ) ){
					$( '#slide-range-set' ).addClass( 'dp-none' );
				}
			}
		} );

		let previewShape = settings.prev_is_sq;
		let webviewShape = settings.web_is_sq;
		let rangeEnable = settings.slide_limit;
		let slideAlignment = settings.alignment;
		$( '#wpss-settings' ).on( 'change', function() {
			previewShape = $( 'input[name=radio-shape]:checked', '#wpss-settings' ).val();
			webviewShape = $( 'input[name=radio-shape-wv]:checked', '#wpss-settings' ).val();
			rangeEnable = $( 'input[name=radio-slide-limit]:checked', '#wpss-settings' ).val();
			slideAlignment = $( 'input[name=radio-slide-alignment]:checked', '#wpss-settings' ).val();

			if ( '0' === previewShape && $( '#preview_height_enc' ).hasClass( 'dp-none' ) ) {
				$( '#preview_height_enc' ).removeClass( 'dp-none' );
				settings.prev_is_sq = '0';
			} else if ( '1' === previewShape && ! $( '#preview_height_enc' ).hasClass( 'dp-none' ) ) {
				$( '#preview_height_enc' ).addClass( 'dp-none' );
				settings.prev_is_sq = '1';
				settings.prev_height = settings.prev_width;
				$( '.img-holder' ).css( 'height', settings.prev_height );
				$( '.img-holder' ).css( 'width', settings.prev_width );
			}

			if ( '0' === webviewShape && $( '#webview_height_enc' ).hasClass( 'dp-none' ) ) {
				$( '#webview_height_enc' ).removeClass( 'dp-none' );
				settings.web_is_sq = '0';
			} else if ( '1' === webviewShape && ! $( '#webview_height_enc' ).hasClass( 'dp-none' ) ) {
				$( '#webview_height_enc' ).addClass( 'dp-none' );
				settings.web_is_sq = '1';
			}

			if ( '1' === rangeEnable && $( '#slide-range-set' ).hasClass( 'dp-none' ) ) {
				$( '#slide-range-set' ).removeClass( 'dp-none' );
				settings.slide_limit = '1';
			} else if ( '0' === rangeEnable && ! $( '#slide-range-set' ).hasClass( 'dp-none' ) ) {
				$( '#slide-range-set' ).addClass( 'dp-none' );
				settings.slide_limit = '0';
			}

			if ( '0' === slideAlignment ) {
				settings.alignment = '0';
			} else if ( '1' === slideAlignment ) {
				settings.alignment = '1';
			} else if ( '2' === slideAlignment ) {
				settings.alignment = '2';
			}
		} );
		$( '#wpss-settings' ).trigger( 'change' );
	} );
}( jQuery ) );