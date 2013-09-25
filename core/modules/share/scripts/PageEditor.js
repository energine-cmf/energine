ScriptLoader.load('ckeditor/ckeditor', 'ModalBox', 'Overlay');

var PageEditor = new Class({
    editorClassName:'nrgnEditor',
    editors:[],

    initialize:function () {

        Asset.css('pageeditor.css');

        CKEDITOR.disableAutoInline = true;
        CKEDITOR.config.extraPlugins = 'sourcedialog,energineimage,energinefile';
        CKEDITOR.config.removePlugins = 'sourcearea';
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

PageEditor.BlockEditor = new Class({

    initialize:function (pageEditor, area) {
        this.area = area;
        this.area.setProperty('contenteditable', true);
        this.pageEditor = pageEditor;
        this.isActive = false;
        this.singlePath = this.area.getProperty('single_template');
        this.ID = this.area.getProperty('eID') ? this.area.getProperty('eID') : false;
        this.num = this.area.getProperty('num') ? this.area.getProperty('num') : false;
        this.editor = CKEDITOR.inline(this.area.get('id'));
        this.editor.singleTemplate = this.area.getProperty('single_template');
        this.editor.editorId = this.area.get('id');
        this.overlay = new Overlay();
    },

    save:function (async) {
        if (async == undefined) async = true;
        var data = 'data=' + encodeURIComponent(this.editor.getData());
        if (this.ID) data += '&ID=' + this.ID;
        if (this.num) data += '&num=' + this.num;
        if (!async) this.overlay.show();

        new Request({
            url:this.singlePath + 'save-text',
            'async':async,
            method:'post',
            'data':data,
            onSuccess:function (response) {
                this.editor.setData(response);
                if (!async)this.overlay.hide();
            }.bind(this)
        }).send();
    }

});
