<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-table
    mc-select
');
?>

<!-- @clear-filters="clearFilters" @remove-filter="removeFilter($event)" -->
<entity-table controller="blame" endpoint="find" type="blame" order="id DESC" identifier="blameTable" :raw-processor="rawProcessor" :headers="headers" :visible="visible" :query="query" :limit="100" show-index hide-sort hide-actions> 
    <template #filters>
        <div class="grid-12">
            <div class="field col-4 sm:col-6">
                <label> <?= i::__('Periodo inicial') ?></label>
                <div class="datepicker">
                    <datepicker 
                        teleport
                        :locale="locale" 
                        :weekStart="0"
                        :format="dateFormat" 
                        v-model="initialDate" 
                        :enableTimePicker='false' 
                        :dayNames="['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']"
                        autoApply utc></datepicker>
                </div>
            </div>

            <div class="field col-4 sm:col-6">
                <label> <?= i::__('Periodo final') ?></label>
                <div class="datepicker">
                    <datepicker 
                        teleport
                        :locale="locale" 
                        :weekStart="0"
                        :format="dateFormat" 
                        v-model="finalDate" 
                        :enableTimePicker='false' 
                        :dayNames="['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']"
                        autoApply utc></datepicker>
                </div>
            </div>
    
            <div class="field col-4 sm:col-6">
                <label> <?= i::__('Filtar por id da seção') ?></label>
                <input type="text" placeholder="ID da seção" v-model="sessionId" />
            </div>

            <div class="field col-4 sm:col-6">
                <label> <?= i::__('Filtar por IP') ?></label>
                <input type="text" maxlength="15" placeholder="endereço IP" v-model="IPAddress" />
            </div>

            <div class="field col-4 sm:col-6">
                <label> <?= i::__('Filtrar por ações') ?> </label>
                <mc-multiselect class="col-2" :model="selectedActions" :items="actionOptions" title="<?= i::esc_attr__('Filtrar por ações') ?>" @selected="filterActions()" @removed="filterActions()" hide-button>
                    <template #default="{popover, setFilter, filter}">
                        <div class="field">
                            <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as ações: ') ?>">
                        </div>
                    </template>
                </mc-multiselect>
            </div>

        </div>
    </template>    

    <template #logTimestamp="entity">
        {{entity.entity.logTimestamp.date('2-digit year')}} {{entity.entity.logTimestamp.time('long')}} 
    </template>

    <template #requestTimestamp="entity">
        {{entity.entity.requestTimestamp.date('2-digit year')}} {{entity.entity.logTimestamp.time('long')}} 
    </template>
</entity-table>