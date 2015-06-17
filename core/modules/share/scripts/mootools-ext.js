Class.Mutators = Object.append(Class.Mutators, {
    Static: function (m) {
        this.extend(m);
    },
    Protected: function (m) {
        for (var k in m) {
            if (m[k] instanceof Function) {
                this.implement(k, m[k].protect());
            }
        }
    }
});

(function () {
    Browser[Browser.name] = true;
    Browser[Browser.name + parseInt(Browser.version, 10)] = true;

    if (Browser.name == 'ie' && Browser.version >= '11') {
        delete Browser.ie;
    }

    var platform = Browser.platform;
    if (platform == 'windows') {
        platform = 'win';
    }
    Browser.Platform = {
        name: platform
    };
    Browser.Platform[platform] = true;
})();

Asset = Object.append(Asset, {
    loaded: {css: {}},
    cssParent: Asset.css,
    css: function (source) {
        if (Asset.loaded.css[source]) return null;
        var fs = ((Energine['static']) ? Energine['static'] : '') + 'stylesheets/' + source;
        Asset.loaded.css[source] = fs;
        return Asset.cssParent(fs, {'media': 'Screen, projection'});
    }
});

Element.implement({
    getComputedStyle: function (p) {
        var f = (document.html.style.cssFloat == null) ? 'styleFloat' : 'cssFloat',
            d = Element.getDocument(this).defaultView,
            c = d ? d.getComputedStyle(this, null) : null;
        return (c) ? c.getPropertyValue((p == f) ? 'float' : p.hyphenate()) : null;
    },
    getComputedSize: function (options) {
        function getStylesList(sts, p) {
            var l = [];
            Object.each(p, function (d) {
                Object.each(d, function (e) {
                    sts.each(function (s) {
                        l.push(s + '-' + e + (s == 'border' ? '-width' : ''));
                    });
                });
            });
            return l;
        }

        function calculateEdgeSize(e, sts) {
            var t = 0;
            Object.each(sts, function (v, s) {
                if (s.test(e)) t += v.toInt();
            });
            return t;
        }

        options = Object.merge({
            styles: ['padding', 'border'],
            planes: {
                height: ['top', 'bottom'],
                width: ['left', 'right']
            },
            mode: 'both'
        }, options);
        var sts = {}, s = {width: 0, height: 0}, d;
        if (options.mode == 'vertical') {
            delete s.width;
            delete options.planes.width;
        } else if (options.mode == 'horizontal') {
            delete s.height;
            delete options.planes.height;
        }
        getStylesList(options.styles, options.planes).each(function (s) {
            var v = parseInt(this.getStyle(s));
            sts[s] = isNaN(v) ? 0 : v;
        }, this);
        Object.each(options.planes, function (es, p) {
            var c = p.capitalize(), st = this.getStyle(p);
            if (st == 'auto' && !d) d = this.getDimensions();
            var v = (st == 'auto') ? d[p] : parseInt(st);
            st = sts[p] = isNaN(v) ? 0 : v;
            s['total' + c] = st;
            es.each(function (e) {
                var ed = calculateEdgeSize(e, sts);
                s['computed' + e.capitalize()] = ed;
                s['total' + c] += ed;
            });
        }, this);
        return Object.append(s, sts);
    }
});

/*
 ---
 description:     PostMessager

 authors:
 - David Walsh (http://davidwalsh.name)

 license:
 - MIT-style license

 requires:
 core/1.2.1:   '*'

 provides:
 - PostMessager
 ...
 */

/* navive base onMessage support */
Element.NativeEvents.message = 2;
Element.Events.message = {
    base: 'message',
    condition: function (event) {
        if (!event.$message_extended) {
            event.data = event.event.data;
            event.source = event.event.source;
            event.origin = event.event.origin;
            for (key in event) {
                if (event[key] == undefined) {
                    event[key] = false;
                }
            }
            event.$message_extended = true;
        }
        return true;
    }
};

/* the class */
var PostMessager = new Class({

    Implements: [Options, Events],

    options: {
        allowReceive: true,
        allowSend: true,
        source: window,
        validReceiveURIs: [] /*,
         onSend: $empty,
         onReceive: $empty,
         onReply: $empty
         */
    },

    initialize: function (destFrame, options) {
        this.setOptions(options);
        this.source = document.id(this.options.source);
        this.dest = destFrame;

        this.allowReceive = this.options.allowReceive;
        this.allowSend = this.options.allowSend;

        this.validURIs = this.options.validReceiveURIs;

        this.listener = function (e) {
            if (this.allowReceive && (this.validURIs.length == 0 || this.validURIs.contains(e.origin))) {
                this.fireEvent('receive', [e.data, e.source, e.origin]);
            }
        }.bind(this);

        this.started = false;
        this.start();
    },

    send: function (message, URI) {
        if (this.allowSend) {
            this.dest.postMessage(message, URI);
            this.fireEvent('send', [message, this.dest]);
        }
    },

    reply: function (message, source, origin) {
        source.postMessage(message, origin);
        this.fireEvent('reply', [message, source, origin]);
    },

    start: function () {
        if (!this.started) {
            this.source.addEvent('message', this.listener);
            this.started = true;
        }
    },

    stop: function () {
        this.source.removeEvent('message', this.listener);
        this.started = false;
    },

    addReceiver: function (receiver) {
        this.validURIs.push(receiver);
    },

    removeReceiver: function (receiver) {
        this.validURIs.erase(receiver);
    },

    enableReceive: function () {
        this.allowReceive = true;
    },

    disableReceive: function () {
        this.allowReceive = false;
    },

    enableSend: function () {
        this.allowSend = true;
    },

    disableSend: function () {
        this.allowSend = false;
    }

});

