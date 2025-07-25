<?php

namespace Accessibility;

use MapasCulturais\App;
use MapasCulturais\i;


class Plugin extends \MapasCulturais\Plugin
{
    function __construct(array $config = [])
    {
              
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();

        $app->view->enqueueStyle('app-v2', 'accessibility-v2', 'css/plugin-Accessibility.css');
        $app->view->enqueueStyle('app-v2', 'contrast-accessibility-v2', 'css/accessibility.css');
         
        // add hooks
        $app->hook('template(<<*>>.<<*>>.mc-header-menu):end', function () use ($app) {
            $this->part('accessibility/controls');
        });

        $app->hook('template(<<*>>.<<*>>.head):end', function () use ($app) {
            $this->part('accessibility/vlibras');    
        }); 
        
        $app->hook('template(<<*>>.<<*>>.body):begin', function () use ($app) {
            $this->part('accessibility/controls-itens');
        });

        $app->hook('component(mc-icon).iconset', function(&$iconset){
            $iconset['access_accessibility'] = "carbon:accessibility-alt";
            $iconset['access_adjust'] = "typcn:adjust-contrast";
            $iconset['access_plus'] = "ei:plus";
            $iconset['access_minus'] = "ei:minus";
            $iconset['access_close'] = "ei:close-o";
        });
    }
    /**
     * Registra os controladores e metadados das entidades
     *
     * @return void
     */
    public function register()
    {
        $app = App::i();

       
             
    }
}
?>
