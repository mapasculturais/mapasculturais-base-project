<?php

use Curl\Curl;
use MapasCulturais\App;
use MapasCulturais\Entities\User;

class GovBrStrategy extends OpauthStrategy
{

	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = ['client_id',  'auth_endpoint', 'token_endpoint', 'dic_agent_fields_update'];
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
	public function request()
	{
		$_SESSION['govbr-state'] = md5($this->strategy['state_salt'].time());

		$url = $this->strategy['auth_endpoint'];
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code',
			'scope' => $this->strategy['scope'],
			'state' => $_SESSION['govbr-state'],
			'code_challenge' => $this->strategy['code_challenge'],
			'code_challenge_method' => $this->strategy['code_challenge_method'],
		);

		foreach ($this->optionals as $key) {
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}

		$this->clientGet($url, $params);
	}

	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback()
	{
		$app = App::i();

		if ((array_key_exists('code', $_GET) && !empty($_GET['code'])) && (array_key_exists("state", $_GET) && $_GET['state'] == $_SESSION['govbr-state'])) {
			
			$code = $_GET['code'];
		
			$url = $this->strategy['token_endpoint'];
			$params = array(
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' => $this->strategy['redirect_uri'],
				'code_verifier' => $this->strategy['code_verifier'],
			);

			$token = base64_encode("{$this->strategy['client_id']}:{$this->strategy['client_secret']}");
			$curl = new Curl;
			$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
			$curl->setHeader('Authorization', "Basic {$token}");

			$curl->post($url, $params);
			$curl->close();
			$response = $curl->response;

			$results = json_decode($response);

			if (!empty($results) && !empty($results->id_token)) {

				/** @var stdClass $userinfo */
				$userinfo = $this->userinfo($results->id_token);
				$userinfo->access_token =  $results->access_token;
				$userinfo->cpf =  $userinfo->sub;

				$exp_name = explode(" ", $userinfo->name);
			
				$info = [
					'name' => $exp_name[0],
					'cpf' => $userinfo->sub,
					'email' => $userinfo->email_verified ? $userinfo->email : null,
					'phone_number' => ($userinfo->phone_number_verified ?? false) ? $userinfo->phone_number : null,
					'full_name' => $userinfo->name,
					'dic_agent_fields_update' => $this->strategy['dic_agent_fields_update']
				];
				
				$this->auth = array(
					'uid' => $userinfo->jti,
					'credentials' => array(
						'token' => $results->id_token,
						'expires' => $userinfo->exp
					),
					'raw' => $userinfo,
					'info' => $info,
					'applySeal' => $this->strategy['applySealId']
				);
		
			
				$this->callback();
			} else {
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
					)
				);
				$this->errorCallback($error);
			}
		} else {
			$error = array(
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);

			$this->errorCallback($error);
		}
	}

	/**
	 * @param string $id_token 
	 * @return array Parsed JSON results
	 */
	private function userinfo($id_token)
	{
		$exp = explode(".", $id_token);
		return json_decode(base64_decode($exp[1]));
	}

	public static function checkFileType($filename)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimetype = finfo_file($finfo, $filename);
		if ($mimetype == 'image/jpg' || $mimetype == 'image/jpeg' || $mimetype == 'image/gif' || $mimetype == 'image/png') {
			$is_image = true;
		} else {
			$is_image = false;
		}

		return $is_image;
	}

	public static function getFile($owner, $url, $token){

		$curl = new Curl;
		$curl->setHeader('Authorization', "Bearer {$token}");
		$curl->get($url);
		$curl->close();
		$response = $curl->response;

		if(mb_strpos($response, 'não encontrada')){
			return;
		}
		
		$tmp = tempnam("/tmp", "");
		$handle = fopen($tmp, "wb");
		fwrite($handle,$response);
		fclose($handle);

		if(!self::checkFileType($tmp)){
			return;
		}

		$class_name = $owner->fileClassName;

		$basename = md5(time()).".jpg";

		$file = new $class_name([
			"name" => $basename,
			"type" => mime_content_type($tmp),
			"tmp_name" => $tmp,
			"error" => 0,
			"size" => filesize($tmp)
		]);

		$file->group = "avatar";
		$file->owner = $owner;
		$file->save(true);
	}

	public static function newAccountCheck($response)
	{
		$app = App::i();

		$user = null;
		$cpf = self::mask($response['auth']['raw']['sub'],'###.###.###-##');
		$metadataFieldCpf = $app->config['auth.config']['metadataFieldCPF'];
		
		$agent_meta = null;
		if($am = $app->repo('AgentMeta')->findOneBy(["key" => $metadataFieldCpf, "value" => $cpf])){
			$agent_meta = $am;
		}elseif($am = $app->repo('AgentMeta')->findOneBy(["key" => $metadataFieldCpf, "value" => $response['auth']['raw']['sub']])){
			$agent_meta = $am;
		}

        if($agent_meta){

			$agent = $agent_meta->owner;
			$user = $agent->user;

			if(!$agent->isUserProfile){
				$user = new User();
				$user->authProvider = $response['auth']['provider'];
				$user->authUid = $response['auth']['uid'];
				$user->email = $response['auth']['info']['email'];
	
				$app->em->persist($user);

				$agent->userId = $user->id;
				$agent->save(true);
				$agent->refresh();

				$user->profile = $agent;
				$user->save(true);
			}
		}

		return $user;

	}

	public static function applySeal($user, $response){
		$app = App::i();

		$agent = $user->profile;
		$sealId = $response['auth']['applySeal'];

		if($sealId){
			$app->disableAccessControl();

			$seal = $app->repo('Seal')->find($sealId);
			$relations = $agent->getSealRelations();

			$has_new_seal = false;
			foreach($relations as $relation){
				if($relation->seal->id == $seal->id){
					$has_new_seal = true;
					break;
				}
			}

			if(!$has_new_seal){
				$agent->createSealRelation($seal);
			}
			
			$app->enableAccessControl();

		}
	}

	public static function verifyUpdateData($user, $response)
	{
		$app = App::i();

		$auth_data = $response['auth']['info'];
		$userinfo = (object) $response['auth']['raw'];

		$app->hook("entity(Agent).get(lockedFields)", function(&$lockedFields) use ($app){
			$config = $app->config['auth.config']['strategies']['govbr'];
			$fieladsUnlocked = array_keys($config['dic_agent_fields_update']);
			$lockedFields = array_diff($lockedFields, $fieladsUnlocked);
		});
		
		$app->disableAccessControl();
		foreach($auth_data['dic_agent_fields_update'] as $entity_key => $ref){
			if($user->profile->$entity_key != $auth_data[$ref]){
				if(($entity_key == "name") && ($user->profile->name == "" || $user->profile->name === "Meu Nome")){
					$user->profile->$entity_key = $auth_data[$ref];
				}else{
					$user->profile->$entity_key = $auth_data[$ref];
				}
			}
		}
		$user->profile->save(true);
		$app->enableAccessControl();

		if($allAgents = $app->repo("Agent")->findBy(['userId' => $user->id, '_type' => 1])){
			
			if(count($allAgents) == 1){
				$_agent = $allAgents[0];
				$_agent->setAsUserProfile();
			}
		}
		
		self::getFile($user->profile, $userinfo->picture, $userinfo->access_token);
	}

	public static function  mask($val, $mask) {
        if (strlen($val) == strlen($mask)) return $val;
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if($mask[$i] == '#') {
                if(isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if(isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }
}
