ScriptLoader.load('Form');

var TextBlockSource = new Class({
	Extends: Form,

    initialize: function(element) {
        this.parent(element);
        this.textarea = this.componentElement.getElement('textarea[name="html"]');
        this.textarea.value = window.top.ModalBox.getExtraData();

        this.codeEditor = CodeMirror.fromTextArea(this.textarea, {mode: "text/html", tabMode: "indent", lineNumbers: true});
    },

    update: function() {
        window.top.ModalBox.setReturnValue(this.codeEditor.getValue());
        window.top.ModalBox.close();
    },

    cancel: function() {
        window.top.ModalBox.close();
    }
});