/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Toolbar]{@link Toolbar}</li>
 *     <li>[Toolbar.Control]{@link Toolbar.Control}</li>
 *     <li>[Toolbar.Button]{@link Toolbar.Button}</li>
 *     <li>[Toolbar.File]{@link Toolbar.File}</li>
 *     <li>[Toolbar.Switcher]{@link Toolbar.Switcher}</li>
 *     <li>[Toolbar.Separator]{@link Toolbar.Separator}</li>
 *     <li>[Toolbar.Text]{@link Toolbar.Text}</li>
 *     <li>[Toolbar.Select]{@link Toolbar.Select}</li>
 *     <li>[Toolbar.CustomSelect]{@link Toolbar.CustomSelect}</li>
 * </ul>
 *
 * @requires Energine
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.0.2
 */

/**
 * Abstract toolbar. It uses next CSS classes: icon, highlighted, disabled, separator.
 *
 * @example <caption>DOM structure</caption>
 *   &ltul class="toolbar"&gt
 *     &lt!-- Button --&gt
 *     &ltli title="{tooltip}"&gt{title}&lt/li&gt
 *     &lt!-- Button with icon --&gt
 *     &ltli class="icon" style="background-image: url({icon});" title="{title}"&gt&lt/li&gt
 *   &lt/ul&gt
 *
 * @constructor
 * @param {string} toolbarName The name of the toolbar.
 */
var Toolbar = new Class(/** @lends Toolbar# */{
    /**
     * Object to which the toolbar is bounded.
     * @type {Object}
     */
    boundTo: null,

    /**
     * Array of [controlls]{@link Toolbar.Control}.
     * @type {Array}
     */
    controls: [],

    // constructor
    initialize:function (toolbarName) {
        Asset.css('toolbar.css');

        /**
         * The toolbar name.
         * @type {string}
         */
        this.name = toolbarName;

        /**
         * The main holder element.
         * @type {Element}
         */
        this.element = new Element('ul').addClass('toolbar').addClass('clearfix');
        if (this.name) {
            this.element.addClass(this.name);
        }
    },

    /**
     * Dock the toolbar.
     * @function
     * @public
     */
    dock:function () {
        this.element.addClass('docked_toolbar');
    },

    /**
     * Undock the toolbar.
     * @function
     * @public
     */
    undock:function () {
        this.element.removeClass('docked_toolbar');
    },

    /**
     * Get the toolbar element.
     *
     * @function
     * @public
     * @returns {Element}
     */
    getElement:function () {
        return this.element;
    },

    /**
     * Bind the toolbar to the specific object.
     *
     * @function
     * @public
     * @param {Object} object Object to which the toolbar will be bounded.
     */
    bindTo:function (object) {
        this.boundTo = object;
    },

    /**
     * Load
     * @function
     * @public
     * @param {Element} toolbarDescr
     */
    load:function (toolbarDescr) {
        Array.each(toolbarDescr.childNodes, function (elem) {
            if (elem.nodeType == 1) {
                var control = null;
                switch (elem.getAttribute('type')) {
                    case 'button':
                        control = new Toolbar.Button;
                        break;
                    case 'separator':
                        control = new Toolbar.Separator;
                        break;
                }
                if (control) {
                    control.load(elem);
                    this.appendControl(control);
                }
            }
        }, this);
    },

    /**
     * Append the control(s) from arguments.
     *
     * @function
     * @public
     * @param {} arguments
     */
    appendControl:function () {
        Array.each(arguments, function (control) {
            if (control.type && control.id) {
                control.action = control.onclick;
                delete control.onclick;
                control = new Toolbar[control.type.capitalize()](control);
            }

            if (control instanceof Toolbar.Control) {
                control.toolbar = this;
                control.build();
                this.element.adopt(control.element);
                this.controls.push(control);
            }
        }, this);
    },

    /**
     * Remove the specific control.
     *
     * @function
     * @public
     * @param {Toolbar.Control} control Control element that will be removed.
     */
    removeControl:function (control) {
        if (typeOf(control) == 'string') {
            control = this.getControlById(control);
        }
        if (control instanceof Toolbar.Control) {
            this.controls.each(function (ctrl, index) {
                if (ctrl == control) {
                    ctrl.toolbar = null;
                    ctrl.element.dispose();
                    this.controls.splice(index, 1);
                }
            }, this);
        }
    },

    /**
     * Get the control element by his ID.
     *
     * @function
     * @public
     * @param {number} id ID of the control.
     */
    getControlById:function (id) {
        for (var i = 0; i < this.controls.length; i++) {
            if (this.controls[i].properties.id == id) {
                return this.controls[i];
            }
        }
        return null;
    },

    /**
     * Disable the controls.
     * @function
     * @public
     */
    disableControls:function () {
        if (!arguments.length) {
            this.controls.each(function (control) {
                if (control.properties.id != 'close') control.disable();
            });
        } else {
            var control;
            //Перечисляем идентификаторы контролов которые необходимо активировать
            Array.from(arguments).each(function (controlID) {
                if (control = this.getControlById(controlID)) {
                    control.disable();
                }
            }, this);
        }
    },

    /**
     * Enable the controls.
     * @function
     * @public
     */
    enableControls:function () {
        if (!arguments.length) {
            this.controls.each(function (control) {
                control.enable();
            });
        } else {
            var control;
            //Перечисляем идентификаторы контролов которые необходимо активировать
            Array.from(arguments).each(function (controlID) {
                if (control = this.getControlById(controlID)) {
                    control.enable();
                }
            }, this);
        }
    },

    /**
     * Set all controls up.
     * @function
     * @public
     */
    allButtonsUp: function() {
        this.controls.each(function (control) {
            if (control instanceof Toolbar.Button) {
                control.up();
            }
        });
    },

    /**
     * Call the action.
     *
     * @function
     * @public
     * @param {string} action Action name.
     * @param {*} data Argumet(s) for the action function.
     */
    callAction:function (action, data) {
        if (this.boundTo && typeOf(this.boundTo[action]) == 'function') {
            this.boundTo[action](data);
        }
    }
});

