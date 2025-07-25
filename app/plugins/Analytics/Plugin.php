<?php

namespace Analytics;

use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin
{
    public function __construct(array $config = [])
    {
        $config += [
            "analytics_key" => env("ANALYTICS_KEY",""),
        ];

        parent::__construct($config);
    }
    
    public function _init()
    {
        $app = App::i();
        
        $self = $this;

        // Analytics para o BaseV1
        $app->hook("template(<<*>>.<<*>>.main-head):end", function () use ($self) {
            $config = $self->config;
            if($config['analytics_key']){
            $this->part('analytics',["config" => $config]);
            }
        });

        // Analytics para o BaseV2
        $app->hook("template(<<*>>.<<*>>.head):end", function () use ($self) {
            $config = $self->config;
            if($config['analytics_key']){
                $this->part('analytics',["config" => $config]);
            }
        });
    }

    public function register()
    {
    }
}
