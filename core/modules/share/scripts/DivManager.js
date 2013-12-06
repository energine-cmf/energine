/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[DivManager]{@link DivManager}</li>
 * </ul>
 *
 * @requires TabPane
 * @requires Toolbar
 * @requires ModalBox
 * @requires TreeView
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

// TODO: DivManager class is very similar to the TreeView class! I think, one of them must be merged to another and remove the overloaded functionality. - wait for tests

ScriptLoader.load('TabPane', 'Toolbar', 'ModalBox', 'TreeView');

/**
 * DivManager.
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var DivManager = new Class(/** @lends DivManager# */{
    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    /**
     * Toolbar.
     * @type {Toolbar}
     */
    toolbar: null,

    // constructor
    initialize: function (element) {
        Asset.css('div.css');

        /**
         * The main holder element.
         * @type {Element}
         */
        this.element = $(element);

        /**
         * Tab panels.
         * @type {TabPane}
         */
        this.tabPane = new TabPane(this.element);

        /**
         * Language ID.
         * @type {string|number}
         */
        this.langId = this.element.getProperty('lang_id');

        new Element('ul')
            .setProperty('id', 'divTree')
            .addClass('treeview')
            .inject($('treeContainer'))
            .adopt( new Element('li')
                .setProperty('id', 'treeRoot')
                .adopt( new Element('a', {'href':'#'})
                    .set('html', Energine.translations.get('TXT_DIVISIONS'))
                )
            );

        /**
         * Tree.
         * @type {TreeView}
         */
        this.tree = new TreeView('divTree', {dblClick:this.go.bind(this)});

        /**
         * Trre's root node.
         * @type {TreeView.Node}
         */
        this.treeRoot = this.tree.getSelectedNode();
        //this.treeRoot.onSelect = this.onSelectNode.bind(this);

        /**
         * Path to the component on the page.
         * @type {string}
         */
        this.singlePath = this.element.getProperty('single_template');

        /**
         * Site ID.
         * @type {string}
         */
        this.site = this.element.getProperty('site');

        this.loadTree();

        /* вешаем пересчет размеров формы на ресайз окна */
        if (!(document.getElement('.e-singlemode-layout'))) {
            window.addEvent('resize', this.fitTreeFormSize.bind(this));
        }
    },

    /**
     * Attach toolbar.
     *
     * @function
     * @public
     * @param {Toolbar} toolbar Toolbar that will be attached.
     */
    attachToolbar:function (toolbar) {
        var toolbarContainer = this.element.getElement('.e-pane-b-toolbar');

        this.toolbar = toolbar;

        if (toolbarContainer) {
            toolbarContainer.adopt(this.toolbar.getElement());
        } else {
            this.element.adopt(this.toolbar.getElement());
        }
        this.toolbar.disableControls();

        ['add', 'select', 'close', 'edit'].each(function (btnID) {
            var btn = this.toolbar.getControlById(btnID);
            if (btn) {
                btn.enable();
            }
        }, this);

        toolbar.bindTo(this);
    },

    /**
     * Load the tree.
     * @function
     * @public
     */
    loadTree:function () {
        Energine.request(
            this.singlePath + this.site + '/get-data/',
            'languageID=' + this.langId,
            function (response) {
                this.buildTree(response.data, (response.current) ? response.current : null);

                /* растягиваем всю форму до высоты видимого окна */
                if (!(document.getElement('.e-singlemode-layout'))) {
                    this.pane = this.element;
                    this.paneContent = this.pane.getElement('.e-pane-item');
                    this.treeContainer = this.pane.getElement('.e-divtree-select');
                    this.minPaneHeight = 300;

                    this.fitTreeFormSize();

                    new Fx.Scroll(document.getElement('.e-mainframe') ? document.getElement('.e-mainframe') : window).toElement(this.pane);
                }
            }.bind(this)
        );
    },

    /**
     * Build the tree.
     *
     * @function
     * @public
     * @param {} nodes Tree nodes.
     * @param {} currentNodeID Current node in the tree.
     */
    buildTree:function (nodes, currentNodeID) {
        var treeInfo = {};
        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];
            var pid = node['smap_pid'] || 'treeRoot';
            if (!treeInfo[pid]) {
                treeInfo[pid] = [];
            }
            treeInfo[pid].push(node);
        }

        //todo: Private?
        var lambda = function (nodeId) {
            var node = this.tree.getNodeById(nodeId);

            //console.log(treeInfo[nodeId], nodeId);
            for (var i = 0; i < treeInfo[nodeId].length; i++) {
                var child = treeInfo[nodeId][i],
                    icon = (child['tmpl_icon'])
                        ? Energine.base + child['tmpl_icon']
                        : Energine.base + 'templates/icons/empty.icon.gif',
                    childId = child['smap_id'];

                if (!child['smap_pid']) {
                    this.treeRoot.setName(child['smap_name']);
                    this.treeRoot.id = child['smap_id'];
                    this.treeRoot.setData(child);
                    this.treeRoot.setIcon(icon);
                    this.treeRoot.addEvent('select', this.onSelectNode.bind(this));
                } else {
                    var newNode = new TreeView.Node({
                        id: childId,
                        name: child['smap_name'],
                        data: {
                            'class': ((childId == currentNodeID) ? ' current' : ''),
                            'icon': icon
                        }
                    }, this.tree);

                    newNode.setData(child);
                    newNode.addEvent('select', this.onSelectNode.bind(this));
                    node.adopt(newNode);
                }

                if (treeInfo[childId]) {
                    lambda(childId);
                }
            }
        }.bind(this);

        lambda(this.treeRoot.getId());

        this.tree.setupCssClasses();
        this.treeRoot.expand();
        this.tree.expandToNode(currentNodeID);
        if (this.tree.getNodeById(currentNodeID)) {
            this.tree.getNodeById(currentNodeID).select();
        }
    },

    /**
     * Fit the tree's form size.
     * @function
     * @public
     */
    fitTreeFormSize:function () {
        var windowHeight = window.getSize().y - 10,
            treeContainerHeight = this.treeContainer.getSize().y,
            paneOthersHeight = this.pane.getSize().y - this.paneContent.getSize().y + 22;

        if (windowHeight > this.minPaneHeight) {
            var tree_pane = treeContainerHeight + paneOthersHeight;
            if (tree_pane > windowHeight) {
                this.pane.setStyle('height', windowHeight);
            } else {
                this.pane.setStyle('height', tree_pane);
            }
        } else {
            this.pane.setStyle('height', this.minPaneHeight);
        }
    },

    // todo: What is the reason use 'really' argument? - try
    /**
     * Reload.
     *
     * @function
     * @public
     * @param {boolean} really Confirmation.
     */
    reload:function (really) {
        if (really) {
            this.treeRoot.removeChilds();
            this.treeRoot.id = 'treeRoot';
            this.loadTree();
        }
    },

    // Actions:
    /**
     * Add action.
     * @function
     * @public
     */
    add:function () {
        var nodeId = this.tree.getSelectedNode().getId();
        ModalBox.open({
            url:this.singlePath + 'add/' + nodeId + '/',
            onClose: function (returnValue) {
                if (returnValue) {
                    switch (returnValue.afterClose) {
                        case 'add':
                            this.add();
                            break;

                        case 'go':
                            window.top.location.href = Energine.base + returnValue.url;
                            break;

                        default :
                            this.reload(true);
                    }
                }
            }.bind(this),
            extraData: this.tree.getSelectedNode()
        });
    },

    /**
     * Edit action.
     * @function
     * @public
     */
    edit:function () {
        var nodeId = this.tree.getSelectedNode().getId();
        ModalBox.open({
            url:this.singlePath + nodeId + '/edit',
            onClose:this.refreshNode.bind(this),
            extraData:this.tree.getSelectedNode()
        });
    },

    /**
     * Delete action.
     * @function
     * @public
     */
    del:function () {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
            'Do you really want to delete record?';
        if (!confirm(MSG_CONFIRM_DELETE)) return;

        var nodeId = this.tree.getSelectedNode().getId();
        Energine.request(
            this.singlePath + nodeId + '/delete',
            '',
            function (response) {
                this.tree.getSelectedNode().remove();
                this.treeRoot.select();
            }.bind(this)
        );
    },

    // todo: Private?
    /**
     * Change order.
     *
     * @function
     * @public
     * @param {Object} response Server response.
     */
    changeOrder:function (response) {
        if (!response.result) {
            return;
        }

        this.tree.getSelectedNode()[(response.dir == '<') ? 'moveUp' : 'moveDown']();
    },

    /**
     * Move node up action.
     * @function
     * @public
     */
    up:function () {
        var nodeId = this.tree.getSelectedNode().getId();
        Energine.request(this.singlePath + nodeId + '/up', '', this.changeOrder.bind(this));
    },

    /**
     * Move node down action.
     * @function
     * @public
     */
    down:function () {
        var nodeId = this.tree.getSelectedNode().getId();
        Energine.request(this.singlePath + nodeId + '/down', '', this.changeOrder.bind(this));
    },

    /**
     * Select action.
     * @function
     * @public
     */
    select:function () {
        var nodeData = this.tree.getSelectedNode().getData();

        if ($('site_selector') && nodeData) {
            nodeData.site_name = $('site_selector').getSelected()[0].get('text');
            nodeData.site_id = $('site_selector').getSelected()[0].get('value');
        }

        ModalBox.setReturnValue(nodeData);
        ModalBox.close();
    },

    /**
     * Close action.
     * @function
     * @public
     */
    close:function () {
        ModalBox.close();
    },

    /**
     * Go action.
     * @function
     * @public
     */
    go:function () {
        var nodeData = this.tree.getSelectedNode().getData();

        if (nodeData.smap_segment || !nodeData.smap_pid) {
            window.top.document.location = ((nodeData.site) ? nodeData.site : Energine.base)
                + nodeData.smap_segment;
        }
    },
    // End actions

    /**
     * Event handler. Select node.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node that will be selected.
     */
    onSelectNode:function (node) {
        if (!this.toolbar) {
            return;
        }

        var data = node.getData(),
            buttons = [this.toolbar.getControlById('close')];

        if ((data != undefined) && data.smap_pid) {
            this.toolbar.enableControls();
        } else {
            this.toolbar.disableControls();

            buttons.append([
                this.toolbar.getControlById('add'),
                this.toolbar.getControlById('edit'),
                this.toolbar.getControlById('select')
            ]);
        }

        buttons.each(function(btn) {
            if (btn) {
                btn.enable();
            }
        })
    },

    /**
     * Refresh node.
     * @function
     * @public
     */
    refreshNode:function () {
        var nodeId = this.tree.getSelectedNode().getId();
        Energine.request(
            this.singlePath + 'get-node-data',
            'languageID=' + this.langId + '&id=' + nodeId,
            function (response) {
                if (response.data.smap_pid == null) {
                    response.data.smap_pid = '';
                }
                var smapPid = response.data.smap_pid;
                var currentNode = this.tree.getSelectedNode();
                if (smapPid != currentNode.getData().smap_pid) {
                    var parentNode = (smapPid) ? this.tree.getNodeById(smapPid) : this.treeRoot;
                    this.tree.expandToNode(parentNode);
                    currentNode.injectInside(parentNode);
                }
                currentNode.setData(response.data);
                currentNode.setName(response.data.smap_name);
            }.bind(this)
        );
    }
});