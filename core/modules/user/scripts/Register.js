/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Register]{@link Register}</li>
 * </ul>
 *
 * @requires share/ValidForm
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('ValidForm');

/**
 * Register a user.
 *
 * @augments ValidForm
 *
 * @constructor
 * @param {Element|string} element The main element.
 */
var Register = new Class(/** @lends Register# */{
    Extends: ValidForm,

    // constructor
    initialize:function (element) {
        this.parent(element);

        if (this.componentElement) {
            /**
             * Register button.
             * @type {Element}
             */
            this.registerButton = this.form.getElement('button[name=register]');

            /**
             * Login field.
             * @type {Element}
             */
            this.loginField = this.form.getElementById('u_name');
            this.loginField.addEvent('blur', function (event) {
                if (event.target.value) {
                    if (this.validator.validateElement(event.target)) {
                        this.checkLogin(this.loginField.get('value'));
                    } else {
                        if (this.registerButton) {
                            this.registerButton.setProperty('disabled', 'disabled');
                        }
                        Energine.cancelEvent(event);
                    }
                }
            }.bind(this));

            /**
             * Captcha field.
             * @type {Element}
             */
            this.captchaField = this.form.getElementById('captcha');

            /**
             * Captcha image.
             * @type {Element}
             */
            this.captchaImage = this.form.getElementById('captchaImage');
        }
    },

    /**
     * Checking login.
     *
     * @function
     * @public
     * @param {string} loginValue Login value.
     */
    checkLogin:function (loginValue) {
        new Request.JSON({
            url: this.componentElement.getProperty('single_template') + 'check/',
            method: 'post',
            onSuccess: function (response) {
                if (!response.result) {
                    this.validator.showError(this.loginField, response.message);
                    if (this.registerButton) {
                        this.registerButton.setProperty('disabled', 'disabled');
                    }
                } else if (this.registerButton) {
                    this.registerButton.removeProperty('disabled');
                }
            }.bind(this)
        }).send('login=' + loginValue);
    }
});