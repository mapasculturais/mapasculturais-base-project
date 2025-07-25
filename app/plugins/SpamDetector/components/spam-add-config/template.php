<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tag-list
');

?>
<div id="spam-add-config">

    <mc-modal button-label="<?php i::_e('Controle de Spam') ?>" title="<?= i::esc_attr__('Configuração dos filtros de conteúdo') ?>">
        <template #button='{close, open, toogle, loading}'>
            <a href="#" @click="open()">
                <mc-icon name="security"> </mc-icon>
                <?= i::__('Controle de SPAM') ?>
            </a>
        </template>

        <template #actions="modal">
            <div class="spam-add-config__content">
                <div class="spam-add-config__notification">
                    <div class="spam-add-config__add">
                        <span class="spam-add-config__title semibold"> <?= i::__('Notificações')?></span>
                        <div class="field">
                            <input type="text" placeholder="Digite uma nova palavra chave de notificação" @keydown="change($event, 'notificationTags')"  @blur="clear($event)">
                        </div>    
                    </div>
                    <mc-tag-list class="spam-add-config__tags scrollbar" classes="spam-add-config__tag spam-add-config__tag--notification" :tags="notificationTags" @remove="saveTags()" editable></mc-tag-list>
                </div>

                <div class="spam-add-config__vertical-divisor"></div>
            
                <div class="spam-add-config__block">
                    <div class="spam-add-config__add">
                        <span class="spam-add-config__title semibold"> <?= i::__('Bloqueio')?></span>
                        <div class="field">
                            <input type="text" placeholder="Digite uma nova palavra chave de bloqueio" @keydown="change($event, 'blockedTags')" @blur="clear($event)">
                        </div>
                    </div>
                    <mc-tag-list class="spam-add-config__tags scrollbar" classes="spam-add-config__tag spam-add-config__tag--block" :tags="blockedTags"  @remove="saveTags()" editable></mc-tag-list>
                </div>
            </div>
        </template>
    </mc-modal>

</div>