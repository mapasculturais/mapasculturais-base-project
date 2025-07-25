<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->import('
    mc-alert
    mc-link
');
$user_identifier = $app->user->profile->name ?: $app->user->email;
?>

<div class="logged-as-user">
    <mc-link route='auth/asUserId' icon="logout" class="button button-small button--primary-outline button--icon button-action">
        <?= i::__('Voltar como administrador')?>
    </mc-link>
    <mc-alert type="warning" class="logged-as-user">
        <?= i::__('<strong>Atenção</strong>: Você está acessando o sistema como o usuário ') . "<code>#{$app->user->id}</code> (<strong>{$user_identifier}</strong>)" ?>
    </mc-alert>
</div>