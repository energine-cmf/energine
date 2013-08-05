ScriptLoader.load('Toolbar', 'RichEditor', 'ModalBox', 'Overlay');

var PageEditor = new Class({
    editorClassName:'nrgnEditor',
    editors:[],
    toolbar: null,
    activeEditor: null,

    initialize:function () {
        Asset.css('pagetoolbar.css');
        Asset.css('pageeditor.css');
        $(document.body).getElements('.' + this.editorClassName).each(function (element) {
            this.editors.push(new PageEditor.BlockEditor(this, element));
        }, this);

        // убран обработчик глобального клика для активации контейнера
        // переделано через кнопку "Активизировать" в редакторе блоков
        //document.addEvent('click', this.processClick.bindWithEvent(this));

        if (Browser.opera) {
            window.addEvent('unload', function (e) {
                if (this.activeEditor) {
                    this.activeEditor.save(false);
                    window.location.href = window.location.href;
                    return '';
                }
            }.bind(this));
        } else {
            window.addEvent('beforeunload', function (e) {
                if (this.activeEditor) {
                    this.activeEditor.save(false);
                }
            }.bind(this));
        }
        this.attachToolbar(this.createToolbar());

    },
    createToolbar:function () {
        var toolbar = new Toolbar('wysiwyg_toolbar');
        toolbar.dock();
        //toolbar.appendControl(new Toolbar.Button({ id: 'save', icon: 'images/toolbar/save.gif', title: Energine.translations.get('BTN_SAVE'), state: 'save' }));
        //toolbar.appendControl(new Toolbar.Separator({ id: 'sep2' }));
        toolbar.appendControl(new Toolbar.Button({ id:'bold', icon:'images/toolbar/bold.gif', title:Energine.translations.get('BTN_BOLD'), action:'bold' }));
        toolbar.appendControl(new Toolbar.Button({ id:'italic', icon:'images/toolbar/italic.gif', title:Energine.translations.get('BTN_ITALIC'), action:'italic' }));
        toolbar.appendControl(new Toolbar.Button({ id:'olist', icon:'images/toolbar/olist.gif', title:Energine.translations.get('BTN_OL'), action:'olist' }));
        toolbar.appendControl(new Toolbar.Button({ id:'ulist', icon:'images/toolbar/ulist.gif', title:Energine.translations.get('BTN_UL'), action:'ulist' }));
        toolbar.appendControl(new Toolbar.Button({ id:'link', icon:'images/toolbar/link.gif', title:Energine.translations.get('BTN_HREF'), action:'link' }));
        toolbar.appendControl(new Toolbar.Separator({ id:'sep3' }));
        toolbar.appendControl(new Toolbar.Button({ id:'left', icon:'images/toolbar/justifyleft.gif', title:Energine.translations.get('BTN_ALIGN_LEFT'), action:'alignLeft' }));
        toolbar.appendControl(new Toolbar.Button({ id:'center', icon:'images/toolbar/justifycenter.gif', title:Energine.translations.get('BTN_ALIGN_CENTER'), action:'alignCenter' }));
        toolbar.appendControl(new Toolbar.Button({ id:'right', icon:'images/toolbar/justifyright.gif', title:Energine.translations.get('BTN_ALIGN_RIGHT'), action:'alignRight' }));
        toolbar.appendControl(new Toolbar.Button({ id:'justify', icon:'images/toolbar/justifyall.gif', title:Energine.translations.get('BTN_ALIGN_JUSTIFY'), action:'alignJustify' }));

        var styles = {
            '': {'caption': '', 'html': '&nbsp;', 'element': '', 'class': ''},
            'reset': {'caption': Energine.translations.get('TXT_RESET'), 'html': Energine.translations.get('TXT_RESET'), 'element': '', 'class': ''},
            'h1': {'caption': Energine.translations.get('TXT_H1'), 'html': '<h1>' + Energine.translations.get('TXT_H1') + '</h1>', 'element': 'h1', 'class': ''},
            'h2': {'caption': Energine.translations.get('TXT_H2'), 'html': '<h2>' + Energine.translations.get('TXT_H2') + '</h2>', 'element': 'h2', 'class': ''},
            'h3': {'caption': Energine.translations.get('TXT_H3'), 'html': '<h3>' + Energine.translations.get('TXT_H3') + '</h3>', 'element': 'h3', 'class': ''},
            'h4': {'caption': Energine.translations.get('TXT_H4'), 'html': '<h4>' + Energine.translations.get('TXT_H4') + '</h4>', 'element': 'h4', 'class': ''},
            'h5': {'caption': Energine.translations.get('TXT_H5'), 'html': '<h5>' + Energine.translations.get('TXT_H5') + '</h5>', 'element': 'h5', 'class': ''},
            'h6': {'caption': Energine.translations.get('TXT_H6'), 'html': '<h6>' + Energine.translations.get('TXT_H6') + '</h6>', 'element': 'h6', 'class': ''},
            'address': {'caption': Energine.translations.get('TXT_ADDRESS'), 'html': '<address>' + Energine.translations.get('TXT_ADDRESS') + '</address>', 'element': 'address', 'class': ''}
        };

        if (typeof(window['wysiwyg_styles'] != 'undefined')) {
            Object.each(window['wysiwyg_styles'], function (value, key) {
                styles[key] = {
                    'caption': value['caption'],
                    'html': '<' + value['element'] + ' class="' + value['class'] + '">' + value['caption'] + '</' + value['element'] + '>',
                    'element': value['element'],
                    'class': value['class']
                };
            });
        }

        toolbar.appendControl(new Toolbar.CustomSelect({ id:'selectFormat', action:'changeFormat', action_before: 'beforeChangeFormat' }, styles));

        toolbar.appendControl(new Toolbar.Separator({ id:'sep4' }));
        toolbar.appendControl(new Toolbar.Button({ id:'source', icon:'images/toolbar/source.gif', title:Energine.translations.get('BTN_VIEWSOURCE'), action:'showSource' }));
        toolbar.appendControl(new Toolbar.Separator({ id:'sep5' }));
        toolbar.appendControl(new Toolbar.Button({ id:'imagemngr', icon:'images/toolbar/image.gif', title:Energine.translations.get('BTN_INSERT_IMAGE'), action:'imageManager' }));
        toolbar.appendControl(new Toolbar.Button({ id:'imageurl', icon:'images/toolbar/imageurl.gif', title:Energine.translations.get('BTN_INSERT_IMAGE'), action:'insertImageURL' }));
        toolbar.appendControl(new Toolbar.Button({ id:'filemngr', icon:'images/toolbar/filemngr.gif', title:Energine.translations.get('BTN_FILE_LIBRARY'), action:'fileLibrary' }));
        toolbar.appendControl(new Toolbar.Button({ id:'extflash', icon:'images/toolbar/embed.gif', title:Energine.translations.get('BTN_EXT_FLASH'), action:'insertExtFlash' }));
        toolbar.bindTo(this);
        return toolbar;
    },
    attachToolbar:function (toolbar) {
        this.toolbar = toolbar;
        this.toolbar.getElement().injectInside(
            document.getElement('.e-topframe')
        );
        this.toolbar.disableControls();

        var html = $$('html')[0];
        if (html.hasClass('e-has-topframe2')) {
            html.removeClass('e-has-topframe2');
            html.addClass('e-has-topframe3');
        }
        else if (html.hasClass('e-has-topframe1')) {
            html.removeClass('e-has-topframe1');
            html.addClass('e-has-topframe2');
        }

    },

    getEditorByElement:function (element) {
        var result = false;
        this.editors.each(function (editor) {
            if (editor.area == element) {
                result = editor;
            }
        });
        return result;
    },

    processClick:function (event) {
        var element = $(event.target);

        if (element.get('tag') == 'td') {
            element = element.getElement('div.' + this.editorClassName) || element;
        }

        while ($type(element) == 'element' && !element.hasClass(this.editorClassName)) {
            element = element.getParent();
        }
        if ($type(element) != 'element' || !element.hasClass(this.editorClassName)) return;

        if (this.activeEditor) {
            if (this.activeEditor.area != element) {
                var newActiveEditor = this.getEditorByElement(element);
                if (newActiveEditor) {
                    this.activeEditor.blur();
                    this.activeEditor = newActiveEditor;
                    this.activeEditor.focus();
                }
            }
        }
        else {
            this.activeEditor = this.getEditorByElement(element);
            if (this.activeEditor) this.activeEditor.focus();
        }
        if (!Energine.supportContentEdit && this.activeEditor) {
            this.activeEditor.showSource();
        }
    },
    processKeyEvent:function () {
        if (this.activeEditor) {
            this.activeEditor.dirty = true;
        }
    }
});

