var isset = function(variable) {
	return ('undefined' != typeof(variable));
}
var Energine = {
	debug : false,
	base : null,
	supportContentEdit : Browser.Engine.trident || Browser.Engine.gecko || Browser.Engine.presto
}
Energine.request = {
    request : function(uri, data, onSuccess, onUserError) {
        var callbackFunction = function(response, responseText) {
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
            'method' : 'post',
            'data' : data,
            // 'noCache': true,
            'evalResponse' : false,
            'onComplete' : callbackFunction
        }).send();

    }

};
/*
 * Class: ScriptLoader Загружает указанные скрипты из директории scripts.
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
										+ ((Energine.debug) ? '?'
												+ Math.random() : ''), false);
				this.request.send(null);
                //получаем текст запрашиваемого файла                    
				if (this.request.status == 200) {
					this.loaded[filename] = [];
                    this.loaded[filename]['code'] = this.request.responseText;
				}
			}
            
            if(!this.loaded[filename]['code'])
                throw 'Invalid file code. Filename: ' + filename;
                
            //На этот момент у нас есть текст запрашиваемого файла
            //Но он может быть исполнен в одном из открытых окон а в другом - нет
                
           //this.globalEval(this.loaded[filename]['code']);
                
            if(!this.loaded[filename]['w'])
                    this.loaded[filename]['w'] = [];
            //Текст получен  - но не исполнен ни в одном окне
            if(
                !this.isLoadedInCurrentWindow(this.loaded[filename]['w'])
             ){
                //исполняем в текущем    
                this.loaded[filename]['w'].push(window.top.currentWindow);
                this.globalEval(this.loaded[filename]['code']);
            }
            
		}
	},
    isLoadedInCurrentWindow: function(arr){
        var result = false;
        for(var i = 0, l = arr.length; i < l; i++){
            if(arr[i] === window.top.currentWindow){
                result = true;
                break;
            }
        }
        return result;
    },
	globalEval : function(code) {
        var w = window.top.currentWindow;
		if (w.execScript){
			w.execScript(code, 'javascript');
            return ;
        }

//            (function(){w.eval.call(w, code)})();
			w.eval(code);
	}
};
}()

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
		return new Element('link', $merge({
							'rel' : 'stylesheet',
							'media' : 'screen',
							'type' : 'text/css',
							'href' : Energine.base + 'stylesheets/' + source
						}, properties)).inject(document.head);
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
if (typeof console == 'undefined') {
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
		error: function(){
			var args = arguments;
			var result = '';
			for (var i = 0; i < args.length; i++) {
				result += args[i];
			}
			alert(result);
		}
	}
}
