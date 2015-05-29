/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[TextBlockSource]{@link TextBlockSource}</li>
 * </ul>
 *
 * @requires Form
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form');

/**
 * TextBlockSource
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var TextBlockSource = new Class(/** @lends TextBlockSource# */{
	Extends: Form,

    // constructor
    initialize: function(element) {
        this.parent(element);

        this.element.getElement('div.field').swapClass('min', 'max');
        this.codeEditors[0].setValue(window.top.ModalBox.getExtraData());
    },

    /**
     * Update action.
     * @function
     * @public
     */
    update: function() {
        window.top.ModalBox.setReturnValue(this.codeEditors[0].getValue());
        window.top.ModalBox.close();
    },

    /**
     * Close action.
     * @function
     * @public
     */
    cancel: function() {
        window.top.ModalBox.close();
    }
});