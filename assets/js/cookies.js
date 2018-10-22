(function($) {
	cookie_section = $('.cookies-section');

	//Accept Cookies
	$('.accept-button, .close-button').on('click', function(e) {
		e.preventDefault();

		$.cookie("accept-cookies", true, { expire: 365 });
		cookie_section.hide(800);
	});

}) (jQuery)
