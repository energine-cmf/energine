ScriptLoader.load('Form.js');

var OrderHistoryForm = Form.extend({
	initialize: function(element){
		this.parent(element);
        this.obj = null;
	},
	viewDetails : function (obj) {
        ModalBox.open({
            url: this.singlePath + $('order_id').value + '/show-details/'
        });
	}

});


