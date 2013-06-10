ScriptLoader.load('ModalBox');

var RichEditor = new Class({

    dirty: false,
    selection: null,
    stored_selection: null,
    busy: false,

    initialize:function (area) {
        this.area = $(area);
        this.isActive = false;
        this.selection = new RichEditor.Selection(window);
    },

    monitorElements:function () {

        this.area.getElements('*').removeEvents('click');
        this.area.removeEvents('keyup');
        this.area.removeEvents('mouseup');

        var monitorFunction = function (event) {
            if (this.isActive) {
                event.stopPropagation();
            }
        }.bind(this);

        this.area.getElements('*').addEvent('click', monitorFunction);
        this.area.getElements('*').addEvent('click', this.onSelectionChanged.bind(this));
        this.area.addEvent('keyup', this.onSelectionChanged.bind(this));
        this.area.addEvent('mouseup', this.onSelectionChanged.bind(this));
    },

    activate:function () {
        this.isActive = this.area.contentEditable = true;
        this.monitorElements();
        this.onSelectionChanged(false);
    },

    deactivate:function () {
        this.isActive = this.area.contentEditable = false;
    },

    action:function (cmd, showUI, value) {

        if (this.busy) return;
        this.busy = true;

        if (Browser.Engine.gecko) {
            document.execCommand('styleWithCSS', false, true);
        }

        try {
            document.execCommand(cmd, (showUI || false), (value || null));
        }
        catch (e) {
        }

        this.dirty = true;
        this.busy = false;

        this.onSelectionChanged(false);
    },

    bold:function () {
        this.action('Bold');
    },

    italic:function () {
        this.action('Italic');
    },

    olist:function () {
        this.action('InsertOrderedList');
    },

    ulist:function () {
        this.action('InsertUnorderedList');
    },

    link:function () {
        var link = prompt('URL:', 'http://');
        if (link) {
            var text = this.selection.getText() || link;
            var a = new Element('a', {href: link, html: text});
            this.selection.insertContent(new Element('div').adopt(a).get('html'));
            this.onSelectionChanged(false);
        }
    },

    alignLeft:function () {
        this.action('JustifyLeft');
    },

    alignCenter:function () {
        this.action('JustifyCenter');
    },

    alignRight:function () {
        this.action('JustifyRight');
    },

    alignJustify:function () {
        this.action('JustifyFull');
    },

    imageManager:function () {
        this.stored_selection = this.selection.storeCurrentSelection();
        var n = this.selection.getNode();
        if (n && n.tagName.toLowerCase() == 'img') {
            this.insertImage({
                'upl_path': n.getProperty('src'),
                'upl_width': n.getProperty('width'),
                'upl_height': n.getProperty('height'),
                'align': n.getProperty('align'),
                'upl_title': n.getProperty('alt'),
                'margin-top': n.getStyle('margin-top').toInt(),
                'margin-bottom': n.getStyle('margin-bottom').toInt(),
                'margin-left': n.getStyle('margin-left').toInt(),
                'margin-right': n.getStyle('margin-right').toInt()
            });
        } else {
            ModalBox.open({
                url: this.area.getProperty('single_template') + 'file-library/image/',
                onClose: this.insertImage.bind(this)
            });
        }
    },

    insertImageURL:function () {
        this.action('insertImage');
    },

    fileLibrary:function () {
        this.stored_selection = this.selection.storeCurrentSelection();
        ModalBox.open({
            url: this.area.getProperty('single_template') + 'file-library',
            onClose: this.insertFileLink.bind(this)
        });
    },

    // private methods
    insertImage:function (imageData) {
        if (!imageData) return;

        if (this.stored_selection) {
            this.selection.restoreSelection(this.stored_selection);
        }

        ModalBox.open({
            url: this.area.getProperty('single_template') + 'imagemanager',
            onClose: function (image) {

                if (!image) return;

                image.filename = Energine.media + image.filename;

                var imgStr = '<img src="'
                    + image.filename + '" width="'
                    + image.width + '" height="'
                    + image.height + '" align="'
                    + image.align + '" alt="'
                    + image.alt + '" border="0" style="';

                ['margin-left', 'margin-right', 'margin-top', 'margin-bottom'].each(function (marginProp) {
                    if (image[marginProp] != 0) {
                        imgStr += marginProp + ':' + image[marginProp] +
                            'px;';
                    }
                });

                imgStr += '"/>';

                if (this.stored_selection) {
                    this.selection.restoreSelection(this.stored_selection);
                }

                this.selection.insertContent(imgStr);

                this.dirty = true;
                this.monitorElements();

            }.bind(this),
            extraData: imageData
        });
    },

    insertFileLink:function (data) {

        if (!data) return;

        if (this.stored_selection) {
            this.restoreSelection(this.stored_selection);
        }

        var filename = data['upl_path'];

        var text = this.selection.getText();

        this.selection.insertContent('<a href="' + filename + '">' + text + '</a>');

        this.dirty = true;

    },

    /*
    insertExtFlash:function () {
        this.currentRange = false;
        if (Energine.supportContentEdit)
            this.currentRange = this._getSelection().createRange();

        ModalBox.open({
            onClose:function (result) {
                if (result && result.result) {
                    result = result.result;
                    if (this.currentRange.select)
                        this.currentRange.select();

                    if (this.validateParent(this.currentRange)) {
                        // IE
                        if (this.currentRange.pasteHTML) {
                            if (this.currentRange.text != '') {
                                this.currentRange.pasteHTML(result);
                            } else {
                                this.currentRange.pasteHTML(result);
                            }
                        }
                        // FF
                        else {
                            document.execCommand('inserthtml', false, result);
                        }
                        this.dirty = true;
                    }
                }
            }.bind(this),
            'form':{
                title:Energine.translations.get('TXT_INSERT_EMBED_CODE'),
                field:{
                    'name':'source',
                    'type':'textarea',
                    'title':Energine.translations.get('FIELD_EMBED_CODE')
                }

            }
        });
    },

    processPaste:function (event) {
        var selection = this._getSelection();

        var orig_tr = selection.createRange();
        var new_tr = document.body.createTextRange();

        this.pasteArea.innerHTML = '';
        new_tr.moveToElementText(this.pasteArea);
        new_tr.select();
        document.execCommand('paste', false, null);
        orig_tr.select();

        orig_tr.pasteHTML(this.cleanMarkup(this.area
            .getProperty('componentPath'),
            this.pasteArea.innerHTML, true));

        this.pasteArea.innerHTML = '';
    },

    processPasteFF:function (event) {
        (function () {
            this.area.innerHTML = this.cleanMarkup(this.area
                .getProperty('componentPath'),
                this.area.innerHTML, true);

        }).delay(300, this);
    },
*/
    cleanMarkup:function (path, data, aggressive) {
        var result;
        new Request({
            url:path + 'cleanup'
                + (aggressive ? '?aggressive=1' : ''),
            method:'post',
            async:false,
            onSuccess:function (responseText) {
                result = responseText;
            }
        }).send('data=' + encodeURIComponent(data));
        return result;
    },

    changeFormat:function (control) {
        var selectedOption = control.select.value;
        var tag = (selectedOption == 'reset') ? '<P>' : '<' + selectedOption + '>';
        this.action("FormatBlock", false, tag);
        control.select.value = '';
    },

    onSelectionChanged: function(e) {}
});

