ScriptLoader.load('ModalBox.js');
var Gallery = new Class({
	initialize: function(element){
		this.singleTemplatePath = element.getProperty('single_template');
    },
	add:function(){
		ModalBox.open({
            url: this.singleTemplatePath + 'add',
            onClose: this.reload.bind(this)
        });		
	},
	reload: function(){
		document.location.href = document.location.href;
	}
});
