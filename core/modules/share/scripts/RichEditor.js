ScriptLoader.load('ModalBox.js');

var RichEditor = new Class({

    dirty: false,
    fallback_ie: false,

    initialize: function(area) {
        this.area = $(area);
    },

    validateParent: function(range) {
        var element = $(range.parentElement()) || null;
        while ($type(element) == 'element' && element != this.area) {
            element = element.getParent();
        }
        return (element == this.area);
    },

    action: function(cmd, showUI, value) {
        if (window.gecko || this.fallback_ie) return this.fallback(cmd);
        if (!window.ie || document.selection.type == 'Control') return;

        var range = document.selection.createRange();
        if (this.validateParent(range)) {
            range.execCommand(cmd, (showUI || false), value);
            this.dirty = true;
        }
    },

    fallback: function(cmd) {
        // Предполагается наличие textarea.
        switch (cmd) {
            case 'Bold':                this.wrapSelectionWith('strong');                                   break;
            case 'Italic':              this.wrapSelectionWith('em');                                       break;
            case 'InsertOrderedList':   this.wrapSelectionWith('ol');                                       break;
            case 'InsertUnorderedList': this.wrapSelectionWith('ul');                                       break;
            case 'CreateLink':          this.wrapSelectionWith('a', 'href="' + window.prompt('URL') + '"'); break;
            case 'JustifyLeft':         this.wrapSelectionWith('p', 'style="text-align: left;"');         break;
            case 'JustifyCenter':       this.wrapSelectionWith('p', 'style="text-align: center;"');       break;
            case 'JustifyRight':        this.wrapSelectionWith('p', 'style="text-align: right;"');        break;
            case 'JustifyFull':         this.wrapSelectionWith('p', 'style="text-align: justify;"');      break;
            default: // not used
        }
    },

    getSelectionInfo: function() {
        var selection = { start: -1, end: -1 };
        if (window.gecko) {
            selection.start = this.textarea.selectionStart;
            selection.end = this.textarea.selectionEnd;
        }
        else if (window.ie) {
            var range = (this.currentRange)?this.currentRange:document.selection.createRange();
            var dup_range = range.duplicate();
            dup_range.moveToElementText(this.textarea);
            dup_range.setEndPoint('EndToEnd', range);
            selection.start = dup_range.text.length - range.text.length;
            selection.end = selection.start + range.text.length;
        }
        return selection;
    },

    replaceSelectionWith: function(html) {
        var sel = this.getSelectionInfo();
        this.textarea.value = this.textarea.value.substr(0, sel.start) + html + this.textarea.value.substr(sel.end);
    },

    wrapSelectionWith: function(tagName, attrs) {
        attrs = (attrs ? ' '+attrs : '');
        var sel = this.getSelectionInfo();
        var html = this.textarea.value.substr(sel.start, sel.end - sel.start) || '';
        this.replaceSelectionWith('<'+tagName+attrs+'>' + html + '</'+tagName+'>');
    },

    bold:         function() { this.action('Bold'); },
    italic:       function() { this.action('Italic'); },
    olist:        function() { this.action('InsertOrderedList'); },
    ulist:        function() { this.action('InsertUnorderedList'); },
    link:         function() { this.action('CreateLink', true); },
    alignLeft:    function() { this.action('JustifyLeft'); },
    alignCenter:  function() { this.action('JustifyCenter'); },
    alignRight:   function() { this.action('JustifyRight'); },
    alignJustify: function() { this.action('JustifyFull'); },

	imageManager: function() {
        if (window.ie) {
			this.currentRange = document.selection.createRange();
			if (document.selection.type == 'Control' && this.currentRange(0).tagName == 'IMG') {
                var img = this.currentRange(0);
                imageData = {
                    'upl_path': img.src,
                    'upl_name': img.alt,
                    'upl_data': {
                        'width': img.width,
                        'height': img.height
                    },
                    'align': img.align,
                    'hspace': img.hspace,
                    'vspace': img.vspace
                };
				this.insertImage(imageData);
				return;
            }
		}

        ModalBox.open({
            url: this.area.getProperty('componentPath')+'file-library/image-only',
            onClose: this.insertImage.bind(this)
        });
		
    },
    fileLibrary: function() {
        if (window.ie) this.currentRange = document.selection.createRange();
        ModalBox.open({
            url: this.area.getProperty('componentPath')+'file-library',
            onClose: this.insertFileLink.bind(this)
        });
    },

    // private methods
    insertImage: function(imageData) {
		if (!imageData) return;

		ModalBox.open({
            url: this.area.getProperty('componentPath')+'imagemanager',
            onClose: function(image){
				 if ($type(this.currentRange) == 'collection') {
					var controlRange = this.currentRange;
					if (controlRange(0).tagName == 'IMG') {
						var img = controlRange(0);
						img.src = image.filename;
						img.width  = image.width;
						img.height = image.height;
						img.align  = image.align;
						img.hspace = image.hspace;
						img.vspace = image.vspace;
						img.alt    = image.alt;
					}
					this.currentRange.select();
				}
				else {
					if (window.gecko || this.fallback_ie) {
						this.replaceSelectionWith('<img src="'+image.filename+'" width="'+image.width+'" height="'+image.height+'" align="'+image.align+'" hspace="'+image.hspace+'" vspace="'+image.vspace+'" alt="'+image.alt+'" border="0" />');
						this.dirty = true;
						return;
					}

					this.currentRange.select();
					if (this.validateParent(this.currentRange)) {
						var imgStr = '<img src="'+image.filename+'" width="'+image.width+'" height="'+image.height+'" align="'+image.align+'" hspace="'+image.hspace+'" vspace="'+image.vspace+'" alt="'+image.alt+'" border="0" />';
						this.currentRange.pasteHTML(imgStr);
						this.dirty = true;
					}
				}
			}.bind(this),
            extraData: imageData
        });
    },
    insertFileLink: function(data) {
        if (!data) return;
        var filename = data['upl_path'];

        if (window.gecko) {
            this.wrapSelectionWith('a', 'href="'+filename+'"');
            this.dirty = true;
            return;
        }

        this.currentRange.select();
        if (this.validateParent(this.currentRange)) {
            if (this.currentRange.text != '') {
                this.currentRange.pasteHTML('<a href="'+filename+'">'+this.currentRange.text+'</a>');
            }
            else {
                this.currentRange.pasteHTML('<a href="'+filename+'">'+filename+'</a>');
            }
            this.dirty = true;
        }
    },
    processPaste: function(event) {
        var orig_tr = document.selection.createRange();
        var new_tr = document.body.createTextRange();

        this.pasteArea.innerHTML = 'dummy text';
        new_tr.moveToElementText(this.pasteArea);
        new_tr.select();
        document.execCommand('paste', false, null);

        orig_tr.select();
        orig_tr.pasteHTML(this.cleanMarkup(this.area.getProperty('componentPath'), this.pasteArea.innerHTML, true));
        this.pasteArea.setHTML('');
        event.stop();
    },

    cleanMarkup: function(path, data, aggressive) {
        var ajax = new Ajax(path + 'cleanup' + (aggressive ? '?aggressive=1' : ''), {
            method: 'post',
            postBody: 'data='+encodeURIComponent(data),
            async: false
        }).request();
        return ajax.transport.responseText;
    },
	changeFormat: function(control){
		var selectedOption = control.select.value;
		if (selectedOption == 'reset'){
			document.execCommand("FormatBlock", false, '<P>');
		}
		else {
			document.execCommand("FormatBlock", false, '<'+selectedOption+'>');
		}
		control.select.value = '';
	}

});
