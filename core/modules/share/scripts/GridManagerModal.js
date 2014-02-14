/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Grid]{@link GridManagerModal}</li>
 * </ul>
 *
 * @requires GridManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');
var GridManagerModal = new Class(/** @lends GridManagerModal# */{
    Extends: GridManager,
    /**
     * Overridden parent [onDoubleClick]{@link GridManager#onDoubleClick} event handler.
     * @function
     * @public
     */
    onDoubleClick: function () {
        this.use();
    },
    /**
     * Use action
     * Return selected record as a result of modal box call
     *
     * @function
     * @public
     */
    use: function () {
        ModalBox.setReturnValue(this.grid);
        ModalBox.close();
    }
});