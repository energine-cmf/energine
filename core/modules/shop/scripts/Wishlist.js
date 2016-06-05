var Wishlist = new Class({
    initialize: function (el) {
        if (this.form = $(el)) {
            var content = this.form.getElements('input[type=checkbox]'), buttons = this.form.getElements('button');
            content.addEvent('change', function (e) {
                if (!content.getProperty('checked').some(function (v) {
                        return v;
                    })) {
                    buttons.setProperty('disabled', 'disabled');
                }
                else {
                    buttons.removeProperty('disabled');
                }
            });
            buttons.setProperty('disabled', 'disabled');
        }
    }
});
