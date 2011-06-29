ScriptLoader.load('ValidForm');
var FormBehavior = new Class({
    Extends:ValidForm,
    initialize: function(element){
        this.parent(element);
        this.componentElement = $(element);
    }
});
