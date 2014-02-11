/**
 * @file Testing class ACarousel from Carousel.js
 *
 * @author Valerii Zinchenko
 * @version 1.0
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
    var carousel = new ACarousel(el, opts);
    carousel.build();
    return carousel;
}

new TestCase('ACarousel. Static tests', {
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

    testThrows: function() {
        var NThrows = 0;

        NThrows <<= 1;
        try {
            new ACarousel(this.carouselEl, {classes: {viewbox: 'box'}});
        } catch (e) {
            assertEquals('View box of the carousel was not found.', e);
            NThrows |= 1;
        }

        NThrows <<= 1;
        try {
            new ACarousel(this.carouselEl);
        } catch (e) {
            assertEquals('Carousel can not be created without playlist.', e);
            NThrows |= 1;
        }

        assertEquals(3, NThrows);
    },
    testCheckingOptionsNVisibleItems: function() {
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:-1}).options.NVisibleItems);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:0}).options.NVisibleItems);
        assertEquals(this.playlist.NItems, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:this.playlist.NItems+1}).options.NVisibleItems);
        assertEquals(this.playlist.NItems, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:'all'}).options.NVisibleItems);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:'abc'}).options.NVisibleItems);
        assertEquals(5, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:'5abc'}).options.NVisibleItems);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:[]}).options.NVisibleItems);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:{}}).options.NVisibleItems);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:false}).options.NVisibleItems);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:true}).options.NVisibleItems);
    },
    testCheckingOptionsScrollStep: function() {
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:-1}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:0}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:2}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:[]}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:{}}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:false}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, scrollStep:true}).options.scrollStep);
        assertEquals(3, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:3, scrollStep:4}).options.scrollStep);
        assertEquals(1, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:3, scrollStep:'abc'}).options.scrollStep);
        assertEquals(2, buildCarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:3, scrollStep:'2abc'}).options.scrollStep);
    },
    testCheckingOptionsScrollDirection: function() {
        assertEquals('left', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: 'left'}).options.scrollDirection);
        assertEquals('right', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: 'right'}).options.scrollDirection);
        assertEquals('top', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: 'top'}).options.scrollDirection);
        assertEquals('bottom', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: 'bottom'}).options.scrollDirection);
        assertEquals('left', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: 'abc'}).options.scrollDirection);
        assertEquals('left', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: 5}).options.scrollDirection);
        assertEquals('left', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: {}}).options.scrollDirection);
        assertEquals('left', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: []}).options.scrollDirection);
        assertEquals('left', buildCarousel(this.carouselEl, {playlist:this.playlist, scrollDirection: true}).options.scrollDirection);
    },
    testCheckingOptionsFxDuration: function() {
        assertEquals(700, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:-1}}).options.fx.duration);
        assertEquals(0, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:0}}).options.fx.duration);
        assertEquals(2, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:2}}).options.fx.duration);
        assertEquals(700, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:[]}}).options.fx.duration);
        assertEquals(700, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:{}}}).options.fx.duration);
        assertEquals(700, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:false}}).options.fx.duration);
        assertEquals(700, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:true}}).options.fx.duration);
        assertEquals(700, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:'abc'}}).options.fx.duration);
        assertEquals(2, buildCarousel(this.carouselEl, {playlist:this.playlist, fx: {duration:'2abc'}}).options.fx.duration);
    },
    testCheckingOptionsAutoSelect: function() {
        assertEquals(true, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:true}).options.autoSelect);
        assertEquals(true, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:1}).options.autoSelect);
        assertEquals(true, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:'t'}).options.autoSelect);
        assertEquals(true, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:[]}).options.autoSelect);
        assertEquals(true, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:{}}).options.autoSelect);
        assertEquals(false, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:false}).options.autoSelect);
        assertEquals(false, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:0}).options.autoSelect);
        assertEquals(false, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:''}).options.autoSelect);
        assertEquals(false, buildCarousel(this.carouselEl, {playlist:this.playlist, autoSelect:null}).options.autoSelect);
    },
    testCheckingOptionsPlaylist: function() {
        this.playlist.items.inject($$('#carouselID .carousel_viewbox .playlist_local')[0]);

        assertEquals($$('.playlist_local')[0], buildCarousel(this.carouselEl, {playlist:7}).options.playlist.items[0].getParent());
    },
    testEventEnableScrolling: function() {
        var c = new ACarousel(this.carouselEl, {playlist:this.playlist});
        var ev = false;
        c.addEvent('enableScrolling', function() {
            ev = true;
        });
        c.build();

        assertTrue(ev);
    },
    testEventDisableScrolling: function() {
        var c = new ACarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:'all'});
        var ev = false;
        c.addEvent('disableScrolling', function() {
            ev = true;
        });
        c.build();

        assertTrue(ev);
    },
    testItemPosition: function(N) {
        var expected = [],
            carousel,
            itemWidth,
            n;

        N = N || this.playlist.NItems;

        // Prepare expected positions
        itemWidth = $('playlistID').getElement('.item').getSize().x;
        carousel = buildCarousel(this.carouselEl, {playlist: this.playlist, NVisibleItems:N});
        for (n = 0; n < carousel.options.NVisibleItems; n++)
            expected.push(n*itemWidth);
        expected.push(-itemWidth);

        for (n = 0; n < carousel.options.NVisibleItems; n++)
            assertEquals('"left" style value of item #' + n, expected[n], carousel.items[n].getStyle('left').toInt());
        for (; n < carousel.NItems; n++)
            assertEquals('"left" style value of item #' + n, expected[carousel.options.NVisibleItems], carousel.items[n].getStyle('left').toInt());

        if (N > 1)
            this.testItemPosition(--N);
    },
    testSelectItem: function() {
        var carousel = buildCarousel(this.carouselEl, {playlist: this.playlist, NVisibleItems:5});
        carousel.selectItem(3);
        assertFalse(carousel.items[0].hasClass('active'));
        assertTrue(carousel.items[3].hasClass('active'));
    },
    testEventSelectItem: function() {
        var SELECT_ITEM = 3;
        var c = new ACarousel(this.carouselEl, {playlist:this.playlist, NVisibleItems:'all'});
        var selected = 0;
        c.addEvent('selectItem', function(ev) {
            selected = ev;
        });
        c.build();
        c.selectItem(SELECT_ITEM);

        assertEquals(selected, SELECT_ITEM);
    },

    testCarouselWithoutPlaylist: function() {
        var NThrows = 0;
        try {
            new ACarousel(this.carouselEl);
        } catch (err) {
            assertEquals('Carousel can not be created without playlist.', err);
            NThrows++;
        }
        assertEquals(1, NThrows);
    },
    testExternalPlaylist: function() {
        var carousel = new ACarousel(this.carouselEl, {playlist: this.playlist});
        assertTrue(carousel.options.playlist.isExtern);
        assertNotEquals(this.playlist.items, carousel.items);
    },
    testInternalPlaylistImplicit: function() {
        this.playlist.items.inject($$('#carouselID .playlist_local')[0]);

        var carousel = new ACarousel(this.carouselEl);
        assertFalse(carousel.options.playlist.isExtern);
        assertEquals(this.playlist.items, carousel.items);
    },
    testInternalPlaylistExplicit: function() {
        this.playlist.items.inject($$('#carouselID .playlist_local')[0]);
        $$('#carouselID .playlist_local')[0].addClass('internalPlaylist');

        var carousel = new ACarousel(this.carouselEl, new CarouselPlaylist('.internalPlaylist'));
        assertFalse(carousel.options.playlist.isExtern);
        assertEquals(this.playlist.items, carousel.items);
    }
});
