/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[ShopDivForm]{@link DivForm}</li>
 * </ul>
 *
 * @requires DivForm
 * @requires Form
 * @requires ModalBox
 *
 * @author Andy Karpov
 *
 * @version 1.0.0
 */

ScriptLoader.load('DivForm', 'Form', 'ModalBox');

/**
 * ShopDivForm.
 *
 * @augments Form
 *
 * @borrows Form.Label.setLabel as ShopDivForm#setLabel
 * @borrows Form.Label.prepareLabel as ShopDivForm#prepareLabel
 * @borrows Form.Label.restoreLabel as ShopDivForm#restoreLabel
 * @borrows Form.Label.showTree as ShopDivForm#showTree
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var ShopDivForm = new Class(/** @lends ShopDivForm# */{
    Extends: DivForm,

    // constructor
    initialize: function (element) {
        var PROPS_TAB_IDX = 3;
        this.parent(element);
		// todo: инициализация слушателя изменения pid - обновлять вкладку характеристик при изменении родителя
        var switchPropTab = function(select){	    
            if(select.options[select.selectedIndex].get('value').contains('catalog_products')){
                this.tabPane.enableTab(PROPS_TAB_IDX);
            }
            else { //modBySD enable disabled tabs(ENGLISH)..ps:probably_incorrect
                //this.tabPane.disableTab(PROPS_TAB_IDX);
            }
            return select;
        }.bind(this);

        switchPropTab($('smap_content')).addEvent('change', function(e){
            switchPropTab($(e.target));
        }.bind(this));
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
