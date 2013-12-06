/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[NewTemplateForm]{@link NewTemplateForm}</li>
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
 * @class NewTemplateForm
 *
 * @augments Form
 */
var NewTemplateForm = new Class(/** @lends NewTemplateForm# */{
    Extends: Form,

    /**
     * Overridden parent save method.
     * @function
     * @public
     */
    save: function() {
        if (!this.validator.validate()) {
            return;
        }
        this.overlay.show();

        ModalBox.setReturnValue(this.form.getElementById('content_file_title').get('value'));
        this.close();
    }
});

