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

        showUI = showUI || false;
        value = value || null;

        if (Browser.Engine.gecko) {
            document.execCommand('styleWithCSS', false, true);
        }

        try {
            document.execCommand(cmd, showUI, value);
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
        this.stored_selection = this.selection.storeCurrentSelection();
        var link = prompt('URL:', 'http://');
        if (link) {
            if (this.stored_selection) {
                this.selection.restoreSelection(this.stored_selection);
            }
            if (Browser.ie) {
                this.action('CreateLink', false, link);
            } else {
                var text = this.selection.getText() || link;
                var a = new Element('a', {href: link, html: text});
                this.selection.insertContent(new Element('div').adopt(a).get('html'));
            }
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
        var url = prompt("URL:");
        if (url) {
            this.action('insertImage', false, url);
        }
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

                if (image.filename.toLowerCase().indexOf('http://') == -1) {
                    image.filename = Energine.media + image.filename;
                }

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

        if (this.stored_selection) {
            this.selection.restoreSelection(this.stored_selection);
        }

        if (!data) return;

        var filename = data['upl_path'];

        if (filename.toLowerCase().indexOf('http://') == -1) {
            filename = Energine.media + filename;
        }

        if (Browser.ie) {
            this.action('CreateLink', false, filename);
        } else {
            var text = this.selection.getText();
            this.selection.insertContent('<a href="' + filename + '">' + ((text) ? text : filename) + '</a>');
        }

        this.dirty = true;

    },

    insertExtFlash:function () {

        this.stored_selection = this.selection.storeCurrentSelection();

        ModalBox.open({
            onClose:function (result) {
                if (result && result.result) {
                    result = result.result;
                    if (this.stored_selection) this.selection.restoreSelection(this.stored_selection);
                    this.selection.insertContent(result);
                    this.dirty = true;

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

    processPasteFF:function (event) {
        (function () {
            this.area.innerHTML = this.cleanMarkup(this.area
                .getProperty('componentPath'),
                this.area.innerHTML, true);

        }).delay(300, this);
    },

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

    beforeChangeFormat: function (control) {
        try { this.area.setActive(); } catch (e) {}
        if (!Browser.ie) {
            this.stored_selection = this.selection.storeCurrentSelection();
        }
    },

    changeFormat:function (control) {

        var selectedOption = control.getValue();
        control.setSelected('');
        if (!selectedOption) return;
        if (selectedOption['value'] == '') return;

        if (this.stored_selection && !Browser.ie) {
            this.selection.restoreSelection(this.stored_selection);
        }

        try { this.area.setActive(); } catch (e) {}

        // сброс форматирования
        if (selectedOption['value'] == 'reset') {
            var node = this.selection.getNode();
            if (node) {
                if (!Browser.ie) {
                    this.selection.selectNode(node, false);
                }
                var html = '<P>' + node.get('html') + '</P>';
                this.selection.insertContent(html);
            } else {
                this.action("FormatBlock", false, '<P>');
            }
        }
        // применение стандартных тегов h1-h6, address, ...
        else if (['h1','h2','h3','h4','h5','h6','address'].contains(selectedOption['value'])) {
            var tag = '<' + selectedOption['element'].toUpperCase() + '>';
            this.action("FormatBlock", false, tag);
        }
        // применение кастомных стилей
        else {
            var node = this.selection.getNode();
            if (node) {
                this.selection.selectNode(node, false);
                var html = '<' + selectedOption['element'] + ' class="'+ selectedOption['class'] + '"' + '>'
                    + node.get('html') +
                    '</' + selectedOption['element'] + '>';
                this.selection.insertContent(html);
            } else {
                var html = '<' + selectedOption['element'] + ' class="'+ selectedOption['class'] + '"' + '>'
                    + this.selection.getText() +
                    '</' + selectedOption['element'] + '>';
                this.selection.insertContent(html);
            }
        }
    },

    getAllowedFormatTags: function() {
        return ['b', 'i', 'strong', 'em', 'p', 'p', 'div', 'span', 'ul',
            'ol', 'li', 'h1','h2','h3','h4','h5','h6', 'pre', 'address',
            'dir', 'menu', 'dl', 'dt', 'object', 'param'];
    },

    getAllParentElements: function(el) {
        var els = [el];
        var allowed = this.getAllowedFormatTags();

        var found = false;
        var parentHandler = function(item) {
            var tag = item.tagName.toLowerCase();
            if (allowed.contains(tag) && !found) {
                if ($(item).hasClass('activeEditor')) {
                    found = true;
                    return false;
                }
                els.push(item);
            }
        };

        if (el) {
            if (Browser.ie && el.each) {
                el.each(parentHandler.bind(this));
            }
            el.getParents().each(parentHandler.bind(this));
        }

        return els;
    },

    onSelectionChanged: function(e) {}
});

RichEditor.Selection = new Class({

    initialize: function(win){
        this.win = win;
    },

    getSelection: function(){
        //this.win.focus();
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

        try {
            var r = this.getRange();
            if (r.pasteHTML){
                r.pasteHTML(content);
                r.collapse(false);
                r.select();
            } else if (r.insertNode && !Browser.ie){
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
            } else if (document.selection && document.selection.type != "Control") {
                // IE < 9
                var getCommonAncestor = function(node1, node2) {
                    var method = "contains" in node1 ? "contains" : "compareDocumentPosition",
                        test   = method === "contains" ? 1 : 0x10;

                    while (node1 = node1.parentNode) {
                        if ((node1[method](node2) & test) === test)
                            return node1;
                    }

                    return null;
                }

                var getTextRangeContainerElement = function(textRange) {
                    var parentEl = textRange.parentElement();

                    var range = textRange.duplicate();
                    range.collapse(true);
                    var startEl = range.parentElement();

                    range = textRange.duplicate();
                    range.collapse(false);
                    var endEl = range.parentElement();

                    var startEndContainer = (startEl == endEl) ?
                        startEl : getCommonAncestor(startEl, endEl);

                    return startEndContainer == parentEl ?
                        startEndContainer : getCommonAncestor(parentEl, startEndContainer);
                };

                range = document.selection.createRange();
                var el = range.parentElement();//getTextRangeContainerElement(range);
                if (el) {
                    var new_el = document.createElement('div');
                    new_el.innerHTML = content;
                    el.parentElement.replaceChild(new_el, el);
                    this.selectNode(new_el);
                } else {
                    range.pasteHTML(content);
                }
                //range = document.selection.createRange();
                //range.expand();
                //range.pasteHTML(content);
            } else {
                this.win.document.execCommand('insertHTML', false, content);
            }
        } catch (e) {
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