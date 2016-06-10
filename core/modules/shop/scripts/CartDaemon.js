var CartDaemon = new Class({
    initialize: function (el) {
        this.el = $(el);
        this.afterLoad = null;

        this.request = new Request.HTML({
            'method': 'get',
            'update': this.el.getElement('.body'),
            'onSuccess': function(){
                if(this.afterLoad){
                    this.afterLoad();
                }
            }.bind(this)
        });
    },
    add: function(event, productID){
        event = new DOMEvent(event);
        event.stop();

        this.request.send({url:this.el.getProperty('data-add-url').replace('[productID]', productID)});
    },
    load: function(func){
        this.afterLoad = func;
        this.request.send({url:this.el.getProperty('data-load-url')});
    }
});