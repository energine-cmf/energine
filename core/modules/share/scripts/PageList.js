/**
 *
 * @type {Class}
 *
 * @param {Object} [options] Set of events. This class listens 'pageSelect'-event.
 */
var PageList = new Class({
	Implements: Options,
//    options:{
//            onPageSelect: function(){}
//    },

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
        this.element.setStyle('opacity', 0.25);
    },

    enable: function() {
        this.disabled = false;
        this.element.setStyle('opacity', 1);
    },
    _createPageLink : function(title, index, image){
        var index = index || false;
        var image = image || false;

        var listItem = new Element('li');
            if (image) {
                new Element('img', {'src':image, 'border': 0, 'align':'absmiddle', alt:title, title:title, 'styles':{width:6, height:11}}).inject(listItem);
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
            })).inject(this.element);
        }

        if (startPage > 1) {
            this._createPageLink(1, 1).inject(this.element)
        }

        if (startPage > 2) {
            this._createPageLink(2, 2).inject(this.element);
            if (startPage != 2 + 1) {
                this._createPageLink('...').inject(this.element)
            }
        }
        for (var i = startPage; i <= endPage; i++) {
            if ((currentPage != 1) && (currentPage == i)) {
                this._createPageLink('previous',i-1, 'images/prev_page.gif').inject(this.element);
            }
            this._createPageLink(i, i).inject(this.element);

            if ((currentPage != numPages) && (currentPage == i)) {
                this._createPageLink('next', i+1, 'images/next_page.gif').inject(this.element);
            }
        }

        if (endPage < (numPages - 1)) {
            if (endPage != (numPages - 2)) {
            	this._createPageLink('...').inject(this.element)
            }
            this._createPageLink(numPages - 1, numPages - 1).inject(this.element)
        }
        if (endPage < numPages) {
            this._createPageLink(numPages, numPages).inject(this.element)
        }

        if(numPages && totalRecords)
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