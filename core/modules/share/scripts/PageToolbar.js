ScriptLoader.load('Toolbar.js', 'ModalBox.js');

var PageToolbar = new Class({
	Extends: Toolbar,
    initialize: function(componentPath, documentId) {
        Asset.css('pagetoolbar.css');
        this.parent();
        this.componentPath = componentPath;
        this.documentId = documentId;

        this.createAdminMap();
        //this.element.setProperty('id', 'pageToolbar').injectInside(document.body);
        this.element.setProperty('id', 'pageToolbar');
        
        this.bindTo(this);
    },
    createAdminMap: function(){
        $$('html').addClass('e-has-topframe1');
        $$('html').addClass('e-has-sideframe');
        
        var currentBody = document.body.getChildren();
        
        var mainFrame = new Element('div', {'class': 'e-mainframe'});
        var topFrame = new Element('div', {'class':'e-topframe'});
        var sidebarFrame = new Element('div', {'class':'e-sideframe'})
        document.body.adopt([topFrame, mainFrame, sidebarFrame]);
        mainFrame.adopt(currentBody);
        
        topFrame.grab(this.element);
        
        new Element('iframe').setProperties(
                {
                    'src': this.componentPath + 'show',
                    'frameBorder': '0',
                    'scrolling': 'no'
                }
        ).injectInside(sidebarFrame);
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
