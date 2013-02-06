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
        if ($type(control) == 'string') {
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
            $A(arguments).each(function (controlID) {
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
            $A(arguments).each(function (controlID) {
                if (control = this.getControlById(controlID)) {
                    control.enable();
                }
            }, this);
        }
        return this;
    },

    // Private methods:

    _callAction:function (action, data) {
        if (this.boundTo && $type(this.boundTo[action]) == 'function') {
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
            disabled:false
        };
        $extend(this.properties, $pick(properties, {}));
    },
    load:function (controlDescr) {
        this.properties.id = controlDescr.getAttribute('id') || '';
        this.properties.icon = controlDescr.getAttribute('icon') || '';
        this.properties.title = controlDescr.getAttribute('title') || '';
        this.properties.action = controlDescr.getAttribute('action') || '';
        this.properties.tooltip = controlDescr.getAttribute('tooltip') || '';
        this.properties.disabled =
            controlDescr.getAttribute('disabled') ? true : false;
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
        this.element.addClass('disabled').setOpacity(0.25);
        return this;
    },

    enable:function () {
        this.properties.disabled = false;
        this.element.removeClass('disabled').setOpacity(1);
        return this;
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
        this.element.addEvent('mousedown', function () {
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
        $extend(this.properties, $pick(properties, {}));

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
                this.select.getElement('option[value=' + itemId +
                    ']').setProperty('selected', 'selected');
            }
            else {

            }
        }
    }


});
