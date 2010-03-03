ScriptLoader.load('ValidForm.js');

var ResumeForm = new Class({
    Extends: ValidForm,
    initialize: function(element) {
        if($('captcha')){
            this.componentElement = $(element);
            if(this.componentElement && (this.form = this.componentElement.getParent('form'))){
                this.form.addClass('form').addEvent('submit', this.checkCaptcha.bind(this));
                this.validator = new Validator(this.form);
            }
        }
        else{
            this.parent(element);
        }
    },
     checkCaptcha: function(event){
        var event = event || window.event;
        var uri = this.componentElement.getProperty('single_template') + 'check-captcha/';
        this.cancelEvent(event);
        new Request.JSON({
            'url' : uri,
            'method' : 'post',
            'data' : 'captcha='+$('captcha').value,
            'evalResponse' : false,
            'onComplete' : function(data){
                if(data.result){
                    this.validator.removeError($('captcha'));
                    if(this.validateForm(event)){
                        this.form.removeEvents();
                        this.form.submit();
                    }
                }
                else{
                    $('captchaImage').setProperty('src', $('captchaImage').getProperty('src'));
                    this.validator.showError($('captcha'), $('captcha').getProperty('message'));
                    this.cancelEvent(event);
                }
            }.bind(this)
        }).send();
        
     }
});