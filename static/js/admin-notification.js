jQuery(document).ready(function() {
	let popupPosition = function() {
		let pad = jQuery('#wpcontent').css('padding-left');

		jQuery('.strix-popup').css({
			right: pad,
			'margin-left': pad
		});
	};

	popupPosition();
	jQuery(window).resize(popupPosition);

	jQuery(document).on('click', '.strix-notification-row .strix-close-notification', function(event) {
		let container = jQuery(this).closest('.strix-notification-row');
		container.data('close-url', "").find('.notice-dismiss').trigger('click');
	});

	jQuery(document).on('click', '.strix-notification-row .strix-remind-later, .strix-notification-row .strix-hide-notification', function(event) {
		event.preventDefault();

		let container = jQuery(this).closest('.strix-notification-row');
		container.data('close-url', jQuery(this).attr('href')).find('.notice-dismiss').trigger('click');

		return false;
	});

	jQuery(document).on('click', '.strix-notification-row .notice-dismiss', function(event) {
		event.preventDefault();

		let closeUrl = jQuery(this).closest('.strix-notification-row').data('close-url');
		if (closeUrl) {
			jQuery.post(closeUrl, {});
		}
	});
});