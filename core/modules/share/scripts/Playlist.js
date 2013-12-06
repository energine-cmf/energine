/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Playlist]{@link Playlist}</li>
 * </ul>
 *
 * @requires Energine
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

/**
 * Playlist.
 *
 * @constructor
 * @param {Element} playlistElement Playlist element.
 * @param {Element} playerElement Player element.
 * @param {Element} playerBoxElement Player box element.
 */
var Playlist = new Class(/** @lends Playlist */{
    /**
     * li-elements
     * @type {Elements}
     */
    liElements: [],

    /**
     * Elements
     * @type {Elements}
     */
    elements: [],

    /**
     * Player.
     * @type {}
     */
    player: null,

    // constructor
    initialize : function(playlistElement, playerElement, playerBoxElement) {
        /**
         * Playlist element.
         * @type {Element}
         */
        this.playlistElement = $(playlistElement);

        /**
         * Player element.
         * @type {Element}
         */
        this.playerElement = $(playerElement);

        /**
         * Player box element.
         * @type {Element}
         */
        this.playerBox = $(playerBoxElement);

        this.playlistElement.getElements('.viewbox ul li').each(function(el, index) {
            this.liElements.push(el);
            el = el.getElement('a');
            var clipConf = {
                url: Energine.base + el.getProperty('href')
            };

            if (el.getProperty('nrgn:media_type') == 'image') {
                clipConf.controls = {
                    all:false,
                    fullscreen:true,
                    playlist:false
                };
                clipConf.duration = 5;
                clipConf.scaling = 'orig';
            }

            this.elements.push(clipConf);

            el.addEvent('click', function(event) {
                Energine.cancelEvent(event);
                if(!this.player) {
                    this.player = this.createPlayer(this.playerElement);
                }
                this.player.play(index);
            }.bind(this));
        }, this);

        this.liElements = new Elements(this.liElements);
        this.playerBox.getElement('a').addEvent('click', function(event){
            Energine.cancelEvent(event);
            if (!this.player) {
                this.player = this.createPlayer(this.playerElement);
            }
            this.player.play();
        }.bind(this));
    },

    // todo: Look for unused variables.
    /**
     * Create player.
     *
     * @function
     * @public
     * @param {Element} playerElement Player element.
     * @param {} startIndex
     * @returns {flowplayer}
     */
    createPlayer: function(playerElement, startIndex) {
        var playerProperties = {
            onError: function(errorCode) {
                this.unload();
            }.bind(this),
            /*            onFinish: function() {
             this.player.unload();
             }.bind(this),*/
            playlist: this.elements,
            clip : {
                //provider:'pseudo',
                autoPlay:true,
                scaling: 'fit',
                onBegin: function(){
                    if(this.player.getClip().type == 'video'){
                        if (!this.liElements[this.player.getClip().index]) {
                            return;
                        }
                        var el = this.liElements[this.player.getClip().index].getElement('a');
                    }
                }.bind(this),

                onStart: function(clip, cc) {
                    this.liElements.removeClass('active');
                    this.liElements[this.player.getClip().index].addClass('active');
                }.bind(this)
            },

            plugins:{
                controls:{
                    playlist: false, fullscreen:true, zIndex: 2
                }/*,
                 pseudo:{
                 url: Energine.static +
                 'scripts/flowplayer.pseudostreaming-3.2.7.swf'
                 }*/
            },

            play: {
                label: null,
                replayLabel: null
            },

            canvas: {
                backgroundColor: '#000000',
                backgroundGradient: 'none'
            }
        };

        return flowplayer(
            playerElement,
            {
                src: Energine['static'] + 'scripts/flowplayer.swf',
                wmode: 'opaque'
            },
            playerProperties
        );
    }
});


