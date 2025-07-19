=== WP Knowledge Graph API ===

Contributors: kevinbenabdelhak
Tags: google knowledge graph, entités, recherche, suivi, alerte, API, automatisation, emails
Requires at least: 5.0
Tested up to: 6.6.2
Requires PHP: 7.0
Stable tag: 2.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Outil de recherche et de suivi d'entités via l'API Google Knowledge Graph. Suivez les changements d'entités importantes, recevez des alertes email, et consultez l'historique des notifications, directement dans votre administration WordPress.

== Description ==

WP Knowledge Graph API vous permet d'effectuer des recherches dans le Google Knowledge Graph, de suivre des entités (personnalités, entreprises, lieux, etc.) et d'être informé automatiquement de tout changement sur celles-ci via des alertes email programmables.

Fonctionnalités principales :

- **Recherche intelligente dans le Knowledge Graph** : Trouvez et explorez des entités Google directement depuis l'admin WordPress.
- **Suivi personnalisé des entités** : Enregistrez n'importe quelle entité pour surveiller ses changements dans le temps.
- **Alerte email automatique** : Restez informé des évolutions (valeurs, présence…) des entités que vous suivez.
- **Programmation flexible des vérifications** : Choisissez la fréquence d’envoi des alertes (jours, heures, minutes).
- **Historique rapide des emails d'alerte** : Consultez le détail des notifications envoyées pour chaque entité suivie.
- **Gestion simple et claire du suivi** : Ajoutez, enlevez, ou réinitialisez votre liste de suivi d’un seul clic.
- **Interface utilisateur intégrée** : Tout se gère depuis Outils > WP Knowledge Graph API.

Idéal pour les consultants, communicants, marketeurs, community managers ou pour toute veille sur des personnalités, marques ou sujets précis !

== Installation ==

* Télécharger le plugin :
    - Téléchargez l’archive ZIP sur https://kevin-benabdelhak.fr/plugins/wp-knowledge-graph-api/ 

* Installation sur WordPress :
    1. Rendez-vous dans « Extensions » > « Ajouter ».
    2. Cliquez sur « Téléverser une extension ».
    3. Sélectionnez le fichier ZIP et cliquez sur « Installer maintenant ».
    4. Activez le plugin.

* Configuration :
    - Accédez à Outils > WP Knowledge Graph API
    - Renseignez votre clé API Google Knowledge Graph (voir la [documentation officielle](https://developers.google.com/knowledge-graph)).
    - Commencez vos recherches, ajoutez des entités à suivre, et paramétrez les alertes email.

== Frequently Asked Questions ==

= Où obtenir une clé API Google Knowledge Graph ? =
Créez un projet API sur la [console Google Cloud](https://console.cloud.google.com/apis) et activez le service Knowledge Graph API.

= L’outil peut-il suivre des changements “en temps réel” ? =
Les vérifications s’effectuent selon la fréquence que vous choisissez via le cron WordPress (minimum : chaque minute).

= Que suivre exactement avec ce plugin ? =
N’importe quelle entité présente dans Google Knowledge Graph : personnalités, entreprises, films, lieux, concepts, événements, etc.

= L’ensemble des administrateurs peuvent-ils avoir leur propre liste de suivi ? =
Oui, la liste des entités suivies est propre à chaque utilisateur.

= Où voir l’historique des alertes envoyées ? =
Dans l’onglet « Alerte » de la page Outils > WP Knowledge Graph API, un tableau récapitule les dernières notifications emails envoyées.

== Changelog ==

= 1.0 =
* Rechercher des entités du Knowledge Graph de Google
* Gestion avancée de la fréquence d’alerte jusqu'à la minute.
* Possibilité de réinitialiser l’historique et la liste de suivi (par utilisateur).
* Affichage enrichi des entités (images, types).
* Enregistrement des historiques d’alertes envoyées.
* Personnalisation de l’email cible.
* Programmation des crons d’alerte flexible.