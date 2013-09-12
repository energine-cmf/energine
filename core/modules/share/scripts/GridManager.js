ScriptLoader.load('View', 'TabPane', 'PageList', 'Toolbar', 'Overlay', 'ModalBox', 'datepicker');

var Grid = new Class({
    Extends:View,
    options:{
        onSelect:$empty,
        onSortChange:$empty,
        onDoubleClick:$empty
    },

    initialize:function (element, options) {
        Asset.css('grid.css');

        this.sort = {
            field:null,
            order:null
        };

        this.setOptions(options);
        this.parent(element, this.options);
        this.headOff = this.element.getElement('.gridContainer thead');
        this.headOff.setStyle('display', 'none');
        this.tbody = this.element.getElement('.gridContainer tbody');
        this.headers =
            this.element.getElements('.gridHeadContainer table.gridTable th');
        this.headers.addEvent('click', this.changeSort.bind(this));

        /* добавляем к контейнеру класс, который указывает, что в нем есть грид */
        this.element.getParents('.e-pane')[0].addClass('e-grid-pane');

        /* вешаем пересчет размеров гридовой формы на ресайз окна */
        if (document.getElement('.e-singlemode-layout')) {
            window.addEvent('resize', this.fitGridSize.bind(this));
        }
        else {
            if (navigator.userAgent.indexOf('MSIE 6') == -1) {
                window.addEvent('resize', this.fitGridFormSize.bind(this));
            }
        }
    },

    /*
     * {
     *     <fieldName>: {
     *         type: <fieldType>,
     *         [key: true,]
     *         [sort: 'asc'|'desc',]
     *         [visible: true]
     *     },
     *     ...
     * }
     */
    setMetadata:function (metadata) {
        /*
         * Проверяем соответствие видимых полей физической структуре таблицы,
         * определяем имя ключевого поля
         */
        //var visibleFieldsCount = 0;
        for (var fieldName in metadata) {
            if (metadata[fieldName].key) this.keyFieldName = fieldName;
        }

        this.parent(metadata);
    },

    build:function () {
        var preiouslySelectedRecordKey = this.getSelectedRecordKey();

        this.headOff.setStyle('visibility', 'hidden');
        this.headOff.setStyle('display', 'table-header-group');

        if (this.data.length) {
            if (!this.dataKeyExists(preiouslySelectedRecordKey)) {
                preiouslySelectedRecordKey = false;
            }
            this.data.each(
                function (record, key) {
                    this.addRecord(record, key, preiouslySelectedRecordKey);
                },
                this
            );
            if (!preiouslySelectedRecordKey) {
                this.selectItem(this.tbody.getFirst());
            }
        }
        else {
            this.addRecord(null);
        }

        var headers = new Array();
        this.headOff.getElements('th').each(function (element, key) {
            headers[key] = element.clientWidth;
        })

        if (!(this.element.getElement('table.gridTable').hasClass('fixed_columns'))) {
            this.element.getElements('.gridHeadContainer col').each(function (element, key) {
                element.setStyle('width', headers[key]);
            });

            this.element.getElements('.gridContainer col').each(function (element, key) {
                element.setStyle('width', headers[key]);
            });
        }

        this.element.getElement('.gridContainer table.gridTable').setStyle('tableLayout', 'fixed');
        this.element.getElement('.gridHeadContainer table.gridTable').setStyle('tableLayout', 'fixed');
        this.headOff.setStyle('display', 'none');

        /* растягиваем gridContainer на высоту родительского элемента минус фильтр и голова грида */
        this.paneContent = this.element.getParent('.e-pane-item');
        this.gridToolbar = this.element.getElement('.grid_toolbar');
        this.gridHeadContainer = this.element.getElement('.gridHeadContainer');
        this.gridContainer = this.element.getElement('.gridContainer');
        this.fitGridSize();

        if (!(this.minGridHeight)) {
            var h = this.gridContainer.getStyle('height');
            //Если грид запустился внутри вкладки формы
            if (h) {
                this.minGridHeight = h.toInt();
            }
            else {
                //отфонарное на самом деле значение
                this.minGridHeight = 300;//h.toInt();
            }
        }

        /* растягиваем всю форму до высоты видимого окна */
        if (!(document.getElement('.e-singlemode-layout'))) {
            this.pane = this.element.getParents('.e-pane')[0];
            this.gridBodyContainer =
                this.element.getElement('.gridBodyContainer');
            this.fitGridFormSize();            
            if (document.getElements('.grid')[0] == this.element) {
                new Fx.Scroll(document.getElement('.e-mainframe') ? document.getElement('.e-mainframe') : window).toElement(this.pane);
            }
        }
    },

    fitGridSize:function () {
        if (this.paneContent) {
            var gridHeight = this.paneContent.getSize().y -
                ((this.gridToolbar) ? this.gridToolbar.getSize().y : 0) -
                this.gridHeadContainer.getSize().y - 4;
            if (gridHeight > 0) {
                this.gridContainer.setStyle('height', gridHeight);
            }
        }
    },

    fitGridFormSize:function () {
        if (this.pane) {
            var windowHeight = window.getSize().y - 10;
            var paneHeight = this.pane.getSize().y;
            var gridBodyHeight = ((this.gridBodyContainer.getSize().y + 2) >
                this.minGridHeight) ? (this.gridBodyContainer.getSize().y +
                2) : this.minGridHeight;
            var gridContainerHeight = this.gridContainer.getSize().y;
            var paneOthersHeight = paneHeight - gridContainerHeight;
            if (windowHeight > (this.minGridHeight + paneOthersHeight)) {
                if ((gridBodyHeight + paneOthersHeight) > windowHeight) {
                    this.pane.setStyle('height', windowHeight);
                }
                else {
                    this.pane.setStyle('height', gridBodyHeight +
                        paneOthersHeight);
                }
            }
            else {
                this.pane.setStyle('height', this.minGridHeight +
                    paneOthersHeight);
            }
            this.fitGridSize();
        }
    },

    isEmpty:function () {
        return !this.data.length;
    },

    getSelectedRecord:function () {
        if (!this.getSelectedItem()) return false;
        return this.getSelectedItem().record;
    },

    getSelectedRecordKey:function () {
        if (!this.keyFieldName) return false;
        return this.getSelectedRecord()[this.keyFieldName];
    },

    dataKeyExists:function (key) {
        if (!this.data) return false;
        if (!this.keyFieldName) return false;

        return this.data.some(function (item, index) {
            return (item[this.keyFieldName] == key);
        }.bind(this));
    },

    clear:function () {
        this.selectItem(false);
        while (this.tbody.hasChildNodes()) {
            this.tbody.removeChild(this.tbody.firstChild);
        }
    },

    // Private methods:
    clearHeaders:function () {
        this.sort.field = null;
        this.sort.field = null;
        this.headers.removeProperty('class');
    },
    fitHeaders:function () {
        this.headersContainer.setStyle('visibility', '');
        var firstRow = this.tbody.getFirst();
        this.headers.each(function (header, i) {
            var delta = -(i == 0 ? 27 : 28);
            // Увеличиваем дельту на 16px (размер полосы прокрутки) если это последняя колонка и грид не пустой.
            if (i == firstRow.childNodes.length - 1) {
                delta += ((this.data.length ||
                    this.prevDataLength > 0) ? 16 : 0);
                this.prevDataLength = this.data.length;
            }
            header.setStyle('width', firstRow.childNodes[i].getSize().size.x +
                delta + 'px');
        }, this);
        if (!this.data.length) this.tbody.getFirst().dispose();
    },

    addRecord:function (record, key, currentKey) {

        if (!record) {
            var row = new Element('tr').injectInside(this.tbody);
            return;
        }
        // Проверяем соответствие записи метаданным.
        for (var fieldName in record) {
            if (!this.metadata[fieldName]) {
                alert('Grid: record doesn\'t conform to metadata.');
                return false;
            }
        }

        // Создаем новую строку в таблице.
        var row = new Element('tr').addClass(((key / 2) == Math.ceil(key /
            2)) ? 'odd' : 'even').setProperty('unselectable', 'on').injectInside(this.tbody);
        // Сохраняем запись в объекте строки.
        row.record = record;
        var prevRow;

        for (var fieldName in record) {
            this.iterateFields(record, fieldName, row);
        }

        // Помечаем первую ячейку строки.
        row.getFirst().addClass('firstColumn');
        if (currentKey == record[this.keyFieldName]) {
            this.selectItem(row);
            new Fx.Scroll($(document.body).getElement('.gridContainer')).toElement(row);
        }

        var grid = this;
        row.addEvents({
            'mouseover':function () {
                if (this !=
                    grid.getSelectedItem()) this.addClass('highlighted');
            },
            'mouseout':function () {
                this.removeClass('highlighted');
            },
            'click':function () {
                if (this != grid.getSelectedItem()) grid.selectItem(this);
            },
            'dblclick':function () {
                this.fireEvent('onDoubleClick');
            }.bind(this)
        });

    },
    iterateFields:function (record, fieldName, row) {
        // Пропускаем невидимые поля.
        if (!this.metadata[fieldName].visible ||
            this.metadata[fieldName].type == 'hidden') return;
        var cell = new Element('td').injectInside(row);
        if (this.metadata[fieldName].type == 'boolean') {
            var checkbox = new Element('img').setProperties({
                'src':'images/checkbox_' +
                    (record[fieldName] == true ? 'on' : 'off') + '.png',
                'width':'13', 'height':'13'
            }).injectInside(cell);
            cell.setStyles({ 'text-align':'center', 'vertical-align':'middle' });
        }
        else if (this.metadata[fieldName].type == 'textbox') {
            if (record[fieldName] && Object.getLength(record[fieldName])) {
                cell.set('html', Object.values(record[fieldName]).join(', '));
            }
            else {
                cell.set('html', '&nbsp;');
            }
        }
        else if (this.metadata[fieldName].type == 'file') {
            if (record[fieldName]) {
                var image = new Element('img').setProperties({ 'src':Energine.resizer + 'w40-h40/' + record[fieldName], 'width':40, 'height':40 }).injectInside(cell);
                cell.setStyles({ 'text-align':'center', 'vertical-align':'middle' });
            }
        }
        else {
            var fieldValue = '';
            if (record[fieldName]
                || record[fieldName] == 0) {
                var fieldValue = record[fieldName].toString().clean();
            }
            if (
                (this.metadata[fieldName].type == 'select')
                    &&
                    (row.getFirst() == cell)
                    &&
                    (prevRow = row.getPrevious())
                    &&
                    (prevRow.record[fieldName] == record[fieldName])
                ) {
                fieldValue = '';
                prevRow.getFirst().setStyle('font-weight', 'bold');
            }
            if (fieldValue != '') cell.set('html', fieldValue);
            //if (fieldValue != '') cell.appendText(fieldValue);
            else cell.set('html', '&#160;');
        }
    },
    changeSort:function (event) {
        var getNextDirectionOrderItem = function (current) {
            if(!current)current = '';
            var sortDirectionOrder = ['', 'asc', 'desc'], result, currentIndex;

            if ((currentIndex = sortDirectionOrder.indexOf(current)) != -1) {
                if ((currentIndex + 1) < sortDirectionOrder.length)
                    result = sortDirectionOrder[currentIndex + 1];
                else
                    result = sortDirectionOrder[0];
            }
            else {
                result = sortDirectionOrder[0];
            }

            return result;
        }

        var
            header = $(event.target),
            sortFieldName = header.getProperty('name'),
            sortDirection = header.getProperty('class');

        //проверяем есть ли колонка сортировки в списке колонок
        if (
            this.metadata[sortFieldName]
                &&
                this.metadata[sortFieldName].sort == 1
            ) {
            this.clearHeaders();
            this.sort.field = sortFieldName;
            this.sort.order = getNextDirectionOrderItem(sortDirection);

            header.addClass(this.sort.order);
            this.fireEvent('onSortChange');
        }
    }
});

