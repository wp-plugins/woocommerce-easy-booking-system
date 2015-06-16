(function($) {
	$(document).ready(function() {

		$('input#_booking_option').change( function() {

			if ( $(this).is(':checked') ) {
				$('.show_if_bookable').show();
			} else {
				$('.show_if_bookable').hide();
				$('input.variable_is_bookable').attr('checked', false).change();
			}

			if ( $('.WCEB_tab').is('.active') ) {
				$( 'ul.wc-tabs li:visible' ).eq(0).find( 'a' ).click();
			}

		}).change();

		$('input#_manage_bookings').change( function() {
			if ( $(this).is(':checked') ) {
				$('.show_if_manage_bookings').show();
			} else {
				$('.show_if_manage_bookings').hide();
			}
		}).change();

		$( '#variable_product_options' ).on( 'change', 'input.variable_is_bookable', function () {
			$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_bookable' ).hide();

			if ( $( this ).is( ':checked' ) ) {
				$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_bookable' ).show();
			}
		}).change();

	});
})(jQuery);