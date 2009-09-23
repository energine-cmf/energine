ScriptLoader.load('GridManager.js');

var ResumeGridManager = new Class({
    Extends: GridManager,
    onDoubleClick: function(){
        this.view();
    }    
});