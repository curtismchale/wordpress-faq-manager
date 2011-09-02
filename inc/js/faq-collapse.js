
jQuery(document).ready(function () {
	jQuery(".faq_answer").hide();
	jQuery(".faq_question").live ('click', function () {
    	jQuery(this).next(".faq_answer").slideToggle(200);
	});
});

/*
jQuery(document).ready(function () {
	jQuery(".faq_answer").hide();
	jQuery(".faq_question").click(function () {
		jQuery(".faq_answer").not(this).hide().parent().removeClass("current_faq");
		jQuery(this).stop().next(".faq_answer").slideToggle(400).parent().addClass("current_faq");
	});
});

*/