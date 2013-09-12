ScriptLoader.load('GridManager');

var AttachmentEditor = new Class({
    Extends: GridManager,

    initialize: function (element) {
        this.parent(element);
        this.quick_upload_path = element.get('quick_upload_path');
        this.quick_upload_pid = element.get('quick_upload_pid');
        this.quick_upload_enabled = element.get('quick_upload_enabled');
    },

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
