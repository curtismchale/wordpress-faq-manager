jQuery(document).ready(function(){
	jQuery('p.faq_nav a').live('click', function(e){
		e.preventDefault();
		var link = jQuery(this).attr('href');
		jQuery('#faq_block').fadeOut(500).load(link + ' .faq_list', function(){ jQuery('#faq_block').fadeIn(500); });
		jQuery("body").scrollTop(1000);
	});
});