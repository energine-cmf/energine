/**
 * @file Library to create carousel(s) on the web-pages. Contain the description of the next classes:
 * <ul>
 *     <li>[ACarousel]{@link ACarousel}</li>
 *     <li>[ACarousel.AControls]{@link ACarousel.AControls}</li>
 *     <li>[Carousel]{@link Carousel}</li>
 *     <li>[Carousel.Types]{@link Carousel.Types}</li>
 *     <li>[Carousel.Types.Loop]{@link Carousel.Types.Loop}</li>
 *     <li>[Carousel.Types.Line]{@link Carousel.Types.Line}</li>
 *     <li>[Carousel.Controls]{@link Carousel.Controls}</li>
 *     <li>[Carousel.Controls.TwoButtons]{@link Carousel.Controls.TwoButtons}</li>
 *     <li>[CarouselPlaylist]{@link CarouselPlaylist}</li>
 *     <li>[CarouselConnector]{@link CarouselConnector}</li>
 * </ul>
 *
 * @author Valerii Zinchenko, Pavel Dubenko
 *
 * @version 2.0.0
 */

/**
 * Abstract carousel. The general HTML structure is showed in the example below.
 * The HTML element with class "playlist_local" is optional. If the playlist is undefined, then the carousel will try to create the playlist from that element.
 * From MooTools it implements: Options, Events, Chain.
 *
 * @example <caption>HTML structure for carousel.</caption>
 * &ltdiv id="carouselID" class="carousel"&gt
 *     &ltdiv class="carousel_viewbox"&gt
 *         &ltdiv class="playlist_local"&gt
 *         &lt/div&gt
 *     &lt/div&gt
 * &lt/div&gt
 *
 * @author Valerii Zinchenko
 *
 * @throws {string} View box of the carousel was not found.
 * @throws {string} Carousel can not be created without playlist.
 *
 * @constructor
 * @param {Element} el Main element for the carousel.
 * @param {Object} [opts] [Options]{@link ACarousel#options} for the carousel.
 */
