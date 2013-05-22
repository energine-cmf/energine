ScriptLoader.load('Form', 'FileAPI/FileAPI.min');

var FileRepoForm = new Class({
    Extends:Form,
    initialize:function (el) {
        this.parent(el);

        FileAPI.staticPath = Energine.base + 'scripts/FileAPI/';
        FileAPI.debug = false;

        var uploader;
        if (uploader = this.componentElement.getElementById('uploader')) {
            uploader.addEvent('change', this.showPreview.bind(this))
        }
        if (this.thumbs = this.componentElement.getElements('img.thumb')) {
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
       this.showThumbPreview(evt);
    },
    showThumbPreview:function (evt) {
        var el = $(evt.target);
        var files = FileAPI.getFiles(evt);

        for (var i = 0, f; f = files[i]; i++) {
            if (f.type.match('image.*')) {
                this.xhrFileUpload(el.getProperty('id'), files, function (response) {
                    var previewElement = $(el.getProperty('preview')),
                        dataElement = $(el.getProperty('data'));
                    if (previewElement) previewElement.removeClass('hidden').setProperty('src', Energine.base + 'resizer/' + 'w0-h0/' + response.tmp_name);
                    if (dataElement) dataElement.set('value', response.tmp_name);
                });
            }
        }

        //FileAPI.reset(evt.currentTarget);
    },
    generatePreviews:function (tmpFileName) {

        if (this.thumbs)
            this.thumbs.each(function (el) {
                el.removeClass('hidden');
                el.setProperty('src', Energine.resizer + 'w' + el.getProperty('width') + '-h' + el.getProperty('height') + '/' + tmpFileName);
            });
    },
    xhrFileUpload: function(field_name, files, response_callback) {

        var f = {};
        f[field_name] = files;

        return FileAPI.upload({
            url: this.singlePath + 'upload-temp/?json',
            data: {
                'key': field_name,
                'pid': $('upl_pid').get('value')
            },
            files: f,
            prepare: function (file, options){
                options.data[FileAPI.uid()] = 1;
            },
            beforeupload: function (){
                // FileAPI.log('beforeupload:', arguments);
            },

            upload: function (){
                // FileAPI.log('upload:', arguments);
            },

            fileupload: function (file, xhr){
                // FileAPI.log('fileupload:', file.name);
            },

            fileprogress: function (evt, file){
                // FileAPI.log('fileprogress:', file.name, '--', evt.loaded/evt.total*100);
            },

            filecomplete: function (err, xhr, file){
                // FileAPI.log('filecomplete:', err, file.name);

                if( !err ){
                    try {
                        var result = FileAPI.parseJSON(xhr.responseText);
                        // FileAPI.log(result);
                        if (result && !result.error) {
                            response_callback(result);
                        }
                    } catch (er){
                        //FileAPI.log('PARSE ERROR:', er.message);
                    }
                }
            },

            progress: function (evt, file){
                //FileAPI.log('progress:', evt.loaded/evt.total*100, '('+file.name+')');
            },

            complete: function (err, xhr){
                //FileAPI.log('complete:', err, xhr);
            }
        });
    },
    showPreview:function (evt) {
        var previewElement = document.getElementById('preview')
        previewElement.removeProperty('src').addClass('hidden');
        if (this.thumbs)this.thumbs.removeProperty('src').addClass('hidden');
        previewElement.removeClass('hidden').setProperty('src', Energine.base + 'images/loading.gif');
        var files = FileAPI.getFiles(evt);
        var enableTab = this.tabPane.enableTab.pass(1, this.tabPane);
        var generatePreviews = this.generatePreviews.bind(this);
        for (var i = 0, f; f = files[i]; i++) {

            this.xhrFileUpload('uploader', files, function (response) {

                document.getElementById('upl_name').set('value', response.name);
                document.getElementById('upl_filename').set('value', response.name);
                //document.getElementById('file_type').set('value', theFile.type);
                document.getElementById('data').set('value', response.tmp_name);
                document.getElementById('upl_title').set('value', response.name.split('.')[0]);

                if (response.type.match('image.*')) {
                    previewElement.removeProperty('src').addClass('hidden');
                    previewElement.removeClass('hidden').setProperty('src', Energine.base + 'resizer/' + 'w0-h0/' + response.tmp_name);
                    generatePreviews(response.tmp_name);
                    enableTab();
                }
                else if (response.type.match('video.*')) {
                    previewElement.removeProperty('src').addClass('hidden');
                    previewElement.removeClass('hidden').setProperty('src', Energine.base + 'resizer/' + 'w0-h0/' + response.tmp_name);
                    generatePreviews(response.tmp_name);
                    enableTab();
                }
                else {
                    previewElement.removeClass('hidden').setProperty('src', Energine.static + 'images/icons/icon_undefined.gif');
                }
            });
        }
        //FileAPI.reset(evt.currentTarget);
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
