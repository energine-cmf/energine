/**
 * @file Additional extensions to the MooTools framework. It contain the description of the next objects:
 * <ul>
 *     <li>[Mutators]{@link Mutators}</li>
 *     <li>[Mutators.Static]{@link Mutators.Static}</li>
 *     <li>[Mutators.Protected]{@link Mutators.Protected}</li>
 *     <li>[Asset.loaded]{@link Asset.loaded}</li>
 *     <li>[Asset.cssParent]{@link Asset.cssParent}</li>
 *     <li>[Asset.css]{@link Asset.css}</li>
 * </ul>
 *
 * @author Valerii Zinchenko
 *
 * @version 1.0.3
 */

/**
 * @namespace
 */
Class.Mutators = Object.append(Class.Mutators, {
    /**
     * Create static members for a class.
     *
     * @constructor
     * @param {Object} members Object that contains properties and methods, which must be static in the class.
     */
    Static: function (members) {
        this.extend(members);
    },

    /**
     * Create protected methods for a class.
     *
     * @constructor
     * @param {Object} methods Object with methods, which must be protected.
     */
    Protected: function (methods) {
        for (var key in methods) {
            if (methods[key] instanceof Function) {
                this.implement(key, methods[key].protect());
            }
        }
    }
});

(function(){
    Browser[Browser.name] = true;
    Browser[Browser.name + parseInt(Browser.version, 10)] = true;

    if (Browser.name == 'ie' && Browser.version >= '11') {
    	delete Browser.ie;
    }

    var platform = Browser.platform;
    if (platform == 'windows'){
    	platform = 'win';
    }
    Browser.Platform = {
    	name: platform
    };
    Browser.Platform[platform] = true;
})();


/**
 * @namespace
 * @augments Asset
 */
Asset = Object.append(Asset, /** @lends Asset# */{
    /**
     * List of loaded files.
     * @type {Object}
     *
     * @property {Object} css List of loaded CSS files.
     */
    loaded: {
        css: {}
    },

    /**
     * Set the default [Asset.css]{@link http://mootools.net/docs/more/Utilities/Assets#Asset:Asset-css} method as the parent method for extension.
     *
     * @function
     * @public
     */
    cssParent: Asset.css,

    /**
     * Overridden Asset.css function.
     *
     * @function
     * @public
     * @param {string} source Filename.
     * @returns {Element}
     */
    css: function (source) {
        if (Asset.loaded.css[source]) {
            return null;
        }

        var fullSource = ((Energine['static']) ? Energine['static'] : '') + 'stylesheets/' + source;
        Asset.loaded.css[source] = fullSource;

        return Asset.cssParent(fullSource, {'media': 'Screen, projection'});
    }
});

Element.implement({
    getComputedStyle: function(property){
        var floatName = (document.html.style.cssFloat == null) ? 'styleFloat' : 'cssFloat',
            defaultView = Element.getDocument(this).defaultView,
            computed = defaultView ? defaultView.getComputedStyle(this, null) : null;
        return (computed) ? computed.getPropertyValue((property == floatName) ? 'float' : property.hyphenate()) : null;
    },

    // NOTE: This function is overwritten because of not secure style value casting.
    getComputedSize: function(options){
        function calculateEdgeSize(edge, styles){
            var total = 0;
            Object.each(styles, function(value, style){
                if (style.test(edge)) total = total + value.toInt();
            });
            return total;
        }
        function getStylesList(styles, planes){
            var list = [];
            Object.each(planes, function(directions){
                Object.each(directions, function(edge){
                    styles.each(function(style){
                        list.push(style + '-' + edge + (style == 'border' ? '-width' : ''));
                    });
                });
            });
            return list;
        }

        options = Object.merge({
            styles: ['padding','border'],
            planes: {
                height: ['top','bottom'],
                width: ['left','right']
            },
            mode: 'both'
        }, options);

        var styles = {},
            size = {width: 0, height: 0},
            dimensions;

        if (options.mode == 'vertical'){
            delete size.width;
            delete options.planes.width;
        } else if (options.mode == 'horizontal'){
            delete size.height;
            delete options.planes.height;
        }

        getStylesList(options.styles, options.planes).each(function(style){
            // here was not checked if the type casting return NaN
            var value = parseInt(this.getStyle(style));
            styles[style] = isNaN(value) ? 0 : value;
        }, this);

        Object.each(options.planes, function(edges, plane){

            var capitalized = plane.capitalize(),
                style = this.getStyle(plane);

            if (style == 'auto' && !dimensions) dimensions = this.getDimensions();

            var value = (style == 'auto') ? dimensions[plane] : parseInt(style);
            style = styles[style] = isNaN(value) ? 0 : value;
            size['total' + capitalized] = style;

            edges.each(function(edge){
                var edgesize = calculateEdgeSize(edge, styles);
                size['computed' + edge.capitalize()] = edgesize;
                size['total' + capitalized] += edgesize;
            });

        }, this);

        return Object.append(size, styles);
    }
});

