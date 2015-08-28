/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Validator]{@link Validator}</li>
 * </ul>
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

/**
 * Validator.
 *
 * @constructor
 * @param {Element|string} form Form element.
 * @param {TabPane} tabPane Tab panels.
 */
var Validator = new Class(/** @lends Validator# */{
    // constructor
    initialize: function (form, tabPane) {
        /**
         * The form element.
         * @type {Element}
         */
        this.form = $(form);

        /**
         * The tab panels.
         * @type {TabPane}
         */
        this.tabPane = tabPane || null;
        this.prepareFloatFields();
    },

    /**
     * Prepare the fields with float values.
     * @function
     * @public
     */
    prepareFloatFields: function () {
        function prepareFunction(event) {
            event.target.value = event.target.value.replace(/\,/, '.');
        }

        //Для всех field type=float(class=float)
        //меняем , на .
        this.form.getElements('.float').each(function (element) {
            element.removeEvent('change', prepareFunction);
            element.addEvent('change', prepareFunction);
        });
    },

    /**
     * Remove the error from the invalid field.
     *
     * @function
     * @public
     * @param {Element} field Invalid field element (with the class <tt>'invalid'</tt>).
     */
    removeError: function (field) {
        field = $(field);
        if (field.hasClass('invalid')) {
            field.removeClass('invalid');
            var errorDiv;
            if (errorDiv = field.getParent('.field').getElement('div.error')) {
                errorDiv.destroy();
            }
        }
    },
    clearErrors: function (field) {
        this.form.getElements('.field').each(this.removeError);
    },
    /**
     * Show the error message for specific field.
     *
     * @function
     * @public
     * @param {Element} field Make the field element invalid (add the class <tt>'invalid'</tt>).
     * @param {string} [message] Error message.
     */
    showError: function (field, message) {
        field = $(field);
        this.removeError(field);
        field.addClass('invalid');
        new Element('div', {
            'class': 'error',
            'html': message,
            'events': {
                'click': this.removeError.pass(field, this)
            }
        }).inject(field.parentNode, 'after');
    },

    /**
     * Scroll to the field.
     *
     * @function
     * @public
     * @param {Element} field Field element.
     */
    scrollToElement: function (field) {
        var context = document.getElement('.e-mainframe') || window;

        var scroll = new Fx.Scroll(context, {
            offset: {
                'x': -30,
                'y': -20
            },
            transition: Fx.Transitions.linear
        });

        scroll.toElement(field).chain(function () {
            try {
                field.focus()
            } catch (e) {
                console.warn(e);
            }
        });
    },

    /**
     * Validate the field.
     * @param {Element} field Field element.
     * @returns {boolean} True, if the field was successful validated, otherwise - false.
     */
    validateElement: function (field) {
        var result = true;
        var pattern,
            message;
        field = $(field);
        pattern = field.getProperty('nrgn:pattern');
        if (pattern
            && (message = field.getProperty('nrgn:message'))
            && !field.getProperty('disabled')
            && !field.hasClass('novalidation')) {
            var a = pattern.split('/');

            if (!new RegExp(a[1], a[2]).test(field.value)) {
                //if (!eval('field.value.match('+pattern+');')) {
                //Выводим информацию об ошибке
                this.showError(field, message);
                //Вешаем проверку правильности введения данных на onblur
                if (!field.getProperty('check')) {
                    field.addEvent('blur', this.validateElement.bind(this, field));
                    field.addEvent('keydown', this.removeError.bind(this, field));
                    field.setProperty('check', 'check');
                }
                result = false;
            } else {
                //Убираем информацию о предыдущей ошибке
                this.removeError(field);
            }
        }
        return result;
    },

    /**
     * Validate all fields.
     *
     * @function
     * @public
     * @returns {boolean} True if there were no errors, otherwise - false.
     */
    validate: function () {
        //Массив ошибочных полей
        var error = false,
            firstErrorField = null;

        //заполняем массив ошибочных полей
        new Elements(this.form.elements).each(function (field) {
            if (!this.validateElement(field) && !error) {
                //Нас интересует только первое поле
                firstErrorField = field;
                error = true;
            }
        }, this);

        //Если есть ошибки
        if (error) {
            //Если мы внутри табов
            //определяем таб первого ошибочного поля и переключаемся на этот таб
            if (this.tabPane) {
                this.tabPane.show(this.tabPane.whereIs(firstErrorField))
            }
            //Скроллируем
            this.scrollToElement(firstErrorField);

            try {
                firstErrorField.focus()
            } catch (e) {
                console.warn(e);
            }
        }

        return !error;
    }
});