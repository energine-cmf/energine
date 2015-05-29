ScriptLoader.load('Overlay');

var Subscriptions = new Class({

	initialize: function (el) {

		this.element = $(el);

		if (this.element) {
			this.element.getElements('input[type=checkbox]').addEvent('click', this.toggle.bind(this));
		}
	},

	toggle: function(e) {

		var id = $(e.target).getAttribute('value');
		var checked = $(e.target).checked;

		this.showOverlay();

		Energine.request(
			this.element.getAttribute('data-url') + id + '/',
			'',
			function(response) {
				this.overlay.hide();
			}.bind(this),
			function(response) {
				this.overlay.hide();
				if (checked) {
					$(e.target).removeAttribute('checked');
				} else {
					$(e.target).setAttribute('checked', 'checked');
				}
			}.bind(this)
		);

	},

	showOverlay: function() {
		if (!this.overlay) {
			this.overlay = new Overlay(this.element);
		}
		this.overlay.show();
	}

});
