/*
 * Загружает указанные скрипты из директории scripts.
 */

var ScriptLoader = function() {
    window.top.currentWindow = window;

    return window.top.ScriptLoader || {
        request : null,
        loaded : {},
        load : function() {
            var filename;
            for (var i = 0, len = arguments.length; i < len; i++) {
                filename = arguments[i];
                //Проверяем загружен ли файл
                if (!this.loaded[filename]) {
                    //Если файл не загружен
                    if (!this.request) {
                        //И нет XMLHTTRequest - создаем
                        this.request = window.XMLHttpRequest
                                ? new XMLHttpRequest
                                : (Browser.Engine.trident
                                ? new ActiveXObject('Microsoft.XMLHTTP')
                                : null);
                    }
                    if (!this.request)
                        throw 'Ajax request is not created';

                    this.request
                            .open('GET', Energine.base
                            + 'scripts/'
                            + filename
                            + '.js'
                            + ((Energine.debug) ? '?'
                            + Math.random() : ''), false);
                    this.request.send(null);
                    //получаем текст запрашиваемого файла
                    if (this.request.status == 200) {
                        this.loaded[filename] = [];
                        this.loaded[filename]['code'] =
                                this.request.responseText;
                    }
                }

                if (!this.loaded[filename] || !this.loaded[filename]['code'])
                    throw 'Invalid file code. Filename: ' + filename;

                //На этот момент у нас есть текст запрашиваемого файла
                //Но он может быть исполнен в одном из открытых окон а в другом - нет
                this.globalEval(this.loaded[filename]['code']);
            }
        },
        isLoadedInCurrentWindow: function(arr) {
            var result = false;
            for (var i = 0, l = arr.length; i < l; i++) {
                if (arr[i] === window.top.currentWindow) {
                    result = true;
                    break;
                }
            }
            return result;
        },
        globalEval : function(code) {
            var w = window.top.currentWindow;
            if (w.execScript) {
                w.execScript(code, 'javascript');
                return;
            }

            //            (function(){w.eval.call(w, code)})();
            w.eval(code);
        }
    };
}();


var isset = function(variable) {
    return ('undefined' != typeof(variable));
}
var Energine = {
    debug : false,
    base : null,
    translations: new Hash(),
    supportContentEdit : Browser.Engine.trident || Browser.Engine.gecko ||
            Browser.Engine.presto
}
Energine.request = {
    request : function(uri, data, onSuccess, onUserError, onServerError, method) {
        onServerError = onServerError || function(responseText){

        };
        method = method || 'post';
        var callbackFunction = function(response, responseText) {
            if(!response){
                onServerError(responseText);
                return;
            }

            if (response.result) {
                onSuccess(response);
            } else {
                var msg = (typeof response.title != 'undefined')
                        ? response.title
                        : 'Произошла ошибка:' + "\n";
                response.errors.each(function(error) {
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
            'url' : uri,
            'method' : method,
            'data' : data,
            // 'noCache': true,
            'evalResponse' : false,
            'onComplete' : callbackFunction,
            'onFailure': function(e){console.log(arguments)}
        }).send();

    }

};
//TODO - некрасиво это, с глобальной переменной
var currentImage = false;

Energine.thumbnail = {
    showImage: function(event) {
        Energine.cancelEvent(event);
        var event = new Event(event || window.event);
        var obj = $(event.target);
        if (obj.get('tag') == 'a') {
            obj = obj.getElement('img');
        }

        var createImage = function() {
            currentImage = new EnlargeImage(obj, {duration: 800});
        }.bind(this);
        if (currentImage) {
            currentImage.zoomOut(createImage);
        }
        else {
            createImage();
        }

        return false;
    }
}
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
    catch(exc) {
    }
    return false;
}

Energine.createDatePicker = function(datePickerObj, nullable) {
    var props = {
        format:'j-m-Y',
        allowEmpty: nullable,
        inputOutputFormat: 'Y-m-d'

    };
    return Energine._createDatePickerObject($(datePickerObj), props);
}

Energine.createDateTimePicker = function(datePickerObj, nullable) {
    //DateTime
    var props = {
        timePicker: true,
        format: 'j-m-Y H:i',
        inputOutputFormat: 'Y-m-d H:i',
        allowEmpty: nullable
    }

    return Energine._createDatePickerObject($(datePickerObj), props);
}

Energine._createDatePickerObject = function(datePickerObj, props) {

    if (!isset(this.datePickerDataLoaded)) {
        Asset.css('datepicker.css');
        ScriptLoader.load('datepicker');
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
    css : {}
};
Asset.css = function(source, properties) {
    if (!Asset.loaded.css[source]) {
        Asset.loaded.css[source] = true;
        properties = properties || {};

        var result = new Element('link');
        result.setProperties($merge({
            'rel' : 'stylesheet',
            'media' : 'Screen, projection',
            'type' : 'text/css',
            'href' : ((Energine.static) ? Energine.static : '') + 'stylesheets/' +
                    source
            }, properties));
        result.inject(document.head);
        return result;
    }
    return false;
}

var showhideField = function(obj, fieldName, fieldLanguage) {
    var fieldLanguage = fieldLanguage || '';
    var obj = $(obj);
    var currentStatus = Number(!Boolean(Number(obj.getProperty('is_hidden'))));
    obj.innerHTML = obj.getProperty('message' + currentStatus);
    obj.setProperty('is_hidden', currentStatus);
    $('control_' + fieldLanguage + '_' + fieldName).setStyle('display',
            ((currentStatus) ? 'none' : 'block'));
    return false;
}

// Для IE создаем console.log
/*if (typeof console == 'undefined') {
    var console = {
        _dump : function(d, l) {
            if (l == null)
                l = 1;
            var s = '';
            if (typeof(d) == "object") {
                s += typeof(d) + "{\n";
                for (var k in d) {
                    for (var i = 0; i < l; i++)
                        s += "  ";
                    var tmp = '...recursion...';
                    if (l < 2) {
                        try {
                            tmp = console._dump(d[k], l + 1);
                        } catch (e) {
                        }

                    }
                    s += k + ": " + tmp;

                }
                for (var i = 0; i < l - 1; i++)
                    s += "  ";
                s += "}\n"
            } else {
                s += "" + d + "\n";
            }
            return s;
        },
        log : function() {
            var args = arguments;
            var result = '';
            for (var i = 0; i < args.length; i++) {
                result += console._dump(args[i]);
            }
            alert(result);
        },
        error: function() {
            var args = arguments;
            var result = '';
            for (var i = 0; i < args.length; i++) {
                result += console._dump(args[i]);
            }
            alert(result);
        }
    }
}*/
