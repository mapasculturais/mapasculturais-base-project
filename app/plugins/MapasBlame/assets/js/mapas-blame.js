$(function() {  

    var query = 
    {
        '@select': 'id, action, logTimestamp, userBrowserName, userBrowserVersion, ip, userId, userOS, userDevice',
        'userId': 'eq(' + MapasCulturais.MapasBlame.logUserId + ')',
        '@limit': '50',
        '@page': '1',
        '@order': 'logTimestamp DESC',
    };

    function find(firstPage) {        
        if(firstPage) 
            query["@page"] = 1; 

        $.getJSON( '/api/blame/find', query, function (response, b, meta){ 
            var html = '';
            response.forEach(e => {
                const option = {
                    year: 'numeric',
                    month: 'numeric',
                    weekday: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    second: 'numeric'
                }
                var data = new Date(e.logTimestamp.date).toLocaleDateString('pt-br', option);

                html += `
                    <div class="logUnit">
                        <div class="partI">                            
                            <span>
                                <label> Action: </label>
                                <p> ${e.action} </p> 
                            </span>
                            
                        </div>
                        <div class="partII">
                            <span>
                                <label> Ip: </label>
                                <p> ${e.ip.trim()} </p>
                            </span>
                            -
                            <span>
                                <label> Data/Hora: </label>
                                <p> ${data.trim()} </p>    
                            </span>
                        </div>

                        <div class="logDetails">
                            <span>
                                <label> Log ID: </label>
                                <p> ${e.id} </p>
                            </span>
                            <span>
                                <label> Dispositivo: </label>
                                <p> ${e.userDevice} </p>
                            </span>
                            <span>
                                <label> Sistema Operacional: </label>
                                <p> ${e.userOS} </p>
                            </span>                            
                            <span>
                                <label> Browser: </label>
                                <p> ${e.userBrowserName} </p>
                            </span>
                            <span>
                                <label> Browser version: </label>
                                <p> ${e.userBrowserVersion} </p>
                            </span>
                            <span>
                                <label> ID do usuário: </label>
                                <p> ${e.userId} </p>    
                            </span>
                        </div>

                        <span class="colapse">
                            ver detalhes
                        </span>
                    </div>
                    `;
            });

            if(firstPage)
                $('#user-log .history-table').html( html );        
            else
                $('#user-log .history-table').append( html );

            var metadata = JSON.parse( meta.getResponseHeader('API-Metadata') );
            if( metadata.page == metadata.numPages || metadata.count == 0)
                $('a', '#user-log .load-more').hide();
            
            if( metadata.count == 0 )
                $('#user-log .history-table').html( 'Sem resultados' );

            $('.logUnit .colapse').click( function(){
                var log = $(this).parent();
                $('.logDetails', log).toggleClass('show');
                $(log).toggleClass('active');

                $(this).html( (log.hasClass('active') ? 'ocultar detalhes' : 'ver detalhes' ) );
            });
        });
    };

    function findMore() {
        query["@page"]++;
        find();
    };
    
    function filterLogs() {
        var form = $(this).closest('#logFilter');
        var action = $('#action', form).val();
        var initDate = $('#initDate', form).val(); 
        var lastDate = $('#lastDate', form).val(); 
        var diff = $('#Diff', form).is(':checked');

        if (initDate && lastDate) {
            query["logTimestamp"] = `BET(${initDate}, ${lastDate})`;
        } else {
            if(initDate) query["logTimestamp"] = `GTE(${initDate})`;
            if(lastDate) query["logTimestamp"] = `LTE(${lastDate})`;
        }

        if (action && diff) {
            query["action"] = `ILIKE(*${action}*)`
        } else if(action) {
            query["action"] = `LIKE(*${action}*)`
        };

        find(true);
    }

    find(true);

    $('button', '#logFilter').click(filterLogs);
    $('a', '#user-log .load-more').click(findMore);
    
});