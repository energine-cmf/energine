/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[TagEditor]{@link TagEditor}</li>
 * </ul>
 *
 * @requires GridManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');

/**
 * Tag editor.
 *
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var TagEditor = new Class(/** @lends TagEditor# */{
    Extends:GridManager,

    // constructor
    initialize:function (element) {
        this.parent(element);

        /**
         * Tag id.
         * @type {string}
         */
        this.tag_id = this.element.getProperty('tag_id');
    },

    /**
     * Overridden parent [buildRequestURL]{@link GridManager#buildRequestURL} method.
     *
     * @param {number|string} pageNum Page number.
     * @returns {string}
     */
    buildRequestURL: function(pageNum) {
        var url = '';

        if (this.grid.sort.order) {
            url = this.singlePath + this.tag_id + '/get-data/' + this.grid.sort.field + '-'
                + this.grid.sort.order + '/page-' + pageNum
        } else {
            url = this.singlePath + this.tag_id + '/get-data/page-' + pageNum;
        }

        return url;
    },

    /**
     * Overridden parent [close]{@link GridManager#close} action.
     * @function
     * @public
     */
    close:function () {
        var overlay = this.overlay;
        overlay.show();
        new Request.JSON({
            'url': this.singlePath + 'tags/get-tags/',
            'method': 'post',
            'data': {
                json: 1,
                tag_id: this.tag_id
            },
            'evalResponse': true,

            'onComplete': function(data) {
                overlay.hide();

                if (data && data.data && data.data.length) {
                    ModalBox.setReturnValue(data.data.join(','));
                } else {
                    ModalBox.setReturnValue('');
                }
                ModalBox.close();
            }.bind(this),

            'onFailure': function (e) {
                overlay.hide();
            }
        }).send();
    },

    /**
     * Select action.
     * @function
     * @public
     */
    select: function() {
        var r = this.grid.getSelectedRecord();
        if (r) {
            this.tag_id = r.tag_id;
            this.close();
        }
    },

    /**
     * Overridden parent [onDoubleClick]{@link GridManager#onDoubleClick} event handler.
     * @function
     * @public
     */
    onDoubleClick: function () {
        this.select();
    },

    /**
     * Overridden parent [onSelect]{@link GridManager#onSelect} event handler.
     * @function
     * @public
     */
    onSelect: function () {
        var r = this.grid.getSelectedRecord();
        this.toolbar.enableControls();
        var selectBtn = this.toolbar.getControlById('select');
        var addBtn = this.toolbar.getControlById('add');
        if (r && !this.tag_id) {
            addBtn.enable(true);
            selectBtn.enable(true);
        }
    }
});
