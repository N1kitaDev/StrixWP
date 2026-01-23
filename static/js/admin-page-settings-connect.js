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

			// Always use modal with Google Places API integration
			openGoogleConnectModal(button, token);
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

		// Disable browser validation for all inputs in the form
		$('#strix-google-connect-form input').each(function() {
			$(this).removeAttr('required');
			$(this).removeAttr('pattern');
			$(this).removeAttr('min');
			$(this).removeAttr('max');
			$(this).removeAttr('minlength');
			$(this).removeAttr('maxlength');
		});
		
		// Reset API key field to default if empty
		let apiKeyInput = $('#strix-google-api-key-modal');
		let config = (typeof strix_connect_config !== 'undefined') ? strix_connect_config : {};
		if (!apiKeyInput.val() && config.google_api_key) {
			apiKeyInput.val(config.google_api_key);
		}

		// Show modal
		$('#strix-connect-modal').modal('show');

		// Initialize autocomplete when API key is entered or changed
		let currentApiKey = apiKeyInput.val().trim();
		if (!currentApiKey && config.google_api_key) {
			currentApiKey = config.google_api_key;
			apiKeyInput.val(currentApiKey);
		}

		// Initialize autocomplete when key is available
		if (currentApiKey) {
			initializeServerSideAutocomplete(currentApiKey);
		}

		// Reinitialize when API key changes
		apiKeyInput.on('input change', function() {
			let newApiKey = $(this).val().trim();
			if (newApiKey && newApiKey !== currentApiKey) {
				currentApiKey = newApiKey;
				$('#strix-selected-profile').hide();
				window.selectedProfileData = null;
				$('#strix-connect-profile-btn').prop('disabled', true);
				// Reinitialize autocomplete with new key
				initializeServerSideAutocomplete(newApiKey);
			}
		});

		// Handle connect button click
		$('#strix-connect-profile-btn').off('click').on('click', function() {
			connectSelectedProfile(button, token);
		});
	}

	function initializeServerSideAutocomplete(apiKey) {
		let input = $('#strix-google-autocomplete-modal');
		if (!input.length) return;

		// Disable browser validation for this input
		input.removeAttr('required');
		input.removeAttr('pattern');
		input.removeAttr('min');
		input.removeAttr('max');
		input.removeAttr('minlength');
		input.removeAttr('maxlength');

		// Clear any existing autocomplete
		if (input.data('autocomplete')) {
			input.autocomplete('destroy');
		}

		// Create simple autocomplete dropdown
		let autocompleteContainer = $('<div class="strix-autocomplete-dropdown"></div>').css({
			position: 'absolute',
			background: '#fff',
			border: '1px solid #ddd',
			borderRadius: '4px',
			maxHeight: '300px',
			overflowY: 'auto',
			zIndex: 1000,
			display: 'none',
			width: input.outerWidth()
		});
		
		// Remove existing dropdown if any
		$('.strix-autocomplete-dropdown').remove();
		input.after(autocompleteContainer);

		let searchTimeout;
		let selectedIndex = -1;

		input.on('input', function() {
			let query = $(this).val().trim();
			
			clearTimeout(searchTimeout);
			
			if (query.length < 2) {
				autocompleteContainer.hide().empty();
				$('#strix-selected-profile').hide();
				window.selectedProfileData = null;
				$('#strix-connect-profile-btn').prop('disabled', true);
				return;
			}

			// Check if it's a Google Maps URL
			if (query.includes('google.com/maps') || query.includes('maps.google.com')) {
				// It's a URL, process it immediately
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(function() {
					processGoogleMapsUrl(query, apiKey, autocompleteContainer, input);
				}, 500);
			} else {
				// Regular text search with debounce
				searchTimeout = setTimeout(function() {
					searchPlaces(query, apiKey, autocompleteContainer, input);
				}, 300);
			}
		});

		input.on('keydown', function(e) {
			let items = autocompleteContainer.find('.autocomplete-item');
			
			if (e.key === 'ArrowDown') {
				e.preventDefault();
				selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
				items.removeClass('active').eq(selectedIndex).addClass('active');
				autocompleteContainer.scrollTop(items.eq(selectedIndex).position().top + autocompleteContainer.scrollTop() - 50);
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				selectedIndex = Math.max(selectedIndex - 1, -1);
				items.removeClass('active');
				if (selectedIndex >= 0) {
					items.eq(selectedIndex).addClass('active');
					autocompleteContainer.scrollTop(items.eq(selectedIndex).position().top + autocompleteContainer.scrollTop() - 50);
				}
			} else if (e.key === 'Enter') {
				e.preventDefault();
				if (selectedIndex >= 0 && items.length > selectedIndex) {
					items.eq(selectedIndex).click();
				} else if (items.length === 1) {
					items.eq(0).click();
				} else {
					// Try to fetch by Place ID if it looks like one
					let value = $(this).val().trim();
					let placeId = extractPlaceIdFromInput(value);
					if (placeId) {
						fetchPlaceDetails(placeId, apiKey);
					}
				}
			} else if (e.key === 'Escape') {
				autocompleteContainer.hide();
			}
		});

		// Handle blur - but allow time for click
		input.on('blur', function() {
			setTimeout(function() {
				autocompleteContainer.hide();
			}, 200);
		});

		// Handle manual Place ID or URL input on blur
		input.on('blur', function() {
			let value = $(this).val().trim();
			if (value) {
				// Check if it's a Google Maps URL
				if (value.includes('google.com/maps') || value.includes('maps.google.com')) {
					// Try to extract Place ID first
					let placeId = extractPlaceIdFromInput(value);
					
					if (placeId && !placeId.match(/^0x[0-9a-fA-F]+:0x[0-9a-fA-F]+$/)) {
						// It's a new format Place ID, use it directly
						fetchPlaceDetails(placeId, apiKey);
					} else {
						// It's old format URL with CID, extract business name and search
						let businessName = extractBusinessNameFromUrl(value);
						if (businessName) {
							// Search by business name
							searchPlaces(businessName, apiKey, autocompleteContainer, input);
						} else {
							showConnectError('Could not extract business information from URL. Please enter the business name manually.');
						}
					}
				} else if (!value.includes(' ') && value.length > 10) {
					// Might be a direct Place ID
					let placeId = extractPlaceIdFromInput(value);
					if (placeId) {
						fetchPlaceDetails(placeId, apiKey);
					}
				}
			}
		});
	}

	// Track last search to prevent duplicate requests
	let lastSearchQuery = '';
	let lastSearchTime = 0;
	let isSearching = false;

	function searchPlaces(query, apiKey, container, input, autoSelectSingle) {
		autoSelectSingle = autoSelectSingle || false;
		
		// Prevent duplicate requests
		let now = Date.now();
		if (isSearching || (query === lastSearchQuery && (now - lastSearchTime) < 500)) {
			return;
		}
		
		isSearching = true;
		lastSearchQuery = query;
		lastSearchTime = now;
		
		container.html('<div class="autocomplete-item" style="padding: 10px; color: #999;">Searching...</div>').show();

		// Use ajaxurl from config or fallback
		let config = (typeof strix_connect_config !== 'undefined') ? strix_connect_config : {};
		let ajaxUrl = config.ajaxurl || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
		let nonce = config.nonce || '';

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'strix_search_google_places',
				nonce: nonce,
				query: query,
				api_key: apiKey
			},
			success: function(response) {
				isSearching = false;
				console.log('Search response:', response);
				
				if (response.success && response.data && response.data.places) {
					let places = response.data.places;
					console.log('Found places:', places.length);
					
					if (places.length === 0) {
						container.html('<div class="autocomplete-item" style="padding: 10px; color: #999;">No results found for "' + query + '"</div>');
						return;
					}
					
					// If auto-select is enabled and only one result, select it automatically
					if (autoSelectSingle && places.length === 1) {
						fetchPlaceDetails(places[0].place_id, apiKey);
						container.hide();
						input.val(places[0].name);
					} else {
						displaySearchResults(places, container, input, apiKey);
					}
				} else {
					let errorMsg = 'No results found';
					if (response.data) {
						errorMsg = typeof response.data === 'string' ? response.data : 'Search failed';
					}
					container.html('<div class="autocomplete-item" style="padding: 10px; color: #d00;">' + errorMsg + '</div>');
					console.error('Search failed:', response);
				}
			},
			error: function(xhr, status, error) {
				isSearching = false;
				let errorMsg = 'Search failed';
				if (xhr.status === 403) {
					errorMsg = 'Access denied. Please refresh the page and try again.';
				} else if (xhr.status === 0) {
					errorMsg = 'Network error. Please check your connection.';
				} else if (xhr.responseJSON && xhr.responseJSON.data) {
					errorMsg = xhr.responseJSON.data;
				}
				container.html('<div class="autocomplete-item" style="padding: 10px; color: #d00;">' + errorMsg + '</div>');
				console.error('Search error:', {
					status: xhr.status,
					statusText: xhr.statusText,
					error: error,
					response: xhr.responseText,
					responseJSON: xhr.responseJSON
				});
			}
		});
	}

	function displaySearchResults(places, container, input, apiKey) {
		container.empty();
		
		if (places.length === 0) {
			container.html('<div class="autocomplete-item" style="padding: 10px; color: #999;">No results found</div>');
			return;
		}

		places.forEach(function(place) {
			let item = $('<div class="autocomplete-item" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;"></div>');
			item.html('<strong>' + place.name + '</strong><br><small style="color: #666;">' + place.formatted_address + '</small>');
			
			item.on('mouseenter', function() {
				$(this).css('background', '#f5f5f5');
			}).on('mouseleave', function() {
				$(this).css('background', '');
			}).on('click', function() {
				fetchPlaceDetails(place.place_id, apiKey);
				container.hide();
				input.val(place.name);
			});
			
			container.append(item);
		});
		
		container.show();
	}

	function extractPlaceIdFromInput(input) {
		if (!input) return null;
		
		// 1. Extract Place ID from new Google Maps URL format: place_id=ChIJ...
		let match = input.match(/[?&]place_id=([^&]+)/);
		if (match) {
			return decodeURIComponent(match[1]);
		}
		
		// 2. Check if it's a direct Place ID (usually starts with ChIJ or similar, 27+ chars)
		if (input.match(/^[A-Za-z0-9_-]{27,}$/)) {
			return input;
		}
		
		return null;
	}

	function extractBusinessNameFromUrl(url) {
		if (!url) return null;
		
		// Extract business name from Google Maps URL
		// Format: /maps/place/Business+Name/@lat,lng
		let match = url.match(/\/place\/([^\/@]+)/);
		if (match) {
			let name = decodeURIComponent(match[1].replace(/\+/g, ' '));
			// Clean up the name (remove extra encoding)
			name = name.replace(/%2B/g, '+').replace(/%20/g, ' ');
			return name;
		}
		
		// Try alternative format: /maps/place/Business+Name
		match = url.match(/place\/([^\/\?@]+)/);
		if (match) {
			let name = decodeURIComponent(match[1].replace(/\+/g, ' '));
			name = name.replace(/%2B/g, '+').replace(/%20/g, ' ');
			return name;
		}
		
		return null;
	}

	function processGoogleMapsUrl(url, apiKey, container, input) {
		// Try to extract Place ID first
		let placeId = extractPlaceIdFromInput(url);
		
		if (placeId && !placeId.match(/^0x[0-9a-fA-F]+:0x[0-9a-fA-F]+$/)) {
			// It's a new format Place ID, fetch directly
			fetchPlaceDetails(placeId, apiKey);
		} else {
			// It's old format URL with CID, extract business name and search
			let businessName = extractBusinessNameFromUrl(url);
			if (businessName) {
				// Search by business name - if only one result, auto-select it
				searchPlaces(businessName, apiKey, container, input, true);
			} else {
				showConnectError('Could not extract business information from URL. Please enter the business name manually.');
			}
		}
	}

	function searchPlacesByCid(cid, apiKey) {
		// For old CID format, we need to search by the business name from URL
		// Extract business name from URL if possible
		let input = $('#strix-google-autocomplete-modal');
		let value = input.val().trim();
		
		// Try to extract business name from URL
		let nameMatch = value.match(/\/place\/([^\/@]+)/);
		if (nameMatch) {
			let businessName = decodeURIComponent(nameMatch[1].replace(/\+/g, ' '));
			searchPlaces(businessName, apiKey, $('.strix-autocomplete-dropdown'), input);
		} else {
			// If we can't extract name, try to use CID directly (though this might not work)
			showConnectError('Please enter the business name or use a Place ID instead of the old URL format.');
		}
	}

	function fetchPlaceDetails(placeId, apiKey) {
		// Get current API key from input if not provided
		if (!apiKey) {
			apiKey = $('#strix-google-api-key-modal').val().trim();
		}
		
		if (!apiKey) {
			showConnectError('Please enter your Google Maps API Key first.');
			return;
		}

		// Use ajaxurl from config or fallback
		let config = (typeof strix_connect_config !== 'undefined') ? strix_connect_config : {};
		let ajaxUrl = config.ajaxurl || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
		let nonce = config.nonce || '';

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'strix_get_place_details',
				nonce: nonce,
				place_id: placeId,
				api_key: apiKey
			},
			success: function(response) {
				if (response.success && response.data) {
					let place = response.data;
					displaySelectedProfile({
						id: place.place_id,
						name: place.name,
						address: place.formatted_address || '',
						rating_score: place.rating || 0,
						rating_number: place.user_ratings_total || 0,
						avatar_url: place.photo_url || '',
						type: place.types || (place.primary_type ? [place.primary_type] : [])
					});
					// Update input field
					$('#strix-google-autocomplete-modal').val(place.name);
				} else {
					showConnectError(response.data || 'Place not found. Please check your Place ID or try searching by name.');
				}
			},
			error: function(xhr) {
				let errorMsg = 'Failed to get place details. ';
				if (xhr.responseJSON && xhr.responseJSON.data) {
					errorMsg = xhr.responseJSON.data;
				}
				showConnectError(errorMsg);
			}
		});
	}

	function displaySelectedProfile(profile) {
		$('#strix-profile-name').text(profile.name || '');
		$('#strix-profile-address').text(profile.address || '');
		$('#strix-profile-score').text(profile.rating_score ? profile.rating_score.toFixed(1) : '0.0');
		$('#strix-profile-count').text(profile.rating_number || 0);

		// Generate stars
		let starsHtml = '';
		let rating = profile.rating_score ? Math.round(profile.rating_score) : 0;
		for (let i = 1; i <= 5; i++) {
			starsHtml += '<i class="fas fa-star' + (i <= rating ? '' : '-o') + '" style="color: #f39c12;"></i>';
		}
		$('#strix-profile-stars').html(starsHtml);

		// Set category/type
		if (profile.type && profile.type.length > 0) {
			// Get the first meaningful type (skip generic types like 'establishment', 'point_of_interest')
			let types = Array.isArray(profile.type) ? profile.type : profile.type.split(',');
			let meaningfulTypes = types.filter(t => {
				t = t.trim();
				return t && !['establishment', 'point_of_interest', 'store', 'business'].includes(t);
			});
			let displayType = meaningfulTypes.length > 0 ? meaningfulTypes[0] : types[0];
			
			// Format type name (capitalize and replace underscores)
			displayType = displayType.trim().replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
			$('#strix-profile-category').html('<span style="display: inline-block; background: #f0f0f0; color: #666; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500;">' + displayType + '</span>');
		} else {
			$('#strix-profile-category').html('');
		}

		// Set avatar
		if (profile.avatar_url) {
			$('#strix-profile-avatar').attr('src', profile.avatar_url).show();
		} else {
			// Use default placeholder
			$('#strix-profile-avatar').attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik00MCAyMEMzMy4zNzI2IDIwIDI4IDI1LjM3MjYgMjggMzJDMjggMzguNjI3NCAzMy4zNzI2IDQ0IDQwIDQ0QzQ2LjYyNzQgNDQgNTIgMzguNjI3NCA1MiAzMkM1MiAyNS4zNzI2IDQ2LjYyNzQgMjAgNDAgMjBaIiBmaWxsPSIjQ0NDQ0NDIi8+CjxwYXRoIGQ9Ik0yMCA1NkMyMCA1MS41ODE3IDIzLjU4MTcgNDggMjggNDhINTJDNTYuNDE4MyA0OCA2MCA1MS41ODE3IDYwIDU2VjYwSDIwVjU2WiIgZmlsbD0iI0NDQ0NDQyIvPgo8L3N2Zz4K').show();
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
	let config = (typeof strix_connect_config !== 'undefined') ? strix_connect_config : {};
	let ajaxUrl = config.ajaxurl || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
	let nonce = config.nonce || '';
	
	$.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: {
			action: 'strix_get_google_reviews',
			nonce: nonce,
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