/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[LayoutManager]{@link LayoutManager}</li>
 *     <li>[LayoutManager.Column]{@link LayoutManager.Column}</li>
 *     <li>[LayoutManager.DummyWidget]{@link LayoutManager.}</li>
 *     <li>[LayoutManager.Widget]{@link LayoutManager.Widget}</li>
 *     <li>[LayoutManager.Widget.DragBehavior]{@link LayoutManager.Widget.DragBehavior}</li>
 *     <li>[LayoutManager.Component]{@link LayoutManager.Component}</li>
 *     <li>[LayoutManager.Component.Param]{@link LayoutManager.Component.Param}</li>
 *     <li>[PseudoXML]{@link PseudoXML}</li>
 *     <li>Extention to the MooTools [Element]{@link Element} class</li>
 * </ul>
 *
 * @requires Energine
 * @requires Overlay
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.0.1
 */

ScriptLoader.load('Overlay');

/**
 * @class Element
 * @classdesc Extention to the MooTools Element class.
 * @see [Element]{@link http://mootools.net/docs/core/Element/Element} for further information.
 */

/**
 * Расширяем прототип Element'а
 * для работы с псевдо-xml узлами в виде обычных DIV с доп атрибутом xmltag
 */
Element.implement(/** @lends Element# */{
    /**
     * Get XML elements.
     *
     * @function
     * @public
     * @param {string} tag Value of the <tt>'xmltag'</tt> attribute.
     * @returns {Elements}
     */
    getXMLElements: function (tag) {
        return this.getElements('[xmltag=' + tag + ']');
    },

    /**
     * Get XML element.
     *
     * @function
     * @public
     * @param {string} tag Value of the <tt>'xmltag'</tt> attribute.
     * @returns {Elements}
     */
    getXMLElement: function (tag) {
        return this.getElement('[xmltag=' + tag + ']');
    },

    /**
     * Get all pseudo XML as string.
     *
     * @function
     * @public
     * @returns {string}
     */
    asXMLString: function () {
        return '<?xml version="1.0" encoding="utf-8" ?>' + PseudoXML.getElementAsXMLString(this);
    }

});

/**
 * Layout manager.
 *
 * @constructor
 * @param {string} singlePath Path.
 */
