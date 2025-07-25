<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>

<mc-link v-if="global.auth.is('admin') && !entity.userId" route="auth/asUserId" :params="{id:entity.id}" icon="arrow-up" class="button button--primary button--sm button--icon" right-icon><?= i::__('logar')?></mc-link>
<mc-link v-if="global.auth.is('admin') && entity.userId" route="auth/asUserId" :params="{id:entity.userId, agent_id: entity.id}" icon="arrow-up" class="button button--icon button--sm login" right-icon><?= i::__('logar')?></mc-link>