var ACarousel = new Class(/** @lends ACarousel# */{
    Implements: [Options, Events, Chain],

    Static: {
        /**
         * Counter of Carousel objects.
         *
         * @memberOf ACarousel
         * @static
         * @type {number}
         */
        count: 0,

        /**
         * Assigns id.
         *
         * @memberOf ACarousel
         * @function
         * @static
         * @returns {number}
         */
        assignID: function () {
            return this.count++;
        }
    },

    /**
     * Carousel options.
     * @type {object}
     *
     * @property {number|string} [NVisibleItems = 1] Number of visible items. It can be also 'all' to show all items in the playlist.
     * @property {number} [scrollStep = 1] Default scrolling step.
     * @property {number} [scrollDirection = 'left'] Default scrolling direction. Here can be used 'left', 'right', 'top', 'bottom'.
     * @property {CarouselPlaylist} [playlist = null] Reference to the playlist. If the playlist is not defined, then the playlist will be created from the element class <tt>'.playlist_local'</tt>.
     * @property {boolean} [autoSelect = true] Defines whether the items can be auto selected.
     * @property {string} [activeLabel = 'active'] Value of an element's parameter 'class' at the active item in the carousel.
     * @property {Object} fx Scrolling effect options.
     * @property {number} [fx.duration = 700] Effect duration.
     * @property {string} [fx.transition = 'cubic:in:out'] Effect transition. Possible values see [here]{@link http://mootools.net/docs/core/Fx/Fx.Transitions}.
     * @property {Object} [classes] It contains the element's class names for each component.
     * @property {string} [classes.viewbox = '.carousel_viewbox'] Class name for the carousel's view box.
     * @property {string} [classes.item = '.item'] Class name for the carousel's items.
     * @property {Object} [styles] Array like object with styles for the carousel.
     * @property {Object} [styles.element = {position: 'relative'}] Styles for the main [element]{@link ACarousel#element}.
     * @property {Object} [styles.viewbox = {position: 'relative', overflow: 'hidden', margin: 'auto'}] Styles for the [view box]{@link ACarousel#viewbox}.
     * @property {Object} [styles.item = {position: 'absolute', textAlign: 'center', verticalAlign: 'middle'}] Styles for scrolled [items]{@link ACarousel#items}.
     */
    options: {
        NVisibleItems: 1,
        scrollStep: 1,
        scrollDirection: 'left',
        playlist: null,
        autoSelect: true,
        activeLabel: 'active',
        fx: {
            duration: 700,
            transition: 'cubic:in:out'
        },
        classes: {
            viewbox: '.carousel_viewbox',
            item: '.item'
        },
        /* NOTE:
         * This is need for the elimination the using of Asset.css('carousel.css') in the carousel's constructor,
         * because we do not know how long the file 'carousel.css' must be parsed and applied to the HTML document
         * before the using of stylized elements.
         */
        styles: {
            element: {
                position: 'relative'
            },
            viewbox: {
                position: 'relative',
                overflow: 'hidden',
                margin: 'auto'
            },
            item: {
                position: 'absolute',
                textAlign: 'center',
                verticalAlign: 'middle'
            }
        }
    },

    /**
     * Holder element for the local playlist.
     * @type {Element}
     */
    playlistHolder: null,

    /**
     * Array of objects with effects, that will be applied to the items by scrolling.
     * @type {Array}
     */
    effects: [],

    /**
     * Indicates whether scrolling is finished.
     * @type {boolean}
     */
    isEffectCompleted: true,

    /**
     * Indicates current active item in playlist.
     * @type {number}
     */
    currentActiveID: 0,

    /**
     * Item size. It holds the maximal width and height.
     * @type {number[]}
     */
    itemSize: [0,0],

    // constructor
    initialize: function (el, opts) {
        /**
         * Main element for the carousel. It must contain the '.carousel_viewbox'.
         * @type {Element}
         */
        this.element = el;

        this.setOptions(opts);
        // This is need to save the reference to the playlist.
        if (opts != undefined && 'playlist' in opts) {
            this.options.playlist = opts.playlist;
        }
        this.checkOptions();

        /**
         * View-box element of the carousel that holds an playlist items.
         * @type {Element}
         */
        this.viewbox = this.element.getElement(this.options.classes.viewbox);
        if (!this.viewbox) {
            throw 'View box of the carousel was not found.';
        }

        /**
         * Carousel ID.
         * @type {number}
         */
        this._id = ACarousel.assignID();
        this.element.store('id', this._id);

        this.createPlaylist();

        /**
         * Holds all items from the playlist.
         * @type {Elements|Element[]}
         */
        this.items = this.playlistHolder.getChildren();
    },

    /**
     * Build the carousel.
     *
     * @fires ACarousel#enableScrolling
     * @fires ACarousel#disableScrolling
     *
     * @function
     * @public
     */
    build: function() {
        this.calcItemSize();

        // Add 'click'-event to all items
        this.items.each(function (it, n) {
            var self = this;
            it.addEvent('click', function(ev) {
                var el = $(ev.target);
                if (el !== this
                    && ( el.tagName.toLowerCase() === 'a' || this.contains(el.getParent('a')) ))
                {
                    return;
                }

                ev.stop();

                self.selectItem(n);
            });
        }, this);

        /**
         * Indicates whether the scrolling can be done.
         * @type {boolean}
         */
        this.canScroll = this.options.NVisibleItems != 'all' && this.options.NVisibleItems < this.options.playlist.NItems;

        if (this.canScroll) {
            /**
             * Indicates the first visible item ID in the carousel.
             * @type {number}
             */
            this.firstVisibleItemID = 0;

            if (this.options.scrollStep > this.options.NVisibleItems) {
                console.warn('The option "scrollStep" (' + this.options.scrollStep
                    + ') > "NVisibleItems" (' + this.options.NVisibleItems
                    + '). It is reset to ' + this.options.NVisibleItems + '.');
                this.options.scrollStep = this.options.NVisibleItems;
            }

            this.prepareScrolling();

            /**
             * Fired when the carousel can scroll.
             * @event ACarousel#enableScrolling
             */
            this.fireEvent('enableScrolling');
        } else {
            this.disable();

            /**
             * Fired when the carousel can not scroll.
             * @event ACarousel#disableScrolling
             */
            this.fireEvent('disableScrolling');
        }

        this.applyStyles();
        this.prepareItems();
    },

    /**
     * Scroll forward by one step. Multiple calls will be chained.
     * @function
     * @public
     */
    scrollForward: function () {
        if (this.isEffectCompleted) {
            this.scroll(1);
        } else {
            this.chain(this.scrollForward.bind(this));
        }
    },

    /**
     * Scroll backward by one step. Multiple calls will be chained.
     * @function
     * @public
     */
    scrollBackward: function () {
        if (this.isEffectCompleted) {
            this.scroll(-1);
        } else {
            this.chain(this.scrollBackward.bind(this));
        }
    },

    /**
     * Select item in the carousel and mark him as active.
     *
     * @fires ACarousel#selectItem
     *
     * @function
     * @public
     * @param {number} id Item ID.
     */
    selectItem: function (id) {
        if (this.currentActiveID === id) {
            return;
        }

        for (var n = 0; n < this.items.length / this.options.playlist.NItems; n++) {
            this.items[this.currentActiveID + this.options.playlist.NItems * n].removeClass(this.options.activeLabel);
            this.items[id + this.options.playlist.NItems * n].addClass(this.options.activeLabel);
        }
        this.currentActiveID = id;

        /**
         * Fired when the new item ID was selected.
         * @event ACarousel#selectItem
         * @param {number} n New item ID.
         */
        this.fireEvent('selectItem', this.currentActiveID);
    },

    //todo: this must somehow called from scrollForward and scrollBackward with the id difference.
    /**
     * Scrolls to the specific item ID.
     *
     * @function
     * @public
     * @param {number} id Item ID.
     */
    scrollTo: function (id) {
        var direction,
            NTimes;

        // Check whether the desired item ID is visible in the carousel. If it is, then do not scroll.
        for (var n = 0; n < this.options.NVisibleItems; n++) {
            if (this.wrapIndices(this.firstVisibleItemID + n, 0, this.options.playlist.NItems) == id) {
                return;
            }
        }

        direction = (id > this.currentActiveID) ? 1 : -1;
        var diffFromLeft = Math.abs(id - this.currentActiveID);

        if (this.options.loop) {
            var diffFromRight = this.options.playlist.NItems - diffFromLeft;

            if (diffFromLeft <= diffFromRight) {
                NTimes = diffFromLeft;
            } else {
                direction *= -1;
                NTimes = diffFromRight;
            }
        } else {
            NTimes = diffFromLeft;
            if (NTimes > this.options.playlist.NItems - this.options.NVisibleItems) {
                NTimes = this.options.playlist.NItems - this.options.NVisibleItems;
            }
        }
        NTimes = Math.ceil(NTimes / this.options.scrollStep);
        this.scroll(direction, Math.abs(NTimes), true);
    },

    /**
     * Stop the scrolling.
     * @function
     * @public
     */
    stop: function() {
        this.$chain = [];
    },

    Protected: {
        // Abstract
        // ====================
        /**
         * Prepare the carousel to the scrolling.
         *
         * @memberOf ACarousel#
         * @abstract
         * @function
         * @protected
         */
        prepareScrolling: function() {
            /**
             * Shift steps by scrolling the items.
             * @type {number[]}
             */
            this.itemShifts = [
                this.length * this.options.NVisibleItems,
                -this.length * this.options.scrollStep
            ];

            // Calculate effects for scrolling
            // NOTE: Do not create unique effects for both opposite style. It is complex:
            //      - each element must have only style and not both
            //      - before applying effect we need delete one style from the visible and new items and assign other
            //      - prepare an object with proper effects for the Fx.Elements
            var N = this.options.NVisibleItems + this.options.scrollStep;
            this.effects[0] = this.createEffect(this.options.scrollDirection, -this.length * this.options.scrollStep, this.length, N);
            this.effects[1] = this.createEffect(this.options.scrollDirection, 0, this.length, N);
        },

        /**
         * Prepare items for scrolling.
         *
         * @memberOf ACarousel#
         * @abstract
         * @function
         * @protected
         */
        prepareItems: function() {
            this.items.slice(0, this.options.NVisibleItems).each(function (it, n) {
                it.setStyle(this.options.scrollDirection, n * this.length);
            }, this);
            this.items.slice(this.options.NVisibleItems).each(function (it) {
                it.setStyle(this.options.scrollDirection, -this.length);
            }, this);
        },

        /**
         * Get the items, that will become visible after scrolling.
         *
         * @memberOf ACarousel#
         * @abstract
         * @function
         * @protected
         * @param {number} direction Scrolling direction.
         * @param {number} scrollNTimes Scrolling multiplier.
         * @return {Element[]}
         */
        getNewVisibleItems: function(direction, scrollNTimes) {},

        /**
         * Method for calculation the ID of the first visible item.
         *
         * @memberOf ACarousel#
         * @abstract
         * @function
         * @protected
         * @param {number} direction Scrolling direction.
         * @param {number} scrollNTimes Scrolling multiplier.
         */
        calcFirstItemID: function(direction, scrollNTimes) {},

        /**
         * Get the effects, that will be applied to scrolled items.
         *
         * @memberOf ACarousel#
         * @abstract
         * @function
         * @protected
         * @param {number} direction Scrolling direction.
         * @param {number} scrollNTimes Scrolling multiplier.
         * @return {Object}
         */
        getItemEffects: function(direction, scrollNTimes) {},
        // ====================

        /**
         * Checks types and boundaries of carousel options.
         * @memberOf ACarousel#
         * @function
         * @protected
         */
        checkOptions: function() {
            if (this.options.NVisibleItems !== 'all') {
                this.options.NVisibleItems = this.checkNumbers('NVisibleItems', [this.options.NVisibleItems, 1, 1]);
            }
            this.options.scrollStep = this.checkNumbers('scrollStep', [this.options.scrollStep, 1, 1, this.options.NVisibleItems]);

            // fx
            this.options.fx.duration = this.checkNumbers('duration', [this.options.fx.duration, 700, 0]);

            // scrollDirection
            if (typeOf(this.options.scrollDirection) != 'string'
                || (this.options.scrollDirection != 'left'
                && this.options.scrollDirection != 'right'
                && this.options.scrollDirection != 'top'
                && this.options.scrollDirection != 'bottom'))
            {
                this.options.scrollDirection = 'left';
                console.warn('The option \"scrollDirection\" is incorrect. Its value reset to \"' + this.options.scrollDirection + '\"');
            }

            // playlist
            if (this.options.playlist != null && !instanceOf(this.options.playlist, CarouselPlaylist)) {
                this.options.playlist = null;
                console.warn('The option for \"playlist\" is incorrect. Its value reset to \"null\"');
            }

            // autoSelect
            if (typeOf(this.options.autoSelect) != 'boolean') {
                this.options.autoSelect = !!this.options.autoSelect;
                console.warn('The option \"autoSelect\" was not with type of \"boolean\". Its value set to \"' + this.options.autoSelect.toString() + '\"');
            }
        },

        /**
         * Create the playlist.
         *
         * @throws {string} Carousel can not be created without playlist.
         *
         * @memberOf ACarousel#
         * @function
         * @protected
         */
        createPlaylist: function() {
            // If the playlist is not explicitly specified, set than try to get a playlist from the carousel.
            if (this.options.playlist === null) {
                try {
                    this.playlistHolder = this.viewbox.getElement('.playlist_local');
                    this.options.playlist = new CarouselPlaylist(this.playlistHolder);
                } catch (err) {
                    console.warn(err);
                    throw 'Carousel can not be created without playlist.';
                }
            }

            // Check whether the playlist is internal. If not - make clone
            if (this.viewbox === this.options.playlist.items[0].getParent('.carousel_viewbox')){
                this.playlistHolder = this.options.playlist.getHolder();
                this.options.playlist.isExtern = false;
            } else {
                this.playlistHolder = new Element(this.options.playlist.getHolder().get('tag'));
                this.viewbox.grab(this.playlistHolder);
                this.options.playlist.items.each(function (item) {
                    item.clone().inject(this.playlistHolder);
                }, this);
            }
        },

        /**
         * Get the the maximal item size.
         * @memberOf ACarousel#
         * @function
         * @protected
         */
        calcItemSize: function() {
            // Get the size of the biggest item.
            this.items.getDimensions({computeSize:true}).each(function(dims) {
                if (this.itemSize[0] < dims.totalWidth) {
                    this.itemSize[0] = dims.totalWidth;
                }
                if (this.itemSize[1] < dims.totalHeight) {
                    this.itemSize[1] = dims.totalHeight;
                }
            }, this);

            // add margins
            this.itemSize[0] += this.items[0].getStyle('margin-left').toInt() + this.items[0].getStyle('margin-right').toInt();
            this.itemSize[1] += this.items[0].getStyle('margin-top').toInt() + this.items[0].getStyle('margin-bottom').toInt();

            // Apply new width to the 'view-box'-element
            if (this.options.scrollDirection == 'left' || this.options.scrollDirection == 'right') {
                /**
                 * Item length.
                 * @type {number}
                 */
                this.length = this.itemSize[0];
            } else {
                this.length = this.itemSize[1];
            }
        },

        /**
         * Apply styles to the carousel and his elements.
         * @memberOf ACarousel#
         * @function
         * @protected
         */
        applyStyles: function() {
            var opts = this.options,
                el = this.element;

            el.setStyles(opts.styles.element);
            delete opts.styles.element;
            for (var selector in opts.styles) {
                (selector in opts.classes)
                    ? el.getElements(opts.classes[selector]).setStyles(opts.styles[selector])
                    : el.getElements(selector).setStyles(opts.styles[selector]);
            }
            delete opts.styles;

            // Apply new width to the 'view-box'-element
            if (opts.scrollDirection == 'left' || opts.scrollDirection == 'right') {
                this.viewbox.setStyle('width', this.length * opts.NVisibleItems);
                this.viewbox.setStyle('height', this.itemSize[1]);
            } else {
                this.viewbox.setStyle('width', this.itemSize[0]);
                this.viewbox.setStyle('height', this.length * opts.NVisibleItems);
            }
        },

        /**
         * Disable carousel.
         * @memberOf ACarousel#
         * @function
         * @protected
         */
        disable: function() {
            this.options.NVisibleItems = this.options.playlist.NItems;
            this.options.scrollStep = 0;
        },

        /**
         * The core method that scrolls the items. If one of the new visible items is not marked as active, this will set
         * leftmost or rightmost item as active and after that an event will be fired, that the new item is set as active.
         * If this method will be called with <tt>isSelected == true</tt> then the selection will be ignored.
         *
         * @fires ACarousel#scroll
         * @fires ACarousel#singleScroll
         *
         * @throws {string} scrollNTimes must be > 0
         *
         * @memberOf ACarousel#
         * @abstract
         * @function
         * @protected
         * @param {number} direction Defines the scroll direction. It can be 1 or -1 to scroll forward and backward respectively.
         * @param {number} [scrollNTimes = 1] Defines how many scrolls must be done by one call of scrolling.
         * @param {boolean} [isSelected = false] Indicates whether one of the new visible items is already selected.
         */
        scroll: function (direction, scrollNTimes, isSelected) {
            var itemsToScroll,
                effects;

            if (!this.canScroll) {
                return;
            }

            if (scrollNTimes <= 0) {
                throw 'scrollNTimes must be > 0';
            }
            scrollNTimes = scrollNTimes || 1;

            itemsToScroll = this.getScrolledItems(direction, scrollNTimes);

            effects = this.getItemEffects(direction, scrollNTimes);

            this.calcFirstItemID(direction, scrollNTimes);

            if (this.options.autoSelect && !isSelected) {
                // Checks whether the selected item is visible
                var isSelectedVisible = false;
                for (var n = 0; n < this.options.NVisibleItems; n++) {
                    if (this.wrapIndices(this.firstVisibleItemID + n, 0, this.options.playlist.NItems) == this.currentActiveID) {
                        isSelectedVisible = true;
                        break;
                    }
                }
                // If the selected item is not visible, then the leftmost or rightmost visible item will be selected
                if (!isSelectedVisible) {
                    this.selectItem(this.wrapIndices(
                        (direction == 1)
                            ? this.firstVisibleItemID
                            : this.firstVisibleItemID + this.options.NVisibleItems - 1,
                        0, this.options.playlist.NItems));
                }
            }

            this.isEffectCompleted = false;

            new Fx.Elements(itemsToScroll, {
                duration: this.options.fx.duration,
                transition: this.options.fx.transition,
                onChainComplete: function () {
                    this.isEffectCompleted = true;

                    if (this.$chain.length == 0) {
                        /**
                         * Fired when the whole scrolling is finished.
                         * @event ACarousel#scroll
                         * @param {number} direction Last scrolled direction.
                         */
                        this.fireEvent('scroll', direction);
                    }
                    this.callChain();

                    /**
                     * Fired when the single scroll is finished.
                     * @event ACarousel#singleScroll
                     * @param {number} direction Last scrolled direction.
                     */
                    this.fireEvent('singleScroll', direction);
                }.bind(this)
            }).start(effects);
        },

        /**
         * Get the items, that will be scrolled.
         *
         * @throws {string} Scrolling direction must be -1 or 1 but received "{number}"!
         *
         * @memberOf ACarousel#
         * @function
         * @protected
         * @param {number} direction Scrolling direction.
         * @param {number} scrollNTimes Scrolling multiplier.
         * @return {Element[]}
         */
        getScrolledItems: function(direction, scrollNTimes) {
            var itemsToScroll = [],
                n;

            var newItems = this.getNewVisibleItems(direction, scrollNTimes);

            // Collects all visible items
            for (n = 0; n < this.options.NVisibleItems; n++) {
                var itemID = this.firstVisibleItemID + n;
                if (itemID >= this.items.length) {
                    itemID -= this.items.length;
                }

                itemsToScroll[n] = this.items[itemID];
            }

            // Connects all visible and new items
            itemsToScroll = (direction == 1)
                ? itemsToScroll.concat(newItems)
                : newItems.concat(itemsToScroll);

            return itemsToScroll;
        },

        /**
         * Clone each item in '<tt>items</tt>' and place them to '<tt>where</tt>'.
         *
         * @memberOf ACarousel#
         * @function
         * @protected
         * @param {Array} items Items that will be cloned.
         * @param {HTMLElement} holder Element that stores <tt>items</tt>.
         * @param {number} [NTimes = 1] Specifies how many clones will be created. You must use explicit value 1 if you want to use last argument <tt>'where'</tt>.
         * @param {string} [where = 'bottom'] The place to inject each clone. It can be '<tt>top</tt>', '<tt>bottom</tt>', '<tt>after</tt>', or '<tt>before</tt>'.
         */
        cloneItems: function (items, holder, NTimes, where) {
            NTimes = NTimes || 1;
            where = where || 'bottom';
            var N = items.length;
            for (var i = 1; i <= NTimes; i++) {
                for (var n = 0; n < N; n++) {
                    items.push(items[n].clone().inject(holder, where));
                    items[n + N * i].cloneEvents(items[n]);
                    if (items[n].hasClass(this.activeLabel)) {
                        items[n + N * i].addClass(this.activeLabel);
                    }
                }
            }
        },

        /**
         * Create an object with effects.
         *
         * @memberOf ACarousel#
         * @function
         * @protected
         * @param {string} key Key for value.
         * @param {number} begin Indicates a start value of effect.
         * @param {number} step Indicates a step values between effects.
         * @param {number} count Indicates how many effects must be generated.
         * @returns {Object} Object with '<tt>count</tt>' objects like {key: effectValue}
         *
         * @example
         * createEffect('left', 0, 10, 3) == { { 'left': 0  }
         *                                     { 'left': 10 }
         *                                     { 'left': 20 } }
         */
        createEffect: function (key, begin, step, count) {
            var obj = {};
            for (var i = 0; i < count; i++) {
                obj[i] = (function () {
                    var subobj = {};
                    subobj[key] = begin + step * i;
                    return subobj;
                })();
            }
            return obj;
        },

        /**
         * Checks if the variable in the input object is with type of number and lower than some default value.
         *
         * @memberOf ACarousel#
         * @function
         * @protected
         * @param {string} varName Variable name.
         * @param {Object} values Object with name of variable that contain an array with size 3: [0] value that will be checked; [1] default value; [2] min value; [3] max value;
         * @return {Object} Object with checked values.
         */
        checkNumbers: function (varName, values) {
            if (typeOf(values[0]) != 'number') {
                values[0] = Number.from(values[0]);
            }

            if (values[0] == null || isNaN(values[0]) || values[0] < values[2]) {
                values[0] = values[1];
                console.warn('The value for \"' + varName + '\" is incorrect. Its value reset to', values[0] + '.');
            } else if (values[3] !== undefined && values[0] > values[3]) {
                values[0] = values[3];
                console.warn('The value for \"' + varName + '\" is incorrect. Its value reset to', values[0] + '.');
            }
            return values[0];
        },

        /**
         * Wrap an index between lower and upper limits.
         *
         * @throws {string} Arguments must be: maxID != 0 minID >= 0 maxID > minID
         *
         * @memberOf ACarousel#
         * @function
         * @protected
         * @param {number} id Index that must be wrapped.
         * @param {number} minID Lower limit.
         * @param {number} maxID Upper limit.
         * @param {boolean} [toWrap = true] Determines, will the id wrapped (true) or cropped (false) by limits.
         * @returns {number} Wrapped id.
         *
         * @example
         * wrapIndices(-2, 0, 8, true)  == 6
         * wrapIndices(-2, 0, 8, false) == 0
         */
        wrapIndices: function(id, minID, maxID, toWrap) {
            if (maxID === 0 || minID < 0 || maxID < minID) {
                throw 'Arguments must be: maxID != 0 minID >= 0 maxID > minID';
            }
            if (toWrap === undefined) {
                toWrap = true;
            }
            if (toWrap) {
                return (id >= maxID) ? id - maxID * Math.floor(id / maxID) :
                    (id < minID) ? id + maxID * Math.ceil(Math.abs(id) / maxID) : id;
            } else {
                return (id >= maxID) ? maxID :
                    (id < minID) ? minID : id;
            }
        }
    }
});