var LayoutManager = new Class(/** @lends LayoutManager# */{
    Static: {
        /**
         * Single path.
         *
         * @memberOf LayoutManager
         * @static
         * @type {string}
         */
        singlePath: '',

        /**
         * Defines whether the layout is changed.
         *
         * @memberOf LayoutManager
         * @static
         * @type {boolean}
         */
        changed: false,

        /**
         * Main frame element.
         *
         * @memberOf LayoutManager
         * @static
         * @type {Element}
         */
        mFrame: null
    },

    /**
     * Array like object of LayoutManager.Column.
     * @type {Object}
     */
    columns: {},

    /**
     * 'content' node of the pseudo XML structure.
     * @type {Element}
     */
    xml: null,

    // constructor
    initialize: function (singlePath) {
        LayoutManager.singlePath = singlePath;
        LayoutManager.mFrame = document.getElement('.e-mainframe');

        // Получаем XML структуру текущего документа
        new Request({
            url: document.location.href,
            method: 'get',
            data: 'struct',
            onSuccess: function (text, xml) {
                this.setup(xml);
            }.bind(this)
        }).send();
    },

    /**
     * Setup the layout manager.
     *
     * @function
     * @public
     * @param {} xml XML document structure.
     */
    setup: function (xml) {
        Asset.css('layout_manager.css');
        this.createToolbar();
        /*
         * this.xml содержит Element с псевдо-xml структурой
         * нас интересует только узел content
         */
        this.xml = PseudoXML.createPseudoXML(xml.documentElement).getXMLElements('content')[0];

        //Проходимся по всем контейнерам контента
        this.xml.getXMLElements('container').each(function (xml) {
            //И создаем для каждого контейнера, который выступает в качестве колонки  - соответствующий объект колонки
            if (xml.getProperty('data-column')) {
                this.columns[xml.getProperty('data-name')] = new LayoutManager.Column(xml, this);
            }
        }, this);
    },

    /**
     * Create toolbar.
     * @function
     * @public
     */
    createToolbar: function () {
        /**
         * Toolbar.
         * @type {Toolbar}
         */
        this.toolbar = new Toolbar('block_management_toolbar');
        this.toolbar.dock();
        this.toolbar.getElement().inject(
            document.getElement('.e-topframe')
        );
        this.toolbar.bindTo(this);

        var html = $$('html')[0];
        if (html.hasClass('e-has-topframe2')) {
            html.removeClass('e-has-topframe2');
            html.addClass('e-has-topframe3');
        } else if (html.hasClass('e-has-topframe1')) {
            html.removeClass('e-has-topframe1');
            html.addClass('e-has-topframe2');
        }

        //получаем данные селекта с вариантами содержимого
        new Request.JSON({
            url: LayoutManager.singlePath + 'get-template-info/',
            method: 'get',
            onSuccess: function (result) {
                if (!result.result) {
                    return;
                }

                this.toolbar.appendControl(
                    new Toolbar.Text({
                        id: 'text3',
                        title: result.data.layout.title
                    }),
                    new Toolbar.Text({
                        id: 'text4',
                        title: result.data.layout.name,
                        tooltip: result.data.layout.file
                    }),
                    new Toolbar.Text({
                        id: 'text1',
                        title: result.data.content.title
                    }),
                    new Toolbar.Text({
                        id: 'text2',
                        title: result.data.content.name
                            + ((result.data.content.modified)
                            ? ' (' + result.data.content.modified + ')'
                            : ''),
                        tooltip: result.data.content.file}),
                    new Toolbar.Separator({id: 'sep1'}),
                    new Toolbar.Select({
                        id: 'actionSelector',
                        title: result.data.actionSelectorText
                    }, result.data.actionSelector, 'save'),
                    new Toolbar.Button({
                        id: 'save',
                        title: result.data.saveText,
                        action: 'applyChanges'
                    }),
                    new Toolbar.Separator({id: 'sep2'}),
                    new Toolbar.Button({
                        id: 'reset',
                        title: result.data.cancelText,
                        action: 'resetChanges'
                    })
                );

                //для красоты
                this.toolbar.getElement().getElement('select').setStyle('width', 'auto');
            }.bind(this)
        }).send();
    },

    /**
     * Apply changes.
     * @function
     * @public
     */
    applyChanges: function () {
        var fRevert = function () {
                new Request.JSON({
                    url: LayoutManager.singlePath + 'widgets/revert-template/' + ((Energine.forceJSON) ? '?json' : ''),
                    method: 'post',
                    evalScripts: false,
                    onSuccess: function (response) {
                        if (response.result) {
                            //document.location = document.location.href;
                        }
                    }
                }).send();
            },
            fReset = function () {
                new Request.JSON({
                    url: LayoutManager.singlePath + 'reset-templates/' + ((Energine.forceJSON) ? '?json' : ''),
                    method: 'post',
                    evalScripts: false,
                    onSuccess: function (response) {
                        if (response.result) {
                            document.location = document.location.href;
                        }
                    }
                }).send();
            },
            fSaveTemplate = function () {
                new Request.JSON({
                    url: LayoutManager.singlePath + 'widgets/save-template/' + ((Energine.forceJSON) ? '?json' : ''),
                    method: 'post',
                    evalScripts: false,
                    data: { 'xml': this.xml.asXMLString() },
                    onSuccess: function (response) {
                        if (response.result) {
                            document.location = document.location.href;
                        }
                    }
                }).send();
            },
            fSaveNewTemplate = function () {
                ModalBox.open({
                    url: LayoutManager.singlePath + 'widgets/show-new-template-form/',
                    onClose: function (result) {
                        if (!result) {
                            return;
                        }
                        new Request.JSON({
                            url: LayoutManager.singlePath + 'widgets/save-new-template/' + ((Energine.forceJSON) ? '?json' : ''),
                            method: 'post',
                            evalScripts: false,
                            data: {
                                'xml': this.xml.asXMLString(),
                                'title': result
                            },
                            onSuccess: function (response) {
                                if (response.result) {
                                    document.location = document.location.href;
                                }
                            }
                        }).send();
                    }.bind(this)
                });
            },
            fSave = function () {
                new Request.JSON({
                    url: LayoutManager.singlePath + 'widgets/save-content/' + ((Energine.forceJSON) ? '?json' : ''),
                    method: 'post',
                    evalScripts: false,
                    data: { 'xml': this.xml.asXMLString() },
                    onSuccess: function (response) {
                        if (response.result) {
                            document.location = document.location.href;
                        }
                    }
                }).send();
            };

        // todo: Why not to merge the above Request.JSONs to the below switch?
        switch (this.toolbar.getElement().getElement('select').get('value')) {
            case 'revert':
                fRevert.apply(this);
                break;
            case 'reset':
                fReset.apply(this);
                break;
            case 'saveTemplate':
                fSaveTemplate.apply(this);
                break;
            case 'saveNewTemplate':
                fSaveNewTemplate.apply(this);
                break;
            default:
                fSave.apply(this);
        }
    },


    /**
     * Reset changes.
     * @function
     * @public
     */
    resetChanges: function () {
        document.location = document.location.href;
    },

    /**
     * Find widget by his coordinates.
     *
     * @function
     * @public
     * @param {number} x X coordinate.
     * @param {number} y Y coordinate.
     * @param {LayoutManager.Widget} currentWidget
     * @returns {LayoutManager.Widget}
     */
    findWidgetByCoords: function (x, y, currentWidget) {
        var cl,
            mFrame = LayoutManager.mFrame;

        Object.some(this.columns, function (clm) {
            return Object.some(clm.widgets, function (wdg) {
                var res = false;
                if (wdg != currentWidget && wdg.visible) {
                    var pos = wdg.container.getPosition(mFrame),
                        size = wdg.container.getSize();

                    if (pos.x <= x && x <= (pos.x + size.x)
                        && pos.y <= y && y <= (pos.y + size.y)) {
                        cl = wdg;
                        res = true;
                    }
                }
                return res;
            })
        });

        return cl;
    },

    /**
     * Get the column by name.
     *
     * @function
     * @public
     * @param {string} columnName Column name.
     */
    getColumn: function (columnName) {
        return this.columns[columnName] || {};
    }
});

