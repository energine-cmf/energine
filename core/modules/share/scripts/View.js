var View = new Class({
	Implements:[Events, Options],
    metadata: null,
    data: null,
    selectedItem: null,
    options: {
    	onSelect: $empty
    },

    initialize: function(element, options) {
        this.element = $(element);
		this.setOptions(options);
    },

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
