$( function() {
			
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
} );