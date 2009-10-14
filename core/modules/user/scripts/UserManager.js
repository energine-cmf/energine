ScriptLoader.load('GridManager.js');

var UserManager  = new Class({
    Extends: GridManager,
    activate: function(){
        
        this.request(this.singleTemplatePath + this.grid.getSelectedRecordKey() + '/activate/', null, this.loadPage.pass(this.pageList.currentPage, this));
    }
});