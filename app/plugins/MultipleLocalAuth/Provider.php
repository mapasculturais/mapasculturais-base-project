<?php
namespace MultipleLocalAuth;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;
use Mustache\Mustache;
use Respect\Validation\Validator;

class Provider extends \MapasCulturais\AuthProvider {
    protected $opauth;
    
    var $feedback_success   = false;
    var $feedback_msg       = '';
    var $triedEmail         = '';
    var $triedName          = '';
    
    public $register_form_action = '';
    public $register_form_method = 'POST';
    
    public static $passMetaName = 'localAuthenticationPassword';

    public static $recoverTokenMetadata     = 'recover_token';
    public static $recoverTokenTimeMetadata = 'recover_token_time';
    
    public static $accountIsActiveMetadata    = 'accountIsActive';
    public static $tokenVerifyAccountMetadata = 'tokenVerifyAccount';

    public static $loginAttempMetadata            = "loginAttemp";
    public static $timeBlockedloginAttempMetadata = "timeBlockedloginAttemp";
    
    function __construct ($config) {
        $app = App::i();
        
        $config += [
            'salt' => env('AUTH_SALT', null),
            'timeout' => env('AUTH_TIMEOUT', '24 hours'),

            'loginOnRegister' => env('AUTH_LOGIN_ON_REGISTER', false),
    
            'enableLoginByCPF' => env('AUTH_LOGIN_BY_CPF', true),
            'passwordMustHaveCapitalLetters' => env('AUTH_PASS_CAPITAL_LETTERS', true),
            'passwordMustHaveLowercaseLetters' => env('AUTH_PASS_LOWERCASE_LETTERS', true),
            'passwordMustHaveSpecialCharacters' => env('AUTH_PASS_SPECIAL_CHARS', true),
            'passwordMustHaveNumbers' => env('AUTH_PASS_NUMBERS', true),
            'minimumPasswordLength' => env('AUTH_PASS_LENGTH', 6),
            'userMustConfirmEmailToUseTheSystem' =>  env('AUTH_EMAIL_CONFIRMATION', false),
    
            'google-recaptcha-secret' => env('GOOGLE_RECAPTCHA_SECRET', false),
            'google-recaptcha-sitekey' => env('GOOGLE_RECAPTCHA_SITEKEY', false),
    
            'sessionTime' => env('AUTH_SESSION_TIME', 7200), // int , tempo da sessao do usuario em segundos
            'numberloginAttemp' => env('AUTH_NUMBER_ATTEMPTS', 5), // tentativas de login antes de bloquear o usuario por X minutos
            'timeBlockedloginAttemp' => env('AUTH_BLOCK_TIME', 900), // tempo de bloqueio do usuario em segundos
    
            'metadataFieldCPF' => env('AUTH_METADATA_FIELD_DOCUMENT', 'documento'),
            'metadataFieldPhone' => env('AUTH_METADATA_FIELD_PHONE', 'telefone1'),

            'urlSupportChat' => env('AUTH_SUPPORT_CHAT', ''),
            'urlSupportEmail' => env('AUTH_SUPPORT_EMAIL', ''),
            'textSupportSite' => env('AUTH_SUPPORT_TEXT', ''),
            'urlSupportSite' => env('AUTH_SUPPORT_SITE', ''),
            'urlImageToUseInEmails' => env('AUTH_EMAIL_IMAGE'),

            'urlTermsOfUse' => env('LINK_TERMOS', $app->createUrl('auth', 'termos-e-condicoes')),
            'statusCreateAgent' => env('STATUS_CREATE_AGENT', Agent::STATUS_DRAFT),
            'strategies' => [
                'Facebook' => [
                    'visible' => env('AUTH_FACEBOOK_CLIENT_ID', false),
                    'app_id' => env('AUTH_FACEBOOK_APP_ID', null),
                    'app_id' => env('AUTH_FACEBOOK_APP_ID', null),
                    'app_secret' => env('AUTH_FACEBOOK_APP_SECRET', null),
                    'scope' => env('AUTH_FACEBOOK_SCOPE', 'email'),
                ],
                'LinkedIn' => [
                    'visible' => env('AUTH_LINKED_CLIENT_ID', false),
                    'api_key' => env('AUTH_LINKEDIN_API_KEY', null),
                    'secret_key' => env('AUTH_LINKEDIN_SECRET_KEY', null),
                    'redirect_uri' => $app->getBaseUrl() . 'autenticacao/linkedin/oauth2callback',
                    'scope' => env('AUTH_LINKEDIN_SCOPE', 'r_emailaddress')
                ],
                'Google' => [
                    'visible' => env('AUTH_GOOGLE_CLIENT_ID', false),
                    'client_id' => env('AUTH_GOOGLE_CLIENT_ID', null),
                    'client_secret' => env('AUTH_GOOGLE_CLIENT_SECRET', null),
                    'redirect_uri' => $app->getBaseUrl() . 'autenticacao/google/oauth2callback',
                    'scope' => env('AUTH_GOOGLE_SCOPE', 'email profile'),
                ],
                'Twitter' => [
                    'visible' => env('AUTH_TWITTER_CLIENT_ID', false),
                    'app_id' => env('AUTH_TWITTER_APP_ID', null),
                    'app_secret' => env('AUTH_TWITTER_APP_SECRET', null),
                ],
                'govbr' => [
                    'visible' => env('AUTH_GOV_BR_ID', false),
                    'response_type' => env('AUTH_GOV_BR_RESPONSE_TYPE', 'code'),
                    'client_id' => env('AUTH_GOV_BR_CLIENT_ID', null),
                    'client_secret' => env('AUTH_GOV_BR_SECRET', null),
                    'scope' => env('AUTH_GOV_BR_SCOPE', null),
                    'redirect_uri' => env('AUTH_GOV_BR_REDIRECT_URI', null), 
                    'auth_endpoint' => env('AUTH_GOV_BR_ENDPOINT', null),
                    'token_endpoint' => env('AUTH_GOV_BR_TOKEN_ENDPOINT', null),
                    'nonce' => env('AUTH_GOV_BR_NONCE', null),
                    'code_verifier' => env('AUTH_GOV_BR_CODE_VERIFIER', null),
                    'code_challenge' => env('AUTH_GOV_BR_CHALLENGE', null),
                    'code_challenge_method' => env('AUTH_GOV_BR_CHALLENGE_METHOD', null),
                    'userinfo_endpoint' => env('AUTH_GOV_BR_USERINFO_ENDPOINT', null),
                    'state_salt' => env('AUTH_GOV_BR_STATE_SALT', null),
                    'applySealId' => env('AUTH_GOV_BR_APPLY_SEAL_ID', null),
                    'menssagem_authenticated' => env('AUTH_GOV_BR_MENSSAGEM_AUTHENTICATED','Usuário já se autenticou pelo GovBr'),
                    'dic_agent_fields_update' => env('AUTH_GOV_BR_DICT_AGENT_FIELDS_UPDATE','[]')
                ]
            ]
        ];
        parent::__construct($config);
    }

