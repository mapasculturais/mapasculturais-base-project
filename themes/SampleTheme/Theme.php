<?php
namespace SampleTheme;

use MapasCulturais\App;


// class Theme extends \Subsite\Theme {
class Theme extends \MapasCulturais\Themes\BaseV1\Theme {
 
    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        parent::_init();
        $app = App::i();
        
        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->_publishAssets();
        });
    }

    protected function _publishAssets() {

        // $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);

        // $this->enqueueScript('app', 'hide-fields', 'js/hide-fields.js');
    }

}