ScriptLoader.load('ckeditor/ckeditor', 'TabPane', 'Toolbar', 'Validator', 'ModalBox', 'Overlay', 'datepicker');
var Form = new Class({
    initialize:function (element) {
        Asset.css('form.css');
        this.componentElement = $(element);
        this.overlay = null;
        this.singlePath = this.componentElement.getProperty('single_template');

        this.form = this.componentElement.getParent('form').addClass('form');
        this.state = this.form.getElementById('componentAction').get('value');
        this.tabPane = new TabPane(this.componentElement, {
            // onTabChange: this.onTabChange.bind(this)
        });
        this.validator = new Validator(this.form, this.tabPane);

        this.richEditors = [], this.uploaders = [], this.textBoxes = [], this.dateControls = [], this.codeEditors = []/*, this.smapSelectors = []*/;

        this.form.getElements('textarea.richEditor').each(function (textarea) {
            this.richEditors.push(new Form.RichEditor(textarea, this,
                this.fallback_ie));

        }, this);

        this.form.getElements('textarea.code').each(function (textarea) {
            this.codeEditors.push(CodeMirror.fromTextArea(textarea, {mode: "text/html", tabMode: "indent", lineNumbers: true}));
        }, this);

        var showHideFunc = function (e) {
            Energine.cancelEvent(e);
            var field, el = $(e.target);
            if (field = el.getParent('.field')) {
                if(field.hasClass('min'))field.swapClass('min', 'max');
                else if(el.hasClass('icon_min_max') && field.hasClass('max'))field.swapClass('max', 'min');
            }
        };
        this.form.getElements('.field .control.toggle').addEvent('click', showHideFunc);
        this.form.getElements('.icon_min_max').addEvent('click', showHideFunc);
        /*this.form.getElements('#copy_lang_data a').addEvent('click', function(e){
            Energine.cancelEvent(e);
            var a = $(e.target).getProperty('href');
            //console.log(this.tabPane.getCurrentTab().pane.toQueryString());
        }.bind(this));*/

        this.form.getElements('.smap_selector').each(function (el) {
            new Form.SmapSelector(el, this);
        }, this);

        this.form.getElements('.attachment_selector').each(function (el) {
            new Form.AttachmentSelector(el, this);
        }, this);

        this.componentElement.getElements('.uploader').each(function (uploader) {
            this.uploaders.push(new Form.Uploader(uploader, this, 'upload/'));
        }, this);

        (this.componentElement.getElements('.inp_date') ||
            []).append(this.componentElement.getElements('.inp_datetime') ||
            []).each(function (dateControl) {
                var isNullable = !dateControl.getParent('.field').hasClass('required');
                this.dateControls.push(
                    (dateControl.hasClass('inp_date') ? Energine.createDatePicker(dateControl, isNullable) : Energine.createDateTimePicker(dateControl, isNullable))
                );
            }, this);
        this.buildAttachmentPane();

        /*
         this.componentElement.getElements('.textbox').each(function(textBox){
         this.textBoxes.push(new TextboxList2(textBox));
         }, this);
         */
        this.componentElement.getElements('.pane').setStyles({
            'border':'1px dotted #777',
            'overflow':'auto'
        });

        /*Checking if opened in modalbox*/
        var mb;
        if((mb = window.parent.ModalBox) && mb.initialized && mb.getCurrent()){
            document.body.addEvent('keypress', function(evt){
                if(evt.key=='esc'){
                    mb.close();
                }
            });
        }
    },
    buildAttachmentPane:function () {
        if (this.componentElement.getElementById('attached_files')) {
            (function () {
                new Form.AttachmentPane(this)
            }).delay(300, this);
        }
    },
    attachToolbar:function (toolbar) {
        this.toolbar = toolbar;
        var toolbarContainer = this.componentElement.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.toolbar.getElement());
        }
        else {
            this.componentElement.adopt(this.toolbar.getElement());
        }
        var afterSaveActionSelect;
        if (afterSaveActionSelect =
            this.toolbar.getControlById('after_save_action')) {
            var savedActionState = Cookie.read('after_add_default_action');
            if (savedActionState) {
                afterSaveActionSelect.setSelected(savedActionState);
            }
        }
        toolbar.bindTo(this);
    },
    save:function () {
        this.richEditors.each(function (editor) {
            editor.onSaveForm();
        });
        this.codeEditors.each(function (editor) {
            editor.save();
        });

        if (!this.validator.validate()) {
            return false;
        }
        this._getOverlay().show();

        var errorFunc = function (responseText) {
            this._getOverlay().hide();
        }.bind(this);

        this.request(this.singlePath +
            'save', this.form.toQueryString(), this.processServerResponse.bind(this), errorFunc, errorFunc);
    },
    _getOverlay:function () {
        return (!this.overlay) ? this.overlay = new Overlay() : this.overlay;
    },
    processServerResponse:function (response) {
        var nextActionSelector;
        if (response && (nextActionSelector =
            this.toolbar.getControlById('after_save_action'))) {
            Cookie.write('after_add_default_action', nextActionSelector.getValue(), {path:new URI(Energine.base).get('directory'), duration:1});
            response.afterClose = nextActionSelector.getValue();
        }
        ModalBox.setReturnValue(response);
        this._getOverlay().hide();
        this.close();
    },
    close:function () {
        ModalBox.close();
    },
    clearFileField:function (fieldId, lnk) {
        var preview;
        this.form.getElementById(fieldId).set('value', '');
        if (preview = this.form.getElementById(fieldId + '_preview')) {
            preview.removeProperty('href').hide();
        }
        lnk.hide();
    },

    processFileResult: function(result, button) {
        var image, btnDF;
        if (result) {

            button = $(button);
            $(button.getProperty('link')).value = result['upl_path'];

            image = ($(button.getProperty('preview')).get('tag') == 'img')
                ? $(button.getProperty('preview'))
                : $(button.getProperty('preview')).getElement('img');
            if (image) {
                var src;
                if (result['upl_internal_type'] == 'image') {
                    src = Energine.media + result['upl_path'];
                }
                else if (result['upl_internal_type'] == 'video') {
                    src = Energine.resizer + 'w0-h0/' + result['upl_path'];
                }
                else {
                    src = Energine['static'] + 'images/icons/icon_undefined.gif';
                }

                image.setProperty('src', src);
                $(button.getProperty('preview')).setProperty('href', Energine.media + result['upl_path']).show();
            }
            if (button.getNext('.lnk_clear')) {
                button.getNext('.lnk_clear').show('inline');
            }
        }
    },

    openFileLib:function (button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        ModalBox.open({
            url:this.singlePath + 'file-library/',
            extraData:path,
            onClose:function (result) {
                this.processFileResult(result, button);
            }.bind(this)
        });
    },

    openTagEditor: function (button) {
        var tags = $($(button).getProperty('link')).get('value');
        if (tags == '') {
            tags = null;
        }
        var overlay = this._getOverlay();
        overlay.show();
        new Request.JSON({
            'url': this.singlePath + 'tags/get-tag-ids/',
            'method': 'post',
            'data': {
                json: 1,
                tags: tags
            },
            'evalResponse': true,
            'onComplete': function(data) {
                overlay.hide();
                if (data) {
                    ModalBox.open({
                        url:this.singlePath + 'tags/show/' + ((data.data) ? encodeURIComponent(data.data.join(',')) + '/' : ''),
                        extraData: data.data,
                        onClose:function (result) {
                            if (result) {
                                $($(button).getProperty('link')).set('value', result);
                            }
                        }.bind(this)
                    });
                }
            }.bind(this),
            'onFailure': function (e) {
                overlay.hide();
            }
        }).send();
    },

    openQuickUpload:function (button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        var quick_upload_path = $(button).getProperty('quick_upload_path');
        var quick_upload_pid = $(button).getProperty('quick_upload_pid');
        var quick_upload_enabled = $(button).getProperty('quick_upload_enabled');
        var overlay = this._getOverlay();
        var processResult = this.processFileResult;

        if (!quick_upload_enabled) return;

        ModalBox.open({
            url:this.singlePath + 'file-library/' + quick_upload_pid + '/add',
            extraData:path,
            onClose:function (result) {
                if (result && result.data) {
                    var upl_id = result.data;

                    if (upl_id) {
                        overlay.show();
                        new Request.JSON({
                            'url': this.singlePath + 'file-library/' + quick_upload_pid + '/get-data/',
                            'method': 'post',
                            'data': {
                                json: 1,
                                filter: {
                                    condition: '=',
                                    share_uploads: {'upl_id': [upl_id]}
                                }
                            },
                            'evalResponse': true,
                            'onComplete': function(data) {
                                if (data && data.data && data.data.length == 2) {
                                    overlay.hide();
                                    processResult(data.data[1], button);
                                }
                            }.bind(this),
                            'onFailure': function (e) {
                                overlay.hide();
                            }
                        }).send();
                    }
                }
            }.bind(this)
        });
    }
});
Form.implement(Energine.request);
Form.Uploader = new Class({
    initialize:function (uploaderElement, form, path) {
        if (!(this.element = $(uploaderElement))) return;
        /*var cookieKeys = /(\w+)=(\w+);/i;
         console.log(cookieKeys.exec(document.cookie), document.cookie);
         //console.log(document.cookie.split(';').map(function(cook){console.log(cook); return 1;}));*/
        this.form = form;
        this.swfUploader = new Swiff.Uploader({
            path:'scripts/Swiff.Uploader.swf',
            url:this.form.singlePath + path + '?json',
            verbose:(Energine.debug) ? true : false,
            queued:false,
            multiple:false,
            target:this.element,
            instantStart:true,
            appendCookieData:false,
            timeLimit:0,
            data:{'NRGNCookie':document.cookie, 'path':(typeOf(ModalBox.getExtraData()) == 'string') ? ModalBox.getExtraData() : '', 'element':this.element.getProperty('nrgn:input')},
            typeFilter:{
                'All files (*.*)':'*.*',
                'Images (*.jpg, *.jpeg, *.gif, *.png)':'*.jpg; *.jpeg; *.gif; *.png',
                'Flash video (*.flv)':'*.flv'
            },
            onFileComplete:this.afterUpload.bind(this),
            onFileProgress:function (uploadInfo) {
                form.form.getElementById('indicator').set('text', uploadInfo.progress.percentLoaded + "%")
            },
            onFileOpen:function () {
                form.form.getElementById('loader').removeClass('hidden');
                form.form.getElementById('indicator').removeClass('hidden');
            },
            onComplete:function () {
                form.form.getElementById('loader').addClass('hidden');
                form.form.getElementById('indicator').addClass('hidden');
            },
            onFail:this.handleError.bind(this),
            onSelectFail:this.handleError.bind(this)
        });
    },
    afterUpload:function (uploadInfo) {
        this._show_preview(uploadInfo);
    },
    handleError:function () {
        this.form.validator.showError(this.element, 'При загрузке файла произошла ошибка');
    },
    _show_preview:function (file) {
        if (!file.response.error) {
            var data = JSON.decode(file.response.text, true);
            var preview, input, previewImg;
            if ((preview = $(data.element + '_preview')) &&
                (input = $(data.element))) {
                input.set('value', data.file);
                if ($('upl_name') &&
                    (!$('upl_name').get('value'))) $('upl_name').set('value', data.title);
                if (!(previewImg = preview.getElement('img'))) {
                    previewImg =
                        new Element('img', {'border':0}).inject(preview);
                }
                previewImg.setProperty('src', data.preview);
            }
        }
        else {
            this.form.validator.showError(this.element, 'При загрузке файла произошла ошибка');
        }
    },

    //todo Сделать удаление файла
    removeFilePreview:function (fieldId, control) {
        var tmpNode;
        $(fieldId).value = '';

        if (tmpNode = $(fieldId + '_preview')) {
            tmpNode.setProperty('src', '');
        }

        if (tmpNode = $(fieldId + '_link')) {
            tmpNode.set('html', '');
        }
        return false;
    }
});
Form.Sked = new Class({
    Implements:Options,
    options:{
        handlers:{
            'delete':this.delItem,
            'add': function(){}/*,
             'iterate': this._iterate*/
        },
        tableName:'items',
        pk:'target'
    },
    initialize:function (element, options) {
        this.setOptions(options);
        this.element = $(element);
        this.element.getElementById('add_item').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.options.handlers.add();
        }.bind(this));
        this.element.getElements('.deleteItem').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.options.deleteFunc.call($(event.target).getProperty('target'));
        }.bind(this));
        this.element.getElements('.upItem').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.upItem($(event.target).getProperty('target'));
        }.bind(this));
        this.element.getElements('.downItem').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.downItem($(event.target).getProperty('target'));
        }.bind(this));

    },
    upItem:function (id) {
        this._moveItem(id, 'up');
    },
    downItem:function (id) {
        this._moveItem(id, 'down');
    },
    _moveItem:function (id, direction) {
        var currentRow, changeRow, position;
        if (currentRow = $('row_' + id)) {

            if (direction == 'up') {
                changeRow = currentRow.getPrevious();
                position = 'before';
            }
            else {
                changeRow = currentRow.getNext();
                position = 'after';
            }

            if (changeRow) {
                currentRow.inject(changeRow, position);
            }
        }
        this._zebraRows();
    },
    _zebraRows:function () {
        this.element.getElements('tbody tr').removeClass('even');
        this.element.getElements('tbody tr:even').addClass('even');
    },
    insertItem:function (data) {
        var emptyRow, pk = this.options.pk, tr = new Element('tr');
        if (emptyRow = $('empty_row')) emptyRow.dispose();
        if (!$('row_' + data[pk])) {
            tr.setProperty('id', 'row_' + data[pk]);
            $H(data).each(function (value, key) {
                tr.grab(((key == pk) ? this._buildPKColumn(value) : this._buildColumn(value)));
            }.bind(this));
            this.element.getElement('tbody').grab(tr);
            this._zebraRows();
        }
    },
    _buildPKColumn:function (pkValue) {
        return new Element('td').adopt([
            new Element('button',
                {'type':'button', 'events':{'click':function (event) {
                    this.delItem(pkValue);
                }.bind(this)
                }
                }).set('text', Energine.translations.get('BTN_DEL_ITEM')),
            new Element('button',
                {'type':'button', 'events':{'click':function (event) {
                    this.upItem(pkValue);
                }.bind(this)
                }
                }).set('text', Energine.translations.get('BTN_UP')),
            new Element('button',
                {'type':'button', 'events':{'click':function (event) {
                    this.downItem(pkValue);
                }.bind(this)
                }
                }).set('text', Energine.translations.get('BTN_DOWN')),
            new Element('input', {'name':this.options.tableName + '[' +
                this.options.pk +
                '][]', 'type':'hidden', 'value':pkValue})
        ]);
    },
    _buildColumn:function (value) {
        return new Element('td').set('html', value)
    },
    delItem:function (id) {
        $('row_' + id).dispose();
        if (this.element.getElement('tbody').getChildren().length ==
            0) {
            this.element.getElement('tbody').adopt(
                new Element('tr', {'id':'empty_row'}).adopt(
                    new Element('td', {'colspan':'3'}).set('html', Energine.translations.get('MSG_NO_ITEMS'))
                )
            );
        }
        this._zebraRows();
    }
});

