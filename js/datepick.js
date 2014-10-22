(function($) {
	$(document).ready(function() {

		$input = $('.datepicker').pickadate({
			min: true,
          	close: '',
          	formatSubmit: 'yyyy-mm-dd'
		});

		$inputStart = $('.datepicker_start').pickadate();
		$inputEnd = $('.datepicker_end').pickadate();

		// Use the picker object directly.
		picker = $input.pickadate('picker');
		pickerStart = $inputStart.pickadate('picker');
		pickerEnd = $inputEnd.pickadate('picker');

		pickerStart.on({
			set: function(startTime) {
				startPick = startTime.select;
				startDate = pickerStart.get('select', 'yyyy-mm-dd');
				disabledDate = pickerStart.get('select');
				startDateDisplay = pickerStart.get('select', 'dd mmmm yyyy');

				if ( typeof startPick != 'undefined' ) {
				
					pickerEnd.set('disable', false, { muted: true });

					if ( ajax_object.calc_mode == 'days' ) {
						pickerEnd.set('disable', [
							{ from : true, to : [disabledDate.year, disabledDate.month, disabledDate.date - 1] }
						], { muted: true });
					} else {
						pickerEnd.set('disable', [
							{ from : true, to : [disabledDate.year, disabledDate.month, disabledDate.date] }
						], { muted: true });
					}

				}

				if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' )
					ebs_set_price(startPick, endPick, startDate, endDate, startDateDisplay, endDateDisplay);
				
			}
		});

		pickerEnd.on({
			set: function(endTime) {
				endPick = endTime.select;
				endDate = pickerEnd.get('select', 'yyyy-mm-dd');
				disabledEndDate = pickerEnd.get('select');
				endDateDisplay = pickerEnd.get('select', 'dd mmmm yyyy');

				if ( typeof endPick != 'undefined' ) {

					if ( ajax_object.calc_mode == 'days' ) {
						pickerStart.set('max', [disabledEndDate.year, disabledEndDate.month, disabledEndDate.date],
						{ muted: true });
					} else {
						pickerStart.set('max', [disabledEndDate.year, disabledEndDate.month, disabledEndDate.date -1],
						{ muted: true });
					}

				}

				if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' )
					ebs_set_price(startPick, endPick, startDate, endDate, startDateDisplay, endDateDisplay);
			}
		});

		ebs_set_price = function(startPick, endPick, startDate, endDate, startDateDisplay, endDateDisplay) {

			product_id = $('#variation_id').data('product_id');
			variation_id = $('#variation_id').val();

			var interval = parseInt( (endPick - startPick) / 86400000 );

			if ( interval == 0 ) {
				interval = 1;
			}

			var data = {
				action: 'add_new_price',
				product_id: product_id,
				variation_id: variation_id,
				days: interval,
				start: startDateDisplay,
				end: endDateDisplay,
				start_format: startDate,
				end_format: endDate
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