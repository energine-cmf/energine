
var Lookup = new Class({
    initialize: function(el, componentPath){
        var button;
        this.el =$(el);
        this.url = componentPath + this.el.getProperty('data-url');
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
    },
    load: function(data){
        var valueEl = this.el.getElement('input[type=hidden]');
        valueEl.set('value', data.data[valueEl.id]);
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
        }
        this.list.hide();
    }
});