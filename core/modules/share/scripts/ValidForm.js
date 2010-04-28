ScriptLoader.load('Validator');

var ValidForm = new Class({
    initialize: function(element) {
        this.componentElement = $(element);
        if(this.componentElement && (this.form = this.componentElement.getParent('form'))){
            this.singlePath = this.componentElement.getProperty('single_template');
            this.form.addClass('form').addEvent('submit', this.validateForm.bind(this));
            this.validator = new Validator(this.form);
        }

    },
    validateForm: function(event) {
        var result = false;
        if (!this.validator.validate()) {
            this.cancelEvent(event);            
        }
        else{
        	result = true;
        }
        return result;
    },
    cancelEvent: function(event){
        event = event || window.event;
        if (event.stopPropagation) event.stopPropagation();
        else event.cancelBubble = true;

        if (event.preventDefault) event.preventDefault();
        else event.returnValue = false;
    }
});