ScriptLoader.load('Overlay');

var Search = new Class({

    request: Energine.request,
    trans: [],

    initialize: function (element) {

        this.element = $(element);
        this.input = this.element.getElement('input[name=keyword]');
        this.searchUrl = this.element.getAttribute('data-url') + '?html&' + Math.floor((Math.random() * 10000));
        this.autocomplete = this.element.getElement('.search_autocomplete');

        //Вешаем на keyup для того чтобы у нас было реальное value поля
        this.input.addEvent('keyup', this.enter.bind(this));
        this.date = false;

        this.autocomplete.set(
            'load',
            {
                method: 'get',
                'onFailure': this.onError.bind(this),
                'onComplete': this.onAutocomplete.bind(this)
            });
    },

    enter: function (e) {

        var val = this.input.value;

        switch (e.key) {
            case 'esc':
                this.autocomplete.hide();
                this.autocomplete.empty();
                break;

            case 'up':
            case 'down':
            case 'enter':
                //this.list.keyPressed.call(this.list, e);
                break;

            default :
                if (val != this.value) {
                    if (val.length > Search.START_CHAR_COUNT) {
                        this.value = val;
                        this.requestValues(val);
                    }
                }
        }
    },

    requestValues: function (str) {
        if (!this.date) {
            this.date = new Date();
        }

        if ((this.date.get('sec') - new Date().get('sec')) < Search.TIMEOUT_PERIOD) {
            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }
        }

        this.timeout = (function () {
            this.autocomplete.hide();
            this.autocomplete.load(this.searchUrl + '&' + this.element.toQueryString());
        }).delay(Search.TIMEOUT_PERIOD, this);

    },

    onAutocomplete: function () {
        this.autocomplete.show();
    },

    onError: function () {
        this.autocomplete.hide();
    }

});

Search.TIMEOUT_PERIOD = 500;
Search.START_CHAR_COUNT = 2;