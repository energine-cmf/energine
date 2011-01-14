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

                this.carousel = $(element);
                this.element = this.carousel.getElement('.viewbox');
                this.holder = this.element.getElement('ul');
                this.effectCompleted = true;

                // Вешаем обработчики событий на кнопки с классами next и previous
                // Если элементы с такими классами существуют
                if (this.nextButton = this.carousel.getElement('.next')) {
                        this.nextButton.addEvent(this.options.event, function(event) {
                                                if (this.effectCompleted) {
                                                        this.scrollLeft();
                                                }
                                                event.stop();
                                        }.bind(this));
            this.nextButton.setProperty('unselectable', 'on');
                }
                if (this.previousButton = this.carousel.getElement('.previous')) {
                        this.previousButton.addEvent(this.options.event, function(event) {
                                                if (this.effectCompleted) {
                                                        this.scrollRight();
                                                }
                                                event.stop();
                                        }.bind(this));
            this.previousButton.setProperty('unselectable', 'on');
                }

                // вызываем функцию просчета размеров с задержкой, чтобы динамически
                // загружаемый css успел обработаться
                this._init.delay(100, this);
        },

        scrollLeft : function() {
                // передвигаем невидимые елементы находящиеся слева(в начале стека) - в
                // конец стека и позиционируем их на нуле
                new Elements(this.holder.getElements('li').slice(
                                                0,
                                                (this.options.scrollItems == 1)
                                                                ? 1
                                                                : this.options.scrollItems)).inject(
                                this.holder, 'bottom').setStyle('left', 0);

                var items = [], item, effects = {};
                // видимые елементы
                for (var i = 0; i < (this.options.scrollItems
                                + this.options.visibleItems); i++) {
                        items.push(this.holder.getElements('li')[i]);
                        effects[i] = {
                                'left' : (i - this.options.scrollItems + 1) * this.width
                        };
                }
                var startIndex = this.options.visibleItems + this.options.scrollItems;
                this.holder.getElements('li').slice(startIndex,
                                startIndex + this.options.scrollItems).each(
                                function(element, key) {
                                        element.setStyle('left', (startIndex + key) * this.width)
                                }.bind(this));
                this._scrollEffect(items, effects);
        },
        scrollRight : function() {
                this.holder.getElements('li').splice(0 - this.options.scrollItems,
                                this.options.scrollItems).reverse().each(this._positionLeft
                                .bind(this));

                var items = [], effects = {}, index = 0;

                for (var i = (this.options.scrollItems); i <= (this.options.scrollItems
                                * 2 + this.options.visibleItems - 1); i++) {
                        items.push(this.holder.getElements('li')[i]);
                        effects[index] = {
                                'left' : this.width * (index + 1)
                        };

                        index++;
                }

                this._scrollEffect(items, effects, function() {
                                        if (this.options.scrollItems != 1)
                                                new Elements(this.holder.getElements('li').slice(
                                                                                this.options.scrollItems
                                                                                                + this.options.visibleItems,
                                                                                this.options.scrollItems
                                                                                                + this.options.visibleItems
                                                                                                + this.options.scrollItems * 2))
                                                                .setStyle('left', 0);

                                }.bind(this));
        },
        _init : function() {
                var items = this.holder.getElements('li');
        var scrollerItems;
        //На тот случай когда у нас реальных видимых елементов меньше чем заданных в конфигурации
        //то и скроллировать нам нечего
        if(items.length <= this.options.visibleItems) {
            this.options.visibleItems = items.length;
            this.options.scrollItems = 0;
        }
        scrollerItems = ((scrollerItems = (this.options.scrollItems * 2 + this.options.visibleItems))> items.length)? items.length:scrollerItems;

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
                        'width' : this.width * scrollerItems,
                        'left' : 0 - this.width
                });

                // последние scrollItems елементов списка перемещаем в начало
                // для сохранения порядка следования
                // позиционируем их на соответствующих местах
                // не на нуле
                items.slice(-this.options.scrollItems).reverse()
                                .each(this._positionLeft.bind(this));

                // позиционируем видимые елементы находящиеся в очереди
                for (var i = this.options.scrollItems; i < scrollerItems; i++) {
                        this.element.getElements('ul li')[i].setStyle('left', (i
                                                        - this.options.scrollItems + 1)
                                                        * this.width)
                }
        },
        _positionLeft : function(element, key) {
                element.inject(this.holder, 'top').setStyle('left',
                                0 - this.width * key);
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
