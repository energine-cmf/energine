/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[ModalBox]{@link ModalBox}</li>
 * </ul>
 *
 * @requires Energine
 * @requires Overlay
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Overlay');

/**
 * Modal box
 * @namespace
 */
var ModalBox = window.top.ModalBox || /** @lends ModalBox */{
    /**
     * Array of boxes.
     * @type {Array}
     */
    boxes: [],

    /**
     * Defines whether the ModalBox is initialised or not.
     * @type {boolean}
     */
    initialized: false,

    /**
     * Initialisation.
     * @function
     * @static
     */
    init: function () {
        Asset.css('modalbox.css');
        /**
         * Overlay for the modal box.
         * @type {Overlay}
         */
        this.overlay = new Overlay(null, {indicator: false});
        this.initialized = true;
    },

    /**
     * Open the modal box.
     *
     * @function
     * @static
     * @param {Object} options Set of options for the modal box.
     */
    open: function (options) {
        function createIframe (mbName, iframeSrc) {
            return new Element('iframe').setProperties({
                'name': mbName,
                'src': iframeSrc,
                'frameBorder': '0',
                'scrolling': 'no',
                'class': 'e-modalbox-frame'
            });
        }

        // todo: I think it would better to make AbstractModalBox class. -- try
        var box = new Element('div').addClass('e-modalbox').inject(document.body);
        box.options = {
            url: null,
            onClose: function () {},
            extraData: null,
            post: null
        };
        Object.append(box.options, options);

        if (box.options.url) {
            var iframeSrc = box.options.url,
                mbName = 'modalBoxIframe' + this.boxes.length.toString();

            if (box.options.post) {
                var postForm = new Element('form', {target: mbName, action: iframeSrc, method: 'post'}).grab(new Element('input', {'type': 'hidden', 'name': 'modalBoxData', 'value': box.options.post}));
                iframeSrc = 'about:blank';
            }

            var iframe = createIframe(mbName, iframeSrc);

            box.iframe = iframe.inject(box);
            if (box.options.post) {
                box.grab(postForm);
                postForm.submit();
                postForm.destroy();
            }
        } else if (box.options.code) {
            box.grab(box.options.code);
        } else if (box.options.form) {
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
                form = new Element('form', {'class': 'e-grid-form form'}),
                tabPane = new Element('div', {'class': 'e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1', id: tabID}),
                sharedEvents = {'onmouseover': "this.className = 'highlighted';", 'onmouseout': "this.className=''"},
                saveCode = 'var w = window.parent.ModalBox;w.setReturnValue.call(w, {"result": document.getElementById("result").value});w.close.apply(w);';

            tabPane.adopt(
                new Element('div', {'class': 'e-pane-t-toolbar'}).grab(
                    new Element('ul', {'class': 'e-pane-toolbar e-tabs clearfix'}).grab(
                        new Element('li', {'unselectable': 'on', 'class': 'current'}).grab(
                            new Element('a', {'href': '#', 'html': (box.options.form.title || 'Properties')})
                        )
                    )
                ),
                new Element('div', {'class': 'e-pane-content'}).grab(
                    new Element('div', {'class': 'e-pane-item'}).grab(
                        new Element('div', {'class': 'field'}).adopt(
                            new Element('div', {'class': 'name'}).grab(
                                new Element('label', {'text': (box.options.form.field.title || 'Field value'), 'for': 'result'})
                            ),
                            new Element('div', {'class': 'control'}).grab(
                                new Element('textarea', {'id': 'result', 'value': (box.options.form.field.value || '')})
                            )
                        )
                    )
                ),
                new Element('div', {'class': 'e-pane-b-toolbar'}).grab(
                    new Element('ul', {'class': 'toolbar clearfix'}).adopt(
                        new Element('li', Object.merge({'unselectable': 'on', 'text': 'Save'}, sharedEvents, {'onclick': saveCode})),
                        new Element('li', Object.merge({'unselectable': 'on', 'text': 'Close'}, sharedEvents, {'onclick': 'var w = window.parent.ModalBox;w.close.apply(w);'}))
                    )
                )
            );
            form.grab(tabPane);
            /**
             * Финт ушами с задержкой
             * без задержки работать с Iframe нереально
             */
            (function () {
                var b = fakeIframe.contentDocument.body;
                b.className = 'e-singlemode-layout';
                form.inject(b);
                ['tabpane', 'form', 'toolbar', 'energine'].each(function (item) {
                    new Element('link', {'type': 'text/css', 'rel': 'stylesheet', 'href': Energine.base + 'stylesheets/' + item + '.css'}).inject(fakeIframe.contentDocument.head);
                });

            }).delay(30);

            box.grab(fakeIframe);
        }
        $(document.body).addEvent('keypress', this.keyboardListener.bind(this));

        box.closeButton = new Element('div').addClass('e-modalbox-close').inject(box);
        box.closeButton.addEvents({
            'click': this.close.bind(this)
        });

        this.boxes.push(box);
        if (this.boxes.length == 1) {
            this.overlay.show();
            this.overlay.element.removeEvents('click');
            this.overlay.element.addEvent('click', function () {
                this.close();
            }.bind(this));
        }
    },

    /**
     * Get the current modal box.
     *
     * @function
     * @static
     * @returns {Object}
     */
    getCurrent: function () {
        if (!this.boxes.length) {
            return null;
        }
        return this.boxes[this.boxes.length - 1];
    },

    /**
     * Get the extra data.
     *
     * @function
     * @static
     * @returns {null}
     */
    getExtraData: function () {
        var result = null;
        if (this.getCurrent()) {
            result = this.getCurrent().options.extraData;
        }

        return result;
    },

    /**
     * Store the return value in the modal box.
     *
     * @function
     * @static
     * @param {*} value Value that will be stored.
     */
    setReturnValue: function (value) {
        var result = this.getCurrent();
        if (result) {
            result.store('returnValue', value);
        }
    },

    /**
     * Close the modal box.
     * @function
     * @static
     */
    close: function () {
        if (!this.boxes.length) {
            return;
        }

        var box = this.boxes.pop();
        box.options.onClose(box.retrieve('returnValue'));

        var destroyBox = function () {
            if (box.iframe) {
                box.iframe.setProperty('src', 'about:blank');
                box.iframe.destroy();

                //After iframe was destroyed, focus has been lost, so focusing on main document
                if(window.parent.document.body.getElement('a'))
                    window.parent.document.body.getElement('a').focus();
            }
            box.destroy();
        };

        // todo: Do we really need this delay? Without this it seams all works fine. -- test in other browsers
        destroyBox.delay(1);

        if (!this.boxes.length) {
            this.overlay.hide();
        }
    },

    /**
     * Event handler for events from keyboard.
     * @param {Object} event Default event object.
     */
    keyboardListener: function (event) {
        switch (event.key) {
            case 'esc':
                if(this.getCurrent())
                    this.close();
                break;
        }
    }
};

if (!ModalBox.initialized) {
    window.addEvent('domready', ModalBox.init.bind(ModalBox));
}
