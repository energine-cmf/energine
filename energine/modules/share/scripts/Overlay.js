var Overlay = new Class({

    getOptions: function() {
        return {
            top: null,
            left: null,
            width: null,
            height: null,
            opacity: 0.25,
            hideObjects: true,
            indicator: {
                image: 'images/overlay_loading.gif',
                width: 32, height: 32
            }
        };
    },

    initialize: function(options) {
        this.setOptions(this.getOptions(), options);
        this.element = new Element('div').setStyles({
            'position': 'absolute',
            'z-index': '1000',
            'background': '#000',
            'text-align': 'center'
        }).injectInside(document.body);
        this.fx = this.element.effect('opacity', { wait: false }).hide();
        this.indicator = new Element('img').setProperties({
            'src': this.options.indicator.image,
            'width': this.options.indicator.width,
            'height': this.options.indicator.height
        }).setStyles({
            'position': 'absolute', 'top': '50%', 'left': '50%',
            'margin-top': -(this.options.indicator.height / 2) + 'px',
            'margin-left': -(this.options.indicator.width / 2) + 'px'
        }).injectInside(this.element);
    },

    show: function(options) {
        this.setOptions(this.options, options);
        this.element.setStyles({
            'top': options.top - (window.ie ? $(document.body).getStyle('margin-top').toInt() : 0) + 'px',
            'left': options.left + 'px',
            'width': options.width + 'px',
            'height': options.height + 'px'
        });
        this.setupObjects(true);
        this.fx.start(this.options.opacity);
    },

    hide: function() {
        this.fx.chain(this.setupObjects.pass(false, this)).start(0);
        this.fx.start(0);
    },

    setupObjects: function(hide) {
        if (!this.options.hideObjects) return;
        var elements = $A(document.getElementsByTagName('object'));
        elements.extend(document.getElementsByTagName(window.ie ? 'select' : 'embed'));
        elements.each(function(element) { element.style.visibility = hide ? 'hidden' : ''; });
    }
});

Overlay.implement(new Options);