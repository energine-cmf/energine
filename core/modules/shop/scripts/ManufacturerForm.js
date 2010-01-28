ScriptLoader.load('Form.js');

var ManufacturerForm = new Class({
    Extends: Form,
    initialize: function(element){
        this.parent(element);
    },
    save : function() {
        if (!this.validator.validate()) {
            return false;
        }
        this.request(this.singlePath + 'save', this.form.toQueryString(),
                function(response) {
                    var result = false;
                    if(response.result){
                        result = {
                            id: response.data,
                            name: $('producer_name').get('value')
                        }
                    }
                    ModalBox.setReturnValue(result); 
                    this.close();
                }.bind(this));
    }
});