/**
 * Abstract controls for the carousel. The control elements must be located inside the carousel.
 * From MooTools it implements: Options, Events.
 *
 * @constructor
 * @param {Element|string} el Main carousel element.
 * @param {Object} [opts] [Options]{@link ACarousel.AControls#options} for the controls.
 */
ACarousel.AControls = new Class(/** @lends ACarousel.AControls# */{
    Implements: [Options, Events],

    /**
     * Control options
     *
     * @property {Object} [classes = {}] It contains the element's class names for each component.
     * @property {Object} [styles = {}] Array like object with styles.
     * @property {Object} [styles.all = {}] Styles for all controls.
     */
    options: {
        classes: {},
        styles: {
            all: {}
        }
    },

    /**
     * Array like object of controls.
     * @type {Object}
     */
    controls: {},

    // constructor
    initialize: function(el, opts) {
        /**
         * Main carousel element.
         * @type {Element}
         */
        this.carouselElement = el;

        this.setOptions(opts);

        this.applyStyles();
    },

    // Abstract
    // ====================
    /**
     * Enable controls.
     * @abstract
     * @function
     * @public
     */
    enable: function() {},

    /**
     * Disable controls.
     * @abstract
     * @function
     * @public
     */
    disable: function() {},
    // ====================

    Protected: {
        /**
         * Apply the styles to the controls.
         * @memberOf ACarousel#
         * @function
         * @protected
         */
        applyStyles: function() {
            var opts = this.options,
                el = this.carouselElement,
                allCSS = '';

            for (var selector in opts.classes) {
                allCSS += opts.classes[selector] + ', ';
            }
            opts.styles[allCSS] = opts.styles.all;

            for (var selector in opts.styles) {
                (selector in opts.classes)
                    ? el.getElements(opts.classes[selector]).setStyles(opts.styles[selector])
                    : el.getElements(selector).setStyles(opts.styles[selector]);
            }
            delete opts.styles;
        }
    }
});

