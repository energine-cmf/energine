ScriptLoader.load('ValidForm.js');

var Register  = new Class({
    Extends: ValidForm,
    
    initialize: function(element){
		this.parent(element);
        if(this.componentElement){
            this.loginField = this.form.getElement('#u_name');
            this.loginField.store('valid', '');
            //Вешаем проверку наявности логина на поле ввода логина
            this.loginField.addEvent('blur', this.checkLogin.bind(this, false));
        }
	},
    validateForm: function(event){
        if(this.parent(event)){
            if(!login.retrieve('valid')){
                this.cancelEvent(event);
            }
        }
    },
    checkLogin: function(){
        var login = this.loginField;
        if(login && /*(login.retrieve('valid')) && */(login.retrieve('valid') != login.value))
            new Request.JSON(
                {
                    url: this.componentElement.getProperty('single_template')+'check/', 
                    method: 'post',
                    onSuccess: function(response){
                        if(!response.result){
                            this.validator.showError(login, response.message);
                            this.loginField.store('valid', '');
                        }
                        else{
                            this.validator.removeError(login);
                            this.loginField.store('valid', login.value);
                        }
                        
                    }.bind(this) 
                }
            ).send('login='+login.value);
    }
});
