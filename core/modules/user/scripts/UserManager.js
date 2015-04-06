/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[UserManager]{@link UserManager}</li>
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
 * User manager.
 * @class
 * @augments GridManager
 */
var UserManager  = new Class(/** @lends UserManager# */{
    Extends: GridManager,

    /**
     * Activate.
     * @function
     * @public
     */
    activate: function(){
        this.request(
            this.singlePath + this.grid.getSelectedRecordKey() + '/activate/',
            null,
            this.loadPage.pass(this.pageList.currentPage, this)
        );
    }
});