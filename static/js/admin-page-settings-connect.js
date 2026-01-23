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
		
		// Remove loading class when modal is shown (modal handles its own state)
		setTimeout(function() {
			button.removeClass('ti-btn-loading');
		}, 100);
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
		hideConnectError();
		$('#strix-google-autocomplete-modal').val('');
		$('#strix-connect-profile-btn').prop('disabled', true);
		window.selectedProfileData = null;
		
		// Reset API key field to default if empty
		let apiKeyInput = $('#strix-google-api-key-modal');
		if (!apiKeyInput.val() && strix_connect_config && strix_connect_config.google_api_key) {
			apiKeyInput.val(strix_connect_config.google_api_key);
		}

		// Show modal
		$('#strix-connect-modal').modal('show');

		// Initialize autocomplete when API key is entered
		apiKeyInput.on('input', function() {
			let apiKey = $(this).val().trim();
			if (apiKey) {
				loadGoogleMapsAPI(apiKey);
			}
		});

		// Try to initialize with current API key
		let currentApiKey = apiKeyInput.val().trim();
		if (currentApiKey) {
			loadGoogleMapsAPI(currentApiKey);
		} else {
			// Use default API key from config
			let defaultApiKey = strix_connect_config && strix_connect_config.google_api_key 
				? strix_connect_config.google_api_key 
				: 'AIzaSyBrTmPaIMGG6NSb6KEcbfhVny314e3_d6c';
			loadGoogleMapsAPI(defaultApiKey);
		}

		// Handle connect button click
		$('#strix-connect-profile-btn').off('click').on('click', function() {
			connectSelectedProfile(button, token);
		});
	}

	function loadGoogleMapsAPI(apiKey) {
		// Check if API is already loaded with this key
		if (typeof google !== 'undefined' && google.maps && google.maps.places) {
			// API already loaded, just initialize autocomplete
			initializeGooglePlacesInModal(apiKey);
			return;
		}

		// Remove old script if exists
		$('script[src*="maps.googleapis.com"]').remove();
		window.googleMapsApiLoaded = false;

		// Load Google Maps API dynamically
		$.getScript('https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&v=weekly')
			.done(function() {
				window.googleMapsApiLoaded = true;
				initializeGooglePlacesInModal(apiKey);
			})
			.fail(function() {
				showConnectError('Failed to load Google Maps API. Please check your API key.');
			});
	}

	function initializeGooglePlacesInModal(apiKey) {
		let input = document.getElementById('strix-google-autocomplete-modal');
		if (!input) return;

		// Destroy existing autocomplete if exists
		if (input.autocomplete) {
			google.maps.event.clearInstanceListeners(input);
		}

		let autocomplete = new google.maps.places.Autocomplete(input, {
			fields: ['formatted_address', 'name', 'place_id', 'photos', 'rating', 'user_ratings_total', 'types'],
			types: ['establishment']
		});

		// Store reference
		input.autocomplete = autocomplete;

		autocomplete.addListener('place_changed', function() {
			let place = autocomplete.getPlace();

			if (place.place_id) {
				displaySelectedProfile({
					id: place.place_id,
					name: place.name,
					address: place.formatted_address || '',
					rating_score: place.rating || 0,
					rating_number: place.user_ratings_total || 0,
					avatar_url: (place.photos && place.photos.length > 0) ? place.photos[0].getUrl({maxWidth: 400, maxHeight: 400}) : '',
					type: place.types ? place.types.join(', ') : ''
				});
			}
		});

		// Handle manual Place ID or URL input
		$(input).on('blur', function() {
			let value = $(this).val().trim();
			if (value && !value.includes(' ')) {
				// Might be a Place ID or URL
				let placeId = extractPlaceIdFromInput(value);
				if (placeId) {
					fetchPlaceDetails(placeId, apiKey);
				}
			}
		});
	}

	function extractPlaceIdFromInput(input) {
		// Extract Place ID from Google Maps URL
		let match = input.match(/place_id=([^&]+)/);
		if (match) {
			return match[1];
		}
		// Check if it's a direct Place ID (usually starts with ChIJ or similar)
		if (input.match(/^[A-Za-z0-9_-]{27,}$/)) {
			return input;
		}
		return null;
	}

	function fetchPlaceDetails(placeId, apiKey) {
		let service = new google.maps.places.PlacesService(document.createElement('div'));
		
		service.getDetails({
			placeId: placeId,
			fields: ['formatted_address', 'name', 'place_id', 'photos', 'rating', 'user_ratings_total', 'types']
		}, function(place, status) {
			if (status === google.maps.places.PlacesServiceStatus.OK && place) {
				displaySelectedProfile({
					id: place.place_id,
					name: place.name,
					address: place.formatted_address || '',
					rating_score: place.rating || 0,
					rating_number: place.user_ratings_total || 0,
					avatar_url: (place.photos && place.photos.length > 0) ? place.photos[0].getUrl({maxWidth: 400, maxHeight: 400}) : '',
					type: place.types ? place.types.join(', ') : ''
				});
				// Update input field
				$('#strix-google-autocomplete-modal').val(place.name);
			} else {
				showConnectError('Place not found. Please check your Place ID or try searching by name.');
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
		hideConnectError(); // Hide any previous errors

		// Store profile data
		window.selectedProfileData = profile;
	}

function connectSelectedProfile(button, token) {
	if (!window.selectedProfileData) {
		showConnectError('Please select a Google Business Profile first.');
		return;
	}

	// Get API key from input field
	let apiKey = $('#strix-google-api-key-modal').val().trim();
	if (!apiKey) {
		showConnectError('Please enter your Google Maps API Key.');
		return;
	}

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
		$('#strix-connect-error').text(message).show().fadeIn();
		// Scroll to error
		$('html, body').animate({
			scrollTop: $('#strix-connect-error').offset().top - 100
		}, 300);
	}

	function hideConnectError() {
		$('#strix-connect-error').hide();
	}
});