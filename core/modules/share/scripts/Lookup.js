ScriptLoader.load('scripts/select2/Select2_wrapper_jquery.js', 'scripts/select2/select2.full.jquery.min.js', 'Filters');

var Lookup = new Class({
    initialize: function (el, componentPath) {
        var button;
        this.el = $(el);
        Asset.css('select2/select2.css');
        this.url = componentPath + this.el.getProperty('data-url');

        Select2_wrapper_jquery(el,
            this.url+'get-data/',
            this.requestValues.bind(this),
            this.rebuild.bind(this),
            this.show.bind(this),
            this.show.bind(this)
        );

        //this.keyField = this.el.getElement('input[type=hidden]');
        this.keyFieldName = this.el.getProperty('data-key-field');
        this.valueFieldName = this.el.getProperty('data-value-field');
        this.valueTable = this.el.getProperty('data-value-table');

        /*this.input = this.el.getElement('input[type=text]');*/

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
    },
    show: function(row, obj){
        return row[this.valueFieldName];
    },
    
    /**
     * Prepare the data.
     *
     * @param {Object} result Result object.
     */
    rebuild:  function (response, requestParams) {

        // parse the results into the format expected by Select2
        // since we are using custom formatting functions we do not need to
        // alter the remote JSON data, except to indicate that infinite
        // scrolling can be used
        if(response.data) {
            requestParams.page = requestParams.page || 1;

            return {
                results: response.data/*,
                pagination: {
                    more: (params.page * 30) < response.total_count
                }*/
            }
        }

        return {
            results:[]
        }
    },

    /**
     * Send the POST request.
     *
     * @param {string} str Data string.
     */
    requestValues: function (query) {
        if(query.term)
        return {'filter': JSON.encode(
            new Filter.ClauseSet(Filter.Clause.create(this.valueFieldName, this.valueTable, 'like', 'string').setValue(query.term))
        ) };

    },

    load: function (data) {
        //this.keyField.set('value', data[this.keyFieldName]);
        //this.input.set('value', data[this.valueFieldName]);
        //this.keyField.fireEvent('change', data);
    },

    /**
     * Select an item from the [list]{@link Tags#list}.
     *
     * @param {HTMLLIElement} li Element that will be selected.
     */
    select: function () {
        console.log(arguments)
    }
});

Lookup.TIMEOUT_PERIOD = 500;
Lookup.START_CHAR_COUNT = 1;