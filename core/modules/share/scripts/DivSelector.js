/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[DivSelector]{@link DivSelector}</li>
 * </ul>
 *
 * @requires DivManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('DivManager');

/**
 * @class DivSelector
 *
 * @augments DivManager
 */
var DivSelector = new Class(/** @lends DivSelector# */{
    Extends:DivManager,

    /**
     * Overridden parent [go]{@link DivManager#go} method.
     * @function
     * @public
     */
    go:function () {
        this.select();
    }
});
