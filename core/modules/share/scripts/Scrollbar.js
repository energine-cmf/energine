var Scrollbar = new Class({

    options: {
            type: 'vertical',
            scrolledElement: null
    },

    initialize: function(options) {
        this.setOptions(options);
        this.options.type = ['vertical', 'horizontal'].test(this.options.type) ? this.options.type : 'vertical';
        this.element = new Element('div').setStyles({ 'position': 'absolute', 'background': '#EED url(images/scrollbar_bg.gif)', 'display': 'none' }).injectInside(document.body);
        this.knob = new Element('div').setStyles({ 'background': '#EED url(images/scrollbar_knob.gif)', 'width': '16px', 'height': '20px' }).injectInside(this.element);
        this.contents = this.options.scrolledElement.getFirst(); // Первый дочерний элемент прокручиваемой области считается содержимым.
    },

    scrolledElement: function(event){
		if (event.wheel < 0) this.set(this.step + 1);
		else if (event.wheel > 0) this.set(this.step - 1);
		event.stop();
	},

    setup: function(steps) {
        if (!steps) {
            this.element.setStyle('display', 'none');
            return;
        }

        if (!this.options.scrolledElement) return false;

        var size = this.options.scrolledElement.getSize();
        if (size.size.y >= size.scrollSize.y) steps = 0;
        this.steps = steps;

        if (!this.contentsTuned) {
            this.contentsTuned = true;
            this.contents.setStyle('width', this.contents.getSize().size.x - 16 + 'px');
        }

        var coords = this.contents.getCoordinates();
        this.element.setStyles({
            'display': '',
            'top': coords.top - (Browser.Engine.trident ? (window.singleMode ? -1 : 24) : 0) + 'px',
            'left': coords.right + (Browser.Engine.trident ? 2 : 1) + 'px',
            'width': '16px',
            'height': this.options.scrolledElement.getSize().size.y - 1 + 'px'
        });

        if (!this.slider) {
            this.slider = new Slider(this.element, this.knob, {
                mode: this.options.type,
                onChange: function(step) {
                    var size = this.options.scrolledElement.getSize();
                    this.options.scrolledElement.scrollTo(0, step * ((size.scrollSize.y - size.size.y) / this.steps));
                }.bind(this)
            });
            this.contents.addEvent('mousewheel', this.scrolledElement.bindWithEvent(this.slider)); // Hack.
        }

        // More hacks:
        if (this.steps != 0) {
            this.slider.options.steps = this.steps; // Устанавливаем новое количество шагов полосы прокрутки.
        }
        this.slider.set(0);
    }
});

Scrollbar.implement(new Options);