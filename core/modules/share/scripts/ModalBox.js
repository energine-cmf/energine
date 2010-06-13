var ModalBox = window.top.ModalBox || {

    boxes: [],

    init: function() {
        Asset.css('modalbox.css');
        this.overlay = new Element('div').setProperties({
        	'id': 'mb_overlay',
        	'class': 'e-overlay'
        }).injectInside(document.body);
        this.fx = new Fx.Tween(this.overlay);

        this.overlay.fade();//this.overlay.effect('opacity').hide();
        //this.eventKeyDown = this.keyboardListener.bindWithEvent(this);
        this.eventPosition = this.position.bind(this);
        this.initialized = true;
    },
    open: function(options) {
		var box = new Element('div').addClass('e-modalbox').injectInside(document.body);
		box.options = {
            url: null,            
            onClose: $empty,//$empty,
            extraData: null
		};
		$extend(box.options, $pick(options, {}));

		if (Browser.Engine.trident) {
			iframe = $(document.createElement('<iframe class="e-modalbox-frame" src="' + box.options.url + '" frameBorder="0" scrolling="no" />'));
		}
		else {
			iframe = new Element('iframe').setProperties(
					{
						'src': box.options.url,
						'frameBorder': '0',
						'scrolling': 'no',
						'class': 'e-modalbox-frame'
					}
				)
		}
		box.iframe = iframe.injectInside(box);

		/*box.iframe = new IFrame({
			'src': box.options.url,
			'frameBorder': '0',
			'scrolling': 'no'
		}).injectInside(box);
*/
		//box.iframe.addEvent('keydown', this.keyboardListener.bindWithEvent(this));
        box.closeButton = new Element('div').addClass('e-modalbox-close').injectInside(box);
        box.closeButton.addEvents({
            'click': this.close.bind(this),
            'mouseover': function() { this.addClass('highlighted'); },
            'mouseout': function() { this.removeClass('highlighted'); }
        });

        this.boxes.push(box);

        if (this.boxes.length == 1) {
            //this.position();
            this.setup(true);
            this.fx.set('opacity', 0.5);
        }
        //alert(2);

    },

    getCurrent: function() {
        return this.boxes[this.boxes.length - 1];
    },

    getExtraData: function() {
        var result = null;
        if(this.getCurrent()){
            result = this.getCurrent().options.extraData;
        }
        
        return result;
    },

    setReturnValue: function(value) {
        this.getCurrent().returnValue = value;
    },

    close: function() {
        if (!this.boxes.length) {
            return;
        }
        var box = this.boxes.pop();
        box.options.onClose(box.returnValue);

        var destroyBox = function(){
        	box.iframe.setProperty('src', 'about:blank');
			box.iframe.destroy();
			box.destroy();
        }
        
        destroyBox.delay(1);
        
		if (!this.boxes.length) {
			this.setup(false);
    		this.fx.start('opacity', 0);
		}
    },

    keyboardListener: function(event) {
		switch (event.key) {
			case 'esc': this.close(); break;
		}
	},
		
	position: function() {
		this.overlay.setStyles({ 'height': Window.getHeight() + 'px' });
    },

    setup: function(open) {
        var elements = $(document.body).getElements('object');

        elements.extend(
        	$(document.body).getElements(Browser.Engine.trident ? 'select' : 'embed')
        );
        elements.each(function(element) { element.style.visibility = open ? 'hidden' : ''; });
        /*
        var fn = open ? 'addEvent' : 'removeEvent';
        window[fn]('resize', this.eventPosition);
*/
        //document[fn]('keydown', this.eventKeyDown);
    }
};

if (!ModalBox.initialized) {
	window.addEvent('domready', ModalBox.init.bind(ModalBox));
}