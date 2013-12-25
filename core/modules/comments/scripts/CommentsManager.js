/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[CommentsManager]{@link CommentsManager}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/GridManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');

/**
 * CommentsManager
 *
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var CommentsManager = new Class(/** @lends CommentsManager# */{
    Extends: GridManager,

    // constructor
    initialize : function(element) {
        this.parent(element)
    },

    // todo: This method is almost equal to the parent method. Make unique!
    /**
     * Overridden parent [loadPage]{@link GridManager#loadPage} method.
     *
     * @function
     * @public
     * @param {number} pageNum Page number.
     */
    loadPage: function(pageNum) {
        var postBody = '',
            url = '';

        this.pageList.disable();
        if (this.toolbar) {
            this.toolbar.disableControls();
        }
        this.overlay.show();
        this.grid.clear();

        if (this.langId) {
            postBody += 'languageID=' + this.langId + '&';
        }
        if (this.filter.active && this.filter.query.value.length > 0) {
            var fieldName = this.filter.fields.options[this.filter.fields.selectedIndex].value;
            postBody += 'filter' + fieldName + '=' + this.filter.query.value + '&';
        }
        postBody += 'tab_index=' + this.getNumCurrTab() + '&';

        if(this.grid.sort.order){
            url = this.singlePath + 'get-data/' + this.grid.sort.field + '-'
                + this.grid.sort.order + '/page-' + pageNum
        } else {
            url = this.singlePath + 'get-data/page-' + pageNum;
        }

        this.request(url,
            postBody,
            this.processServerResponse.bind(this),
            null,
            this.processServerError.bind(this)
        );
    },

    // todo: This is not current - this is the last.
    /**
     * Get the number of the current tab.
     *
     * @function
     * @public
     * @returns {Number}
     */
    getNumCurrTab: function(){
        return $$('div.e-pane-t-toolbar ul.e-pane-toolbar li.current').getAllPrevious().flatten().length
    },

    // todo: Is this somewhere used?
    /**
     * Approve action.
     * @function
     * @public
     */
    approve: function(){
        var url = this.singlePath + this.grid.getSelectedRecordKey() + '/approve/',
            postBody = 'tab_index=' + this.getNumCurrTab() + '&',
            selectedItem = this.grid.getSelectedItem().getElement('img');

        this.request(url, postBody, function(result) {
            if (result['result']) {
                selectedItem.setProperty('src','images/checkbox_on.png');
            }
        });
    },

    /**
     * Overridden [edit]{@link GridManager#edit} action.
     * @function
     * @public
     */
    edit: function() {
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/edit/' + this.getNumCurrTab() + '/tab',
            onClose: this.loadPage.pass(this.pageList.currentPage, this)
        });
    },

    /**
     * Overridden [del]{@link GridManager#del} action.
     * @function
     * @public
     */
    del: function() {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
            'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            this.request(this.singlePath + this.grid.getSelectedRecordKey() +
                '/delete/' + this.getNumCurrTab() + '/tab',
                null, this.loadPage.pass(this.pageList.currentPage, this));
        }
    }
});
