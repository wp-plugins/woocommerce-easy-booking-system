(function($) {
	$(document).ready(function() {

		$input = $('.datepicker').pickadate({
			formatSubmit: 'yyyy-mm-dd'
		});

		// $input = $('.datepicker').pickadate();
		$inputStart = $('.datepicker_start').pickadate();
		$inputEnd = $('.datepicker_end').pickadate();

		// Use the picker object directly.
		pickerStart = $inputStart.pickadate('picker');
		pickerEnd = $inputEnd.pickadate('picker');

		var pickerStartItem = pickerStart.component.item,
			pickerEndItem = pickerEnd.component.item;

		var productType = ajax_object.product_type,
			calc_mode = ajax_object.calc_mode, // Days or Nights
			bookingMin, bookingMax, firstDate,
			session = false;

		if ( productType === 'simple' ) {
			var firstDate = ajax_object.first_date,
				bookingMin = ajax_object.min,
				bookingMax = ajax_object.max;

			initPickers( firstDate, bookingMin );

			$('body').trigger( 'pickers_init' );

			pickerStart.render();
			pickerEnd.render();
			
			$('body').trigger( 'after_pickers_init' );

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

					variationId = variation.variation_id;
					firstDate = ajax_object.first_date[variationId];
					bookingMin = ajax_object.min[variationId];

					initPickers( firstDate, bookingMin );

					$('body').trigger( 'pickers_init', [variation] );

					pickerStart.render();
					pickerEnd.render();

					$('body').trigger( 'after_pickers_init', [variation] );

					$('.price').find('.wceb-price-format').show();
				}

			});

			$('body').on('reset_image', '.variations_form', function(e, variation) {
				$pickerWrap.hide();
			});

		}

		function initPickers( firstDate, bookingMin ) {
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
				firstDay = false;

			if ( endFirstDay <= 0 )
				endFirstDay = false;

			var minObject = createDateObject( false, firstDay ),
				endMinObject = createDateObject( false, endFirstDay ),
				max = createDateObject( 'infinity' ),
				highlight = createDateObject(),
				view = createDateObject( new Date( highlight.year, highlight.month, 1 ) );

			pickerStartItem.clear = null;
			pickerStartItem.select = undefined;
			pickerStartItem.min = minObject;
			pickerStartItem.max = max;
			pickerStartItem.highlight = highlight;
			pickerStartItem.view = view;

			pickerStart.$node.val('');
			
			pickerEndItem.clear = null;
			pickerEndItem.select = undefined;
			pickerEndItem.min = endMinObject;
			pickerEndItem.max = max;
			pickerEndItem.highlight = highlight;
			pickerEndItem.view = view;

			pickerEnd.$node.val('');

			return false;

		}

		function createDateObject( date, add ) {
			if ( ! date ) var date = new Date();
			if ( add ) date.setDate( date.getDate() + add );

			if ( date === 'infinity' ) {
				var dateObject = {
					date: Infinity,
					day: Infinity,
					month: Infinity,
					obj: Infinity,
					pick: Infinity,
					year: Infinity
				}

				return dateObject;
			}

			date.setHours(0,0,0,0);

			var dateObject = {
				date: date.getDate(),
				day: date.getDay(),
				month: date.getMonth(),
				obj: date,
				pick: date.getTime(),
				year: date.getFullYear()
			}

			return dateObject;
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
				var pickerEndItem = pickerEnd.component.item,
					startSet = pickerStart.get('select'),
					endSet = pickerEnd.get('select');

				// Clear Start Picker
				if ( typeof startTime.clear != 'undefined' && startTime.clear == null ) {

					var minAfterClear = ebs_get_first_available_date( firstDate ) + parseInt( bookingMin );

					if ( calc_mode === 'nights' && bookingMin == 0 && minAfterClear != 0 )
						minAfterClear += 1;

					if ( minAfterClear == 0 ) {

						 if ( calc_mode === 'nights') {
						 	minAfterClear = 1;
						 }
						
					}

					if ( calc_mode === 'days' && bookingMin > 0 && minAfterClear != 0 )
						minAfterClear -= 1;

					var min = createDateObject( false, minAfterClear ),
						max = createDateObject( 'infinity' ),
						view = createDateObject( new Date(min.year,min.month,01) );

					pickerEndItem.disable = [];
					pickerEndItem.min = min;
					pickerEndItem.max = max;

					if ( endSet == null ) {
						pickerEndItem.highlight = min;
						pickerEndItem.view = view;
					}

					$('body').trigger('clear_start_picker', pickerEndItem );

					pickerEnd.render();

					ebs_clear_booking_session();
				}

				// Set Start Picker
				if ( startTime.select && typeof startTime.select != 'undefined' ) {

					startPick = startTime.select;
					startDate = pickerStart.get('select', 'yyyy-mm-dd');
					disabledDate = pickerStart.get('select');
					startDateDisplay = pickerStart.get('select', 'dd mmmm yyyy');

					// Hotfix for pickadate.js bug when selecting dates with keyboard, waiting for v4.0 fix
					if ( typeof startPick === 'object' ) {
						startPick = startTime.select.pick;
					}

					var minAndMax = ebs_get_min_and_max( disabledDate, 'plus' ),
						min = minAndMax.min,
						max = minAndMax.max;

					if ( ! max ) max = 'infinity';

					var min = createDateObject( min ),
						max = createDateObject( max ),
						view = createDateObject( new Date(min.year,min.month,01) );

					pickerEndItem.min = min;
					pickerEndItem.max = max;

					if ( endSet == null ) {
						pickerEndItem.highlight = min;
						pickerEndItem.view = view;
					}

					$('body').trigger('set_start_picker', [pickerEndItem, startPick] );
					pickerEnd.render();

					if ( startSet != null && endSet != null )
						ebs_set_price(startDate, endDate, endSet, endDateDisplay);

				} else {
					return false;
				}
				
			},
			close: function() {
				$(document.activeElement).blur();

				var startSet = pickerStart.get('select'),
					endSet = pickerEnd.get('select');

				if ( startSet != null && endSet == null )
					setTimeout(function() { pickerEnd.open(); }, 250);
			}
		});

		pickerEnd.on({
			render: function() {
				pickerEnd.$root.find('.picker__header').prepend('<div class="picker__title">' + ajax_object.end_text + '</div>');
			},
			set: function(endTime) {
				var startSet = pickerStart.get('select'),
					endSet = pickerEnd.get('select');

				// Clear End Picker
				if ( typeof endTime.clear != 'undefined' && endTime.clear == null ) {

					var minAfterClear = ebs_get_first_available_date( firstDate );

					var min = createDateObject( false, minAfterClear ),
						max = createDateObject( 'infinity' );
						highlight = createDateObject(),
						view = createDateObject( new Date(highlight.year,highlight.month,01) );

					pickerStartItem.disable = [];
					pickerStartItem.min = min;
					pickerStartItem.max = max;

					if ( startSet == null ) {
						pickerStartItem.highlight = highlight;
						pickerStartItem.view = view;
					}

					$('body').trigger('clear_end_picker', pickerStartItem );
					pickerStart.render();

					ebs_clear_booking_session();
				}

				// Set End Picker
				if ( endTime.select && typeof endTime.select != 'undefined' ) {

					endPick = endTime.select;
					endDate = pickerEnd.get('select', 'yyyy-mm-dd');
					disabledEndDate = pickerEnd.get('select');
					endDateDisplay = pickerEnd.get('select', 'dd mmmm yyyy');

					// Hotfix for pickadate.js bug when selecting dates with keyboard, waiting for v4.0 fix
					if ( typeof endPick === 'object' ) {
						endPick = endTime.select.pick;
					}

					var minAndMax = ebs_get_min_and_max( disabledEndDate, 'minus' ),
						min = minAndMax.min,
						max = minAndMax.max;

					if ( ! max ) max = 'infinity';

					var min = createDateObject( min ),
						max = createDateObject( max ),
						view = createDateObject( new Date(min.year,min.month,01) );

					pickerStartItem.min = min;
					pickerStartItem.max = max;

					if ( startSet == null ) {
						pickerStartItem.highlight = min;
						pickerStartItem.view = view;
					}

					$('body').trigger('set_end_picker', [pickerStartItem, endPick] );
					pickerStart.render();

					if ( startSet != null && endSet != null )
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