/**
 * Active list. From MooTools it implements: Events.
 *
 * @constructor
 * @param {Element|string} container Active list container.
 */
var ActiveList = new Class(/** @lends ActiveList# */{
    Implements: Events,

    /**
     * Defines whether the active list is active.
     * @type {boolean}
     */
    active: false,

    /**
     * Defines whether the active list is selected.
     * @type {number}
     */
    selected: -1,

    /**
     * The first ul-element in the container.
     * @type {Element}
     */
    ul: null,

    /**
     * Items from the [ul-element]{@link ActiveList#ul}.
     * @type {Elements}
     */
    items: null,

    // constructor
    initialize: function(container) {
        Asset.css('acpl.css');

        /**
         * Active list container.
         * @type {Element}
         */
        this.container = $(container);
        this.container.addClass('alist');
        this.container.tabIndex = 1;
        this.container.setStyle('-moz-user-select', 'none');

        if (!this.container.getChildren('ul').length) {
            this.ul = new Element('ul');
            this.container.grab(this.ul);
        } else {
            this.ul = this.container.getChildren('ul')[0];
        }
        this.items = this.ul.getChildren();
    },

    /**
     * Activate list.
     *
     * @fires ActiveList#choose
     */
    activate: function() {
        this.items = this.ul.getChildren();
        this.active = true;
        //this.container.focus();

        this.selectItem();

        this.container.addEvent('keypress', this.keyPressed.bind(this));

        this.items.addEvent('mouseover', function(e) {
            this.selectItem(e.target.getAllPrevious().length);
        }.bind(this));
        this.items.addEvent('click', function(e) {
            /**
             * The item from [items]{@link ActiveList#items} is selected.
             * @event ActiveList#choose
             * @param {Element} item Selected item.
             */
            this.fireEvent('choose', this.items[this.selected]);
        }.bind(this));
    },

    /**
     * Event handler of the pressed key.
     *
     * @fires ActiveList#choose
     *
     * @param {Object} e Event.
     */
    keyPressed: function(e) {
        switch (e.key) {
            case 'up':
                this.selectItem(this.selected - 1);
                e.preventDefault();
                break;

            case 'down':
                this.selectItem(this.selected + 1);
                e.preventDefault();
                break;

            case 'enter':
                this.fireEvent('choose', this.items[this.selected]);
                e.stopPropagation();
                break;
        }
    },

    /**
     * Select the item by ID.
     *
     * @param {number} [id = 0] Item ID.
     */
    selectItem: function(id) {
        if (!id) {
            id = 0;
        }

        if (id < 0) {
            id = this.items.length + id;
        } else if (id >= this.items.length) {
            id -= this.items.length;
        }

        this.unselectItem(this.selected);

        this.items[id].addClass('selected');
        this.selected = id;

        var body = $(document.body);
        if (body.scrollHeight > body.getSize().y) {
            this.input.scrollIntoView();
        }
    },

    /**
     * Deselect selected item.
     *
     * @param {number} id Item ID.
     */
    unselectItem: function(id) {
        if (this.items[id]) {
            this.items[id].removeClass('selected');
        }
    }
});

/**
 * Drop box list.
 *
 * @augments ActiveList
 *
 * @constructor
 * @param {Element|string} input Drop box element.
 */
var DropBoxList = new Class(/** @lends DropBoxList# */{
    Extends: ActiveList,

    // constructor
    initialize: function(input) {
        /**
         * Drop box list element.
         * @type {Element}
         */
        this.input = $(input);
        Asset.css('acpl.css');

        this.parent(new Element('div', {
            'class': 'acpl_variants'
        }));

        this.input.addEvent('blur', this.hide.bind(this));

        this.hide();
    },

    /**
     * Return true if the drop box list is opened, otherwise - false.
     *
     * @returns {boolean}
     */
    isOpen: function() {
        return (this.container.getStyle('display') !== 'none');
    },

    /**
     * Get the drop box list [container]{@link DropBoxList#container}.
     *
     * @returns {Element}
     */
    get: function() {
        return this.container;
    },

    /**
     * Show the drop box list.
     */
    show: function() {
        this.container.removeClass('hidden');
        var size = this.container.getComputedSize();
        this.container.setStyle('width', this.input.getSize().x - size['border-left-width'] - size['border-right-width']);
        this.activate();
    },

    /**
     * Hide the drop box list.
     */
    hide: function() {
        this.container.addClass('hidden');
    },

    /**
     * Empty the list.
     */
    empty: function() {
        this.ul.empty();
    },

    /**
     * Create an item for the list.
     * @param {{value: string, key:string}} data Object withe the properties for the item.
     * @returns {Element}
     */
    create: function(data) {
        return new Element('li').set('text', data.value).store('key', data.key);
    },

    /**
     * Add the new item to the list.
     *
     * @param {HTMLLIElement} li New item for the list.
     */
    add: function(li) {
        this.ul.grab(li);
    }
});
