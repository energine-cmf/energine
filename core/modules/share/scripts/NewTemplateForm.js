ScriptLoader.load('Form');

var NewTemplateForm = new Class({
    Extends: Form,
    save: function() {
        if (!this.validator.validate()) {
            return false;
        }
        this._getOverlay().show();
        ModalBox.setReturnValue(this.form.getElementById('content_file_title').get('value'));
        this.close();
    }
});

