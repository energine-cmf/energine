var GoodsFilter = new Class({
    initialize: function (el) {
        this.element = $(el);
        if (this.form = this.element.getParent('form')) {
            this.form.getElementById('reset').addEvent('click', function () {
                document.location = this.form.getProperty('action');
            }.bind(this));
            this.form.addEvent('submit', function (e) {
                e.stop();
                document.location = this.form.getProperty('action') + '?' + this.serialize($(e.target));
            }.bind(this))
        }
    },
    serialize: function (form) {
        var queryString = [], filterName = form.getProperty('data-filter-name'), rangeFilters = {}, multiFilters = {};
        form.getElements('input, select, textarea').each(function (el) {
            var type = el.type;
            if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') return;

            var value = (el.get('tag') == 'select') ? el.getSelected().map(function (opt) {
                // IE
                return document.id(opt).get('value');
            }) : ((type == 'radio' || type == 'checkbox') && !el.checked) ? null : el.get('value');
            var name = el.name.substr(filterName.length);
            var matches = /^\[(\w+)\](\[\w*\])?/.exec(name);
            Array.from(value).each(function (val) {
                var val = encodeURIComponent(val);
                //console.log(val, name);
                if ((typeof val != 'undefined') && matches) {
                    name = matches[1] + matches[2];
                    if ((matches[2] == '[begin]') || (matches[2] == '[end]')) {
                        if (!rangeFilters[matches[1]]) {
                            rangeFilters[matches[1]] = [];
                        }
                        rangeFilters[matches[1]].push(val);
                    }
                    else if (matches[2] == '[]') {
                        if (!multiFilters[matches[1]]) {
                            multiFilters[matches[1]] = [];
                        }
                        multiFilters[matches[1]].push(val);
                    }
                    else {
                        queryString.push(name + '=' + val);
                    }
                }
            });

        });
        Object.each(multiFilters, function (values, name) {
            queryString.push(name + '=' + values.join(','));
        });
        Object.each(rangeFilters, function (values, name) {
            console.log((values[0] || values[1]) == true, values)
            if(values[0] || values[1])
                queryString.push(name + '=' + values.join('-'));
        });

        if(queryString = queryString.join(';')) return filterName + '=' + queryString;

        return '';
    }
});