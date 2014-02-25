/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[PageList]{@link PageList}</li>
 * </ul>
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.0.2
 */

/**
 * List of pages. From MooTools it implements: Options, Events.
 *
 * @constructor
 * @param {Object} [options] Set of events. [Options]{@link PageList#options}.
 */
var PageList = new Class(/** @lends PageList# */{
    Implements: [Options, Events],

    /**
     * Current page number.
     * @type {number}
     */
    currentPage: 1,

    /**
     * Indicates whether the PageList is disabled.
     * @type {boolean}
     */
    disabled: false,

    // constructor
    initialize: function(options) {
        Asset.css('pagelist.css');
        this.setOptions(options);

        /**
         * The main holder element.
         * @type {Element}
         */
        this.element = new Element('ul').addClass('e-pane-toolbar e-pagelist').setProperty('unselectable', 'on');
    },

    /**
     * Get the [main holder element]{@link PageList#element}.
     * @function
     * @public
     * @returns {Element}
     */
    getElement: function() {
        return this.element;
    },

    /**
     * Disable the PageList.
     * @function
     * @public
     */
    disable: function() {
        this.disabled = true;
        this.element.setStyle('opacity', 0.25);
    },

    /**
     * Enable PageList.
     * @function
     * @public
     */
    enable: function() {
        this.disabled = false;
        this.element.setStyle('opacity', 1);
    },

    /**
     * Build the list of pages.
     *
     * @param {number} numPages Total amount of pages.
     * @param {number} currentPage Current viewed page.
     */
    build: function(numPages, currentPage) {
        this.currentPage = currentPage;
        this.clear();

        if (numPages <= 1) {
            this.element.dispose();
            return;
        }
        /*else if(numPages == 1){
            this.element.setStyle('visibility', 'hidden');
            return;
        }
        this.element.setStyle('visibility', 'visible');*/




        // Amount of visible pages on the each side relative to the current page.
        var VISIBLE_PAGES_COUNT = 2;

        var startPage = (currentPage > VISIBLE_PAGES_COUNT) ? currentPage - VISIBLE_PAGES_COUNT : 1;
        var endPage = currentPage + VISIBLE_PAGES_COUNT;
        if (endPage > numPages) {
            endPage = numPages;
        }

        // Build first pages.
        if (startPage > 1) {
            this._createPageLink(1, 1).inject(this.element);
            if (startPage > 2) {
                this._createPageLink(2, 2).inject(this.element);
                if (startPage > 3) {
                    this._createPageLink('...').inject(this.element)
                }
            }
        }

        // Build the main range of pages.
        for (var i = startPage; i <= endPage; i++) {
            this._createPageLink(i, i).inject(this.element);
        }

        // Build last pages.
        if (endPage < numPages) {
            if (endPage < (numPages - 1)) {
                if (endPage < (numPages - 2)) {
                    this._createPageLink('...').inject(this.element)
                }
                this._createPageLink(numPages - 1, numPages - 1).inject(this.element)
            }
            this._createPageLink(numPages, numPages).inject(this.element)
        }

        this.element.getElement('[index=' + this.currentPage + ']').addClass('current');

        // Add buttons to the current page.
        if (currentPage != 1) {
            this._createPageLink('previous',currentPage-1, 'images/prev_page.gif').inject(this.element, 'top');
        }
        if (currentPage != numPages) {
            this._createPageLink('next', currentPage+1, 'images/next_page.gif').inject(this.element, 'bottom');
        }
    },

    /**
     * Select the page based on the page item.
     *
     * @fires PageList#pageSelect
     *
     * @function
     * @public
     * @param {Element} listItem Page item.
     */
    selectPage: function(listItem) {
        this.element.getElement('li.current').removeClass('current');
        this.currentPage = listItem.getProperty('index').toInt();
        /**
         * The page is selected.
         * @event PageList#selectPage
         * @param {number} Current selected page number.
         */
        this.fireEvent('pageSelect', this.currentPage);
    },

    /**
     * Select the page by number.
     *
     * @fires PageList#pageSelect
     *
     * @function
     * @public
     * @param num Page number.
     */
    selectPageByNum: function (num) {
        this.currentPage = num;
        this.fireEvent('pageSelect', this.currentPage);
    },

    /**
     * Create the element for the specific page.
     *
     * @function
     * @protected
     * @param {string|number} title Page title.
     * @param {number} [index = 0] Page number.
     * @param {string} [image = ''] Source to the image.
     * @returns {Element}
     */
    _createPageLink : function(title, index, image){
        index = index || 0;
        image = image || '';

        var listItem = new Element('li');
        if (image) {
            new Element('img', {'src':image, 'border': 0, 'align':'absmiddle', alt:title, title:title, 'styles':{width:6, height:11}}).inject(listItem);
        } else {
            listItem.appendText(title);
        }

        listItem.setProperty('index', index);

        if (index) {
            var pageList = this;
            listItem.addEvents({
                'mouseover': function() {
                    if (!pageList.disabled) {
                        this.addClass('highlighted');
                    }
                },
                'mouseout': function() {
                    this.removeClass('highlighted');
                },
                'click': function() {
                    if (!pageList.disabled && this.getProperty('index') != pageList.currentPage.toString()) {
                        pageList.selectPage(this);
                    }
                }
            });
        }

        return listItem;
    }.protect(),

    /**
     * Clear the PageList.
     * @function
     * @protected
     */
    clear: function() {
        while (this.element.hasChildNodes()) {
            this.element.removeChild(this.element.firstChild);
        }
    }.protect()
});