ScriptLoader.load('EnlargeImage');
var Gallery = new Class({
    Implements: Energine.thumbnail,
    initialize : function(objID) {
        this.element = $(objID);
        this.element.getElements('a.thumbnail').addEvent('click', this.showImage.bind(this));
    }
    
});