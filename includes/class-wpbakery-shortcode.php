<?php
/**
 * WP Bakery / Visual Composer Integration
 */
if (!defined('ABSPATH')) {
    exit;
}

// Check if WPBakery is installed
if (!defined('WPB_VC_VERSION')) {
    return;
}

// Add custom shortcode to Visual Composer
add_action('vc_before_init', 'strix_google_reviews_vc_map');

function strix_google_reviews_vc_map() {
    vc_map(array(
        'name' => __('Google Reviews', 'strix-google-reviews'),
        'base' => 'strix_google_reviews',
        'icon' => 'icon-wpb-ui-separator',
        'category' => __('Content', 'strix-google-reviews'),
        'description' => __('Display Google Business Profile Reviews', 'strix-google-reviews'),
        'params' => array(
            array(
                'type' => 'dropdown',
                'heading' => __('Data Source', 'strix-google-reviews'),
                'param_name' => 'data_source',
                'value' => array(
                    __('Google Business Profile', 'strix-google-reviews') => 'google',
                    __('Custom Reviews', 'strix-google-reviews') => 'custom',
                    __('Facebook Reviews', 'strix-google-reviews') => 'facebook',
                    __('Yelp Reviews', 'strix-google-reviews') => 'yelp',
                ),
                'std' => 'google',
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Google Account ID', 'strix-google-reviews'),
                'param_name' => 'account_id',
                'dependency' => array(
                    'element' => 'data_source',
                    'value' => 'google',
                ),
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Google Location ID', 'strix-google-reviews'),
                'param_name' => 'location_id',
                'dependency' => array(
                    'element' => 'data_source',
                    'value' => 'google',
                ),
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Layout', 'strix-google-reviews'),
                'param_name' => 'layout',
                'value' => array(
                    __('List', 'strix-google-reviews') => 'list',
                    __('Grid', 'strix-google-reviews') => 'grid',
                    __('Slider', 'strix-google-reviews') => 'slider',
                    __('Masonry Grid', 'strix-google-reviews') => 'masonry',
                    __('Badge', 'strix-google-reviews') => 'badge',
                    __('Popup', 'strix-google-reviews') => 'popup',
                ),
                'std' => 'list',
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Layout Style', 'strix-google-reviews'),
                'param_name' => 'layout_style',
                'value' => array(
                    __('Style 1', 'strix-google-reviews') => '1',
                    __('Style 2', 'strix-google-reviews') => '2',
                    __('Style 3', 'strix-google-reviews') => '3',
                    __('Style 4', 'strix-google-reviews') => '4',
                    __('Style 5', 'strix-google-reviews') => '5',
                ),
                'std' => '1',
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Number of Reviews', 'strix-google-reviews'),
                'param_name' => 'limit',
                'value' => '5',
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Minimum Rating', 'strix-google-reviews'),
                'param_name' => 'filter_rating',
                'value' => array(
                    __('All Ratings', 'strix-google-reviews') => '',
                    __('2+ Stars', 'strix-google-reviews') => '2',
                    __('3+ Stars', 'strix-google-reviews') => '3',
                    __('4+ Stars', 'strix-google-reviews') => '4',
                    __('5 Stars Only', 'strix-google-reviews') => '5',
                ),
                'std' => '',
            ),
            array(
                'type' => 'dropdown',
                'heading' => __('Sort By', 'strix-google-reviews'),
                'param_name' => 'sort_by',
                'value' => array(
                    __('Newest First', 'strix-google-reviews') => 'newest',
                    __('Oldest First', 'strix-google-reviews') => 'oldest',
                    __('Highest Rating', 'strix-google-reviews') => 'highest',
                    __('Lowest Rating', 'strix-google-reviews') => 'lowest',
                ),
                'std' => 'newest',
            ),
        ),
    ));
}
