jQuery(document).ready(function ($) {

//********************************************************
// expand / collapse
//********************************************************

	function boom_goes_the_dynamite() {

		jQuery('div.expand_faq').each(function() {
			jQuery(this).find('div.faq_answer').hide();
		});

		jQuery('.expand_title').click (function () {
			jQuery(this).next('div.faq_answer').slideToggle(200);
		});

	}

//********************************************************
// pagination function
//********************************************************

	function show_me_some_more() {
		jQuery('p.faq_nav a').live('click', function(e){
			e.preventDefault();
			var link = jQuery(this).attr('href');
			jQuery('div#faq_block').fadeOut(500).load(link + ' div.faq_list', function() {
				jQuery('div#faq_block').fadeIn(500);
				// reset the hide
				boom_goes_the_dynamite();
			});
			jQuery('body').scrollTop(1000);
		});

	}

//********************************************************
// call specific functions
//********************************************************

	boom_goes_the_dynamite();
	show_me_some_more();

//********************************************************
// what, you're still here? it's over. go home.
//********************************************************


});
