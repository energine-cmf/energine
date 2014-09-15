/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[FeedToolbar]{@link FeedToolbar}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/Toolbar
 * @requires share/ModalBox
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Toolbar', 'ModalBox');

// TODO: This must extend PageToolbar class, not Toolbar, or this constructor must be the constructor from the PageToolbar. -- wait for tests
//todo: Some of actions were already implemented (I think in the GridManager.js or TreeView.js). - leave as is
/**
 * FeedToolbar
 *
 * @augments Toolbar
 *
 * @constructor
 * @param {string} toolbarName The name of the toolbar.
 */
var FeedToolbar = new Class(/** @lends FeedToolbar# */{
    Extends: Toolbar,

    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    // constructor
    initialize: function (Container) {
        Asset.css('pagetoolbar.css');
        Asset.css('feedtoolbar.css');

        //TODO это слегка костыль --> @ 31.10.2013 See above TODO
        this.parent('feed_toolbar');

        this.bindTo(this);
        this.dock();

        this.element.inject(document.getElement('.e-topframe'), 'bottom');

        var html = $$('html')[0];
        if (html.hasClass('e-has-topframe1')) {
            html.removeClass('e-has-topframe1');
            html.addClass('e-has-topframe2');
        }
        if (html.hasClass('e-has-topframe2')) {
            html.removeClass('e-has-topframe2');
            html.addClass('e-has-topframe3');
        }

        this.load(Container);
        this.singlePath = Container.getProperty('single_template');
        var feedElement = $(Container.getProperty('linkedTo'));
        this.disableControls();
        if (feedElement) {
            this._prepareDataSet(feedElement);
            if (this.selected = feedElement.getProperty('current')) {
                this.enableControls('add', 'edit'/*, 'delete'*/);
            } else {
                this.enableControls('add');
                this.selected = false;
            }
        }
        Container.dispose();

        this.previous = false;
    },

    /**
     * Add action.
     * @function
     * @public
     */
    add: function () {
        ModalBox.open({
            url: this.singlePath + 'add/',
            onClose: function (returnValue) {
                if (returnValue == 'add') {
                    this.add();
                }
                else if (returnValue) {
                    this._reload(true);
                }
            }.bind(this)
        });
    },

    /**
     * Edit action.
     * @function
     * @public
     */
    edit: function () {
        ModalBox.open({
            url: this.singlePath + this.selected + '/edit/',
            onClose: this._reload.bind(this)
        });
    },

    /**
     * Delete action.
     * @function
     * @public
     */
    del: function () {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE]') || 'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            Energine.request(this.singlePath + this.selected + '/delete/', null, this._reload);
        }
    },

    /**
     * Move up action.
     * @function
     * @public
     */
    up: function () {
        Energine.request(this.singlePath + this.selected + '/up/', null, this._aftermove.pass('up', this));
    },

    /**
     * Move down action.
     * @function
     * @public
     */
    down: function () {
        Energine.request(this.singlePath + this.selected + '/down/', null, this._aftermove.pass('down', this));
    },

    /**
     * After move actions.
     *
     * @memberOf FeedToolbar#
     * @function
     * @protected
     * @param {string} direction Moving direction.
     */
    _aftermove: function (direction) {
        try {
            if (direction == 'up') {
                var sibling = this.previous.getPrevious();
                if (!sibling.getProperty('record')) {
                    throw 'error';
                }
                $(this.previous).inject(sibling, 'before');
            } else {
                $(this.previous).inject(this.previous.getNext(), 'after');
            }
        } catch (err) {
            console.warn(err);
            this._reload(true);
        }
    },

    /**
     * Selecting.
     *
     * @memberOf FeedToolbar#
     * @function
     * @protected
     * @param {Element} element Element that must be selected.
     */
    _select: function (element) {
        if (this.previous) {
            this.previous.removeClass('record_select');
        }

        if (this.previous == element) {
            this.selected = this.previous = false;
            this.disableControls();
            this.enableControls('add');
        } else {
            this.previous = element;
            element.addClass('record_select');
            this.selected = element.getProperty('record');
            this.enableControls();
        }
    },

    /**
     * Reload.
     *
     * @memberOf FeedToolbar#
     * @function
     * @protected
     * @param data
     */
    _reload: function (data) {
        if (data) {
            var form = new Element('form').setProperties({'action': '', 'method': 'POST'});
            form.adopt(new Element('input').setProperty('name', 'editMode').setProperty('type', 'hidden'));
            $(document.body).adopt(form);
            form.submit();
        }
    },

    /**
     * Prepare dataset.
     *
     * @memberOf FeedToolbar#
     * @function
     * @protected
     * @param {Element} linkID
     */
    _prepareDataSet: function (linkID) {
        var linkChilds;
        linkChilds = linkID.getElements('[record]');
        if (linkChilds.length) {
            //список
            linkID.addClass('active_component');
            linkID.fade(0.7);
            linkChilds.each(function (element) {
                element.addEvent('mouseover', function () {
                    this.addClass('record_highlight')
                });
                element.addEvent('mouseout', function () {
                    this.removeClass('record_highlight')
                });
                element.addEvent('click', function(e){
                    e.preventDefault();
                    this._select(element);
                }.bind(this));
            }, this);
        }
    }
});