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
            if(sourceWidget.xml) {
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
        this.element = this.container = new Element('div', {class:'e-lm-dummy-widget'});
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
                            result = container.getElement('div').clone();
                            container.destroy();
                            new LayoutManager.Widget(XML.hashToElement(XMLHash), this.column, result, this);
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
    Implements:Energine.request,
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
            if(!this.static) this.dragger = new LayoutManager.Widget.DragBehavior(this);
            if (!this.element.hasClass('e-widget')) this.element.addClass('e-widget');
            this.overlay = new Overlay(this.element);
            this.overlay.element.removeClass('e-overlay-loading');
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
                new Element('div', {class:'e-lm-widget'/*, styles:{'position': 'relative'}*/});
        if (this.element.getParent())
            this.container.wraps(this.element);
        else {
            this.container.grab(this.element);
        }
        this.static = new Boolean(this.element.getProperty('static')).valueOf();
        if (this.static) this.container.addClass('e-lm-static-widget'); 
        var c;
        if ((c = this.xml.getElement('component')) && !this.static) {
            this.component = new LayoutManager.Component(c, this.element);
        }
    },
    _buildToolbar: function() {
        var tb = new Toolbar('widgetToolbar_' + this.name);
        if(!this.static)
            tb.appendControl(new Toolbar.Button({id:'add', 'icon': 'images/toolbar/add.gif', title: 'Add', action:'addWidget'}));
        if(this.component && this.component.params.getLength())
            tb.appendControl(new Toolbar.Button({id:'edit', 'icon': 'images/toolbar/edit.gif', title: 'Edit', action:'editProps'}));
        if(!this.static)
            tb.appendControl(new Toolbar.Button({id:'delete', 'icon': 'images/toolbar/delete.gif', title: 'Delete', action:'delWidget'}));
        if(/*this.element.getElement('.rcbox_content')*/ true){
            tb.appendControl(new Toolbar.Switcher({id:'resize', 'icon': 'images/toolbar/minimize.gif','aicon': 'images/toolbar/restore.gif', title: 'Minimize/Expand', action:'resizeWidget'}));
        }
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
                            result = container.getElement('div').clone();
                            container.destroy();
                            new LayoutManager.Widget(XML.hashToElement(XMLHash), this.column, result, this);
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
                } else {                    
                    this.overlay.element.removeClass('e-overlay-loading');    
                }
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
    resizeWidget: function(){
        this.element.toggleClass('minimized');
        if(this.dragger){
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
                result = container.getElement('div').clone();
                container.destroy();
                this.replaceElement(result);
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
        this.overlay = new Overlay(this.element);
        this.overlay.element.removeClass('e-overlay-loading');
        this.overlay.show();
    },
    findDirection:function(y) {
        var pos = this.container.getPosition(LayoutManager.mFrame), size = this.container.getSize();
        if(this.static) return 'after';
        return ((y >= pos.y) &&
                (y <= (pos.y + size.y / 4))) ? 'before' : 'after';
    }
});
LayoutManager.Widget.DragBehavior = new Class({
    initialize: function(widget) {
        this.widget = widget;
        this.strut =
                new Element('div', {class:'e-lm-strut'});
        this.recalculateSize();
        
        this.drag = new Drag(this.widget.container, {
            grid:6,
            snap:6,
            handle: this.widget.toolbar.getElement(),
            onBeforeStart: function() {
                var mFrame = LayoutManager.mFrame;

                this.position = this.widget.container.getPosition(mFrame);

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
                    if(w.xml){
                        this.widget.xml.inject(w.xml, this.strut.retrieve('direction'));
                    }
                    else{
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
    recalculateSize: function(){
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
