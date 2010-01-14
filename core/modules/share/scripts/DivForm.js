ScriptLoader.load('Form.js', 'ModalBox.js');
var DivForm = new Class({
	Extends: Form,
    Implements: [Form.Label, Form.Attachments],
	initialize: function(element){
		this.parent(element);
        this.obj = null;
	},
    attachToolbar : function(toolbar) {
        this.parent(toolbar);
        var afterSaveActionSelect; 
        if(afterSaveActionSelect = this.toolbar.getControlById('after_save_action')){
            var savedActionState = Cookie.read('after_add_default_action');
            if(savedActionState){
                afterSaveActionSelect.setSelected(savedActionState);
            }
        }
    },
    showTree: function(obj) {
        this.obj = obj;
        ModalBox.open({
            url: this.singlePath+'/list',
            onClose: this.setLabel.bind(this),
            extraData: { disabledNode: this.form.getElement('#smap_id').value } // restrictSubtree
        });
    },
    /*showInternalRedirect: function(obj) {
        this.obj = obj;
        ModalBox.open({
            url: this.singlePath+'/list',
            onClose: function(result){
				console.log(result);
            }.bind(this)
        });
    },*/
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
            alert('Ошибка: необходимо указать название раздела для всех не отключенных языков!');
            return false;
        }
        
        
        
        this.request(
            this.singlePath + 'save',
            this.form.toQueryString(),
            function(response) {
                if (response.mode == 'insert') {
                    var nextActionSelector;
                    if(nextActionSelector = this.toolbar.getControlById('after_save_action')){
                        Cookie.write('after_add_default_action', nextActionSelector.getValue(), {path:new URI(Energine.base).get('directory'), duration:1});
                        switch (nextActionSelector.getValue()){
                            case 'go':
                                window.top.location.href = Energine.base + response.url;
                                break;
                            case 'close':
                                ModalBox.setReturnValue(true); this.close();
                                break;
                        }
                    }                    
                }
                else {
                    ModalBox.setReturnValue(true); this.close();
                }
            }.bind(this)
        );
    }
});
