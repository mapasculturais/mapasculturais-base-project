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

<div v-if="open" class="accessibility-controls__controls">
    <div class="accessibility-controls__actions-itens">
        <h4><?php i::_e('Acessibilidade') ?></h4>
        <div class="itens">
            <div class="item">
            <div vw class="enabled">
                <div vw-access-button class="active"></div>
                    <div vw-plugin-wrapper>
                    <div class="vw-plugin-top-wrapper"></div>
                </div>
            </div>
            </div>
            <div class="item">
                <a @click="ajustContrast()" href="javascript:void(0);">
                    <mc-icon name="access_adjust"></mc-icon>
                </a>
            </div>

            <div class="item">
                <a @click="ajustFontPlus()" href="javascript:void(0);">
                    <mc-icon name="access_plus"></mc-icon>
                </a>
            </div>

            <div class="item">
                <a @click="adjustFontMinus()" href="javascript:void(0);">
                    <mc-icon name="access_minus"></mc-icon>
                </a>
            </div>

            <div class="item">
                <a @click="resetFontSize()" href="javascript:void(0);">
                    <mc-icon name="access_close"></mc-icon>
                </a>
            </div>
        </div>
    </div>
</div>