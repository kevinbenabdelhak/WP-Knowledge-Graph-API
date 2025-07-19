<?php 

if (!defined('ABSPATH')) {
    exit;
}

function search_knowledge_graph() {
    $apiKey = sanitize_text_field($_POST['api_key']);
    $query  = sanitize_text_field($_POST['query']);
    $url = 'https://kgsearch.googleapis.com/v1/entities:search?query=' . urlencode($query) . '&key=' . $apiKey . '&limit=10&indent=True';
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        echo json_encode(['error'=>'Erreur lors de la récupération des données']);
        wp_die();
    }
    $body = wp_remote_retrieve_body($response);
    echo $body;
    wp_die();
}

if (!defined('ABSPATH')) {
    exit;
}

function save_api_key() {
    if (isset($_POST['api_key'])) {
        update_option('knowledge_graph_api_key', sanitize_text_field($_POST['api_key']));
        echo 'Clé API enregistrée';
    }
    wp_die();
}
function get_user_followed_entities($user_id = null) {
    if (is_null($user_id)) $user_id = get_current_user_id();
    $data = get_user_meta($user_id, 'knowledge_graph_followed_entities', true);
    if (empty($data) || !is_array($data)) return [];
    return $data;
}
function save_user_followed_entities($entities, $user_id = null) {
    if (is_null($user_id)) $user_id = get_current_user_id();
    update_user_meta($user_id, 'knowledge_graph_followed_entities', $entities);
}
function ajax_get_followed_entities() {
    $entities = get_user_followed_entities();
    echo json_encode($entities); wp_die();
}
function ajax_add_followed_entity() {
    $name = sanitize_text_field($_POST['name']);
    $types = sanitize_text_field($_POST['types']);
    $score = sanitize_text_field($_POST['score']);
    $kgid = sanitize_text_field($_POST['kgid']);
    $image = isset($_POST['image']) ? esc_url_raw($_POST['image']) : '';
    $entities = get_user_followed_entities();
    if (!array_filter($entities, function($e)use($name,$kgid){return $e['name']==$name && $e['kgid']==$kgid;})) {
        $entities[] = [
            'name'=>$name,
            'type'=>$types,
            'score'=>$score,
            'kgid'=>$kgid,
            'image'=>$image,
        ];
        save_user_followed_entities($entities);
    }
    echo 'OK'; wp_die();
}
function ajax_remove_followed_entity() {
    $name = sanitize_text_field($_POST['name']);
    $entities = get_user_followed_entities();
    $entities = array_values(array_filter($entities, function($e)use($name){return $e['name'] != $name;}));
    save_user_followed_entities($entities);
    echo 'OK'; wp_die();
}