/**
 * DOM structure:
 *
 *   <ul class="toolbar">
 *
 *     <!-- Button -->
 *     <li title="{tooltip}">{title}</li>
 *
 *     <!-- Button with icon -->
 *     <li class="icon" style="background-image: url({icon});" title="{title}"></li>
 *
 *   </ul>
 *
 * CSS classes:
 *
 *   icon
 *   highlighted
 *   disabled
 *   separator
 */

var Toolbar = new Class({

    imagesPath:'',

    initialize:function (toolbarName) {
        Asset.css('toolbar.css');
        this.name = toolbarName;
        this.element =
            new Element('ul').addClass('toolbar').addClass('clearfix');
        if (this.name) {
            this.element.addClass(this.name);
        }
        this.controls = [];
    },
    dock:function () {
        this.element.addClass('docked_toolbar');
    },
    undock:function () {
        this.element.removeClass('docked_toolbar');
    },
    getElement:function () {
        return this.element;
    },

    bindTo:function (object) {
        this.boundTo = object;
        return this;
    },

    load:function (toolbarDescr) {
        Array.each(toolbarDescr.childNodes, function (elem) {
            if (elem.nodeType == 1) {
                var control = null;
                switch (elem.getAttribute('type')) {
                    case 'button':
                        control =
                            new Toolbar.Button;
                        break;
                    case 'separator':
                        control =
                            new Toolbar.Separator;
                        break;
                }
                if (control) {
                    control.load(elem);
                    this.appendControl(control);
                }
            }
        }, this);

    },
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

        return this;
    },

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
        return this;
    },

    getControlById:function (id) {
        for (var i = 0; i < this.controls.length; i++) {
            if (this.controls[i].properties.id == id) return this.controls[i];
        }
        return false;
    },

    disableControls:function () {
        if (!arguments.length) {
            this.controls.each(function (control) {
                if (control.properties.id != 'close') control.disable();
            });
        }
        else {
            var control;
            //Перечисляем идентификаторы контролов которые необходимо активировать
            Array.from(arguments).each(function (controlID) {
                if (control = this.getControlById(controlID)) {
                    control.disable();
                }
            }, this);
        }
        return this;
    },

    enableControls:function () {
        if (!arguments.length) {
            this.controls.each(function (control) {
                control.enable();
            });
        }
        else {
            var control;
            //Перечисляем идентификаторы контролов которые необходимо активировать
            Array.from(arguments).each(function (controlID) {
                if (control = this.getControlById(controlID)) {
                    control.enable();
                }
            }, this);
        }
        return this;
    },

    allButtonsUp: function() {
        this.controls.each(function (control) {
            if (control instanceof Toolbar.Button) {
                control.up();
            }
        });
        return this;
    },

    // Private methods:

    _callAction:function (action, data) {
        if (this.boundTo && typeOf(this.boundTo[action]) == 'function') {
            this.boundTo[action](data);
        }
    }
});

Toolbar.Control = new Class({

    toolbar:null,

    initialize:function (properties) {
        this.properties = {
            id:null,
            icon:null,
            title:'',
            tooltip:'',
            action:null,
            disabled:false,
            initially_disabled: false
        };
        Object.append(this.properties, properties);
    },
    load:function (controlDescr) {
        this.properties.id = controlDescr.getAttribute('id') || '';
        this.properties.icon = controlDescr.getAttribute('icon') || '';
        this.properties.title = controlDescr.getAttribute('title') || '';
        this.properties.action = controlDescr.getAttribute('action') || '';
        this.properties.tooltip = controlDescr.getAttribute('tooltip') || '';
        this.properties.disabled =
            controlDescr.getAttribute('disabled') ? true : false;
        this.properties.initially_disabled = this.properties.disabled;
    },
    buildAsIcon:function (icon) {
        this.element.addClass('icon unselectable')
            .setProperty('id', this.toolbar.name + this.properties.id)
            .setProperty('title', this.properties.title +
            (this.properties.tooltip ? ' (' + this.properties.tooltip +
                ')' : ''))
            .setStyle('-moz-user-select', 'none')
            .setStyle('background-image', 'url(' + Energine.base +
            this.toolbar.imagesPath + icon + ')');
    },
    build:function () {
        if (!this.toolbar || !this.properties.id) {
            return false;
        }
        this.element = new Element('li').setProperty('unselectable', 'on');
        if (this.properties.icon) {
            this.buildAsIcon(this.properties.icon);
            //.setHTML('&#160;');
        }
        else {
            this.element.setProperty('title', this.properties.tooltip).appendText(this.properties.title);
        }

        if (this.properties.disabled) {
            this.disable();
        }
    },

    disable:function () {
        this.properties.disabled = true;
        this.element.addClass('disabled').setStyle('opacity', 0.25);
        return this;
    },

    enable:function (force) {
        force = force || false;
        if (force) {
            this.properties.initially_disabled = false;
        }
        if (!this.properties.initially_disabled) {
            this.properties.disabled = false;
            this.element.removeClass('disabled').setStyle('opacity', 1);
        }
        return this;
    },

    disabled:function() {
        return this.properties.disabled;
    },

    initially_disabled:function() {
        return this.properties.initially_disabled;
    },

    setAction:function (action) {
        this.properties.action = action;
    }
});


