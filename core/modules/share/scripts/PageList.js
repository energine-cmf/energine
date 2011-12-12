var PageList = new Class({
	Implements: Options,
    options:{
            onPageSelect: $empty
    },

    initialize: function(options) {
        Asset.css('pagelist.css');
        this.setOptions(options);
        this.element = new Element('ul').addClass('e-pane-toolbar e-pagelist').setProperty('unselectable', 'on');
        this.currentPage = 1;
    },

    getElement: function() {
        return this.element;
    },

    disable: function() {
        this.disabled = true;
        this.element.setOpacity(0.25);
    },

    enable: function() {
        this.disabled = false;
        this.element.setOpacity(1);
    },
    _createPageLink : function(title, index, image){
        var index = index || false;
        var image = image || false;

        var listItem = new Element('li');
            if (image) {
                new Element('img', {'src':image, 'border': 0, 'align':'absmiddle', alt:title, title:title, 'styles':{width:6, height:11}}).injectInside(listItem);
            }
            else {
                listItem.appendText(title);
            }
            listItem.setProperty('index', index);

        if (index == this.currentPage) listItem.addClass('current');
        if (index) {
            var pageList = this;
            listItem.addEvents({
                'mouseover': function() { if (!pageList.disabled && this.pageNum != pageList.currentPage) this.addClass('highlighted'); },
                'mouseout': function() { this.removeClass('highlighted'); },
                'click': function() { if (!pageList.disabled && this.pageNum != pageList.currentPage) { pageList.selectPage(this); } }
            });
        }
        return listItem;
    },
    build: function(numPages, currentPage, totalRecords) {
        this.currentPage = currentPage;
        this.clear();
        var VISIBLE_PAGES_COUNT = 2
        var page;

        var startPage = ((page = (currentPage - VISIBLE_PAGES_COUNT))<1)?1:page;
        var endPage = ((page = (currentPage + VISIBLE_PAGES_COUNT))>numPages)?numPages:page;

        if (numPages>5) {
            new Element('li').adopt(new Element('input', {
                'events':{
                    'keydown':function(event){
                        event = new Event(event);
                        if ((event.key == 'enter') && (event.target.get('value') != '')) {
                            var num = parseInt(event.target.get('value'));
                            event.target.value = '';
                            if (num >=1 && num <=numPages) {
                                this.selectPageByNum(num);
                            }
                            event.stop();
                        }
                    }.bind(this)
                },
                'type':'text'
            })).injectInside(this.element);
        }

        if (startPage > 1) {
            this._createPageLink(1, 1).injectInside(this.element)
        }

        if (startPage > 2) {
            this._createPageLink(2, 2).injectInside(this.element);
            if (startPage != 2 + 1) {
                this._createPageLink('...').injectInside(this.element)
            }
        }
        for (var i = startPage; i <= endPage; i++) {
            if ((currentPage != 1) && (currentPage == i)) {
                this._createPageLink('previous',i-1, 'images/prev_page.gif').injectInside(this.element);
            }
            this._createPageLink(i, i).injectInside(this.element);

            if ((currentPage != numPages) && (currentPage == i)) {
                this._createPageLink('next', i+1, 'images/next_page.gif').injectInside(this.element);
            }
        }

        if (endPage < (numPages - 1)) {
            if (endPage != (numPages - 2)) {
            	this._createPageLink('...').injectInside(this.element)
            }
            this._createPageLink(numPages - 1, numPages - 1).injectInside(this.element)
        }
        if (endPage < numPages) {
            this._createPageLink(numPages, numPages).injectInside(this.element)
        }
        this.element.grab(new Element('span', {'styles': {'padding-left':'20px'}, 'text': totalRecords}));
    },

    // Private methods:

    clear: function() {
        while (this.element.hasChildNodes()) {
            this.element.removeChild(this.element.firstChild);
        }
    },

    selectPage: function(listItem) {
        this.element.getElement('li.current').removeClass('current');
        listItem.addClass('current');
        this.currentPage = listItem.getProperty('index').toInt();
        this.options.onPageSelect(this.currentPage);
    },
    selectPageByNum: function (num) {
        this.currentPage = num;
        this.options.onPageSelect(this.currentPage);
    }
});