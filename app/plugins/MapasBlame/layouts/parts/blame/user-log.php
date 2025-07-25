<?php 
$this->jsObject['MapasBlame']['logUserId'] = $this->controller->data['userId'];
$this->enqueueScript(
    'app', // grupo de scripts
    'mapas-blame',  // nome do script
    'js/mapas-blame.js', // arquivo do script
    [] // dependências do script
);
?>

<div id="user-log" class="aba-content">

    <form id="logFilter">
        <div class="row">
            <div class="filter-title">
                <?php \MapasCulturais\i::_e("Filtros:"); ?> 
            </div>
        </div>

        <div class="row">
            <div class="filter filter-action">
                <input type="text" placeholder="<?php \MapasCulturais\i::_e("Action"); ?>" id="action"> 
            </div>

            <div class="filter filter-datetime">
                <input type="checkbox" id="Diff"> 
                <label class="show-label" for="Diff">Não diferenciar maiúsculas de minúsculas</label>
            </div>

            <div class="filter filter-datetime">
                <label class="show-label" for="initDate">De</label>
                <input type="date" name="initDate" id="initDate"> 

                <label class="show-label" for="lastDate">até</label>
                <input type="date" name="lastDate" id="lastDate">
            </div>

            <div class="filter-submit">
                <button type="button" class="btn btn-success">
                    <?php \MapasCulturais\i::_e("Filtar logs");?>
                </button>
            </div>
        </div>
    </form>

    <div class="history-table">
    </div>

    <div class="load-more">
        <a class="prev"> <?php \MapasCulturais\i::_e("Carregar mais"); ?> </a>
    </div>

</div>

<!-- 
'log_id'
'request_id'
'ip'
'session_id'
'user_id'
'action'
'user_agent'
'user_browser_name'
'user_browser_version'
'user_os'
'user_device'
'request_metadata'
'log_metadata'
'request_ts'
'log_ts' 
-->

