/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[FormBehavior]{@link FormBehavior}</li>
 * </ul>
 *
 * @requires share/ValidForm
 * @requires share/datepicker
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('ValidForm', 'datepicker');

/**
 * FormBehavior
 *
 * @augments ValidForm
 *
 * @constructor
 * @param {Element|string} element The main element.
 */
var FormBehavior = new Class(/** @lends FormBehavior# */{
    Extends: ValidForm,

    // constructor
    initialize: function(element){
        this.parent(element);
    },

    /**
     * Overridden parent [validateForm]{@link ValidForm#validateForm} method.
     * @function
     * @public
     * @param {Object} event Event.
     */
    validateForm: function(event){
        //NOTE: Recaptcha comes from Google.
        if((typeof Recaptcha !== 'undefined') && !Recaptcha.get_response()) {
            this.validator.showError($('recaptcha_widget_div'), 'Необходимо ввести значения');
            event.stop();
            return false;
        }
        
        return this.parent(event);
    }
});
