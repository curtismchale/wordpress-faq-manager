jQuery(document).ready(function ($) {

//********************************************************
// scrolling for FAQ Combo
//********************************************************

	$('div.faq_list li.faqlist_question').each(function() {
		// now clickin
		$(this).find('a').click (function (e) {
			// determine where I'm going
			var jump = $(this).prop('rel');

			// set some variables for later
			var title_size		= $('.faq_question').css('font-size');
			var title_height	= Math.floor(parseInt(title_size.replace('px', ''), 10) * 2.4);

			// now scroll to my FAQ
			$('html, body').animate({
				scrollTop: $('div.faq_content').find('div.single_faq[rel="'+ jump +'"]').offset().top - title_height
			}, 500);
			// this removes the hash in the URL for cleaner UI
			e.preventDefault();

		});
	});

//********************************************************
// what, you're still here? it's over. go home.
//********************************************************


});