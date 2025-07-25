<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-icon
 ')
?>

<div class="accessibility-controls">
    <li class="accessibility-controls__controls-toogle">
        <a @click="toggleControls" href="javascript:void(0);" class="mc-header-menu--item home">
            <span class="icon"> <mc-icon name="access_accessibility"></mc-icon> </span>
            <p class="label"> <?php i::_e('Acessibilidade') ?> </p>
        </a>
    </li>
</div>