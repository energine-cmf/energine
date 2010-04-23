ScriptLoader.load('ValidForm');

var OrderForm = new Class({
    Extends: ValidForm,
	initialize: function(element){
        this.parent(element);
        
        if(this.componentElement){
            this.loginField = this.form.getElementById('u_name');
            this.orderButton = this.form.getElement('button[name=order]');
            
            if(this.loginField)
                this.loginField.addEvent('blur', function(event){
                    if(this.validator.validateElement(event.target)){
                        this.checkLogin(this.loginField.get('value'));            
                    }
                    else {
                        this.orderButton.setProperty('disabled', 'disabled');
                        this.cancelEvent(event);
                    }
                }.bind(this));
        }
	},
    checkLogin: function(loginValue){
        new Request.JSON({
            url:this.componentElement.getProperty('single_template')+'check/',
            method: 'post',
            onSuccess: function(response){
                if(!response.result){
                    this.validator.showError(this.loginField, response.message);       
                    this.orderButton.setProperty('disabled', 'disabled');
                }
                else{
                    this.orderButton.removeProperty('disabled');
                }
            }.bind(this)
        }).send('login=' + loginValue);
    
    }
});