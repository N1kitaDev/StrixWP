<?php
/**
 * Plugin Name: Strix Google Reviews
 * Plugin URI: https://strixmedia.ru
 * Description: Clean Google Places Reviews plugin for WordPress
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
        add_action('wp_ajax_strix_refresh_reviews', array($this, 'ajax_refresh_reviews'));
        add_action('wp_ajax_nopriv_strix_refresh_reviews', array($this, 'ajax_refresh_reviews'));

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
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_place_id');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_company_name');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_website');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_show_phone');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_filter_5_star');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_cache_timeout');
        register_setting('strix_google_reviews_settings', 'strix_google_reviews_demo_mode');

        add_settings_section(
            'strix_google_reviews_main',
            __('Google Places API Settings', 'strix-google-reviews'),
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
            'strix_google_reviews_place_id',
            __('Google Place ID', 'strix-google-reviews'),
            array($this, 'place_id_field_callback'),
            'strix-google-reviews',
            'strix_google_reviews_main'
        );

        add_settings_section(
            'strix_google_reviews_display',
            __('Display Settings', 'strix-google-reviews'),
            array($this, 'display_section_callback'),
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

        add_submenu_page(
            'strix-google-reviews',
            __('Reviews', 'strix-google-reviews'),
            __('Reviews', 'strix-google-reviews'),
            'manage_options',
            'strix-google-reviews-reviews',
            array($this, 'reviews_page')
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure your Google Places API settings to display reviews from Google.', 'strix-google-reviews') . '</p>';
        echo '<p>' . __('Get your API key from <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a> and enable Places API.', 'strix-google-reviews') . '</p>';
        echo '<p>' . __('Find your Place ID using <a href="https://developers.google.com/places/place-id" target="_blank">Google Place ID Finder</a>.', 'strix-google-reviews') . '</p>';
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
     * Place ID field callback
     */
    public function place_id_field_callback() {
        $place_id = get_option('strix_google_reviews_place_id');
        echo '<input type="text" name="strix_google_reviews_place_id" value="' . esc_attr($place_id) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your Google Place ID. Example: ChIJd8BlQ2gVwAARRapDhqKPvCQ', 'strix-google-reviews') . '</p>';
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
        $value = get_option('strix_google_reviews_demo_mode', '1');
        echo '<input type="checkbox" name="strix_google_reviews_demo_mode" value="1"' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Enable demo mode to show sample reviews when API is not configured', 'strix-google-reviews') . '</label>';
        echo '<p class="description">' . __('Demo mode displays sample reviews for testing purposes. Disable it to show only real reviews.', 'strix-google-reviews') . '</p>';
    }

    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap strix-google-reviews-admin">
            <h1><?php _e('Google Reviews Settings', 'strix-google-reviews'); ?></h1>

            <?php if (!get_option('strix_google_reviews_api_key') || !get_option('strix_google_reviews_place_id')): ?>
            <div class="notice notice-info">
                <p><?php _e('Configure your Google Places API key and Place ID to start displaying reviews.', 'strix-google-reviews'); ?></p>
            </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('strix_google_reviews_settings');
                do_settings_sections('strix-google-reviews');
                submit_button(__('Save Settings', 'strix-google-reviews'));
                ?>
            </form>
        </div>
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
    }

    /**
     * Register widgets
     */
    public function register_widgets() {
        register_widget('Strix_Google_Reviews_Widget');
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('strix_google_reviews', array($this, 'render_shortcode'));
    }

    /**
     * Fetch reviews from Google Places API
     */
    public function fetch_google_reviews($place_id = null, $force_refresh = false, $force_demo = false) {
        $demo_mode = get_option('strix_google_reviews_demo_mode', '1');
        $api_key = get_option('strix_google_reviews_api_key');
        $default_place_id = get_option('strix_google_reviews_place_id');

        // Use demo mode if enabled or forced, or if API is not configured
        if ($force_demo || $demo_mode || !$api_key) {
            return $this->generate_mock_reviews($place_id);
        }

        $place_id = $place_id ?: $default_place_id;
        if (!$place_id) {
            // Fallback to demo if no place ID
            return $this->generate_mock_reviews($place_id);
        }

        $cache_key = 'strix_google_reviews_' . md5($place_id);
        $cache_timeout = get_option('strix_google_reviews_cache_timeout', 24) * HOUR_IN_SECONDS;

        if (!$force_refresh && ($cached = get_transient($cache_key))) {
            return $cached;
        }

        $url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query(array(
            'place_id' => $place_id,
            'fields' => 'name,rating,reviews,formatted_address,website,formatted_phone_number',
            'key' => $api_key,
            'reviews_sort' => 'most_relevant'
        ));

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data['status'] !== 'OK') {
            return array('error' => sprintf(__('Google API Error: %s', 'strix-google-reviews'), $data['status']));
        }

        $result = array(
            'place_info' => array(
                'name' => $data['result']['name'] ?? '',
                'rating' => $data['result']['rating'] ?? 0,
                'address' => $data['result']['formatted_address'] ?? '',
                'website' => $data['result']['website'] ?? '',
                'phone' => $data['result']['formatted_phone_number'] ?? ''
            ),
            'reviews' => array()
        );

        if (isset($data['result']['reviews'])) {
            $filter_5_star = get_option('strix_google_reviews_filter_5_star', '0');

            foreach ($data['result']['reviews'] as $review) {
                if ($filter_5_star && $review['rating'] != 5) {
                    continue;
                }

                $result['reviews'][] = array(
                    'author_name' => $review['author_name'] ?? '',
                    'rating' => $review['rating'] ?? 0,
                    'text' => $review['text'] ?? '',
                    'time' => $review['time'] ?? 0,
                    'relative_time' => $review['relative_time_description'] ?? '',
                    'profile_photo_url' => $review['profile_photo_url'] ?? '',
                    'language' => $review['language'] ?? 'en'
                );
            }
        }

        set_transient($cache_key, $result, $cache_timeout);
        return $result;
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
     * Display reviews HTML
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
                            <?php echo wp_kses_post(nl2br($review['text'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
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
            'place_id' => '',
            'limit' => 5,
            'show_company' => null,
            'show_rating' => '1',
            'layout' => 'list',
            'demo' => null
        ), $atts);

        $force_demo = ($atts['demo'] === 'true' || $atts['demo'] === '1');
        $reviews_data = $this->fetch_google_reviews($atts['place_id'], false, $force_demo);

        if (isset($reviews_data['error'])) {
            return '<div class="strix-google-reviews-error">' . esc_html($reviews_data['error']) . '</div>';
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

        ob_start();
        echo '<div class="strix-google-reviews-shortcode strix-layout-' . esc_attr($atts['layout']) . '">';
        $this->display_reviews($reviews_data);
        echo '</div>';

        // Restore original setting
        if ($atts['show_company'] !== null) {
            update_option('strix_google_reviews_show_company_name', $original_show_company);
        }

        return ob_get_clean();
    }
}

/**
 * Initialize the plugin
 */
function strix_google_reviews_init() {
    return Strix_Google_Reviews::get_instance();
}

// Start the plugin
strix_google_reviews_init();