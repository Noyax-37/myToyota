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
      $coordinates = myToyota::getGPSCoordinates($eqLogic->getConfiguration('vehicle_vin'));
//      myToyota::getDistanceLocation2($eqlogic, $coordinates['latitude'], $coordinates['longitude']);
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
  
    $hw = jeedom::getHardwareName();
    if ($hw == 'diy')
        $hw = trim(shell_exec('systemd-detect-virt'));
    if ($hw == 'none')
        $hw = 'diy';
    $distrib = trim(shell_exec('. /etc/*-release && echo $ID $VERSION_ID'));
    $res = 'OS: ' . $distrib . ' on ' . $hw;
    $res .= ' ; PHP: ' . phpversion();
    $res .= ' ; Python: ' . trim(shell_exec("python3 -V | cut -d ' ' -f 2"));
    $res .= '<br/>myToyota: version ' . $core_version;
    $res .= ' ; cmds: ' . count(cmd::searchConfiguration('', myToyota::class));
    return $res;
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

  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
    $this->setLogicalId($this->getConfiguration('vehicle_vin'));
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

  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      return "les infos essentiel de mon plugin";
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

	public static function synchronize($vin, $username, $password)
    {
      
      log::add('myToyota', 'info', '┌─Command execution : synchronize');
          
      log::add('myToyota', 'info', '---------------------------------------------------------------');
      log::add('myToyota', 'info', '-----------------Démarrage synchro des données-----------------');
      $myToyotaPath         	  = realpath(dirname(__FILE__) . '/../../ressources');
      $output = [];
      $vehicle = [];
      $vehicle['vin'] = $vin;
      $retours = 0;
  
      $cmd          = 'sudo nice -n 19 '. $myToyotaPath . '/venv/bin/python3 ' . $myToyotaPath . '/synchro.py';
      $cmd         .= ' --loglevel warning';
      $cmdbis       = $cmd . ' --username ***** --password ***** --vin *****';
      $cmd         .= ' --username ' . $username;
      $cmd         .= ' --password ' . $password;
      $cmd         .= ' --vin ' . $vin;
      log::add('myToyota', 'info', ' lancement programme : ' . $cmdbis);
      $result = exec('nohup ' . $cmd, $output);
      foreach ($output as $i => $value){
        log::add('myToyota', 'info', $value);
        if (substr($value,0,4) == "Type") {
          $vehicle['attributes']['driveTrain'] = substr($value,5);
          $retours += 1;
        }
        if (substr($value,0,4) == "Date") {
          $vehicle['attributes']['year'] = substr($value,5);
          $retours += 1;
        }
        if (substr($value,0,4) == "Nom ") {
          $vehicle['attributes']['model'] = substr($value,5);
          $retours += 1;
        }
        if (substr($value,0,4) == "Capa ") {
          $vehicle['attributes']['capa'] = substr($value,5);
        }


      }
      
      if ((strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) && ($retours != 3)) {
          log::add('myToyota', 'error', 'Erreur pendant la synchro : ' . $result);
          return false;
      }
      log::add('myToyota', 'info', '------------------------Synchro terminée-----------------------');
      log::add('myToyota', 'info', '---------------------------------------------------------------');

      return $vehicle;
		
	  }
	
    public static function all_data($username, $password)
    {
      
      log::add('myToyota', 'info', '┌─Command execution : recup all datas');
          
      log::add('myToyota', 'info', '---------------------------------------------------------------');
      log::add('myToyota', 'info', '------------------Démarrage recup des données------------------');
      $myToyotaPath         	  = realpath(dirname(__FILE__) . '/../../ressources');
      $output = [];
  
      $cmd          = 'sudo nice -n 19 '. $myToyotaPath . '/venv/bin/python3 ' . $myToyotaPath . '/data.py';
      $cmd         .= ' --loglevel debug';
      $cmdbis       = $cmd . ' --username ***** --password *****';
      $cmd         .= ' --username ' . $username;
      $cmd         .= ' --password ' . $password;
      log::add('myToyota', 'info', ' lancement programme : ' . $cmdbis);
      $result = exec('nohup ' . $cmd, $output);
      foreach ($output as $i => $value){
        log::add('myToyota', 'info', $value);
      }
      
      if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
          log::add('myToyota', 'error', 'Erreur pendant la synchro : ' . $result);
          return false;
      }
      log::add('myToyota', 'info', '-------------------------recup terminée------------------------');
      log::add('myToyota', 'info', '---------------------------------------------------------------');
		
	  }
	

    public static function interromyToyota($eqLogic)
    {
      $myToyotaPath         	  = realpath(dirname(__FILE__) . '/../../ressources');
      log::add('myToyota', 'info', '---------------------------------------------------------------');
      log::add('myToyota', 'info', ' Démarrage Interrogation serveur myToyota ' . strval($eqLogic->getName()));
      $idvehicule = $eqLogic->getId();
      $nomvehicule = str_replace(' ', '_', strval($eqLogic->getName()));
  
      if ($idvehicule!='Aucun' && $idvehicule!=''){
        $cmd          = 'sudo nice -n 19 ' . $myToyotaPath . '/venv/bin/python3 ' . $myToyotaPath . '/myToyotad.py';
        $cmd         .= ' --apikey ' . jeedom::getApiKey('myToyota');
        $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/myToyota/core/php/jeemyToyota.php';
        $cmd         .= ' --nomvehicule ' . $nomvehicule;
        $cmd         .= ' --idvehicule ' . $eqLogic->getId();
        $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));
        $cmdbis       = $cmd . ' --username ***** --password ***** --vin *****';
        $cmd         .= " --username '" . $eqLogic->getConfiguration('username') . "'";
        $cmd         .= " --password '" . $eqLogic->getConfiguration('password') . "'";
        $cmd         .= ' --vin ' . $eqLogic->getConfiguration('vehicle_vin');
  
        log::add('myToyota', 'debug', ' Exécution du service : ' . $cmdbis);
        $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('myToyota_' . $nomvehicule) . ' 2>&1 &');
        if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
            log::add('myToyota', 'error', '[myToyota]-----' . $result);
            return false;
        }
        log::add('myToyota', 'info', '[myToyota] OK');
        log::add('myToyota', 'info', '---------------------------------------------------------------');
      }
      else {
        log::add('myToyota', 'info', '[myToyota] HS, aucun fichier de paramètres Onduleur sélectionné');
        log::add('myToyota', 'info', '---------------------------------------------------------------');
      }
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

    public function getDistanceLocation2($eqLogic, $lat1, $lng1)
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
      log::add('myToyota', 'info', '[myToyota] dernière position de ' . $nomvehicule . ': Lat et long ' . $lat2 . ' ' . $lng2);
      
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

    public static function chercheLycos($vin, $latitude, $longitude){
      $eqLogic = myToyota::getToyotaEqLogic($vin);
      $nomvehicule = $eqLogic->getName();
      $distance = myToyota::getDistanceLocation2($eqLogic, $latitude, $longitude); //en metres
      log::add('myToyota', 'info', '[myToyota] ' . $nomvehicule . ': distance avec le domicile ' . $distance . ' mètre(s)');
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

    public function getConnection()
    {
        $vin = $this->getConfiguration("vehicle_vin");
        $username = $this->getConfiguration("username");
        $password = $this->getConfiguration("password");
        $brand = 'T'; //$this->getConfiguration("vehicle_brand");
        if ($brand == 'T'){
          $constructeur = 'Toyota';
        }else if ($brand=='L'){
          $constructeur = 'Lexus';
        }
        log::add('myToyota', 'debug', '| Result user : '. substr($username,0,3) . '***' . substr($username,-3,3) . ' ; password : '. substr($password,0,2) . '***' . substr($password,-2,2) . ' ; vin : '. substr($vin,0,3) . '***' . substr($vin,-3,3) . ' ; constructeur : ' . $constructeur);
        $myConnection = new MyToyota_API($username, $password, $vin, $brand);
        return $myConnection;
  	}


    public function update_datas($eqLogic)
    {
      $myConnection = $eqLogic->getConnection();
      //log::add('myToyota', 'debug', '| Result telemetry : ' . ($myConnection));
      $result = $myConnection->getDevice();
      $devices = json_decode($result->body);
      log::add('myToyota', 'debug', '| Return devices body :' . str_replace('\n','',json_encode($devices)));

      $result = $myConnection->getLocationEndPoint();
      $locationEndpoint = json_decode($result->body);
      log::add('myToyota', 'debug', '| Return location endpoint body :' . str_replace('\n','',json_encode($locationEndpoint)));


    }


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
		log::add('myToyota', 'debug', '┌─Command execution : '.$logical);
		
		try {
            switch ($logical) {
                case 'refresh':
                    myToyota::interromyToyota($eqLogic);
					          break;
                case 'hornBlow':
                    //$eqLogic->doHornBlow();
                    break;
                case 'lightFlash':
                    //$eqLogic->doLightFlash();
                    break;
                case 'doorLock':
                    myToyota::update_datas($eqLogic);
                    //$eqLogic->doDoorLock();
                    break;
                case 'doorUnlock':
                    //$eqLogic->doDoorUnlock();
                    break;
                case 'climateNow':
                    //$eqLogic->doClimateNow();
                    break;
				        case 'stopClimateNow':
                    //$eqLogic->stopClimateNow();
                    break;
				        case 'chargeNow':
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