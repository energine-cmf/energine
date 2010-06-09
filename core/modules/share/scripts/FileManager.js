ScriptLoader.load('TabPane', 'Toolbar', 'ModalBox', 'DirView');
var FILE_COOKIE_NAME = 'lastPath';

var FileManager = new Class({
	Implements: Energine.request,
    currentFolder: false,
    initialized: false,

	initialize: function(element){
		this.element = $(element);
		this.tabPane = new TabPane(this.element);
		this.viewWidget = new DirView(this.element.getElement('.e-filemanager'), {
            onSelect: this.onSelect.bind(this),
            onEdit: this.onEdit.bind(this),
            onOpen: this.open.bind(this)
        });

        var path = ModalBox.getExtraData();
        if (path) {
            var index = path.lastIndexOf('/');
            if (index != -1) {
                this.load(path.substring(0, index));
                return;
            }
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
    },

	load: function(path) {
        var cookiePath;
        if (!path && (cookiePath = Cookie.read(FILE_COOKIE_NAME))) {
            path = cookiePath;
        }
		var postBody = path ? 'path='+path+'&' : '';
        if (this.element.getProperty('file_type') == 'media') {
            postBody += 'onlymedia=true';
        }
        this.request(
            this.element.getProperty('single_template')+'get-data',
            postBody,
            function(response) {
                if (!this.initialized) {
                    this.viewWidget.setMetadata(response.meta);
                    this.initialized = true;
                }
                this.viewWidget.setData(response.data || []);
                if (typeof response.currentDirectory != 'undefined') {
                    this.currentFolder = response.currentDirectory;
                    this.tabPane.setTabTitle(this.currentFolder);
                }
				this.viewWidget.build();
				Cookie.write(FILE_COOKIE_NAME, path?path:'', {path:new URI(Energine.base).get('directory'), duration:1});
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
        if (entryChanged) this.load(this.currentFolder);
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
            this.load(this.viewWidget.getSelectedItem().obj['upl_path']);
	    }
	    else {
	        this.insert();
	    }
    },

    addDir: function() {
        ModalBox.open({
            url: this.element.getProperty('single_template')+'add-dir',
            onClose: this.onActionComplete.bind(this),
            extraData: this.currentFolder
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
            extraData: this.currentFolder
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
            extraData: this.currentFolder
        });
    }
});