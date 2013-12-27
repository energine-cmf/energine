/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Grid]{@link Grid}</li>
 *     <li>[GridManager]{@link GridManager}</li>
 *     <li>[GridManager.Filter]{@link GridManager.Filter}</li>
 *     <li>[GridManager.Filter.QueryControls]{@link GridManager.Filter.QueryControls}</li>
 * </ul>
 *
 * @requires Energine
 * @requires TabPane
 * @requires PageList
 * @requires Toolbar
 * @requires Overlay
 * @requires ModalBox
 * @requires datepicker
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.0.3
 */

// todo: Strange to use scrolling and changing pages to see more data fields.

ScriptLoader.load('TabPane', 'PageList', 'Toolbar', 'Overlay', 'ModalBox', 'datepicker');

/**
 * From MooTools it implements: Events, Options.
 *
 * @constructor
 * @param {Element} element Element identifier in DOM Tree for the Grid.
 * @param {Object} [options] Set of events.
 */
var Grid = (function() {
    /**
     * Fit the headers.
     * @deprecated
     * @function
     * @memberOf Grid#
     * @private
     */
    function fitHeaders() {
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
    }

    /**
     * Adds records to the Grid.
     *
     * @function
     * @memberOf Grid#
     * @private
     * @param {Object} record Object with current record properties.
     * @param {number} id ID of the recordset.
     * @param {string|boolean} currentKey Defines which recordset must be selected.
     */
    function addRecord(record, id, currentKey) {
        var row,
            prevRow;

        // Проверяем соответствие записи метаданным.
        for (var fieldName in record) {
            if (!this.metadata[fieldName]) {
                alert('Grid: record doesn\'t conform to metadata.');
                return;
            }
        }

        // Создаем новую строку в таблице.
        row = new Element('tr').addClass((id % 2 == 0) ? 'odd' : 'even').setProperty('unselectable', 'on').inject(this.tbody);
        // Сохраняем запись в объекте строки.
        row.record = record;

        for (var fieldName in record) {
            this.iterateFields(fieldName, record, row);
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
                if (this != grid.getSelectedItem()) {
                    this.addClass('highlighted');
                }
            },
            'mouseout':function () {
                this.removeClass('highlighted');
            },
            'click':function () {
                if (this != grid.getSelectedItem()) {
                    grid.selectItem(this);
                }
            },
            'dblclick':function () {
                /**
                 * Double click event.
                 * @event Grid#doubleClick
                 */
                grid.fireEvent('doubleClick');
            }
        });
    }

    return new Class(/** @lends Grid# */{
        Implements: [Events, Options],

        /**
         * Array of data fields.
         * @type {Object[]}
         */
        data: null,

        /**
         * Array-like object. Each internal object contain the properties (see below) to the each data field in the [data]{@link Grid#data}.
         * @type {Object}
         *
         * @property {string} type Type of the field.
         * @property {boolean} [key] Defines if this field is key field.
         * @property {boolean|number} [sort] Defines the sorting allowed (true == 1; false == 0).
         * @property {boolean} [visible] Defines if the field is visible or not.
         *
         * @example <caption>Structure of metadata</caption>
         * metadata = {
         *     'field1': {
         *         type: 'fieldType1',
         *         [key: true,]
         *         [sort: 'asc'|'desc',]
         *         [visible: true]
         *     },
         *     'field2': {
         *         type: 'fieldType2',
         *         [key: true,]
         *         [sort: 'asc'|'desc',]
         *         [visible: true]
         *     },
         *     ...
         * }
         */
        metadata: null,

        /**
         * Current selected data field.
         * @type {Element}
         */
        selectedItem: null,

        /**
         * Sorting properties.
         * @type {Object}
         * @property {string} [field = null] Defines the field by which the sorting will applied.
         * @property {string} [order = null] Defines the direction of the sorting. Can be: '', 'asc', 'desc'.
         */
        sort: {
            field:null,
            order:null
        },

        // constructor
        initialize: function(element, options) {
            Asset.css('grid.css');

            /**
             * The main element.
             * @type {Element}
             */
            this.element = $(element);
            this.setOptions(options);

            // TODO: I think this.headOff can be removed, because it is allways hidden.
            /**
             * Header of a table in the element '.gridContainer'.
             * @type {Element}
             * @deprecated
             */
            this.headOff = this.element.getElement('.gridContainer thead').setStyle('display', 'none');

            /**
             * Grid's table body.
             * @type {Element}
             */
            this.tbody = this.element.getElement('.gridContainer tbody');

            /**
             * Grid's header.
             * @type {Element}
             */
            this.headers = this.element.getElements('.gridHeadContainer table.gridTable th');
            this.headers.addEvent('click', this.onChangeSort.bind(this));

            // добавляем к контейнеру класс, который указывает, что в нем есть грид
            this.element.getParents('.e-pane')[0].addClass('e-grid-pane');

            // вешаем пересчет размеров гридовой формы на ресайз окна
            if (document.getElement('.e-singlemode-layout')) {
                window.addEvent('resize', this.fitGridSize.bind(this));
            } else {
                if (navigator.userAgent.indexOf('MSIE 6') == -1) {
                    window.addEvent('resize', this.fitGridFormSize.bind(this));
                }
            }
        },

        /**
         * Set the [metadata]{@link Grid.metadata}. It also finds there the [key field name]{@link Grid#keyFieldName}.
         *
         * @function
         * @public
         * @param {Object} metadata [Metadata]{@link Grid#metadata}.
         */
        setMetadata: function(metadata) {
            /*
             * Проверяем соответствие видимых полей физической структуре таблицы,
             * определяем имя ключевого поля
             */
            //var visibleFieldsCount = 0;
            for (var fieldName in metadata) {
                if (metadata[fieldName].key) {
                    /**
                     * Key field name.
                     * @type {string}
                     */
                    this.keyFieldName = fieldName;
                }
            }

            this.metadata = metadata;
        },

        /**
         * Get the current [metadata]{@link Grid#metadata}.
         *
         * @function
         * @public
         * @returns {Object} [Metadata]{@link Grid#metadata}.
         */
        getMetadata: function() {
            return this.metadata;
        },

        /**
         * Set the [data fields]{@link Grid#data}.
         *
         * @function
         * @public
         * @param {Object[]} data Object with [data fields]{@link Gird#data}.
         * @returns {boolean} Returns true if the data fields were successful set, otherwise false.
         */
        setData: function(data) {
            if (!this.metadata) {
                alert('Cannot set data without specified metadata.');
                return false;
            }
            this.data = data;
            return true;
        },

        /**
         * Select the one data field from all [data fields]{@link Grid#data}.
         *
         * @fires Grid#select
         *
         * @function
         * @public
         * @param {Element} item Data field that will be selected.
         */
        selectItem: function(item) {
            this.deselectItem();
            if (item) {
                item.addClass('selected');
                this.selectedItem = item;
                /**
                 * Select event.
                 * @event Grid#select
                 * @param {Element} item Item element that will be selected.
                 */
                this.fireEvent('select', item);
            }
        },

        /**
         * Deselect the selected item.
         * @function
         * @public
         */
        deselectItem: function() {
            if (this.selectedItem) {
                this.selectedItem.removeClass('selected');
            }
        },

        /**
         * Return the [selected item]{@link Grid#selectedItem}.
         *
         * @function
         * @public
         * @returns {Element}
         */
        getSelectedItem: function() {
            return this.selectedItem;
        },

        /**
         * Build Grid. Fill the Grid's table body with data fields.
         *
         * @function
         * @public
         */
        build: function() {
            var preiouslySelectedRecordKey = this.getSelectedRecordKey(),
                headers = [];

            this.selectedItem = null;

            // Set the column width to the 0, if the columns are not fixed.
            if (!(this.element.getElement('table.gridTable').hasClass('fixed_columns'))) {
                this.element.getElements('.gridHeadContainer col').each(function (element, id) {
                    element.setStyle('width', 0);
                });

                this.element.getElements('.gridContainer col').each(function (element, id) {
                    element.setStyle('width', 0);
                });
            }

            if (!this.isEmpty()) {
                if (!this.dataKeyExists(preiouslySelectedRecordKey)) {
                    preiouslySelectedRecordKey = false;
                }
                this.data.each(function (record, id) {
                    addRecord.call(this, record, id, preiouslySelectedRecordKey);
                }, this);
                if (!this.selectedItem && !preiouslySelectedRecordKey) {
                    this.selectItem(this.tbody.getFirst());
                }
            } else {
                new Element('tr').inject(this.tbody);
            }

            // Adjust padding-right for '.gridHeadContainer' element.
            var gridHeadContainer = this.element.getElement('.gridHeadContainer');
            var headSize = gridHeadContainer.getDimensions({computeSize: true});
            var bodySize = this.element.getElement('.gridBodyContainer .gridTable').getDimensions({computeSize: true});

            var scrollBarWidth = headSize.totalWidth - bodySize.totalWidth;
            scrollBarWidth -= headSize['border-left-width'] + headSize['border-right-width'];
            gridHeadContainer.setStyle('padding-right', scrollBarWidth + 'px');

            // FIXME: If the element is not visible, then the column width will be incorrect extracted. (Опроси -> Редактировать)
            if (!(this.element.getElement('table.gridTable').hasClass('fixed_columns'))) {
                //FIXME: In Opera the width sets incorrect.
                // Get the col width from the tbody
                this.tbody.getElement('tr').getElements('td').each(function (td, id) {
                    headers[id] = td.getDimensions({computeSize: true}).totalWidth;
                });
                // Set the col width of the header
                this.element.getElements('.gridHeadContainer col').each(function (col, id) {
                    col.setStyle('width', (!Browser.opera1) ? headers[id] : headers[id]-10);
                });
            } else {
                this.tbody.getParent().setStyles({
                    wordWrap: 'break-word',
                    tableLayout: 'fixed'
                });
            }

            /**
             * Main element that holds Grid's toolbar, header and container.
             * @type {Element}
             */
            this.paneContent = this.element.getParent('.e-pane-item');

            /**
             * Element for Grid's toolbar.
             * @type {Element}
             */
            this.gridToolbar = this.element.getElement('.grid_toolbar');

            /**
             * Element for Grid's header.
             * @type {Element}
             */
            this.gridHeadContainer = this.element.getElement('.gridHeadContainer');

            /**
             * Element for Grid's container.
             * @type {Element}
             */
            this.gridContainer = this.element.getElement('.gridContainer');

            // растягиваем gridContainer на высоту родительского элемента минус фильтр и голова грида
            this.fitGridSize();

            if (!(this.minGridHeight)) {
                var h = this.gridContainer.getStyle('height');
                //Если грид запустился внутри вкладки формы
                if (h) {
                    /**
                     * Minimal Grid's height.
                     * @type {number}
                     */
                    this.minGridHeight = h.toInt();
                } else {
                    // todo: :)
                    //отфонарное на самом деле значение
                    this.minGridHeight = 300;//h.toInt();
                }
            }

            /* растягиваем всю форму до высоты видимого окна */
            if (!(document.getElement('.e-singlemode-layout'))) {
                this.pane = this.element.getParent('.e-pane');
                /**
                 * @deprecated
                 * @type {Element}
                 */
                this.gridBodyContainer = this.element.getElement('.gridBodyContainer');
                this.fitGridFormSize();
                if (document.getElements('.grid')[0] == this.element) {
                    new Fx.Scroll(document.getElement('.e-mainframe') ? document.getElement('.e-mainframe') : window).toElement(this.pane);
                }
            }
        },

        /**
         * Iterates over record's fields and inserts them to the [Grid's table body]{@link Grid#tbody}.
         *
         * @function
         * @protected
         * @param {Object} record Object with fields.
         * @param {Element} row Table row where the data will be inserted.
         */
        iterateFields: function(fieldName, record, row) {
            // Пропускаем невидимые поля.
            if (!this.metadata[fieldName].visible || this.metadata[fieldName].type == 'hidden') {
                return;
            }

            var cell = new Element('td').inject(row);
            switch (this.metadata[fieldName].type) {
                case 'boolean':
                    var checkbox = new Element('img').setProperties({
                        'src':'images/checkbox_' + (record[fieldName] == true ? 'on' : 'off') + '.png',
                        'width':'13', 'height':'13'}).inject(cell);
                    cell.setStyles({ 'text-align':'center', 'vertical-align':'middle' });
                    break;
                case 'textbox':
                    if (record[fieldName] && Object.getLength(record[fieldName])) {
                        cell.set('html', Object.values(record[fieldName]).join(', '));
                    } else {
                        cell.set('html', '&nbsp;');
                    }
                    break;
                case 'file':
                    if (record[fieldName]) {
                        var image = new Element('img').setProperties({ 'src':Energine.resizer + 'w40-h40/' + record[fieldName], 'width':40, 'height':40 }).inject(cell);
                        cell.setStyles({ 'text-align':'center', 'vertical-align':'middle' });
                    }
                    break;
                default :
                    var fieldValue = '';
                    if (record[fieldName] || record[fieldName] == 0) {
                        fieldValue = record[fieldName].toString().clean();
                    }
                    var prevRow = row.getPrevious();
                    if ((this.metadata[fieldName].type == 'select')
                        && (row.getFirst() == cell)
                        && (row.getPrevious())
                        && (prevRow.record[fieldName] == record[fieldName]))
                    {
                        fieldValue = '';
                        prevRow.getFirst().setStyle('font-weight', 'bold');
                    }
                    if (fieldValue != '') {
                        cell.set('html', fieldValue);
                    } else {
                        cell.set('html', '&#160;');
                    }
            }
        }.protect(),

        /**
         * Fit the height of the Grid's container.
         * @function
         * @public
         */
        fitGridSize: function() {
            if (this.paneContent) {
                var gridHeight = this.paneContent.getSize().y -
                    ((this.gridToolbar) ? this.gridToolbar.getSize().y : 0) -
                    this.gridHeadContainer.getSize().y - 4;
                if (gridHeight > 0) {
                    this.gridContainer.setStyle('height', gridHeight);
                }
            }
        },

        /**
         * Fit the height of the Grid's container if the container is not new modal frame.
         * @function
         * @public
         */
        fitGridFormSize: function() {
            if (this.pane) {
                var gridBodyContainer = this.element.getElement('.gridBodyContainer');
                var gridBodyHeight = ((gridBodyContainer.getSize().y + 2)
                    > this.minGridHeight) ? (gridBodyContainer.getSize().y + 2) : this.minGridHeight;
                var paneOthersHeight = this.pane.getSize().y - this.gridContainer.getSize().y;

                var windowHeight = window.getSize().y - 10;
                if (windowHeight > (this.minGridHeight + paneOthersHeight)) {
                    if ((gridBodyHeight + paneOthersHeight) > windowHeight) {
                        this.pane.setStyle('height', windowHeight);
                    } else {
                        this.pane.setStyle('height', gridBodyHeight + paneOthersHeight);
                    }
                } else {
                    this.pane.setStyle('height', this.minGridHeight + paneOthersHeight);
                }
                this.fitGridSize();
            }
        },

        /**
         * Return true if no data fields are stored, otherwise - false.
         *
         * @function
         * @public
         * @returns {boolean}
         */
        isEmpty: function() {
            return !this.data.length;
        },

        /**
         * Return the recordset from the selected data field.
         *
         * @function
         * @public
         * @returns {Object}
         */
        getSelectedRecord: function() {
            if (!this.getSelectedItem()) {
                return false;
            }
            return this.getSelectedItem().record;
        },

        /**
         * Returns the value of the key field from the selected item.
         *
         * @function
         * @public
         * @returns {boolean}
         */
        getSelectedRecordKey: function() {
            if (!this.keyFieldName) {
                return false;
            }
            return this.getSelectedRecord()[this.keyFieldName];
        },

        /**
         * Find the <tt>'key'<tt> in the [<tt>'data'</tt>]{@link Grid.data}. If the key exist tru will be returns, otherwise - false.
         *
         * @function
         * @public
         * @param key
         * @returns {boolean}
         */
        dataKeyExists: function(key) {
            if (!this.data) return false;
            if (!this.keyFieldName) return false;

            return this.data.some(function (item, index) {
                return (item[this.keyFieldName] == key);
            }.bind(this));
        },

        /**
         * Clear the [Grid's table body]{@link Grid#tbody}.
         *
         * @function
         * @public
         */
        clear: function() {
            this.deselectItem();
            while (this.tbody.hasChildNodes()) {
                this.tbody.removeChild(this.tbody.firstChild);
            }
        },

        /**
         * Event handler. Change the sorting of the [data fields]{@link Grid#data}.
         *
         * @fires Grid#sortChange
         *
         * @function
         * @public
         * @param {Object} event Default event object.
         */
        onChangeSort: function(event) {
            var getNextDirectionOrderItem = function (current) {
                var sortDirectionOrder = ['', 'asc', 'desc'],
                    currentIndex,
                    result;

                current = current || '';

                if ((currentIndex = sortDirectionOrder.indexOf(current)) != -1) {
                    if ((++currentIndex) < sortDirectionOrder.length) {
                        result = sortDirectionOrder[currentIndex];
                    } else {
                        result = sortDirectionOrder[0];
                    }
                } else {
                    result = sortDirectionOrder[0];
                }

                return result;
            };

            var header = $(event.target),
                sortFieldName = header.getProperty('name'),
                sortDirection = header.getProperty('class');

            //проверяем есть ли колонка сортировки в списке колонок
            if (this.metadata[sortFieldName] && this.metadata[sortFieldName].sort == 1) {
                this.sort.field = sortFieldName;
                this.sort.order = getNextDirectionOrderItem(sortDirection);

                header.removeProperty('class').addClass(this.sort.order);

                /**
                 * Change the sorting.
                 * @event Grid#sortChange
                 */
                this.fireEvent('sortChange');
            }
        }
    });
})();

