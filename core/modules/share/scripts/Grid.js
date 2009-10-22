ScriptLoader.load('View.js');

var Grid = new Class({
	Extends: View,
	options:{
	    onSelect: $empty,
	    onSortChange: $empty,
	    onDoubleClick: $empty
    },

    initialize: function(element, options) {
        Asset.css('grid.css');
        this.setOptions(options);
        this.parent(element, this.options);
		this.headOff = this.element.getElement('.gridContainer thead');
		this.headOff.setStyle('display', 'none');
        this.tbody = this.element.getElement('.gridContainer tbody');

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
    setMetadata: function(metadata) {
        /*
         * Проверяем соответствие видимых полей физической структуре таблицы,
         * определяем имя ключевого поля, и параметры сортировки.
         */
        var visibleFieldsCount = 0;
        for (var fieldName in metadata) {
            if (metadata[fieldName].key) this.keyFieldName = fieldName;
            if (metadata[fieldName].visible) {
                if (typeof metadata[fieldName].sort == 'string') {
                    if (['asc', 'desc'].test(metadata[fieldName].sort)) {
                        this.sortOrder = metadata[fieldName].sort;
                    }
                    this.changeSort(visibleFieldsCount);
                }

                visibleFieldsCount++;
            }
        }

        this.parent(metadata);
    },

    build: function() {
		var preiouslySelectedRecordKey = this.getSelectedRecordKey();

		this.headOff.setStyle('visibility', 'hidden');
		this.headOff.setStyle('display', 'table-header-group');

        if (this.data.length) {
			if(!this.dataKeyExists(preiouslySelectedRecordKey)){
				preiouslySelectedRecordKey = false;
			}
            this.data.each(
            	function(record, key) {
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
		this.headOff.getElements('th').each(function(element, key){
			headers[key] = element.clientWidth;
		})


        this.element.getElements('.gridHeadContainer col').each(function(element, key){
			element.setStyle('width',headers[key]);
		});

        this.element.getElements('.gridContainer col').each(function(element, key){
			element.setStyle('width',headers[key]);
		});

		this.element.getElement('.gridContainer table.gridTable').setStyle('tableLayout', 'fixed');
		this.element.getElement('.gridHeadContainer table.gridTable').setStyle('tableLayout', 'fixed');
		this.headOff.setStyle('display', 'none');

    },

    isEmpty: function() {
        return !this.data.length;
    },

    getSelectedRecord: function() {
        if (!this.getSelectedItem()) return false;
        return this.getSelectedItem().record;
    },

    getSelectedRecordKey: function() {
        if (!this.keyFieldName) return false;
        return this.getSelectedRecord()[this.keyFieldName];
    },

    dataKeyExists: function(key){
		if(!this.data) return false;
		if (!this.keyFieldName) return false;

		return this.data.some(function(item, index){
			return (item[this.keyFieldName]== key);
		}.bind(this));
	},

    getSortFieldName: function() {
        //return this.headers[this.sortFieldIndex].fieldName;
    },

    getSortOrder: function() {
        return this.sortOrder;
    },

    clear: function() {
        this.selectItem(false);
        while (this.tbody.hasChildNodes()) {
            this.tbody.removeChild(this.tbody.firstChild);
        }
    },

    // Private methods:

    fitHeaders: function() {
        this.headersContainer.setStyle('visibility', '');
        var firstRow = this.tbody.getFirst();
        this.headers.each(function(header, i) {
            var delta = -(i == 0 ? 27 : 28);
            // Увеличиваем дельту на 16px (размер полосы прокрутки) если это последняя колонка и грид не пустой.
            if (i == firstRow.childNodes.length-1) {
                delta += ((this.data.length || this.prevDataLength > 0) ? 16 : 0);
                this.prevDataLength = this.data.length;
            }
            header.setStyle('width', firstRow.childNodes[i].getSize().size.x + delta + 'px');
        }, this);
        if (!this.data.length) this.tbody.getFirst().dispose();
    },

    addRecord: function(record, key, currentKey) {

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
        var row = new Element('tr').addClass(((key/2)==Math.ceil(key/2))?'odd':'even').setProperty('unselectable', 'on').injectInside(this.tbody);
        // Сохраняем запись в объекте строки.
        row.record = record;

        for (var fieldName in record) {
            // Пропускаем невидимые поля.
            if (!this.metadata[fieldName].visible || this.metadata[fieldName].type == 'hidden') continue;
            var cell = new Element('td').injectInside(row);
            if (this.metadata[fieldName].type == 'boolean') {
                var checkbox = new Element('img').setProperties({
                    'src': 'images/checkbox_' + (record[fieldName] == true ? 'on' : 'off') + '.png',
                    'width': '13', 'height': '13'
                }).injectInside(cell);
                cell.setStyles({ 'text-align': 'center', 'vertical-align': 'middle' });
            }
            else if (this.metadata[fieldName].type == 'image') {
                if(record[fieldName]){
                    var image = new Element('img').setProperties({ 'src': record[fieldName], 'width':50, 'height':50 }).injectInside(cell);
                    cell.setStyles({ 'text-align': 'center', 'vertical-align': 'middle' });                    
                }
            }
            else {
                var fieldValue = record[fieldName].clean();
                if (fieldValue != '') cell.appendText(fieldValue);
                else cell.set('html', '&#160;');
            }
        }

        // Помечаем первую ячейку строки.
        row.getFirst().addClass('firstColumn');
		if (currentKey == record[this.keyFieldName]) {
			this.selectItem(row);
			new Fx.Scroll($(document.body).getElement('.gridContainer')).toElement(row);
		}

        var grid = this;
        row.addEvents({
            'mouseover': function() { if (this != grid.getSelectedItem()) this.addClass('highlighted'); },
            'mouseout': function() { this.removeClass('highlighted'); },
            'click': function() { if (this != grid.getSelectedItem()) grid.selectItem(this); },
            'dblclick': function() { this.fireEvent('onDoubleClick'); }.bind(this)
        });

    },

    changeSort: function(fieldIndex) {
        if (typeof this.sortFieldIndex == 'number') {
            if (fieldIndex == this.sortFieldIndex) {
                this.headers[this.sortFieldIndex].removeClass(this.sortOrder);
                this.sortOrder = (this.sortOrder == 'asc' ? 'desc' : 'asc');
                this.headers[this.sortFieldIndex].addClass(this.sortOrder);
            }
            else {
                this.headers[this.sortFieldIndex].removeClass('sortField').removeClass('asc').removeClass('desc');
            }
        }
        if (fieldIndex != this.sortFieldIndex) {
            this.sortFieldIndex = fieldIndex;
            this.sortOrder = 'asc';
            this.headers[this.sortFieldIndex].addClass('sortField').addClass(this.sortOrder);
        }
        this.fireEvent('onSortChange');
    }
});