/**
 *
 * @type {Class}
 *
 * @param {Object} [options] Set of events. This class listens 'tabChange'-event.
 */
var TabPane = new Class({
//    options: {
//        onTabChange: function(){}
//    },
    Implements: [Options, Events],
    initialize: function (element, options) {
        Asset.css('tabpane.css');
        this.setOptions(options);
        this.element = $(element);

        this.tabs = this.element.getElement('ul.e-tabs').addClass('clearfix').getElements('li');
        this.tabs.each(function (tab) {
            tab.setProperty('unselectable', 'on');
            var anchor = tab.getElement('a');
            var paneId = anchor.getProperty('href').slice(anchor.getProperty('href').lastIndexOf('#'));
            anchor.addEvent('click', function (event) {
                event.preventDefault();
                tab.blur();
            });
            var tabData = tab.getElement('span.data');
            tab.data = (tabData ? JSON.decode(tabData.firstChild.nodeValue) : {});
            tab.pane = this.element.getElement('div' + paneId).addClass('e-pane-item').setStyle('display', 'none');
            tab.pane.tab = tab;

            var tabpane = this;
            tab.addEvents({
                'mouseover': function () {
                    if (this != tabpane.currentTab) this.addClass('highlighted');
                },
                'mouseout': function () {
                    this.removeClass('highlighted');
                },
                'click': function () {
                    if ((this != tabpane.currentTab) && !this.hasClass('disabled')) tabpane.show(this);
                }
            });
        }, this);

        this.show(this.currentTab = this.tabs[0]);
    },
    show: function (tab) {
        this.currentTab.removeClass('current').pane.setStyle('display', 'none');
        tab.addClass('current').pane.setStyle('display', '');
        this.currentTab = tab;
        this.fireEvent('onTabChange', this.currentTab.data);

        var firstInput;
        if (
            (firstInput = this.currentTab.pane.getElement('div.field div.control input[type=text]'))
            ||
            (firstInput = this.currentTab.pane.getElement('div.field div.control textarea'))
            ) {
            firstInput.focus();
        }
    },
    getTabs: function () {
        return this.tabs;
    },
    getCurrentTab: function () {
        return this.currentTab;
    },
    setTabTitle: function (title, tab) {
        tab = Array.pick([tab, this.getCurrentTab()]);
        tab.getElement('a').set('html', title);
    },
    createNewTab: function (tabTitle) {
        var tabID = 'id' + Math.floor(Math.random() * 101),
            titleElement = new Element('a', {'href': '#' + tabID, 'html': tabTitle}),
            tabPane = new Element('div', {'id': tabID, 'class': 'e-pane-item', 'styles': {'display': 'none'}}).inject(this.element.getElement('.e-pane-content')),
            tabElement = new Element('li', {'unselectable': 'on'}).grab(titleElement);
        this.element.getElement('ul.e-tabs').grab(tabElement);
        this.tabs.push(tabElement);
        titleElement.addEvent('click', function (event) {
            event.preventDefault();
            tabElement.blur();
        });
        tabElement.pane = tabPane;
        tabElement.pane.tab = tabElement;

        var tabpane = this;
        tabElement.addEvents({
            'mouseover': function () {
                if (this != tabpane.currentTab) this.addClass('highlighted');
            },
            'mouseout': function () {
                this.removeClass('highlighted');
            },
            'click': function () {
                if (this != tabpane.currentTab) tabpane.show(this);
            }
        });
        return tabElement;
    },
    whereIs: function (element) {
        var el = $(element), pane = false;
        while (el = el.getParent()) {
            if (el.hasClass('e-pane-item') && el.tab) {
                pane = el.tab;
                break;
            }
        }
        return pane;
    },
    enableTab: function (tabIndex) {
        if (this.tabs[tabIndex]) {
            this.tabs[tabIndex].removeClass('disabled');
        }
    },
    disableTab: function (tabIndex) {
        var tab;
        if (tab = this.tabs[tabIndex]) {
            tab.addClass('disabled');
        }
    }
});
