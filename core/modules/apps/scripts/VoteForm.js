/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[VoteForm]{@link VoteForm}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/Form
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form');


// TODO: This is the same as SiteForm. The difference only in the url!!! - leave as is
/**
 * VoteForm
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var VoteForm = new Class(/** @lends VoteForm# */{
    Extends: Form,

    // constructor
    initialize:function (el) {
        this.parent(el);

        var tab = this.tabPane.createNewTab(Energine.translations.get('TAB_VOTE_QUESTIONS'));
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
    loadData:function () {
        var iframe,
            url = this.singlePath + (
                (this.state !== 'add')
                    ? this.componentElement.getElementById('vote_id').get('value')
                    : ''
                ) + '/question/';

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