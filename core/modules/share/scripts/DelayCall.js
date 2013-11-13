/**
 * @file Contain the description of the next objects:
 * <ul>
 *     <li>[DelayCall]{@link DelayCall}</li>
 * </ul>
 *
 * @author Valerii Zinchenko
 *
 * @version 1.0.0
 */

/**
 * Object of properties and functions for calling some external function in the loop with the delay defined by some URL.
 * @namespace
 */
var DelayCall = /** @lends DelayCall# */{
    /**
     * URL, where the delay is stored.
     * @type {string}
     */
    delayURL: '',

    /**
     * Defines whether the loop call of delayed function is enabled.
     * @type {boolean}
     */
    delayEnabled: true,

    /**
     * Function that will be delayed.
     * @function
     * @public
     */
    delayFn: function() {},

    /**
     * Start the loop call.
     * @function
     * @public
     */
    startLoop: function() {
        setTimeout(function(){
            this.delayFn();
            if (this.delayEnabled) {
                this.startLoop();
            }
        }.bind(this), this.getDelay())
    },

    /**
     * Get the delay.
     * @function
     * @public
     */
    getDelay: function() {
        var self = this,
            dt = 0,
            dtc = Date.now();

        new Request({
            delayURL: self.delayURL,
            async: false,
            onSuccess: function(dts) {
                dt = dts.toInt() - Date.now() + dtc;
                if (dt < 0) {
                    dt = 0;
                }
            },
            onFailure: function() {
                console.warn('Request was failed. The delayed call will be disabled!');
                self.delayEnabled = false;
            }
        }).send();

        return dt;
    }
};