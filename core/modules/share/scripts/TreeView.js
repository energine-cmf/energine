var TreeView = new Class({
    options: {
        dblClick:$empty
    },
    Implements: Options,
    selectedNode: null,

    initialize: function(element, options) {
        this.element = $(element);
        this.options.dblClick = this.nodeToggleListener;
        this.setOptions(options);
        
        this.nodes = [];
        this.element.getElements('li').each(function(item) {
            this.nodes.push(new TreeView.Node(item, this));
        }, this);
        this.nodes[0].select();
        this.setupCssClasses();
    },

    getSelectedNode: function() {
        return this.selectedNode;
    },

    getNodeById: function(id) {
        for (var i = 0, len = this.nodes.length; i < len; i++) {
            var node = this.nodes[i];
            if (node.id == id) return node;
        }
        return false;
    },
    expandToNode: function(nodeId){
        var nodes = [];
        var lambda = function(node){
            var n;
            if(n = node.getParent()){
                nodes.push(n);
                lambda(n);
            }    
        };
        lambda(this.getNodeById(nodeId));
        nodes.reverse();
        nodes.each(function(node){
            node.expand();
        });
    },
    expandAllNodes: function() {
        for (var i = 0, len = this.nodes.length; i < len; this.nodes[i].expand(), i++);
    },

    collapseAllNodes: function() {
        for (var i = 0, len = this.nodes.length; i < len; this.nodes[i].collapse(), i++);
    },

    setupCssClasses: function() {
        this.element.getElements('li').each(function(item) {
            if (item.treeNode.childs && item.treeNode.childs.childNodes.length) {
                item.addClass('folder');
                item.getElement('a').addClass('folder');
            }
            else {
                item.removeClass('folder');
                item.getElement('a').removeClass('folder');
            }

            if (item.getNext()) {
                item.removeClass('last');
            }
            else {
                item.addClass('last');
            }
        });
        this.setupStyles();
    },

    setupStyles: function() {
        if (!Browser.Engine.trident) return;
        this.element.getElements('li.last').each(function(item) {
            if (item.hasClass('folder')) {
                if (item.hasClass('opened')) {
                    item.setStyles({ 'background': '#FFF url(images/treeview/opened_last.gif) left -3px no-repeat' });
                }
                else {
                    item.setStyles({ 'background': '#FFF url(images/treeview/closed_last.gif) left -3px no-repeat' });
                }
            }
            else {
                item.setStyles({ 'background': '#FFF url(images/treeview/h_line_last.gif) left -7px no-repeat' });
            }
        });
    },

    nodeToggleListener: function(event) {
        event = new Event(event || window.event);
        event.stop();

        var node = event.target.treeNode;
        if (event.target.get('tag') == 'a') {
            node = event.target.getParent().treeNode;
            if (!node) return;
        }

        if (event.target.get('tag') == 'li') {
            var x = event.page.x - node.element.getLeft();
            var y = event.page.y - node.element.getTop();

            // Fix fo IE.
            var delta_x = Browser.Engine.trident ? $(document.documentElement).getStyle('border-left-width') : 0;
            var delta_y = Browser.Engine.trident ? $(document.documentElement).getStyle('border-top-width') : 0;
            if (delta_x == 'medium' && delta_y == 'medium') {
                delta_x = 2; delta_y = 2;
            }
            var container = $('treeContainer');
            delta_x -= container.getScroll().x;
            delta_y -= container.getScroll().y;
            x -= delta_x; y -= delta_y;

            if ((0 > x || x > 8) || (4 > y || y > 12)) return; // a little magic here ))
        }

        node.toggle();
    },

    nodeSelectListener: function(event) {
        event = new Event(event || window.event);
        event.stop();

        var node = event.target.getParent().treeNode;
        node.select();
        node.onSelect(node);
    }
});