/**
 * The main carousel builder.
 * To use the carousel with default controls simply supply 'controls: {}' in the options. If the options for controls are not supplied then the controls will be not created.
 * If no options were supplied the builder will try to get the options from the element property 'data-carousel'. The property value should be in JSON format.
 * From MooTools it implements: Options.
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @throws {string} Constructor of Carousel expected 1 or 2 arguments, but received {number}!
 * @throws {string} Element for Carousel was not found in DOM Tree!
 *
 * @constructor
 * @param {string | Element} el Can be the id of an element in DOM Tree, or CSS Selector, or an Element. In case with CSS Selector it will get only the first element.
 * @param {Object} [opts] [Options]{@link Carousel#options} for the Carousel.
 */
var Carousel = new Class(/** @lends Carousel# */{
    Implements: Options,

    /**
     * Carousel options.
     * @type {object}
     *
     * @property {Object} [carousel] [Options for the carousel]{@link ACarousel#options}.
     * @property {string} [carousel.type = 'loop'] Carousel type (can be: 'loop', 'line').
     * @property {Object} [controls] [Options for the controls]{@link ACarousel.AControls#options}.
     * @property {string} [controls.type = 'twoButtons'] Type of the carousel controls (can be: 'twoButtons');
     */
    options: {
        carousel: {
            type: 'loop'
        },

        controls: {
            type: 'twoButtons'
        }
    },

    /**
     * The carousel.
     * @type {*}
     */
    carousel: null,

    /**
     * The carousel controls.
     * @type {*}
     */
    controls: null,

    // constructor
    initialize: function (el, opts) {
        var carouselEvents = {},
            controlsEvents;

        if (arguments.length < 1 || arguments.length > 2) {
            throw 'Constructor of Carousel expected 1 or 2 arguments, but received ' + arguments.length + '!';
        }

        el = $(el) || $$(el);
        if (el == null) {
            throw 'Element for Carousel was not found in the DOM Tree!';
        }

        if (opts == undefined) {
            opts = JSON.decode(el.getProperty('data-carousel')) || {};
        }
        this.setOptions(opts);

        // Select the carousel constructor
        switch (this.options.carousel.type) {
            case 'line':
                this.carousel = new Carousel.Types.Line(el, this.options.carousel);
                break;

            case 'loop':
            default:
                this.carousel = new Carousel.Types.Loop(el, this.options.carousel);
        }

        if (opts.controls) {
            // Select the controls constructor
            switch (this.options.controls.type) {
                case 'twoButtons':
                default :
                    this.controls = new Carousel.Controls.TwoButtons(el, this.options.controls);
                    this.carousel.items[this.carousel.currentActiveID].addClass(this.carousel.options.activeLabel);
                    controlsEvents = {
                        scrollForward: this.carousel.scrollForward.bind(this.carousel),
                        scrollBackward: this.carousel.scrollBackward.bind(this.carousel)
                    };
                    carouselEvents = {
                        enableScrolling: this.controls.enable.bind(this.controls),
                        disableScrolling: this.controls.disable.bind(this.controls),
                        singleScroll: function(direction) {
                            if (!this[(direction == 1) ? 'backward' : 'forward' ].isEnabled) {
                                this[ (direction == 1) ? 'backward' : 'forward' ].isEnabled = true;
                            }
                        }.bind(this.controls.controls),
                        endReached: function() {
                            this.controls.controls.forward.isEnabled = false;
                            this.carousel.stop();
                        }.bind(this),
                        beginReached: function() {
                            this.controls.controls.backward.isEnabled = false;
                            this.carousel.stop();
                        }.bind(this)
                    };
            }

            this.controls.addEvents(controlsEvents);
        }

        this.carousel.addEvents(carouselEvents);
        this.carousel.build();
    }
});

