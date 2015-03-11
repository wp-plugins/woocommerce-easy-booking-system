(function($) {
	$(document).ready(function() {

		$('.easy-booking-notice-close').on('click', function(e) {
			e.preventDefault();
			var $this = $(this),
				notice = $this.data('notice');

			var data = {
				action: 'easy_booking_hide_notice',
				notice: notice
			};

			$.ajax({
				url:  ajax_object.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					$this.parent('.easy-booking-notice').hide();
				}
			});
			
		});
		
	});
})(jQuery);