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
    const VEHICLE_GLOBAL_REMOTE_STATUS_ENDPOINT = '/v1/global/remote/status';
    const VEHICLE_GLOBAL_REMOTE_ELECTRIC_STATUS_ENDPOINT = '/v1/global/remote/electric/status';
    const VEHICLE_TELEMETRY_ENDPOINT = '/v3/telemetry';
    const VEHICLE_NOTIFICATION_HISTORY_ENDPOINT = '/v2/notification/history';
    const VEHICLE_TRIPS_ENDPOINT = '/v1/trips?from=%s&to=%s&route=%s&summary=%s&limit=%s&offset=%s';
    const VEHICLE_SERVICE_HISTORY_ENDPONT = '/v1/servicehistory/vehicle/summary';
		
    //Parameters
    private $username;
    private $password;
    private $vin;
    private $brand;
    private $access_token;
    private $token_expiration;
    private $refresh_token;
    private $uuid;
    private $timeout;


    public function  __construct($username, $password, $vin, $brand)
    {
        if (!$username || !$password  || !$vin || !$brand) {
            throw new \Exception('Config parameters missing');
        }

		$this->username = $username;
		$this->password = $password;
        $this->vin = $vin;
        $this->brand = $brand;
        $this->timeout = 60;

        if (file_exists(dirname(__FILE__).'/../data/myToyota_token.json')) {
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
	
		$array = json_decode(file_get_contents(dirname(__FILE__).'/../data/myToyota_token.json'), true);
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
		file_put_contents(dirname(__FILE__).'/../data/myToyota_token.json', json_encode($array));
	}


    private function _checkAuth()
    {
        if (!$this->access_token)
		{
			log::add('myToyota', 'debug', '| 1ère demande de Token');
            return $this->getToken();
		}

		if ($this->access_token && time() >= ($this->token_expiration))
		{
            log::add('myToyota', 'debug', '| Token expiré => renouvellement');
            return $this->refreshToken();
        } else {
            log::add('myToyota', 'debug', '| Token encore en cours');
        }
	}


    private function getToken()
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
        log::add('myToyota', 'debug', '| Result getToken() myToyota - Stage 1 - body : ' . $data1);
        
        
        //Stage 2 - set Username
        $url = $this::AUTHENTICATE_URL;
        $method = 'POST';
        $headers = $this->_setDefaultHeaders();
        $headers = [
            'x-correlationid: BDD02A22-CD76-4877-90A9-196EDA5DC695',
            'content-type: application/json',
        ];
                      
        $result2 = $this->_request($url, $method, $data1, $headers);
        log::add('myToyota', 'debug', '| Result getToken() myToyota - Stage 2 - body : ' . $result2->body);
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
        log::add('myToyota', 'debug', '| Result getToken() myToyota - Stage 3 - body : ' . $result3->body);
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
        log::add('myToyota', 'debug', '| Result getToken() myToyota - Stage 4 - tokenId : ' . $tokenId);


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
        log::add('myToyota', 'debug', '| Result getToken() myToyota - Stage 5 - authorizationCode : ' . $authorizationCode);


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
        log::add('myToyota', 'debug', '| Result getToken() myToyota - Stage 6 - body : ' . $result6->body);
        $data6 = json_decode($result6->body, true);
        
        //$this->uuid = '';
      	$expToken = explode('.', $data6['access_token']);
      
      	
        $uuid = json_decode(base64_decode(strtr($expToken[1], '-_', '+/,'), true));
      	log::add('myToyota', 'debug', '| uuid: ' . $uuid->{'uuid'});
        $this->uuid = $uuid->{'uuid'};
        $this->access_token = $data6['access_token'];
        $this->refresh_token = $data6['refresh_token'];
        $this->token_expiration = time() + $data6['expires_in'];
        $this->saveToken();
        log::add('myToyota', 'debug', '| Result getToken() myToyota : Token sauvegardé');

    }


    private function refreshToken()
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
            log::add('myToyota', 'debug', '| Result refreshToken() myToyota : ' . $result1->body);
            $data1 = json_decode($result1->body, true);

            $expToken = explode('.', $data1['access_token']);
      
      	
            $uuid = json_decode(base64_decode(strtr($expToken[1], '-_', '+/,'), true));
              log::add('myToyota', 'debug', '| uuid: ' . $uuid->{'uuid'});
    

            $this->uuid = $uuid->{'uuid'};
            $this->access_token = $data1['access_token'];
            $this->refresh_token = $data1['refresh_token'];
            $this->token_expiration = time() + $data1['expires_in'];
            $this->saveToken();
            log::add('myToyota', 'debug', '| Result refreshToken() myToyota : Token sauvegardé');
    }

	private function _setHeadersUpdate()   {				//Define headers update data
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
            'accept-api-version: resource=2.0, protocol=1.0', //fin du default header
            'authorization: Bearer ' . $this->access_token,
            'x-device-timezone: CEST',
            'x-correlationid: 7683DC30-D4DA-4FEC-850E-F3557A7DCEF4',
            'guid: ' . $this->uuid,
            'x-guid: ' . $this->uuid,
            'x-locale: fr-FR',
            'x-user-region: FR',
            'x-api-key: tTZipv6liF74PwMfk9Ed68AQ0bISswwf3iHQdqcF',
            'vin: ' . $this->vin,
        ];
        return $headers;
	}

    public function getDevice()
    {
        $this->_checkAuth();
		//$headers = $this->_setDefaultHeaders();
        $headers = $this->_setHeadersUpdate();
        //$headers = [
        //    'vin :' . $this->vin,
        //];
        
        log::add('myToyota', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		$return1 = json_decode($this->_request($this::API_BASE_URL . $this::VEHICLE_GUID_ENDPOINT, 'GET', null, $headers));
		$return2 =  json_decode($this->_request($this::API_BASE_URL . $this::VEHICLE_LOCATION_ENDPOINT, 'GET', null, $headers));
        log::add('myToyota', 'debug', '| VEHICLE_GUID_ENDPOINT : ' . str_replace('\n','',json_encode($return1)));
        log::add('myToyota', 'debug', '| VEHICLE_LOCATION_ENDPOINT : ' . str_replace('\n','',json_encode($return2)));
    }

    public function getLocationEndPoint()
    {
        $this->_checkAuth();
		$headers = $this->_setHeadersUpdate();
//		log::add('myToyota', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
//		return $this->_request($this::API_BASE_URL . $this::VEHICLE_LOCATION_ENDPOINT, 'GET', null, $headers);
    }
    
}
?>