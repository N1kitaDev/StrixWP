<?php
/**
 * Review Management System
 * Handles review hiding, highlighting, anonymization, and statistics
 */
if (!defined('ABSPATH')) {
    exit;
}

class Strix_Review_Manager {
    
    private static $instance = null;
    private $table_reviews;
    private $table_views;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->table_reviews = $wpdb->prefix . 'strix_reviews';
        $this->table_views = $wpdb->prefix . 'strix_review_views';
        
        // Create tables on admin init (safer than init)
        if (is_admin()) {
            add_action('admin_init', array($this, 'create_tables'));
        }
        
        // AJAX handlers
        add_action('wp_ajax_strix_hide_review', array($this, 'ajax_hide_review'));
        add_action('wp_ajax_strix_highlight_review', array($this, 'ajax_highlight_review'));
        add_action('wp_ajax_strix_anonymize_review', array($this, 'ajax_anonymize_review'));
        add_action('wp_ajax_strix_track_view', array($this, 'ajax_track_view'));
        add_action('wp_ajax_nopriv_strix_track_view', array($this, 'ajax_track_view'));
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        // Check if tables already exist to avoid unnecessary operations
        $reviews_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        $views_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_views}'") == $this->table_views;
        
        if ($reviews_table_exists && $views_table_exists) {
            return; // Tables already exist
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Reviews management table
        $sql_reviews = "CREATE TABLE IF NOT EXISTS {$this->table_reviews} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            review_id varchar(255) NOT NULL,
            source varchar(50) NOT NULL DEFAULT 'google',
            hidden tinyint(1) NOT NULL DEFAULT 0,
            highlighted tinyint(1) NOT NULL DEFAULT 0,
            anonymized tinyint(1) NOT NULL DEFAULT 0,
            original_author_name varchar(255) DEFAULT NULL,
            highlight_color varchar(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY review_id (review_id),
            KEY source (source),
            KEY hidden (hidden)
        ) $charset_collate;";
        
        // Views statistics table
        $sql_views = "CREATE TABLE IF NOT EXISTS {$this->table_views} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            viewed bigint(20) NOT NULL DEFAULT 0,
            widget_id varchar(255) DEFAULT NULL,
            page_url varchar(500) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY date_widget (date, widget_id),
            KEY date (date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        if (!$reviews_table_exists) {
            dbDelta($sql_reviews);
        }
        
        if (!$views_table_exists) {
            dbDelta($sql_views);
        }
    }
    
    /**
     * Save review to management table
     */
    public function save_review($review_id, $source = 'google', $author_name = '') {
        global $wpdb;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        if (!$table_exists) {
            return false;
        }
        
        $review_id = sanitize_text_field($review_id);
        $source = sanitize_text_field($source);
        $author_name = sanitize_text_field($author_name);
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_reviews} WHERE review_id = %s AND source = %s",
            $review_id,
            $source
        ));
        
        if (!$exists) {
            return $wpdb->insert(
                $this->table_reviews,
                array(
                    'review_id' => $review_id,
                    'source' => $source,
                    'original_author_name' => $author_name,
                ),
                array('%s', '%s', '%s')
            );
        }
        
        return true;
    }
    
    /**
     * Get review settings
     */
    public function get_review_settings($review_id, $source = 'google') {
        global $wpdb;
        
        // Check if table exists before querying
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        if (!$table_exists) {
            return null;
        }
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_reviews} WHERE review_id = %s AND source = %s",
            $review_id,
            $source
        ), ARRAY_A);
    }
    
    /**
     * Hide review
     */
    public function hide_review($review_id, $source = 'google', $hide = true) {
        global $wpdb;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        if (!$table_exists) {
            return false;
        }
        
        $this->save_review($review_id, $source);
        
        return $wpdb->update(
            $this->table_reviews,
            array('hidden' => $hide ? 1 : 0),
            array('review_id' => $review_id, 'source' => $source),
            array('%d'),
            array('%s', '%s')
        );
    }
    
    /**
     * Highlight review
     */
    public function highlight_review($review_id, $source = 'google', $highlight = true, $color = '#ffd700') {
        global $wpdb;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        if (!$table_exists) {
            return false;
        }
        
        $this->save_review($review_id, $source);
        
        return $wpdb->update(
            $this->table_reviews,
            array(
                'highlighted' => $highlight ? 1 : 0,
                'highlight_color' => $highlight ? $color : null
            ),
            array('review_id' => $review_id, 'source' => $source),
            array('%d', '%s'),
            array('%s', '%s')
        );
    }
    
    /**
     * Anonymize review
     */
    public function anonymize_review($review_id, $source = 'google', $anonymize = true) {
        global $wpdb;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        if (!$table_exists) {
            return false;
        }
        
        $settings = $this->get_review_settings($review_id, $source);
        if (!$settings) {
            $this->save_review($review_id, $source);
            $settings = $this->get_review_settings($review_id, $source);
        }
        
        if ($anonymize && empty($settings['original_author_name'])) {
            // Store original name before anonymizing
            // This would need to be called before anonymization
        }
        
        return $wpdb->update(
            $this->table_reviews,
            array('anonymized' => $anonymize ? 1 : 0),
            array('review_id' => $review_id, 'source' => $source),
            array('%d'),
            array('%s', '%s')
        );
    }
    
    /**
     * Track review view
     */
    public function track_view($widget_id = null, $page_url = null) {
        global $wpdb;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_views}'") == $this->table_views;
        if (!$table_exists) {
            return false;
        }
        
        $date = current_time('Y-m-d');
        $widget_id = $widget_id ? sanitize_text_field($widget_id) : 'default';
        $page_url = $page_url ? sanitize_url($page_url) : '';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_views} WHERE date = %s AND widget_id = %s",
            $date,
            $widget_id
        ));
        
        if ($exists) {
            return $wpdb->query($wpdb->prepare(
                "UPDATE {$this->table_views} SET viewed = viewed + 1 WHERE date = %s AND widget_id = %s",
                $date,
                $widget_id
            ));
        } else {
            return $wpdb->insert(
                $this->table_views,
                array(
                    'date' => $date,
                    'viewed' => 1,
                    'widget_id' => $widget_id,
                    'page_url' => $page_url
                ),
                array('%s', '%d', '%s', '%s')
            );
        }
    }
    
    /**
     * Get statistics
     */
    public function get_statistics($days = 30) {
        global $wpdb;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_views}'") == $this->table_views;
        if (!$table_exists) {
            return array(
                'daily' => array(),
                'total' => 0,
                'period' => $days
            );
        }
        
        $date_from = date('Y-m-d', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT date, SUM(viewed) as total_views 
             FROM {$this->table_views} 
             WHERE date >= %s 
             GROUP BY date 
             ORDER BY date DESC",
            $date_from
        ), ARRAY_A);
        
        $total_views = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(viewed) FROM {$this->table_views} WHERE date >= %s",
            $date_from
        ));
        
        return array(
            'daily' => $stats ? $stats : array(),
            'total' => $total_views ? intval($total_views) : 0,
            'period' => $days
        );
    }
    
    /**
     * Apply review settings to review data
     */
    public function apply_review_settings($reviews, $source = 'google') {
        global $wpdb;
        
        if (empty($reviews) || !is_array($reviews)) {
            return $reviews;
        }
        
        // Check if table exists before querying
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_reviews}'") == $this->table_reviews;
        if (!$table_exists) {
            return $reviews; // Return reviews as-is if table doesn't exist
        }
        
        $review_ids = array_map(function($review) {
            return isset($review['reviewId']) ? $review['reviewId'] : 
                   (isset($review['id']) ? $review['id'] : md5($review['author_name'] . $review['text']));
        }, $reviews);
        
        if (empty($review_ids)) {
            return $reviews;
        }
        
        $placeholders = implode(',', array_fill(0, count($review_ids), '%s'));
        $settings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_reviews} 
             WHERE review_id IN ($placeholders) AND source = %s",
            array_merge($review_ids, array($source))
        ), ARRAY_A);
        
        $settings_map = array();
        foreach ($settings as $setting) {
            $settings_map[$setting['review_id']] = $setting;
        }
        
        $filtered_reviews = array();
        foreach ($reviews as $review) {
            $review_id = isset($review['reviewId']) ? $review['reviewId'] : 
                        (isset($review['id']) ? $review['id'] : md5($review['author_name'] . $review['text']));
            
            $setting = isset($settings_map[$review_id]) ? $settings_map[$review_id] : null;
            
            // Skip hidden reviews
            if ($setting && $setting['hidden']) {
                continue;
            }
            
            // Apply anonymization
            if ($setting && $setting['anonymized']) {
                $review['author_name'] = __('Anonymous', 'strix-google-reviews');
                $review['profile_photo_url'] = '';
            }
            
            // Add highlight info
            if ($setting && $setting['highlighted']) {
                $review['highlighted'] = true;
                $review['highlight_color'] = $setting['highlight_color'] ?: '#ffd700';
            }
            
            $filtered_reviews[] = $review;
        }
        
        return $filtered_reviews;
    }
    
    /**
     * AJAX: Hide review
     */
    public function ajax_hide_review() {
        check_ajax_referer('strix_reviews_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'strix-google-reviews')));
        }
        
        $review_id = sanitize_text_field($_POST['review_id'] ?? '');
        $source = sanitize_text_field($_POST['source'] ?? 'google');
        $hide = isset($_POST['hide']) ? (bool)$_POST['hide'] : true;
        
        $result = $this->hide_review($review_id, $source, $hide);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Review updated', 'strix-google-reviews')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update review', 'strix-google-reviews')));
        }
    }
    
    /**
     * AJAX: Highlight review
     */
    public function ajax_highlight_review() {
        check_ajax_referer('strix_reviews_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'strix-google-reviews')));
        }
        
        $review_id = sanitize_text_field($_POST['review_id'] ?? '');
        $source = sanitize_text_field($_POST['source'] ?? 'google');
        $highlight = isset($_POST['highlight']) ? (bool)$_POST['highlight'] : true;
        $color = sanitize_hex_color($_POST['color'] ?? '#ffd700');
        
        $result = $this->highlight_review($review_id, $source, $highlight, $color);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Review updated', 'strix-google-reviews')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update review', 'strix-google-reviews')));
        }
    }
    
    /**
     * AJAX: Anonymize review
     */
    public function ajax_anonymize_review() {
        check_ajax_referer('strix_reviews_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'strix-google-reviews')));
        }
        
        $review_id = sanitize_text_field($_POST['review_id'] ?? '');
        $source = sanitize_text_field($_POST['source'] ?? 'google');
        $anonymize = isset($_POST['anonymize']) ? (bool)$_POST['anonymize'] : true;
        
        $result = $this->anonymize_review($review_id, $source, $anonymize);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Review updated', 'strix-google-reviews')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update review', 'strix-google-reviews')));
        }
    }
    
    /**
     * AJAX: Track view
     */
    public function ajax_track_view() {
        $widget_id = sanitize_text_field($_POST['widget_id'] ?? 'default');
        $page_url = isset($_SERVER['HTTP_REFERER']) ? sanitize_url($_SERVER['HTTP_REFERER']) : '';
        
        $this->track_view($widget_id, $page_url);
        
        wp_send_json_success();
    }
}

// Initialize on plugins_loaded hook to ensure WordPress is fully loaded
add_action('plugins_loaded', function() {
    if (class_exists('Strix_Review_Manager')) {
        Strix_Review_Manager::get_instance();
    }
}, 10);
