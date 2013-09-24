var Overlay = new Class({
    Implements: Options,
    options:{
        opacity: 0.5,
        hideObjects: true,
        indicator: true
    },

    initialize: function(parentElement, options) {
        Asset.css('overlay.css');
        this.setOptions(options);

        //определяем родительский элемент
        parentElement = parentElement ? parentElement : document.body;
        if (!parentElement.getElement) parentElement = document;
        this.parentElement = parentElement;
        //создаем елемент но не присоединяем его
        this.element = new Element('div', {'class': 'e-overlay' + ((this.options.indicator) ? ' e-overlay-loading' : ''), 'styles':{'opacity': 0}});
    },
    show: function() {
        this.setupObjects(true);
        if (!this.parentElement.getChildren('.e-overlay').length) {
            this.element.injectInside(this.parentElement);

        }
        this.element.fade(this.options.opacity);
    },

    hide: function() {
        var fx = new Fx.Tween(this.element, {property: 'opacity'});
        this.setupObjects(false);
        fx.start(this.options.opacity, 0).chain(
            function() {
                this.start(0);
            },
            function() {
                this.element = this.element.dispose();
            }.bind(this)
        );
    },
    setupObjects: function(hide) {

        var body;
        if (!this.options.hideObjects) return;
        var elements = $A((body = $(document.body)).getElements('object'));
        elements.extend(
            $A(body.getElements(Browser.Engine.trident ? 'select' : 'embed'))
        );
        elements.each(function(element) {
            element.style.visibility = hide ? 'hidden' : '';
        });
    }
});
