var DynListDaemon = new Class({
    initialize: function (el, loadURL, addURL) {
        this.el = $(el);
        this.afterLoad = null;
        this.loadURL = (!loadURL) ? this.el.getProperty('data-load-url') : loadURL;
        this.addURL = (!addURL) ? this.el.getProperty('data-add-url') : addURL;

        this.request = new Request.HTML({
            'method': 'get',
            'update': this.el.getElement('.body'),
            'onSuccess': function () {
                if (this.afterLoad) {
                    this.afterLoad();
                }
            }.bind(this)
        });
    },
    add: function (productID) {
        this.request.send({url: this.addURL.replace('[productID]', productID)});
    },
    load: function (func) {
        this.afterLoad = func;
        this.request.send({url: this.loadURL});
    }
});