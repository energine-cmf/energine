var Carousel = new Class({
    Implements : Options,
    options : {
        // Количество видимых элементов
        visibleItems : 5,
        // Количество скроллируемых элементов
        scrollItems : 1,
        // Путь к файлу со стилями для карусели
        css : 'carousel.css',
        // Событие при котором происходит скроллинг
        event : 'click' // click
    },
    initialize : function(element, options) {
        this.setOptions($pick(options, {}));
        Asset.css(this.options.css);

        var carousel = $(element), nextButtonHandler,  previousButtonHandler;
        this.element = carousel.getElement('.viewbox');
        this.holder = this.element.getElement('ul');
        this.effectCompleted = true;

        //стек невидимых елементов
        this.deck = [];
        //перечень елементов
        var items = this.holder.getElements('li');
        var buttonsInfo = {
            previous:{
                button:carousel.getElement('.previous'),
                handler: Energine.cancelEvent
            },
            next:{
                button:carousel.getElement('.next'),
                handler: Energine.cancelEvent
            }
        };


        //На тот случай когда у нас реальных видимых елементов меньше чем заданных в конфигурации
        //то и скроллировать нам нечего
        //соответственно мы и кнопки не подключаем
        if (items.length <= this.options.visibleItems) {
            this.options.visibleItems = items.length;
            this.options.scrollItems = 0;
        }
        else {
            // Вешаем обработчики событий на кнопки с классами next и previous
            // Если элементы с такими классами существуют
            buttonsInfo.next.handler = function(event) {
                    if (this.effectCompleted) {
                        this.scrollRight();
                    }
                    event.stop();
                }.bind(this);
            buttonsInfo.previous.handler = function(event) {
                    if (this.effectCompleted) {
                        this.scrollLeft();
                    }
                    event.stop();
                }.bind(this);
        }
        $each(buttonsInfo, function(btnInfo){
            if(btnInfo.button){
                btnInfo.button.addEvent(this.options.event, btnInfo.handler).setProperty('unselectable', 'on');
            }
        }, this);
        // вызываем функцию просчета размеров с задержкой, чтобы динамически
        // загружаемый css успел обработаться
        this._init.delay(100, this, items);
    },

    scrollLeft : function() {
        //itemsToScroll - перечень елементов которые будут скроллироваться
        //включает все видимые елементы + 1 находящийся справа
        var itemsToScroll = this.holder.getElements('li').slice(0, this.options.visibleItems), el, effects = {};
        //дописываем в перечень скроллируемых елементов первый из очереди
        itemsToScroll.push(el = this.deck.shift().setStyle('left', (this.options.visibleItems + 1 )* this.width));
        //ложим в очередь первый из списка скроллируемых - он станет невидимым
        this.deck.push(this.holder.getElements('li')[0]);
        //указываем новую позицию
        itemsToScroll.each(function(el, i){effects[i] = {'left': this.width*i};}, this);
        this.holder.grab(el, 'bottom');
        //после скроллинга - удаляем елемент
        this._scrollEffect(itemsToScroll, effects, function(){
            this.deck[this.deck.length - 1].dispose();
        }.bind(this));
    },
    scrollRight : function() {
        //Передвинули елемент из стека в конец
        var itemsToScroll = this.holder.getElements('li').slice(0, this.options.visibleItems), el, effects = {};
        //дописываем в перечень скроллируемых елементов последний из очереди
        itemsToScroll.unshift(el = this.deck.pop().setStyle('left', 0));
        //ложим в начало очереди последний елмент из видимых
        this.deck.unshift(this.holder.getElements('li')[this.options.visibleItems - 1]);

        itemsToScroll.each(function(el, i){effects[i] = {'left': this.width*(i+1)};}, this);
            
        this.holder.grab(el, 'top');
        this._scrollEffect(itemsToScroll, effects, function(){this.deck[0].dispose();}.bind(this));
    },
    _init : function() {
        //поскольку функция вызывается с задержкой через delay,
        //а аргументом у нас должен выступать массив,
        //то превращаем набор аргументов в массив
        var items = $A(arguments);
        //количество скроллируемых елементов всегда равно количеству видимых + 1 тот который выезжает справа или слева
        var scrollerItemsCount = this.options.visibleItems + 1;

        // Вычисляем ширину пункта
        this.width = 110;
        //this.width = this.holder.getElement('li img').getSize().x.toInt();
        // Ширина окна скролла равна ширине все видимых елементов
        this.element.setStyle('width', this.width * this.options.visibleItems);

        // ширина контейнера равна ширине всех видимых элементов и ширина всех
        // скроллируемых елементов с обеих сторон
        // отступ контейнера для скрытия невидимых елементов и центрирования
        // посередине скроллируемой области
        this.holder.setStyles({
            'width' : this.width * scrollerItemsCount,
            'left' : 0 - this.width
        });


        this.deck = $$(items.slice(this.options.visibleItems).reverse());
        
        this.deck.dispose();
        this.deck.setStyle('left', 0);    
        // позиционируем видимые елементы находящиеся в очереди
        for (var i = 0; i < this.options.visibleItems; i++) {
            this.element.getElements('ul li')[i].setStyle('left', (i+1)*this.width)
        }
    },
    _scrollEffect : function(elements, effects, afterEffectFunction) {
        this.effectCompleted = false;
        if (!afterEffectFunction)
            new Fx.Elements(new Elements(elements), {
                'duration' : '700',
                'transition' : 'cubic:in:out',
                'onChainComplete' : function() {
                    this.effectCompleted = true;
                }.bind(this)
            }).start(effects);
        else {
            new Fx.Elements(new Elements(elements), {
                'duration' : '700',
                'transition' : 'cubic:in:out',
                'onChainComplete' : function() {
                    this.effectCompleted = true;
                }.bind(this)
            }).start(effects).chain(afterEffectFunction);
        }
    }
});
