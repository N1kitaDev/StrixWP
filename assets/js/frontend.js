// Frontend JavaScript for Strix Google Reviews
jQuery(document).ready(function($) {
    'use strict';

    console.log('Strix Google Reviews Frontend JS loaded');

    // Initialize Swiper sliders
    initializeSwipers();

    // Add any frontend-specific JavaScript here
    // For example, animations, lazy loading, etc.

    // Smooth scroll for review links if needed
    $('.strix-google-reviews-shortcode a').on('click', function(e) {
        // Handle external links
        if ($(this).attr('target') === '_blank') {
            // Let default behavior work
        }
    });

    // Add hover effects
    $('.strix-review-item, .strix-custom-review-item').hover(
        function() {
            $(this).addClass('strix-review-hover');
        },
        function() {
            $(this).removeClass('strix-review-hover');
        }
    );

    // Review form submission
    $('#strix-review-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $form.find('.strix-submit-review');
        var $loading = $form.find('.strix-form-loading');
        var $message = $form.find('.strix-form-message');

        // Show loading
        $submitBtn.prop('disabled', true).hide();
        $loading.show();
        $message.hide();

        // Prepare form data
        var formData = new FormData(this);
        formData.append('action', 'strix_submit_review');
        formData.append('nonce', $('#strix_review_nonce').val());

        // Submit review
        $.ajax({
            url: strix_reviews_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $message.removeClass('error').addClass('success').html(response.data.message).show();
                    $form[0].reset();

                    // Reload reviews if we're on the same page
                    if ($('.strix-custom-reviews-list').length) {
                        $('.strix-custom-reviews-list').load(window.location.href + ' .strix-custom-reviews-list > *');
                    }
                } else {
                    var errors = response.data.errors.join('<br>');
                    $message.removeClass('success').addClass('error').html(errors).show();
                }
            },
            error: function() {
                $message.removeClass('success').addClass('error').html('<?php _e("Error submitting review. Please try again.", "strix-google-reviews"); ?>').show();
            },
            complete: function() {
                $submitBtn.prop('disabled', false).show();
                $loading.hide();
            }
        });
    });

    // Load more reviews
    $('.strix-load-more-reviews').on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $container = $('.strix-custom-reviews-list');
        var $loading = $('.strix-loading');
        var currentPage = parseInt($container.data('page'));
        var limit = parseInt($container.data('limit'));

        $btn.prop('disabled', true).hide();
        $loading.show();

        $.ajax({
            url: strix_reviews_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'strix_load_reviews',
                page: currentPage + 1,
                per_page: limit,
                nonce: strix_reviews_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $container.append(response.data.html);
                    $container.data('page', currentPage + 1);

                    if (!response.data.has_more) {
                        $btn.hide();
                    } else {
                        $btn.prop('disabled', false).show();
                    }
                } else {
                    alert(response.data.message || '<?php _e("Error loading reviews", "strix-google-reviews"); ?>');
                    $btn.prop('disabled', false).show();
                }
            },
            error: function() {
                alert('<?php _e("Error loading reviews. Please try again.", "strix-google-reviews"); ?>');
                $btn.prop('disabled', false).show();
            },
            complete: function() {
                $loading.hide();
            }
        });
    });

    // Star rating input handling
    $('.strix-rating-input input[type="radio"]').on('change', function() {
        var $rating = $(this).closest('.strix-rating-input');
        var value = $(this).val();

        // Remove all active classes
        $rating.find('label').removeClass('active');

        // Add active class to selected and previous stars
        for (var i = 1; i <= value; i++) {
            $rating.find('label[for="star' + i + '"]').addClass('active');
        }
    });

    // Swiper slider configurations and functions
    var sliderConfigs = {
        ".strix-slider-1": {
            slidesPerView: 1,
            slidesPerGroup: 1,
            breakpoints: {
                1200: { slidesPerView: 3, slidesPerGroup: 3 },
                768: { slidesPerView: 2, slidesPerGroup: 2 },
                480: { slidesPerView: 1, slidesPerGroup: 1 }
            }
        },
        ".strix-slider-2": {
            slidesPerView: 1,
            slidesPerGroup: 1,
            breakpoints: {
                1200: { slidesPerView: 3, slidesPerGroup: 3 },
                768: { slidesPerView: 2, slidesPerGroup: 2 },
                480: { slidesPerView: 1, slidesPerGroup: 1 }
            }
        },
        ".strix-slider-3": {
            slidesPerView: 1,
            slidesPerGroup: 1,
            breakpoints: {
                1200: { slidesPerView: 2, slidesPerGroup: 2 },
                480: { slidesPerView: 1, slidesPerGroup: 1 }
            }
        },
        ".strix-slider-4": { slidesPerView: 1, slidesPerGroup: 1 },
        ".strix-slider-5": {
            slidesPerView: 1,
            slidesPerGroup: 1,
            breakpoints: {
                1200: { slidesPerView: 2, slidesPerGroup: 2 },
                480: { slidesPerView: 1, slidesPerGroup: 1 }
            }
        },
        ".strix-slider-6": {
            slidesPerView: 1,
            slidesPerGroup: 1,
            breakpoints: {
                1200: { slidesPerView: 3, slidesPerGroup: 3 },
                768: { slidesPerView: 2, slidesPerGroup: 2 },
                480: { slidesPerView: 1, slidesPerGroup: 1 }
            }
        }
    };

    // Store Swiper instances
    var swiperInstances = {};

    function initializeSwipers() {
        Object.keys(sliderConfigs).forEach(function(selector) {
            var sliderElements = document.querySelectorAll(selector);

            if (sliderElements.length > 0) {
                sliderElements.forEach(function(sliderElement) {
                    var parentElement = sliderElement.parentElement;
                    var slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
                    var config = sliderConfigs[selector];
                    var minSlidesRequired = (config.slidesPerView || 1) + 1;
                    var enableLoop = slideCount >= minSlidesRequired;

                    swiperInstances[selector] = new Swiper(sliderElement, {
                        slidesPerView: config.slidesPerView,
                        slidesPerGroup: config.slidesPerGroup,
                        spaceBetween: 20,
                        loop: enableLoop,
                        navigation: {
                            nextEl: parentElement.querySelector(".swiper-button-next"),
                            prevEl: parentElement.querySelector(".swiper-button-prev"),
                        },
                        breakpoints: config.breakpoints || {},
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false,
                        },
                    });
                });
            }
        });
    }

    function reinitializeAllSwipers(container) {
        if (!(container instanceof HTMLElement)) {
            console.error('Invalid container element!', container);
            return;
        }

        Object.keys(sliderConfigs).forEach(function(selector) {
            var sliderElements = container.querySelectorAll(selector);

            sliderElements.forEach(function(sliderElement) {
                var slideCount = sliderElement.querySelectorAll('.swiper-slide').length;
                var config = sliderConfigs[selector];
                var minSlidesRequired = (config.slidesPerView || 1) + 1;
                var enableLoop = slideCount >= minSlidesRequired;

                if (swiperInstances[selector]) {
                    swiperInstances[selector].destroy(true, true);
                }

                swiperInstances[selector] = new Swiper(sliderElement, {
                    slidesPerView: config.slidesPerView,
                    slidesPerGroup: config.slidesPerGroup,
                    spaceBetween: 20,
                    loop: enableLoop,
                    navigation: {
                        nextEl: sliderElement.closest(".strix-reviews-slider").querySelector(".swiper-button-next"),
                        prevEl: sliderElement.closest(".strix-reviews-slider").querySelector(".swiper-button-prev"),
                    },
                    breakpoints: config.breakpoints || {},
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                });
            });
        });
    }

    // Popup functionality
    $('.strix-popup-btn').on('click', function() {
        var popupId = $(this).data('popup');
        $('#' + popupId).addClass('active');
        $('body').addClass('strix-popup-open');
    });

    $('.strix-popup-close, .strix-popup-overlay').on('click', function() {
        $('.strix-popup-modal').removeClass('active');
        $('body').removeClass('strix-popup-open');
    });

    // Close popup on ESC key
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && $('.strix-popup-modal.active').length) {
            $('.strix-popup-modal').removeClass('active');
            $('body').removeClass('strix-popup-open');
        }
    });

    // Read more functionality
    $('.strix-read-more').on('click', function(e) {
        e.preventDefault();

        var $link = $(this);
        var reviewId = $link.data('review-id');
        var $content = $('#' + reviewId);
        var $preview = $content.find('.strix-review-text-preview');
        var $full = $content.find('.strix-review-text-full');

        if ($full.is(':visible')) {
            $full.hide();
            $preview.show();
            $link.text('<?php _e("Read more", "strix-google-reviews"); ?>');
        } else {
            $preview.hide();
            $full.show();
            $link.text('<?php _e("Read less", "strix-google-reviews"); ?>');
        }
    });

    // Initialize Masonry Grid
    function initializeMasonry() {
        var masonryContainers = document.querySelectorAll('.strix-reviews-masonry');
        
        masonryContainers.forEach(function(container) {
            // Use CSS columns for masonry effect
            // JavaScript can be used for more advanced masonry if needed
            var items = container.querySelectorAll('.strix-masonry-item');
            
            // Add animation on load
            items.forEach(function(item, index) {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(function() {
                    item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    }
    
    // Initialize masonry on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMasonry);
    } else {
        initializeMasonry();
    }
    
    // Reinitialize masonry after AJAX loads
    $(document).on('strix_reviews_loaded', function() {
        initializeMasonry();
    });

    // Track review views
    function trackReviewViews() {
        var widgets = document.querySelectorAll('.strix-google-reviews-shortcode, .strix-google-reviews-widget');
        widgets.forEach(function(widget) {
            var widgetId = widget.getAttribute('data-widget-id') || 'default';
            
            // Track view once per page load
            if (!widget.hasAttribute('data-view-tracked')) {
                $.ajax({
                    url: strix_reviews_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'strix_track_view',
                        widget_id: widgetId,
                        nonce: strix_reviews_ajax.nonce
                    }
                });
                
                widget.setAttribute('data-view-tracked', 'true');
            }
        });
    }
    
    // Track views on page load
    trackReviewViews();
    
    // Track views after AJAX loads
    $(document).on('strix_reviews_loaded', function() {
        trackReviewViews();
    });

    // Expose functions globally for AJAX calls
    window.initializeSwipers = initializeSwipers;
    window.reinitializeAllSwipers = reinitializeAllSwipers;
    window.initializeMasonry = initializeMasonry;
    window.trackReviewViews = trackReviewViews;
});