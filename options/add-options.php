<?php 



if (!defined('ABSPATH')) {
    exit;
}


function knowledge_graph_menu() {
    // Ajoute une page d'option sous Outils
    add_submenu_page(
        'tools.php',                       // Parent : Outils
        'WP Knowledge Graph API',          // Titre de la page
        'WP Knowledge Graph API',            // Libellé du sous-menu
        'manage_options',                  // Capability
        'knowledge-graph',                 // Slug
        'knowledge_graph_page'             // Callback
    );
}
add_action('admin_menu', 'knowledge_graph_menu');