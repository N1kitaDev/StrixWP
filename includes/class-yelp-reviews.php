<?php
/**
 * Yelp Reviews Integration
 */
if (!defined('ABSPATH')) {
    exit;
}

class Strix_Yelp_Reviews {
    
    /**
     * Fetch Yelp reviews
     */
    public function fetch_reviews($business_id = null, $api_key = null, $force_refresh = false) {
        $business_id = $business_id ?: get_option('strix_yelp_business_id');
        $api_key = $api_key ?: get_option('strix_yelp_api_key');
        
        if (!$business_id || !$api_key) {
            return array('error' => __('Yelp Business ID and API Key are required', 'strix-google-reviews'));
        }
        
        $cache_key = 'strix_yelp_reviews_' . md5($business_id);
        $cache_timeout = get_option('strix_google_reviews_cache_timeout', 24) * HOUR_IN_SECONDS;
        
        if (!$force_refresh && ($cached = get_transient($cache_key))) {
            return $cached;
        }
        
        $url = 'https://api.yelp.com/v3/businesses/' . urlencode($business_id);
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return array('error' => sprintf(__('Yelp API Error: %s', 'strix-google-reviews'), $data['error']['description'] ?? 'Unknown error'));
        }
        
        $result = array(
            'source' => 'yelp',
            'place_info' => array(
                'name' => $data['name'] ?? '',
                'rating' => $data['rating'] ?? 0,
                'total_reviews' => $data['review_count'] ?? 0,
                'website' => $data['url'] ?? '',
                'phone' => $data['phone'] ?? '',
                'address' => isset($data['location']['display_address']) ? implode(', ', $data['location']['display_address']) : ''
            ),
            'reviews' => array()
        );
        
        // Note: Yelp API v3 doesn't provide reviews directly
        // Reviews are typically fetched via scraping or Yelp Fusion API with special access
        // For now, we'll return business info and note that reviews need to be added manually
        // or via Yelp's official review widget
        
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
