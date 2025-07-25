<?php

use SpamDetector\Plugin;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$data = Plugin::getFileTerms();
$this->jsObject['config']['spamAddConfig'] = [
    "spamTerms" => $data,
];