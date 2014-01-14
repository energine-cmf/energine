/**
 * Singleton class, so it instance can be referenced
 * through all project.
 */
var Basket = (function() {
    var BasketSingleton = new Class({
        element: null,
        dataUrl: null,
        products: [],
        initialize: function(el) {
            if(this.element = $(el)) {
                this.dataUrl = this.element.getProperty('data-url');
                this.update();
            }
        },
        put: function(product) {
            this.request(
                this.dataUrl + 'put/' + product + '/?json',
                null,
                function() {
                    this.update();
                }.bind(this)
            );
        },
        update: function() {
            this.element.load(this.dataUrl + 'list/?html');
        }
    });
    BasketSingleton.implement(Energine.request);
    var instance;
    return function(el) {
        return (instance)
            ? instance
            : instance = new BasketSingleton(el);
    }
})();
