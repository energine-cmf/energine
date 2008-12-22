ScriptLoader.load('Validator.js');

var Register  = new Class({
    initialize: function(objID){
		this.form = $(objID);
		this.validator = new Validator(this.form);
	},
	validate: function () {
		if(this.validator.validate()){
			this.form.submit();
		}
	}
});
