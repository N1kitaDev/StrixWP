<?php
defined('ABSPATH') or die('No script kiddies please!');
if (!isset($strixShortCodeText)) {
$strixShortCodeText = $pluginManagerInstance->get_shortcode_name().' no-registration='.$pluginManagerInstance->getShortName();
}
$strixShortCodeId = "ti-shortcode-id-".uniqid();
?>
<div class="strix-form-group" style="margin-bottom: 2px">
<label>Shortcode</label>
<code class="strix-shortcode" id="<?php echo esc_attr($strixShortCodeId); ?>">[<?php echo esc_html($strixShortCodeText); ?>]</code>
<a href="#<?php echo esc_attr($strixShortCodeId); ?>" class="strix-btn ti-tooltip ti-toggle-tooltip btn-copy2clipboard">
<?php echo esc_html(__('Copy to clipboard', 'wp-reviews-plugin-for-google')); ?>
<span class="strix-tooltip-message">
<span style="color: #00ff00; margin-right: 2px">✓</span>
<?php echo esc_html(__('Copied', 'wp-reviews-plugin-for-google')); ?>
</span>
</a>
</div>
<div class="strix-info-text"><?php echo esc_html(__('Copy and paste this shortcode into post, page or widget.', 'wp-reviews-plugin-for-google')); ?></div>