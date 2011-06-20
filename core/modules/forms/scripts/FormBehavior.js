ScriptLoader.load('Form');
var FormBehavior = new Class({
    Extends:Form,
    initialize: function(element){
        this.parent(element);
        this.componentElement = $(element);


    },
    saveField: function(){
        this.request(this.singlePath +
                'saveField', this.form.toQueryString(), this.processServerResponse.bind(this));

    }


});