Toolbar.Button = new Class({
    Extends:Toolbar.Control,
    callAction:function (data) {
        if (!this.properties.disabled) {
            this.toolbar._callAction(this.properties.action, data);
        }
    },
    down: function() {
        this.element.addClass('pressed');
        return this;
    },
    up: function() {
        this.element.removeClass('pressed');
        return this;
    },
    isDown: function() {
        return this.element.hasClass('pressed');
    },
    build:function () {
        this.parent();
        var control = this;
        this.element.addEvents({
            'mouseover':function () {
                if (!control.properties.disabled) {
                    this.addClass('highlighted');
                }
            },
            'mouseout':function () {
                this.removeClass('highlighted');
            }});
        if (Browser.chrome) {
            this.element.addEvents({
                'click':this.callAction.bind(this),
                'mousedown':function () {
                    return false;
                }
            })
        }
        else {
            this.element.addEvent('mousedown', function (event) {
                if(!event) return;

                if (event.rightClick) return;
                this.callAction();
            }.bind(this));
        }

    }
});

Toolbar.File = new Class({
    Extends:Toolbar.Button,
    callAction:function () {
        this.element.getElementById(this.properties.id).click();
    },
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
                        if(!obj.properties.disabled)
                            obj.toolbar._callAction(obj.properties.action, e.target);
                    }
                })(file);
                reader.readAsDataURL(file);
            }.bind(this)
        }
        }));
    }
});

Toolbar.Switcher = new Class({
    Extends:Toolbar.Button,
    initialize:function (props) {
        this.parent(props);
        this.properties.state =
            new Boolean((this.properties.state || 0).toInt()).valueOf();
    },
    load:function (controlDescr) {
        this.parent(controlDescr);
        this.properties.aicon = controlDescr.getAttribute('aicon') || '';
        this.properties.state = controlDescr.getAttribute('state') || 0;
    },
    build:function () {
        this.parent();
        var toggle = (function () {
            if (this.properties.state) {
                if (this.properties.aicon)
                    this.buildAsIcon(this.properties.aicon);
                else
                    this.element.addClass('pressed');

            }
            else {
                if (this.properties.icon)
                    this.buildAsIcon(this.properties.icon);
                else
                    this.element.removeClass('pressed');
            }
        }).bind(this);
        this.element.addEvent('click', function () {
            if (!this.properties.disabled) {
                this.properties.state = (!this.properties.state);
                toggle();
            }
        }.bind(this));
        toggle();
    },
    getState:function () {
        return this.properties.state;
    }
});

