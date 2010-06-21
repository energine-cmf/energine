ScriptLoader.load('View');

var Grid = new Class({
	Extends: View,
	options:{
	    onSelect: $empty,
	    onSortChange: $empty,
	    onDoubleClick: $empty
    },

    initialize: function(element, options) {    	
        Asset.css('grid.css');        
        
        this.sort = {
            field: null,
            order:null
        };
        
        this.setOptions(options);
        this.parent(element, this.options);
		this.headOff = this.element.getElement('.gridContainer thead');
		this.headOff.setStyle('display', 'none');
        this.tbody = this.element.getElement('.gridContainer tbody');
        this.headers = this.element.getElements('.gridHeadContainer table.gridTable th');
        this.headers.addEvent('click', this.changeSort.bind(this));
        
        /* добавляем к контейнеру класс, который указывает, что в нем есть грид */
    	this.element.getParents('.e-pane')[0].addClass('e-grid-pane');
    	
    	/* вешаем пересчет размеров гридовой формы на ресайз окна */
    	if(document.getElement('.e-singlemode-layout')){
    		window.addEvent('resize', this.fitGridSize.bind(this));
    	}
    	else {
    		if(navigator.userAgent.indexOf ('MSIE 6') == -1){
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
    setMetadata: function(metadata) {
        /*
         * Проверяем соответствие видимых полей физической структуре таблицы,
         * определяем имя ключевого поля
         */
        var visibleFieldsCount = 0;
        for (var fieldName in metadata) {
            if (metadata[fieldName].key) this.keyFieldName = fieldName;
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
		
		/* растягиваем gridContainer на высоту родительского элемента минус фильтр и голова грида */		
		this.paneContent = this.element.getParent('.e-pane-item');
    	this.filter = this.element.getElement('.filter');
    	this.gridHeadContainer = this.element.getElement('.gridHeadContainer');
    	this.gridContainer = this.element.getElement('.gridContainer');
    	this.fitGridSize();
    	if(!(this.minGridHeight)){
    		this.minGridHeight = this.gridContainer.getStyle('height').toInt();
    	}    	
    	
    	/* растягиваем всю форму до высоты видимого окна */    	
    	if(!(document.getElement('.e-singlemode-layout'))){
    		this.pane = this.element.getParents('.e-pane')[0];
        	this.gridBodyContainer = this.element.getElement('.gridBodyContainer');    	    	
        	this.fitGridFormSize();
        	new Fx.Scroll(document.getElement('.e-mainframe') ? document.getElement('.e-mainframe') : window).toElement(this.pane);
    	}    	
    },
    
    fitGridSize: function() {    	
    	var gridHeight = this.paneContent.getSize().y - this.filter.getSize().y - this.gridHeadContainer.getSize().y - 14;    	
    	if(gridHeight > 0){
    		this.gridContainer.setStyle('height', gridHeight);    		
    	}
    },
    
    fitGridFormSize: function() {
    	var windowHeight = window.getSize().y - 10;
    	var paneHeight = this.pane.getSize().y;
    	var gridBodyHeight = ((this.gridBodyContainer.getSize().y + 2)>this.minGridHeight)?(this.gridBodyContainer.getSize().y + 2):this.minGridHeight;
    	var gridContainerHeight = this.gridContainer.getSize().y;
    	var paneOthersHeight = paneHeight - gridContainerHeight;		
    	if(windowHeight > (this.minGridHeight + paneOthersHeight)){
    		if((gridBodyHeight + paneOthersHeight) > windowHeight){    				
    			this.pane.setStyle('height', windowHeight);
    		}
    		else {
    			this.pane.setStyle('height', gridBodyHeight + paneOthersHeight);
    		}    		
    	}
    	else {
    		this.pane.setStyle('height', this.minGridHeight + paneOthersHeight);	    		
    	}
    	this.fitGridSize();
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

    clear: function() {
        this.selectItem(false);
        while (this.tbody.hasChildNodes()) {
            this.tbody.removeChild(this.tbody.firstChild);
        }
    },

    // Private methods:
    clearHeaders: function(){
          this.sort.field = null;    
          this.sort.field = null;
          this.headers.removeProperty('class');    
    },
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
				var fieldValue = '';
				if (record[fieldName]){
					var fieldValue = record[fieldName].clean();
                }
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

    changeSort: function(event) {
        var getNextDirectionOrderItem = function(current){
            //console.log(current);
            var sortDirectionOrder = ['', 'asc', 'desc'], result, currentIndex;
            if((currentIndex = sortDirectionOrder.indexOf(current)) != -1){
                if((currentIndex + 1) < sortDirectionOrder.length)
                    result = sortDirectionOrder[currentIndex + 1];
                else
                    result = sortDirectionOrder[0];
            }
            else{
                result = sortDirectionOrder[0];
            }
            
            return result;
        }

        var 
            header = $(event.target), 
            sortFieldName = header.getProperty('name'),
            sortDirection  = header.getProperty('class');
            
            
            //проверяем есть ли колонка сортировки в списке колонок    
            if(
                this.metadata[sortFieldName] 
                && 
                this.metadata[sortFieldName].sort == 1
             ){
                this.clearHeaders();
                this.sort.field = sortFieldName;
                this.sort.order = getNextDirectionOrderItem(sortDirection);
                header.addClass(this.sort.order);
                this.fireEvent('onSortChange');
            }
    }
});