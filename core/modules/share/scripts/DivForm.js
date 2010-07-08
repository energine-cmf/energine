ScriptLoader.load('Form', 'ModalBox');
var DivForm = new Class({
	Extends: Form,
    Implements: [Form.Label],
	initialize: function(element){
		this.parent(element);
        this.prepareLabel($('site_id').get('value') + '/list/');

	},
    save: function() {
        this.richEditors.each(function(editor) { editor.onSaveForm(); });
        if (!this.validator.validate()) {
            return false;
        }

        var tabs = this.tabPane.getTabs();
        var valid = true;
        tabs.each(function(tab) {
            if (tab.data.lang) {
                var checkbox = tab.pane.getElement('input[type="checkbox"]');
                var disabled = checkbox.name.test(/share_sitemap_translation\[\d+\]\[smap_is_disabled\]/) ? checkbox.checked : false;
                if (!disabled) {
                    if (tab.pane.getElement('input[type="text"]').value.trim().length == 0) {
                        valid = false;
                    }
                }
            }
        });

        
        if (!valid) {
            alert(Energine.translations.get('ERR_NO_DIV_NAME'));
            return false;
        }
        this.request(
            this.singlePath + 'save',
            this.form.toQueryString(), 
            this.processServerResponse.bind(this)
        );
    }
});
