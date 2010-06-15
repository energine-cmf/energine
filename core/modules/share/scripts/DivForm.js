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
            function(response) {
                ModalBox.setReturnValue(true); 
                if (response.mode == 'insert') {
                    var nextActionSelector;
                    if(nextActionSelector = this.toolbar.getControlById('after_save_action')){
                        Cookie.write('after_add_default_action', nextActionSelector.getValue(), {path:new URI(Energine.base).get('directory'), duration:1});
                        switch (nextActionSelector.getValue()){
                            case 'go':
                                window.top.location.href = Energine.base + response.url;
                                break;
                            case 'add':
                                    ModalBox.setReturnValue('add'); 
                                    break;                                
                            case 'close':
                                break;
                        }
                    }                    
                }
                this.close();
            }.bind(this)
        );
    }
});
