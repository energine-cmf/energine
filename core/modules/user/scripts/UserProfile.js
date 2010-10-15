ScriptLoader.load('ValidForm');

var UserProfile = new Class({
    Extends: ValidForm,
	initialize: function(element){
		this.parent(element);
	},
    validateForm: function(event){
        var field = $('u_password');
        var field2 = $('u_password2');

        if ((field.value != field2.value)) {
            this.validator.showError(field, field.getProperty('nrgn:message2'));
            this.cancelEvent(event);
        }
        else{
            this.parent(event);
        }
    
    }
});