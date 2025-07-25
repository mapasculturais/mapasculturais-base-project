<?php
/**
 * Login Cidadão strategy for Opauth
 * based on https://github.com/secultce/login-cidadao
 *
 * More information on Opauth: http://opauth.org
 * @author 		 Ben Rainir <benrainir@gmail.com>
 * @link         http://opauth.org
 * @package      Opauth.LoginCidadaoStrategy
 * @license      GNU Affero General Public License v3.0 License
 */

class LoginCidadaoStrategy extends OpauthStrategy{

	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = ['client_id', 'client_secret', 'auth_endpoint', 'token_endpoint', 'userinfo_endpoint'];
	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = ['redirect_uri', 'scope', 'response_type', 'register_form_action', 'register_form_method'];
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = ['redirect_uri' => '{complete_url_to_strategy}oauth2callback'];

	/**
	 * Auth request
	 */
	public function request(){
		$url = $this->strategy['auth_endpoint'];
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'client_secret' => $this->strategy['client_secret'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code',
			'scope' => $this->strategy['scope']
		);
		foreach ($this->optionals as $key){
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}
		
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback(){
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$code = $_GET['code'];
			$url = $this->strategy['token_endpoint'];
			$params = array(
				'code' => $code,
				'client_id' => $this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri' => $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code'
			);
			$response = $this->serverPost($url, $params, null, $headers);
			
			$results = json_decode($response);
			
			if (!empty($results) && !empty($results->access_token)){
				$userinfo = $this->userinfo($results->access_token);
				
				$this->auth = array(
					'uid' => $userinfo['id'],
					'info' => array(),
					'credentials' => array(
						'token' => $results->access_token,
						'expires' => date('c', time() + $results->expires_in)
					),
					'raw' => $userinfo
				);
				if (!empty($results->refresh_token))
				{
					$this->auth['credentials']['refresh_token'] = $results->refresh_token;
				}
				
				$this->mapProfile($userinfo, 'name', 'info.name');
				$this->mapProfile($userinfo, 'email', 'info.email');
				$this->mapProfile($userinfo, 'given_name', 'info.first_name');
				$this->mapProfile($userinfo, 'family_name', 'info.last_name');
				$this->mapProfile($userinfo, 'picture', 'info.image');
				
				$this->callback();
			}
			else{
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
						'headers' => $headers
					)
				);
				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
	/**
	 * Queries Google API for user info
	 *
	 * @param string $access_token 
	 * @return array Parsed JSON results
	 */
	private function userinfo($access_token){
		$userinfo = $this->serverGet($this->strategy['userinfo_endpoint'], array('access_token' => $access_token), null, $headers);
		if (!empty($userinfo)){
			return $this->recursiveGetObjectVars(json_decode($userinfo));
		}
		else{
			$error = array(
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query for user information',
				'raw' => array(
					'response' => $userinfo,
					'headers' => $headers
				)
			);
			$this->errorCallback($error);
		}
	}
}
