ScriptLoader.load('Toolbar.js', 'ModalBox.js');

var PageToolbar = new Class({
	Extends: Toolbar,
    initialize: function(componentPath, documentId) {
        Asset.css('pagetoolbar.css');
        this.parent();

        this.element.setProperty('id', 'pageToolbar').injectInside(document.body);

        this.bindTo(this);
        this.componentPath = componentPath;
        this.documentId = documentId;
    },

    // Actions:

    editMode: function() {
        var form = new Element('form').setProperties({ 'action': '', 'method': 'post' }).injectInside(document.body);
        new Element('input').setProperty('name', 'editMode').setProperties({ 'type': 'hidden', 'value': '1' }).injectInside(form);
        form.submit();
    },

    add: function() {
        ModalBox.open({ 'url': this.componentPath + 'add/' + this.documentId });
    },

	edit: function() {
	    ModalBox.open({ 'url': this.componentPath + this.documentId + '/edit' });
	},

	showDivEditor: function() {
	    ModalBox.open({ 'url': this.componentPath + 'show' });
	},

    showTmplEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'template' });
    },
    showTransEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'translation' });
    },
    showFileRepository: function() {
        ModalBox.open({ 'url': this.componentPath + 'file-library' });
    }
});
