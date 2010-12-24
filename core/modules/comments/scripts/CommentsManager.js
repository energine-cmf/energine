// CommentsManager
ScriptLoader.load('GridManager');
var CommentsManager = new Class({
	Extends: GridManager,
	initialize : function(element) {
		this.parent(element)
	},
	loadPage: function(pageNum) {
        this.pageList.disable();

        this.toolbar.disableControls();
        this.overlay.show();
        this.grid.clear();
        var postBody = '', url = this.singlePath + 'get-data/page-' + pageNum;
        if (this.langId) postBody += 'languageID='+this.langId+'&';
        if (this.filter.active && this.filter.query.value.length > 0) {
            var fieldName = this.filter.fields.options[this.filter.fields.selectedIndex].value;
            postBody  += 'filter'+fieldName+'='+this.filter.query.value+'&';
        }
        if(this.grid.sort.order){
            url = this.singlePath + 'get-data/' + this.grid.sort.field + '-' + this.grid.sort.order + '/page-' + pageNum
        }
        
        postBody += 'tab_index=' + this.getNumCurrTab() + '&'
        this.request(url,
                postBody, function(result) {
                if (!this.initialized) {
                    this.grid.setMetadata(result.meta);
                    this.initialized = true;
                }
                this.grid.setData(result.data || []);
                this.grid.build();
                this.pageList.build(result.pager.count, result.pager.current);

                this.overlay.hide();

                if (this.grid.isEmpty()) {
                    if (control = this.toolbar.getControlById('add')) control.enable();
                }
                else {
                    this.toolbar.enableControls();
                    this.pageList.enable();
            	}
            }.bind(this)
        );
    },
    edit: function() {
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/edit/' + this.getNumCurrTab() + '/tab',
            onClose: this.loadPage.pass(this.pageList.currentPage, this)
        });
    },
    getNumCurrTab: function(){
    	return $$('div.e-pane-t-toolbar ul.e-pane-toolbar li.current').getAllPrevious().flatten().length
    },
    approve: function(){
    	var url = this.singlePath + this.grid.getSelectedRecordKey() + '/approve/'
    	var postBody = 'tab_index=' + this.getNumCurrTab() + '&'
    	var selectedItem = this.grid.getSelectedItem().getElement('img')
        this.request(url, postBody, function(result) {
        		if(result['result']) selectedItem.setProperty('src','images/checkbox_on.png')
            }.bind(this)
        );
    },
    del: function() {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
                'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            this.request(this.singlePath + this.grid.getSelectedRecordKey() +
                    '/delete/' + this.getNumCurrTab() + '/tab', 
                    null, this.loadPage.pass(this.pageList.currentPage, this));
        }
    }
})
