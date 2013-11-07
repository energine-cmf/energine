ScriptLoader.load('Form');
var ComponentParamsForm = new Class({
    Extends: Form,
    initialize: function(el){
        this.parent(el);
        if(this.codeEditors.length){
            this.codeEditors.each(function(editor){
                editor.setValue(editor.getValue().replace('<![CDATA['+"\n", '').replace("\n" + ']]>', ''));
            });
        }
    },
    save: function(){
        var result = {};
        if(this.codeEditors.length){
            this.codeEditors.each(function(editor){
                editor.setValue('<![CDATA[' + "\n" + editor.getValue() + "\n" +']]>');
                editor.save();
            });
        }

        if (!this.validator.validate()) {
            return false;
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
                result[el.getProperty('name')] = value;
        });

        if(this.richEditors.length){
            this.richEditors.each(function(editor){
                result[editor.hidden.getProperty('name')] = editor.area.innerHTML;
            });
        }

        ModalBox.setReturnValue(result);
        this.close();
    }
});
