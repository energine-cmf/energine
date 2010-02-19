ScriptLoader.load('EnlargeImage');
var Gallery = new Class({
    Implements: Energine.thumbnail,
    initialize : function(objID) {
        this.element = $(objID);
        if(this.element){
            var elements = this.element.getElements('a.thumbnail');
            if(elements)
                elements.addEvent('click', this.showImage.bind(this));
        }
    }
    
});