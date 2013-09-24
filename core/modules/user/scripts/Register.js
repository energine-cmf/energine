ScriptLoader.load('ValidForm');

var Register = new Class({
    Extends:ValidForm,

    initialize:function (element) {
        this.parent(element);

        if (this.componentElement) {
            this.loginField = this.form.getElementById('u_name');
            this.registerButton = this.form.getElement('button[name=register]');
            this.loginField.addEvent('blur', function (event) {

                var event = new Event(event || window.event);
                if (event.target.value) {
                    if (this.validator.validateElement(event.target)) {
                        this.checkLogin(this.loginField.get('value'));
                    }
                    else {
                        if(this.registerButton)this.registerButton.setProperty('disabled', 'disabled');
                        this.cancelEvent(event);
                    }
                }
            }.bind(this));
            this.captchaField = this.form.getElementById('captcha');
            this.captchaImage = this.form.getElementById('captchaImage');
        }
    },
    checkLogin:function (loginValue) {
        new Request.JSON({
            url:this.componentElement.getProperty('single_template') + 'check/',
            method:'post',
            onSuccess:function (response) {
                if (!response.result) {
                    this.validator.showError(this.loginField, response.message);
                    if(this.registerButton)this.registerButton.setProperty('disabled', 'disabled');
                }
                else {
                    if(this.registerButton)this.registerButton.removeProperty('disabled');
                }
            }.bind(this)
        }).send('login=' + loginValue);

    }
});
