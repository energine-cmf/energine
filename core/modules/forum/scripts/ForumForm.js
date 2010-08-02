ScriptLoader.load('Form');

var ForumForm = new Class({
    Extends: Form,
    initialize: function(element) {
        Asset.css('form.css');
		this.componentElement = $(element);
		this.singlePath = this.componentElement.getProperty('single_template');

        this.form = this.componentElement.getParent('form').addClass('form');

		this.validator = new Validator(this.form);

		this.richEditors = [], this.uploaders = [], this.textBoxes = [], this.dateControls = [];

        this.form.getElements('textarea.richEditor').each(function(textarea) {
            var e = new Form.RichEditor(textarea, this, this.fallback_ie);
            ['imgmngr','filelib','sep3','source','sep2'].each(function(control){
                e.toolbar.removeControl(control)
            });
			this.richEditors.push(e);
		}, this);
    }
});