/**
 * This namespace holds the implementations of the abstract carousel [ACarousel]{@link ACarousel}.
 * @namespace
 */
Carousel.Types = {
    /**
     * Loop carousel. All items are scrolled in the loop, that means there are no end of the scrolling.
     *
     * @augments ACarousel
     *
     * @constructor
     * @param {Element} el Main element for the carousel.
     * @param {Object} [opts] [Options]{@link ACarousel#options} for the carousel.
     */
    Loop: new Class(/** @lends Carousel.Types.Loop# */{
        Extends: ACarousel,

        effects: [{},{}],

        Protected: {
            /**
             * Extends the parent [prepareScrolling]{@link ACarousel#prepareScrolling} method.
             * @memberOf ACarousel#
             * @function
             * @protected
             */
            prepareScrolling: function() {
                this.parent();

                // If the amount of items that will be scrolled in loop is greater than the total number of items,
                // then make clones of all items.
                if (this.options.NVisibleItems + this.options.scrollStep > this.options.playlist.NItems) {
                    this.cloneItems(this.items, this.playlistHolder);
                }
            },

            /**
             * Implements the parent abstract [getNewVisibleItems]{@link ACarousel#prepareScrolling} method.
             *
             * @throws {string} Scrolling direction must be -1 or 1 but received "{number}".
             *
             * @memberOf ACarousel#
             * @function
             * @protected
             * @param {number} direction Scrolling direction.
             * @param {number} scrollNTimes Scrolling multiplier.
             * @return {Element[]}
             */
            getNewVisibleItems: function(direction, scrollNTimes) {
                var newItems = [],
                // new first visible ID in this.items after scrolling
                    newItemID,
                // helper variables
                    itemPosition,
                    itemShift,
                    n;

                if (direction === 1) {
                    newItemID = this.firstVisibleItemID + this.options.NVisibleItems;
                    itemShift = this.itemShifts[0];
                    itemPosition = 'bottom';
                } else if (direction === -1) {
                    newItemID = this.firstVisibleItemID - this.options.scrollStep * scrollNTimes;
                    itemShift = this.itemShifts[1] - ((scrollNTimes == 1) ? 0 : this.length * (scrollNTimes - 1));
                    itemPosition = 'top';
                } else {
                    throw 'Scrolling direction must be -1 or 1 but received \"' + direction + '\"!';
                }

                if (scrollNTimes > 1) {
                    var NClones = Math.floor((this.options.NVisibleItems + this.options.scrollStep * scrollNTimes) / this.items.length);
                    if (NClones > 0) {
                        cloneItems(this.items, this.playlistHolder, NClones);
                        for (n = this.options.playlist.NItems; n < this.items.length; n++) {
                            this.items[n].setStyle(this.options.scrollDirection, -this.length);
                        }
                    }
                }

                for (n = 0; n < this.options.scrollStep * scrollNTimes; n++) {
                    newItems[n] = this.items[this.wrapIndices(newItemID + n, 0, this.items.length, true)].setStyle(this.options.scrollDirection, this.length * n + itemShift);
                    this.playlistHolder.grab(newItems[n], itemPosition);
                }

                return newItems;
            },

            /**
             * Implements the parent abstract [calcFirstItemID]{@link ACarousel#calcFirstItemID} method.
             *
             * @memberOf ACarousel#
             * @function
             * @protected
             * @param {number} direction Scrolling direction.
             * @param {number} scrollNTimes Scrolling multiplier.
             */
            calcFirstItemID: function(direction, scrollNTimes) {
                this.firstVisibleItemID += direction * this.options.scrollStep * scrollNTimes;
                this.firstVisibleItemID = this.wrapIndices(this.firstVisibleItemID, 0, this.items.length, true);
            },

            /**
             * Implements the parent abstract [getItemEffects]{@link ACarousel#getItemEffects} method.
             *
             * @memberOf ACarousel#
             * @function
             * @protected
             * @param {number} direction Scrolling direction.
             * @param {number} scrollNTimes Scrolling multiplier.
             * @return {Object}
             */
            getItemEffects: function(direction, scrollNTimes) {
                var effects = {};

                if (scrollNTimes > 1) {
                    var shift = 0;
                    if (direction == 1) {
                        shift = this.options.scrollStep * scrollNTimes;
                    }

                    effects = this.createEffect(this.options.scrollDirection, -this.length * shift, this.length,
                        this.firstVisibleItemID + this.options.NVisibleItems + this.options.scrollStep * scrollNTimes);
                } else {
                    effects = this.effects[( (direction == 1) ? 0 : 1 )];
                }

                return effects;
            }
        }
    }),

    /**
     * Line carousel. All items scrolled in the line, that means it has two ends of the scrolling.
     * By reaching each end will corresponding event fired: [beginReached]{@link Carousel.Line#beginReached} and [endReached]{@link Carouse.Line#endReached}
     *
     * @augments ACarousel
     *
     * @constructor
     * @param {Element} el Main element for the carousel.
     * @param {Object} [opts] [Options]{@link ACarousel#options} for the carousel.
     */
    Line: (function() {
        /**
         * Defines whether the last scroll will made.
         * @type {boolean}
         * @memberOf Carousel.Types.Line~
         */
        var isLast;

        return new Class(/** @lends Carousel.Types.Line# */{
            Extends: ACarousel,

            effects: [{},{},{},{}],

            Protected: {
                /**
                 * Extends the parent [prepareScrolling]{@link ACarousel#prepareScrolling} method.
                 *
                 * @fires Carousel.Line#beginReached
                 *
                 * @memberOf ACarousel#
                 * @function
                 * @protected
                 */
                prepareScrolling: function() {
                    this.parent();

                    /**
                     * Last possible scroll step for limited scrolling.
                     * @type {number}
                     */
                    this.lastScrollStep = this.items.length - this.options.NVisibleItems
                        - this.options.scrollStep * Math.floor((this.items.length - this.options.NVisibleItems) / this.options.scrollStep);
                    if (this.lastScrollStep == 0) {
                        this.lastScrollStep = this.options.scrollStep;
                    }

                    /**
                     * Fired when the carousel has the begin reached.
                     * @event Carousel.Line#beginReached
                     */
                    this.fireEvent('beginReached');

                    var N = this.options.NVisibleItems + this.lastScrollStep;
                    this.effects[2] = this.createEffect(this.options.scrollDirection, -this.length * this.lastScrollStep, this.length, N);
                    this.effects[3] = this.createEffect(this.options.scrollDirection, 0, this.length, N);

                    this.itemShifts[2] = -this.length * this.lastScrollStep;
                },

                /**
                 * Implements the parent abstract [getNewVisibleItems]{@link ACarousel#prepareScrolling} method.
                 *
                 * @fires Carousel.Line#beginReached
                 * @fires Carousel.Line#endReached
                 *
                 * @throws {string} Scrolling direction must be -1 or 1 but received "{number}".
                 * @throws {string} Carousel: The end is reached
                 *
                 * @memberOf ACarousel#
                 * @function
                 * @protected
                 * @param {number} direction Scrolling direction.
                 * @param {number} scrollNTimes Scrolling multiplier.
                 * @return {Element[]}
                 */
                getNewVisibleItems: function(direction, scrollNTimes) {
                    var newItems = [],
                    // new first visible ID in this.items after scrolling
                        newItemID,
                    // helper variables
                        itemPosition,
                        itemShift,
                        n;

                    if (direction === 1) {
                        newItemID = this.firstVisibleItemID + this.options.NVisibleItems;
                        itemShift = this.itemShifts[0];
                        itemPosition = 'bottom';
                    } else if (direction === -1) {
                        newItemID = this.firstVisibleItemID - this.options.scrollStep * scrollNTimes;
                        itemShift = this.itemShifts[1] - ((scrollNTimes == 1) ? 0 : this.length * (scrollNTimes - 1));
                        itemPosition = 'top';
                    } else {
                        throw 'Scrolling direction must be -1 or 1 but received "' + direction + '".';
                    }

                    // If new item id reaches the last item then disable clicked button
                    if (newItemID <= 0 || newItemID + this.options.scrollStep * scrollNTimes >= this.items.length) {
                        (direction == 1)
                        /**
                         * Fired when the carousel has the end reached.
                         * @event Carousel.Line#endReached
                         */
                            ? this.fireEvent('endReached')
                            : this.fireEvent('beginReached');
                    }
                    // Check if last item effects are needed to be applied.
                    isLast = (newItemID + this.options.scrollStep * scrollNTimes) > this.items.length || newItemID < 0;

                    // Gets new items
                    if (isLast) {
                        newItemID = this.wrapIndices(newItemID, 0, this.items.length, false);
                        if (newItemID == 0) {
                            itemShift = this.itemShifts[2] - this.length * this.options.scrollStep * (scrollNTimes - 1);
                        }

                        // Gets last possible new items
                        for (n = 0; n < this.lastScrollStep + this.options.scrollStep * (scrollNTimes - 1); n++) {
                            newItems[n] = this.items[newItemID + n].setStyle(this.options.scrollDirection, this.length * n + itemShift);
                            this.playlistHolder.grab(newItems[n], itemPosition);
                        }
                    } else {
                        this.atEnd = false;
                        if (scrollNTimes > 1) {
                            var NClones = Math.floor((this.options.NVisibleItems + this.options.scrollStep * scrollNTimes) / this.items.length);
                            if (NClones > 0) {
                                cloneItems(this.items, this.playlistHolder, NClones);
                                for (n = this.options.playlist.NItems; n < this.items.length; n++) {
                                    this.items[n].setStyle(this.options.scrollDirection, -this.length);
                                }
                            }
                        }

                        for (n = 0; n < this.options.scrollStep * scrollNTimes; n++) {
                            newItems[n] = this.items[this.wrapIndices(newItemID + n, 0, this.items.length, true)].setStyle(this.options.scrollDirection, this.length * n + itemShift);
                            this.playlistHolder.grab(newItems[n], itemPosition);
                        }
                    }

                    return newItems;
                },

                /**
                 * Implements the parent abstract [calcFirstItemID]{@link ACarousel#calcFirstItemID} method.
                 *
                 * @memberOf ACarousel#
                 * @function
                 * @protected
                 * @param {number} direction Scrolling direction.
                 * @param {number} scrollNTimes Scrolling multiplier.
                 */
                calcFirstItemID: function(direction, scrollNTimes) {
                    this.firstVisibleItemID += direction * ((!isLast)
                        ? this.options.scrollStep * scrollNTimes
                        : this.lastScrollStep + this.options.scrollStep * (scrollNTimes - 1));
                    this.firstVisibleItemID = this.wrapIndices(this.firstVisibleItemID, 0, this.items.length, false);
                },

                /**
                 * Implements the parent abstract [getItemEffects]{@link ACarousel#getItemEffects} method.
                 *
                 * @memberOf ACarousel#
                 * @function
                 * @protected
                 * @param {number} direction Scrolling direction.
                 * @param {number} scrollNTimes Scrolling multiplier.
                 * @return {Object}
                 */
                getItemEffects: function(direction, scrollNTimes) {
                    var effects = {};

                    if (scrollNTimes > 1) {
                        var shift = 0;
                        if (direction == 1) {
                            shift = this.options.scrollStep * scrollNTimes - this.options.scrollStep + this.lastScrollStep;
                        }

                        effects = this.createEffect(this.options.scrollDirection, -this.length * shift, this.length,
                            this.firstVisibleItemID + this.options.NVisibleItems + this.options.scrollStep * scrollNTimes);
                    } else {
                        effects = this.effects[(isLast)
                            ? ( (direction == 1) ? 2 : 3 )
                            : ( (direction == 1) ? 0 : 1 )
                            ];
                    }

                    return effects;
                }
            }
        })
    })()
};

