ScriptLoader.load('Toolbar.js', 'ModalBox.js');

var PageToolbar = new Class({
	Extends: Toolbar,
    initialize: function(componentPath, documentId, toolbarName) {
        this.parent(toolbarName);
        Asset.css('pagetoolbar.css');
        this.componentPath = componentPath;
        this.documentId = documentId;

        this.setupLayout();
        this.dock();
        
        this.bindTo(this);
    },
    setupLayout: function(){
        var html = $$('html')[0];
        if(!html.hasClass('e-has-topframe1')) html.addClass('e-has-topframe1');
        if((Cookie.read('sidebar')== null) || (Cookie.read('sidebar') == 1))
        $$('html')[0].addClass('e-has-sideframe');
            
        var currentBody = $(document.body).getChildren().filter(function(element){return (element.id !== 'mb_overlay');});
        
        var mainFrame = new Element('div', {'class': 'e-mainframe'});
        var topFrame = new Element('div', {'class':'e-topframe'});
        var sidebarFrame = new Element('div', {'class':'e-sideframe'});
        var sidebarFrameContent = new Element('div', {'class':'e-sideframe-content'});
        var sidebarFrameBorder = new Element('div', {'class':'e-sideframe-border'});
        $(document.body).adopt([topFrame, mainFrame, sidebarFrame]);
        mainFrame.adopt(currentBody);
        sidebarFrame.adopt([sidebarFrameContent, sidebarFrameBorder]);
        topFrame.grab(this.element);
        
        new Element('iframe').setProperties({
                    'src': this.componentPath + 'show/'/* + this.documentId + '/'*/,
                    'frameBorder': '0'
       }).injectInside(sidebarFrameContent);
    },

    // Actions:

    editMode: function() {
        if(this.getControlById('editMode').getState() == 0){
            var form = new Element('form').setProperties({ 'action': '', 'method': 'post' }).injectInside(document.body);
            new Element('input').setProperty('name', 'editMode').setProperties({ 'type': 'hidden', 'value': '1' }).injectInside(form);
            form.submit();
        }
        else{
            window.location = window.location;
        }
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
    showUserEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'user' });
    },
    showRoleEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'role' });
    },
    showLangEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'languages' });
    },
    showFileRepository: function() {
        ModalBox.open({ 'url': this.componentPath + 'file-library' });
    }
});
