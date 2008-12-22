ScriptLoader.load('Form.js', 'ModalBox.js');

var FileForm = Form.extend({

    initialize: function(element){
        this.parent(element);
    },

    upload: function(fileField) {
        var iframe;
        
        if (window.ie) {
            iframe = $(document.createElement('<iframe name="uploader" id="uploader">'));
        }
        else {
            iframe = new Element('iframe').setProperties({ name: 'uploader', id: 'uploader' });
        }
        iframe.setStyles({ width: 0, height: 0, border: 0 }).injectBefore(this.form);
        iframe.filename = $(fileField.getAttribute('link'));
        iframe.preview = $(fileField.getAttribute('preview'));
        var path = new Element('input').setProperty('name', 'path').setProperties({ 'id': 'path', 'type': 'hidden', 'value': ModalBox.getExtraData() }).injectInside(this.form);
        var progressBar = new Element('img').setProperties({ id: 'progress_bar', src: 'images/loading.gif' }).injectAfter(fileField);
        this.form.setProperties({ action: this.componentElement.getProperty('single_template')+'upload', target: 'uploader' });
        this.form.submit();
        this.form.setProperty('target', '_self');
    },

    saveDir: function() {
        if (!this.validator.validate()) {
            return false;
        }

        this.request(
            this.componentElement.getProperty('single_template')+'save-dir',
            this.form.toQueryString()+'&path='+ModalBox.getExtraData(),
            function() { ModalBox.setReturnValue(true); this.close(); }.bind(this)
        );
    }
});