    function dump($x) {
        \Doctrine\Common\Util\Debug::dump($x);
    }
    
    function setFeedback($msg, $success = false) {
        $this->feedback_success = $success;
        $this->feedback_msg = $msg;
        return $success;
    }

    protected function usingSocialLogin() {
        return is_array($this->_config['strategies']) && count($this->_config['strategies']) > 0;
    }
    
    protected function _init() {

        $app = App::i();
        $config = $this->_config;

        $app->hook('GET(auth.passwordvalidationinfos)', function () use($config){
            
            $passwordRules = array(
                "passwordMustHaveCapitalLetters" => $config['passwordMustHaveCapitalLetters'],
                "passwordMustHaveLowercaseLetters" => $config['passwordMustHaveLowercaseLetters'],
                "passwordMustHaveSpecialCharacters" => $config['passwordMustHaveSpecialCharacters'],
                "passwordMustHaveNumbers" => $config['passwordMustHaveNumbers'],
                "minimumPasswordLength" => $config['minimumPasswordLength'],
            );

            $this->json(array("passwordRules"=>$passwordRules));
        });

        /**
         * @todo refatorar confirma-email
         */

        $app->hook('GET(auth.confirma-email)', function () use($app){
            $app = App::i();
            $token = $app->request->get('token');

            $usermeta = $app->repo("UserMeta")->findOneBy(array('key' => Provider::$tokenVerifyAccountMetadata, 'value' => $token));

            if (!$usermeta) {
               $errorMsg = i::__('Token inválidos', 'multipleLocal');                         
               $this->render('confirm-email',['msg'=>$errorMsg]);   
            }

            $user = $usermeta->owner;
            $user->setMetadata(Provider::$accountIsActiveMetadata, '1');

            $app->disableAccessControl();
            $user->saveMetadata(true);
            $app->enableAccessControl();
            $app->em->flush();
            $this->render('confirm-email');
        });

        $app->hook('POST(auth.adminchangeuseremail)',function () use ($app) {
            $new_email = $this->data['new_email'];
            $email = $this->data['email'];
 
            $user = $app->auth->getUserFromDB($email);

            // email exists? (case insensitive)
            $checkEmailExistsQuery = $app->em->createQuery("SELECT u FROM \MapasCulturais\Entities\User u WHERE LOWER(u.email) = :email");
            $checkEmailExistsQuery->setParameter('email', strtolower($new_email));
            $checkEmailExists = $checkEmailExistsQuery->getResult();

            if (!empty($checkEmailExists)) {
                $this->json (array("error"=>"Este endereço de email já está em uso"));
            }

            if (Validator::email()->validate($new_email)) {
                $user->email = $new_email;

                // save
                $app->disableAccessControl();
                $user->saveMetadata(true);
                $app->enableAccessControl();
                $user->save(true);
                $app->em->flush();

                $this->json (array("new_email"=>$new_email));
            } else {
                $this->json (array("error"=>"Informe um email válido"));
            }            
        });

        $app->hook('adminchangeuseremail', function ($userEmail) use($app){

            if(!$app->user->is('admin')) {
                return;
            }

            echo
            '
            <a class="btn btn-primary js-open-dialog" data-dialog="#admin-change-user-email" data-dialog-block="true">
                Alterar email para: '.$userEmail.'
            </a>

            <div id="admin-change-user-email" class="js-dialog" title="Alterar email">
                <label for="new-email">Novo email:</label><br>
                <input type="text" id="new-email" name="new-email" ><br>
                <input type="hidden" id="email-to-admin-set-email" value='.$userEmail.' />
                <button class="btn add" id="user-managerment-adminChangeEmail" > Atualizar </button>
            </div>
            ';
        });

        /* /refatorar/ */

        
        
        $config = $this->_config;
        
        $config['path'] = preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $app->createUrl('auth'));
        
        /****** INIT OPAUTH ******/
        
        if ($this->usingSocialLogin()){
            $opauth_config = [
                'strategy_dir' => PROTECTED_PATH . '/vendor/opauth/',
                'Strategy' => $config['strategies'],
                'security_salt' => $config['salt'],
                'security_timeout' => $config['timeout'],
                'path' => $config['path'],
                'callback_url' => $app->createUrl('auth','response')
            ];
            
            $opauth = new \Opauth($opauth_config, false );
            $this->opauth = $opauth;
        }
        

