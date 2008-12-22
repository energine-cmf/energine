ScriptLoader.load('Validator.js');

var Order = new Class({
	initialize: function(objID){
		this.validator = new Validator(this.form = $(objID));
	},
	validate: function () {
		if(this.validator.validate()){
			this.form.submit();		
		}
	}
});