ScriptLoader.load('EnlargeImage');

var Product = new Class({
    Implements: Energine.thumbnail,
    
	initialize : function(objID) {
		this.form = $(objID);
        this.form.getElements('a.thumbnail').addEvent('click', this.showImage.bind(this));
	},
	addToBasket : function(productID, count) {
		this.form.grab(new Element('input', 
                { 
                    'name': 'basket[add][' + productID + ']', 
        			'type' : 'hidden',
					'value' : count || 1,
                    'styles': {
                        'display': 'none'
                    }
				}
                
        ));
		this.form.submit();
	}
});