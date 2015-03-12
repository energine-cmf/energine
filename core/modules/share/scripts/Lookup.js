
var Lookup = new Class({
    initialize: function(el, componentPath){
        var button;
        this.el =$(el);
        this.url = componentPath + this.el.getProperty('data-url');
        this.keyField = this.el.getElement('input[type=hidden]');
        this.keyFieldName = this.keyField.id;
        this.valueFieldName = this.el.getProperty('data-value-field');

        this.input = this.el.getElement('input[type=text]');

        this.el.getElement('button').addEvent('click', function(e){
            e.stop();
            ModalBox.open({
                url: this.url,
                onClose: function (returnValue) {
                    if (returnValue) {
                        this.load(returnValue);
                    }
                }.bind(this)
            });
        }.bind(this));

        this.list = new DropBoxList(this.input);
        this.list.addEvent('choose', this.select.bind(this));
        this.list.get().inject(this.input, 'after');
        //Вешаем на keyup для того чтобы у нас было реальное value поля
        this.input.addEvent('keyup', this.enter.bind(this));
    },

    /**
     * Event handler. Enter.
     *
     * @param {Object} e Event.
     */
    enter: function(e) {
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
                this.value = val;
                this.requestValues(val);

        }
    },

    /**
     * Prepare the data.
     *
     * @param {Object} result Result object.
     */
    rebuild: function(result) {
        if(result.result && result.data){
            this.list.update(result.data.map(function(item){
                return {
                    key: item[this.keyFieldName],
                    'value':item[this.valueFieldName]
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
    requestValues: function(str) {
        var filterString = 'filter[test_fk_table_translation]['+this.valueFieldName+'][]='+str+'&filter[condition]=like';
        new Request.JSON({
            url: this.url+'get-data/',
            onSuccess: this.rebuild.bind(this)
        }).send({
                method: 'post',
                data: filterString
            });
    },

    load: function(data){
        this.keyField.set('value', data.data[this.keyFieldName]);
        this.input.set('value', data.data[this.valueFieldName]);
    },
    /**
     * Select an item from the [list]{@link Tags#list}.
     *
     * @param {HTMLLIElement} li Element that will be selected.
     */
    select: function(li) {
        var text = li.get('text');

        if ((this.list.selected !== false) && this.list.items[this.list.selected]) {
            this.input.set('value', text);
            this.keyField.set('value', li.retrieve('key'));
        }
        this.list.hide();
    }
});