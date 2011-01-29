var Overlay = new Class({
	Implements: Options,
	options:{            
            opacity: 0.5,
            hideObjects: true,
            indicator: true
    },
    
    initialize: function(parentElement, options) {
    	Asset.css('overlay.css');
        this.setOptions(options);
        parentElement = parentElement ? parentElement : document.body;
        if(!(this.element = parentElement.getElement('.e-overlay'))){
            this.element = new Element('div').addClass('e-overlay e-overlay-loading').injectInside(parentElement);
        }
        //this.fx = this.element.effect('opacity', { wait: false }).hide();
        this.element.fade('hide');
        if(!(this.options.indicator)){this.element.removeClass('e-overlay-loading')};
    },

    show: function() {    	
        this.setupObjects(true);        
        this.element.fade(this.options.opacity);
        //this.fx.start(this.options.opacity);
    },

    hide: function() {
		var fx = new Fx.Tween(this.element, {property: 'opacity'});
        this.setupObjects(false);
    	fx.start(this.options.opacity, 0).chain(
            function(){this.start(0);}
    	);
    },
    setupObjects: function(hide) {

    	var body;
        if (!this.options.hideObjects) return;        
        var elements = $A((body = $(document.body)).getElements('object'));                
        elements.extend(
        	$A(body.getElements(Browser.Engine.trident ? 'select' : 'embed'))
        );        
        elements.each(function(element) { element.style.visibility = hide ? 'hidden' : ''; });        
    }
});
