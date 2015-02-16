(function($) {
	$(document).ready(function() {

		var bookingOption = options.booking_option;

		if ( bookingOption != 'yes' )
			$('.show_if_bookable').hide();

		$('input#_booking_option').change(function() {

			if ( $(this).is(':checked') ) {
				$('.show_if_bookable').show();
			} else {
				$('.show_if_bookable').hide();
			}

			$( 'ul.wc-tabs li:visible' ).eq(0).find( 'a' ).click();

		});

	});
})(jQuery);