/**
 * Column.
 *
 * @constructor
 * @param {HTMLDivElement} xmlDescr Pseudo XML column description.
 * @param {LayoutManager} layoutManager Main layout manager.
 */
LayoutManager.Column = new Class(/** @lends LayoutManager.Column# */{
    /**
     * Array like object with widgets (LayoutManager.Widget)
     * @type {Object}
     */
    widgets: {},

    // constructor
    initialize: function (xmlDescr, layoutManager) {
        /**
         * Pseudo XML column description.
         * @type {HTMLDivElement}
         */
        this.xml = xmlDescr;

        /**
         * Main layout manager.
         * @type {LayoutManager}
         */
        this.layoutManager = layoutManager;

        /**
         * Column name.
         * @type {string}
         */
        this.name = xmlDescr.getProperty('data-name');

        /**
         * Column element.
         * @type {Element}
         */
        this.element = document.getElement('[column=' + this.name + ']');
        //Если существует соответствующий HTML елемент  - представление колонки
        if (this.element) {
            this.element.addClass('e-lm-column');
            //проходимся по всем дочерним контейнерам и добавляем их в this.widgets
            xmlDescr.getXMLElements('container').each(this.addWidgetAsXML, this);
            this.widgets[this.name] = new LayoutManager.DummyWidget(this);
        }
    },

    /**
     * Add widget to the column.
     *
     * @function
     * @public
     * @param {Element} widgetXML Widget XML element.
     * @return {LayoutManager.Widget}
     */
    addWidgetAsXML: function (widgetXML) {
        var widget = null;
        if (widgetXML.getProperty('data-widget')) {
            widget = this.widgets[widgetXML.getProperty('data-name')] = new LayoutManager.Widget(widgetXML, this);
        }
        return widget;
    },

    /**
     * Inject the widget.
     *
     * @function
     * @public
     * @param {LayoutManager.Widget} widget
     * @param {LayoutManager.Widget} [sibling] Widget's sibling. If the <tt>sibling</tt> is not defined, then the widget will be placed at the bottom of the column.
     * @param {string} [location = 'before'] Widget's location relative to the defined sibling in the column: 'before', 'after'.
     */
    injectWidget: function (widget, sibling, location) {
        this.widgets[widget.name] = widget;
//        this.widgets.set(injectedWidget.name, injectedWidget, this);

        if (location != 'before' && location != 'after') {
            location = location || 'before'
        }

        // Если передан sibling - то инжектим. Иначе - просто добавляем в конец.
        if (sibling) {
            (sibling.xml)
                ? widget.xml.inject(sibling.xml, location)
                : this.xml.grab(widget.xml);

            widget.container.inject(sibling.container, location);
        } else {
            this.xml.grab(widget.xml);
            this.element.grab(widget.container);
        }

        LayoutManager.changed = true;
    },

    /**
     * Get the widget by name.
     * @param {string} name Widget name.
     */
    getWidget: function (name) {
        return this.widgets[name] || {};
    }
});

