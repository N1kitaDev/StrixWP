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
    $('.strix-review-item').hover(
        function() {
            $(this).addClass('strix-review-hover');
        },
        function() {
            $(this).removeClass('strix-review-hover');
        }
    );
});