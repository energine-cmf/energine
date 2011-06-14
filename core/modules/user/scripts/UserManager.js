ScriptLoader.load('GridManager');

var UserManager  = new Class({
    Extends: GridManager,
    activate: function(){
        
        this.request(this.singlePath + this.grid.getSelectedRecordKey() + '/activate/', null, this.loadPage.pass(this.pageList.currentPage, this));
    },
    ban: function() {
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/ban/',
            onClose:this.reload.bind(this)
        });
    }
});