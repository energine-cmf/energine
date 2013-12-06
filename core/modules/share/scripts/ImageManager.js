/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[ImageManager]{@link ImageManager}</li>
 * </ul>
 *
 * @requires Form
 * @requires ModalBox
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form', 'ModalBox');

/**
 * ImageManager
 *
 * @augments Form
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var ImageManager = new Class(/** @lends ImageManager# */{
    Extends:Form,

    // constructor
    initialize:function (element) {
        this.parent(element);

        /**
         * Image.
         * @type {Object}
         */
        this.image = {};

        /**
         * Defines whether the image ratio will be kept.
         * @type {boolean}
         */
        this.saveRatio = false;

        /**
         * Array of image margins.
         * @type {string[]}
         */
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

    /**
     * Event handler. Check the image ratio.
     *
     * @function
     * @public
     * @param {Object} e Event.
     */
    checkRatio:function (e) {
        var target = $(e.target).id,
            oldWidth = this.image.upl_width,
            oldHeight = this.image.upl_height,
            width = $('width').get('value').toInt(),
            height = $('height').get('value').toInt(),
            src;

        if (oldWidth != width || oldHeight != height) {
            (target == 'width')
                ? height = Math.round((oldHeight * width) / oldWidth)
                : width = Math.round((oldWidth * height) / oldHeight);

            $('width').set('value', width);
            $('height').set('value', height);
            $('filename').set('value', src = Energine.resizer + 'w'+width+'-h'+height+'/' + this.image['upl_path']);
            $('thumbnail').set('src', src);
        }
    },

    /**
     * Open image library.
     * @function
     * @public
     */
    openImageLib:function () {
        ModalBox.open({
            url: this.singlePath + 'file-library/',
            'post': JSON.encode(this.image),
            onClose: function (result) {
                if (result) {
                    this.image = result;
                    this.updateForm();
                }
                window.focus();
            }.bind(this)
        });
    },

    /**
     * Update form.
     * @function
     * @public
     */
    updateForm:function () {
        $('filename').value = this.image['upl_path'];
        $('thumbnail').src = Energine.media /*+ 'resizer/w40-h40/'*/ + this.image['upl_path'];
        $('width').value = this.image['upl_width'] || 0;
        $('height').value = this.image['upl_height'] || 0;
        $('align').set('value', this.image.align || '');

        this.imageMargins.each(function (propertyName) {
            $(propertyName).value = $(propertyName).value || this.image[propertyName] || '0';
        }, this);

        var alt = $('alt');
        if (!alt.value) {
            alt.value = this.image['upl_title'] || '';
        }
    },

    /**
     * Insert image.
     * @function
     * @public
     */
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