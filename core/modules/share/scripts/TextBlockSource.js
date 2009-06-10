ScriptLoader.load('Form.js');

var TextBlockSource = new Class({
	Extends: Form,
    fallback_ie: true,

    initialize: function(element) {
        this.parent(element);
        var htmlSource = window.top.ModalBox.getExtraData();
        this.textarea = this.componentElement.getElement('textarea[name="html"]');
        this.textarea.setStyles({
            'margin': '1px 0',
            'width': '570px',
            'height': '330px',
            'border': '1px solid gray',
            'background': '#FFF',
            'font': '12px monospace'
        });
        this.componentElement.getElement('.toolbar').setStyle('width', '570px');
        this.textarea.value = htmlSource;
    },

    update: function() {
        window.top.ModalBox.setReturnValue(this.textarea.value);
        window.top.ModalBox.close();
    },

    cancel: function() {
        window.top.ModalBox.close();
    }
});