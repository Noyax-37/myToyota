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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

if (!class_exists('myToyota_API')) {
	require_once __DIR__ . '/../php/myToyota_API.php';
}

//définition des constantes
const CMD_LAST_UPDATE = 'lastUpdate';
const CMD_GPS_COORDINATES = 'gps_coordinates';
const TYPE_INFO = 'info';
const TYPE_ACTION = 'action';
const SUBTYPE_STRING = 'string';
const SUBTYPE_NUMERIC = 'numeric';
const SUBTYPE_BINARY = 'binary';
const SUBTYPE_OTHER = 'other';


class myToyota extends eqLogic {
  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtmlavecpar exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */

/*  
  // Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {
  }
*/

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

/*  
  // Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron() {
  }
*/  

/*  
  // Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {    
    $debug = false;
    $idvehicule = 'Aucun';
    foreach (eqLogic::byType('myToyota', true) as $eqLogic) {
      $nameVehicule = $eqLogic->getName();
      log::add('myToyota', 'debug', "| récupération des données du véhicule : " . '  ' . $nameVehicule);
      myToyota::interromyToyota($eqLogic);
      //$coordinates = myToyota::getGPSCoordinates($eqLogic->getConfiguration('vehicle_vin'));
      //myToyota::getDistanceLocation2($eqlogic, $coordinates['latitude'], $coordinates['longitude']);
    }
  }
*/
  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {}
  */

  /*     * *********************Méthodes d'instance************************* */
  // * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
  // * lors de la création semi-automatique d'un post sur le forum community

  public static function recupdata() {    
    $debug = false;
    $idvehicule = 'Aucun';
    foreach (eqLogic::byType('myToyota', true) as $eqLogic) {
      $nameVehicule = $eqLogic->getName();
      log::add('myToyota', 'debug', __("| récupération des données du véhicule : ", __FILE__) . $nameVehicule);
      myToyota::interromyToyota($eqLogic);
      //$coordinates = myToyota::getGPSCoordinates($eqLogic->getConfiguration('vehicle_vin'));
      //myToyota::getDistanceLocation2($eqlogic, $coordinates['latitude'], $coordinates['longitude']);
    }
  }

  public static function getConfigForCommunity() {
    if (!file_exists('/var/www/html/plugins/myToyota/plugin_info/info.json')) {
      log::add('myToyota','warning',__('Pas de fichier info.json', __FILE__));
    }
    $data = json_decode(file_get_contents('/var/www/html/plugins/myToyota/plugin_info/info.json'), true);
    if (!is_array($data)) {
        log::add('myToyota','warning',__('Impossible de décoder le fichier info.json', __FILE__));
    }
    try {
        $core_version = $data['pluginVersion'];
    } catch (\Exception $e) {
        log::add('myToyota','warning',__('Impossible de récupérer la version.', __FILE__));
    }


    $index = 1;
    $CommunityInfo = "";
    foreach (eqLogic::byType('myToyota', true) as $myToyota)  {
      if ($myToyota->getConfiguration('vehicle_brand') == 'T') { $brand = 'Toyota'; }
      else if ($myToyota->getConfiguration('vehicle_brand') == 'L') { $brand = 'Lexus'; }
      else { $brand = $myToyota->getConfiguration('vehicle_brand'); }
      $CommunityInfo = $CommunityInfo . "Vehicle #" . $index . " - Brand : " . $brand . " - Model : ". $myToyota->getConfiguration('vehicle_model') . " - Year : ". $myToyota->getConfiguration('vehicle_year') . " - Type : ". $myToyota->getConfiguration('vehicle_type') . "\n";
      $index++;
    }
    
    $CommunityInfo .= '<br/>';


    $hw = jeedom::getHardwareName();
    if ($hw == 'diy')
        $hw = trim(shell_exec('systemd-detect-virt'));
    if ($hw == 'none')
        $hw = 'diy';
    $distrib = trim(shell_exec('. /etc/*-release && echo $ID $VERSION_ID'));
    $CommunityInfo .= 'OS: ' . $distrib . ' on ' . $hw;
    $CommunityInfo .= ' ; PHP: ' . phpversion();
    $CommunityInfo .= '<br/>myToyota: version ' . $core_version;
    $CommunityInfo .= ' ; cmds: ' . count(cmd::searchConfiguration('', myToyota::class));
    return $CommunityInfo;
  }
   


  // Fonction pour exclure un sous répertoire de la sauvegarde
  public static function backupExclude() {
	}

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  	/* fonction appelée pendant la séquence de sauvegarde avant l'insertion 
    * dans la base de données pour une mise à jour d'une entrée */
    
    if (empty($this->getConfiguration('username'))) {
			throw new Exception(__('L\'identifiant ne peut pas être vide', __FILE__));
		}
		if (empty($this->getConfiguration('password'))) {
			throw new Exception(__('Le mot de passe ne peut etre vide', __FILE__));
		}
		if (empty($this->getConfiguration('vehicle_vin'))) {
			throw new Exception(__('Le numéro d\'identification du véhicule ne peut pas être vide', __FILE__));
		}
		if (empty($this->getConfiguration('vehicle_brand'))) {
			throw new Exception(__('La marque du véhicule ne peut pas être vide', __FILE__));
		}

  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
    //$this->setLogicalId($this->getConfiguration('vehicle_vin'));
    if ($this->getLogicalId() == 'refresh') {
      log::add('myToyota', 'debug', '| Return refresh');
			return;
		}
    $capabilities = $this->getConfiguration('capabilities');
    if (is_array($capabilities)){
      $this->setConfiguration('capabilities',json_encode($capabilities));
      $this->save(true);
    }
    log::add('myToyota', 'debug', '| Return presave config capabilities :' . $this->getConfiguration('capabilities'));
    

