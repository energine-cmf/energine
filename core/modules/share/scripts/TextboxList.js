/*
  Moogets - TextboxList 0.2
  - MooTools version required: 1.2
  - MooTools components required: Element.Event, Element.Style and dependencies.
  
  Credits:
  - Idea: Facebook + Apple Mail
  - Caret position method: Diego Perini <http://javascript.nwbox.com/cursor_position/cursor.js>
  
  Changelog:
  - 0.1: initial release
  - 0.2: code cleanup, small blur/focus fixes
*/

/* Copyright: Guillermo Rauch <http://devthought.com/> - Distributed under MIT - Keep this message! */

Element.implement({
  
  getCaretPosition: function() {
    if (this.createTextRange) {
      var r = document.selection.createRange().duplicate();
    	r.moveEnd('character', this.value.length);
    	if (r.text === '') return this.value.length;
    	return this.value.lastIndexOf(r.text);
    } else return this.selectionStart;
  }
  
});

var ResizableTextbox = new Class({
  
  Implements: Options,
  
  options: {
    min: 5,
    max: 500,
    step: 7
  },
  
  initialize: function(element, options) {
    var that = this;
    this.setOptions(options);
    this.el = $(element);
    this.width = this.el.offsetWidth;
    this.el.addEvents({
      'keydown': function() {
        this.store('rt-value', this.get('value').length);
      },
      'keyup': function() {
        var newsize = that.options.step * this.get('value').length;
        if(newsize <= that.options.min) newsize = that.width;
        if(! (this.get('value').length == this.retrieve('rt-value') || newsize <= that.options.min || newsize >= that.options.max))
          this.setStyle('width', newsize);
      }
    });
  }
  
});

