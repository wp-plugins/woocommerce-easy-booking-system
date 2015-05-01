(function($) {
	$(document).ready(function() {

		$('.wc-metabox > h3').click( function(event){
			$( this ).parent( '.wc-metabox' ).toggleClass( 'closed' ).toggleClass( 'open' );
		});

		// META BOXES - Open/close
		$('.wc-metaboxes-wrapper').on('click', '.wc-metabox h3', function(event) {
			$(this).next('.wc-metabox-content').stop().slideToggle();
		})
		.on('click', '.expand_all', function(event){
			$(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox > .wc-metabox-content').show();
			return false;
		})
		.on('click', '.close_all', function(event){
			$(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox > .wc-metabox-content').hide();
			return false;
		});
		$('.wc-metabox.closed').each(function(){
			$(this).find('.wc-metabox-content').hide();
		});
		
	});
})(jQuery);