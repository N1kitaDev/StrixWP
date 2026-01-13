<?php
defined('ABSPATH') or die('No script kiddies please!');
?>
<div class="strix-box ti-rate-us-box">
<div class="strix-box-header"><?php echo esc_html(__("Do you like our free plugin?", 'wp-reviews-plugin-for-google')); ?></div>
<p><strong><?php echo esc_html(__('Support our work by leaving a review!', 'wp-reviews-plugin-for-google')); ?></strong></p>
<div class="strix-quick-rating" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-rate-us')); ?>">
<?php for ($i = 5; $i >= 1; $i--): ?><div class="strix-star-check" data-value="<?php echo esc_attr($i); ?>"></div><?php endfor; ?>
</div>
</div>
<div class="strix-modal ti-rateus-modal" id="strix-rateus-modal-feedback">
<div class="strix-modal-dialog">
<div class="strix-modal-content">
<span class="strix-close-icon btn-modal-close"></span>
<div class="strix-modal-body">
<div class="strix-rating-textbox">
<div class="strix-quick-rating">
<?php for ($i = 5; $i >= 1; $i--): ?><div class="strix-star-check" data-value="<?php echo esc_attr($i); ?>"></div><?php endfor; ?>
<div class="clear"></div>
</div>
</div>
<div class="strix-rateus-title"><?php echo wp_kses_post(__('Thanks for your feedback!<br />Let us know how we can improve.', 'wp-reviews-plugin-for-google')); ?></div>
<input type="text" class="strix-form-control" placeholder="<?php echo esc_html(__('Contact e-mail', 'wp-reviews-plugin-for-google')); ?>" value="<?php echo esc_attr($current_user->user_email); ?>" />
<textarea class="strix-form-control" placeholder="<?php echo esc_html(__('Describe your experience', 'wp-reviews-plugin-for-google')); ?>"></textarea>
</div>
<div class="strix-modal-footer">
<a href="#" class="strix-btn ti-btn-default btn-modal-close"><?php echo esc_html(__('Cancel', 'wp-reviews-plugin-for-google')); ?></a>
<a href="#" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-rate-us')); ?>" class="strix-btn btn-rateus-support"><?php echo esc_html(__('Contact our support', 'wp-reviews-plugin-for-google')); ?></a>
</div>
</div>
</div>
</div>