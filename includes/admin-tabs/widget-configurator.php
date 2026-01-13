<?php
/**
 * Widget Configurator Tab
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<h1 class="strix-header-title"><?php _e('Widget Configurator', 'strix-google-reviews'); ?></h1>

<div class="strix-box">
    <div class="strix-box-header">
        <?php _e('Create Custom Review Widget', 'strix-google-reviews'); ?>
    </div>
    <p><?php _e('Configure your review widget layout, style, and display options.', 'strix-google-reviews'); ?></p>
    
    <div class="strix-preview-boxes-container">
        <div class="strix-full-width">
            <div class="strix-box strix-preview-boxes">
                <div class="strix-box-inner">
                    <div class="strix-box-header strix-box-header-normal">
                        <?php _e('Layout', 'strix-google-reviews'); ?>: <strong><?php _e('List', 'strix-google-reviews'); ?></strong>
                        <a href="#" class="strix-btn strix-btn-sm strix-pull-right strix-btn-loading-on-click"><?php _e('Select', 'strix-google-reviews'); ?></a>
                        <div class="clear"></div>
                    </div>
                    <div class="preview">
                        <?php _e('Preview will appear here', 'strix-google-reviews'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
