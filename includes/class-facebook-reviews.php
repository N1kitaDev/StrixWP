<?php
/**
 * Facebook Reviews Integration
 */
if (!defined('ABSPATH')) {
    exit;
}

class Strix_Facebook_Reviews {
    
    /**
     * Fetch Facebook reviews
     */
    public function fetch_reviews($page_id = null, $access_token = null, $force_refresh = false) {
        $page_id = $page_id ?: get_option('strix_facebook_page_id');
        $access_token = $access_token ?: get_option('strix_facebook_access_token');
        
        if (!$page_id || !$access_token) {
            return array('error' => __('Facebook Page ID and Access Token are required', 'strix-google-reviews'));
        }
        
        $cache_key = 'strix_facebook_reviews_' . md5($page_id);
        $cache_timeout = get_option('strix_google_reviews_cache_timeout', 24) * HOUR_IN_SECONDS;
        
        if (!$force_refresh && ($cached = get_transient($cache_key))) {
            return $cached;
        }
        
        // Fetch page info
        $page_url = 'https://graph.facebook.com/v18.0/' . $page_id . '?fields=name,rating_count,overall_star_rating&access_token=' . urlencode($access_token);
        $page_response = wp_remote_get($page_url);
        
        if (is_wp_error($page_response)) {
            return array('error' => $page_response->get_error_message());
        }
        
        $page_body = wp_remote_retrieve_body($page_response);
        $page_data = json_decode($page_body, true);
        
        if (isset($page_data['error'])) {
            return array('error' => sprintf(__('Facebook API Error: %s', 'strix-google-reviews'), $page_data['error']['message']));
        }
        
        // Fetch reviews (ratings)
        $reviews_url = 'https://graph.facebook.com/v18.0/' . $page_id . '/ratings?limit=50&access_token=' . urlencode($access_token);
        $reviews_response = wp_remote_get($reviews_url);
        
        if (is_wp_error($reviews_response)) {
            return array('error' => $reviews_response->get_error_message());
        }
        
        $reviews_body = wp_remote_retrieve_body($reviews_response);
        $reviews_data = json_decode($reviews_body, true);
        
        if (isset($reviews_data['error'])) {
            return array('error' => sprintf(__('Facebook API Error: %s', 'strix-google-reviews'), $reviews_data['error']['message']));
        }
        
        $result = array(
            'source' => 'facebook',
            'place_info' => array(
                'name' => $page_data['name'] ?? '',
                'rating' => $page_data['overall_star_rating'] ?? 0,
                'total_reviews' => $page_data['rating_count'] ?? 0,
                'website' => '',
                'phone' => ''
            ),
            'reviews' => array()
        );
        
        if (isset($reviews_data['data'])) {
            foreach ($reviews_data['data'] as $review) {
                $result['reviews'][] = array(
                    'author_name' => $review['reviewer']['name'] ?? __('Anonymous', 'strix-google-reviews'),
                    'rating' => $review['rating'] ?? 0,
                    'text' => $review['review_text'] ?? '',
                    'time' => isset($review['created_time']) ? strtotime($review['created_time']) : time(),
                    'relative_time' => $this->get_relative_time(isset($review['created_time']) ? strtotime($review['created_time']) : time()),
                    'profile_photo_url' => isset($review['reviewer']['picture']['data']['url']) ? $review['reviewer']['picture']['data']['url'] : '',
                    'language' => 'en',
                    'source' => 'facebook'
                );
            }
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
}
