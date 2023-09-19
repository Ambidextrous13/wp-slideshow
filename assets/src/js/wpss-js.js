( function ( $ ) {
	$( document ).ready( function () {

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

		/**
		 * Activates the loading Animation.
		 */
		function loaderPlease() {
			if ( 0 === $( '#wpss-loading' ).length ) {
				let loaderHtml = '<div id="wpss-loading"> <div class="lds-spinner">';
				let i = 0;
				for ( i; 12 > i; i++ ) {
					loaderHtml += '<div></div>';
				}
				$( loaderHtml ).insertBefore( '#accordion' );
			}
		}

		/**
		 * deactivates the loading Animation.
		 */
		function noMoreLoader() {
			while ( 0 !== $( '#wpss-loading' ).length ) {
				$( '#wpss-loading' ).remove();
			}
		}
		/**
		 * Displays an error message based on the 'alert_string' key's value in the response JSON.
		 *
		 * @param {JSON}    response The response JSON received from Ajax.
		 * @param {boolean} succeed  True for a green alert, false for a red alert.
		 */
		function wpssAlert ( response, succeed ) {
			if( typeof '' === typeof response ){
				response = JSON.parse( response );
			}
			if ( response.alert_string || response.data?.alert_string ) {
				let alert = response.alert_string ? response.alert_string : response.data.alert_string;
				alert = succeed ? alert : alert + '.  Reloading...';
				$( '#wpss-main-alert-text' ).html( alert );
				if ( succeed ) {
					$( '#wpss-main-alert' ).addClass( 'wpss-alerts-green' );
				} else {
					$( '#wpss-main-alert' ).addClass( 'wpss-alerts-red' );
				}
				if ( $( '#wpss-main-alert' ).hasClass( 'dp-none' ) ) {
					$( '#wpss-main-alert' ).removeClass( 'dp-none' );
				}
				setTimeout( () => {
					if ( $( '#wpss-main-alert' ).hasClass( 'wpss-alerts-green' ) ) {
						$( '#wpss-main-alert' ).removeClass( 'wpss-alerts-green' );
					} else if ( $( '#wpss-main-alert' ).hasClass( 'wpss-alerts-red' ) ) {
						$( '#wpss-main-alert' ).removeClass( 'wpss-alerts-red' );
					}
					$( '#wpss-main-alert' ).addClass( 'dp-none' );
				}, 2500 );
			}
		}
		// styles
		$( 'input[type="radio"]' ).checkboxradio();
		$( document ).tooltip();
		$( '#upload' ).button( {
			icon: 'ui-icon-arrowthickstop-1-n',
			iconPosition: 'end',
		} );

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
		$( '#upload' ).on( 'click', function () {
			loaderPlease();
		} );

		// Globals
		let slidesOrder = [];
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

		// delete button
		$( '.slide-delete' ).each( function() {
			$( this ).on( 'click', function() {
				const parent = $( this ).parent();
				$( parent ).css( 'border', '2px solid red' );
				$( '#slide-delete-buttons' ).append( '<button id="slide-confirm-delete" class="slide-delete-button">Confirm</button>' );

				$( '#slide-confirm-delete' ).on( 'click', function() {
					parent.remove();
					$( '#delete-dialogue' ).addClass( 'dp-none' );
					$( '#slide-confirm-delete' ).remove();
					$( parent ).css( 'border', 'none' );
					$( '#sortable' ).trigger( 'sortupdate' );
					if ( parseInt( ajaxOP.slide_end ) >= slidesOrder.length ) {
						$( '#slider-range' ).slider( 'destroy' );
						sliderBuilder( '#slider-range', true, [ parseInt( ajaxOP.slide_start ), slidesOrder.length - 1 ], slidesOrder.length - 1, 1, slideRangeCallback );
						$( '#slide-range-end' ).val( slidesOrder.length - 1 );
					} else {
						$( '#slider-range' ).slider( 'destroy' );
						sliderBuilder( '#slider-range', true, [ parseInt( ajaxOP.slide_start ), parseInt( ajaxOP.slide_end ) ], slidesOrder.length - 1, 1, slideRangeCallback );
					}
				} );

				$( '#slide-cancel-delete' ).on( 'click', function() {
					$( '#slide-confirm-delete' ).remove();
					$( '#delete-dialogue' ).addClass( 'dp-none' );
					$( parent ).css( 'border', 'none' );
				} );
				$( '#delete-dialogue' ).removeClass( 'dp-none' );
			} );
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

		// sortable settings
		$( '#sortable' ).sortable( {
			revert: true,
			tolerance: 'pointer',
			cursor: 'grab',
		} );
		$( '#sortable' ).disableSelection();
		$( '#sortable' ).on( 'sortupdate', function() {
			slidesOrder = $( '#sortable' ).sortable(
				'toArray',
				{
					key: 'sort',
					attribute: 'data',
				}
			);
			slidesOrder.push( '-1' );
		} );

		// accordion settings
		$( '#accordion' ).accordion( {
			heightStyle: 'content',
			collapsible: true,
			active: 1,
			icons: { header: 'ui-icon-plus', activeHeader: 'ui-icon-minus' },
		} );

		// sliders
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

		//Ajax request
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
			$( '#preview-width' ).val( ajaxOP.prev_width + 'px' );
			$( '#preview-height' ).val( ajaxOP.prev_height + 'px' );
			$( '#webview-width' ).val( ajaxOP.web_width + 'px' );
			$( '#webview-height' ).val( ajaxOP.web_height + 'px' );
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

		// Callbacks for Sliders

		/**
		 * callback for preview width slider.
		 * @param {object} ui jQuery returned object.
		 */
		const previewWidthCallback = function ( ui ) {
			$( '.img-holder' ).css( 'width', ui.value );
			if ( '1' === settings.prev_is_sq ) {
				$( '.img-holder' ).css( 'height', ui.value );
				settings.prev_height = ui.value.toString();
			}
			$( '#preview-width' ).val( ui.value + 'px' );
			settings.prev_width = ui.value.toString();
		};

		/**
		 * callback for preview height slider.
		 * @param {object} ui jQuery returned object.
		 */
		const previewHeightCallback = function ( ui ) {
			$( '.img-holder' ).css( 'height', ui.value );
			$( '#preview-height' ).val( ui.value + 'px' );
			settings.prev_height = ui.value.toString();
		};
		sliderBuilder( '#slider-width', 'min', ajaxOP.prev_width, ajaxOP.prev_w_max, 35, previewWidthCallback );
		sliderBuilder( '#slider-height', 'min', ajaxOP.prev_height, ajaxOP.prev_h_max, 35, previewHeightCallback );

		/**
		 * callback for web view width slider.
		 * @param {object} ui jQuery returned object.
		 */
		const previewWidthWVCallback = function ( ui ) {
			$( '#webview-width' ).val( ui.value + 'px' );
			settings.web_width = ui.value.toString();
		};

		/**
		 * callback for web view height slider.
		 * @param {object} ui jQuery returned object.
		 */
		const previewHeightWVCallback = function ( ui ) {
			$( '#webview-height' ).val( ui.value + 'px' );
			settings.web_height = ui.value.toString();
		};
		sliderBuilder( '#slider-width-wv', 'min', ajaxOP.web_width, ajaxOP.web_w_max, 80, previewWidthWVCallback );
		sliderBuilder( '#slider-height-wv', 'min', ajaxOP.web_height, ajaxOP.web_h_max, 80, previewHeightWVCallback );

		/**
		 * callback for slide' range slider.
		 * @param {object} ui jQuery returned object.
		 */
		const slideRangeCallback = function ( ui ) {
			$( '#slide-range-start' ).val( ui.values[ 0 ] );
			$( '#slide-range-end' ).val( ui.values[ 1 ] );
			settings.slide_start = ui.values[ 0 ].toString();
			settings.slide_end = ui.values[ 1 ].toString();
		};

		sliderBuilder( '#slider-range', true, [ parseInt( ajaxOP.slide_start ), parseInt( ajaxOP.slide_end ) ], slidesOrder.length - 1, 1, slideRangeCallback );

		// Settings change event listener
		let previewShape = settings.prev_is_sq;
		let webviewShape = settings.web_is_sq;
		let rangeEnable = settings.slide_limit;
		let slideAlignment = settings.alignment;

		$( '#wpss-settings' ).on( 'change', function() {
			previewShape = $( 'input[name=radio-shape]:checked', '#wpss-settings' ).val();
			webviewShape = $( 'input[name=radio-shape-wv]:checked', '#wpss-settings' ).val();
			rangeEnable = $( 'input[name=radio-slide-limit]:checked', '#wpss-settings' ).val();
			slideAlignment = $( 'input[name=radio-slide-alignment]:checked', '#wpss-settings' ).val();

			if ( '0' === previewShape && $( '#preview-height-enc' ).hasClass( 'dp-none' ) ) {
				$( '#preview-height-enc' ).removeClass( 'dp-none' );
				settings.prev_is_sq = '0';
			} else if ( '1' === previewShape && ! $( '#preview-height-enc' ).hasClass( 'dp-none' ) ) {
				$( '#preview-height-enc' ).addClass( 'dp-none' );
				settings.prev_is_sq = '1';
				settings.prev_height = settings.prev_width;
				$( '.img-holder' ).css( 'height', settings.prev_height );
				$( '.img-holder' ).css( 'width', settings.prev_width );
			}

			if ( '0' === webviewShape && $( '#webview-height-enc' ).hasClass( 'dp-none' ) ) {
				$( '#webview-height-enc' ).removeClass( 'dp-none' );
				settings.web_is_sq = '0';
			} else if ( '1' === webviewShape && ! $( '#webview-height-enc' ).hasClass( 'dp-none' ) ) {
				$( '#webview-height-enc' ).addClass( 'dp-none' );
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

		// Settings Saver.
		$( '#save-settings' ).on( 'click', function() {
			const settingSaverPayload = {
				action: 'wpss_plugin_settings_setter',
				ajaxNonce: ajaxNonce,
				wpss_settings: settings,
			};
			doAjax( settingSaverPayload );
		} );

		// Reset Buttons.
		$( '#wpss-reset,#reset-settings' ).on( 'click', function() {
			location.reload();

		} );
	} );
}( jQuery ) );
