// Trying to prevent Player from double loading
if('undefined' === typeof Player) {
    /**
     * Wrapper for custom player, placed in share module
     * Now in use: JWPlayer 6
     * @type {Class}
     */
    var Player = new Class({
        initialize: function (player_id, image, files, width, height, autostart) {
            this.player_id = player_id;
            this.image = image || '';
            this.files = files || [];
            this.width = width || 0;
            this.height = height || 0;
            this.autostart = autostart || false;

            $(document).addEvent('domready', function () {
                if (Browser.firefox && !this.base_fixed) {
                    this.fix_base();
                }
                this.init_player();
            }.bind(this));
        },

        init_player: function () {
            var playlist = [];
            var sources = [];

            for (var i = 0; i < this.files.length; i++) {
                sources[sources.length] = {file: this.files[i]};
            }

            playlist[playlist.length] = {
                image: this.image,
                sources: sources
            };

            var options = {
                playlist: playlist,
                stretching: 'uniform',
                primary: (Browser.Platform.ios || Browser.ie || Browser.opera || (Browser.name == 'unknown')) ? 'flash' : 'html5',
                controls: true,
                width: this.width,
                height: this.height,
                autostart: this.autostart
            };

            if (this.width == '100%') {
                options['aspectratio'] = '4:3';
            }

            jwplayer(this.player_id).setup(options);

            // Player Events
            var player = jwplayer(this.player_id);
            player.onReady(function () {
                this.fireEvent('ready');
            }.bind(this));
            player.onPlay(function () {
                this.fireEvent('play');
            }.bind(this));
            player.onPause(function () {
                this.fireEvent('pause');
            }.bind(this));
            player.onBufferFull(function () {
                this.fireEvent('bufferFull');
            }.bind(this));
            player.onComplete(function () {
                this.fireEvent('complete');
            }.bind(this));
            player.onSeek(function () {
                this.fireEvent('seek');
            }.bind(this));
            player.onError(function () {
                this.fireEvent('error');
            }.bind(this));
            player.onIdle(function () {
                this.fireEvent('idle');
            }.bind(this));
        },

        fix_base: function () {
            if (!Player.base_fixed) {
                var b = document.getElementsByTagName('base');
                if (b.length) {
                    b[0].parentNode.removeChild(b[0]);
                    document.getElements('a').each(function (el) {
                        var href;
                        if (
                            (href = el.getProperty('href'))
                                &&
                                (href.substr(0, 2) == Energine.lang)
                            ) {
                            el.setProperty('href', Energine.base + href);
                        }
                    });
                }
                Player.base_fixed = true;
            }
        }
    });
    // Static variable for escape double base - fixing
    Player.base_fixed = false;
}