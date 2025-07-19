<?php 

if (!defined('ABSPATH')) {
    exit;
}

function knowledge_graph_check_followed_entities() {
    $mode = get_option('knowledge_graph_alert_mode', 'none');
    if ($mode=='none') return;
    $apiKey = get_option('knowledge_graph_api_key');
    if (!$apiKey) return;
    $alert_mail = get_option('knowledge_graph_alert_email', get_bloginfo('admin_email'));

    foreach ( get_users(['fields'=>'ID']) as $user_id ) {
        $followed = get_user_followed_entities($user_id);
        if (empty($followed)) continue;
        $cache_key = "kg_follow_cache_user_$user_id";
        $previous = get_transient( $cache_key );
        if (!$previous) $previous = [];
        $changes = [];
        $new_entities = [];
        foreach ($followed as $entity) {
            $url = 'https://kgsearch.googleapis.com/v1/entities:search?query=' . urlencode($entity['name']) . '&key=' . $apiKey . '&limit=10';
            $response = wp_remote_get($url);
            if (is_wp_error($response)) continue;
            $json = json_decode(wp_remote_retrieve_body($response), 1);

            $found = false; $score = null; $types = null; $image = '';
            if (!empty($json['itemListElement'])) {
                foreach ($json['itemListElement'] as $el) {
                    if (!empty($el['result']['@id']) && $el['result']['@id'] === $entity['kgid']) {
                        $found = true;
                        $score = isset($el['resultScore']) ? $el['resultScore'] : null;
                        $types = isset($el['result']['@type']) ? (is_array($el['result']['@type']) ? implode(', ', $el['result']['@type']) : $el['result']['@type']) : '';
                        $image = (isset($el['result']['image']['contentUrl']) ? $el['result']['image']['contentUrl'] : '');
                        break;
                    }
                }
            }
            if (!$found) {
                $changes[] = '[SUPPRIMÉE] ' . $entity['name'];
                knowledge_graph_add_sent_mail_entity($alert_mail, $entity['name'], $entity['score'], $entity['kgid'], $entity['image']);
                // On ne garde pas dans la meta une entité supprimée
            } else {
                $before_score = null;
                foreach ($previous as $prev) {
                    if ($prev['name'] === $entity['name'] && $prev['kgid'] === $entity['kgid']) {
                        $before_score = $prev['score'];
                        break;
                    }
                }
                if ($score === null) $score = 'ND';
                if ($mode == 'change' && $before_score !== null && (string)$before_score !== (string)$score) {
                    $changes[] = '[CHANGEMENT DE SCORE] ' . $entity['name'] . ' : ' . $before_score . ' → ' . $score;
                }
                $new_entities[] = [
                    'name'   => $entity['name'],
                    'type'   => $types,
                    'score'  => $score,
                    'kgid'   => $entity['kgid'],
                    'image'  => $image ?: $entity['image'],
                ];
                if ($mode == 'regular' || ($mode == 'change' && $before_score !== null && (string)$before_score !== (string)$score)) {
                    knowledge_graph_add_sent_mail_entity($alert_mail, $entity['name'], $entity['score'], $entity['kgid'], $image ?: $entity['image']);
                }
            }
        }
        set_transient($cache_key, $new_entities, 12 * HOUR_IN_SECONDS);

        // *** MISE À JOUR de la meta utilisateur ! ***
        // On ne garde dans le suivi que les entités encore existantes
        save_user_followed_entities($new_entities, $user_id);

        // Construction du mail (comme expliqué dans la précédente réponse)
        if ($mode == 'regular' || ($mode == 'change' && $changes)) {
            $mess = '';
            if($changes) $mess .= "Changements détectés:\n" . implode("\n",$changes) . "\n\n";
            if($mode=='regular') {
                $mess .= "Etat actuel de vos entités suivies:\n";
                $alert_logs = get_option('knowledge_graph_alert_mails_entites', []);
                $latest_scores = [];
                foreach($alert_logs as $log_row) {
                    $k = $log_row['entite'] . '|' . $log_row['kgid'];
                    $latest_scores[$k] = $log_row;
                }
                foreach ($new_entities as $e) {
                    $k = $e['name'] . '|' . $e['kgid'];
                    $score = isset($latest_scores[$k]) ? $latest_scores[$k]['score'] : $e['score'];
                    $types = $e['type'];
                    $link = $e['kgid'] ? 'https://www.google.com/search?kgmid=' . preg_replace('/^kg:/', '', $e['kgid']) : '';
                    $mess .= ($link ? $e['name']." → $link" : $e['name']).' ('.$types.') : '.$score."\n";
                }
            }
            if($mess) {
                wp_mail($alert_mail, '[Knowledge Graph] Alerte sur vos entités suivies', $mess);
            }
        }
    }
}