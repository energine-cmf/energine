/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[TreeView]{@link TreeView}</li>
 *     <li>[TreeView.Node]{@link TreeView.Node}</li>
 * </ul>
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

//todo: Why the tree is always rebuilds after the double click on the node?

/**
 * Simple tree of items.
 * From MooTools it implements: Options, Events.
 *
 * @constructor
 * @param {Element|string} element The main tree element.
 * @param {Object} [options] Tree options.
 */
var TreeView = new Class(/** @lends TreeView# */{
    Implements: [Options, Events],

    //TODO: Why not to use an event?
    /**
     * Tree options.
     * @type {Object}
     *
     * @property {Function} dblClick
     */
    options: {
        dblClick: this.nodeToggleListener//function(){}
    },

    /**
     * Selected node.
     * @type {TreeView.Node}
     */
    selectedNode: null,

    /**
     * Array of the tree's nodes.
     * @type {TreeView.Node[]}
     */
    nodes: [],

    // constructor
    initialize: function(element, options) {
        Asset.css('treeview.css');

        this.element = $(element);
        this.setOptions(options);

        this.element.getElements('li').each(function(item) {
            this.nodes.push(new TreeView.Node(item, this));
        }, this);

        this.nodes[0].select();
        this.setupCssClasses();
    },

    /**
     * Setup CSS classes.
     * @function
     * @public
     */
    setupCssClasses: function() {
        this.element.getElements('li').each(function(item) {
            if (item.retrieve('treeNode').childs && item.retrieve('treeNode').childs.childNodes.length) {
                item.addClass('folder');
            } else {
                item.removeClass('folder');
            }

            if (item.getNext()) {
                item.removeClass('last');
            } else {
                item.addClass('last');
            }
        });
        this.setupStyles();
    },

    /**
     * Setup styles.
     * @function
     * @public
     */
    setupStyles: function() {
        if (!Browser.ie) {
            return;
        }

        this.element.getElements('li.last').each(function(item) {
            if (item.hasClass('folder')) {
                if (item.hasClass('opened')) {
                    item.setStyles({ 'background': '#FFF url(images/treeview/opened_last.gif) left -3px no-repeat' });
                } else {
                    item.setStyles({ 'background': '#FFF url(images/treeview/closed_last.gif) left -3px no-repeat' });
                }
            } else {
                item.setStyles({ 'background': '#FFF url(images/treeview/h_line_last.gif) left -7px no-repeat' });
            }
        });
    },

    /**
     * Get the [selected node]{@link TreeView#selectedNode}.
     *
     * @function
     * @public
     * @returns {TreeView.Node}
     */
    getSelectedNode: function() {
        return this.selectedNode;
    },

    /**
     * Get the node by his ID.
     *
     * @function
     * @public
     * @param {string|number} id Node ID.
     * @returns {TreeView.Node}
     */
    getNodeById: function(id) {
        var node = null;
        for (var i = 0, len = this.nodes.length; i < len; i++) {
            if (this.nodes[i].id == id) {
                node = this.nodes[i];
                break;
            }
        }
        return node;
    },

    /**
     * Expand the tree to the node ID.
     *
     * @function
     * @public
     * @param {string|number|TreeView.Node} nodeId Node ID or the real node object.
     */
    expandToNode: function(nodeId){
        var nodes = [];
        var lambda = function(node){
            var n;
            if(node && (n = node.getParent())){
                nodes.push(n);
                lambda(n);
            }
        };
        lambda( (!(nodeId instanceof TreeView.Node))
            ? this.getNodeById(nodeId)
            : nodeId );
        nodes.reverse();
        nodes.each(function(node){
            node.expand();
        });
    },

    //todo: Nowhere used. Delete this?
    /**
     * Expand all nodes.
     * @deprecated
     * @function
     * @public
     */
    expandAllNodes: function() {
        for (var i = 0, len = this.nodes.length; i < len; i++) {
            this.nodes[i].expand();
        }
    },

    //todo: Nowhere used. Delete this?
    /**
     * Collapse all nodes.
     * @deprecated
     * @function
     * @public
     */
    collapseAllNodes: function() {
        for (var i = 0, len = this.nodes.length; i < len; i++) {
            this.nodes[i].collapse();
        }
    },

    /**
     * Event handler. Listener of the toggling the node.
     *
     * @function
     * @public
     * @param {Object} event Default event.
     */
    nodeToggleListener: function(event) {
        event.stop();

        var node = event.target.retrieve('treeNode');
        if (event.target.get('tag') == 'a') {
            node = event.target.getParent().retrieve('treeNode');
            if (!node) {
                return;
            }
        }

        if (event.target.get('tag') == 'li') {
            var x = event.page.x - node.element.getLeft();
            var y = event.page.y - node.element.getTop();

            // Fix fo IE.
            var delta_x = Browser.ie ? $(document.documentElement).getStyle('border-left-width') : 0;
            var delta_y = Browser.ie ? $(document.documentElement).getStyle('border-top-width') : 0;
            if (delta_x == 'medium' && delta_y == 'medium') {
                delta_x = 2; delta_y = 2;
            }

            var container = $('treeContainer');
            delta_x -= container.getScroll().x;
            delta_y -= container.getScroll().y;

            x -= delta_x;
            y -= delta_y;

            //todo: What is the magic?
            // a little magic here ))
            if ( (0 > x || x > 8) || (4 > y || y > 12) ) {
                return;
            }
        }
        this.fireEvent('toggle', node);
        node.toggle();
    },

    /**
     * Event handler. Listener of the selecting node.
     *
     * @function
     * @public
     * @param {Object} event Default event.
     */
    nodeSelectListener: function(event) {
        event.stop();
        var node = event.target.getParent().retrieve('treeNode');
        this.fireEvent('select', node);
        node.select();
    }
});

