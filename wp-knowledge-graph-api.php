<?php 
/**
 * Plugin Name: WP Knowledge Graph API
 * Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-knowledge-graph-api/
 * Description: WP Knowledge Graph API est un plugin WordPress qui permet de rechercher, suivre et surveiller des entités via l'API Google Knowledge Graph. Il offre une interface simple pour explorer des entités, s'abonner à leur suivi et recevoir des alertes par email en cas de changements, aidant ainsi à garder une veille active sur les sujets importants pour votre site.
 * Version: 1.0
 * Author: Kevin Benabdelhak
 * Author URI: https://kevin-benabdelhak.fr/
 * Contributors: kevinbenabdelhak
 */

if (!defined('ABSPATH')) {
    exit;
}



if ( !class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kevinbenabdelhak/WP-Knowledge-Graph-API/', 
    __FILE__,
    'wp-knowledge-graph-api' 
);
$monUpdateChecker->setBranch('main');





require_once plugin_dir_path(__FILE__) . 'options/add-options.php';
require_once plugin_dir_path(__FILE__) . 'options/options.php';
require_once plugin_dir_path(__FILE__) . 'script/script.php';
require_once plugin_dir_path(__FILE__) . 'mail/add-mail.php';
require_once plugin_dir_path(__FILE__) . 'mail/mail.php';
require_once plugin_dir_path(__FILE__) . 'mail/save-alert.php';
require_once plugin_dir_path(__FILE__) . 'cron/cron.php';


add_action('wp_ajax_search_knowledge_graph',   'search_knowledge_graph');
add_action('wp_ajax_save_api_key',             'save_api_key');
add_action('wp_ajax_get_followed_entities',    'ajax_get_followed_entities');
add_action('wp_ajax_add_followed_entity',      'ajax_add_followed_entity');
add_action('wp_ajax_remove_followed_entity',   'ajax_remove_followed_entity');