PageEditor.BlockEditor = new Class({
    Extends:RichEditor,

    initialize:function (pageEditor, area) {
        this.pageEditor = pageEditor;
        this.parent(area);
        this.isActive = false;
        this.singlePath = this.area.getProperty('single_template');
        this.ID = this.area.getProperty('eID') ? this.area.getProperty('eID') : false;
        this.num = this.area.getProperty('num') ? this.area.getProperty('num') : false;


        if (Energine.supportContentEdit) {

            this.injectActivateButton();

            document.addEvent('keydown', this.pageEditor.processKeyEvent.bind(this.pageEditor));
            if (!(this.pasteArea = $('pasteArea'))) {
                this.pasteArea = new Element('div', {'id':'pasteArea'}).setStyles({ 'visibility':'hidden', 'width':'0', 'height':'0', 'font-size':'0', 'line-height':'0' }).injectInside(document.body);
            }
            ////addEvent('paste' работать не захотело
            if (Browser.Engine.trident) this.area.onpaste = this.processPasteFF.bindWithEvent(this);
            else if (Browser.Engine.gecko || Browser.Engine.presto) this.area.onpaste = this.processPasteFF.bindWithEvent(this);
        }
        //this.switchToViewMode = this.pageEditor.switchToViewMode;
        this.overlay = new Overlay();
    },

    injectActivateButton: function() {

        var btn_activate = new Element('a', {href: '#', html: Energine.translations.get('BTN_ACTIVATE')})
            .setStyle('visibility', 'hidden')
            .setStyle('display', 'block')
            .setStyle('float', 'right')
            .setStyle('marginBottom', '-20px')
            .addClass('btn');

        btn_activate.injectBefore(this.area);

        var show_btn = function() {
            if (!this.isActive) {
                btn_activate.setStyle('visibility', 'visible');
            } else {
                btn_activate.setStyle('visibility', 'hidden');
            }
        }.bind(this);

        var hide_btn = function() {
                btn_activate.setStyle('visibility', 'hidden');
        };

        var click_btn = function(event){
            event.stopPropagation();

            hide_btn();

            if (this.pageEditor.activeEditor) {
                if (this.pageEditor.activeEditor.area != this.area) {
                    var newActiveEditor = this.pageEditor.getEditorByElement(this.area);
                    if (newActiveEditor) {
                        this.pageEditor.activeEditor.blur();
                        this.pageEditor.activeEditor = newActiveEditor;
                        this.pageEditor.activeEditor.focus();
                    }
                }
            }
            else {
                this.pageEditor.activeEditor = this.pageEditor.getEditorByElement(this.area);
                if (this.pageEditor.activeEditor) this.pageEditor.activeEditor.focus();
            }

            return false;
        }.bind(this);

        btn_activate
            .addEvent('mouseover', show_btn)
            .addEvent('mouseout', hide_btn)
            .addEvent('click', click_btn);

        $(this.area)
            .addEvent('mouseover', show_btn)
            .addEvent('mouseout', hide_btn);
    },

    activate:function () {
        this.parent();
        this.area.addClass('activeEditor');
    },

    deactivate:function () {
        this.parent();
        this.area.removeClass('activeEditor');
    },

    focus:function () {
        this.activate();
        var toolbar = this.pageEditor.toolbar.bindTo(this);
        if (!Energine.supportContentEdit) {
            //if (this.dirty) toolbar.getControlById('save').enable();
            return;
        }
        toolbar.enableControls();
        //toolbar.getControlById('save').disable();

    },

    blur:function () {
        this.pageEditor.toolbar.bindTo(this.pageEditor).disableControls();
        if (!Energine.supportContentEdit) {
            return;
        }
        if (this.dirty) this.save();
        //this.pageEditor.toolbar.getControlById('save').disable();
        this.deactivate();
    },

    showSource:function () {
        this.blur();
        ModalBox.open({
            url:this.singlePath + 'source',
//            extraData:this.cleanMarkup('dummy', this.area.innerHTML),
            extraData:this.area.innerHTML,
            onClose:function (returnValue) {
                if (returnValue || (returnValue === '')) {
                    //this.area.set('html', this.cleanMarkup('dummy', returnValue));
                    this.area.set('html', returnValue);
                    this.dirty = true;
                }
                this.focus();
                this.monitorElements();
            }.bind(this)
        });
    },

    save:function (async) {
        if (async == undefined) async = true;
        this.dirty = false;
        var data = 'data=' + encodeURIComponent(this.area.innerHTML);
        if (this.ID) data += '&ID=' + this.ID;
        if (this.num) data += '&num=' + this.num;
        if (!async) this.overlay.show();

        new Request({
            url:this.singlePath + 'save-text',
            'async':async,
            method:'post',
            'data':data,
            onSuccess:function (response) {
                this.area.innerHTML = response;
                if (!async)this.overlay.hide();
            }.bind(this)
        }).send();
    },

    saveWithConfirmation:function () {
        if (this.dirty && confirm('Хотите сохранить изменённый блок?')) {
            this.save();
        }
    },
    cleanMarkup:function (dummyPath, data, aggressive) {
        return this.parent(this.singlePath, data, aggressive);
    },

    onSelectionChanged: function(e)
    {
        this.parent();
        if (!this.isActive) return false;

        this.pageEditor.toolbar.allButtonsUp();

        var el = this.selection.getNode();

        if (el == this.area) return;

        var tags = [];
        var format_selected = false;
        var align_selected = false;
        var els = this.getAllParentElements(el);
        if (els.length > 0)
        {
            for (var i=0; i<els.length; i++)
            {
                if (!els[i] || !els[i].tagName) return;
                var tag = els[i].tagName.toLowerCase();
                var cls = els[i].get('class');
                tags.push(tag);
                el = els[i];
                var dirs = ['left', 'right', 'center', 'justify'];
                var align = el.getProperty('align');
                var text_align = el.getStyle('text-align');
                if (dirs.contains(text_align)) {
                    align = text_align;
                }
                var font_weight = el.getStyle('font-weight');
                var font_style = el.getStyle('font-style');

                if (tag == 'b' || tag == 'strong' || font_weight == 'bold') this.pageEditor.toolbar.getControlById('bold').down();
                if (tag == 'i' || tag == 'em' || font_style == 'italic') this.pageEditor.toolbar.getControlById('italic').down();
                if (tag == 'ul') this.pageEditor.toolbar.getControlById('ulist').down();
                if (tag == 'ol') this.pageEditor.toolbar.getControlById('olist').down();

                if (dirs.contains(align) && !align_selected) {
                    this.pageEditor.toolbar.getControlById(align).down();
                    align_selected = true;
                } else if (!align_selected && !this.pageEditor.toolbar.getControlById('right').isDown() && !this.pageEditor.toolbar.getControlById('center').isDown() && !this.pageEditor.toolbar.getControlById('justify').isDown()) {
                    this.pageEditor.toolbar.getControlById('left').down();
                }

                Object.each(this.pageEditor.toolbar.getControlById('selectFormat').getOptions(), function (value, key) {
                    if (key == tag && !format_selected) {
                        format_selected = true;
                        this.pageEditor.toolbar.getControlById('selectFormat').setSelected(tag);
                    }
                    if (key == tag + '.' + cls) {
                        format_selected = true;
                        this.pageEditor.toolbar.getControlById('selectFormat').setSelected(key);
                    }
                }.bind(this));

                if (!format_selected) {
                    this.pageEditor.toolbar.getControlById('selectFormat').setSelected('');
                }
            }
        }
    }
});
