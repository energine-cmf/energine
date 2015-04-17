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

/**
 * PageToolbar
 *
 * @augments Toolbar
 *
 * @constructor
 * @param {string} componentPath Component path.
 * @param {number} documentId Document ID.
 * @param {string} toolbarName Toolbar name.
 * @param controlsDesc
 */
var PageToolbar = new Class(/** @lends PageToolbar# */{
    Extends: Toolbar,

    // constructor
    initialize: function (componentPath, documentId, toolbarName, controlsDesc, props) {
        this.parent(toolbarName, props);

        Asset.css('pagetoolbar.css');

        /**
         * Component path.
         * @type {string}
         */
        this.componentPath = componentPath;

        /**
         * Document ID.
         * @type {number}
         */
        this.documentId = documentId;

        /**
         * Layout manager.
         * @type {LayoutManager}
         */
        this.layoutManager = null;

        this.dock();
        this.bindTo(this);
        if (controlsDesc) {
            controlsDesc.each(this.appendControl.bind(this));
        }

        this.setupLayout();
    },

    /**
     * Setup the layout.
     * @function
     * @public
     */
    setupLayout: function () {
        var html = $$('html')[0];
        if (!html.hasClass('e-has-topframe1')) {
            html.addClass('e-has-topframe1');
        }
        var currentBody = $(document.body).getChildren().filter(function (element) {
                return !((element.get('tag') !== 'svg') && element.hasClass('e-overlay'));
            }),
            mainFrame = new Element('div', {'class': 'e-mainframe'}),
            topFrame = new Element('div', {'class': 'e-topframe'});

        $(document.body).adopt([topFrame, mainFrame]);
        mainFrame.adopt(currentBody);

        topFrame.grab(this.element);
        var gear = new Element('img', {
            'src': Energine.static + ((Energine.debug) ? 'images/toolbar/nrgnptbdbg.png' : 'images/toolbar/nrgnptb.png'),
            'class': 'pagetb_logo'
        }).inject(topFrame, 'top');

        if (!this.properties['noSideFrame']) {
            if ((Cookie.read('sidebar') == 1)) {
                $$('html')[0].addClass('e-has-sideframe');
            }
            var sidebarFrame = new Element('div', {'class': 'e-sideframe'}), sidebarFrameContent = new Element('div', {'class': 'e-sideframe-content'}), sidebarFrameBorder = new Element('div', {'class': 'e-sideframe-border'});
            $(document.body).grab(sidebarFrame);
            sidebarFrame.adopt([sidebarFrameContent, sidebarFrameBorder]);
            new Element('iframe').setProperties({
                'src': this.componentPath + 'show/'/* + this.documentId + '/'*/,
                'frameBorder': '0'
            }).inject(sidebarFrameContent);
            gear.addEvent('click', this.toggleSidebar);
        }


        var editBlocksButton = this.getControlById('editBlocks');
        if (this.getControlById('editMode') && this.getControlById('editMode').getState() && editBlocksButton) {
            editBlocksButton.disable();
        }

    },

    // Actions:
    /**
     * Edit mode action.
     * @function
     * @public
     */
    editMode: function () {
        if (this.getControlById('editMode')
            && this.getControlById('editMode').getState() == 0) {
            this._reloadWindowInEditMode();
        } else {
            window.location = window.location;
        }
    },

    /**
     * Add action.
     * @function
     * @public
     */
    add: function () {
        ModalBox.open({'url': this.componentPath + 'add/' + this.documentId});
    },

    /**
     * Edit action.
     * @function
     * @public
     */
    edit: function () {
        ModalBox.open({
            'url': this.componentPath + this.documentId +
            '/edit'
        });
    },

    /**
     * Toggle sidebar.
     * @function
     * @public
     */
    toggleSidebar: function () {
        $$('html')[0].toggleClass('e-has-sideframe');
        var url;
        if (new URI(Energine.base).get('host').contains(new URI(Energine.root).get('host'))) {
            url = Energine.root;
        }
        else {
            url = Energine.base;
        }
        url = new URI(url);

        Cookie.write('sidebar',
            $$('html')[0].hasClass('e-has-sideframe') ? 1 : 0,
            {
                domain: '.' + url.get('host'),
                path: url.get('directory'),
                duration: 30
            });
    },

    /**
     * Show template editor.
     * @function
     * @public
     */
    showTmplEditor: function () {
        ModalBox.open({'url': this.componentPath + 'template'});
    },

    /**
     * Show translation editor.
     * @function
     * @public
     */
    showTransEditor: function () {
        ModalBox.open({'url': this.componentPath + 'translation'});
    },

    /**
     * Show user editor.
     * @function
     * @public
     */
    showUserEditor: function () {
        ModalBox.open({'url': this.componentPath + 'user'});
    },

    /**
     * Show role editor.
     * @function
     * @public
     */
    showRoleEditor: function () {
        ModalBox.open({'url': this.componentPath + 'role'});
    },

    /**
     * Show language editor.
     * @function
     * @public
     */
    showLangEditor: function () {
        ModalBox.open({'url': this.componentPath + 'languages'});
    },

    /**
     * Show file repository.
     * @function
     * @public
     */
    showFileRepository: function () {
        ModalBox.open({'url': this.componentPath + 'file-library'});
    },

    /**
     * Show site editor.
     * @function
     * @public
     */
    showSiteEditor: function () {
        ModalBox.open({'url': this.componentPath + 'sites'});
    },

    /**
     * Edit blocks.
     * @function
     * @public
     */
    editBlocks: function () {
        if (!this.getControlById('editBlocks').getState()) {
            /**
             * Layout manager.
             * @type {LayoutManager}
             */
            this.layoutManager = new LayoutManager(this.componentPath);
        } else {
            if (this.layoutManager && LayoutManager.changed) {
                if (!confirm('The page has unsaved changes. Are you sure you want to quit and lost all changes?')) {
                    return;
                }
            }

            document.location = document.location.href;
        }
    },

    //todo: Why not to inject this to the editMode() method? - try
    /**
     * Reload window in the edit mode.
     * @function
     * @private
     */
    _reloadWindowInEditMode: function () {
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

PageToolbar.Logo = new Class({
    Extends: Toolbar.Control
});