ScriptLoader.load('Form', 'ModalBox');

var ImageManager = new Class({
    Extends:Form,
    initialize:function (objID) {
        this.parent(objID);
        this.image = {};
        this.saveRatio = false;
        this.imageMargins = ['margin-left', 'margin-right', 'margin-top', 'margin-bottom'];
        $('filename').disabled = true;
        var imageData = ModalBox.getExtraData();
        if (imageData != null) {
            this.image = imageData;
            this.updateForm();
        }
        $('width').addEvent('change', this.checkRatio.bind(this));
        $('height').addEvent('change', this.checkRatio.bind(this));
    },
    checkRatio:function (e) {
        var
            target = $(e.target).id,
            oldWidth = this.image.upl_width,
            oldHeight = this.image.upl_height,
            width = $('width').get('value').toInt(),
            height = $('height').get('value').toInt(),
            src;

        if ((oldWidth != width) || (oldHeight != height)) {
            if (target == 'width') {
                height = Math.round((oldHeight * width) / oldWidth);
            }
            else {
                width = Math.round((oldWidth * height) / oldHeight);
            }
            $('width').set('value', width);
            $('height').set('value', height);
            $('filename').set('value', src = Energine.resizer + 'w'+width+'-h'+height+'/' + this.image['upl_path']);
            $('thumbnail').set('src', src);
        }

    },
    openImageLib:function () {
        ModalBox.open({
            url:this.singlePath + 'file-library/',
            'post': JSON.encode(this.image),
            onClose:function (result) {
                if (result) {
                    this.image = result;
                    this.updateForm();
                }
                window.focus();
            }.bind(this)
        });
    },

    updateForm:function () {
        $('filename').value = this.image['upl_path'];
        $('thumbnail').src = Energine.media /*+ 'resizer/w40-h40/'*/ + this.image['upl_path'];
        $('width').value = this.image['upl_width'] || 0;
        $('height').value = this.image['upl_height'] || 0;
        $('align').set('value', this.image.align || '');
        this.imageMargins.each(function (propertyName) {
            $(propertyName).value = $(propertyName).value || this.image[propertyName] || '0';

        }, this);
        $('alt').value = $('alt').value || this.image['upl_title'] || '';
    },

    insertImage:function () {
        if ($('filename').value) {
            this.image.filename = $('filename').value;
            this.image.width = parseInt($('width').value) || '';
            this.image.height = parseInt($('height').value) || '';
            this.image.align = $('align').value || '';
            this.imageMargins.each(function (propertyName) {
                this.image[propertyName] = parseInt($(propertyName).value) || 0;
            }, this);
            this.image.alt = $('alt').value;
            this.image.thumbnail = $('thumbnail').src;
            //this.image.insertThumbnail = $('insThumbnail').checked;
            ModalBox.setReturnValue(this.image)
        }
        this.close();
    }
});