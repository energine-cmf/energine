ScriptLoader.load('DivManager.js');
var DivSidebar = new Class({
    Extends: DivManager,

    initialize: function(element) {
        Asset.css('treeview.css');
        Asset.css('div.css');

        this.element = $(element);
        new Element('ul').setProperty('id', 'divTree').addClass('treeview').injectInside($('treeContainer')).adopt(
            new Element('li').setProperty('id', 'treeRoot').addClass('folder').adopt(
                new Element('a').setProperty('href', '#').addClass('folder').setStyle('font-weight', 'bold').set('html', TXT_DIVISIONS)
            )
        );
        this.langId = this.element.getProperty('lang_id');
        this.tree = new TreeView('divTree', {dblClick: this.go.bind(this)});
        this.treeRoot = this.tree.getSelectedNode();
        this.treeRoot.onSelect = this.onSelectNode.bind(this);
        this.singlePath = this.element.getProperty('single_template');
        $$('html')[0].addClass('e-divtree-panel');
        this.loadTree();  
    },
    attachToolbar: function(toolbar) {
        this.toolbar = toolbar;
        this.toolbar.getElement().inject(this.element, 'top');
        this.toolbar.disableControls();
        var addBtn, selectBtn;
        if (addBtn = this.toolbar.getControlById('add')) {
            addBtn.enable();
        }
        if (selectBtn = this.toolbar.getControlById('select')) {
            selectBtn.enable();
        }
    }
});