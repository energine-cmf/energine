var Cart = new Class({
    initialize: function (el) {
        if (this.el = $(el)) {
            this.deleteButtons = this.el.getElements('.delete');
            this.editFields = this.el.getElements('.edit');

            this.request = new Request.HTML({
                'update': this.el,
                'onSuccess': function () {
                    (function(){
                        this.editFields.removeEvents('keyup');
                        this.deleteButtons.removeEvents('click');
                        this.prepare();
                    }).delay(10, this);

                }.bind(this)
            });
            this.prepare();
            /*editFields.addEvent('change', function (e) {
             var value = e.target.value.toInt();
             if(!value){
             e.target.value = 1;
             }
             }.bind(this));*/
        }
    },
    sendEdit: function (cartID, count) {
        this.request.send({
            'method': 'post',
            url: this.el.getProperty('data-edit-url').replace('[productID]', cartID),
            'data': 'count=' + count
        });
    },
    prepare: function () {
        this.deleteButtons = this.el.getElements('.delete');
        this.editFields = this.el.getElements('.edit');

        this.deleteButtons.addEvent('click', function (e) {
            e.stop();
            var el = $(e.target), cartID;
            if (cartID = el.getProperty('data-id')) {
                this.request.send({
                    'method': 'get',
                    url: this.el.getProperty('data-delete-url').replace('[productID]', cartID)
                });
            }
        }.bind(this));

        this.editFields.addEvent('keyup', function (e) {
            var value = e.target.value.toInt();
            var el = $(e.target), cartID;
            if ((cartID = el.getProperty('data-id')) && value) {
                if (this.timeOffset) clearTimeout(this.timeOffset);

                this.timeOffset = this.sendEdit.delay(1000, this, [cartID, value]);
            }
        }.bind(this));
    }

});