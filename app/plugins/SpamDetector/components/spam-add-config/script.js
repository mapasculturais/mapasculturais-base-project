app.component('spam-add-config', {
    template: $TEMPLATES['spam-add-config'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('spam-add-config')
        return { text, messages }
    },

    computed: {
        spamTerms() {
            return $MAPAS.config.spamAddConfig.spamTerms;
        },
    },

    data() {
        return {
            notificationTags: $MAPAS.config.spamAddConfig.spamTerms.notification.sort() || [],
            blockedTags: $MAPAS.config.spamAddConfig.spamTerms.blocked.sort() || [],
        };
    },

    methods: {
        change(event, type) {
            if (event.key === 'Enter' || event.key === 'Tab') {
                this.addTerms(event, type);
                event.preventDefault();
            }
        },

        check(value, type) {
            let index = null;
            let _type = null;

            if (type == "notificationTags") {
                index = this.blockedTags.indexOf(value);
                _type = "blockedTags";
            } else {
                index = this.notificationTags.indexOf(value);
                _type = "notificationTags";
            }
            
            if (index && index !== -1) {
                this[_type].splice(index, 1);
            }
        },

        addTerms(event, type) {
            let value = event.target.value.trim();
            if (value) {
                if (this[type].includes(value)) {
                    this.messages.error(`${this.text('O termo')} ${value} ${this.text('já esta cadastrado')}`);
                    return;
                }

                this.check(value, type);
                this[type].push(value);
                this.saveTags();
                this.clear(event);
            }
        },

        clear(event) {
            event.target.value = '';
        },

        async saveTags() {
            const tagsData = {
                notification: this.notificationTags,
                blocked: this.blockedTags
            };

            const api = new API();
            let url = Utils.createUrl("spamdetector", "saveterms");
            api.POST(url, tagsData).then(res => res.json()).then(data => {
                this.messages.success(this.text('Bloqueio e notificações foi salvo com sucesso'));
            })
        },
    },

});