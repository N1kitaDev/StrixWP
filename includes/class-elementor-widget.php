<?php
/**
 * Elementor Widget Integration
 */
if (!defined('ABSPATH')) {
    exit;
}

// Check if Elementor is installed and activated
// Don't load if Elementor is not available
if (!did_action('elementor/loaded')) {
    return;
}

// Check if Elementor classes exist before using them
if (!class_exists('\Elementor\Widget_Base') || !class_exists('\Elementor\Controls_Manager')) {
    return;
}

// Use fully qualified names to avoid issues
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Strix_Google_Reviews_Elementor_Widget extends Widget_Base {
    
    public function get_name() {
        return 'strix_google_reviews';
    }
    
    public function get_title() {
        return __('Google Reviews', 'strix-google-reviews');
    }
    
    public function get_icon() {
        return 'eicon-star';
    }
    
    public function get_categories() {
        return ['general'];
    }
    
    protected function _register_controls() {
        // Data Source Section
        $this->start_controls_section(
            'data_source_section',
            [
                'label' => __('Data Source', 'strix-google-reviews'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'data_source',
            [
                'label' => __('Review Source', 'strix-google-reviews'),
                'type' => Controls_Manager::SELECT,
                'default' => 'google',
                'options' => [
                    'google' => __('Google Business Profile', 'strix-google-reviews'),
                    'custom' => __('Custom Reviews', 'strix-google-reviews'),
                    'facebook' => __('Facebook Reviews', 'strix-google-reviews'),
                    'yelp' => __('Yelp Reviews', 'strix-google-reviews'),
                ],
            ]
        );
        
        $this->add_control(
            'account_id',
            [
                'label' => __('Google Account ID', 'strix-google-reviews'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'data_source' => 'google',
                ],
            ]
        );
        
        $this->add_control(
            'location_id',
            [
                'label' => __('Google Location ID', 'strix-google-reviews'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'data_source' => 'google',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'strix-google-reviews'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'layout',
            [
                'label' => __('Layout Type', 'strix-google-reviews'),
                'type' => Controls_Manager::SELECT,
                'default' => 'list',
                'options' => [
                    'list' => __('List', 'strix-google-reviews'),
                    'grid' => __('Grid', 'strix-google-reviews'),
                    'slider' => __('Slider', 'strix-google-reviews'),
                    'masonry' => __('Masonry Grid', 'strix-google-reviews'),
                    'badge' => __('Badge', 'strix-google-reviews'),
                    'popup' => __('Popup', 'strix-google-reviews'),
                ],
            ]
        );
        
        $this->add_control(
            'layout_style',
            [
                'label' => __('Layout Style', 'strix-google-reviews'),
                'type' => Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => __('Style 1', 'strix-google-reviews'),
                    '2' => __('Style 2', 'strix-google-reviews'),
                    '3' => __('Style 3', 'strix-google-reviews'),
                    '4' => __('Style 4', 'strix-google-reviews'),
                    '5' => __('Style 5', 'strix-google-reviews'),
                ],
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Number of Reviews', 'strix-google-reviews'),
                'type' => Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 50,
            ]
        );
        
        $this->end_controls_section();
        
        // Filter Section
        $this->start_controls_section(
            'filter_section',
            [
                'label' => __('Filters & Sorting', 'strix-google-reviews'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'filter_rating',
            [
                'label' => __('Minimum Rating', 'strix-google-reviews'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('All Ratings', 'strix-google-reviews'),
                    '2' => __('2+ Stars', 'strix-google-reviews'),
                    '3' => __('3+ Stars', 'strix-google-reviews'),
                    '4' => __('4+ Stars', 'strix-google-reviews'),
                    '5' => __('5 Stars Only', 'strix-google-reviews'),
                ],
            ]
        );
        
        $this->add_control(
            'sort_by',
            [
                'label' => __('Sort By', 'strix-google-reviews'),
                'type' => Controls_Manager::SELECT,
                'default' => 'newest',
                'options' => [
                    'newest' => __('Newest First', 'strix-google-reviews'),
                    'oldest' => __('Oldest First', 'strix-google-reviews'),
                    'highest' => __('Highest Rating', 'strix-google-reviews'),
                    'lowest' => __('Lowest Rating', 'strix-google-reviews'),
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array(
            'account_id' => $settings['account_id'] ?? '',
            'location_id' => $settings['location_id'] ?? '',
            'layout' => $settings['layout'] ?? 'list',
            'layout_style' => $settings['layout_style'] ?? '1',
            'limit' => $settings['limit'] ?? 5,
            'filter_rating' => $settings['filter_rating'] ?? '',
            'sort_by' => $settings['sort_by'] ?? 'newest',
        );
        
        $plugin = Strix_Google_Reviews::get_instance();
        echo $plugin->render_shortcode($shortcode_atts);
    }
}

// Register Elementor Widget - only if class exists
if (class_exists('Strix_Google_Reviews_Elementor_Widget')) {
    add_action('elementor/widgets/register', function($widgets_manager) {
        if (class_exists('Strix_Google_Reviews_Elementor_Widget')) {
            $widgets_manager->register(new Strix_Google_Reviews_Elementor_Widget());
        }
    });
}
