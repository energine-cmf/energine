ScriptLoader.load('GridManager');
var FormEditor = new Class({
    Extends:GridManager,
    initialize: function(element){
        this.parent(element);
    },
    onSelect: function(){
	/*var curr = this.grid.getSelectedRecord();
	
	if(curr.field_id == 1){
	    this.toolbar.disableControls();
	}*/
    }
});
