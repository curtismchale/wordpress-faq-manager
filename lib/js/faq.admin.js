
//*********************************************************************************************
// start the engine
//*********************************************************************************************
jQuery(document).ready( function($) {

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
	var sortList;
	var sortNonce;

//*********************************************************************************************
// handle the drag and drop sorting
//*********************************************************************************************
	$( '.faq-sort-list' ).divExists( function() {

		// Pull our sort list wrapper and nonce.
		sortList    = $( 'ul#faq-sort-type-list' );
		sortNonce   = $( 'input#wpfaq_sort_nonce' ).val();

		sortList.sortable({
			update: function( event, ui ) {

				// Remove any existing messages.
				$( '.faq-admin-sort-wrap' ).find( '.wpfaq-message' ).remove();

				// Show the animated loading gif while waiting.
				$( '.faq-sort-spinner' ).addClass( 'is-active' );

				opts = {
					url:    ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
					type:   'POST',
					async:  true,
					cache:  false,
					nonce:  sortNonce,
					dataType: 'json',
					data:{
						action: 'save_faq_sort', // Tell WordPress how to handle this ajax request
						order: sortList.sortable( 'toArray' ).toString() // Passes ID's of list items in 1,3,2 format
					},
					success: function( response ) {
						$( '.faq-sort-spinner' ).removeClass( 'is-active' ); // Hide the loading animation
						$( '.faq-admin-sort-wrap h1:first' ).after( faqAdmin.updateText );
						return;
					},
					error: function( xhr,textStatus,e ) {
						$( '.faq-sort-spinner' ).removeClass( 'is-active' ); // Hide the loading animation
						$( '.faq-admin-sort-wrap h1:first' ).after( faqAdmin.errorText );
						return;
					}
				};
				$.ajax( opts );
			}
		});
	});

//*********************************************************************************************
// Handle the notice dismissal.
//*********************************************************************************************
	$( '.faq-admin-sort-wrap' ).on( 'click', '.notice-dismiss', function() {
		$( '.faq-admin-sort-wrap' ).find( '.wpfaq-message' ).remove();
	});

//*********************************************************************************************
// we are done here. go home
//*********************************************************************************************
});
