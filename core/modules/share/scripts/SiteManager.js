ScriptLoader.load('GridManager');
var SiteManager = new Class({
    Extends:GridManager,
    initialize: function(element) {
        this.parent(element);
    },
    go: function (){
        var site = this.grid.getSelectedRecord();
        window.location.href = site.site_protocol + '://' + site.site_host + ':' + site.site_port + site.site_root ;  
    }
});
