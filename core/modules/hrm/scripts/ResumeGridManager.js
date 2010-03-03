ScriptLoader.load('GridManager');

var ResumeGridManager = new Class({
    Extends: GridManager,
    onDoubleClick: function(){
        this.view();
    }    
});