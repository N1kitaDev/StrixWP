<?php
/**
 * Statistics Tab
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Strix_Review_Manager')) {
    echo '<div class="strix-box"><p>' . __('Review Manager is not available.', 'strix-google-reviews') . '</p></div>';
    return;
}

$review_manager = Strix_Review_Manager::get_instance();
$stats = $review_manager->get_statistics(30);
?>
<div class="strix-box">
    <div class="strix-box-header">
        <?php _e('Review Statistics', 'strix-google-reviews'); ?>
    </div>
    
    <div class="strix-stats-overview strix-mb-2">
        <div class="strix-stat-card">
            <h3><?php echo esc_html($stats['total']); ?></h3>
            <p><?php _e('Total Views', 'strix-google-reviews'); ?></p>
        </div>
        <div class="strix-stat-card">
            <h3><?php echo esc_html(count($stats['daily'])); ?></h3>
            <p><?php _e('Days Tracked', 'strix-google-reviews'); ?></p>
        </div>
    </div>
    
    <div class="strix-stats-chart">
        <h3><?php _e('Views Over Time', 'strix-google-reviews'); ?></h3>
        <p><?php _e('Chart will be displayed here', 'strix-google-reviews'); ?></p>
    </div>
</div>
