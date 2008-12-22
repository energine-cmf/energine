ScriptLoader.load('Form.js');

var GalleryForm = Form.extend({
	initialize: function(element){
		this.parent(element);
        this.obj = null;
	},
	showTree : function (obj) {
        this.obj = obj;
        ModalBox.open({
            url: this.singlePath + '/show-tree/',
            onClose: this.setLabel.bind(this)
        });
	}

});

GalleryForm.implement(Label);