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
        /*var values = this.grid.getSelectedRecordKey();

        ModalBox.setReturnValue(Object.map(this.grid.getMetadata(), function (value, key) {
            if (values[key]) {
                value.value = values[key];
            }
            return value;
        }));*/
        ModalBox.setReturnValue({'key': this.grid.getSelectedRecordKey(), 'data':this.grid.getSelectedRecord(), 'dirty':this.grid.isDirty});
        ModalBox.close();
    },
    /**
     * Close action.
     * @function
     * @public
     */
    close: function () {
        ModalBox.setReturnValue({'dirty':this.grid.isDirty});
        ModalBox.close();
    }
});