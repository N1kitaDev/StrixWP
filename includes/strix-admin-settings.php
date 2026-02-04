<?php
/**
 * Strix Google Reviews - Admin Settings
 * 
 * Управление настройками плагина и интеграция с Strix Box API
 */

defined('ABSPATH') or die('No script kiddies please!');

class StrixGoogleReviewsSettings {
    
    private $option_name = 'strix_google_reviews_settings';
    private $strix_api_base = 'https://strixbox.com';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX обработчики
        add_action('wp_ajax_strixbox_google_connected', array($this, 'handle_google_connected'));
        add_action('wp_ajax_strix_get_reviews', array($this, 'get_reviews_ajax'));
        add_action('wp_ajax_strix_save_state', array($this, 'save_state_ajax'));
        add_action('wp_ajax_strix_disconnect_place', array($this, 'disconnect_place_ajax'));
    }
    
    /**
     * Добавить пункт меню в админке
     */
    public function add_admin_menu() {
        add_options_page(
            'Strix Google Reviews Settings',
            'Strix Google Reviews',
            'manage_options',
            'strix-google-reviews',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Инициализация настроек
     */
    public function init_settings() {
        register_setting('strix_google_reviews_group', $this->option_name);
        
        add_settings_section(
            'strix_google_reviews_main',
            'Основные настройки',
            array($this, 'section_callback'),
            'strix-google-reviews'
        );
        
        add_settings_field(
            'license_key',
            'Лицензионный ключ',
            array($this, 'license_key_callback'),
            'strix-google-reviews',
            'strix_google_reviews_main'
        );
        
        add_settings_field(
            'place_info',
            'Подключенное место',
            array($this, 'place_info_callback'),
            'strix-google-reviews',
            'strix_google_reviews_main'
        );
    }
    
    /**
     * Подключить скрипты админки
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_strix-google-reviews') {
            return;
        }
        
        wp_enqueue_script(
            'strix-admin-js',
            plugins_url('assets/js/strix-admin.js', dirname(__FILE__)),
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('strix-admin-js', 'strixAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('strix_nonce'),
            'connectUrl' => $this->strix_api_base . '/connect/google',
            'siteUrl' => home_url(),
            'callbackUrl' => admin_url('admin-ajax.php?action=strixbox_google_connected'),
        ));
        
        wp_enqueue_style(
            'strix-admin-css',
            plugins_url('assets/css/strix-admin.css', dirname(__FILE__)),
            array(),
            '1.0.0'
        );
    }
    
    /**
     * Страница настроек
     */
    public function settings_page() {
        $settings = get_option($this->option_name, array());
        ?>
        <div class="wrap">
            <h1>Strix Google Reviews Settings</h1>
            
            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>Настройки сохранены!</p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('strix_google_reviews_group');
                do_settings_sections('strix-google-reviews');
                submit_button();
                ?>
            </form>
            
            <?php if (!empty($settings['place_id'])): ?>
                <div class="strix-reviews-section">
                    <h2>Отзывы Google</h2>
                    <button type="button" class="button button-primary" id="strix-load-reviews">
                        Загрузить отзывы
                    </button>
                    <div id="strix-reviews-container" style="margin-top: 20px;"></div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Описание секции настроек
     */
    public function section_callback() {
        echo '<p>Настройте подключение к Google Business Profile через Strix Box API.</p>';
    }
    
    /**
     * Поле лицензионного ключа
     */
    public function license_key_callback() {
        $settings = get_option($this->option_name, array());
        $license_key = isset($settings['license_key']) ? $settings['license_key'] : '';
        
        echo '<input type="text" id="license_key" name="' . $this->option_name . '[license_key]" value="' . esc_attr($license_key) . '" class="regular-text" />';
        echo '<p class="description">Введите ваш лицензионный ключ Strix Box.</p>';
    }
    
    /**
     * Информация о подключенном месте
     */
    public function place_info_callback() {
        $settings = get_option($this->option_name, array());
        
        if (empty($settings['place_id'])) {
            echo '<p>Место не подключено.</p>';
            echo '<button type="button" class="button button-secondary" id="strix-connect-btn">Подключить место Google</button>';
        } else {
            echo '<div class="strix-place-info">';
            echo '<p><strong>Название:</strong> ' . esc_html($settings['place_name']) . '</p>';
            echo '<p><strong>Адрес:</strong> ' . esc_html($settings['place_address']) . '</p>';
            echo '<p><strong>Place ID:</strong> <code>' . esc_html($settings['place_id']) . '</code></p>';
            echo '<button type="button" class="button button-secondary" id="strix-disconnect-btn">Отключить место</button>';
            echo '</div>';
        }
    }
    
    /**
     * Обработчик AJAX для подключения Google места
     */
    public function handle_google_connected() {
        check_ajax_referer('strix_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав доступа');
        }
        
        // Получаем данные от Strix Box
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            wp_send_json_error('Неверные данные');
        }
        
        // Проверяем обязательные поля
        $required_fields = ['state', 'place_id', 'place_name', 'place_address', 'signature'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                wp_send_json_error('Отсутствует поле: ' . $field);
            }
        }
        
        // Проверяем state
        $stored_state = get_transient('strix_connect_state');
        if (!$stored_state || $stored_state !== $data['state']) {
            wp_send_json_error('Неверный state параметр');
        }
        
        // Проверяем HMAC подпись
        $settings = get_option($this->option_name, array());
        if (empty($settings['license_key'])) {
            wp_send_json_error('Лицензионный ключ не настроен');
        }
        
        if (!$this->verify_webhook_signature($data, $settings['license_key'])) {
            wp_send_json_error('Неверная подпись');
        }
        
        // Сохраняем данные места
        $settings['place_id'] = sanitize_text_field($data['place_id']);
        $settings['place_name'] = sanitize_text_field($data['place_name']);
        $settings['place_address'] = sanitize_text_field($data['place_address']);
        $settings['connected_at'] = current_time('mysql');
        
        update_option($this->option_name, $settings);
        
        // Удаляем state
        delete_transient('strix_connect_state');
        
        wp_send_json_success('Место успешно подключено!');
    }
    
    /**
     * AJAX обработчик для получения отзывов
     */
    public function get_reviews_ajax() {
        check_ajax_referer('strix_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав доступа');
        }
        
        $settings = get_option($this->option_name, array());
        
        if (empty($settings['place_id']) || empty($settings['license_key'])) {
            wp_send_json_error('Место не подключено или лицензия не настроена');
        }
        
        // Проверяем кэш
        $cache_key = 'strix_reviews_' . md5($settings['place_id']);
        $cached_reviews = get_transient($cache_key);
        
        if ($cached_reviews) {
            wp_send_json_success(array(
                'reviews' => $cached_reviews,
                'cached' => true
            ));
        }
        
        // Запрос к Strix Box API
        $response = wp_remote_post($this->strix_api_base . '/api/v1/google/reviews/latest', array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'place_id' => $settings['place_id'],
                'license_key' => $settings['license_key'],
                'domain' => home_url(),
            )),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка запроса: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !$data['success']) {
            wp_send_json_error('Ошибка API: ' . ($data['error'] ?? 'Неизвестная ошибка'));
        }
        
        // Кэшируем отзывы на 15 минут
        set_transient($cache_key, $data['reviews'], 15 * MINUTE_IN_SECONDS);
        
        wp_send_json_success(array(
            'reviews' => $data['reviews'],
            'place_info' => $data['place_info'] ?? null,
            'cached' => false
        ));
    }
    
    /**
     * Проверка HMAC подписи webhook
     */
    private function verify_webhook_signature($data, $license_key) {
        if (empty($data['signature'])) {
            return false;
        }
        
        $signature = $data['signature'];
        unset($data['signature']);
        
        // Сортируем данные для консистентности
        ksort($data);
        $payload = json_encode($data);
        
        // Создаем ожидаемую подпись
        $server_secret = 'your-secret-key-here'; // Должен совпадать с Laravel
        $secret = $server_secret . $license_key;
        $expected_signature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Получить настройки плагина
     */
    public function get_settings() {
        return get_option($this->option_name, array());
    }
    
    /**
     * Проверить, подключено ли место
     */
    public function is_place_connected() {
        $settings = $this->get_settings();
        return !empty($settings['place_id']);
    }
    
    /**
     * Получить отзывы (для использования в шорткодах/виджетах)
     */
    public function get_reviews() {
        $settings = $this->get_settings();
        
        if (empty($settings['place_id']) || empty($settings['license_key'])) {
            return false;
        }
        
        // Проверяем кэш
        $cache_key = 'strix_reviews_' . md5($settings['place_id']);
        $cached_reviews = get_transient($cache_key);
        
        if ($cached_reviews) {
            return $cached_reviews;
        }
        
        // Запрос к API (аналогично AJAX обработчику)
        $response = wp_remote_post($this->strix_api_base . '/api/v1/google/reviews/latest', array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'place_id' => $settings['place_id'],
                'license_key' => $settings['license_key'],
                'domain' => home_url(),
            )),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !$data['success']) {
            return false;
        }
        
        // Кэшируем отзывы на 15 минут
        set_transient($cache_key, $data['reviews'], 15 * MINUTE_IN_SECONDS);
        
        return $data['reviews'];
    }
    
    /**
     * AJAX обработчик для сохранения state
     */
    public function save_state_ajax() {
        check_ajax_referer('strix_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав доступа');
        }
        
        $state = sanitize_text_field($_POST['state']);
        if (empty($state)) {
            wp_send_json_error('Пустой state');
        }
        
        // Сохраняем state на 10 минут
        set_transient('strix_connect_state', $state, 10 * MINUTE_IN_SECONDS);
        
        wp_send_json_success('State сохранен');
    }
    
    /**
     * AJAX обработчик для отключения места
     */
    public function disconnect_place_ajax() {
        check_ajax_referer('strix_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав доступа');
        }
        
        $settings = get_option($this->option_name, array());
        
        // Удаляем данные места
        unset($settings['place_id']);
        unset($settings['place_name']);
        unset($settings['place_address']);
        unset($settings['connected_at']);
        
        update_option($this->option_name, $settings);
        
        // Очищаем кэш отзывов
        if (!empty($settings['place_id'])) {
            $cache_key = 'strix_reviews_' . md5($settings['place_id']);
            delete_transient($cache_key);
        }
        
        wp_send_json_success('Место отключено');
    }
}

// Инициализация класса
new StrixGoogleReviewsSettings();