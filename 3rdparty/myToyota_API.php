<?php

class myToyota_API
{
    //TOYOTA URLs
    const API_BASE_URL = 'https://ctpa-oneapi.tceu-ctp-prd.toyotaconnectedeurope.io';
    const ACCESS_TOKEN_URL = 'https://b2c-login.toyota-europe.com/oauth2/realms/root/realms/tme/access_token';
    const AUTHENTICATE_URL = 'https://b2c-login.toyota-europe.com/json/realms/root/realms/tme/authenticate?authIndexType=service&authIndexValue=oneapp';
    const AUTHORIZE_URL = 'https://b2c-login.toyota-europe.com/oauth2/realms/root/realms/tme/authorize?client_id=oneapp&scope=openid+profile+write&response_type=code&redirect_uri=com.toyota.oneapp:/oauth2Callback&code_challenge=plain&code_challenge_method=plain';
    
    const VEHICLE_ASSOCIATION_ENDPOINT = '/v1/vehicle-association/vehicle';
    const VEHICLE_GUID_ENDPOINT = '/v2/vehicle/guid';
    const VEHICLE_LOCATION_ENDPOINT = '/v1/location';
    const VEHICLE_HEALTH_STATUS_ENDPOINT = '/v1/vehiclehealth/status';
    const VEHICLE_GLOBAL_REMOTE_STATUS_ENDPOINT = '/v1/global/remote/status'; // update device 
    const VEHICLE_GLOBAL_REMOTE_ELECTRIC_STATUS_ENDPOINT = '/v1/global/remote/electric/status';
    const VEHICLE_TELEMETRY_ENDPOINT = '/v3/telemetry'; // update device 
    const VEHICLE_NOTIFICATION_HISTORY_ENDPOINT = '/v2/notification/history';
    const VEHICLE_TRIPS_ENDPOINT = '/v1/trips?from=%s&to=%s&route=%s&summary=%s&limit=%s&offset=%s';
    const VEHICLE_SERVICE_HISTORY_ENDPONT = '/v1/servicehistory/vehicle/summary';
    const VEHICLE_REMOTE_CLIMATE_STATUS = '/v1/global/remote/climate-status'; // update device 
    const VEHICLE_GLOBAL_REMOTE_COMMAND = '/v1/global/remote/command';  // commandes diverses
    const VEHICLE_REMOTE_CLIMATE_CONTROL = '/v1/global/remote/climate-control'; // commande on - off pour la clim

    // à tester sur des véhicules qui en sont capables
    const VEHICLE_REMOTE_CLIMATE_SETTING = '/v1/global/remote/climate-settings'; // commande réglage de la clim (GET et PUT) 
                                                                                    //=> GET: 
                                                                                    //{"status":{"messages":[{"responseCode":"ONE-GLOBAL-RS-10000","description":"Request Completed Successfully","detailedDescription":"Request Completed Successfully"}]},
                                                                                        //"payload":{"temperature":null,"temperatureUnit":null,"minTemp":18.0,"maxTemp":29.0,"tempInterval":1.0,"acOperations":[],"settingsOn":true}}
    const VEHICLE_REMOTE_AC_RESERVATION  = '/v1/global/remote/ac-reservation'; // commande et contrôle recharge ? (GET, POST, PUT et DELETE) 
                                                                                    //=> GET: 
                                                                                    //{"status":{"messages":[{"responseCode":"ONE-GLOBAL-RS-10000","description":"Request Completed Successfully","detailedDescription":"Request Completed Successfully"}]},
                                                                                        //"payload":{"airConditioningReservation":[]}}

