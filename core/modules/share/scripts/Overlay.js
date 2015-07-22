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
    Implements: [Options, Events],

    /**
     * Overlay options.
     * @type {Object}
     *
     * @property {number} [opacity = 0.5] The opacity of the overlay.
     * @property {boolean} [hideObjects = true] Defines whether to hide the objects under or not.
     * @property {boolean} [indicator = true]
     */
    options: {
        duration: 500,
        opacity: 0.5,
        indicator: true
    },

    // constructor
    initialize: function (parentElement, options) {
        this.setOptions(options);

        //определяем родительский элемент
        parentElement = $(parentElement) || $(window.top.document.body);
        if (!parentElement.getElement) {
            parentElement = window.top.document;
        }
        this.container = parentElement;

        //создаем елемент но не присоединяем его
        this.element = new Element('div', {
            'styles': {opacity: 0},
            'class': 'e-overlay ' + ((this.options.indicator)?'e-overlay-loading':''),
            events: {
                'click': function () {
                    ModalBox.close();
                }
            }
        });

        this.tween = new Fx.Tween(this.element, {
            duration: this.options.duration,
            link: 'cancel',
            property: 'opacity',
            onComplete: function () {
                this.fireEvent(this.element.get('opacity') == this.options.opacity ? 'show' : 'hide');
            }.bind(this)
        });
    },

    /**
     * Show the overlay.
     * @function
     * @public
     */
    show: function () {
        this.setupObjects(true);

        if (!this.container.getChildren('.e-overlay').length) {
            this.element.inject(this.container);
        }
        this.tween.start(this.options.opacity);
    },

    /**
     * Hide the overlay.
     * @function
     * @public
     */
    hide: function () {
        this.setupObjects(false);
        this.tween.start(0).chain(
            function () {
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
    setupObjects: function (hide) {
        var body;

        var elements = Array.from((body = $(document.body)).getElements('object'));
        elements.append(Array.from(body.getElements(Browser.ie ? 'select' : 'embed')));
        elements.each(function (element) {
            element.style.visibility = hide ? 'hidden' : '';
        });
    }
});
