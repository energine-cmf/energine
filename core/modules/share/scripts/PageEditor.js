ScriptLoader.load('Toolbar', 'RichEditor', 'ModalBox', 'Overlay');

var PageEditor = new Class({
    editorClassName: 'nrgnEditor',
    editors: [],

    initialize: function() {
        Asset.css('pagetoolbar.css');
        Asset.css('pageeditor.css');
        $(document.body).getElements('.' + this.editorClassName).each(function(element) {
            this.editors.push(new PageEditor.BlockEditor(this, element));
        }, this);

        document.addEvent('click', this.processClick.bindWithEvent(this));
        
        window.addEvent('beforeunload', function(e) {
            if (this.activeEditor) {
                this.activeEditor.save(false);
            }
        }.bind(this));

		this.attachToolbar(this.createToolbar());

    },
	createToolbar: function(){
		var toolbar = new Toolbar('wysiwyg_toolbar');
        toolbar.dock();
		/*toolbar.appendControl(new Toolbar.Button({ id: 'save', icon: 'images/toolbar/save.gif', title: Energine.translations.get('BTN_SAVE'), state: 'save' }));
		toolbar.appendControl(new Toolbar.Separator({ id: 'sep2' }));*/
		toolbar.appendControl(new Toolbar.Button({ id: 'bold', icon: 'images/toolbar/bold.gif', title: Energine.translations.get('BTN_BOLD'), action: 'bold' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'italic', icon: 'images/toolbar/italic.gif', title: Energine.translations.get('BTN_ITALIC'), action: 'italic' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'olist', icon: 'images/toolbar/olist.gif', title: Energine.translations.get('BTN_OL'), action: 'olist' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'ulist', icon: 'images/toolbar/ulist.gif', title: Energine.translations.get('BTN_UL'), action: 'ulist' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'link', icon: 'images/toolbar/link.gif', title: Energine.translations.get('BTN_HREF'), action: 'link' }));
		toolbar.appendControl(new Toolbar.Separator({ id: 'sep3' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'left', icon: 'images/toolbar/justifyleft.gif', title: Energine.translations.get('BTN_ALIGN_LEFT'), action: 'alignLeft' }));
        toolbar.appendControl(new Toolbar.Button({ id: 'center', icon: 'images/toolbar/justifycenter.gif', title: Energine.translations.get('BTN_ALIGN_CENTER'), action: 'alignCenter' }));
        toolbar.appendControl(new Toolbar.Button({ id: 'right', icon: 'images/toolbar/justifyright.gif', title: Energine.translations.get('BTN_ALIGN_RIGHT'), action: 'alignRight' }));
        toolbar.appendControl(new Toolbar.Button({ id: 'justify', icon: 'images/toolbar/justifyall.gif', title: Energine.translations.get('BTN_ALIGN_JUSTIFY'), action: 'alignJustify' }));
		toolbar.appendControl(new Toolbar.Select({ id: 'selectFormat', action: 'changeFormat' },
		 {'':'', 'reset':Energine.translations.get('TXT_RESET'),'H1':Energine.translations.get('TXT_H1'), 'H2':Energine.translations.get('TXT_H2'), 'H3':Energine.translations.get('TXT_H3'),'H4':Energine.translations.get('TXT_H4'), 'H5':Energine.translations.get('TXT_H5'), 'H6':Energine.translations.get('TXT_H6'), 'ADDRESS':Energine.translations.get('TXT_ADDRESS')}));
		toolbar.appendControl(new Toolbar.Separator({ id: 'sep4' }));
        toolbar.appendControl(new Toolbar.Button({ id: 'source', icon: 'images/toolbar/source.gif', title: Energine.translations.get('BTN_VIEWSOURCE'), action: 'showSource' }));
		toolbar.appendControl(new Toolbar.Separator({ id: 'sep5' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'imagemngr', icon: 'images/toolbar/image.gif', title:Energine.translations.get('BTN_INSERT_IMAGE'), action: 'imageManager' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'filemngr', icon: 'images/toolbar/filemngr.gif', title: Energine.translations.get('BTN_FILE_LIBRARY'), action: 'fileLibrary' }));
		toolbar.appendControl(new Toolbar.Button({ id: 'extflash', icon: 'images/toolbar/embed.gif', title: Energine.translations.get('BTN_EXT_FLASH'), action: 'insertExtFlash' }));
		toolbar.bindTo(this);
		return toolbar;
	},
    attachToolbar: function(toolbar) {
        this.toolbar = toolbar;
        this.toolbar.getElement().injectInside(
            document.getElement('.e-topframe')
        );
        this.toolbar.disableControls();
        
        var html = $$('html')[0];
        if(html.hasClass('e-has-topframe2')) {
                html.removeClass('e-has-topframe2');
                html.addClass('e-has-topframe3');
        }
        else if(html.hasClass('e-has-topframe1')) {
                html.removeClass('e-has-topframe1');
                html.addClass('e-has-topframe2');
        }
        
    },

    getEditorByElement: function(element) {
        var result = false;
        this.editors.each(function(editor) {
            if (editor.area == element) {
                result = editor;
            }
        });
        return result;
    },

    processClick: function(event) {
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
    processKeyEvent: function() {
        if (this.activeEditor) {
            this.activeEditor.dirty = true;
        }
    }
});

PageEditor.BlockEditor = new Class({
	Extends: RichEditor,

    initialize: function(pageEditor, area) {
        this.pageEditor = pageEditor;
        this.parent(area);

        this.singlePath  = this.area.getProperty('single_template');
        this.ID = this.area.getProperty('eID') ? this.area.getProperty('eID') : false;
        this.num = this.area.getProperty('num') ? this.area.getProperty('num') : false;
        

        if (Energine.supportContentEdit && !this.fallback_ie) {
            document.addEvent('keydown', this.pageEditor.processKeyEvent.bind(this.pageEditor));
            if(!(this.pasteArea = $('pasteArea'))){
                this.pasteArea = new Element('div', {'id':'pasteArea'}).setStyles({ 'visibility': 'hidden', 'width': '0', 'height': '0', 'font-size': '0', 'line-height': '0' }).injectInside(document.body);
            }
			//addEvent('paste' работать не захотело
            if(Browser.Engine.trident) this.area.onpaste = this.processPasteFF.bindWithEvent(this);
            else if(Browser.Engine.gecko || Browser.Engine.presto) this.area.onpaste = this.processPasteFF.bindWithEvent(this);
        }
        //this.switchToViewMode = this.pageEditor.switchToViewMode;
		this.overlay = new Overlay();
    },

    focus: function() {
        this.area.addClass('activeEditor');
        var toolbar = this.pageEditor.toolbar.bindTo(this);
        if (!Energine.supportContentEdit) {
//            if (this.dirty) toolbar.getControlById('save').enable();
            return;
        }
        toolbar.enableControls();
//        toolbar.getControlById('save').disable();
        this.area.contentEditable = true;
    },

    blur: function() {
        this.area.removeClass('activeEditor');
        this.pageEditor.toolbar.bindTo(this.pageEditor).disableControls();
        if (!Energine.supportContentEdit) {
            return;
        }
        if(this.dirty) this.save();
        //this.pageEditor.toolbar.getControlById('save').disable();
        this.area.contentEditable = 'false';
    },

    showSource: function() {
        this.blur();
        ModalBox.open({
            url: this.singlePath + 'source',
            extraData: this.cleanMarkup('dummy', this.area.innerHTML),
            onClose: function(returnValue) {
                if (returnValue || (returnValue === '')) {
                    this.area.set('html',this.cleanMarkup('dummy', returnValue));
                    this.dirty = true;
                }
                this.focus();
            }.bind(this)
        });
    },

    save: function(async) {
        if(async == undefined) async = true;
        this.dirty = false;
		var data = 'data='+encodeURIComponent(this.area.innerHTML);
		if (this.ID) data += '&ID='+this.ID;
        if (this.num) data += '&num='+this.num;
        if(!async) this.overlay.show();

		new Request({
			url: this.singlePath + 'save-text',
            'async': async,
            method: 'post',
            'data': data,
            onSuccess: function(response){
				this.area.innerHTML = response;
				if(!async)this.overlay.hide();
			}.bind(this)
        }).send();
    },

    saveWithConfirmation: function() {
        if (this.dirty && confirm('Хотите сохранить изменённый блок?')) {
            this.save();
        }
    },
    cleanMarkup: function(dummyPath, data, aggressive) {
        return this.parent(this.singlePath, data, aggressive);
    }
});
