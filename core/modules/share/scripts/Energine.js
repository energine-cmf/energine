/*
 * Загружает указанные скрипты из директории scripts.
 */

var ScriptLoader = {load: function(){}};

var isset = function (variable) {
    return ('undefined' != typeof(variable));
}

var Energine = {
    debug: false,
    base: null,
    translations: {
        'get': function (constant) {
            return (Energine.translations[constant] || null);
        },
        'set': function (constant, translation) {
            Energine.translations[constant] = translation;
        },
        'extend': function (obj){
            Object.append(Energine.translations, obj);
        }
    },
    forceJSON: false,
    supportContentEdit: Browser.ie || Browser.firefox ||
        Browser.opera || Browser.chrome
}
Energine.request = {
    request: function (uri, data, onSuccess, onUserError, onServerError, method) {
        onServerError = onServerError || function (responseText) {

        };
        method = method || 'post';
        var callbackFunction = function (response, responseText) {
            if (!response) {
                onServerError(responseText);
                return;
            }

            if (response.result) {
                onSuccess(response);
            } else {
                var msg = (typeof response.title != 'undefined')
                    ? response.title
                    : 'Произошла ошибка:' + "\n";
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
        };
        new Request.JSON({
            'url': uri + ((Energine.forceJSON) ? '?json' : ''),
            'method': method,
            'data': data,
            // 'noCache': true,
            'evalResponse': false,
            'onComplete': callbackFunction,
            'onFailure': function (e) {/*console.log(arguments)*/
            }
        }).send();

    }

};

Energine.cancelEvent = function (e) {
    e = e || window.event;
    try {
        if (e.preventDefault) {
            e.stopPropagation();
            e.preventDefault();

        } else {
            e.returnValue = false;
            e.cancelBubble = true;
        }
    }
    catch (exc) {
    }
    return false;
}

Energine.createDatePicker = function (datePickerObj, nullable) {
    var props = {
        format: 'j-m-Y',
        allowEmpty: nullable,
        inputOutputFormat: 'Y-m-d',
        useFadeInOut: false
    };
    return Energine._createDatePickerObject($(datePickerObj), props);
}

Energine.createDateTimePicker = function (datePickerObj, nullable) {
    //DateTime
    var props = {
        timePicker: true,
        format: 'j-m-Y H:i',
        inputOutputFormat: 'Y-m-d H:i',
        allowEmpty: nullable,
        useFadeInOut: false
    }

    return Energine._createDatePickerObject($(datePickerObj), props);
}

Energine._createDatePickerObject = function (datePickerObj, props) {

    if (!isset(this.datePickerDataLoaded)) {
        Asset.css('datepicker.css');
        this.datePickerDataLoaded = true;
    }
    var dp = new DatePicker(datePickerObj, $extend({
            //debug:true
        },
        props
    ));

    //dp.input.set('value', dp.visual.get('value'));
    return dp;
}

/*
 * Улучшения: - Проверка уже загруженных стилей; - Загрузка стилей из директории
 * stylesheets.
 */
Asset.loaded = {
    css: {}
};
Asset.css = function (source, properties) {
    if (!Asset.loaded.css[source]) {
        Asset.loaded.css[source] = true;
        properties = properties || {};

        var result = new Element('link');
        result.setProperties($merge({
            'rel': 'stylesheet',
            'media': 'Screen, projection',
            'type': 'text/css',
            'href': ((Energine.static) ? Energine.static : '') + 'stylesheets/' +
                source
        }, properties));
        result.inject(document.head);
        return result;
    }
    return false;
}

