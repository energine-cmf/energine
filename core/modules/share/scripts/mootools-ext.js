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