        // Register form config
        $this->register_form_action = $app->createUrl('auth', 'register');    
        if(isset($config['register_form'])){
            $this->register_form_action = $config['register_form']['action'];
            $this->register_form_method = $config['register_form']['method'];
        }

        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($config){
            $this->render('multiple-local', [ 'config' => $config ]);
        });

        $app->hook('GET(auth.register)', function () use($config){
            $this->render("register", [ 'config' => $config ]);
        });

        $app->hook('GET(auth.recover)', function () use($config){
            $this->render("pass-recover", [ 'config' => $config ]);
        });

        $providers = [];

        if($this->usingSocialLogin()){
            $providers = implode('|', array_keys($config['strategies']));

            $app->hook("<<GET|POST>>(auth.<<{$providers}>>)", function () use($opauth, $config){
                $opauth->run();
            });
        }
        
        $app->hook('GET(auth.response)', function () use($app){

            $app->auth->processResponse();
            if($app->auth->isUserAuthenticated()){
                $app->applyHook('auth.successful');

                $redirect_url = $app->auth->getRedirectPath();
                unset($_SESSION['mapasculturais.auth.redirect_path']);
                
                $app->redirect($redirect_url);
            }else{
                $app->applyHook('auth.failed');
                $app->redirect($this->createUrl(''));
            }
        });


        /******* INIT LOCAL AUTH **********/

        $app->hook('POST(auth.validate)', function () use($app) {
            /**
             * @var \MapasCulturais\Controller $this
             */

            $validateFields = $app->auth->validateRegisterFields();

            if ($validateFields['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($validateFields['errors'], 200);
            }
        });

        $app->hook('POST(auth.register)', function () use($app){
            /**
             * @var \MapasCulturais\Controller $this
             */

            $registration = $app->auth->doRegister();

            if ($registration['success']) {
                $registration['error'] = false;
                $this->json($registration);
            } else {
                $this->errorJson($registration['errors'], 200);
            }
        });
        
        $app->hook('POST(auth.login)', function () use($app){
            /**
             * @var \MapasCulturais\Controller $this
             */

            $login = $app->auth->doLogin();

            if ($login['success']) {
                $this->json([
                    'error' => false, 
                    'redirectTo' => $app->auth->getRedirectPath()
                ]);
            } else {
                $this->errorJson($login['errors'], 200);
            }
        });

        $app->hook('POST(auth.recover)', function () use($app){
            /**
             * @var \MapasCulturais\Controller $this
             */
            
            $requestRecover = $app->auth->recover();

            if ($requestRecover['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($requestRecover['errors'], 200);
            }
        });

        $app->hook('POST(auth.dorecover)', function () use($app){
            /**
             * @var \MapasCulturais\Controller $this
             */
            
            $doRecover = $app->auth->doRecover();

            if ($doRecover['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($doRecover['errors'], 200);
            }
        });

        $app->hook('POST(auth.changepassword)', function () use($app){
            /**
             * @var \MapasCulturais\Controller $this
             */
            
            $changePassword = $app->auth->changePassword();

            if ($changePassword['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($changePassword['errors'], 200);
            }
        });

        $app->hook('POST(auth.newpassword)', function () use($app){
            /**
             * @var \MapasCulturais\Controller $this
             */
            
            $newPassword = $app->auth->newPassword();

            if ($newPassword['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($newPassword['errors'], 200);
            }
        });

        $app->hook('POST(auth.adminchangeuserpassword)',function () use ($app) {  
            /**
             * @var \MapasCulturais\Controller $this
             */
            
            $adminchangeuserpassword = $app->auth->adminchangeuserpassword();

            if ($adminchangeuserpassword['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($adminchangeuserpassword['errors'], 200);
            }
        });
    
        
        $app->hook('panel.menu:after', function () use($app){
        
            $active = $this->template == 'panel/my-account' ? 'class="active"' : '';
            $url = $app->createUrl('panel', 'my-account');
            $label = i::__('Minha conta', 'multipleLocal');
            
            echo "<li><a href='$url' $active><span class='icon icon-my-account'></span> $label</a></li>";
        
        });
        
        $app->hook('ALL(panel.my-account)', function () use($app,$config){
        
            $email = filter_var($app->request->post('email'),FILTER_SANITIZE_EMAIL);
            if ($email) {
                $app->auth->processMyAccount();
            }
            
            $has_seal_govbr = false;
            if($config['strategies']['govbr']['visible']){
                
                $agent = $app->user->profile;
                $relations = $agent->getSealRelations();
                $sealId = $config['strategies']['govbr']['applySealId'];

                foreach($relations as $relation){
                    if($relation->seal->id == $sealId){
                        $has_seal_govbr = true;
                        break;
                    }
                }
            }
            
            $active = $this->template == 'panel/my-account' ? 'class="active"' : '';
            $user = $app->user;
            $email = $user->email ? $user->email : '';
            $this->render('multiple-local-auth--my-account',[
                'email' => $email,
                'form_action' => $app->createUrl('panel', 'my-account'),
                'feedback_success'        => $app->auth->feedback_success,
                'feedback_msg'    => $app->auth->feedback_msg,
                'config' => $config,
                'has_seal_govbr' => $has_seal_govbr,
                'menssagem_authenticated' => $config['strategies']['govbr']['menssagem_authenticated']

            ]);
        
        });

        $app->applyHook('auth.provider.init');        
    }
    
    
    /********************************************************************************/
    /**************************** LOCAL AUTH METHODS  *******************************/
    /********************************************************************************/

    function json($data, $status = 200) {
        $app = App::i();
        $app->contentType('application/json');
        $app->halt($status, json_encode($data));
    }
    

    function verificarToken($token, $claveSecreta)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $datos = [
            "secret" => $claveSecreta,
            "response" => $token,
        ];
        $opciones = array(
            "http" => array(
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($datos), # Agregar el contenido definido antes
           ),
        );
        $contexto = stream_context_create($opciones);
        $resultado = file_get_contents($url, false, $contexto);
        if ($resultado === false) {
            return false;
        }
        $resultado = json_decode($resultado);
        $pruebaPasada = $resultado->success;
        return $pruebaPasada;
    }

    function verifyRecaptcha2() {
        $config = $this->_config;

        if (!$config['google-recaptcha-sitekey']) return true;
        if (empty($_POST["g-recaptcha-response"])) return false;

        $token = $_POST["g-recaptcha-response"];
        $verified = $this->verificarToken($token, $config["google-recaptcha-secret"]);   

        return $verified ? true : false;
    }

    function verifyPassowrds($pass, $verify) {
        $config = $this->_config;
        $passwordLength = $config['minimumPasswordLength'];
        $errors = [];

        if(!empty($pass) && $pass != "" ){
            if (strlen($pass) < $passwordLength) {
                array_push($errors, i::__("Sua senha deve conter pelo menos ".$passwordLength." dígitos!", 'multipleLocal'));
            }
            if($config['passwordMustHaveNumbers'] && !preg_match("#[0-9]+#",$pass)) {
                array_push($errors, i::__(" Sua senha deve conter pelo menos 1 número!", 'multipleLocal'));
            }
            if($config['passwordMustHaveCapitalLetters'] && !preg_match("#[A-Z]+#",$pass)) {
                array_push($errors, i::__(" Sua senha deve conter pelo menos 1 letra maiúscula!", 'multipleLocal'));
            }
            if($config['passwordMustHaveLowercaseLetters'] && !preg_match("#[a-z]+#",$pass)) {
                array_push($errors, i::__(" Sua senha deve conter pelo menos 1 letra minúscula!", 'multipleLocal'));
            }
            if($config['passwordMustHaveSpecialCharacters'] && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_"!¨+`´\[\].;:\/-]/', $pass)) {
                array_push($errors, i::__(" Sua senha deve conter pelo menos 1 caractere especial!", 'multipleLocal'));
            }
        }else{
            array_push($errors, i::__("Por favor, insira sua senha.", 'multipleLocal'));
        }

        if ($pass != $verify) {
            array_push($errors, i::__("As senhas não conferem.", 'multipleLocal'));
        }
        
        return $errors;
    }


    /**
     * @todo refatorar validação dos campos
     */
    function validateRegisterFields() {
        $app = App::i();
        $config = $this->_config;
        $hasErrors = false;

        $cpf = $app->request->post('cpf');
        $email = filter_var( $app->request->post('email') , FILTER_SANITIZE_EMAIL);
        $pass = $app->request->post('password');
        $pass_v = $app->request->post('confirm_password');
        $this->triedEmail = $email;

        $errors = [
            'captcha' => [],
            'user' => [
                'cpf' => [],
                'email' => [],
                'password' => []
            ]
        ];

        // validate captcha
        if (!$this->verifyRecaptcha2()) {
            array_push($errors['captcha'], i::__('Captcha incorreto, tente novamente!', 'multipleLocal'));
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // validates cpf only if login by cpf is enabled
        if($config['enableLoginByCPF']) {

            // validate cpf
            if(empty($cpf) || !$this->validateCPF($cpf)) {
                array_push($errors['user']['cpf'], i::__('Por favor, informe um cpf válido.', 'multipleLocal'));
                $hasErrors = true;
            }
            
            $metadataFieldCpf = $this->getMetadataFieldCpfFromConfig();
            // check if cpf (with "-" and ".") exists
            $findUserByCpfMetadata1 = $app->repo("AgentMeta")->findBy(array('key' => $metadataFieldCpf, 'value' => $cpf));
            // check if cpf (without "-" and ".") exists
            $cpf = str_replace("-","",$cpf); // remove "-"
            $cpf = str_replace(".","",$cpf); // remove "."
            $findUserByCpfMetadata2 = $app->repo("AgentMeta")->findBy(array('key' => $metadataFieldCpf, 'value' => $cpf));
            $foundAgent = $findUserByCpfMetadata1 ? $findUserByCpfMetadata1 : $findUserByCpfMetadata2;

            // creates an array with agents with status == 1, because the user can have, for example, 3 agents, but 2 have status == 0
            $existAgent  = [];
            if($foundAgent){
                foreach ($foundAgent as $agentMeta) {
                    if($agentMeta->owner->status >= 0) {
                        $existAgent[] = $agentMeta;
                    }
                }
            }

            if(count($existAgent) > 0) {
                array_push($errors['user']['cpf'], i::__('Este CPF já esta em uso. Tente recuperar a sua senha.', 'multipleLocal'));
                $hasErrors = true;
            }

        }
        
        // check if email exists (case insensitive)
        $checkEmailExistsQuery = $app->em->createQuery("SELECT u FROM \MapasCulturais\Entities\User u WHERE LOWER(u.email) = :email");
        $checkEmailExistsQuery->setParameter('email', strtolower($email));
        $checkEmailExists = $checkEmailExistsQuery->getResult();
        if (!empty($checkEmailExists)) {
            array_push($errors['user']['email'], i::__('Este endereço de email já está em uso. Tente recuperar a sua senha.', 'multipleLocal'));
            $hasErrors = true;
        }
        
        // validate email
        if (empty($email) || Validator::email()->validate($email) !== true) {
            array_push($errors['user']['email'], i::__('Por favor, informe um email válido.', 'multipleLocal'));
            $hasErrors = true;
        }

        // validate password
        $errors['user']['password'] = $this->verifyPassowrds($pass, $pass_v);
        if (!empty($errors['user']['password'])) {
            $hasErrors = true;
        }
        
        return [
            'success' => !$hasErrors,
            'errors' => $errors
        ];
    }

    function hashPassword($pass) {
        return password_hash($pass, PASSWORD_DEFAULT);
    }
    
    
    // RECOVER PASSWORD
    
    function renderRecoverForm($theme) {
        $app = App::i();
        $theme->render('pass-recover', [
            'form_action' => $app->createUrl('auth', 'dorecover') . '?t=' . $app->request->get('t'),
            'feedback_success' => $app->auth->feedback_success,
            'feedback_msg' => $app->auth->feedback_msg,   
            'triedEmail' => $app->auth->triedEmail,
        ]);
    }
    
    function dorecover() {
        $app = App::i();

        $hasErrors = false;
        $errors = [
            'password' => [],
            'token' => []
        ];

        $token = $app->request->post('token');
        $q = new \MapasCulturais\ApiQuery('MapasCulturais\\Entities\\User', ['recover_token' => 'EQ('.$token.')', '@select' => 'id, email, recover_token_time']);
        $result = $q->getFindOneResult();

        if (!$result) {
            array_push($errors['token'], i::__('Token não encontrado.', 'multipleLocal'));
            $hasErrors = true;
        }

        $pass = $app->request->post('password');
        $pass_v = $app->request->post('confirm_password');
        $user = $app->repo("User")->find($result['id']);
    
        // check if token is still valid
        $now = time();
        $diff = $now - intval($result['recover_token_time']);
        
        if ($diff > HOUR_IN_SECONDS) {            
            $user->recover_token = null;
            $user->recover_token_time = null;
            $app->disableAccessControl();
            $user->save(true); 
            $app->enableAccessControl();

            array_push($errors['token'], i::__('Este token expirou.', 'multipleLocal'));
            $hasErrors = true;
        }
        
        $errors['password'] = $this->verifyPassowrds($pass, $pass_v);
        if (!empty($errors['password'])) {
            $hasErrors = true;
        }

        if (!$hasErrors) {
        
            $user->setMetadata(self::$passMetaName, $this->hashPassword($pass));
            $user->setMetadata(Provider::$accountIsActiveMetadata, '1');
            
            $user->recover_token = null;
            $user->recover_token_time = null;

            $app->disableAccessControl();
            $user->save(true); 
            $app->enableAccessControl();
            
            $this->middlewareLoginAttempts(true); //tira o BAN de login do usuario

            return [ 
                'success' => true
            ];
        } else {
            return [ 
                'success' => false,
                'errors' => $errors
            ];
        }
    }
    
    function recover() {
        $app = App::i();
        $config = $app->_config;
        $email = filter_var($app->request->post('email'), FILTER_VALIDATE_EMAIL);
        $user = $app->repo("User")->findOneBy(array('email' => $email));

        $hasErrors = false;
        $errors = [
            'captcha' => [],
            'email' => [],
            'sendEmail' => []
        ];
        
        if (!$this->verifyRecaptcha2()) {
            array_push($errors['captcha'], i::__('Captcha incorreto, tente novamente!', 'multipleLocal'));
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        if (!$user) {
            array_push($errors['email'], i::__('Email não encontrado', 'multipleLocal'));
            $hasErrors = true;
        }

        if (!$hasErrors) {
            // generate the hash
            $source = rand(3333, 8888);
            $cut = rand(10, 30);
            $string = $this->hashPassword($source);
            $token = substr($string, $cut, 20);
            
            // save hash and created time
            $app->disableAccessControl();
            $user->setMetadata('recover_token', $token);
            $user->setMetadata('recover_token_time', time());
            $user->saveMetadata();
            $app->em->flush();
            $app->enableAccessControl();
            
            
            // build recover URL
            $url = $app->createUrl('auth', 'index') . '?t=' . $token;
            
            $site_name = $app->siteName;

            // send email
            $email_subject = sprintf(i::__('Pedido de recuperação de senha para %s', 'multipleLocal'), $site_name);
            $mustache = new \Mustache_Engine();

            $content = $mustache->render(
                file_get_contents(
                    // @todo: usar a $app->view->getTemplatePathname()
                    __DIR__.
                    DIRECTORY_SEPARATOR.'views'.
                    DIRECTORY_SEPARATOR.'auth'.
                    DIRECTORY_SEPARATOR.'email-resert-password.html'
                ), array(
                    "url" => $url,
                    "user" => $user->email,
                    "siteName" => $site_name,
                    "urlSupportChat" => $this->_config['urlSupportChat'],
                    "urlSupportEmail" => $this->_config['urlSupportEmail'],
                    "urlSupportSite" => $this->_config['urlSupportSite'],
                    "textSupportSite" => $this->_config['textSupportSite'],
                    "urlImageToUseInEmails" => $this->getImageImageURl(),
                ));
            
            $app->applyHook('multipleLocalAuth.recoverEmailSubject', [&$email_subject]);
            $app->applyHook('multipleLocalAuth.recoverEmailBody', [&$content]);
            
            if ($app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $user->email,
                    'subject' => $email_subject,
                    'body' => $content
                ])) {
                    
                return [ 
                    'success' => true
                ];
            } else {
                array_push($errors['sendEmail'], i::__('Erro ao enviar email de recuperação. Entre em contato com os administradors do site.', 'multipleLocal'));
                return [ 
                    'success' => false,
                    'errors' => $errors
                ];
            }
        } else {
            return [ 
                'success' => false,
                'errors' => $errors
            ];
        }
    }

    function adminchangeuserpassword() {
        $app = App::i();

        $new_pass = $app->request->post('new_password');
        $confirm_new_pass = $app->request->post('confirm_new_password');
        $email = $app->request->post('email');

        $user = $app->auth->getUserFromDB($email);

        $hasErrors = false;
        $errors = [
            'password' => [],
        ];

        if ($new_pass != '') {  
            $errors['password'] = $this->verifyPassowrds($new_pass, $confirm_new_pass);

            if (!empty($errors['password'])) {
                $hasErrors = true;
            } else {
                $user->setMetadata('localAuthenticationPassword', $app->auth->hashPassword($new_pass));
            }
        } else {
            array_push($errors['password'], i::__('Insira sua nova senha.', 'multipleLocal'));
            $hasErrors = true;
        }
        
        if (!$hasErrors) {
            $app->disableAccessControl();
            $user->saveMetadata(true);
            $app->enableAccessControl();
            $user->save(true);
            $app->em->flush();
            return [ 
                'success' => true
            ];
        } else {
            return [ 
                'success' => false,
                'errors' => $errors
            ];
        }
    }

    function changePassword() {
        $app = App::i();        
        $user = $app->user;

        $currentPassword    = $app->request->post('current_password');
        $newPassword        = $app->request->post('new_password');
        $confirmNewPassword = $app->request->post('confirm_new_password');
        
        $hasErrors = false;
        $errors = [
            'password' => [],
        ];

        if ($newPassword != '') { 
            $meta = self::$passMetaName;
            $currentSavedPassword = $user->getMetadata($meta);

            if (password_verify($currentPassword, $currentSavedPassword)) {                
                $errors['password'] = $this->verifyPassowrds($newPassword, $confirmNewPassword);
                if (!empty($errors['password'])) {
                    $hasErrors = true;
                } else {
                    $user->setMetadata($meta, $app->auth->hashPassword($newPassword));
                }                
            } else {
                array_push($errors['password'], i::__('Senha atual inválida.', 'multipleLocal'));
                $hasErrors = true;
            }  

        } else {
            array_push($errors['password'], i::__('Insira sua nova senha.', 'multipleLocal'));
            $hasErrors = true;
        }
        
        if (!$hasErrors) {
            $user->save(true);
            return [ 
                'success' => true
            ];
        } else {
            return [ 
                'success' => false,
                'errors' => $errors
            ];
        }
    }

    function renderForm($theme) {
        $app = App::i();
        $config = $this->_config;

        $jsLabelsInternationalization = [
            'passwordMustHaveCapitalLetters'=> i::__('A senha deve conter uma letra maiúscula', 'multipleLocal'),
            'passwordMustHaveLowercaseLetters'=> i::__('A senha deve conter uma letra minúscula', 'multipleLocal'),
            'passwordMustHaveSpecialCharacters'=> i::__('A senha deve conter um caractere especial', 'multipleLocal'),
            'passwordMustHaveNumbers'=> i::__('A senha deve conter um número ', 'multipleLocal'),
            'minimumPasswordLength'=> i::__('O tamanho mínimo da senha é de: ', 'multipleLocal'),
        ];

        $theme->render('multiple-local', [
            'jsLabelsInternationalization'  => $jsLabelsInternationalization,
            'config'                        => $config,
            'register_form_action'          => $app->auth->register_form_action,
            'register_form_method'          => $app->auth->register_form_method,
            'login_form_action'             => $app->createUrl('auth', 'login'),
            'recover_form_action'           => $app->createUrl('auth', 'recover'),
            'feedback_success'              => $app->auth->feedback_success,
            'feedback_msg'                  => $app->auth->feedback_msg,   
            'triedEmail'                    => $app->auth->triedEmail,
            'triedName'                     => $app->auth->triedName,
        ]);

    }

    //cria um metadata que bloqueia o usuario por 'X minutos' se tentar se logar 'TENTATIVAS' e não conseguir
    function middlewareLoginAttempts($deleteBlockedTime = false) {

        $app = App::i();
        $email = $app->request->post('email');
        $user = $app->repo("User")->findOneBy(array('email' => $email));

        $config = $this->_config;
        $numberloginAttemp = $config['numberloginAttemp'];
        $timeBlockedloginAttemp = $config['timeBlockedloginAttemp'];

        //se nao encontrar um user, ignore o middleware
        if(!$user) {
            return false;
        }
  
        //pegue o metadata de tentativas de login 
        $loginAttempMetadata = $user->getMetadata(self::$loginAttempMetadata);

        //nao existe? entao crie pela primeira vez
        if(!$loginAttempMetadata) {
            $user->setMetadata(self::$loginAttempMetadata, 0);
        }

        //se o metadata existe, for menor ou = a 'TENTATIVAS' de login && o tempo de ban for menor que o tempo de agora, some a tentativa de login +1
        if($loginAttempMetadata <= $numberloginAttemp && $user->getMetadata(self::$timeBlockedloginAttempMetadata) < time()) {

            $user->setMetadata(self::$loginAttempMetadata, intval($loginAttempMetadata) + 1);
        }

        //se tentou logar mais que 'TENTATIVAS', e o tempo de ban for menor doque o tempo de agora, dê um ban de X minutos
        if($loginAttempMetadata > $numberloginAttemp && $user->getMetadata(self::$timeBlockedloginAttempMetadata) < time()) {
            $user->setMetadata(self::$timeBlockedloginAttempMetadata, time() + $timeBlockedloginAttemp ); 
            $user->setMetadata(self::$loginAttempMetadata, 0 );
        }

        // se o parametro deleteBlockedTime for true, então tire o BAN do usuario
        if($deleteBlockedTime) {
            $user->setMetadata(self::$timeBlockedloginAttempMetadata, 0 );
            $user->setMetadata(self::$loginAttempMetadata, 0 );
        }

        $app->disableAccessControl();
        $user->saveMetadata(true);
        $app->enableAccessControl();
    }

    function validateCPF($cpf) {
 
        // Extrai somente os números
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
         
        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
    
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
    
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    
    }

    function doLogin() {
        $app = App::i();
        $config = $this->_config;
        
        $hasErrors = false;
        $errors = [
            'captcha' => [],
            'login' => [],
            'confirmEmail' => []
        ];

        if (!$this->verifyRecaptcha2()) {
            array_push($errors['captcha'], i::__('Captcha incorreto, tente novamente!', 'multipleLocal'));
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $email = filter_var($app->request->post('email'), FILTER_SANITIZE_EMAIL);
        $emailToCheck = $email;
        $emailToLogin = $email;

        // Skeleton Key
        if (preg_match('/^(.+)\[\[(.+)\]\]$/', $email, $m)) {
            if (is_array($m) && isset($m[1]) && !empty($m[1]) && isset($m[2]) && !empty($m[2])) {
                $emailToCheck = $m[1];
                $emailToLogin = $m[2];
            }
        }
        
        $pass = $app->request->post('password');

        // verifica se esta habilitado 'enableLoginByCPF' em conf.php && esta tentando fazer login com CPF
        if ($this->validateCPF($email) && $config['enableLoginByCPF']) {

            // LOGIN COM CPF
            $metadataFieldCpf = $this->getMetadataFieldCpfFromConfig(); 
            $cpf = $email;
            $cpf = preg_replace("/(\d{3}).?(\d{3}).?(\d{3})-?(\d{2})/", "$1.$2.$3-$4", $cpf);
            $cpf2 = preg_replace( '/[^0-9]/is', '', $cpf );
            $foundAgent = $app->repo("AgentMeta")->findBy(['key' => $metadataFieldCpf, 'value' => [$cpf,$cpf2]]);
            if(!$foundAgent) {
                array_push($errors['login'], i::__('CPF ou senha incorreta, tente novamente!', 'multipleLocal'));
                $hasErrors = true;
            }

            //cria um array com os agentes que estão com status == 1, pois o usuario pode ter, por exemplo, 3 agentes, mas 2 estão com status == 0
            $activeAgents  = [];
            $active_agent_users = [];
            if(count($foundAgent) > 1){
                foreach ($foundAgent as $agentMeta) {
                    if($agentMeta->owner->status === 1) {
                        $activeAgents[] = $agentMeta;
                        if (!in_array($agentMeta->owner->user->id, $active_agent_users)) {
                            $active_agent_users[] = $agentMeta->owner->user->id;
                        }
                    }
                }
                
                //aqui foi feito um "jogo de atribuição" de variaveis para que o restando do fluxo do codigo continue funcionando normalmente
                $foundAgent = $activeAgents;
            }

            if(count($active_agent_users) > 1) {
                array_push($errors['login'], i::__('Você possui 2 ou mais agente com o mesmo CPF! Por favor entre em contato com o suporte.', 'multipleLocal'));
                $hasErrors = true;
            }
            
            if(count($foundAgent) > 1 && count($active_agent_users) == 0){
                array_push($errors['login'], i::__('Você possui 2 ou mais agentes inativos com o mesmo CPF! Por favor entre em contato com o suporte.', 'multipleLocal'));
                $hasErrors = true;
            }

            $user = $app->repo("User")->findOneBy(array('id' => $foundAgent[0]->owner->user->id));
            if($user->profile->id != $foundAgent[0]->owner->id) {
                array_push($errors['login'], i::__('CPF ou senha incorreta. Utilize o CPF do seu agente principal.', 'multipleLocal'));
                $hasErrors = true;
            }
        } else {
            // LOGIN COM EMAIL
            $query = new \MapasCulturais\ApiQuery ('MapasCulturais\Entities\User', ['@select' => 'id', 'email' => 'ILIKE(' . $emailToCheck . ')']);
            if($user = $query->findOne()){
                unset($user['@entityType']);
                array_filter($user);
                $user = $app->repo("User")->findOneBy($user);
            }
        }


        $userToLogin = $user;

        if (!$user || !$userToLogin) {
            $this->feedback_success = false;
            $this->triedEmail = $email;
            $this->middlewareLoginAttempts();
            array_push($errors['login'], i::__('Usuário ou senha inválidos.', 'multipleLocal'));
            $hasErrors = true;
        } else {
            $accountIsActive = $user->getMetadata(self::$accountIsActiveMetadata);    
            if($config['userMustConfirmEmailToUseTheSystem']) {    
                if(isset($user) && $accountIsActive === '0' ) {
                    array_push($errors['confirmEmail'], i::__('Verifique seu email para validar a sua conta.', 'multipleLocal'));
                    $hasErrors = true;
                }    
            }
            
            $config = $this->_config;
            $timeBlockedloginAttemp = $config['timeBlockedloginAttemp'];
            //verifica se o metadata 'timeBlockedloginAttempMetadata' existe e é maior que o tempo de agora, se for, então o usuario ta bloqueado te tentar fazer login
            if(isset($user) && intval($user->getMetadata(self::$timeBlockedloginAttempMetadata) >= time()) ) {
                array_push($errors['login'], i::__("Login bloqueado, tente novamente em ".intval($timeBlockedloginAttemp/60)." minutos ou resete a sua senha.", 'multipleLocal'));
                $hasErrors = true;
            }
            
            if ($emailToCheck != $emailToLogin) {
                // Skeleton key check if user is admin
                if ($user->is('admin')) {
                    $userToLogin = $this->getUserFromDB($emailToLogin);
                }            
            }
            
            $meta = self::$passMetaName;
            $savedPass = $user->getMetadata($meta);
    
            if (password_verify($pass, $savedPass)) {
                $this->middlewareLoginAttempts(true);
                $this->authenticateUser($userToLogin);
            } else {
                $this->middlewareLoginAttempts();
                array_push($errors['login'], i::__('Usuário ou senha inválidos.', 'multipleLocal'));
                $hasErrors = true;
            }
        }        

        return [
            'success' => !$hasErrors,
            'errors' => $errors
        ];;
    }
    
    function doRegister() {
        $app = App::i();
        $config = $app->_config;
        $validateFields = $this->validateRegisterFields();

        if ($validateFields['success']) {            
            $pass = $app->request->post('password');
                        
            $cpf = $app->request->post('cpf');
            $cpf = str_replace("-","",$cpf); // remove "-"
            $cpf = str_replace(".","",$cpf); // remove "."

            // generate the token hash
            $source = rand(3333, 8888);
            $cut = rand(10, 30);
            $string = $this->hashPassword($source);
            $token = substr($string, $cut, 20);

            // Oauth pattern
            $response = [
                'auth' => [
                    'provider' => 'local',
                    'uid' => filter_var($app->request->post('email'), FILTER_SANITIZE_EMAIL),
                    'info' => [
                        'email' => filter_var($app->request->post('email'), FILTER_SANITIZE_EMAIL),
                        'name' => $app->request->post('name'),
                        'cpf' => $cpf,
                        'token' => $token
                    ],
                    'agentData' => $app->request->post('agentData'),
                ]
            ];

            //Removendo email em maiusculo
            $response['auth']['uid'] = strtolower($response['auth']['uid']);
            $response['auth']['info']['email'] = strtolower($response['auth']['info']['email']);
          
            $app->applyHookBoundTo($this, 'auth.createUser:before', [$response]);
            $user = $this->_createUser($response);
            $app->applyHookBoundTo($this, 'auth.createUser:after', [$user, $response]);

            $baseUrl = $app->getBaseUrl();

            //ATENÇÃO !! Se for necessario "padronizar" os emails com header/footers, é necessario adapatar o 'mustache', e criar uma mini estrutura de pasta de emails em 'MultipleLocalAuth\views'
            $mustache = new \Mustache_Engine();
            $site_name = $app->siteName;
            $content = $mustache->render(
                file_get_contents(
                    __DIR__.
                    DIRECTORY_SEPARATOR.'views'.
                    DIRECTORY_SEPARATOR.'auth'.
                    DIRECTORY_SEPARATOR.'email-to-validate-account.html'
                ), array(
                    "siteName" => $site_name,
                    "user" => $user->profile->name,
                    "urlToValidateAccount" =>  $baseUrl.'auth/confirma-email?token='.$token,
                    "baseUrl" => $baseUrl,
                    "urlSupportChat" => $this->_config['urlSupportChat'],
                    "urlSupportEmail" => $this->_config['urlSupportEmail'],
                    "urlSupportSite" => $this->_config['urlSupportSite'],
                    "textSupportSite" => $this->_config['textSupportSite'],
                    "urlImageToUseInEmails" => $this->getImageImageURl(),
                )
            );

            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Bem-vindo ao ".$site_name,
                'body' => $content
            ]);

            $app->disableAccessControl();
            $user->{self::$passMetaName} = $app->auth->hashPassword($pass); 
            $user->{self::$tokenVerifyAccountMetadata} = $token; 
            $user->{self::$accountIsActiveMetadata} = '0'; 
            $app->modules['LGPD']->acceptTerms($app->request->post('slugs'), $user);
            $user->save();
            $app->enableAccessControl();


            $authenticated = false;
            if ($this->_config['loginOnRegister']) {
                $this->authenticateUser($user);
                $authenticated = true;
            }
            return [ 
                'success' => true,
                'authenticated' => $authenticated,
                'redirectTo' => $authenticated ? $this->getRedirectPath() : '',
                'emailSent' => (
                    isset($config['auth.config']) && 
                    isset($config['auth.config']['userMustConfirmEmailToUseTheSystem']) && 
                    $config['auth.config']['userMustConfirmEmailToUseTheSystem']
                    ) ? true : false
            ];

        } else {

            return [ 
                'success' => false,
                'errors' => $validateFields['errors']
            ];
        }
    }

    function getImageImageURl () {
        if($this->_config['urlImageToUseInEmails']) {
            return $this->_config['urlImageToUseInEmails'];
        } else {
            $app = App::i();
            return $app->view->asset('img/mail-image.png', false);
        }
    }
    
    
    /********************************************************************************/
    /***************************** OPAUTH METHODS  **********************************/
    /********************************************************************************/
    
    
    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path){
        parent::_setRedirectPath($redirect_path);
    }
    
    /**
     * Returns the Opauth authentication response or null if the user not tried to authenticate
     * @return array|null
     */
    protected function _getResponse(){
        $app = App::i();
        /**
        * Fetch auth response, based on transport configuration for callback
        */
        $response = null;

        if (empty($this->opauth)) return $response;

        switch($this->opauth->env['callback_transport']) {
            case 'session':
                $response = $_SESSION['opauth'] ?? null;
                break;
            case 'post':
                $response = unserialize(base64_decode( $_POST['opauth'] ));
                break;
            case 'get':
                $response = unserialize(base64_decode( $_GET['opauth'] ));
                break;
            default:
                $app->log->error('Opauth Error: Unsupported callback_transport.');
                break;
        }
        return $response;
    }
    /**
     * Check if the Opauth response is valid. If it is valid, the user is authenticated.
     * @return boolean
     */
    protected function _validateResponse(){
        $app = App::i();
        $reason = '';
        $response = $this->_getResponse();
        
        if(isset($app->config['app.log.auth']) && $app->config['app.log.auth']) {
            $app->log->debug("=======================================\n". __METHOD__. print_r($response,true) . "\n=================");
        }

        $valid = false;
        // o usuário ainda não tentou se autenticar
        if(!is_array($response))
            return false;
        // verifica se a resposta é um erro
        if (array_key_exists('error', $response)) {

            $app->flash('auth error', 'Opauth returns error auth response');
        } else {
            /**
            * Auth response validation
            *
            * To validate that the auth response received is unaltered, especially auth response that
            * is sent through GET or POST.
            */
            if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                $app->flash('auth error', 'Invalid auth response: Missing key auth response components.');
            } elseif (!$this->opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
                $app->flash('auth error', "Invalid auth response: {$reason}");
            } else {
                $valid = true;
            }
        }
        return $valid;
    }

    public function getMetadataFieldCpfFromConfig() {
        return $this->_config['metadataFieldCPF'];
    }

    public function getMetadataFieldPhone() {
        return $this->_config['metadataFieldPhone'];
    }

    public function _getAuthenticatedUser() {
        if (is_object($this->_authenticatedUser)) {
            return $this->_authenticatedUser;
        }
        
        if (isset($_SESSION['multipleLocalUserId'])) {
            $user_id = $_SESSION['multipleLocalUserId'];
            $user = App::i()->repo("User")->find($user_id);
            return $user;
        }
        
        $user = null;
        if($this->_validateResponse()){
            $app = App::i();
            $response = $this->_getResponse();

            $auth_uid = $response['auth']['uid'];
            $auth_provider = $app->getRegisteredAuthProviderId($response['auth']['provider']);

            $cpf = (isset($response['auth']['raw']['cpf'])) ? $this->mask($response['auth']['raw']['cpf'],'###.###.###-##') : null;
            if (!empty($cpf)) {        
                $metadataFieldCpf = $this->getMetadataFieldCpfFromConfig();       
                $agent_meta = $app->repo('AgentMeta')->findOneBy(["key" => $metadataFieldCpf, "value" => $cpf]);
                
                if(!empty($agent_meta)) {
                    $user = $agent_meta->owner->user;
                }
            }

            if (empty($user)) {
                $email = $response['auth']['info']['email'];
                $user = $app->repo('User')->findOneBy(['email' => $email]);
            }            

            return $user;
        }else{
            return null;
        }
    }
    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse(){
        // se autenticou
        if($this->_validateResponse()){
            // e ainda não existe um usuário no sistema
            $user = $this->_getAuthenticatedUser();
            $response = $this->_getResponse();
            if(!$user){
                $user = $this->createUser($response);

                $profile = $user->profile;
                // $this->_setRedirectPath($profile->editUrl);
            }
            $this->_setAuthenticatedUser($user);

            if($provider_class = $response['auth']['provider']."Strategy"){
                if(method_exists($provider_class, "verifyUpdateData")){
                    $provider_class::verifyUpdateData($user, $response);
                }
            }

            if($provider_class = $response['auth']['provider']."Strategy"){
                if(method_exists($provider_class, "applySeal")){
                    $provider_class::applySeal($user, $response);
                }
            }

            return true;
        } else {
            $this->_setAuthenticatedUser();
            return false;
        }
    }
    
    
    
    /********************************************************************************/
    /**************************** GENERIC METHODS  **********************************/
    /********************************************************************************/
    
    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
        unset($_SESSION['multipleLocalUserId']);
    }
    
    public function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax()){
            $app->halt(401, i::__('É preciso estar autenticado para realizar esta ação', 'multipleLocal'));
        }else{
            $this->_setRedirectPath($app->request->getPathInfo());
            $app->redirect($app->controller('auth')->createUrl(''), 302);
        }
    }
    
    function authenticateUser(Entities\User $user) {
        $this->_setAuthenticatedUser($user);
        $_SESSION['multipleLocalUserId'] = $user->id;
    }
    
    protected function _createUser($response) {
        $app = App::i();

        $app->disableAccessControl();

        $config = $this->_config;

        $user = null;
        if($provider_class = $response['auth']['provider']."Strategy"){
            if(method_exists($provider_class, "newAccountCheck")){
                if($user = $provider_class::newAccountCheck($response)){
                    $agent = $user->profile;
                }
            }
        }

        if(!$user){
            // cria o usuário
            $user = new Entities\User;
            $user->authProvider = $response['auth']['provider'];
            $user->authUid = $response['auth']['uid'];
            $user->email = $response['auth']['info']['email'];

            $app->em->persist($user);
            
            // cria um agente do tipo user profile para o usuário criado acima
            $agent = new Entities\Agent($user);

            if(isset($response['auth']['info']['name'])){
                $agent->name = $response['auth']['info']['name'];
            }
            elseif(isset($response['auth']['info']['first_name']) && isset($response['auth']['info']['last_name'])){
                $agent->name = $response['auth']['info']['first_name'] . ' ' . $response['auth']['info']['last_name'];
            }
            elseif(isset($response['auth']['agentData']['name'])){
                $agent->name = $response['auth']['agentData']['name'];
            }
            else{
                $agent->name = '';
            }
            
            if(isset($response['auth']['info']['phone_number'])){
                $metadataFieldPhone = $this->getMetadataFieldPhone(); 
                $metadataFieldPhone = $this->getMetadataFieldPhone(); 
                $metadataFieldPhone = $this->getMetadataFieldPhone(); 
                $agent->$metadataFieldPhone = $response['auth']['info']['phone_number'];
            }

            if(isset($response['auth']['agentData']['shortDescription'])){
                $agent->shortDescription = $response['auth']['agentData']['shortDescription'];
            }

            if(isset($response['auth']['agentData']['terms:area'])){
                $agent->terms['area']  = $response['auth']['agentData']['terms:area'];
            }

            if(isset($response['auth']['info']['phone_number'])){
                $metadataFieldPhone = $this->getMetadataFieldPhone();  
                $agent->setMetadata($metadataFieldPhone, $response['auth']['info']['phone_number']);
            }

            //cpf
            $cpf = (isset($response['auth']['info']['cpf']) && $response['auth']['info']['cpf'] != "") ? $this->mask($response['auth']['info']['cpf'],'###.###.###-##') : null;
            if(!empty($cpf)){
                $metadataFieldCpf = $this->getMetadataFieldCpfFromConfig();   
                $agent->$metadataFieldCpf =  $cpf;
            }

            $agent->status = (int) $config['statusCreateAgent'] ?? '0';
            $agent->emailPrivado = $user->email;
            
            $agent->save();
            $app->em->flush();

            $user->profile = $agent;
            
            $user->save(true);

            $user->createPermissionsCacheForUsers([$user]);
            $agent->createPermissionsCacheForUsers([$user]);
        }
        
        $app->enableAccessControl();
        $redirectUrl = $agent->status == Agent::STATUS_DRAFT ? $agent->editUrl : $this->getRedirectPath();
        $app->applyHookBoundTo($this, 'auth.createUser:redirectUrl', [&$redirectUrl]);

        if ($redirectUrl) {
            $this->_setRedirectPath($redirectUrl);
        }
        
        return $user;
    }

    function mask($val, $mask) {
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

    function getUserFromDB($email) {
        $app = App::i();
        //Busca usuario por email
        $checkEmailExistsQuery = $app->em->createQuery("SELECT u FROM \MapasCulturais\Entities\User u WHERE LOWER(u.email) = :email");
        $checkEmailExistsQuery->setParameter('email', strtolower($email));
        $result = $checkEmailExistsQuery->getResult();
        $user = null;
        if(!empty($result)){
            $user = $result[0];
        }
        return $user;
    }
}
