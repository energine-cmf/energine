var Vote = new Class({
    initialize:function (el) {
        this.element = $(el);
        var url = this.element.getProperty('vote_url')+'?html&' + Math.floor((Math.random()*10000));
        if(url){
            this.element.set('load', {method: 'get', 'onFailure': this.error.bind(this), 'onComplete': this.init.bind(this)});
            this.element.load(url);
        }

    },
    init: function(){
        this.element.getElements('.vote .vote_option a').addEvent('click', function(e){
            Energine.cancelEvent(e);
            this.element.getElement('.vote_options').addClass('invisible');
            this.element.load(e.target.href);
        }.bind(this))
    },
    error: function(){
        this.element.empty();
        this.element.set('html', 'При голосовании произошла ошибка.');
    }
});