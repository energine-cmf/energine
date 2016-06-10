ScriptLoader.load('ModalBox', 'Overlay');

var GoodsCompare = new Class({

	initialize:function (el) {
		this.element = $(el);

		// инициализация информера в шапке + мини-папапа информера
		this.informer();

		// обработчик по ссылке "сравнить" из блоков товаров
		$$('a[name=to_compare]').addEvent('click', function(e) {
			e.stop();
			var id = e.target.getProperty('data-goods-id');
			this.add(id);
		}.bind(this));
	},

	informer: function() {
		var url = this.element.getProperty('data-informer-url')+'?html&' + Math.floor((Math.random()*10000));
		this.element.set(
			'load',
			{
				method: 'get',
				'onFailure': this.error.bind(this),
				'onComplete': this.init.bind(this)
			});
		this.element.load(url);
	},

	init: function() {
		// если информер не пустой - инициализация обработчиков
		if (this.element.getElement('.compare_link')) {

			// показ мини-попапа сравнения по клику на кнопку информера
			this.element.getElement('.compare_link').addEvent('click', function (e) {
				e.stop();
				this.popup();
			}.bind(this));

			// показ попапа сравнения по клику на кнопку внутри мини-попапа сравнения
			this.element.getElements('.compare').addEvent('click', function (e) {
				e.stop();
				var goods_ids = e.target.getProperty('data-goods-ids');
				this.compare(goods_ids);
			}.bind(this));

			// очистка сравнения
			this.element.getElement('.clear_compare_list').addEvent('click', function (e) {
				e.stop();
				this.clear();
			}.bind(this));
		}
	},

	error: function() {
		this.element.empty();
		this.element.set('html', '');
	},

	add: function(goods_id) {
		var url = this.element.getProperty('data-add-url') + goods_id + '/?html&' + Math.floor((Math.random()*10000));
		this.element.set(
			'load',
			{
				method: 'get',
				'onFailure': this.error.bind(this),
				'onComplete': this.init.bind(this)
			});
		this.element.load(url);
	},

	remove: function(goods_id) {
		alert(1);
		var url = this.element.getProperty('data-remove-url') + goods_id + '/?html&' + Math.floor((Math.random()*10000));
		this.element.set(
			'load',
			{
				method: 'get',
				'onFailure': this.error.bind(this),
				'onComplete': this.init.bind(this)
			});
		this.element.load(url);
	},

	popup: function() {
		var el = this.element.getElement('.popup_compare');
		el.toggle();
	},

	compare: function(goods_ids) {
		var url = this.element.getProperty('data-compare-url') + goods_ids + '/?html';
		ModalBox.open({
			url: url,
			onClose: function (result) {
			}.bind(this)
		});
		// todo: init onclick for remove from compare to
		// 1. call this.remove()
		// 2. remove element from compare form by js
	},

	clear: function() {
		var url = this.element.getProperty('data-clear-url') + '?html&' + Math.floor((Math.random() * 10000));
		this.element.set(
			'load',
			{
				method: 'get',
				'onFailure': this.error.bind(this),
				'onComplete': this.init.bind(this)
			});
		this.element.load(url);
	}

});