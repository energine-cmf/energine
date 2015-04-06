/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[AMenu]{@link AMenu}</li>
 * </ul>
 *
 * @author Valerii Zinchenko
 *
 * @version 1.1.1
 */

/**
 * Menu. From MooTools it implements: Options, Events.
 *
 * @throws {string} Element for the menu is not found.
 *
 * @example <caption>Menu structure. For class names see [options]{@link AMenu#options}</caption>
 * &ltdiv class="menu"&gt
 *     &ltul class="menu_list"&gt
 *         &ltli class="menu_item active"&gt&lt/li&gt
 *         &ltli class="menu_item submenu"&gt
 *             &ltul class="menu_list"&gt
 *                 &ltli class="menu_item"&gt&lt/li&gt
 *             &lt/ul&gt
 *         &lt/li&gt
 *         &ltli class="menu_item"&gt&lt/li&gt
 *     &lt/ul&gt
 * &lt/div&gt
 *
 * @constructor
 * @param {Element|string} el Main element or element ID.
 * @param {Object} options Menu [options]{@link AMenu#options}.
 */
var AMenu = new Class(/** @lends AMenu# */{
    Implements: [Options, Events],

    /**
     * Menu options.
     * @type {Object}
     *
     * @property {string} [itemSelect = 'mouseenter'] Event name for selecting the menu item.
     * @property {string} [deactivateMenu = 'mouseleave'] Event name for deactivating the menu.
     * @property {Object} [classes] Definitions of the element class names in the menu structure. <b>This is not CSS selectors.</b>
     * @property {string} [classes.menuList = 'menu_list'] Class for the list of items in the menu.
     * @property {string} [classes.menuItem = 'menu_item'] Class for the item in the menu list.
     * @property {string} [classes.activeItem = 'active'] Class for identification of the active items in the menu.
     * @property {string} [classes.submenu = 'submenu'] Class for the submenu in the menu.
     */
    options: {
        itemSelect: 'mouseenter',
        deactivateMenu: 'mouseleave',
        classes: {
            menuList: 'menu_list',
            menuItem: 'menu_item',
            activeItem: 'active',
            submenu: 'submenu'
        }
    },

    /**
     * Reference constructor for the submenu.
     * @type {Function}
     * @memberOf AMenu#
     * @private
     */
    submenuRef: null,

    // constructor
    initialize: function(el, options) {
        /**
         * Root menu element.
         * @type {Element}
         */
        this.element = $(el) || $$(el)[0];
        if (this.element == null) {
            throw 'Element for the menu is not found.';
        }

        this.element.addEvent(this.options.deactivateMenu, this.deactivate.bind(this));

        this.setOptions(options);

        this.setSubmenuConstructor();

        var list = this.element.getChildren('.' + this.options.classes.menuList)[0];
        /**
         * Menu items.
         * @type {Elements}
         */
        this.items = list.getChildren('.' + this.options.classes.menuItem);
        if (!this.items.length) {
            console.warn('Menu is empty.');
        }

        var self = this;
        this.items.each(function(item) {
            item.addEvent(self.options.itemSelect, function() {
                self.selectItem(item);
            });
        });
        this.items.each(function(item) {
            if (item.hasClass(self.options.classes.submenu)) {
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
        submenu.store(this.options.classes.submenu, new this.submenuRef(submenu, this.options));
    },

    /**
     * Activate the menu.
     * @abstract
     * @function
     * @public
     */
    activate: function() {
        this.element.addClass(this.options.classes.activeItem);
    },

    /**
     * Deactivate the menu.
     * @abstract
     * @function
     * @public
     */
    deactivate: function() {
        this.element.removeClass(this.options.classes.activeItem);
        this.items.removeClass(this.options.classes.activeItem);
    },

    /**
     * Return true if the menu is active, otherwise - false.
     *
     * @function
     * @public
     * @returns {boolean}
     */
    isActive: function() {
        return this.element.hasClass(this.options.classes.activeItem);
    },

    /**
     * Event handler. Select the item menu.
     * @function
     * @public
     */
    selectItem: function(item) {
        this.items.removeClass(this.options.classes.activeItem);
        item.addClass(this.options.classes.activeItem);

        var submenu = item.retrieve(this.options.classes.submenu);
        if (submenu) {
            submenu.activate();
        }
    },

    /**
     * Set the reference constructor for the submenu.
     *
     * @function
     * @protected
     * @param {Function} [ref = AMenu] Reference constructor.
     */
    setSubmenuConstructor: function(ref) {
        this.submenuRef = ref || AMenu;
    }.protect()
});
