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
    checkLogin: function(event){
        var login = event.target.value;
        if(login)
            new Request.JSON(
                {
                    url: this.componentElement.getProperty('single_template')+'check/', 
                    method: 'post',
                    onSuccess: function(response){
                        console.log(response);
                    } 
                }
            ).send('login='+login);
    }
});
