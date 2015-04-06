/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Scrollbar]{@link Scrollbar}</li>
 * </ul>
 *
 * @requires Energine
 * @requires Overlay
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

//todo try to remove
/**
 * Scroll bar. From MooTools it implements: Options.
 *
 * @constructor
 * @param {Object} [options] [Options]{@link Scrollbar#options}.
 */
var Scrollbar = new Class(/** @lends Scrollbar# */{
    Implements: Options,

    /**
     * Defines whether the contents is tuned.
     * @type {boolean}
     */
    contentsTuned: false,

    /**
     * Scroll bar options.
     * @type {Object}
     *
     * @property {string} [type = 'vertical']
     * @property {Element} [scrolledElement = null] Element that scrolls.
     */
    options: {
        type: 'vertical',
        scrolledElement: null
    },

    initialize: function(options) {
        this.setOptions(options);

        this.options.type = ['vertical', 'horizontal'].test(this.options.type)
            ? this.options.type
            : 'vertical';

        /**
         * Main element.
         * @type {Element}
         */
        this.element = new Element('div').setStyles({
            position: 'absolute',
            background: '#EED url(images/scrollbar_bg.gif)',
            display: 'none'
        }).inject(document.body);

        /**
         * Knob element.
         * @type {Element}
         */
        this.knob = new Element('div').setStyles({
            background: '#EED url(images/scrollbar_knob.gif)',
            width: '16px',
            height: '20px'
        }).inject(this.element);

        /**
         * Contents.
         * @type {Element}
         */
        this.contents = this.options.scrolledElement.getFirst(); // Первый дочерний элемент прокручиваемой области считается содержимым.
    },

    // todo: Rename to eliminate the collision with the options member.
    /**
     *
     * @param {Object} event Event
     */
    scrolledElement: function(event){
        // TODO: Which set() method is used? --> To the Slider class. Where is the Slider class?
        if (event.wheel < 0) {
            this.set(this.step + 1);
        } else if (event.wheel > 0) {
            this.set(this.step - 1);
        }

        event.stop();
    },

    /**
     * Setup.
     *
     * @function
     * @public
     * @param {number} steps
     */
    setup: function(steps) {
        if (!steps) {
            this.element.setStyle('display', 'none');
            return;
        }

        if (!this.options.scrolledElement) {
            return;
        }

        var size = this.options.scrolledElement.getSize();
        if (size.size.y >= size.scrollSize.y) {
            steps = 0;
        }

        //todo: what is it?
        this.steps = steps;

        if (!this.contentsTuned) {
            /**
             * Defines whether the contents is tuned.
             * @type {boolean}
             */
            this.contentsTuned = true;
            this.contents.setStyle('width', this.contents.getSize().size.x - 16 + 'px');
        }

        var coords = this.contents.getCoordinates();
        this.element.setStyles({
            'display': '',
            'top': coords.top - (Browser.ie ? (Energine.singleMode ? -1 : 24) : 0) + 'px',
            'left': coords.right + (Browser.ie ? 2 : 1) + 'px',
            'width': '16px',
            'height': this.options.scrolledElement.getSize().size.y - 1 + 'px'
        });

        if (!this.slider) {
            this.slider = new Slider(this.element, this.knob, {
                mode: this.options.type,
                onChange: function(step) {
                    var size = this.options.scrolledElement.getSize();
                    this.options.scrolledElement.scrollTo(0, step * ((size.scrollSize.y - size.size.y) / this.steps));
                }.bind(this)
            });
            this.contents.addEvent('mousewheel', this.scrolledElement.bind(this.slider)); // Hack.
        }

        // More hacks:
        if (this.steps != 0) {
            this.slider.options.steps = this.steps; // Устанавливаем новое количество шагов полосы прокрутки.
        }
        this.slider.set(0);
    }
});