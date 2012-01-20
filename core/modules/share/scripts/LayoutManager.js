ScriptLoader.load('XML', 'Overlay');
/**
 * Редактор лейаута
 */
var LayoutManager = new Class({
    initialize: function(singlePath) {
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
            url:document.location.href,
            method: 'get',
            data: 'struct',
            onSuccess: function(text, xml) {
                this.setup(xml);
            }.bind(this)
        }).send();
    },
    /**
     *
     * @param xml XML структура документа
     */
    setup: function(xml) {
        Asset.css('layout_manager.css');
        this.createToolbar();
        /**
         * нас интересует только узел content
         */
        this.xml = $(xml).getElement('content');
        //Проходимся по всем контейнерам контента
        this.xml.getElements('container').each(function(xml) {
            //И создаем для каждого контейнера, который выступает в качестве колонки  - соответствующий объект колонки
            if (xml.getProperty('column')) {
                this.columns.set(xml.getProperty('name'), new LayoutManager.Column(xml, this));
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
            url:LayoutManager.singlePath + 'get-template-info/',
            method: 'get',
            onSuccess: function(result) {
                if (result.result) {
                    this.toolbar.appendControl(
                        new Toolbar.Text({id:'text3', 'title':result.data.layout.title}),
                        new Toolbar.Text({id:'text4', 'title':result.data.layout.name, 'tooltip':result.data.layout.file}),
                        new Toolbar.Text({id:'text1', 'title':result.data.content.title}),
                        new Toolbar.Text({id:'text2', 'title':result.data.content.name + ((result.data.content.modified) ? ' (' + result.data.content.modified + ')' : ''), 'tooltip':result.data.content.file}),
                        new Toolbar.Separator({id:'sep1'}),
                        new Toolbar.Select({id: 'actionSelector', title:result.data.actionSelectorText}, result.data.actionSelector, 'save'),
                        new Toolbar.Button({id: 'save', 'title':result.data.saveText, 'action': 'applyChanges'}),
                        new Toolbar.Separator({id:'sep2'}),
                        new Toolbar.Button({id: 'reset', 'title':result.data.cancelText, 'action':'resetChanges'})
                    );

                    //для красоты
                    this.toolbar.getElement().getElement('select').setStyle('width', 'auto')

                }
            }.bind(this)
        }).send();

    },
    applyChanges: function() {
        var
            fRevert = function() {
                new Request.JSON({
                    url:LayoutManager.singlePath + 'widgets/revert-template/' + ((Energine.forceJSON)?'?json':''),
                    method: 'post',
                    evalScripts: false,
                    onSuccess: function(response) {
                        if (response.result) {
                            //document.location = document.location.href;
                        }
                    }
                }).send();
            },
            fReset = function() {
                new Request.JSON({
                    url:LayoutManager.singlePath + 'reset-templates/' + ((Energine.forceJSON)?'?json':''),
                    method: 'post',
                    evalScripts: false,
                    onSuccess: function(response) {
                        if (response.result) {
                            document.location = document.location.href;
                        }
                    }
                }).send();
            },
            fSaveTemplate = function() {
                new Request.JSON({
                    url:LayoutManager.singlePath + 'widgets/save-template/' + ((Energine.forceJSON)?'?json':''),
                    method: 'post',
                    evalScripts: false,
                    data: 'xml=' + '<?xml version="1.0" encoding="utf-8" ?>' + XML.hashToHTML(XML.nodeToHash(this.xml)),
                    onSuccess: function(response) {
                        if (response.result) {
                            document.location = document.location.href;
                        }
                    }
                }).send();
            },
            fSaveNewTemplate = function() {
                ModalBox.open({
                    url: LayoutManager.singlePath + 'widgets/show-new-template-form/',
                    onClose: function(result) {
                        if (!result) return;
                        new Request.JSON({
                            url:LayoutManager.singlePath + 'widgets/save-new-template/' + ((Energine.forceJSON)?'?json':''),
                            method: 'post',
                            evalScripts: false,
                            data: 'xml=' +
                                '<?xml version="1.0" encoding="utf-8" ?>' +
                                XML.hashToHTML(XML.nodeToHash(this.xml)) + '&title=' + result,
                            onSuccess: function(response) {
                                if (response.result) {
                                    document.location = document.location.href;
                                }
                            }
                        }).send();
                    }.bind(this)
                });
            },
            fSave = function() {
                new Request.JSON({
                    url:LayoutManager.singlePath + 'widgets/save-content/' + ((Energine.forceJSON)?'?json':''),
                    method: 'post',
                    evalScripts: false,
                    data: 'xml=' +
                        '<?xml version="1.0" encoding="utf-8" ?>' +
                        XML.hashToHTML(XML.nodeToHash(this.xml)),
                    onSuccess: function(response) {
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
    resetChanges: function() {
        document.location = document.location.href;
    },
    findWidgetByCoords: function(x, y, currentWidget) {
        var cl, mFrame = LayoutManager.mFrame;
        this.columns.some(function(clm) {
            return clm.widgets.some(function(wdg) {
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
    getColumn: function(columnName) {
        return this.columns.get(columnName) || {};
    }
});

/**
 * Колонка
 */
LayoutManager.Column = new Class({
    /**
     * На вход получаем XML описание колонки
     * @param xmlDescr DOMNode
     */
    initialize: function(xmlDescr, layoutManager) {
        this.xml = xmlDescr;
        this.layoutManager = layoutManager;
        this.name = xmlDescr.getProperty('name');
        //хеш виджетов, находящихся внутри колонки
        this.widgets = new Hash();
        //Если существует соответствующий HTML елемент  - представление колонки
        if (this.element = document.getElement('[column=' + this.name + ']')) {
            this.element.addClass('e-lm-column');
            //проходимся по всем дочерним контейнерам и добавляем их в this.widgets
            xmlDescr.getElements('container').each(this.addWidgetAsXML, this);
            this.widgets.set(this.name, new LayoutManager.DummyWidget(this));
        }
    },
    /**
     * Добавление виджета в хеш виджетов колонки
     *
     * @param widgetXML DOMNode
     * @return LayoutManager.Widget
     */
    addWidgetAsXML: function(widgetXML) {
        var widget = null;
        if (widgetXML.getProperty('widget')) {
            this.widgets.set(widgetXML.getProperty('name'), widget =
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
    injectWidget: function(injectedWidget, sourceWidget, location) {
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
    getWidget: function(widgetName) {
        return this.widgets.get(widgetName) || {};
    }
});

LayoutManager.DummyWidget = new Class({
    initialize: function(column) {
        this.column = column;
        this.element = this.container = new Element('div', {'class':'e-lm-dummy-widget'});
        this.column.element.grab(this.element);
        this.visible = true;
        this.toolbar = this._buildToolbar();
    },
    _buildToolbar: function() {
        var tb = new Toolbar('widgetToolbar_' + this.column.name);
        tb.appendControl(new Toolbar.Button({id:'add', 'icon': 'images/toolbar/add.gif', title: 'Add', action:'addWidget'}));
        tb.getElement().inject(this.element, 'top');
        tb.bindTo(this);
        return tb;
    },
    findDirection:function() {
        return 'before';
    },
    addWidget: function() {
        ModalBox.open({
            url:LayoutManager.singlePath + 'widgets/',
            onClose: function(response) {
                if (response) {
                    var XMLHash;
                    var changeNames = function(hash) {
                        if (hash.tag == 'container' ||
                            hash.tag == 'component') {
                            if (hash.attributes['name'] !== undefined) {
                                hash.attributes['name'] +=
                                    Math.floor(Math.random() *
                                        10001).toString();
                                if (hash.children.length) {
                                    hash.children.each(changeNames);
                                }
                            }
                        }
                    }
                    changeNames(XMLHash =
                        XML.rootToHashes(XML.rootFromString(response))[0]);
                    new Request({
                        url:LayoutManager.singlePath + 'widgets/build-widget/',
                        method: 'post',
                        evalScripts: false,
                        data: 'xml=' +
                            '<?xml version="1.0" encoding="utf-8" ?>' +
                            XML.hashToHTML(XMLHash),
                        onSuccess: function(text) {
                            var container = new Element('div'), result;
                            container.set('html', text);
                            if (container.getElement('div')) {
                                result = container.getElement('div').clone();
                                new LayoutManager.Widget(XML.hashToElement(XMLHash), this.column, result, this);
                            }
                            container.destroy();
                        }.bind(this)
                    }).send();
                }
            }.bind(this)
        });
    }
});

/**
 * Виджет
 */
LayoutManager.Widget = new Class({
    /**
     *
     * @param xmlDescr DOMNode
     * @param column LayoutManager.Column
     */
    initialize:function(xmlDescr, column, htmlElement, injectBeforeThisWidget) {
        this.xml = xmlDescr;
        this.column = column;
        this.name = xmlDescr.getProperty('name');
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
            this.overlay = new Overlay(this.element, {indicator:false});

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
    bindElement: function(element) {
        //Создаем елемент контейнера  - содержащего тулбар виджета
        this.container =
            new Element('div', {'class':'e-lm-widget'/*, styles:{'position': 'relative'}*/});
        if (this.element.getParent())
            this.container.wraps(this.element);
        else {
            this.container.grab(this.element);
        }
        this.static = new Boolean(this.element.getProperty('static')).valueOf();
        if (this.static) this.container.addClass('e-lm-static-widget');
        var c;
        if ((c = this.xml.getElement('component')) /*&& !this.static*/) {
            this.component = new LayoutManager.Component(c, this.element);
        }
    },
    _buildToolbar: function() {
        var tb = new Toolbar('widgetToolbar_' + this.name);
        if (!this.static)
            tb.appendControl(new Toolbar.Button({id:'add', 'icon': 'images/toolbar/add.gif', title: 'Add', action:'addWidget'}));
        if (this.component && this.component.params.getLength())
            tb.appendControl(new Toolbar.Button({id:'edit', 'icon': 'images/toolbar/edit.gif', title: 'Edit', action:'editProps'}));
        if (!this.static)
            tb.appendControl(new Toolbar.Button({id:'delete', 'icon': 'images/toolbar/delete.gif', title: 'Delete', action:'delWidget'}));

        tb.appendControl(new Toolbar.Switcher({id:'resize', 'icon': 'images/toolbar/minimize.gif','aicon': 'images/toolbar/restore.gif', title: 'Minimize/Expand', action:'resizeWidget'}));
        tb.getElement().inject(this.container, 'top');
        tb.bindTo(this);
        return tb;
    },
    /**
     * Выводит модальное окно добавления виджета
     */
    addWidget: function() {
        ModalBox.open({
            url:LayoutManager.singlePath + 'widgets/',
            onClose: function(response) {
                if (response) {
                    var XMLHash;
                    var changeNames = function(hash) {
                        if (hash.tag == 'container' ||
                            hash.tag == 'component') {
                            if (hash.attributes['name'] !== undefined) {
                                hash.attributes['name'] +=
                                    Math.floor(Math.random() *
                                        10001).toString();
                                if (hash.children.length) {
                                    hash.children.each(changeNames);
                                }
                            }
                        }
                    }
                    changeNames(XMLHash =
                        XML.rootToHashes(XML.rootFromString(response))[0]);
                    new Request({
                        url:LayoutManager.singlePath + 'widgets/build-widget/',
                        method: 'post',
                        evalScripts: false,
                        data: 'xml=' +
                            '<?xml version="1.0" encoding="utf-8" ?>' +
                            XML.hashToHTML(XMLHash),
                        onSuccess: function(text) {
                            var container = new Element('div'), result;
                            container.set('html', text);
                            if (container.getElement('div')) {
                                result = container.getElement('div').clone();
                                new LayoutManager.Widget(XML.hashToElement(XMLHash), this.column, result, this);
                            }
                            container.destroy();
                        }.bind(this)
                    }).send();
                }
            }.bind(this)
        });
    },
    /**
     * Выводит форму редактирования параметров компонента виджета
     */
    editProps: function() {
        ModalBox.open({
            post:'<?xml version="1.0" encoding="utf-8" ?>' +
                XML.hashToHTML(XML.nodeToHash(this.xml)),
            url:LayoutManager.singlePath + 'widgets/edit-params/' +
                this.component.name +
                '/',
            onClose: function(result) {
                this.overlay.element.addClass('e-overlay-loading');
                if (result) {
                    result.each(function(param, paramName) {
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
    delWidget: function() {
        this.xml.destroy();
        this.container.destroy();
        LayoutManager.changed = true;
    },
    resizeWidget: function() {
        this.element.toggleClass('minimized');
        if (this.dragger) {
            this.dragger.recalculateSize();
        }
    },
    /**
     * Перегружает HTML предеставление виджета
     */
    reload: function() {
        new Request({
            url:LayoutManager.singlePath + 'widgets/build-widget/',
            method: 'post',
            evalScripts: false,
            data: 'xml=' + '<?xml version="1.0" encoding="utf-8" ?>' +
                XML.hashToHTML(XML.nodeToHash(this.xml)),
            onSuccess: function(text) {
                var container = new Element('div'), result;
                container.set('html', text);
                if (container.getElement('div')) {
                    result = container.getElement('div').clone();
                    this.replaceElement(result);
                }
                container.destroy();
            }.bind(this)
        }).send();
    },
    /**
     * Возвращает компонент виджета
     */
    getComponent: function() {
        return this.component || {};
    },
    /**
     * Заменяет елемент
     * @param el
     */
    replaceElement: function(el) {
        el.replaces(this.element);
        this.element = el;
        this.element.addClass('e-widget');
        this.overlay = new Overlay(this.element, {indicator: false});
        //this.overlay.getElement().removeClass('e-overlay-loading');
        this.overlay.show();
    },
    findDirection:function(y) {
        var pos = this.container.getPosition(LayoutManager.mFrame), size = this.container.getSize();
        if (this.static) return 'after';
        return ((y >= pos.y) &&
            (y <= (pos.y + size.y / 4))) ? 'before' : 'after';
    }
});

LayoutManager.Widget.implement(Energine.request);

LayoutManager.Widget.DragBehavior = new Class({
    initialize: function(widget) {
        this.widget = widget;
        this.strut =
            new Element('div', {'class':'e-lm-strut'});
        this.recalculateSize();

        this.drag = new Drag(this.widget.container, {
            grid:6,
            snap:6,
            handle: this.widget.toolbar.getElement(),
            onBeforeStart: function() {
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
                    width:this.size.x + 'px',
                    height:this.size.y + 'px',
                    'z-index':9999,
                    'position': 'absolute'
                });
                this.strut.replaces(this.widget.container);
                this.widget.container.setPosition(this.position);
                this.widget.container.inject(mFrame);

            }.bind(this),
            onComplete: function() {
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
            onCancel: function() {
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
            onDrag: function(el, evt) {
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
    recalculateSize: function() {
        this.size = this.widget.container.getSize();
        this.strut.setStyle('height', (this.size.y - 14) + 'px'); //от высоты контейнера отнимаем 14px, 10px это padding-bottom, 4px это бордеры внизу и вверху страта  
    }

});
/**
 * Компонент
 */
LayoutManager.Component = new Class({
    initialize: function(xmlDescr, element) {
        this.xml = xmlDescr;
        this.name = xmlDescr.getProperty('name');
        this.element = element;
        this.params = new Hash();
        xmlDescr.getElements('param').each(function(xml) {
            this.params.set(xml.getProperty('name'), new LayoutManager.Component.Param(xml));
        }, this);
    },
    getParam: function(paramName) {
        return this.params.get(paramName) || {};
    }
});
/**
 * параметр компонента
 */
LayoutManager.Component.Param = new Class({
    initialize: function(xml) {
        this.xml = xml;
    },
    /**
     * Присвоение значения узлу меняет значение общего XML
     * @param value string
     */
    setValue:function(value) {
        this.xml.set('text', value.toString());
    },
    getValue: function() {
        return this.xml.get('text');
    }

});
