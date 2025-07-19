<?php 

if (!defined('ABSPATH')) {
    exit;
}


// --- PAGE ADMIN ---
function knowledge_graph_page() {
    $api_key     = esc_attr(get_option('knowledge_graph_api_key'));
    $alert_mode  = get_option('knowledge_graph_alert_mode', 'none');
    $alert_email = esc_attr(get_option('knowledge_graph_alert_email', get_bloginfo('admin_email')));
    $freq_days   = intval(get_option('knowledge_graph_alert_days', 0));
    $freq_hours  = intval(get_option('knowledge_graph_alert_hours', 1));
    $freq_minutes= intval(get_option('knowledge_graph_alert_minutes', 0));
    $next_cron   = wp_next_scheduled('knowledge_graph_check_alerts');
    $now         = time();
    $diff        = $next_cron ? max(0, $next_cron-$now) : 0;
    ?>
    <div class="wrap">
        <h1>WP Knowledge Graph API</h1>
        <p class="nav-tab-wrapper">
            <a href="#search" class="nav-tab nav-tab-active" id="search-tab">Rechercher</a>
            <a href="#follow" class="nav-tab" id="follow-tab">Suivre</a>
            <a href="#alert" class="nav-tab" id="alert-tab">Alerte</a>
            <a href="#settings" class="nav-tab" id="settings-tab">Paramètres</a>
        </p>
        <div id="search" class="tab-content">
            <input type="text" id="search-query" class="regular-text" placeholder="Entrez votre recherche...">
            <button id="search-btn" class="button button-primary">Chercher</button>
            <div id="loader" style="display:none;" class="notice notice-info">Chargement...</div>
            <table id="search-results" class="widefat" style="display: none;">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Score du résultat</th>
                        <th>Suivre</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div id="settings" class="tab-content" style="display:none;">
            <label for="api-key">Clé API Google :</label>
            <input type="text" id="api-key" class="regular-text" value="<?php echo $api_key; ?>">
            <button id="save-api-key" class="button button-primary">Enregistrer</button>
        </div>
        <div id="follow" class="tab-content" style="display:none;">
            <h2>Entités suivies</h2>
            <table id="follow-list" class="widefat" style="display: none;">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Score du résultat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button id="reset-follow-list" class="button button-secondary" style="margin-top:10px;">Réinitialiser le tableau</button>
            <div id="reset-follow-list-msg" style="margin:8px 0;color:green;"></div>
        </div>
        <div id="alert" class="tab-content" style="display:none;">
            <h2>
                Alertes sur entités suivies
                <span id="next-verif-span" style="font-size:14px;font-weight:normal;margin-left:24px;color:#0073aa;">
                Prochaine vérification dans <span id="recap-next-cron"><?php echo gmdate("z\j G\h i\m s\s", $diff); ?></span>
                </span>
            </h2>
            <form id="alert-settings-form">
                <label for="alert-mode">Mode d'alerte&nbsp;:</label>
                <select id="alert-mode" name="alert-mode">
                    <option value="none" <?php selected($alert_mode, 'none'); ?>>Ne pas être alerté</option>
                    <option value="change" <?php selected($alert_mode, 'change'); ?>>Être alerté au moindre changement</option>
                    <option value="regular" <?php selected($alert_mode, 'regular'); ?>>Recevoir des alertes régulièrement</option>
                </select>
                <div id="alert-extra-fields" style="<?php echo ($alert_mode=='none'?'display:none;':''); ?> margin-top: 15px;">
                    <br>
                    <label for="alert-email">Email de réception :</label>
                    <input type="email" id="alert-email" class="regular-text" value="<?php echo $alert_email; ?>">
                    <br><br>
                    <div id="alert-freq" style="display:block;">
                        <label>Fréquence de vérification :</label>
                        <input type="number" min="0" style="width:60px" id="freq-days" value="<?php echo $freq_days; ?>"> jours 
                        <input type="number" min="0" max="23" style="width:60px" id="freq-hours" value="<?php echo $freq_hours; ?>"> heures 
                        <input type="number" min="0" max="59" style="width:60px" id="freq-minutes" value="<?php echo $freq_minutes; ?>"> minutes 
                    </div>
                    <br>
                </div>
                <button id="save-alert-settings" class="button button-primary" type="button">Enregistrer</button>
                <span class="alert-settings-status"></span>
            </form>
            <hr>
            <h3>Récapitulatif des alertes envoyées</h3>
            <div id="recap-emails-content" style="margin-bottom:30px;">
                <em>(Récupération...)</em>
            </div>
            <button id="reset-alert-list" class="button button-secondary" style="margin-bottom:20px;">Réinitialiser le tableau</button>
            <div id="reset-alert-list-msg" style="margin-bottom:20px;color:green;"></div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        // Recherche KG (tableau "Rechercher")
        $('#search-btn').on('click', function() {
            var query = $('#search-query').val();
            var apiKey = $('#api-key').val();
            $('#loader').show();
            $('#search-results').hide();
            if (query && apiKey) {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {action: 'search_knowledge_graph', query: query, api_key: apiKey},
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#loader').hide();
                        if (data.itemListElement) {
                            var rows = '';
                            $.each(data.itemListElement, function(index, element) {
                                var item = element.result;
                                var name = item.name || 'N/A';
                                var types = (item['@type'] ? item['@type'].join(', ') : 'N/A');
                                var score = element.resultScore || 'N/A';
                                var kgid  = item['@id'] || '';
                                var image = (item.image && item.image.contentUrl) ? item.image.contentUrl : '';
                                var cleanedKgid = kgid.replace(/^kg:/, "");
                                var kglink = cleanedKgid ? '<a href="https://www.google.com/search?kgmid=' + cleanedKgid + '" target="_blank">' + name + '</a>' : name;
                                var imgTag = image ? '<img src="' + image + '" alt="" style="height:36px;max-width:48px;border-radius:5px;">' : '';
                                rows += '<tr>';
                                rows += '<td>' + imgTag + '</td>';
                                rows += '<td>' + kglink + '</td>';
                                rows += '<td>' + types + '</td>';
                                rows += '<td>' + score + '</td>';
                                rows += '<td><input type="checkbox" class="follow-checkbox" data-name="' + encodeURIComponent(name) + '" data-kgid="'+encodeURIComponent(kgid)+'" data-types="' + encodeURIComponent(types) + '" data-score="' + encodeURIComponent(score) + '" data-image="' + encodeURIComponent(image) + '" /></td>';
                                rows += '</tr>';
                            });
                            $('#search-results tbody').html(rows);
                            $('#search-results').show();
                            $.ajax({
                                url: ajaxurl,
                                method: 'POST',
                                data: {action: 'get_followed_entities'},
                                success: function(response2) {
                                    let followed = [];
                                    try { followed = JSON.parse(response2);} catch(e){}
                                    $('#search-results .follow-checkbox').each(function(){
                                        var $cb = $(this);
                                        var n = decodeURIComponent($cb.data('name'));
                                        var kgid = decodeURIComponent($cb.data('kgid'));
                                        if (followed.find(f => f.name === n && f.kgid === kgid)) {
                                            $cb.prop('checked', true);
                                        }
                                    });
                                }
                            });
                        }
                    },
                    error: function() {
                        $('#loader').hide();
                        alert('Erreur lors de la récupération des données');
                    }
                });
            } else {
                alert('Veuillez remplir les champs requis.');
                $('#loader').hide();
            }
        });

        // Affichage du tableau Suivre
        function loadFollowedEntities() {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action: 'get_followed_entities'},
                success: function(response) {
                    let entities = [];
                    try { entities = JSON.parse(response); } catch(e) {}
                    const followList = $('#follow-list tbody');
                    followList.empty();
                    if (entities && entities.length > 0) {
                        entities.forEach(function(entity) {
                            var cleanedKgid = (entity.kgid||'').replace(/^kg:/, "");
                            var kglink = cleanedKgid ? '<a href="https://www.google.com/search?kgmid=' + cleanedKgid + '" target="_blank">' + entity.name + '</a>' : entity.name;
                            var imgTag = entity.image ? '<img src="' + entity.image + '" alt="" style="height:36px;max-width:48px;border-radius:5px;">' : '';
                            followList.append('<tr><td>' + imgTag + '</td><td>' + kglink + '</td><td>' + entity.type + '</td><td>' + entity.score + '</td><td><button class="remove-follow" data-name="' + encodeURIComponent(entity.name) + '">✖️</button></td></tr>');
                        });
                        $('#follow-list').show();
                    } else {
                        $('#follow-list').hide();
                    }
                }
            });
        }
        loadFollowedEntities();

        // Bouton reset suivi
        $('#reset-follow-list').on('click', function(e){
            e.preventDefault();
            if(!confirm('Voulez-vous vraiment supprimer toutes les entités suivies ?')) return;
            $('#reset-follow-list-msg').text('Réinitialisation...');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action: 'reset_followed_entities'},
                success: function() {
                    $('#reset-follow-list-msg').text('Tableau vidé.');
                    loadFollowedEntities();
                    setTimeout(function(){$('#reset-follow-list-msg').text('');},2000);
                }
            });
        });

        // Bouton reset alertes
        $('#reset-alert-list').on('click', function(e){
            e.preventDefault();
            if(!confirm('Voulez-vous vraiment vider l’historique des alertes ?')) return;
            $('#reset-alert-list-msg').text('Réinitialisation...');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action: 'reset_alert_log'},
                success: function() {
                    $('#reset-alert-list-msg').text('Historique vidé.');
                    loadRecapEmails();
                    setTimeout(function(){$('#reset-alert-list-msg').text('');},2000);
                }
            });
        });

        // Recap alertes avec liens Google
        function loadRecapEmails() {
            $('#recap-emails-content').html('<em>(Récupération...)</em>');
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {action:"get_kg_sent_mails_entites"},
                dataType: "json",
                success: function(resp){
                    if( !resp || !resp.length ) {
                        $('#recap-emails-content').html('<em>Aucune alerte envoyée récemment.</em>');
                    } else {
                        var html = '<table class="widefat"><thead><tr><th>Image</th><th>Date/Heure</th><th>Email destinataire</th><th>Nom de l\'entité</th><th>Score du résultat</th></tr></thead><tbody>';
                        $.each(resp,function(i,entry){
                            var cleanedKgid = (entry.kgid||'').replace(/^kg:/, "");
                            var link = cleanedKgid ? '<a href="https://www.google.com/search?kgmid=' + cleanedKgid + '" target="_blank">' + entry.entite + '</a>' : entry.entite;
                            var imgTag = entry.image ? '<img src="' + entry.image + '" alt="" style="height:36px;max-width:48px;border-radius:5px;">' : '';
                            html += "<tr>";
                            html += '<td>'+imgTag+'</td>';
                            html += '<td style="white-space:nowrap;">'+entry.date+'</td>';
                            html += '<td>'+entry.email+'</td>';
                            html += '<td>'+link+'</td>';
                            html += '<td>'+entry.score+'</td>';
                            html += "</tr>";
                        });
                        html += "</tbody></table>";
                        $('#recap-emails-content').html(html);
                    }
                }
            });
        }
        if($('#alert').is(':visible')) loadRecapEmails();

        // UI divers
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').hide();
            $('#' + $(this).attr('id').replace('-tab', '')).show();
            if ($(this).attr('id') == 'follow-tab') loadFollowedEntities();
            if ($(this).attr('id') == 'alert-tab') {
                loadRecapEmails(); updateNextCron();
            }
        });
        $(document).on('change', '.follow-checkbox', function() {
            var name = decodeURIComponent($(this).data('name'));
            var types = decodeURIComponent($(this).data('types'));
            var score = decodeURIComponent($(this).data('score'));
            var kgid = decodeURIComponent($(this).data('kgid'));
            var image = decodeURIComponent($(this).data('image'));
            var action_type = $(this).is(':checked') ? "add" : "remove";
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action: action_type+'_followed_entity', name: name, types: types, score: score, kgid: kgid, image: image},
                success: function() { loadFollowedEntities(); }
            });
        });
        $(document).on('click', '.remove-follow', function() {
            var name = decodeURIComponent($(this).data('name'));
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action: 'remove_followed_entity', name: name},
                success: function() { loadFollowedEntities(); }
            });
        });
        $('#save-api-key').on('click', function() {
            var apiKey = $('#api-key').val();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action: 'save_api_key', api_key: apiKey},
                success: function() { alert('Clé API enregistrée!'); }
            });
        });
        function toggleAlertFields() {
            var sel = $('#alert-mode').val();
            if(sel == 'none') {
                $('#alert-extra-fields').hide();
                $('#next-verif-span').hide();
            } else {
                $('#alert-extra-fields').show();
                $('#alert-freq').show();
                $('#next-verif-span').show();
            }
        }
        $('#alert-mode').on('change', function(){ toggleAlertFields(); });
        toggleAlertFields();

        $('#save-alert-settings').on('click', function(e){
            e.preventDefault();
            var mode  = $('#alert-mode').val();
            var email = $('#alert-email').val();
            var days  = $('#freq-days').val();
            var hours = $('#freq-hours').val();
            var mins  = $('#freq-minutes').val();
            $('.alert-settings-status').text('Enregistrement...');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'save_alert_settings',
                    alert_mode: mode,
                    alert_email: email,
                    freq_days: days,
                    freq_hours: hours,
                    freq_minutes: mins
                },
                success: function(resp) {
                    $('.alert-settings-status').text('✅ Enregistré');
                    setTimeout(function(){ $('.alert-settings-status').text(''); }, 2000);
                    updateNextCron();
                }
            });
        });
        function updateNextCron() {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {action:'get_next_kg_cron'},
                success: function(resp){
                    if(resp === '') return;
                    try {
                        var seconds = parseInt(resp);
                        if (isNaN(seconds) || seconds<0) seconds = 0;
                        window.clearInterval(window.kgCountdownInterval);
                        function displayCountdown(secs){
                            var s = secs%60, m=Math.floor(secs/60)%60, h=Math.floor(secs/3600)%24, d=Math.floor(secs/86400);
                            var str = "" + (d>0 ? (d+"j "):"") + (h>0? h+"h ":"") + (m>0? m+"m ":"") + s+"s";
                            $('#recap-next-cron').text(str);
                        }
                        displayCountdown(seconds);
                        window.kgCountdownInterval = window.setInterval(function(){
                            seconds--;
                            if(seconds<0){
                                $('#recap-next-cron').text('—');
                                window.clearInterval(window.kgCountdownInterval);
                            } else {
                                displayCountdown(seconds);
                            }
                        },1000);
                    } catch(e){}
                }
            });
        }
        if($('#alert').is(':visible')) updateNextCron();
    });
    </script>
<?php
}