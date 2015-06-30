/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[ValidForm]{@link ValidForm}</li>
 * </ul>
 *
 * @requires Energine
 * @requires Validator
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Validator');

/**
 * ValidForm
 *
 * @constructor
 * @param {Element|string} element The main element.
 */
var ValidForm = new Class(/** @lends ValidForm# */{
    // constructor
    initialize: function (element) {
        /**
         * The main element.
         * @type {Element}
         */
        this.element = $(element);
        if (this.element) {
            /**
             * Form element.
             * @type {Element}
             */
            this.form = (this.element.get('tag') === 'form')?this.element:this.element.getParent('form');

            if (this.form) {
                /**
                 * Single path.
                 * @type {string}
                 */
                this.singlePath = this.element.getProperty('single_template');

                this.form.addClass('form').addEvent('submit', this.validateForm.bind(this));

                /**
                 * Validator.
                 * @type {Validator}
                 */
                this.validator = new Validator(this.form);
            }
        }
    },

    /**
     * Event handler. Validate form.
     *
     * @function
     * @public
     * @param {Object} event Event.
     * @returns {boolean} true if the form is valid, otherwise - false.
     */
    validateForm: function (event) {
        if (!this.validator.validate()) {
            event.preventDefault();
            return false;
        } else {
            return true;
        }
    }
});
