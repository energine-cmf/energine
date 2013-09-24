ScriptLoader.load('Toolbar', 'ModalBox', 'LayoutManager');

var PageToolbar = new Class({
    Extends: Toolbar,
    initialize: function(componentPath, documentId, toolbarName, controlsDesc) {
        this.parent(toolbarName);
        Asset.css('pagetoolbar.css');
        this.componentPath = componentPath;
        this.documentId = documentId;
        this.layoutManager = false;

        this.dock();
        this.bindTo(this);
        if (controlsDesc) {
            controlsDesc.each(function(controlDesc) {
                this.appendControl(controlDesc);
            }, this);
        }

        this.setupLayout();
    },
    setupLayout: function() {
        var html = $$('html')[0];
        if (!html.hasClass('e-has-topframe1')) html.addClass('e-has-topframe1');
        if (/*(Cookie.read('sidebar') == null) || */(Cookie.read('sidebar') == 1))
            $$('html')[0].addClass('e-has-sideframe');

        var currentBody = $(document.body).getChildren().filter(function(element) {
            return (!(element.hasClass('e-overlay')));
        });

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
        }).inject(sidebarFrameContent);
        var editBlocksButton = this.getControlById('editBlocks');

        if(this.getControlById('editMode').getState() && editBlocksButton){
            editBlocksButton.disable();
        }

    },

    // Actions:

    editMode: function() {
        if (this.getControlById('editMode') &&
                (this.getControlById('editMode').getState() == 0)) {
            this._reloadWindowInEditMode();
        }
        else {
            window.location = window.location;
        }
    },

    add: function() {
        ModalBox.open({ 'url': this.componentPath + 'add/' + this.documentId });
    },

    edit: function() {
        ModalBox.open({ 'url': this.componentPath + this.documentId +
                '/edit' });
    },

    toggleSidebar: function() {
        $$('html')[0].toggleClass('e-has-sideframe');
        var url = new URI(Energine.base), domainChunks = url.get('host').split('.'), domain;
        if(domainChunks.length > 2){
            domainChunks.shift();
        }
        domain = '.' + domainChunks.join('.');
        Cookie.write('sidebar', $$('html')[0].hasClass('e-has-sideframe') ? 1 : 0, {'domain': domain,
            path:url.get('directory'), duration:30});
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
    },
    showSiteEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'sites' });
    },
    editBlocks: function() {

        if (!this.getControlById('editBlocks').getState()) {
            this.layoutManager = new LayoutManager(this.componentPath);
        }
        else {
            if (this.layoutManager && LayoutManager.changed){
                if(!confirm('The page has unsaved changes. Are you sure you want to quit and lost all changes?'))   {
                    return;
                }
            }

            document.location = document.location.href;

            /*if (this.layoutManager && LayoutManager.changed) {
                new Request.JSON({
                    url:this.componentPath + 'widgets/save-content/',
                    method: 'post',
                    evalScripts: false,
                    data: 'xml=' +
                            '<?xml version="1.0" encoding="utf-8" ?>' +
                            XML.hashToHTML(XML.nodeToHash(this.layoutManager.xml)),
                    onSuccess: function(response) {
                        if (response.result) {
                            document.location = document.location.href;
                        }

                    }.bind(this)
                }).send();
            }
            else {
                document.location = document.location.href;
            }*/
        }
    },
    _reloadWindowInEditMode: function() {
        new Element('form', {'styles':{'display':'none'}}).setProperties({ 'action': '', 'method': 'post' }).grab(
                new Element('input').setProperty('name', 'editMode').setProperties({ 'type': 'hidden', 'value': '1' })
                ).inject(document.body).submit();
    }
});
