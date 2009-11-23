ScriptLoader.load('ValidForm.js');

var Register  = new Class({
    Extends: ValidForm,
    
    initialize: function(element){
		this.parent(element);
        if(this.componentElement){
            this.loginField = this.form.getElement('#u_name');
            this.captchaField = this.form.getElement('#captcha');
            this.captchaImage = this.form.getElement('#captchaImage');
        }
	},
    validateForm: function(event){
        var result = false;
        
        if (!this.validator.validate()) {
            this.cancelEvent(event);            
        }
        else{
            this.check();
            this.cancelEvent(event);            
            result = true;
        }
        return result;
    },
    check: function(){
            new Request.JSON(
                {
                    url: this.componentElement.getProperty('single_template')+'check/', 
                    method: 'post',
                    async: false,
                    onSuccess: function(response){
                        if(!response.result){
                            this.validator.removeError(this.captchaField);
                            this.validator.removeError(this.loginField);
                            var cp = this.captchaImage.getProperty('src');
                            this.captchaImage.removeProperty('src');
                            this.captchaImage.setProperty('src', cp);                            
                            this.captchaField.value = '';
                            this.validator.showError(((response.field == 'login')?this.loginField:this.captchaField), response.message);
                        }
                        else{
                            this.form.removeEvents();
                            this.form.submit();
                        }
                        
                    }.bind(this) 
                }
            ).send('login='+this.loginField.value+'&captcha='+this.captchaField.value);
    }
});
