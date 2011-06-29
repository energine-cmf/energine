ScriptLoader.load('ValidForm');
var FormBehavior = new Class({
    Extends:ValidForm,
    initialize: function(element){
        this.parent(element);
        this.componentElement = $(element);

    },
    validateForm: function(event){
        var result;
        if(Recaptcha && !Recaptcha.get_response()) {
            this.validator.showError($('recaptcha_widget_div'), 'Необходимо ввести значения');
            Energine.cancelEvent(event);
            return false;
        }
        
        return this.parent(event);
    }
});