/*
 ---
 description:     PostMessager

 authors:
 - David Walsh (http://davidwalsh.name)

 license:
 - MIT-style license

 requires:
 core/1.2.1:   '*'

 provides:
 - PostMessager
 ...
 */

/* navive base onMessage support */
Element.NativeEvents.message = 2;
Element.Events.message = {
    base: 'message',
    condition: function(event) {
        if(!event.$message_extended) {
            event.data = event.event.data;
            event.source = event.event.source;
            event.origin = event.event.origin;
            for(key in event) {
                if(event[key] == undefined) {
                    event[key] = false;
                }
            }
            event.$message_extended = true;
        }
        return true;
    }
};

/**
 * PostMessager 0.4


 PostMessager is a MooTools plugin that acts as a wrapper for the window.postMessage API which is available in IE8+, Firefox 3.1+, Opera 9+, Safari, and Chrome. PostMessager also normalizes the onMessage event for use within MooTools.

 * @see http://mootools.net/forge/p/postmessager
**/
var PostMessager  = new Class({

    Implements: [Options,Events],

    options: {
        allowReceive: true,
        allowSend: true,
        source: window,
        validReceiveURIs: [] /*,
         onSend: $empty,
         onReceive: $empty,
         onReply: $empty
         */
    },

    initialize: function(destFrame,options) {
        this.setOptions(options);
        this.source = document.id(this.options.source);
        this.dest = destFrame;

        this.allowReceive = this.options.allowReceive;
        this.allowSend = this.options.allowSend;

        this.validURIs = this.options.validReceiveURIs;

        this.listener = function(e) {
            if(this.allowReceive && (this.validURIs.length == 0 || this.validURIs.contains(e.origin))) {
                this.fireEvent('receive',[e.data,e.source,e.origin]);
            }
        }.bind(this);

        this.started = false;
        this.start();
    },

    send: function(message,URI) {
        if(this.allowSend) {
            this.dest.postMessage(message,URI);
            this.fireEvent('send',[message,this.dest]);
        }
    },

    reply: function(message,source,origin) {
        source.postMessage(message,origin);
        this.fireEvent('reply',[message,source,origin]);
    },

    start: function() {
        if(!this.started) {
            this.source.addEvent('message',this.listener);
            this.started = true;
        }
    },

    stop: function() {
        this.source.removeEvent('message',this.listener);
        this.started = false;
    },

    addReceiver: function(receiver) {
        this.validURIs.push(receiver);
    },

    removeReceiver: function(receiver) {
        this.validURIs.erase(receiver);
    },

    enableReceive: function() {
        this.allowReceive = true;
    },

    disableReceive: function() {
        this.allowReceive = false;
    },

    enableSend: function() {
        this.allowSend = true;
    },

    disableSend: function() {
        this.allowSend = false;
    }

});

/*
 ---
 description: Simple color picker for mootools.

 license: MIT-style

 authors:
 - Fiona Coulter

 requires:
 - core/1.2: [Class, Class.Extras, Element, Element.Event,Element.Style,Fx,Fx.Tween]

 provides: [ColorPicker]

 ...
 */

