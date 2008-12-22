ScriptLoader.load('Validator.js');

var UserProfile = new Class({
	initialize: function(objID){
		this.form = $(objID).getElementsByTagName('input')[0].form;
		this.validator = new Validator(this.form);
	},
	validate: function () {
		var field = $('u_password');
		var field2 = $('u_password2');

		if ((field.value != field2.value)) {

			if (!field.hasClass('invalid')) {
				field.addClass('invalid');
				new Element('div').addClass('error').appendText('^ '+field.getProperty('message2')).injectAfter(field.parentNode);
			}
			/*if (!field2.hasClass('invalid')) {
				field2.addClass('invalid');
				new Element('div').addClass('error').appendText('^ '+field2.getProperty('message2')).injectAfter(field2.parentNode);
			}*/
		}
		else if(this.validator.validate()){
			this.form.submit();
		}
	}

});