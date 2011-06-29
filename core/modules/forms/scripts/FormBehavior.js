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
            Energine.cancelEvent(event);
            return false;
        }
        
        return this.parent(event);
    }
});
