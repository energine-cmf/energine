/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Overlay]{@link Overlay}</li>
 * </ul>
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

/**
 * This can be used to create a 'busy' effect. From MooTools it implements: Options.
 *
 * @constructor
 * @param {Element} parentElement
 * @param {Object} [options] [Options]{@link Overlay#options}.
 */
var Overlay = new Class(/** @lends Overlay# */{
    Implements: Options,

    /**
     * Overlay options.
     * @type {Object}
     *
     * @property {number} [opacity = 0.5] The opacity of the overlay.
     * @property {boolean} [hideObjects = true] Defines whether to hide the objects under or not.
     * @property {boolean} [indicator = true]
     */
    options:{
        opacity: 0.5,
        indicator: true
    },

    // constructor
    initialize: function(parentElement, options) {
        Asset.css('overlay.css');
        this.setOptions(options);

        //определяем родительский элемент
        parentElement = $(parentElement) || document.body;
        // todo: What is the underground of this? Detect IE? -- comment and check
        if (!parentElement.getElement) {
            parentElement = document;
        }
        this.parentElement = parentElement;

        //создаем елемент но не присоединяем его
        this.element = new Element('div', {'class': 'e-overlay' + ((this.options.indicator) ? ' e-overlay-loading' : ''), 'styles':{'opacity': 0}});
    },

    /**
     * Show the overlay.
     * @function
     * @public
     */
    show: function() {
        this.setupObjects(true);
        if (!this.parentElement.getChildren('.e-overlay').length) {
            this.element.inject(this.parentElement);
        }
        this.element.fade(this.options.opacity);
    },

    /**
     * Hide the overlay.
     * @function
     * @public
     */
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

    /**
     * Setup the objects.
     *
     * @function
     * @public
     * @param {boolean} hide
     */
    setupObjects: function(hide) {
        var body;

        var elements = Array.from((body = $(document.body)).getElements('object'));
        elements.append(Array.from(body.getElements(Browser.ie ? 'select' : 'embed')) );
        elements.each(function(element) {
            element.style.visibility = hide ? 'hidden' : '';
        });
    }
});
