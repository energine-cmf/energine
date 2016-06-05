/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[GoodsForm]{@link DivForm}</li>
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
 * GoodsForm.
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var GoodsForm = new Class(/** @lends GoodsForm# */{
	Extends: Form,

	// constructor
	initialize: function (element) {
        Asset.css('goods_editor.css');
		this.parent(element);
	},

	onTabChange: function () {
		var currentTab = this.tabPane.currentTab;
		var element = this.element;
		// принудительно переписовываем вкладку с характеристиками при каждой активации
		// передаем в нее текущий goodsID и smapID
		if (currentTab.hasAttribute('data-src') && currentTab.getProperty('data-src').test("feature/show")) {
			var smapID = element.getElement('[name=shop_goods[smap_id]]').get('value');
			var goodsID = element.getElementById('goods_id').get('value');
			currentTab.setProperty('data-src', element.getProperty('single_template').replace(Energine['base'], '') + goodsID + '/feature/show/' + smapID + '/');
			currentTab.loaded = false;
			if (!smapID) {
				currentTab.pane.empty();
				alert('No smap id selected');
			} else {
				this.parent();
			}
		}
		// иначе вызываем родительский метод
		else {
			this.parent();
		}

	},

	/**
	 * Overridden parent [save]{@link Form#save} action.
	 * @function
	 * @public
	 */
	save: function () {	 
		return this.parent();		
	},
	close: function () {
	    //if (confirm('ПОДТВЕРДИТЕ ДЕЙСТВИЕ')) {	    
	    this.parent();
	    //}
	}

});
