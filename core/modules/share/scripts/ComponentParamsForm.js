ScriptLoader.load('Form');
var ComponentParamsForm = new Class({
    Extends: Form,
    initialize: function(el){
        this.parent(el);
    },
    save: function(){
        if (!this.validator.validate()) {
            return false;
        }
        var result = new Hash();
        this.form.getElements('input[type=text],input[type=checkbox], select').each(function(el){
            var value;
            if(el.getProperty('type') == 'checkbox'){
                value = (el.checked)?1:0;
            }
            else {
                value = el.get('value');
            }
            result.set(el.getProperty('name'), value);
        });
        ModalBox.setReturnValue(result);
        this.close();
    }
});
