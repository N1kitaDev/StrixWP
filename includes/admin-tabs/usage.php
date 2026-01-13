<?php
/**
 * Usage Tab
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="strix-box">
    <div class="strix-box-header">
        <?php _e('How to Display Reviews on Your Site', 'strix-google-reviews'); ?>
    </div>
    
    <div class="strix-usage-section strix-mb-2">
        <h3><?php _e('Google Reviews (from Google Places)', 'strix-google-reviews'); ?></h3>
        <p><?php _e('Display reviews from your Google Business Profile.', 'strix-google-reviews'); ?></p>
        
        <div class="strix-code-example strix-mb-1">
            <h4><?php _e('Basic usage:', 'strix-google-reviews'); ?></h4>
            <code class="strix-shortcode">[strix_google_reviews]</code>
            <a href="#" class="btn-copy2clipboard" href="#shortcode-basic"><?php _e('Copy', 'strix-google-reviews'); ?></a>
            <span id="shortcode-basic" class="strix-d-none">[strix_google_reviews]</span>
        </div>
        
        <div class="strix-code-example strix-mb-1">
            <h4><?php _e('With custom Account ID and Location ID:', 'strix-google-reviews'); ?></h4>
            <code class="strix-shortcode">[strix_google_reviews account_id="123456789012345678901" location_id="98765432109876543210" limit="5"]</code>
            <a href="#" class="btn-copy2clipboard" href="#shortcode-advanced"><?php _e('Copy', 'strix-google-reviews'); ?></a>
            <span id="shortcode-advanced" class="strix-d-none">[strix_google_reviews account_id="123456789012345678901" location_id="98765432109876543210" limit="5"]</span>
        </div>
        
        <div class="strix-code-example strix-mb-1">
            <h4><?php _e('Different layouts:', 'strix-google-reviews'); ?></h4>
            <code class="strix-shortcode">[strix_google_reviews layout="slider" layout_style="1"]</code><br>
            <code class="strix-shortcode">[strix_google_reviews layout="grid" layout_style="1"]</code><br>
            <code class="strix-shortcode">[strix_google_reviews layout="badge" layout_style="1"]</code><br>
            <code class="strix-shortcode">[strix_google_reviews layout="popup" layout_style="1"]</code>
        </div>
    </div>
    
    <div class="strix-usage-section strix-mb-2">
        <h3><?php _e('Widget Usage', 'strix-google-reviews'); ?></h3>
        <p><?php _e('Add reviews to your sidebar or other widget areas.', 'strix-google-reviews'); ?></p>
        <p><?php _e('Go to Appearance → Widgets and add the "Google Reviews" widget.', 'strix-google-reviews'); ?></p>
    </div>
    
    <div class="strix-usage-tips">
        <h3><?php _e('Tips:', 'strix-google-reviews'); ?></h3>
        <ul class="strix-check-list">
            <li><?php _e('Use shortcodes in pages, posts, or custom page builders.', 'strix-google-reviews'); ?></li>
            <li><?php _e('Configure API settings above to show real Google reviews.', 'strix-google-reviews'); ?></li>
            <li><?php _e('Custom reviews work without API configuration.', 'strix-google-reviews'); ?></li>
        </ul>
    </div>
</div>