                                                                                    //=> POST:
                                                                                    //{"status":{"messages":[{"responseCode":"ONE-GLOBAL-RS-10201","description":"[temperatureUnit:Missing temperatureUnit in the request, 
                                                                                                                                                                    //temperature:Missing/Invalid temperature in the request, 
                                                                                                                                                                    //reservationType:Invalid/Missing reservationType in the request. Allowed values are ONE_TIME, REPETITION]",
                                                                                                                                                                    //"detailedDescription":"Invalid Input"}]}}
    const VEHICLE_REMOTE_ELECTRIC_COMMAND = '/v1/global/remote/electric/command'; // commande de (?) (POST)
                                                                                    //=> POST:
                                                                                    //{"status":{"messages":[{"responseCode":"ONE-GLOBAL-RS-40004","description":"Invalid Command. 
                                                                                        //Allowed commands are CHARGE_NOW, RESERVE_CHARGE and SET_CHARGING_TIME",
                                                                                        //"detailedDescription":"Invalid Command. Allowed commands are CHARGE_NOW, RESERVE_CHARGE and SET_CHARGING_TIME"}]}}
    const VEHICLE_REMOTE_CHARGING_SCHEDULE = '/v1/global/remote/electric/charging-schedule/{id}'; // commande période de recharge ? (DELETE et PUT) (POST sans {id})
                                                                                    //=> POST:
                                                                                    //{"status":{"messages":[{"responseCode":"ONE-GLOBAL-RS-10201",
                                                                                        //"description":"Invalid/Missing type in the requestmust not be nullInvalid/Missing day in the requestInvalid/Missing day in the requestmust not be null.validation failed for the field - startTimedaysdaysenabledtype",
                                                                                        //"detailedDescription":"Invalid Input"}]}}
    const VEHICLE_REMOTE_PROFILE_SETTING = '/v1/global/remote/profile-settings'; // commande ? (POST, GET et PUT)  => apparemment pour paramétrer des utilisateurs 'guest' sur des périodes par jour... => aucun intéret
    const VEHICLE_REMOTE_REALTIME_STATUS = '/v1/global/remote/electric/realtime-status'; // commande ? (POST)
                                                                                    //=> POST:
                                                                                    //{"status":{"messages":[{"responseCode":"ONE-GLOBAL-RC-10004","description":"Extended capability either econnectVehicleStatusCapable or stellantisVehicleStatusCapable is not present for the VIN",
                                                                                        //"detailedDescription":"Extended capability either econnectVehicleStatusCapable or stellantisVehicleStatusCapable is not present for the VIN"}]}}
    const VEHICLE_REMOTE_LEGACY_PROFILE_SETTING = '/v1/legacy/remote/profile-settings'; // commande ? (GET et PUT)
    const VEHICLE_REMOTE_REFRESH_CLIMATE_STATUS = '/v1/global/remote/refresh-climate-status'; // commande clim ? (POST)
    const VEHICLE_REMOTE_REFRESH_STATUS = '/v1/global/remote/refresh-status'; // commande clim ? (POST)

    //Parameters
    private $username;
    private $password;
    private $vin;
    private $brand;
    private $fichierLog;
    private $access_token;
    private $token_expiration;
    private $refresh_token;
    private $uuid;
    private $timeout;



    //zone de tests ***************************************************************************
    public function testPoubelle_post($commande) // création d'une ressource
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_REMOTE_REFRESH_STATUS;
        $headers = $this->_setHeadersUpdate();
      	$headers[] = [ 'x-correlationid: D7F048C1-F0A1-4920-AA37-264C8A1FB4A3' ];
        
