if (typeof StrixJsLoaded === 'undefined') {
	var StrixJsLoaded = {};
}

StrixJsLoaded.connect = true;

// autocomplete config
var strixConnect = null;
jQuery(document).ready(function($) {
	/*************************************************************************/
	/* NO REG MODE */
	strixConnect = {
		button: $('.strix-connect-platform .strix-btn'),
		form: $('#strix-connect-platform-form'),
		asyncRequest: function(callback, btn) {
			// get url params
			let params = new URLSearchParams({
				type: 'google',
				page_id: $('#strix-noreg-page-id').val().trim(),
				access_token: $('#strix-noreg-access-token').length ? $('#strix-noreg-access-token').val() : "",
				webhook_url: $('#strix-noreg-webhook-url').val(),
				email: $('#strix-noreg-email').val(),
				token: $('#strix-noreg-connect-token').val(),
				version: $('#strix-noreg-version').val()
			});

			// open window
			let tiWindow = window.open('https://admin.strix.io/source/wordpressPageRequest?' + params.toString(), 'strix', 'width=850,height=850,menubar=0' + popupCenter(850, 850));

			// wait for process complete
			window.addEventListener('message', function(event) {
				if (event.origin.startsWith('https://admin.strix.io/'.replace(/\/$/,'')) && event.data.success) {
					tiWindow.close();

					callback($('#strix-noreg-connect-token').val(), event.data.request_id, (event.data.manual_download | 0), event.data.place || null);
				}
			});

			// show popup info
			$('#strix-connect-info').removeClass('ti-d-none');
			let timer = setInterval(function() {
				if (tiWindow.closed) {
					$('#strix-connect-info').addClass('ti-d-none');

					if (!dontRemoveLoading) {
						button.removeClass('ti-btn-loading');
					}

					clearInterval(timer);
				}
			}, 1000);
		}
	};

	
		$('.btn-connect-public').click(function(event) {
			event.preventDefault();

			let button = $(this);
			let token = $('#strix-noreg-connect-token').val();

			button.addClass('ti-btn-loading').blur();

			let dontRemoveLoading = false;

			// Check if admin plugin is active
			if (typeof strix_connect_config === 'undefined' || !strix_connect_config.admin_available) {
				// Fallback to original behavior if admin plugin not available
				let params = new URLSearchParams({
					type: 'Google',
					referrer: 'public',
					webhook_url: $('#strix-noreg-webhook-url').val(),
					token: token,
					version: $('#strix-noreg-version').val()
				});

				let tiWindow = window.open('https://admin.strix.io/source/edit2?' + params.toString(), 'strix', 'width=850,height=850,menubar=0' + popupCenter(850, 850));

				window.addEventListener('message', function(event) {
					if (event.origin.startsWith('https://admin.strix.io/'.replace(/\/$/,'')) && event.data.id) {
						dontRemoveLoading = true;
						$('#strix-noreg-page-details').val(JSON.stringify(event.data));
						$('#strix-noreg-review-request-id').val(event.data.request_id || '');
						button.closest('form').submit();
					}
				});

				return;
			}

			// Use modal with Google Places API integration
			openGoogleConnectModal(button, token);

			$('#strix-connect-info').removeClass('ti-d-none');
			let timer = setInterval(function() {
				if (tiWindow.closed) {
					$('#strix-connect-info').addClass('ti-d-none');

					if (!dontRemoveLoading) {
						button.removeClass('ti-btn-loading');
					}

					clearInterval(timer);
				}
			}, 1000);
		});

	
		// try reply again
		jQuery(document).on('click', '.btn-try-reply-again', function(event) {
			event.preventDefault();

			let btn = jQuery(this);
			let replyBox = btn.closest('td').find('.strix-reply-box');

			replyBox.attr('data-state', btn.data('state'));
			replyBox.find('.state-'+ btn.data('state') +' .btn-post-reply').attr('data-reconnect', 1).trigger('click');
		});

	// make async request on review download
	$('.btn-download-reviews').on('click', function(event) {
		event.preventDefault();

		let btn = jQuery(this);

		strixConnect.asyncRequest(function(token, request_id, manual_download, place) {
			if (place) {
				$.ajax({
					type: 'POST',
					data: {
						_wpnonce: btn.data('nonce'),
						download_data: JSON.stringify(place)
					}
				}).always(() => location.reload());
			}
			else {
				$.ajax({
					type: 'POST',
					data: {
						_wpnonce: btn.data('nonce'),
						review_download_request: token,
						review_download_request_id: request_id,
						manual_download: manual_download
					}
				}).always(() => location.reload());
			}
		}, btn);
	});

	// manual download
	$('#strix-review-manual-download').on('click', function(event) {
		event.preventDefault();

		let btn = $(this);
		btn.addClass('ti-btn-loading').blur();

		$.ajax({
			url: location.search.replace(/&tab=[^&]+/, '&tab=free-widget-configurator'),
			type: 'POST',
			data: {
				command: 'review-manual-download',
				_wpnonce: btn.data('nonce')
			},
			success: () => location.reload(),
			error: function() {
				btn.removeClass('ti-btn-loading');

				btn.removeClass('ti-toggle-tooltip').addClass('ti-show-tooltip');
				setTimeout(() => btn.removeClass('ti-show-tooltip').addClass('ti-toggle-tooltip'), 3000);
			}
		});
	});

	// Google Places API Modal Integration
	function openGoogleConnectModal(button, token) {
		// Reset modal state
		$('#strix-selected-profile').hide();
		$('#strix-connect-error').hide();
		$('#strix-google-autocomplete-modal').val('');
		$('#strix-connect-profile-btn').prop('disabled', true);

		// Show modal
		$('#strix-connect-modal').modal('show');

		// Initialize Google Places API if available
		if (typeof google !== 'undefined' && google.maps && google.maps.places) {
			initializeGooglePlacesInModal();
		} else {
			// Load Google Maps API dynamically
			if (!window.googleMapsApiLoaded) {
				window.googleMapsApiLoaded = true;
				let apiKey = strix_connect_config && strix_connect_config.google_api_key ? strix_connect_config.google_api_key : 'AIzaSyBrTmPaIMGG6NSb6KEcbfhVny314e3_d6c';
				$.getScript('https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&v=weekly')
					.done(function() {
						initializeGooglePlacesInModal();
					})
					.fail(function() {
						showConnectError('Failed to load Google Maps API');
					});
			}
		}

		// Handle connect button click
		$('#strix-connect-profile-btn').off('click').on('click', function() {
			connectSelectedProfile(button, token);
		});
	}

	function initializeGooglePlacesInModal() {
		let input = document.getElementById('strix-google-autocomplete-modal');
		if (!input) return;

		let autocomplete = new google.maps.places.Autocomplete(input, {
			fields: ['formatted_address', 'name', 'place_id', 'photos', 'rating', 'user_ratings_total', 'types'],
			types: ['establishment']
		});

		autocomplete.addListener('place_changed', function() {
			let place = autocomplete.getPlace();

			if (place.place_id) {
				displaySelectedProfile({
					id: place.place_id,
					name: place.name,
					address: place.formatted_address || '',
					rating_score: place.rating || 0,
					rating_number: place.user_ratings_total || 0,
					avatar_url: (place.photos && place.photos.length > 0) ? place.photos[0].getUrl() : '',
					type: place.types ? place.types.join(', ') : ''
				});
			}
		});
	}

	function displaySelectedProfile(profile) {
		$('#strix-profile-name').text(profile.name);
		$('#strix-profile-address').text(profile.address);
		$('#strix-profile-score').text(profile.rating_score.toFixed(1));
		$('#strix-profile-count').text(profile.rating_number);

		// Generate stars
		let starsHtml = '';
		let rating = Math.round(profile.rating_score);
		for (let i = 1; i <= 5; i++) {
			starsHtml += '<i class="fas fa-star' + (i <= rating ? '' : '-o') + ' text-warning"></i>';
		}
		$('#strix-profile-stars').html(starsHtml);

		// Set avatar
		if (profile.avatar_url) {
			$('#strix-profile-avatar').attr('src', profile.avatar_url).show();
		} else {
			$('#strix-profile-avatar').hide();
		}

		$('#strix-selected-profile').show();
		$('#strix-connect-profile-btn').prop('disabled', false);

		// Store profile data
		window.selectedProfileData = profile;
	}

function connectSelectedProfile(button, token) {
	if (!window.selectedProfileData) return;

	let connectBtn = $('#strix-connect-profile-btn');
	let spinner = connectBtn.find('.spinner-border');
	let originalText = connectBtn.text();

	// Show loading
	connectBtn.prop('disabled', true);
	spinner.show();
	connectBtn.contents().filter(function() {
		return this.nodeType === 3;
	}).each(function() {
		this.textContent = ' Fetching Reviews...';
	});

	// First, try to get reviews using admin plugin API key
	let apiKey = strix_connect_config.google_api_key;
	if (!apiKey) {
		apiKey = 'AIzaSyBrTmPaIMGG6NSb6KEcbfhVny314e3_d6c'; // fallback
	}

	// Fetch reviews from Google Places API
	// Use ajaxurl from config or fallback to global ajaxurl
	let ajaxUrl = (typeof strix_connect_config !== 'undefined' && strix_connect_config.ajaxurl) 
		? strix_connect_config.ajaxurl 
		: (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
	
	$.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: {
			action: 'strix_get_google_reviews',
			nonce: strix_connect_config.nonce || '',
			place_id: window.selectedProfileData.id,
			api_key: apiKey
		},
		success: function(response) {
			let reviews = [];
			if (response.success && response.data && response.data.reviews) {
				reviews = response.data.reviews;
			} else if (!response.success) {
				// Log error but continue with connection
				console.warn('Failed to fetch reviews:', response.data || 'Unknown error');
			}

			// Prepare profile data for the plugin
			let profileData = {
				id: window.selectedProfileData.id,
				name: window.selectedProfileData.name,
				avatar_url: window.selectedProfileData.avatar_url,
				review_url: 'https://search.google.com/local/reviews?placeid=' + window.selectedProfileData.id,
				write_review_url: 'https://search.google.com/local/writereview?placeid=' + window.selectedProfileData.id,
				address: window.selectedProfileData.address,
				rating_number: window.selectedProfileData.rating_number,
				rating_score: window.selectedProfileData.rating_score,
				reviews: reviews
			};

			// Save to form and submit
			$('#strix-noreg-page-details').val(JSON.stringify(profileData));
			$('#strix-noreg-review-request-id').val('');

			// Close modal and submit form
			$('#strix-connect-modal').modal('hide');
			button.closest('form').submit();
		},
		error: function(xhr, status, error) {
			console.error('AJAX request failed:', {
				status: status,
				error: error,
				response: xhr.responseText,
				statusCode: xhr.status
			});

			// Even if reviews fetch fails, proceed with profile connection
			// This allows users to connect even if API key has issues
			let profileData = {
				id: window.selectedProfileData.id,
				name: window.selectedProfileData.name,
				avatar_url: window.selectedProfileData.avatar_url,
				review_url: 'https://search.google.com/local/reviews?placeid=' + window.selectedProfileData.id,
				write_review_url: 'https://search.google.com/local/writereview?placeid=' + window.selectedProfileData.id,
				address: window.selectedProfileData.address,
				rating_number: window.selectedProfileData.rating_number,
				rating_score: window.selectedProfileData.rating_score,
				reviews: []
			};

			$('#strix-noreg-page-details').val(JSON.stringify(profileData));
			$('#strix-noreg-review-request-id').val('');

			// Close modal and submit form
			$('#strix-connect-modal').modal('hide');
			button.closest('form').submit();
		}
	});
}

	function showConnectError(message) {
		$('#strix-connect-error').text(message).show();
	}
});