<?php
/**
 * Google Reviews Widget
 */
class Strix_Google_Reviews_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'strix_google_reviews_widget',
            __('Google Reviews', 'strix-google-reviews'),
            array(
                'description' => __('Display Google Reviews', 'strix-google-reviews'),
            )
        );
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Google Reviews', 'strix-google-reviews');
        $place_id = !empty($instance['place_id']) ? $instance['place_id'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'strix-google-reviews'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('place_id'); ?>"><?php _e('Google Place ID:', 'strix-google-reviews'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('place_id'); ?>" name="<?php echo $this->get_field_name('place_id'); ?>" type="text" value="<?php echo esc_attr($place_id); ?>">
            <small><?php _e('Find your Place ID in Google Places API', 'strix-google-reviews'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of reviews:', 'strix-google-reviews'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" min="1" max="20" value="<?php echo esc_attr($limit); ?>">
        </p>
        <?php
    }

    /**
     * Update widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['place_id'] = (!empty($new_instance['place_id'])) ? sanitize_text_field($new_instance['place_id']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? absint($new_instance['limit']) : 5;

        return $instance;
    }

    /**
     * Display widget
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        echo '<div class="strix-google-reviews-widget">';

        // Get reviews data
        $place_id = !empty($instance['place_id']) ? $instance['place_id'] : get_option('strix_google_reviews_place_id');
        $limit = !empty($instance['limit']) ? absint($instance['limit']) : 5;

        if (class_exists('Strix_Google_Reviews')) {
            $plugin = Strix_Google_Reviews::get_instance();
            $reviews_data = $plugin->fetch_google_reviews($place_id);

            if (isset($reviews_data['error'])) {
                echo '<div class="strix-google-reviews-error">';
                echo '<p>' . esc_html($reviews_data['error']) . '</p>';
                echo '<p><a href="' . admin_url('admin.php?page=strix-google-reviews') . '">' . __('Configure API settings', 'strix-google-reviews') . '</a></p>';
                echo '</div>';
            } else {
                // Limit reviews for widget
                if (count($reviews_data['reviews']) > $limit) {
                    $reviews_data['reviews'] = array_slice($reviews_data['reviews'], 0, $limit);
                }

                $plugin->display_reviews($reviews_data);
            }
        } else {
            echo '<div class="strix-google-reviews-placeholder">';
            echo '<p>' . __('Plugin not properly initialized.', 'strix-google-reviews') . '</p>';
            echo '</div>';
        }

        echo '</div>';
        echo $args['after_widget'];
    }
}