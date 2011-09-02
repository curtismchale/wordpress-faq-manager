jQuery(document).ready(function(){

	function boom_goes_the_dynamite() {
    jQuery(".faq_answer").hide();
        jQuery(".faq_question").click(function () {
            jQuery(this).next(".faq_answer").slideToggle(200);
        });
	}
/*
	function boom_goes_the_dynamite() {
	jQuery(".faq_answer").hide();
		jQuery(".faq_question").click(function () {
			jQuery(".faq_answer").not(this).hide().parent().removeClass("current_faq");
			jQuery(this).stop().next(".faq_answer").slideToggle(400).parent().addClass("current_faq");
		});
	}
*/	
	boom_goes_the_dynamite();
	// ajax pagination
    jQuery('p.faq_nav a').live('click', function(e){
        e.preventDefault();
        var link = jQuery(this).attr('href');
        jQuery('#faq_block').fadeOut(500).load(link + ' .faq_list', function(){ jQuery('#faq_block').fadeIn(500);
		jQuery("body").scrollTop(1000);
		boom_goes_the_dynamite(); });
    });

});