/**
 * Dummy widget.
 *
 * @constructor
 * @param {LayoutManager.Column} column Column of the dummy widget.
 */
LayoutManager.DummyWidget = new Class(/** @lends LayoutManager.DummyWidget# */{
    /**
     * Defines whether the widget is visible.
     * @type {boolean}
     */
    visible: true,

    // constructor
    initialize: function (column) {
        /**
         * Column of the widget.
         * @type {LayoutManager.Column}
         */
        this.column = column;

        /**
         * Main element.
         * @type {Element}
         */
        this.element = this.container = new Element('div', {'class': 'e-lm-dummy-widget'});

        this.column.element.grab(this.element);

        /**
         * Widget's toolbar.
         * @type {Toolbar}
         */
        this.toolbar = this._buildToolbar();
    },

    /**
     * Build the toolbar.
     *
     * @function
     * @protected
     * @returns {Toolbar}
     */
    _buildToolbar: function () {
        var tb = new Toolbar('widgetToolbar_' + this.column.name);
        tb.appendControl(new Toolbar.Button({
            id: 'add',
            icon: 'images/toolbar/add.gif',
            title: 'Add',
            action: 'addWidget'
        }));
        tb.getElement().inject(this.element, 'top');
        tb.bindTo(this);

        return tb;
    },

    /**
     * Find the direction.
     * @function
     * @public
     * @returns {string}
     */
    findDirection: function () {
        return 'before';
    },

    /**
     * Add the widget.
     * @function
     * @public
     */
    addWidget: function () {
        ModalBox.open({
            url: LayoutManager.singlePath + 'widgets/',
            onClose: function (response) {
                if (!response) {
                    return;
                }

                var xml = PseudoXML.createPseudoXML(response),
                    widgetTitle = xml.getProperty('data-dynamic');

                if (!widgetTitle) {
                    new Request({
                        url: LayoutManager.singlePath + 'widgets/build-widget/',
                        method: 'post',
                        evalScripts: false,
                        data: { 'xml': xml.asXMLString() },
                        onSuccess: function (text) {
                            var container = new Element('div').set('html', text),
                                result;

                            if (container.getElement('div')) {
                                result = container.getElement('div').clone();
                                new LayoutManager.Widget(xml, this.column, result, this);
                            }
                            container.destroy();
                        }.bind(this)
                    }).send();
                } else {
                    new LayoutManager.Widget(
                        xml,
                        this.column,
                        new Element('div', {
                            'class': 'dynamic',
                            'text': widgetTitle
                        }),
                        this
                    );
                }
            }.bind(this)
        });
    }
});

/**
 * Widget.
 *
 * @augments LayoutManager.DummyWidget
 *
 * @constructor
 * @param {HTMLDivElement} xmlDescr Pseudo XML widget description.
 * @param {LayoutManager.Column} column
 * @param {Element} [htmlElement] Element for widget.
 * @param {LayoutManager.Widget} [injectBeforeThisWidget] Sibling of the new widget.
 */
