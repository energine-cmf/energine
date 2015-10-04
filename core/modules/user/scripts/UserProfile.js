/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[UserProfile]{@link UserProfile}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/ValidForm
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('ValidForm');

/**
 * User profile.
 *
 * @augments ValidForm
 *
 * @constructor
 * @param {Element|string} element The main element.
 */
var UserProfile = new Class(/** @lends UserProfile# */{
    Extends: ValidForm,

    // constructor
    initialize: function(element){
        this.parent(element);
    },

    /**
     * Extended parent [validateForm]{@link ValidForm#validateForm} method.
     * @function
     * @public
     * @param {Object} event Event.
     */
    validateForm: function(event) {
        var field = $('u_password');
        var field2 = $('u_password2');

        if (field && field2 && field.value != field2.value) {
            this.validator.showError(field, field.getProperty('nrgn:message2'));
            event.stop();
        } else {
            this.parent(event);
        }
    }
});