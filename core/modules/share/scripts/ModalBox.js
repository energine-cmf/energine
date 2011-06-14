ScriptLoader.load(
    'Overlay'
);

var ModalBox = window.top.ModalBox || {

    boxes: [],

    init: function() {
        Asset.css('modalbox.css');
        this.overlay = new Overlay(document.body, {indicator: false});        
        this.initialized = true;
    },
    /**
     *
     * @param Object options
     */
    open: function(options) {
		var box = new Element('div').addClass('e-modalbox').injectInside(document.body);
		box.options = {
            url: null,            
            onClose: $empty,//$empty,
            extraData: null,
            post: null
		};
		$extend(box.options, $pick(options, {}));

		if(box.options.url){
            var iframeSrc = box.options.url, mbName = 'modalBoxIframe' + this.boxes.length.toString();

            if(box.options.post){
                var postForm = new Element('form',{target: mbName, action: iframeSrc, method: 'post'}).grab(new Element('input', {'type': 'hidden', 'name': 'modalBoxData', 'value': box.options.post}));
                iframeSrc = 'about:blank';
            }

		if (Browser.Engine.trident) {
                iframe = $(document.createElement('<iframe class="e-modalbox-frame" src="' + iframeSrc + '" frameBorder="0" name="' + mbName + '" scrolling="no" />'));
		}
		else {
			iframe = new Element('iframe').setProperties(
					{
                        'name': mbName,
                        'src': iframeSrc,
						'frameBorder': '0',
						'scrolling': 'no',
						'class': 'e-modalbox-frame'
					}
				)
		}
		box.iframe = iframe.injectInside(box);
            if(box.options.post){
                box.grab(postForm);
                postForm.submit();
                postForm.destroy();
            }
        }
        else if(box.options.code){
            //box.set('html', code);
            box.grab(box.options.code);
        }

		//box.iframe.addEvent('keydown', this.keyboardListener.bindWithEvent(this));
        box.closeButton = new Element('div').addClass('e-modalbox-close').injectInside(box);
        box.closeButton.addEvents({
            'click': this.close.bind(this),
            'mouseover': function() { this.addClass('highlighted'); },
            'mouseout': function() { this.removeClass('highlighted'); }
        });

        this.boxes.push(box);

        if (this.boxes.length == 1) {
            this.overlay.show();
        }        

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
        this.getCurrent().store('returnValue', value);
    },

    close: function() {
        if (!this.boxes.length) {
            return;
        }
        var box = this.boxes.pop();
        box.options.onClose(box.retrieve('returnValue'));

        var destroyBox = function(){
            if(box.iframe){
        	box.iframe.setProperty('src', 'about:blank');
			box.iframe.destroy();
            }
			box.destroy();
        }
        
        destroyBox.delay(1);
        
		if (!this.boxes.length) {
			this.overlay.hide();
		}
    },

    keyboardListener: function(event) {
		switch (event.key) {
			case 'esc': this.close(); break;
		}
	}
};

if (!ModalBox.initialized) {
	window.addEvent('domready', ModalBox.init.bind(ModalBox));
}
