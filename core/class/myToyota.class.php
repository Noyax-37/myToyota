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
	require_once __DIR__ . '/../../3rdparty/myToyota_API.php';
}
    


class myToyota extends eqLogic {
  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
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

  
  // Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron() {
    $dt = time();
    if (date( "i", $dt )!="00" || date( "i", $dt )!="01" || date( "i", $dt )!="30" || date( "i", $dt )!="31") {
      system::kill('myToyotad.py');
    }
  }
  

  
  // Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {    
    $debug = false;
    $idvehicule = 'Aucun';
    system::kill('myToyotad.py');
    foreach (eqLogic::byType('myToyota', true) as $eqLogic) {
      $nameVehicule = $eqLogic->getName();
      log::add('myToyota', 'debug', " récupération des données du véhicule : " . '  ' . $nameVehicule);
      myToyota::interromyToyota($eqLogic);
      //$coordinates = myToyota::getGPSCoordinates($eqLogic->getConfiguration('vehicle_vin'));
      //myToyota::getDistanceLocation2($eqlogic, $coordinates['latitude'], $coordinates['longitude']);
    }
  }

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
  public static function getConfigForCommunity() {
    if (!file_exists('/var/www/html/plugins/myToyota/plugin_info/info.json')) {
      log::add('myToyota','warning','Pas de fichier info.json');
    }
    $data = json_decode(file_get_contents('/var/www/html/plugins/myToyota/plugin_info/info.json'), true);
    if (!is_array($data)) {
        log::add('myToyota','warning','Impossible de décoder le fichier info.json');
    }
    try {
        $core_version = $data['pluginVersion'];
    } catch (\Exception $e) {
        log::add('myToyota','warning','Impossible de récupérer la version.');
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
		return ['ressources/venv'];
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
			throw new Exception('L\'identifiant ne peut pas être vide');
		}
		if (empty($this->getConfiguration('password'))) {
			throw new Exception('Le mot de passe ne peut etre vide');
		}
		if (empty($this->getConfiguration('vehicle_vin'))) {
			throw new Exception('Le d\'identification du véhicule ne peut pas être vide');
		}
		if (empty($this->getConfiguration('vehicle_brand'))) {
			throw new Exception('La marque du véhicule ne peut pas être vide');
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
		
    $this->createCmd('brand', 'Marque', 1, 'info', 'string'); //brand
    $this->createCmd('model', 'Modèle', 2, 'info', 'string'); //car_line_name
    $this->createCmd('year', 'Année', 3, 'info', 'string'); //manufactured_date
    $this->createCmd('type', 'Type', 4, 'info', 'string'); //electrique, hybride, ...

    $this->createCmd('carburant', 'Carburant', 5, 'info', 'string', 1); //essence ou diesel

    $this->createCmd('mileage', 'Kilométrage', 6, 'info', 'numeric', 1); //odometer

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

    $this->createCmd('tireFrontLeft_pressure', 'Pression pneu avant gauche', 20, 'info', 'numeric');
    $this->createCmd('tireFrontLeft_target', 'Consigne pneu avant gauche', 21, 'info', 'numeric');
    $this->createCmd('tireFrontRight_pressure', 'Pression pneu avant droit', 22, 'info', 'numeric');
    $this->createCmd('tireFrontRight_target', 'Consigne pneu avant droit', 23, 'info', 'numeric');		
    $this->createCmd('tireRearLeft_pressure', 'Pression pneu arrière gauche', 24, 'info', 'numeric');
    $this->createCmd('tireRearLeft_target', 'Consigne pneu arrière gauche', 25, 'info', 'numeric');		
    $this->createCmd('tireRearRight_pressure', 'Pression pneu arrière droit', 26, 'info', 'numeric');
    $this->createCmd('tireRearRight_target', 'Consigne pneu arrière droit', 27, 'info', 'numeric');		

    $this->createCmd('chargingStatus', 'Etat de la charge', 28, 'info', 'string');
    $this->createCmd('connectorStatus', 'Etat de la prise', 29, 'info', 'binary');
    $this->createCmd('beRemainingRangeElectric', 'Km restant (électrique)', 30, 'info', 'numeric');
    $this->createCmd('chargingLevelHv', 'Charge restante', 31, 'info', 'numeric');
    $this->createCmd('chargingEndTime', 'Heure de fin de charge', 32, 'info', 'string');

    $this->createCmd('beRemainingRangeFuelKm', 'Km restant (thermique)', 33, 'info', 'numeric');
    $this->createCmd('remaining_fuel', 'Carburant restant', 34, 'info', 'numeric');

    $this->createCmd('vehicleMessages', 'Messages', 35, 'info', 'string');
    $this->createCmd('gps_coordinates', 'Coordonnées GPS', 36, 'info', 'string');

    $this->createCmd('refresh', 'Rafraichir', 37, 'action', 'other');
    $this->createCmd('climateNow', 'Climatiser', 38, 'action', 'other');
    $this->createCmd('stopClimateNow', 'Stop Climatiser', 39, 'action', 'other');
    $this->createCmd('chargeNow', 'Charger', 40, 'action', 'other');
    $this->createCmd('stopChargeNow', 'Stop Charger', 41, 'action', 'other');
    $this->createCmd('doorLock', 'Verrouiller', 42, 'action', 'other');
    $this->createCmd('doorUnlock', 'Déverrouiller', 43, 'action', 'other');
    $this->createCmd('lightFlash', 'Appel de phares', 44, 'action', 'other');
    $this->createCmd('hornBlow', 'Klaxonner', 45, 'action', 'other');
    $this->createCmd('vehicleFinder', 'Recherche véhicule', 46, 'action', 'other');
    $this->createCmd('sendPOI', 'Envoi POI', 47, 'action', 'other');
    $this->createCmd('lastUpdate', 'Dernière mise à jour', 48, 'info', 'string');
    $this->createCmd('climateNow_status', 'Statut climatiser', 49, 'info', 'string');
    $this->createCmd('stopClimateNow_status', 'Statut stop climatiser', 50, 'info', 'string');
    $this->createCmd('chargeNow_status', 'Statut charger', 51, 'info', 'string');
    $this->createCmd('stopChargeNow_status', 'Statut stop charger', 52, 'info', 'string');
    $this->createCmd('doorLock_status', 'Statut verrouiller', 53, 'info', 'string');
    $this->createCmd('doorUnlock_status', 'Statut déverrouiller', 54, 'info', 'string');
    $this->createCmd('lightFlash_status', 'Statut appel de phares', 55, 'info', 'string');
    $this->createCmd('hornBlow_status', 'Statut klaxonner', 56, 'info', 'string');
    $this->createCmd('vehicleFinder_status', 'Statut recherche véhicule', 57, 'info', 'string');
    $this->createCmd('sendPOI_status', 'Statut envoi POI', 58, 'info', 'string');
    $this->createCmd('hazardOn', 'Feux de détresse', 45, 'action', 'other');
    $this->createCmd('hazardOff', 'Stop feux de détresse', 45, 'action', 'other');
    
    $this->createCmd('presence', 'Présence domicile', 59, 'info', 'binary');
    $this->createCmd('distance', 'Distance domicile', 60, 'info', 'numeric');

    $this->createCmd('totalEnergyCharged', 'Charge électrique totale', 61, 'info', 'numeric');
    $this->createCmd('chargingSessions', 'Sessions de charge', 62, 'info', 'string');
    $this->createCmd('services', 'Services', 63, 'info', 'string');
    $this->createCmd('beRemainingRangeTotal', 'Km restant (global)', 64, 'info', 'numeric');
    $this->createCmd('moy_sem', 'Moyenne semaine', 65, 'info', 'string');
    $this->createCmd('trajets', 'trajet 7 derniers jours', 66, 'info', 'string');
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

		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $template, 'myToyota')));
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
			log::add('myToyota', 'debug', 'Add command '.$cmd->getName().' (LogicalId : '.$cmd->getLogicalId().'), historisé : ' . $hist);
        }
  }

  public static function synchro_post_update($eqlogic){
      $vin = $eqlogic->getConfiguration('vehicle_vin');
      $username = $eqlogic->getConfiguration("username");
      $password = $eqlogic->getConfiguration("password");
      $brand = $eqlogic->getConfiguration("vehicle_brand");
      myToyota::synchronize($vin, $username, $password, $brand);
}

	public static function synchronize($vin, $username, $password, $brand)
    {
      $eqLogic = self::getToyotaEqLogic($vin);
      
      log::add('myToyota', 'info', '┌─Command execution : synchronize');
          
      log::add('myToyota', 'info', '---------------------------------------------------------------');
      log::add('myToyota', 'info', '-----------------Démarrage synchro des données-----------------');

      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->getDevice();
      $devices = json_decode($result->body);
      $vin = $eqLogic->getConfiguration('vehicle_vin');
      log::add('myToyota', 'debug', '| Return devices body :' . $result->body);
      log::add('myToyota', 'debug', '| Return nombre de véhicules :' . count($devices->payload) );

      $return['erreur'] = 'erreur';

      if ( count($devices->payload) == 0 )
      {
        log::add('myToyota', 'debug', '| Result getVehicles() : pas de véhicule trouvé avec service myToyota activé');
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
            log::add('myToyota', 'info', "| Result getVehicles() : ok c'est le VIN recherché");
            //if ( isset($vehicle->attributes->brand) ) { $eqLogic->checkAndUpdateCmd('brand', $vehicle->attributes->brand); } else { $eqLogic->checkAndUpdateCmd('brand', 'not available'); }
            if ( isset($vehicle->manufacturerCode) ) { 
              $eqLogic->checkAndUpdateCmd('brand', $vehicle->manufacturerCode);
              log::add('myToyota', 'info', '| Result fabricant : ' . $vehicle->manufacturerCode);
            } else { 
              $eqLogic->checkAndUpdateCmd('brand', 'not available'); 
              log::add('myToyota', 'info', '| Result fabricant : Inconnu');
            }
            $return['modelName'] = 'inconnu'; 
            if ( isset($vehicle->modelName) ) { 
              $eqLogic->checkAndUpdateCmd('model', $vehicle->modelName);
              $return['modelName'] = $vehicle->modelName;
              log::add('myToyota', 'info', '| Result modèle : ' . $return['modelName']);
            } else { 
              $eqLogic->checkAndUpdateCmd('model', 'not available');
              log::add('myToyota', 'info', '| Result modèle : inconnu');
            }
            $eqLogic->setConfiguration('vehicle_model', $return['modelName']);
            $eqLogic->save(true);
            if ( isset($vehicle->manufacturedDate) ) { 
              $return['modelYear'] = date("d-m-Y", strtotime($vehicle->manufacturedDate));
              $eqLogic->setConfiguration('vehicle_year', $return['modelYear']);
              $eqLogic->checkAndUpdateCmd('year', $return['modelYear']);
              $eqLogic->save(true);
              log::add('myToyota', 'info', '| Result fabrication : ' . $return['modelYear']);
            } else { 
              $eqLogic->checkAndUpdateCmd('year', 'not available'); 
              log::add('myToyota', 'info', '| Result fabrication : Inconnu');
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
            log::add('myToyota', 'info', '| Result type de motorisation : ' . $return['driveTrain']);
            if (isset($vehicle->fuelType)){
              if ($vehicle->fuelType == 'B'){
                $eqLogic->checkAndUpdateCmd('carburant', 'Essence');
              } else {
                $eqLogic->checkAndUpdateCmd('carburant', 'Diesel');
              }
              log::add('myToyota', 'info', '| Result carburant type : ' . $vehicle->fuelType);
            }
            if ( isset($vehicle->stockPicReference)) {
              $filename = dirname(__FILE__).'/../../data/'.$vin.'.png';
              $img = $vehicle->stockPicReference;
              log::add('myToyota', 'debug', '| Result image : '. $img);
              file_put_contents($filename,file_get_contents($img));
            }
            $return['erreur'] = 'ok';
            $return['vin'] = $vin;
          } else {
            log::add('myToyota', 'info', '| Result getVehicles() : pas le bon VIN');
          }
        }
      }
      log::add('myToyota', 'info', "| Fin de la synchronisation");

      return $return;
  
	  }
	
    public static function all_data($username, $password, $vin)
    {
      
      log::add('myToyota', 'info', '┌─Command execution : recup all datas');
          
      log::add('myToyota', 'info', '| Démarrage recup des données');
      $eqLogic = self::getToyotaEqLogic($vin);
      
      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->getDevice();
      $devices = json_decode($result->body);
      log::add('myToyota', 'info', '| Return nombre de véhicules :' . count($devices->payload) );

      if ( count($devices->payload) == 0 ){
        log::add('myToyota', 'info', '| Result getVehicles() : pas de véhicule trouvé avec service myToyota activé');
        //log::add('myToyota', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─fin de la synchronisation : ['.$result->httpCode.']');
      } else {
        log::add('myToyota', 'info', '| Return getDevice :' . $result->body );

        $result = $myConnection->getLocationEndPoint();
        log::add('myToyota', 'info', '| Retour localisation : ' . $result->body);

        $result = $myConnection->getRemoteStatusEndPoint(); //status des équipements
        log::add('myToyota', 'info', '| Retour remote status :' . $result->body);

        $result = $myConnection->getTelemetryEndPoint(); //dernière localisation
        log::add('myToyota', 'info', '| Retour télémétrie : ' . $result->body);

        $result = $myConnection->getRemoteClimateStatus(); //dernière localisation
        log::add('myToyota', 'info', '| Retour climatisation : ' . $result->body);

        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime('-7 days', strtotime($to)));
        $route = false;
        $summary = true;
        $limit = 1000; // 1000 max si $route = false et 50 max si $rpute = true
        $offset = 0;
  
        $result = $myConnection->getTripsEndpoint($from, $to, $route, $summary, $limit, $offset); //pour récupérer les 7 derniers jours
        log::add('myToyota', 'info', '| Retour trips : ' . $result->body);

        $result = $myConnection->historiqueNotification();
        log::add('myToyota', 'info', '| Retour historique des notifications : ' . $result->body);

        $result = $myConnection->remoteElectric();
        log::add('myToyota', 'info', '| Retour remote electric : ' . $result->body);

        $result = $myConnection->remoteClimateSettings();
        log::add('myToyota', 'info', '| Retour remote climate setting : ' . $result->body);

        $result = $myConnection->remoteACReservation();
        log::add('myToyota', 'info', '| Retour remote AC reservation : ' . $result->body);

      }



      log::add('myToyota', 'info', '| recup terminée');
	  }
	

    public static function interromyToyota($eqLogic)
    {
      $myToyotaPath = realpath(dirname(__FILE__) . '/../../ressources');
      $myConnection = $eqLogic->getConnection();
      $idvehicule = $eqLogic->getId();
      $nomvehicule = $eqLogic->getName();
      $capabilities = json_decode($eqLogic->getConfiguration('capabilities'));
      $vehicle_type = $eqLogic->getConfiguration('vehicle_type');
      
      if ($idvehicule!='Aucun' && $idvehicule!=''){
        log::add('myToyota', 'info', '| ----------------------------------------------------------------------------------------------');
        log::add('myToyota', 'info', '| Démarrage Interrogation serveur myToyota pour le véhicule ' . strval($eqLogic->getName()) . ' avec ID ' . $idvehicule);
          
        //dernière localisation
        $result = $myConnection->getLocationEndPoint();
        $location = json_decode($result->body);
        log::add('myToyota', 'debug', '| Retour localisation : ' . $result->body);
        if ( isset($location->payload->vehicleLocation) ) { 
          $eqLogic->checkAndUpdateCmd('lastUpdate',date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
          $eqLogic->checkAndUpdateCmd('gps_coordinates', $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude);
          myToyota::chercheLycos($eqLogic,$location->payload->vehicleLocation->latitude, $location->payload->vehicleLocation->longitude);
          log::add('myToyota', 'info', '| Dernière localisation connue: "' . 
                                            $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude . '" le ' . 
                                            date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
        }

        // état des fenetres, portes, ...
        $result = $myConnection->getRemoteStatusEndPoint(); //status des équipements
        $remoteStatus = json_decode($result->body);
        log::add('myToyota', 'debug', '| Retour remote status :' . $result->body);
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
        $doors = 0;
        $doorOpen = 0;
        $doorsToLock = 0;
        $doorUnlocked = 0;
        if ($remoteStatus->status->messages[0]->description == "Request Completed Successfully") {
          foreach ($remoteStatus->payload->vehicleStatus as $vehicleStatus){
            foreach ($vehicleStatus->sections as $sections){
              $element = 'Other';
              if ($vehicleStatus->category == 'carstatus_category_driver'){
                $element = 'Driver';
              } else if ($vehicleStatus->category == 'carstatus_category_passenger'){
                $element = 'Passenger';
              }
              if ($element != 'Other'){
                if (strstr($sections->section, 'rear')){
                  $element .= 'Rear';
                } else {
                  $element .= 'Front';
                }
                if (strstr($sections->section, 'door')){
                  $element = 'door' . $element;
                  $doors++;
                } else if (strstr($sections->section, 'window')){
                  $element = 'window' . $element;
                  $windows++;
                }
              } else {
                if (strstr($sections->section, 'rear_hatch')){
                  $element = 'trunk_state';
                  $doors++;
                } else if (strstr($sections->section, 'hood')){
                  $element = 'hood_state';
                  $doors++;
                } else if (strstr($sections->section, 'moonroof')){
                  $element = 'moonroof_state';
                  $windows++;
                }
              }
              foreach ($sections->values as $values){
                $status = 'UNKNOWN';
                if ($values->value == 'carstatus_closed' || $values->value == 'carstatus_open'){
                  if ($values->status == 0 ){
                    $status = 'CLOSED';
                  } else {
                    $status = 'OPEN';
                    if (strstr($element, 'window') || strstr($element, 'moonroof')){
                      $windowOpen++;
                    } else {
                      $doorOpen++;
                    }
                  }
                  log::add('myToyota', 'info', '| Return élement: ' . $element . ' Status : ' . $status);
                  $eqLogic->checkAndUpdateCmd($element, $status);
                }
                if ($values->value == 'carstatus_locked' || $values->value == 'carstatus_unlocked'){
                  $doorsToLock++;
                  if ($values->status != 0 ){
                    $doorUnlocked++;
                  }
                }
              }
            }
          }
        }
        if ($doors == 0){
          $eqLogic->checkAndUpdateCmd('doorLockState', 'UNKNOWN');
          log::add('myToyota', 'info', '| Return élement: doorLockState Status : UNKNOWN');
        } else if ($doorUnlocked == 0){
          $eqLogic->checkAndUpdateCmd('doorLockState', 'LOCKED');
          log::add('myToyota', 'info', '| Return élement: doorLockState Status : LOCKED ' . $doorsToLock . ' / ' . $doorsToLock);
        } else {
          $eqLogic->checkAndUpdateCmd('doorLockState', 'UNLOCKED');
          log::add('myToyota', 'info', '| Return élement: doorLockState Status : UNLOCKED ' . $doorUnlocked . ' non verrouillée(s) / ' . $doorsToLock . ' portes verrouillables');
        }
        if ($doors == 0){
          $eqLogic->checkAndUpdateCmd('allDoorsState', 'UNKNOWN');
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : UNKNOWN ');
        } else if ($doorOpen == 0){
          $eqLogic->checkAndUpdateCmd('allDoorsState', 'CLOSED');
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : CLOSED ' . $doors . ' / ' . $doors);
        } else {
          $eqLogic->checkAndUpdateCmd('allDoorsState', 'OPEN');
          log::add('myToyota', 'info', '| Return élement: allDoorsState Status : OPEN ' . $doorOpen . ' / ' . $doors);
        }
        if ($windows == 0){
          $eqLogic->checkAndUpdateCmd('allWindowsState', 'UNKNOWN');
          log::add('myToyota', 'info', '| Return élement: allWindowsState Status : UNKNOWN ');
        } else if ($windowOpen == 0){
          $eqLogic->checkAndUpdateCmd('allWindowsState', 'CLOSED');
          log::add('myToyota', 'info', '| Return élement: allWindowsState Status : CLOSED ' . $windows . ' / ' . $windows);
        } else {
          $eqLogic->checkAndUpdateCmd('allWindowsState', 'OPEN');
          log::add('myToyota', 'info', '| Return élement: allWindowsState Status : OPEN ' . $windowOpen . ' / ' . $windows);
        }
  
        // télémétrie
        $result = $myConnection->getTelemetryEndPoint(); //dernière localisation
        $body = json_decode($result->body);
        log::add('myToyota', 'debug', '| Retour télémétrie : ' . $result->body);

        if ($body->status == 'SUCCESS'){
          $telemetrie = $body->payload;
          // km totaux
          if ( isset($telemetrie->odometer) ){
            $eqLogic->checkAndUpdateCmd('mileage', $telemetrie->odometer->value);
            log::add('myToyota', 'info', '| Return élement: km totaux : '. $telemetrie->odometer->value . ' km');
          } else {
            $eqLogic->checkAndUpdateCmd('mileage', '---');
            log::add('myToyota', 'info', '| Return élement: km totaux inconnu');
          }

          //paramètres communs
          if ( isset($telemetrie->fuelLevel) ){
            $eqLogic->checkAndUpdateCmd('remaining_fuel', $telemetrie->fuelLevel);
            log::add('myToyota', 'info', '| Return élement: niveau réservoir : '. $telemetrie->fuelLevel . '%');
          } else {
            $eqLogic->checkAndUpdateCmd('remaining_fuel', '---');
            log::add('myToyota', 'info', '| Return élement: niveau réservoir inconnu');
          }

          if ( isset($telemetrie->distanceToEmpty) ){
            $eqLogic->checkAndUpdateCmd('beRemainingRangeTotal', $telemetrie->distanceToEmpty->value);
            log::add('myToyota', 'info', '| Return élement: distance possible (ev + essence) : '. $telemetrie->distanceToEmpty->value . ' km');
          } else {
            $eqLogic->checkAndUpdateCmd('beRemainingRangeTotal', '---');
            log::add('myToyota', 'info', '| Return élement: distance possible ev + essence inconnu');
          }
          
          // paramétres hybrides
          if ($vehicle_type == 'Hybride' || $vehicle_type == 'Thermique'){
            $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', $telemetrie->distanceToEmpty->value);
            log::add('myToyota', 'info', '| Return élement: distance possible thermique : '. $telemetrie->distanceToEmpty->value . ' km');
         } else {
            $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', '---');
            log::add('myToyota', 'info', '| Return élement: distance possible essence inconnue');
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
                log::add('myToyota', 'info', '| Return élement: niveau réservoir : '. $telemetrie_elec->fuelLevel . '%');
              } else {
                $eqLogic->checkAndUpdateCmd('remaining_fuel', '---');
                log::add('myToyota', 'info', '| Return élement: niveau réservoir inconnu');
              }

              if ( isset($telemetrie_elec->fuelRange) ){
                $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', $telemetrie_elec->fuelRange->value);
                log::add('myToyota', 'info', '| Return élement: distance avant réservoir vide : '. $telemetrie_elec->fuelRange->value . ' km');
              } else {
                $eqLogic->checkAndUpdateCmd('beRemainingRangeFuelKm', '---');
                log::add('myToyota', 'info', '| Return élement: distance avant réservoir vide inconnu');
              }

              if ( isset($telemetrie_elec->batteryLevel) ){
                $eqLogic->checkAndUpdateCmd('chargingLevelHv', $telemetrie_elec->batteryLevel);
                log::add('myToyota', 'info', '| Return élement: niveau batterie : '. $telemetrie_elec->batteryLevel . '%');
              } else {
                $eqLogic->checkAndUpdateCmd('chargingLevelHv', '---');
                log::add('myToyota', 'info', '| Return élement: niveau batterie inconnu');
              }

              if ( isset($telemetrie_elec->evRangeWithAc) ){
                $eqLogic->checkAndUpdateCmd('beRemainingRangeElectric', $telemetrie_elec->evRangeWithAc->value);
                log::add('myToyota', 'info', '| Return élement: distance avant batterie vide : '. $telemetrie_elec->evRangeWithAc->value . ' km');
              } else {
                $eqLogic->checkAndUpdateCmd('beRemainingRangeElectric', '---');
                log::add('myToyota', 'info', '| Return élement: distance avant batterie vide inconnu');
              }

              if ( isset($telemetrie_elec->chargingStatus)){
                $eqLogic->checkAndUpdateCmd('chargingStatus', $telemetrie_elec->chargingStatus);
                log::add('myToyota', 'info', '| Return élement: status de la charge' . $telemetrie_elec->chargingStatus);
              } else {
                $eqLogic->checkAndUpdateCmd('chargingStatus', 'UNKNOWN');
                log::add('myToyota', 'info', '| Return élement: status de la charge inconnue');
              }

            }
          }


          // Climatisation ???
          $result = $myConnection->getRemoteClimateStatus(); //dernière localisation
          $climateStatus = json_decode($result->body);
    
          log::add('myToyota', 'debug', '| Retour climatisation : ' . $result->body);

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
    
          log::add('myToyota', 'debug', '| retour trips' . $result->body);
          
          // moyennes sur 7 jours
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
            log::add('myToyota', 'info', '| retour trips moyenne sur 7 jours: ' . $summarySem);          
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
              log::add('myToyota', 'info', '| retour résumé trajet n° ' . strval($i) . ' : ' . json_encode($trajet['trajet' . strval($i++)]));
          }
          $tripsJson = json_encode($trajet);
          $eqLogic->checkAndUpdateCmd('trajets', $tripsJson);

        }

        // Santé du véhicule
        $result = $myConnection->statusHealth(); //dernière localisation
        $body = json_decode($result->body);
        log::add('myToyota', 'debug', '| Retour santé véhicule : ' . $result->body);
        if ($body->status == 'SUCCESS'){
          $sante = $body->payload;
          if ($sante->quantityOfEngOilIcon[0] == ''){
          $table_messages['checkControlMessages'] = array( "type" => "ENGINE_OIL ", "date" => '', "mileage" => '', "state" => '', "title" => "Huile moteur", "description" => "", "severity" => 'OK' );
  
          $eqLogic->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
          log::add('myToyota', 'info', '| retour message santé  : ' . json_encode($table_messages['checkControlMessages']));
          }
        }

        // services et entretien
        $result = $myConnection->historiqueService();
        $body = json_decode($result->body);
        log::add('myToyota', 'debug', '| Retour services et entretien : ' . $result->body);
        if ($body->status->messages[0]->description == 'Request Processed successfully'){
          $histoservice = $body->payload->serviceHistories;
          $i=1;
          foreach ($histoservice as $service){
            $services['service' . $i] = array('date' => date('d-m-Y', strtotime($service->serviceDate)), 'enregistrement_consommateur' => $service->customerCreatedRecord, 
              'compteur' => $service->mileage . ' ' . $service->unit, 'notes' => $service->notes,
              'operations'=> $service->operationsPerformed, 'ro_number' => $service->roNumber,
              'categorie' => $service->serviceCategory, 'garage' => $service->serviceProvider,
              'concessionaire' => $service->servicingDealer);
              log::add('myToyota', 'info', '| retour résumé services n° ' . strval($i) . ' : ' . json_encode($services['service' . strval($i++)]));
          }
          $servicesJson = json_encode($services);
          $eqLogic->checkAndUpdateCmd('services', $servicesJson);
        }

        

        //fin du traitement
        log::add('myToyota', 'info', '| fin du traitement pour le véhicule ' . strval($eqLogic->getName()) . ' avec ID ' . $idvehicule);
        log::add('myToyota', 'info', '| ------------------------------------------------------------------------');

      } else {
      log::add('myToyota', 'info', '| ------------------------------------------------------------------------');
      log::add('myToyota', 'info', '| Démarrage Interrogation serveur myToyota impossible, ID inconnu ou nul');
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
      log::add('myToyota', 'info', '| dernière position de ' . $nomvehicule . ': Lat et long ' . $lat2 . ' ' . $lng2);
      
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
      log::add('myToyota', 'info', '| ' . $nomvehicule . ': distance avec le domicile ' . $distance . ' mètre(s)');
			$eqLogic->checkAndUpdateCmd('distance', $distance);
			if ( $distance <= $eqLogic->getConfiguration("home_distance") ) { $eqLogic->checkAndUpdateCmd('presence', 1); }
			else { $eqLogic->checkAndUpdateCmd('presence', 0); }
    }
    
    public static function getGPSCoordinates($vin)
    {
      $eqLogic = self::getToyotaEqLogic($vin);
      $cmd = $eqLogic->getCmd(null, 'gps_coordinates');
      
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
      log::add('myToyota', 'debug', '| Retour recherche véhicule : ' . $result->body);
      if ( isset($location->payload->vehicleLocation) ) { 
        $gps_coordinates = $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude;
        $eqLogic->checkAndUpdateCmd('lastUpdate',date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
        $eqLogic->checkAndUpdateCmd('gps_coordinates', $gps_coordinates);
        log::add('myToyota', 'info', '| Dernière localisation connue: "' . 
          $location->payload->vehicleLocation->latitude . ',' . $location->payload->vehicleLocation->longitude . '" le ' . 
          date("d-m-Y à G:i:s", strtotime($location->payload->vehicleLocation->locationAcquisitionDatetime)));
          return $gps_coordinates;
      } else {
        log::add('myToyota', 'error', 'le serveur ne répond pas');
        return false;
      }
    }
  
    public function getConnection()
    {
        $vin = $this->getConfiguration("vehicle_vin");
        $username = $this->getConfiguration("username");
        $password = $this->getConfiguration("password");
        $brand = $this->getConfiguration("vehicle_brand");
        if ($brand == 'T'){
          $constructeur = 'Toyota';
        }else if ($brand=='L'){
          $constructeur = 'Lexus';
        }
        log::add('myToyota', 'debug', '| Result user : '. substr($username,0,3) . '***' . substr($username,-3,3) . ' ; password : '. substr($password,0,2) . '***' . substr($password,-2,2) . ' ; vin : '. substr($vin,0,3) . '***' . substr($vin,-3,3) . ' ; constructeur : ' . $constructeur);
        $myConnection = new MyToyota_API($username, $password, $vin, $brand);
        return $myConnection;
  	}

    public function commandes($eqLogic, $commande)
    {

      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->remoteCommande($commande); //commande envoyées vers véhicule
      $resultCmde = json_decode($result->body);

      log::add('myToyota', 'debug', '| retour ' . $result->body . ' à la commande : '. $commande);

    }

    public function commande_clim($eqLogic, $commande)
    {

      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->remoteClimate($commande); //commande envoyées vers véhicule
      $resultCmde = json_decode($result->body);

      log::add('myToyota', 'debug', '| retour ' . $result->body . ' à la commande : '. $commande);

    }

// zone de tests ********************************************************************************************

    public function update_datas($eqLogic, $commande) // fonction de test pour développement
    {

      $myConnection = $eqLogic->getConnection();
      $result = $myConnection->testPoubelle_post($commande);
      $tripsEndpoint = json_decode($result->body);

      log::add('myToyota', 'debug', '| retour ' . $result->body);

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
            log::add('myToyota', 'debug', '└─Command execution error : '.$logical.' - '.$e->getMessage());
        }
		
		$eqLogic->refreshWidget();
	}
	


  /*     * **********************Getteur Setteur*************************** */
}