ScriptLoader.load('GridManager');
var FormsManager = new Class({
    Extends: GridManager,
    editForm: function() {
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/edit-form/',
            onClose: this._processAfterCloseAction.bind(this)
        });
    },
    viewForm: function(){
        ModalBox.open({
            url:this.singlePath + this.grid.getSelectedRecordKey() + '/viewForm/'
        });
    }
});
