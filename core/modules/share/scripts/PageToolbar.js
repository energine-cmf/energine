/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[PageToolbar]{@link PageToolbar}</li>
 * </ul>
 *
 * @requires Toolbar
 * @requires ModalBox
 * @requires LayoutManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Toolbar', 'ModalBox', 'LayoutManager');

// TODO: This constructor is more general as the parents constructor, or something is wrong with parent constructor and/or parent class.
/**
 * PageToolbar
 *
 * @augments Toolbar
 *
 * @constructor
 * @param componentPath
 * @param documentId
 * @param {string} toolbarName Toolbar name.
 * @param controlsDesc
 */
var PageToolbar = new Class(/** @lends PageToolbar# */{
    Extends: Toolbar,

    // constructor
    initialize: function(componentPath, documentId, toolbarName, controlsDesc) {
        this.parent(toolbarName);

        // todo: Strange, to override parent CSS.
        Asset.css('pagetoolbar.css');

        /**
         *
         * @type {*}
         */
        this.componentPath = componentPath;

        /**
         *
         * @type {*}
         */
        this.documentId = documentId;

        /**
         *
         * @type {boolean}
         */
        this.layoutManager = false;

        this.dock();
        this.bindTo(this);
        if (controlsDesc) {
            controlsDesc.each(function(controlDesc) {
                this.appendControl(controlDesc);
            }, this);
        }

        this.setupLayout();
    },

    /**
     * Setup the layout.
     * @function
     * @public
     */
    setupLayout: function() {
        var html = $$('html')[0];
        if (!html.hasClass('e-has-topframe1')) {
            html.addClass('e-has-topframe1');
        }
        if (/*(Cookie.read('sidebar') == null) || */(Cookie.read('sidebar') == 1)) {
            $$('html')[0].addClass('e-has-sideframe');
        }

        var currentBody = $(document.body).getChildren().filter(function(element) {
                return !((element.get('tag') !== 'svg') && element.hasClass('e-overlay'));
            }),
            mainFrame = new Element('div', {'class': 'e-mainframe'}),
            topFrame = new Element('div', {'class':'e-topframe'}),
            sidebarFrame = new Element('div', {'class':'e-sideframe'}),
            sidebarFrameContent = new Element('div', {'class':'e-sideframe-content'}),
            sidebarFrameBorder = new Element('div', {'class':'e-sideframe-border'});

        $(document.body).adopt([topFrame, mainFrame, sidebarFrame]);
        mainFrame.adopt(currentBody);
        sidebarFrame.adopt([sidebarFrameContent, sidebarFrameBorder]);
        topFrame.grab(this.element);

        new Element('iframe').setProperties({
            'src': this.componentPath + 'show/'/* + this.documentId + '/'*/,
            'frameBorder': '0'
        }).inject(sidebarFrameContent);

        new Element('img').setProperties({
            'src': 'images/toolbar/pagetb_logo.png',
            'class' : 'pagetb_logo'
        }).inject(topFrame, 'top');

        var editBlocksButton = this.getControlById('editBlocks');
        if(this.getControlById('editMode').getState() && editBlocksButton) {
            editBlocksButton.disable();
        }

    },

    // Actions:
    /**
     * Edit mode action.
     * @function
     * @public
     */
    editMode: function() {
        if (this.getControlById('editMode')
            && this.getControlById('editMode').getState() == 0)
        {
            this._reloadWindowInEditMode();
        } else {
            //todo: What is it?
            window.location = window.location;
        }
    },

    /**
     * Add action.
     * @function
     * @public
     */
    add: function() {
        ModalBox.open({ 'url': this.componentPath + 'add/' + this.documentId });
    },

    /**
     * Edit action.
     * @function
     * @public
     */
    edit: function() {
        ModalBox.open({ 'url': this.componentPath + this.documentId +
            '/edit' });
    },

    /**
     * Toggle sidebar.
     * @function
     * @public
     */
    toggleSidebar: function() {
        $$('html')[0].toggleClass('e-has-sideframe');
        var url = new URI(Energine.base),
            domainChunks = url.get('host').split('.'),
            domain;

        if(domainChunks.length > 2){
            domainChunks.shift();
        }

        domain = '.' + domainChunks.join('.');
        Cookie.write('sidebar',
            $$('html')[0].hasClass('e-has-sideframe') ? 1 : 0,
            {
                domain: domain,
                path: url.get('directory'),
                duration: 30
            });
    },

    /**
     * Show template editor.
     * @function
     * @public
     */
    showTmplEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'template' });
    },

    /**
     * Show translation editor.
     * @function
     * @public
     */
    showTransEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'translation' });
    },

    /**
     * Show user editor.
     * @function
     * @public
     */
    showUserEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'user' });
    },

    /**
     * Show role editor.
     * @function
     * @public
     */
    showRoleEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'role' });
    },

    /**
     * Show language editor.
     * @function
     * @public
     */
    showLangEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'languages' });
    },

    /**
     * Show file repository.
     * @function
     * @public
     */
    showFileRepository: function() {
        ModalBox.open({ 'url': this.componentPath + 'file-library' });
    },

    /**
     * Show site editor.
     * @function
     * @public
     */
    showSiteEditor: function() {
        ModalBox.open({ 'url': this.componentPath + 'sites' });
    },

    /**
     * Edit blocks.
     * @function
     * @public
     */
    editBlocks: function() {
        if (!this.getControlById('editBlocks').getState()) {
            /**
             * Layout manager.
             * @type {LayoutManager}
             */
            this.layoutManager = new LayoutManager(this.componentPath);
        } else {
            if (this.layoutManager && LayoutManager.changed){
                if(!confirm('The page has unsaved changes. Are you sure you want to quit and lost all changes?'))   {
                    return;
                }
            }

            document.location = document.location.href;
        }
    },

    //todo: Why not to inject this to the editMode() method?
    /**
     * Reload window in the edit mode.
     * @function
     * @private
     */
    _reloadWindowInEditMode: function() {
        new Element('form', {styles: {display: 'none'}})
            .setProperties({
                action: '',
                method: 'post'
            })
            .grab(new Element('input')
                .setProperty('name', 'editMode')
                .setProperties({
                    type: 'hidden',
                    value: '1'
                }))
            .inject(document.body).submit();
    }
});
