<?php 

if (!defined('ABSPATH')) {
    exit;
}


function knowledge_graph_update_cron_schedule() {
    $timestamp = wp_next_scheduled('knowledge_graph_check_alerts');
    if ($timestamp) wp_unschedule_event($timestamp, 'knowledge_graph_check_alerts');
    $mode = get_option('knowledge_graph_alert_mode', 'none');
    if ($mode == 'none') return;
    $days = intval(get_option('knowledge_graph_alert_days', 0));
    $hours = intval(get_option('knowledge_graph_alert_hours', 1));
    $minutes = intval(get_option('knowledge_graph_alert_minutes', 0));
    $interval = $days*24*60*60 + $hours*60*60 + $minutes*60;
    if ($interval<60) $interval=60;
    update_option('knowledge_graph_alert_frequency_seconds', $interval);
    wp_schedule_event(time()+$interval, 'knowledge_graph_custom_interval', 'knowledge_graph_check_alerts');
}
register_activation_hook( __FILE__, 'knowledge_graph_update_cron_schedule' );
add_filter('cron_schedules', function($schedules){
    $interval = intval(get_option('knowledge_graph_alert_frequency_seconds', 3600));
    $schedules['knowledge_graph_custom_interval'] = [
        'interval' => $interval,
        'display' => 'Intervalle du plugin Knowledge Graph ('.$interval.'s)'
    ];
    return $schedules;
});
add_action('wp_ajax_get_next_kg_cron', function(){
    $t = wp_next_scheduled('knowledge_graph_check_alerts');
    $diff = $t ? max(0, $t-time()) : 0;
    echo $diff; wp_die();
});
