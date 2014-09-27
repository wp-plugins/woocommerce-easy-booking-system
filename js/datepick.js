(function($) {
	$(document).ready(function() {

		$input = $('.datepicker').pickadate({
			min: true,
          	close: ''
		});

		$inputStart = $('.datepicker_start').pickadate();
		$inputEnd = $('.datepicker_end').pickadate();

		// Use the picker object directly.
		picker = $input.pickadate('picker');
		pickerStart = $inputStart.pickadate('picker');
		pickerEnd = $inputEnd.pickadate('picker');

		pickerStart.on({
			set: function(startTime) {
				start = startTime.select;
				startDate = pickerStart.get('select', 'dd mmmm yyyy');

				if ( typeof start != 'undefined' && typeof end != 'undefined' )
					ebs_set_price(start, end, startDate, endDate);
			}
		});

		pickerEnd.on({
			set: function(endTime) {
				end = endTime.select;
				endDate = pickerEnd.get('select', 'dd mmmm yyyy');

				if ( typeof start != 'undefined' && typeof end != 'undefined' )
					ebs_set_price(start, end, startDate, endDate);
			}
		});

		ebs_set_price = function(start, end, startDate, endDate) {

			product_id = $('#variation_id').data('product_id');
			variation_id = $('#variation_id').val();

			var interval = parseInt( (end - start) / 86400000 );

			if ( interval == 0 ) {
				interval = 1;
			}

			var data = {
				action: 'add_new_price',
				product_id: product_id,
				variation_id: variation_id,
				days: interval,
				start: startDate,
				end: endDate
			};

			var this_page = window.location.toString();

			$('form.cart').fadeTo('400', '0.6').block({message: null, overlayCSS: {background: 'transparent url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6 } } );

			$.post(ajax_object.ajax_url, data, function( response ) {

				$('.woocommerce-error, .woocommerce-message').remove();
				fragments = response.fragments;
				errors = response.errors;

				if ( errors ) {
					$.each(errors, function(key, value) {
						$(key).replaceWith(value);
					});
				}

				if ( fragments ) {
					$.each(fragments, function(key, value) {
						$(key).replaceWith(value);
					});
				}

				// Unblock
				$('form.cart').stop(true).css('opacity', '1').unblock();
			
			});
		}

		// Clear picker when variation changes
		$( '.variations_form' ).on( 'change', '.variations select', function() {
			pickerStart.clear();
			pickerEnd.clear();
		});

	});
})(jQuery);