/**
 * Abstract control element for the [Toolbar]{@link Toolbar}.
 *
 * @constructor
 * @param {Object} [properties] [Properties]{@link Toolbar.Control#properties} for the control element.
 */
Toolbar.Control = new Class(/** @lends Toolbar.Control# */{
    /**
     * Toolbar to which the control is connected.
     * @type {Toolbar}
     */
    toolbar:null,

    /**
     * Control properties.
     * @type {Object}
     *
     * @property {string|number} [id = ''] Control ID.
     * @property {string} [icon = ''] Control icon.
     * @property {string} [title = ''] Control title.
     * @property {string} [tooltip = ''] Control tooltip.
     * @property {string} [action = ''] Control action
     * @property {boolean} [disabled = false] Defines if the control is disables or not.
     * @property {boolean} [initially_disabled = false] Defiens the initial value of [disabled]{@link Toolbar.Control#properties#disabled} property.
     */
    properties: {
        id: '',
        icon: '',
        title: '',
        tooltip: '',
        action: '',
        disabled: false,
        initially_disabled: false
    },

    // constructor
    initialize:function (properties) {
        Object.append(this.properties, properties);
    },

    /**
     * Load the properties.
     *
     * @function
     * @public
     * @param {Element} controlDescr Element with properties in the attributes.
     */
    load:function (controlDescr) {
        this.properties.id = controlDescr.getAttribute('id') || '';
        this.properties.icon = controlDescr.getAttribute('icon') || '';
        this.properties.title = controlDescr.getAttribute('title') || '';
        this.properties.action = controlDescr.getAttribute('action') || '';
        this.properties.tooltip = controlDescr.getAttribute('tooltip') || '';
        this.properties.isDisabled = !!controlDescr.getAttribute('disabled');
        this.properties.isInitiallyDisabled = this.properties.isDisabled;
    },

    /**
     * Build the control as an icon.
     *
     * @function
     * @public
     * @param {string} icon Icon url.
     */
    buildAsIcon:function (icon) {
        this.element
            .addClass('icon unselectable')
            .setProperties({
                'id': this.toolbar.name + this.properties.id,
                'title': this.properties.title + (this.properties.tooltip ? ' (' + this.properties.tooltip + ')' : '')
            })
            .setStyles({
                '-moz-user-select': 'none',
                'background-image': 'url(' + Energine.base + icon + ')'
            })
    },

    /**
     * Build the control.
     * @function
     * @public
     */
    build:function () {
        if (!this.toolbar || !this.properties.id) {
            return;
        }

        this.element = new Element('li').setProperty('unselectable', 'on');

        if (this.properties.icon) {
            this.buildAsIcon(this.properties.icon);
            //.setHTML('&#160;');
        } else {
            this.element.setProperty('title', this.properties.tooltip).appendText(this.properties.title);
        }

        if (this.properties.isDisabled) {
            this.disable();
        }
    },

    /**
     * Disable the control.
     * @function
     * @public
     */
    disable:function () {
        this.properties.isDisabled = true;
        this.element.addClass('disabled').setStyle('opacity', 0.25);
    },

    /**
     * Enable the control.
     *
     * @function
     * @public
     * @param {boolean} force
     */
    enable:function (force) {
        force = force || false;
        if (force) {
            this.properties.isInitiallyDisabled = false;
        }
        if (!this.properties.isInitiallyDisabled) {
            this.properties.isDisabled = false;
            this.element.removeClass('disabled').setStyle('opacity', 1);
        }
    },

    /**
     * Get whether the control is displayed.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    disabled:function() {
        return this.properties.isDisabled;
    },

    /**
     * Get whether the control is initially displayed.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    initially_disabled:function() {
        return this.properties.isInitiallyDisabled;
    },

    /**
     * Set the action to the control.
     *
     * @function
     * @public
     * @param {string} action Action name.
     */
    setAction:function (action) {
        this.properties.action = action;
    }
});

