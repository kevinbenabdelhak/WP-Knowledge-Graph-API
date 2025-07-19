<?php 
if (!defined('ABSPATH')) {
    exit;
}

function knowledge_graph_save_alert_settings() {
    $mode = in_array($_POST['alert_mode'], ['none','change','regular']) ? $_POST['alert_mode'] : 'none';
    update_option('knowledge_graph_alert_mode', $mode);
    if (!empty($_POST['alert_email']) && is_email($_POST['alert_email']))
        update_option('knowledge_graph_alert_email', sanitize_email($_POST['alert_email']));
    update_option('knowledge_graph_alert_days', intval($_POST['freq_days']));
    update_option('knowledge_graph_alert_hours', intval($_POST['freq_hours']));
    update_option('knowledge_graph_alert_minutes', intval($_POST['freq_minutes']));
    knowledge_graph_update_cron_schedule();
    echo 'OK'; wp_die();
}


add_action('wp_ajax_save_alert_settings', 'knowledge_graph_save_alert_settings');