LayoutManager.Widget = new Class(/** @lends LayoutManager.Widget */{
    Extends: LayoutManager.DummyWidget,

    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    /**
     * Component.
     * @type {LayoutManager.Component}
     */
    component: {},

    // constructor
    initialize: function (xmlDescr, column, htmlElement, injectBeforeThisWidget) {
        /**
         * Pseudo XML widget description.
         * @type {HTMLDivElement}
         */
        this.xml = xmlDescr;
        this.column = column;
        this.name = xmlDescr.getProperty('data-name');
        this.visible = false;
        // static widget
        this['static'] = false;

        htmlElement = htmlElement || document.getElement('[widget=' + this.name + ']');

        this.element = $(htmlElement);
        if (this.element) {
            if (!this.element.hasClass('e-widget')) {
                this.element.addClass('e-widget');
            }

            this.bindElement(this.element);

            if (injectBeforeThisWidget) {
                this.column.injectWidget(this, injectBeforeThisWidget, 'before');
            }

            this.toolbar = this._buildToolbar();
            this.toolbar.size = this.toolbar.element.getSize();

            if (!this['static']) {
                /**
                 * Dragger.
                 * @type {LayoutManager.Widget.DragBehavior}
                 */
                this.dragger = new LayoutManager.Widget.DragBehavior(this);
            }

            this.overlay = new Overlay(this.element, {indicator: false});
            this.overlay.show();
            this.visible = true;
        }
    },

    /**
     * Bind the widget to the element.
     *
     * @function
     * @public
     * @param {Element} element Element to whicht the widget will be binded.
     */
    bindElement: function (element) {
        //Создаем елемент контейнера  - содержащего тулбар виджета
        /**
         * Container element.
         * @type {Element}
         */
        this.container = new Element('div', {
            'class': 'e-lm-widget'/*,
             styles:{'position': 'relative'}*/
        });

        (this.element.getParent())
            ? this.container.wraps(this.element)
            : this.container.grab(this.element);

        this['static'] = new Boolean(this.element.getProperty('static')).valueOf();
        if (this['static']) {
            this.container.addClass('e-lm-static-widget');
        }
        var c = this.xml.getXMLElement('component');
        if (c /*&& !this.static*/) {
            this.component = new LayoutManager.Component(c, this.element);
        }
    },

    /**
     * Overridden parent [_buildToolbar]{@link LayoutManager.DummyWidget} method.
     *
     * @function
     * @protected
     * @returns {Toolbar}
     */
    _buildToolbar: function () {
        var tb = new Toolbar('widgetToolbar_' + this.name);

        if (!this['static']) {
            tb.appendControl(new Toolbar.Button({
                id: 'add',
                'icon': 'images/toolbar/add.gif',
                title: 'Add',
                action: 'addWidget'
            }));
        }

        if (this.component
            && this.component['params']
            && Object.getLength(this.component.params)
            && Object.some(this.component.params, function (obj) {
            return (obj.xml.getProperty('data-type') != 'hidden');
        })) {
            tb.appendControl(new Toolbar.Button({
                id: 'edit',
                icon: 'images/toolbar/edit.gif',
                title: 'Edit',
                action: 'editProps'
            }));
        }

        if (!this['static']) {
            tb.appendControl(new Toolbar.Button({
                id: 'delete',
                icon: 'images/toolbar/delete.gif',
                title: 'Delete',
                action: 'delWidget'
            }));
        }

        tb.appendControl(new Toolbar.Switcher({
            id: 'resize',
            icon: 'images/toolbar/minimize.gif',
            aicon: 'images/toolbar/restore.gif',
            title: 'Minimize/Expand',
            action: 'resizeWidget'
        }));

        tb.getElement().inject(this.container, 'top');
        tb.bindTo(this);
        return tb;
    }.protect(),

    /**
     * Edit widget's properties.
     * @function
     * @public
     */
    editProps: function () {
        ModalBox.open({
            post: this.xml.asXMLString(),
            url: LayoutManager.singlePath + 'widgets/edit-params/' + this.component.name + '/',
            onClose: function (result) {
                this.overlay.element.addClass('e-overlay-loading');
                if (result) {
                    Object.each(result, function (param, paramName) {
                        if(this.getComponent().getParam(paramName).setValue)
                            this.getComponent().getParam(paramName).setValue(param);
                    }, this);
                    this.reload();
                    LayoutManager.changed = true;
                }
                this.overlay.element.removeClass('e-overlay-loading');
            }.bind(this)
        });
    },

    /**
     * Delete widget.
     * @function
     * @public
     */
    delWidget: function () {
        this.xml.destroy();
        this.container.destroy();
        LayoutManager.changed = true;
    },

    /**
     * Resize widget.
     * @function
     * @public
     */
    resizeWidget: function () {
        this.element.toggleClass('minimized');
        if (this.dragger) {
            this.dragger.recalculateSize();
        }
    },

    /**
     * Reload the widget.
     * @function
     * @public
     */
    reload: function () {
        if (this.xml.getProperty('data-dynamic')) {
            new LayoutManager.Widget(this.xml, this.column, new Element('div', {'class': 'dynamic', 'text': ''}), this);
        } else {
            new Request({
                url: LayoutManager.singlePath + 'widgets/build-widget/',
                method: 'post',
                evalScripts: false,
                data: { 'xml': this.xml.asXMLString() },
                onSuccess: function (text) {
                    var container = new Element('div'), result;
                    container.set('html', text);
                    if (container.getElement('div')) {
                        result = container.getElement('div').clone();
                        this.replaceElement(result);
                    }
                    container.destroy();
                }.bind(this)
            }).send();
        }
    },

    /**
     * Get the component
     *
     * @function
     * @public
     * @returns {LayoutManager.Component}
     */
    getComponent: function () {
        return this.component;
    },

    /**
     * Replase the main [element]{@link LayoutManager.Widget#element}
     *
     * @function
     * @public
     * @param {Element} el Element that replaces the main element.
     */
    replaceElement: function (el) {
        el.replaces(this.element);

        this.element = el;
        this.element.addClass('e-widget');

        this.overlay = new Overlay(this.element, {indicator: false});
        this.overlay.show();
        //this.overlay.getElement().removeClass('e-overlay-loading');
    },

    /**
     * Overridden parent [findDirection]{@link LayoutManager.DummyWidget} method.
     *
     * @param {number} y Y position of the draggable widget.
     * @returns {string} 'after' or 'before'
     */
    findDirection: function (y) {
        var r = 'after';

        if (!this['static']) {
            var pos = this.container.getPosition(LayoutManager.mFrame),
                size = this.container.getSize();

            r = (y >= pos.y && y <= (pos.y + size.y / 4))
                ? 'before'
                : 'after';
        }

        return r;
    }
});

