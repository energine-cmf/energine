/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[FormEditor]{@link FormEditor}</li>
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
 * FormEditor
 *
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var FormEditor = new Class(/** @lends FormEditor# */{
    Extends:GridManager,

    // constructor
    initialize: function(element){
        this.parent(element);
    },

    /**
     * Edit properties action.
     * @function
     * @public
     */
    editProps: function(){
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/values/'
        });
    },

    /**
     * Extended parent [onSelect]{@link GridManager#onSelect} event handler.
     * @function
     * @public
     */
    onSelect: function(){
        this.parent();

        var curr = this.grid.getSelectedRecord();
        if(curr.field_id <= 2){
            this.toolbar.disableControls();
        } else {
            this.toolbar.enableControls();
            var t = curr.field_type_real;
            if((t != 'select') && (t != 'multi')) {
                this.toolbar.disableControls('editProps');
            }
        }
        this.toolbar.enableControls('add');
    }
});
