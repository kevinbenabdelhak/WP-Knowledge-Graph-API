<?php 

if (!defined('ABSPATH')) {
    exit;
}


function knowledge_graph_add_sent_mail_entity($dest, $entite, $score, $kgid = '', $image = '') {
    $logs = get_option('knowledge_graph_alert_mails_entites', []);
    if(!is_array($logs)) $logs = [];
    $logs[] = [
        'date'   => current_time('mysql'),
        'email'  => $dest,
        'entite' => $entite,
        'score'  => $score,
        'kgid'   => $kgid,
        'image'  => $image,
    ];
    $logs = array_slice($logs, -50, 50, true);
    update_option('knowledge_graph_alert_mails_entites', $logs);
}
add_action('wp_ajax_get_kg_sent_mails_entites', function(){
    $logs = get_option('knowledge_graph_alert_mails_entites', []);
    $logs = array_reverse($logs);
    foreach($logs as &$row){
        $row['date'] = date_i18n('d/m/Y H:i:s', strtotime($row['date']));
    }
    echo json_encode($logs);
    wp_die();
});
add_action('knowledge_graph_check_alerts', 'knowledge_graph_check_followed_entities');