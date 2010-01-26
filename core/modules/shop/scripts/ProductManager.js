ScriptLoader.load('GridManager.js');

var ProductManager = new Class({
    Extends: GridManager,
    initialize: function(element){
		this.parent(element);
	},
    add: function() {
        ModalBox.open({
            url: this.singlePath + 'add/',
            onClose: function(returnValue){
                if(returnValue == 'add'){
                    this.add();   
                }
                else{
                    this.reloadGrid();
                }
            }.bind(this)
        });
    }
});
