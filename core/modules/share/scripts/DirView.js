ScriptLoader.load('View');

var DirView = new Class({
	Extends: View,
    getOptions: function() {
        return Object.extend(this.parent(), {
            onEdit: $empty,
            onOpen: $empty
        });
    },

    initialize: function(element, options) {
        Asset.css('filemanager.css');
        this.parent(element, options);
        
        /* вешаем пересчет размеров формы на ресайз окна */
        if(!(document.getElement('.e-singlemode-layout'))){    		
        	window.addEvent('resize', this.fitFilemanagerFormSize.bind(this));        	
    	}
	},

	build: function() {		
		if (!this.metadata) {
			alert('View have no metadata.');
			return;
		}
		this.clear();		
		this.data.each(function(record) { this.addRecord(record); }, this);

		var firstChild = this.element.getFirst();
	    if (firstChild) this.selectItem(firstChild);
	    	    
	    /* растягиваем всю форму до высоты видимого окна */			    
    	if(!(document.getElement('.e-singlemode-layout'))){    		
    		this.pane = this.element.getParents('.e-pane')[0];
    		this.paneContent = this.pane.getElement('.e-pane-item');
        	this.filemanager = this.element;        	
        	this.minPaneHeight = 300;
        	this.fitFilemanagerFormSize();
        	new Fx.Scroll(document.getElement('.e-mainframe') ? document.getElement('.e-mainframe') : window).toElement(this.pane);
    	}
	},
	
	fitFilemanagerFormSize: function() {    	  	
    	var windowHeight = window.getSize().y - 10;
    	var paneHeight = this.pane.getSize().y;
    	var filemanagerHeight = this.filemanager.getSize().y;
    	var paneContentHeight = this.paneContent.getSize().y - 22;    	
    	var paneOthersHeight = paneHeight - paneContentHeight;    	
    	if(windowHeight > this.minPaneHeight){
    		if((filemanagerHeight + paneOthersHeight) > windowHeight){    				
    			this.pane.setStyle('height', windowHeight);    			
    		}
    		else {
    			this.pane.setStyle('height', filemanagerHeight + paneOthersHeight);    			
    		}    		
    	}
    	else {
    		this.pane.setStyle('height', this.minPaneHeight);    		
    	}
    },

    clear: function() {
        this.selectItem(false);
        while (this.element.hasChildNodes()) {
            this.element.removeChild(this.element.firstChild);
        }
	},

	addRecord: function(record) {		
        for (var fieldName in record) {
            if (!this.metadata[fieldName]) {
                alert('DirView: record doesn\'t conform to metadata.');
                return false;
            }
        }

        var obj = new Element('div').setProperty('title', record['upl_name']).injectInside(this.element);
		new Element('div').addClass('name').set('html', record['upl_name']).injectInside(obj);

        if ($type(record['upl_data'].thumb) == 'string') {
            new Element('img').setProperty('src', record['upl_data'].thumb).injectInside(obj);
        }
        else {
            new Element('div').addClass(record['className']).injectInside(obj);
        }

		obj.obj = record;

		var widget = this;
		obj.addEvents({
		    'mouseover': function() { if (this != widget.getSelectedItem()) this.addClass('highlighted'); },
            'mouseout': function() { this.removeClass('highlighted'); },
            'click': function() { if (this != widget.getSelectedItem()) widget.selectItem(this); },
            'dblclick': this.fireEvent.pass('onOpen', this)
		});
	},

    switchMode: function() {
        var divElem = this.getSelectedItem().getElement('div.name');
        if (divElem) {
            var inputElem = new Element('input').setProperties({ 'type': 'text', 'value': divElem.innerHTML }).addClass('name');
            //divElem.replaceWith(inputElem);
            inputElem.replaces(divElem);

            inputElem.select();
            inputElem.focus();
            inputElem.addEvent('blur', this.edit.bind(this));
        }
        else {
            var inputElem = this.getSelectedItem().getElement('input.name');
            var divElem = new Element('div').addClass('name').set('html', inputElem.get('value'));
            //inputElem.replaceWith(divElem);
            divElem.replaces(inputElem);
        }
    },

    edit: function() {
        this.switchMode();
        this.fireEvent('onEdit', 'dummy');
    }
});