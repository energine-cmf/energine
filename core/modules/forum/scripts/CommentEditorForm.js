ScriptLoader.load('Form');
var CommentEditorForm = new Class({
    Extends: Form,
    initialize: function(element) {
        this.parent(element);
        this.form.getElementById('target_name').addEvent('click', function(e){Energine.cancelEvent(e); this.openThemeEditor();}.bind(this));
    },
    openThemeEditor: function(){
        ModalBox.open({
            url: this.singlePath + 'theme/',
            onClose: function(returnValue) {
                if (returnValue) {
                    this.form.getElementById('target_id').set('value', returnValue.id);
                    this.form.getElementById('target_name').set('text', returnValue.name);
                }
            }.bind(this)
        });
    }
});