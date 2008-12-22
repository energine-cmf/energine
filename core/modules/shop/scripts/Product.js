var Product = new Class({
	initialize: function(objID){
		this.form = $(objID); 
	},
	addToBasket: function(productID, count){
        var count = count || 1;
        var oProductID = new Element('input').setProperty('name', 'shop_products[product_id]').setProperties({'type':'hidden', 'value': productID});	
		oProductID.setStyle('display', 'none');
        this.form.adopt(oProductID);
        if (count>1) {
            var oProductCount = new Element('input').setProperty('name', 'shop_products[product_count]').setProperties({'type':'hidden', 'value': count});	
    		oProductCount.setStyle('display', 'none');
            this.form.adopt(oProductCount);                
        }
        //this.form.setProperty('action', 'shop/basket/');
	this.form.submit();
	}
});