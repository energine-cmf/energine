ScriptLoader.load('GridManager');

var GoodsGridManager = new Class({
    Extends: GridManager,
    initialize: function (el) {
        this.parent(el);
        this.productWindowRef = null;
    },
    go: function () {
        var url = this.grid.getSelectedRecord()['goods_full_path'];
        if (this.productWindowRef == null || this.productWindowRef.closed){
            this.productWindowRef = window.open(url, '_blank');
        }
        else {
            this.productWindowRef.location = url;
            this.productWindowRef.focus();
        }

    }
});