ScriptLoader.load('Filters');

var Lookup = new Class({
    initialize: function (el, componentPath) {
        var button;

        this.el = $(el);
        this.url = componentPath + this.el.getProperty('data-url');
        this.keyField = this.el.getElement('input[type=hidden]');
        this.keyFieldName = this.el.getProperty('data-key-field');
        this.valueFieldName = this.el.getProperty('data-value-field');
        this.valueTable = this.el.getProperty('data-value-table');

        this.input = this.el.getElement('input[type=text]');

        this.el.getElement('button').addEvent('click', function (e) {
            e.stop();
            ModalBox.open({
                url: this.url,
                onClose: function (returnValue) {
                    if (returnValue && returnValue.data) {
                        this.load(returnValue.data);
                    }
                }.bind(this)
            });
        }.bind(this));

        this.list = new DropBoxList(this.input);
        this.list.addEvent('choose', this.select.bind(this));
        this.list.get().inject(this.input, 'after');
        //Вешаем на keyup для того чтобы у нас было реальное value поля
        this.input.addEvent('keyup', this.enter.bind(this));
        this.date = false;
    },

    /**
     * Event handler. Enter.
     *
     * @param {Object} e Event.
     */
    enter: function (e) {
        if (!this.url) {
            return;
        }

        var val = this.input.value;

        switch (e.key) {
            case 'esc':
                this.list.hide();
                this.list.empty();
                break;

            case 'up':
            case 'down':
            case 'enter':
                this.list.keyPressed.call(this.list, e);
                break;

            default :
                if (val != this.value) {
                    if (val.length > Lookup.START_CHAR_COUNT) {
                        this.value = val;
                        this.requestValues(val);
                    }
                }


        }
    },

    /**
     * Prepare the data.
     *
     * @param {Object} result Result object.
     */
    rebuild: function (result) {
        if (result.result && result.data) {
            this.list.update(result.data.map(function (item) {
                return {
                    key: item[this.keyFieldName],
                    'value': item[this.valueFieldName]
                }
            }.bind(this)), this.value);
            this.list.show();
        }
        else {
            this.list.hide();
        }


    },

    /**
     * Send the POST request.
     *
     * @param {string} str Data string.
     */
    requestValues: function (str) {
        if (!this.date) {
            this.date = new Date();
        }

        if ((this.date.get('sec') - new Date().get('sec')) < Lookup.TIMEOUT_PERIOD) {
            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }
        }

        this.timeout = (function () {
            new Request.JSON({
                url: this.url + 'get-data/',
                link: 'cancel',
                onSuccess: this.rebuild.bind(this)
            }).send({
                    data: 'filter=' + JSON.encode(
                        new Filter.ClauseSet(Filter.Clause.create(this.valueFieldName, this.valueTable, 'like', 'string').setValue(str))
                    ) + '&'
                });
        }).delay(Lookup.TIMEOUT_PERIOD, this);

    },

    load: function (data) {
        this.keyField.set('value', data[this.keyFieldName]);
        this.input.set('value', data[this.valueFieldName]);
        this.keyField.fireEvent('change');
    },

    /**
     * Select an item from the [list]{@link Tags#list}.
     *
     * @param {HTMLLIElement} li Element that will be selected.
     */
    select: function (li) {
        var text = li.get('text');

        if ((this.list.selected !== false) && this.list.items[this.list.selected]) {
            this.input.set('value', text);
            this.keyField.set('value', li.retrieve('key'));
            this.keyField.fireEvent('change');
        }
        this.list.hide();
    }
});

Lookup.TIMEOUT_PERIOD = 500;
Lookup.START_CHAR_COUNT = 2;