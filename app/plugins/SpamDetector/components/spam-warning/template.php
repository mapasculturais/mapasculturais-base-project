<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
use SpamDetector\Plugin;

$instance = Plugin::getInstance();
$entity = $this->controller->requestedEntity;
$dict_entity = $instance->dictEntity($entity, 'pronome');
$dict_entity = ucfirst($dict_entity);

$url = $app->createUrl('panel', 'user-detail', [$entity->owner->user->id]);

$this->import('
    mc-alert
    mc-confirm-button
');
?>

<div class="spam-alert">
    <mc-alert v-if="entity.spam_status != 2" type="danger" class="spam-alert">
        <span v-if="entity.status != -10">
            <?= i::__('Identificamos um possível spam neste cadastro. CASO NÃO SEJA,')  ?>
        </span>
        <span v-if="entity.status == -10">
            <?= i::__('Identificamos que este cadastro contém termos ofensivos ou inadequados, por isso foi movido para a lixeira. CASO AINDA ASSIM DESEJE PERMITIR A PUBLICAÇÃO,')  ?>
        </span>
            
        <mc-confirm-button message="<?= i::esc_attr__('Deseja retirar do spam?')?>" @cancel="closeModal($event)" @confirm="setSpamStatus(2)">
            <template #button="modal">
                <button @click="modal.open()" class="spam-click">
                    <?php i::_e("clique aqui") ?>
                </button>
            </template>
        </mc-confirm-button> 

        <span v-if="entity.status != -10">
            <?= i::__('para desativar mensagens e bloqueios futoros. CASO SEJA SPAM, clique no botão <strong>Excluir</strong> no rodapé.')  ?>
        </span>
        <span v-if="entity.status == -10">
            <?= i::__('para removê-lo da lista de conteúdos suspeitos e recupere o usuário na gestão de usuários clicando no botão RECUPERAR')  ?>
        </span>
    </mc-alert>

    <mc-alert v-else type="warning" class="spam-alert">
        <?= i::__("{$dict_entity} possui histórico de conteúdos identificados como SPAM. Caso deseje reativar o monitoramento do conteúdo e voltar a receber notificações,") ?>

        <mc-confirm-button message="<?= i::esc_attr__('Deseja marcar como spam?')?>" @cancel="closeModal($event)" @confirm="setSpamStatus(1)">
            <template #button="modal">
                <button @click="modal.open()" class="spam-click">
                    <?= i::__('clique aqui') ?>
                </button>
            </template>
        </mc-confirm-button> 

        <span >
            <?= i::__('ou')  ?> <a href="<?=$url?>" class="spam-click"><?= i::__('acesse a gestão do usuario clicando aqui')  ?></a>
        </span>
    </mc-alert>
</div>