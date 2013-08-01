ScriptLoader.load('Overlay');

/**
 * Расширяем прототип Element'а
 * для работы с псевдо-xml узлами в виде обычных DIV с доп атрибутом xmltag
 */
Element.implement({

    getXMLElements: function (tag) {
        return this.getElements('[xmltag=' + tag + ']');
    },

    getXMLElement: function (tag) {
        return this.getElement('[xmltag=' + tag + ']');
    },

    asXMLString: function () {
        return '<?xml version="1.0" encoding="utf-8" ?>' + PseudoXML.getElementAsXMLString(this);
    }

});

/**
 * Редактор лейаута
 */
var LayoutManager = new Class({
    initialize: function (singlePath) {
        this.columns = new Hash();
        /**
         * По сути, статическая переменная
         */
        LayoutManager.singlePath = singlePath;
        LayoutManager.changed = false;
        LayoutManager.mFrame = document.getElement('.e-mainframe');
        /**
         * Получаем XML структуру текущего документа
         */
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
     *
     * @param xml XML структура документа
     */
    setup: function (xml) {
        Asset.css('layout_manager.css');
        this.createToolbar();
        /**
         * this.xml содержит Element с псевдо-xml структурой
         * нас интересует только узел content
         */
        this.xml = PseudoXML.createPseudoXML(xml.documentElement).getXMLElements('content')[0];

        //Проходимся по всем контейнерам контента
        this.xml.getXMLElements('container').each(function (xml) {
            //И создаем для каждого контейнера, который выступает в качестве колонки  - соответствующий объект колонки
            if (xml.getProperty('data-column')) {
                this.columns.set(xml.getProperty('data-name'), new LayoutManager.Column(xml, this));
            }
        }, this);
    },
    /*changeContent: function() {
     console.log(arguments)
     },*/
    createToolbar: function () {
        this.toolbar = new Toolbar('block_management_toolbar');

        this.toolbar.dock();
        this.toolbar.getElement().injectInside(
            document.getElement('.e-topframe')
        );
        this.toolbar.bindTo(this);
        var html = $$('html')[0];
        if (html.hasClass('e-has-topframe2')) {
            html.removeClass('e-has-topframe2');
            html.addClass('e-has-topframe3');
        }
        else if (html.hasClass('e-has-topframe1')) {
            html.removeClass('e-has-topframe1');
            html.addClass('e-has-topframe2');
        }
        //получаем данные селекта с вариантами содержимого
        new Request.JSON({
            url: LayoutManager.singlePath + 'get-template-info/',
            method: 'get',
            onSuccess: function (result) {
                if (result.result) {
                    this.toolbar.appendControl(
                        new Toolbar.Text({id: 'text3', 'title': result.data.layout.title}),
                        new Toolbar.Text({id: 'text4', 'title': result.data.layout.name, 'tooltip': result.data.layout.file}),
                        new Toolbar.Text({id: 'text1', 'title': result.data.content.title}),
                        new Toolbar.Text({id: 'text2', 'title': result.data.content.name + ((result.data.content.modified) ? ' (' + result.data.content.modified + ')' : ''), 'tooltip': result.data.content.file}),
                        new Toolbar.Separator({id: 'sep1'}),
                        new Toolbar.Select({id: 'actionSelector', title: result.data.actionSelectorText}, result.data.actionSelector, 'save'),
                        new Toolbar.Button({id: 'save', 'title': result.data.saveText, 'action': 'applyChanges'}),
                        new Toolbar.Separator({id: 'sep2'}),
                        new Toolbar.Button({id: 'reset', 'title': result.data.cancelText, 'action': 'resetChanges'})
                    );

                    //для красоты
                    this.toolbar.getElement().getElement('select').setStyle('width', 'auto')

                }
            }.bind(this)
        }).send();

    },
    applyChanges: function () {
        var
            fRevert = function () {
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
                        if (!result) return;
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
    resetChanges: function () {
        document.location = document.location.href;
    },
    findWidgetByCoords: function (x, y, currentWidget) {
        var cl, mFrame = LayoutManager.mFrame;
        this.columns.some(function (clm) {
            return clm.widgets.some(function (wdg) {
                var res = false;
                if ((wdg != currentWidget) && wdg.visible) {
                    var pos = wdg.container.getPosition(mFrame), size = wdg.container.getSize();
                    if (res = ((x >= pos.x) && (x <= (pos.x + size.x)))
                        &&
                        ((y >= pos.y) && (y <= (pos.y + size.y)))) {
                        cl = wdg;
                    }
                }
                return res;
            })
        })
        return cl;
    },
    /**
     * Возвращает объект колонки по имени
     * @param columnName имя колонки
     */
    getColumn: function (columnName) {
        return this.columns.get(columnName) || {};
    }
});

/**
 * Колонка
 */
LayoutManager.Column = new Class({
    /**
     * На вход получаем Pseudo-XML описание колонки
     * @param xmlDescr Element
     */
    initialize: function (xmlDescr, layoutManager) {
        this.xml = xmlDescr;
        this.layoutManager = layoutManager;
        this.name = xmlDescr.getProperty('data-name');
        //хеш виджетов, находящихся внутри колонки
        this.widgets = new Hash();
        //Если существует соответствующий HTML елемент  - представление колонки
        if (this.element = document.getElement('[column=' + this.name + ']')) {
            this.element.addClass('e-lm-column');
            //проходимся по всем дочерним контейнерам и добавляем их в this.widgets
            xmlDescr.getXMLElements('container').each(this.addWidgetAsXML, this);
            this.widgets.set(this.name, new LayoutManager.DummyWidget(this));
        }
    },
    /**
     * Добавление виджета в хеш виджетов колонки
     *
     * @param widgetXML DOMNode
     * @return LayoutManager.Widget
     */
    addWidgetAsXML: function (widgetXML) {
        var widget = null;
        if (widgetXML.getProperty('data-widget')) {
            this.widgets.set(widgetXML.getProperty('data-name'), widget =
                new LayoutManager.Widget(widgetXML, this))
        }
        return widget;
    },
    /**
     *
     * @param injectedWidget LayoutManager.Widget
     * @param sourceWidget LayoutManager.Widget
     * @param location string 'before'|'after'
     */
    injectWidget: function (injectedWidget, sourceWidget, location) {
        /**
         * Если передан sourceWidget - то инжектим
         */
        if (sourceWidget) {
            if (location != 'before') {
                location = 'after';
            }
            this.widgets.set(injectedWidget.name, injectedWidget, this);
            if (sourceWidget.xml) {
                injectedWidget.xml.inject(sourceWidget.xml, location);
            }
            else {
                this.xml.grab(injectedWidget.xml)
            }

            injectedWidget.container.inject(sourceWidget.container, location);
        }
        //Иначе - просто добавляем в конец
        else {
            this.widgets.set(injectedWidget.name, injectedWidget, this);
            this.xml.grab(injectedWidget.xml);
            this.element.grab(injectedWidget.container);
        }
        LayoutManager.changed = true;
    },
    /**
     * Получаем виджет находящийся в колонке, по его имени
     * @param widgetName string имя вижета
     */
    getWidget: function (widgetName) {
        return this.widgets.get(widgetName) || {};
    }
});

LayoutManager.DummyWidget = new Class({
    initialize: function (column) {
        this.column = column;
        this.element = this.container = new Element('div', {'class': 'e-lm-dummy-widget'});
        this.column.element.grab(this.element);
        this.visible = true;
        this.toolbar = this._buildToolbar();
    },
    _buildToolbar: function () {
        var tb = new Toolbar('widgetToolbar_' + this.column.name);
        tb.appendControl(new Toolbar.Button({id: 'add', 'icon': 'images/toolbar/add.gif', title: 'Add', action: 'addWidget'}));
        tb.getElement().inject(this.element, 'top');
        tb.bindTo(this);
        return tb;
    },
    findDirection: function () {
        return 'before';
    },
    /**
     * Выводит модальное окно добавления виджета
     */
    addWidget: function () {
        ModalBox.open({
            url: LayoutManager.singlePath + 'widgets/',
            onClose: function (response) {
                if (response) {
                    var xml = PseudoXML.createPseudoXML(response), widgetTitle;
                    if (!(widgetTitle = xml.getProperty('data-dynamic'))) {
                        new Request({
                            url: LayoutManager.singlePath + 'widgets/build-widget/',
                            method: 'post',
                            evalScripts: false,
                            data: { 'xml': xml.asXMLString() },
                            onSuccess: function (text) {
                                var container = new Element('div'), result;
                                container.set('html', text);
                                if (container.getElement('div')) {
                                    result = container.getElement('div').clone();
                                    new LayoutManager.Widget(xml, this.column, result, this);
                                }
                                container.destroy();
                            }.bind(this)
                        }).send();
                    }
                    else {
                        new LayoutManager.Widget(xml, this.column, new Element('div', {'class': 'dynamic', 'text': widgetTitle}), this);
                    }

                }
            }.bind(this)
        });
    }
});

/**
 * Виджет
 */
LayoutManager.Widget = new Class({
    Extends: LayoutManager.DummyWidget,
    /**
     *
     * @param xmlDescr DOMNode
     * @param column LayoutManager.Column
     */
    initialize: function (xmlDescr, column, htmlElement, injectBeforeThisWidget) {
        this.xml = xmlDescr;
        this.column = column;
        this.name = xmlDescr.getProperty('data-name');
        this.visible = false;
        this.static = false;
        htmlElement = htmlElement ||
            document.getElement('[widget=' + this.name + ']');
        if (this.element = $(htmlElement)) {
            this.bindElement(this.element);
            if (injectBeforeThisWidget) {
                this.column.injectWidget(this, injectBeforeThisWidget, 'before');
            }
            this.toolbar = this._buildToolbar();
            if (!this.static) this.dragger = new LayoutManager.Widget.DragBehavior(this);
            if (!this.element.hasClass('e-widget')) this.element.addClass('e-widget');
            this.overlay = new Overlay(this.element, {indicator: false});

            this.overlay.show();
            this.visible = true;
        }
    },
    /**
     * Привязка к HTML представлению
     *
     * @param element Element
     * @return void
     */
    bindElement: function (element) {
        //Создаем елемент контейнера  - содержащего тулбар виджета
        this.container =
            new Element('div', {'class': 'e-lm-widget'/*, styles:{'position': 'relative'}*/});
        if (this.element.getParent())
            this.container.wraps(this.element);
        else {
            this.container.grab(this.element);
        }
        this.static = new Boolean(this.element.getProperty('static')).valueOf();
        if (this.static) this.container.addClass('e-lm-static-widget');
        var c;
        if ((c = this.xml.getXMLElement('component')) /*&& !this.static*/) {
            this.component = new LayoutManager.Component(c, this.element);
        }
    },
    _buildToolbar: function () {
        var tb = new Toolbar('widgetToolbar_' + this.name);
        if (!this.static)
            tb.appendControl(new Toolbar.Button({id: 'add', 'icon': 'images/toolbar/add.gif', title: 'Add', action: 'addWidget'}));
        if (this.component && this.component.params.getLength() && this.component.params.some(function (obj) {
            return (obj.xml.getProperty('data-type') != 'hidden');
        }))
            tb.appendControl(new Toolbar.Button({id: 'edit', 'icon': 'images/toolbar/edit.gif', title: 'Edit', action: 'editProps'}));
        if (!this.static)
            tb.appendControl(new Toolbar.Button({id: 'delete', 'icon': 'images/toolbar/delete.gif', title: 'Delete', action: 'delWidget'}));

        tb.appendControl(new Toolbar.Switcher({id: 'resize', 'icon': 'images/toolbar/minimize.gif', 'aicon': 'images/toolbar/restore.gif', title: 'Minimize/Expand', action: 'resizeWidget'}));
        tb.getElement().inject(this.container, 'top');
        tb.bindTo(this);
        return tb;
    },
    /**
     * Выводит модальное окно добавления виджета
     */
    /*addWidget: function () {
     ModalBox.open({
     url: LayoutManager.singlePath + 'widgets/',
     onClose: function (response) {
     if (response) {
     response = new LayoutManager.Macros(response).replace();
     var xml = PseudoXML.createPseudoXML(response);
     if (!xml.getProperty('data-dynamic')) {
     new Request({
     url: LayoutManager.singlePath + 'widgets/build-widget/',
     method: 'post',
     evalScripts: false,
     data: { 'xml': xml.asXMLString() },
     onSuccess: function (text) {
     var container = new Element('div'), result;
     container.set('html', text);
     if (container.getElement('div')) {
     result = container.getElement('div').clone();
     new LayoutManager.Widget(xml, this.column, result, this);
     }
     container.destroy();
     }.bind(this)
     }).send();
     }
     else {
     new LayoutManager.Widget(xml, this.column, new Element('div', {'class': 'dynamic', 'text': 'Жопа'}), this);
     }

     }
     }.bind(this)
     });
     },*/
    /**
     * Выводит форму редактирования параметров компонента виджета
     */
    editProps: function () {
        ModalBox.open({
            post: this.xml.asXMLString(),
            url: LayoutManager.singlePath + 'widgets/edit-params/' +
                this.component.name +
                '/',
            onClose: function (result) {
                this.overlay.element.addClass('e-overlay-loading');
                if (result) {
                    result.each(function (param, paramName) {
                        this.getComponent().getParam(paramName).setValue(param);
                    }.bind(this));
                    this.reload();
                    LayoutManager.changed = true;
                }

                this.overlay.element.removeClass('e-overlay-loading');

            }.bind(this)
        });
    },
    /**
     * Удаляет виджет
     */
    delWidget: function () {
        this.xml.destroy();
        this.container.destroy();
        LayoutManager.changed = true;
    },
    resizeWidget: function () {
        this.element.toggleClass('minimized');
        if (this.dragger) {
            this.dragger.recalculateSize();
        }
    },
    /**
     * Перегружает HTML предеставление виджета
     */
    reload: function () {
        if (!this.xml.getProperty('data-dynamic')) {
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
        else {
            new LayoutManager.Widget(this.xml, this.column, new Element('div', {'class': 'dynamic', 'text': ''}), this);
        }


    },
    /**
     * Возвращает компонент виджета
     */
    getComponent: function () {
        return this.component || {};
    },
    /**
     * Заменяет елемент
     * @param el
     */
    replaceElement: function (el) {
        el.replaces(this.element);
        this.element = el;
        this.element.addClass('e-widget');
        this.overlay = new Overlay(this.element, {indicator: false});
        //this.overlay.getElement().removeClass('e-overlay-loading');
        this.overlay.show();
    },
    findDirection: function (y) {
        var pos = this.container.getPosition(LayoutManager.mFrame), size = this.container.getSize();
        if (this.static) return 'after';
        return ((y >= pos.y) &&
            (y <= (pos.y + size.y / 4))) ? 'before' : 'after';
    }
});

LayoutManager.Widget.implement(Energine.request);

LayoutManager.Widget.DragBehavior = new Class({
    initialize: function (widget) {
        this.widget = widget;
        this.strut =
            new Element('div', {'class': 'e-lm-strut'});
        this.recalculateSize();

        this.drag = new Drag(this.widget.container, {
            grid: 6,
            snap: 6,
            handle: this.widget.toolbar.getElement(),
            onBeforeStart: function () {
                var mFrame = LayoutManager.mFrame;
                this.position = this.widget.container.getPosition(mFrame);

                //Непонятного происхождения костыль
                //для 1.3
                /**
                 * @todo проверить при следующих обновлениях библиотеки
                 */
                this.position.y += mFrame.getScrollTop();
                //end of kostyly

                this.widget.container.setStyles({
                    width: this.size.x + 'px',
                    height: this.size.y + 'px',
                    'z-index': 9999,
                    'position': 'absolute'
                });
                this.strut.replaces(this.widget.container);
                this.widget.container.setPosition(this.position);
                this.widget.container.inject(mFrame);

            }.bind(this),
            onComplete: function () {
                var w;
                this.widget.container.setStyles({
                    width: null,
                    height: null,
                    'z-index': 'auto',
                    'position': null,
                    'top': null,
                    'left': null
                });
                this.widget.container.replaces(this.strut);
                this.size = this.widget.container.getSize();
                if (w = this.strut.retrieve('widget')) {
                    if (w.xml) {
                        this.widget.xml.inject(w.xml, this.strut.retrieve('direction'));
                    }
                    else {
                        w.column.xml.grab(this.widget.xml);
                    }
                    if (w.column != this.widget.column) {
                        w.column.widgets.set(this.widget.name, this.widget);
                        this.widget.column.widgets.erase(this.widget.name);
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
            /* onBeforeDrag: function(el, evt){
             if((evt.client.y - document.getElement('.e-topframe').getSize().y - 35)<0){
             LayoutManager.mFrame.scrollTop = LayoutManager.mFrame.scrollTop - 2;
             evt.page.y = evt.page.y - 35;
             }
             }.bind(this),*/
            onDrag: function (el, evt) {
                var cx, cy, pos, w, dir;
                pos = this.widget.container.getPosition(LayoutManager.mFrame);

                //Центр блока
                cx = (pos.x + (this.size.x) / 2).toInt();
                //cy = (pos.y + (this.size.y) / 4).toInt();
                /* координата Y центра блока сделана равной pos.y + 25 (число 25 найдено методом подбора, при этом блоки ведут себя наиболее ожидаемо) */
                cy = (pos.y + (this.size.y < 100 ? (this.size.y) / 4 : 25)).toInt();
                if (w =
                    this.widget.column.layoutManager.findWidgetByCoords(cx, cy, this.widget)) {
                    this.strut.inject(w.container, dir = w.findDirection(cy));
                    this.strut.store('widget', w);
                    this.strut.store('direction', dir);
                }
            }.bind(this)
        });
    },
    recalculateSize: function () {
        this.size = this.widget.container.getSize();
        this.strut.setStyle('height', (this.size.y - 14) + 'px'); //от высоты контейнера отнимаем 14px, 10px это padding-bottom, 4px это бордеры внизу и вверху страта  
    }

});
/**
 * Компонент
 */
LayoutManager.Component = new Class({
    initialize: function (xmlDescr, element) {
        this.xml = xmlDescr;
        this.name = xmlDescr.getProperty('data-name');
        this.element = element;
        this.params = new Hash();
        xmlDescr.getXMLElements('param').each(function (xml) {
            this.params.set(xml.getProperty('data-name'), new LayoutManager.Component.Param(xml));
        }, this);
    },
    getParam: function (paramName) {
        return this.params.get(paramName) || {};
    }
});
/**
 * параметр компонента
 */
LayoutManager.Component.Param = new Class({
    initialize: function (xml) {
        this.xml = xml;
    },
    /**
     * Присвоение значения узлу меняет значение общего XML
     * @param value string
     */
    setValue: function (value) {
        this.xml.set('text', value.toString());
    },
    getValue: function () {
        return this.xml.get('text');
    }

});

/**
 * Вспомогательный объект со статическими методами по созданию псевдо-XML коллекции в
 * виде DIV блоков и кастомного атрибута xmltag, а также по преобразованию ее
 * обратно в XML строку
 *
 * @type {{createPseudoXML: Function, getElementAsXMLString: Function}}
 */
var PseudoXML = {

    /**
     * Хитрый метод создания корневого элемента XML из строки
     * с использованием Microsoft.XMLDOM или DOMParser'a
     *
     * @param string
     * @returns {*}
     */
    createXMLRoot: function (string) {

        // todo: проверить работоспособность во всех браузерах

        var root;

        if (window.DOMParser) {
            var parser = new DOMParser();
            root = parser.parseFromString(string, "text/xml");
        }
        else // Internet Explorer
        {
            root = new ActiveXObject("Microsoft.XMLDOM");
            root.async = false;
            root.loadXML(string);
        }

        return root;
    },

    /**
     * Создает псевдо-XML в виде DIV-ов
     *
     * @param Document|Element xml
     * @param null|Element parent
     * @returns Element
     */
    createPseudoXML: function (xml, parent) {

        if (typeof(xml) == 'string') {
            xml = this.createXMLRoot(xml).documentElement;
        }

        if (!parent) {
            parent = new Element('div');
            parent.setProperty('xmltag', xml.nodeName);
        }

        var tmp;
        if (xml.attributes) {
            for (var j = 0; j < xml.attributes.length; j++) {
                tmp = xml.attributes[j];
                parent.setProperty('data-' + tmp.nodeName, tmp.nodeValue);
            }
        }

        if (!xml.childNodes || !xml.childNodes.length) return parent;

        var i, j, k, currChildNode, curSubNode;
        for (i = 0; i < xml.childNodes.length; i++) {
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
                    for (k = 0; k < currChildNode.childNodes.length; k++) {
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

                if (currChildNode.childNodes.length) this.createPseudoXML(currChildNode, el);
            }
        }

        return parent;
    },

    /**
     * Собирает псевдо-XML обратно из псевдо-структуры в XML строку
     *
     * @param Element el
     * @returns {string}
     */
    getElementAsXMLString: function (el) {

        var result = '<' + el.getProperty('xmltag');
        if (el.attributes && el.attributes.length) {
            for (var i = 0; i < el.attributes.length; i++) {
                var attr_name = el.attributes[i].nodeName;
                if (attr_name.indexOf('data-') != -1) {
                    result += ' ' + attr_name.replace('data-', '') + '="' +
                        el.getProperty(attr_name)
                            .replace(/"/g, '\\"')
                            .replace(/[\r\n]/g, ' ')
                        + '"';
                }
            }
        }
        result += '>';

        var children = el.getChildren();
        if (children.length) {
            children.each(function (e) {
                result += this.getElementAsXMLString(e);
            }.bind(this));
        } else {
            result += el.get('text')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        result += '</' + el.getProperty('xmltag') + '>';
        return result;
    }
};

