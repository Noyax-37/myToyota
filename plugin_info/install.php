<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
function myToyota_install() {
    // $output= shell_exec('/var/www/html/plugins/myToyota/ressources/post-install.sh');
    // echo $output;
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function myToyota_update() {
    $output= shell_exec('/var/www/html/plugins/myToyota/ressources/post-install.sh');
    echo $output;
    message::add('myToyota', 'Mise à jour du plugin myToyota terminée, ATTENTION: pour cette mise à jour vous devez aller dans chaque équipement pour indiquer le constructeur, refaire une synchro et le sauvegarder');
    log::add('myToyota', 'error', 'Mise à jour du plugin myToyota terminée, ATTENTION: pour cette mise à jour vous devez aller dans chaque équipement pour indiquer le constructeur, refaire une synchro et le sauvegarder');
}

// Fonction exécutée automatiquement après la suppression du plugin
function myToyota_remove() {
    message::add('myToyota', 'Désinstallation du plugin myToyota terminée');
}
