ScriptLoader.load('GridManager');
var FormEditor = new Class({
    Extends:GridManager,
    initialize: function(element){
        this.parent(element);
    },
    editProps: function(){

    },
    onSelect: function(){
        this.parent();
        var curr = this.grid.getSelectedRecord();
        if(curr.field_id == 1){
            this.toolbar.disableControls();
        }
        else {
            this.toolbar.enableControls();
            var b;
            if((curr.field_type != 'FIELD_TYPE_SELECT') && (curr.field_type !=
                    'FIELD_TYPE_MULTI'))
                this.toolbar.disableControls('editProps');
        }
        this.toolbar.enableControls('add');
    }
    

});
