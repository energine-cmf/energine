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
 * @version 1.0.0
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
Asset = Object.append(Asset, {
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