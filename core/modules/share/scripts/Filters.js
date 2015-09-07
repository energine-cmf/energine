/**
 * <ul>
 *     <li>[Filters]{@link Filters}</li>
 *     <li>[Filter.QueryControls]{@link Filter.QueryControls}</li>
 * </ul>
 */


/**
 * Filter tool.
 *
 * @throws Element for GridManager.Filter was not found.
 *
 * @constructor
 * @param {GridManager} gridManager
 */
var FiltersFabric = new Class({
        initialize: function (templateEl) {
            this.parentContainer = templateEl.getParent('.filters');

            this.template = templateEl.clone();
            templateEl.destroy();
        },
        create: function () {
            var result = this.template.clone();
            this.parentContainer.grab(result, 'bottom');
            return new Filter(result);
        }
    }
);

var Filters = new Class(/** @lends Filter# */{
    Implements: Events,
    /**
     * Filter object.
     * @type {Filter[]}
     */
    filters: [],


    /**
     * Indicates whether the filter is active or not.
     * @type {boolean}
     */
    active: false,
    /**
     * @type {FiltersFabric}
     */
    fabric: null,
    gridManager: null,

    // constructor
    initialize: function (gridManager) {
        this.gridManager = gridManager;
        /**
         * Filter element of the GridManager.
         * @type {Element}
         */
        this.element = this.gridManager.element.getElement('.filters_block');

        if (this.element) {
            this.fabric = new FiltersFabric(this.element.getElement('.filter'));
            this.add();

            var addFilter = this.element.getElement('.add_filter'),
                applyButton = this.element.getElement('.f_apply'),
                resetLink = this.element.getElement('.f_reset'),
                inner = this.element.getElement('.filters_block_inner');

            this.element.getElement('.filter_toggle').addEvent('click', function (e) {
                e.stop();

                if (inner.hasClass('toggled')) {
                    inner.tween('height').removeClass('toggled');
                }
                else {
                    inner.tween('height', '0').addClass('toggled');
                }
            }.bind(this));
            addFilter.addEvent('click', function (e) {
                e.stop();
                this.add();

            }.bind(this));
            applyButton.addEvent('click', function () {
                if (this.use()) this.gridManager.reload();
            }.bind(this));

            resetLink.addEvent('click', function (e) {
                e.stop();
                if (this.reset()) this.gridManager.reload();
            }.bind(this));
        }
    },
    add: function () {
        var f = this.fabric.create();
        f.addEvent('apply', function () {
            if (this.use()) this.gridManager.reload();
        }.bind(this));
        this.filters.push(f);
        f.addEvent('delete', this.remove.bind(this));
        if (this.filters.length == 1) {
            f.element.getElement('.operand_container').hide();
            f.element.getElement('.remove_filter').setProperty('disabled', 'disabled');
        }
        else {
            f.element.getElement('.filters_operand').show();
            this.element.getElements('.remove_filter').removeProperty('disabled');

        }
    },
    remove: function (f) {
        if (f) {
            f.removeEvents('delete');
            this.filters.erase(f);
            f.reset();
            if (this.filters.length == 1) {
                this.filters[0].element.getElement('.operand_container').hide();
                this.filters[0].element.getElement('.remove_filter').setProperty('disabled', 'disabled');
            }
        }

    },
    /**
     * Reset the whole [filter element]{@link Filter#element}.
     * @function
     * @public
     */
    reset: function () {
        if (this.active || (this.filters.length > 1)) {
            var i=0;
            do{
                this.filters[i].reset();
            }while(this.filters.length);

            this.element.removeClass('active');
            this.add();

            return !(this.active = false)
        }
        return false;
    },

    /**
     * Mark the filter element as used or not.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    use: function () {
        if (!this.isEmpty()) {
            this.element.addClass('active');
            this.active = true;
        } else {
            this.reset();
        }

        return this.active;
    },

    /**
     * Get filter string.
     *
     * @function
     * @public
     * @returns {string}
     */
    getValue: function () {
        var result = '', fs;
        if (this.active && !this.isEmpty()) {
            fs = new Filter.ClauseSet();
            this.filters.each(function (filter) {
                fs.add(filter.getValue());
            })
            result = 'filter=' + JSON.encode(fs) + '&';
        }
        return result;
    },
    isEmpty: function () {
        return this.filters.some(function (filter) {
            return filter.isEmpty();
        }, this);
    }
});
var Filter = new Class({
    Implements: Events,

    element: null,
    /**
     * Query controls for the filter.
     * @type {Filter.QueryControls}
     */
    inputs: null,
    /**
     * Column names for filter.
     * @type {Elements}
     */
    fields: null,

    /**
     * Filter condition.
     * @type {Elements}
     */
    condition: null,
    operator: null,
    initialize: function (element) {
        this.element = $(element);
        this.inputs = new Filter.QueryControls(this.element.getElements('.f_query_container'));
        this.removeBtn = this.element.getElement('.remove_filter');

        this.removeBtn.addEvent('click', function () {
            this.reset();
        }.bind(this));
        this.inputs.addEvent('apply', function (e) {
            this.fireEvent('apply');
        }.bind(this));
        this.condition = this.element.getElement('.f_condition');
        this.conditionOptions = [];

        this.condition.getChildren().each(function (el) {
            var types;
            this.conditionOptions.push(el);
            if (types = el.getProperty('data-types')) {
                el.store('type', types.split('|'));
                el.removeProperty('data-types');
            }
        }, this);

        this.fields = this.element.getElement('.f_fields');
        this.fields.addEvent('change', this.checkCondition.bind(this));
        this.condition.addEvent('change', function (event) {
            this.switchInputs($(event.target).get('value'), this.fields.getSelected()[0].getAttribute('type'));
        }.bind(this));
        this.checkCondition();
        this.operator = this.element.getElement('.filters_operand');
    },
    /**
     * Check the filter's condition option.
     */
    checkCondition: function () {
        var fieldType = this.fields.getSelected()[0].getAttribute('type'),
            isDate = (fieldType == 'datetime' || fieldType == 'date');
        this.conditionOptions.each(function (el) {
            var types;
            if (types = el.retrieve('type')) {
                if (types.contains(fieldType)) {
                    this.condition.grab(el);
                }
                else {
                    el.dispose();
                }
            }
        }, this);
        this.switchInputs(this.condition.get('value'), fieldType);
        this.disableInputField(isDate);
        this.inputs.showDatePickers(isDate);

        if (this.inputs.inputs[0][0].getStyle('display') != 'none') {
            this.inputs.inputs[0][0].focus();
        }
    },
    /**
     * Shows inputs depending on fields' types and filter's condition
     * @param {string} condition Filter condition name
     * @param {string} type Filter field type
     * @function
     * @public
     */
    switchInputs: function (condition, type) {
        if (type == 'boolean') {
            this.inputs.hide();
        }
        else {
            if (condition == 'between') {
                this.inputs.asPeriod();
            } else {
                this.inputs.asScalar();
            }
        }
    },
    /**
     * Disable input fields.
     *
     * @param {boolean} disable Disable input fields?
     */
    disableInputField: function (disable) {
        if (disable) {
            this.inputs.inputs.each(function (input) {
                input[0].setProperty('disabled', true);
                input[0].value = '';
            });
        } else if (this.inputs.inputs[0][0].get('disabled')) {
            this.inputs.inputs.each(function (input) {
                input[0].removeProperty('disabled');
            });
        }
    },
    isEmpty: function () {
        return !((this.fields.getSelected()[0].getAttribute('type') == 'boolean') || this.inputs.hasValues());

        return !this.inputs.hasValues();
    },
    reset: function () {
        this.inputs.removeEvents('click');
        this.fields.removeEvents('change');
        this.condition.removeEvents('change');
        this.removeBtn.removeEvents('click');
        this.element.destroy();
        this.fireEvent('delete', this);
    },
    getValue: function () {
        return this.inputs.getValues(
            new Filter.Clause(
                this.fields.options[this.fields.selectedIndex].value,
                this.condition.options[this.condition.selectedIndex].value,
                this.fields.options[this.fields.selectedIndex].getAttribute('type'),
                (this.operator.offsetParent) ? this.operator.options[this.operator.selectedIndex].value : null
            )
        );
    }
});
/**
 * Query controls.
 *
 * @constructor
 * @param {Elements} els Elements with input fields.
 * @param {Element} applyAction Apply button.
 */
