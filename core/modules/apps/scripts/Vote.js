/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Vote]{@link Vote}</li>
 * </ul>
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

/**
 * Vote
 *
 * @constructor
 * @param {Element|string} el Main element.
 */
var Vote = new Class(/** @lends Vote# */{
    // constructor
    initialize:function (el) {
        /**
         * The main element.
         * @type {Element}
         */
        this.element = $(el);
        var url = this.element.getProperty('vote_url')+'?html&' + Math.floor((Math.random()*10000));
        if(url){
            this.element.set(
                'load',
                {
                    method: 'get',
                    'onFailure': this.error.bind(this),
                    'onComplete': this.init.bind(this)
                });
            this.element.load(url);
        }
    },

    /**
     * Init.
     * @function
     * @public
     */
    init: function(){
        this.element.getElements('.vote .vote_option a').addEvent('click', function(e){
            e.stop();
            this.element.getElement('.vote_options').addClass('invisible');
            this.element.load(e.target.href);
        }.bind(this))
    },

    /**
     * Error handler.
     * @function
     * @public
     */
    error: function(){
        this.element.empty();
        this.element.set('html', 'При голосовании произошла ошибка.');
    }
});