ScriptLoader.load('Form');
var FeatureForm = new Class({
    Extends: Form,

    initialize: function (el) {
        this.parent(el);
        this.prepareFilterProperties();
        this.element.getElementById('feature_is_filter').addEvent('change', this.prepareFilterProperties.bind(this));
		this.element.getElementById('feature_type').addEvent('change', this.prepareFilterProperties.bind(this));	
    },

    prepareFilterProperties: function () {
        var filter, feature_type;

        if ((filter = this.element.getElementById('feature_is_filter')) && filter.checked) {
            this.element.getElementById('feature_sysname').getParent('.field').show();
            this.element.getElementById('feature_filter_type').getParent('.field').show();
        }
        else {
            this.element.getElementById('feature_sysname').getParent('.field').hide();
            this.element.getElementById('feature_filter_type').getParent('.field').hide();
        }

		if (feature_type = this.element.getElementById('feature_type')) {

			// поиск индекса вкладки с перечисляемыми значениями
			var tabs = this.tabPane.getTabs();
			var tabIndex = null;
			tabs.each(function(t, idx) {
				if (t.hasAttribute('data-src') && t.getAttribute('data-src').indexOf('feature') != -1) {
					tabIndex = idx;
				}
			});

			if (tabIndex != null && ['OPTION','MULTIOPTION','VARIANT'].indexOf(feature_type.get('value')) >= 0) {
				// показываем вкладку Перечисляемые значения
				this.tabPane.enableTab(tabIndex);
			} else {
				// прячем вкладку Перечисляемые значения
				this.tabPane.disableTab(tabIndex);
			}
		}
    }
});