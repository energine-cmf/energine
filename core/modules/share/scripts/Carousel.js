/**
 * @file Library to create carousel(s) on the web-pages. It uses [MooTools]{@link http://mootools.net/} framework.
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.2.2
 *
 * @requires MooTools
 */

// TODO: For external playlist only items must be cloned to the local carousel's playlist, not the whole playlist, because of possibility to use item selector.

/**
 * Mutator that creates static members in class.
 *
 * @constructor
 * @augments Class.Mutators
 * @param {Object} members Object that contains properties and methods, which must be static in the class.
 */
Class.Mutators.Static = function(members) {
    this.extend(members);
};

/**
 * @class Holds an playlist, that will be used by Carousel objects.
 *
 * @type {Class}
 * @author Valerii Zinchenko
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
    /**
     * Amount of items in the playlist.
     * @type {Number}
     */
    NItems: 0,
    // constructor
    initialize: function(element, itemSelector) {
        this.itemSelector = itemSelector;
        /**
         * Main holder of playlist.
         * @type {Element}
         */
        this.holder = $(element) || $$(element)[0];
        if (this.holder == null)
            throw 'Element for CarouselPlaylist was not found in DOM Tree!';

        if (this.itemSelector === undefined)
            this.items = this.holder.getChildren();
        else
            this.items = this.holder.getElements(this.itemSelector);
        if (this.items.length == 0)
            throw 'No items were found in the playlist.';

        this.NItems = this.items.length;
    },
    /**
     * Hides playlist.
     * @public
     */
    hide: function() {
        this.holder.dispose();
    }
});

/**
 * @class Connects an Carousel objects and attach events to them. From MooTools it implements: Events
 *
 * @type {Class}
 * @author Valerii Zinchenko
 *
 * @constructor
 * @param {Carousel[]} carousels Array of Carousel objects that will be connected.
 */
var CarouselConnector = new Class(/** @lends CarouselConnector# */{
    Implements: Events,
    // constructor
    initialize: function(carousels) {
        // Check input arguments
        if (arguments.length != 1)
            throw 'Not enough arguments!';
        if ( !(carousels instanceof Array) )
            throw 'Second argument must be an Array of Carousel objects!';
        for (n = 0; n < carousels.length; n++)
            if ( !(carousels[n] instanceof Carousel) )
                throw 'Element #' + n + ' in second argument is not instance of Carousel!';

        /**
         * Array of connected carousels.
         * @type {Carousel[]}
         */
        this.carousels = carousels;

        // hide playlist if it is external relative to all connected carousels
        if (this.carousels[0].options.playlist.isExtern === true)
            this.carousels[0].options.playlist.hide();

        // Add events to the connected carousels
        for (var n = 0; n < this.carousels.length; n++) {
            (function(n) {
                var self = this;
                self.carousels[n].addEvent('selectItem', function(id) {
                    // With slicing we exclude the carousel that fires event
                    self.carousels.slice(0, n).each(function(carousel) {
                        self.carouselEventFn(carousel, id);
                    });
                    self.carousels.slice(n+1, self.carousels.length).each(function(carousel) {
                        self.carouselEventFn(carousel, id);
                    });
                });
            }.bind(this))(n);
        }
    },

    /**
     * It stores the functions for selecting a specific item <tt>id</tt> in <tt>carousel</tt> by fired event <tt>selectItem</tt>.
     *
     * @public
     * @param {Carousel} carousel Connected carousel.
     * @param {number} id Item ID in carousel that will be selected.
     */
    carouselEventFn: function (carousel, id) {
        carousel.scrollTo(id);
        carousel.selectItem(id);
    }
});

/**
 * @class Carousel. The carousel self is located in the one of the element in DOM Tree, by default in div-tag.
 * The carousel's element must contain the view-box element with class name property <b>'viewbox'</b> and two buttons for scrolling
 * to the left and to the right with class name property <b>'next'</b> and <b>'previous'</b> respectively.
 * From MooTools it implements: Options, Events, Chain.
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @type {Class}
 *
 * @constructor
 * @param {string | Element} element Can be the id of an element in DOM Tree, or CSS Selector, or an Element. In case with CSS Selector it will get only the first element.
 * @param {Object} [options] [Options]{@link Carousel#options}, that can be applied to the Carousel.
 */
