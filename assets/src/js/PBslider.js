( function ( $ ) {
	$( document ).ready( function() {

		const imgs = $( 'li.pbSlider' ),
			totalImages = imgs.length,
			delay = 5000,
			firstImage = imgs.eq( 0 ).find( 'img' );
		let slideNo = 0,
			sliderInterval;

		firstImage.on( 'load', function() {
			const imgHeight = imgs.eq( slideNo ).find( 'img' ).height();
			const imgWidth = $( 'img', '#wpss-slideshow' )[ 0 ].width;
			$( '.pbSliderContainer' ).css( 'height', imgHeight + 'px' );
			$( '.pbSliderContainer' ).css( 'width', imgWidth + 'px' );
		} ).each( function() {
			if ( this.complete ) {
				$( this ).trigger( 'load' );
			}
		} );

		/**
		 * Starts the slide show.
		 */
		function startSlider() {
			sliderInterval = setInterval( changeSlide, delay );
		}

		/**
		 * Stops the slide show.
		 */
		function stopSlider() {
			clearInterval( sliderInterval );
		}

		startSlider();

		$( window ).on( 'resize', function() {
			const imgHeight = imgs.eq( slideNo ).height();
			$( '.pbSliderContainer' ).css( 'height', imgHeight + 'px' );
		} );

		$( window ).on( 'blur', function() {
			stopSlider();
		} );

		$( window ).on( 'focus', function() {
			startSlider();
		} );

		/**
		 * Changes the slides during on time.
		 */
		function changeSlide() {
			imgs.eq( slideNo ).slideUp( 2500 );
			slideNo++;
			if ( slideNo === totalImages ) {
				slideNo = 0;
			}
			imgs.eq( slideNo ).fadeIn( 3500 );
		}
	} );
}( jQuery ) );
