ScriptLoader.load('Form');

var AdsItemForm = new Class({

	Extends: Form,

	initialize: function (el) {
		this.parent(el);
		this.prepareFilterProperties();
		this.element.getElementById('ads_item_mode').addEvent('change', this.prepareFilterProperties.bind(this));
	},

	prepareFilterProperties: function () {
		var filter, mode, name;
		var data = {'image': ['ads_item_url', 'ads_item_img'], 'html' : ['ads_item_html']};

		if ((filter = this.element.getElementById('ads_item_mode')) && (mode = filter.get('value'))) {
			this.element.getElements('input,textarea').each(function(el) {
				name = $(el).get('name');
				Object.each(data, function(fields, m) {
					Array.each(fields, function(field) {
						if (name.indexOf(field) != -1) {
							if (m == mode) {
								$(el).getParents('div.field').show();
							} else {
								$(el).getParents('div.field').hide();
							}
						}
					});
				});
			});
		}
	}

});