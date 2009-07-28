ScriptLoader.load('Validator.js', 'Calendar.js');

var ValidForm = new Class({

    initialize: function(element) {
        this.componentElement = $(element);
        if(this.componentElement && (this.form = this.componentElement.getParent('form'))){
            this.form.addClass('form').addEvent('submit', this.validateForm.bind(this));
            this.validator = new Validator(this.form);
        }

    },
    validateForm: function(event) {
        event = event || window.event;

        var result = false;
        if (!this.validator.validate()) {
            if (event.stopPropagation) event.stopPropagation();
            else event.cancelBubble = true;

            if (event.preventDefault) event.preventDefault();
            else event.returnValue = false;
        }
        else{
        	result = true;
        }
        return result;
    }
});