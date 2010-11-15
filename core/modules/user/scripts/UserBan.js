ScriptLoader.load('Form');

var UserBan = new Class({
    Extends: Form,
    Implements: Energine.request,
    /*initialize: function(element){
        this.parent(element);
        this.form.getElementById('save').addEvent('click', this.save.bind(this));
    },*/
    save: function(){
        if (!this.validator.validate()) {
            return false;
        }
        this.request(this.singlePath +
                'saveban', this.form.toQueryString(), this.processServerResponse.bind(this));
    }
});