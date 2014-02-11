/**
 * @file Testing class Carousel.Types.Loop from Carousel.js
 *
 * @author Valerii Zinchenko
 * @version 0.9
 */

/**
 * Function for inserting special predefined HTML fixture like playlist or carousel.
 *
 * @param {string} fixture File name in the directory ./fixtures.
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

/**
 * Simple carousel builder.
 *
 * @param {string|Element} el HTML element.
 * @param {Object} opts Carousel options.
 * @returns {ACarousel}
 */
function buildCarousel(el, opts) {
    var carousel = new Carousel.Types.Loop(el, opts);
    carousel.build();
    return carousel;
}

new TestCase('Carousel.Types.Loop. Static tests.', {
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
        playlistEl.getElements('.item').setStyle('width', 94);  // 94 + styles in carousel.css gives the width of item 100px.

        this.playlist = new CarouselPlaylist('playlistID');
        this.carouselEl = $('carouselID');
    },

    testCloningItems: function() {
        assertEquals(this.playlist.NItems*2, buildCarousel(this.carouselEl, {playlist: this.playlist, NVisibleItems:4, scrollStep:4}).items.length);
    }
});