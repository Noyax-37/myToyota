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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!class_exists('myToyota')) {
	require_once __DIR__ . '/../../core/class/myToyota.class.php';
}

set_time_limit(15);

if (!jeedom::apiAccess(init('apikey'), 'myToyota')) {
    echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (myToyota)', __FILE__);
    http_response_code(403);
    die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}

$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	die();
}


$var_to_log = '';

if (isset($result['device'])) {
	
    foreach ($result['device'] as $key => $data) {
        log::add('myToyota','debug',__("Message du programme myToyota. Id de l'équipement : ", __FILE__) . $key);
        $eqlogic = eqLogic::byId(intval($key), 'myToyota');
        //if (is_object($eqlogic)) {
            foreach ($data as $key2 => $value) {
                if ($key2 == 'vin'){
                    log::add('myToyota','debug',__('Info récupérée :', __FILE__) . ' ' . $key2 . __(' valeur = *****', __FILE__));
                    $vin = $value;
                } else {
                    log::add('myToyota','debug',__('Info récupérée :', __FILE__) . ' ' . $key2 . __(' valeur =', __FILE__) . ' ' . strval($value));
                }
                if ($key2 == 'PID'){
                    //log::add('myToyota','debug',"Message du programme myToyota. PId de l'équipement : " . $value);
                    posix_kill(intval($value), 15);
                } else {
					$cmd = $eqlogic->getCmd('info',$key2);
					if (is_object($cmd)){
						$cmd->event(strval($value));
                        if ($key2 == 'gps_coordinates'){
                            $coordinates = explode(",", $value);
                            //log::add('myToyota','info',"Message du programme myToyota. Localisation véhicule : " . $value . ' ' . $coordinates[0] . ' ' . $coordinates[1]);
                        }
					}
				}
            }
        //}
    }
    myToyota::chercheLycos($vin, $coordinates[0], $coordinates[1]);
}

