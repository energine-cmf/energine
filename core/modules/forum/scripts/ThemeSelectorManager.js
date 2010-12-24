ScriptLoader.load('GridManager');

var ThemeSelectorManager = new Class({
    Extends : GridManager,
    initialize : function(element) {
        this.parent(element);
    },
    onDoubleClick: function(){
        this.select();
    },
    select : function() {
        var record = this.grid.getSelectedRecord();
        if(record){
            ModalBox.setReturnValue({id:record.theme_id, name: record.theme_name});
        }
        ModalBox.close();
    }
});