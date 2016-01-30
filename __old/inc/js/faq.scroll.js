jQuery(document).ready(function ($) {

//********************************************************
// scrolling for FAQ Combo
//********************************************************

	$('div.faq-list li.faqlist-question').each(function() {
		// now clickin
		$(this).find('a').click (function (e) {
			// determine where I'm going
			var jump = $(this).prop('rel');

			// set some variables for later
			var title_size		= $('.faq-question').css('font-size');
			var title_height	= Math.floor(parseInt(title_size.replace('px', ''), 10) * 2.4);

			// now scroll to my FAQ
			$('html, body').animate({
				scrollTop: $('div.faq-content').find('div.single-faq[rel="'+ jump +'"]').offset().top - title_height
			}, 500);
			// this removes the hash in the URL for cleaner UI
			e.preventDefault();

		});
	});

//********************************************************
// optional return to top
//********************************************************

	$('div.faq-content div.single-faq').each(function() {
		// now clickin
		$(this).find('p.scroll-back a').click (function (e) {

			// now scroll to my FAQ
			$('html, body').animate({
				scrollTop: $('div#faq-block').offset().top -55
			}, 500);
			// this removes the hash in the URL for cleaner UI
			e.preventDefault();

		});
	});

//********************************************************
// what, you're still here? it's over. go home.
//********************************************************


});