Form.SmapSelector = new Class({
    initialize:function (selector, form) {
        var selector = $(selector);
        this.form = form;
        this.field = selector.getProperty('field');

        selector.addEvent('click', function (e) {
            Energine.cancelEvent(e);
            this.smapName = $($(e.target).getProperty('smap_name'));
            this.smapId = $($(e.target).getProperty('smap_id'));
            this.showSelector.apply(this);
        }.bind(this));
    },

    showSelector:function () {
        ModalBox.open({
            url:this.form.componentElement.getProperty('template') + 'selector/',
            onClose:this.setName.bind(this)
        });
    },
    setName:function (result) {
        if (result) {
            var name = '';
            if (result.site_name) {
                name += result.site_name + ' : ';
            }
            name += result.smap_name;
            this.smapName.set('value', name);
            this.smapId.set('value', result.smap_id);
        }

    }
});

Form.AttachmentSelector = new Class({
    initialize:function (selector, form) {
        var selector = $(selector);
        this.form = form;
        this.field = selector.getProperty('field');

        selector.addEvent('click', function (e) {
            Energine.cancelEvent(e);
            this.uplName = $($(e.target).getProperty('upl_name'));
            this.uplId = $($(e.target).getProperty('upl_id'));
            this.showSelector.apply(this);
        }.bind(this));
    },

    showSelector:function () {
        ModalBox.open({
            url:this.form.componentElement.getProperty('template') + 'file-library/',
            onClose:this.setName.bind(this)
        });
    },
    setName:function (result) {
        if (result) {
            this.uplName.set('value', result.upl_path);
            this.uplId.set('value', result.upl_id);
        }

    }
});

