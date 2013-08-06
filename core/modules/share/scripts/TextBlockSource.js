ScriptLoader.load('Form');
var TextBlockSource = new Class({
	Extends: Form,
    initialize: function(element) {
        this.parent(element);
        this.componentElement.getElement('div.field').swapClass('min', 'max');

        this.codeEditors[0].setValue(window.top.ModalBox.getExtraData());
    },

    update: function() {
        window.top.ModalBox.setReturnValue(this.codeEditors[0].getValue());
        window.top.ModalBox.close();
    },

    cancel: function() {
        window.top.ModalBox.close();
    }
});