        $data = json_encode(array('command' => $commande));
        //$data = http_build_query($data);
        log::add('myToyota', 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'POST', $data, $headers);
		return $return;
    }

    public function testPoubelle_put($commande) // mise à jour d'une ressource
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_GLOBAL_REMOTE_ELECTRIC_STATUS_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
      	$headers[] = [ 'x-correlationid: D7F048C1-F0A1-4920-AA37-264C8A1FB4A3' ];
        
        $data = json_encode(array('command' => $commande));
        //$data = http_build_query($data);
        log::add('myToyota', 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'PUT', $data, $headers);
		return $return;
    }

    public function testPoubelle_get() // récupération des infos d'une ressource
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_REMOTE_CLIMATE_SETTING;
        $headers = $this->_setHeadersUpdate();
        log::add('myToyota', 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function testPoubelle_delete($commande) // destruction d'une ressource
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_GLOBAL_REMOTE_ELECTRIC_STATUS_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
      	$headers[] = [ 'x-correlationid: D7F048C1-F0A1-4920-AA37-264C8A1FB4A3' ];
        
        $data = json_encode(array('command' => $commande));
        //$data = http_build_query($data);
        log::add('myToyota', 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'DELETE', $data, $headers);
		return $return;
    }


    //fin zone de tests ************************************************************************


    public function  __construct($username, $password, $vin, $brand, $fichierLog)
    {
        if (!$username || !$password  || !$vin || !$brand || !$fichierLog) {
            throw new \Exception('Config parameters missing');
        }

		$this->username = $username;
		$this->password = $password;
        $this->vin = $vin;
        $this->brand = $brand;
        $this->fichierLog = $fichierLog;
        $this->timeout = 60;

        if (file_exists(dirname(__FILE__).'/../data/myToyota_' . $this->vin . '_token.json')) {
            $this->loadToken();
		}
		else  {
			$this->createToken();
		}
    }


    public function __destruct() {
	}


    private function _request($url, $method = 'GET', $data = null, $extra_headers = [])
    {
        $ch = curl_init();

        $headers = [];
        
        //$proxy = '192.168.1.106:8888';
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);

        // Default CURL options
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Set datas
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        // Add extra headers
        if (count($extra_headers)) {
            foreach ($extra_headers as $header) {
                $headers[] = $header;
            }
        }
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute request
        $response = curl_exec($ch);

        if (!$response) {
            throw new \Exception('Unable to retrieve data');
        }

        // Get response
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

		return (object)[
            'headers' => $header,
            'body' => $body,
            'httpCode' => $httpCode,
        ];
    }


	private function _setDefaultHeaders()   {				//Define default headers
		
		// $datetime = (new \DateTime('now', new DateTimeZone(config::byKey('timezone'))))->format('c');
		$headers = [
          'x-appbrand: ' . $this->brand,
          'x-osname: iOS',
          'user-agent: Toyota/134 CFNetwork/1410.0.3 Darwin/22.6.0',
          'x-region: EU',
          'region: EU',
          'brand: ' . $this->brand,
          'x-channel: ONEAPP',
          'x-osversion: 16.7.2',
          'x-brand: ' . $this->brand,
          'accept-language: fr-FR,fr;q=0.9',
          'x-appversion: 2.4.2',
          'accept: */*',
          'accept-api-version: resource=2.0, protocol=1.0',
        ];
     return $headers;
	}

    private function createToken() {

		$this->uuid = null;
        $this->access_token = null;
		$this->refresh_token = null;
        $this->token_expiration = null;
		$this->saveToken();
	}


	private function loadToken() {
	
		$array = json_decode(file_get_contents(dirname(__FILE__).'/../data/myToyota_' . $this->vin . '_token.json'), true);
		$this->uuid = $array['uuid'];
		$this->access_token = $array['access_token'];
		$this->refresh_token = $array['refresh_token'];
        $this->token_expiration = $array['token_expiration'];
	}


	private function saveToken() {
		
		$array = array(
			'uuid' => $this->uuid,
			'access_token' => $this->access_token,
			'refresh_token' => $this->refresh_token,
            'token_expiration' => $this->token_expiration,
		);
		file_put_contents(dirname(__FILE__).'/../data/myToyota_' . $this->vin . '_token.json', json_encode($array));
	}


    private function _checkAuth($fichierLog = 'myToyota')
    {
        if (!$this->access_token)
		{
			log::add($fichierLog, 'debug', '| 1ère demande de Token');
            return $this->getToken($fichierLog);
		}

		if ($this->access_token && time() >= ($this->token_expiration))
		{
            log::add($fichierLog, 'debug', '| Token expiré => renouvellement');
            return $this->refreshToken($fichierLog);
        } else {
            log::add($fichierLog, 'debug', '| Token encore en cours');
        }
	}


    private function getToken($fichierLog = 'myToyota')
    {
        //Stage 1 - Callbacks
        $url = $this::AUTHENTICATE_URL;
        $method = 'POST';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'x-correlationid: B10AD742-22D0-4211-8B25-B213BE9A8A00',
            'Cookie: route=e8e8b55de08efd3c4b34265c0069d319',
        ];
                      
        $result1 = $this->_request($url, $method, null, $headers);
        $data1 = $result1->body;
        log::add($fichierLog, 'debug', '| Result getToken() myToyota - Stage 1 - body : ' . $data1);
        
        
        //Stage 2 - set Username
        $url = $this::AUTHENTICATE_URL;
        $method = 'POST';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'x-correlationid: BDD02A22-CD76-4877-90A9-196EDA5DC695',
            'content-type: application/json',
        ];
                      
        $result2 = $this->_request($url, $method, $data1, $headers);
        log::add($fichierLog, 'debug', '| Result getToken() myToyota - Stage 2 - body : ' . $result2->body);
        $data2 = $result2->body;
        $data2 = json_decode($data2, true);
        $data2['callbacks'][0]['input'][0]['value'] = $this->username;
        $data2 = json_encode($data2);

        
        //Stage 3 - set Password
        $url = $this::AUTHENTICATE_URL;
        $method = 'POST';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'x-correlationid: BDD02A22-CD76-4877-90A9-196EDA5DC695',
            'content-type: application/json',
        ];
                       
        $result3 = $this->_request($url, $method, $data2, $headers);
        log::add($fichierLog, 'debug', '| Result getToken() myToyota - Stage 3 - body : ' . $result3->body);
        $data3 = $result3->body;
        $data3 = json_decode($data3, true);
        $data3['callbacks'][0]['input'][0]['value'] = $this->password;
        $data3 = json_encode($data3);


        //Stage 4 - get tokenId
        $url = $this::AUTHENTICATE_URL;
        $method = 'POST';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'x-correlationid: BDD02A22-CD76-4877-90A9-196EDA5DC695',
            'content-type: application/json',
        ];
                       
        $result4 = $this->_request($url, $method, $data3, $headers);
        $data4 = json_decode($result4->body, true);
        $tokenId = $data4['tokenId'];
        log::add($fichierLog, 'debug', '| Result getToken() myToyota - Stage 4 - tokenId : ' . $tokenId);


        //Stage 5 - get authorizationCode
        $url = $this::AUTHORIZE_URL;
        $method = 'GET';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'x-correlationid: 0F34C246-11F3-4584-AB13-0EA5DA96CB41',
            'Cookie: iPlanetDirectoryPro='.$tokenId,
        ];
                       
        $result5 = $this->_request($url, $method, null, $headers);
        $data5 = $result5->headers;
        if (preg_match('/code=([^&\s]+)/', $data5, $matches))
        {
            $authorizationCode = $matches[1];
        }
        log::add($fichierLog, 'debug', '| Result getToken() myToyota - Stage 5 - authorizationCode : ' . $authorizationCode);


        //Stage 6 - exchangeToken
        $url = $this::ACCESS_TOKEN_URL;
        $method = 'POST';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'authorization: Basic b25lYXBwOm9uZWFwcA==',
            'x-correlationid: E422A08C-1A04-415E-BB08-2386EE06CF90',
            'content-type: application/x-www-form-urlencoded',
        ];
        $data = array(
            'client_id' => 'oneapp',
            'code' => $authorizationCode,
            'redirect_uri' => 'com.toyota.oneapp:/oauth2Callback',
            'grant_type' => 'authorization_code',
            'code_verifier' => 'plain',            
        );
        $data = http_build_query($data);
                
        $result6 = $this->_request($url, $method, $data, $headers);
        log::add($fichierLog, 'debug', '| Result getToken() myToyota - Stage 6 - body : ' . $result6->body);
        $data6 = json_decode($result6->body, true);
        
        //$this->uuid = '';
      	$expToken = explode('.', $data6['access_token']);
      
      	
        $uuid = json_decode(base64_decode(strtr($expToken[1], '-_', '+/,'), true));
      	log::add($fichierLog, 'debug', '| uuid: ' . $uuid->{'uuid'});
        $this->uuid = $uuid->{'uuid'};
        $this->access_token = $data6['access_token'];
        $this->refresh_token = $data6['refresh_token'];
        $this->token_expiration = time() + $data6['expires_in'];
        $this->saveToken();
        log::add($fichierLog, 'debug', '| Result getToken() myToyota : Token sauvegardé');

    }


    private function refreshToken($fichierLog = 'myToyota')
    {
            $url = $this::ACCESS_TOKEN_URL;
            $method = 'POST';
            $headers = $this->_setDefaultHeaders();
            $headers = [
                'authorization: Basic b25lYXBwOm9uZWFwcA==',
                'x-correlationid: E422A08C-1A04-415E-BB08-2386EE06CF90',
                'content-type: application/x-www-form-urlencoded',
            ];
            $data = array(
                'client_id' => 'oneapp',
                'code' => $authorizationCode,
                'redirect_uri' => 'com.toyota.oneapp:/oauth2Callback',
                'grant_type' => 'refresh_token',
                'code_verifier' => 'plain',
                'refresh_token' => $this->refresh_token,            
            );
            $data = http_build_query($data);
                    
            $result1 = $this->_request($url, $method, $data, $headers);
            log::add($fichierLog, 'debug', '| Result refreshToken() myToyota : ' . $result1->body);
            $data1 = json_decode($result1->body, true);

            $expToken = explode('.', $data1['access_token']);
      
      	
            $uuid = json_decode(base64_decode(strtr($expToken[1], '-_', '+/,'), true));
            log::add($fichierLog, 'debug', '| uuid: ' . $uuid->{'uuid'});
    

            $this->uuid = $uuid->{'uuid'};
            $this->access_token = $data1['access_token'];
            $this->refresh_token = $data1['refresh_token'];
            $this->token_expiration = time() + $data1['expires_in'];
            $this->saveToken();
            log::add($fichierLog, 'debug', '| Result refreshToken() myToyota : Token sauvegardé');
    }

	private function _setHeadersUpdate()   {				//Define headers update data
        $headers = [
            'content-type: application/json',
            'x-api-key: tTZipv6liF74PwMfk9Ed68AQ0bISswwf3iHQdqcF',
            'x-guid: ' . $this->uuid,
            'guid: ' . $this->uuid,
            'authorization: Bearer ' . $this->access_token,
            'x-channel: ONEAPP',
            'x-brand: ' . $this->brand,
            'user-agent: okhttp/4.10.0',
            'vin: ' . $this->vin,
        ];
        return $headers;
	}
    
    public function getDevice($fichierLog='myToyota')
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_GUID_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function getLocationEndPoint($fichierLog='myToyota')
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_LOCATION_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }
    
    public function getRemoteStatusEndPoint($fichierLog='myToyota') // update device 
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_GLOBAL_REMOTE_STATUS_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function getTelemetryEndPoint($fichierLog='myToyota') // update device 
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_TELEMETRY_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function getRemoteClimateStatus($fichierLog='myToyota')  // update device 
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_REMOTE_CLIMATE_STATUS;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function getClimateRemoteStatus()
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_CLIMATE_REMOTE_STATUS;
        $headers = $this->_setHeadersUpdate();
        log::add('myToyota', 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function getTripsEndpoint($from = "xxx", $to = "xxx", $route = false, $summary = true, $limit = 0, $offset = 0, $fichierLog='myToyota')
    {
        $this->_checkAuth($fichierLog);
        if ($to== 'xxx'){
            $to = date('Y-m-d');
        }
        if ($from== 'xxx'){
            $from = date('Y-m-d', strtotime('-7 days', strtotime($to)));
        }
        //$to = date('Y-m-d', now());
		$url = $this::API_BASE_URL . sprintf($this::VEHICLE_TRIPS_ENDPOINT, $from, $to, $route, $summary, $limit, $offset)  ;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }


    public function remoteCommande($commande, $fichierLog='myToyota')  // telecommande de quoi? Portes + ... = {door-lock, door-unlock, engine-start, engine-stop, hazard-on, hazard-off, power-window-on, power-window-off, 
                                                        //ac-settings-on, sound-horn, buzzer-warning, find-vehicle, ventilation-on, trunk-lock, trunk-unlock}
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_GLOBAL_REMOTE_COMMAND;
        $headers = $this->_setHeadersUpdate();
      	$headers[] = [ 'x-correlationid: D7F048C1-F0A1-4920-AA37-264C8A1FB4A3' ];
        
        $data = json_encode(array('command' => $commande));
        //$data = http_build_query($data);
        log::add($fichierLog, 'debug', '| Url : '. $url . ' avec la commande : ' . $commande);      
		$return = $this->_request($url, 'POST', $data, $headers);
		return $return;
    }

    public function remoteClimate($commande, $fichierLog='myToyota')  // telecommande de quoi? Clim
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_REMOTE_CLIMATE_CONTROL;
        $headers = $this->_setHeadersUpdate();
        $headers[] = [ 'x-correlationid: D7F048C1-F0A1-4920-AA37-264C8A1FB4A3' ];
        
        $data = json_encode(array('command' => $commande));
        //$data = http_build_query($data);
        log::add($fichierLog, 'debug', '| Url : '. $url . ' avec la commande : ' . $commande);      
		$return = $this->_request($url, 'POST', $data, $headers);
		return $return;
    }

    public function statusHealth($fichierLog='myToyota') // santé du véhicule (huile + warning ...)
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_HEALTH_STATUS_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function historiqueNotification($fichierLog='myToyota')
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_NOTIFICATION_HISTORY_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function remoteElectric($fichierLog='myToyota') // à voir avec un véhicule électrique
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_GLOBAL_REMOTE_ELECTRIC_STATUS_ENDPOINT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function historiqueService($fichierLog='myToyota')
    {
        $this->_checkAuth();
		$url = $this::API_BASE_URL . $this::VEHICLE_SERVICE_HISTORY_ENDPONT;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function remoteClimateSettings($fichierLog='myToyota') // A tester sur véhicule capable
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_REMOTE_CLIMATE_SETTING;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }

    public function remoteACReservation($fichierLog='myToyota') // A tester sur véhicule capable
    {
        $this->_checkAuth($fichierLog);
		$url = $this::API_BASE_URL . $this::VEHICLE_REMOTE_AC_RESERVATION;
        $headers = $this->_setHeadersUpdate();
        log::add($fichierLog, 'debug', '| Url : '. $url);      
		$return = $this->_request($url, 'GET', null, $headers);
		return $return;
    }



    //    const VEHICLE_TRIPS_ENDPOINT = '/v1/trips?from=%s&to=%s&route=%s&summary=%s&limit=%s&offset=%s';

    /* test 
    VEHICLE_ASSOCIATION_ENDPOINT => 403
    VEHICLE_GLOBAL_REMOTE_ELECTRIC_STATUS_ENDPOINT => "description":"Extended capability either econnectVehicleStatusCapable or stellantisVehicleStatusCapable is not present for the VIN"
     
    
dm.n

/*  Remote commande

        DefaultConstructorMarker defaultConstructorMarker2 = null;
        DOOR_LOCK_17P = new RemoteCommand("DOOR_LOCK_17P", 2, "door-lock", aVar2, i11, defaultConstructorMarker2);
        DOOR_UNLOCK_17P = new RemoteCommand("DOOR_UNLOCK_17P", 3, "door-unlock", aVar, i10, defaultConstructorMarker);
        HAZARD_ON_17P = new RemoteCommand("HAZARD_ON_17P", 4, "hazard-on", aVar2, i11, defaultConstructorMarker2);
        HAZARD_OFF_17P = new RemoteCommand("HAZARD_OFF_17P", 5, "hazard-off", aVar, i10, defaultConstructorMarker);
        SOUND_HORN = new RemoteCommand("SOUND_HORN", 6, "sound-horn", aVar2, i11, defaultConstructorMarker2);
        HEAD_LIGHTS = new RemoteCommand("HEAD_LIGHTS", 7, "headlight-on", aVar, i10, defaultConstructorMarker);


    public enum ActionType {
    DoorLock("door-lock"),
    DoorUnlock("door-unlock"),
    Open("open"),
    Close("close"),
    Activate("activate"),
    Deactivate("deactivate"),
    Navigate("navigate"),
    CarLocationNavigate("car-location-navigate"),
    ViewMore("view more"),
    Display(Constants.ScionAnalytics.MessageType.DISPLAY_NOTIFICATION),
    ClickBar("click-bar"),
    ViewDestinations("view my destinations"),
    Home("home"),
    Work("work"),
    Favourites("favourites"),
    SendToCar("send-to-car"),
    Remove("remove"),
    Save("save"),
    Favourite("favourite"),
    FavouriteLimitReached("favourite-limit-reached"),
    Skip("skip"),
    Start("start"),
    Cancel("cancel"),
    CancelButton("cancel-button"),
    MileageSubmit("mileage-submit"),
    MileageReload("mileage-reload"),
    MileageRestart("mileage-restart"),
    BlacklistedContactDealer("blacklisted-contact-dealer"),
    BlacklistedCancel("blacklisted-cancel"),
    ActivateServices("activate-services"),
    SaveUpcomingCharge("save-upcoming-charge"),
    Overview("overview"),
    Trips("trips"),
    Search(FirebaseAnalytics.Event.SEARCH),
    OpenSearchFilters("open-search-filters"),
    CarLocationCenter("car-location-center"),
    UserLocationCenter("user-location-center"),
    ChargingStationDetails("charging-station-details"),
    CarLocationDetails("car-location-details"),
    Unlock("unlock"),
    Share(FirebaseAnalytics.Event.SHARE),
    CarLocationShare("car-location-share"),
    SeePrices("see-prices"),
    SeeMyReview("see-my-review"),
    StartClimate("start-climate"),
    StopClimate("stop-climate"),
    HazardLights("hazard-lights"),
    TurnHeadlights("turn-headlights"),
    WindowsClose("windows-close"),
    Horn("horn"),
    TrunkLock("trunk-lock"),
    Buzzer("buzzer"),
    TrunkUnlock("trunk-unlock"),
    AccessSharing("access-sharing"),
    ChargingHistory("charging-history"),
    StartCharging("start-charging"),
    StopCharging("stop-charging"),
    OverrideSmartCharging("override-smart-charging"),
    SmartCharging("smart-charging"),
    SeeHowItWorks("see-how-it-works"),
    SelectChargeState("select-charge-state"),
    SelectChargeTime("select-charge-time"),
    SelectCharger("select-charger"),
    ManageRfid("manage-rfid"),
    AddRfid("add-rfid"),
    SaveNewFrid("save-new-rfid"),
    ErrorRetry("error-retry"),
    SeeUseAccess("see-user-access"),
    AddUsers("add-users"),
    Accept("accept"),
    Decline("decline");



*/
}
?>