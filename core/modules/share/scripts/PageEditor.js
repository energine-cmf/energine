/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[PageEditor]{@link PageEditor}</li>
 *     <li>[PageEditor.BlockEditor]{@link PageEditor.BlockEditor}</li>
 * </ul>
 *
 * @requires Energine
 * @requires ckeditor/ckeditor
 * @requires ModalBox
 * @requires Overlay
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('ckeditor/ckeditor', 'ModalBox', 'Overlay');

/**
 * @class PageEditor
 * @classdesc Page editor.
 */
var PageEditor = new Class(/** @lends PageEditor# */{
    // todo: Make it sense to store this two members in the object? They used only by initialize.
    /**
     * Editor class name.
     * @type {string}
     */
    editorClassName:'nrgnEditor',

    /**
     * Array of block editors.
     * @type {PageEditor.BlockEditor[]}
     */
    editors:[],

    initialize:function () {
        Asset.css('pageeditor.css');

        CKEDITOR.disableAutoInline = true;
        CKEDITOR.config.extraPlugins = 'sourcedialog,energineimage,energinefile';
        CKEDITOR.config.removePlugins = 'sourcearea';
        CKEDITOR.config.allowedContent = true;
        CKEDITOR.config.toolbar = [
            { name: 'document', groups: [ 'mode' ], items: [ 'Sourcedialog' ] },
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

        $(document.body).getElements('.' + this.editorClassName).each(function (element) {
            this.editors.push(new PageEditor.BlockEditor(this, element));
        }, this);

        if (Browser.opera) {
            window.addEvent('unload', function (e) {
                if (this.editors.length) {
                    this.editors.each(function(editor) {
                        editor.save(false);
                    }.bind(this));
                    // todo: What is it?
                    window.location.href = window.location.href;
                    return '';
                }
            }.bind(this));
        } else {
            window.addEvent('beforeunload', function (e) {
                if (this.editors.length) {
                    this.editors.each(function(editor) {
                        editor.save(false);
                    }.bind(this));
                }
            }.bind(this));
        }
    }
});

/**
 * Block editor.
 *
 * @constructor
 * @param pageEditor
 * @param area
 */
PageEditor.BlockEditor = new Class(/** @lends PageEditor.BlockEditor# */{
    // constructor
    initialize:function (pageEditor, area) {
        /**
         * Area element.
         * @type {Element}
         */
        this.area = area;
        this.area.setProperty('contenteditable', true);

        // todo: Need?
        /**
         * Page editor.
         * @type {PageEditor}
         */
        this.pageEditor = pageEditor;

        /**
         * Defines whether the editor is active.
         * @type {boolean}
         */
        this.isActive = false;

        /**
         * Single path.
         * @type {string}
         */
        this.singlePath = this.area.getProperty('single_template');

        /**
         * Block editor ID.
         * @type {string}
         */
        this.ID = this.area.getProperty('eID') ? this.area.getProperty('eID') : '';

        // todo: What is num?
        /**
         * Number.
         * @type {string}
         */
        this.num = this.area.getProperty('num') ? this.area.getProperty('num') : '';

        /**
         * Editor.
         * @type {CKEDITOR}
         */
        this.editor = CKEDITOR.inline(this.area.get('id'));
        this.editor.singleTemplate = this.area.getProperty('single_template');
        this.editor.editorId = this.area.get('id');

        /**
         * Overlay.
         * @type {Overlay}
         */
        this.overlay = new Overlay();
    },

    /**
     * Save.
     *
     * @function
     * @public
     * @param {boolean} [async = true] Defines whether the request be asynchronous or not.
     */
    save:function (async) {
        if (async == undefined) {
            async = true;
        }
        if (!async) {
            this.overlay.show();
        }

        var data = 'data=' + encodeURIComponent(this.editor.getData());
        if (this.ID) {
            data += '&ID=' + this.ID;
        }
        if (this.num) {
            data += '&num=' + this.num;
        }

        new Request({
            url:this.singlePath + 'save-text',
            async: async,
            method:'post',
            data: data,
            onSuccess: function (response) {
                this.editor.setData(response);
                if (!async) {
                    this.overlay.hide();
                }
            }.bind(this)
        }).send();
    }
});
