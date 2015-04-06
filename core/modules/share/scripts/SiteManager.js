/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[SiteManager]{@link SiteManager}</li>
 * </ul>
 *
 * @requires GridManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');

/**
 * Site manager.
 *
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var SiteManager = new Class(/** @lends SiteManager# */{
    Extends:GridManager,

    /**
     * Reset action.
     * @function
     * @public
     */
    reset: function() {
        if (confirm(Energine.translations.get('MSG_CONFIRM_TEMPLATES_RESET'))) {
            Energine.request(
                    this.singlePath + 'reset/' +
                            this.grid.getSelectedRecordKey() +
                            '/reset-templates/',
                    null,
                    function(response) {
                        if (response.result) {
                            alert(Energine.translations.get('MSG_TEMPLATES_RESET'));
                        }
                    }
            );
        }
    },
    siteProps: function() {
        ModalBox.open({ url: this.singlePath +
            this.grid.getSelectedRecordKey() + '/properties/' });
    },
    /**
     * Go action.
     * @function
     * @public
     */
    go: function () {
        window.top.location.href = this.singlePath + 'goto/' + this.grid.getSelectedRecordKey() + '/';
    }
});
