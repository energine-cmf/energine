/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[ComponentParamsForm]{@link ComponentParamsForm}</li>
 * </ul>
 *
 * @requires Form
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form');

/**
 * Component parameters form.
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var ComponentParamsForm = new Class({
    Extends: Form,

    // constructor
    initialize: function(el){
        this.parent(el);
        if(this.codeEditors.length){
            this.codeEditors.each(function(editor){
                editor.setValue(editor.getValue().replace('<![CDATA['+"\n", '').replace("\n" + ']]>', ''));
            });
        }
    },

    /**
     * Overridden parent [save]{@link Form#save} method.
     * @function
     * @public
     */
    save: function(){
        var result = {};

        if (!this.validator.validate()) {
            return false;
        }

        if(this.codeEditors.length){
            this.codeEditors.each(function(editor){
                editor.setValue('<![CDATA[' + "\n" + editor.getValue() + "\n" +']]>');
                editor.save();
            });
        }
        this.form.getElements('input[type=text],input[type=checkbox], select, textarea').each(function(el){
            var value;
            if(el.getProperty('type') == 'checkbox'){
                value = (el.checked)?1:0;
            } else {
                value = el.get('value');
            }

            if(el.getProperty('name')) {
                result[el.getProperty('name')] = value;
            }
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
