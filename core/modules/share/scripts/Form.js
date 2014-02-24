/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Form]{@link Form}</li>
 *     <li>[Form.Uploader]{@link Form.Uploader}</li>
 *     <li>[Form.Sked]{@link Form.Sked}</li>
 *     <li>[Form.SmapSelector]{@link Form.SmapSelector}</li>
 *     <li>[Form.AttachmentSelector]{@link Form.AttachmentSelector}</li>
 *     <li>[Form.Label]{@link Form.Label}</li>
 *     <li>[Form.RichEditor]{@link Form.RichEditor}</li>
 * </ul>
 *
 * @requires Energine
 * @requires ckeditor/ckeditor
 * @requires TabPane
 * @requires Toolbar
 * @requires Validator
 * @requires ModalBox
 * @requires Overlay
 * @requires datepicker
 * @requires Swiff.Uploader
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.1
 */

ScriptLoader.load('ckeditor/ckeditor', 'TabPane', 'Toolbar', 'Validator', 'ModalBox', 'Overlay', 'datepicker', 'Swiff.Uploader');

/**
 * Form.
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var Form = new Class(/** @lends Form# */{
    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    /**
     * The overlay.
     * @type {Overlay}
     */
    overlay: null,

    /**
     * Attached toolbar.
     * @type {Toolbar}
     */
    toolbar: null,

    /**
     * Array of RichEditors.
     * @type {RichEditor[]}
     */
    richEditors: [],

    /**
     * Array of Uploaders.
     * @type {Uploader[]}
     */
    uploaders: [],

    /**
     * Array of text boxes.
     * @type {Array}
     */
    textBoxes: [],

    /**
     * Array of date controls.
     * @type {Array}
     */
    dateControls: [],

    /**
     * Array of code editors.
     * @type {CodeMirror[]}
     */
    codeEditors: [],

//    smapSelectors: [],

    // constructor
    initialize: function (element) {
        Asset.css('form.css');

        this.overlay = new Overlay();

        /**
         * The component element.
         * @type {Element}
         */
        this.componentElement = $(element);

        /**
         * Value of property 'single_template'.
         * @type {string}
         */
        this.singlePath = this.componentElement.getProperty('single_template');

        /**
         * The main holder element.
         * @type {Element}
         */
        this.form = this.componentElement.getParent('form').addClass('form');

        /**
         * State of the form.
         * @type {string}
         */
        this.state = this.form.getElementById('componentAction').get('value');

        /**
         * Tab panels.
         * @type {TabPane}
         */
        this.tabPane = new TabPane(this.componentElement, {
            onTabChange: this.onTabChange
        });

        /**
         * The Validator.
         * @type {Validator}
         */
        this.validator = new Validator(this.form, this.tabPane);

        this.form.getElements('textarea.richEditor').each(function (textarea) {
            this.richEditors.push(new Form.RichEditor(textarea, this));
        }, this);

        this.form.getElements('textarea.code').each(function (textarea) {
            this.codeEditors.push(CodeMirror.fromTextArea(textarea, {mode: "text/html", tabMode: "indent", lineNumbers: true}));
        }, this);

        this.form.getElements('input.acpl').each(function (el) {
            new AcplField(el);
        }, this);

        var showHideFunc = function (e) {
            Energine.cancelEvent(e);
            var el = $(e.target),
                field = el.getParent('.field');

            if (field) {
                if (field.hasClass('min')) {
                    field.swapClass('min', 'max');
                } else if (el.hasClass('icon_min_max') && field.hasClass('max')) {
                    field.swapClass('max', 'min');
                }
            }
        };

        this.form.getElements('.field .control.toggle').addEvent('click', showHideFunc);
        this.form.getElements('.icon_min_max').addEvent('click', showHideFunc);

        this.form.getElements('.smap_selector').each(function (el) {
            new Form.SmapSelector(el, this);
        }, this);

        this.form.getElements('.attachment_selector').each(function (el) {
            new Form.AttachmentSelector(el, this);
        }, this);

        this.componentElement.getElements('.uploader').each(function (uploader) {
            this.uploaders.push(new Form.Uploader(uploader, this, 'upload/'));
        }, this);

        (this.componentElement.getElements('.inp_date')
            || []).append(this.componentElement.getElements('.inp_datetime')
                || []).each(function (dateControl) {
                var isNullable = !dateControl.getParent('.field').hasClass('required');
                this.dateControls.push(
                    (dateControl.hasClass('inp_date') ? Energine.createDatePicker(dateControl, isNullable)
                        : Energine.createDateTimePicker(dateControl, isNullable))
                );
            }, this);

        this.componentElement.getElements('.pane').setStyles({
            'border': '1px dotted #777',
            'overflow': 'auto'
        });

        /*Checking if opened in modalbox*/
        var mb = window.parent.ModalBox;
        if (mb && mb.initialized && mb.getCurrent()) {
            $(document.body).addEvent('keypress', function (evt) {
                if (evt.key == 'esc') {
                    mb.close();
                }
            });
        }
        this.componentElement.getElements('.crud').addEvent('click', function (e) {
            var control = $($(e.target).getProperty('data-field'));
            if (control) {
                ModalBox.open({
                    url: [this.singlePath, $(e.target).getProperty('data-field') , '-', $(e.target).getProperty('data-editor'), '/crud/'].join(''),
                    onClose: function (result) {
                        var selectedValue = result.key;
                        if (result.dirty) {
                            Energine.request(
                                [this.singlePath, $(e.target).getProperty('data-field'), '/fk-values/'].join(''),
                                null,
                                function (data) {
                                    if (data.result) {
                                        control.empty();
                                        var id = data.result[1];
                                        var title = data.result[2];
                                        data.result[0].each(function (row) {
                                            var option = new Element('option');
                                            Object.each(row, function (value, key) {
                                                if (key == id) {
                                                    option.setProperty('value', value);
                                                }
                                                else if (key == title) {
                                                    option.set('text', value);
                                                }
                                                else {
                                                    option.setProperty(key, value);
                                                }
                                            });
                                            control.grab(option);
                                        });
                                        if (selectedValue) {
                                            control.set('value', selectedValue);
                                        }
                                    }
                                },
                                this.processServerError.bind(this),
                                this.processServerError.bind(this)
                            );
                        }
                        else {
                            if (selectedValue) {
                                control.set('value', selectedValue);
                            }
                        }
                    }.bind(this)
                });
            }
        }.bind(this));

        /**
         * Controls, that appended with additional controls, like buttons.
         * @type {Element[]}
         */
        this.appendedControls = this.form.getElements('.with_append');
        this.appendedControls.each(function(el) {
            Object.append(el, {
                isOnFocus: false,
                controlEl: el
            });
            el.addEvents({
                mouseenter: this.glow.bind(this),
                mouseleave: this.glow.bind(this)
            });
        }, this);
        this.appendedControls.getElements('input,select').each(function(el, id) {
            el.each(function(el){
                el.controlEl = this.appendedControls[id];
            }.bind(this));

            el.addEvents({
                focus: this.glow.bind(this),
                blur: this.glow.bind(this)
            });
        }, this);
    },

    /**
     * Create required IFrame by tab changing.
     */
    onTabChange: function () {
        if (this.currentTab.getProperty('data-src') && !this.currentTab.loaded) {
            this.currentTab.pane.empty();
            this.currentTab.pane.grab(new Element('iframe', {
                src: Energine['static'] + this.currentTab.getProperty('data-src'),
                frameBorder: 0,
                scrolling: 'no',
                styles: {
                    width: '99%',
                    height: '99%'
                }
            }));
            this.currentTab.loaded = true;
        }
    },

    /**
     * Apply or remove glow effect to the appended buttons near the input fields.
     * @param {Object} ev Event. By default this function is connected to 'onFocus', 'onBlur', 'onMouseover' and 'onMouseout' events.
     */
    glow: function (ev) {
        switch (ev.type) {
            case 'focus':
                ev.target.controlEl.isOnFocus = true;
            case 'mouseenter':
                ev.target.controlEl.addClass('focus_block');
                ev.stopPropagation();
                break;

            case 'blur':
                ev.target.controlEl.isOnFocus = false;
            case 'mouseleave':
                if (!ev.target.controlEl.isOnFocus) {
                    ev.target.controlEl.removeClass('focus_block');
                    ev.stopPropagation();
                }
                break;
        }
    },

    /**
     * Attach the toolbar.
     *
     * @function
     * @public
     * @param {Toolbar} toolbar Toolbar that will be attached.
     */
    attachToolbar: function (toolbar) {
        this.toolbar = toolbar;
        var toolbarContainer = this.componentElement.getElement('.e-pane-b-toolbar'),
            afterSaveActionSelect = this.toolbar.getControlById('after_save_action');

        if (toolbarContainer) {
            toolbarContainer.adopt(this.toolbar.getElement());
        } else {
            this.componentElement.adopt(this.toolbar.getElement());
        }

        if (afterSaveActionSelect) {
            var savedActionState = Cookie.read('after_add_default_action');
            if (savedActionState) {
                afterSaveActionSelect.setSelected(savedActionState);
            }
        }
        toolbar.bindTo(this);
    },

    /**
     * Build the URL for saving.
     *
     * @function
     * @public
     * @return {string}
     */
    buildSaveURL: function () {
        return this.singlePath + 'save';
    },

    /**
     * Save all in the form.
     * @function
     * @public
     */
    save: function () {
        this.richEditors.each(function (editor) {
            editor.onSaveForm();
        });
        this.codeEditors.each(function (editor) {
            editor.save();
        });

        if (!this.validator.validate()) {
            return;
        }

        this.overlay.show();

        Energine.request(
            this.buildSaveURL(),
            this.form.toQueryString(),
            this.processServerResponse.bind(this),
            this.processServerError.bind(this),
            this.processServerError.bind(this)
        );
    },

    /**
     * Callback function by successful server response.
     *
     * @function
     * @public
     * @param {Object} response Result data from the server.
     */
    processServerResponse: function (response) {
        var nextActionSelector;
        if (response && (nextActionSelector = this.toolbar.getControlById('after_save_action'))) {
            Cookie.write('after_add_default_action', nextActionSelector.getValue(), {path: new URI(Energine.base).get('directory'), duration: 1});
            response.afterClose = nextActionSelector.getValue();
        }
        ModalBox.setReturnValue(response);
        this.overlay.hide();
        this.close();
    },

    /**
     * Callback function by server error.
     *
     * @function
     * @public
     * @param {Object} response Result data from the server.
     */
    processServerError: function (response) {
        this.overlay.hide();
    },

    /**
     * Close the form.
     * @function
     * @public
     */
    close: function () {
        ModalBox.close();
    },

    /**
     * Clear the file field.
     *
     * @function
     * @public
     * @param {string|number} fieldId
     * @param {} lnk
     */
    clearFileField: function (fieldId, lnk) {
        var preview;
        this.form.getElementById(fieldId).set('value', '');
        if (preview = this.form.getElementById(fieldId + '_preview')) {
            preview.removeProperty('href').hide();
        }
        lnk.hide();
    },

    /**
     * Process file result.
     *
     * @function
     * @public
     * @param {Object} result
     * @param {Element|string} button Button element.
     */
    processFileResult: function (result, button) {
        var image;

        if (!result) {
            return;
        }

        button = $(button);
        $(button.getProperty('link')).value = result['upl_path'];

        image = ($(button.getProperty('preview')).get('tag') == 'img')
            ? $(button.getProperty('preview'))
            : $(button.getProperty('preview')).getElement('img');

        if (image) {
            var src;
            switch (result['upl_internal_type']) {
                case 'image':
                    src = Energine.media + result['upl_path'];
                    break;
                case 'video':
                    src = Energine.resizer + 'w0-h0/' + result['upl_path'];
                    break;
                default:
                    src = Energine['static'] + 'images/icons/icon_undefined.gif';
            }

            image.setProperty('src', src);
            $(button.getProperty('preview')).setProperty('href', Energine.media + result['upl_path']).show();
        }

        if (button.getNext('.lnk_clear')) {
            button.getNext('.lnk_clear').show('inline');
        }
    },

    /**
     * Open the file library.
     *
     * @function
     * @public
     * @param {Element|string} button Button element.
     */
    openFileLib: function (button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        ModalBox.open({
            url: this.singlePath + 'file-library/',
            extraData: path,
            onClose: function (result) {
                this.processFileResult(result, button);
            }.bind(this)
        });
    },

    /**
     * Open the tag editor.
     *
     * @function
     * @public
     * @param {Element|string} button Button element.
     */
    openTagEditor: function (button) {
        var tags = $($(button).getProperty('link')).get('value');
        if (tags == '') {
            tags = null;
        }
        var overlay = this.overlay;
        overlay.show();
        new Request.JSON({
            'url': this.singlePath + 'tags/get-tag-ids/',
            'method': 'post',
            'data': {
                json: 1,
                tags: tags
            },
            'evalResponse': true,
            'onComplete': function (data) {
                overlay.hide();
                if (data) {
                    ModalBox.open({
                        url: this.singlePath + 'tags/show/' + ((data.data) ? encodeURIComponent(data.data.join(',')) + '/' : ''),
                        extraData: data.data,
                        onClose: function (result) {
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

    /**
     * Open th equick upload window.
     *
     * @function
     * @public
     * @param {Element|string} button Button element.
     */
    openQuickUpload: function (button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        var quick_upload_path = $(button).getProperty('quick_upload_path');
        var quick_upload_pid = $(button).getProperty('quick_upload_pid');
        var quick_upload_enabled = $(button).getProperty('quick_upload_enabled');
        var overlay = this.overlay;
        var processResult = this.processFileResult;

        if (!quick_upload_enabled) return;

        ModalBox.open({
            url: this.singlePath + 'file-library/' + quick_upload_pid + '/add',
            extraData: path,
            onClose: function (result) {
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
                            'onComplete': function (data) {
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

/**
 * File uploader.
 *
 * @constructor
 * @param uploaderElement
 * @param form
 * @param path
 */
Form.Uploader = new Class(/** @lends Form.Uploader# */{
    // constructor
    initialize: function (uploaderElement, form, path) {
        /**
         * The main uploader element.
         * @type {Element}
         */
        this.element = $(uploaderElement);
        if (!this.element) {
            return;
        }

        /*var cookieKeys = /(\w+)=(\w+);/i;
         console.log(cookieKeys.exec(document.cookie), document.cookie);
         //console.log(document.cookie.split(';').map(function(cook){console.log(cook); return 1;}));*/

        /**
         * The form.
         * @type {Form}
         */
        this.form = form;

        /**
         * swf uploader.
         * @type {Swiff.Uploader}
         */
        this.swfUploader = new Swiff.Uploader({
            path: 'scripts/Swiff.Uploader.swf',
            url: this.form.singlePath + path + '?json',
            verbose: (Energine.debug) ? true : false,
            queued: false,
            multiple: false,
            target: this.element,
            instantStart: true,
            appendCookieData: false,
            timeLimit: 0,
            data: {'NRGNCookie': document.cookie, 'path': (typeOf(ModalBox.getExtraData()) == 'string') ? ModalBox.getExtraData() : '', 'element': this.element.getProperty('nrgn:input')},
            typeFilter: {
                'All files (*.*)': '*.*',
                'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png',
                'Flash video (*.flv)': '*.flv'
            },
            onFileComplete: this.afterUpload.bind(this),
            onFileProgress: function (uploadInfo) {
                form.form.getElementById('indicator').set('text', uploadInfo.progress.percentLoaded + "%")
            },
            onFileOpen: function () {
                form.form.getElementById('loader').removeClass('hidden');
                form.form.getElementById('indicator').removeClass('hidden');
            },
            onComplete: function () {
                form.form.getElementById('loader').addClass('hidden');
                form.form.getElementById('indicator').addClass('hidden');
            },
            onFail: this.handleError.bind(this),
            onSelectFail: this.handleError.bind(this)
        });
    },

    /**
     * Callback function after upload.
     *
     * @function
     * @public
     * @param {} uploadInfo
     */
    afterUpload: function (uploadInfo) {
        this._show_preview(uploadInfo);
    },

    /**
     * Callback function for error handling.
     * @function
     * @public
     */
    handleError: function () {
        this.form.validator.showError(this.element, 'При загрузке файла произошла ошибка');
    },

    /**
     * Show the preview.
     *
     * @function
     * @private
     * @param file
     */
    _show_preview: function (file) {
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
                        new Element('img', {'border': 0}).inject(preview);
                }
                previewImg.setProperty('src', data.preview);
            }
        }
        else {
            this.form.validator.showError(this.element, 'При загрузке файла произошла ошибка');
        }
    },

    //todo Сделать удаление файла
    /**
     * Remove the file preview.
     *
     * @function
     * @public
     * @param {string} fieldId Field identifier.
     * @param {} control
     */
    removeFilePreview: function (fieldId, control) {
        var tmpNode;
        $(fieldId).value = '';

        if (tmpNode = $(fieldId + '_preview')) {
            tmpNode.setProperty('src', '');
        }

        if (tmpNode = $(fieldId + '_link')) {
            tmpNode.set('html', '');
        }
    }
});

/**
 * The smap (parent ID selector) selector.
 *
 * @constructor
 * @param {string|Element} selector The element id.
 * @param {Form} form The form.
 */
Form.SmapSelector = new Class(/** @lends Form.SmapSelector# */{
    /**
     * The properties of the smap.
     * @type {Object}
     *
     * @property {string} id The identifier.
     * @property {string} name The name.
     */
    smap: {
        id: '',
        name: ''
    },

    // constructor
    initialize: function (selector, form) {
        var selector = $(selector);

        /**
         * The form.
         * @type {Form}
         */
        this.form = form;

        /**
         * The value of the property 'field' from the stltctor by initialising.
         * @type {string}
         */
        this.field = selector.getProperty('field');

        selector.addEvent('click', function (e) {
            Energine.cancelEvent(e);
            this.smap.id = $($(e.target).getProperty('smap_id'));
            this.smap.name = $($(e.target).getProperty('smap_name'));
            this.showSelector.apply(this);
        }.bind(this));
    },

    /**
     * Show the selector.
     * @function
     * @public
     */
    showSelector: function () {
        ModalBox.open({
            url: this.form.componentElement.getProperty('template') + 'selector/',
            onClose: this.setName.bind(this)
        });
    },

    /**
     * Set the name and ID of the smap.
     *
     * @function
     * @public
     * @param {} result
     */
    setName: function (result) {
        if (result) {
            var name = '';
            if (result.site_name) {
                name += result.site_name + ' : ';
            }
            name += result.smap_name;
            this.smap.name.set('value', name);
            this.smap.id.set('value', result.smap_id);
        }

    }
});

/**
 * AttachmentSelector.
 *
 * @constructor
 * @param {string|Element} selector The element id.
 * @param {Form} form The form.
 */
Form.AttachmentSelector = new Class(/** @lends Form.AttachmentSelector# */{
    // constructor
    initialize: function (selector, form) {
        selector = $(selector);
        this.form = form;
        this.field = selector.getProperty('field');

        selector.addEvent('click', function (e) {
            Energine.cancelEvent(e);
            /**
             * Upload name.
             * @type {string}
             */
            this.uplName = $($(e.target).getProperty('upl_name'));
            /**
             * Upload ID.
             * @type {string|number}
             */
            this.uplId = $($(e.target).getProperty('upl_id'));
            this.showSelector.apply(this);
        }.bind(this));
    },

    /**
     * Show the selector.
     * @function
     * @public
     */
    showSelector: function () {
        ModalBox.open({
            url: this.form.componentElement.getProperty('template') + 'file-library/',
            onClose: this.setName.bind(this)
        });
    },

    /**
     * Set the name and ID of the smap.
     *
     * @function
     * @public
     * @param {} result
     */
    setName: function (result) {
        if (result) {
            this.uplName.set('value', result.upl_path);
            this.uplId.set('value', result.upl_id);
        }
    }
});

// Предназначен для последующей имплементации
// Содержит метод setLabel использующийся для привязки кнопки выбора разделов
/**
 * Contain the methods that will be implemented in other classes.
 *
 * @namespace
 */
Form.Label = /** @lends Form.Label */{
    /**
     * Set the label.
     *
     * @function
     * @static
     * @param {} result The server result.
     */
    setLabel: function (result) {
        var id = name = segment = segmentObject = '';

        if (typeOf(result) != 'null') {
            if (result) {
                id = result.smap_id;
                name = result.smap_name;
                segment = result.smap_segment;
            }

            $(this.obj.getProperty('hidden_field')).value = id;
            $(this.obj.getProperty('span_field')).innerHTML = name;

            if (segmentObject = $('smap_pid_segment')) {
                segmentObject.innerHTML = segment;
            }

            Cookie.write(
                'last_selected_smap',
                JSON.encode({'id': id, 'name': name, 'segment': segment}),
                {path: new URI(Energine.base).get('directory'), duration: 1}
            );
        }
    },

    /**
     * Prepare the label.
     *
     * @function
     * @static
     * @param {string} treeURL The URL of the tree.
     * @param {boolean|*} restore
     */
    prepareLabel: function (treeURL, restore) {
        if (!arguments[1]) {
            restore = false;
        }

        /**
         * Sitemap selector element.
         * @type {Element}
         */
        this.obj = $('sitemap_selector');
        if (this.obj) {
            this.obj.addEvent('click', this.showTree.pass(treeURL, this));
            if (restore) {
                this.restoreLabel();
            }
        }
    },

    /**
     * Show the tree.
     *
     * @function
     * @static
     * @param {string} url The URL.
     */
    showTree: function (url) {
        ModalBox.open({
            url: this.singlePath + url,
            onClose: this.setLabel.bind(this)
        });
    },

    /**
     * Restore the label.
     * @function
     * @static
     */
    restoreLabel: function () {
        var savedData = Cookie.read('last_selected_smap');
        if (this.obj && savedData) {
            savedData = JSON.decode(savedData);

            $(this.obj.getProperty('hidden_field')).value = savedData.id;
            $(this.obj.getProperty('span_field')).innerHTML = savedData.name;

            var segmentObject = $('smap_pid_segment');
            if (segmentObject) {
                segmentObject.innerHTML = savedData.segment;
            }
        }
    }
}

/**
 * The rich editor form.
 *
 * @constructor
 * @param {} textarea
 * @param {Form} form
 * @param {} fallback_ie
 */
Form.RichEditor = new Class(/** @lends Form.RichEditor# */{

    /**
     * @type {}
     */
    area: null,

    /**
     * Editor.
     * @type {CKEDITOR}
     */
    editor: null,

    // constructor
    initialize: function (textarea, form) {

        this.setupEditors();

        /**
         * The text area element.
         * @type {Element}
         */
        this.textarea = $(textarea);

        /**
         * The main form.
         * @type {Form}
         */
        this.form = form;
        try {
            this.editor = CKEDITOR.replace(this.textarea.get('id'));
            this.editor.editorId = this.textarea.get('id');
            this.editor.singleTemplate = this.form.singlePath;
        } catch (e) {
            console.warn(e);
        }
    },

    /**
     * Setup the editors.
     * @function
     * @public
     */
    setupEditors: function () {
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
                Object.each(window['wysiwyg_styles'], function (style) {
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

    /**
     * Save the form.
     * @function
     * @public
     */
    onSaveForm: function () {
        try {
            var data = this.editor.getData();
            this.textarea.value = data;
        } catch (e) {
            console.warn(e);
        }
    }
});

