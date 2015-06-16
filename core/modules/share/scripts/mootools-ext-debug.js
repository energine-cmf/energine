/**
 * @file Additional extensions to the MooTools framework. It contain the description of the next objects:
 * <ul>
 *     <li>[Mutators]{@link Mutators}</li>
 *     <li>[Mutators.Static]{@link Mutators.Static}</li>
 *     <li>[Mutators.Protected]{@link Mutators.Protected}</li>
 *     <li>[Asset.loaded]{@link Asset.loaded}</li>
 *     <li>[Asset.cssParent]{@link Asset.cssParent}</li>
 *     <li>[Asset.css]{@link Asset.css}</li>
 * </ul>
 *
 * @author Valerii Zinchenko
 *
 * @version 1.0.3
 */

/**
 * @namespace
 */
Class.Mutators = Object.append(Class.Mutators, {
    /**
     * Create static members for a class.
     *
     * @constructor
     * @param {Object} members Object that contains properties and methods, which must be static in the class.
     */
    Static: function (members) {
        this.extend(members);
    },

    /**
     * Create protected methods for a class.
     *
     * @constructor
     * @param {Object} methods Object with methods, which must be protected.
     */
    Protected: function (methods) {
        for (var key in methods) {
            if (methods[key] instanceof Function) {
                this.implement(key, methods[key].protect());
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


/**
 * @namespace
 * @augments Asset
 */
Asset = Object.append(Asset, /** @lends Asset# */{
    /**
     * List of loaded files.
     * @type {Object}
     *
     * @property {Object} css List of loaded CSS files.
     */
    loaded: {
        css: {}
    },

    /**
     * Set the default [Asset.css]{@link http://mootools.net/docs/more/Utilities/Assets#Asset:Asset-css} method as the parent method for extension.
     *
     * @function
     * @public
     */
    cssParent: Asset.css,

    /**
     * Overridden Asset.css function.
     *
     * @function
     * @public
     * @param {string} source Filename.
     * @returns {Element}
     */
    css: function (source) {
        if (Asset.loaded.css[source]) {
            return null;
        }

        if (source.substr(0, 4) == 'http')return Asset.cssParent(source, {'media': 'Screen, projection'});

        var fullSource = ((Energine['static']) ? Energine['static'] : '') + 'stylesheets/' + source;
        Asset.loaded.css[source] = fullSource;

        return Asset.cssParent(fullSource, {'media': 'Screen, projection'});
    }
});

Element.implement({
    setPosition: function (obj) {
        if (obj)
            return this.setStyles(this.computePosition(obj));
    },
    getComputedStyle: function (property) {
        var floatName = (document.html.style.cssFloat == null) ? 'styleFloat' : 'cssFloat',
            defaultView = Element.getDocument(this).defaultView,
            computed = defaultView ? defaultView.getComputedStyle(this, null) : null;
        return (computed) ? computed.getPropertyValue((property == floatName) ? 'float' : property.hyphenate()) : null;
    },

    // NOTE: This function is overwritten because of not secure style value casting.
    getComputedSize: function (options) {
        function calculateEdgeSize(edge, styles) {
            var total = 0;
            Object.each(styles, function (value, style) {
                if (style.test(edge)) total = total + value.toInt();
            });
            return total;
        }

        function getStylesList(styles, planes) {
            var list = [];
            Object.each(planes, function (directions) {
                Object.each(directions, function (edge) {
                    styles.each(function (style) {
                        list.push(style + '-' + edge + (style == 'border' ? '-width' : ''));
                    });
                });
            });
            return list;
        }

        options = Object.merge({
            styles: ['padding', 'border'],
            planes: {
                height: ['top', 'bottom'],
                width: ['left', 'right']
            },
            mode: 'both'
        }, options);

        var styles = {},
            size = {width: 0, height: 0},
            dimensions;

        if (options.mode == 'vertical') {
            delete size.width;
            delete options.planes.width;
        } else if (options.mode == 'horizontal') {
            delete size.height;
            delete options.planes.height;
        }

        getStylesList(options.styles, options.planes).each(function (style) {
            // here was not checked if the type casting return NaN
            var value = parseInt(this.getStyle(style));
            styles[style] = isNaN(value) ? 0 : value;
        }, this);

        Object.each(options.planes, function (edges, plane) {

            var capitalized = plane.capitalize(),
                style = this.getStyle(plane);

            if (style == 'auto' && !dimensions) dimensions = this.getDimensions();

            var value = (style == 'auto') ? dimensions[plane] : parseInt(style);
            style = styles[style] = isNaN(value) ? 0 : value;
            size['total' + capitalized] = style;

            edges.each(function (edge) {
                var edgesize = calculateEdgeSize(edge, styles);
                size['computed' + edge.capitalize()] = edgesize;
                size['total' + capitalized] += edgesize;
            });

        }, this);

        return Object.append(size, styles);
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

/**
 * PostMessager 0.4


 PostMessager is a MooTools plugin that acts as a wrapper for the window.postMessage API which is available in IE8+, Firefox 3.1+, Opera 9+, Safari, and Chrome. PostMessager also normalizes the onMessage event for use within MooTools.

 * @see http://mootools.net/forge/p/postmessager
 **/
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