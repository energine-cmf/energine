/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[AttachmentEditor]{@link AttachmentEditor}</li>
 * </ul>
 *
 * @requires GridManager
 *
 * @author Andrii Karpov
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');

/**
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var AttachmentEditor = new Class(/** @lends AttachmentEditor# */{
    Extends: GridManager,

    // constructor
    initialize: function (element) {
        this.parent(element);

        /**
         * Quick upload path.
         * @type {string}
         */
        this.quick_upload_path = element.get('quick_upload_path');

        /**
         * Quick upload PID (Parent repository ID).
         * @type {string}
         */
        this.quick_upload_pid = element.get('quick_upload_pid');

        /**
         * Defines whether quick upload is enabled.
         * @type {string}
         */
        this.quick_upload_enabled = element.get('quick_upload_enabled');
    },

    /**
     * Overridden parent [callback function by successful server response]{@link GridManager#processServerResponse} method.
     *
     * @function
     * @public
     * @param {Object} response Response from the server.
     */
    processServerResponse: function(response) {
        this.parent(response);

        if (control = this.toolbar.getControlById('quickupload')) {
            if (this.quick_upload_enabled) {
                control.enable();
            } else {
                control.disable();
            }
        }
    },

    /**
     * Open the quick upload window.
     * @function
     * @public
     */
    quickupload: function () {
        var overlay = this.overlay;
        ModalBox.open({
            url: this.singlePath + 'file-library/' + this.quick_upload_pid + '/add/',
            onClose: function (response) {
                if (response && response.result) {
                    if (response.data) {
                    this.overlay.show();
                    new Request.JSON({
                        'url': this.singlePath + 'savequickupload/',
                        'method': 'post',
                        'data': {
                            'json': 1,
                            'componentAction': 'add',
                            'upl_id': response.data
                        },
                        'evalResponse': true,
                        'onComplete': function(data) {
                            overlay.hide();
                                if (data) {
                                    if (data.result) {
                                        this.loadPage(1);
                                    }
                                }
                            }.bind(this),
                            'onFailure': function (e) {
                                overlay.hide();
                            }
                        }).send();
                    }
                }
            }.bind(this)
        });
    }

});
