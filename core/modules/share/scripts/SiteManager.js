ScriptLoader.load('GridManager');
var SiteManager = new Class({
    Extends:GridManager,
    initialize: function(element) {
        this.parent(element);
    },
    reset: function() {
        if (confirm(Energine.translations.get('MSG_CONFIRM_TEMPLATES_RESET'))) {
            this.request(
                    this.singlePath + 'reset/' +
                            this.grid.getSelectedRecordKey() +
                            '/reset-templates/',
                    null,
                    function(response) {
                        if (response.result) {
                            alert(Energine.translations.get('MSG_TEMPLATES_RESET'));
                        }
                    }
            );
        }

    },
    go: function () {
        window.top.location.href = this.singlePath + 'goto/' + this.grid.getSelectedRecordKey() + '/';
    }
});
