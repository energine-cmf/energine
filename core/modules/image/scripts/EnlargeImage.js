var EnlargeImage = new Class({
	initialize: function(element,options) {
        this.thumbnail = {};
		this.thumbnail.element = $(element);
        this.thumbnail.properties = {};
        
        $H(this.thumbnail.element.getCoordinates()).each(function(value, key){
            this.thumbnail.properties[key] = value;
        }, this);
        
		this.options = options;
        
        this.image = {};
        this.image.properties = {
            'width': this.thumbnail.element.getProperty('real_width'),
            'height': this.thumbnail.element.getProperty('real_height'),
            'src': this.thumbnail.element.getProperty('main')
        };
        this.image.element = new Asset.image(
            this.image.properties.src, {
            'styles': {
                'width':'0px',
                'height':'0px',
                'position': 'absolute',
                'top': '0px',
                'z-index': '0',
                'cursor': 'pointer'/*,
                'display':'none'*/
            },
            'id': 'EnlargedImage',
            'onload': this.zoomIn.bind(this)
            }
        );
        this.image.fx = new Fx.Morph(
            this.image.element,{
                duration: this.options.duration,
                transition:Fx.Transitions.Cubic.easeInOut
            }
        );
        
        this.overlay = {};
        this.overlay.element = new Element('div', {
            styles:{
                'width':this.thumbnail.properties.width + 'px',
                'height':this.thumbnail.properties.height + 'px',
                'top':(this.thumbnail.properties.top - (($('pageToolbar'))?24:0)) + 'px',
                'left':this.thumbnail.properties.left + 'px',
                'position': 'absolute',
                'z-index': '100000',
                'background': '#fff url(images/overlay_loading.gif) 50% 50% no-repeat'
            }
            
        });
        this.overlay.fx = new Fx.Tween(this.overlay.element, {property: 'opacity'}).set(0.5);
        this.overlay.element.inject(document.body);
	},
    zoomIn: function(){
        
        //Удаляем оверлей
        
        this.overlay.fx.addEvent('complete', function(){
            this.overlay.element.dispose();
            delete this.overlay;
        }.bind(this));
        this.overlay.fx.start(0);
        
        
        //ПРисоединяем имидж
        if($(document.body).grab(this.image.element)){
            
            this.image.properties.startTop = 
                this.thumbnail.properties.top 
                + this.thumbnail.properties.height/2 
                - (($('pageToolbar'))?24:0) 
                /*+ Window.getScroll().y*/;
            this.image.properties.startLeft = (this.thumbnail.properties.left + this.thumbnail.properties.width/2);
            this.image.properties.top = ((Window.getSize().y/2 - this.image.properties.height/2) + Window.getScroll().y) + 'px';
            this.image.properties.left = ((Window.getSize().x/2)-(this.image.properties.width/2)) + 'px';
            
            this.image.element.setStyles({
                'top':this.image.properties.startTop + 'px',
                'left':this.image.properties.startLeft  + 'px',
                'border': '1px solid silver',
                'z-index': '99'/*,
                'display': 'inline'*/
            });

            
            this.image.fx.start({
                'top':  [this.image.properties.startTop, this.image.properties.top],
                'left': [this.image.properties.startLeft, this.image.properties.left],
                'width': [0, this.image.properties.width],
                'height': [0, this.image.properties.height]
            });            
            this.image.element.addEvent('click', this.zoomOut.pass(false, this));
            
        }
    
    },
    zoomOut: function(onCompleteFunction){
            if(this.image.element){
                this.image.fx.addEvent('complete', function(){
                    this.image.element.dispose();
                    delete this.image.element;
                    if(onCompleteFunction)onCompleteFunction();
                }.bind(this));
    
                this.image.fx.start({
                    'top':  [this.image.properties.top, this.image.properties.startTop],
                    'left': [this.image.properties.left, this.image.properties.startLeft],
                    'width': [this.image.properties.width, 0],
                    'height': [this.image.properties.height, 0]
                });
            }
            currentImage = null;
    }
});