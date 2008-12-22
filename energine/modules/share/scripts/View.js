var View = new Class({

    metadata: null,
    data: null,
    selectedItem: null,

    getOptions: function() {
        return {
            onSelect: Class.empty
        };
    },

    initialize: function(element, options) {
        this.element = $(element);
		this.setOptions(this.getOptions(), options);
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

View.implement(new Events);
View.implement(new Options);