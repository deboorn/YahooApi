<?php 

	/**
	 * YahooApi PHP 5 class is a simple wrapper for the YahooApi with OAuth. 
	 * @author Daniel Boorn - daniel.boorn@gmail.com
	 * @copyright Daniel Boorn - daniel.boorn@gmail.com
	 * @license Apaache 2.0 License, Use Code At Own Risk, All Rights Reserved
	 * @requires PHP OAuth Extension
	 */
	class YahooApi{
		
		private $settings;
		
		private $key;
		private $secret;
		private $verifierStr;
		
		private $accessTokenObj;
		private $requestTokenObj;
		
		private $apiUrls = array(
			'getRequestToken'=>'https://api.login.yahoo.com/oauth/v2/get_request_token',
			'getToken'=>'https://api.login.yahoo.com/oauth/v2/get_token',
			
		);
		
		
		/**
		 * class constructor
		 * @param array $settings
		 */
		public function __construct($settings){
			$this->settings = (object) $settings;
			$this->key = $this->settings->key;
			$this->secret = $this->settings->secret;
			$this->verifierStr = $this->settings->verifier;
			$this->authenticate();
		}
		
		/**
		 * magic getter function
		 * @param string $property
		 * @return object
		 */
		public function __get($property){
			if (method_exists($this, 'get'.ucfirst($property))){
				return call_user_func(array($this, 'get'.ucfirst($property)));
			}		
		}
		
		/**
		 * debug var dump
		 * @param object $object
		 */
		public function debug($object){
			if($this->settings->debug){
				echo "<pre>";
				var_dump($object);
			}
		}
		
		public function getJson($url){
			try{	
				$o = new OAuth($this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				if($o->fetch($url,array('format'=>'json'))){
					return json_decode($o->getLastResponse());
				}
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
		/**
		 * gets verifier value
		 * @return string
		 */
		protected function getVerifier(){
			if($this->verifierStr) return $this->verifierStr;
			throw new Exception('Verifier String Now Found or NULL. Yahoo Request Verifier Must be Supplied/Saved to YahooApi Class');
		}
		
		/**
		 * gets access token from file or from object
		 * @throws Exception
		 * @return object
		 */
		protected function getAccessToken(){
			if($this->accessTokenObj) return $this->accessTokenObj;
			if(file_exists($this->accessTokenFileName)){
				try{
					$this->accessTokenObj = json_decode(file_get_contents($this->accessTokenFileName));
				}catch(Exception $e){
					throw new Exception($e);
				}
			}
			return $this->accessTokenObj;	
		}
		
		/**
		 * gets access token file name
		 * @return string
		 */
		protected function getAccessTokenFileName(){
			return "_yahoo_oauth_{$this->key}.accesstoken";
		}
		
		/**
		 * gets request token file name
		 * @return string
		 */
		protected function getRequestTokenFileName(){
			return "_yahoo_oauth_{$this->key}.requesttoken";
		}
		
		/**
		 * gets request token from file or class object
		 * @throws Exception
		 * @return object
		 */
		protected function getRequestToken(){
			if($this->requestTokenObj) return $this->requestTokenObj;
			if(file_exists($this->requestTokenFileName)){
				try{
					$this->requestTokenObj = json_decode(file_get_contents($this->requestTokenFileName));
				}catch(Exception $e){
					throw new Exception($e);
				}
			}
			return $this->requestTokenObj;
		}
		
		/**
		 * saves request token response array to file
		 * @param array $r
		 */
		protected function saveRequestToken($r){
			$fileName = $this->requestTokenFileName;
			file_put_contents($fileName,json_encode($r));
		}
		
		/**
		 * saves access token reponse array to file
		 * @param array $r
		 */
		protected function saveAccessToken($r){
			$fileName = $this->accessTokenFileName;
			file_put_contents($fileName,json_encode($r));
		}
		
		/**
		 * redirect to url
		 * @param string $url
		 */
		protected function redirect($url){
			header("Location: {$url}");
		}

		/**
		 * obtains a request token information from api, saves token object to file, redirects for verifier
		 */
		protected function obtainRequestToken(){
			$o = new OAuth($this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
			$o->enableDebug();

			try{
				$r = $o->getRequestToken($this->apiUrls['getRequestToken'], 'oob');
				$this->saveRequestToken($r);
				$this->redirect($r['xoauth_request_auth_url']);
			}catch( OAuthException $e ) {
				$this->debug($e);
				die($e->getMessage());
			}
						
		}
		
		/**
		 * obtains access token information from api, saves token object to file
		 */
		protected function obtainAccessToken(){
			try{
				$o = new OAuth($this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
				$o->setToken($this->requestToken->oauth_token, $this->requestToken->oauth_token_secret);
				$r = $o->getAccessToken($this->apiUrls['getToken'], NULL, $this->verifier);
      			$this->saveAccessToken($r);
			}catch( OAuthException $e){
				$this->debug($e);
				die($e->getMessage());
			}
		}
		
		/**
		 * handles obtaining request and access tokens from api
		 */
		public function authenticate(){
			//redirect user to get request token before proceeding any further
			if(!$this->accessToken && !$this->requestToken){
				$this->obtainRequestToken();
			}else if(!$this->accessToken){//obtain access token
				$this->obtainAccessToken();
			}
		}
		
	}


?>