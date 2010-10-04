ScriptLoader.load('TabPane', 'Toolbar', 'ModalBox', 'DirView', 'PageList');
var FILE_COOKIE_NAME = 'lastPath';

var FileManager = new Class({
	Implements: Energine.request,
    currentPath: false,
    initialized: false,

	initialize: function(element){
		this.element = $(element);
		this.tabPane = new TabPane(this.element);
        this.overlay = new Overlay(this.element);
		this.viewWidget = new DirView(this.element.getElement('.e-filemanager'), {
            onSelect: this.onSelect.bind(this),
            onEdit: this.onEdit.bind(this),
            onOpen: this.open.bind(this)
        });
        this.pageList = new PageList({ onPageSelect: this.load.bind(this) });

        /*
        var path = ModalBox.getExtraData();
        if (path) {
            var index = path.lastIndexOf('/');
            if (index != -1) {
                this.load(path.substring(0, index));
                return;
            }
        }
        */

        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.pageList.getElement());
            this.tabPane.element.removeClass('e-pane-has-b-toolbar1');
            this.tabPane.element.addClass('e-pane-has-b-toolbar2');
        }
        else {
            this.tabPane.element.adopt(this.pageList.getElement());
        }
        this.load();
    },
    
    attachToolbar: function(toolbar) {
        this.toolbar = toolbar;        
        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
		if(toolbarContainer){
			toolbarContainer.adopt(this.toolbar.getElement());
		}
		else {
			this.tabPane.element.adopt(this.toolbar.getElement());
		}
        toolbar.bindTo(this);
    },

	load: function(pageNum) {
        if(!pageNum)pageNum=1;
        
        var cookiePath;
        this.overlay.show();
        if (!this.currentPath && (cookiePath = Cookie.read(FILE_COOKIE_NAME))) {
            this.currentPath = cookiePath;
        }
		var postBody = this.currentPath ? 'path='+this.currentPath+'&' : '';
        if (this.element.getProperty('file_type') == 'media') {
            postBody += 'onlymedia=true';
        }
        var url = this.element.getProperty('single_template') + 'get-data/page-' + pageNum + '/';
        this.request(
            url,
            postBody,
            function(response) {
                this.overlay.hide();
                if (!this.initialized) {
                    this.viewWidget.setMetadata(response.meta);
                    this.initialized = true;
                }
                this.pageList.build(response.pager.count, response.pager.current);
                this.viewWidget.setData(response.data || []);
                if (typeof response.currentDirectory != 'undefined') {
                    this.currentPath = response.currentDirectory;
                    this.tabPane.setTabTitle(this.currentPath);
                }
				this.viewWidget.build();				
				Cookie.write(FILE_COOKIE_NAME, this.currentPath?this.currentPath:'', {path:new URI(Energine.base).get('directory'), duration:1});
            }.bind(this)
        );        
	},
	
    onSelect: function() {
        var openBtn = this.toolbar.getControlById('open');
        var delBtn = this.toolbar.getControlById('delete');
        var renBtn = this.toolbar.getControlById('rename');
        var selectedItem = this.viewWidget.getSelectedItem();

        var action = selectedItem.obj['upl_mime_type'] == 'folder' ? 'open' : 'insert';
        if(openBtn)
            openBtn.setAction(action);

        if (selectedItem.obj['upl_name']== '...') {
            delBtn.disable();
            if(renBtn)renBtn.disable();
        }
        else {
            delBtn.enable();
            if(renBtn)renBtn.enable();
        }
    },

    onActionComplete: function(entryChanged) {
        if (entryChanged) this.load();
    },

    onEdit: function() {
        var divElem = this.viewWidget.getSelectedItem().getElement('div.name');
        this.toolbar.disableControls();
        this.request(
            this.element.getProperty('single_template')+'rename',
            'name='+divElem.innerHTML+'&file['+this.viewWidget.getSelectedItem().obj['upl_mime_type']+']='+this.viewWidget.getSelectedItem().obj['upl_path'],
            function(response) {
                this.toolbar.enableControls();
            }.bind(this)
        );
    },

	open: function() {
	    var item = this.viewWidget.getSelectedItem();
	    if (item.obj['upl_mime_type'] == 'folder') {
            this.currentPath = this.viewWidget.getSelectedItem().obj['upl_path'];
            this.load();
	    }
	    else {
	        this.insert();
	    }
    },

    addDir: function() {
        ModalBox.open({
            url: this.element.getProperty('single_template')+'add-dir',
            onClose: this.onActionComplete.bind(this),
            extraData: this.currentPath
        });
    },

    del: function() {
        this.request(
            this.element.getProperty('single_template')+'delete',
            'file['+this.viewWidget.getSelectedItem().obj['upl_mime_type']+']='+this.viewWidget.getSelectedItem().obj['upl_path'],
            this.onActionComplete.bind(this)
        );
    },

    add: function() {
        ModalBox.open({
            url: this.element.getProperty('single_template')+'add',
            onClose: this.onActionComplete.bind(this),
            extraData: this.currentPath
        });
    },

    rename: function() {
        this.viewWidget.switchMode();
    },

    close: function() {
        ModalBox.setReturnValue(false);
        ModalBox.close();
    },

    insert: function() {
        //Вроде как костыль
        if(this.toolbar.getControlById('open')){
            ModalBox.setReturnValue(this.viewWidget.getSelectedItem().obj);
            ModalBox.close();
        }
    },
    uploadZip: function(){
		ModalBox.open({
            url: this.element.getProperty('single_template')+'upload-zip',
            onClose: this.onActionComplete.bind(this),
            extraData: this.currentPath
        });
    }
});