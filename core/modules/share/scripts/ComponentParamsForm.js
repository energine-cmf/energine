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
        if(this.codeEditors.length){
            this.codeEditors.each(function(editor){
                editor.save();
            });
        }

        this.form.getElements('input[type=text],input[type=checkbox], select, textarea').each(function(el){
            var value;
            if(el.getProperty('type') == 'checkbox'){
                value = (el.checked)?1:0;
            }
            else {
                value = el.get('value');
            }

            if(el.getProperty('name'))
                result.set(el.getProperty('name'), value);
        });

        if(this.richEditors.length){
            this.richEditors.each(function(editor){
                result.set(editor.hidden.getProperty('name'), editor.area.innerHTML);
            });
        }

        ModalBox.setReturnValue(result);
        this.close();
    }
});
