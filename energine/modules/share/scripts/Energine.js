/*
 * Class: ScriptLoader
 *     Загружает указанные скрипты из директории scripts.
 */
var ScriptLoader = {

    request: null,

    loaded: {},

    load: function() {
        for (var i = 0, len = arguments.length; i < len; i++) {
            var filename = arguments[i];
            if (!this.loaded[filename]) {
                if (!this.request) {
                    this.request = window.XMLHttpRequest ? new XMLHttpRequest : (window.ie ? new ActiveXObject('Microsoft.XMLHTTP') : null);
                }
                if (!this.request) return false;
                this.loaded[filename] = true;
                this.request.open('GET', $E('base', document.head).getProperty('href')+'scripts/'+filename, false);
                this.request.send(null);
                if (this.request.status == 200) {
                    this.globalEval(this.request.responseText);
                }
            }
        }
    },

    globalEval: function(code) {
        if (window.execScript) window.execScript(code, 'javascript');
        else window.eval(code);
    }
}

// Словарь для хранения языкозависимых констант и их переводов.
var Dictionary = {};

/*
 * Улучшения:
 *   - Проверка уже загруженных стилей;
 *   - Загрузка стилей из директории stylesheets.
 */
Asset.loaded = { css: {} };
Asset.css = function(source, properties) {
    if (!Asset.loaded.css[source]) {
        Asset.loaded.css[source] = true;
        return new Element('link', $merge({
			'rel': 'stylesheet', 'media': 'screen', 'type': 'text/css', 'href': $E('base', document.head).getProperty('href') + 'stylesheets/'+source
		}, properties)).inject(document.head);
    }
    return false;
}


Object.toQueryString = function(source){
	var queryString = [];
	for (var property in source) {
	    if ($type(source[property]) == 'array') {
	        for (var i = 0; i < source[property].length; i++) {
	            queryString.push(encodeURIComponent(property)+'='+encodeURIComponent(source[property][i]));
	        }
	    }
	    else {
	        queryString.push(encodeURIComponent(property)+'='+encodeURIComponent(source[property]));
	    }
	}
	return queryString.join('&');
};

Element.implement({

    // Для единообразия.
    removeProperty: function(property){
        this.removeAttribute(property);
        return this;
    }
});
    //Объект предназначен для последующей имплементации его в нужных местах
    //отправляет запрос на сервер и обрабатывает результат
    var Request = {
    request : function(uri, data, onSuccess, onUserError) {
        new Ajax(uri, {
            method: 'post',
            postBody: data,
            onComplete: function(responseText) {
                try {
                    var response = Json.evaluate(responseText);
                }
                catch (e) {
                    var response = {
                        result: false,
                        title: 'Ошибка',
                        errors: [{ message: 'Произошла ошибка. Пожалуйста, обновите страницу.' }]
                    };
                }

                if (response.result) {
                    onSuccess(response);
                }
                else {
                    var msg = (typeof response.title != 'undefined')?response.title:'Произошла ошибка:' + "\n";
                    response.errors.each(function(error) {
                        if (typeof error.field != 'undefined') {
                            msg += error.field + " :\t";
                        }
                        
                        if (typeof error.field != 'undefined') {
                            msg += error.message + "\n";
                        }
                        else{
                        	msg += error + "\n";
                        }
                    });
                    alert(msg);
					if(onUserError){
						onUserError(response);
					}
                }
            }
        }).request();
    }
    }

    //Предназначен для последующей имплементации
    //Содержит метод setLabel использующийся для привязки кнопки выбора разделов
    var Label = {
        setLabel: function(result) {
            var id = name = segment = segmentObject = '';
            if (typeof (result)!= 'undefined') {
                if (result) {
                    id = result.smap_id;
                    name = result.smap_name;
					segment = result.smap_segment;
                }
                $(this.obj.getProperty('hidden_field')).value = id;
                $(this.obj.getProperty('span_field')).innerHTML = name;
				if(segmentObject = $('smap_pid_segment')) segmentObject.innerHTML = segment;
            }
        }
    }

	//Разбиваем строку URL на путь и имя файла
	var getPathInfo = function(str) {
		var re = new RegExp("(.*)/([A-Za-z0-9\-_\. ]+\.[A-Za-z0-9\-_]+)$","gi");
		var arr = re.exec(str);
		
		return {'path':RegExp.$1, 'filename':RegExp.$2};
	}

	var showhideField = function (obj, fieldName, fieldLanguage) {
		var fieldLanguage = fieldLanguage || '';
		var obj = $(obj); 
		var currentStatus =  Number(!Boolean(Number(obj.getProperty('is_hidden')))); 
		obj.innerHTML = obj.getProperty('message'+currentStatus); 
		obj.setProperty('is_hidden',currentStatus); 
		$('control_'+fieldLanguage+'_'+fieldName).setStyle('display', ((currentStatus)?'none':'block')); 
		return false;
	}

	//Для IE создаем console.log
	if (typeof console == 'undefined') {
		var console = {
			_dump: function (d,l) {
				if (l == null) l = 1;
				var s = '';
				if (typeof(d) == "object") {
					s += typeof(d) + "{\n";
					for (var k in d) {
						for (var i=0; i<l; i++) s += "  ";
						var tmp = '...recursion...';
						if (l<2) {
							try {
								tmp = console._dump(d[k],l+1);	
							}
							catch (e) {
							}
							
						}
						s += k+": " + tmp;	
						
					}
					for (var i=0; i<l-1; i++) s += "  ";
					s += "}\n"
				} 
				else {
					s += "" + d + "\n";
				}
				return s;
			},
			log: function () {
				var args = arguments;
				var result = '';
				for (var i=0; i<args.length; i++) {
					result += console._dump(args[i]);
				}
				alert(result);
			}
		}
	}