/**
 * Grid Manager.
 *
 * @constructor
 * @param {Element} element The main holder element for the Grid Manager.
 */
var GridManager = new Class(/** @lends GridManager# */{
    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    /**
     * Element ID that will be moved.
     * @type {number}
     */
    mvElementId: null,

    /**
     * Language ID.
     * @type {number}
     */
    langId: 0,

    // constructor
    initialize: function(element) {
        /**
         * The main holder element.
         * @type {Element}
         */
        this.element = element;

        /**
         * Filter tool.
         * @type {GridManager.Filter}
         */
        try {
            this.filter = new GridManager.Filter(this);
        } catch (err) {
            console.warn(err);
            console.warn('Filter is not created.');
        }

        /**
         * Pages.
         * @type {PageList}
         */
        this.pageList = new PageList({ onPageSelect:this.loadPage.bind(this) });

        /**
         * Grid.
         * @type {Grid}
         */
        this.grid = new Grid(this.element.getElement('.grid'), {
            onSelect: this.onSelect.bind(this),
            onSortChange: this.onSortChange.bind(this),
            onDoubleClick: this.onDoubleClick.bind(this)
        });

        /**
         * Tabs.
         * @type {TabPane}
         */
        this.tabPane = new TabPane(this.element, { onTabChange:this.onTabChange.bind(this) });

        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.pageList.getElement());
            this.tabPane.element.removeClass('e-pane-has-b-toolbar1');
            this.tabPane.element.addClass('e-pane-has-b-toolbar2');
        } else {
            this.tabPane.element.adopt(this.pageList.getElement());
        }

        /**
         * Visual imitation of waiting.
         * @type {Overlay}
         */
        this.overlay = new Overlay(this.element);

        /**
         * Property <tt>'single_template'</tt> of the [main holder element]{@link GridManager#element}.
         * @type {string}
         */
        this.singlePath = this.element.getProperty('single_template');
        /*Checking if opened in modalbox*/
        var mb = window.parent.ModalBox;
        if(mb && mb.initialized && mb.getCurrent()){
            document.body.addEvent('keypress', function(evt){
                if(evt.key == 'esc'){
                    mb.close();
                }
            });
        }

        // инициализация id записи, которую будем двигать в стейте /move/
        var move_from_id = this.element.getProperty('move_from_id');
        if (move_from_id) {
            this.setMvElementId(move_from_id);
        }

        this.reload();
    },

    /**
     * Set the element ID that will be moved.
     * @function
     * @public
     * @param {string|number} id Element ID.
     */
    setMvElementId: function(id) {
        this.mvElementId = id;
    },

    /**
     * Get the moved element ID.
     * @function
     * @public
     * @returns {string|number}
     */
    getMvElementId: function() {
        return this.mvElementId;
    },

    /**
     * Reset the moved element ID.
     * @function
     * @public
     */
    clearMvElementId: function() {
        this.mvElementId = null;
    },

    /**
     * Attach the <tt>'toolbar'</tt> to the Grid Manager.
     *
     * @function
     * @public
     * @param {Toolbar} toolbar Toolbar that will be attached to this GridManager.
     */
    attachToolbar: function(toolbar) {
        /**
         * Toolbar.
         * @type {}
         */
        this.toolbar = toolbar;
        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.toolbar.getElement());
        } else {
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

    /**
     * Changing the tab of the Grid Manager.
     *
     * @function
     * @public
     * @param {Object} data Object with language ID.
     */
    onTabChange: function(data) {
        this.langId = data.lang;
        // Загружаем первую страницу только если панель инструментов уже прикреплена.
        if (this.filter) {
            this.filter.remove();
        }
        this.reload();
    },

    /**
     * Event handler. Select the item.
     * @function
     * @public
     */
    onSelect: function() {},

    /**
     * Event handler. Double click.
     * @function
     * @public
     */
    onDoubleClick: function() {
        this.edit();
    },

    /**
     * Event handler. Change the sorting of the data.
     * @function
     * @public
     */
    onSortChange: function() {
        this.loadPage(1);
    },

    /**
     * Load the first page.
     * @function
     * @public
     */
    reload: function() {
        this.loadPage(1);
    },

    // todo: Define methods to get url and postBody. - do
    /**
     * Load the specified page number.
     *
     * @function
     * @public
     * @param {number} pageNum Page number.
     */
    loadPage: function(pageNum) {
        var postBody = '',
            url = '';

        this.pageList.disable();
        // todo: The toolbar is attached later as this functions calls.
        if (this.toolbar) {
            this.toolbar.disableControls();
        }
        this.overlay.show();
        this.grid.clear();

        if (this.langId) {
            postBody += 'languageID=' + this.langId + '&';
        }
        if (this.filter) {
            postBody += this.filter.getValue();
        }

        if (this.grid.sort.order) {
            url = this.singlePath + 'get-data/' + this.grid.sort.field + '-'
                + this.grid.sort.order + '/page-' + pageNum
        } else {
            url = this.singlePath + 'get-data/page-' + pageNum;
        }

        /*
        This delay was created because of some stupid behavior in Firefox.
        this.paneContent in build() has different height without delay.
        Firefox 26
         */
        (function() {
            Energine.request(url,
                postBody,
                this.processServerResponse.bind(this),
                null,
                this.processServerError.bind(this)
            );
        }).delay(0, this);
    },

    /**
     * Callback function by successful server response.
     *
     * @function
     * @public
     * @param {Object} result Result data from the server.
     */
    processServerResponse: function(result) {
        var control = this.toolbar.getControlById('add');

        if (!this.initialized) {
            this.grid.setMetadata(result.meta);
            this.initialized = true;
        }

        this.grid.setData(result.data || []);

        if (result.pager) {
            this.pageList.build(result.pager.count, result.pager.current, result.pager.records);
        }

        if (!this.grid.isEmpty()) {
            this.toolbar.enableControls();
            this.pageList.enable();
        }

        if (control) {
            control.enable();
        }

        this.grid.build();
        this.overlay.hide();
    },

    /**
     * Callback function by server error.
     *
     * @function
     * @public
     * @param {string} responseText Server error message.
     */
    processServerError: function(responseText) {
        alert(responseText);
        this.overlay.hide();
    },

    /**
     * Call the next action after finished action 'close'.
     *
     * @function
     * @public
     * @param {Object} [returnValue] Object, that can contain the next action name.
     */
    processAfterCloseAction: function(returnValue) {
        if (returnValue) {
            if (returnValue.afterClose && this[returnValue.afterClose]) {
                this[returnValue.afterClose].attempt(null, this);
            } else {
                this.loadPage(this.pageList.currentPage);
            }
        }
    },

    // Actions:
    /**
     * View action.
     * @function
     * @public
     */
    view: function() {
        ModalBox.open({ url:this.singlePath +
            this.grid.getSelectedRecordKey() });
    },

    /**
     * Add action.
     * @function
     * @public
     */
    add: function() {
        ModalBox.open({
            url:this.singlePath + 'add/',
            onClose:this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Edit action.
     * @function
     * @public
     * @param [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    edit: function(id) {
        if(!parseInt(id)){
            id = this.grid.getSelectedRecordKey();
        }
        ModalBox.open({
            url:this.singlePath + id + '/edit',
            onClose:this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Move action.
     * @function
     * @public
     * @param {string|number} [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    move: function(id) {
        if(!parseInt(id)) {
            id = this.grid.getSelectedRecordKey();
        }
        this.setMvElementId(id);
        ModalBox.open({
            url:this.singlePath + 'move/' + id,
            onClose: this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Move to the top action.
     * @function
     * @public
     */
    moveFirst: function() {
        this.moveTo('first', this.getMvElementId());
    },

    /**
     * Move to the bottom action.
     * @function
     * @public
     */
    moveLast: function() {
        this.moveTo('last', this.getMvElementId());
    },

    /**
     * Move above action.
     * @function
     * @public
     * @param {string|number} [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    moveAbove: function(id) {
        if(!parseInt(id)){
            id = this.grid.getSelectedRecordKey();
        }
        this.moveTo('above', this.getMvElementId(), id);
    },

    /**
     * Move below action.
     * @function
     * @public
     * @param {string|number} [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    moveBelow: function(id) {
        if(!parseInt(id)){
            id = this.grid.getSelectedRecordKey();
        }
        this.moveTo('below', this.getMvElementId(), id);
    },

    /**
     * Move action.
     *
     * @function
     * @public
     * @param {string} dir Defines specific item position ('belolw', 'above', 'last', 'first').
     * @param {string|number} fromId Defines from which ID will the element moved.
     * @param {string|number} toId Defines to which ID will the element moved.
     */
    moveTo: function(dir, fromId, toId) {
        toId = toId || '';
        this.overlay.show();
        Energine.request(this.singlePath + 'move/' + fromId + '/' + dir + '/' + toId + '/',
            null,
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

    /**
     * Edit previous action.
     * @function
     * @public
     */
    editPrev: function() {
        var prevRow;
        if (this.grid.getSelectedItem() && (prevRow = this.grid.getSelectedItem().getPrevious())) {
            this.grid.selectItem(prevRow);
            this.edit();
        }
    },

    /**
     * Edit next action.
     * @function
     * @public
     */
    editNext: function() {
        var nextRow;
        if (this.grid.getSelectedItem() && (nextRow = this.grid.getSelectedItem().getNext())) {
            this.grid.selectItem(nextRow);
            this.edit();
        }
    },

    /**
     * Delete action.
     * @function
     * @public
     */
    del: function() {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
            'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            this.overlay.show();
            Energine.request(this.singlePath + this.grid.getSelectedRecordKey() +
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

    /**
     * Close action.
     * @function
     * @public
     */
    close: function() {
        ModalBox.close();
    },

    /**
     * Up action.
     * @function
     * @public
     */
    up: function() {
        Energine.request(this.singlePath + this.grid.getSelectedRecordKey() + '/up/',
            this.filter.getValue(), this.loadPage.pass(this.pageList.currentPage, this));
    },

    /**
     * Down action.
     * @function
     * @public
     */
    down: function() {
        Energine.request(this.singlePath + this.grid.getSelectedRecordKey() + '/down/',
            this.filter.getValue(), this.loadPage.pass(this.pageList.currentPage, this));
    },

    /**
     * Print action.
     * @function
     * @public
     */
    print: function() {
        window.open(this.element.getProperty('single_template') + 'print/');
    },

    /**
     * CSV action.
     * @function
     * @public
     */
    csv: function() {
        document.location.href = this.element.getProperty('single_template') + 'csv/';
    }
});

/**
 * Filter tool.
 *
 * @throws Element for GridManager.Filter was not found.
 *
 * @constructor
 * @param {GridManager} gridManager
 */
GridManager.Filter = new Class(/** @lends GridManager.Filter# */{
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

    /**
     * Query controls for the filter.
     * @type {GridManager.Filter.QueryControls}
     */
    inputs: null,

    /**
     * Indicates whether the filter is active or not.
     * @type {boolean}
     */
    active: false,

    // constructor
    initialize: function(gridManager) {
        /**
         * Filter element of the GridManager.
         * @type {Element}
         */
        this.element = gridManager.element.getElement('.filter');

        if (!this.element) {
            throw 'Element for GridManager.Filter was not found.';
        }

        var applyButton = this.element.getElement('.f_apply'),
            resetLink = this.element.getElement('.f_reset');

        applyButton.addEvent('click', function () {
            this.use();
            gridManager.reload();
        }.bind(this));

        resetLink.addEvent('click', function (e) {
            Energine.cancelEvent(e);
            this.remove();
            gridManager.reload();
        }.bind(this));

        this.inputs = new GridManager.Filter.QueryControls(this.element.getElements('.f_query_container'), applyButton);

        //FIXME: The filter works not properly by condition '=' and '!=' when the input for field 'datetime' is given only by date (without TIME!!!) -- report a bug.
        this.condition = this.element.getElement('.f_condition');
        this.condition.addEvent('change', function (event) {
            if ($(event.target).get('value') == 'between') {
                this.inputs.asPeriod();
            } else {
                this.inputs.asScalar();
            }
        }.bind(this));

        this.fields = this.element.getElement('.f_fields');
        this.fields.addEvent('change', this.checkCondition.bind(this));

        this.checkCondition();
    },

    /**
     * Check the filter's condition option.
     * @function
     * @public
     */
    checkCondition: function() {
        var isDate = this.fields.getSelected()[0].getAttribute('type') == 'datetime';
        this.inputs.showDatePickers(isDate);
        this.condition.getElements('option[value=like],option[value=notlike]').setStyle('display', (isDate ? 'none' : ''));
        for (var n = 0; isDate && n < this.condition.options.length; n++) {
            if (this.condition.options[n].getStyle('display') !== 'none') {
                this.condition.selectedIndex = n;
                break;
            }
        }
    },

    /**
     * Reset the whole [filter element]{@link GridManager.Filter#element}.
     * @function
     * @public
     */
    remove: function() {
        this.inputs.empty();
        this.element.removeClass('active');
        this.active = false;
    },

    /**
     * Mark the filter element as used or not.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    use: function() {
        if (this.inputs.hasValues()) {
            this.element.addClass('active');
            this.active = true;
        } else {
            this.remove();
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
    getValue: function() {
        var result = '';
        if (this.active && this.inputs.hasValues()) {
            var fieldName = this.fields.options[this.fields.selectedIndex].value,
                fieldCondition = this.condition.options[this.condition.selectedIndex].value;

            result = this.inputs.getValues('filter' + fieldName) + '&filter[condition]=' + fieldCondition + '&';
        }
        return result;
    }
});

/**
 * Query controls.
 *
 * @constructor
 * @param {Elements} els Elements with input fields.
 * @param {Element} applyAction Apply button.
 */
GridManager.Filter.QueryControls = new Class(/** @lends GridManager.Filter.QueryControls# */{
    // constructor
    initialize: function(els, applyAction) {
        /**
         * Holds the query containers.
         * @type {Elements}
         */
        this.containers = els;
        //TODO: Remove the style hidden of the first container from the CSS or HTML!
        this.containers[0].removeClass('hidden');

        /**
         * Holds all input fields.
         * @type {Elements}
         */
        this.inputs = new Elements(this.containers.getElements('input'));

        /**
         * Holds the elements for DatePickers.
         * @type {Elements}
         */
        this.dps = new Elements(this.containers.getElements('.f_datepicker'));

        this.dps.each(function(el, index){
            //note: This is small trick to open DatePicker on the image element, not on the span.
            Energine._createDatePickerObject(el.getFirst(), {
                format:'%Y-%m-%d',
                allowEmpty: true,
                toggle: this.inputs[index],
                useFadeInOut: false
            })
        }.bind(this));


        this.inputs.addEvent('keydown', function (event) {
            if ((event.key == 'enter') && (event.target.value != '')) {
                Energine.cancelEvent(event);
                applyAction.click();
            }
        });
    },

    /**
     * Return true if one of the [inputs]{@link GridManager.Filter.QueryControls#inputs} has a value, otherwise - false.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    hasValues:function () {
        return this.inputs.some(function (el) {
            return el.get('value');
        });
    },

    /**
     * Clear the [input fields]{@link GridManager.Filter.QueryControls#inputs}.
     * @function
     * @public
     */
    empty:function () {
        this.inputs.each(function (el) {
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
    getValues:function (fieldName) {
        var str = '';
        this.inputs.each(function (el, index, els) {
            if (el.get('value')) {
                str += fieldName + '[]=' + el.get('value');
            }
            if (index != (els.length - 1)) {
                str += '&';
            }
        });
        return str;
    },

    /**
     * Enable additional input fields for using the <tt>'between'</tt> filter condition.
     * @function
     * @public
     */
    asPeriod:function () {
        this.containers[1].removeClass('hidden');
        this.inputs.addClass('small');
    },

    /**
     * Enable only one input field for filter.
     * @function
     * @public
     */
    asScalar:function () {
        this.containers[1].addClass('hidden');
        this.inputs.removeClass('small');
    },

    /**
     * Show/hide date pickers.
     * @function
     * @public
     * @param {boolean} toShow Defines whether the date pickers will be visible (by <tt>true</tt>) or hidden (by <tt>false</tt>).
     */
    showDatePickers: function(toShow) {
        if (toShow) {
            this.dps.removeClass('hidden');
        } else {
            this.dps.addClass('hidden');
        }
    }
});