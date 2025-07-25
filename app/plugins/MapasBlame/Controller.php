<?php

namespace MapasBlame;
use MapasCulturais\Traits;

class Controller extends \MapasCulturais\Controllers\EntityController
{
    use Traits\ControllerAPI;


    protected $entityClassName = 'MapasBlame\Entities\Blame';

    function __construct()
    { 
    }
      
}
