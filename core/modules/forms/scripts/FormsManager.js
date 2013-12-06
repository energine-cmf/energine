/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[FormsManager]{@link FormsManager}</li>
 * </ul>
 *
 * @requires share/GridManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');

/**
 * @class FormsManager
 *
 * @augments GridManager
 */
var FormsManager = new Class(/** @lends FormsManager# */{
    Extends: GridManager,

    // Actions:
    /**
     * Edit form action.
     * @function
     * @public
     */
    editForm: function() {
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/edit-form/',
            onClose: this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * View form action.
     * @function
     * @public
     */
    viewForm: function(){
        ModalBox.open({
            url:this.singlePath + this.grid.getSelectedRecordKey() + '/viewForm/'
        });
    },

    /**
     * Show result action.
     * @function
     * @public
     */
    showResults: function(){
        ModalBox.open({
            url:this.singlePath + this.grid.getSelectedRecordKey() + '/results/'
        });
    }
});
