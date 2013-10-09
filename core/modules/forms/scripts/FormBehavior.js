ScriptLoader.load('ValidForm', 'datepicker');
var FormBehavior = new Class({
    Extends:ValidForm,
    initialize: function(element){
        this.parent(element);
        this.componentElement = $(element);

    },
    validateForm: function(event){
        var result;
        if((typeof Recaptcha !== 'undefined') && !Recaptcha.get_response()) {
            this.validator.showError($('recaptcha_widget_div'), 'Необходимо ввести значения');
            Energine.cancelEvent(event);
            return false;
        }
        
        return this.parent(event);
    }
});
