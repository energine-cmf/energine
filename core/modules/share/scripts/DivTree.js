ScriptLoader.load('DivManager');
var DivTree = new Class({
    Extends:DivManager,
    initialize:function (el) {
        this.parent(el);
        var el;
        this.currentID = false;
        var iframes = window.top.document.getElementsByTagName('iframe'), srcWindows = [window.top];
        for (var i = 0, l = iframes.length; i < l; i++) {
            if (iframes[i].contentWindow) srcWindows.push(iframes[i].contentWindow);
        }
        iframes = null;

        for (var i = 0, l = srcWindows.length, result = false; i < l; i++) {
            if (result = srcWindows[i].document.getElementById('smap_id')) {
                this.currentID = result.value.toInt();
                break;
            }
        }
    },
    onSelectNode:function (node) {
        this.parent(node);
        var btnSelect = this.toolbar.getControlById('select');
        if (this.currentID) {

            if (this.currentID == node.id) {
                if (btnSelect)btnSelect.disable();
            }
            else {
                var p = node.getParents(), l;
                if (l = p.length) {
                    for (var i = 0; i < l; i++) {
                        if (p[i].id == this.currentID) {
                            if (btnSelect)btnSelect.disable();
                            break;
                        }
                    }
                }
            }
        }
        else {
            if (btnSelect)btnSelect.enable();
        }

    }
});
