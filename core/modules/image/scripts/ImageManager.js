ScriptLoader.load('Form.js', 'ModalBox.js');

var ImageManager = new Class({
	Extends: Form,
    initialize: function(objID) {
        this.parent(objID);
        this.image = {};
		$('filename').disabled = true;
        var imageData = ModalBox.getExtraData();
        if (imageData != null) {
            this.image = imageData;
            this.updateForm();
        }
    },

    openImageLib: function() {
        ModalBox.open({
            url: this.singlePath+'file-library/image-only',
            onClose: function(result) {
                if (result) {
                    this.image = result;
                    this.updateForm();
                }
                window.focus();
            }.bind(this)
        });
    },

    updateForm: function() {
		if (this.image.insertThumbnail) {
			$('filename').value = this.image.thumbnail;
			$('thumbnail').src  = this.image.filename;
		}
		else {
			$('filename').value = this.image['upl_path'];
			$('thumbnail').src  = this.image['upl_data'].thumb;
		}
		$('width').value  = this.image['upl_data'].width || 0;
		$('height').value = this.image['upl_data'].height || 0;
		$('align').value  = $('align').value  || this.image.align || '';
		$('hspace').value = $('hspace').value || this.image.hspace || '0';
		$('vspace').value = $('vspace').value || this.image.vspace || '0';
		$('alt').value    = $('alt').value    || this.image['upl_name'] || '';
		$('insThumbnail').checked = this.image.insertThumbnail;
    },

    insertImage: function() {
        if ($('filename').value) {
            this.image.filename  = $('filename').value;
            this.image.width     = parseInt($('width').value) || '';
            this.image.height    = parseInt($('height').value) || '';
            this.image.align     = $('align').value || '';
            this.image.hspace    = parseInt($('hspace').value) || 0;
            this.image.vspace    = parseInt($('vspace').value) || 0;
            this.image.alt       = $('alt').value;
            this.image.thumbnail = $('thumbnail').src;
            this.image.insertThumbnail = $('insThumbnail').checked;
            ModalBox.setReturnValue(this.image)
        }
        this.close();
    }
});