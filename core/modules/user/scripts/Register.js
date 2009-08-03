ScriptLoader.load('ValidForm.js');

var Register  = new Class({
    Extends: ValidForm,
    
    initialize: function(element){
		this.parent(element);

        if(this.componentElement){
            //Вешаем проверку наявности логина на поле ввода логина
            $('u_name').addEvent('blur', this.checkLogin.bind(this));
        }
	},
    validateForm: function(event){
        this.checkLogin();
        this.parent(event);
    },
    checkLogin: function(){
        var login = $('u_name');
        if(login && login.value)
            new Request.JSON(
                {
                    url: this.componentElement.getProperty('single_template')+'check/', 
                    method: 'post',
                    onSuccess: function(response){
                        if(!response.result){
                            this.validator.showError(login, response.message);
                            //this.validator.scrollToElement(login);
                        }
                    }.bind(this) 
                }
            ).send('login='+login.value);
    }
});
