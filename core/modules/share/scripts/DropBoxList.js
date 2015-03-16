/**
 * Drop box list.
 *
 * @augments ActiveList
 *
 * @constructor
 * @param {Element|string} input Drop box element.
 */
var DropBoxList = new Class(/** @lends DropBoxList# */{
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
    initialize: function (input) {
        /**
         * Drop box list element.
         * @type {Element}
         */
        this.input = $(input);
        Asset.css('acpl.css');
        Asset.css('acpl.css');

        /**
         * Active list container.
         * @type {Element}
         */
        this.container = new Element('div', {
            'class': 'acpl_variants'
        });
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
        this.input.addEvent('blur', function () {
            this.hide.delay(100, this)
        }.bind(this)/**/);
        this.container.addEvent('keypress', this.keyPressed.bind(this));
        this.active = true;
        this.selectItem();
        this.hide();
    },
    /**
     *
     * @param data
     */
    update: function (data, string) {

        this.empty();
        if (data && data.length) {
            data.each(function (row) {
                this.add(row, string);
            }, this);
        }
        this.active = true;
        this.items = this.ul.getChildren();
        this.selectItem();

    },

    /**
     * Event handler of the pressed key.
     *
     * @fires ActiveList#choose
     *
     * @param {Object} e Event.
     */
    keyPressed: function (e) {
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
    selectItem: function (id) {
        if (!id) {
            id = 0;
        }

        if (id < 0) {
            id = this.items.length + id;
        } else if (id >= this.items.length) {
            id -= this.items.length;
        }

        this.unselectItem(this.selected);
        if (this.items[id])
            this.items[id].addClass('selected');

        this.selected = id;

        var body = $(document.body);
        if (body.scrollHeight > body.getSize().y) {
            this.input.scrollIntoView();
        }

        if(this.items[id])
            new Fx.Scroll(this.container).toElementEdge(this.items[id]);
    },

    /**
     * Deselect selected item.
     *
     * @param {number} id Item ID.
     */
    unselectItem: function (id) {
        if (this.items[id]) {
            this.items[id].removeClass('selected');
        }
    },
    /**
     * Empty the list.
     */
    empty: function () {
        this.items.removeEvents('click');
        this.items.removeEvents('mouseover');
        this.ul.empty();

    },

    /**
     * Return true if the drop box list is opened, otherwise - false.
     *
     * @returns {boolean}
     */
    isOpen: function () {
        return (this.container.getStyle('display') !== 'none');
    },

    /**
     * Get the drop box list [container]{@link DropBoxList#container}.
     *
     * @returns {Element}
     */
    get: function () {
        return this.container;
    },

    /**
     * Show the drop box list.
     */
    show: function () {
        this.container.removeClass('hidden');
        var size = this.container.getComputedSize();
        this.container.setStyle('width', this.input.getSize().x - size['border-left-width'] - size['border-right-width']);

    },

    /**
     * Hide the drop box list.
     */
    hide: function () {
        this.container.addClass('hidden');
    },
    highlight: function (data, search) {
        if (!search) return data;

        return data.replace(new RegExp("(" + search + ")", 'gi'), "<b>$1</b>");
    },

    /**
     * Add the new item to the list.
     *
     * @param {HTMLLIElement} li New item for the list.
     */
    add: function (data, string) {
        if (data.key && data.value) {
            var value = this.highlight(data.value, string);
            var item;
            this.ul.grab(item = new Element('li').set('html', value).store('key', data.key));
            item.addEvent('click', function (e) {
                /**
                 * The item from [items]{@link ActiveList#items} is selected.
                 * @event ActiveList#choose
                 * @param {Element} item Selected item.
                 */
                this.fireEvent('choose', this.items[this.selected]);
            }.bind(this));
            item.addEvent('mouseover', function (e) {
                this.selectItem(e.target.getAllPrevious().length);
            }.bind(this));


        }

    }
});
