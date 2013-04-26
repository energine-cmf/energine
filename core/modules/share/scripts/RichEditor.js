ScriptLoader.load('ModalBox');
var RichEditor = new Class({

    dirty:false,
    fallback_ie:false,
    stored_selection: false,

    initialize:function (area) {
        this.area = $(area);
        this.isActive = false;
        //Текущий выделенный объект
        //Используется для хранения информации о выделенном имидже для его редактирования
        this.selectedObject = false;
    },
    monitorElements:function () {
        this.area.getElements('*').removeEvents('click');

        //если редактор активизирован - отменяем всплытие
        //Для всех имеджей при клике(выделении) сохраняем инфу в selectedObject
        //для всех остальных елементов - очищаем selectedObject
        var monitorFunction = function (event) {
            if (this.isActive) {
                var element = $(event.target);
                if (element.get('tag') == 'img') {
                    this.selectedObject = element;
                }
                else {
                    this.selectedObject = false;
                }
                event.stopPropagation();
            }
        }.bind(this);
        this.area.getElements('*').addEvent('click', monitorFunction);
    },
    activate:function () {
        this.isActive = this.area.contentEditable = true;
        this.monitorElements();
    },
    deactivate:function () {
        this.isActive = this.area.contentEditable = false;
    },
    validateParent:function (range) {
        var element = $(range.parentElement()) || null;
        while ($type(element) == 'element' && element != this.area) {
            element = element.getParent();
        }
        return (element == this.area);
    },
    action:function (cmd, showUI, value) {
        if (/* Browser.Engine.gecko || */this.fallback_ie)
            return this.fallback(cmd);
        var selection = this._getSelection();
        if (!Energine.supportContentEdit || selection.type == 'Control')
            return;

        if (Browser.Engine.gecko) {
            document.execCommand('styleWithCSS', false, false);
        }

        var range = selection.createRange();
        if (this.validateParent(range)) {
            if (isset(range.execCommand))
                range.execCommand(cmd, (showUI || false), value);
            else {
                if ((cmd == 'CreateLink') || (cmd == 'insertImage')) {
                    value = prompt("Enter a URL:", "http://");
                    showUI = false;
                }
                try {
                    document.execCommand(cmd, (showUI || false), value);
                }
                catch (e) {
                }

            }
            this.dirty = true;
        }
    },

    fallback:function (cmd) {
        switch (cmd) {
            case 'Bold' :
                this.wrapSelectionWith('strong');
                break;
            case 'Italic' :
                this.wrapSelectionWith('em');
                break;
            case 'InsertOrderedList' :
                this.wrapSelectionWith('ol');
                break;
            case 'InsertUnorderedList' :
                this.wrapSelectionWith('ul');
                break;
            case 'CreateLink' :
                this.wrapSelectionWith('a', 'href="'
                    + window.prompt('URL') + '"');
                break;
            case 'JustifyLeft' :
                this
                    .wrapSelectionWith('p',
                    'style="text-align: left;"');
                break;
            case 'JustifyCenter' :
                this.wrapSelectionWith('p',
                    'style="text-align: center;"');
                break;
            case 'JustifyRight' :
                this.wrapSelectionWith('p',
                    'style="text-align: right;"');
                break;
            case 'JustifyFull' :
                this.wrapSelectionWith('p',
                    'style="text-align: justify;"');
                break;
            default : // not used
        }
    },
    replaceSelectionWith:function (html) {
        var sel = this.textarea.getSelectedRange();
        this.textarea.value = this.textarea.value.substr(0, sel.start)
            + html + this.textarea.value.substr(sel.end);
    },

    wrapSelectionWith:function (tagName, attrs) {
        attrs = (attrs ? ' ' + attrs : '');
        this.textarea.insertAroundCursor({before:'<' + tagName + attrs +
            '>', defaultMiddle:'', after:'</' + tagName + '>'});
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
        this.action('CreateLink', true);
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
        this.currentRange = false;

        this.stored_selection = this.storeCurrentSelection();

        if (Energine.supportContentEdit && !this.fallback_ie) {
            this.currentRange = this._getSelection().createRange();
        }

        if (!this.selectedObject) {
            ModalBox.open({
                url:this.area.getProperty('single_template') + 'file-library/image/',
                onClose:this.insertImage.bind(this)
            });
        }
        else {
            this.insertImage({
                'upl_path':this.selectedObject.getProperty('src'),
                'upl_width':this.selectedObject.getProperty('width'),
                'upl_height':this.selectedObject.getProperty('height'),
                'align':this.selectedObject.getProperty('align'),
                'upl_title':this.selectedObject.getProperty('alt'),
                'margin-top':this.selectedObject.getStyle('margin-top').toInt(),
                'margin-bottom':this.selectedObject.getStyle('margin-bottom').toInt(),
                'margin-left':this.selectedObject.getStyle('margin-left').toInt(),
                'margin-right':this.selectedObject.getStyle('margin-right').toInt()
            });
        }
    },

    insertImageURL:function () {
        this.action('insertImage');
    },

    fileLibrary:function () {

        this.stored_selection = this.storeCurrentSelection();

        this.currentRange = false;
        if (Energine.supportContentEdit && !this.fallback_ie)
            this.currentRange = this._getSelection().createRange();

        ModalBox.open({
            url:this.area.getProperty('single_template')
                + 'file-library',
            onClose:this.insertFileLink.bind(this)
        });
    },

    // private methods
    insertImage:function (imageData) {
        if (!imageData)
            return;

        if (this.stored_selection) {
            this.restoreSelection(this.stored_selection);
        }

        ModalBox.open({
            url:this.area.getProperty('single_template') + 'imagemanager',
            onClose:function (image) {
                //TODO Fix image margins in IE
                if (!image) return;
                if (!Browser.chrome && !this.fallback_ie) {
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
                    this.restoreSelection(this.stored_selection);
                    this.pasteHtml(imgStr);
                    this.dirty = true;
                    this.monitorElements();
                    return;
                }
                else if (Browser.chrome) {
                    this.currentRange.insertNode(new Element('img', {'src':image.filename, 'width':image.width, 'height':image.height, 'align':image.align, 'alt':image.alt, 'border':0}));
                    this.dirty = true;
                    this.monitorElements();
                    return;
                }
                else if (this.fallback_ie) {
                    this.textarea.insertAtCursor('<img src="'
                        + image.filename
                        + '" width="'
                        + image.width
                        + '" height="'
                        + image.height
                        + '" align="'
                        + image.align
                        + '" alt="'
                        + image.alt
                        + '" border="0" />', true);
                    this.dirty = true;
                    return;
                }

                this.currentRange.select();
                if (this.validateParent(this.currentRange)) {
                    var imgStr = '<img src="'
                        + image.filename + '" width="'
                        + image.width + '" height="'
                        + image.height + '" align="'
                        + image.align + '" alt="'
                        + image.alt + '" border="0" />';
                    this.currentRange.pasteHTML(imgStr);
                    this.dirty = true;
                    this.monitorElements();
                }

            }.bind(this),
            extraData:imageData
        });
    },

    insertFileLink:function (data) {
        if (!data)
            return;

        if (this.stored_selection) {
            this.restoreSelection(this.stored_selection);
        }

        var filename = data['upl_path'];
        if (this.fallback_ie) {
            this.textarea.insertAtCursor(
                '<a href="' + filename + '">' + data['upl_name'] +
                    '</a>', true);
            return;
        }
        if (this.currentRange.select)
            this.currentRange.select();

        if (this.validateParent(this.currentRange)) {
            // IE
            if (this.currentRange.pasteHTML) {
                if (this.currentRange.text != '') {
                    this.currentRange.pasteHTML('<a href="' + filename
                        + '">' + this.currentRange.text + '</a>');
                } else {
                    this.currentRange.pasteHTML('<a href="' + filename
                        + '">' + filename + '</a>');
                }
            }
            // FF
            else {
                var str;
                if (this.currentRange.toString())
                    str = '<a href="' + filename + '">'
                        + this.currentRange.toString() + '</a>';
                else
                    str = '<a href="' + filename + '">' + filename
                        + '</a>';

                document.execCommand('inserthtml', false, str);
            }
            this.dirty = true;
        }
    },

    insertExtFlash:function () {
        this.currentRange = false;
        if (Energine.supportContentEdit && !this.fallback_ie)
            this.currentRange = this._getSelection().createRange();

        ModalBox.open({
            onClose:function (result) {
                if (result && result.result) {
                    result = result.result;
                    if (this.fallback_ie) {
                        this.textarea.insertAtCursor(result, true);
                        return;
                    }
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
                /*,
                 'toolbar':toolbar*/

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
        if (selectedOption == 'reset') {
            document.execCommand("FormatBlock", false, '<P>');
        } else {
            document.execCommand("FormatBlock", false, '<'
                + selectedOption + '>');
        }
        control.select.value = '';
    },

    _getSelection:function () {
        var selection = (document.selection || window.getSelection());

        if (!isset(selection.type)) {
            selection.type = 'Text';
        }

        if (!isset(selection.createRange)) {
            selection.createRange = function () {
                var range = this.getRangeAt(0);

                range.parentElement = function () {
                    // var result = this.startContainer;
                    var result = this.commonAncestorContainer;
                    if (result.nodeType == 3) {
                        result = result.parentNode;
                    }
                    return result;
                };

                return range;
            };
        }
        return selection;
    },

    // кросс-браузерный метод получения range для selection
    _getRange: function(selection) {
        return (selection.getRangeAt) ? selection.getRangeAt(0) : ((selection.createRange) ? selection.createRange() : null);
    },

    // активирует текущий редактируемый контейнер, если по какой-то причине он потерял фокус
    // (например при открытии диалога)
    _setActiveMode: function() {
        try {
            this.area.focus();
            this.area.setActive();
        } catch (e) { }
    },

    // возвращает Element из текущего html кода
    _getNodeFromHtml: function(html) {
        var temp = new Element('div').set('html', html);
        var els = temp.getElements('*');
        return els[0];
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

    // восстанавливает текущий selection в активном контейнере
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
    },

    // метод вставки html кода узла в текущую выделенную позицию активного контейнера
    pasteHtml: function(html) {
        try {
            var s = this._getSelection();
            var r = this._getRange(s);
            if (Browser.ie) { // IE
                this._setActiveMode();
                r.pasteHTML(html);
            } else if (Browser.firefox) { // FF
                r.deleteContents();
                r.insertNode(this._getNodeFromHtml(html));
            } else { // Safari
                r.deleteContents();
                r.insertNode(this._getNodeFromHtml(html));
            }
            r.collapse(false);
            r.select();
        } catch (e) { }
    }

});