var TextboxList = new Class({
  
  Implements: [Events, Options],

  options: {/*
    onFocus: function(){},
    onBlur: function(){},
    onInputFocus: function(){},
    onInputBlur: function(){},
    onBoxFocus: function(){},
    onBoxBlur: function(){},
    onBoxDispose: function(){},*/
    resizable: {},
    className: 'bit',
    separator: '###',
    extrainputs: true,
    startinput: true,
    hideempty: true
  },
  
  initialize: function(element, options) {
    this.setOptions(options);
    this.element = $(element).setStyle('display', 'none');    
    this.bits = {};
    this.events = {};
    this.count = 0;
    this.current = false;
    this.maininput = this.createInput({'class': 'maininput'});
    this.holder = new Element('ul', {
      'class': 'holder', 
      'events': {
        'click': function(e) { 
          e.stop();
          if(this.maininput != this.current) this.focus(this.maininput); 
        }.bind(this)
      }
    }).inject(this.element, 'before').adopt(this.maininput);
    this.makeResizable(this.maininput);
    this.setEvents();
  },
  
  setEvents: function() {
    document.addEvent(Browser.ie ? 'keydown' : 'keypress', function(e) {
      if(! this.current) return;
      if(this.current.retrieve('type') == 'box' && e.key == 'backspace') e.stop();
    }.bind(this));      
         
    document.addEvents({
      'keyup': function(e) { 
        e.stop();
        if(! this.current) return;
        switch(e.key){
          case 'left': return this.move('left');
          case 'right': return this.move('right');
          case 'backspace': return this.moveDispose();
        }
      }.bind(this),
      'click': function() { this.fireEvent('onBlur').blur(); }.bind(this)
    });
  },
  
  update: function() {
    this.element.set('value', Object.values(this.bits).join(this.options.separator));
    return this;
  },
  
  add: function(text, html) {
    var id = this.options.className + '-' + this.count++;
    var el = this.createBox(Array.pick([html, text]), {'id': id}).inject(this.current || this.maininput, 'before');
    el.addEvent('click', function(e) {
      e.stop();
      this.focus(el);
    }.bind(this));
    this.bits[id] = text;
    if(this.options.extrainputs && (this.options.startinput || el.getPrevious())) this.addSmallInput(el, 'before');
    return el;
  },
  
  addSmallInput: function(el, where) {
    var input = this.createInput({'class': 'smallinput'}).inject(el, where);
    input.store('small', true);
    this.makeResizable(input);
    if(this.options.hideempty) input.setStyle('display', 'none');
    return input;
  },
  
  dispose: function(el) {
    delete this.bits(el.id);
    if(el.getPrevious().retrieve('small')) el.getPrevious().destroy();
    if(this.current == el) this.focus(el.getNext());
    if(el.retrieve('type') == 'box') this.fireEvent('onBoxDispose', el);
    el.destroy();    
    return this;
  },
  
  focus: function(el, nofocus) {
    if(! this.current) this.fireEvent('onFocus', el);
    else if(this.current == el) return this;
    this.blur();
    el.addClass(this.options.className + '-' + el.retrieve('type') + '-focus');
    if(el.retrieve('small')) el.setStyle('display', 'block');
    if(el.retrieve('type') == 'input') {
      this.fireEvent('onInputFocus', el);      
      if(! nofocus) this.callEvent(el.retrieve('input'), 'focus');
    }
    else this.fireEvent('onBoxFocus', el);
    this.current = el;    
    return this;
  },
  
  blur: function(noblur) {
    if(! this.current) return this;
    if(this.current.retrieve('type') == 'input') {
      var input = this.current.retrieve('input');
      if(! noblur) this.callEvent(input, 'blur');   
      this.fireEvent('onInputBlur', input);
    }
    else this.fireEvent('onBoxBlur', this.current);
    if(this.current.retrieve('small') && ! input.get('value') && this.options.hideempty) 
      this.current.setStyle('display', 'none');
    this.current.removeClass(this.options.className + '-' + this.current.retrieve('type') + '-focus');
    this.current = false;
    return this;
  },
  
  createBox: function(text, options) {
    return new Element('li', Object.append(options, {'class': this.options.className + '-box'})).set('html', text).store('type', 'box');
  },
  
  createInput: function(options) {
    var li = new Element('li', {'class': this.options.className + '-input'});
    var el = new Element('input', Object.append(options, {
      'type': 'text', 
      'events': {
        'click': function(e) { e.stop(); },
        'focus': function(e) { if(! this.isSelfEvent('focus')) this.focus(li, true); }.bind(this),
        'blur': function() { if(! this.isSelfEvent('blur')) this.blur(true); }.bind(this),
        'keydown': function(e) { this.store('lastvalue', this.value).store('lastcaret', this.getCaretPosition()); }
      }
    }));
    return li.store('type', 'input').store('input', el).adopt(el);
  },
  
  callEvent: function(el, type) {
    this.events[type] = el;
    el[type]();
  },
  
  isSelfEvent: function(type) {
    return (this.events[type]) ? !! delete this.events[type] : false;
  },
  
  makeResizable: function(li) {
    var el = li.retrieve('input');
    el.store('resizable', new ResizableTextbox(el, Object.append(this.options.resizable, {min: el.offsetWidth, max: this.element.getStyle('width').toInt()})));
    return this;
  },
  
  checkInput: function() {
    var input = this.current.retrieve('input');
    return (! input.retrieve('lastvalue') || (input.getCaretPosition() === 0 && input.retrieve('lastcaret') === 0));
  },
  
  move: function(direction) {
    var el = this.current['get' + (direction == 'left' ? 'Previous' : 'Next')]();
    if(el && (! this.current.retrieve('input') || ((this.checkInput() || direction == 'right')))) this.focus(el);
    return this;
  },
  
  moveDispose: function() {
    if(this.current.retrieve('type') == 'box') return this.dispose(this.current);
    if(this.checkInput() && Object.keys(this.bits).length && this.current.getPrevious()) return this.focus(this.current.getPrevious());
  }
  
});

