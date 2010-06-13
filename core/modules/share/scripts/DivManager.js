ScriptLoader.load('TabPane', 'Toolbar', 'ModalBox', 'TreeView');

var DivManager = new Class({
	Implements: Energine.request,

    initialize: function(element) {
        Asset.css('treeview.css');
        Asset.css('div.css');

        this.element = $(element);
        this.tabPane = new TabPane(this.element);

        this.langId = this.element.getProperty('lang_id');
        new Element('ul').setProperty('id', 'divTree').addClass('treeview').injectInside($('treeContainer')).adopt(
		    new Element('li').setProperty('id', 'treeRoot').adopt(
                new Element('a', {'href': '#'}).set('html', Energine.translations.get('TXT_DIVISIONS'))
            )
		);
		this.tree = new TreeView('divTree', {dblClick: this.go.bind(this)});
		this.treeRoot = this.tree.getSelectedNode();
        //this.treeRoot.onSelect = this.onSelectNode.bind(this);
        this.singlePath = this.element.getProperty('single_template');
        this.site = this.element.getProperty('site');    
        this.loadTree();
    },

    attachToolbar: function(toolbar) {
        this.toolbar = toolbar;
        var toolbarContainer = this.element.getElement('.e-pane-b-toolbar');
        if(toolbarContainer){
			toolbarContainer.adopt(this.toolbar.getElement());
		}
		else {
			this.element.adopt(this.toolbar.getElement());
		}        
        this.toolbar.disableControls();
        var btn;
        ['add', 'select', 'close', 'edit'].each(function(btnID){
            var btn;
            if (btn = this.toolbar.getControlById(btnID)) {
                btn.enable();
            }
        }, this);
        toolbar.bindTo(this);
    },

    loadTree: function() {
        this.request(
            this.singlePath + this.site + '/get-data/',
            'languageID='+this.langId,
            function(response) {
                this.buildTree(response.data, (response.current)?response.current:null);
            }.bind(this)
        );
    },
    buildTree: function(nodes, currentNodeID) {
        var treeInfo = {};
        for (var i = 0, len = nodes.length; i < len; i++) {
            var node = nodes[i];
            var pid = node['smap_pid'] || 'treeRoot';
            if (!treeInfo[pid]) treeInfo[pid] = [];
            treeInfo[pid].push(node);
        }
        
        var lambda = function(nodeId) {

            var node = this.tree.getNodeById(nodeId);
            
            //console.log(treeInfo[nodeId], nodeId);
            for (var i = 0, len = treeInfo[nodeId].length; i < len; i++) {
                var child = treeInfo[nodeId][i];
                var icon = (child['tmpl_icon'])?Energine.base + child['tmpl_icon']:Energine.base + 'templates/icons/empty.icon.gif';
                var childId = child['smap_id'];
                if(!child['smap_pid']){
                    this.treeRoot.setName(child['smap_name']);
                    this.treeRoot.id = child['smap_id'];
                    this.treeRoot.setData(child);
                    this.treeRoot.setIcon(icon);
                    this.treeRoot.addEvent('select', this.onSelectNode.bind(this));
                }
                else{
                    var newNode = new TreeView.Node({ 
                        id: childId, 
                        name: child['smap_name'], 
                        data:{
                            'class':
                                ((childId == currentNodeID)?' current':''),
                            'icon': icon
                        } 
                    }, this.tree);
                    newNode.setData(child);
                    newNode.addEvent('select', this.onSelectNode.bind(this));
                    node.adopt(newNode);
                }
                if (treeInfo[childId]) lambda(childId);                    
            }
            
        }.bind(this);
        lambda(this.treeRoot.getId());

        this.tree.setupCssClasses();
        this.treeRoot.expand();
        this.tree.expandToNode(currentNodeID);
        if(this.tree.getNodeById(currentNodeID))
            this.tree.getNodeById(currentNodeID).select();
    },

    reload: function(really) {
        if (really) {
            this.treeRoot.removeChilds();
            this.treeRoot.id = 'treeRoot';
            this.loadTree();
        }
    },

	add: function() {
		var nodeId = this.tree.getSelectedNode().getId();
        ModalBox.open({
            url: this.singlePath+'add/'+nodeId+'/',
            onClose: function(returnValue){
                if(returnValue == 'add'){
                    this.add();   
                }
                else if(returnValue){
                    this.reload(true);
                }
            }.bind(this),
            extraData: this.tree.getSelectedNode()
        });
	},

	edit: function() {
	    var nodeId = this.tree.getSelectedNode().getId();
        ModalBox.open({
            url: this.singlePath+nodeId+'/edit',
            onClose: this.refreshNode.bind(this),
            extraData: this.tree.getSelectedNode()
        });
	},

	del: function() {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') || 'Do you really want to delete record?';
        if (!confirm(MSG_CONFIRM_DELETE)) return;

        var nodeId = this.tree.getSelectedNode().getId();
		this.request(
            this.singlePath+nodeId+'/delete',
            '',
			function(response) {
			    this.tree.getSelectedNode().remove();
				this.treeRoot.select();
			}.bind(this)
		);
	},

	changeOrder: function(response) {
	    if (!response.result) return;
		if (response.dir == '<') {
		    this.tree.getSelectedNode().moveUp();
		}
		else {
		    this.tree.getSelectedNode().moveDown();
		}
	},

	up: function() {
	    var nodeId = this.tree.getSelectedNode().getId();
		this.request(this.singlePath+nodeId+'/up', '', this.changeOrder.bind(this));
	},

	down: function() {
	    var nodeId = this.tree.getSelectedNode().getId();
		this.request(this.singlePath+nodeId+'/down', '', this.changeOrder.bind(this));
	},

	select: function() {
	    var nodeData = this.tree.getSelectedNode().getData();
        ModalBox.setReturnValue(nodeData);
        ModalBox.close();
	},

	close: function() {
        ModalBox.close();
	},

    go: function () {
        var nodeData = this.tree.getSelectedNode().getData();

        if (nodeData.smap_segment || !nodeData.smap_pid) {
            window.top.document.location = ((nodeData.site)?nodeData.site:Energine.base) + nodeData.smap_segment;
        }
    },
    onSelectNode: function (node) {
        var data = node.getData(), btn;
        var addBtn = this.toolbar.getControlById('add');
        var editBtn = this.toolbar.getControlById('edit');
        var selectBtn = this.toolbar.getControlById('select');

        if ((undefined != data) && data.smap_pid) {
            this.toolbar.enableControls();
        }
        else {
                this.toolbar.disableControls();
                if(addBtn) addBtn.enable();
                if(selectBtn)selectBtn.enable();
                if(editBtn)editBtn.enable();
        }
        
        if (btn = this.toolbar.getControlById('close')) {
            btn.enable();
        }
        
    },
	refreshNode: function() {
	    var nodeId = this.tree.getSelectedNode().getId();
		this.request(
            this.singlePath+'get-node-data',
            'languageID='+this.langId+'&id='+nodeId,
            function(response) {
                if(response.data.smap_pid == null){
                    response.data.smap_pid = '';
                }
                var smapPid = response.data.smap_pid;
                var currentNode = this.tree.getSelectedNode();
                if(smapPid != currentNode.getData().smap_pid) {
                    var parentNode = (smapPid)?this.tree.getNodeById(smapPid):this.treeRoot;
                    this.tree.expandToNode(parentNode);
                    currentNode.injectInside(parentNode);
                }
                currentNode.setData(response.data);
    			currentNode.setName(response.data.smap_name);
            }.bind(this)
        );
	}
});
