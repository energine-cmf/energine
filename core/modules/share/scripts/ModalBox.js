ScriptLoader.load(
    'Overlay'
);

var ModalBox = window.top.ModalBox || {

    boxes: [],

    init: function() {
        Asset.css('modalbox.css');
        this.overlay = new Overlay(document.body, {indicator: false});
        this.initialized = true;
    },
    /**
     *
     * @param Object options
     */
    open: function(options) {
        var createIframe = function(mbName, iframeSrc) {
            var iframe;
            if (Browser.Engine.trident && (Browser.version < 9)) {
                iframe = $(document.createElement('<iframe class="e-modalbox-frame" src="' + iframeSrc + '" frameBorder="0" name="' + mbName + '" scrolling="no" />'));
            }
            else {
                iframe = new Element('iframe').setProperties(
                    {
                        'name': mbName,
                        'src': iframeSrc,
                        'frameBorder': '0',
                        'scrolling': 'no',
                        'class': 'e-modalbox-frame'
                    }
                )
            }
            return iframe;
        }

        var box = new Element('div').addClass('e-modalbox').injectInside(document.body);
        box.options = {
            url: null,
            onClose: $empty,//$empty,
            extraData: null,
            post: null
        };
        $extend(box.options, $pick(options, {}));

        if (box.options.url) {
            var iframeSrc = box.options.url,
                mbName = 'modalBoxIframe' + this.boxes.length.toString();

            if (box.options.post) {
                var postForm = new Element('form', {target: mbName, action: iframeSrc, method: 'post'}).grab(new Element('input', {'type': 'hidden', 'name': 'modalBoxData', 'value': box.options.post}));
                iframeSrc = 'about:blank';
            }

            var iframe = createIframe(mbName, iframeSrc);

            box.iframe = iframe.injectInside(box);
            if (box.options.post) {
                box.grab(postForm);
                postForm.submit();
                postForm.destroy();
            }
        }
        else if (box.options.code) {

            //box.set('html', code);
            box.grab(box.options.code);
        }
        else if (box.options.form) {
            /*
             * Тут все очень не просто и требует пояснений
             * Мы создаем пустой iframe в который пишем код формы
             * Причина - верстка с position:fixed
             * В Iframe нет мутулза
             * Поэтому код внутри него идет просто текстом
             * Events тоже как то странно вешаются(возникает ошибка при исполнении)
             * Поэтому все обработчки прописаны текстом
             */
            var tabID = 'id' + ((Math.random() * 10000).toInt()),
                fakeIframe = createIframe('iframe' + tabID, 'about:blank'),
                form = new Element('form', {'class':'e-grid-form form'}),
                tabPane = new Element('div', {'class': 'e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1', id:tabID}),
                mainTab,
                sharedEvents = {'onmouseover':"this.className = 'highlighted';", 'onmouseout':"this.className=''"},
                saveCode = 'var w = window.parent.ModalBox;w.setReturnValue.call(w, {"result": document.getElementById("result").value});w.close.apply(w);';

            tabPane.adopt(
                new Element('div', {'class':'e-pane-t-toolbar'}).grab(
                    new Element('ul', {'class':'e-pane-toolbar e-tabs clearfix'}).grab(
                        new Element('li', {'unselectable':'on', 'class':'current'}).grab(
                            new Element('a', {'href':'#', 'html':(box.options.form.title || 'Properties')})
                        )
                    )
                ),
                new Element('div', {'class':'e-pane-content'}).grab(
                    mainTab = new Element('div', {'class':'e-pane-item'}).grab(
                        new Element('div', {'class':'field'}).adopt(
                            new Element('div', {'class':'name'}).grab(
                                new Element('label',{'text':(box.options.form.field.title || 'Field value'), 'for':'result'})
                            ),
                            new Element('div', {'class':'control'}).grab(
                                new Element('textarea', {'id':'result', 'value': (box.options.form.field.value || '')})
                            )
                        )
                    )
                ),
                new Element('div', {'class':'e-pane-b-toolbar'}).grab(
                    new Element('ul', {'class': 'toolbar clearfix'}).adopt(
                        new Element('li', Object.merge({'unselectable':'on', 'text':'Save'}, sharedEvents, {'onclick':saveCode})),
                        new Element('li', Object.merge({'unselectable':'on', 'text':'Close'}, sharedEvents, {'onclick':'var w = window.parent.ModalBox;w.close.apply(w);'}))
                    )
                )
            );
            form.grab(tabPane);
            /**
             * Финт ушами с задержкой
             * без задержки работать с Iframe нереально
             */
            (function() {
                var b = fakeIframe.contentDocument.body;
                b.className = 'e-singlemode-layout';
                form.inject(b);
                ['tabpane', 'form', 'toolbar', 'energine'].each(function(item){
                    new Element('link', {'type':'text/css', 'rel': 'stylesheet', 'href':Energine.base + 'stylesheets/' + item + '.css'}).inject(fakeIframe.contentDocument.head);
                });

            }).delay(30);

            //box.set('html', code);
            box.grab(fakeIframe);
        }
        //box.iframe.addEvent('keydown', this.keyboardListener.bindWithEvent(this));
        box.closeButton = new Element('div').addClass('e-modalbox-close').injectInside(box);
        box.closeButton.addEvents({
            'click': this.close.bind(this),
            'mouseover': function() {
                this.addClass('highlighted');
            },
            'mouseout': function() {
                this.removeClass('highlighted');
            }
        });

        this.boxes.push(box);

        if (this.boxes.length == 1) {
            this.overlay.show();
        }

    },

    getCurrent: function() {
        return this.boxes[this.boxes.length - 1];
    },

    getExtraData: function() {
        var result = null;
        if (this.getCurrent()) {
            result = this.getCurrent().options.extraData;
        }

        return result;
    },

    setReturnValue: function(value) {
        var result = this.getCurrent();
        if (result) {
            result.store('returnValue', value);
        }
    },

    close: function() {
        if (!this.boxes.length) {
            return;
        }
        var box = this.boxes.pop();
        box.options.onClose(box.retrieve('returnValue'));

        var destroyBox = function() {
            if (box.iframe) {
                box.iframe.setProperty('src', 'about:blank');
                box.iframe.destroy();
            }
            box.destroy();
        }

        destroyBox.delay(1);

        if (!this.boxes.length) {
            this.overlay.hide();
        }
    },

    keyboardListener: function(event) {
        switch (event.key) {
            case 'esc':
                this.close();
                break;
        }
    }
};

if (!ModalBox.initialized) {
    window.addEvent('domready', ModalBox.init.bind(ModalBox));
}