var GridManager = new Class({
    initialize:function (element) {
        this.element = element;

        this.filter = new GridManager.Filter(this);

        this.tabPane =
            new TabPane(this.element, { onTabChange:this.onTabChange.bind(this) });

        this.grid = new Grid(this.element.getElement('.grid'), {
            onSelect:this.onSelect.bind(this),
            onSortChange:this.changeSort.bind(this),
            onDoubleClick:this.onDoubleClick.bind(this)
        });
        this.pageList =
            new PageList({ onPageSelect:this.loadPage.bind(this) });
        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.pageList.getElement());
            this.tabPane.element.removeClass('e-pane-has-b-toolbar1');
            this.tabPane.element.addClass('e-pane-has-b-toolbar2');
        }
        else {
            this.tabPane.element.adopt(this.pageList.getElement());
        }
        this.overlay = new Overlay(this.element);
        this.singlePath = this.element.getProperty('single_template');

        this.mvElementId = null;

        // инициализация id записи, которую будем двигать в стейте /move/
        var move_from_id = this.element.getProperty('move_from_id');
        if (move_from_id) {
            this.setMvElementId(move_from_id);
        }
    },

    setMvElementId: function(id) {
        this.mvElementId = id;
    },

    getMvElementId: function() {
        return this.mvElementId;
    },

    clearMvElementId: function() {
        this.mvElementId = null;
    },

    attachToolbar:function (toolbar) {
        this.toolbar = toolbar;
        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.toolbar.getElement());
        }
        else {
            this.tabPane.element.adopt(this.toolbar.getElement());
        }
        this.toolbar.disableControls();
        toolbar.bindTo(this);
        /*
         * Панель инструментов прикреплена, загружаем первую страницу.
         *
         * Делаем секундную задержку для надёжности:
         * пусть браузер распарсит стили и просчитает размеры элементов.
         */
        //this.reload.delay(1000, this);
    },

    onTabChange:function (tabData) {
        this.langId = tabData.lang;
        // Загружаем первую страницу только если панель инструментов уже прикреплена.
        this.filter.remove();
        this.reload();
    },

    onSelect:function () {

    },
    onDoubleClick:function () {
        this.edit();
    },
    changeSort:function () {
        this.loadPage.delay(10, this, 1);
    },

    reload:function () {
        this.loadPage.delay(10, this, 1);
    },

    loadPage:function (pageNum) {
        this.pageList.disable();

        this.toolbar.disableControls();
        this.overlay.show();
        this.grid.clear();
        var postBody = '', url = this.singlePath + 'get-data/page-' + pageNum;
        if (this.langId) postBody += 'languageID=' + this.langId + '&';
        postBody += this.filter.getValue();
        if (this.grid.sort.order) {
            url = this.singlePath + 'get-data/' + this.grid.sort.field + '-' +
                this.grid.sort.order + '/page-' + pageNum
        }
        this.request(url,
            postBody,
            this.processServerResponse.bind(this),
            null,
            this.processServerError.bind(this)
        );
    },
    processServerResponse:function (result) {
        var control;
        if (!this.initialized) {
            this.grid.setMetadata(result.meta);
            this.initialized = true;
        }
        this.grid.setData(result.data || []);


        if (result.pager)
            this.pageList.build(result.pager.count, result.pager.current, result.pager.records);


        if (!this.grid.isEmpty()) {
            this.toolbar.enableControls();
            this.pageList.enable();
        }


        if (control = this.toolbar.getControlById('add')) control.enable();
        this.grid.build();
        this.overlay.hide();
    },
    processServerError:function (responseText) {
        alert(responseText);
        this.overlay.hide();
    },
    // Actions:

    view:function () {
        ModalBox.open({ url:this.singlePath +
            this.grid.getSelectedRecordKey() });
    },

    add:function () {
        ModalBox.open({
            url:this.singlePath + 'add/',
            onClose:this._processAfterCloseAction.bind(this)
        });
    },

    edit:function (id) {
        if(!parseInt(id)){
            id = this.grid.getSelectedRecordKey();
        }
        ModalBox.open({
            url:this.singlePath + id + '/edit',
            onClose:this._processAfterCloseAction.bind(this)
        });
    },

    move:function (id) {
        if(!id) {
            id = this.grid.getSelectedRecordKey();
        }
        this.setMvElementId(id);
        ModalBox.open({
            url:this.singlePath + 'move/' + id,
            onClose: this._processAfterCloseAction.bind(this)
        });
    },

    moveFirst: function() {
        return this.moveTo('first', this.getMvElementId());
    },

    moveLast: function() {
        return this.moveTo('last', this.getMvElementId());
    },

    moveAbove: function(id) {
        if(!parseInt(id)){
            id = this.grid.getSelectedRecordKey();
        }
        return this.moveTo('above', this.getMvElementId(), id);
    },

    moveBelow: function(id) {
        if(!parseInt(id)){
            id = this.grid.getSelectedRecordKey();
        }
        return this.moveTo('below', this.getMvElementId(), id);
    },

    moveTo: function (dir, fromId, toId) {
        toId = toId || '';
        this.overlay.show();
        this.request(this.singlePath + 'move/' + fromId + '/' + dir + '/' + toId + '/'
            , null,
            function () {
                this.overlay.hide();
                ModalBox.setReturnValue(true); // reload
                this.close();
            }.bind(this),
            function (responseText) {
                this.overlay.hide();
            }.bind(this),
            function (responseText) {
                alert(responseText);
                this.overlay.hide();
            }.bind(this)
        );
    },

    _processAfterCloseAction:function (returnValue) {
        if (returnValue) {
            if (returnValue.afterClose && this[returnValue.afterClose]) {
                this[returnValue.afterClose].attempt(null, this);
            }
            else {
                this.loadPage(this.pageList.currentPage);
            }
        }
    },
    editPrev:function () {
        var prevRow;
        if (this.grid.getSelectedItem() && (prevRow = this.grid.getSelectedItem().getPrevious())) {
            this.grid.selectItem(prevRow);
            this.edit();
        }
    },
    editNext:function () {
        var nextRow;
        if (this.grid.getSelectedItem() && (nextRow = this.grid.getSelectedItem().getNext())) {
            this.grid.selectItem(nextRow);
            this.edit();
        }
    },
    del:function () {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
            'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            this.overlay.show();
            this.request(this.singlePath + this.grid.getSelectedRecordKey() +
                '/delete/', null,
                function () {
                    this.overlay.hide();
                    this.loadPage(this.pageList.currentPage);
                }.bind(this),
                function (responseText) {
                    this.overlay.hide();
                }.bind(this),
                function (responseText) {
                    alert(responseText);
                    this.overlay.hide();
                }.bind(this)
            );
        }
    },

    close:function () {
        ModalBox.close();
    },
    up:function () {
        this.request(this.singlePath + this.grid.getSelectedRecordKey() +
            '/up/', '', this.loadPage.pass(this.pageList.currentPage, this));
    },

    down:function () {
        this.request(this.singlePath + this.grid.getSelectedRecordKey() +
            '/down/', '', this.loadPage.pass(this.pageList.currentPage, this));
    },
    print:function () {
        window.open(this.element.getProperty('single_template') + 'print/');
    },
    csv:function () {
        document.location.href =
            this.element.getProperty('single_template') + 'csv/';
    }
});

