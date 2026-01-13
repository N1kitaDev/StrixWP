<?php
/**
 * Manage Reviews Tab
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Strix_Review_Manager')) {
    echo '<div class="strix-box"><p>' . __('Review Manager is not available.', 'strix-google-reviews') . '</p></div>';
    return;
}

$review_manager = Strix_Review_Manager::get_instance();
?>
<div class="strix-box">
    <div class="strix-box-header">
        <?php _e('Manage Reviews', 'strix-google-reviews'); ?>
    </div>
    <p><?php _e('Hide, highlight, or anonymize reviews displayed on your website.', 'strix-google-reviews'); ?></p>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Review', 'strix-google-reviews'); ?></th>
                <th><?php _e('Source', 'strix-google-reviews'); ?></th>
                <th><?php _e('Actions', 'strix-google-reviews'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"><?php _e('No reviews found. Reviews will appear here once they are fetched.', 'strix-google-reviews'); ?></td>
            </tr>
        </tbody>
    </table>
</div>
