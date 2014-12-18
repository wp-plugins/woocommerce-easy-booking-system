(function($) {
	$(document).ready(function() {

		var item_picker = $('body').on('click', '.edit-order-item', function() {

			var i_id = $(this).closest( 'tr' );
			var a = i_id.find('.datepicker');
			var id = i_id.find('.variation_id');

			if ( a.length && id.length ) {

				$input = a.pickadate({
					min: true,
		          	close: '',
		          	formatSubmit: 'yyyy-mm-dd'
				});

				setStartOnLoad = false;
				setEndOnLoad = false;

				var item_id = id.data('item_id'),
					$inputStart = $('.datepicker_start--' + item_id).pickadate(),
					$inputEnd = $('.datepicker_end--' + item_id).pickadate(),
					picker = $input.pickadate('picker'),
					pickerStart = $inputStart.pickadate('picker'),
					pickerEnd = $inputEnd.pickadate('picker');

					setStart = $('.datepicker_start--' + item_id).data('value'),
					setEnd = $('.datepicker_end--' + item_id).data('value');

					pickerStart.on({
						set: function(startTime) {

							if ( typeof startTime.clear != 'undefined' && startTime.clear == null ) {
								pickerEnd.set('disable', false, { muted: true })
										 .set('max', false, { muted: true })
										 .set('highlight', new Date(), { muted: true });

								startPick = undefined;
								ebs_clear_booking_session();
							} else {
								startPick = startTime.select;
							}

							if ( startTime.select && typeof startTime.select != 'undefined' ) {

								startPickerData = {
									startDate: pickerStart.get('select', 'yyyy-mm-dd'),
									disabledDate: pickerStart.get('select'),
									startDateDisplay: pickerStart.get('select', 'dd mmmm yyyy')
								}
							
								pickerEnd.set('disable', false, { muted: true });

								if ( order_ajax_info.calc_mode == 'days' ) {
									pickerEnd.set('disable', [
										{ from : true, to : [startPickerData.disabledDate.year, startPickerData.disabledDate.month, startPickerData.disabledDate.date - 1] }
									], { muted: true });
								} else {
									pickerEnd.set('disable', [
										{ from : true, to : [startPickerData.disabledDate.year, startPickerData.disabledDate.month, startPickerData.disabledDate.date] }
									], { muted: true });
								}

							if ( setStart == '' )
								setStartOnLoad = true;

							if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' && setStartOnLoad == true && setEndOnLoad == true )
								ebs_set_price(item_id, startPickerData, endPickerData);

							if ( setStart != '' )
								setStartOnLoad = true;

							}
							
						}
					});

					pickerEnd.on({
						set: function(endTime) {

							if ( typeof endTime.clear != 'undefined' && endTime.clear == null ) {
								pickerStart.set('disable', false, { muted: true })
										   .set('max', false, { muted: true })
										   .set('highlight', new Date(), { muted: true });

								endPick = undefined;
								ebs_clear_booking_session();
							} else {
								endPick = endTime.select;
							}			

							if ( endTime.select && typeof endTime.select != 'undefined' ) {

								endPickerData = {
									endDate: pickerEnd.get('select', 'yyyy-mm-dd'),
									disabledEndDate: pickerEnd.get('select'),
									endDateDisplay: pickerEnd.get('select', 'dd mmmm yyyy')
								}

								if ( order_ajax_info.calc_mode == 'days' ) {
									pickerStart.set('max', [endPickerData.disabledEndDate.year, endPickerData.disabledEndDate.month, endPickerData.disabledEndDate.date],
									{ muted: true });
								} else {
									pickerStart.set('max', [endPickerData.disabledEndDate.year, endPickerData.disabledEndDate.month, endPickerData.disabledEndDate.date -1],
									{ muted: true });
								}

							if ( setEnd == '' )
								setEndOnLoad = true;

							if ( typeof startPick != 'undefined' && typeof endPick != 'undefined' && setStartOnLoad == true && setEndOnLoad == true )
								ebs_set_price(item_id, startPickerData, endPickerData);

							if ( setEnd != '' )
								setEndOnLoad = true;

							}
							
						}
					});

					ebs_clear_booking_session = function() {

						var data = {
							action: 'clear_booking_session'
						};

						$.post(order_ajax_info.ajax_url, data);

					}

					ebs_set_price = function( item_id, startPickerData, endPickerData ) {

						var qty_input = $('.quantity[name="order_item_qty[' + item_id + ']"]'),
							qty = qty_input.val(),
							o_qty = qty_input.attr( 'data-qty' );

						var data = {
							action: 'ebs_sku_order_update_product_dates',
							order_id: order_ajax_info.order_id,
							item_id: item_id,
							quantity: qty,
							start: startPickerData.startDateDisplay,
							end: endPickerData.endDateDisplay,
							start_format: startPickerData.startDate,
							end_format: endPickerData.endDate
						};

						$('.item[data-order_item_id=' + item_id + ']').fadeTo('400', '0.6').block({message: null, overlayCSS: {background: 'transparent url(' + woocommerce_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', backgroundSize: '16px 16px', opacity: 0.6 } } );

						$.post( order_ajax_info.ajax_url, data, function( totals ) {
							
							var line_total = $('.line_total[name="line_total[' + item_id + ']"]'),
								line_subtotal = $('.line_subtotal[name="line_subtotal[' + item_id + ']"]'),
								line_total_tax = $('.line_tax[name="line_tax[' + item_id + '][1]"]'),
								line_subtotal_tax = $('.line_subtotal_tax[name="line_subtotal_tax[' + item_id + '][1]"]');

							// Totals
							var unit_total = accounting.unformat( totals.total, woocommerce_admin.mon_decimal_point );
							var single_total = accounting.unformat( unit_total / o_qty, woocommerce_admin.mon_decimal_point );
							line_total.val(
								parseFloat( accounting.formatNumber( single_total * qty, woocommerce_admin_meta_boxes.rounding_precision, '' ) )
									.toString()
									.replace( '.', woocommerce_admin.mon_decimal_point )
							);

							var unit_subtotal = accounting.unformat( totals.subtotal, woocommerce_admin.mon_decimal_point );
							var single_subtotal = accounting.unformat( unit_subtotal / o_qty, woocommerce_admin.mon_decimal_point );
							line_subtotal.val(
								parseFloat( accounting.formatNumber( single_subtotal * qty, woocommerce_admin_meta_boxes.rounding_precision, '' ) )
									.toString()
									.replace( '.', woocommerce_admin.mon_decimal_point )
							);

							if ( totals.tax_total && typeof totals.tax_total != 'undefined' ) {
								
								var unit_total_tax = accounting.unformat( totals.tax_total[1], woocommerce_admin.mon_decimal_point );
								var single_total_tax = accounting.unformat( unit_total_tax / o_qty, woocommerce_admin.mon_decimal_point );
								if ( 0 < single_total_tax ) {
									line_total_tax.val(
										parseFloat( accounting.formatNumber( single_total_tax * qty, woocommerce_admin_meta_boxes.rounding_precision, '' ) )
											.toString()
											.replace( '.', woocommerce_admin.mon_decimal_point )
									);
								}
							}

							if ( totals.tax_subtotal && typeof totals.tax_subtotal != 'undefined' ) {

								var unit_subtotal_tax = accounting.unformat( totals.tax_subtotal[1], woocommerce_admin.mon_decimal_point );
								var single_subtotal_tax = accounting.unformat( unit_subtotal_tax / o_qty, woocommerce_admin.mon_decimal_point );
								if ( 0 < single_subtotal_tax ) {
									line_subtotal_tax.val(
										parseFloat( accounting.formatNumber( single_subtotal_tax * qty, woocommerce_admin_meta_boxes.rounding_precision, '' ) )
											.toString()
											.replace( '.', woocommerce_admin.mon_decimal_point )
									);
								}

							}

							// Unblock
							$('.item[data-order_item_id=' + item_id + ']').stop(true).css('opacity', '1').unblock();
						
						});
					}

					if ( setStart != '' )
						pickerStart.set('select', setStart);

					if ( setEnd != '' )
						pickerEnd.set('select', setEnd);

			}

		});

	});
})(jQuery);