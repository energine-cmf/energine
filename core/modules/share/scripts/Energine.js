var isset = function(variable) {
	return ('undefined' != typeof(variable));
}
var Energine = {
	debug : false,
	base : null,
	supportContentEdit : Browser.Engine.trident || Browser.Engine.gecko || Browser.Engine.presto
}

/*
 * Class: ScriptLoader Загружает указанные скрипты из директории scripts.
 */
var ScriptLoader = {

	request : null,

	loaded : {},

	load : function() {
		for (var i = 0, len = arguments.length; i < len; i++) {
			var filename = arguments[i];
			if (!this.loaded[filename]) {
				if (!this.request) {
					this.request = window.XMLHttpRequest
							? new XMLHttpRequest
							: (Browser.Engine.trident
									? new ActiveXObject('Microsoft.XMLHTTP')
									: null);
				}

				if (!this.request)
					return false;
				this.loaded[filename] = true;

				this.request
						.open('GET', Energine.base
										+ 'scripts/'
										+ filename
										+ ((Energine.debug) ? '?'
												+ Math.random() : ''), false);
				this.request.send(null);

				if (this.request.status == 200) {
					this.globalEval(this.request.responseText);
				}
			}
		}

	},

	globalEval : function(code) {
		if (window.execScript)
			window.execScript(code, 'javascript');
		else
			window.eval(code);
	}
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
		return new Element('link', $merge({
							'rel' : 'stylesheet',
							'media' : 'screen',
							'type' : 'text/css',
							'href' : Energine.base + 'stylesheets/' + source
						}, properties)).inject(document.head);
	}
	return false;
}

/*
 * Object.toQueryString = function(source){ var queryString = []; for (var
 * property in source) { if ($type(source[property]) == 'array') { for (var i =
 * 0; i < source[property].length; i++) {
 * queryString.push(encodeURIComponent(property)+'='+encodeURIComponent(source[property][i])); } }
 * else {
 * queryString.push(encodeURIComponent(property)+'='+encodeURIComponent(source[property])); } }
 * return queryString.join('&'); };
 *
 * Element.implement({ // Для единообразия. removeProperty: function(property){
 * this.removeAttribute(property); return this; } });
 */
// Объект предназначен для последующей имплементации его в нужных местах
// отправляет запрос на сервер и обрабатывает результат
/*
 * var ERequest = { request : function(uri, data, onSuccess, onUserError) { var
 * callbackFunction = function(responseText) { try { var response =
 * JSON.decode(responseText); } catch (e) { var response = { result : false,
 * title : 'Ошибка', errors : [{ message : 'Произошла ошибка. Пожалуйста,
 * обновите страницу.' }] }; }
 *
 * if (response.result) { onSuccess(response); } else { var msg = (typeof
 * response.title != 'undefined') ? response.title : 'Произошла ошибка:' + "\n";
 * response.errors.each(function(error) { if (typeof error.field != 'undefined') {
 * msg += error.field + " :\t"; } if (typeof error.message != 'undefined') { msg +=
 * error.message + "\n"; } else { msg += error + "\n"; } }); alert(msg); if
 * (onUserError) { onUserError(response); } } };
 *
 * new Request({ 'url' : uri, 'method' : 'post', 'data' : data, //'noCache':
 * true, 'evalResponse': false, 'onComplete' : callbackFunction }).send();
 *  } }
 */
var ERequest = {
	request : function(uri, data, onSuccess, onUserError) {
		var callbackFunction = function(response, responseText) {
			/*try {
				var response = JSON.decode(responseText);
			} catch (e) {
				var response = {
					result : false,
					title : 'Ошибка',
					errors : [{
						message : 'Произошла ошибка. Пожалуйста, обновите страницу.'
					}]
				};
			}*/
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

// Предназначен для последующей имплементации
// Содержит метод setLabel использующийся для привязки кнопки выбора разделов
var Label = {
	setLabel : function(result) {
		var id = name = segment = segmentObject = '';
		if (typeof(result) != 'undefined') {
			if (result) {
				id = result.smap_id;
				name = result.smap_name;
				segment = result.smap_segment;
			}
			$(this.obj.getProperty('hidden_field')).value = id;
			$(this.obj.getProperty('span_field')).innerHTML = name;
			if (segmentObject = $('smap_pid_segment'))
				segmentObject.innerHTML = segment;
		}
	}
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
