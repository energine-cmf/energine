/**
 * @file Testing CarouselConnector from Carousel.js
 *
 * @author Valerii Zinchenko
 * @version 0.1
 */

/**
 * Function for inserting special predefined HTML fixture like playlist or carousel.
 *
 * @param {string} fixture File name.
 */
function loadFixture(fixture) {
    new Request({
        async: false,
        method: 'get',
        url: '/test/fixtures/' + fixture,
        onSuccess: function() {
            var context = document.body.get('html');
            document.body.set('html', context + this.response.text);
        },
        onFailure: function() {
            console.error('Failed to load ', this.url);
        }
    }).send();
}

new TestCase('CarouselConnector initialisation', {
    setUp: function() {
        // Amount of items in the playlist
        var NItems = 7;

        loadFixture('playlist.html');
        loadFixture('carousel.html');

        // Create an playlist
        var playlistEl = $('playlistID');
        var item = playlistEl.getElement('.item');
        for (var n = 1; n < NItems; n++)
            item.clone().inject(playlistEl);
        // Set styles to the items
        playlistEl.getElements('.item').setStyle('width', 94);

        // create another CarouselBox
        var carouselElement = $('carouselID');
        carouselElement.clone().inject(carouselElement, 'after').addClass('another');

        this.playlist = new CarouselPlaylist('playlistID');
    },

    testThrows: function() {
        var expected = 30,
            NThrows = 0;

        try{
            new CarouselConnector();
        } catch (err) {
            assertEquals('Not enough arguments!', err);
            NThrows |= 1;
        }

        NThrows <<= 1;
        try{
            new CarouselConnector(0);
        } catch (err) {
            assertEquals('Second argument must be an Array of Carousel objects!', err);
            NThrows |= 1;
        }

        NThrows <<= 1;
        try{
            new CarouselConnector([new Carousel('.carousel', {carousel:{playlist:this.playlist}}), 5]);
        } catch (err) {
            assertEquals('Element #1 in the array is not instance of Carousel!', err);
            NThrows |= 1;
        }

        this.playlist.items.each(function(item) {
            item.clone().inject($$('.carousel.another .playlist_local')[0]);
        }.bind(this));
        this.playlist.items[0].clone().inject($$('.carousel.another .playlist_local')[0]);

        NThrows <<= 1;
        try{
            new CarouselConnector([new Carousel('.carousel', {carousel:{playlist:this.playlist}}), new Carousel('.carousel.another')]);
        } catch (err) {
            assertEquals('Carousels can not be connected, because of different amount of items in the playlists!', err);
            NThrows |= 1;
        }

        NThrows <<= 1;
        try {
            new CarouselConnector([new Carousel('.carousel', {carousel:{playlist:this.playlist}}),
                                   new Carousel('.carousel.another', {carousel:{playlist:this.playlist}})]);
        } catch (err) {
            NThrows |= 1;
        }

        assertEquals(expected, NThrows);
    }
});

new AsyncTestCase('Selecting items', {
    setUp: function() {
        // Amount of items in the playlist
        var NItems = 7;

        loadFixture('playlist.html');
        loadFixture('carousel.html');

        // Create an playlist
        var playlistEl = $('playlistID');
        var item = playlistEl.getElement('.item');
        for (var n = 1; n < NItems; n++)
            item.clone().inject(playlistEl);
        // Set styles to the items
        playlistEl.getElements('.item').setStyle('width', 94);

        // create another CarouselBox
        var carouselElement = $('carouselID');
        carouselElement.clone().inject(carouselElement, 'after').addClass('another');

        this.playlist = new CarouselPlaylist('playlistID');

        this.shortAnimation = 50;
    },
    testItemSelecting: function(queue) {
        var cc = new CarouselConnector([new Carousel('.carousel', {carousel:{playlist:this.playlist, fx:{duration:0}}}),
                                        new Carousel('.carousel.another', {carousel:{playlist:this.playlist}, fx:{duration:0}})]);

        queue.call('select item', function(callback) {
            cc.carousels[0].scrollForward();

            window.setTimeout(callback.add(function() {
                assertTrue(cc.carousels[0].items[1].hasClass('active'));
                assertTrue(cc.carousels[1].items[1].hasClass('active'));
            }), this.shortAnimation);
        });
    }
});