Toolbar.Separator = new Class({
    Extends:Toolbar.Control,
    build:function () {
        this.parent();
        this.element.addClass('separator');
    },

    disable:function () {
        // Separator cannot be disabled.
    }
});
Toolbar.Text = new Class({
    Extends:Toolbar.Control,
    build:function () {
        this.parent();
        this.element.addClass('text');
    }
});
Toolbar.Select = new Class({
    Extends:Toolbar.Control,
    select:null,
    toolbar:null,

    initialize:function (properties, options, initialValue) {
        this.properties = {
            id:null,
            title:'',
            tooltip:'',
            action:null,
            disabled:false
        };
        Object.append(this.properties, properties);

        this.options = options || {};
        this.initial = initialValue || false;
    },

    build:function () {
        if (!this.toolbar || !this.properties.id) {
            return false;
        }

        this.element =
            new Element('li').setProperty('unselectable', 'on').addClass('select');
        if (this.properties.title) this.element.adopt(new Element('span').addClass('label').set('text', this.properties.title));
        this.select = new Element('select');

        var control = this;
        this.select.addEvent('change', function () {
            control.toolbar._callAction(control.properties.action, control);
        });

        this.element.adopt(this.select);

        if (this.properties.disabled) {
            this.disable();
        }
        var props = {};
        Object.each(this.options, function (value, key) {
            props = {'value':key};
            if (key == this.initial) {
                props.selected = 'selected';
            }
            control.select.adopt(
                new Element('option').setProperties(props).set('text', value));

        }, this);

    },

    disable:function () {
        if (!this.properties.disabled) {
            this.properties.disabled = true;
            this.select.setProperty('disabled', 'disabled');
        }
    },

    enable:function () {
        if (this.properties.disabled) {
            this.properties.disabled = false;
            this.select.removeProperty('disabled');
        }
    },

    setAction:function (action) {
        this.properties.action = action;
    },
    getValue:function () {
        return this.select.getSelected().getLast().get('value');
    },
    /**
     * Устанавливает выделенный элемент
     * @param int itemId
     */
    setSelected:function (itemId) {
        //Если существует такая опция
        if (this.options[itemId]) {
            //Элемент уже построен
            if (this.select) {
                this.select.getElement('option[value="' + itemId +
                    '"]').setProperty('selected', 'selected');
            }
            else {

            }
        }
    }
});

Toolbar.CustomSelect = new Class({
    Extends:Toolbar.Control,
    select:null,
    view: null,
    button: null,
    dropbox: null,
    options_container: null,
    expanded: false,
    toolbar:null,

    initialize:function (properties, options, initialValue) {
        this.properties = {
            id:null,
            title:'',
            tooltip:'',
            action:null,
            action_before:null,
            disabled:false
        };
        Object.append(this.properties, properties);

        this.options = options || {};
        this.initial = initialValue || false;
    },

    build:function () {

        if (!this.toolbar || !this.properties.id) {
            return false;
        }

        this.element =
            new Element('li').addClass('custom_select');
        if (this.properties.title) this.element.adopt(new Element('span').addClass('label').set('text', this.properties.title));
        this.select = new Element('div').addClass('custom_select_box');
        this.view = new Element('div').addClass('custom_select_view');
        this.button = new Element('div').addClass('custom_select_button');
        this.dropbox = new Element('div').addClass('custom_select_dropbox');
        this.options_container = new Element('div').addClass('custom_select_options');
        this.dropbox.adopt(this.options_container);
        this.select.adopt(this.view);
        this.select.adopt(this.button);
        this.select.adopt(this.dropbox);

        var control = this;

        this.select.addEvent('afterchange', function () {
            control.toolbar._callAction(control.properties.action, control);
            return false;
        });

        this.select.addEvent('beforechange', function () {
            control.toolbar._callAction(control.properties.action_before, control);
            return false;
        });

        this.element.adopt(this.select);

        if (this.properties.disabled) {
            this.disable();
        }
        var props = {};
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
                control.select.fireEvent('afterchange');
                return false;
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
            el.addEvent('selectstart', function(e) {e.stop(); return false;});
            el.addEvent('mousedown', function(e) {e.stop(); return false;});
            el.addEvent('click', function(e) {e.stop(); return false;});
        };

        disableSelection(this.element);
        disableSelection(this.view);
        disableSelection(this.button);
        disableSelection(this.dropbox);
        disableSelection(this.options_container);

        this.collapse();
    },

    toggle: function(e) {
        e.stop();
        this.select.fireEvent('beforechange');
        if (this.expanded) {
            this.collapse();
        } else {
            this.expand();
        }
        return false;
    },

    expand: function() {
        if (!this.properties.disabled) {
            this.expanded = true;
            this.dropbox.show();
        }
    },

    collapse: function() {
        this.expanded = false;
        this.dropbox.hide();
    },

    disable:function () {
        if (!this.properties.disabled) {
            this.properties.disabled = true;
            this.select.addClass('disabled');
        }
    },

    enable:function () {
        if (this.properties.disabled) {
            this.properties.disabled = false;
            this.select.removeClass('disabled');
        }
    },

    setAction:function (action) {
        this.properties.action = action;
    },

    getOptions: function() {
        return this.options;
    },

    getValue:function () {
        var selected = this.select.getElements('.selected').getLast();
        if (!selected) return false;
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