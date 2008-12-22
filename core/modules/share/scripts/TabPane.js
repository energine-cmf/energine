var TabPane = new Class({

    getOptions: function() {
        return {
            onTabChange: Class.empty
        };
    },

    initialize: function(element, options) {
        Asset.css('tabpane.css');
        this.element = $(element).addClass('tabpane');
        this.setOptions(this.getOptions(), options);

        this.tabs = this.element.getElement('ul.tabs').addClass('clearfix').getElements('li');
        this.tabs.each(function(tab) {
            tab.setProperty('unselectable', 'on');
            var anchor = tab.getElement('a');
            var paneId = anchor.href.slice(anchor.href.lastIndexOf('#'));
            anchor.onclick = function() { this.blur(); return false; }
            var tabData = tab.getElement('span.data');
            tab.data = (tabData ? Json.evaluate(tabData.firstChild.nodeValue) : {});
            tab.pane = this.element.getElement('div'+paneId).addClass('pane').setStyle('display', 'none');
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
        tab.getElement('a').setHTML(title);
    },

    whereIs: function(element) {
        var el = $(element), pane = false;
        while (el = el.getParent()) {
            if (el.hasClass('pane') && el.tab) {
                pane = el.tab;
                break;
            }
        }
        return pane;
    }
});

TabPane.implement(new Events);
TabPane.implement(new Options);