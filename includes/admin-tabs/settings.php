<?php
/**
 * Settings Tab
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="strix-box">
    <div class="strix-box-header">
        <?php _e('Google Business Profile API Settings', 'strix-google-reviews'); ?>
    </div>
    
    <?php if (!get_option('strix_google_reviews_api_key') || !get_option('strix_google_reviews_account_id') || !get_option('strix_google_reviews_location_id')): ?>
    <div class="strix-notice strix-notice-info strix-mb-1">
        <p><?php _e('Configure your Google Business Profile API key, Account ID, and Location ID to start displaying reviews.', 'strix-google-reviews'); ?></p>
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
// Facebook Reviews Section
if (get_option('strix_facebook_enabled', '0')): ?>
<div class="strix-box strix-mt-1">
    <div class="strix-box-header">
        <?php _e('Facebook Reviews Settings', 'strix-google-reviews'); ?>
    </div>
    <form method="post" action="options.php">
        <?php
        settings_fields('strix_google_reviews_settings');
        do_settings_sections('strix-facebook-reviews');
        submit_button(__('Save Settings', 'strix-google-reviews'));
        ?>
    </form>
</div>
<?php endif; ?>

<?php
// Yelp Reviews Section
if (get_option('strix_yelp_enabled', '0')): ?>
<div class="strix-box strix-mt-1">
    <div class="strix-box-header">
        <?php _e('Yelp Reviews Settings', 'strix-google-reviews'); ?>
    </div>
    <form method="post" action="options.php">
        <?php
        settings_fields('strix_google_reviews_settings');
        do_settings_sections('strix-yelp-reviews');
        submit_button(__('Save Settings', 'strix-google-reviews'));
        ?>
    </form>
</div>
<?php endif; ?>
