ScriptLoader.load('TabPane', 'Toolbar', 'Validator', 'RichEditor', 'ModalBox', 'Overlay', 'datepicker');

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
        }
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

        this.componentElement.getElements('.uploader').each(function (uploader) {
            this.uploaders.push(new Form.Uploader(uploader, this, 'upload/'));
        }, this);

        (this.componentElement.getElements('.inp_date') ||
            []).extend((this.componentElement.getElements('.inp_datetime') ||
            [])).each(function (dateControl) {
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
                    src = Energine.static + 'images/icons/icon_undefined.gif';
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
    openQuickUpload:function (button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        var quick_upload_path = $(button).getProperty('quick_upload_path');
        var quick_upload_pid = $(button).getProperty('quick_upload_pid');
        var overlay = this._getOverlay();
        var processResult = this.processFileResult;

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
            data:{'NRGNCookie':document.cookie, 'path':($type(ModalBox.getExtraData()) == 'string') ? ModalBox.getExtraData() : '', 'element':this.element.getProperty('nrgn:input')},
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
            'add':$empty/*,
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
            this.options.deleteFunc.run($(event.target).getProperty('target'));
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

Form.AttachmentPane = new Class({
    Extends:Form.Uploader,
    initialize:function (form) {
        if (/*!$('add_attachment') || */!$('insert_attachment')) return;

        this.form = form;
        this.overlay = null;
        //this.parent($('add_attachment'), form, 'put/');
        $('insert_attachment').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            ModalBox.open({
                'url': form.singlePath + 'file-library/',
                'onClose': this._insertRow.bind(this)
            });
        }.bind(this));

        var quick_upload = $('quick_upload_attachment');
        var overlay = this._getOverlay();
        if (quick_upload) {
            var quick_upload_pid = quick_upload.getProperty('quick_upload_pid') || '1';
            quick_upload.addEvent('click', function (event) {
                Energine.cancelEvent(event);
                ModalBox.open({
                    'url': form.singlePath + 'file-library/' + quick_upload_pid + '/add',
                    'onClose': function(data) {
                        if (data && data.result && data.data) {
                            var upl_id = data.data;
                            if (upl_id) {
                                overlay.show();
                                new Request.JSON({
                                    'url': form.singlePath + 'file-library/' + quick_upload_pid + '/get-data/',
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
                                            // вставляем вторую строчку, ибо первая - folderup
                                            this._insertRow(data.data[1]);
                                            overlay.hide();
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
            }.bind(this));
        }

        this.form.componentElement.getElements('.delete_attachment').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.delAttachment($(event.target).getProperty('upl_id'));
        }.bind(this));
        this.form.componentElement.getElements('.up_attachment').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.upAttachment($(event.target).getProperty('upl_id'));
        }.bind(this));
        this.form.componentElement.getElements('.down_attachment').addEvent('click', function (event) {
            Energine.cancelEvent(event);
            this.downAttachment($(event.target).getProperty('upl_id'));
        }.bind(this));

    },
    _getOverlay:function () {
        return (!this.overlay) ? this.overlay = new Overlay() : this.overlay;
    },
    afterUpload:function (file) {
        if (!file.response.error) {
            var data = JSON.decode(file.response.text);
            this._insertRow(data);
        }
    },
    upAttachment:function (uplID) {
        this._moveAttachment(uplID, 'up');
    },
    downAttachment:function (uplID) {
        this._moveAttachment(uplID, 'down');
    },
    _moveAttachment:function (uplID, direction) {
        var currentRow, changeRow, position;
        if (currentRow = $('row_' + uplID)) {

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
        document.getElements('#attached_files tbody tr').removeClass('even');
        document.getElements('#attached_files tbody tr:even').addClass('even');
    },
    _insertRow:function (result) {
        if (result) {
            var createThumb = function (fileData) {
                var thumb = new Element('img', {'border':'0'});

                if (fileData) {
                    switch (fileData.upl_internal_type) {
                        case 'image':
                        case 'video':
                            thumb.setProperty('src', Energine.resizer + 'w150-h150/' + fileData.upl_path)
                            break;
                        default:

                            break;
                    }
                }
                return new Element('a', {'href':fileData.upl_path, 'target':'blank'}).adopt(thumb)
            }

            var data = result;
            var emptyRow;
            if (emptyRow = $('empty_row')) emptyRow.dispose();

            if (!$('row_' + data.upl_id)) {
                document.getElement('#attached_files tbody').adopt(
                    new Element('tr', {'id':'row_' + data.upl_id}).adopt([
                        new Element('td').adopt([
                            new Element('button',
                                {'type':'button', 'events':{'click':function (event) {
                                    this.delAttachment(data.upl_id);
                                }.bind(this)
                                }
                                }).set('text', Energine.translations.get('BTN_DEL_FILE')),
                            new Element('button',
                                {'type':'button', 'events':{'click':function (event) {
                                    this.upAttachment(data.upl_id);
                                }.bind(this)
                                }
                                }).set('text', Energine.translations.get('BTN_UP')),
                            new Element('button',
                                {'type':'button', 'events':{'click':function (event) {
                                    this.downAttachment(data.upl_id);
                                }.bind(this)
                                }
                                }).set('text', Energine.translations.get('BTN_DOWN')),
                            //                        new Element('input', {'name': 'uploads[upl_is_main][]', 'type': 'checkbox'}),
                            new Element('input', {'name':'uploads[upl_id][]', 'type':'hidden', 'value':data.upl_id})
                        ]),
                        new Element('td').set('html', data.upl_title),
                        new Element('td').adopt(createThumb(data))
                    ])
                )
            }
        }
        this._zebraRows();
    },
    delAttachment:function (id) {
        if ($('row_' + id)) {
            $('row_' + id).dispose();
        }
        else {
            $('row_').dispose();
        }
        if (document.getElement('#attached_files tbody').getChildren().length ==
            0) {
            document.getElement('#attached_files tbody').adopt(
                new Element('tr', {'id':'empty_row'}).adopt(
                    new Element('td', {'colspan':'3'}).set('html', Energine.translations.get('MSG_NO_ATTACHED_FILES'))
                )
            );

        }
        this._zebraRows();
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
    Extends:RichEditor,

    toolbar: null,
    selection: null,
    area: null,

    initialize:function (textarea, form, fallback_ie) {
        this.fallback_ie = fallback_ie;
        if (!Energine.supportContentEdit)
            return;
        this.textarea = $(textarea);
        this.form = form;
        this.selection = new RichEditor.Selection(window);

        if (Energine.supportContentEdit && !this.fallback_ie) {
            this.hidden = new Element('input').setProperty('name',
                this.textarea.name).setProperties({
                    'class':'richEditorValue',
                    'type':'hidden',
                    'value':''
                }).injectBefore(this.textarea);
            var prop;
            if (prop = this.textarea.getProperty('nrgn:pattern')) {
                this.hidden.setProperty('nrgn:pattern', prop);
            }
            if (prop = this.textarea.getProperty('nrgn:message')) {
                this.hidden.setProperty('nrgn:message', prop);
            }

            this.area = new Element('div').addEvent('blur', function () {
                this.hidden.value = this.area.innerHTML;
                this.hidden.fireEvent('blur');
            }.bind(this)).setProperties({
                    componentPath:this.form.singlePath
                }).addClass('richEditor').setStyles({
                    clear:'both',
                    overflow:'auto'
                }).set('html', this.textarea.value);
            if (this.textarea.hasClass('half'))this.area.addClass('half');
            if (this.textarea.hasClass('quarter'))this.area.addClass('quarter');
            this.area.replaces(this.textarea);
            this.area.addEvent('keydown', function () {
                this.hidden.fireEvent('keydown')
            }.bind(this));
            // document.addEvent('keydown',
            // this.processKeyEvent.bind(this));
            this.pasteArea = new Element('div').setStyles({
                'visibility':'hidden',
                'width':'0',
                'height':'0',
                'font-size':'0',
                'line-height':'0'
            }).injectInside(document.body);
            //addEvent('paste' работать не захотело
            if (Browser.Engine.trident) this.area.onpaste =
                this.processPasteFF.bindWithEvent(this);
            else if (Browser.Engine.gecko) this.area.onpaste =
                this.processPasteFF.bindWithEvent(this);
            //this.area.onpaste = this.processPaste.bindWithEvent(this);
            this.activate();
        } else {
            this.area = this.textarea.setProperty('componentPath', this.form.singlePath);
        }
        //@todo тут какие то непонятки с componentpath и single_Template

        this.area.setProperty('single_template', this.form.singlePath);
        this.toolbar = new Toolbar(this.textarea.name);
        this.toolbar.appendControl(new Toolbar.Button({
            id:'bold',
            icon:'images/toolbar/bold.gif',
            title:Energine.translations.get('BTN_BOLD'),
            action:'bold'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'italic',
            icon:'images/toolbar/italic.gif',
            title:Energine.translations.get('BTN_ITALIC'),
            action:'italic'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'olist',
            icon:'images/toolbar/olist.gif',
            title:Energine.translations.get('BTN_OL'),
            action:'olist'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'ulist',
            icon:'images/toolbar/ulist.gif',
            title:Energine.translations.get('BTN_UL'),
            action:'ulist'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'link',
            icon:'images/toolbar/link.gif',
            title:Energine.translations.get('BTN_HREF'),
            action:'link'
        }));
        this.toolbar.appendControl(new Toolbar.Separator({
            id:'sep1'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'left',
            icon:'images/toolbar/justifyleft.gif',
            title:Energine.translations.get('BTN_ALIGN_LEFT'),
            action:'alignLeft'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'center',
            icon:'images/toolbar/justifycenter.gif',
            title:Energine.translations.get('BTN_ALIGN_CENTER'),
            action:'alignCenter'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'right',
            icon:'images/toolbar/justifyright.gif',
            title:Energine.translations.get('BTN_ALIGN_RIGHT'),
            action:'alignRight'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'justify',
            icon:'images/toolbar/justifyall.gif',
            title:Energine.translations.get('BTN_ALIGN_JUSTIFY'),
            action:'alignJustify'
        }));

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

        this.toolbar.appendControl(new Toolbar.Separator({ id:'sep4' }));
        this.toolbar.appendControl(new Toolbar.CustomSelect({ id:'selectFormat', action:'changeFormat', action_before: 'beforeChangeFormat' }, styles));

        if (Energine.supportContentEdit && !this.fallback_ie) {
            this.toolbar.appendControl(new Toolbar.Separator({
                id:'sep2'
            }));
            this.toolbar.appendControl(new Toolbar.Button({
                id:'source',
                icon:'images/toolbar/source.gif',
                title:Energine.translations.get('BTN_VIEWSOURCE'),
                action:'showSource'
            }));
        }
        this.toolbar.appendControl(new Toolbar.Separator({
            id:'sep3'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'filelib',
            icon:'images/toolbar/filemngr.gif',
            title:Energine.translations.get('BTN_FILE_LIBRARY'),
            action:'fileLibrary'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'imgmngr',
            icon:'images/toolbar/image.gif',
            title:Energine.translations.get('BTN_INSERT_IMAGE'),
            action:'imageManager'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id:'imgURL',
            icon:'images/toolbar/imageurl.gif',
            title:Energine.translations.get('BTN_INSERT_IMAGE_URL'),
            action:'insertImageURL'
        }));
        this.toolbar.appendControl(
            new Toolbar.Button({
                id:'extflash',
                icon:'images/toolbar/embed.gif',
                title:Energine.translations.get('BTN_EXT_FLASH'),
                action:'insertExtFlash'
            })
        );
        $pick(this.area, this.textarea).getParent().grab(this.toolbar.getElement(), 'top');

        this.toolbar.element.setStyle('width', '650px');
        this.toolbar.bindTo(this);
    },

    onSaveForm:function () {
        if (!Energine.supportContentEdit || this.fallback_ie)
            return;
        this.hidden.value = this.area.innerHTML;
    },

    showSource:function () {
        //this.blur();
        ModalBox.open({
            url:this.form.singlePath + 'source',
            extraData:this.area.innerHTML,
            onClose:function (returnValue) {
                if (returnValue != null) {
                    this.area.set('html', returnValue);
                    this.monitorElements();
                }
            }.bind(this)
        });
    },

    onSelectionChanged: function(e)
    {
        this.parent();
        if (!this.toolbar) return false;

        this.toolbar.allButtonsUp();

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

                if (tag == 'b' || tag == 'strong' || font_weight == 'bold') this.toolbar.getControlById('bold').down();
                if (tag == 'i' || tag == 'em' || font_style == 'italic') this.toolbar.getControlById('italic').down();
                if (tag == 'ul') this.toolbar.getControlById('ulist').down();
                if (tag == 'ol') this.toolbar.getControlById('olist').down();

                if (dirs.contains(align) && !align_selected) {
                    this.toolbar.getControlById(align).down();
                    align_selected = true;
                } else if (!align_selected && !this.toolbar.getControlById('right').isDown() && !this.toolbar.getControlById('center').isDown() && !this.toolbar.getControlById('justify').isDown()) {
                    this.toolbar.getControlById('left').down();
                }

                Object.each(this.toolbar.getControlById('selectFormat').getOptions(), function (value, key) {
                    if (key == tag && !format_selected) {
                        format_selected = true;
                        this.toolbar.getControlById('selectFormat').setSelected(tag);
                    }
                    if (key == tag + '.' + cls) {
                        format_selected = true;
                        this.toolbar.getControlById('selectFormat').setSelected(key);
                    }
                }.bind(this));

                if (!format_selected) {
                    this.toolbar.getControlById('selectFormat').setSelected('');
                }
            }
        }
    }
});

