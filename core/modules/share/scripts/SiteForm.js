/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[SiteForm]{@link SiteForm}</li>
 * </ul>
 *
 * @requires Form
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form');

/**
 * SiteForm
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var SiteForm = new Class(/** @lends SiteForm# */{
    Extends: Form,

    // constructor
    initialize: function(el) {
        this.parent(el);
        var tab = this.tabPane.createNewTab(Energine.translations.get('TAB_DOMAINS'));
        tab.pane.grab(this.loadData());
        tab.pane.setStyle('padding', '0px');
    },

    /**
     * Load data.
     *
     * @function
     * @public
     * @returns {Element} iframe
     */
    loadData: function() {
        var url = this.singlePath
            + ((this.state !== 'add')
                    ? this.componentElement.getElementById('site_id').get('value')
                    : '')
            + '/domains/';

        return new Element('iframe')
            .setProperties({
                src: url,
                frameBorder: '0',
                scrolling: 'no'
            })
            .setStyles({
                width:'99%',
                height:'99%'
            });
    }
});