var Carousel = new Class(/** @lends Carousel# */{
    Implements: [Options, Events, Chain],

    Static: {
        /**
         * Counter of Carousel objects.
         *
         * @memberOf Carousel
         * @static
         * @type {number}
         */
        count: 0,

        /**
         * Assigns id.
         *
         * @memberOf Carousel
         * @static
         * @function
         * @returns {number}
         */
        assignID: function() {
            return this.count++;
        }
    },

    /**
     * Value of an element's parameter 'class' at the active item in the carousel.
     * @type {string}
     */
    activeLabel : 'active',

    /**
     * Options of the Carousel.
     * @type {object}
     * @property {number} [NVisibleItems = 1] Number of visible items.
     * @property {number} [scrollStep = 1] Default scrolling step.
     * @property {boolean} [loop = true] Defines if scrolled items are in loop or not.
     * @property {number} [effectDuration = 700] Duration of the scrolling.
     * @property {CarouselPlaylist} [playlist = null] Reference to the playlist. If the playlist is not defined, then the playlist will be created from the element class <tt>'.viewbox'</tt>.
     * @property {Object} [style = internal predefined style] Reference to the object with core styles for the carousel.
     * This is need for the elimination the using of Asset.css('carousel.css') in the carousel's constructor,
     * because we do not know how long the file 'carousel.css' must be parsed and applied to the HTML document
     * before the using of stylized elements.
     * @property {string} [event = 'click'] Defines an event for the buttons, that will scroll the carousel.
     */
    options: {
        // Number of visible items.
        NVisibleItems: 1,

        // Default scroll step.
        scrollStep: 1,

        // Defines if scrolled items are in loop or not.
        loop: true,

        // Duration of the scrolling.
        effectDuration: 700,

        // Playlist of the carousel.
        playlist: null,

        // Core styles for the carousel.
        style: {
            '.carousel': {
                position: 'relative'
            },
            '.carousel_viewbox': {
                position: 'relative',
                overflow: 'hidden',
                margin: 'auto'
            },
            '.item': {
                position: 'absolute',
                textAlign: 'center',
                verticalAlign: 'middle'
            },
            '.item.active': {
                textAlign: 'center',
                verticalAlign: 'middle'
            },
            '.next_control, .previous_control': {
                display: 'block',
                overflow: 'hidden',
                position: 'absolute',
                top: '50%',
                zIndex: '2',
                '-moz-user-select': 'none'
            },
            '.next_control': {
                marginLeft: '100%'
            }
        },

        // Defines an event for the buttons, that will scroll the carousel.
        event: 'click'
    },
    // constructor
    initialize: function(element, options) {
        /**
         * Indicates whether scrolling is finished.
         * @type {boolean}
         */
        this.isEffectCompleted = true;
        /**
         * indicates current active item in playlist.
         * @type {number}
         */
        this.currentActiveID = 0;
        /**
         * Carousel ID.
         * @type {number}
         * @private
         */
        this._id = Carousel.assignID();

        if (arguments.length < 1 || arguments.length > 2)
            throw 'Constructor of Carousel expected 1 or 2 arguments, but received ' + arguments.length + '!';

        /**
         * Main element from DOM Tree for the carousel. It must contain the '.viewbox' element and buttons (a-tags) for scrolling.
         * @type {Element}
         */
        this.carousel = $(element) || $$(element)[0];
        if (this.carousel == null)
            throw 'Element for Carousel was not found in DOM Tree!';

        this.setOptions(options);
        this.checkOptions();

        /**
         * View-box element of the carousel that holds an playlist items.
         * @type {Element}
         */
        this.element = this.carousel.getElement('.viewbox');

        // If the playlist is not explicitly specified, set than try to get a playlist from the carousel.
        if (this.options.playlist === null)
            try {
                this.options.playlist = new CarouselPlaylist(this.element.getChildren()[0]);
            } catch (err) {
                console.warn(err);
                throw 'Carousel can not be created without playlist.';
            }

        // Check whether the playlist is internal. If not - make clone
        if (this.element === this.options.playlist.holder.getParent()) {
            /**
             * Internal holder of playlist items.
             * @type {Element}
             */
            this.holder = this.options.playlist.holder;
            this.options.playlist.isExtern = false;
        } else
            this.holder = this.options.playlist.holder.clone().inject(this.element);

        /**
         * Holds all items from the playlist.
         * @type {Elements|Element[]}
         */
        this.items = this.holder.getChildren();
        this.NItems = this.items.length;
        this.items[this.currentActiveID].addClass(this.activeLabel);

        // Add 'click'-event to all items
        this.items.each(function(it,n) {
            var self = this;
            it.addEvent('click', function(defaultEvent) {
                defaultEvent.stop();
                self.selectItem(n);
                self.fireEvent('selectItem', n);
            });
        }, this);

        /**
         * Buttons from the DOM Tree.
         * @type {object}
         * @property {object} previous Previous button.
         * @property {boolean} [previous.IsEnabled = true] Indicates whether this button enabled or not.
         * @property {Element} previous.button Element in the DOM Tree for the previous button. It must be the in carousel's element with class name <b>'previous'</b>.
         * @property {object} next Next button.
         * @property {boolean} [next.IsEnabled = true] Indicates whether this button enabled or not.
         * @property {Element} next.button Element in the DOM Tree for the next button. It must be in the carousel's element with class name <b>'next'</b>.
         */
        this.buttons = {
            previous: {
                isEnabled: true,
                button: this.carousel.getElement('.previous')
            },
            next: {
                isEnabled: true,
                button: this.carousel.getElement('.next')
            }
        };

        // Sets core styles
        this.carousel.setStyles(this.options.style['.carousel']);
        delete this.options.style['.carousel'];
        for (var selector in this.options.style)
            this.carousel.getElements(selector).setStyles(this.options.style[selector]);
        delete this.options.style;

        /**
         * Indicates whether the scrolling can be done. If the amount of items in the playlist less or equal
         * to the amount of visible items, than in the carousel then there is nothing to scroll.
         * @type {boolean}
         */
        this.canScroll = this.NItems > this.options.NVisibleItems;
        // If in the carousel is nothing to scroll, then hide the buttons and set scrollStep to 0.
        if (!this.canScroll) {
            this.options.NVisibleItems = this.NItems;
            this.options.scrollStep = 0;
            if (this.buttons.previous.button)
                this.buttons.previous.button.setStyle('display', 'none');
            if (this.buttons.next.button)
                this.buttons.next.button.setStyle('display', 'none');
        } else {
            /**
             * Indicates the first visible item ID in the carousel.
             * @type {number}
             */
            this.firstVisibleItemID = 0;

            if (this.options.scrollStep > this.options.NVisibleItems) {
                this.options.scrollStep = this.options.NVisibleItems;
                console.warn('The option \"scrollStep\" > \"NVisibleItems\". It is reseted to the value of \"NVisibleItems\".');
            }

            // If the amount of items that will be scrolled in loop is greater than the total number of items,
            // then make clones of all items (only if scrolling is in loop).
            if (this.options.NVisibleItems + this.options.scrollStep > this.NItems && this.options.loop)
                this.cloneItems(this.items, this.holder);

            if (this.options.loop)
                this.effects = [{}, {}];
            else {
                /**
                 * Array of objects with effects, that will be applied to the items by scrolling.
                 * @type {Array}
                 */
                this.effects = [{}, {}, {}, {}];
                /**
                 * Last possible scroll step for limited scrolling.
                 * @type {number}
                 */
                this.lastScrollStep = this.items.length - this.options.NVisibleItems -
                    this.options.scrollStep * Math.floor( (this.items.length - this.options.NVisibleItems) / this.options.scrollStep);
                if (this.lastScrollStep == 0)
                    this.lastScrollStep = this.options.scrollStep;
                this.buttons.previous.isEnabled = false;
            }

            // Set events for the buttons.
            this.buttons.next.handler = function(defaultEvent) {
                defaultEvent.stop();
                this.scrollLeft();
            }.bind(this);
            this.buttons.previous.handler = function(defaultEvent) {
                defaultEvent.stop();
                this.scrollRight();
            }.bind(this);

            Object.each(this.buttons, function(btn) {
                if (btn.button) {
                    btn.button.addEvent(this.options.event, btn.handler).setProperty('unselectable', 'on');
                }
            }, this);
        }

        /**
         * Item width.
         * @type {number}
         */
        this.width = this.items[0].getSize().x;

        // Calculate effects for scrolling
        // NOTE: Do not create unique effects for 'left' and 'right' style. It is complex:
        //      - each element must have only 'left' or 'right' style and not both
        //      - before applying effect we need delete one style from the visible and new items and assign other
        //      - prepare an object with proper effects for the Fx.Elements
        if (this.canScroll) {
            var N = this.options.NVisibleItems + this.options.scrollStep;
            this.effects[0] = this.createEffect('left', -this.width*this.options.scrollStep, this.width, N);
            this.effects[1] = this.createEffect('left', 0, this.width, N);

            this.itemShifts = [ this.width * this.options.NVisibleItems,
                -this.width * this.options.scrollStep ];

            // Only if scrolling is not in loop
            if (!this.options.loop) {
                N = this.options.NVisibleItems + this.lastScrollStep;
                this.effects[2] = this.createEffect('left', -this.width*this.lastScrollStep, this.width, N);
                this.effects[3] = this.createEffect('left', 0, this.width, N);

                this.itemShifts.push(-this.width * this.lastScrollStep);
            }
        }

        // Apply new width to the 'view-box'-element
        this.element.setStyle('width', this.width * this.options.NVisibleItems);

        // Reposition elements
        this.items.slice(0, this.options.NVisibleItems).each(function(it,n) {
            it.setStyle('left', n * this.width);
        }, this);
        this.items.slice(this.options.NVisibleItems).each(function(it) {
            it.setStyle('left', -this.width);
        }, this);
    },
    /**
     * Scroll left by one step. Multiple calls by not finished effect will be queued.
     * @public
     */
    scrollLeft:  function() {
        if (this.buttons.next.isEnabled)
            if (this.isEffectCompleted)
                this._scrollEffect(1);
            else
                this.chain(this.scrollLeft.bind(this));
    },
    /**
     * Scroll right by one step. Multiple calls by not finished effect will be queued.
     * @public
     */
    scrollRight: function() {
        if (this.buttons.previous.isEnabled)
            if (this.isEffectCompleted)
                this._scrollEffect(-1);
            else
                this.chain(this.scrollRight.bind(this));
    },
    /**
     * Selects item in the carousel and marks its as active.
     * @public
     * @param {number} id Item ID.
     */
    selectItem: function(id) {
        if (this.currentActiveID === id)
            return;

        for (var n = 0; n < this.items.length / this.NItems; n++) {
            this.items[this.currentActiveID + this.NItems*n].removeClass(this.activeLabel);
            this.items[id + this.NItems*n].addClass(this.activeLabel);
        }
        this.currentActiveID = id;
    },
    /**
     * Scrolls to the specific item ID.
     * @public
     * @param {number} id Item ID.
     */
    scrollTo: function(id) {
        var direction,
            NTimes;

        // Check whether the desired item ID is visible in the carousel. If it is, then do not scroll.
        for (var n = 0; n < this.options.NVisibleItems; n++ )
            if (this.wrapIndices(this.firstVisibleItemID + n, 0, this.NItems) == id)
                return;

        direction = (id > this.currentActiveID) ? 1 : -1;
        var diffFromLeft = Math.abs(id - this.currentActiveID);

        if (this.options.loop) {
            var diffFromRight = this.NItems - diffFromLeft;

            if (diffFromLeft <= diffFromRight) {
                NTimes = diffFromLeft;
            } else {
                direction *= -1;
                NTimes = diffFromRight;
            }
        } else {
            NTimes = diffFromLeft;
            if (NTimes > this.NItems - this.options.NVisibleItems)
                NTimes = this.NItems - this.options.NVisibleItems;
        }
        NTimes = Math.ceil(NTimes / this.options.scrollStep);
        this._scrollEffect(direction, Math.abs(NTimes), true);
    },
    /**
     * The core method that starts effects. If one of the new visible items is not marked as active,
     * this will set leftmost or rightmost item as active and after that fires an event, that new item is set as active.
     * If this method will be called with <tt>isSelected == true</tt> then the selection will be ignored.
     *
     * @function
     * @protected
     * @param {number} direction Identifies the scrolling direction. It can be 1 or -1 to scroll left and right respectively.
     * @param {number} [scrollNTimes = 1] Defines how many scrollings must be done by one call of scrolling.
     * @param {boolean} [isSelected = false] Indicates whether one of the new visible items is already selected.
     */
    _scrollEffect: function(direction, scrollNTimes, isSelected) {
        var fx,
            itemsToScroll = [], // all items from playlist, that will be scrolled
            newItems = [],      // collects all new items, that will be visible after scrolling
            newItemID,          // new first visible ID in this.items after scrolling
            isLast = false,     // is true if non-default effects should be applied (only if scrolling is not in loop)
        // helper variables
            effects = {},
            itemPosition,
            itemShift,
            itemID,
            n;

        if (!this.canScroll)
            return;
        this.isEffectCompleted = false;

        if (scrollNTimes <= 0)
            throw 'scrollNTimes must be > 0';
        scrollNTimes = scrollNTimes || 1;

        // Selects proper properties for scrolling
        if (direction === 1) {
            newItemID = this.firstVisibleItemID + this.options.NVisibleItems;
            itemShift = this.itemShifts[0];
            itemPosition = 'bottom';
        } else if (direction === -1) {
            newItemID = this.firstVisibleItemID - this.options.scrollStep * scrollNTimes;
            itemShift = this.itemShifts[1] - ((scrollNTimes == 1) ? 0 : this.width * (scrollNTimes-1));
            itemPosition = 'top';
        } else
            throw 'Scrolling direction must be -1 or 1 but received \"' + direction + '\"!';

        // Only if scrolling is not in loop
        if (!this.options.loop) {
            // If new item id reaches the last item then disable clicked button
            if (newItemID <= 0 || newItemID + this.options.scrollStep * scrollNTimes >= this.items.length)
                this.buttons[(direction == 1) ? 'next' : 'previous'].isEnabled = false;
            // Check if not default effects needed to be applied
            isLast = (newItemID + this.options.scrollStep * scrollNTimes) > this.items.length || newItemID < 0;
        }

        // Gets new items
        if (!isLast) {
            if ( scrollNTimes > 1) {
                var NClones = Math.floor( (this.options.NVisibleItems + this.options.scrollStep*scrollNTimes) / this.items.length);
                if (NClones > 0) {
                    this.cloneItems(this.items, this.holder, NClones);
                    for (n = this.NItems; n < this.items.length; n++)
                        this.items[n].setStyle('left', -this.width);
                }
            }

            // The default behaviour to get new items
            for (n = 0; n < this.options.scrollStep * scrollNTimes; n++) {
                newItems[n] = this.items[this.wrapIndices(newItemID + n, 0, this.items.length, true)].setStyle('left', this.width * n + itemShift);
                this.holder.grab(newItems[n], itemPosition);
            }
        } else {
            // Only if scrolling is not in loop
            newItemID = this.wrapIndices(newItemID, 0, this.items.length, this.options.loop);
            if (newItemID == 0)
                itemShift = this.itemShifts[2] - this.width * this.options.scrollStep * (scrollNTimes-1);

            // Gets last possible new items
            for (n = 0; n < this.lastScrollStep + this.options.scrollStep * (scrollNTimes-1); n++) {
                newItems[n] = this.items[newItemID + n].setStyle('left', this.width * n + itemShift);
                this.holder.grab(newItems[n], itemPosition);
            }
        }

        // Collects all visible items
        for (n = 0; n < this.options.NVisibleItems; n++) {
            itemID = this.firstVisibleItemID + n;
            if (itemID >= this.items.length)
                itemID -= this.items.length;

            itemsToScroll[n] = this.items[itemID];
        }
        // Connects all visible and new items
        itemsToScroll = (direction == 1) ? itemsToScroll.concat(newItems) :
            newItems.concat(itemsToScroll);

        if (scrollNTimes > 1) {
            var shift = 0;
            if (direction == 1)
                shift = this.options.scrollStep * scrollNTimes + ((this.options.loop) ?  0: -this.options.scrollStep + this.lastScrollStep);
            effects = this.createEffect('left', -this.width * shift, this.width, this.firstVisibleItemID + this.options.NVisibleItems + this.options.scrollStep * scrollNTimes);
        } else {
            effects = this.effects[(!this.options.loop && isLast) ? (direction == 1) ? 2 : 3 :
                (direction == 1) ? 0 : 1 ];
        }

        // Save first visible item ID from this.items
        this.firstVisibleItemID += direction * ((!isLast) ? this.options.scrollStep * scrollNTimes :
            this.lastScrollStep + this.options.scrollStep * (scrollNTimes-1));
        this.firstVisibleItemID = this.wrapIndices(this.firstVisibleItemID, 0, this.items.length, this.options.loop);

        if (!isSelected) {
            // Checks whether the selected item is visible
            var isSelectedVisible = false;
            for (n = 0; n < this.options.NVisibleItems; n++ ) {
                if (this.wrapIndices(this.firstVisibleItemID + n, 0, this.NItems) == this.currentActiveID) {
                    isSelectedVisible = true;
                    break;
                }
            }
            // If the selected item is not visible, then the leftmost or rightmost visible item will be selected
            if (!isSelectedVisible) {
                this.selectItem(this.wrapIndices( (direction == 1) ? this.firstVisibleItemID :
                    this.firstVisibleItemID+this.options.NVisibleItems-1, 0, this.NItems));
                this.fireEvent('selectItem', this.currentActiveID);
            }
        }

        fx = new Fx.Elements(new Elements(itemsToScroll), {
            'duration': this.options.effectDuration,
            'transition': 'cubic:in:out',
            'onChainComplete': function () {
                this.isEffectCompleted = true;
                this.callChain();
                // Turn on disabled button (only if scrolling is not in loop!)
                if (!this.options.loop)
                    if (!this.buttons[(direction == 1) ? 'previous' : 'next'].isEnabled)
                        this.buttons[(direction == 1) ? 'previous' : 'next'].isEnabled = true;
            }.bind(this)
        });
        fx.start(effects);
    }.protect(),

    /**
     * This clones each item in '<tt>items</tt>' and place them to '<tt>where</tt>'.
     *
     * @function
     * @protected
     * @param {Array} items Items that will be cloned.
     * @param {HTMLElement} holder Element that stores <tt>items</tt>.
     * @param {number} [NTimes = 1] Specifies how many clones will be created. You must use explicit value 1 if you want to use last argument <tt>'where'</tt>.
     * @param {string} [where = 'bottom'] The place to inject each clone. It can be '<tt>top</tt>', '<tt>bottom</tt>', '<tt>after</tt>', or '<tt>before</tt>'.
     */
    cloneItems: function(items, holder, NTimes, where) {
        NTimes = NTimes || 1;
        where = where || 'bottom';
        var N = items.length;
        for (var i = 1; i <= NTimes; i++) {
            for (var n = 0; n < N; n++) {
                items.push(items[n].clone().inject(holder, where));
                items[n+N*i].cloneEvents(items[n]);
                if (items[n].hasClass(this.activeLabel))
                    items[n+N*i].addClass(this.activeLabel);
            }
        }
    }.protect(),

    /**
     * This function creates an object with effects. This will be applied to the Fx.Element by scrolling.
     *
     * @function
     * @protected
     * @param {string} key Key for value.
     * @param {number} begin Indicates a start value of effect.
     * @param {number} step Indicates a step values between effects.
     * @param {number} count Indicates how many effects must be generated.
     * @returns {Object} Object with '<tt>count</tt>' objects like {key: effectValue}
     *
     * @example
     * <pre>
     * createEffect('left', 0, 10, 3) == { { 'left': 0  }
     *                                     { 'left': 10 }
     *                                     { 'left': 20 } }
     * </pre>
     */
    createEffect: function(key, begin, step, count) {
        var obj = {};
        for (var i=0; i < count; i++)
            obj[i] = (function() {
                var subobj = {};
                subobj[key] = begin + step*i;
                return subobj;
            })();

        return obj;
    }.protect(),

    /**
     * This function wraps an index between lower and upper limits.
     *
     * @function
     * @protected
     * @param {number} id Index that must be wrapped.
     * @param {number} minID Lower limit.
     * @param {number} maxID Upper limit.
     * @param {boolean} [toWrap = true] Determines, will the id wrapped (true) or cropped (false) by limits.
     * @returns {number} Wrapped id.
     *
     * @example
     * <pre>
     * wrapIndices(-2, 0, 8, true)  == 6
     * wrapIndices(-2, 0, 8, false) == 0
     * </pre>
     */
    wrapIndices: function(id, minID, maxID, toWrap) {
        if (maxID === 0 || minID < 0 || maxID < minID)
            throw 'Arguments must be:\n\tmaxID != 0\n\tminID >= 0\n\tmaxID > minID';
        if (toWrap === undefined)
            toWrap = true;
        if (toWrap)
            return (id >= maxID) ? id - maxID * Math.floor(id/maxID) :
                (id < minID) ? id + maxID * Math.ceil(Math.abs(id)/maxID) : id;
        else
            return (id >= maxID) ? maxID :
                (id < minID) ? minID : id;
    }.protect(),

    /**
     * Checks types and boundaries of options.
     *
     * @function
     * @protected
     */
    checkOptions: function() {
        var checked = this.checkNumbers({
            NVisibleItems:  [this.options.NVisibleItems,  1, 1],
            scrollStep:     [this.options.scrollStep,     1, 1],
            effectDuration: [this.options.effectDuration, 0, 700]
        });
        this.options.NVisibleItems = checked.NVisibleItems[0];
        this.options.scrollStep = checked.scrollStep[0];
        this.options.effectDuration = checked.effectDuration[0];

        // loop
        if (typeOf(this.options.loop) != 'boolean') {
            this.options.loop = !!this.options.loop;
            console.warn('The option \"loop\" was not with type of \"boolean\". Its value set to \"' + this.options.loop.toString() + '\"');
        }

        // playlist
        if (this.options.playlist != null && !instanceOf(this.options.playlist, CarouselPlaylist)) {
            this.options.playlist = null;
            console.warn('The option for \"playlist\" is incorrect. Its value reset to \"null\"');
        }

        // event
        if (typeOf(this.options.event) != 'string' || this.options.event === '') {
            this.options.event = 'click';
            console.warn('The option for \"event\" is not type of the \"string\". Its value reset to \"click\"')
        }
    }.protect(),

    /**
     * Checks if the variable in input object is type of number and lower than some default value.
     *
     * @function
     * @protected
     * @param {Object} obj Object with name of variable that contain an array with size 3: [0] value that will be checked; [1] min value; [2] default value.
     * @return {Object} Object with checked values.
     */
    checkNumbers: function(obj) {
        for (var key in obj) {
            if (typeOf(key) != 'number')
                obj[key][0] = Number.from(obj[key][0]);
            if (obj[key][0] == null || isNaN(obj[key][0]) || obj[key][0] < obj[key][1]) {
                obj[key][0] = obj[key][2];
                console.warn('The option for \"' + key + '\" is incorrect. Its value reset to', obj[key][0] + '.');
            }
        }
        return obj;
    }.protect()
});