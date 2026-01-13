if (typeof StrixJsLoaded === 'undefined') {
	var StrixJsLoaded = {};
}

StrixJsLoaded.common = true;

function popupCenter(w, h)
{
	let dleft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
	let dtop = window.screenTop !== undefined ? window.screenTop : window.screenY;

	let width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
	let height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

	let left = parseInt((width - w) / 2 + dleft);
	let top = parseInt((height - h) / 2 + dtop);

	return ',top=' + top + ',left=' + left;
}

jQuery.fn.expand = function() {
	let textarea = jQuery(this);
	let val = textarea.val();

	textarea.css('height', textarea.get(0).scrollHeight + 'px');
	textarea.val('').val(val);
};

jQuery(document).ready(function() {
	/*************************************************************************/
	/* PASSWORD TOGGLE */
	jQuery('.strix-toggle-password').on('click', function(event) {
		event.preventDefault();

		let icon = jQuery(this);
		let parent = icon.closest('.strix-form-group');

		if (icon.hasClass('dashicons-visibility')) {
			parent.find('input').attr('type', 'text');
			icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
		}
		else {
			parent.find('input').attr('type', 'password');
			icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
		}
	});

	// toggle opacity
	jQuery('.strix-toggle-opacity').css('opacity', 1);

	/*************************************************************************/
	/* TOGGLE */
	jQuery('#strix-plugin-settings-page .btn-toggle').on('click', function(event) {
		event.preventDefault();

		jQuery(jQuery(this).attr('href')).toggle();

		return false;
	});

	/*************************************************************************/
	/* FILTER */
	// checkbox
	jQuery('.strix-checkbox:not(.strix-disabled)').on('click', function() {
		let checkbox = jQuery(this).find('input[type=checkbox], input[type=radio]');
		checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');

		return false;
	});

	let htmlentities = function(str) {
		let div = document.createElement('div');
		div.textContent = str;

		return div.innerHTML;
	}

	// background post save - style and set change
	let backgroundPostSave = function(event) {
		let form = jQuery(event.target).closest('form');
		let data = form.serializeArray();

		// include unchecked checkboxes
		form.find('input[type=checkbox]').each(function() {
			let checkbox = jQuery(this);

			if (!checkbox.prop('checked') && checkbox.attr('name')) {
				data.push({
					name: checkbox.attr('name'),
					value: 0
				});
			}
		});

		data.forEach(item => {
			if (['fomo-title', 'fomo-text'].indexOf(item.name) !== -1) {
				item.value = htmlentities(item.value);
			}
		});

		// show loading
		jQuery('#strix-loading').addClass('strix-active');
		jQuery('li.strix-preview-box').addClass('disabled');

		jQuery.ajax({
			url: form.attr('action'),
			type: 'post',
			dataType: 'application/json',
			data: data
		}).always(() => location.reload(true));

		return false;
	};
	jQuery('#strix-widget-selects select, #strix-widget-options input[type=checkbox]').on('change', backgroundPostSave);
	jQuery('.strix-save-input-on-change-color').on('change-color', backgroundPostSave);
	jQuery('.strix-save-input-on-change').on('change', event => {
		let input = event.target;

		if (input.changeTimeout) {
			clearTimeout(input.changeTimeout);
		}

		input.changeTimeout = setTimeout(() => backgroundPostSave(event), 400);
	});

	// layout select filter
	jQuery('input[name=layout-select]').on('change', function(event) {
		event.preventDefault();

		let ids = (jQuery('input[name=layout-select]:checked').data('ids') + "").split(',');

		if (ids.length === 0 || ids[0] === "") {
			jQuery('.strix-preview-boxes-container').find('.strix-full-width, .strix-half-width').fadeIn();
		}
		else {
			jQuery('.strix-preview-boxes-container').find('.strix-full-width, .strix-half-width').hide();
			ids.forEach(id => jQuery('.strix-preview-boxes-container').find('.strix-preview-boxes[data-layout-id="'+ id + '"]').parent().fadeIn());
		}

		return false;
	});

	/*************************************************************************/
	/* NOTICE HIDE */
	jQuery(document).on('click', '.strix-notice.is-dismissible .notice-dismiss', function() {
		let button = jQuery(this);
		let container = button.closest('.strix-notice');

		container.fadeOut(200);

		if (button.data('command') && !button.data('ajax-run')) {
			button.data('ajax-run', 1); // prevent multiple triggers

			jQuery.post('', { command: button.data('command') });
		}
	});

	jQuery('.strix-checkbox input[type=checkbox][onchange]').on('change', function() {
		jQuery('#strix-loading').addClass('strix-active');
	});

	/*************************************************************************/
	/* DROPDOWN */

	// change dropdown arrow positions
	let fixDropdownArrows = function() {
		jQuery('.strix-button-dropdown-arrow').each(function() {
			let arrow = jQuery(this);
			let button = arrow.closest('td').find(arrow.data('button'));

			// add prev buttons' width
			let left = 0;
			button.prevAll('.strix-btn').each(function() {
				left += jQuery(this).outerWidth(true);
			});

			// center the arrow
			left += button.outerWidth() / 2;

			arrow.css('left', left + 'px');
		});
	};

	fixDropdownArrows();

	/*************************************************************************/
	/* Color Picker */
	jQuery('.strix-color-picker').each(function() {
		let input = jQuery(this);
		let moveTimeout = null;

		if (typeof jQuery.fn.spectrum !== 'undefined') {
			input.spectrum({
				replacerClassName: 'strix-color-picker',
				color: input.val(),
				showAlpha: true,
				showInput: true,
				showInitial: true,
				showPalette: true,
				hideAfterPaletteSelect:true,
				showButtons: false,
				preferredFormat: 'hex',
				palette: [
					[ '#000','#444','#666','#999','#ccc','#eee','#f3f3f3','#fff' ],
					[ '#f00','#f90','#ff0','#0f0','#0ff','#00f','#90f','#f0f' ],
					[ '#f4cccc','#fce5cd','#fff2cc','#d9ead3','#d0e0e3','#cfe2f3','#d9d2e9','#ead1dc' ],
					[ '#ea9999','#f9cb9c','#ffe599','#b6d7a8','#a2c4c9','#9fc5e8','#b4a7d6','#d5a6bd' ],
					[ '#e06666','#f6b26b','#ffd966','#93c47d','#76a5af','#6fa8dc','#8e7cc3','#c27ba0' ],
					[ '#c00','#e69138','#f1c232','#6aa84f','#45818e','#3d85c6','#674ea7','#a64d79' ],
					[ '#900','#b45f06','#bf9000','#38761d','#134f5c','#0b5394','#351c75','#741b47' ],
					[ '#600','#783f04','#7f6000','#274e13','#0c343d','#073763','#20124d','#4c1130' ]
				],
				change: function(color) {
					let value = color.toRgbString();

					// check only rgb
					if (value.substr(0, 4) === 'rgb(') {
						value = color.toHexString();
					}

					// set value & trigger change
					input.val(value).trigger('change-color');

					if (moveTimeout) {
						clearTimeout(moveTimeout);
					}
				},
				move: function(color) {
					if (moveTimeout) {
						clearTimeout(moveTimeout);
					}

					moveTimeout = setTimeout(function() {
						let value = color.toRgbString();

						// check only rgb
						if (value.substr(0, 4) === 'rgb(') {
							value = color.toHexString();
						}

						// set value & trigger change
						input.val(value).trigger('change-color');
					}, 400);
				}
			});
		}
	});
});


