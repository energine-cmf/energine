ScriptLoader.load('Form');
var FileRepoForm = new Class({
    Extends:Form,
    initialize:function (el) {
        this.parent(el);
        var uploader;
        if (uploader = this.componentElement.getElementById('uploader')) {
            this.reader = new FileReader();
            uploader.addEvent('change', this.showPreview.bind(this))
        }
        if (this.thumbs = this.componentElement.getElements('img.thumb')) {
            this.thumbReader = new FileReader();
            this.componentElement.getElements('input.thumb').addEvent('change', this.showThumbPreview.bind(this));
            var altPreview;
            if (altPreview = this.componentElement.getElements('input.preview')) {
                altPreview.addEvent('change', this.showAltPreview.bind(this));
            }
        }
        if(this.componentElement.getElementById('data') && !(this.componentElement.getElementById('data').get('value')))
            this.tabPane.disableTab(1);
    },
    showAltPreview:function (evt) {
        var el = $(evt.target);
        var files = evt.target.files;
        var createTemporaryFile = this.createTemporaryFile.bind(this);

        for (var i = 0, f; f = files[i]; i++) {
            if (f.type.match('image.*')) {
                this.thumbReader.onload = (function (file) {
                    return function (e) {
                        var previewElement = $(el.getProperty('preview')), dataElement = $(el.getProperty('data'));
                        if (previewElement) previewElement.removeClass('hidden').setProperty('src', e.target.result);
                        if (dataElement) dataElement.set('value', e.target.result);

                        createTemporaryFile.attempt([e.target.result, file.name, file.type, true]);
                    }
                })(f);
                this.thumbReader.readAsDataURL(f);
            }
        }
    },
    showThumbPreview:function (evt) {
        var el = $(evt.target);
        var files = evt.target.files;
        //var binaryReader = new FileReader();

        for (var i = 0, f; f = files[i]; i++) {
            if (f.type.match('image.*')) {
                this.thumbReader.onload = (function (file) {
                    return function (e) {
                        var previewElement = $(el.getProperty('preview')), dataElement = $(el.getProperty('data'));

                        if (previewElement) previewElement.removeClass('hidden').setProperty('src', e.target.result);
                        if (dataElement) dataElement.set('value', e.target.result);
                    }
                })(f);
                this.thumbReader.readAsDataURL(f);
            }
        }


    },
    generatePreviews:function (tmpFileName) {

        if (this.thumbs)
            this.thumbs.each(function (el) {
                el.removeClass('hidden');
                el.setProperty('src', Energine.resizer + 'w' + el.getProperty('width') + '-h' + el.getProperty('height') + '/' + tmpFileName);
            });
    },
    createTemporaryFile:function (data, filename, type, isAlts) {
        data = {
            'data':data,
            'name':filename
        };
        if(isAlts){
            data = Object.append(data, {'alts':1});
        }
        data = Object.toQueryString(data);
        this.request(this.singlePath + 'temp-file/', data, function (response) {
            if (response.result && (type.match('image.*') || type.match('video.*'))) {
                if (type.match('video.*')) {
                    document.getElementById('preview').removeClass('hidden').setProperty('src', Energine.resizer + 'w0-h0/' + response.data);
                }
                this.generatePreviews(response.data)
            }
        }.bind(this)
        )
        ;
    },
    showPreview:function (evt) {
        var previewElement = document.getElementById('preview')
        previewElement.removeProperty('src').addClass('hidden');
        if (this.thumbs)this.thumbs.removeProperty('src').addClass('hidden');
        previewElement.removeClass('hidden').setProperty('src', Energine.base + 'images/loading.gif');
        var createTemporaryFile = this.createTemporaryFile.bind(this);
        var files = evt.target.files;
        var enableTab = this.tabPane.enableTab.pass(1, this.tabPane);
        for (var i = 0, f; f = files[i]; i++) {
            this.reader.onload = (function (theFile) {
                return function (e) {
                    document.getElementById('upl_name').set('value', theFile.name);
                    document.getElementById('upl_filename').set('value', theFile.name);
                    //document.getElementById('file_type').set('value', theFile.type);
                    document.getElementById('data').set('value', e.target.result);
                    document.getElementById('upl_title').set('value', theFile.name.split('.')[0]);

                    if (theFile.type.match('image.*')) {
                        previewElement.removeClass('hidden').setProperty('src', e.target.result);
                        createTemporaryFile.attempt([e.target.result, theFile.name, theFile.type]);
                        enableTab();
                    }
                    else if (theFile.type.match('video.*')) {
                        createTemporaryFile.attempt([e.target.result, theFile.name, theFile.type]);
                        enableTab();
                    }
                    else {
                        previewElement.removeClass('hidden').setProperty('src', Energine.static + 'images/icons/icon_undefined.gif');
                    }
                };
            })(f);
            this.reader.readAsDataURL(f);
        }
    },
    save:function () {
        if (!this.validator.validate()) {
            return false;
        }
        this._getOverlay().show();

        var errorFunc = function (responseText) {
            this._getOverlay().hide();
        }.bind(this);
        this.request(Energine.base + this.form.getProperty('action'), this.form.toQueryString(), this.processServerResponse.bind(this), errorFunc, errorFunc);
    }
});
