<?php
namespace Apps;

use MapasCulturais\Controllers\EntityController;
use MapasCulturais\Traits;

/**
 * UserApp Controller
 *
 * By default this controller is registered with the id 'space'.
 *
 */
class Controller extends EntityController {

    use Traits\ControllerSoftDelete,
        Traits\ControllerAPI;


    function __construct()
    {
        $this->entityClassName = Entities\UserApp::class;
    }

    /**
     * Returns the entity with the requested id.
     *
     * @example for the url http://mapasculturais/agent/33  or http://mapasculturais/agent/id:33 returns the agent with the id 33
     *
     * @return \MapasCulturais\Entity|null
     */
    public function getRequestedEntity() {
        $entity = parent::getRequestedEntity();
        if (!$entity && key_exists(0, $this->urlData)) {
            $entity = $this->repository->find($this->urlData[0]);
        } else if (!$entity && !key_exists(0, $this->urlData)) {

            return false;
        }

        if ($entity) {
            $entity->checkPermission('view');
        }

        return $entity;
    }

}
