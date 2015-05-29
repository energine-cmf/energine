ScriptLoader.load('ValidForm');

var SubscriptionForm = new Class({
    Extends: ValidForm,
    initialize: function (el) {
        this.parent($(el));
        this.request = new Request.JSON({
            'url': this.element.getProperty('single_template') + this.form.getProperty('action'),
            'onSuccess': this.success.bind(this)
        });
    },
    validateForm: function (event) {
        var result = this.parent(event);
        if (result) {
            this.request.send(this.form.toQueryString());
        }
        return false;
    },
    success: function (response) {
        if (response.result) {
            this.form.getElements('input').setProperty('disabled', 'disabled');
            this.form.getElement('button').setProperty('disabled', 'disabled').set('html', response.message);
        }
        else {
            this.validator.showError('email', response.message);
        }

    }
});