var ColorPicker = new Class({
    getOptions: function(){
        return { cellWidth: 5, cellHeight: 10, top: 20, left: -100, transition: true
        };
    },
    initialize: function(el,options){
        this.setOptions(this.getOptions(), options);
        var ms = new String(MooTools.version);
        this.version = ms.substr(0,3);

        if(this.version == '1.1')
        {
            this.el = $(el);

        }
        else
        {
            this.el = document.id(el);
        }
        this.el.addEvent("focus", function(e){ this.openPicker();}.bind(this));
        this.el.addEvent("change", function(e){ this.validate(); this.closePicker();}.bind(this));
        this.el.addEvent('keyup', function(e){ e = new Event(e);  try{ this.colorPanel.setStyle("backgroundColor", this.el.value); } catch(e){}}.bind(this));

        this.height = this.options.cellHeight * 8 + this.options.top;
        this.active = false;


        this.container = new Element("div");

        this.el.parentNode.insertBefore(this.container, this.el);
        this.container.appendChild(this.el);
        this.el.setStyle("float","left");


        this.colorPanel = new Element("input");
        this.colorPanel.setAttribute("size","2");
        this.colorPanel.setAttribute("type","text");
        this.colorPanel.setAttribute("readonly","readonly");
        this.colorPanel.setStyle("backgroundColor",this.el.value);
        this.colorPanel.setStyle("cursor","pointer");
        this.colorPanel.setStyle("float","left");

        this.colorPanel.addEvent("focus", function(e){ this.openPicker();}.bind(this));
        this.container.appendChild(this.colorPanel);

        this.infoPanel = new Element("span");
        this.infoPanel.setStyle("float","left");
        this.container.appendChild(this.infoPanel);

        //color chart container
        this.chartContainer = new Element("div");
        this.chartContainer.setStyles({position:"relative", "z-index": 100, cursor: "pointer","background-color":"#000000", float:"left","overflow":"visible"});
        this.chartContainer.addEvent('blur', function(e){ e = new Event(e);  this.closePicker();}.bind(this));


        this.container.parentNode.insertBefore(this.chartContainer, this.container);


        this.colorTable = new Element("table");
        this.colorTable.setAttribute("border","1");
        this.colorTable.setAttribute("bordercolor","silver");
        this.colorTable.setAttribute("cellpadding","0");
        this.colorTable.setAttribute("cellspacing","0");
        this.colorTable.setStyles({"background-color":"#000000", "margin":"4px",visibility:"visible", position:"absolute", top: this.options.top + "px", left: this.options.left + "px", "z-index": 100, cursor: "pointer"});
        var tabBody = new Element("tbody");
        this.colorTable.appendChild(tabBody);


        var colorArray = ["00", "33", "66", "99", "cc", "ff"];
        for ( var i=0; i< colorArray.length; i++)
        {
            var currRow = new Element("tr");
            tabBody.appendChild(currRow);

            for ( var j=0; j< colorArray.length; j++)
            {

                for ( var k=0; k< colorArray.length; k++)
                {

                    var currColor = "#"+colorArray[i]+colorArray[j]+colorArray[k];
                    var currCell = new Element("td");
                    currCell.innerHTML = '<div width="'+  this.options.cellWidth +'px" height="'+ this.options.cellHeight +'px" style="width:'+ this.options.cellWidth +'px;height:'+ this.options.cellHeight +'px;">&nbsp;</div>';
                    currCell.setStyle("backgroundColor",currColor);

                    currCell.addEvent('click', function(){
                        this.el.value = currColor; this.closePicker();
                    }.bind(this));

                    currCell.addEvent('mouseover', function(){
                        this.colorPanel.setStyle("backgroundColor", currColor);  this.infoPanel.innerHTML = currColor;
                    }.bind(this));
                    //currCell.setStyles({"width":this.options.cellWidth +"px", "height":this.options.cellHeight +"px"});
                    currCell.setStyles({"padding":"0px"});
                    //currCell.setAttribute("width", this.options.cellWidth +'px');

                    currRow.appendChild(currCell);


                }


            }


        }

        this.fader = null;
        if(this.options.transition)
        {
            if(this.version == '1.1')
            {
                this.fader = new Fx.Style(this.colorTable,'opacity', {duration:1000});
            }
            else
            {
                this.fader = new Fx.Tween(this.colorTable,'opacity', {duration:1000});
            }

        }


        this.chartContainer.addEvent('mouseout', function(){
            try{this.colorPanel.setStyle("backgroundColor", this.el.value); } catch(e){} this.infoPanel.innerHTML = '';
        }.bind(this));

        this.chartContainer.appendChild(this.colorTable);

        $(document).addEvent('click', function(e){e = new Event(e); if((e.target != this.el) &&(e.target != this.colorPanel)){ this.closePicker();}}.bind(this));
        this.hidePicker();

    },
    closePicker: function(){
        this.colorTable.setStyle("visibility","hidden");
        this.infoPanel.innerHTML = '';
        this.colorPanel.setStyle("backgroundColor",this.el.value);
        //this.chartContainer.setStyle("height","auto");
        if(this.options.transition && this.active)
        {
            if(this.version == '1.1')
            {
                this.fader.start(1,0);
            }
            else
            {
                this.colorTable.fade('show');
                this.colorTable.fade('out');
            }
        }
        this.active = false;

    },
    openPicker: function(){
        this.colorTable.setStyle("visibility","visible");
        //this.chartContainer.setStyle("height",this.height + "px");
        if(this.options.transition)
        {
            if(this.version == '1.1')
            {
                this.fader.start(0,1);
            }
            else
            {
                this.colorTable.fade('hide');
                this.colorTable.fade('in');
            }
        }
        this.active = true;
    },
    hidePicker: function(){
        this.colorTable.setStyle("visibility","hidden");
        this.infoPanel.innerHTML = '';
        this.colorPanel.setStyle("backgroundColor",this.el.value);
    },
    validate: function(){
        var pattern = /#[0-9A-Fa-f]{6}/;
        if( pattern.test(this.el.value))
        {
            return;
        }

        var stringVal = new String(this.el.value);
        if(stringVal.charAt(0) != '#')
        {
            stringVal = '#' + stringVal;
        }

        var pattern2 = /[^#A-Fa-f0-9]/g;
        stringVal = stringVal.replace(pattern2, '');

        var l = 7 - stringVal.length; //extra 0s to pad
        for(var i=0; i<l; i++)
        {
            stringVal = stringVal + '0';
        }

        stringVal = stringVal.substr(0,7);

        //finally retest
        if( ! pattern.test(stringVal))
        {
            stringVal = '#ffffff';
        }

        this.el.value = stringVal;

    }




});

ColorPicker.implement(new Events);
ColorPicker.implement(new Options);
