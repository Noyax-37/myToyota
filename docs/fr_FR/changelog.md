# Changelog plugin myToyota

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.


# V1.5

- modification suite à changement des conditions d'accès à l'API de Toyota
- qq corrections de code par ci par là

# V1.4

- ajout d'un fichier log destiné entièrement à la récupération des données suite à action sur bouton "données brutes" de l'équipement. C'est dans ce fichier que vous retrouverez les données utiles à m'envoyer si vous souhaitez rajouter des fonctionnalités au plugin
- nettoyage des paramètres inutiles de certains fonctions
- transfert du cron d'interrogation dans les "tâches planifiées" de jeedom. Per défaut le cycle d'interrogation est de 30 minutes mais vous pouvez le régler depuis cette page comme vous le souhaitez
- mise en forme plus lisible de certaines fonctions
- ajout des paramètres pour les véhicules électriques (merci à ceux qui m'ont envoyés leurs données)

# 05/04/2024 V1.3

- Affichage des infos sur les capacités du véhicule en commandes à distance (merci @Mips pour avoir trouvé la solution, à @Xav-74 pour ses conseils et à tous ceux qui m'ont aidé)

# 02/04/2024 V1.2

- correction pour la commande de certaines fonctions

# 01/04/2024 V1.1 

- Les commandes fonctionnent sauf l'envoi de POI
- pas mal d'amélioration surtout dans la présentation

Comme les autres mise à jour, il faut réinstaller les dépendances + aller dans chaque équipement (=véhicule) + synchroniser et enfin sauvegarder


# 26/03/2024 V1.0 

- Ecriture de la doc
- suppression de l'utilisation du module python
- modification des interfaces (panel, dashboard)
