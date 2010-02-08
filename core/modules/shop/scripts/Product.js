var Product = new Class({
	initialize : function(objID) {
		this.form = $(objID);
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