    return;
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
    // Informations générales sur le véhicule comme la marque, le modèle, l'année, etc.
    $this->createCmd('brand', 'Marque', 1, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd('model', 'Modèle', 2, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd('year', 'Année', 3, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd('type', 'Type', 4, TYPE_INFO, SUBTYPE_STRING); // Type de véhicule (électrique, hybride, etc.)
    $this->createCmd('carburant', 'Carburant', 5, TYPE_INFO, SUBTYPE_STRING, 1); // Type de carburant utilisé
    $this->createCmd('mileage', 'Kilométrage', 6, TYPE_INFO, SUBTYPE_NUMERIC, 1); // Kilométrage total du véhicule

    // État des portes et fenêtres du véhicule
    $doorsAndWindows = [
        ['doorLockState', 'Verrouillage', 7],
        ['allDoorsState', 'Toutes les portes', 8],
        ['allWindowsState', 'Toutes les fenêtres', 9],
        ['doorDriverFront', 'Porte Conducteur Avant', 10],
        ['doorDriverRear', 'Porte Conducteur Arrière', 11],
        ['doorPassengerFront', 'Porte Passager Avant', 12],
        ['doorPassengerRear', 'Porte Passager Arrière', 13],
        ['windowDriverFront', 'Fenêtre Conducteur Avant', 14],
        ['windowDriverRear', 'Fenêtre Conducteur Arrière', 15],
        ['windowPassengerFront', 'Fenêtre Passager Avant', 16],
        ['windowPassengerRear', 'Fenêtre Passager Arrière', 17],
        ['trunk_state', 'Coffre', 18],
        ['hood_state', 'Capot Moteur', 19],
        ['moonroof_state', 'Toit ouvrant', 20],
    ];
    foreach ($doorsAndWindows as $cmd) {
        $this->createCmd($cmd[0], $cmd[1], $cmd[2], TYPE_INFO, SUBTYPE_STRING);
    }

    // Pression des pneus et consignes pour chaque pneu
    $tires = [
        ['tireFrontLeft_pressure', 'Pression pneu avant gauche', 21],
        ['tireFrontLeft_target', 'Consigne pneu avant gauche', 22],
        ['tireFrontRight_pressure', 'Pression pneu avant droit', 23],
        ['tireFrontRight_target', 'Consigne pneu avant droit', 24],
        ['tireRearLeft_pressure', 'Pression pneu arrière gauche', 25],
        ['tireRearLeft_target', 'Consigne pneu arrière gauche', 26],
        ['tireRearRight_pressure', 'Pression pneu arrière droit', 27],
        ['tireRearRight_target', 'Consigne pneu arrière droit', 28],
    ];
    foreach ($tires as $cmd) {
        $this->createCmd($cmd[0], $cmd[1], $cmd[2], TYPE_INFO, SUBTYPE_NUMERIC);
    }

    // Informations liées à la charge électrique du véhicule
    $charging = [
        ['chargingStatus', 'Etat de la charge', 29, SUBTYPE_STRING],
        ['connectorStatus', 'Etat de la prise', 30, SUBTYPE_BINARY],
        ['beRemainingRangeElectric', 'Km restant (électrique)', 31, SUBTYPE_NUMERIC],
        ['chargingLevelHv', 'Charge restante', 32, SUBTYPE_NUMERIC],
        ['chargingEndTime', 'Heure de fin de charge', 33, SUBTYPE_STRING],
    ];
    foreach ($charging as $cmd) {
        $this->createCmd($cmd[0], $cmd[1], $cmd[2], TYPE_INFO, $cmd[3]);
    }

    // Informations sur la consommation de carburant pour les véhicules thermiques
    $this->createCmd('beRemainingRangeFuelKm', 'Km restant (thermique)', 34, TYPE_INFO, SUBTYPE_NUMERIC);
    $this->createCmd('remaining_fuel', 'Carburant restant', 35, TYPE_INFO, SUBTYPE_NUMERIC);

    // Autres informations utiles comme les messages du véhicule, coordonnées GPS et dernière mise à jour
    $this->createCmd('vehicleMessages', 'Messages', 36, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd(CMD_GPS_COORDINATES, 'Coordonnées GPS', 37, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd(CMD_LAST_UPDATE, 'Dernière mise à jour', 38, TYPE_INFO, SUBTYPE_STRING);

    // Actions disponibles pour interagir avec le véhicule (comme climatiser, charger, etc.)
    $actions = [
        ['refresh', 'Rafraichir', 39],
        ['climateNow', 'Climatiser', 40],
        ['stopClimateNow', 'Stop Climatiser', 41],
        ['chargeNow', 'Charger', 42],
        ['stopChargeNow', 'Stop Charger', 43],
        ['doorLock', 'Verrouiller', 44],
        ['doorUnlock', 'Déverrouiller', 45],
        ['lightFlash', 'Appel de phares', 46],
        ['hornBlow', 'Klaxonner', 47],
        ['vehicleFinder', 'Recherche véhicule', 48],
        ['sendPOI', 'Envoi POI', 49],
        ['hazardOn', 'Feux de détresse', 50],
        ['hazardOff', 'Stop feux de détresse', 51],
    ];
    foreach ($actions as $cmd) {
        $this->createCmd($cmd[0], $cmd[1], $cmd[2], TYPE_ACTION, SUBTYPE_OTHER);
    }

    // Statuts des actions effectuées sur le véhicule
    for ($i = 40; $i <= 51; $i++) {
        $actionName = array_column($actions, 0)[$i - 40];
        $this->createCmd($actionName . '_status', 'Statut ' . strtolower($actions[$i - 40][1]), $i + 10, TYPE_INFO, SUBTYPE_STRING);
    }

    // Informations supplémentaires comme la présence à domicile, la distance par rapport au domicile, etc.
    $this->createCmd('presence', 'Présence domicile', 62, TYPE_INFO, SUBTYPE_BINARY);
    $this->createCmd('distance', 'Distance domicile', 63, TYPE_INFO, SUBTYPE_NUMERIC);
    $this->createCmd('totalEnergyCharged', 'Charge électrique totale', 64, TYPE_INFO, SUBTYPE_NUMERIC);
    $this->createCmd('chargingSessions', 'Sessions de charge', 65, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd('services', 'Services', 66, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd('beRemainingRangeTotal', 'Km restant (global)', 67, TYPE_INFO, SUBTYPE_NUMERIC);
    $this->createCmd('moy_sem', 'Moyenne semaine', 68, TYPE_INFO, SUBTYPE_STRING);
    $this->createCmd('trajets', 'trajet 7 derniers jours', 69, TYPE_INFO, SUBTYPE_STRING);
}

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  
  // Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {
    	
		$this->emptyCacheWidget(); 		//vide le cache. Pratique pour le développement
		
		$panel = false;
		if ($_version == 'panel') {
			$panel = true;
			//$_version = 'dashboard';
		}

    $UtilTemplate = $this->getConfiguration("UtilTemplate", "1"); // Récupération du template choisi (par défaut : horizontal)
    if (($UtilTemplate == "0") && ($_version == 'dashboard')) {
      return parent::toHtml($_version);
    }else{
      $_version = 'dashboard';
    }
		
		/*if ($this->getConfiguration('widget_template') == 0) {
			return parent::toHtml($_version);
		}*/
			
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		
		$version = jeedom::versionAlias($_version);
		$replace['#version#'] = $_version;
		
		$replace['#vehicle_vin'.$this->getId().'#'] = $this->getConfiguration('vehicle_vin');
		$replace['#vehicle_brand'.$this->getId().'#'] = $this->getConfiguration('vehicle_brand');
		$replace['#vehicle_type'.$this->getId().'#'] = $this->getConfiguration('vehicle_type');
		$replace['#home_distance'.$this->getId().'#'] = $this->getConfiguration('home_distance');
		$replace['#panel_doors_windows_display'.$this->getId().'#'] = $this->getConfiguration('panel_doors_windows_display');
		$replace['#panel_color_icon_closed'.$this->getId().'#'] = $this->getConfiguration('panel_color_icon_closed');
		$replace['#fuel_value_unit'.$this->getId().'#'] = $this->getConfiguration('fuel_value_unit');
							
		// Traitement des commandes infos
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
			$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_visible#'] = $cmd->getIsVisible();
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			if ($cmd->getIsHistorized() == 1) { $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor'; }
		}

		// Traitement des commandes actions
		foreach ($this->getCmd('action') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_visible#'] = $cmd->getIsVisible();
			if ($cmd->getSubType() == 'select') {
				$listValue = "<option value>" . $cmd->getName() . "</option>";
				$listValueArray = explode(';', $cmd->getConfiguration('listValue'));
				foreach ($listValueArray as $value) {
					list($id, $name) = explode('|', $value);
					$listValue = $listValue . "<option value=" . $id . ">" . $name . "</option>";
				}
				$replace['#' . $cmd->getLogicalId() . '_listValue#'] = $listValue;
			}
		}
		
		//Traitement des paramètres optionnels
		/*if (!key_exists('#all_info_display#', $replace)) $replace['#all_info_display#'] = 'show';
		if (!key_exists('#doors_windows_display#', $replace)) $replace['#doors_windows_display#'] = 'text';
		if (!key_exists('#color_icon_closed#', $replace)) $replace['#color_icon_closed#'] = '';*/
		
		// On definit le template à appliquer par rapport à la version Jeedom utilisée
		if ($panel == true) { $template = 'myToyota_panel_flatdesign'; }
		elseif (version_compare(jeedom::version(), '4.0.0') >= 0) {
			$template = 'myToyota_dashboard_flatdesign';
			//if ($this->getConfiguration('widget_template') == 1) { $template = 'myToyota_dashboard_flatdesign'; }
			//if ($this->getConfiguration('widget_template') == 2) { $template = 'myToyota_dashboard_legacy'; }
		}
		$replace['#template#'] = $template;

    $filepath = 'plugins/'.__CLASS__.'/core/template/'.$version.'/'.$template.'.html';
    $content = getTemplate('core', $version, $template, 'myToyota');
    $content = translate::exec($content, $filepath);
    return $this->postToHtml($_version, template_replace($replace, $content));
	}
    

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */


  /*     * **********************Getteur Setteur*************************** */
	private function createCmd($commandName, $commandDescription, $order, $type, $subType, $historized = 0, $template = [])
	{	
		$cmd = $this->getCmd(null, $commandName);
        if (!is_object($cmd)) {
            $cmd = new myToyotaCmd();
            $cmd->setOrder($order);
			$cmd->setName(__($commandDescription, __FILE__));
			$cmd->setEqLogic_id($this->getId());
			$cmd->setLogicalId($commandName);
			$cmd->setType($type);
			$cmd->setSubType($subType);
      $cmd->setIsHistorized($historized);
			if (!empty($template)) { $cmd->setTemplate($template[0], $template[1]); }
			$cmd->save();
      if ($historized==0) {$hist = 'non';}else{$hist='oui';}
			log::add('myToyota', 'debug', __('Ajout de la commande', __FILE__) . ' ' . $cmd->getName() . ' (LogicalId : '.$cmd->getLogicalId().'), ' . __('historisé :', __FILE__) . ' ' . $hist);
        }
  }

  public static function synchro_post_update($eqlogic){
      $vin = $eqlogic->getConfiguration('vehicle_vin');
      myToyota::synchronize($vin);
}

	public static function synchronize($vin)
    {
      $eqLogic = self::getToyotaEqLogic($vin);
      
      log::add('myToyota', 'info', __('┌─Command execution : synchronize', __FILE__));
          
      log::add('myToyota', 'info', '| -------------------------------------------------------------');
      log::add('myToyota', 'info', __('| ---------------Démarrage synchro des données-----------------', __FILE__));

      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->getDevice();
      $devices = json_decode($result->body);
      //$vin = $eqLogic->getConfiguration('vehicle_vin');
      log::add('myToyota', 'debug', '| Return devices body :' . $result->body);
      log::add('myToyota', 'debug', __('| Retour nombre de véhicules :', __FILE__) . count($devices->payload) );

      $return['erreur'] = 'erreur';

      if ( count($devices->payload) == 0 )
      {
        log::add('myToyota', 'debug', '| Result getVehicles() : ' . __('pas de véhicule trouvé avec service myToyota activé', __FILE__));
        //log::add('myToyota', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─fin de la synchronisation : ['.$result->httpCode.']');
      }
      else
      {
        $vehicles = $devices->payload;
        foreach ($vehicles as $vehicle)
        {
          log::add('myToyota', 'debug', '| Result vin() : ' . $vehicle->vin);
          if ( $vehicle->vin == $vin )
          {
            log::add('myToyota', 'info', "| Result getVehicles() : " . __("ok c'est le VIN recherché", __FILE__));
            //if ( isset($vehicle->attributes->brand) ) { $eqLogic->checkAndUpdateCmd('brand', $vehicle->attributes->brand); } else { $eqLogic->checkAndUpdateCmd('brand', 'not available'); }
            if ( isset($vehicle->manufacturerCode) ) { 
              $eqLogic->checkAndUpdateCmd('brand', $vehicle->manufacturerCode);
              log::add('myToyota', 'info', '| Result ' . __('fabricant :', __FILE__) . ' ' . $vehicle->manufacturerCode);
            } else { 
              $eqLogic->checkAndUpdateCmd('brand', 'not available'); 
              log::add('myToyota', 'info', '| Result ' . __('fabricant : Inconnu', __FILE__));
            }
            $return['modelName'] = 'inconnu'; 
            if ( isset($vehicle->modelName) ) { 
              $eqLogic->checkAndUpdateCmd('model', $vehicle->modelName);
              $return['modelName'] = $vehicle->modelName;
              log::add('myToyota', 'info', __('| Résultat modèle :', __FILE__) . ' ' . $return['modelName']);
            } else { 
              $eqLogic->checkAndUpdateCmd('model', 'not available');
              log::add('myToyota', 'info', __('| Résultat modèle : inconnu', __FILE__));
            }
            $eqLogic->setConfiguration('vehicle_model', $return['modelName']);
            $eqLogic->save(true);
            if ( isset($vehicle->manufacturedDate) ) { 
              $return['modelYear'] = date("d-m-Y", strtotime($vehicle->manufacturedDate));
              $eqLogic->setConfiguration('vehicle_year', $return['modelYear']);
              $eqLogic->checkAndUpdateCmd('year', $return['modelYear']);
              $eqLogic->save(true);
              log::add('myToyota', 'info', __('| Résultat fabrication :', __FILE__) . ' ' . $return['modelYear']);
            } else { 
              $eqLogic->checkAndUpdateCmd('year', 'not available'); 
              log::add('myToyota', 'info', __('| Résultat fabrication : Inconnu', __FILE__));
            }
            //if ( isset($vehicle->attributes->driveTrain) ) { $eqLogic->checkAndUpdateCmd('type', $vehicle->attributes->driveTrain); } else { $eqLogic->checkAndUpdateCmd('type', 'not available'); }
            log::add('myToyota', 'debug', '| Result getDevice() : '.str_replace('\n','',json_encode($vehicle)));
            //log::add('myToyota', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of synchronisation : ['.$result->httpCode.']');
            if (isset($vehicle->extendedCapabilities)){
              $capabilities = json_encode($vehicle->extendedCapabilities);
              $return['capabilities'] = $capabilities;
              $eqLogic->setConfiguration('capabilities', $capabilities);
              $eqLogic->save(true);
              log::add('myToyota', 'info', '| Result extendedCapabilities : ' . $capabilities);
              if ($vehicle->extendedCapabilities->hybridPulse){
                if (isset($vehicle->evVehicle)){
                  if ($vehicle->evVehicle){
                    $return['driveTrain'] = 'Hybride Rechargeable';
                  } else {
                    $return['driveTrain'] = 'Hybride';
                  }
                } else{
                  $return['driveTrain'] = 'Hybride';
                }
              } else if ($vehicle->extendedCapabilities->electricPulse){
                $return['driveTrain'] = 'Electrique';
              } else if ($vehicle->extendedCapabilities->drivePulse){
                $return['driveTrain'] = 'Thermique';
              } else {
                $return['driveTrain'] = 'Inconnu';
              }
              $eqLogic->setConfiguration('vehicle_type', $return['driveTrain']);
              $eqLogic->save(true);
            }
            log::add('myToyota', 'info', __('| Résultat type de motorisation :', __FILE__) . ' ' . $return['driveTrain']);
            if (isset($vehicle->fuelType)){
              if ($vehicle->fuelType == 'B'){
                $eqLogic->checkAndUpdateCmd('carburant', 'Essence');
              } else {
                $eqLogic->checkAndUpdateCmd('carburant', 'Diesel');
              }
              log::add('myToyota', 'info', __('| Résultat carburant type :', __FILE__) . ' ' . $vehicle->fuelType);
            }
            if ( isset($vehicle->stockPicReference)) {
              $filename = dirname(__FILE__).'/../../data/'.$vin.'.png';
              $img = $vehicle->stockPicReference;
              log::add('myToyota', 'debug', __('| Résultat image :', __FILE__) . ' ' . $img);
              file_put_contents($filename,file_get_contents($img));
            }
            $return['erreur'] = 'ok';
            $return['vin'] = $vin;
          } else {
            log::add('myToyota', 'info', __('| Résultat getVehicles() : pas le bon VIN', __FILE__));
          }
        }
      }
      log::add('myToyota', 'info', __("| Fin de la synchronisation", __FILE__));

      return $return;
  
	  }
	
    public static function all_data($vin)
    {
      
      $fichierLog = 'myToyota_datas';
      log::add($fichierLog, 'info', __('┌─Commande exécution : récupération de toutes les données', __FILE__));
          
      log::add($fichierLog, 'info', __('| Démarrage récupération des données', __FILE__));
      $eqLogic = self::getToyotaEqLogic($vin);
      
      $myConnection = $eqLogic->getConnection($fichierLog);
      $result = $myConnection->getDevice($fichierLog);
      $devices = json_decode($result->body);
      log::add($fichierLog, 'info', '| Return getDevices :' . $result->body );
      log::add($fichierLog, 'info', __('| Résultat => nombre de véhicules :', __FILE__) . ' ' . count($devices->payload) );

      if ( count($devices->payload) == 0 ){
        log::add($fichierLog, 'info', '| Result getVehicles() : ' . __('pas de véhicule trouvé avec service myToyota activé', __FILE__));
        //log::add('myToyota', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─fin de la synchronisation : ['.$result->httpCode.']');
      } else {
        $result = $myConnection->getLocationEndPoint($fichierLog);
        log::add($fichierLog, 'info', __('| Retour localisation :', __FILE__) . ' ' . $result->body);

        $result = $myConnection->getRemoteStatusEndPoint($fichierLog); //status des équipements
        log::add($fichierLog, 'info', __('| Retour', __FILE__) . 'remote status :' . $result->body);

        $result = $myConnection->getTelemetryEndPoint($fichierLog); //dernière localisation
        log::add($fichierLog, 'info', __('| Retour télémétrie :', __FILE__) . ' ' . $result->body);

        $result = $myConnection->getRemoteClimateStatus($fichierLog); //dernière localisation
        log::add($fichierLog, 'info', __('| Retour climatisation :', __FILE__) . ' ' . $result->body);

        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime('-7 days', strtotime($to)));
        $route = false;
        $summary = true;
        $limit = 1000; // 1000 max si $route = false et 50 max si $rpute = true
        $offset = 0;
  
        $result = $myConnection->getTripsEndpoint($from, $to, $route, $summary, $limit, $offset, $fichierLog); //pour récupérer les 7 derniers jours
        log::add($fichierLog, 'info', __('| Retour circuits :', __FILE__) . ' ' . $result->body);

        $result = $myConnection->historiqueNotification($fichierLog);
        log::add($fichierLog, 'info', __('| Retour historique des notifications :', __FILE__) . ' ' . $result->body);

        $result = $myConnection->remoteElectric($fichierLog);
        log::add($fichierLog, 'info', __('| Retour remote electric :', __FILE__) . ' ' . $result->body);

        $result = $myConnection->remoteClimateSettings($fichierLog);
        log::add($fichierLog, 'info', '| Retour remote climate setting :' . ' ' . $result->body);

        $result = $myConnection->remoteACReservation($fichierLog);
        log::add($fichierLog, 'info', '| Retour remote AC reservation :' . ' ' . $result->body);

        $result = $myConnection->statusHealth($fichierLog); //dernière localisation
        log::add($fichierLog, 'info', __('| Retour santé véhicule :', __FILE__) . ' ' . $result->body);

        $result = $myConnection->historiqueService($fichierLog);
        log::add($fichierLog, 'info', __('| Retour historique services :', __FILE__) . ' ' . $result->body);

      }



      log::add($fichierLog, 'info', __('| recup terminée', __FILE__));
	  }
	
    // fonction pour test de l'état des éléments portes, fenêtres, coffre, ... ouvert, fermé, verrouillé, ...
    public static function checkStatus($values, &$status, &$closed, &$open, &$locked = 0, &$unlocked = 0) {
      foreach ($values as $value) {
        switch ($value->value){
          case 'carstatus_closed':
            $closed++;
            $status = 'CLOSED';
            break;
          case 'carstatus_open':
            $open++;
            $status = 'OPEN';
            break;
          case 'carstatus_unlocked':
            $unlocked++;
            break;
          case 'carstatus_locked':
            $locked++;
            break;
        }
      }
    }


    public static function interromyToyota($eqLogic)
    {
      $myConnection = $eqLogic->getConnection();
      $idvehicule = $eqLogic->getId();
      $nomvehicule = $eqLogic->getName();
      $capabilities = json_decode($eqLogic->getConfiguration('capabilities'));
      $vehicle_type = $eqLogic->getConfiguration('vehicle_type');
      
      if ($idvehicule!='Aucun' && $idvehicule!=''){
        log::add('myToyota', 'info', '| ----------------------------------------------------------------------------------------------');
        log::add('myToyota', 'info', __('| Démarrage Interrogation serveur myToyota pour le véhicule', __FILE__) . ' ' . strval($nomvehicule) . __(' avec ID', __FILE__) . ' ' . $idvehicule);
          
        //dernière localisation
        $result = $myConnection->getLocationEndPoint();
        $location = json_decode($result->body);
        log::add('myToyota', 'debug', __('| Retour localisation :', __FILE__) . ' ' . $result->body);
        if ( isset($location->payload->vehicleLocation) ) { 
          $eqLogic->checkAndUpdateCmd(CMD_LAST_UPDATE,date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
          $eqLogic->checkAndUpdateCmd(CMD_GPS_COORDINATES, $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude);
          myToyota::chercheLycos($eqLogic,$location->payload->vehicleLocation->latitude, $location->payload->vehicleLocation->longitude);
          log::add('myToyota', 'info', __('| Dernière localisation connue:', __FILE__) . ' "' . 
                                            $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude . '" le ' . 
                                            date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
        }

        // état des fenetres, portes, ...
        $result = $myConnection->getRemoteStatusEndPoint(); //status des équipements
        $remoteStatus = json_decode($result->body);
        log::add('myToyota', 'debug', '| Retour remote status : ' . $result->body);
        $eqLogic->checkAndUpdateCmd('doorLockState', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('allDoorsState', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('allWindowsState', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('doorDriverFront', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('doorDriverRear', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('doorPassengerFront', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('doorPassengerRear', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('windowDriverFront', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('windowDriverRear', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('windowPassengerFront', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('windowPassengerRear', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('trunk_state', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('hood_state', 'UNKNOWN');
        $eqLogic->checkAndUpdateCmd('moonroof_state', 'UNKNOWN');
        $windows = 0;
        $windowOpen = 0;
        $windowClosed = 0;
        $doors = 0;
        $doorOpen = 0;
        $doorClosed = 0;
        $doorUnlocked = 0;
        
        //
        if (isset($remoteStatus->payload->vehicleStatus)) {
          foreach ($remoteStatus->payload->vehicleStatus as $category) {
            foreach ($category->sections as $section) {
                switch ($section->section) {
                    case 'carstatus_item_driver_door':
                      $element = 'doorDriverFront';
                      $doors++;
                      myToyota::checkStatus($section->values, $status, $doorClosed, $doorOpen, $doorLocked, $doorUnlocked);
                      break;
                    case 'carstatus_item_driver_rear_door':
                      $element = 'doorDriverRear';
                      $doors++;
                      myToyota::checkStatus($section->values, $status, $doorClosed, $doorOpen, $doorLocked, $doorUnlocked);
                      break;
                    case 'carstatus_item_passenger_door':
                      $element = 'doorPassengerFront';
                      $doors++;
                      myToyota::checkStatus($section->values, $status, $doorClosed, $doorOpen, $doorLocked, $doorUnlocked);
                      break;
                    case 'carstatus_item_passenger_rear_door':
                      $element = 'doorPassengerRear';
                      $doors++;
                      myToyota::checkStatus($section->values, $status, $doorClosed, $doorOpen, $doorLocked, $doorUnlocked);
                      break;
                    case 'hood':
                      $element = 'hood_state';
                      $doors++;
                      myToyota::checkStatus($section->values, $status, $doorClosed, $doorOpen, $doorLocked, $doorUnlocked);
                      break;
                    case 'carstatus_item_rear_hatch':
                      $element = 'trunk_state';
                      $doors++;
                      myToyota::checkStatus($section->values, $status, $doorClosed, $doorOpen, $doorLocked, $doorUnlocked);
                      break;
                    case 'carstatus_item_driver_window':
                      $element = 'windowDriverFront';
                      $windows++;
                      myToyota::checkStatus($section->values, $status, $windowClosed, $windowOpen); // Assuming windows can't be locked
                      break;
                    case 'carstatus_item_driver_rear_window':
                      $element = 'windowDriverRear';
                      $windows++;
                      myToyota::checkStatus($section->values, $status, $windowClosed, $windowOpen); // Assuming windows can't be locked
                      break;
                    case 'carstatus_item_passenger_window':
                      $element = 'windowPassengerFront';
                      $windows++;
                      myToyota::checkStatus($section->values, $status, $windowClosed, $windowOpen); // Assuming windows can't be locked
                      break;
                    case 'carstatus_item_passenger_rear_window':
                      $element = 'windowpassengerRear';
                      $windows++;
                      myToyota::checkStatus($section->values, $status, $windowClosed, $windowOpen); // Assuming windows can't be locked
                      break;
                    case 'moonroof':
                      $element = 'moonroof_state';
                      $windows++;
                      myToyota::checkStatus($section->values, $status, $windowClosed, $windowOpen); // Assuming windows can't be locked
                      break;
                  }
                  $eqLogic->checkAndUpdateCmd($element, $status);
                  log::add('myToyota', 'info', '| Return élement: ' . $element . ' Status : ' . $status);
                }
          }
        } else {
          log::add('myToyota', 'info', __('| Status du véhicules (portes, fenêtres, ...) non disponible.', __FILE__));
        }          

        if ($doors == 0){
          $eqLogic->checkAndUpdateCmd('doorLockState', 'UNKNOWN');
          log::add('myToyota', 'info', '| Return élement: doorLockState Status : UNKNOWN');
          $eqLogic->checkAndUpdateCmd('allDoorsState', 'UNKNOWN');
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : UNKNOWN ');
        } else if ($doorUnlocked == 0){
          $eqLogic->checkAndUpdateCmd('doorLockState', 'LOCKED');
          log::add('myToyota', 'info', '| Return élement: doorLockState Status : LOCKED ' . $doorLocked . ' / ' . $doors);
        } else {
          $eqLogic->checkAndUpdateCmd('doorLockState', 'UNLOCKED');
          log::add('myToyota', 'info', '| Return élement: doorLockState Status : UNLOCKED ' . $doorUnlocked . ' non verrouillée(s) / ' . $doors . ' portes verrouillables');
        }

        if (($doors != 0) && ($doorOpen == 0)){
          $eqLogic->checkAndUpdateCmd('allDoorsState', 'CLOSED');
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : CLOSED ' . $doors . ' / ' . $doors);
        } else if ($doors !=0) {
          $eqLogic->checkAndUpdateCmd('allDoorsState', 'OPEN');
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : OPEN ' . $doorOpen . ' / ' . $doors);
        } else {
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : UNKNOW');
        }

        if ($windows == 0){
          $eqLogic->checkAndUpdateCmd('allWindowsState', 'UNKNOWN');
          log::add('myToyota', 'info', '| Return élement: allWindowsState Status : UNKNOWN ');
        } else if ($windowOpen == 0){
          $eqLogic->checkAndUpdateCmd('allWindowsState', 'CLOSED');
          log::add('myToyota', 'info', '| Return élement: allWindowsState Status : CLOSED ' . $windowClosed . ' / ' . $windows);
        } else {
          $eqLogic->checkAndUpdateCmd('allWindowsState', 'OPEN');
          log::add('myToyota', 'info', '| Return élement: allWindowsState Status : OPEN ' . $windowOpen . ' / ' . $windows);
        }
  
        // télémétrie
        $result = $myConnection->getTelemetryEndPoint(); //dernière localisation
        $body = json_decode($result->body);
        log::add('myToyota', 'debug', __('| Retour télémétrie :', __FILE__) . ' ' . $result->body);

        if ($body->status == 'SUCCESS'){
          $telemetrie = $body->payload;
          // km totaux
          if ( isset($telemetrie->odometer) ){
            $eqLogic->checkAndUpdateCmd('mileage', $telemetrie->odometer->value);
            log::add('myToyota', 'info', __('| Retour élement: km totaux :', __FILE__) . ' ' . $telemetrie->odometer->value . ' km');
          } else {
            $eqLogic->checkAndUpdateCmd('mileage', '---');
            log::add('myToyota', 'info', __('| Retour élement: km totaux inconnu', __FILE__));
          }

          //paramètres communs
          if ( isset($telemetrie->fuelLevel) ){
            $eqLogic->checkAndUpdateCmd('remaining_fuel', $telemetrie->fuelLevel);
            log::add('myToyota', 'info', __('| Retour élement: niveau réservoir :', __FILE__). ' ' . $telemetrie->fuelLevel . '%');
          } else {
            $eqLogic->checkAndUpdateCmd('remaining_fuel', '---');
            log::add('myToyota', 'info', __('| Retour élement: niveau réservoir inconnu', __FILE__));
          }

          if ( isset($telemetrie->distanceToEmpty) ){
            $eqLogic->checkAndUpdateCmd('beRemainingRangeTotal', $telemetrie->distanceToEmpty->value);
            log::add('myToyota', 'info', __('| Retour élement: distance possible (ev + essence) :', __FILE__) . ' ' . $telemetrie->distanceToEmpty->value . ' km');
          } else {
            $eqLogic->checkAndUpdateCmd('beRemainingRangeTotal', '---');
            log::add('myToyota', 'info', __('| Retour élement: distance possible ev + essence inconnu', __FILE__));
          }
          
          // paramétres hybrides
          if ($vehicle_type == 'Hybride' || $vehicle_type == 'Thermique'){
            $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', $telemetrie->distanceToEmpty->value);
            log::add('myToyota', 'info', __('| Retour élement: distance possible thermique :', __FILE__). ' ' . $telemetrie->distanceToEmpty->value . ' km');
         } else {
            $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', '---');
            log::add('myToyota', 'info', __('| Retour élement: distance possible essence inconnue', __FILE__));
          }
 
          // paramètres électriques et PEHV
          if ($vehicle_type == 'Hybride Rechargeable' || $vehicle_type == 'Electrique'){
            $result_elec = $myConnection->remoteElectric();
            $body_elec = json_decode($result_elec->body);
            log::add('myToyota', 'debug', '| Retour télémétrie électrique : ' . $result_elec->body);
    
            if ($body_elec->status->detailedDescription == 'Request Completed Successfully'){
              $telemetrie_elec = $body->payload;
              if ( isset($telemetrie_elec->fuelLevel) ){
                $eqLogic->checkAndUpdateCmd('remaining_fuel', $telemetrie_elec->fuelLevel);
                log::add('myToyota', 'info', __('| Retour élement: niveau réservoir :', __FILE__) . ' ' . $telemetrie_elec->fuelLevel . '%');
              } else {
                $eqLogic->checkAndUpdateCmd('remaining_fuel', '---');
                log::add('myToyota', 'info', __('| Retour élement: niveau réservoir inconnu', __FILE__));
              }

              if ( isset($telemetrie_elec->fuelRange) ){
                $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', $telemetrie_elec->fuelRange->value);
                log::add('myToyota', 'info', __('| Retour élement: distance avant réservoir vide :', __FILE__) . ' ' . $telemetrie_elec->fuelRange->value . ' km');
              } else {
                $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', '---');
                log::add('myToyota', 'info', __('| Retour élement: distance avant réservoir vide inconnu', __FILE__));
              }

              if ( isset($telemetrie_elec->batteryLevel) ){
                $eqLogic->checkAndUpdateCmd('chargingLevelHv', $telemetrie_elec->batteryLevel);
                log::add('myToyota', 'info', __('| Retour élement: niveau batterie :', __FILE__) . ' ' . $telemetrie_elec->batteryLevel . '%');
              } else {
                $eqLogic->checkAndUpdateCmd('chargingLevelHv', '---');
                log::add('myToyota', 'info', __('| Retour élement: niveau batterie inconnu', __FILE__));
              }

              if ( isset($telemetrie_elec->evRangeWithAc) ){
                $eqLogic->checkAndUpdateCmd('beRemainingRangeElectric', $telemetrie_elec->evRangeWithAc->value);
                log::add('myToyota', 'info', __('| Retour élement: distance avant batterie vide :', __FILE__) . ' ' . $telemetrie_elec->evRangeWithAc->value . ' km');
              } else {
                $eqLogic->checkAndUpdateCmd('beRemainingRangeElectric', '---');
                log::add('myToyota', 'info', __('| Retour élement: distance avant batterie vide inconnu', __FILE__));
              }

              if ( isset($telemetrie_elec->chargingStatus)){
                $eqLogic->checkAndUpdateCmd('chargingStatus', $telemetrie_elec->chargingStatus);
                log::add('myToyota', 'info', __('| Retour élement: status de la charge', __FILE__) . $telemetrie_elec->chargingStatus);
              } else {
                $eqLogic->checkAndUpdateCmd('chargingStatus', 'UNKNOWN');
                log::add('myToyota', 'info', __('| Retour élement: status de la charge inconnue', __FILE__));
              }

            }
          }


          // Climatisation ???
          $result = $myConnection->getRemoteClimateStatus(); //dernière localisation
          $climateStatus = json_decode($result->body);
    
          log::add('myToyota', 'debug', __('| Retour climatisation :', __FILE__) . ' ' . $result->body);

          // trips
          $to = date('Y-m-d');
          $from = date('Y-m-d', strtotime('-7 days', strtotime($to)));
          $route = false;
          $summary = true;
          if ($route){
            $limit = 50; // 1000 max si $route = false et 50 max si $rpute = true
          } else {
            $limit = 1000;
          }
          $offset = 0;
    
          $result = $myConnection->getTripsEndpoint($from, $to, $route, $summary, $limit, $offset); //pour récupérer les 7 derniers jours
          $tripsEndpoint = json_decode($result->body);
    
          log::add('myToyota', 'debug', __('| retour circuits', __FILE__) . $result->body);
          
          // moyennes sur 7 jours
          $fuelConsumption = 0;
          $length = 0;
          $evDistance = 0;
          $evTime = 0;
          $duration = 0;
          $summarys = $tripsEndpoint->payload->summary;
          $metatData = $tripsEndpoint->payload->_metadata;
          foreach ($summarys as $summary){
            $fuelConsumption += $summary->summary->fuelConsumption;
            $length += $summary->summary->length;
            $evDistance += $summary->hdc->evDistance;
            $evTime += $summary->hdc->evTime;
            $duration += $summary->summary->duration;
          }
          $dureeTot = myToyota::convertSecondes($duration);
          $dureeEv = myToyota::convertSecondes($evTime);
          $consoMoy = myToyota::consoMoyenne($fuelConsumption, $length);
          $averageSpeed = myToyota::averageSpeed($length, $duration);

          $summarySem = '{"conso_moy":' . $consoMoy . ', "vit_moy":' . $averageSpeed . 
            ', "distance_tot":' . strval($length / 1000) . ',"duree_tot": "' . $dureeTot . 
            '", "distance_ev":' . strval($evDistance / 1000) . ',"duree_ev":"' . $dureeEv . 
            '","conso_essence":' . strval($fuelConsumption / 1000) . ',"nb_trajets":' . $metatData->pagination->totalCount . '}';
            log::add('myToyota', 'info', __('| retour trips moyenne sur 7 jours:', __FILE__) . ' ' . $summarySem);          
            $eqLogic->checkAndUpdateCmd('moy_sem', $summarySem);

          // les trajets des 7 derniers jours
          $trips = $tripsEndpoint->payload->trips;
          $i = 1;
          foreach ($trips as $trip){
            $trajet['trajet' . strval($i)] = array('debut_trajet' => date("d-m-Y G:i:s", strtotime($trip->summary->startTs)) ,
              'conso_moy' => myToyota::consoMoyenne($trip->summary->fuelConsumption, $trip->summary->length) , 'vit_moy' => $trip->summary->averageSpeed , 
              'distance_tot' => strval($trip->summary->length / 1000) , 'duree_tot' => myToyota::convertSecondes($trip->summary->duration) ,
              'distance_ev' => strval($trip->hdc->evDistance / 1000) , 'duree_ev' => myToyota::convertSecondes($trip->hdc->evTime) ,
              'conso_essence' => strval($trip->summary->fuelConsumption / 1000));
              log::add('myToyota', 'info', __('| retour résumé trajet n°', __FILE__) . ' ' . strval($i) . ' : ' . json_encode($trajet['trajet' . strval($i++)]));
          }
          $tripsJson = json_encode($trajet);
          $eqLogic->checkAndUpdateCmd('trajets', $tripsJson);

        }

        // Santé du véhicule
        $result = $myConnection->statusHealth(); //dernière localisation
        $body = json_decode($result->body);
        log::add('myToyota', 'debug', __('| Retour santé véhicule :', __FILE__) . ' ' . $result->body);
        if ($body->status == 'SUCCESS'){
          $sante = $body->payload;
          if ($sante->quantityOfEngOilIcon[0] == ''){
          $table_messages['checkControlMessages'] = array( "type" => "ENGINE_OIL ", "date" => '', "mileage" => '', "state" => '', "title" => "Huile moteur", "description" => "", "severity" => 'OK' );
  
          $eqLogic->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
          log::add('myToyota', 'info', __('| retour message santé  :', __FILE__) . ' ' . json_encode($table_messages['checkControlMessages']));
          }
        }

        // services et entretien
        $result = $myConnection->historiqueService();
        $body = json_decode($result->body);
        log::add('myToyota', 'debug', __('| Retour services et entretien :', __FILE__) . ' ' . $result->body);
        if ($body->status->messages[0]->description == 'Request Processed successfully'){
          $histoservice = $body->payload->serviceHistories;
          $i=1;
          foreach ($histoservice as $service){
            $services['service' . $i] = array('date' => date('d-m-Y', strtotime($service->serviceDate)), 'enregistrement_consommateur' => $service->customerCreatedRecord, 
              'compteur' => $service->mileage . ' ' . $service->unit, 'notes' => $service->notes,
              'operations'=> $service->operationsPerformed, 'ro_number' => $service->roNumber,
              'categorie' => $service->serviceCategory, 'garage' => $service->serviceProvider,
              'concessionaire' => $service->servicingDealer);
              log::add('myToyota', 'info', __('| retour résumé services n°', __FILE__) . ' ' . strval($i) . ' : ' . json_encode($services['service' . strval($i++)]));
          }
          $servicesJson = json_encode($services);
          $eqLogic->checkAndUpdateCmd('services', $servicesJson);
        }

        

        //fin du traitement
        log::add('myToyota', 'info', __('| fin du traitement pour le véhicule', __FILE__) . ' ' . strval($nomvehicule) . ' avec ID ' . $idvehicule);
        log::add('myToyota', 'info', '| ------------------------------------------------------------------------');

      } else {
      log::add('myToyota', 'info', '| ------------------------------------------------------------------------');
      log::add('myToyota', 'info', __('| Démarrage Interrogation serveur myToyota impossible, ID inconnu ou nul', __FILE__));
      }

    }
/*
                                services[str('service' + str(i))] = {'date':str((service.service_date).strftime('%d-%m-%Y')), 'enregistrement_consommateur':service.customer_created_record,
                               'compteur': str(service.odometer) + ' ' + service._distance_unit, 'notes': service.notes,
                               'operations': service.operations_performed, 'ro_number': service.ro_number,
                               'categorie': service.service_category, 'garage': service.service_provider,
                               'concessionaire': service.servicing_dealer}


                               for trajets in await car.get_trips(date.today() - timedelta(days=7), date.today(), full_route=False):
                mestrajets[str('trajet' + str(i))] = {'debut_trajet': str(utc_to_local(trajets.start_time).strftime('%d-%m-%Y %H:%M:%S')),
                    'conso_moy':trajets.average_fuel_consumed,
                    'distance_tot':trajets.distance,'duree_tot':str(trajets.duration),
                    'distance_ev':trajets.ev_distance,'duree_ev':str(trajets.ev_duration),
                    'conso_essence':trajets.fuel_consumed}
                i += 1
            vehicule['trajets'] = json.dumps(mestrajets)

    */
    public static function convertSecondes($secondes)
    {
      $result = strval(intval($secondes / 3600)). ':' . strval(intval(($secondes % 3600) / 60)) . ':' . strval(intval((($secondes % 3600) % 60)));
      return $result;
    }

    public static function consoMoyenne($conso, $metres)
    {
      $result = round($conso / $metres * 100, 2);
      return $result;
    }

    public static function averageSpeed($lenght, $dureeTot)
    {
      $result = round(($lenght/1000) / ($dureeTot / 3600), 2);
      return $result;
    }

    public static function getToyotaEqLogic($vehicle_vin)
    {
      foreach ( eqLogic::byTypeAndSearhConfiguration('myToyota', 'vehicle_vin') as $myToyota ) {
        if ( $myToyota->getConfiguration('vehicle_vin') == $vehicle_vin )   {
          $eqLogic = $myToyota;
          break;
        }
      }
      return $eqLogic;
    }
    
    public function getDistanceLocation($lat1, $lng1)
    {
      if ( $this->getConfiguration("option_localisation") == "jeedom" ) {
        $lat2 = config::byKey('info::latitude','core','0');
        $lng2 = config::byKey('info::longitude','core','0');
      }
      else if ( $this->getConfiguration("option_localisation") == "manual" || $this->getConfiguration("option_localisation") == "vehicle") {
        $lat2 = $this->getConfiguration("home_lat");
        $lng2 = $this->getConfiguration("home_long");
      }	
      else {
        $lat2 = 0;
        $lng2 = 0;
      }
      
      $earth_radius = 6371; // Terre = sphère de 6371km de rayon
      $rla1 = deg2rad( floatval($lat1) );
      $rlo1 = deg2rad( floatval($lng1) );
      $rla2 = deg2rad( floatval($lat2) );
      $rlo2 = deg2rad( floatval($lng2) );
      $dlo = ($rlo2 - $rlo1) / 2;
      $dla = ($rla2 - $rla1) / 2;
      $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
      $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
      return round(($earth_radius * $d * 1000), 1); //retour en m
    }

    public static function getDistanceLocation2($eqLogic, $lat1, $lng1)
    {
      if ( $eqLogic->getConfiguration("option_localisation") == "jeedom" ) {
        $lat2 = config::byKey('info::latitude','core','0');
        $lng2 = config::byKey('info::longitude','core','0');
      }
      else if ( $eqLogic->getConfiguration("option_localisation") == "manual" || $eqLogic->getConfiguration("option_localisation") == "vehicle") {
        $lat2 = $eqLogic->getConfiguration("home_lat");
        $lng2 = $eqLogic->getConfiguration("home_long");
      }	
      else {
        $lat2 = 0;
        $lng2 = 0;
      }
      $nomvehicule = $eqLogic->getName();
      log::add('myToyota', 'info', __('| dernière position de', __FILE__) . ' ' . $nomvehicule . ': Lat et long ' . $lat2 . ' ' . $lng2);
      
      $earth_radius = 6371; // Terre = sphère de 6371km de rayon
      $rla1 = deg2rad( floatval($lat1) );
      $rlo1 = deg2rad( floatval($lng1) );
      $rla2 = deg2rad( floatval($lat2) );
      $rlo2 = deg2rad( floatval($lng2) );
      $dlo = ($rlo2 - $rlo1) / 2;
      $dla = ($rla2 - $rla1) / 2;
      $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
      $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
      return round(($earth_radius * $d * 1000), 1); //retour en m
    }

    public static function chercheLycos($eqLogic, $latitude, $longitude){
      //$eqLogic = myToyota::getToyotaEqLogic($vin);
      $nomvehicule = $eqLogic->getName();
      $distance = myToyota::getDistanceLocation2($eqLogic, $latitude, $longitude); //en metres
      log::add('myToyota', 'info', '| ' . $nomvehicule . __(': distance avec le domicile', __FILE__) . ' ' . $distance . __(' mètre(s)', __FILE__));
			$eqLogic->checkAndUpdateCmd('distance', $distance);
			if ( $distance <= $eqLogic->getConfiguration("home_distance") ) { $eqLogic->checkAndUpdateCmd('presence', 1); }
			else { $eqLogic->checkAndUpdateCmd('presence', 0); }
    }
    
    public static function getGPSCoordinates($vin)
    {
      $eqLogic = self::getToyotaEqLogic($vin);
      $cmd = $eqLogic->getCmd(null, CMD_GPS_COORDINATES);
      
      if ( is_object($cmd) )  {
        $coordinates = explode(",", $cmd->execCmd());
        $gps = array( "latitude" => $coordinates[0], "longitude" => $coordinates[1] );
      }
      else  {
        $gps = array( "latitude" => '0.000000', "longitude" => '0.000000' );
      }
      
      log::add('myToyota', 'debug', '| Result getGPSCoordinates() : '.json_encode($gps));
      return $gps;
    }

    public function vehicleFinder($vin)
    {
      $eqLogic = self::getToyotaEqLogic($vin);
      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->getLocationEndPoint();
      $location = json_decode($result->body);
      log::add('myToyota', 'debug', __('| Retour recherche véhicule :', __FILE__) . ' ' . $result->body);
      if ( isset($location->payload->vehicleLocation) ) { 
        $gps_coordinates = $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude;
        $eqLogic->checkAndUpdateCmd(CMD_LAST_UPDATE,date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
        $eqLogic->checkAndUpdateCmd(CMD_GPS_COORDINATES, $gps_coordinates);
        log::add('myToyota', 'info', __('| Dernière localisation connue: "', __FILE__) . 
          $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude . '" le ' . 
          date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
          return $gps_coordinates;
      } else {
        log::add('myToyota', 'error', __('le serveur ne répond pas', __FILE__));
        return false;
      }
    }
  
    public function getConnection($fichierLog='myToyota')
    {
        $vin = $this->getConfiguration("vehicle_vin");
        $username = $this->getConfiguration("username");
        $password = $this->getConfiguration("password");
        $brand = $this->getConfiguration("vehicle_brand");
        switch ($brand) {
          case 'T':
              $constructeur = 'Toyota';
              break;
          case 'L':
              $constructeur = 'Lexus';
              break;
          default:
              $constructeur = 'Inconnu';
              log::add('myToyota', 'warning', __('| Marque inconnue :', __FILE__) . ' ' . $brand);
              return null;
              break;
        }
        log::add($fichierLog, 'debug', '| Result user : '. substr($username,0,3) . '***' . substr($username,-3,3) . ' ; password : '. substr($password,0,2) . '***' . substr($password,-2,2) . ' ; vin : '. substr($vin,0,3) . '***' . substr($vin,-3,3) . ' ; constructeur : ' . $constructeur);
        if (empty($vin) || empty($username) || empty($password) || empty($brand)) {
          log::add($fichierLog, 'error', __('| Configuration manquante ou invalide pour la connexion.', __FILE__));
          return null; // ou throw new Exception("Configuration manquante");
        }
        $myConnection = new MyToyota_API($username, $password, $vin, $brand, $fichierLog);
        return $myConnection;
  	}

    public function commandes($eqLogic, $commande, $fichierLog='myToyota')
    {

      $myConnection = $eqLogic->getConnection($fichierLog);
      $result = $myConnection->remoteCommande($commande, $fichierLog); //commande envoyées vers véhicule
      $resultCmde = json_decode($result->body);

      log::add($fichierLog, 'debug', '| retour ' . $result->body . __(' à la commande :', __FILE__) . ' ' . $commande);

    }

    public function commande_clim($eqLogic, $commande, $fichierLog='myToyota')
    {

      $myConnection = $eqLogic->getConnection($fichierLog);
      $result = $myConnection->remoteClimate($commande, $fichierLog); //commande envoyées vers véhicule
      $resultCmde = json_decode($result->body);

      log::add($fichierLog, 'debug', '| retour ' . $result->body . __(' à la commande :', __FILE__). ' ' . $commande);

    }

// zone de tests ********************************************************************************************

    public function update_datas($eqLogic, $commande , $fichierLog='myToyota') // fonction de test pour développement
    {

      $myConnection = $eqLogic->getConnection($fichierLog);
      $result = $myConnection->testPoubelle_post($commande, $fichierLog);
      $tripsEndpoint = json_decode($result->body);

      log::add($fichierLog, 'debug', '| retour ' . $result->body);

    }

// fin zone de tests *****************************************************************************************

/*
    $this->createCmd('doorLockState', 'Verrouillage', 6, 'info', 'string');
    $this->createCmd('allDoorsState', 'Toutes les portes', 7, 'info', 'string');
    $this->createCmd('allWindowsState', 'Toutes les fenêtres', 8, 'info', 'string');
    $this->createCmd('doorDriverFront', 'Porte Conducteur Avant', 9, 'info', 'string'); //car.lock_status.doors.driver_seat.closed
    $this->createCmd('doorDriverRear', 'Porte Conducteur Arrière', 10, 'info', 'string');
    $this->createCmd('doorPassengerFront', 'Porte Passager Avant', 11, 'info', 'string');
    $this->createCmd('doorPassengerRear', 'Porte Passager Arrière', 12, 'info', 'string');
    $this->createCmd('windowDriverFront', 'Fenêtre Conducteur Avant', 13, 'info', 'string');
    $this->createCmd('windowDriverRear', 'Fenêtre Conducteur Arrière', 14, 'info', 'string');
    $this->createCmd('windowPassengerFront', 'Fenêtre Passager Avant', 15, 'info', 'string');
    $this->createCmd('windowPassengerRear', 'Fenêtre Passager Arrière', 16, 'info', 'string');
    $this->createCmd('trunk_state', 'Coffre', 17, 'info', 'string');
    $this->createCmd('hood_state', 'Capot Moteur', 18, 'info', 'string');
    $this->createCmd('moonroof_state', 'Toit ouvrant', 19, 'info', 'string');

*/
}

class myToyotaCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
    
		$eqLogic = $this->getEqLogic(); 										// On récupère l'éqlogic de la commande $this
		$logical = $this->getLogicalId();
		log::add('myToyota', 'info', '┌─Command execution : '.$logical);
		
		try {
            switch ($logical) {
                case 'refresh':
                    myToyota::interromyToyota($eqLogic);
					          break;
                case 'hornBlow':
                    myToyota::commandes($eqLogic, 'buzzer-warning');
                    //$eqLogic->doHornBlow();
                    break;
                case 'hazardOn':
                    myToyota::commandes($eqLogic, 'hazard-on');
                    //$eqLogic->doLightFlash();
                    break;
                case 'hazardOff':
                    myToyota::commandes($eqLogic, 'hazard-off');
                    //$eqLogic->doLightFlash();
                    break;
                case 'lightFlash': // ne sert pas pour l'instant
                    //myToyota::commandes($eqLogic, 'hazard-off');
                    //$eqLogic->doLightFlash();
                    break;
                case 'doorLock': 
                    myToyota::commandes($eqLogic, 'door-lock');
                    //$eqLogic->doDoorLock();
                    break;
                case 'doorUnlock':
                    myToyota::commandes($eqLogic, 'door-unlock');
                    //$eqLogic->doDoorUnlock();
                    break;
                case 'climateNow':
                    myToyota::commande_clim($eqLogic, 'engine-start');
                    //$eqLogic->doClimateNow();
                    break;
				        case 'stopClimateNow':
                  myToyota::commande_clim($eqLogic, 'engine-stop');
                      //$eqLogic->stopClimateNow();
                    break;
				        case 'chargeNow': // provisoirement bouton qui me sert pour tester les fonctions (faire apparaitre le bouton dans le panel)
                                    // commenter ligne $('#charge_btn#id#').hide();
                    myToyota::update_datas($eqLogic, 'find-vehicle');
                    //$eqLogic->doChargeNow();
                    break;
				        case 'stopChargeNow': 
					          //$eqLogic->stopChargeNow();
					          break;
				        default:
                    throw new \Exception("Unknown command", 1);
                    break;
            }
        } catch (Exception $e) {
            echo 'Exception : ',  $e->getMessage(), "\n";
            log::add('myToyota', 'debug', __('└─Commande exécution erreur :', __FILE__) . ' ' . $logical.' - '.$e->getMessage());
        }
		
		$eqLogic->refreshWidget();
	}
	


  /*     * **********************Getteur Setteur*************************** */
}