/**
 * Drag behavior.
 *
 * @constructor
 * @param {LayoutManager.Widget} widget Parent widget.
 */
LayoutManager.Widget.DragBehavior = new Class(/** @lends LayoutManager.Widget.DragBehavior# */{
    // constructor
    initialize: function (widget) {
        /**
         * Parent widget.
         * @type {LayoutManager.Widget}
         */
        this.widget = widget;

        /**
         * Strut element.
         * @type {Element}
         */
        this.strut = new Element('div', {'class': 'e-lm-strut'});

        this.recalculateSize();

        /**
         * Drag object.
         * @type {Drag}
         */
        this.drag = new Drag(this.widget.container, {
            grid: 6,
            snap: 6,
            handle: this.widget.toolbar.getElement(),
            onBeforeStart: function () {
                var mFrame = LayoutManager.mFrame;
                /**
                 * Position of the frame.
                 * @type {{x: number, y: number}}
                 */
                this.position = this.widget.container.getPosition(mFrame);

                //Непонятного происхождения костыль
                //для 1.3
                // @todo проверить при следующих обновлениях библиотеки
                this.position.y += mFrame.getScrollTop();
                //end of kostyly

                this.widget.container.setStyles({
                    width: this.size.x + 'px',
                    height: this.size.y + 'px',
                    'z-index': 9999,
                    position: 'absolute'
                });
                this.strut.replaces(this.widget.container);

                this.widget.container.setPosition(this.position);
                this.widget.container.inject(mFrame);
            }.bind(this),

            onComplete: function () {
                var w = this.strut.retrieve('widget');

                this.widget.container.setStyles({
                    width: null,
                    height: null,
                    'z-index': 'auto',
                    position: null,
                    top: null,
                    left: null
                });
                this.widget.container.replaces(this.strut);

                /**
                 * Size of the widget's container.
                 * @type {{x: number, y: number}}
                 */
                this.size = this.widget.container.getSize();
                if (w) {
                    (w.xml)
                        ? this.widget.xml.inject(w.xml, this.strut.retrieve('direction'))
                        : w.column.xml.grab(this.widget.xml);

                    if (w.column != this.widget.column) {
                        w.column.widgets[this.widget.name] = this.widget;

                        delete this.widget.column.widgets[this.widget.name];

                        this.widget.column = w.column;
                    }

                    this.strut.eliminate('widget');
                    this.strut.eliminate('direction');
                }

                LayoutManager.changed = true;
            }.bind(this),

            onCancel: function () {
                this.widget.container.setStyles({
                    width: null,
                    height: null,
                    'z-index': 'auto',
                    'position': null,
                    'top': null,
                    'left': null
                });
                this.widget.container.replaces(this.strut);
            }.bind(this),

            onDrag: function (el, evt) {
                var pos = this.widget.container.getPosition(LayoutManager.mFrame),
                //Центр блока
                    cx = (pos.x + (this.size.x) / 2).toInt(),
                /* координата Y центра блока сделана равной pos.y + 25 (число 25 найдено методом подбора, при этом блоки ведут себя наиболее ожидаемо) */
                    cy = (pos.y + this.widget.toolbar.size.y).toInt(),
                    w = this.widget.column.layoutManager.findWidgetByCoords(cx, cy, this.widget),
                    dir;

                if (w) {
                    dir = w.findDirection(cy);
                    this.strut.inject(w.container, dir);
                    this.strut.store('widget', w);
                    this.strut.store('direction', dir);
                } else {
                    cy = (pos.y + this.size.y - this.widget.toolbar.size.y).toInt();
                    w = this.widget.column.layoutManager.findWidgetByCoords(cx, cy, this.widget);
                    if (w) {
                        dir = w.findDirection(cy);
                        this.strut.inject(w.container, dir);
                        this.strut.store('widget', w);
                        this.strut.store('direction', dir);
                    }
                }
            }.bind(this)
        });
    },

    /**
     * Recalculate the widget's container size.
     * @function
     * @public
     */
    recalculateSize: function () {
        this.size = this.widget.container.getSize();
        this.strut.setStyle('height', (this.size.y - 14) + 'px'); //от высоты контейнера отнимаем 14px, 10px это padding-bottom, 4px это бордеры внизу и вверху страта
    }
});

