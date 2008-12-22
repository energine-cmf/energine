ScriptLoader.load('GridManager.js');

var ProductTypeEditor = GridManager.extend({
	initialize: function(element){
		this.parent(element);
	},
	showParams : function () {
        ModalBox.open({
            url: this.element.getProperty('single_template') + '/' + this.grid.getSelectedRecordKey() + '/show-params/'
        });

	}
});