/**
 * This namespace holds the implementations of the abstract controls [ACarousel.AControls]{@link ACarousel.AControls} for the carousel.
 * @namespace
 */
Carousel.Controls = {
    /**
     * Control the carousel with tow buttons.
     *
     * @augments ACarousel.AControls
     *
     * @constructor
     * @param {Element|string} el Main carousel element.
     * @param {Object} [opts] [Options]{@link ACarousel.AControls#options} for the controls.
     */
    TwoButtons: new Class(/** @lends Carousel.Controls.TwoButtons# */{
        Extends: ACarousel.AControls,

        /**
         * Control options
         * @property {string} [event = 'click'] Defines an event for the buttons, that will scroll the carousel.
         * @property {Object} [classes] It contains the element's class names for each component.
         * @property {string} [classes.forward = '.next'] Class name for the forward button.
         * @property {string} [classes.backward = '.previous'] Class name for the backward button.
         * @property {Object} [styles] Array like object with styles.
         * @property {Object} [styles.forward = {marginLeft: '100%'}] Styles for the forward button.
         * @property {Object} [styles.backward = {}] Styles for the backward button.
         * @property {Object} [styles.all = {display: 'block', overflow: 'hidden', position: 'absolute', top: '50%', zIndex: '2', '-moz-user-select': 'none'}] Styles for all controls.
         */
        options: {
            event: 'click',
            classes: {
                forward: '.next',
                backward: '.previous'
            },
            styles: {
                all: {
                    display: 'block',
                    overflow: 'hidden',
                    position: 'absolute',
                    top: '50%',
                    zIndex: '2',
                    '-moz-user-select': 'none'
                },
                forward: {
                    marginLeft: '100%'
                },
                backward: {}
            }
        },

        /**
         * Button controls.
         * @type {object}
         *
         * @property {object} forward Button for scrolling forward.
         * @property {Element} forward.element Element in the DOM Tree for the button.
         * @property {boolean} [forward.IsEnabled = true] Defines whether the forward button is enabled.
         * @property {object} backward Button for scrolling backward.
         * @property {Element} backward.element Element in the DOM Tree for the backward button.
         * @property {boolean} [backward.IsEnabled = true] Defines whether the backward button is enabled.
         */
        controls: {
            forward: {
                element: null,
                isEnabled: true
            },
            backward: {
                element: null,
                isEnabled: true
            }
        },

        // constructor
        initialize: function(el, opts) {
            this.parent(el, opts);

            this.controls.forward.element = this.carouselElement.getElement(this.options.classes.forward).setProperty('unselectable', 'on');
            this.controls.backward.element = this.carouselElement.getElement(this.options.classes.backward).setProperty('unselectable', 'on');

            if (!this.controls.forward.element) {
                throw 'Forward control button for the carousel was not found.';
            }
            if (!this.controls.backward.element) {
                throw 'Backward control button for the carousel was not found.';
            }
        },

        /**
         * Implements the parent abstract [enable]{@link ACarousel.AControls#enable} method.
         * @function
         * @public
         */
        enable: function() {
            this.controls.forward.element.addEvent(this.options.event, function (ev) {
                ev.stop();

                if (this.controls.forward.isEnabled) {
                    /**
                     * Fired when the forward button is enabled and clicked.
                     * @event Carousel.Controls.TwoButtons#scrollForward
                     */
                    this.fireEvent('scrollForward');
                }
            }.bind(this));

            this.controls.backward.element.addEvent(this.options.event, function (ev) {
                ev.stop();

                if (this.controls.backward.isEnabled) {
                    /**
                     * Fired when the backward button is enabled and clicked.
                     * @event Carousel.Controls.TwoButtons#scrollBackward
                     */
                    this.fireEvent('scrollBackward');
                }
            }.bind(this));
        },

        /**
         * Implements the parent abstract [disable]{@link ACarousel.AControls#disable} method.
         * @function
         * @public
         */
        disable: function() {
            if (this.controls.backward.element) {
                this.controls.backward.element.setStyle('display', 'none');
            }
            if (this.controls.forward.element) {
                this.controls.forward.element.setStyle('display', 'none');
            }
        }
    })
};