// - import/btn-loading.js
// loading on click
jQuery(document).on('click', '.strix-btn-loading-on-click', function() {
	let btn = jQuery(this);

	btn.addClass('strix-btn-loading').blur();
});

// - import/copy-to-clipboard.js
jQuery(document).on('click', '.btn-copy2clipboard', function(event) {
	event.preventDefault();

	let btn = jQuery(this);
	btn.blur();

	let obj = jQuery(btn.attr('href'));
	let text = obj.html() ? obj.html() : obj.val();

	// parse html
	let textArea = document.createElement('textarea');
	textArea.innerHTML = text;
	text = textArea.value;

	let feedback = () => {
		btn.removeClass('strix-toggle-tooltip').addClass('strix-show-tooltip');

		if (typeof this.timeout !== 'undefined') {
			clearTimeout(this.timeout);
		}

		this.timeout = setTimeout(() => btn.removeClass('strix-show-tooltip').addClass('strix-toggle-tooltip'), 3000);
	};

	if (!navigator.clipboard) {
		// fallback
		textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed'; // avoid scrolling to bottom
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');

			feedback();
		}
		catch (err) { }

		document.body.removeChild(textArea);
		return;
	}

	navigator.clipboard.writeText(text).then(feedback);
});

// - import/modal.js
jQuery(document).on('click', '.btn-modal-close', function(event) {
	event.preventDefault();

	jQuery(this).closest('.strix-modal').fadeOut();
});

