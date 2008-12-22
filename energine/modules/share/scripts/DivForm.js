ScriptLoader.load('Form.js', 'ModalBox.js');

var DivForm = Form.extend({

	initialize: function(element){
		this.parent(element);
        this.obj = null;
	},

    showTree: function(obj) {
        this.obj = obj;
        ModalBox.open({
            url: this.singlePath+'/list',
            onClose: this.setLabel.bind(this),
            extraData: { disabledNode: this.form.getElement('#smap_id').value } // restrictSubtree
        });
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
            alert('Ошибка: необходимо указать название раздела для всех не отключенных языков!');
            return false;
        }

        this.request(
            this.singlePath + 'save',
            this.form.toQueryString(),
            function(response) {
                if (response.mode == 'insert' && confirm(MSG_START_EDITING)) {
                    window.top.location.href = $E('base', window.top.document.head).getProperty('href')+response.url;
                }
                else {
                    ModalBox.setReturnValue(true); this.close();
                }
            }.bind(this)
        );
    }
});

DivForm.implement(Label);