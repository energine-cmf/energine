var ActiveList = new Class({
    Implements: Events,
    initialize: function(container) {
        this.container = $(container);
        this.container.addClass('alist');
        this.container.tabIndex = 1;
        this.container.setStyle('-moz-user-select', 'none');

        Asset.css('acpl.css');

        this.active = false;
        this.selected = false;
        this.load();
    },
    load: function() {
        if (!this.container.getChildren('ul').length) {
            this.ul = new Element('ul');
            this.container.grab(this.ul);
        }
        else {
            this.ul = this.container.getChildren('ul')[0];
        }
        this.items = this.ul.getChildren();
    },
    activate: function() {
        this.items = this.ul.getChildren();
        this.active = true;
        //this.container.focus();
        this.selectItem();
        this.container.addEvent('keypress', this.keyPressed.bind(this));
        this.items.addEvent('mouseover', function(e) {
            this.selectItem(e.target.getAllPrevious().length);
        }.bind(this));
        this.items.addEvent('click', function(e) {
            this.fireEvent('choose', this.items[this.selected]);
        }.bind(this));
        //Вроде как нормально и без этого
        //срабатывает unselectItem вызываемый из selectItem

        /*this.items.addEvent('mouseout', function(e){
         this.unselectItem(e.target.getAllPrevious().length);
         }.bind(this));*/
    },
    keyPressed: function(e) {
        if (e.key == 'up' || e.key == 'down') {
            var itemNum, l = this.items.length;
            if (e.key == 'up') {
                itemNum = (this.selected !== false) ? (this.selected - 1 < 0) ? l - 1 : this.selected - 1 : l - 1;
            }
            else if (e.key == 'down') {
                itemNum = (this.selected !== false) ? (this.selected + 1 > l - 1) ? 0 : this.selected + 1 : 0;
            }
            this.selectItem(itemNum);
            e.preventDefault();
        }
        else if (e.key == 'enter') {
            this.fireEvent('choose', this.items[this.selected]);
            e.stopPropagation();
        }

    },
    selectItem: function(itemNum) {
        if (!itemNum) itemNum = 0;
        if (this.selected !== false)
            this.unselectItem(this.selected);

        if (this.items[itemNum]) {
            this.items[itemNum].addClass('selected');
            this.selected = itemNum;
            var
                //Позиция елемента по Y относительно контейнера
                //Если она отрицательная значит скролл сверху
                posY = this.items[itemNum].getPosition(this.container).y,
                //Высота елемента
                height = this.items[itemNum].getSize().y,
                //Высота контейнера
                cHeight = this.container.getSize().y;
            //Если скролл сверху не позволяет видеть елемент
            //Или высота елемента и его позиция больше высоты контейнера(скролл снизу)
            if (posY < 0 || posY + height > cHeight) {
                //скроллим
                this.items[itemNum].scrollIntoView();
            }
        }
        else this.selected = false;

    },
    unselectItem: function(itemNum) {
        if (this.items[itemNum]) this.items[itemNum].removeClass('selected');
    }

});

var DropBoxList = new Class({
    Extends: ActiveList,
    initialize: function(input) {
        this.input = $(input);
        this.parent(new Element('div', {'class': 'acpl_variants', styles:{'position':'absolute', 'min-width': this.input.getSize().x}}));
        this.hide();
    },
    isOpen: function() {
        return !(this.container.getStyle('display') === 'none');
    },
    get: function() {
        return this.container;
    },
    show: function() {
        this.container.removeClass('hidden');
        this.activate();
    },
    hide: function() {
        this.container.addClass('hidden');
    },
    empty: function() {
        this.ul.empty();
    },
    create: function(data) {
        var result = new Element('li').set('text', data.value).store('key', data.key);

        return result;
    },
    add: function(li) {
        this.ul.grab(li);
    }
});
var AcplField = new Class({
    Implements: [Options,Events],
    options: {
        startFrom: 1,
        separator: ','
    },
    initialize: function(element, options) {
        this.element = $(element);
        this.container  = new Element('div', {'class': '', 'styles': {'position': 'relative'}}).wraps(this.element);

        this.list = new DropBoxList(this.element);
        this.list.addEvent('choose', this.select.bind(this));
        this.list.get().inject(this.element, 'after');


        Asset.css('acpl.css');
        this.url = this.element.getProperty('url');
        this.setOptions(options);
        this.value = '';

        //this.element.addEvent('focus', this._focus.bind(this));
        //this.element.addEvent('blur', this._blur.bind(this));

        //Вешаем на keyup для того чтобы у нас было реальное value поля
        this.element.addEvent('keyup', this._enter.bind(this));
    },
    /*_blur: function(){
     this.focused = false;
     },
     _focus: function(){
     this.focused = true;
     },*/
    _enter: function(e) {
        var key = e.key, val = this.element.value;

        if (((key == 'up') || (key == 'down') || (key == 'enter'))) {
            this.list.keyPressed.call(this.list, e);
        }
        else if ((val > this.options.startFrom) && (this.value != val)) {
            this.requestValues(this.value = val);
        }
        //Это ввели какое то значение
        /*if(key.length == 1){
         val +=  key;
         }
         else if(key == 'backspace'){

         }*/


    },
    _prepareData: function(result) {
        this.setValues(result.data);
    },
    /**
     *
     * @param str
     * return Object | false
     */
    requestValues: function(str) {
        new Request.JSON({url: this.url, onSuccess: this._prepareData.bind(this)}).send({
            method: 'post',
            data: 'value=' + str
        });
    },
    /**
     *
     * @param data array
     */
    setValues: function(data) {
        this.list.empty();

        if (data.length) {
            data.each(function(row) {
                this.list.add(this.list.create(row));
            }, this);

        }
        this.list.show();
    },
    select: function(li){
        this.element.set('value', li.get('text'));
        this.list.hide();
    }

});

window.addEvent('domready', function() {
    //new ActiveList(document.getElement('.alist')).activate();
    document.getElements('input.acpl').each(function(el) {
        new AcplField(el);
    })

});