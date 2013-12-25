/**
 * @file Testing class CarouselPlaylist from Carousel.js
 *
 * @author Valerii Zinchenko
 *
 * @version 0.2
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

new TestCase('CarouselPlaylist initialisation', {
    setUp: function() {
        loadFixture('playlist.html');
        this.playlistElement = $('playlistID');
    },

    testGetPlaylistByID: function() {
        var NThrows = 0;
        assertEquals(this.playlistElement.getChildren()[0], new CarouselPlaylist('playlistID').items[0]);
        try {
            new CarouselPlaylist('playlistI');
        } catch (err) {
            assertEquals('Element for CarouselPlaylist was not found in DOM Tree!', err);
            NThrows++;
        }
        assertEquals(1, NThrows);
    },
    testGetPlaylistByClass: function() {
        var NThrows = 0;
        assertEquals(this.playlistElement.getChildren()[0], new CarouselPlaylist('.playlist').items[0]);
        try {
            new CarouselPlaylist('.playlis');
        } catch (err) {
            assertEquals('Element for CarouselPlaylist was not found in DOM Tree!', err);
            NThrows++;
        }
        assertEquals(1, NThrows);
    },
    testHidePlaylist: function () {
        new CarouselPlaylist('.playlist').hide();
        assertEquals(0, $$('.playlist').length);
    },
    testItemSelector: function() {
        // Amount of items in the playlist
        var NItems = 7,
            item = this.playlistElement.getElement('.item'),
            n,
            NThrows = 0;
        for (n = 1; n < NItems; n++)
            item.clone().inject(this.playlistElement);

        assertEquals(NItems, new CarouselPlaylist('.playlist').NItems);
        assertEquals(NItems, new CarouselPlaylist('.playlist', '.item').NItems);
        try {
            new CarouselPlaylist('.playlist', '.other');
        } catch (err) {
            assertEquals('No items were found in the playlist.', err);
            NThrows++;
        }
        assertEquals(1, NThrows);

        var items = this.playlistElement.getElements('.item');
        for (n = 0; n < NItems; n += 2)
            items[n].removeClass('item').addClass('other');

        assertEquals(NItems, new CarouselPlaylist('.playlist').NItems);
        assertEquals(3, new CarouselPlaylist('.playlist', '.item').NItems);
        assertEquals(4, new CarouselPlaylist('.playlist', '.other').NItems);
    }
});