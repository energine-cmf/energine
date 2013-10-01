ScriptLoader.load('Toolbar', 'ModalBox');

var FeedToolbar = new Class({
	Extends: Toolbar,
    initialize: function(Container) {
        Asset.css('pagetoolbar.css');
        Asset.css('feedtoolbar.css');
        //TODO это слегка костыль        
        this.parent('feed_toolbar');
        this.bindTo(this);
        this.dock();
        this.feedElementType = false;
        
        
        this.element.inject(document.getElement('.e-topframe'), 'bottom');
       
        var html = $$('html')[0];
        if(html.hasClass('e-has-topframe1')) {
                html.removeClass('e-has-topframe1');
                html.addClass('e-has-topframe2');
        }
        if(html.hasClass('e-has-topframe2')) {
                html.removeClass('e-has-topframe2');
                html.addClass('e-has-topframe3');
        }

		this.load(Container);
		this.singlePath = Container.getProperty('single_template');
        var feedElement = $(Container.getProperty('linkedTo'));
        this.disableControls();
        if(feedElement){
            this._prepareDataSet(feedElement);
            if(this.selected = feedElement.getProperty('current')){
                this.feedElementType = 'form';
                this.enableControls('add', 'edit'/*, 'delete'*/);
            }
            else {
                this.feedElementType = 'list';
                this.enableControls('add');
                this.selected = false;
            }
        }
		Container.dispose();

		this.previous = false;
    },
    
	add: function() {
		ModalBox.open({
            url: this.singlePath + 'add/',
            onClose: function(returnValue){
                if(returnValue == 'add'){
                    this.add();   
                }
                else if(returnValue){
                    this._reload(true);
                }
            }.bind(this)
        });
	},
	edit: function() {
        ModalBox.open({
            url: this.singlePath+this.selected+'/edit/',
            onClose: this._reload.bind(this)
        });
	},
	del: function() {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE]') || 'Do you really want to delete selected record?';
		if (confirm(MSG_CONFIRM_DELETE)) {
            this.request(this.singlePath + this.selected + '/delete/', null, this._reload);
		}
    },
	up: function(){
		this.request(this.singlePath + this.selected + '/up/', null, this._aftermove.pass('up', this));
	},
	down: function(){
		this.request(this.singlePath + this.selected + '/down/', null, this._aftermove.pass('down', this));
	},
	_aftermove: function(direction){
		try {
			if (direction == 'up') {
				var sibling = this.previous.getPrevious();

				if (!sibling.getProperty('record')) {
					throw 'error';
				}
				$(this.previous).inject(sibling, 'before');
			}
			else {
				$(this.previous).inject(this.previous.getNext(), 'after');
			}
		}
		catch (exception) {
			this._reload(true);
		}


	},
	_select:function(element){

		if (this.previous){
			this.previous.removeClass('record_select');
		}

		if (this.previous == element){
			this.selected = this.previous = false;
            this.disableControls(), this.enableControls('add');
		}
		else {
			this.previous = element;
			element.addClass('record_select');
			this.selected = element.getProperty('record');
			this.enableControls();
		}
	},
	_prepareDataSet: function (linkID){
		var linkID, linkChilds;
            linkChilds = linkID.getElements('[record]');
            if(linkChilds.length){
    			//список
                linkID.addClass('active_component');
    			linkID.fade(0.7);
    			linkChilds.each(function(element){
    				element.addEvent('mouseover', function(){this.addClass('record_highlight')});
    				element.addEvent('mouseout', function(){this.removeClass('record_highlight')});
    				element.addEvent('click', this._select.bind(this, element));
    			}, this);
		  }
	},
	_reload: function(data){
		if (data) {
			var form = new Element('form').setProperties({'action':'', 'method':'POST'});
			form.adopt(new Element('input').setProperty('name', 'editMode').setProperty('type', 'hidden'));
			document.body.adopt(form);
			form.submit();
		}
	}

});

FeedToolbar.implement(Energine.request);