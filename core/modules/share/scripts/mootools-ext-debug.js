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

        var fullSource = ((Energine['static']) ? Energine['static'] : '') + 'stylesheets/' + source;
        Asset.loaded.css[source] = fullSource;

        return Asset.cssParent(fullSource, {'media': 'Screen, projection'});
    }
});

Element.implement({
    getComputedStyle: function(property){
        var floatName = (document.html.style.cssFloat == null) ? 'styleFloat' : 'cssFloat',
            defaultView = Element.getDocument(this).defaultView,
            computed = defaultView ? defaultView.getComputedStyle(this, null) : null;
        return (computed) ? computed.getPropertyValue((property == floatName) ? 'float' : property.hyphenate()) : null;
    },

    // NOTE: This function is overwritten because of not secure style value casting.
    getComputedSize: function(options){
        function calculateEdgeSize(edge, styles){
            var total = 0;
            Object.each(styles, function(value, style){
                if (style.test(edge)) total = total + value.toInt();
            });
            return total;
        }
        function getStylesList(styles, planes){
            var list = [];
            Object.each(planes, function(directions){
                Object.each(directions, function(edge){
                    styles.each(function(style){
                        list.push(style + '-' + edge + (style == 'border' ? '-width' : ''));
                    });
                });
            });
            return list;
        }

        options = Object.merge({
            styles: ['padding','border'],
            planes: {
                height: ['top','bottom'],
                width: ['left','right']
            },
            mode: 'both'
        }, options);

        var styles = {},
            size = {width: 0, height: 0},
            dimensions;

        if (options.mode == 'vertical'){
            delete size.width;
            delete options.planes.width;
        } else if (options.mode == 'horizontal'){
            delete size.height;
            delete options.planes.height;
        }

        getStylesList(options.styles, options.planes).each(function(style){
            // here was not checked if the type casting return NaN
            var value = parseInt(this.getStyle(style));
            styles[style] = isNaN(value) ? 0 : value;
        }, this);

        Object.each(options.planes, function(edges, plane){

            var capitalized = plane.capitalize(),
                style = this.getStyle(plane);

            if (style == 'auto' && !dimensions) dimensions = this.getDimensions();

            var value = (style == 'auto') ? dimensions[plane] : parseInt(style);
            style = styles[style] = isNaN(value) ? 0 : value;
            size['total' + capitalized] = style;

            edges.each(function(edge){
                var edgesize = calculateEdgeSize(edge, styles);
                size['computed' + edge.capitalize()] = edgesize;
                size['total' + capitalized] += edgesize;
            });

        }, this);

        return Object.append(size, styles);
    }
});