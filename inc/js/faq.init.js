jQuery(document).ready(function($) {

//********************************************************
// expand / collapse
//********************************************************

	function boom_goes_the_dynamite() {

		var speed_v = jQuery('div.faq-list').data('speed');
		speed		= (speed_v)		? speed_v		: 200;

		jQuery('div.expand-faq').each(function() {
			jQuery(this).find('div.faq-answer').hide();
		});

		jQuery('.expand-title').click(function (event) {
			var faq = jQuery(this).attr('id');
			jQuery('div.faq-list').find('div.faq-answer[rel="' + faq + '"]').slideToggle(speed);
			jQuery('div.faq-list').find('div.faq-answer').not('[rel="' + faq + '"]').hide(speed);
		});

	}

//********************************************************
// pagination function
//********************************************************

	function show_me_some_more() {
		jQuery('p.faq-nav a').live('click', function(e){
			e.preventDefault();
			var link = jQuery(this).attr('href');
			jQuery('div#faq-block').fadeOut(500).load(link + ' div.faq-list', function() {
				jQuery('div#faq-block').fadeIn(500);
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
