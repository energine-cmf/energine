ScriptLoader.load('GridManager');
var ActionLogManager = new Class({
    Extends: GridManager,
    clear: function () {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
            'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            this.overlay.show();
            Energine.request(this.singlePath + '/clear/', null,
                function () {
                    this.overlay.hide();
                    this.grid.fireEvent('dirty');
                    this.loadPage(this.pageList.currentPage);
                }.bind(this),
                function (responseText) {
                    this.overlay.hide();
                }.bind(this),
                function (responseText) {
                    alert(responseText);
                    this.overlay.hide();
                }.bind(this)
            );
        }
    }
});