ScriptLoader.load('Form');

var VoteForm = new Class({
    Extends:Form,
    initialize:function (el) {
        this.parent(el);
        var tab = this.tabPane.createNewTab(Energine.translations.get('TAB_VOTE_QUESTIONS'));
        tab.pane.grab(this.loadData());
        tab.pane.setStyle('padding', '0px');
    },
    loadData:function () {
        var iframe, url = this.singlePath + ((this.state !== 'add') ? this.componentElement.getElementById('vote_id').get('value') : '') + '/question/';
        if (Browser.ie && (Browser.version < 9)) {
            iframe = $(document.createElement('<iframe src="' + url + '" frameBorder="0" scrolling="no" style="width:99%; height:99%;"/>'));
        }
        else {
            iframe = new Element('iframe', {
                'src':url,
                'frameBorder':'0',
                'scrolling':'no',
                'styles':{
                    'width':'99%',
                    'height':'99%'
                }
            });
        }
        return iframe;
    }
});