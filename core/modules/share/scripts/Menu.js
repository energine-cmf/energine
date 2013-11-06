/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[GroupForm]{@link GroupForm}</li>
 * </ul>
 *
 * @requires share/Form
 *
 * @author
 *
 * @version 1.0.0
 */

/**
 * Menu. From MooTools it implements: Options, Events.
 *
 * @throws {string} Element for the menu is not found.
 *
 * @constructor
 * @param {Element|string} el Main element or element ID.
 * @param {Object} options Menu [options]{@link Menu#options}.
 */
var AMenu = new Class(/** @lends Menu# */{
    Implements: [Options, Events],

    /**
     * Menu options.
     * @type {Object}
     *
     * @property {string} [itemSelect = 'mouseenter'] Event name for selecting the menu item.
     * @property {string} [deactivateMenu = 'mouseleave'] Event name for deactivating the menu.
     */
    options: {
        itemSelect: 'mouseenter',
        deactivateMenu: 'mouseleave'
    },

    /**
     * Reference constructor for the submenu.
     * @type {Function}
     */
    submenuRef: null,

    // constructor
    initialize: function(el, options) {
        var self = this;

        /**
         * Root menu element.
         * @type {Element}
         */
        this.element = $(el);
        if (this.element == null) {
            throw 'Element for the menu is not found.';
        }

        this.element.addEvent(this.options.deactivateMenu, function() {
            self.deactivate();
        });

        this.setOptions(options);

        this.setSubmenuConstructor();

        /**
         * Menu items.
         * @type {Elements}
         */
        this.items = this.element.getChildren('.menu_item');
        if (this.items.length) {
            console.warn('Menu is empty.');
        }
        this.items.each(function(item) {
            item.addEvent(self.options.itemSelect, function() {
                self.selectItem(item);
            });
        });
        this.items.each(function(item) {
            if (item.hasClass('submenu')) {
                self.createSubmenu(item);
            }
        });
    },

    /**
     * Create submenu.
     *
     * @abstract
     * @function
     * @public
     * @param {Element} submenu Submenu element.
     */
    createSubmenu: function(submenu) {
        submenu.store('submenu', new this.submenuRef(submenu));
    },

    /**
     * Activate the menu.
     * @abstract
     * @function
     * @public
     */
    activate: function() {
        this.element.addClass('active');
    },

    /**
     * Deactivate the menu.
     * @abstract
     * @function
     * @public
     */
    deactivate: function() {
        this.element.removeClass('active');
        this.items.removeClass('active');
    },

    /**
     * Return true if the menu is visible, otherwise - false.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    isActive: function() {
        return this.element.hasClass('active');
    },

    /**
     * Event handler. Select the item menu.
     * @function
     * @public
     */
    selectItem: function(item) {
        this.items.removeClass('active');
        item.addClass('active');

        var submenu = item.retrieve('submenu');
        if (submenu) {
            submenu.activate();
        }
    },

    /**
     * Set the reference constructor for the submenu.
     *
     * @abstract
     * @function
     * @protected
     * @param {Function} [ref = Menu] Reference constructor.
     */
    setSubmenuConstructor: function(ref) {
        this.submenuRef = ref || Menu;
    }.protect()
});