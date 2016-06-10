/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[OrderForm]{@link DivForm}</li>
 * </ul>
 *
 * @requires Form
 * @requires ModalBox
 *
 * @author Andy Karpov
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form', 'ModalBox');

/**
 * OrderForm.
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var OrderForm = new Class(/** @lends OrderForm# */{
    Extends: Form,

    // constructor
    initialize: function (element) {
        //Asset.css('order_editor.css');
        this.parent(element);

        $(window).addEvent('orderTabMain', this.onOrderTabMain.bind(this));
        $(window).addEvent('orderTabGoods', this.onOrderTabGoods.bind(this));
        new Elements([
            this.element.getElement('[name=shop_orders[order_discount]]')/*,
            this.element.getElement('[name=shop_orders[order_discount]]')*/
        ]).addEvents({
            'keyup': this.recalculateTotals.bind(this),
            'change': this.recalculateTotals.bind(this)
        });

        var $uid = this.element.getElementById('u_id');
            $uid.addEvent('change', this.fetchUserDetails.bind(this, $uid));

        $(window).fireEvent('orderTabMain');

    },

    onTabChange: function () {
        var currentTab = this.tabPane.currentTab;

        // вкладка "товары заказа"
        if (currentTab.hasAttribute('data-src') && currentTab.getProperty('data-src').test("goods")) {
            $(window).fireEvent('orderTabGoods');
            if (!currentTab.loaded) {
                currentTab.pane.empty();
                currentTab.pane.grab(new Element('iframe', {
                    src: Energine['base'] + currentTab.getProperty('data-src'),
                    frameBorder: 0,
                    scrolling: 'no',
                    styles: {
                        width: '99%',
                        height: '99%'
                    }
                }));
                currentTab.loaded = true;
            }
            else {
                this.codeEditors.each(function(ce){
                    ce.refresh();
                });
            }
        }
        // вкладка "основная"
        else {
            $(window).fireEvent('orderTabMain');
            this.parent();
        }
    },

    fetchUserDetails: function ($uID) {
        var value;
        if ($uID) {
            Cookie.write(
                'uid',
                value = $uID.get('value'),
                {path: new URI(Energine.base).get('directory'), duration: 1}
            );

            var url = [this.singlePath, value, '/user-details/'].join('');

            // ajax request
            Energine.request(
                url,
                null,
                function (data) {
                    if (data.result) {
                        delete data.result, data.mode;
                        Object.each(data, function(value, key){
                            if(this.element.getElementById(key)){
                                this.element.getElementById(key).set('value', value);
                            }
                        }, this);
                        $(this.element.getElementById('u_id_name')).set('value', data.order_user_name);
                    }
                }.bind(this),
                this.processServerError.bind(this),
                this.processServerError.bind(this)
            );
        }
    },

    recalculateTotals: function (onSuccess) {

        var orderID = this.element.getElementById('order_id').get('value');

        var order_amount = this.element.getElement('[name=shop_orders[order_amount]]');
        var order_discount = this.element.getElement('[name=shop_orders[order_discount]]');
        var order_total = this.element.getElement('[name=shop_orders[order_total]]');

        var url = (orderID) ? [this.singlePath, orderID, '/order-total/'].join('') : [this.singlePath, 'order-total/'].join('');

        // ajax request
        Energine.request(
            url,
            {
                'order_discount': order_discount.get('value')
            },
            function (data) {
                if (data.result) {
                    order_amount.set('value', data.amount);
                    if(this.element.getElementById('order_amount_read')){
                        this.element.getElementById('order_amount_read').set('html', data.amount);
                    }
                    order_total.set('value', data.total);
                    if(this.element.getElementById('order_total_read')){
                        this.element.getElementById('order_total_read').set('html', data.total);
                    }
                    if(this.element.getElementById('order_goods_discount_read')){
                        this.element.getElementById('order_goods_discount_read').set('html', data.discount);
                    }

                    if(onSuccess) onSuccess();
                }
            }.bind(this),
            this.processServerError.bind(this),
            this.processServerError.bind(this)
        );
    },

    onOrderTabMain: function (e) {
        this.recalculateTotals();
    },

    onOrderTabGoods: function (e) {
        this.recalculateTotals();
    },

    /**
     * Overridden parent [save]{@link Form#save} action.
     * @function
     * @public
     */
    save: function () {
        this.recalculateTotals(function(){
            Cookie.dispose('uid', {path: new URI(Energine.base).get('directory'), duration: 0});
            this.richEditors.each(function (editor) {
                editor.onSaveForm();
            });
            this.codeEditors.each(function (editor) {
                editor.save();
            });

            if (!this.validator.validate()) {
                return;
            }

            this.booleanTags.each(function(bt){
                bt.save();
            });

            this.overlay.show();

            Energine.request(
                this.buildSaveURL(),
                this.form.toQueryString(),
                this.processServerResponse.bind(this),
                this.processServerError.bind(this),
                this.processServerError.bind(this)
            );
        }.bind(this));
    }

});

Lookup = Class.refactor(Lookup, {
    rebuild: function (result) {
        if (result.result && result.data) {
            this.list.update(result.data.map(function (item) {
                return {
                    key: item[this.keyFieldName],
                    'value': item['u_acp_name']
                }
            }.bind(this)), this.value);
            this.list.show();
        }
        else {
            this.list.hide();
        }
    },
    requestValues: function (str) {
        if (!this.date) {
            this.date = new Date();
        }

        if ((this.date.get('sec') - new Date().get('sec')) < Lookup.TIMEOUT_PERIOD) {
            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }
        }

        this.timeout = (function () {
            new Request.JSON({
                url: this.url + 'get-data/',
                link: 'cancel',
                onSuccess: this.rebuild.bind(this)
            }).send({
                data: 'filter=' + JSON.encode(
                    new Filter.ClauseSet(
                        Filter.Clause.create('u_name', this.valueTable, 'like', 'string', null).setValue(str),
                        Filter.Clause.create('u_fullname', this.valueTable, 'like', 'string', 'OR ').setValue(str),
                        Filter.Clause.create('u_phone', this.valueTable, 'like', 'string', 'OR').setValue(str.replace(/-|\(|\)/g,''))
                    )
                ) + '&'
            });
        }).delay(Lookup.TIMEOUT_PERIOD, this);

    },
})