/**
 * Component.
 *
 * @constructor
 * @param {HTMLDivElement} xmlDescr Pseudo XML component description.
 * @param {Element} element The component element.
 */
LayoutManager.Component = new Class(/** @lends LayoutManager.Component# */{
    /**
     * Array like object of the component parameters (LayoutManager.Component.Param).
     * @type {Object}
     */
    params: {},

    // constructor
    initialize: function (xmlDescr, element) {
        /**
         * Pseudo XML component description.
         * @type {HTMLDivElement}
         */
        this.xml = xmlDescr;

        /**
         * Component name.
         * @type {srting}
         */
        this.name = xmlDescr.getProperty('data-name');

        /**
         * The main element.
         * @type {Element}
         */
        this.element = element;

        xmlDescr.getXMLElements('param').each(function (xml) {
            this.params[xml.getProperty('data-name')] = new LayoutManager.Component.Param(xml);
        }, this);
    },

    /**
     * Get parameter by name.
     *
     * @function
     * @public
     * @param {string} paramName Parameter name.
     * @returns {LayoutManager.Component.Param}
     */
    getParam: function (paramName) {
        return this.params[paramName] || {};
    }
});

/**
 * Component parameter.
 *
 * @constructor
 * @param {HTMLDivElement} xml Pseudo XML parameter description.
 */
LayoutManager.Component.Param = new Class(/** @lends LayoutManager.Component.Param# */{
    // constructor
    initialize: function (xml) {
        this.xml = xml;
    },

    /**
     * Set the parameter value.
     *
     * @function
     * @public
     * @param {string} value Value
     */
    setValue: function (value) {
        this.xml.set('text', value.toString());
    },

    /**
     * Get the parameter value.
     *
     * @function
     * @public
     * @returns {string}
     */
    getValue: function () {
        return this.xml.get('text');
    }
});

