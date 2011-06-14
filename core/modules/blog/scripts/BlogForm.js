ScriptLoader.load('Form');

var BlogForm = new Class({
    Extends: Form,
    initialize: function(element) {
        Asset.css('form.css');
		this.componentElement = $(element);
		this.singlePath = this.componentElement.getProperty('single_template');

        this.form = this.componentElement.getParent('form').addClass('form');

		this.validator = new Validator(this.form);

		this.richEditors = [], this.uploaders = [], this.textBoxes = [], this.dateControls = [];

		this.form.getElements('textarea.richEditor').each(function(textarea) {
			this.richEditors.push(new Form.RichEditor(textarea, this,
					this.fallback_ie));

		}, this);
    }
});