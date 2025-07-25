<?php
use MapasCulturais\i;
$this->import('
    blame-table
');
?>

<?php $this->applyTemplateHook('blame', 'before') ?>
<div class="user-management__properties">
    <?php $this->applyTemplateHook('blame', 'begin') ?>
    <h3 class="user-management__properties-label">
        <?= i::__('Logs do usuário') ?>
    </h3>
    
    <blame-table :user-id="entity.id"></blame-table>

    <?php $this->applyTemplateHook('blame', 'end') ?>
</div>
<?php $this->applyTemplateHook('blame', 'after') ?>