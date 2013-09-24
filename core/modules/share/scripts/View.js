/**
 * @file View.js.
 * From MooTools it implements: Events, Options.
 *
 * @author
 *
 * @version 0.9
 */

/**
 * @class View.
 *
 * @type {Class}
 *
 * @constructor
 * @param {string | Element} element Element ID in the DOM Tree or already selected element for the View object.
 * @param {Object} [options] Set of events. This class listens 'select'-event.
 */
var View = new Class(/** @lends View# */{
	Implements:[Events, Options],
    /**
     * @type {}
     */
    metadata: null,
    /**
     * @type {}
     */
    data: null,
    /**
     * @type {}
     */
    selectedItem: null,
    // constructor
    initialize: function(element, options) {
        /**
         * Holds an element from the DOM Tree
         * @type {Element}
         */
        this.element = $(element);
		this.setOptions(options);
    },
    /**
     * Sets metadata.
     * @param metadata
     */
    setMetadata: function(metadata) {
        this.metadata = metadata;
    },

    getMetadata: function() {
        return this.metadata;
    },

    setData: function(data) {
        if (!this.metadata) {
            alert('Cannot set data without specified metadata.');
            return false;
        }
        this.data = data;
    },

	selectItem: function(item) {
        if (this.selectedItem) this.selectedItem.removeClass('selected');
        if (item) {
            this.selectedItem = item;
            item.addClass('selected');
            this.fireEvent('onSelect', item);
        }
	},
	getSelectedItem: function() {
        return this.selectedItem;
    }
});