Filter.QueryControls = new Class(/** @lends Filter.QueryControls# */{
    Implements: Events,
    /**
     * Indicate, whether the date picker is used as query control.
     * @type {boolean}
     */
    isDate: false,

    // constructor
    initialize: function (els) {
        Asset.css('datepicker.css');

        /**
         * Holds the query containers.
         * @type {Elements}
         */
        this.containers = els;
        //TODO: Remove the style hidden of the first container from the CSS or HTML!
        this.containers[0].removeClass('hidden');

        /**
         * Holds all input fields, from which input fields for DatePicker will be created.
         * @type {Elements}
         */
        this.inputs = new Elements(this.containers.getElements('input'));
        /**
         * Input elements for DatePicker.
         * @type {Elements}
         */
        this.dpsInputs = new Elements();

        for (var n = 0; n < this.containers.length; n++) {
            this.dpsInputs.push(this.inputs[n][0].clone().addClass('hidden'));
            this.containers[n].grab(this.dpsInputs[n]);
        }

        /**
         * DatePickers.
         * @type {DatePicker[]}
         */
        this.dps = [];

        this.dpsInputs.each(function (el) {
            this.dps.push(new DatePicker(el, {
                format: '%Y-%m-%d',
                allowEmpty: true,
                useFadeInOut: false
            }));
        }.bind(this));

        this.dpsInputs.concat(this.inputs).addEvent('keydown', function (event) {
            if ((event.key == 'enter') && (event.target.value != '')) {
                this.fireEvent('apply');
            }
        }.bind(this));
    },

    /**
     * Return true if one of the [inputs]{@link Filter.QueryControls#dpsInputs} has a value, otherwise - false.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    hasValues: function () {
        return this[(this.isDate) ? 'dpsInputs' : 'inputs'].some(function (el) {
            return el[0].get('value');
        });
    },

    /**
     * Clear the [input fields]{@link Filter.QueryControls#dpsInputs}.
     * @function
     * @public
     */
    empty: function () {
        this.dpsInputs.concat(this.inputs).each(function (el) {
            el.set('value', '')
        });
    },

    /**
     * Build the filter's pattern string.
     *
     * @function
     * @public
     * @param {string} fieldName The field name from the recordset.
     * @returns {string}
     */
    getValues: function (clause) {
        this[(this.isDate) ? 'dpsInputs' : 'inputs'].each(function (el) {
            clause.setValue(el.get('value').toString());
        });
        return clause;
    },

    /**
     * Enable additional input fields for using the <tt>'between'</tt> filter condition.
     * @function
     * @public
     */
    asPeriod: function () {
        this.show();
        this.dpsInputs.concat(this.inputs).addClass('small');
    },

    /**
     * Enable only one input field for filter.
     * @function
     * @public
     */
    asScalar: function () {
        this.show();
        this.containers[1].addClass('hidden');
        this.dpsInputs.concat(this.inputs).removeClass('small');
    },
    /**
     * Show all inputs
     * @function
     * @public
     */
    show: function () {
        this.containers.removeClass('hidden');
    },
    /**
     * hide inputs
     * @function
     * @public
     */
    hide: function () {
        this.containers.addClass('hidden');
    },
    /**
     * Show/hide date pickers.
     * @function
     * @public
     * @param {boolean} toShow Defines whether the date pickers will be visible (by <tt>true</tt>) or hidden (by <tt>false</tt>).
     */
    showDatePickers: function (toShow) {
        this.isDate = toShow;
        if (toShow) {
            this.inputs.addClass('hidden');
            this.dpsInputs.removeClass('hidden');
        } else {
            this.inputs.removeClass('hidden');
            this.dpsInputs.addClass('hidden');
        }
    }
});
Filter.Clause = new Class({
    value: '',
    initialize: function (fieldName, condition, type, operator) {
        this.field = fieldName;
        this.condition = condition;
        this.type = type;
        this.operator = ('undefined' != typeof operator) ? operator : '';
    },
    setValue: function (value) {
        if (value) {
            if (this.value) {
                (this.value = [this.value]).push(value);
            }
            else {
                this.value = value;
            }
        }
        return this;
    }
});
Filter.Clause.create = function (fieldName, tableName, condition, type) {
    return new Filter.Clause('[' + tableName + '][' + fieldName + ']', condition, type);
};

Filter.ClauseSet = new Class({
    children: [],
    initialize: function () {
        if (arguments.length) {
            Array.each(arguments, function (arg) {
                this.add(arg);
            }, this);
        }

    },
    add: function (clause) {
        this.children.push(clause);
    }
});