/**
 * Holds an playlist, that will be used by Carousel objects.
 *
 * @author Valerii Zinchenko
 *
 * @throws {string} No items were found in the playlist.
 *
 * @example <caption>HTML container for playlist.</caption>
 * &ltdiv id="playlistID" class="playlist"&gt
 *     &ltdiv class="item"&gtitem1&lt/div&gt
 *     &ltdiv class="item"&gtitem2&lt/div&gt
 *     ...
 * &lt/div&gt
 *
 * @constructor
 * @param {string | Element} element Can be the id of an element in DOM Tree, or CSS Selector, or an Element that holds playlist's items. In case with CSS Selector it will get only the first element.
 * @param {string} [itemSelector] CSS Selector of the playlist's items. If this argument is not defined, then all children of the holder will be selected as playlist's items.
 */
var CarouselPlaylist = new Class(/** @lends CarouselPlaylist# */{
    /**
     * Indicates whether this playlist is external relative to the carousel, which uses this playlist.
     * @type {boolean}
     */
    isExtern: true,

    // constructor
    initialize: function (element, itemSelector) {
        this.itemSelector = itemSelector;

        var holder = $(element) || $$(element)[0];
        if (holder == null) {
            throw 'Element for CarouselPlaylist was not found in DOM Tree!';
        }

        if (this.itemSelector === undefined) {
            this.items = holder.getChildren();
        } else {
            this.items = holder.getElements(this.itemSelector);
        }

        /**
         * Amount of items in the playlist.
         * @type {Number}
         */
        this.NItems = this.items.length;
        if (this.NItems == 0) {
            throw 'No items were found in the playlist.';
        }
    },
    /**
     * Hide the playlist.
     * @function
     * @public
     */
    hide: function () {
        this.items[0].getParent().dispose();
    },

    /**
     * Get the playlist's holder.
     *
     * @function
     * @public
     * @returns {Element}
     */
    getHolder: function(){
        return this.items[0].getParent();
    }
});