/**
 * Node of the tree.
 *
 * @constructor
 * @param {Element|Object} nodeInfo Node info. The following properties are only if the the nodeInfo is an object.
 * @param {string|number} nodeInfo.id Node ID.
 * @param {string} nodeInfo.name Node name.
 * @param {Object} nodeInfo.data Additional data.
 * @param {string} nodeInfo.data.class Node's class.
 * @param {string} nodeInfo.data.icon Node's icon.
 * @param {TreeView} tree Tree object where the node is placed.
 */
TreeView.Node = new Class(/** @lends TreeView.Node# */{
    Implements: Events,

    /**
     * Tree object where the node is placed.
     * @type {TreeView}
     */
    tree: null,

    /**
     * Node element.
     * @type {Element}
     */
    element: null,

    /**
     * Children.
     * @type {Elements}
     */
    childs: null,

    /**
     * Defines whether the node is expanded or not.
     * @type {boolean}
     */
    opened: false,

    /**
     * Defines whether the node is selected or not.
     * @type {boolean}
     */
    selected: false,

    /**
     * Node ID.
     * @type {string|number}
     */
    id: null,

    /**
     * Additional data.
     * @type {Object}
     */
    data: null,

    // constructor
    initialize: function(nodeInfo, tree) {
        this.tree = tree;
        if (typeOf(nodeInfo) == 'element') {
            this.element = $(nodeInfo);
            this.element.getElement('a').setProperty('href', '#');
            this.id = this.element.getProperty('id');
        } else {
            this.element = new Element('li').adopt(
                new Element('a')
                    .setProperties({
                        'href': '#'
                    })
                    .set('html', nodeInfo['name'])
            );
            this.id = nodeInfo['id'];
            this.data = nodeInfo['data'];
            this.setIcon(nodeInfo.data.icon);
        }
        this.element.store('treeNode', this);
        //this.element.treeNode = this;

        var anchor = this.element.getElement('a');
        if(nodeInfo.data && nodeInfo.data['class']){
            anchor.addClass(nodeInfo.data['class']);
        }
        this.childs = this.element.getElement('ul');
        this.opened = this.element.hasClass('opened');

        this.element.addEvent('click', this.tree.nodeToggleListener);
        anchor.addEvent('dblclick', this.tree.options.dblClick);
        anchor.addEvent('click', this.tree.nodeSelectListener);
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Adopt the node into this node.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node that will be adopted.
     */
    adopt: function(node) {
        if (!(node instanceof TreeView.Node)) {
            return;
        }
        if (!this.childs) {
            this.childs = new Element('ul').addClass('hidden').inject(this.element);
        }
        this.childs.adopt(node.element);
        //todo: Why we push the node in the tree's node array? -- try to solve
        this.tree.nodes.push(node);
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Place the node before the [element]{@link TreeView.Node#element}.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node.
     */
    injectBefore: function(node) {
        if (!(node instanceof TreeView.Node)) {
            return;
        }
        this.element.inject(node.element, 'before');
        //todo: Why we push the node in the tree's node array?
        this.tree.nodes.push(node);
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Place the node inside the [element]{@link TreeView.Node#element}.
     *
     * @function
     * @public
     * @param {TreeView.Node} parentNode Node.
     */
    injectInside: function(parentNode){
        if (!(parentNode instanceof TreeView.Node)) {
            return;
        }
        if (!parentNode.childs) {
            parentNode.childs = new Element('ul').addClass('hidden').inject(parentNode.element);
        }
        this.element.inject(parentNode.childs, 'top');
        parentNode.expand();
        this.tree.setupCssClasses();
    },

    /**
     * Remove all child nodes.
     * @function
     * @public
     */
    removeChilds: function() {
        if (!this.childs) {
            return;
        }
        this.childs.getChildren().each(function(child){
            child.retrieve('treeNode').remove();
        }, this);
    },

    /**
     * Get the previous node.
     *
     * @function
     * @public
     * @returns {TreeView.Node}
     */
    getPrevious: function() {
        var prev = this.element.getPrevious();
        return (prev
            ? prev.retrieve('treeNode')
            : null);
    },

    /**
     * Get the next node.
     *
     * @function
     * @public
     * @returns {TreeView.Node}
     */
    getNext: function() {
        var next = this.element.getNext();
        return (next
            ? next.retrieve('treeNode')
            : null);
    },

    /**
     * Get the node parent.
     *
     * @function
     * @public
     * @returns {TreeView.Node}
     */
    getParent: function() {
        var parent = this.element.getParent().getParent(); // li / ul / li
        return (parent
            ? parent.retrieve('treeNode')
            : null);
    },

    /**
     * Get an array of the node parents
     *
     * @function
     * @public
     * @returns {Element[]}
     */
    getParents: function(){
        var p, node = this, result = [];
        while(p = node.getParent()){
            result.push(node = p);
        }
        return result;
    },

    /**
     * Check if this node is a parent of the checked node.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node.
     * @returns {boolean}
     */
    isParentOf: function(node) {
        var items = this.element.getElements('li');
        for (var i = 0; i < items.length; i++) {
            if (items[i].retrieve('treeNode') == node) {
                return true;
            }
        }
        return false;
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Swap this node with the node.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node that will be moved.
     */
    swap: function(node) {
        if (!(node instanceof TreeView.Node)
            || this.isParentOf(node)
            || node.isParentOf(this))
        {
            return;
        }

        var tmpNode = this.getNext();
        if (tmpNode) {
            if (tmpNode == node) {
                node.swap(this);
            } else {
                this.injectBefore(node);
                node.injectBefore(tmpNode);
            }
        } else {
            tmpNode = this.getParent();
            this.injectBefore(node);
            tmpNode.adopt(node);
        }
        this.tree.setupCssClasses();
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Move this node up.
     * @function
     * @public
     */
    moveUp: function() {
        this.swap(this.getPrevious());
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Move this node down.
     * @function
     * @public
     */
    moveDown: function() {
        this.swap(this.getNext());
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Remove.
     * @function
     * @public
     */
    remove: function() {
        this.removeChilds();
        this.element.dispose();
        this.tree.nodes.pop(this);
        this.tree.setupCssClasses();
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Toggle (expand/collapse).
     * @function
     * @public
     */
    toggle: function() {
        if (this.childs && this.childs.childNodes.length) {
            this.element.toggleClass('opened');
            this.opened = this.element.hasClass('opened');
            this.childs.toggleClass('hidden');
            this.tree.setupStyles();
        }
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Expand.
     * @function
     * @public
     */
    expand: function() {
        if (!this.opened) {
            this.toggle();
        }
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Collapse.
     * @function
     * @public
     */
    collapse: function() {
        if (this.opened) {
            this.toggle();
        }
    },

    /**
     * Disable.
     * @deprecated
     * @function
     * @public
     */
    disable: function() {
        //TODO
    },

    /**
     * Enable.
     * @deprecated
     * @function
     * @public
     */
    enable: function() {
        //TODO
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Select.
     *
     * @fires TreeView.Node#select
     * @function
     * @public
     */
    select: function() {
        if (this == this.tree.selectedNode) {
            /**
             * Event by selecting the node.
             * @event TreeView.Node#select
             * @param {TreeView.Node} Selected node.
             */
            this.fireEvent('select', this);
        }
        if (this.tree.selectedNode) {
            this.tree.selectedNode.unselect();
        }
        this.tree.selectedNode = this;
        this.element.addClass('selected');
        this.selected = true;
        this.fireEvent('select', this);
    },

    //todo: Why this located here and not in the TreeVew?
    /**
     * Deselect.
     * @function
     * @public
     */
    unselect: function() {
        this.element.removeClass('selected');
        this.selected = false;
    },

    /**
     * Get node ID.
     *
     * @function
     * @public
     * @returns {string|number} [id]{@link TreeView.Node#id}
     */
    getId: function() {
        return this.id;
    },

    /**
     * Set the node name.
     *
     * @function
     * @public
     * @param {string} name Name.
     */
    setName: function(name) {
        this.element.getElement('a').set('html', name);
    },

    /**
     * Set the [additional data]{@link TreeView.Node#data}.
     *
     * @function
     * @public
     * @param {Object} data Data.
     */
    setData: function(data) {
        this.data = data;
    },

    /**
     * Set the node icon.
     *
     * @function
     * @public
     * @param {string} icon Icon URL.
     */
    setIcon: function(icon){
        this.element.getElement('a').setStyles({
         'background-image':'url(' + icon + ')', 
         'background-position': '1px 1px',
         'background-repeat':'no-repeat'
         })        
    },

    /**
     * Get the [additional data]{@link TreeView.Node#data}.
     *
     * @function
     * @public
     * @returns {Object}
     */
    getData: function() {
        return this.data;
    }
});