/**
 * Вспомогательный объект со статическими методами по созданию псевдо-XML коллекции в
 * виде DIV блоков и кастомного атрибута xmltag, а также по преобразованию ее
 * обратно в XML строку
 *
 * @namespace
 */
var PseudoXML = /** @lends PseudoXML */{

    /**
     * Create XML root element.
     *
     * @function
     * @static
     * @param {string} str String from which the XML root element will be created.
     * @returns {*}
     */
    createXMLRoot: function (str) {
        // todo: проверить работоспособность во всех браузерах
        var root;

        if (window.DOMParser) {
            root = new DOMParser().parseFromString(str, "text/xml");
        }
        else // Internet Explorer
        {
            root = new ActiveXObject("Microsoft.XMLDOM");
            root.async = false;
            root.loadXML(str);
        }

        return root;
    },

    /**
     * Create pseudo XML as div-element.
     *
     * @param {string|Document|Element} xml
     * @param {Element} [parent]  Parent element.
     * @returns {Element}
     */
    createPseudoXML: function (xml, parent) {
        var j,
            currChildNode,
            curSubNode;

        if (typeof(xml) == 'string') {
            xml = this.createXMLRoot(xml).documentElement;
        }

        if (!parent) {
            parent = new Element('div');
            parent.setProperty('xmltag', xml.nodeName);
        }

        var tmp;
        if (xml.attributes) {
            for (j = 0; j < xml.attributes.length; j++) {
                tmp = xml.attributes[j];
                parent.setProperty('data-' + tmp.nodeName, tmp.nodeValue);
            }
        }

        if (!xml.childNodes || !xml.childNodes.length) {
            return parent;
        }

        for (var i = 0; i < xml.childNodes.length; i++) {
            currChildNode = xml.childNodes[i];

            if (currChildNode.nodeType == 1) { // Element type
                // NodeName
                var el = new Element('div');
                el.setProperty('xmltag', currChildNode.nodeName);

                // Attributes
                if (currChildNode.attributes.length) {
                    for (j = 0; j < currChildNode.attributes.length; j++) {
                        tmp = currChildNode.attributes[j];
                        el.setProperty('data-' + tmp.nodeName, tmp.nodeValue);
                    }
                }

                // Value
                if (currChildNode.childNodes.length) {
                    var text = '';
                    for (var k = 0; k < currChildNode.childNodes.length; k++) {
                        curSubNode = currChildNode.childNodes[k];
                        if (curSubNode.nodeType == 3) {
                            text += curSubNode.nodeValue;
                        }
                    }
                    if (text != '') {
                        el.set({'text': text});
                    }
                }

                parent.adopt(el);

                if (currChildNode.childNodes.length) {
                    this.createPseudoXML(currChildNode, el);
                }
            }
        }

        return parent;
    },

    /**
     * Convert the pseudo XML element to the string.
     *
     * @param {Element} el Element that will be converted.
     * @returns {string}
     */
    getElementAsXMLString: function (el) {
        var result = '<' + el.getProperty('xmltag'),
            children = el.getChildren();

        if (el.attributes && el.attributes.length) {
            for (var i = 0; i < el.attributes.length; i++) {
                var attr_name = el.attributes[i].nodeName;

                if (attr_name.indexOf('data-') != -1) {
                    result += ' '
                        + attr_name.replace('data-', '')
                        + '="'
                        + el.getProperty(attr_name)
                        .replace(/"/g, '\\"')
                        .replace(/[\r\n]/g, ' ')
                        + '"';
                }
            }
        }
        result += '>';

        if (children.length) {
            children.each(function (e) {
                result += this.getElementAsXMLString(e);
            }.bind(this));
        } else {
            result += el.get('html');
            //.replace(/&/g, '&amp;')
            //.replace(/</g, '&lt;')
            //.replace(/>/g, '&gt;')
            //.replace(/"/g, '&quot;')
            //.replace(/'/g, '&#039;');
        }
        result += '</' + el.getProperty('xmltag') + '>';

        return result;
    }
};

