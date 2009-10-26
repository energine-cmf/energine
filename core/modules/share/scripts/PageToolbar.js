ScriptLoader.load('Toolbar.js', 'ModalBox.js');

var PageToolbar = new Class({
	Extends: Toolbar,
    initialize: function(componentPath, documentId) {
        Asset.css('pagetoolbar.css');
        this.parent();
        this.componentPath = componentPath;
        this.documentId = documentId;

        this.setupLayout();
        //this.element.setProperty('id', 'pageToolbar').injectInside(document.body);
        this.element.setProperty('id', 'pageToolbar');
        
        this.bindTo(this);
    },
    setupLayout: function(){
        var html = $$('html')[0];
        if(!html.hasClass('e-has-topframe1')) html.addClass('e-has-topframe1');
        this.createLayout();
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

	toggleSidebar: function() {
        $$('html')[0].toggleClass('e-has-sideframe');
        Cookie.write('sidebar', $$('html')[0].hasClass('e-has-sideframe')?1:0, {path:new URI(Energine.base).get('directory')});
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
