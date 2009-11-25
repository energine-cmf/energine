ScriptLoader.load('TabPane.js', 'Toolbar.js', 'ModalBox.js', 'TreeView.js');

var DivManager = new Class({
	Implements: ERequest,

    initialize: function(element) {
        Asset.css('treeview.css');
        Asset.css('div.css');

        this.element = $(element);
        this.tabPane = new TabPane(this.element);
        this.langId = this.tabPane.getCurrentTab().data.lang;
        new Element('ul').setProperty('id', 'divTree').addClass('treeview').injectInside($('treeContainer')).adopt(
		    new Element('li').setProperty('id', 'treeRoot').addClass('folder').adopt(
		        new Element('a').setProperty('href', '#').setStyle('font-weight', 'bold').set('html', TXT_DIVISIONS)
		    )
		);
		this.tree = new TreeView('divTree');
		this.treeRoot = this.tree.getSelectedNode();
        this.treeRoot.onSelect = this.onSelectNode.bind(this);
        this.singlePath = this.element.getProperty('single_template');

        this.loadTree();
    },

    attachToolbar: function(toolbar) {
        this.toolbar = toolbar;
        this.element.adopt(this.toolbar.getElement());
        this.toolbar.disableControls();
        var btn;
        ['add', 'select', 'close'].each(function(btnID){
            var btn;
            if (btn = this.toolbar.getControlById(btnID)) {
                btn.enable();
            }
        }, this);
    },

    loadTree: function() {
        this.request(
            this.singlePath+'get-data',
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
            for (var i = 0, len = treeInfo[nodeId].length; i < len; i++) {
                var child = treeInfo[nodeId][i];
                var icon = (child['tmpl_icon'])?Energine.base + child['tmpl_icon']:Energine.base + 'templates/icons/empty.icon.gif';
                if(child['smap_default']){
                    this.treeRoot.setName(child['smap_name']);
                    this.treeRoot.id = child['smap_id'];
                    this.treeRoot.setData(child);
                    this.treeRoot.setIcon(icon);
                    this.treeRoot.addEvent('select', this.onSelectNode.bind(this));
                }
                else{
                    var childId = child['smap_id'];
                    var newNode = new TreeView.Node({ 
                        id: childId, 
                        name: child['smap_name'], 
                        data:{
                            'class':
                                ((child['smap_is_final'])?'final':'') +
                                ((childId == currentNodeID)?' current':''),
                            'icon': icon
                        } 
                    }, this.tree);
                    newNode.setData(child);
                    newNode.addEvent('select', this.onSelectNode.bind(this));
                    node.adopt(newNode);
                    if (treeInfo[childId]) lambda(childId);                    
                }
            }
        }.bind(this);
        lambda(this.treeRoot.getId());

        this.tree.setupCssClasses();
        this.treeRoot.expand();
        this.tree.expandToNode(currentNodeID)
        this.tree.getNodeById(currentNodeID).select();
    },

    reload: function(really) {
        if (really) {
            this.treeRoot.removeChilds();
            this.loadTree();
        }
    },

	add: function() {
		var nodeId = this.tree.getSelectedNode() != this.treeRoot ? this.tree.getSelectedNode().getId() : '';
        ModalBox.open({
            url: this.singlePath+'add/'+nodeId,
            onClose: this.reload.bind(this),
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
        var MSG_CONFIRM_DELETE = window.MSG_CONFIRM_DELETE || 'Do you really want to delete record?';
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
        if (nodeData.smap_segment) {
            window.top.document.location = Energine.base + nodeData.smap_segment;
        }
    },
    onSelectNode: function (node) {
        var data = node.getData();
        var delBtn = this.toolbar.getControlById('delete');
        var addBtn = this.toolbar.getControlById('add');
        var selectBtn = this.toolbar.getControlById('select');
        var downBtn = this.toolbar.getControlById('down');
        var upBtn = this.toolbar.getControlById('up');
        
        if (undefined != data) {
            this.toolbar.enableControls();
            if (data.smap_is_system || data.smap_default) {
                if (delBtn) delBtn.disable();
                if(data.smap_default && upBtn && downBtn){
                    upBtn.disable();
                    downBtn.disable();
                }
            }
            if (data.smap_is_final) {
                if (addBtn) addBtn.disable();
                if(selectBtn)selectBtn.disable();
            }
        }
        else {
            this.toolbar.disableControls();
            if(addBtn) addBtn.enable();
            if(selectBtn)selectBtn.enable();
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
                var currentNode = this.tree.getSelectedNode();
                var parentNode = (response.data.smap_pid)?this.tree.getNodeById(response.data.smap_pid):this.treeRoot;
                this.tree.expandToNode(parentNode);
                currentNode.injectInside(parentNode);
    			currentNode.setName(response.data.smap_name);
            }.bind(this)
        );
	}
});
