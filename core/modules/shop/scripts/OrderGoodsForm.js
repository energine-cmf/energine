/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[OrderGoodsForm]{@link DivForm}</li>
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
 * OrderGoodsForm.
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var OrderGoodsForm = new Class(/** @lends OrderGoodsForm# */{

	Extends: Form,

	// constructor
	initialize: function (element) {
        //Asset.css('order_editor.css');
		this.parent(element);

		$(window).addEvent('orderGoodsTabMain', this.onOrderTabMain.bind(this));
		new Elements([this.element.getElementById('goods_quantity'), this.element.getElementById('goods_price')])
			.addEvents({'keyup': this.recalculateTotals.bind(this), 'change': this.recalculateTotals.bind(this)});

		jQuery('#' + 'goods_id').on('change', this.fetchGoodsDetails.bind(this));


		$(window).fireEvent('orderGoodsTabMain');

	},

	onTabChange: function () {
		$(window).fireEvent('orderGoodsTabMain');
		this.parent();
	},

	fetchGoodsDetails: function() {

		var goodsID = this.element.getElementById('goods_id').get('value');

		var goods_title = this.element.getElement('[name=shop_orders_goods[goods_title]]');
		var goods_description = this.element.getElement('[name=shop_orders_goods[goods_description]]');
		var goods_price = this.element.getElement('[name=shop_orders_goods[goods_price]]');
		var goods_real_price = this.element.getElement('[name=shop_orders_goods[goods_real_price]]');
		var goods_quantity = this.element.getElement('[name=shop_orders_goods[goods_quantity]]');
		var goods_amount = this.element.getElement('[name=shop_orders_goods[goods_amount]]');

		if (goodsID) {
			var url = [this.singlePath, goodsID, '/goods-details/'].join('');
			var UID = Cookie.read(
				'uid'
			);

			var postData = null;
			if(UID){
				postData = {
					'u_id':UID
				}
			}

			// ajax request
			Energine.request(
				url,
				postData,
				function (data) {
					if (data.result) {
						goods_title.set('value', data.goods_title);
						goods_price.set('value', data.goods_price);
						goods_real_price.set('value', data.goods_real_price);
						this.element.getElementById('goods_real_price_read').set('value', data.goods_real_price);
						this.element.getElementById('goods_price_read').set('value', data.goods_price);

						goods_quantity.set('value', data.goods_quantity);
						goods_amount.set('value', data.goods_amount);
						goods_description.set('value', data.goods_description);
					}
				}.bind(this),
				this.processServerError.bind(this),
				this.processServerError.bind(this)
			);
		} else {
			goods_title.set('value', '');
			goods_description.set('value', '');
			goods_price.set('value', '0.00');
			goods_quantity.set('value', '1');
			goods_amount.set('value', '0.00');
		}
	},

	recalculateTotals: function() {

		var goodsID = this.element.getElementById('goods_id').get('value');

		var goods_amount = this.element.getElement('[name=shop_orders_goods[goods_amount]]');
		var goods_quantity = this.element.getElement('[name=shop_orders_goods[goods_quantity]]');
		var goods_price = this.element.getElement('[name=shop_orders_goods[goods_price]]');

		if (goodsID) {

			var url = (goodsID) ? [this.singlePath, goodsID, '/goods-total/'].join('') : [this.singlePath, 'goods-total/'].join('');

			// ajax request
			Energine.request(
				url,
				{
					'goods_price': goods_price.get('value'),
					'goods_quantity': goods_quantity.get('value')
				},
				function (data) {
					if (data.result) {
						goods_amount.set('value', data.goods_amount);
						if(this.element.getElementById('goods_amount_read')){
							this.element.getElementById('goods_amount_read').set('value', data.goods_amount);
						}
					}
				}.bind(this),
				this.processServerError.bind(this),
				this.processServerError.bind(this)
			);
		}
	},

	onOrderTabMain: function(e) {
		this.recalculateTotals();
	},

	/**
	 * Overridden parent [save]{@link Form#save} action.
	 * @function
	 * @public
	 */
	save: function () {
		return this.parent();
	}

});
