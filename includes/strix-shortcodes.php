<?php
/**
 * Strix Google Reviews - Shortcodes
 * 
 * Шорткоды для отображения отзывов на фронтенде
 */

defined('ABSPATH') or die('No script kiddies please!');

class StrixGoogleReviewsShortcodes {
    
    public function __construct() {
        add_shortcode('strix_google_reviews', array($this, 'display_reviews_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    /**
     * Подключить стили для фронтенда
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'strix-frontend-css',
            plugins_url('assets/css/strix-frontend.css', dirname(__FILE__)),
            array(),
            '1.0.0'
        );
    }
    
    /**
     * Шорткод для отображения отзывов Google
     * 
     * Использование: [strix_google_reviews limit="5" show_rating="true" show_date="true"]
     */
    public function display_reviews_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 5,
            'show_rating' => 'true',
            'show_date' => 'true',
            'show_author' => 'true',
            'layout' => 'list', // list, grid, slider
            'columns' => 3, // для grid layout
        ), $atts, 'strix_google_reviews');
        
        // Получаем настройки плагина
        $settings_class = new StrixGoogleReviewsSettings();
        
        if (!$settings_class->is_place_connected()) {
            return '<p class="strix-error">Google место не подключено. Настройте плагин в админке.</p>';
        }
        
        // Получаем отзывы
        $reviews = $settings_class->get_reviews();
        
        if (!$reviews || empty($reviews)) {
            return '<p class="strix-no-reviews">Отзывы не найдены.</p>';
        }
        
        // Ограничиваем количество отзывов
        $limit = intval($atts['limit']);
        if ($limit > 0 && count($reviews) > $limit) {
            $reviews = array_slice($reviews, 0, $limit);
        }
        
        // Генерируем HTML
        return $this->render_reviews($reviews, $atts);
    }
    
    /**
     * Отрендерить отзывы в HTML
     */
    private function render_reviews($reviews, $atts) {
        $layout = $atts['layout'];
        $show_rating = $atts['show_rating'] === 'true';
        $show_date = $atts['show_date'] === 'true';
        $show_author = $atts['show_author'] === 'true';
        
        $container_class = 'strix-reviews-container strix-layout-' . esc_attr($layout);
        
        if ($layout === 'grid') {
            $container_class .= ' strix-columns-' . intval($atts['columns']);
        }
        
        $html = '<div class="' . $container_class . '">';
        
        foreach ($reviews as $review) {
            $html .= $this->render_single_review($review, $show_rating, $show_date, $show_author);
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Отрендерить один отзыв
     */
    private function render_single_review($review, $show_rating, $show_date, $show_author) {
        $html = '<div class="strix-review-item">';
        
        // Заголовок с автором и рейтингом
        if ($show_author || $show_rating) {
            $html .= '<div class="strix-review-header">';
            
            if ($show_author) {
                $html .= '<div class="strix-review-author">';
                
                // Аватар автора (если есть)
                if (!empty($review['profile_photo_url'])) {
                    $html .= '<img src="' . esc_url($review['profile_photo_url']) . '" alt="' . esc_attr($review['author_name']) . '" class="strix-author-avatar" />';
                }
                
                $html .= '<span class="strix-author-name">' . esc_html($review['author_name']) . '</span>';
                $html .= '</div>';
            }
            
            if ($show_rating && !empty($review['rating'])) {
                $html .= '<div class="strix-review-rating">';
                $html .= $this->generate_stars($review['rating']);
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        // Текст отзыва
        if (!empty($review['text'])) {
            $html .= '<div class="strix-review-text">';
            $html .= '<p>' . esc_html($review['text']) . '</p>';
            $html .= '</div>';
        }
        
        // Дата
        if ($show_date && !empty($review['time'])) {
            $html .= '<div class="strix-review-date">';
            $html .= esc_html($this->format_date($review['time']));
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Генерировать звезды для рейтинга
     */
    private function generate_stars($rating) {
        $rating = floatval($rating);
        $full_stars = floor($rating);
        $half_star = ($rating - $full_stars) >= 0.5;
        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
        
        $html = '<div class="strix-stars">';
        
        // Полные звезды
        for ($i = 0; $i < $full_stars; $i++) {
            $html .= '<span class="strix-star strix-star-full">★</span>';
        }
        
        // Половинная звезда
        if ($half_star) {
            $html .= '<span class="strix-star strix-star-half">★</span>';
        }
        
        // Пустые звезды
        for ($i = 0; $i < $empty_stars; $i++) {
            $html .= '<span class="strix-star strix-star-empty">☆</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Форматировать дату
     */
    private function format_date($date_string) {
        $date = new DateTime($date_string);
        return $date->format('d.m.Y');
    }
}

// Инициализация шорткодов
new StrixGoogleReviewsShortcodes();