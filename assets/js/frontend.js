// Frontend JavaScript for Strix Google Reviews
jQuery(document).ready(function($) {
    'use strict';

    console.log('Strix Google Reviews Frontend JS loaded');

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
});