app.component('blame-table', {
    template: $TEMPLATES['blame-table'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('blame-table');
        return { text, hasSlot }
    },

    props: {
        userId: {
            type: Number,
            default: null,
        },
    },
    
    watch: {
        initialDate: {
            handler() { this.filterByDate() }
        },

        finalDate: {
            handler() { this.filterByDate() }
        },

        sessionId: {
            handler(id) {
                if (!id) {
                    delete this.query['sessionId'];
                    return;
                }

                this.query['sessionId'] = `LIKE(*${id}*)`;
            }
        },

        IPAddress: {
            handler(ip) {
                if (!ip) {
                    delete this.query['ip'];
                    return;
                }

                this.query['ip'] = `LIKE(*${ip}*)`
            }
        },
    },

    data() {
        let query = {
            '@select': 'id,sessionId,requestId,requestTimestamp,action,logTimestamp,userBrowserName,userBrowserVersion,ip,userId,userOS,userDevice,user.{email,profile.{name,avatar}}',
        };

        if (this.userId) {
            query['userId'] = `EQ(${this.userId})`;
        }

        return {
            query,
            initialDate: '',
            finalDate: '',
            date: [new Date(), new Date()],
            locale: $MAPAS.config.locale,
            sessionId: '',
            IPAddress: '',
            action: '',
            selectedActions: [],

            actionOptions: {
                // REQUISIÇÕES GERAIS
                'GET': __('REQUISIÇÕES - GET (acessos)', 'blame-table'),
                'POST': __('REQUISIÇÕES - POST (Criações e outras ações)', 'blame-table'),
                'PATCH': __('REQUISIÇÕES - PATCH (Atualizações parciais)', 'blame-table'),
                'PUT': __('REQUISIÇÕES - PUT (Atualizações completas)', 'blame-table'),
                'DELETE': __('REQUISIÇÕES - DELETE (Exclusões)', 'blame-table'),

                // AVALIAÇÕES
                'POST%registration.sendEvaluation%': __('Envio de Avaliações', 'blame-table'),

                // INSCRIÇÕES
                'POST%(registration.index)': __('INSCRIÇÕES - criação de inscrição', 'blame-table'),
                'PATCH%(registration.single)': __('INSCRIÇÕES - modificação de inscrição', 'blame-table'),
                'POST%(registration.send)': __('INSCRIÇÕES - envio de inscrição', 'blame-table'),
                'POST%(registration.validateEntity)': __('INSCRIÇÕES - validação de inscrição', 'blame-table'),
                'DELETE%(registration.single)': __('INSCRIÇÕES - exclusão de inscrição', 'blame-table'),

                // AGENTES
                'GET%(agent.single)': __('AGENTES - acesso à página', 'blame-table'),
                'POST%(agent.index)': __('AGENTES - criação de agente', 'blame-table'),
                'PATCH%(agent.single)': __('AGENTES - modificação de agente', 'blame-table'),
                'DELETE%(agent.single)': __('AGENTES - exclusão de agente', 'blame-table'),

                // ESPAÇOS
                'GET%(space.single)': __('ESPAÇOS - acesso à página', 'blame-table'),
                'POST%(space.index)': __('ESPAÇOS - criação de espaço', 'blame-table'),
                'PATCH%(space.single)': __('ESPAÇOS - modificação de espaço', 'blame-table'),
                'DELETE%(space.single)': __('ESPAÇOS - exclusão de espaço', 'blame-table'),

                // PROJETOS
                'GET%(project.single)': __('PROJETOS - acesso à página', 'blame-table'),
                'POST%(project.index)': __('PROJETOS - criação de projeto', 'blame-table'),
                'PATCH%(project.single)': __('PROJETOS - modificação de projeto', 'blame-table'),
                'DELETE%(project.single)': __('PROJETOS - exclusão de projeto', 'blame-table'),
                
                // EVENTOS
                'GET%(event.single)': __('EVENTOS - acesso à página', 'blame-table'),
                'POST%(event.index)': __('EVENTOS - criação de evento', 'blame-table'),
                'PATCH%(event.single)': __('EVENTOS - modificação de evento', 'blame-table'),
                'DELETE%(event.single)': __('EVENTOS - exclusão de evento', 'blame-table'),
                'POST%(eventoccurrence.index)': __('EVENTOS - criação de ocorrência', 'blame-table'),
                'DELETE%(eventoccurrence.single)': __('EVENTOS - exclusão de ocorrência', 'blame-table'),

                // OPORTUNIDADES
                'GET%(opportunity.single)': __('OPORTUNIDADES - acesso à página', 'blame-table'),
                'GET%(opportunity.edit)': __('OPORTUNIDADES - acesso à página de gestão', 'blame-table'),
                'POST%(opportunity.index)': __('OPORTUNIDADES - criação de oportunidade', 'blame-table'),
                'PATCH%(opportunity.single)': __('OPORTUNIDADES - modificação de oportunidade', 'blame-table'),
                'DELETE%(opportunity.single)': __('OPORTUNIDADES - exclusão de oportunidade', 'blame-table'),

                // OPORTUNIDADES - fases
                'POST%(evaluationmethodconfiguration.index)': __('OPORTUNIDADES - fase de avaliação - criação', 'blame-table'),
                'POST%(evaluationmethodconfiguration.index)': __('OPORTUNIDADES - fase de avaliação - exclusão', 'blame-table'),

                // OPORTUNIDADES - campos
                'POST%(registrationfieldconfiguration.index)': __('OPORTUNIDADES - configuração do formulário - criação de campo', 'blame-table'),
                'POST%(registrationfieldconfiguration.single)': __('OPORTUNIDADES - configuração do formulário - modificação de campo', 'blame-table'),
                'GET%(registrationfieldconfiguration.delete)': __('OPORTUNIDADES - configuração do formulário - exclusão de campo', 'blame-table'),
                'POST%(registrationfileconfiguration.index)': __('OPORTUNIDADES - configuração do formulário - criação de anexo', 'blame-table'),
                'POST%(registrationfileconfiguration.single)': __('OPORTUNIDADES - configuração do formulário - modificação de anexo', 'blame-table'),
                'POST%(registrationfileconfiguration.delete)': __('OPORTUNIDADES - configuração do formulário - exclusão de anexo', 'blame-table'),
                
            },
        }
    },

    computed: {
        headers() {
            let itens = [
                { text: __('ID do log', 'blame-table'), value: "id"},
                { text: __('ID da seção', 'blame-table'), value: "sessionId"},
                { text: __('ID do usuário', 'blame-table'), value: "userId"},
                { text: __('Ação', 'blame-table'), value: "action"},
                { text: __('Data do log', 'blame-table'), value: "logTimestamp"},
                { text: __('Data da requisição', 'blame-table'), value: "requestTimestamp"},
                { text: __('Navegador', 'blame-table'), value: "userBrowserName"},
                { text: __('Versão', 'blame-table'), value: "userBrowserVersion"},
                { text: __('IP', 'blame-table'), value: "ip"},
                { text: __('Sistema Operacional', 'blame-table'), value: "userOS"},
                { text: __('Dispositivo', 'blame-table'), value: "userDevice"},
            ];

            return itens;
        },

        visible() {
            return ['id', 'action', 'userId', 'ip']
        }
    },
    
    methods: {
        rawProcessor(data) {
            data.logTimestamp = new McDate(data.logTimestamp.date);
            data.requestTimestamp = new McDate(data.requestTimestamp.date);
            return data;
        },
        filterActions() {
            let search = [];

            for (const action of this.selectedActions) {
                let clausure = `LIKE(${action}*)`;
                search.push(clausure);
            }

            if (search.length > 0) {
                this.query['action'] = `OR(${search.join()})`;
            } else {
                delete this.query['action'];
            }
        },
        filterByDate() {            
            if (this.initialDate && this.finalDate) {
                let d0 = new McDate(new Date(this.initialDate));
                let d1 = new McDate(new Date(this.finalDate));
                this.query['requestTimestamp'] = `BET(${d0.date('sql')}, ${d1.date('sql')})`;
            }

            if (this.initialDate && !this.finalDate) {
                let d0 = new McDate(new Date(this.initialDate));
                this.query['requestTimestamp'] = `GTE(${d0.date('sql')})`;
            }

            if (!this.initialDate && this.finalDate) {
                let d1 = new McDate(new Date(this.finalDate));
                this.query['requestTimestamp'] = `LTE(${d1.date('sql')})`;
            }

            if (!this.initialDate && !this.finalDate) {
                delete this.query['requestTimestamp'];
            }
        },
        dateFormat(date) {
            let mcdate = new McDate (date);
            return mcdate.date('2-digit year');
        },
    },
});
