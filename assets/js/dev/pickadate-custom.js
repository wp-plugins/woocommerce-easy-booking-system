(function($) {
	$(document).ready(function() {

		productType = ajax_object.product_type;

		if ( productType === 'variable' ) {

			var $pickerWrap = $('.wceb_picker_wrap');
				$pickerWrap.hide();

			$('body').on('found_variation', '.variations_form', function(e, variation) {
				if ( ! variation.is_purchasable || ! variation.is_in_stock || ! variation.variation_is_visible ) {
					$pickerWrap.hide();
				} else {
					$pickerWrap.slideDown( 200 );
				}
			});

			$('body').on('reset_image', '.variations_form', function(e, variation) {
				$pickerWrap.hide();
			});
		}

		firstDay = +ajax_object.first_date;

		if ( firstDay == 0 )
			firstDay = true;

		$input = $('.datepicker').pickadate({
			min: firstDay,
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

				if ( typeof startTime.clear != 'undefined' && startTime.clear == null ) {
					pickerEnd.set({
						disable: false,
						max: false,
						highlight: new Date()
					}, { muted: true });

					startPick = undefined;
					ebs_clear_booking_session();
				}

				if ( startTime.select && typeof startTime.select != 'undefined' ) {

					startPick = startTime.select;
					startDate = pickerStart.get('select', 'yyyy-mm-dd');
					var disabledDate = pickerStart.get('select');
					startDateDisplay = pickerStart.get('select', 'dd mmmm yyyy');

					// Hotfix for pickadate.js bug when selecting dates with keyboard, waiting for v4.0 fix
					if ( typeof startPick === 'object' ) {
						startPick = startTime.select.pick;
					}

					var minAndMax = ebs_get_min_and_max(disabledDate, 'plus');
					var min = minAndMax.min;
					var max = minAndMax.max;
				
					pickerEnd.set({
						disable: false,
						max: false,
						highlight: min
					}, { muted: true });

					if ( ajax_object.calc_mode == 'days' ) {

						pickerEnd.set({
							disable: [
								{ from : true, to : [disabledDate.year, disabledDate.month, disabledDate.date - 1] },
								{ from : [disabledDate.year, disabledDate.month, disabledDate.date], to : min }
							],
							max: max
						}, { muted: true });

					} else {

						pickerEnd.set({
							disable: [
								{ from : true, to : [disabledDate.year, disabledDate.month, disabledDate.date] },
								{ from : [disabledDate.year, disabledDate.month, disabledDate.date], to : min }
							],
							max: max
						}, { muted: true });

					}

					if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' )
						ebs_set_price(startPick, endPick, startDate, endDate, startDateDisplay, endDateDisplay);

				} else {
					return false;
				}
				
			}
		});

		pickerEnd.on({
			set: function(endTime) {

				if ( typeof endTime.clear != 'undefined' && endTime.clear == null ) {
					pickerStart.set({
						disable: false,
						max: false,
						highlight: new Date()
					}, { muted: true });

					endPick = undefined;
					ebs_clear_booking_session();
				}

				if ( endTime.select && typeof endTime.select != 'undefined' ) {

					endPick = endTime.select;
					endDate = pickerEnd.get('select', 'yyyy-mm-dd');
					var disabledEndDate = pickerEnd.get('select');
					endDateDisplay = pickerEnd.get('select', 'dd mmmm yyyy');

					// Hotfix for pickadate.js bug when selecting dates with keyboard, waiting for v4.0 fix
					if ( typeof endPick === 'object' ) {
						endPick = endTime.select.pick;
					}

					var minAndMax = ebs_get_min_and_max(disabledEndDate, 'minus');
					var min = minAndMax.min;
					var max = minAndMax.max;

					pickerStart.set({
						disable: false,
						highlight: min
					}, { muted: true });

					if ( ajax_object.calc_mode == 'days' ) {

						pickerStart.set({
							disable: [ { from : true, to : min } ],
							max: max
						}, { muted: true });

					} else {

						pickerStart.set({
							disable: [ { from : true, to : min } ],
							max: max
						}, { muted: true });

					}

					if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' )
						ebs_set_price(startPick, endPick, startDate, endDate, startDateDisplay, endDateDisplay);

				} else {
					return false;
				}
				
			}
		});

		ebs_clear_booking_session = function() {

			var data = {
				action: 'clear_booking_session'
			};

			$.post(ajax_object.ajax_url, data);

		}

		ebs_get_min_and_max = function(disabledDate, operator) {

			var selectedMinDate = new Date( disabledDate.year, disabledDate.month, disabledDate.date ); // Selected date
			var selectedMaxDate = new Date( disabledDate.year, disabledDate.month, disabledDate.date ); // Selected date

			var minimumDuration = parseInt(ajax_object.min); // Minimum days
			var maximumDuration = parseInt(ajax_object.max); // Maximum days

			var calc_mode = ajax_object.calc_mode; // Days or Nights

			if ( operator == 'plus' ) { // Start date calendar

				if ( minimumDuration == 0 ) {
					minimumDuration -= 1;
				} else {
					minimumDuration -= 2;

					if ( calc_mode == 'nights' )
						minimumDuration += 1;
				}

				if ( maximumDuration == 0 )
					maximumDuration -= 1;

				if ( calc_mode == 'days' )
					maximumDuration -= 1;

			} else { // End date calendar

				if ( maximumDuration == 0 ) {

					selectedMinDate = new Date();

					if ( minimumDuration != 0 ) {
						maximumDuration = minimumDuration;
						maximumDuration -= 1;
					}

					minimumDuration = 1;

					if ( calc_mode == 'nights' )
						maximumDuration += 1;

				} else {

					temp = minimumDuration;
					minimumDuration = maximumDuration;
					maximumDuration = temp;

					if ( temp != 0 )
						maximumDuration -= 1;

					if ( calc_mode == 'nights' ) {
						minimumDuration += 1;
						maximumDuration += 1;
					}

				}

			}

			if ( operator == 'plus' ) {
				selectedMinDate.setDate(selectedMinDate.getDate() + minimumDuration);
				selectedMaxDate.setDate(selectedMaxDate.getDate() + maximumDuration);
			} else {
				selectedMinDate.setDate(selectedMinDate.getDate() - minimumDuration);
				selectedMaxDate.setDate(selectedMaxDate.getDate() - maximumDuration);
			}

			if ( maximumDuration < 0 )
				selectedMaxDate = false;

			var minAndMax = {};
     		minAndMax['min'] = selectedMinDate;
     		minAndMax['max'] = selectedMaxDate;
			
			return minAndMax;

		}

		ebs_set_price = function(startPick, endPick, startDate, endDate, startDateDisplay, endDateDisplay) {

			if ( productType === 'variable' ) {
				product_id = $('input[name=product_id]').val();
			} else {
				product_id = $('input[name=add-to-cart]').val();
			}
			
			variation_id = $('.variations_form').find('input.variation_id').val();

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