TreeView.Node = new Class({

    tree: null,
    element: null,
    anchor: null,
    childs: null,
    opened: false,
    selected: false,
    id: null,
    data: null,

    /**
     * nodeInfo = {
     *     id: <ID>,
     *     name: <Name>,
     *     data: <Additional data>
     * };
     */
    initialize: function(nodeInfo, tree) {
        this.tree = tree;

        if ($type(nodeInfo) == 'element') {
            this.element = $(nodeInfo);
            this.id = this.element.getProperty('id');
        }
        else {
            this.element = new Element('li').adopt(
                new Element('a').setProperty('href', '#').set('html', nodeInfo['name'])
            );
            this.id = nodeInfo['id'];
            this.data = nodeInfo['data'];
        }

        this.element.treeNode = this;
        this.anchor = this.element.getElement('a');
        if(nodeInfo.data && nodeInfo.data['class']){
            this.anchor.addClass(nodeInfo.data['class']);
        }
        this.childs = this.element.getElement('ul');
        this.opened = this.element.hasClass('opened');

        this.element.addEvent('click', this.tree.nodeToggleListener);
        this.anchor.addEvent('dblclick', this.tree.options.dblClick);
        this.anchor.addEvent('click', this.tree.nodeSelectListener);
    },

    adopt: function(node) {
        if (!(node instanceof TreeView.Node)) return false;
        if (!this.childs) this.childs = new Element('ul').addClass('hidden').injectInside(this.element);
        this.childs.adopt(node.element);
        this.tree.nodes.push(node);
        //this.tree.setupCssClasses();
        return this;
    },

    injectBefore: function(node) {
        if (!(node instanceof TreeView.Node)) return false;
        this.element.injectBefore(node.element);
        this.tree.nodes.push(node);
        //this.tree.setupCssClasses();
        return this;
    },

    removeChilds: function() {
        if (!this.childs) return;
        for (var i = this.childs.childNodes.length-1; i >= 0; this.childs.childNodes[i].treeNode.remove(), i--);
    },

    getPrevious: function() {
        var prev = this.element.getPrevious();
        return (prev ? prev.treeNode : false);
    },

    getNext: function() {
        var next = this.element.getNext();
        return (next ? next.treeNode : false);
    },

    getParent: function() {
        var parent = this.element.getParent().getParent(); // li / ul / li
        return (parent ? parent.treeNode : false);
    },

    isParentOf: function(node) {
        var items = this.element.getElements('li');
        for (var i = 0, len = items.length; i < len; i++) {
            if (items[i].treeNode == node) return true;
        }
        return false;
    },

    swap: function(node) {
        if (!(node instanceof TreeView.Node)) return false;
        if (this.isParentOf(node) || node.isParentOf(this)) return false;
        var tmpNode;
        if (tmpNode = this.getNext()) {
            if (tmpNode == node) {
                node.swap(this);
            }
            else {
                this.injectBefore(node);
                node.injectBefore(tmpNode);
            }
        }
        else {
            tmpNode = this.getParent();
            this.injectBefore(node);
            tmpNode.adopt(node);
        }
        //this.tree.setupCssClasses();
        return this;
    },

    moveUp: function() {
        return this.swap(this.getPrevious());
    },

    moveDown: function() {
        return this.swap(this.getNext());
    },

    remove: function() {
        this.removeChilds();
        this.element.dispose();
        this.tree.nodes.pop(this);
        this.tree.setupCssClasses();
        return this;
    },

    toggle: function() {
        if (this.childs && this.childs.childNodes.length) {
            this.element.toggleClass('opened');
            this.opened = this.element.hasClass('opened');
            this.element.getElement('ul').toggleClass('hidden');
            this.tree.setupStyles();
        }
        return this;
    },

    expand: function() {
        if (!this.opened) this.toggle();
        return this;
    },

    collapse: function() {
        if (this.opened) this.toggle();
        return this;
    },

    disable: function() {
        // @TODO
    },

    enable: function() {
        // @TODO
    },

    select: function() {
        if (this == this.tree.selectedNode) return;
        if (this.tree.selectedNode) this.tree.selectedNode.unselect();
        this.tree.selectedNode = this;
        this.element.addClass('selected');
        this.selected = true;
        return this;
    },

    unselect: function() {
        this.element.removeClass('selected');
        this.selected = false;
        return this;
    },

    getId: function() {
        return this.id;
    },

    setName: function(name) {
        this.element.getElement('a').set('html', name);
    },

    setData: function(data) {
        this.data = data;
    },

    getData: function() {
        return this.data;
    },
    onSelect: function (node) {

    }
});