var ColorPicker = new Class({
    Implements: [Options],
    options: {
        defaultColor: '#FFFFFF',
        colorsPerLine: 8,
        changeOnHover: false,
        prefix: 'colorpicker',
        colors: ['#000000', '#444444', '#666666', '#999999', '#cccccc', '#eeeeee', '#f3f3f3', '#ffffff'
            , '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#9900ff', '#ff00ff'
            , '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc'
            , '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd'
            , '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0'
            , '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79'
            , '#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47'
            , '#660000', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4C1130']
    },
    element: null,
    box: null,
    input: null,
    initialize: function (element, options) {
        this.setOptions(options);
        this.element = document.id(element);
        Asset.css('mootools-colorpicker.css');
        // Create element
        this.build();
    },
    build: function () {
        // Build colorbox
        this.box = new Element('div', {
            'class': 'colorpicker-box',
            id: this.options.prefix + '-colorbox',
            events: {
                mouseleave: function () {
                    if (this.options.changeOnHover === true) {
                        this.setColor(this.element.get('value'));
                    }
                }.bind(this)
            }
        });

        var colorBoxColors = new Element('ul');
        // Build color selection
        Array.each(this.options.colors, function (currentColor, i) {
            currentColor = currentColor.toUpperCase();
            var colorUnit = new Element('li', {
                styles: {
                    'background-color': currentColor
                },
                'class': 'colorpicker-color',
                title: currentColor,
                id: this.options.prefix + '-color-' + i,
                'data-color': currentColor,
                events: {
                    click: function () {
                        this.selectColor(currentColor);
                    }.bind(this),
                    mouseover: function () {
                        if (this.options.changeOnHover === true) {
                            this.hoverColor(currentColor);
                        }
                    }.bind(this)
                }
            });

            if (i % this.options.colorsPerLine === 0) {
                colorUnit.setStyle('clear', 'both');
            }

            colorUnit.inject(colorBoxColors);
        }, this);

        // Build color input
        this.input = new Element('div', {
            'class': 'colorpicker-input clearfix',
            styles: {
                'background-color': this.options.defaultColor
            },
            'data-color': this.options.defaultColor,
            'title': this.options.defaultColor,
            events: {
                click: function () {
                    this.positionAndShowBox();
                }.bind(this)
            }
        });
        this.input.grab(
            new Element('i',
                {
                    'class': 'fa fa-eyedropper',
                    events: {
                        click: function (e) {
                            e.stopPropagation();
                            if ($(e.target).hasClass('fa-close')) {
                                this.resetColor();
                            }
                            else {
                                this.positionAndShowBox();
                            }

                        }.bind(this)
                    }
                }
            )
        );

        // Initialize default color
        this.setColor(this.element.get('value'));

        // Onblur event
        $$('body').addEvent('click', function (e) {
            if (!$(e.target).hasClass('colorpicker-color') && !$(e.target).hasClass('colorpicker-input')) {
                this.setColor(this.element.get('value'))
                this.box.hide();
            }
        }.bind(this));

        // Place elements
        this.input.inject(this.element, 'after');
        colorBoxColors.inject(this.box);
        this.box.inject(this.input, 'after');

        // Hide the colorbox
        this.box.hide();
        this.element.hide();

        return this;
    },
    selectColor: function (color) {
        console.log(color)
        this.box.hide();
        this.setColor(color);
        this.element.fireEvent('onSelectColor');
    },
    setColor: function (color) {

        if (color) {
            this.input.set('data-color', color)
                .set('title', color)
                .setStyle('background-color', color);
            this.input.getElement('i').removeClass('fa-eyedropper').addClass('fa-close');

            var cc = new Color(color);
            console.log(cc)
            //this.input.setStyle('color', cc.invert());
        }
    },
    resetColor: function () {
        this.input.getElement('i').removeClass('fa-close').addClass('fa-eyedropper');
        this.element.erase('value');
        this.input.erase('value').erase('data-color').erase('title');
        this.input.setStyle('background-color', '').setStyle('color', '');

        this.box.hide();
    },
    hoverColor: function (color) {
        this.input.setStyle('background-color', color);
    },
    positionAndShowBox: function () {
        this.box.position({
            relativeTo: this.input,
            position: 'bottomLeft',
            edge: 'upperLeft'
        });
        this.box.show();
    }
});