GridManager.Filter = new Class({
    initialize:function (gridManager) {
        this.gm = gridManager;
        this.element = this.gm.element.getElement('.filter');
        this.fields = false;
        this.inputs = false;
        this.active = false;
        if (this.element) {
            var applyButton = this.element.getElement('.f_apply'), resetLink = this.element.getElement('.f_reset');
            this.fields = this.element.getElement('.f_fields');
            applyButton.addEvent('click', function () {
                this.use();
                this.gm.reload.apply(this.gm);
            }.bind(this));
            resetLink.addEvent('click', function (e) {
                Energine.cancelEvent(e);
                this.remove();
                this.gm.reload.apply(this.gm);
            }.bind(this));

            this.inputs =
                new GridManager.Filter.QueryControls(this.element.getElements('.f_query_container'), applyButton);
            this.condition = this.element.getElement('.f_condition');

            this.condition.addEvent('change', function (event) {
                //prepareInputs();
                var condition = $(event.target).get('value');

                if (condition == 'between') {
                    this.inputs.asPeriod();
                }
                else {
                    this.inputs.asScalar();
                }
            }.bind(this));
        }
    },
    remove:function () {
        if (this.element) {
            this.inputs.empty();
            this.element.removeClass('active');
            this.active = false;
        }
    },
    use:function () {
        var reloadOnExit = true;
        if (this.inputs.hasValues()) {
            this.element.addClass('active');
            this.active = true;
        }
        else if (this.active) {
            this.remove();
        }
        else {
            reloadOnExit = false;
        }

        return reloadOnExit;
    },
    getValue:function () {
        var result = '';
        if (this.active && this.inputs.hasValues()) {
            var
                fieldName = this.fields.options[this.fields.selectedIndex].value,
                fieldCondition = this.condition.options[this.condition.selectedIndex].value;
            result = this.inputs.getValues('filter' + fieldName) +
                '&filter[condition]=' + fieldCondition + '&';
        }
        return result;
    }
});

