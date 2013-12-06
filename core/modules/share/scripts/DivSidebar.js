/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[DivSidebar]{@link DivSidebar}</li>
 * </ul>
 *
 * @requires DivManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('DivManager');

// todo: Bad constructor! It is almost equal to the parent constructor except of few lines. By construct it seams, that this class must be the parent, not the child.
/**
 * DivSidebar
 *
 * @augments DivManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var DivSidebar = new Class(/** @lends DivSidebar# */{
    Extends: DivManager,

    // constructor
    initialize: function(element) {
        Asset.css('div.css');

        this.element = $(element);

        new Element('ul')
            .setProperty('id', 'divTree')
            .addClass('treeview')
            .inject($('treeContainer'))
            .adopt( new Element('li')
                .setProperty('id', 'treeRoot')
                .adopt( new Element('a')
                    .set('html', Energine.translations.get('TXT_DIVISIONS'))
                )
            );

        this.langId = this.element.getProperty('lang_id');

        this.tree = new TreeView('divTree', {dblClick: this.go.bind(this)});

        this.treeRoot = this.tree.getSelectedNode();
        this.treeRoot.onSelect = this.onSelectNode.bind(this);

        this.singlePath = this.element.getProperty('single_template');

        this.site = this.element.getProperty('site');

        $$('html')[0].addClass('e-divtree-panel');

        this.loadTree();
    },

    /**
     * Overridden parent [attachToolbar]{@link DivManager#attachToolbar} method.
     *
     * @function
     * @public
     * @param {Toolbar} toolbar Toolbar that will be attached.
     */
    attachToolbar: function(toolbar) {
        if (this.toolbar = toolbar) {
            this.toolbar.getElement().inject(this.element, 'top');
            this.toolbar.disableControls();
            var addBtn, selectBtn;
            if (addBtn = this.toolbar.getControlById('add')) {
                addBtn.enable();
            }
            if (selectBtn = this.toolbar.getControlById('select')) {
                selectBtn.enable();
            }
            toolbar.bindTo(this);
        }
    }
});