ScriptLoader.load('Toolbar');

var ForumRTE = new Class({
    initialize: function(element) {
        if ((this.componentElement = $(element)) && (this.srcElement =
                this.componentElement.getElement('.simpleRTE'))) {
            this._attachToolbar(this._createToolbar());

            if ($(document.body).contentEditable) {
                this.valueField =
                        new Element('input', {'type': 'hidden', 'name':this.srcElement.getProperty('name'), 'id': this.srcElement.getProperty('id')}).inject(this.componentElement);

                this.editor = new Element('div');
                this.editor.addClass(this.srcElement.className);
                this.editor.replaces(this.srcElement);
                //this.editor.innerHTML = '&#8203;';
                this.editor.contentEditable = true;

                this.editor.addEvents({
                    'focus': function(evt) {
                        evt = new Event(evt || window.event);
                        var element = $(evt.target);
                        if (!element.get('html')) {
                            element.set('html', ((Browser.Engine.gecko)?'&#8203;':''));
                        }
                    }.bind(this)
                });

                this.editor.onpaste = Energine.cancelEvent//this._processPaste.bindWithEvent(this);
                this.editor.ondrop = Energine.cancelEvent;
                this.editor.ondragenter = Energine.cancelEvent;
            }
            else {
                this.editor = this.srcElement;
            }

        }
    },
    getText: function() {
        return this.editor.get('html');
    },
    setText: function(text) {
        this.editor.set('html', text);
        this.valueField.set('value', text);
    },
    sync: function() {
        this.valueField.set('value', this.getText());
    },
    _attachToolbar: function(toolbar) {
        this.toolbar = toolbar;
        this.toolbar.getElement().inject(
                this.componentElement.getElement('.comment_field'),
                'top'
                );

    },
    _createToolbar: function() {
        var toolbar = new Toolbar('comment_toolbar');
        toolbar.dock();
        toolbar.appendControl(new Toolbar.Button({ id: 'bold', icon: 'images/toolbar/bold.gif', action: 'bold' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep0'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'italic', icon: 'images/toolbar/italic.gif', action: 'italic' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep01'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'smiley', icon: 'images/smileys/smile.gif', action: 'smile' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep1'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'sad', icon: 'images/smileys/sad.gif', action: 'sad' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep2'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'shamed', icon: 'images/smileys/shamed.gif', action: 'shamed' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep3'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'silent', icon: 'images/smileys/silent.gif', action: 'silent' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep4'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'winking', icon: 'images/smileys/winking.gif', action: 'winking' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep5'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'teasing', icon: 'images/smileys/teasing.gif', action: 'teasing' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep6'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'laughing', icon: 'images/smileys/laughing.gif', action: 'laughing' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep7'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'displeased', icon: 'images/smileys/displeased.gif', action: 'displeased' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep8'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'crying', icon: 'images/smileys/crying.gif', action: 'crying' }));
        toolbar.appendControl(new Toolbar.Separator({ id: 'sep9'}));
        toolbar.appendControl(new Toolbar.Button({ id: 'cool', icon: 'images/smileys/cool.gif', action: 'cool' }));

        toolbar.bindTo(this);
        return toolbar;
    },
    insertQuote: function(quoteText, userName) {
        var quoteBox = [new Element('div', {'class':'quote_box'}).adopt([
            new Element('div', {'class':'quote_author', 'unselectable': 'on'}).set('text', userName),
            new Element('div', {'class':'quote'}).set('html', quoteText)
        ])];
        quoteBox.push(new Element('br'));

        this.editor.adopt(quoteBox, 'top');
        this.setCaretToEnd();
    },
    bold:function() {
        this._executeWYSIWYGCommand(
                this._execCommand.pass('bold')
                );
    },
    italic:function() {
        this._executeWYSIWYGCommand(
                this._execCommand.pass('italic')
                );
    },
    smile: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('smile')
                );
    },
    cool: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('cool')
                );
    },
    crying: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('crying')
                );
    },
    displeased: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('displeased')
                );
    },
    laughing: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('laughing')
                );
    },
    teasing: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('teasing')
                );
    },
    winking: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('winking')
                );
    },
    silent: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('silent')
                );
    },
    sad: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('sad')
                );
    },
    shamed: function() {
        this._executeWYSIWYGCommand(
                this._insertSmile.pass('shamed')
                );
    },
    setCaretToEnd: function () {
        var el = this.editor, sel, range;
        this.editor.focus();
        if (window.getSelection && document.createRange) {
            range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (document.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText(el);
            range.collapse(false);
            range.select();
        }
    },
    _selectionIsUnder: function(parentElement){
        parentElement = $(parentElement);
        var range, commonAncestor;
        if(window.getSelection){
            range = window.getSelection().getRangeAt(0);
            commonAncestor = range.commonAncestorContainer;
        }
        else if(document.selection){
            range = document.selection.createRange();
            commonAncestor = range.parentElement();
        }
        if(!(commonAncestor = $(commonAncestor))) return false;
        return ((commonAncestor == parentElement) || parentElement.hasChild(commonAncestor));
    },
    _executeWYSIWYGCommand: function(func) {
        if(!this._selectionIsUnder(this.editor))this.setCaretToEnd();

        func();
    },
    _insertSmile: function(smile) {

        var imgString = '<span class ="smiley unselectable smiley_' + smile + '" unselectable="on">' + smile + '</span>';
        if (Browser.Engine.gecko) {
            document.execCommand('insertHTML', false, imgString);
        }
        else if (Browser.Engine.trident) {

            var range = document.selection.createRange();
            range.select();
            range.pasteHTML(imgString);
        }
    },
    _execCommand: function(cmd, value) {
        value = value || null;
        var selection = (document.selection || window.getSelection());

        if (Browser.Engine.gecko) {
            document.execCommand('styleWithCSS', false, false);
        }

        if (selection.createRange) {
            selection.createRange().execCommand(cmd, false, value);
        }
        else if (selection.getRangeAt) {
            /*var range = selection.getRangeAt(0);
             range.execCommand(cmd);*/
            document.execCommand(cmd, false, value);
        }

    },
    _prepareQuote: function(btn) {
        var selectedText = '';
        var commentElement = $(btn.getParent('table.feed_item_f').getElements('.feed_announce')[0]);

        if (document.selection) {
            //Для ИЕ сотоварищи
            if (this._selectionIsUnder(commentElement))
                selectedText = document.selection.createRange().htmlText;
        }
        else if (window.getSelection &&
                window.getSelection().toString()) {
            var range = window.getSelection().getRangeAt(0).cloneRange();
            if (this._selectionIsUnder(commentElement)) {
                var newNode = document.createElement('div');
                newNode.appendChild(range.cloneContents());
                selectedText = newNode.innerHTML;
                range.detach();
            }
        }
        if (!selectedText) {
            selectedText = commentElement.get('html');
        }
        return selectedText;
    }
});
