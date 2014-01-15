ScriptLoader.load('shop/Basket');
var ProductFeed = new Class({
    basket: null,
    initialize: function(el) {
        if(el) {
            this.element = $(el);
            this.basket = new Basket();
            this.initControls();
        }
    },
    initControls: function() {
        this.element.getElements('.basket_put').addEvent('click', function(e) {
            e.stop();
            var product = parseInt(e.target.getParent('li').getProperty('data-product-id'));
            this.basket.put(product);
        }.bind(this));
    }
});
