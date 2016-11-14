/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[getDirsTree]{@link getDirsTree}</li>
 * </ul>
 *
 * @requires DivManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('DivManager');

/**
 * DivTree.
 *
 * @augments DivManager
 *
 * @constructor
 * @param {Element|string} el The main holder element.
 */
var getDirsTree = new Class(/** @lends DivTree# */{
    Extends: DivManager,

    /**
     * Current ID.
     * @type {number}
     */
    currentID: 0,

    // constructor
    
    /**
     * Load the tree.
     * @function
     * @public
     */
    loadTree: function () {
        Energine.request(
            this.singlePath + '/getDirs/',
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
    buildTree: function (nodes, currentNodeID) {

        var treeInfo = {};
        var move_from_id=$('treeContainer').getAttribute('move_id');  
        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];            
            if (node['upl_id']==move_from_id) continue;
            var pid = node['upl_pid'] || 'treeRoot';                        
            if (pid==move_from_id) continue;
            if (!treeInfo[pid]) {
                treeInfo[pid] = [];
            }
            treeInfo[pid].push(node);
        }

        var lambda = function (nodeId, parentElement) {
            //console.log(treeInfo[nodeId], nodeId);

            for (var i = 0; i < treeInfo[nodeId].length; i++) {
                var child = treeInfo[nodeId][i],
                    icon = (child['tmpl_icon'])
                        ? Energine.base + child['tmpl_icon']
                        : Energine.base + 'templates/icons/divisions_list.icon.gif',
                        //: Energine.base + 'templates/icons/empty.icon.gif',
                    childId = child['upl_id'];

                var newNode = new TreeView.Node({
                    id: childId,
                    name: child['upl_title'],
                    data: {
                        'segment':child['upl_segment'],
                        'class': ((childId == currentNodeID) ? ' current' : ''),
                        'icon': icon
                    }
                }, this.tree);

                newNode.setData(child);
                newNode.addEvent('select', this.onSelectNode.bind(this));
                parentElement.adopt(newNode);


                if (treeInfo[childId]) {
                    lambda(childId, newNode);
                }
            }
        }.bind(this);
        lambda('treeRoot', this.tree);


        this.tree.setupCssClasses();
        this.tree.expandToNode(currentNodeID);

        if (this.tree.getNodeById(currentNodeID)) {
            this.tree.getNodeById(currentNodeID).select();
            this.tree.getNodeById(currentNodeID).expand();
        }
        else {
            this.tree.expandAllNodes();
        }
    },
    /**
     * Extend parent [onSelectNode]{@link DivManager#onSelectNode} method.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node that will be selected.
     */
    onSelectNode: function (node) {
        var btnSelect = this.toolbar.getControlById('saveDirsMove');
         if (btnSelect) {
            btnSelect.enable();
         }        
    },
    go: function () { //disable dblclick
    },
  
    saveDirsMove: function () {              
       var move_to_id=this.tree.getSelectedNode().getId();
       var move_from_id=$('treeContainer').getAttribute('move_id');       
       Energine.request(            
            this.singlePath + move_from_id+','+move_to_id+'/getDirsMove/',            
                'languageID=' + this.langId,
                function (response) {
                        this.close();                
            }.bind(this)
        );        
    }
});
