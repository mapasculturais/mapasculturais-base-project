<?php
namespace MultipleLocalAuth;

use MapasCulturais\App;
use MapasCulturais\i;

include('Facebook/FacebookStrategy.php');
include('Google/GoogleStrategy.php');
include('LinkedIn/LinkedInStrategy.php');
include('LoginCidadao/LoginCidadaoStrategy.php');
include('GovBr/GovBrStrategy.php');

class Plugin extends \MapasCulturais\Plugin {
    
    public function _init() {
        $app = App::i();
        
        // register translation text domain
        i::load_textdomain( 'multipleLocal', __DIR__ . "/translations" );
        
        // Load JS & CSS
        $app->hook('GET(<<auth|panel>>.<<*>>):before', function() use ($app) {
            $app->view->enqueueStyle('app-v2', 'multipleLocal-v2', 'css/plugin-MultiplLocalAuth.css');
        });

        $app->hook('GET(auth.<<index|register>>)', function() use($app) {
            if (env('GOOGLE_RECAPTCHA_SITEKEY', false)) {
                $app->view->enqueueScript('app-v2', 'multipleLocal-v2', 'https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit');
            }
        });

        $app->hook('template(panel.<<my-account|user-detail>>.user-mail):end ', function() {
            /** @var \MapasCulturais\Theme $this */
            $this->part('password/change-password');
        });

        $app->hook('entity(User).permissionsList,doctrine.emum(permission_action).values', function (&$permissions) {
            $permissions[] = 'changePassword';
        });

        $app->hook('module(UserManagement).permissionsLabels', function(&$labels) {
            $labels['changePassword'] = i::__('modificar senha');
        });

        if (php_sapi_name() == "cli") {
            if (!isset($_SERVER['HTTP_HOST'])) {
                $_SERVER['HTTP_HOST'] = 'localhost';
            }
        }
    }
    
    public function register() {
        $this->registerUserMetadata(Provider::$passMetaName, ['label' => i::__('Senha')]);
        $this->registerUserMetadata(Provider::$recoverTokenMetadata, ['label' => i::__('Token para recuperação de senha')]);
        $this->registerUserMetadata(Provider::$recoverTokenTimeMetadata, ['label' => i::__('Timestamp do token para recuperação de senha')]);
        $this->registerUserMetadata(Provider::$accountIsActiveMetadata, ['label' => i::__('Conta ativa?')]);
        $this->registerUserMetadata(Provider::$tokenVerifyAccountMetadata, ['label' => i::__('Token de verificação')]);
        $this->registerUserMetadata(Provider::$loginAttempMetadata, ['label' => i::__('Número de tentativas de login')]);
        $this->registerUserMetadata(Provider::$timeBlockedloginAttempMetadata, ['label' => i::__('Tempo de bloqueio por excesso de tentativas')]);        
    }
}