/**
 * Connects an Carousel objects and attach events to them. From MooTools it implements: Events
 *
 * @author Valerii Zinchenko
 *
 * @throws {string} Not enough arguments!
 * @throws {string} Second argument must be an Array of Carousel objects!
 * @throws {string} Element #{number} in the array is not instance of Carousel!
 * @throws {string} Carousels can not be connected, because of different amount of items in the playlists!
 *
 * @constructor
 * @param {Carousel[]} carousels Array of Carousel objects that will be connected.
 */
var CarouselConnector = new Class(/** @lends CarouselConnector# */{
    Implements: Events,

    // constructor
    initialize: function (carousels) {
        // Check input arguments
        if (arguments.length != 1) {
            throw 'Not enough arguments!';
        }
        if (!(carousels instanceof Array)) {
            throw 'Second argument must be an Array of Carousel objects!';
        }
        for (n = 0; n < carousels.length; n++) {
            if (!(carousels[n] instanceof Carousel)) {
                throw 'Element #' + n + ' in the array is not instance of Carousel!';
            }
            carousels[n] = carousels[n].carousel;
        }
        for (var n = 0; n < carousels.length - 1; n++) {
            if (carousels[n].options.playlist.NItems !== carousels[n + 1].options.playlist.NItems) {
                throw 'Carousels can not be connected, because of different amount of items in the playlists!';
            }
        }

        /**
         * Array of connected carousels.
         * @type {Carousel[]}
         */
        this.carousels = carousels;

        // hide playlist if it is external relative to all connected carousels
        if (this.carousels[0].options.playlist.isExtern === true) {
            this.carousels[0].options.playlist.hide();
        }

        // Add events to the connected carousels
        for (n = 0; n < this.carousels.length; n++) {
            (function (n) {
                var self = this;
                self.carousels[n].addEvent('selectItem', function (id) {
                    // With slicing we exclude the carousel that fires event
                    self.carousels.slice(0, n).each(function (carousel) {
                        self.carouselEventFn(carousel, id);
                    });
                    self.carousels.slice(n + 1, self.carousels.length).each(function (carousel) {
                        self.carouselEventFn(carousel, id);
                    });
                });
            }.bind(this))(n);
        }
    },

    /**
     * It stores the functions for selecting a specific item <tt>id</tt> in <tt>carousel</tt> by fired event [selectItem]{@link Carousel#selectItem}.
     *
     * @function
     * @public
     * @param {Carousel} carousel Connected carousel.
     * @param {number} id Item ID in carousel that will be selected.
     */
    carouselEventFn: function (carousel, id) {
        carousel.scrollTo(id);
        carousel.selectItem(id);
    }
});