GridManager.Filter.QueryControls = new Class({
    initialize:function (els, applyAction) {
        this.containers = els;
        this.inputs = [];
        this.dps = [];
        this.containers.each(function (el) {
            this.inputs.push(el.getElement('input'));
            this.dps.push(el.getElement('.f_datepicker'));
        }.bind(this));

        this.inputs = new Elements(this.inputs);
        this.dps = new Elements(this.dps);
        /*this.dps.each(function(el, index){
         Energine._createDatePickerObject(el, {
         format:'j-m-Y',
         allowEmpty: true,
         inputOutputFormat: 'Y-m-d',
         toggleElements: this.inputs[index]
         })
         }.bind(this));*/


        this.inputs.addEvent('keydown', function (event) {
            event = new Event(event);
            if ((event.key == 'enter') && (event.target.value != '')) {
                Energine.cancelEvent(event);
                applyAction.click();
            }
        });
    },
    hasValues:function () {
        return this.inputs.some(function (el) {
            return ($(el)) ? el.get('value') : false
        });
    },
    empty:function () {
        this.inputs.each(function (el) {
            el.set('value', '')
        });
    },
    getValues:function (fieldName) {
        var str = '';
        this.inputs.each(function (el, index, els) {
            if (el.get('value')) str += fieldName + '[]=' + el.get('value');
            if (index != (els.length - 1)) str += '&';
        });
        return str;
    },
    asDateSelector:function () {
        //this.dps.removeClass('hidden').setStyle('display', '');
    },
    asTextSelector:function () {
        //this.dps.addClass('hidden');
    },
    asPeriod:function () {
        this.containers.removeClass('hidden');
        this.inputs.addClass('small');
    },
    asScalar:function () {
        this.containers[1].addClass('hidden');
        this.inputs.removeClass('small');
    }
});
GridManager.implement(Energine.request);