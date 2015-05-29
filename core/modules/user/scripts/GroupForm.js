/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[GroupForm]{@link GroupForm}</li>
 * </ul>
 *
 * @requires share/Form
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form');

/**
 * GroupForm
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var GroupForm = new Class(/** @lends GroupForm# */{
    Extends: Form,

    // constructor
    initialize:function(element) {
        this.parent(element);

        this.element.getElements('.groupRadio').addEvent('click', this.checkAllRadioInColumn);
//        this.element.getElements('input[type=radio]').addEvent('change', this.uncheckGroupRadio);
    },

    /**
     * Event handler. Check radio button.
     *
     * @function
     * @public
     * @param {Object} event Event.
     */
    checkAllRadioInColumn:function(event) {
        var radio = $(event.target);
        radio.getParent('tbody')
            .getElements('td.' + radio.getParent('td').getProperty('class') + ' input[type=radio]')
            .setProperty('checked', 'checked');
    },

    /**
     * Event handler. Uncheck the radio button.
     *
     * @function
     * @public
     * @param {Object} event Event.
     */
    uncheckGroupRadio: function(event) {
        if (!radio.hasClass('groupRadio')) {
            radio.getParent('tbody')
                .getElement('tr.section_name td.' + radio.getParent('td').getProperty('class') + ' input[type=radio]')
                .removeProperty('checked');
        }
    }
});