jQuery(document).on('click', '.strix-modal', function(event) {
	if (event.target.nodeName !== 'A') {
		event.preventDefault();

		if (!jQuery(event.target).closest('.strix-modal-dialog').length) {
			jQuery(this).fadeOut();
		}
	}
});

// - import/rate-us.js
// remember on hover
jQuery(document).on('mouseenter', '.strix-quick-rating', function(event) {
	let container = jQuery(this);
	let selected = container.find('.strix-star-check.strix-active, .star-check.active');

	if (selected.length) {
		// add index to data & remove all active stars
		container.data('selected', selected.index()).find('.strix-star-check, .star-check').removeClass('strix-active active');

		// give back active star on mouse enter
		container.one('mouseleave', () => container.find('.strix-star-check, .star-check').eq(container.data('selected')).addClass('strix-active active'));
	}
});

// star click
jQuery(document).on('click', '.strix-rate-us-box .strix-quick-rating .strix-star-check', function(event) {
	event.preventDefault();

	let star = jQuery(this);
	let container = star.parent();

	// add index to data & remove all active stars
	container.data('selected', star.index()).find('.strix-star-check').removeClass('strix-active');

	// select current star
	star.addClass('strix-active');

	// show modals
	if (parseInt(star.data('value')) >= 4) {
		// open new window
		window.open(location.href + '&command=rate-us-feedback&_wpnonce='+ container.data('nonce') +'&star=' + star.data('value'), '_blank');

		jQuery('.strix-rate-us-box').fadeOut();
	}
	else {
		let feedbackModal = jQuery('#strix-rateus-modal-feedback');

		if (feedbackModal.data('bs') == '5') {
			feedbackModal.modal('show');
			setTimeout(() => feedbackModal.find('textarea').focus(), 500);
		}
		else {
			feedbackModal.fadeIn();
			feedbackModal.find('textarea').focus();
		}

		feedbackModal.find('.strix-quick-rating .strix-star-check').removeClass('strix-active').eq(star.index()).addClass('strix-active');
	}
});

// write to support
jQuery(document).on('click', '.btn-rateus-support', function(event) {
	event.preventDefault();

	let btn = jQuery(this);
	btn.blur();

	let container = jQuery('#strix-rateus-modal-feedback');
	let email = container.find('input[type=text]').val().trim();
	let text = container.find('textarea').val().trim();

	// hide errors
	container.find('input[type=text], textarea').removeClass('is-invalid');

	// check email
	if (email === "" || !/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email)) {
		container.find('input[type=text]').addClass('is-invalid').focus();
	}

	// check text
	if (text === "") {
		container.find('textarea').addClass('is-invalid').focus();
	}

	// there is error
	if (container.find('.is-invalid').length) {
		return false;
	}

	// show loading animation
	btn.addClass('strix-btn-loading');
	container.find('a, button').css('pointer-events', 'none');

	// ajax request
	jQuery.ajax({
		type: 'post',
		dataType: 'application/json',
		data: {
			command: 'rate-us-feedback',
			_wpnonce: btn.data('nonce'),
			email: email,
			text: text,
			star: container.find('.strix-quick-rating .strix-star-check.strix-active').data('value')
		}
	}).always(() => location.reload(true));
});