// Предназначен для последующей имплементации
// Содержит метод setLabel использующийся для привязки кнопки выбора разделов
Form.Label = {
    setLabel:function (result) {
        var id = name = segment = segmentObject = '';
        if (typeOf(result) != 'null') {
            if (result) {
                id = result.smap_id;
                name = result.smap_name;
                segment = result.smap_segment;
            }
            $(this.obj.getProperty('hidden_field')).value = id;
            $(this.obj.getProperty('span_field')).innerHTML = name;
            if (segmentObject = $('smap_pid_segment'))
                segmentObject.innerHTML = segment;
            Cookie.write(
                'last_selected_smap',
                JSON.encode({'id':id, 'name':name, 'segment':segment}),
                {path:new URI(Energine.base).get('directory'), duration:1}
            );
        }
    },
    prepareLabel:function (treeURL, restore) {
        if (!arguments[1]) {
            restore = false;
        }
        if (this.obj = $('sitemap_selector')) {
            this.obj.addEvent('click', this.showTree.pass(treeURL, this));
            if (restore) {
                this.restoreLabel();
            }
        }
    },
    showTree:function (url) {
        ModalBox.open({
            url:this.singlePath + url,
            onClose:this.setLabel.bind(this)
        });
    },
    restoreLabel:function () {
        var savedData = Cookie.read('last_selected_smap');
        if (this.obj && savedData) {
            savedData = JSON.decode(savedData);
            $(this.obj.getProperty('hidden_field')).value = savedData.id;
            $(this.obj.getProperty('span_field')).innerHTML = savedData.name;
            if (segmentObject = $('smap_pid_segment'))
                segmentObject.innerHTML = savedData.segment;
        }
    }
}

