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
            if (!field.hasClass('invalid')) {
                field.addClass('invalid');
                new Element('div').addClass('error').appendText('^ '+field.getProperty('message2')).inject(field.parentNode, 'after');
            }
            this.cancelEvent(event);
        }
        else{
            this.parent(event);
        }
    
    }
});