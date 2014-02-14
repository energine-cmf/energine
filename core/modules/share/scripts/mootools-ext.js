Class.Mutators = Object.append(Class.Mutators,{
    Static: function (m) {this.extend(m);},
    Protected: function (m) {for (var k in m) {if (m[k] instanceof Function) {this.implement(k, m[k].protect());}}}
});

Asset = Object.append(Asset, {
    loaded: {css: {}},
    cssParent: Asset.css,
    css: function (source) {
        if (Asset.loaded.css[source]) return null;
        var fs = ((Energine['static']) ? Energine['static'] : '') + 'stylesheets/' + source;
        Asset.loaded.css[source] = fs;
        return Asset.cssParent(fs, {'media': 'Screen, projection'});
    }
});

Element.implement({
    getComputedSize: function(options){
        function getStylesList(sts, p){
            var l = [];
            Object.each(p, function(d){Object.each(d, function(e){
                    sts.each(function(s){l.push(s + '-' + e + (s == 'border' ? '-width' : ''));});
            });});
            return l;
        }
        function calculateEdgeSize(e, sts){
            var t = 0;
            Object.each(sts, function(v, s){
                if (s.test(e)) t += v.toInt();
            });
            return t;
        }
        options = Object.merge({
            styles: ['padding','border'],
            planes: {
                height: ['top','bottom'],
                width: ['left','right']
            },
            mode: 'both'
        }, options);
        var sts = {}, s = {width: 0, height: 0}, d;
        if (options.mode == 'vertical'){
            delete s.width;
            delete options.planes.width;
        } else if (options.mode == 'horizontal'){
            delete s.height;
            delete options.planes.height;
        }
        getStylesList(options.styles, options.planes).each(function(s){
            var v = this.getStyle(s).toInt();
            sts[s] = isNaN(v) ? 0 : v;
        }, this);
        Object.each(options.planes, function(es, p){
            var c = p.capitalize(),st = this.getStyle(p);
            if (st == 'auto' && !d) d = this.getDimensions();
            st = sts[p] = (st == 'auto') ? d[p] : st.toInt();
            s['total' + c] = st;
            es.each(function(e){
                var ed = calculateEdgeSize(e, sts);
                s['computed' + e.capitalize()] = ed;
                s['total' + c] += ed;
            });
        }, this);
        return Object.append(s, sts);
    }
});