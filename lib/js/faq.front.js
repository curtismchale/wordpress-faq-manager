//*********************************************************************************************
// hide all my FAQ answers on load.
//*********************************************************************************************
function hideAllAnswers() {

	// Loop through each FAQ block.
	jQuery( '.expand-faq' ).each( function() {

		// Hide the answer.
		jQuery( this ).find( '.faq-answer' ).hide();
	});

	// And finish up.
	return;
}

//*********************************************************************************************
// handle all the click functions.
//*********************************************************************************************
function clickExpandAnswer( expandSpeed ) {

	jQuery( '.expand-faq-list' ).on( 'click', '.expand-title', function() {

		// Get the FAQ that I am clicking.
		expandFAQ   = jQuery( this ).attr( 'id' );

		// First check if it's already opened.
		if ( jQuery( '.expand-faq-list' ).find( '.faq-answer[rel="' + expandFAQ + '"]' ).hasClass( 'faq-answer-expanded' ) ) {

			// Remove the class and hide it.
			jQuery( '.expand-faq-list' ).find( '.faq-answer[rel="' + expandFAQ + '"]' ).removeClass( 'faq-answer-expanded' ).hide( expandSpeed );

			// And finish.
			return;
		}

		// Expand the one I clicked.
		jQuery( '.expand-faq-list' ).find( '.faq-answer[rel="' + expandFAQ + '"]' ).addClass( 'faq-answer-expanded' ).slideToggle( expandSpeed );

		// Hide the rest.
		jQuery( '.expand-faq-list' ).find( '.faq-answer' ).not( '[rel="' + expandFAQ + '"]' ).removeClass( 'faq-answer-expanded' ).hide( expandSpeed );
	});

	// And finish up.
	return;
}

//*********************************************************************************************
// start the engine
//*********************************************************************************************
jQuery(document).ready(function($) {

//*********************************************************************************************
// quick helper to check for an existance of an element
//*********************************************************************************************
	$.fn.divExists = function(callback) {
		// slice some args
		var args = [].slice.call( arguments, 1 );
		// check for length
		if ( this.length ) {
			callback.call( this, args );
		}
		// return it
		return this;
	};

//*********************************************************************************************
// set some vars
//*********************************************************************************************
	var expandSpeed = 200;
	var expandFAQ;
	var navFAQlink;
	var comboJump;
	var comboSize;
	var comboHeight;

//*********************************************************************************************
// expand / collapse
//*********************************************************************************************
	$( '.expand-faq-list' ).divExists( function() {

		// First hide each one.
		hideAllAnswers();

		// Get my speed value.
		expandSpeed = $( '.expand-faq-list' ).data( 'speed' );

		// Now handle the click expanding.
		clickExpandAnswer( expandSpeed );
	});

//*********************************************************************************************
// pagination function
//*********************************************************************************************
	$( '.faq-nav' ).divExists( function() {

		// Handle my clickin'
		$( '.faq-list' ).on( 'click', '.page-numbers', function( event ) {

			// Keep the URL clean.
			event.preventDefault();

			// Get the URL I am going to.
			navFAQlink  = $( this ).attr( 'href' );

			// Get my speed value.
			expandSpeed = $( '.expand-faq-list' ).data( 'speed' );

			// Handle the fade out / in with the new content.
			$( '#faq-block' ).fadeOut( 500 ).load( navFAQlink + ' .faq-list', function() {

				$( '#faq-block' ).fadeIn( 500 );

					// reset the hide
					hideAllAnswers();

					// Now handle the click expanding.
					clickExpandAnswer( expandSpeed );
			});

			// And scroll back to the top.
			$( 'body' ).scrollTop( 1000 );
		});
	});

//*********************************************************************************************
// scrolling for FAQ combo list.
//*********************************************************************************************
	$( '.faq-block-combo-wrap-scroll' ).divExists( function() {

		// Handle the actual clicking.
		$( '.faq-list' ).on( 'click', '.faqlist-question a', function( event ) {

			// Keep the URL clean.
			event.preventDefault();

			// Determine where I'm going.
			comboJump   = $( this ).prop( 'rel' );

			// Set some variables for later.
			comboSize   = $( '.faq-question' ).css( 'font-size' );
			comboHeight = Math.floor( parseInt( comboSize.replace( 'px', '' ), 10 ) * 2.4 );

			// Now scroll to my FAQ.
			$( 'html, body' ).animate({
				scrollTop: $( 'div.faq-content' ).find( 'div.single-faq[rel="' + comboJump + '"]').offset().top - comboHeight
			}, 500 );
		});
	});

//*********************************************************************************************
// return to top for FAQ combo list.
//*********************************************************************************************
	$( '.faq-block-combo-wrap' ).divExists( function() {

		// Handle the return to top.
		$( '.faq-answer' ).on( 'click', '.scroll-back a', function( event ) {

			// Keep the URL clean.
			event.preventDefault();

			// And scroll back to the list.
			$( 'html, body' ).animate({
				scrollTop: $( '.faq-block-combo-wrap' ).offset().top - 55
			}, 500 );
		});
	});

//*********************************************************************************************
// we are done here. go home
//*********************************************************************************************
});