RichEditor.Selection = new Class({

    initialize: function(win){
        this.win = win;
    },

    getSelection: function(){
        this.win.focus();
        return (this.win.getSelection) ? this.win.getSelection() : this.win.document.selection;
    },

    getRange: function(){
        var s = this.getSelection();

        if (!s) return null;

        try {
            return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
        } catch(e) {
            // IE bug when used in frameset
            return this.doc.body.createTextRange();
        }
    },

    setRange: function(range){
        if (range.select){
            Function.attempt(function(){
                range.select();
            });
        } else {
            var s = this.getSelection();
            if (s.addRange){
                s.removeAllRanges();
                s.addRange(range);
            }
        }
    },

    selectNode: function(node, collapse){
        var r = this.getRange();
        var s = this.getSelection();

        if (r.moveToElementText){
            Function.attempt(function(){
                r.moveToElementText(node);
                r.select();
            });
        } else if (s.addRange){
            collapse ? r.selectNodeContents(node) : r.selectNode(node);
            s.removeAllRanges();
            s.addRange(r);
        } else {
            s.setBaseAndExtent(node, 0, node, 1);
        }

        return node;
    },

    isCollapsed: function(){
        var r = this.getRange();
        if (r.item) return false;
        return r.boundingWidth == 0 || this.getSelection().isCollapsed;
    },

    collapse: function(toStart){
        var r = this.getRange();
        var s = this.getSelection();

        if (r.select){
            r.collapse(toStart);
            r.select();
        } else {
            toStart ? s.collapseToStart() : s.collapseToEnd();
        }
    },

    getContent: function(){
        var r = this.getRange();
        var body = new Element('body');

        if (this.isCollapsed()) return '';

        if (r.cloneContents){
            body.appendChild(r.cloneContents());
        } else if (r.item != undefined || r.htmlText != undefined){
            body.set('html', r.item ? r.item(0).outerHTML : r.htmlText);
        } else {
            body.set('html', r.toString());
        }

        var content = body.get('html');
        return content;
    },

    getText : function(){
        var r = this.getRange();
        var s = this.getSelection();
        return this.isCollapsed() ? '' : r.text || (s.toString ? s.toString() : '');
    },

    getNode: function(){
        var r = this.getRange();

        if (!Browser.ie || Browser.version >= 9){
            var el = null;

            if (r){
                el = r.commonAncestorContainer;

                // Handle selection a image or other control like element such as anchors
                if (!r.collapsed)
                    if (r.startContainer == r.endContainer)
                        if (r.startOffset - r.endOffset < 2)
                            if (r.startContainer.hasChildNodes())
                                el = r.startContainer.childNodes[r.startOffset];

                while (typeOf(el) != 'element') el = el.parentNode;
            }

            return document.id(el);
        }

        return document.id(r.item ? r.item(0) : r.parentElement());
    },

    insertContent: function(content){
        if (Browser.ie){
            var r = this.getRange();
            if (r.pasteHTML){
                r.pasteHTML(content);
                r.collapse(false);
                r.select();
            } else if (r.insertNode){
                r.deleteContents();
                if (r.createContextualFragment){
                    r.insertNode(r.createContextualFragment(content));
                } else {
                    var doc = this.win.document;
                    var fragment = doc.createDocumentFragment();
                    var temp = doc.createElement('div');
                    fragment.appendChild(temp);
                    temp.outerHTML = content;
                    r.insertNode(fragment);
                }
            }
        } else {
            this.win.document.execCommand('insertHTML', false, content);
        }
    },

    // сохраняет и возвращает текущий selection активного контейнера
    storeCurrentSelection: function () {
        if (window.getSelection) {
            var selection = window.getSelection();
            if (selection.rangeCount > 0) {
                var selectedRange = selection.getRangeAt(0);
                return selectedRange.cloneRange();
            }
            else {
                return null;
            }
        }
        else if (document.selection) {
            var selection = document.selection;
            if (selection.type.toLowerCase() == 'text') {
                return selection.createRange().getBookmark();
            }
            else if (selection.type.toLowerCase() == 'none' && Browser.ie) {
                var rng = document.selection.createRange();
                rng.text="_";
                this.setSelectionRange(-1,0);
                this._no_select = false;

                return selection.createRange().getBookmark();
            }
            else
                return null;
        }
        else {
            return null;
        }
    },

    setSelectionRange: function(start, end) {
        var range = document.selection.createRange();
        range.collapse(true);
        range.moveStart("character", start);
        range.moveEnd("character", end);
        range.select();
    },

    restoreSelection: function (storedSelection) {
        if (storedSelection) {
            if (window.getSelection) {
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(storedSelection);
            }
            else if (document.selection && document.body.createTextRange) {
                var range = document.body.createTextRange();
                range.moveToBookmark(storedSelection);
                range.select();
            }
        }
    }

});