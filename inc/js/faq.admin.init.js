jQuery(document).ready(function($) {

//********************************************************
// change icon ID
//********************************************************

	$('div#icon-edit').attr('id','icon-faq-admin');


//********************************************************
// trigger checkbox on label
//********************************************************

	$('div.faq_form_options label[rel="checkbox"]').each(function() {
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
					$('ul#custom-type-list').after('<div id="message" class="updated below-h2"><p>Menu order saved</p></div>');
					return;
				},
				error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
					alert('There was an error saving the updates');
					$('#loading-animation').hide(); // Hide the loading animation
					return;
				}
			};
			$.ajax(opts);
		}
	});

});