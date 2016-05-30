/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Grid]{@link Grid}</li>
 *     <li>[GridManager]{@link GridManager}</li>
 * </ul>
 *
 * @requires Energine
 * @requires TabPane
 * @requires PageList
 * @requires Toolbar
 * @requires Overlay
 * @requires ModalBox
 * @requires datepicker
 * @requires Filters
 *
 * @author Pavel Dubenko
 * @author Valerii Zinchenko
 *
 * @version 1.1.5
 */

// todo: Strange to use scrolling and changing pages to see more data fields.

ScriptLoader.load('TabPane', 'PageList', 'Toolbar', 'Overlay', 'ModalBox', 'datepicker', 'Filters');

/**
 * From MooTools it implements: Events, Options.
 *
 * @constructor
 * @param {Element} element Element identifier in DOM Tree for the Grid.
 * @param {Object} [options] Set of events.
 */
var Grid = (function () {
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
            'mouseover': function () {
                if (this != grid.getSelectedItem()) {
                    this.addClass('highlighted');
                }
            },
            'mouseout': function () {
                this.removeClass('highlighted');
            },
            'click': function (e) {
                if (!e.control) {
                    if (this != grid.getSelectedItem()) {
                        grid.selectItem(this);
                    }
                }
                else {
                    grid.selectItem(this, true);
                }

            },
            'dblclick': function () {
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
         * @type {Elements}
         */
        selectedItem: new Elements(),

        /**
         * Sorting properties.
         * @type {Object}
         * @property {string} [field = null] Defines the field by which the sorting will applied.
         * @property {string} [order = null] Defines the direction of the sorting. Can be: '', 'asc', 'desc'.
         */
        sort: {
            field: null,
            order: null
        },
        /**
         * Flag that show that dataset is changed eg: when rows are added, updated or deleted
         * Changed as a result
         * @type {Boolean}
         */
        isDirty: false,

        // constructor
        initialize: function (element, options) {
            Asset.css('grid.css');

            /**
             * The main element.
             * @type {Element}
             */
            this.element = $(element);
            this.setOptions(options);

            // TODO: I think this.headOff can be removed, because it is always hidden.
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
                window.addEvent('resize', this.fitGridFormSize.bind(this));
            }

            this.addEvent('dirty', function () {
                this.isDirty = true;
            }.bind(this));
        },

        /**
         * Set the [metadata]{@link Grid.metadata}. It also finds there the [key field name]{@link Grid#keyFieldName}.
         *
         * @function
         * @public
         * @param {Object} metadata [Metadata]{@link Grid#metadata}.
         */
        setMetadata: function (metadata) {
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
        getMetadata: function () {
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
        setData: function (data) {
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
        selectItem: function (item, multiple) {
            if(!multiple)
                this.deselectItem();

            if (item) {
                item.addClass('selected');
                if(multiple)
                    this.selectedItem.push(item);
                else
                    this.selectedItem = new Elements([item]);

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
        deselectItem: function () {
            if (this.selectedItem.length) {
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
        getSelectedItem: function (returnAsArray) {
            if (!arguments.length)
                return (this.selectedItem.length) ? this.selectedItem[0] : null;
            else {
                return (this.selectedItem.length) ? this.selectedItem : null;
            }
        },

        /**
         * Build Grid. Fill the Grid's table body with data fields.
         *
         * @function
         * @public
         */
        build: function () {	  
            var preiouslySelectedRecordKey = this.getSelectedRecordKey();

            this.selectedItem = new Elements();

            if (!this.isEmpty()) {
                if (!this.dataKeyExists(preiouslySelectedRecordKey)) {
                    preiouslySelectedRecordKey = false;
                }
                this.data.each(function (record, id) {
                    addRecord.call(this, record, id, preiouslySelectedRecordKey);		    
                }, this);
                if (!this.selectedItem.length && !preiouslySelectedRecordKey) {
                    this.selectItem(this.tbody.getFirst());
                }
            } else {
                new Element('tr').inject(this.tbody);
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

            this.adjustColumns();

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
                /*if (document.getElements('.grid')[0] == this.element) {
                    new Fx.Scroll(document.getElement('.e-mainframe') ? document.getElement('.e-mainframe') : window).toElement(this.pane);
                }*/
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
        iterateFields: function (fieldName, record, row) {
            // Пропускаем невидимые поля.
            if (!this.metadata[fieldName].visible || this.metadata[fieldName].type == 'hidden') {
                return;
            }

            var cell = new Element('td').inject(row);
            switch (this.metadata[fieldName].type) {
                case 'boolean':
                    var checkbox = new Element('img').setProperties({
                        'src': 'images/checkbox_' + (record[fieldName] == true ? 'on' : 'off') + '.png',
                        'width': '13', 'height': '13'
                    }).inject(cell);
                    cell.setStyles({'text-align': 'center', 'vertical-align': 'middle'});
                    break;
                case 'value':
                case 'lookup':
                    cell.set('html', record[fieldName]['value']);
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
                        var image = new Element('img').setProperties({
                            'src': Energine.resizer + 'w40-h40/' + record[fieldName],
                            'width': 40,
                            'height': 40
                        }).inject(cell);
                        cell.setStyles({'text-align': 'center', 'vertical-align': 'middle'});
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
                        && (prevRow.record[fieldName] == record[fieldName])) {
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
         * Adjust column widths of the table body and table header.
         *
         * @function
         * @protected
         */
        adjustColumns: function () {
            var headers = [];

            // Adjust padding-right for '.gridHeadContainer' element.
            this.gridHeadContainer.setStyle('padding-right', ScrollBarWidth + 'px');

            if (!(this.element.getElement('table.gridTable').hasClass('fixed_columns'))) {
                var tds = this.tbody.getElement('tr').getElements('td'),
                    ths = this.gridHeadContainer.getElements('th'),
                    headCols = this.gridHeadContainer.getElements('col'),
                    bodyCols = this.element.getElements('.gridContainer col');

                // Get the col width from the tbody
                for (var n = 0; n < tds.length; n++) {
                    headers[n] = tds[n].getDimensions({computeSize: true}).totalWidth;
                }

                // Set col width
                for (n = 0; n < tds.length; n++) { 
                    if (headCols[n]!==undefined)
		    headCols[n].setStyle('width', headers[n]);
		    if (bodyCols[n]!==undefined)
                    bodyCols[n].setStyle('width', headers[n]);
                }

                var oversizeHead = [];
                for (n = 0; n < tds.length; n++) {
		    if (ths[n]!==undefined)
                    oversizeHead[n] = ths[n].getDimensions({computeSize: true}).totalWidth > headers[n];
                }
                if (oversizeHead.length > 0) {
                    var newWidth = [],
                        colWidth = [0, 0];

                    for (n = 0; n < tds.length; n++) {
                        if (oversizeHead[n]) {
                            newWidth[n] = ths[n].getDimensions({computeSize: true}).totalWidth;
                            colWidth[1] += newWidth[n] - headers[n];
                        } else {
                            colWidth[0] += headers[n];
                        }
                    }
                    colWidth[1] += colWidth[0];

                    var scaleCoef = colWidth[0] / colWidth[1];

                    for (n = 0; n < tds.length; n++) {			
                        headers[n] = (oversizeHead[n]) ? newWidth[n] : Math.floor(headers[n] * scaleCoef);

                        // Reset col width
			if (headCols[n]!==undefined)			
                        headCols[n].setStyle('width', headers[n]);
			if (bodyCols[n]!==undefined)
                        bodyCols[n].setStyle('width', headers[n]);
                    }
                }
            } else {
                this.tbody.getParent().setStyles({
                    'table-layout': 'fixed'
                });
            }

            this.tbody.getParent().setStyles({
                wordWrap: 'break-word'
            });
        }.protect(),

        /**
         * Fit the height of the Grid's container.
         */
        fitGridSize: function () {
            if (this.paneContent) {
                var margin = this.element.getStyle('margin-top'),
                    eBToolbar = $(document.body).getElement('.e-pane-b-toolbar'),
                    gridHeight = this.paneContent.getSize().y
                        - this.gridHeadContainer.getSize().y
                        - ((this.gridToolbar) ? this.gridToolbar.getSize().y : 0)
                        - ((margin) ? margin.toInt() : 0)
                        - ((eBToolbar) ? eBToolbar.getSize().y : 0);
                if (gridHeight > 0) {
                    this.gridContainer.setStyle('height', gridHeight-9);
                }
            }
        },

        /**
         * Fit the height of the Grid's container if the container is not new modal frame.
         */
        fitGridFormSize: function () {
            if (this.pane) {
                var toolbarH = (this.gridToolbar) ? this.gridToolbar.getSize().y : 0,
                    gridHeadH = this.gridHeadContainer.getComputedSize().totalHeight,
                    paneToolbarT = this.pane.getElement('.e-pane-t-toolbar'),
                    paneToolbarTH = (paneToolbarT) ? paneToolbarT.getSize().y : 0,
                    paneToolbarB = this.pane.getElement('.e-pane-b-toolbar'),
                    paneToolbarBH = (paneToolbarB) ? paneToolbarB.getSize().y : 0,
                    paneH = this.pane.getSize().y,
                    margin = this.element.getStyle('margin-top'),

                    gridBodyContainer = this.element.getElement('.gridBodyContainer'),
                    gridBodyHeight = gridBodyContainer.getSize().y
                        + this.gridContainer.getStyle('border-top-width').toInt()
                        + this.gridContainer.getStyle('border-bottom-width').toInt();
                if (gridBodyHeight < this.minGridHeight) {
                    gridBodyHeight = this.minGridHeight;
                }

                /*
                 * +3 at the end is:
                 *   +2 from e-pane-content border
                 *   +1 from somewhere, I do not why this should be
                 */
                var totalH = toolbarH + gridHeadH + gridBodyHeight + paneToolbarTH + paneToolbarBH
                    + ((margin) ? margin.toInt() : 0) + 3;
                /*
                 * -81 at the end is:
                 *   -31 from e-topframe height
                 *   -50 from footer
                 * they are not visible from grid
                 */
                var windowHeight = window.getSize().y;
                var freespace = windowHeight;
                if ($(document.body).scrollHeight - ScrollBarWidth  < windowHeight) {
                    freespace -= this.pane.getPosition().y + ScrollBarWidth ;
                }

                if (totalH > paneH) {
                    this.pane.setStyle('height', (totalH > freespace) ? freespace : totalH);
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
        isEmpty: function () {
            return !this.data.length;
        },

        /**
         * Return the recordset from the selected data field.
         *
         * @function
         * @public
         * @returns {Object}
         */
        getSelectedRecord: function () {
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
        getSelectedRecordKey: function () {
            if (!this.keyFieldName || !this.getSelectedRecord()) {
                return false;
            }
            var id = this.getSelectedRecord()[this.keyFieldName];
            if (this.metadata[this.keyFieldName].type == 'lookup') {
                id = this.getSelectedRecord()[this.keyFieldName]['id'];
            }
            return id;
        },

        /**
         * Find the <tt>'key'<tt> in the [<tt>'data'</tt>]{@link Grid.data}. If the key exist tru will be returns, otherwise - false.
         *
         * @function
         * @public
         * @param key
         * @returns {boolean}
         */
        dataKeyExists: function (key) {
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
        clear: function () {
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
        onChangeSort: function (event) {
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
    initialize: function (element) {
        /**
         * The main holder element.
         * @type {Element}
         */
        this.element = $(element);
        if($(window.parent.document).getElement('form.e-grid-form')){
            this.element.addClass('inside-form');
        }
        this.delConfirmCounter = 0;
        /**
         * Filter tool.
         * @type {Filters}
         */

            this.filter = new Filters(this);


        /**
         * Pages.
         * @type {PageList}
         */
        this.pageList = new PageList({onPageSelect: this.loadPage.bind(this)});

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
        this.tabPane = new TabPane(this.element, {onTabChange: this.onTabChange.bind(this)});

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
        if (mb && mb.initialized && mb.getCurrent()) {
            $(document.body).addEvent('keypress', function (evt) {
                if (evt.key == 'esc') {
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
    setMvElementId: function (id) {
        this.mvElementId = id;
    },

    /**
     * Get the moved element ID.
     * @function
     * @public
     * @returns {string|number}
     */
    getMvElementId: function () {
        return this.mvElementId;
    },

    /**
     * Reset the moved element ID.
     * @function
     * @public
     */
    clearMvElementId: function () {
        this.mvElementId = null;
    },

    /**
     * Attach the <tt>'toolbar'</tt> to the Grid Manager.
     *
     * @function
     * @public
     * @param {Toolbar} toolbar Toolbar that will be attached to this GridManager.
     */
    attachToolbar: function (toolbar) {
        /**
         * Toolbar.
         * @type {}
         */
        this.toolbar = toolbar;
        //modBySD move toolbar up var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar'); .getStyle('border-bottom-width')
	//var toolbarContainer = this.tabPane.element.getElement('.grid_toolbar');
	//var toolbarContainer = this.tabPane.element.getElement('.e-pane-toolbar');
	var toolbarContainer = this.tabPane.element.getElement('.e-pane-t-toolbar');
        if (toolbarContainer) {
            //toolbarContainer.adopt(this.toolbar.getElement());
	    toolbarContainer.grab(this.toolbar.getElement(),'top');
        } else {
            //this.tabPane.element.adopt(this.toolbar.getElement());
	  this.tabPane.element.grab(this.toolbar.getElement(),'top');
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
    onTabChange: function (data) {
        this.langId = data.lang;
        // Загружаем первую страницу только если панель инструментов уже прикреплена.
        if (this.filter.element) {
            this.filter.remove();
        }
        this.reload();
    },

    /**
     * Event handler. Select the item.
     * @function
     * @public
     */
    onSelect: function () {
    },

    /**
     * Event handler. Double click.
     * @function
     * @public
     */
    onDoubleClick: function () {
        var c;
        if ((c = this.toolbar.getControlById('edit')) && !c.disabled()) {
            this.edit();
        }
        else if (this.toolbar.controls.length) {
            var action = this.toolbar.controls[0].properties.action;
            if (this[action] && !this.toolbar.controls[0].disabled()) this[action]();
        }
    },

    /**
     * Event handler. Change the sorting of the data.
     * @function
     * @public
     */
    onSortChange: function () {
        this.loadPage(1);
    },

    /**
     * Load the first page.
     * @function
     * @public
     */
    reload: function () {
        this.loadPage(1);
    },

    /**
     * Load the specified page number.
     *
     * @function
     * @public
     * @param {number|string} pageNum Page number.
     */
    loadPage: function (pageNum) {
        this.pageList.disable();
        // todo: The toolbar is attached later as this function calls.
        if (this.toolbar) {
            this.toolbar.disableControls();
        }
        this.overlay.show();
        this.grid.clear();

        /*
         This delay was created because of some stupid behavior in Firefox.
         this.paneContent in build() has different height without delay.
         Firefox 26
         */
        (function () {
            Energine.request(
                this.buildRequestURL(pageNum),
                this.buildRequestPostBody(),
                this.processServerResponse.bind(this),
                null,
                this.processServerError.bind(this)
            );
        }).delay(0, this);
    },

    /**
     * Build request URL.
     *
     * @abstract
     * @param {number|string} pageNum Page number.
     * @returns {string}
     */
    buildRequestURL: function (pageNum) {
        var url = '';

        if (this.grid.sort.order) {
            url = this.singlePath + 'get-data/' + this.grid.sort.field + '-'
                + this.grid.sort.order + '/page-' + pageNum
        } else {
            url = this.singlePath + 'get-data/page-' + pageNum;
        }

        return url;
    },

    /**
     * Build request post body.
     *
     * @abstract
     * @returns {string}
     */
    buildRequestPostBody: function () {
        var postBody = '';

        if (this.langId) {
            postBody += 'languageID=' + this.langId + '&';
        }
        
        if (this.filter) {
            postBody += this.filter.getValue();
        }
        return postBody;
    },

    /**
     * Callback function by successful server response.
     *
     * @function
     * @public
     * @param {Object} result Result data from the server.
     */
    processServerResponse: function (result) {
        var control = false;//console.log(result);
        if (this.toolbar) {
            control = this.toolbar.getControlById('add');
        }


        if (!this.initialized) {
            this.grid.setMetadata(result.meta);
            this.initialized = true;
        }

        this.grid.setData(result.data || []);

        if (result.pager) {
            this.pageList.build(result.pager.count, result.pager.current);
        }

        if (!this.grid.isEmpty()) {
            if (this.toolbar) this.toolbar.enableControls();
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
    processServerError: function (responseText) {
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
    processAfterCloseAction: function (returnValue) {
        if (returnValue) {
            if (returnValue.afterClose && this[returnValue.afterClose]) {
                this[returnValue.afterClose].attempt(null, this);
            } else {
                this.loadPage(this.pageList.currentPage);
            }
            this.grid.fireEvent('dirty');
        }
    },

    // Actions:
    /**
     * View action.
     * @function
     * @public
     */
    view: function () {
        ModalBox.open({
            url: this.singlePath +
            this.grid.getSelectedRecordKey()
        });
    },

    /**
     * Add action.
     * @function
     * @public
     */
    add: function () {
        ModalBox.open({
            url: this.singlePath + 'add/',
            onClose: this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Edit action.
     * @function
     * @public
     * @param [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    edit: function (id) {
        if (!parseInt(id)) {
            id = this.grid.getSelectedRecordKey();
        }
        ModalBox.open({
            url: this.singlePath + id + '/edit',
            onClose: this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Move action.
     * @function
     * @public
     * @param {string|number} [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    move: function (id) {
        
        if (!parseInt(id)) {
            id = this.grid.getSelectedRecordKey();
        }
        this.setMvElementId(id);
        ModalBox.open({
            url: this.singlePath + 'move/' + id,
            onClose: this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Move to the top action.
     * @function
     * @public
     */
    moveFirst: function () {
	//modbySD maybeID???
        //this.moveTo('first', this.getMvElementId());
	this.moveTo('first', this.grid.getSelectedRecordKey());
    },

    /**
     * Move to the bottom action.
     * @function
     * @public
     */
    moveLast: function () {
	//modbySD maybeID???
	//this.moveTo('last', this.getMvElementId());
	this.moveTo('last', this.grid.getSelectedRecordKey());
    },

    /**
     * Move above action.
     * @function
     * @public
     * @param {string|number} [id] ID of the data field. If <tt>id</tt> is not specified it will be get from [getSelectedRecordKey()]{@link Grid#getSelectedRecordKey}.
     */
    moveAbove: function (id) {
        if (!parseInt(id)) {
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
    moveBelow: function (id) {
        if (!parseInt(id)) {
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
    moveTo: function (dir, fromId, toId) {
        toId = toId || '';
        this.overlay.show();
        Energine.request(this.singlePath + 'move/' + fromId + '/' + dir + '/' + toId + '/',
            null,
            function () {
                this.overlay.hide();
                ModalBox.setReturnValue(true); // reload
                this.reload();//modbySD this.close() to fix GroupEditMove window 
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
    editPrev: function () {
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
    editNext: function () {
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
    del: function () {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
            'Do you really want to delete selected record?';
        if ((this.delConfirmCounter > 1) || confirm(MSG_CONFIRM_DELETE)) {
            this.delConfirmCounter++;
            this.overlay.show();
            Energine.request(this.singlePath + this.grid.getSelectedRecordKey() +
                '/delete/', null,
                function () {
                    this.overlay.hide();
                    this.grid.fireEvent('dirty');
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
        else {
            this.delConfirmCounter = 0;
        }
    },
     /**
     * Copy action.
     * @function
     * @public modBySD
     */
    copy: function () {
            this.overlay.show();
	    //catalogue/single/goodsEditor/16487/copy/ ERR_DEV_NO_BUILDER:GOODSEDITOR: COPY	    
            Energine.request(this.singlePath + this.grid.getSelectedRecordKey() +
                '/copy/', null,
                function () {		  
                    this.overlay.hide();
                    this.grid.fireEvent('dirty');
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
    },   
    /**
     * Use action
     * Return selected record as a result of modal box call
     *
     * @function
     * @public
     */
    use: function () {
        ModalBox.setReturnValue(this.grid.getSelectedRecord());
        ModalBox.close();
    },
    /**
     * Close action.
     * @function
     * @public
     */
    close: function () {
        ModalBox.close();
    },

    /**
     * Up action.
     * @function
     * @public
     */
    up: function () {
        Energine.request(this.singlePath + this.grid.getSelectedRecordKey() + '/up/',
            (this.filter) ? this.filter.getValue() : null, this.loadPage.pass(this.pageList.currentPage, this));
    },

    /**
     * Down action.
     * @function
     * @public
     */
    down: function () {
        Energine.request(this.singlePath + this.grid.getSelectedRecordKey() + '/down/',
            (this.filter) ? this.filter.getValue() : null, this.loadPage.pass(this.pageList.currentPage, this));
    },

    /**
     * Print action.
     * @function
     * @public
     */
    print: function () {
        window.open(this.element.getProperty('single_template') + 'print/');
    },

    /**
     * CSV action.
     * @function
     * @public
     */
    csv: function () {
        document.location.href = this.element.getProperty('single_template') + 'csv/';
    }
});

document.addEvent('domready', function () {
    /**
     * Scroll bar width of the browser.
     * @type {number}
     */
    ScrollBarWidth = window.top.ScrollBarWidth || (function () {
            var parent = new Element('div', {
                styles: {
                    height: '1px',
                    overflow: 'scroll',
                    visibility: 'hidden'
                }
            });
            var child = new Element('div', {
                styles: {
                    height: '2px'
                }
            });
            parent.grab(child);
            $(document.body).grab(parent);
            var width = parent.offsetWidth - child.offsetWidth;
            parent.destroy();

            return width;
        })();
});
