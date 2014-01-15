/**
 * @file Contain the description of the next objects:
 * <ul>
 *     <li>[Energine]{@link Energine}</li>
 *     <li>[ScriptLoader]{@link ScriptLoader}</li>
 * </ul>
 *
 * @requires GridManager
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.1.0
 */

/**
 * Загружает указанные скрипты из директории scripts.
 */
var ScriptLoader = {load: function () {}};

/**
 * @namespace
 */
var Energine = /** @lends Energine */{
    /**
     * Debug flag.
     * @type {boolean}
     */
    debug: false,

    //todo: Append to all URLs ending 'URL'
    //---------
    /**
     * Base URL.
     * @type {string}
     */
    base: '',

    /**
     * Static URL.
     * @type {string}
     */
    'static' : '',

    /**
     * Resizer URL.
     * @type {string}
     */
    resizer: '',

    /**
     * Media URL.
     * @type {string}
     */
    media: '',

    /**
     * Root URL.
     * @type {string}
     */
    root: '',
    //---------

    /**
     * Language ID.
     * @type {string}
     */
    lang: '',

    /**
     * Translations.
     * @type {Object}
     *
     * @property {Function} [get] Get the translation.
     * @param {string} get.constant Translation ID.
     * @property {Function} [set] Set the translation.
     * @param {string} set.constant Translation ID.
     * @param {Object} set.translation Translations.
     * @property {Function} [extend] Extend the translation.
     * @param {Object} obj New translation.
     */
    translations: {
        'get': function (constant) {
            return (Energine.translations[constant] || null);
        },
        'set': function (constant, translation) {
            Energine.translations[constant] = translation;
        },
        'extend': function (obj) {
            Object.append(Energine.translations, obj);
        }
    },

    /**
     * Force ths using of JSON.
     * @type {boolean}
     */
    forceJSON: false,

    /**
     * Support content editing.
     * @type {boolean}
     */
    supportContentEdit: true,


    /**
     * Send the request.
     *
     * @function
     * @static
     * @param {string} uri URI
     * @param {string} data Request.
     * @param {function} onSuccess Callback function that will be called by successful response.
     * @param {function} [onUserError] Callback function that will be called by user error.
     * @param {function} [onServerError] Callback function that will be called by server error.
     * @param {string} [method = 'post'] Request method: 'get', 'post'.
     */
    request: function (uri, data, onSuccess, onUserError, onServerError, method) {
        onServerError = onServerError || function (responseText) {};
        method = method || 'post';

        new Request.JSON({
            'url': uri + ((Energine.forceJSON) ? '?json' : ''),
            'method': method,
            'data': data,
            // 'noCache': true,
            'evalResponse': false,
            'onComplete': function (response, responseText) {
                if (!response) {
                    onServerError(responseText);
                    return;
                }

                if (response.result) {
                    onSuccess(response);
                } else {
                    var msg = (typeof response.title != 'undefined')
                        ? response.title
                        : 'Произошла ошибка:\n';
                    response.errors.each(function (error) {
                        if (typeof error.field != 'undefined') {
                            msg += error.field + " :\t";
                        }
                        if (typeof error.message != 'undefined') {
                            msg += error.message + "\n";
                        } else {
                            msg += error + "\n";
                        }
                    });

                    alert(msg);

                    if (onUserError) {
                        onUserError(response);
                    }
                }
            },
            'onFailure': function (e) {
                console.error(arguments)
            }
        }).send();
    },

    /**
     * Cancel event.
     *
     * @deprecated Use MooTools implementation: e.stop()
     *
     * @function
     * @static
     * @param {Object} e Event.
     */
    cancelEvent: function (e) {
        e = e || window.event;
        try {
            if (e.preventDefault) {
                e.stopPropagation();
                e.preventDefault();
            } else {
                e.returnValue = false;
                e.cancelBubble = true;
            }
        } catch (err) {
            console.warn(err)
        }
    },

    /**
     * Create the DatePicker object without time selecting.
     *
     * @function
     * @static
     * @param {Element} datePickerObj Element for DatePicker.
     * @param {boolean} [nullable] Defines whether the an empty field for the date is allowed.
     * @returns {DatePicker}
     */
    createDatePicker: function (datePickerObj, nullable) {
        var props = {
            format: '%Y-%m-%d',
            allowEmpty: nullable,
            useFadeInOut: false
        };
        return Energine._createDatePickerObject($(datePickerObj), props);
    },

    /**
     * Create the DatePicker object with time selecting.
     *
     * @function
     * @static
     * @param {Element} datePickerObj Element for DatePicker.
     * @param {boolean} [nullable] Defines whether the an empty field for the date is allowed.
     * @returns {DatePicker}
     */
    createDateTimePicker: function (datePickerObj, nullable) {
        //DateTime
        var props = {
            timePicker: true,
            format: '%Y-%m-%d %H:%M',
            allowEmpty: nullable,
            useFadeInOut: false
        };

        return Energine._createDatePickerObject($(datePickerObj), props);
    },

    //fixme: bug
    /**
     * Create the DatePicker object.
     *
     * @function
     * @static
     * @param {Element} datePickerObj Element for DatePicker.
     * @param {Object} props Properties for the DatePicker.
     * @returns {DatePicker}
     */
    _createDatePickerObject: function (datePickerObj, props) {
        Asset.css('datepicker.css');

        var dp = new DatePicker(datePickerObj, Object.append({
                //debug:true
            },
            props
        ));

        try {
            if (!props.allowEmpty && dp.inputs[0].get('value') == '') {
                var currentDate = new Date(),
                    dateString = [
                        currentDate.getFullYear(),
                        currentDate.getMonth() + 1,
                        currentDate.getDate()
                    ].join('-');

                if (props.timePicker) {
                    dateString += ' ' + [currentDate.getHours(), currentDate.getMinutes()].join(':');
                }
                dp.inputs[0].set('value', dateString);
            }
        } catch (e) {
            if (Energine.debug && Browser.chrome && instanceOf(e, TypeError)) {
                console.warn(e.stack);
            } else {
                console.warn(e);
            }
        }

        return dp;
    },

    /**
     * Resize an requested image. The attribute <tt>src</tt> of the img-tag will be build as follow:
     * <tt>Energine.resizer + r + 'w' + w + '-h' + h + '/' + src</tt>
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
     * Energine.resizer = 'http://www.site.ua/resizer/';
     * Energine.resize($$('img')[0], 'images/img01.png', 100, 50);
     * $$('img')[0].getProperty('src') == 'http://www.site.ua/resizer/w100-h50/images/img01.png'
     */
    resize: function(img, src, w, h, r) {
        if (r === undefined)
            r = '';
        img.setAttribute('src', Energine.resizer + r + 'w' + w + '-h' + h + '/' + src);
    }
};

var safeConsoleError = function(e){
    if (window['console'] && console['error']) {
        if (Browser.chrome && instanceOf(e, TypeError) && Energine.debug) {
            console.error(e.stack);
        } else {
            console.error(e);
        }
    }
};

/**
 * Compatibility fix.
 * @type {Function}
 *
 * @deprecated Use Energine.request instead.
 */
Energine.request.request = Energine.request;