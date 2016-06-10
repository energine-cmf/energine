var ProductList = new Class({
    initialize: function (el) {
        this.element = $(el);
        this.productList = this.element.getElement('.goods_list');
        this.element.getElements('.goods_view_type a').addEvent('click', function (e) {
            e.stop();
            $(e.target).getParent('.goods_view_type').getElements('a').removeClass('active');
            $(e.target).addClass('active');
            this.productList.toggleClass('wide_list');
        }.bind(this));
    }
});