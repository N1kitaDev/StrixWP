<?php
/**
 * Plugin Name: Strix Google Reviews
 * Plugin URI: https://strixmedia.ru
 * Description: Clean Google Business Profile Reviews plugin for WordPress
 * Version: 1.0.0
 * Author: Strix Media
 * Text Domain: strix-google-reviews
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 */
class Strix_Google_Reviews {

    /**
     * Plugin version
     */
    const VERSION = '1.0.0';

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('widgets_init', array($this, 'register_widgets'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('init', array($this, 'register_custom_post_type'));
        add_action('init', array($this, 'register_widget_post_type'));
        add_action('add_meta_boxes', array($this, 'strix_add_widget_meta_box'));
        add_action('save_post', array($this, 'strix_save_widget_meta'));

        add_filter('manage_strix_widget_posts_columns', array($this, 'strix_widget_columns'));
        add_action('manage_strix_widget_posts_custom_column', array($this, 'strix_widget_custom_column'), 10, 2);
        add_action('wp_ajax_strix_refresh_reviews', array($this, 'ajax_refresh_reviews'));
        add_action('wp_ajax_nopriv_strix_refresh_reviews', array($this, 'ajax_refresh_reviews'));
        add_action('wp_ajax_strix_submit_review', array($this, 'ajax_submit_review'));
        add_action('wp_ajax_nopriv_strix_submit_review', array($this, 'ajax_submit_review'));
        add_action('wp_ajax_strix_load_reviews', array($this, 'ajax_load_reviews'));
        add_action('wp_ajax_nopriv_strix_load_reviews', array($this, 'ajax_load_reviews'));

        // Include required files
        $this->includes();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-widget.php';
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_api_key');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_account_id');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_location_id');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_company_name');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_website');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_phone');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_filter_5_star');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_cache_timeout');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_demo_mode');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_auto_approve');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_require_name');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_require_email');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_form');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_review_button');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_review_button_text');

        add_settings_section(
            'strix_google_reviews_main',
            __('Google Business Profile API Settings', 'strix-google-reviews'),
            array($this, 'settings_section_callback'),
            'strix-google-reviews'
        );

        add_settings_field(
            'strix_google_reviews_api_key',
            __('Google Places API Key', 'strix-google-reviews'),
            array($this, 'api_key_field_callback'),
            'strix-google-reviews',
            'strix_google_reviews_main'
        );

        add_settings_field(
            'strix_google_reviews_account_id',
            __('Google Account ID', 'strix-google-reviews'),
            array($this, 'account_id_field_callback'),
            'strix-google-reviews',
            'strix_google_reviews_main'
        );

        add_settings_field(
            'strix_google_reviews_location_id',
            __('Google Location ID', 'strix-google-reviews'),
            array($this, 'location_id_field_callback'),
            'strix-google-reviews',
            'strix_google_reviews_main'
        );

        add_settings_section(
            'strix_google_reviews_display',
            __('Display Settings', 'strix-google-reviews'),
            array($this, 'display_section_callback'),
            'strix-google-reviews'
        );

        add_settings_section(
            'strix_google_reviews_custom',
            __('Custom Reviews Settings', 'strix-google-reviews'),
            array($this, 'custom_reviews_section_callback'),
            'strix-google-reviews'
        );

        add_settings_field(
            'strix_google_reviews_show_company_name',
            __('Show Company Name', 'strix-google-reviews'),
            array($this, 'show_company_name_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_show_website',
            __('Show Company Website', 'strix-google-reviews'),
            array($this, 'show_website_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_show_phone',
            __('Show Company Phone', 'strix-google-reviews'),
            array($this, 'show_phone_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_filter_5_star',
            __('Show Only 5-Star Reviews', 'strix-google-reviews'),
            array($this, 'filter_5_star_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_cache_timeout',
            __('Cache Timeout (hours)', 'strix-google-reviews'),
            array($this, 'cache_timeout_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_demo_mode',
            __('Demo Mode', 'strix-google-reviews'),
            array($this, 'demo_mode_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_auto_approve',
            __('Auto-approve Reviews', 'strix-google-reviews'),
            array($this, 'auto_approve_callback'),
            'strix-google-reviews',
            'strix_google_reviews_custom'
        );

        add_settings_field(
            'strix_google_reviews_require_name',
            __('Require Name', 'strix-google-reviews'),
            array($this, 'require_name_callback'),
            'strix-google-reviews',
            'strix_google_reviews_custom'
        );

        add_settings_field(
            'strix_google_reviews_require_email',
            __('Require Email', 'strix-google-reviews'),
            array($this, 'require_email_callback'),
            'strix-google-reviews',
            'strix-google-reviews_custom'
        );

        add_settings_field(
            'strix_google_reviews_show_form',
            __('Show Review Form', 'strix-google-reviews'),
            array($this, 'show_form_callback'),
            'strix-google-reviews',
            'strix_google_reviews_custom'
        );

        add_settings_field(
            'strix_google_reviews_show_review_button',
            __('Show "Review us on Google" Button', 'strix-google-reviews'),
            array($this, 'show_review_button_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );

        add_settings_field(
            'strix_google_reviews_review_button_text',
            __('Review Button Text', 'strix-google-reviews'),
            array($this, 'review_button_text_callback'),
            'strix-google-reviews',
            'strix_google_reviews_display'
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Google Reviews', 'strix-google-reviews'),
            __('Google Reviews', 'strix-google-reviews'),
            'manage_options',
            'strix-google-reviews',
            array($this, 'admin_page'),
            'dashicons-star-filled',
            30
        );

        add_submenu_page(
            'strix-google-reviews',
            __('Settings', 'strix-google-reviews'),
            __('Settings', 'strix-google-reviews'),
            'manage_options',
            'strix-google-reviews',
            array($this, 'admin_page')
        );

        $google_title = __('Google Reviews', 'strix-google-reviews');
        $api_key = get_option('strix_google_reviews_api_key');
        $demo_mode = get_option('strix_google_reviews_demo_mode', '1');

        if (!$api_key || $demo_mode) {
            $google_title .= ' (' . __('Demo', 'strix-google-reviews') . ')';
        }

        add_submenu_page(
            'strix-google-reviews',
            $google_title,
            $google_title,
            'manage_options',
            'strix-google-reviews-reviews',
            array($this, 'reviews_page')
        );


        // Custom Reviews submenu is automatically added by register_post_type with show_in_menu
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure your Google Business Profile API settings to display reviews from your Google Business Profile.', 'strix-google-reviews') . '</p>';
        echo '<p>' . __('Get your API key from <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a> and enable Business Profile API.', 'strix-google-reviews') . '</p>';
        echo '<p>' . __('Find your Account ID and Location ID from your <a href="https://business.google.com/" target="_blank">Google Business Profile</a>.', 'strix-google-reviews') . '</p>';
        echo '<p><strong>' . __('Note:', 'strix-google-reviews') . '</strong> ' . __('This plugin retrieves publicly available details using the Google Business Profile API. All displayed reviews are subject to Google\'s Privacy Policy.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Display section callback
     */
    public function display_section_callback() {
        echo '<p>' . __('Configure how reviews should be displayed on your website.', 'strix-google-reviews') . '</p>';
    }

    /**
     * API Key field callback
     */
    public function api_key_field_callback() {
        $api_key = get_option('strix_google_reviews_api_key');
        echo '<input type="password" name="strix_google_reviews_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your Google Places API key. Keep this secure and never share it publicly.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Account ID field callback
     */
    public function account_id_field_callback() {
        $account_id = get_option('strix_google_reviews_account_id');
        echo '<input type="text" name="strix_google_reviews_account_id" value="' . esc_attr($account_id) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your Google Business Profile Account ID. Find it in your Google Business Profile settings.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Location ID field callback
     */
    public function location_id_field_callback() {
        $location_id = get_option('strix_google_reviews_location_id');
        echo '<input type="text" name="strix_google_reviews_location_id" value="' . esc_attr($location_id) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your Google Business Profile Location ID. Find it in your Google Business Profile settings.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Show company name callback
     */
    public function show_company_name_callback() {
        $value = get_option('strix_google_reviews_show_company_name', '1');
        echo '<input type="checkbox" name="strix_google_reviews_show_company_name" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Display company name in reviews', 'strix-google-reviews') . '</label>';
    }

    /**
     * Show website callback
     */
    public function show_website_callback() {
        $value = get_option('strix_google_reviews_show_website', '0');
        echo '<input type="checkbox" name="strix_google_reviews_show_website" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Display company website', 'strix-google-reviews') . '</label>';
    }

    /**
     * Show phone callback
     */
    public function show_phone_callback() {
        $value = get_option('strix_google_reviews_show_phone', '0');
        echo '<input type="checkbox" name="strix_google_reviews_show_phone" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Display company phone number', 'strix-google-reviews') . '</label>';
    }

    /**
     * Filter 5 star callback
     */
    public function filter_5_star_callback() {
        $value = get_option('strix_google_reviews_filter_5_star', '0');
        echo '<input type="checkbox" name="strix_google_reviews_filter_5_star" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Show only 5-star reviews', 'strix-google-reviews') . '</label>';
    }

    /**
     * Cache timeout callback
     */
    public function cache_timeout_callback() {
        $value = get_option('strix_google_reviews_cache_timeout', '24');
        echo '<input type="number" name="strix_google_reviews_cache_timeout" value="' . esc_attr($value) . '" min="1" max="168" />';
        echo '<p class="description">' . __('How long to cache reviews (in hours). Default is 24 hours.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Demo mode callback
     */
    public function demo_mode_callback() {
        $value = get_option('strix_google_reviews_demo_mode', '0');
        echo '<input type="checkbox" name="strix_google_reviews_demo_mode" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Enable demo mode to show sample reviews', 'strix-google-reviews') . '</label>';
        echo '<p class="description">' . __('When enabled, shows sample reviews instead of Google reviews. Useful for testing.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Custom reviews section callback
     */
    public function custom_reviews_section_callback() {
        echo '<p>' . __('Configure settings for custom reviews submitted by visitors.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Auto-approve callback
     */
    public function auto_approve_callback() {
        $value = get_option('strix_google_reviews_auto_approve', '0');
        echo '<input type="checkbox" name="strix_google_reviews_auto_approve" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Automatically approve and publish submitted reviews', 'strix-google-reviews') . '</label>';
        echo '<p class="description">' . __('If unchecked, reviews will need manual approval in admin panel.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Require name callback
     */
    public function require_name_callback() {
        $value = get_option('strix_google_reviews_require_name', '1');
        echo '<input type="checkbox" name="strix_google_reviews_require_name" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Require visitor name for review submission', 'strix-google-reviews') . '</label>';
    }

    /**
     * Require email callback
     */
    public function require_email_callback() {
        $value = get_option('strix_google_reviews_require_email', '0');
        echo '<input type="checkbox" name="strix_google_reviews_require_email" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Require visitor email for review submission', 'strix-google-reviews') . '</label>';
    }

    /**
     * Show form callback
     */
    public function show_form_callback() {
        $value = get_option('strix_google_reviews_show_form', '1');
        echo '<input type="checkbox" name="strix_google_reviews_show_form" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Show review submission form with reviews list', 'strix-google-reviews') . '</label>';
        echo '<p class="description">' . __('If unchecked, only reviews will be displayed without submission form.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Show review button callback
     */
    public function show_review_button_callback() {
        $value = get_option('strix_google_reviews_show_review_button', '1');
        echo '<input type="checkbox" name="strix_google_reviews_show_review_button" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Show "Review us on Google" button with reviews', 'strix-google-reviews') . '</label>';
    }

    /**
     * Review button text callback
     */
    public function review_button_text_callback() {
        $value = get_option('strix_google_reviews_review_button_text', __('Review us on Google', 'strix-google-reviews'));
        echo '<input type="text" name="strix_google_reviews_review_button_text" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Text for the review button. Leave empty to use default.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap strix-google-reviews-admin">
            <h1><?php _e('Google Reviews Settings', 'strix-google-reviews'); ?></h1>

            <?php if (!get_option('strix_google_reviews_api_key') || !get_option('strix_google_reviews_account_id') || !get_option('strix_google_reviews_location_id')): ?>
            <div class="notice notice-info">
                <p><?php _e('Configure your Google Business Profile API key, Account ID, and Location ID to start displaying reviews.', 'strix-google-reviews'); ?></p>
            </div>
            <?php endif; ?>

            <div class="strix-admin-tabs">
                <div class="strix-admin-tab active" data-tab="settings"><?php _e('Settings', 'strix-google-reviews'); ?></div>
                <div class="strix-admin-tab" data-tab="usage"><?php _e('How to Use', 'strix-google-reviews'); ?></div>
            </div>

            <div class="strix-admin-content">
                <div class="strix-admin-tab-content active" id="settings">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('strix_google_reviews_settings');
                        do_settings_sections('strix-google-reviews');
                        submit_button(__('Save Settings', 'strix-google-reviews'));
                        ?>
                    </form>
                </div>

                <div class="strix-admin-tab-content" id="usage">
                    <div class="strix-usage-guide">
                        <h2><?php _e('How to Display Reviews on Your Site', 'strix-google-reviews'); ?></h2>

                        <div class="strix-usage-section">
                            <h3><?php _e('Google Reviews (from Google Places)', 'strix-google-reviews'); ?></h3>
                            <p><?php _e('Display reviews from your Google Business Profile.', 'strix-google-reviews'); ?></p>

                            <div class="strix-code-example">
                                <h4><?php _e('Basic usage:', 'strix-google-reviews'); ?></h4>
                                <code>[strix_google_reviews]</code>
                            </div>

                            <div class="strix-code-example">
                                <h4><?php _e('With custom Account ID and Location ID:', 'strix-google-reviews'); ?></h4>
                                <code>[strix_google_reviews account_id="123456789012345678901" location_id="98765432109876543210" limit="5"]</code>
                            </div>

                            <div class="strix-code-example">
                                <h4><?php _e('Different layouts:', 'strix-google-reviews'); ?></h4>
                                <code>[strix_google_reviews layout="slider" layout_style="1"]</code><br>
                                <code>[strix_google_reviews layout="grid" layout_style="1"]</code><br>
                                <code>[strix_google_reviews layout="badge" layout_style="1"]</code><br>
                                <code>[strix_google_reviews layout="popup" layout_style="1"]</code>
                            </div>

                            <div class="strix-code-example">
                                <h4><?php _e('Demo mode (shows sample reviews):', 'strix-google-reviews'); ?></h4>
                                <code>[strix_google_reviews demo="true"]</code>
                            </div>
                        </div>

                        <div class="strix-usage-section">
                            <h3><?php _e('Custom Widgets', 'strix-google-reviews'); ?></h3>
                            <p><?php _e('Create and manage custom review widgets with individual settings.', 'strix-google-reviews'); ?></p>

                            <div class="strix-code-example">
                                <h4><?php _e('Using custom widget:', 'strix-google-reviews'); ?></h4>
                                <code>[strix_widget id="123"]</code>
                                <p><?php _e('Replace "123" with your widget ID from the Review Widgets page.', 'strix-google-reviews'); ?></p>
                            </div>

                            <p><?php _e('To create a custom widget:', 'strix-google-reviews'); ?></p>
                            <ol>
                                <li><?php _e('Go to Google Reviews → Review Widgets', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Click "Add New"', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Configure layout, style, and other settings', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Copy the generated shortcode', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Use the shortcode anywhere on your site', 'strix-google-reviews'); ?></li>
                            </ol>
                        </div>

                        <div class="strix-usage-section">
                            <h3><?php _e('Custom Reviews (from your visitors)', 'strix-google-reviews'); ?></h3>
                            <p><?php _e('Display reviews submitted by your website visitors.', 'strix-google-reviews'); ?></p>

                            <div class="strix-code-example">
                                <h4><?php _e('Reviews with submission form:', 'strix-google-reviews'); ?></h4>
                                <code>[strix_custom_reviews]</code>
                            </div>

                            <div class="strix-code-example">
                                <h4><?php _e('Only reviews (no form):', 'strix-google-reviews'); ?></h4>
                                <code>[strix_custom_reviews show_form="0"]</code>
                            </div>

                            <div class="strix-code-example">
                                <h4><?php _e('Limited number of reviews:', 'strix-google-reviews'); ?></h4>
                                <code>[strix_custom_reviews limit="5" show_form="1"]</code>
                            </div>
                        </div>

                        <div class="strix-usage-section">
                            <h3><?php _e('Review Submission Form Only', 'strix-google-reviews'); ?></h3>
                            <p><?php _e('Display only the form for submitting reviews.', 'strix-google-reviews'); ?></p>

                            <div class="strix-code-example">
                                <code>[strix_review_form]</code>
                            </div>
                        </div>

                        <div class="strix-usage-section">
                            <h3><?php _e('Combined Example (Recommended)', 'strix-google-reviews'); ?></h3>
                            <p><?php _e('Show both Google reviews and custom reviews on one page.', 'strix-google-reviews'); ?></p>

                            <div class="strix-code-example">
<pre><code>&lt;h2&gt;Отзывы из Google&lt;/h2&gt;
[strix_google_reviews limit="3"]

&lt;h2&gt;Отзывы посетителей&lt;/h2&gt;
[strix_custom_reviews limit="5"]</code></pre>
                            </div>
                        </div>

                        <div class="strix-usage-section">
                            <h3><?php _e('Widget Usage', 'strix-google-reviews'); ?></h3>
                            <p><?php _e('Add reviews to your sidebar or other widget areas.', 'strix-google-reviews'); ?></p>
                            <p><?php _e('Go to Appearance → Widgets and add the "Google Reviews" widget.', 'strix-google-reviews'); ?></p>
                        </div>

                        <div class="strix-usage-tips">
                            <h3><?php _e('Tips:', 'strix-google-reviews'); ?></h3>
                            <ul>
                                <li><?php _e('Use shortcodes in pages, posts, or custom page builders.', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Configure API settings above to show real Google reviews.', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Custom reviews work without API configuration.', 'strix-google-reviews'); ?></li>
                                <li><?php _e('Moderate custom reviews in the "Custom Reviews" menu.', 'strix-google-reviews'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.strix-admin-tab').on('click', function() {
                var tab = $(this).data('tab');

                $('.strix-admin-tab').removeClass('active');
                $(this).addClass('active');

                $('.strix-admin-tab-content').removeClass('active');
                $('#' + tab).addClass('active');
            });
        });
        </script>
        <?php
    }

    /**
     * Reviews page
     */
    public function reviews_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Google Reviews Management', 'strix-google-reviews'); ?></h1>

            <div class="strix-reviews-actions">
                <button id="strix-refresh-reviews" class="button button-primary">
                    <?php _e('Refresh Reviews', 'strix-google-reviews'); ?>
                </button>
                <span id="strix-loading" style="display:none;"><?php _e('Loading...', 'strix-google-reviews'); ?></span>
            </div>

            <div id="strix-reviews-container">
                <?php $this->display_cached_reviews(); ?>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#strix-refresh-reviews').on('click', function() {
                $('#strix-loading').show();
                $(this).prop('disabled', true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'strix_refresh_reviews',
                        nonce: '<?php echo wp_create_nonce("strix_refresh_reviews"); ?>'
                    },
                    success: function(response) {
                        $('#strix-reviews-container').html(response);
                        $('#strix-loading').hide();
                        $('#strix-refresh-reviews').prop('disabled', false);
                    },
                    error: function() {
                        alert('<?php _e("Error refreshing reviews. Please try again.", "strix-google-reviews"); ?>');
                        $('#strix-loading').hide();
                        $('#strix-refresh-reviews').prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_strix-google-reviews' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'strix-google-reviews-admin',
            plugins_url('assets/css/admin.css', __FILE__),
            array(),
            self::VERSION
        );

        wp_enqueue_script(
            'strix-google-reviews-admin',
            plugins_url('assets/js/admin.js', __FILE__),
            array('jquery'),
            self::VERSION,
            true
        );
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'strix-google-reviews-frontend',
            plugins_url('assets/css/frontend.css', __FILE__),
            array(),
            self::VERSION
        );

        wp_enqueue_script(
            'strix-google-reviews-frontend',
            plugins_url('assets/js/frontend.js', __FILE__),
            array('jquery'),
            self::VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('strix-google-reviews-frontend', 'strix_reviews_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('strix_reviews_nonce'),
        ));
    }

    /**
     * Register widgets
     */
    public function register_widgets() {
        register_widget('Strix_Google_Reviews_Widget');
    }

    /**
     * Register custom post type for reviews
     */
    public function register_custom_post_type() {
        register_post_type('strix_review', array(
            'label' => __('Custom Reviews', 'strix-google-reviews'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'strix-google-reviews',
            'supports' => array('title', 'editor', 'custom-fields'),
            'capability_type' => 'post',
            'capabilities' => array(
                'edit_posts' => 'edit_posts',
                'edit_others_posts' => 'edit_others_posts',
                'publish_posts' => 'publish_posts',
                'read_private_posts' => 'read_private_posts',
                'delete_posts' => 'delete_posts',
                'delete_others_posts' => 'delete_others_posts',
                'edit_published_posts' => 'edit_published_posts',
                'delete_published_posts' => 'delete_published_posts',
            ),
            'menu_position' => 20,
            'menu_icon' => 'dashicons-star-filled',
            'map_meta_cap' => true,
        ));

        // Add custom status for pending reviews
        register_post_status('pending_review', array(
            'label' => __('Pending Review', 'strix-google-reviews'),
            'public' => false,
            'internal' => true,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Pending Review <span class="count">(%s)</span>', 'Pending Reviews <span class="count">(%s)</span>', 'strix-google-reviews'),
        ));

        // Flush rewrite rules on activation if needed
        if (get_option('strix_reviews_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('strix_reviews_flush_rewrite_rules');
        }
    }

    /**
     * Register widget post type for custom review widgets
     */
    public function register_widget_post_type() {
        register_post_type('strix_widget', array(
            'label' => __('Review Widgets', 'strix-google-reviews'),
            'labels' => array(
                'name' => __('Review Widgets', 'strix-google-reviews'),
                'singular_name' => __('Review Widget', 'strix-google-reviews'),
                'add_new' => __('Add New Widget', 'strix-google-reviews'),
                'add_new_item' => __('Add New Review Widget', 'strix-google-reviews'),
                'edit_item' => __('Edit Widget', 'strix-google-reviews'),
                'new_item' => __('New Widget', 'strix-google-reviews'),
                'view_item' => __('View Widget', 'strix-google-reviews'),
                'search_items' => __('Search Widgets', 'strix-google-reviews'),
                'not_found' => __('No widgets found', 'strix-google-reviews'),
                'not_found_in_trash' => __('No widgets found in trash', 'strix-google-reviews'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'strix-google-reviews',
            'supports' => array('title'),
            'capability_type' => 'post',
            'capabilities' => array(
                'edit_posts' => 'edit_posts',
                'edit_others_posts' => 'edit_others_posts',
                'publish_posts' => 'publish_posts',
                'read_private_posts' => 'read_private_posts',
                'delete_posts' => 'delete_posts',
                'delete_others_posts' => 'delete_others_posts',
                'edit_published_posts' => 'edit_published_posts',
                'delete_published_posts' => 'delete_published_posts',
            ),
            'menu_position' => 25,
            'menu_icon' => 'dashicons-screenoptions',
            'map_meta_cap' => true,
        ));
    }

    /**
     * Add meta box for widget configuration
     */
    public function strix_add_widget_meta_box() {
        add_meta_box(
            'strix_widget_settings',
            __('Widget Settings', 'strix-google-reviews'),
            array($this, 'strix_widget_meta_box_callback'),
            'strix_widget',
            'normal',
            'high'
        );

        add_meta_box(
            'strix_widget_shortcode',
            __('Shortcode', 'strix-google-reviews'),
            array($this, 'strix_widget_shortcode_callback'),
            'strix_widget',
            'side',
            'default'
        );
    }

    /**
     * Widget meta box callback
     */
    public function strix_widget_meta_box_callback($post) {
        wp_nonce_field('strix_widget_meta_box', 'strix_widget_meta_box_nonce');

        $account_id = get_post_meta($post->ID, '_strix_account_id', true);
        $location_id = get_post_meta($post->ID, '_strix_location_id', true);
        $layout = get_post_meta($post->ID, '_strix_layout', true) ?: 'list';
        $layout_style = get_post_meta($post->ID, '_strix_layout_style', true) ?: '1';
        $limit = get_post_meta($post->ID, '_strix_limit', true) ?: 5;
        $show_company = get_post_meta($post->ID, '_strix_show_company', true) ?: '1';
        $filter_5_star = get_post_meta($post->ID, '_strix_filter_5_star', true) ?: '0';
        $filter_rating = get_post_meta($post->ID, '_strix_filter_rating', true) ?: '';
        $filter_keywords = get_post_meta($post->ID, '_strix_filter_keywords', true) ?: '';
        $sort_by = get_post_meta($post->ID, '_strix_sort_by', true) ?: 'newest';
        $data_source = get_post_meta($post->ID, '_strix_data_source', true) ?: 'google';

        ?>
        <div class="strix-widget-editor-pro">
            <!-- Навигация по вкладкам -->
            <div class="strix-editor-nav">
                <button type="button" class="strix-nav-tab active" data-tab="data-source">
                    <span class="strix-tab-icon">📊</span>
                    <span class="strix-tab-title"><?php _e('Data Source', 'strix-google-reviews'); ?></span>
                </button>
                <button type="button" class="strix-nav-tab" data-tab="layout-design">
                    <span class="strix-tab-icon">🎨</span>
                    <span class="strix-tab-title"><?php _e('Layout & Design', 'strix-google-reviews'); ?></span>
                </button>
                <button type="button" class="strix-nav-tab" data-tab="display-options">
                    <span class="strix-tab-icon">⚙️</span>
                    <span class="strix-tab-title"><?php _e('Display Options', 'strix-google-reviews'); ?></span>
                </button>
                <button type="button" class="strix-nav-tab" data-tab="filters-sorting">
                    <span class="strix-tab-icon">🔍</span>
                    <span class="strix-tab-title"><?php _e('Filters & Sorting', 'strix-google-reviews'); ?></span>
                </button>
            </div>

            <!-- Вкладка 1: Источник данных -->
            <div class="strix-editor-tab active" id="data-source">
                <div class="strix-tab-header">
                    <h3><?php _e('Choose Your Data Source', 'strix-google-reviews'); ?></h3>
                    <p><?php _e('Select where to get reviews from', 'strix-google-reviews'); ?></p>
                </div>

                <div class="strix-data-source-selector">
                    <div class="strix-source-option">
                        <input type="radio" id="source_google" name="strix_data_source" value="google"
                               <?php checked($data_source, 'google'); ?> />
                        <label for="source_google" class="strix-source-card strix-google-source">
                            <div class="strix-source-icon">🌐</div>
                            <div class="strix-source-content">
                                <h4><?php _e('Google Business Profile', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Display real reviews from your Google Business Profile', 'strix-google-reviews'); ?></p>
                                <div class="strix-source-features">
                                    <span class="strix-feature-tag">⭐ Real Reviews</span>
                                    <span class="strix-feature-tag">📍 Location-based</span>
                                    <span class="strix-feature-tag">🔄 Auto-sync</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="strix-source-option">
                        <input type="radio" id="source_custom" name="strix_data_source" value="custom"
                               <?php checked($data_source, 'custom'); ?> />
                        <label for="source_custom" class="strix-source-card strix-custom-source">
                            <div class="strix-source-icon">💬</div>
                            <div class="strix-source-content">
                                <h4><?php _e('Custom Reviews', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Display reviews submitted by your website visitors', 'strix-google-reviews'); ?></p>
                                <div class="strix-source-features">
                                    <span class="strix-feature-tag">✍️ User-generated</span>
                                    <span class="strix-feature-tag">🎯 Targeted</span>
                                    <span class="strix-feature-tag">📝 Editable</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="strix-google-settings" id="google-settings" style="<?php echo $data_source === 'google' ? '' : 'display: none;'; ?>">
                    <div class="strix-settings-group">
                        <h4><?php _e('Google Business Profile Settings', 'strix-google-reviews'); ?></h4>
                        <div class="strix-form-grid">
                            <div class="strix-form-field">
                                <label for="strix_account_id">
                                    <span class="strix-label-icon">🏢</span>
                                    <?php _e('Account ID', 'strix-google-reviews'); ?>
                                </label>
                                <input type="text" id="strix_account_id" name="strix_account_id"
                                       value="<?php echo esc_attr($account_id); ?>"
                                       placeholder="<?php _e('Enter Account ID...', 'strix-google-reviews'); ?>" />
                                <span class="strix-field-hint"><?php _e('Your Google Business Profile Account ID', 'strix-google-reviews'); ?></span>
                            </div>
                            <div class="strix-form-field">
                                <label for="strix_location_id">
                                    <span class="strix-label-icon">📍</span>
                                    <?php _e('Location ID', 'strix-google-reviews'); ?>
                                </label>
                                <input type="text" id="strix_location_id" name="strix_location_id"
                                       value="<?php echo esc_attr($location_id); ?>"
                                       placeholder="<?php _e('Enter Location ID...', 'strix-google-reviews'); ?>" />
                                <span class="strix-field-hint"><?php _e('Your specific location ID', 'strix-google-reviews'); ?></span>
                            </div>
                        </div>
                        <div class="strix-info-box">
                            <div class="strix-info-icon">ℹ️</div>
                            <div class="strix-info-content">
                                <strong><?php _e('How to find your IDs:', 'strix-google-reviews'); ?></strong>
                                <p><?php _e('Go to Google Business Profile → Settings → Find Account ID and Location ID in the URL.', 'strix-google-reviews'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка 2: Layout & Design -->
            <div class="strix-editor-tab" id="layout-design">
                <div class="strix-tab-header">
                    <h3><?php _e('Choose Your Layout Style', 'strix-google-reviews'); ?></h3>
                    <p><?php _e('Select how reviews will be displayed on your website', 'strix-google-reviews'); ?></p>
                </div>

                <div class="strix-layout-selector-pro">
                    <div class="strix-layout-option-pro">
                        <input type="radio" id="layout_list" name="strix_layout" value="list"
                               <?php checked($layout, 'list'); ?> />
                        <label for="layout_list" class="strix-layout-card-pro">
                            <div class="strix-layout-visual">
                                <div class="strix-layout-mockup strix-list-mockup">
                                    <div class="strix-mockup-review">
                                        <div class="strix-mockup-avatar"></div>
                                        <div class="strix-mockup-content">
                                            <div class="strix-mockup-stars">★★★★★</div>
                                            <div class="strix-mockup-text"></div>
                                        </div>
                                    </div>
                                    <div class="strix-mockup-review">
                                        <div class="strix-mockup-avatar"></div>
                                        <div class="strix-mockup-content">
                                            <div class="strix-mockup-stars">★★★★★</div>
                                            <div class="strix-mockup-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="strix-layout-info">
                                <h4><?php _e('List Layout', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Clean vertical list of reviews', 'strix-google-reviews'); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="strix-layout-option-pro">
                        <input type="radio" id="layout_grid" name="strix_layout" value="grid"
                               <?php checked($layout, 'grid'); ?> />
                        <label for="layout_grid" class="strix-layout-card-pro">
                            <div class="strix-layout-visual">
                                <div class="strix-layout-mockup strix-grid-mockup">
                                    <div class="strix-mockup-review-card">
                                        <div class="strix-mockup-stars">★★★★★</div>
                                        <div class="strix-mockup-text"></div>
                                    </div>
                                    <div class="strix-mockup-review-card">
                                        <div class="strix-mockup-stars">★★★★★</div>
                                        <div class="strix-mockup-text"></div>
                                    </div>
                                    <div class="strix-mockup-review-card">
                                        <div class="strix-mockup-stars">★★★★★</div>
                                        <div class="strix-mockup-text"></div>
                                    </div>
                                    <div class="strix-mockup-review-card">
                                        <div class="strix-mockup-stars">★★★★★</div>
                                        <div class="strix-mockup-text"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="strix-layout-info">
                                <h4><?php _e('Grid Layout', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Modern card-based grid layout', 'strix-google-reviews'); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="strix-layout-option-pro">
                        <input type="radio" id="layout_slider" name="strix_layout" value="slider"
                               <?php checked($layout, 'slider'); ?> />
                        <label for="layout_slider" class="strix-layout-card-pro">
                            <div class="strix-layout-visual">
                                <div class="strix-layout-mockup strix-slider-mockup">
                                    <div class="strix-slider-container">
                                        <div class="strix-mockup-slide active">
                                            <div class="strix-mockup-stars">★★★★★</div>
                                            <div class="strix-mockup-text"></div>
                                        </div>
                                        <div class="strix-mockup-slide">
                                            <div class="strix-mockup-stars">★★★★★</div>
                                            <div class="strix-mockup-text"></div>
                                        </div>
                                    </div>
                                    <div class="strix-slider-nav">
                                        <div class="strix-nav-dot active"></div>
                                        <div class="strix-nav-dot"></div>
                                        <div class="strix-nav-dot"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="strix-layout-info">
                                <h4><?php _e('Slider Layout', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Interactive carousel with navigation', 'strix-google-reviews'); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="strix-layout-option-pro">
                        <input type="radio" id="layout_badge" name="strix_layout" value="badge"
                               <?php checked($layout, 'badge'); ?> />
                        <label for="layout_badge" class="strix-layout-card-pro">
                            <div class="strix-layout-visual">
                                <div class="strix-layout-mockup strix-badge-mockup">
                                    <div class="strix-mockup-badge">
                                        <div class="strix-badge-rating">4.8</div>
                                        <div class="strix-badge-stars">★★★★★</div>
                                        <div class="strix-badge-text">12 reviews</div>
                                    </div>
                                </div>
                            </div>
                            <div class="strix-layout-info">
                                <h4><?php _e('Badge Layout', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Compact rating badge', 'strix-google-reviews'); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="strix-layout-option-pro">
                        <input type="radio" id="layout_popup" name="strix_layout" value="popup"
                               <?php checked($layout, 'popup'); ?> />
                        <label for="layout_popup" class="strix-layout-card-pro">
                            <div class="strix-layout-visual">
                                <div class="strix-layout-mockup strix-popup-mockup">
                                    <div class="strix-mockup-popup">
                                        <div class="strix-popup-trigger">View Reviews</div>
                                        <div class="strix-popup-window">
                                            <div class="strix-mockup-stars">★★★★★</div>
                                            <div class="strix-mockup-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="strix-layout-info">
                                <h4><?php _e('Popup Layout', 'strix-google-reviews'); ?></h4>
                                <p><?php _e('Reviews in modal popup', 'strix-google-reviews'); ?></p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="strix-style-selector">
                    <h4><?php _e('Choose Style Variant', 'strix-google-reviews'); ?></h4>
                    <div class="strix-style-options">
                        <label class="strix-style-option">
                            <input type="radio" name="strix_layout_style" value="1" <?php checked($layout_style, '1'); ?> />
                            <span class="strix-style-preview strix-style-1"></span>
                            <span class="strix-style-name"><?php _e('Classic', 'strix-google-reviews'); ?></span>
                        </label>
                        <label class="strix-style-option">
                            <input type="radio" name="strix_layout_style" value="2" <?php checked($layout_style, '2'); ?> />
                            <span class="strix-style-preview strix-style-2"></span>
                            <span class="strix-style-name"><?php _e('Modern', 'strix-google-reviews'); ?></span>
                        </label>
                        <label class="strix-style-option">
                            <input type="radio" name="strix_layout_style" value="3" <?php checked($layout_style, '3'); ?> />
                            <span class="strix-style-preview strix-style-3"></span>
                            <span class="strix-style-name"><?php _e('Minimal', 'strix-google-reviews'); ?></span>
                        </label>
                        <label class="strix-style-option">
                            <input type="radio" name="strix_layout_style" value="4" <?php checked($layout_style, '4'); ?> />
                            <span class="strix-style-preview strix-style-4"></span>
                            <span class="strix-style-name"><?php _e('Elegant', 'strix-google-reviews'); ?></span>
                        </label>
                        <label class="strix-style-option">
                            <input type="radio" name="strix_layout_style" value="5" <?php checked($layout_style, '5'); ?> />
                            <span class="strix-style-preview strix-style-5"></span>
                            <span class="strix-style-name"><?php _e('Bold', 'strix-google-reviews'); ?></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Вкладка 3: Display Options -->
            <div class="strix-editor-tab" id="display-options">
                <div class="strix-tab-header">
                    <h3><?php _e('Configure Display Options', 'strix-google-reviews'); ?></h3>
                    <p><?php _e('Customize how reviews appear and behave', 'strix-google-reviews'); ?></p>
                </div>

                <div class="strix-display-settings">
                    <div class="strix-setting-group">
                        <h4><?php _e('Basic Settings', 'strix-google-reviews'); ?></h4>
                        <div class="strix-setting-grid">
                            <div class="strix-setting-item">
                                <label for="strix_limit" class="strix-setting-label">
                                    <span class="strix-setting-icon">📊</span>
                                    <?php _e('Number of Reviews', 'strix-google-reviews'); ?>
                                </label>
                                <div class="strix-setting-control">
                                    <input type="number" id="strix_limit" name="strix_limit"
                                           value="<?php echo esc_attr($limit); ?>" min="1" max="50" />
                                    <span class="strix-setting-hint"><?php _e('How many reviews to display', 'strix-google-reviews'); ?></span>
                                </div>
                            </div>

                            <div class="strix-setting-item">
                                <label for="strix_sort_by" class="strix-setting-label">
                                    <span class="strix-setting-icon">🔄</span>
                                    <?php _e('Sort Reviews', 'strix-google-reviews'); ?>
                                </label>
                                <div class="strix-setting-control">
                                    <select id="strix_sort_by" name="strix_sort_by">
                                        <option value="newest" <?php selected($sort_by, 'newest'); ?>><?php _e('Newest First', 'strix-google-reviews'); ?></option>
                                        <option value="oldest" <?php selected($sort_by, 'oldest'); ?>><?php _e('Oldest First', 'strix-google-reviews'); ?></option>
                                        <option value="highest" <?php selected($sort_by, 'highest'); ?>><?php _e('Highest Rating', 'strix-google-reviews'); ?></option>
                                        <option value="lowest" <?php selected($sort_by, 'lowest'); ?>><?php _e('Lowest Rating', 'strix-google-reviews'); ?></option>
                                    </select>
                                    <span class="strix-setting-hint"><?php _e('Order of reviews display', 'strix-google-reviews'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="strix-setting-group">
                        <h4><?php _e('Visibility Options', 'strix-google-reviews'); ?></h4>
                        <div class="strix-toggle-grid">
                            <div class="strix-toggle-item">
                                <div class="strix-toggle-control">
                                    <input type="checkbox" id="strix_show_company" name="strix_show_company" value="1"
                                           <?php checked($show_company, '1'); ?> />
                                    <label for="strix_show_company" class="strix-toggle-slider"></label>
                                </div>
                                <div class="strix-toggle-info">
                                    <span class="strix-toggle-title"><?php _e('Company Info', 'strix-google-reviews'); ?></span>
                                    <span class="strix-toggle-desc"><?php _e('Show company name and overall rating', 'strix-google-reviews'); ?></span>
                                </div>
                            </div>

                            <div class="strix-toggle-item">
                                <div class="strix-toggle-control">
                                    <input type="checkbox" id="strix_filter_5_star" name="strix_filter_5_star" value="1"
                                           <?php checked($filter_5_star, '1'); ?> />
                                    <label for="strix_filter_5_star" class="strix-toggle-slider"></label>
                                </div>
                                <div class="strix-toggle-info">
                                    <span class="strix-toggle-title"><?php _e('5-Star Only', 'strix-google-reviews'); ?></span>
                                    <span class="strix-toggle-desc"><?php _e('Display only 5-star reviews', 'strix-google-reviews'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка 4: Filters & Sorting -->
            <div class="strix-editor-tab" id="filters-sorting">
                <div class="strix-tab-header">
                    <h3><?php _e('Advanced Filtering', 'strix-google-reviews'); ?></h3>
                    <p><?php _e('Fine-tune which reviews are displayed', 'strix-google-reviews'); ?></p>
                </div>

                <div class="strix-filter-settings">
                    <div class="strix-filter-group">
                        <div class="strix-filter-visual">
                            <div class="strix-filter-icon">🎯</div>
                            <h4><?php _e('Rating Filter', 'strix-google-reviews'); ?></h4>
                            <p><?php _e('Show only reviews with specific ratings', 'strix-google-reviews'); ?></p>
                        </div>
                        <div class="strix-filter-control">
                            <select id="strix_filter_rating" name="strix_filter_rating">
                                <option value="" <?php selected($filter_rating, ''); ?>><?php _e('Show all ratings', 'strix-google-reviews'); ?></option>
                                <option value="2" <?php selected($filter_rating, '2'); ?>><?php _e('2 stars and above', 'strix-google-reviews'); ?></option>
                                <option value="3" <?php selected($filter_rating, '3'); ?>><?php _e('3 stars and above', 'strix-google-reviews'); ?></option>
                                <option value="4" <?php selected($filter_rating, '4'); ?>><?php _e('4 stars and above', 'strix-google-reviews'); ?></option>
                                <option value="5" <?php selected($filter_rating, '5'); ?>><?php _e('5 stars only', 'strix-google-reviews'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="strix-filter-group">
                        <div class="strix-filter-visual">
                            <div class="strix-filter-icon">🔍</div>
                            <h4><?php _e('Keyword Filter', 'strix-google-reviews'); ?></h4>
                            <p><?php _e('Show reviews containing specific words', 'strix-google-reviews'); ?></p>
                        </div>
                        <div class="strix-filter-control">
                            <input type="text" id="strix_filter_keywords" name="strix_filter_keywords"
                                   value="<?php echo esc_attr($filter_keywords); ?>"
                                   placeholder="<?php _e('Enter keywords separated by commas', 'strix-google-reviews'); ?>" />
                            <div class="strix-keyword-examples">
                                <small><?php _e('Examples: service, quality, fast, friendly', 'strix-google-reviews'); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="strix-filter-preview">
                        <h4><?php _e('Filter Preview', 'strix-google-reviews'); ?></h4>
                        <div class="strix-preview-stats">
                            <div class="strix-stat-item">
                                <span class="strix-stat-number"><?php echo $filter_rating ? $filter_rating . '+' : 'All'; ?></span>
                                <span class="strix-stat-label"><?php _e('Min Rating', 'strix-google-reviews'); ?></span>
                            </div>
                            <div class="strix-stat-item">
                                <span class="strix-stat-number"><?php echo $filter_keywords ? count(explode(',', $filter_keywords)) : '0'; ?></span>
                                <span class="strix-stat-label"><?php _e('Keywords', 'strix-google-reviews'); ?></span>
                            </div>
                            <div class="strix-stat-item">
                                <span class="strix-stat-number"><?php echo ucfirst($sort_by); ?></span>
                                <span class="strix-stat-label"><?php _e('Sort By', 'strix-google-reviews'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <style>
        /* Professional Widget Editor Styles */
        .strix-widget-editor-pro {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Navigation Tabs */
        .strix-editor-nav {
            display: flex;
            background: white;
            border-bottom: 1px solid #e1e5e9;
        }

        .strix-nav-tab {
            flex: 1;
            padding: 20px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .strix-nav-tab:hover {
            background: #f8f9fa;
        }

        .strix-nav-tab.active {
            background: #007cba;
            color: white;
            border-bottom-color: #005a87;
        }

        .strix-nav-tab.active .strix-tab-icon {
            transform: scale(1.1);
        }

        .strix-tab-icon {
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .strix-tab-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tab Content */
        .strix-editor-tab {
            display: none;
            padding: 32px;
            background: white;
            min-height: 500px;
        }

        .strix-editor-tab.active {
            display: block;
        }

        .strix-tab-header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e1e5e9;
        }

        .strix-tab-header h3 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 700;
            color: #1d2327;
        }

        .strix-tab-header p {
            margin: 0;
            color: #646970;
            font-size: 14px;
        }

        /* Data Source Selector */
        .strix-data-source-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .strix-source-option input {
            position: absolute;
            opacity: 0;
        }

        .strix-source-card {
            display: block;
            padding: 24px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .strix-source-card:hover {
            border-color: #007cba;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 186, 0.15);
        }

        .strix-source-option input:checked + .strix-source-card {
            border-color: #007cba;
            background: #f0f8ff;
            box-shadow: 0 0 0 3px rgba(0, 123, 186, 0.1);
        }

        .strix-google-source .strix-source-icon {
            background: linear-gradient(135deg, #4285F4, #34A853);
        }

        .strix-custom-source .strix-source-icon {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
        }

        .strix-source-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
            color: white;
        }

        .strix-source-content h4 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: #1d2327;
        }

        .strix-source-content p {
            margin: 0 0 16px 0;
            color: #646970;
            font-size: 14px;
        }

        .strix-source-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .strix-feature-tag {
            background: #e1f5fe;
            color: #0277bd;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Google Settings */
        .strix-google-settings {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
        }

        .strix-settings-group h4 {
            margin-top: 0;
            margin-bottom: 16px;
            color: #1d2327;
            font-size: 16px;
            font-weight: 600;
        }

        .strix-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .strix-form-field {
            display: flex;
            flex-direction: column;
        }

        .strix-form-field label {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1d2327;
            font-size: 14px;
        }

        .strix-label-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .strix-form-field input {
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .strix-form-field input:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 3px rgba(0, 123, 186, 0.1);
        }

        .strix-field-hint {
            margin-top: 4px;
            font-size: 12px;
            color: #646970;
        }

        .strix-info-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            background: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }

        .strix-info-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .strix-info-content strong {
            display: block;
            margin-bottom: 4px;
            color: #0d47a1;
        }

        /* Layout Selector */
        .strix-layout-selector-pro {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .strix-layout-option-pro input {
            position: absolute;
            opacity: 0;
        }

        .strix-layout-card-pro {
            display: flex;
            padding: 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .strix-layout-card-pro:hover {
            border-color: #007cba;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 186, 0.15);
        }

        .strix-layout-option-pro input:checked + .strix-layout-card-pro {
            border-color: #007cba;
            background: #f0f8ff;
            box-shadow: 0 0 0 3px rgba(0, 123, 186, 0.1);
        }

        .strix-layout-visual {
            flex: 1;
            margin-right: 16px;
        }

        .strix-layout-mockup {
            height: 120px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e1e5e9;
        }

        .strix-list-mockup {
            flex-direction: column;
            padding: 12px;
            gap: 8px;
        }

        .strix-mockup-review {
            display: flex;
            gap: 8px;
        }

        .strix-mockup-avatar {
            width: 20px;
            height: 20px;
            background: #ccc;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .strix-mockup-content {
            flex: 1;
        }

        .strix-mockup-stars {
            font-size: 10px;
            color: #ffd700;
            margin-bottom: 4px;
        }

        .strix-mockup-text {
            height: 8px;
            background: #e1e5e9;
            border-radius: 4px;
        }

        .strix-grid-mockup {
            flex-wrap: wrap;
            padding: 12px;
            gap: 8px;
        }

        .strix-mockup-review-card {
            width: 45%;
            background: white;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e1e5e9;
        }

        .strix-slider-mockup {
            position: relative;
        }

        .strix-slider-container {
            display: flex;
            gap: 8px;
            padding: 12px;
        }

        .strix-mockup-slide {
            flex: 1;
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e1e5e9;
            opacity: 0.5;
        }

        .strix-mockup-slide.active {
            opacity: 1;
            border-color: #007cba;
        }

        .strix-slider-nav {
            display: flex;
            justify-content: center;
            gap: 4px;
            margin-top: 8px;
        }

        .strix-nav-dot {
            width: 6px;
            height: 6px;
            background: #e1e5e9;
            border-radius: 50%;
        }

        .strix-nav-dot.active {
            background: #007cba;
        }

        .strix-badge-mockup {
            justify-content: center;
        }

        .strix-mockup-badge {
            background: linear-gradient(135deg, #4285F4, #34A853);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .strix-badge-rating {
            font-size: 16px;
            display: block;
            margin-bottom: 4px;
        }

        .strix-popup-mockup {
            justify-content: center;
        }

        .strix-mockup-popup {
            position: relative;
        }

        .strix-popup-trigger {
            background: #007cba;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .strix-popup-window {
            position: absolute;
            top: -80px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 12px;
            width: 150px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .strix-layout-info h4 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1d2327;
        }

        .strix-layout-info p {
            margin: 0;
            font-size: 13px;
            color: #646970;
        }

        /* Style Selector */
        .strix-style-selector h4 {
            margin-bottom: 16px;
            color: #1d2327;
            font-size: 16px;
            font-weight: 600;
        }

        .strix-style-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
        }

        .strix-style-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .strix-style-option:hover {
            border-color: #007cba;
        }

        .strix-style-option input:checked + .strix-style-preview + .strix-style-name {
            color: #007cba;
            font-weight: 600;
        }

        .strix-style-option input {
            position: absolute;
            opacity: 0;
        }

        .strix-style-preview {
            width: 60px;
            height: 40px;
            border-radius: 4px;
            margin-bottom: 8px;
            border: 1px solid #e1e5e9;
        }

        .strix-style-1 { background: linear-gradient(135deg, #ffffff 50%, #f8f9fa 50%); }
        .strix-style-2 { background: linear-gradient(135deg, #007cba 50%, #005a87 50%); }
        .strix-style-3 { background: linear-gradient(135deg, #ffffff 50%, #6c757d 50%); }
        .strix-style-4 { background: linear-gradient(135deg, #f8f9fa 50%, #343a40 50%); }
        .strix-style-5 { background: linear-gradient(135deg, #dc3545 50%, #6c757d 50%); }

        .strix-style-name {
            font-size: 12px;
            font-weight: 500;
            color: #646970;
            text-align: center;
        }

        /* Display Settings */
        .strix-display-settings {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .strix-setting-group h4 {
            margin-top: 0;
            margin-bottom: 16px;
            color: #1d2327;
            font-size: 16px;
            font-weight: 600;
        }

        .strix-setting-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .strix-setting-item {
            display: flex;
            flex-direction: column;
        }

        .strix-setting-label {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1d2327;
            font-size: 14px;
        }

        .strix-setting-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .strix-setting-control {
            display: flex;
            flex-direction: column;
        }

        .strix-setting-control input,
        .strix-setting-control select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .strix-setting-control input:focus,
        .strix-setting-control select:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 3px rgba(0, 123, 186, 0.1);
        }

        .strix-setting-hint {
            margin-top: 4px;
            font-size: 12px;
            color: #646970;
        }

        .strix-toggle-grid {
            display: grid;
            gap: 16px;
        }

        .strix-toggle-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e1e5e9;
        }

        .strix-toggle-control {
            position: relative;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }

        .strix-toggle-control input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .strix-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }

        .strix-toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .strix-toggle-control input:checked + .strix-toggle-slider {
            background-color: #007cba;
        }

        .strix-toggle-control input:checked + .strix-toggle-slider:before {
            transform: translateX(20px);
        }

        .strix-toggle-info {
            flex: 1;
        }

        .strix-toggle-title {
            display: block;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 2px;
        }

        .strix-toggle-desc {
            display: block;
            font-size: 13px;
            color: #646970;
        }

        /* Filter Settings */
        .strix-filter-settings {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .strix-filter-group {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e1e5e9;
        }

        .strix-filter-visual {
            flex: 1;
            text-align: center;
        }

        .strix-filter-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .strix-filter-visual h4 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1d2327;
        }

        .strix-filter-visual p {
            margin: 0;
            font-size: 13px;
            color: #646970;
        }

        .strix-filter-control {
            flex: 1;
        }

        .strix-filter-control select,
        .strix-filter-control input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .strix-filter-control select:focus,
        .strix-filter-control input:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 3px rgba(0, 123, 186, 0.1);
        }

        .strix-keyword-examples {
            margin-top: 8px;
        }

        .strix-keyword-examples small {
            color: #646970;
            font-style: italic;
        }

        .strix-filter-preview {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e1e5e9;
        }

        .strix-filter-preview h4 {
            margin-top: 0;
            margin-bottom: 16px;
            color: #1d2327;
            font-size: 16px;
            font-weight: 600;
        }

        .strix-preview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 16px;
        }

        .strix-stat-item {
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .strix-stat-number {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: #007cba;
            margin-bottom: 4px;
        }

        .strix-stat-label {
            display: block;
            font-size: 12px;
            color: #646970;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .strix-layout-selector-pro {
                grid-template-columns: 1fr;
            }
            .strix-data-source-selector {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .strix-editor-nav {
                flex-direction: column;
            }
            .strix-nav-tab {
                padding: 16px;
            }
            .strix-editor-tab {
                padding: 24px 16px;
            }
            .strix-form-grid {
                grid-template-columns: 1fr;
            }
            .strix-setting-grid {
                grid-template-columns: 1fr;
            }
            .strix-filter-group {
                flex-direction: column;
                text-align: center;
            }
            .strix-style-options {
                grid-template-columns: repeat(3, 1fr);
            }
            .strix-preview-stats {
                grid-template-columns: 1fr;
            }
        }

        /* JavaScript Animations */
        .strix-editor-tab {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Tab navigation
            $('.strix-nav-tab').on('click', function() {
                var tabId = $(this).data('tab');

                // Update tab states
                $('.strix-nav-tab').removeClass('active');
                $(this).addClass('active');

                // Show selected tab
                $('.strix-editor-tab').removeClass('active');
                $('#' + tabId).addClass('active');
            });

            // Data source selection
            $('input[name="strix_data_source"]').on('change', function() {
                var source = $(this).val();
                if (source === 'google') {
                    $('#google-settings').show();
                } else {
                    $('#google-settings').hide();
                }
            });

            // Trigger initial data source check
            $('input[name="strix_data_source"]:checked').trigger('change');
        });
        </script>
        <?php
    }

    /**
     * Widget shortcode meta box callback
     */
    public function strix_widget_shortcode_callback($post) {
        $shortcode = '[strix_widget id="' . $post->ID . '"]';
        ?>
        <p><?php _e('Copy this shortcode to display the widget anywhere on your site:', 'strix-google-reviews'); ?></p>
        <input type="text" readonly value="<?php echo esc_attr($shortcode); ?>" class="widefat" onclick="this.select();" />
        <p class="description"><?php _e('Click to select the shortcode', 'strix-google-reviews'); ?></p>
        <?php
    }

    /**
     * Save widget meta data
     */
    public function strix_save_widget_meta($post_id) {
        if (!isset($_POST['strix_widget_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['strix_widget_meta_box_nonce'], 'strix_widget_meta_box') ||
            !current_user_can('edit_post', $post_id) ||
            get_post_type($post_id) !== 'strix_widget') {
            return;
        }

        $fields = array(
            'strix_data_source',
            'strix_account_id',
            'strix_location_id',
            'strix_layout',
            'strix_layout_style',
            'strix_limit',
            'strix_show_company',
            'strix_filter_5_star',
            'strix_filter_rating',
            'strix_filter_keywords',
            'strix_sort_by'
        );

        foreach ($fields as $field) {
            $value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
            update_post_meta($post_id, '_' . $field, $value);
        }
    }

    /**
     * Add shortcode column to widget posts
     */
    public function strix_widget_columns($columns) {
        $columns['shortcode'] = __('Shortcode', 'strix-google-reviews');
        $columns['layout'] = __('Layout', 'strix-google-reviews');
        $columns['source'] = __('Source', 'strix-google-reviews');
        $columns['preview'] = __('Preview', 'strix-google-reviews');
        return $columns;
    }

    /**
     * Render custom columns for widget posts
     */
    public function strix_widget_custom_column($column, $post_id) {
        switch ($column) {
            case 'shortcode':
                $shortcode = '[strix_widget id="' . $post_id . '"]';
                echo '<div style="display:flex;align-items:center;gap:5px;">';
                echo '<code style="background:#f1f1f1;padding:2px 6px;border-radius:3px;font-size:11px;">' . $shortcode . '</code>';
                echo '<button type="button" class="button button-small" onclick="navigator.clipboard.writeText(\'' . $shortcode . '\'); this.innerHTML=\'✅\'; setTimeout(() => this.innerHTML=\'📋\', 1000);" title="' . __('Copy to clipboard', 'strix-google-reviews') . '" style="padding:0 8px;font-size:11px;">📋</button>';
                echo '</div>';
                break;
            case 'source':
                $data_source = get_post_meta($post_id, '_strix_data_source', true) ?: 'google';
                $source_labels = array(
                    'google' => '<span style="background:#e3f2fd;color:#1565c0;padding:2px 6px;border-radius:3px;font-size:11px;">🌐 Google</span>',
                    'custom' => '<span style="background:#fff3e0;color:#ef6c00;padding:2px 6px;border-radius:3px;font-size:11px;">💬 Custom</span>',
                );
                echo $source_labels[$data_source];
                break;
            case 'layout':
                $layout = get_post_meta($post_id, '_strix_layout', true) ?: 'list';
                $layout_style = get_post_meta($post_id, '_strix_layout_style', true) ?: '1';
                $limit = get_post_meta($post_id, '_strix_limit', true) ?: 5;

                $layout_names = array(
                    'list' => __('📝 List', 'strix-google-reviews'),
                    'grid' => __('🔳 Grid', 'strix-google-reviews'),
                    'slider' => __('🎠 Slider', 'strix-google-reviews'),
                    'badge' => __('🏷️ Badge', 'strix-google-reviews'),
                    'popup' => __('📋 Popup', 'strix-google-reviews'),
                );

                echo $layout_names[$layout] . ' (Style ' . $layout_style . ', ' . $limit . ' reviews)';
                break;
            case 'preview':
                $layout = get_post_meta($post_id, '_strix_layout', true) ?: 'list';
                $layout_style = get_post_meta($post_id, '_strix_layout_style', true) ?: '1';

                echo '<div style="display:flex;align-items:center;gap:8px;">';

                switch ($layout) {
                    case 'list':
                        echo '<div style="width:40px;height:30px;background:#f0f0f0;border-radius:4px;display:flex;flex-direction:column;gap:2px;padding:3px;">';
                        echo '<div style="height:3px;background:#ccc;border-radius:1px;"></div>';
                        echo '<div style="height:3px;background:#ccc;border-radius:1px;"></div>';
                        echo '<div style="height:3px;background:#ccc;border-radius:1px;"></div>';
                        echo '</div>';
                        break;
                    case 'grid':
                        echo '<div style="width:40px;height:30px;background:#f0f0f0;border-radius:4px;display:flex;flex-wrap:wrap;gap:2px;padding:3px;">';
                        echo '<div style="width:8px;height:8px;background:#ccc;border-radius:1px;"></div>';
                        echo '<div style="width:8px;height:8px;background:#ccc;border-radius:1px;"></div>';
                        echo '<div style="width:8px;height:8px;background:#ccc;border-radius:1px;"></div>';
                        echo '<div style="width:8px;height:8px;background:#ccc;border-radius:1px;"></div>';
                        echo '</div>';
                        break;
                    case 'slider':
                        echo '<div style="width:40px;height:30px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;padding:0 5px;">';
                        echo '<div style="width:8px;height:8px;background:#ccc;border-radius:50%;margin:0 2px;"></div>';
                        echo '<div style="width:8px;height:8px;background:#007cba;border-radius:50%;margin:0 2px;"></div>';
                        echo '<div style="width:8px;height:8px;background:#ccc;border-radius:50%;margin:0 2px;"></div>';
                        echo '</div>';
                        break;
                    case 'badge':
                        echo '<div style="width:40px;height:30px;background:#007cba;border-radius:15px;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:bold;">★4.8</div>';
                        break;
                    case 'popup':
                        echo '<div style="width:40px;height:30px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;cursor:pointer;">📋</div>';
                        break;
                }

                echo '<span style="font-size:11px;color:#666;">Style ' . $layout_style . '</span>';
                echo '</div>';
                break;
        }
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('strix_google_reviews', array($this, 'render_shortcode'));
        add_shortcode('strix_widget', array($this, 'render_widget_shortcode'));
        add_shortcode('strix_custom_reviews', array($this, 'render_custom_reviews_shortcode'));
        add_shortcode('strix_review_form', array($this, 'render_review_form_shortcode'));
    }

    /**
     * Fetch reviews from Google Places API
     */
    public function fetch_google_reviews($account_location = null, $force_refresh = false, $force_demo = false) {
        $demo_mode = get_option('strix_google_reviews_demo_mode', '1');
        $api_key = get_option('strix_google_reviews_api_key');
        $default_account_id = get_option('strix_google_reviews_account_id');
        $default_location_id = get_option('strix_google_reviews_location_id');

        // Use demo mode if forced, or if demo mode enabled, or if API is not configured
        if ($force_demo || ($demo_mode && $demo_mode === '1') || (!$api_key && !$force_demo)) {
            return $this->generate_mock_reviews($account_location);
        }

        // Parse account/location from parameter or use defaults
        if ($account_location) {
            $parts = explode('/', $account_location);
            $account_id = $parts[0] ?? $default_account_id;
            $location_id = $parts[1] ?? $default_location_id;
        } else {
            $account_id = $default_account_id;
            $location_id = $default_location_id;
        }

        if (!$account_id || !$location_id) {
            // Fallback to demo if no account/location ID
            return $this->generate_mock_reviews($account_location);
        }

        $cache_key = 'strix_google_reviews_' . md5($account_id . '/' . $location_id);
        $cache_timeout = get_option('strix_google_reviews_cache_timeout', 24) * HOUR_IN_SECONDS;

        if (!$force_refresh && ($cached = get_transient($cache_key))) {
            return $cached;
        }

        // First get location details
        $location_url = 'https://mybusiness.googleapis.com/v4/accounts/' . $account_id . '/locations/' . $location_id . '?' . http_build_query(array(
            'key' => $api_key,
            'readMask' => 'name,title,locationName,primaryCategory,websiteUri,regularHours,locationState,metadata,priceLists'
        ));

        $location_response = wp_remote_get($location_url);

        if (is_wp_error($location_response)) {
            return array('error' => $location_response->get_error_message());
        }

        $location_body = wp_remote_retrieve_body($location_response);
        $location_data = json_decode($location_body, true);

        if (isset($location_data['error'])) {
            return array('error' => sprintf(__('Google Business API Error: %s', 'strix-google-reviews'), $location_data['error']['message']));
        }

        // Then get reviews
        $reviews_url = 'https://mybusiness.googleapis.com/v4/accounts/' . $account_id . '/locations/' . $location_id . '/reviews?' . http_build_query(array(
            'key' => $api_key,
            'pageSize' => 50,
            'orderBy' => 'updateTime desc'
        ));

        $reviews_response = wp_remote_get($reviews_url);

        if (is_wp_error($reviews_response)) {
            return array('error' => $reviews_response->get_error_message());
        }

        $reviews_body = wp_remote_retrieve_body($reviews_response);
        $reviews_data = json_decode($reviews_body, true);

        if (isset($reviews_data['error'])) {
            return array('error' => sprintf(__('Google Business API Error: %s', 'strix-google-reviews'), $reviews_data['error']['message']));
        }

        $result = array(
            'place_info' => array(
                'name' => $location_data['locationName'] ?? $location_data['title'] ?? '',
                'rating' => 0, // Rating comes from reviews aggregation
                'address' => '', // Address info may be limited
                'website' => $location_data['websiteUri'] ?? '',
                'phone' => '' // Phone may not be available in basic API
            ),
            'reviews' => array()
        );

        $total_rating = 0;
        $rating_count = 0;

        if (isset($reviews_data['reviews'])) {
            $filter_5_star = get_option('strix_google_reviews_filter_5_star', '0');

            foreach ($reviews_data['reviews'] as $review) {
                $rating = $review['starRating'] ?? 0;

                // Convert star rating enum to number
                switch ($rating) {
                    case 'FIVE': $rating_num = 5; break;
                    case 'FOUR': $rating_num = 4; break;
                    case 'THREE': $rating_num = 3; break;
                    case 'TWO': $rating_num = 2; break;
                    case 'ONE': $rating_num = 1; break;
                    default: $rating_num = 0;
                }

                // Check for 5-star filter (from global settings or widget settings)
                $filter_5_star_enabled = get_option('strix_google_reviews_filter_5_star', '0') === '1' ||
                                        apply_filters('strix_google_reviews_filter_5_star', false);

                if ($filter_5_star_enabled && $rating_num != 5) {
                    continue;
                }

                $total_rating += $rating_num;
                $rating_count++;

                $result['reviews'][] = array(
                    'author_name' => $review['reviewer']['displayName'] ?? __('Anonymous', 'strix-google-reviews'),
                    'rating' => $rating_num,
                    'text' => $review['comment'] ?? '',
                    'time' => strtotime($review['createTime'] ?? ''),
                    'relative_time' => $this->get_relative_time(strtotime($review['createTime'] ?? '')),
                    'profile_photo_url' => $review['reviewer']['profilePhotoUrl'] ?? '',
                    'language' => 'en' // Default to English
                );
            }
        }

        // Calculate average rating
        if ($rating_count > 0) {
            $result['place_info']['rating'] = round($total_rating / $rating_count, 1);
        }

        set_transient($cache_key, $result, $cache_timeout);
        return $result;
    }

    /**
     * Get relative time string
     */
    private function get_relative_time($timestamp) {
        if (!$timestamp) {
            return '';
        }

        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return __('just now', 'strix-google-reviews');
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'strix-google-reviews'), $minutes);
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'strix-google-reviews'), $hours);
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return sprintf(_n('%d day ago', '%d days ago', $days, 'strix-google-reviews'), $days);
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return sprintf(_n('%d week ago', '%d weeks ago', $weeks, 'strix-google-reviews'), $weeks);
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return sprintf(_n('%d month ago', '%d months ago', $months, 'strix-google-reviews'), $months);
        } else {
            $years = floor($diff / 31536000);
            return sprintf(_n('%d year ago', '%d years ago', $years, 'strix-google-reviews'), $years);
        }
    }

    /**
     * Generate mock reviews for demo mode
     */
    public function generate_mock_reviews($place_id = null) {
        $mock_reviews = array(
            array(
                'author_name' => 'Анна Петрова',
                'rating' => 5,
                'text' => 'Отличное место! Очень уютная атмосфера, вежливый персонал и вкусная еда. Обязательно вернусь сюда снова!',
                'time' => time() - 86400 * 7, // 7 дней назад
                'relative_time' => 'неделю назад',
                'profile_photo_url' => '',
                'language' => 'ru'
            ),
            array(
                'author_name' => 'Михаил Сидоров',
                'rating' => 5,
                'text' => 'Прекрасное обслуживание! Шеф-повар настоящий профессионал. Каждый раз когда прихожу сюда, остаюсь доволен.',
                'time' => time() - 86400 * 14, // 14 дней назад
                'relative_time' => '2 недели назад',
                'profile_photo_url' => '',
                'language' => 'ru'
            ),
            array(
                'author_name' => 'Елена Козлова',
                'rating' => 4,
                'text' => 'Хорошее место для семейного ужина. Детям понравилось меню, а взрослым - спокойная атмосфера.',
                'time' => time() - 86400 * 21, // 21 день назад
                'relative_time' => '3 недели назад',
                'profile_photo_url' => '',
                'language' => 'ru'
            ),
            array(
                'author_name' => 'Дмитрий Иванов',
                'rating' => 5,
                'text' => 'Лучшее место в городе! Рекомендую всем знакомым. Качество блюд на высшем уровне.',
                'time' => time() - 86400 * 30, // 30 дней назад
                'relative_time' => 'месяц назад',
                'profile_photo_url' => '',
                'language' => 'ru'
            ),
            array(
                'author_name' => 'Ольга Смирнова',
                'rating' => 5,
                'text' => 'Была здесь на дне рождения. Все было организовано на 5 звезд! Спасибо за чудесный вечер.',
                'time' => time() - 86400 * 45, // 45 дней назад
                'relative_time' => '6 недель назад',
                'profile_photo_url' => '',
                'language' => 'ru'
            )
        );

        // Фильтр 5-звездочных отзывов если включено
        $filter_5_star = get_option('strix_google_reviews_filter_5_star', '0');
        if ($filter_5_star) {
            $mock_reviews = array_filter($mock_reviews, function($review) {
                return $review['rating'] == 5;
            });
        }

        // Перемешиваем отзывы для разнообразия
        shuffle($mock_reviews);

        $company_name = $place_id ? 'Ваша Компания' : 'Demo Restaurant';

        return array(
            'place_info' => array(
                'name' => $company_name,
                'rating' => 4.8,
                'address' => 'ул. Примерная, 123, Город',
                'website' => 'https://example.com',
                'phone' => '+7 (123) 456-78-90'
            ),
            'reviews' => $mock_reviews,
            'is_demo' => true
        );
    }

    /**
     * AJAX refresh reviews
     */
    public function ajax_refresh_reviews() {
        check_ajax_referer('strix_refresh_reviews', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'strix-google-reviews'));
        }

        $reviews_data = $this->fetch_google_reviews(null, true);

        if (isset($reviews_data['error'])) {
            echo '<div class="notice notice-error"><p>' . esc_html($reviews_data['error']) . '</p></div>';
            wp_die();
        }

        $this->display_reviews($reviews_data);
        wp_die();
    }

    /**
     * AJAX submit review
     */
    public function ajax_submit_review() {
        check_ajax_referer('strix_submit_review', 'nonce');

        // Allow anyone to submit reviews (guests included)
        // No need for user capability check here since it's a public form

        // Get form data
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $rating = intval($_POST['rating'] ?? 5);
        $review_text = sanitize_textarea_field($_POST['review_text'] ?? '');

        // Validation
        $errors = array();

        if (get_option('strix_google_reviews_require_name', '1') && empty($name)) {
            $errors[] = __('Name is required', 'strix-google-reviews');
        }

        if (get_option('strix_google_reviews_require_email', '0') && empty($email)) {
            $errors[] = __('Email is required', 'strix-google-reviews');
        }

        if (empty($review_text)) {
            $errors[] = __('Review text is required', 'strix-google-reviews');
        }

        if ($rating < 1 || $rating > 5) {
            $errors[] = __('Invalid rating', 'strix-google-reviews');
        }

        if (!empty($errors)) {
            wp_send_json_error(array('errors' => $errors));
        }

        // Create review post
        $post_data = array(
            'post_title' => $name,
            'post_content' => $review_text,
            'post_type' => 'strix_review',
            'post_status' => get_option('strix_google_reviews_auto_approve', '0') ? 'publish' : 'pending_review',
            'post_author' => get_current_user_id() ?: 1, // Use current user or admin (ID 1) for guests
            'meta_input' => array(
                '_strix_rating' => $rating,
                '_strix_email' => $email,
                '_strix_ip' => $_SERVER['REMOTE_ADDR'],
                '_strix_user_agent' => $_SERVER['HTTP_USER_AGENT'],
            ),
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(array('errors' => array(__('Failed to save review', 'strix-google-reviews'))));
        }

        $status = get_option('strix_google_reviews_auto_approve', '0') ? 'published' : 'pending';
        wp_send_json_success(array(
            'message' => $status === 'published' ?
                __('Thank you! Your review has been published.', 'strix-google-reviews') :
                __('Thank you! Your review is awaiting moderation.', 'strix-google-reviews'),
            'status' => $status
        ));
    }

    /**
     * AJAX load reviews
     */
    public function ajax_load_reviews() {
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 10);

        $args = array(
            'post_type' => 'strix_review',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $reviews_query = new WP_Query($args);

        if (!$reviews_query->have_posts()) {
            wp_send_json_error(array('message' => __('No reviews found', 'strix-google-reviews')));
        }

        ob_start();
        while ($reviews_query->have_posts()) {
            $reviews_query->the_post();
            $this->display_single_custom_review(get_the_ID());
        }
        $html = ob_get_clean();

        wp_reset_postdata();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $page < $reviews_query->max_num_pages,
            'total' => $reviews_query->found_posts
        ));
    }

    /**
     * Display cached reviews
     */
    public function display_cached_reviews() {
        $reviews_data = $this->fetch_google_reviews();

        if (isset($reviews_data['error'])) {
            echo '<div class="notice notice-error"><p>' . esc_html($reviews_data['error']) . '</p></div>';
            return;
        }

        $this->display_reviews($reviews_data);
    }

    /**
     * Display reviews with specific layout
     */
    public function display_reviews_layout($reviews_data, $layout = 'list', $layout_style = '1') {
        // Validate layout
        $valid_layouts = array('list', 'grid', 'slider', 'badge', 'popup');
        if (!in_array($layout, $valid_layouts)) {
            $layout = 'list';
        }

        // Validate layout style
        $layout_style = max(1, min(5, intval($layout_style)));

        $layout_method = 'display_reviews_' . $layout . '_' . $layout_style;

        if (method_exists($this, $layout_method)) {
            $this->$layout_method($reviews_data);
        } else {
            // Try without style suffix
            $fallback_method = 'display_reviews_' . $layout . '_1';
            if (method_exists($this, $fallback_method)) {
                $this->$fallback_method($reviews_data);
            } else {
                // Ultimate fallback to default display method
                $this->display_reviews($reviews_data);
            }
        }
    }

    /**
     * Display reviews HTML (default method)
     */
    public function display_reviews($reviews_data) {
        $show_company = get_option('strix_google_reviews_show_company_name', '1');
        $show_website = get_option('strix_google_reviews_show_website', '0');
        $show_phone = get_option('strix_google_reviews_show_phone', '0');
        $is_demo = isset($reviews_data['is_demo']) && $reviews_data['is_demo'];

        if ($is_demo) {
            echo '<div class="strix-demo-notice">';
            echo '<p><strong>' . __('Demo Mode:', 'strix-google-reviews') . '</strong> ' . __('These are sample reviews. Configure Google Places API to show real reviews.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        ?>
        <div class="strix-reviews-summary">
            <?php if ($show_company && !empty($reviews_data['place_info']['name'])): ?>
                <h3><?php echo esc_html($reviews_data['place_info']['name']); ?></h3>
            <?php endif; ?>

            <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                <div class="strix-rating">
                    <span class="strix-stars"><?php echo $this->render_stars($reviews_data['place_info']['rating']); ?></span>
                    <span class="strix-rating-number"><?php echo number_format($reviews_data['place_info']['rating'], 1); ?></span>
                    <span class="strix-total-reviews">(<?php printf(_n('%d review', '%d reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>)</span>
                </div>
            <?php endif; ?>

            <?php if ($show_website && !empty($reviews_data['place_info']['website'])): ?>
                <p class="strix-website"><a href="<?php echo esc_url($reviews_data['place_info']['website']); ?>" target="_blank"><?php _e('Visit Website', 'strix-google-reviews'); ?></a></p>
            <?php endif; ?>

            <?php if ($show_phone && !empty($reviews_data['place_info']['phone'])): ?>
                <p class="strix-phone"><?php echo esc_html($reviews_data['place_info']['phone']); ?></p>
            <?php endif; ?>
        </div>

        <div class="strix-reviews-list">
            <?php foreach ($reviews_data['reviews'] as $review): ?>
                <div class="strix-review-item">
                    <div class="strix-review-header">
                        <?php if (!empty($review['profile_photo_url'])): ?>
                            <img src="<?php echo esc_url($review['profile_photo_url']); ?>" alt="<?php echo esc_attr($review['author_name']); ?>" class="strix-review-avatar" />
                        <?php else: ?>
                            <div class="strix-review-avatar-placeholder"><?php echo esc_html(substr($review['author_name'], 0, 1)); ?></div>
                        <?php endif; ?>

                        <div class="strix-review-meta">
                            <h4 class="strix-review-author"><?php echo esc_html($review['author_name']); ?></h4>
                            <div class="strix-review-rating"><?php echo $this->render_stars($review['rating']); ?></div>
                            <time class="strix-review-time"><?php echo esc_html($review['relative_time']); ?></time>
                        </div>
                    </div>

                    <?php if (!empty($review['text'])): ?>
                        <div class="strix-review-text">
                            <?php echo $this->format_review_text($review['text']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php $this->display_review_button(); ?>
        <?php
    }

    /**
     * Add structured data for reviews (SEO rich snippets)
     */
    private function add_review_structured_data($reviews_data) {
        if (empty($reviews_data['reviews']) || !is_array($reviews_data['reviews'])) {
            return;
        }

        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $reviews_data['place_info']['name'] ?? '',
            'aggregateRating' => array(
                '@type' => 'AggregateRating',
                'ratingValue' => $reviews_data['place_info']['rating'] ?? 0,
                'reviewCount' => count($reviews_data['reviews']),
            ),
            'review' => array()
        );

        // Add individual reviews
        foreach (array_slice($reviews_data['reviews'], 0, 5) as $review) { // Limit to 5 reviews for structured data
            $structured_data['review'][] = array(
                '@type' => 'Review',
                'author' => array(
                    '@type' => 'Person',
                    'name' => $review['author_name'] ?? ''
                ),
                'reviewRating' => array(
                    '@type' => 'Rating',
                    'ratingValue' => $review['rating'] ?? 0,
                    'bestRating' => 5
                ),
                'reviewBody' => $review['text'] ?? '',
                'datePublished' => date('Y-m-d', $review['time'] ?? time())
            );
        }

        echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
    }

    /**
     * Format review text with read more functionality
     */
    private function format_review_text($text, $max_length = 200) {
        $text = wp_kses_post(nl2br($text));

        if (strlen(strip_tags($text)) <= $max_length) {
            return $text;
        }

        $truncated = substr(strip_tags($text), 0, $max_length);
        $last_space = strrpos($truncated, ' ');

        if ($last_space !== false) {
            $truncated = substr($truncated, 0, $last_space);
        }

        $review_id = 'review_' . uniqid();

        return '<div class="strix-review-text-content" id="' . $review_id . '">' .
               '<span class="strix-review-text-preview">' . $truncated . '...</span>' .
               '<span class="strix-review-text-full" style="display:none;">' . $text . '</span>' .
               '<a href="#" class="strix-read-more" data-review-id="' . $review_id . '">' .
               __('Read more', 'strix-google-reviews') . '</a>' .
               '</div>';
    }

    /**
     * Display "Review us on Google" button
     */
    public function display_review_button() {
        $show_button = get_option('strix_google_reviews_show_review_button', '1');
        if (!$show_button) {
            return;
        }

        $button_text = get_option('strix_google_reviews_review_button_text', __('Review us on Google', 'strix-google-reviews'));
        $account_id = get_option('strix_google_reviews_account_id');
        $location_id = get_option('strix_google_reviews_location_id');

        if (!$account_id || !$location_id) {
            return; // Don't show button if no location configured
        }

        // Generate Google review URL
        $google_review_url = 'https://search.google.com/local/writereview?placeid=' . urlencode($account_id . '/' . $location_id);

        ?>
        <div class="strix-review-button-container">
            <a href="<?php echo esc_url($google_review_url); ?>" target="_blank" class="strix-review-button">
                <span class="strix-review-button-text"><?php echo esc_html($button_text); ?></span>
                <svg class="strix-google-icon" viewBox="0 0 24 24" width="20" height="20">
                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
            </a>
        </div>
        <?php
    }

    /**
     * Apply filters to reviews data
     */
    private function apply_review_filters($reviews_data, $atts) {
        if (!isset($reviews_data['reviews']) || !is_array($reviews_data['reviews']) || empty($reviews_data['reviews'])) {
            return $reviews_data;
        }

        $reviews = $reviews_data['reviews'];

        // Filter by minimum rating
        if (!empty($atts['filter_rating']) && is_numeric($atts['filter_rating'])) {
            $min_rating = intval($atts['filter_rating']);
            $reviews = array_filter($reviews, function($review) use ($min_rating) {
                return ($review['rating'] ?? 0) >= $min_rating;
            });
        }

        // Filter by keywords
        if (!empty($atts['filter_keywords'])) {
            $keywords = array_map('trim', explode(',', $atts['filter_keywords']));
            $reviews = array_filter($reviews, function($review) use ($keywords) {
                $text = strtolower($review['text'] ?? '');
                foreach ($keywords as $keyword) {
                    if (strpos($text, strtolower($keyword)) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Sort reviews
        if (!empty($atts['sort_by'])) {
            switch ($atts['sort_by']) {
                case 'oldest':
                    usort($reviews, function($a, $b) {
                        return ($a['time'] ?? 0) <=> ($b['time'] ?? 0);
                    });
                    break;
                case 'highest':
                    usort($reviews, function($a, $b) {
                        return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
                    });
                    break;
                case 'lowest':
                    usort($reviews, function($a, $b) {
                        return ($a['rating'] ?? 0) <=> ($b['rating'] ?? 0);
                    });
                    break;
                case 'newest':
                default:
                    usort($reviews, function($a, $b) {
                        return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
                    });
                    break;
            }
        }

        $reviews_data['reviews'] = array_values($reviews); // Re-index array
        return $reviews_data;
    }

    /**
     * Display reviews in List Layout 1
     */
    public function display_reviews_list_1($reviews_data) {
        $this->display_reviews($reviews_data);
    }

    /**
     * Display reviews in List Layout 2
     */
    public function display_reviews_list_2($reviews_data) {
        $show_company = get_option('strix_google_reviews_show_company_name', '1');
        $show_website = get_option('strix_google_reviews_show_website', '0');
        $show_phone = get_option('strix_google_reviews_show_phone', '0');
        $is_demo = isset($reviews_data['is_demo']) && $reviews_data['is_demo'];

        if ($is_demo) {
            echo '<div class="strix-demo-notice">';
            echo '<p><strong>' . __('Demo Mode:', 'strix-google-reviews') . '</strong> ' . __('These are sample reviews. Configure Google Business Profile API to show real reviews.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        ?>
        <div class="strix-reviews-summary">
            <?php if ($show_company && !empty($reviews_data['place_info']['name'])): ?>
                <h3><?php echo esc_html($reviews_data['place_info']['name']); ?></h3>
            <?php endif; ?>

            <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                <div class="strix-rating">
                    <span class="strix-stars"><?php echo $this->render_stars($reviews_data['place_info']['rating']); ?></span>
                    <span class="strix-rating-number"><?php echo number_format($reviews_data['place_info']['rating'], 1); ?></span>
                    <span class="strix-total-reviews">(<?php printf(_n('%d review', '%d reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>)</span>
                </div>
            <?php endif; ?>

            <?php if ($show_website && !empty($reviews_data['place_info']['website'])): ?>
                <p class="strix-website"><a href="<?php echo esc_url($reviews_data['place_info']['website']); ?>" target="_blank"><?php _e('Visit Website', 'strix-google-reviews'); ?></a></p>
            <?php endif; ?>

            <?php if ($show_phone && !empty($reviews_data['place_info']['phone'])): ?>
                <p class="strix-phone"><?php echo esc_html($reviews_data['place_info']['phone']); ?></p>
            <?php endif; ?>
        </div>

        <div class="strix-reviews-list strix-list-layout-2">
            <?php foreach ($reviews_data['reviews'] as $review): ?>
                <div class="strix-review-item strix-list-item-2">
                    <div class="strix-review-content-wrapper">
                        <div class="strix-review-rating-stars">
                            <?php echo $this->render_stars($review['rating']); ?>
                        </div>

                        <div class="strix-review-text">
                            <?php echo $this->format_review_text($review['text']); ?>
                        </div>

                        <div class="strix-review-meta">
                            <div class="strix-review-author-info">
                                <?php if (!empty($review['profile_photo_url'])): ?>
                                    <img src="<?php echo esc_url($review['profile_photo_url']); ?>" alt="<?php echo esc_attr($review['author_name']); ?>" class="strix-review-avatar" />
                                <?php else: ?>
                                    <div class="strix-review-avatar-placeholder"><?php echo esc_html(substr($review['author_name'], 0, 1)); ?></div>
                                <?php endif; ?>
                                <span class="strix-review-author"><?php echo esc_html($review['author_name']); ?></span>
                            </div>
                            <time class="strix-review-time"><?php echo esc_html($review['relative_time']); ?></time>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php $this->display_review_button(); ?>
        <?php
    }

    /**
     * Display reviews in Grid Layout 1
     */
    public function display_reviews_grid_1($reviews_data) {
        $show_company = get_option('strix_google_reviews_show_company_name', '1');
        $show_website = get_option('strix_google_reviews_show_website', '0');
        $show_phone = get_option('strix_google_reviews_show_phone', '0');
        $is_demo = isset($reviews_data['is_demo']) && $reviews_data['is_demo'];

        if ($is_demo) {
            echo '<div class="strix-demo-notice">';
            echo '<p><strong>' . __('Demo Mode:', 'strix-google-reviews') . '</strong> ' . __('These are sample reviews. Configure Google Business Profile API to show real reviews.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        ?>
        <div class="strix-reviews-summary">
            <?php if ($show_company && !empty($reviews_data['place_info']['name'])): ?>
                <h3><?php echo esc_html($reviews_data['place_info']['name']); ?></h3>
            <?php endif; ?>

            <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                <div class="strix-rating">
                    <span class="strix-stars"><?php echo $this->render_stars($reviews_data['place_info']['rating']); ?></span>
                    <span class="strix-rating-number"><?php echo number_format($reviews_data['place_info']['rating'], 1); ?></span>
                    <span class="strix-total-reviews">(<?php printf(_n('%d review', '%d reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>)</span>
                </div>
            <?php endif; ?>

            <?php if ($show_website && !empty($reviews_data['place_info']['website'])): ?>
                <p class="strix-website"><a href="<?php echo esc_url($reviews_data['place_info']['website']); ?>" target="_blank"><?php _e('Visit Website', 'strix-google-reviews'); ?></a></p>
            <?php endif; ?>

            <?php if ($show_phone && !empty($reviews_data['place_info']['phone'])): ?>
                <p class="strix-phone"><?php echo esc_html($reviews_data['place_info']['phone']); ?></p>
            <?php endif; ?>
        </div>

        <div class="strix-reviews-grid strix-grid-layout-1">
            <?php foreach ($reviews_data['reviews'] as $review): ?>
                <div class="strix-review-item strix-grid-item-1">
                    <div class="strix-review-header">
                        <?php if (!empty($review['profile_photo_url'])): ?>
                            <img src="<?php echo esc_url($review['profile_photo_url']); ?>" alt="<?php echo esc_attr($review['author_name']); ?>" class="strix-review-avatar" />
                        <?php else: ?>
                            <div class="strix-review-avatar-placeholder"><?php echo esc_html(substr($review['author_name'], 0, 1)); ?></div>
                        <?php endif; ?>

                        <div class="strix-review-meta">
                            <h4 class="strix-review-author"><?php echo esc_html($review['author_name']); ?></h4>
                            <div class="strix-review-rating"><?php echo $this->render_stars($review['rating']); ?></div>
                            <time class="strix-review-time"><?php echo esc_html($review['relative_time']); ?></time>
                        </div>
                    </div>

                    <?php if (!empty($review['text'])): ?>
                        <div class="strix-review-text">
                            <?php echo $this->format_review_text($review['text']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php $this->display_review_button(); ?>
        <?php
    }

    /**
     * Display reviews in Slider Layout 1
     */
    public function display_reviews_slider_1($reviews_data) {
        $show_company = get_option('strix_google_reviews_show_company_name', '1');
        $show_website = get_option('strix_google_reviews_show_website', '0');
        $show_phone = get_option('strix_google_reviews_show_phone', '0');
        $is_demo = isset($reviews_data['is_demo']) && $reviews_data['is_demo'];

        if ($is_demo) {
            echo '<div class="strix-demo-notice">';
            echo '<p><strong>' . __('Demo Mode:', 'strix-google-reviews') . '</strong> ' . __('These are sample reviews. Configure Google Business Profile API to show real reviews.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        ?>
        <div class="strix-reviews-summary">
            <?php if ($show_company && !empty($reviews_data['place_info']['name'])): ?>
                <h3><?php echo esc_html($reviews_data['place_info']['name']); ?></h3>
            <?php endif; ?>

            <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                <div class="strix-rating">
                    <span class="strix-stars"><?php echo $this->render_stars($reviews_data['place_info']['rating']); ?></span>
                    <span class="strix-rating-number"><?php echo number_format($reviews_data['place_info']['rating'], 1); ?></span>
                    <span class="strix-total-reviews">(<?php printf(_n('%d review', '%d reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>)</span>
                </div>
            <?php endif; ?>

            <?php if ($show_website && !empty($reviews_data['place_info']['website'])): ?>
                <p class="strix-website"><a href="<?php echo esc_url($reviews_data['place_info']['website']); ?>" target="_blank"><?php _e('Visit Website', 'strix-google-reviews'); ?></a></p>
            <?php endif; ?>

            <?php if ($show_phone && !empty($reviews_data['place_info']['phone'])): ?>
                <p class="strix-phone"><?php echo esc_html($reviews_data['place_info']['phone']); ?></p>
            <?php endif; ?>
        </div>

        <div class="strix-reviews-slider strix-slider-layout-1">
            <div class="swiper strix-slider-1">
                <div class="swiper-wrapper">
                    <?php foreach ($reviews_data['reviews'] as $review): ?>
                        <div class="swiper-slide">
                            <div class="strix-review-item strix-slide-item">
                                <div class="strix-list-inner">
                                    <div class="strix-rating-wrap">
                                        <div class="strix-rating">
                                            <?php echo $this->render_stars($review['rating']); ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($review['text'])): ?>
                                        <div class="strix-content">
                                            <?php echo $this->format_review_text($review['text']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="strix-review-info">
                                        <div class="strix-review-meta">
                                            <?php if (!empty($review['profile_photo_url'])): ?>
                                                <img src="<?php echo esc_url($review['profile_photo_url']); ?>" alt="<?php echo esc_attr($review['author_name']); ?>" class="strix-review-avatar" />
                                            <?php else: ?>
                                                <div class="strix-review-avatar-placeholder"><?php echo esc_html(substr($review['author_name'], 0, 1)); ?></div>
                                            <?php endif; ?>

                                            <div class="strix-review-details">
                                                <h4 class="strix-title"><?php echo esc_html($review['author_name']); ?></h4>
                                                <p class="strix-days-ago"><?php echo esc_html($review['relative_time']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <?php $this->display_review_button(); ?>
        <?php
    }

    /**
     * Display reviews in Badge Layout 1
     */
    public function display_reviews_badge_1($reviews_data) {
        $show_company = get_option('strix_google_reviews_show_company_name', '1');
        $is_demo = isset($reviews_data['is_demo']) && $reviews_data['is_demo'];

        if ($is_demo) {
            echo '<div class="strix-demo-notice">';
            echo '<p><strong>' . __('Demo Mode:', 'strix-google-reviews') . '</strong> ' . __('These are sample reviews. Configure Google Business Profile API to show real reviews.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        ?>
        <div class="strix-badge-layout-1">
            <div class="strix-badge-content">
                <?php if ($show_company && !empty($reviews_data['place_info']['name'])): ?>
                    <h3><?php echo esc_html($reviews_data['place_info']['name']); ?></h3>
                <?php endif; ?>

                <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                    <div class="strix-badge-stars">
                        <?php echo $this->render_stars($reviews_data['place_info']['rating']); ?>
                    </div>
                    <div class="strix-badge-rating">
                        <?php echo number_format($reviews_data['place_info']['rating'], 1); ?>
                    </div>
                    <p class="strix-badge-text">
                        <?php printf(_n('%d Review', '%d Reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php $this->display_review_button(); ?>
        <?php
    }

    /**
     * Display reviews in Popup Layout 1
     */
    public function display_reviews_popup_1($reviews_data) {
        $show_company = get_option('strix_google_reviews_show_company_name', '1');
        $is_demo = isset($reviews_data['is_demo']) && $reviews_data['is_demo'];

        if ($is_demo) {
            echo '<div class="strix-demo-notice">';
            echo '<p><strong>' . __('Demo Mode:', 'strix-google-reviews') . '</strong> ' . __('These are sample reviews. Configure Google Business Profile API to show real reviews.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        // Display trigger button
        ?>
        <div class="strix-popup-trigger">
            <button class="strix-popup-btn" data-popup="reviews-popup-1">
                <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                    <span class="strix-popup-rating"><?php echo $this->render_stars($reviews_data['place_info']['rating']); ?></span>
                    <span class="strix-popup-score"><?php echo number_format($reviews_data['place_info']['rating'], 1); ?></span>
                    <span class="strix-popup-count">(<?php printf(_n('%d review', '%d reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>)</span>
                <?php endif; ?>
            </button>
        </div>

        <!-- Popup Modal -->
        <div id="reviews-popup-1" class="strix-popup-modal">
            <div class="strix-popup-overlay"></div>
            <div class="strix-popup-content">
                <div class="strix-popup-header">
                    <h3><?php echo esc_html($reviews_data['place_info']['name'] ?? __('Reviews', 'strix-google-reviews')); ?></h3>
                    <button class="strix-popup-close">&times;</button>
                </div>

                <div class="strix-popup-body">
                    <?php if (!empty($reviews_data['place_info']['rating'])): ?>
                        <div class="strix-popup-summary">
                            <div class="strix-popup-rating">
                                <span class="strix-popup-stars"><?php echo $this->render_stars($reviews_data['place_info']['rating']); ?></span>
                                <span class="strix-popup-rating-number"><?php echo number_format($reviews_data['place_info']['rating'], 1); ?></span>
                                <span class="strix-popup-total">(<?php printf(_n('%d review', '%d reviews', count($reviews_data['reviews']), 'strix-google-reviews'), count($reviews_data['reviews'])); ?>)</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="strix-popup-reviews">
                        <?php foreach ($reviews_data['reviews'] as $review): ?>
                            <div class="strix-popup-review-item">
                                <div class="strix-popup-review-header">
                                    <?php if (!empty($review['profile_photo_url'])): ?>
                                        <img src="<?php echo esc_url($review['profile_photo_url']); ?>" alt="<?php echo esc_attr($review['author_name']); ?>" class="strix-popup-review-avatar" />
                                    <?php else: ?>
                                        <div class="strix-popup-review-avatar-placeholder"><?php echo esc_html(substr($review['author_name'], 0, 1)); ?></div>
                                    <?php endif; ?>

                                    <div class="strix-popup-review-meta">
                                        <h4 class="strix-popup-review-author"><?php echo esc_html($review['author_name']); ?></h4>
                                        <div class="strix-popup-review-rating"><?php echo $this->render_stars($review['rating']); ?></div>
                                        <time class="strix-popup-review-time"><?php echo esc_html($review['relative_time']); ?></time>
                                    </div>
                                </div>

                                <?php if (!empty($review['text'])): ?>
                                    <div class="strix-popup-review-text">
                                        <?php echo $this->format_review_text($review['text']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render star rating HTML
     */
    private function render_stars($rating) {
        $stars = '';
        $full_stars = floor($rating);
        $half_star = $rating - $full_stars >= 0.5;
        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

        for ($i = 0; $i < $full_stars; $i++) {
            $stars .= '★';
        }

        if ($half_star) {
            $stars .= '☆';
        }

        for ($i = 0; $i < $empty_stars; $i++) {
            $stars .= '☆';
        }

        return '<span class="strix-stars" data-rating="' . esc_attr($rating) . '">' . $stars . '</span>';
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'account_id' => '',
            'location_id' => '',
            'limit' => 5,
            'show_company' => null,
            'show_rating' => '1',
            'layout' => 'list', // list, grid, slider, badge, popup
            'layout_style' => '1', // style number for each layout type
            'filter_rating' => '', // Filter by minimum rating (1-5)
            'filter_keywords' => '', // Filter by keywords (comma separated)
            'sort_by' => 'newest', // newest, oldest, highest, lowest
            'demo' => null
        ), $atts);

        // Validate and sanitize attributes
        $atts['limit'] = max(1, min(50, intval($atts['limit'])));
        $valid_layouts = array('list', 'grid', 'slider', 'badge', 'popup');
        $atts['layout'] = in_array($atts['layout'], $valid_layouts) ? $atts['layout'] : 'list';
        $atts['layout_style'] = max(1, min(5, intval($atts['layout_style'])));
        $atts['filter_rating'] = is_numeric($atts['filter_rating']) ? max(1, min(5, intval($atts['filter_rating']))) : '';
        $atts['filter_keywords'] = is_string($atts['filter_keywords']) ? sanitize_text_field($atts['filter_keywords']) : '';
        $valid_sorts = array('newest', 'oldest', 'highest', 'lowest');
        $atts['sort_by'] = in_array($atts['sort_by'], $valid_sorts) ? $atts['sort_by'] : 'newest';

        $force_demo = ($atts['demo'] === 'true' || $atts['demo'] === '1');
        $account_location = '';
        if (!empty($atts['account_id']) && !empty($atts['location_id'])) {
            $account_location = sanitize_text_field($atts['account_id']) . '/' . sanitize_text_field($atts['location_id']);
        }

        $reviews_data = $this->fetch_google_reviews($account_location, false, $force_demo);

        if (isset($reviews_data['error'])) {
            return '<div class="strix-google-reviews-error">' . esc_html($reviews_data['error']) . '</div>';
        }

        // Validate reviews data
        if (!isset($reviews_data['reviews']) || !is_array($reviews_data['reviews'])) {
            return '<div class="strix-google-reviews-error">' . __('No reviews available', 'strix-google-reviews') . '</div>';
        }

        // Limit reviews
        if (count($reviews_data['reviews']) > $atts['limit']) {
            $reviews_data['reviews'] = array_slice($reviews_data['reviews'], 0, $atts['limit']);
        }

        // Override display settings if specified in shortcode
        if ($atts['show_company'] !== null) {
            $original_show_company = get_option('strix_google_reviews_show_company_name', '1');
            update_option('strix_google_reviews_show_company_name', $atts['show_company']);
        }

        // Apply filters to reviews data
        $reviews_data = $this->apply_review_filters($reviews_data, $atts);

        ob_start();
        echo '<div class="strix-google-reviews-shortcode strix-layout-' . esc_attr($atts['layout']) . ' strix-layout-' . esc_attr($atts['layout']) . '-' . esc_attr($atts['layout_style']) . '">';
        $this->display_reviews_layout($reviews_data, $atts['layout'], $atts['layout_style']);

        // Add structured data for SEO
        $this->add_review_structured_data($reviews_data);
        echo '</div>';

        // Restore original setting
        if ($atts['show_company'] !== null) {
            update_option('strix_google_reviews_show_company_name', $original_show_company);
        }

        return ob_get_clean();
    }

    /**
     * Render widget shortcode
     */
    public function render_widget_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
        ), $atts);

        if (empty($atts['id']) || !is_numeric($atts['id'])) {
            return '<div class="strix-widget-error">' . __('Invalid widget ID', 'strix-google-reviews') . '</div>';
        }

        $widget_post = get_post($atts['id']);
        if (!$widget_post || $widget_post->post_type !== 'strix_widget') {
            return '<div class="strix-widget-error">' . __('Widget not found', 'strix-google-reviews') . '</div>';
        }

        // Get widget settings from post meta with validation
        $data_source = get_post_meta($atts['id'], '_strix_data_source', true);
        if (!in_array($data_source, array('google', 'custom'))) {
            $data_source = 'google';
        }

        $account_id = get_post_meta($atts['id'], '_strix_account_id', true);
        $location_id = get_post_meta($atts['id'], '_strix_location_id', true);

        $layout = get_post_meta($atts['id'], '_strix_layout', true);
        $valid_layouts = array('list', 'grid', 'slider', 'badge', 'popup');
        if (!in_array($layout, $valid_layouts)) {
            $layout = 'list';
        }

        $layout_style = get_post_meta($atts['id'], '_strix_layout_style', true);
        $layout_style = max(1, min(5, intval($layout_style)));

        $limit = get_post_meta($atts['id'], '_strix_limit', true);
        $limit = max(1, min(50, intval($limit)));

        $show_company = get_post_meta($atts['id'], '_strix_show_company', true) ?: '1';
        $filter_5_star = get_post_meta($atts['id'], '_strix_filter_5_star', true) ?: '0';
        $filter_rating = get_post_meta($atts['id'], '_strix_filter_rating', true) ?: '';
        $filter_keywords = get_post_meta($atts['id'], '_strix_filter_keywords', true) ?: '';
        $sort_by = get_post_meta($atts['id'], '_strix_sort_by', true) ?: 'newest';

        // Choose which shortcode to use based on data source
        if ($data_source === 'custom') {
            // Use custom reviews shortcode
            $shortcode_atts = array(
                'limit' => $limit,
                'show_form' => '0', // Don't show form in widgets
                'pagination' => '0', // No pagination in widgets
            );

            // Apply filters if set
            if ($filter_rating) {
                $shortcode_atts['filter_rating'] = $filter_rating;
            }
            if ($filter_keywords) {
                $shortcode_atts['filter_keywords'] = $filter_keywords;
            }
            if ($sort_by !== 'newest') {
                $shortcode_atts['sort_by'] = $sort_by;
            }

            return $this->render_custom_reviews_shortcode($shortcode_atts);
        } else {
            // Use Google reviews shortcode
            $shortcode_atts = array(
                'account_id' => $account_id,
                'location_id' => $location_id,
                'layout' => $layout,
                'layout_style' => $layout_style,
                'limit' => $limit,
                'show_company' => $show_company,
                'filter_rating' => $filter_rating,
                'filter_keywords' => $filter_keywords,
                'sort_by' => $sort_by,
            );

            // Override global filter settings if specified in widget
            if ($filter_5_star === '1') {
                add_filter('strix_google_reviews_filter_5_star', '__return_true');
            }
        }

        // Call the main shortcode renderer
        $output = $this->render_shortcode($shortcode_atts);

        // Remove filter override
        if ($filter_5_star === '1') {
            remove_filter('strix_google_reviews_filter_5_star', '__return_true');
        }

        return $output;
    }

    /**
     * Render custom reviews shortcode
     */
    public function render_custom_reviews_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'show_form' => null,
            'pagination' => '1',
        ), $atts);

        $show_form = $atts['show_form'] !== null ? $atts['show_form'] : get_option('strix_google_reviews_show_form', '1');

        ob_start();
        echo '<div class="strix-custom-reviews-container">';

        // Show form if enabled
        if ($show_form) {
            echo '<div class="strix-review-form-section">';
            $this->display_review_form();
            echo '</div>';
        }

        // Show reviews
        echo '<div class="strix-custom-reviews-list" data-page="1" data-limit="' . esc_attr($atts['limit']) . '">';
        $this->display_custom_reviews($atts['limit'], 1);
        echo '</div>';

        // Pagination
        if ($atts['pagination']) {
            echo '<div class="strix-reviews-pagination">';
            echo '<button class="strix-load-more-reviews button">' . __('Load More', 'strix-google-reviews') . '</button>';
            echo '<span class="strix-loading" style="display:none;">' . __('Loading...', 'strix-google-reviews') . '</span>';
            echo '</div>';
        }

        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render review form shortcode
     */
    public function render_review_form_shortcode($atts) {
        ob_start();
        $this->display_review_form();
        return ob_get_clean();
    }

    /**
     * Display review submission form
     */
    public function display_review_form() {
        $require_name = get_option('strix_google_reviews_require_name', '1');
        $require_email = get_option('strix_google_reviews_require_email', '0');

        ?>
        <div class="strix-review-form">
            <h3><?php _e('Write a Review', 'strix-google-reviews'); ?></h3>
            <form id="strix-review-form" method="post">
                <?php wp_nonce_field('strix_submit_review', 'strix_review_nonce'); ?>

                <div class="strix-form-group">
                    <label for="strix-review-name">
                        <?php _e('Your Name', 'strix-google-reviews'); ?>
                        <?php if ($require_name): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <input type="text" id="strix-review-name" name="name" required="<?php echo $require_name ? 'required' : ''; ?>" />
                </div>

                <?php if ($require_email || get_option('strix_google_reviews_require_email', '0')): ?>
                <div class="strix-form-group">
                    <label for="strix-review-email">
                        <?php _e('Email', 'strix-google-reviews'); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="email" id="strix-review-email" name="email" required />
                </div>
                <?php endif; ?>

                <div class="strix-form-group">
                    <label><?php _e('Rating', 'strix-google-reviews'); ?><span class="required">*</span></label>
                    <div class="strix-rating-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?> />
                            <label for="star<?php echo $i; ?>" title="<?php printf(__('%d star', 'strix-google-reviews'), $i); ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="strix-form-group">
                    <label for="strix-review-text">
                        <?php _e('Your Review', 'strix-google-reviews'); ?><span class="required">*</span>
                    </label>
                    <textarea id="strix-review-text" name="review_text" rows="4" required placeholder="<?php _e('Share your experience...', 'strix-google-reviews'); ?>"></textarea>
                </div>

                <div class="strix-form-group">
                    <button type="submit" class="strix-submit-review button">
                        <?php _e('Submit Review', 'strix-google-reviews'); ?>
                    </button>
                    <span class="strix-form-loading" style="display:none;"><?php _e('Submitting...', 'strix-google-reviews'); ?></span>
                </div>

                <div class="strix-form-message" style="display:none;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Display custom reviews
     */
    public function display_custom_reviews($limit = 10, $page = 1) {
        $args = array(
            'post_type' => 'strix_review',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $reviews_query = new WP_Query($args);

        if (!$reviews_query->have_posts()) {
            echo '<p class="strix-no-reviews">' . __('No reviews yet. Be the first to leave a review!', 'strix-google-reviews') . '</p>';
            return;
        }

        while ($reviews_query->have_posts()) {
            $reviews_query->the_post();
            $this->display_single_custom_review(get_the_ID());
        }

        wp_reset_postdata();
    }

    /**
     * Display single custom review
     */
    public function display_single_custom_review($post_id) {
        $rating = get_post_meta($post_id, '_strix_rating', true);
        $email = get_post_meta($post_id, '_strix_email', true);

        ?>
        <div class="strix-custom-review-item" data-review-id="<?php echo esc_attr($post_id); ?>">
            <div class="strix-review-header">
                <div class="strix-review-avatar">
                    <?php echo esc_html(substr(get_the_title(), 0, 1)); ?>
                </div>
                <div class="strix-review-meta">
                    <h4 class="strix-review-author"><?php echo esc_html(get_the_title()); ?></h4>
                    <div class="strix-review-rating">
                        <?php echo $this->render_stars($rating); ?>
                    </div>
                    <time class="strix-review-time"><?php echo esc_html(get_the_date()); ?></time>
                </div>
            </div>

            <div class="strix-review-text">
                <?php echo wp_kses_post(get_the_content()); ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Initialize the plugin
 */
function strix_google_reviews_init() {
    return Strix_Google_Reviews::get_instance();
}

// Plugin activation hook
register_activation_hook(__FILE__, 'strix_google_reviews_activate');

function strix_google_reviews_activate() {
    // Set flag to flush rewrite rules on next init
    update_option('strix_reviews_flush_rewrite_rules', true);

    // Create the custom post types immediately
    $plugin = Strix_Google_Reviews::get_instance();
    if (method_exists($plugin, 'register_custom_post_type')) {
        $plugin->register_custom_post_type();
    }
    if (method_exists($plugin, 'register_widget_post_type')) {
        $plugin->register_widget_post_type();
    }
}

// Start the plugin
strix_google_reviews_init();