(function($) {
	$(document).ready(function() {

		$('input#_booking_option').change(function() {

			if ( $(this).is(':checked') ) {
				$('.show_if_bookable').show();
			} else {
				$('.show_if_bookable').hide();
			}

			$( 'ul.wc-tabs li:visible' ).eq(0).find( 'a' ).click();

		});

		$( '#variable_product_options' ).on( 'change', 'input.variable_is_bookable', function () {
			$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_bookable' ).hide();

			if ( $( this ).is( ':checked' ) ) {
				$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_bookable' ).show();
			}
		});

		$( 'input.variable_is_bookable' ).change();

	});
})(jQuery);