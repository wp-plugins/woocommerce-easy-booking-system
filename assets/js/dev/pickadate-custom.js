(function($) {
	$(document).ready(function() {

		$input = $('.datepicker').pickadate({
			formatSubmit: 'yyyy-mm-dd'
		});

		// $input = $('.datepicker').pickadate();
		$inputStart = $('.datepicker_start').pickadate();
		$inputEnd = $('.datepicker_end').pickadate();

		// Use the picker object directly.
		picker = $input.pickadate('picker');
		pickerStart = $inputStart.pickadate('picker');
		pickerEnd = $inputEnd.pickadate('picker');

		var productType = ajax_object.product_type;
		var bookingMin, bookingMax, firstDate;
		var session = false;

		var calc_mode = ajax_object.calc_mode; // Days or Nights

		if ( productType === 'simple' ) {
			var firstDate = ajax_object.first_date,
				bookingMin = ajax_object.min,
				bookingMax = ajax_object.max;

			initPicker( firstDate, bookingMin );
		}

		if ( productType === 'variable' ) {

			var $pickerWrap = $('.wceb_picker_wrap');
				$pickerWrap.hide();

			$('.price').find('.wceb-price-format').hide();

			$('body').on('found_variation', '.variations_form', function(e, variation) {

				if ( ! variation.is_purchasable || ! variation.is_in_stock || ! variation.variation_is_visible || ! variation.is_bookable ) {
					$pickerWrap.hide();
					$('.price').find('.wceb-price-format').hide();
				} else {
					$pickerWrap.slideDown( 200 );

					pickerStart.clear();
					pickerEnd.clear();

					variationId = variation.variation_id,
					firstDate = ajax_object.first_date[variationId],
					bookingMin = ajax_object.min[variationId];

					initPicker( firstDate, bookingMin );

					$('.price').find('.wceb-price-format').show();
				}

			});

			$('body').on('reset_image', '.variations_form', function(e, variation) {
				$pickerWrap.hide();
			});

		}

		function initPicker( firstDate, bookingMin ) {
			var firstDay = ebs_get_first_available_date( firstDate );

			if ( bookingMin > 0 ) { // If minimum booking duration is set
				endFirstDay = parseInt( firstDay ) + parseInt( bookingMin ); // Set endpicker start date to first date + minimum duration

				if ( calc_mode === 'days' )
					endFirstDay -= 1; // If in "Days" mode, remove one day

			} else { // If no minimum booking duration is set

				if ( calc_mode === 'nights' ) {
					endFirstDay = parseInt( firstDay ) + 1; // If in "Nights" mode, add one day
				} else {
					endFirstDay = firstDay;
				}

			}

			if ( firstDay <= 0 )
				firstDay = true;

			if ( endFirstDay <= 0 )
				endFirstDay = true;

			pickerStart.set( 'min', firstDay, { muted: true });
			pickerEnd.set( 'min', endFirstDay, { muted: true });

			

		}

		function ebs_get_first_available_date( firstDate ) {
			var firstDay = +firstDate;

			return firstDay;
		}

		ebs_clear_booking_session = function() {

			if ( session ) {

				var data = {
					action: 'clear_booking_session'
				};
			
				$.post(ajax_object.ajax_url, data, function( response ) {
					session = response;
				});
			}

		}

		ebs_get_min_and_max = function(disabledDate, operator) {

			var selectedMinDate = new Date( disabledDate.year, disabledDate.month, disabledDate.date ), // Selected date
				selectedMaxDate = new Date( disabledDate.year, disabledDate.month, disabledDate.date ); // Selected date

			if ( productType === 'variable' ) {

				var variationId = $('.variations_form').find('input.variation_id').val(),
					minimumDuration = parseInt( ajax_object.min[variationId] ),
					maximumDuration = parseInt( ajax_object.max[variationId] );

			} else {

				var minimumDuration = parseInt( ajax_object.min ),
					maximumDuration = parseInt( ajax_object.max );

			}

			var calc_mode = ajax_object.calc_mode; // "Days" or "Nights" mode

			if ( operator === 'plus' ) { // After setting start date

				if ( minimumDuration == 0 ) { // If no minimum duration was set
					
					if ( calc_mode === 'nights' )
						minimumDuration += 1; // Add one day for the "Nights" mode, as you book the night

				} else { // If a minimum duration is set

					if ( calc_mode === 'days' )
						minimumDuration -= 1; // Remove one day for the "Days" mode, as you can still book the same day
						
				}

				if ( maximumDuration == 0 )
					maximumDuration -= 1;

				if ( calc_mode === 'days' )
					maximumDuration -= 1;

			} else { // End date calendar

				if ( maximumDuration == 0 ) {

					selectedMinDate = new Date();

					if ( minimumDuration != 0 ) {
						maximumDuration = minimumDuration;
						maximumDuration -= 1;
					}

					minimumDuration = 1;

					if ( calc_mode === 'nights' ) {
						minimumDuration -= 1;
						maximumDuration += 1;
					}

				} else {

					temp = minimumDuration;
					minimumDuration = maximumDuration;
					maximumDuration = temp;

					if ( temp != 0 )
						maximumDuration -= 1;

					if ( calc_mode === 'days' ) {
						minimumDuration -= 1;
					} else {
						maximumDuration += 1;
					}

				}

			}

			if ( operator === 'plus' ) {
				selectedMinDate.setDate( selectedMinDate.getDate() + minimumDuration );
				selectedMaxDate.setDate( selectedMaxDate.getDate() + maximumDuration );
			} else {
				selectedMinDate.setDate( selectedMinDate.getDate() - minimumDuration );
				selectedMaxDate.setDate( selectedMaxDate.getDate() - maximumDuration );
			}

			// Check if minimum date is not superior
			var currentDay = new Date();
			var numberOfDaysToAdd = ebs_get_first_available_date( firstDate );

			if ( numberOfDaysToAdd != 0 )
				currentDay.setDate( currentDay.getDate() + numberOfDaysToAdd );

			if ( currentDay > selectedMinDate )
				selectedMinDate = currentDay;

			if ( maximumDuration < 0 )
				selectedMaxDate = false;

			var minAndMax = {};
     		minAndMax['min'] = selectedMinDate;
     		minAndMax['max'] = selectedMaxDate;
			
			return minAndMax;

		}

		// WooCommerce Product Add-ons compatibility
		getAdditionalCosts = function() {

			var total = 0;

			$('form.cart').find( '.addon' ).each( function() {
				var addon_cost = 0;

				if ( $(this).is('.addon-custom-price') ) {
					addon_cost = $(this).val();
				} else if ( $(this).is('.addon-input_multiplier') ) {
					if( isNaN( $(this).val() ) || $(this).val() == "" ) { // Number inputs return blank when invalid
						$(this).val('');
						$(this).closest('p').find('.addon-alert').show();
					} else {
						if( $(this).val() != "" ){
							$(this).val( Math.ceil( $(this).val() ) );
						}
						$(this).closest('p').find('.addon-alert').hide();
					}
					addon_cost = $(this).data('price') * $(this).val();
				} else if ( $(this).is('.addon-checkbox, .addon-radio') ) {
					if ( $(this).is(':checked') )
						addon_cost = $(this).data('price');
				} else if ( $(this).is('.addon-select') ) {
					if ( $(this).val() )
						addon_cost = $(this).find('option:selected').data('price');
				} else {
					if ( $(this).val() )
						addon_cost = $(this).data('price');
				}

				if ( ! addon_cost )
					addon_cost = 0;

				total += addon_cost;
			});

			return total;

		}

		ebs_set_price = function(startDate, endDate, startDateDisplay, endDateDisplay) {

			if ( productType === 'variable' ) {
				product_id = $('input[name=product_id]').val();
			} else {
				product_id = $('input[name=add-to-cart]').val();
			}
			
			variation_id = $('.variations_form').find('input.variation_id').val();

			// WooCommerce Product Add-ons compatibility
			var additionalCost = getAdditionalCosts();

			var data = {
				action: 'add_new_price',
				product_id: product_id,
				variation_id: variation_id,
				start: startDateDisplay,
				end: endDateDisplay,
				start_format: startDate,
				end_format: endDate,
				additional_cost: additionalCost
			};

			var this_page = window.location.toString();

			$('form.cart').fadeTo('400', '0.6').block({message: null, overlayCSS: {background: 'transparent', backgroundSize: '16px 16px', opacity: 0.6 } } );

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

				$('form.cart').trigger( 'update_price', [ data, response ] );

				// Unblock
				$('form.cart').stop(true).css('opacity', '1').unblock();

				session = fragments.session;
			
			});
		}

		pickerStart.on({
			render: function() {
				pickerStart.$root.find('.picker__header').prepend('<div class="picker__title">' + ajax_object.start_text + '</div>');
			},
			set: function(startTime) {

				if ( typeof startTime.clear != 'undefined' && startTime.clear == null ) {

					var minAfterClear = ebs_get_first_available_date( firstDate ) + parseInt( bookingMin );

					if ( minAfterClear === 0 ) {

						 if ( calc_mode === 'nights') {
						 	minAfterClear = 1;
						 } else {
						 	minAfterClear = true;
						 }
						
					}	

					if ( calc_mode === 'nights' && bookingMin === 0 && minAfterClear != true )
						minAfterClear += 1;

					if ( calc_mode === 'days' && bookingMin > 0 && minAfterClear != true )
						minAfterClear -= 1;

					pickerEnd.set({
						disable: false,
						min: minAfterClear,
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

					var minAndMax = ebs_get_min_and_max( disabledDate, 'plus' );
					var min = minAndMax.min;
					var max = minAndMax.max;
				
					pickerEnd.set({
						min: min,
						max: max,
						highlight: min
					}, { muted: true });

					if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' )
						ebs_set_price(startDate, endDate, startDateDisplay, endDateDisplay);
						

				} else {
					return false;
				}
				
			},
			close: function() {
				$(document.activeElement).blur();
				if ( typeof startPick != 'undefined' && typeof endPick == 'undefined' )
					setTimeout(function() { pickerEnd.open(); }, 250);
			}
		});

		pickerEnd.on({
			render: function() {
				pickerEnd.$root.find('.picker__header').prepend('<div class="picker__title">' + ajax_object.end_text + '</div>');
			},
			set: function(endTime) {

				if ( typeof endTime.clear != 'undefined' && endTime.clear == null ) {

					var minAfterClear = ebs_get_first_available_date( firstDate );

					if ( minAfterClear === 0 )
						minAfterClear = true;

					pickerStart.set({
						disable: false,
						min: minAfterClear,
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

					var minAndMax = ebs_get_min_and_max( disabledEndDate, 'minus' );
					var min = minAndMax.min;
					var max = minAndMax.max;

					pickerStart.set({
						min: min,
						max: max,
						highlight: min
					}, { muted: true });

					if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' )
						ebs_set_price(startDate, endDate, startDateDisplay, endDateDisplay);
						

				} else {
					return false;
				}
				
			},
			close: function() {
				$(document.activeElement).blur();
			}
		});

		// WooCommerce Product Add-ons compatibility
		$('body').on('updated_addons', function() {

			if ( productType === 'variable' ) {

				var variationId = $('.variations_form').find('input.variation_id').val(),
				product_price = parseInt( ajax_object.product_price[variationId] );

			} else {

				var product_price = parseInt( ajax_object.product_price );

			}

			var addon_costs = getAdditionalCosts();
			
			var total_price = parseFloat(addon_costs + product_price);

			if ( addon_costs > 0 ) {
				pickerStart.clear();
				pickerEnd.clear();
			}

			var formatted_total = accounting.formatMoney( total_price, {
				symbol 		: ajax_object.currency_format_symbol,
				decimal 	: ajax_object.currency_format_decimal_sep,
				thousand	: ajax_object.currency_format_thousand_sep,
				precision 	: ajax_object.currency_format_num_decimals,
				format		: ajax_object.currency_format
			} );

			if ( productType === 'variable' ) {
				$('.single_variation .price .amount').html(formatted_total);
			} else {
				$('p.booking_price .price .amount').html(formatted_total);
			}
			
		});

	});
})(jQuery);