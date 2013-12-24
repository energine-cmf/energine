/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[TabPane]{@link TabPane}</li>
 * </ul>
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

/**
 * Abstract tab panel.
 *
 * @constructor
 * @param {Object} [options] Set of events. This class listens 'tabChange'-event.
 */
var TabPane = new Class(/** @lends TabPane# */{
    Implements: [Options, Events],

    Static: {
        count: 1,
        assignID: function() {
            return this.count++;
        }
    },

    // constructor
    initialize: function (element, options) {
        Asset.css('tabpane.css');
        this.setOptions(options);

        /**
         * The main holder element.
         * @type {Element}
         */
        this.element = $(element);

        /**
         * Array of tabs.
         * @type {Elements}
         */
        this.tabs = this.element.getElement('ul.e-tabs').addClass('clearfix').getElements('li');

        /**
         * Current viewed tab.
         * @type {Element}
         */
        this.currentTab = this.tabs[0];

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

        this.selectTab(this.currentTab);
    },

    /**
     * Show the specific tab.
     *
     * @fires TabPane#tabChange
     *
     * @function
     * @public
     * @param {Element} tab Tab that will be viewed.
     */
    show: function (tab) {
        this.selectTab(tab);
        /**
         * Changing the tab panel.
         * @event TabPane#tabChange
         * @param {Object} Object with language ID.
         */
        this.fireEvent('tabChange', this.currentTab.data);
    },

    /**
     * Select the tab.
     *
     * @function
     * @public
     * @param {Element} tab Tab that will be selected.
     */
    selectTab: function (tab) {
        this.currentTab.removeClass('current').pane.setStyle('display', 'none');
        tab.addClass('current').pane.setStyle('display', '');
        this.currentTab = tab;

        var firstInput = this.currentTab.pane.getElement('div.field div.control input[type=text]')
            || this.currentTab.pane.getElement('div.field div.control textarea');
        if (firstInput) {
            // TODO Check if this is not DatePicker.
            firstInput.focus();
        }
    },

    /**
     * Get the all [tabs]{@link TabPane#tabs}.
     *
     * @function
     * @public
     * @returns {Elements}
     */
    getTabs: function () {
        return this.tabs;
    },

    /**
     * Get the [current viewed tab]{@link TabPane#currentTab}.
     *
     * @function
     * @public
     * @returns {Element}
     */
    getCurrentTab: function () {
        return this.currentTab;
    },

    /**
     * Set the title of tab.
     *
     * @function
     * @public
     * @param {string} title The title.
     * @param {Element} tab The tab from the [tabs]{@link TabPane#tabs}.
     */
    setTabTitle: function (title, tab) {
        tab = Array.pick([tab, this.getCurrentTab()]);
        tab.getElement('a').set('html', title);
    },

    /**
     * Create the new tab.
     *
     * @function
     * @public
     * @param {string} tabTitle The title.
     * @returns {Element} New tab.
     */
    createNewTab: function (tabTitle) {
        var tabID = 'id' + TabPane.assignID();//Math.floor(Math.random() * 101),
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

    /**
     * Find the tab.
     *
     * @function
     * @public
     * @param {Element} element
     * @returns {Element}
     */
    whereIs: function (element) {
        var el = $(element),
            pane = null;
        while (el = el.getParent()) {
            if (el.hasClass('e-pane-item') && el.tab) {
                pane = el.tab;
                break;
            }
        }
        return pane;
    },

    /**
     * Enable the tab by his index.
     *
     * @function
     * @public
     * @param {number} tabIndex The index of the tab.
     */
    enableTab: function (tabIndex) {
        if (this.tabs[tabIndex]) {
            this.tabs[tabIndex].removeClass('disabled');
        }
    },

    /**
     * Disable the tab by his index.
     *
     * @function
     * @public
     * @param {number} tabIndex The index of the tab.
     */
    disableTab: function (tabIndex) {
        if (this.tabs[tabIndex]) {
            this.tabs[tabIndex].addClass('disabled');
        }
    }
});
