<?php
namespace RecreatePCacheOnLogin;

use MapasCulturais\App;
use MapasCulturais\Entities\User;

class Plugin extends \MapasCulturais\Plugin {
    function _init()
    {
        $app = App::i();

        $app->hook('auth.login', function($user) use($app){
            /** @var User $user */
            // $agents = $app->repo('Agent')->findBy(['user' => $user, '_type' => 1]);
            $agents = $app->repo('Agent')->findBy(['user' => $user]);
            foreach($agents as $agent) {
                $app->enqueueEntityToPCacheRecreation($agent);
            }
        });
    }

    function register() {}
}
