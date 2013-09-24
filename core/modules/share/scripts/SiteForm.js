ScriptLoader.load('Form');
var SiteForm = new Class({
    Extends: Form,
    initialize: function(el) {
        this.parent(el);
        var tab = this.tabPane.createNewTab(Energine.translations.get('TAB_DOMAINS'));
        tab.pane.grab(this.loadData());
        tab.pane.setStyle('padding', '0px');
    },
    loadData: function() {
        var iframe, url = this.singlePath + ((this.state !== 'add') ? this.componentElement.getElementById('site_id').get('value') : '') + '/domains/';
        if (Browser.Engine.trident && (Browser.version < 9)) {
            iframe = $(document.createElement('<iframe src="'+url+'" frameBorder="0" scrolling="no" style="width:99%; height:99%;"/>'));
        }
        else {
            iframe = new Element('iframe', {
                'src': url,
                'frameBorder': '0',
                'scrolling': 'no',
                'styles': {
                    'width':'99%',
                    'height':'99%'
                }
            });
        }
        return iframe;
    }
});