var TextboxList2 = new Class({
  
  Extends: TextboxList,
  
  options: {    
    onBoxDispose: function(item) { this.autoFeed(item.retrieve('text')); },
    onInputFocus: function() { this.autoShow(); },    
    onInputBlur: function(el) { 
      this.lastinput = el;
      this.blurhide = this.autoHide.delay(200, this);
    },
    autocomplete: {
      'opacity': 0.8,
      'maxresults': 10,
      'minchars': 1
    }
  },
  
  initialize: function(element, options) {
    Asset.css('tags.css');
    this.parent(element.getElement('input'), options);
    this.data = [];
        this.autoholder = element.getElement('.textbox_items').setStyle('opacity', this.options.autocomplete.opacity);
        this.autoresults = this.autoholder.getElement('ul');
        var children = this.autoresults.getElements('li');
    children.each(function(el) { this.add(el.innerHTML); }, this); 
  },
  
  autoShow: function(search) {
    this.autoholder.setStyle('display', 'block');
    this.autoholder.getChildren().setStyle('display', 'none');
    if(! search || ! search.trim() || (! search.length || search.length < this.options.autocomplete.minchars)) 
    {
      this.autoholder.getElement('.default').setStyle('display', 'block');
      this.resultsshown = false;
    } else {
      this.resultsshown = true;
      this.autoresults.setStyle('display', 'block').empty();
      this.data.filter(function(str) { return str ? str.test(search, 'i') : false; }).each(function(result, ti) {
        if(ti >= this.options.autocomplete.maxresults) return;
        var that = this;
        var el = new Element('li').addEvents({
          'mouseenter': function() { that.autoFocus(this); },
          'click': function(e) { 
            e.stop();
            that.autoAdd(this); 
          }
        }).set('html', this.autoHighlight(result, search)).inject(this.autoresults);
        el.store('result', result);
        if(ti == 0) this.autoFocus(el);
      }, this);
    }
    return this;
  },
  
  autoHighlight: function(html, highlight) {
    return html.replace(new RegExp(highlight, 'gi'), function(match) {
      return '<em>' + match + '</em>';
    });
  },
  
  autoHide: function() {    
    this.resultsshown = false;
    this.autoholder.setStyle('display', 'none');    
    return this;
  },
  
  autoFocus: function(el) {
    if(! el) return;
    if(this.autocurrent) this.autocurrent.removeClass('auto-focus');
    this.autocurrent = el.addClass('auto-focus');
    return this;
  },
  
  autoMove: function(direction) {    
    if(!this.resultsshown) return;
    this.autoFocus(this.autocurrent['get' + (direction == 'up' ? 'Previous' : 'Next')]());
    return this;
  },
  
  autoFeed: function(text) {
    this.data.include(text);    
    return this;
  },
  
  autoAdd: function(el) {
    if(!el || ! el.retrieve('result')) return;
    this.add(el.retrieve('result'));
    delete this.data[this.data.indexOf(el.retrieve('result'))];
    this.autoHide();
    var input = this.lastinput || this.current.retrieve('input');
    input.set('value', '').focus();
    return this;
  },
  
  createInput: function(options) {
    var li = this.parent(options);
    var input = li.retrieve('input');
    input.addEvents({
      'keydown': function(e) {
        this.dosearch = false;
        switch(e.key) {
          case 'up': return this.autoMove('up');
          case 'down': return this.autoMove('down');
          case 'enter':
            if(! this.autocurrent) break;
            this.autoAdd(this.autocurrent);
            this.autocurrent = false;
            this.autoenter = true;
            break;
          case 'esc':
            this.autoHide();
            if(this.current && this.current.retrieve('input'))
              this.current.retrieve('input').set('value', '');
            break;
          default: this.dosearch = true;
        }
      }.bind(this),
      'keyup': function() {
        if(this.dosearch) this.autoShow(input.value);
      }.bind(this)
    });
    input.addEvent(Browser.ie ? 'keydown' : 'keypress', function(e) {
      if(this.autoenter) e.stop()
      this.autoenter = false;
    }.bind(this));
    return li;
  },
  
  createBox: function(text, options) {
    var li = this.parent(text, options);
    return li.addEvents({
      'mouseenter': function() { this.addClass('bit-hover') },
      'mouseleave': function() { this.removeClass('bit-hover') }
    }).adopt(new Element('a', {
      'href': '#',
      'class': 'closebutton',
      'events': {
        'click': function(e) {
          e.stop();
          if(! this.current) this.focus(this.maininput);
          this.dispose(li);
        }.bind(this)
      }
    })).store('text', text);
  }
  
});