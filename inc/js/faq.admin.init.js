jQuery(document).ready(function($) {

//********************************************************
// change icon ID
//********************************************************

	$('div#icon-edit').attr('id','icon-faq-admin');

//********************************************************
// set default choice on dropdown
//********************************************************

	var htype = $('div.faq-form-options select#faq_htype').hasClass('default');

	if (htype === true)
		$('div.faq-form-options select.faq_htype option[value="h3"]').prop('selected',true);

//********************************************************
// remove caps and other junk from slug
//********************************************************

	$('input#faq_arch').keyup(function() {
		this.value = this.value.replace(/\d+/g, '');
	});


//********************************************************
// show / hide some options on load
//********************************************************

	$('input#faq_expand').each(function() { // for initial load
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('div.secondary-option').show();

		if (checkval === false)
			$('div.secondary-option').hide();

	});

	$('input#faq_exlink').each(function() { // for initial load
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('p.extext').show();

		if (checkval === false)
			$('p.extext').hide();

	});

	$('input#faq_scroll').each(function() { // for initial load
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('p.scrolltop').show();

		if (checkval === false)
			$('p.scrolltop').hide();

	});

	$('input#faq_redirect').each(function() { // for initial load
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('p.redirectid').show();

		if (checkval === false)
			$('p.redirectid').hide();

	});

//********************************************************
// show / hide some options on change
//********************************************************

	$('input#faq_expand').change( function() { // for value change
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('div.secondary-option').slideToggle(200);

		if (checkval === false)
			$('div.secondary-option').hide(200);

	});

	$('input#faq_exlink').change( function() { // for value change
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('p.extext').slideToggle(200);

		if (checkval === false)
			$('p.extext').hide(200);

	});

	$('input#faq_scroll').change( function() { // for value change
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('p.scrolltop').slideToggle(200);

		if (checkval === false) {
			$('p.scrolltop').hide(200);
			$('p.scrolltop select').val('none');
		}

	});

	$('input#faq_redirect').change( function() { // for value change
		var checkval = $(this).is(':checked');

		if (checkval === true)
			$('p.redirectid').slideToggle(200);

		if (checkval === false) {
			$('p.redirectid').hide(200);
			$('p.redirectid select').val('none');
		}

	});

//********************************************************
// remove non-numeric from speed
//********************************************************

	$('input#faq_exspeed').keyup(function () {
		var numcheck = $.isNumeric($(this).val() );

		if(numcheck === false) {
			this.value = this.value.replace(/[^0-9\.]/g,'');
			$(this).next('span.warning').remove();
			$(this).next('label').after('<span class="warning">No non-numeric characters allowed</span>');
		}

		if(numcheck === true)
			$(this).next('span.warning').remove();
	});

//********************************************************
// trigger checkbox on label
//********************************************************

	$('div.faq-form-options label[rel="checkbox"]').each(function() {
		$(this).click(function() {

			var check_me = $(this).prev('input');
			var is_check = $(check_me).is(':checked');

			if (is_check === false) {
				$(check_me).prop('checked', true);
				$(check_me).trigger('change');
			}

			if (is_check === true) {
				$(check_me).prop('checked', false);
				$(check_me).trigger('change');
			}

		});
	});

//********************************************************
// enable drag and drop sorting
//********************************************************

	$('div#faq-admin-sort').each(function() {

		var sortList = $('ul#custom-type-list');

		sortList.sortable({
			update: function(event, ui) {
				$('#loading-animation').show(); // Show the animate loading gif while waiting

				opts = {
					url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
					type: 'POST',
					async: true,
					cache: false,
					dataType: 'json',
					data:{
						action: 'save_sort', // Tell WordPress how to handle this ajax request
						order: sortList.sortable('toArray').toString() // Passes ID's of list items in	1,3,2 format
					},
					success: function(response) {
						$('div#message').remove();
						$('#loading-animation').hide(); // Hide the loading animation
						$('div#faq-admin-sort h2:first').after('<div id="message" class="updated below-h2"><p>FAQ sort order has been saved</p></div>');
						return;
					},
					error: function(xhr,textStatus,e) {
						$('#loading-animation').hide(); // Hide the loading animation
						$('div#faq-admin-sort h2:first').after('<div id="message" class="error below-h2"><p>There was an error saving the sort order. Please try again later.</p></div>');
						return;
					}
				};
				$.ajax(opts);
			}
		});

	});

//********************************************************
// that's all folks. we're done here
//********************************************************

});