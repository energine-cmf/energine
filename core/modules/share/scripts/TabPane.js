var TabPane = new Class({
	options: {
		onTabChange: $empty
	},
	Implements:[Options, Events],

    initialize: function(element, options) {
        Asset.css('tabpane.css');
        this.setOptions(options);
        this.element = $(element);

        this.tabs = this.element.getElement('ul.e-tabs').addClass('clearfix').getElements('li');
        this.tabs.each(function(tab) {
            tab.setProperty('unselectable', 'on');
            var anchor = tab.getElement('a');
            var paneId = anchor.getProperty('href').slice(anchor.getProperty('href').lastIndexOf('#'));            
            anchor.addEvent('click', function(event) {event = new Event(event || window.event); event.preventDefault(); tab.blur();});
            var tabData = tab.getElement('span.data');
            tab.data = (tabData ? JSON.decode(tabData.firstChild.nodeValue) : {});
            tab.pane = this.element.getElement('div'+paneId).addClass('e-pane-item').setStyle('display', 'none');
            tab.pane.tab = tab;

            var tabpane = this;
            tab.addEvents({
                'mouseover': function() { if (this != tabpane.currentTab) this.addClass('highlighted'); },
                'mouseout': function() { this.removeClass('highlighted'); },
                'click': function() { if (this != tabpane.currentTab) tabpane.show(this); }
            });
        }, this);

        this.show(this.currentTab = this.tabs[0]);
    },

    show: function(tab) {
        this.currentTab.removeClass('current').pane.setStyle('display', 'none');
        tab.addClass('current').pane.setStyle('display', '');
        this.currentTab = tab;
        this.fireEvent('onTabChange', this.currentTab.data);
    },

    getTabs: function() {
        return this.tabs;
    },

    getCurrentTab: function() {
        return this.currentTab;
    },

    setTabTitle: function(title, tab) {
        tab = $pick(tab, this.getCurrentTab());
        tab.getElement('a').set('html', title);
    },

    whereIs: function(element) {
        var el = $(element), pane = false;
        while (el = el.getParent()) {
            if (el.hasClass('e-pane-item') && el.tab) {
                pane = el.tab;
                break;
            }
        }
        return pane;
    }
});
