<?php
/**
 * Admin Page Template
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('edit_pages')) {
    die('The account you are logged in to does not have permission to access this page.');
}

$tabs = array(
    array(
        'slug' => 'settings',
        'name' => __('Settings', 'strix-google-reviews'),
        'place' => 'left'
    ),
    array(
        'slug' => 'widget-configurator',
        'name' => __('Widget Configurator', 'strix-google-reviews'),
        'place' => 'left'
    ),
    array(
        'slug' => 'manage-reviews',
        'name' => __('Manage Reviews', 'strix-google-reviews'),
        'place' => 'left'
    ),
    array(
        'slug' => 'statistics',
        'name' => __('Statistics', 'strix-google-reviews'),
        'place' => 'left'
    ),
    array(
        'slug' => 'usage',
        'name' => __('How to Use', 'strix-google-reviews'),
        'place' => 'right'
    )
);

$selectedTab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'settings';
if (!in_array($selectedTab, array_column($tabs, 'slug'))) {
    $selectedTab = 'settings';
}

$noContainerElementTabs = array('widget-configurator');
?>
<div id="strix-assets-error" class="notice notice-warning" style="display: none; margin-left: 0; margin-right: 0; padding-bottom: 9px">
    <p>
        <?php echo wp_kses_post(__('For some reason, the <strong>CSS</strong> file required to run the plugin was not loaded.<br />One of your plugins is probably causing the problem.', 'strix-google-reviews')); ?>
    </p>
</div>
<div id="strix-plugin-settings-page" class="strix-plugin-wrapper strix-toggle-opacity">
    <div class="strix-header-nav">
        <?php foreach ($tabs as $tab): ?>
        <a
            class="strix-nav-item<?php if ($selectedTab === $tab['slug']): ?> strix-active<?php endif; ?><?php if ($tab['place'] === 'right'): ?> strix-right<?php endif; ?>"
            href="<?php echo esc_url(admin_url('admin.php?page=strix-google-reviews&tab='. esc_attr($tab['slug']))); ?>"
        >
            <?php echo esc_html($tab['name']); ?>
        </a>
        <?php endforeach; ?>
        <a href="https://strixmedia.ru" target="_blank" title="Strix Media" class="strix-logo">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/img/strix-logo.svg'); ?>" alt="Strix Media" />
        </a>
    </div>
    
    <?php if (!isset($noContainerElementTabs) || !in_array($selectedTab, $noContainerElementTabs)): ?>
    <div class="strix-container" id="tab-<?php echo esc_attr($selectedTab); ?>">
        <?php 
        $tabFile = plugin_dir_path(__FILE__) . 'admin-tabs/' . $selectedTab . '.php';
        if (file_exists($tabFile)) {
            include($tabFile);
        } else {
            echo '<div class="strix-box"><p>' . __('Tab content not found.', 'strix-google-reviews') . '</p></div>';
        }
        ?>
    </div>
    <?php else: ?>
        <?php 
        $tabFile = plugin_dir_path(__FILE__) . 'admin-tabs/' . $selectedTab . '.php';
        if (file_exists($tabFile)) {
            include($tabFile);
        }
        ?>
    <?php endif; ?>
</div>
<div id="strix-loading">
    <div class="strix-loading-effect">
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
