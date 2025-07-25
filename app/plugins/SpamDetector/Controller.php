<?php

namespace SpamDetector;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Controller as SpamDetectorController;

class Controller extends SpamDetectorController
{

    function __construct() {}

    public function POST_saveterms()
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$app->user->is("admin")) {
            $app->pass();
        }

        $path = Plugin::getPathFile();

        if (file_exists($path)) {
            $data = json_encode($this->data, JSON_PRETTY_PRINT);
            file_put_contents($path, $data);
        }

        $this->json($this->data);
    }
}