/**
 * Abstract button for the [Toolbar]{@link Toolbar}
 *
 * @augments Toolbar.Control
 *
 * @constructor
 * @param {Object} [properties] [Properties]{@link Toolbar.Control#properties} for the button.
 */
Toolbar.Button = new Class(/** @lends Toolbar.Button# */{
    Extends: Toolbar.Control,

    /**
     * Build the button.
     * @function
     * @public
     */
    build:function () {
        this.parent();
        this.element.addClass(this.properties.id + '_btn');
        var control = this;
        this.element.addEvents({
            'mouseover':function () {
                if (!control.properties.isDisabled) {
                    this.addClass('highlighted');
                }
            },
            'mouseout':function () {
                this.removeClass('highlighted');
            }});

        if (Browser.chrome) {
            this.element.addEvents({
                'click': this.callAction.bind(this),
                'mousedown': function (ev) {
                    ev.stop();
                }
            });
        } else {
            this.element.addEvent('click', function (event) {
                if(event && !event.rightClick) {
                    this.callAction();
                }
            }.bind(this));
        }
    },

    /**
     * Call the action function.
     *
     * @function
     * @public
     * @param {*} [data] Argument(s) for the action function.
     */
    callAction:function (data) {
        if (!this.properties.isDisabled) {
            this.toolbar.callAction(this.properties.action, data);
        }
    },

    /**
     * Set the button state to 'down'.
     * @function
     * @public
     */
    down: function() {
        this.element.addClass('pressed');
    },

    /**
     * Set the button state to 'up'.
     * @function
     * @public
     */
    up: function() {
        this.element.removeClass('pressed');
    },

    /**
     * Get the button state.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    isDown: function() {
        return this.element.hasClass('pressed');
    }
});

/**
 * Abstract file as button for the [toolbar]{@link Toolbar}.
 *
 * @augments Toolbar.Button
 *
 * @constructor
 * @param {Object} [properties] [Properties]{@link Toolbar.Control#properties} for the file control element.
 */
Toolbar.File = new Class(/** @lends Toolbar.File# */{
    Extends: Toolbar.Button,

    /**
     * Build the file.
     * @function
     * @public
     */
    build:function () {
        this.parent();
        var obj = this;
        this.element.grab(new Element('input', {'type':'file', 'id':this.properties.id, 'events':{
            'change':function (evt) {
                // Это обработчик для control type= file в ие он наверное не работает , но в данном случае это не сильно принципиально
                var file = evt.target.files[0];
                var reader = new FileReader();
                reader.onload = (function (theFile) {
                    return function (e) {
                        if(!obj.properties.isDisabled) {
                            obj.toolbar.callAction(obj.properties.action, e.target);
                        }
                    }
                })(file);
                reader.readAsDataURL(file);
            }.bind(this)
        }
        }));
    },

    /**
     * Call the action.
     * @function
     * @public
     */
    callAction:function () {
        this.element.getElementById(this.properties.id).click();
    }
});

/**
 * Abstract switcher of the [toolbar]{@link Toolbar}.
 *
 * @augments Toolbar.Button
 *
 * @constructor
 * @param {Object} props [Properties]{@link Toolbar.Control#properties} for the switcher.
 */
Toolbar.Switcher = new Class(/** @lends Toolbar.Switcher# */{
    Extends:Toolbar.Button,

    /**
     * Control properties.
     * @member {Object} Toolbar.Switcher#properties
     *
     * @property {string|number} [id = ''] Control ID.
     * @property {string} [icon = ''] Control icon.
     * @property {string} [title = ''] Control title.
     * @property {string} [tooltip = ''] Control tooltip.
     * @property {string} [action = ''] Control action
     * @property {boolean} [disabled = false] Defines if the control is disables or not.
     * @property {boolean} [initially_disabled = false] Defiens the initial value of [disabled]{@link Toolbar.Control#properties#disabled} property.
     * @property {boolean|string|number} [state] State property.
     * @property {string} [aicon] Aicon property.
     */

    //constructor
    initialize:function (props) {
        this.parent(props);
        this.properties.state = (this.properties.state) ? !!(this.properties.state.toInt()) : false;
    },

    /**
     * Build the switcher.
     * @function
     * @public
     */
    build:function () {
        this.parent();
        var toggle = (function () {
            if (this.properties.state) {
                if (this.properties.aicon) {
                    this.buildAsIcon(this.properties.aicon);
                } else {
                    this.element.addClass('pressed');
                }
            } else {
                if (this.properties.icon) {
                    this.buildAsIcon(this.properties.icon);
                } else {
                    this.element.removeClass('pressed');
                }
            }
        }).bind(this);

        this.element.addEvent('click', function () {
            if (!this.properties.isDisabled) {
                this.properties.state = (!this.properties.state);
                toggle();
            }
        }.bind(this));

        toggle();
    },

    /**
     * Load the properties.
     * @function
     * @public
     */
    load:function (controlDescr) {
        this.parent(controlDescr);
        this.properties.aicon = controlDescr.getAttribute('aicon') || '';
        this.properties.state = controlDescr.getAttribute('state') || 0;
    },

    /**
     * Get the switcher state.
     *
     * @function
     * @public
     * @returns {string|number}
     */
    getState:function () {
        return this.properties.state;
    }
});

/**
 * Abstract separator control element of the [toolbar]{@link Toolbar}.
 *
 * @augments Toolbar.Control
 *
 * @constructor
 * @param {Object} props [Properties]{@link Toolbar.Control#properties} for the separator.
 */
Toolbar.Separator = new Class(/** @lends Toolbar.Separator# */{
    Extends: Toolbar.Control,

    /**
     * Build the separator control element.
     * @function
     * @public
     */
    build:function () {
        this.parent();
        this.element.addClass('separator');
    },

    /**
     * Disable the separator.
     * @function
     * @public
     */
    disable:function () {
        // Separator cannot be disabled.
    }
});

/**
 * Abstract text control element of the [toolbar]{@link Toolbar}.
 *
 * @augments Toolbar.Control
 *
 * @constructor
 * @param {Object} props [Properties]{@link Toolbar.Control#properties} for the text control elenemt.
 */
Toolbar.Text = new Class(/** @lends Toolbar.Text# */{
    Extends: Toolbar.Control,

    /**
     * Build the text control element.
     * @function
     * @public
     */
    build:function () {
        this.parent();
        this.element.addClass('text');
    }
});

/**
 * Abstract select control element.
 *
 * @augments Toolbar.Control
 *
 * @constructor
 * @param {Object} props [Properties]{@link Toolbar.Control#properties} for the select control elenemt.
 * @param {Object} [options = {}] Additional options.
 * @param {} [initialValue = false] Initial value. To use this argument without options-argument - simple set the options-argument to <tt>{}</tt>.
 */
Toolbar.Select = new Class(/** @lends Toolbar.Select# */{
    Extends: Toolbar.Control,

    /**
     * Select element.
     * @type {Element}
     */
    select: null,

    /**
     * Control properties.
     * @type {Object}
     *
     * @property {string} [id = null] Control ID.
     * @property {string} [title = ''] Control title.
     * @property {string} [tooltip = ''] Control tooltip.
     * @property {string} [action = null] Control action
     * @property {boolean} [disabled = false] Defines if the control is disables or not.
     */
    properties: {
        id: null,
        title: '',
        tooltip: '',
        action: null,
        disabled: false
    },

    // constructor
    initialize: function (properties, options, initialValue) {
        Object.append(this.properties, properties);

        /**
         * Additional options for the control element.
         * @type {Object}
         */
        this.options = options || {};

        /**
         * Initial value of the control element.
         * @type {*|boolean}
         */
        this.initial = initialValue || false;
    },

    /**
     * Build the control.
     * @function
     * @public
     */
    build: function () {
        if (!this.toolbar || !this.properties.id) {
            return;
        }

        this.element = new Element('li').setProperty('unselectable', 'on').addClass('select');
        if (this.properties.title) {
            this.element.adopt(new Element('span').addClass('label').set('text', this.properties.title));
        }

        this.select = new Element('select');

        var control = this;
        this.select.addEvent('change', function () {
            control.toolbar.callAction(control.properties.action, control);
        });

        this.element.adopt(this.select);

        if (this.properties.isDisabled) {
            this.disable();
        }

//        var props = {};
        Object.each(this.options, function (value, key) {
            var props = {'value':key};
            if (key == this.initial) {
                props.selected = 'selected';
            }
            control.select.adopt(new Element('option').setProperties(props).set('text', value));
        }, this);
    },

    /**
     * Disable the control.
     * @function
     * @public
     */
    disable:function () {
        if (!this.properties.isDisabled) {
            this.properties.isDisabled = true;
            this.select.setProperty('disabled', 'disabled');
        }
    },

    /**
     * Enable the control.
     * @function
     * @public
     */
    enable:function () {
        if (this.properties.isDisabled) {
            this.properties.isDisabled = false;
            this.select.removeProperty('disabled');
        }
    },

    /**
     * Set the action to the control.
     *
     * @function
     * @public
     * @param action
     */
    setAction:function (action) {
        this.properties.action = action;
    },

    /**
     * Get the value from the select control element.
     *
     * @function
     * @public
     * @returns {string}
     */
    getValue:function () {
        return this.select.getSelected().getLast().get('value');
    },

    /**
     * Set the selected element.
     *
     * @function
     * @public
     * @param {number} itemId Item ID
     */
    setSelected:function (itemId) {
        //Если существует такая опция
        if (this.options[itemId]) {
            //Элемент уже построен
            if (this.select) {
                this.select.getElement('option[value="' + itemId +
                    '"]').setProperty('selected', 'selected');
            }
        }
    }
});

/**
 * Abstract custom select control element.
 *
 * @augments Toolbar.Control
 *
 * @constructor
 * @param {Object} props [Properties]{@link Toolbar.Control#properties} for the select control elenemt.
 * @param {Object} [options = {}] Additional options.
 * @param {} [initialValue = false] Initial value. To use this argument without options-argument - simple set the options-argument to <tt>{}</tt>.
 */
Toolbar.CustomSelect = new Class(/** @lends Toolbar.CustomSelect# */{
    Extends:Toolbar.Control,

    /**
     * Select element.
     * @type {Element}
     */
    select:null,

    /**
     * View element.
     * @type {Element}
     */
    view: null,

    /**
     * Button element.
     * @type {Element}
     */
    button: null,

    /**
     * Dropbox element.
     * @type {Element}
     */
    dropbox: null,

    /**
     * Container with options.
     * @type {Element}
     */
    options_container: null,

    /**
     * Defines whether the control element is expanded or not.
     * @type {boolean}
     */
    expanded: false,

    /**
     * Toolbar element.
     * @type {Element}
     */
    toolbar:null,

    /**
     * Control properties.
     * @type {Object}
     *
     * @property {string} [id = null] Control ID.
     * @property {string} [icon = null] Control icon.
     * @property {string} [title = ''] Control title.
     * @property {string} [tooltip = ''] Control tooltip.
     * @property {string} [action = null] Control action.
     * @property {string} [action_before = null] Control first action.
     * @property {boolean} [disabled = false] Defines if the control is disables or not.
     */
    properties: {
        id:null,
        title:'',
        tooltip:'',
        action:null,
        action_before:null,
        disabled:false
    },

    // constructor
    initialize:function (properties, options, initialValue) {
        Object.append(this.properties, properties);

        this.options = options || {};
        this.initial = initialValue || false;
    },

    /**
     * Build the control.
     *
     * @fires Toolbar.CustomSelect#afterchange
     *
     * @function
     * @public
     */
    build:function () {
        if (!this.toolbar || !this.properties.id) {
            return;
        }
        var control = this;

        this.element = new Element('li').addClass('custom_select');
        if (this.properties.title) {
            this.element.adopt(new Element('span').addClass('label').set('text', this.properties.title));
        }
        this.select = new Element('div').addClass('custom_select_box');
        this.view = new Element('div').addClass('custom_select_view');
        this.button = new Element('div').addClass('custom_select_button');
        this.dropbox = new Element('div').addClass('custom_select_dropbox');
        this.options_container = new Element('div').addClass('custom_select_options');

        this.dropbox.adopt(this.options_container);

        this.select.adopt(this.view);
        this.select.adopt(this.button);
        this.select.adopt(this.dropbox);

        this.select.addEvent('afterchange', function () {
            control.toolbar.callAction(control.properties.action, control);
            return;
        });

        this.select.addEvent('beforechange', function () {
            control.toolbar.callAction(control.properties.action_before, control);
            return;
        });

        this.element.adopt(this.select);

        if (this.properties.isDisabled) {
            this.disable();
        }

        Object.each(this.options, function (value, key) {
            var el = new Element('div').addClass('custom_select_option');
            el.setProperty('data-value', key);
            el.set('html', value['html']);
            el.setProperty('data-caption', value['caption']);
            el.setProperty('data-element', value['element']);
            el.setProperty('data-class', value['class']);

            if (key == this.initial) {
                el.addClass('selected');
            }
            control.select.getElement('.custom_select_options').adopt(el);

            el.addEvent('click', function(e) {
                e.stop();
                var val = el.get('data-value');
                control.setSelected(val);

                /**
                 * Event after changes.
                 * @event Toolbar.CustomSelect#afterchange
                 */
                control.select.fireEvent('afterchange');
                return;
            }.bind(this));
        }, this);

        this.view.addEvent('click', this.toggle.bind(this));
        this.button.addEvent('click', this.toggle.bind(this));

        document.addEvent('click', function(e) {
            if (this.expanded) {
                this.collapse();
            }
        }.bind(this));

        var disableSelection = function(el) {
            el.setProperty('unselectable', 'on');
            el.setStyle('-moz-user-select', 'none');
            el.setStyle('-khtml-user-select', 'none');
            el.setStyle('-webkit-user-select', 'none');
            el.setStyle('-o-user-select', 'none');
            el.setStyle('-ms-user-select', 'none');
            el.setStyle('user-select', 'none');

            el.addEvent('selectstart', function(e) {
                e.stop();
            });
            el.addEvent('mousedown', function(e) {
                e.stop();
            });
            el.addEvent('click', function(e) {
                e.stop();
            });
        };

        disableSelection(this.element);
        disableSelection(this.view);
        disableSelection(this.button);
        disableSelection(this.dropbox);
        disableSelection(this.options_container);

        this.collapse();
    },

    /**
     * Toggle the control element.
     *
     * @fires Toolbar.CustomSelect#beforechange
     *
     * @function
     * @public
     * @param {Object} e Default event.
     */
    toggle: function(e) {
        e.stop();
        /**
         * Event before changes.
         * @event Toolbar.CustomSelect#beforechange
         */
        this.select.fireEvent('beforechange');
        (this.expanded) ? this.collapse() : this.expand();
    },

    /**
     * Expand the control.
     * @function
     * @public
     */
    expand: function() {
        if (!this.properties.isDisabled) {
            this.expanded = true;
            this.dropbox.show();
        }
    },

    /**
     * Collapse the control.
     * @function
     * @public
     */
    collapse: function() {
        this.expanded = false;
        this.dropbox.hide();
    },

    /**
     * Disable the control.
     * @function
     * @public
     */
    disable:function () {
        if (!this.properties.isDisabled) {
            this.properties.isDisabled = true;
            this.select.addClass('disabled');
        }
    },

    /**
     * Enable the control.
     * @function
     * @public
     */
    enable:function () {
        if (this.properties.isDisabled) {
            this.properties.isDisabled = false;
            this.select.removeClass('disabled');
        }
    },

    /**
     * Get the control [options]{@link Toolbar.CustomSelect#options}.
     *
     * @function
     * @public
     * @returns {Object}
     */
    getOptions: function() {
        return this.options;
    },

    /**
     * Get the value of the selected element from [select element]{@link Toolbar.CustomSelect#select}.
     *
     * @function
     * @public
     * @returns {Object}
     */
    getValue:function () {
        var selected = this.select.getElements('.selected').getLast();
        if (!selected) {
            return null;
        }
        return {
            'value': selected.get('data-value'),
            'element': selected.get('data-element'),
            'class': selected.get('data-class')
        };
    },

    setSelected:function (itemId) {
        if (this.options[itemId] && this.select) {
            this.select.getElements('.custom_select_option').removeClass('selected');
            this.select.getElements('.custom_select_option[data-value="' + itemId + '"]').addClass('selected', 'selected');
            this.view.set('text', this.options[itemId].caption);
            this.collapse();
        }
    }
});