Form.RichEditor = new Class({

    area: null,

    initialize:function (textarea, form, fallback_ie) {

        this.setupEditors();

        this.textarea = $(textarea);
        this.form = form;
        try {
            this.editor = CKEDITOR.replace(this.textarea.get('id'));
            this.editor.editorId = this.textarea.get('id');
            this.editor.singleTemplate = this.form.singlePath;
        } catch (e) {
        }

        /*if (Energine.supportContentEdit && !this.fallback_ie) {
            this.hidden = new Element('input', {'name': this.textarea.name, 'value': this.textarea.get('value'), 'type': 'hidden', 'class': 'richEditorValue'}).injectBefore(this.textarea);

        var prop;
        if (prop = this.textarea.getProperty('nrgn:pattern')) {
            this.hidden.setProperty('nrgn:pattern', prop);
        }
        if (prop = this.textarea.getProperty('nrgn:message')) {
            this.hidden.setProperty('nrgn:message', prop);
        }*/
    },

    setupEditors: function() {
        if (!Form.RichEditor.ckeditor_init) {
            CKEDITOR.config.extraPlugins = 'energineimage,energinefile';
            CKEDITOR.config.allowedContent = true;
            CKEDITOR.config.toolbar = [
                { name: 'document', groups: [ 'mode' ], items: [ 'Source' ] },
                { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
                { name: 'editing', groups: [ 'find', 'selection' ], items: [ 'Find', 'Replace', '-', 'SelectAll' ] },
                { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                { name: 'insert', items: [ 'Image', 'Flash', 'Table', 'EnergineImage', 'EnergineFile' ] },
                { name: 'tools', items: [ 'ShowBlocks' ] },
                '/',
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                { name: 'paragraph', groups: [ 'list', 'indent', 'align' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] }
            ];
            var styles = [];
            if (window['wysiwyg_styles']) {
                Object.each(window['wysiwyg_styles'], function(style) {
                    styles.push({
                        name: style['caption'],
                        element: style['element'],
                        attributes: { 'class': style['class'] }
                    });
                });
            }
            CKEDITOR.stylesSet.add('energine', styles);
            CKEDITOR.config.stylesSet = 'energine';
            Form.RichEditor.ckeditor_init = true;
        }
    },

    onSaveForm:function () {
        try {
            var data = this.editor.getData();
            this.textarea.value = data;
        } catch (e) {
        }
    }

});

