ScriptLoader.load('TabPane', 'Toolbar', 'Validator', 'RichEditor', 'ModalBox');

var Form = new Class({
    Implements : [Energine.request],
    initialize : function(element) {
        Asset.css('form.css');
        this.componentElement = $(element);
        this.singlePath = this.componentElement.getProperty('single_template');

        this.form = this.componentElement.getParent('form').addClass('form');

        this.tabPane = new TabPane(this.componentElement, {
            // onTabChange: this.onTabChange.bind(this)
        });
        this.validator = new Validator(this.form, this.tabPane);

        this.richEditors = [],this.uploaders = [],this.textBoxes =
                [],this.dateControls = [];

        this.form.getElements('textarea.richEditor').each(function(textarea) {
            this.richEditors.push(new Form.RichEditor(textarea, this,
                    this.fallback_ie));

        }, this);

        this.componentElement.getElements('.uploader').each(function(uploader) {
            this.uploaders.push(new Form.Uploader(uploader, this, 'upload/'));
        }, this);

        (this.componentElement.getElements('.inp_date') ||
                []).extend((this.componentElement.getElements('.inp_datetime') ||
                [])).each(function(dateControl) {
            this.dateControls.push(
                    (dateControl.hasClass('inp_date') ? Energine.createDatePicker(dateControl) : Energine.createDateTimePicker(dateControl))
                    );
        }, this);

        if (this.componentElement.getElement('#attached_files')) {
            (function() {
                new Form.AttachmentPane(this)
            }).delay(300, this);
        }

        /*
         this.componentElement.getElements('.textbox').each(function(textBox){
         this.textBoxes.push(new TextboxList2(textBox));
         }, this);
         */
        this.componentElement.getElements('.pane').setStyles({
            'border' : '1px dotted #777',
            'overflow' : 'auto'
        });
    },
    attachToolbar : function(toolbar) {
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
    save : function() {
        this.richEditors.each(function(editor) {
            editor.onSaveForm();
        });
        if (!this.validator.validate()) {
            return false;
        }
        this.request(this.singlePath +
                'save', this.form.toQueryString(), this.processServerResponse.bind(this));
    },
    processServerResponse: function(response) {
        if (response && (response.mode == 'insert')) {
            var nextActionSelector;
            if (nextActionSelector =
                    this.toolbar.getControlById('after_save_action')) {
                Cookie.write('after_add_default_action', nextActionSelector.getValue(), {path:new URI(Energine.base).get('directory'), duration:1});
                response.afterClose = nextActionSelector.getValue();
            }
        }
        ModalBox.setReturnValue(response);
        this.close();
    },
    close : function() {
        ModalBox.close();
    },
    openFileLib : function(button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        ModalBox.open({
            url : this.singlePath + 'file-library/',
            extraData : path,
            onClose : function(result) {
                if (result) {
                    button = $(button);
                    $(button.getProperty('link')).value = result['upl_path'];
                    if (image = $(button.getProperty('preview'))) {
                        image.setProperty('src', result['upl_path']);
                    }
                }
            }
        });
    }
});
Form.Uploader = new Class({
    initialize: function(uploaderElement, form, path) {
        if (!(this.element = $(uploaderElement))) return;

        this.form = form;
        this.swfUploader = new Swiff.Uploader({
            path: 'scripts/Swiff.Uploader.swf',
            url: this.form.singlePath + path,
            verbose: (Energine.debug) ? true : false,
            queued: false,
            multiple: false,
            target: this.element,
            instantStart: true,
            appendCookieData: false,
            data:{'NRGNSID':Cookie.read('NRGNSID'),'path': (ModalBox.getExtraData())?ModalBox.getExtraData():'', 'element': this.element.getProperty('nrgn:input')},
            typeFilter: {
                'All files (*.*)': '*.*',
                'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png',
                'Flash video (*.flv)': '*.flv'
            },
            onFileComplete: this.afterUpload.bind(this),
            onFileProgress: function(uploadInfo){form.form.getElementById('indicator').set('text', uploadInfo.progress.percentLoaded + "%")},
            onFileOpen: function(){form.form.getElementById('loader').removeClass('hidden'); form.form.getElementById('indicator').removeClass('hidden');},
            onComplete: function(){form.form.getElementById('loader').addClass('hidden');form.form.getElementById('indicator').addClass('hidden');},
            onFail: this.handleError.bind(this),
            onSelectFail: this.handleError.bind(this)
        });
    },
    afterUpload: function(uploadInfo) {
        this._show_preview(uploadInfo);
    },
    handleError: function() {
        this.form.validator.showError(this.element, 'При загрузке файла произошла ошибка');
    },
    _show_preview: function(file) {
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
    removeFilePreview: function(fieldId, control) {
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
    Implements: Options,
    options: {
        handlers: {
            'delete':this.delItem,
            'add': $empty/*,
             'iterate': this._iterate*/
        },
        tableName:'items',
        pk:'target'
    },
    initialize: function(element, options) {
        this.setOptions(options);
        this.element = $(element);
        this.element.getElementById('add_item').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.options.handlers.add();
        }.bind(this));
        this.element.getElements('.deleteItem').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.options.deleteFunc.run($(event.target).getProperty('target'));
        }.bind(this));
        this.element.getElements('.upItem').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.upItem($(event.target).getProperty('target'));
        }.bind(this));
        this.element.getElements('.downItem').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.downItem($(event.target).getProperty('target'));
        }.bind(this));

    },
    upItem: function(id) {
        this._moveItem(id, 'up');
    },
    downItem: function(id) {
        this._moveItem(id, 'down');
    },
    _moveItem: function(id, direction) {
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
    _zebraRows: function() {
        this.element.getElements('tbody tr').removeClass('even');
        this.element.getElements('tbody tr:even').addClass('even');
    },
    insertItem: function(data) {
        var emptyRow, pk = this.options.pk, tr = new Element('tr');
        if (emptyRow = $('empty_row')) emptyRow.dispose();
        if (!$('row_' + data[pk])) {
            tr.setProperty('id', 'row_' + data[pk]);
            $H(data).each(function(value, key){
                tr.grab(((key == pk)?this._buildPKColumn(value):this._buildColumn(value)));
            }.bind(this));
            this.element.getElement('tbody').grab(tr);
            this._zebraRows();
        }
    },
    _buildPKColumn:function(pkValue) {
        return new Element('td').adopt([
            new Element('button',
            {'type': 'button', 'events': {'click': function(event) {
                this.delItem(pkValue);
            }.bind(this)
            }
            }).set('text', Energine.translations.get('BTN_DEL_ITEM')),
            new Element('button',
            {'type': 'button', 'events': {'click': function(event) {
                this.upItem(pkValue);
            }.bind(this)
            }
            }).set('text', Energine.translations.get('BTN_UP')),
            new Element('button',
            {'type': 'button', 'events': {'click': function(event) {
                this.downItem(pkValue);
            }.bind(this)
            }
            }).set('text', Energine.translations.get('BTN_DOWN')),
            new Element('input', {'name': this.options.tableName + '[' +
                    this.options.pk +
                    '][]', 'type': 'hidden', 'value': pkValue})
        ]);
    },
    _buildColumn: function(value){
        return new Element('td').set('html', value)
    },
    delItem: function(id) {
        $('row_' + id).dispose();
        if (this.element.getElement('tbody').getChildren().length ==
                0) {
            this.element.getElement('tbody').adopt(
                    new Element('tr', {'id': 'empty_row'}).adopt(
                            new Element('td', {'colspan': '3'}).set('html', Energine.translations.get('MSG_NO_ITEMS'))
                            )
                    );
        }
        this._zebraRows();
    }
});
Form.AttachmentPane = new Class({
    Extends: Form.Uploader,
    initialize: function(form) {
        if (!$('add_attachment') || !$('insert_attachment')) return;

        this.parent($('add_attachment'), form, 'put/');
        $('insert_attachment').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            ModalBox.open(
            { 'url': form.singlePath + 'file-library/media/',
                'onClose': this._insertRow.bind(this)});
        }.bind(this));

        this.form.componentElement.getElements('.delete_attachment').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.delAttachment($(event.target).getProperty('upl_id'));
        }.bind(this));
        this.form.componentElement.getElements('.up_attachment').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.upAttachment($(event.target).getProperty('upl_id'));
        }.bind(this));
        this.form.componentElement.getElements('.down_attachment').addEvent('click', function(event) {
            Energine.cancelEvent(event);
            this.downAttachment($(event.target).getProperty('upl_id'));
        }.bind(this));

    },
    afterUpload: function(file) {
        if (!file.response.error) {
            var data = JSON.decode(file.response.text);
            this._insertRow(data);
        }
    },
    upAttachment: function(uplID) {
        this._moveAttachment(uplID, 'up');
    },
    downAttachment: function(uplID) {
        this._moveAttachment(uplID, 'down');
    },
    _moveAttachment: function(uplID, direction) {
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
    _zebraRows: function() {
        document.getElements('#attached_files tbody tr').removeClass('even');
        document.getElements('#attached_files tbody tr:even').addClass('even');
    },
    _insertRow: function(result) {
        if (result) {
            var data = result;
            var emptyRow;
            if (emptyRow = $('empty_row')) emptyRow.dispose();

            if (!$('row_' + data.upl_id)) {
                document.getElement('#attached_files tbody').adopt(
                        new Element('tr', {'id': 'row_' + data.upl_id}).adopt([
                            new Element('td').adopt([
                                new Element('button',
                                {'type': 'button', 'events': {'click': function(event) {
                                    this.delAttachment(data.upl_id);
                                }.bind(this)
                                }
                                }).set('text', Energine.translations.get('BTN_DEL_FILE')),
                                new Element('button',
                                {'type': 'button', 'events': {'click': function(event) {
                                    this.upAttachment(data.upl_id);
                                }.bind(this)
                                }
                                }).set('text', Energine.translations.get('BTN_UP')),
                                new Element('button',
                                {'type': 'button', 'events': {'click': function(event) {
                                    this.downAttachment(data.upl_id);
                                }.bind(this)
                                }
                                }).set('text', Energine.translations.get('BTN_DOWN')),
                                //                        new Element('input', {'name': 'uploads[upl_is_main][]', 'type': 'checkbox'}),
                                new Element('input', {'name': 'uploads[upl_id][]', 'type': 'hidden', 'value': data.upl_id})
                            ]),
                            new Element('td').set('html', data.upl_name),
                            new Element('td').adopt(
                                    new Element('a', {'href':data.upl_path, 'target':'blank'}).adopt(
                                            (!(['image', 'video'].contains(data.upl_mime_type))) ? new Element('span').set('html', data.upl_path) : new Element('img', {'src':data.upl_data.thumb, 'border': '0'})
                                            )
                                    )
                        ])
                        )
            }
        }
        this._zebraRows();
    },
    delAttachment: function(id) {
        $('row_' + id).dispose();
        if (document.getElement('#attached_files tbody').getChildren().length ==
                0) {
            document.getElement('#attached_files tbody').adopt(
                    new Element('tr', {'id': 'empty_row'}).adopt(
                            new Element('td', {'colspan': '3'}).set('html', Energine.translations.get('MSG_NO_ATTACHED_FILES'))
                            )
                    );

        }
        this._zebraRows();
    }
});
// Предназначен для последующей имплементации
// Содержит метод setLabel использующийся для привязки кнопки выбора разделов
Form.Label = {
    setLabel : function(result) {
        var id = name = segment = segmentObject = '';
        if (typeof(result) != 'undefined') {
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
                    JSON.encode({'id':id, 'name': name, 'segment': segment}),
            {path:new URI(Energine.base).get('directory'), duration:1}
                    );
        }
    },
    prepareLabel: function(treeURL, restore) {
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
    showTree : function (url) {
        ModalBox.open({
            url: this.singlePath + url,
            onClose: this.setLabel.bind(this)
        });
    },
    restoreLabel: function() {
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
    Extends : RichEditor,

    initialize : function(textarea, form, fallback_ie) {

        this.fallback_ie = fallback_ie;
        if (!Energine.supportContentEdit)
            return;
        this.textarea = $(textarea);
        this.form = form;

        if (Energine.supportContentEdit && !this.fallback_ie) {
            this.hidden = new Element('input').setProperty('name',
                    this.textarea.name).setProperties({
                'class': 'richEditorValue',
                'type' : 'hidden',
                'value' : '',
                'nrgn:pattern' : this.textarea
                        .getProperty('nrgn:pattern'),
                'nrgn:message' : this.textarea
                        .getProperty('nrgn:message')
            }).injectBefore(this.textarea);
            this.area = new Element('div').addEvent('blur', function() {
                this.hidden.value = this.area.innerHTML;
                this.hidden.fireEvent('blur');
            }.bind(this)).setProperties({
                componentPath : this.form.singlePath
            }).addClass('richEditor').setStyles({
                clear : 'both',
                overflow : 'auto'
            }).set('html', this.textarea.value);
            if (this.textarea.hasClass('half'))this.area.addClass('half');
            if (this.textarea.hasClass('quarter'))this.area.addClass('quarter');
            this.area.replaces(this.textarea);
            this.area.addEvent('keydown', function() {
                this.hidden.fireEvent('keydown')
            }.bind(this));
            // document.addEvent('keydown',
            // this.processKeyEvent.bind(this));
            this.pasteArea = new Element('div').setStyles({
                'visibility' : 'hidden',
                'width' : '0',
                'height' : '0',
                'font-size' : '0',
                'line-height' : '0'
            }).injectInside(document.body);
            //addEvent('paste' работать не захотело
            if (Browser.Engine.trident) this.area.onpaste =
                    this.processPaste.bindWithEvent(this);
            else if (Browser.Engine.gecko) this.area.onpaste =
                    this.processPasteFF.bindWithEvent(this);
            //this.area.onpaste = this.processPaste.bindWithEvent(this);

            this.area.contentEditable = 'true';
        } else {
            this.area = this.textarea.setProperty('componentPath',
                    this.form.singlePath);
        }

        this.toolbar = new Toolbar(this.textarea.name);
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'bold',
            icon : 'images/toolbar/bold.gif',
            title : Energine.translations.get('BTN_BOLD'),
            action : 'bold'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'italic',
            icon : 'images/toolbar/italic.gif',
            title : Energine.translations.get('BTN_ITALIC'),
            action : 'italic'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'olist',
            icon : 'images/toolbar/olist.gif',
            title : Energine.translations.get('BTN_OL'),
            action : 'olist'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'ulist',
            icon : 'images/toolbar/ulist.gif',
            title : Energine.translations.get('BTN_UL'),
            action : 'ulist'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'link',
            icon : 'images/toolbar/link.gif',
            title : Energine.translations.get('BTN_HREF'),
            action : 'link'
        }));
        this.toolbar.appendControl(new Toolbar.Separator({
            id : 'sep1'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'left',
            icon : 'images/toolbar/justifyleft.gif',
            title : Energine.translations.get('BTN_ALIGN_LEFT'),
            action : 'alignLeft'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'center',
            icon : 'images/toolbar/justifycenter.gif',
            title : Energine.translations.get('BTN_ALIGN_CENTER'),
            action : 'alignCenter'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'right',
            icon : 'images/toolbar/justifyright.gif',
            title : Energine.translations.get('BTN_ALIGN_RIGHT'),
            action : 'alignRight'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'justify',
            icon : 'images/toolbar/justifyall.gif',
            title : Energine.translations.get('BTN_ALIGN_JUSTIFY'),
            action : 'alignJustify'
        }));
        if (Energine.supportContentEdit && !this.fallback_ie) {
            this.toolbar.appendControl(new Toolbar.Separator({
                id : 'sep2'
            }));
            this.toolbar.appendControl(new Toolbar.Button({
                id : 'source',
                icon : 'images/toolbar/source.gif',
                title : Energine.translations.get('BTN_VIEWSOURCE'),
                action : 'showSource'
            }));
        }
        this.toolbar.appendControl(new Toolbar.Separator({
            id : 'sep3'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'filelib',
            icon : 'images/toolbar/filemngr.gif',
            title : Energine.translations.get('BTN_FILE_LIBRARY'),
            action : 'fileLibrary'
        }));
        this.toolbar.appendControl(new Toolbar.Button({
            id : 'imgmngr',
            icon : 'images/toolbar/image.gif',
            title : Energine.translations.get('BTN_INSERT_IMAGE'),
            action : 'imageManager'
        }));

        $pick(this.area, this.textarea).getParent().grab(this.toolbar.getElement(), 'top');

        this.toolbar.element.setStyle('width', '650px');
        this.toolbar.bindTo(this);
    },

    onSaveForm : function() {
        if (!Energine.supportContentEdit || this.fallback_ie)
            return;
        this.hidden.value = this.area.innerHTML;
    },

    showSource : function() {
        this.sourceMode = !this.sourceMode;
        if (this.sourceMode) {
            this.fallback_ie = true;
            this.textarea.value = this.area.innerHTML;
            // this.area.replaceWith(this.textarea);
            this.textarea.replaces(this.area);
        } else {
            this.fallback_ie = false;
            this.area.set('html',
                    this.cleanMarkup(
                            this.form.singlePath,
                            this.textarea.value,
                            false
                            )
                    );
            // this.textarea.replaceWith(this.area);
            this.area.replaces(this.textarea);
        }
    },

    disable : function() {
        if (Browser.Engine.trident)
            this.area.contentEditable = 'false';
        else
            this.area.disabled = true;
        this.toolbar.disableControls();
    },

    enable : function() {
        if (Browser.Engine.trident)
            this.area.contentEditable = 'true';
        else
            this.area.disabled = false;
        this.toolbar.enableControls();
    }
});
