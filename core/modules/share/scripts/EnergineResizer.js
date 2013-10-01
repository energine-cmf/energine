/**
 * @file Image resizer for [Energine CMS]{@link http://energine.org/}
 *
 * @author Valerii Zinchenko
 *
 * @version 1.0
 * @since 1.0
 *
 * @requires Energine CMS
 */

/**
 * @class This object is used for resizing images on the web-page using Energine CMS.
 *
 * @type {Object}
 * @property {string} resizerURL Contain the URL of resizer (like <tt>http://www.site.ua/resizer/</tt>).
 */
var EnergineResizer = {
    resizerURL: '',

    /**
     * Resize a requested image. The attribute <tt>src</tt> of the img-tag will be build as follow:
     * <tt>this.resizerURL + r + 'w' + w + '-h' + h + '/' + src</tt>
     *
     * @function
     * @public
     * @param {HTMLImageElement} img Image, that will be resized.
     * @param {string} src Source of the original image.
     * @param {number} w Width of the new image.
     * @param {number} h Height of the new image.
     * @param {string} [r = ''] Special attribute. For example, if additional shrinking must be applied, in order to not cross requested width and height, <tt>r</tt> must be 'r'.
     *
     * @example
     * <pre>
     *     Energine.resizerURL = 'http://www.site.ua/resizer/';
     *     Energine.resize($$('img')[0], 'images/img01.png', 100, 50);
     *     $$('img')[0].getProperty('src') == 'http://www.site.ua/resizer/w100-h50/images/img01.png'
     * </pre>
     */
    resize: function(img, src, w, h, r) {
        if (r === undefined)
            r = '';
        img.setAttribute('src', this.resizerURL + r + 'w' + w + '-h' + h + '/' + src);
    }
};