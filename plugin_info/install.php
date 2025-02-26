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
    myToyota_update(false);
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function myToyota_update($direct=true) {
    if ($direct){
        $insta = __('Mise à jour', __FILE__);
    } else {
        $insta = __('Installation', __FILE__);
    }

    $data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
    if (!is_array($data)) {
        log::add('myToyota','warning',__('Impossible de décoder le fichier info.json (non bloquant ici)', __FILE__));
    }
    try {
        $core_version = $data['pluginVersion'];
        config::save('version', $core_version, 'myToyota');
    } catch (\Exception $e) {
        log::add('myToyota','warning',__('Pas de version de plugin (non bloquant ici)', __FILE__));
    }

    $output = __DIR__ . '/../../../plugins/myToyota/ressources/';
    echo $output;
    $output2= shell_exec($output . 'post-install.sh');
    echo $output2;
    foreach (eqLogic::byType('myToyota') as $eqLogic) {
        $eqLogic->save();
        myToyota::synchro_post_update($eqLogic);
        log::add('myToyota', 'debug', __('| Mise à jour des commandes effectuée pour l\'équipement', __FILE__). ' ' . $eqLogic->getHumanName());
    }

    log::add('myToyota','info',__('| (ré)installation des crons si nécessaire', __FILE__));
    $cron = cron::byClassAndFunction('myToyota', 'recupdata');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('myToyota');
        $cron->setFunction('recupdata');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('30 * * * *');
        $cron->save();
    }
    $cron->stop();

    //affectation du level "info" au fichier de log myToyota_datas
    config::save('log::level::myToyota_datas', '{"200":"1"}');
    
    message::add('myToyota', '| ' . $insta . __(' du plugin myToyota terminée', __FILE__));
}

// Fonction exécutée automatiquement après la suppression du plugin
function myToyota_remove() {
    $cron = cron::byClassAndFunction('myToyota', 'recupdata');
    if (is_object($cron)) {
        $cron->remove();
    }
    message::add('myToyota', __('Désinstallation du plugin myToyota terminée', __FILE__));
}
