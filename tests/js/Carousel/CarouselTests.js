/**
 * @file Testing class Carousel from Carousel.js
 *
 * @author Valerii Zinchenko
 * @version 0.3
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

new TestCase('Carousel initialisation', {
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
    },
    testGetCarouselByID: function() {
        var NThrows = 0;
        assertEquals($('carouselID'), new Carousel('carouselID', {playlist: this.playlist}).carousel);
        try {
            new Carousel('carouselI', {playlist: this.playlist})
        } catch (err) {
            assertEquals('Element for Carousel was not found in DOM Tree!', err);
            NThrows++;
        }
        assertEquals(1, NThrows);
    },
    testGetCarouselByClass: function() {
        var NThrows = 0;
        assertEquals($('carouselID'), new Carousel('.carousel', {playlist: this.playlist}).carousel);
        try {
            new Carousel('.carouse', {playlist: this.playlist})
        } catch (err) {
            assertEquals('Element for Carousel was not found in DOM Tree!', err);
            NThrows++;
        }
        assertEquals(1, NThrows);
    },
    testAmountOfConstructorArgs: function() {
        var NThrows = 0;
        try {
            new Carousel();
        } catch (err) {
            assertEquals('Constructor of Carousel expected 1 or 2 arguments, but received 0!', err);
            NThrows++;
        }
        try {
            new Carousel(1, 2, 3);
        } catch (err) {
            assertEquals('Constructor of Carousel expected 1 or 2 arguments, but received 3!', err);
            NThrows++;
        }
        assertEquals(2, NThrows);
    },
    testCarouselWithoutPlaylist: function() {
        var NThrows = 0;
        try {
            new Carousel('.carousel');
        } catch (err) {
            assertEquals('Carousel can not be created without playlist.', err);
            NThrows++;
        }
        assertEquals(1, NThrows);
    },
    testExternalPlaylist: function() {
        var carousel = new Carousel('.carousel', {playlist: this.playlist});
        assertTrue(carousel.options.playlist.isExtern);
        assertNotEquals(this.playlist.items, carousel.items);
    },
    testInternalPlaylistImplicit: function() {
        this.playlist.items.inject($$('#carouselID .playlist_local')[0]);

        var carousel = new Carousel('.carousel');
        assertFalse(carousel.options.playlist.isExtern);
        assertEquals(this.playlist.items, carousel.items);
    },
    testInternalPlaylistExplicit: function() {
        this.playlist.items.inject($$('#carouselID .playlist_local')[0]);
        $$('#carouselID .playlist_local')[0].addClass('internalPlaylist');

        var carousel = new Carousel('.carousel', new CarouselPlaylist('.internalPlaylist'));
        assertFalse(carousel.options.playlist.isExtern);
        assertEquals(this.playlist.items, carousel.items);
    },
    testOptions: function() {
        // defaults
        var carousel = new Carousel('.carousel', {playlist: this.playlist});
        assertEquals(1, carousel.options.NVisibleItems);
        assertEquals(1, carousel.options.scrollStep);
        assertEquals('left', carousel.options.scrollDirection);
        // Undefined because of removing this object by constructor.
        assertUndefined(carousel.options.style);
        assertEquals('click', carousel.options.event);
        assertEquals(700, carousel.options.effectDuration);
        assertEquals(true, carousel.options.autoSelect);

        // NVisibleItems
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:-1}).options.NVisibleItems);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:0}).options.NVisibleItems);
        assertEquals(this.playlist.NItems, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:this.playlist.NItems+1}).options.NVisibleItems);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:'abc'}).options.NVisibleItems);
        assertEquals(5, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:'5abc'}).options.NVisibleItems);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:[]}).options.NVisibleItems);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:{}}).options.NVisibleItems);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:false}).options.NVisibleItems);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:true}).options.NVisibleItems);

        // scrollStep
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:-1}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:0}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:2}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:[]}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:{}}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:false}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, scrollStep:true}).options.scrollStep);
        assertEquals(3, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:3, scrollStep:4}).options.scrollStep);
        assertEquals(1, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:3, scrollStep:'abc'}).options.scrollStep);
        assertEquals(2, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:3, scrollStep:'2abc'}).options.scrollStep);

        // scrollDirection
        assertEquals('left', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: 'left'}).options.scrollDirection);
        assertEquals('right', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: 'right'}).options.scrollDirection);
        assertEquals('top', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: 'top'}).options.scrollDirection);
        assertEquals('bottom', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: 'bottom'}).options.scrollDirection);
        assertEquals('left', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: 'abc'}).options.scrollDirection);
        assertEquals('left', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: 5}).options.scrollDirection);
        assertEquals('left', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: {}}).options.scrollDirection);
        assertEquals('left', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: []}).options.scrollDirection);
        assertEquals('left', new Carousel('.carousel', {playlist:this.playlist, scrollDirection: true}).options.scrollDirection);

        // loop
        assertTrue(new Carousel('.carousel', {playlist:this.playlist}).options.loop);
        assertTrue(new Carousel('.carousel', {playlist:this.playlist, loop:true}).options.loop);
        assertTrue(new Carousel('.carousel', {playlist:this.playlist, loop:'q'}).options.loop);
        assertTrue(new Carousel('.carousel', {playlist:this.playlist, loop:-1}).options.loop);
        assertTrue(new Carousel('.carousel', {playlist:this.playlist, loop:[]}).options.loop);
        assertTrue(new Carousel('.carousel', {playlist:this.playlist, loop:{}}).options.loop);
        assertFalse(new Carousel('.carousel', {playlist:this.playlist, loop:false}).options.loop);
        assertFalse(new Carousel('.carousel', {playlist:this.playlist, loop:''}).options.loop);
        assertFalse(new Carousel('.carousel', {playlist:this.playlist, loop:0}).options.loop);

        // effectDuration
        assertEquals(700, new Carousel('.carousel', {playlist:this.playlist, effectDuration:-1}).options.effectDuration);
        assertEquals(0, new Carousel('.carousel', {playlist:this.playlist, effectDuration:0}).options.effectDuration);
        assertEquals(2, new Carousel('.carousel', {playlist:this.playlist, effectDuration:2}).options.effectDuration);
        assertEquals(700, new Carousel('.carousel', {playlist:this.playlist, effectDuration:[]}).options.effectDuration);
        assertEquals(700, new Carousel('.carousel', {playlist:this.playlist, effectDuration:{}}).options.effectDuration);
        assertEquals(700, new Carousel('.carousel', {playlist:this.playlist, effectDuration:false}).options.effectDuration);
        assertEquals(700, new Carousel('.carousel', {playlist:this.playlist, effectDuration:true}).options.effectDuration);
        assertEquals(700, new Carousel('.carousel', {playlist:this.playlist, effectDuration:'abc'}).options.effectDuration);
        assertEquals(2, new Carousel('.carousel', {playlist:this.playlist, effectDuration:'2abc'}).options.effectDuration);

        // event
        assertEquals('click', new Carousel('.carousel', {playlist:this.playlist, event:''}).options.event);
        assertEquals('linux', new Carousel('.carousel', {playlist:this.playlist, event:'linux'}).options.event);
        assertEquals('click', new Carousel('.carousel', {playlist:this.playlist, event:0}).options.event);
        assertEquals('click', new Carousel('.carousel', {playlist:this.playlist, event:false}).options.event);
        assertEquals('click', new Carousel('.carousel', {playlist:this.playlist, event:[]}).options.event);
        assertEquals('click', new Carousel('.carousel', {playlist:this.playlist, event:{}}).options.event);

        // autoSelect
        assertEquals(true, new Carousel('.carousel', {playlist:this.playlist, autoSelect:true}).options.autoSelect);
        assertEquals(true, new Carousel('.carousel', {playlist:this.playlist, autoSelect:1}).options.autoSelect);
        assertEquals(true, new Carousel('.carousel', {playlist:this.playlist, autoSelect:'t'}).options.autoSelect);
        assertEquals(true, new Carousel('.carousel', {playlist:this.playlist, autoSelect:[]}).options.autoSelect);
        assertEquals(true, new Carousel('.carousel', {playlist:this.playlist, autoSelect:{}}).options.autoSelect);
        assertEquals(false, new Carousel('.carousel', {playlist:this.playlist, autoSelect:false}).options.autoSelect);
        assertEquals(false, new Carousel('.carousel', {playlist:this.playlist, autoSelect:0}).options.autoSelect);
        assertEquals(false, new Carousel('.carousel', {playlist:this.playlist, autoSelect:''}).options.autoSelect);
        assertEquals(false, new Carousel('.carousel', {playlist:this.playlist, autoSelect:null}).options.autoSelect);

        // playlist
        this.playlist.items.inject($$('#carouselID .carousel_viewbox .playlist_local')[0]);
        carousel = new Carousel('.carousel', {playlist:7});
        assertEquals($$('.playlist_local')[0], carousel.options.playlist.items[0].getParent());
    },
    testButtons: function() {
        Object.each(new Carousel('.carousel', {NVisibleItems:1, playlist: this.playlist}).buttons, function(btn) {
            assertEquals('block', btn.button.getStyle('display'));
        });
        Object.each(new Carousel('.carousel', {NVisibleItems:7, playlist: this.playlist}).buttons, function(btn) {
            assertEquals('none', btn.button.getStyle('display'));
        });
    },
    testClonningItems: function() {
        assertEquals(this.playlist.NItems, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:4, scrollStep:3}).items.length);
        $$('.carousel .playlist_local .item').destroy();
        assertEquals(this.playlist.NItems*2, new Carousel('.carousel', {playlist:this.playlist, NVisibleItems:4, scrollStep:4}).items.length);
    },
    testItemPosition: function(N) {
        var expected = [],
            carousel,
            itemWidth,
            n;

        N = N || this.playlist.NItems;

        // Prepare expected positions
        itemWidth = $('playlistID').getElement('.item').getSize().x;
        carousel = new Carousel('.carousel', {playlist: this.playlist, NVisibleItems:N});
        for (n = 0; n < carousel.options.NVisibleItems; n++)
            expected.push(n*itemWidth);
        expected.push(-itemWidth);

        for (n = 0; n < carousel.options.NVisibleItems; n++)
            assertEquals('\"left\" style value of item #' + n, expected[n], carousel.items[n].getStyle('left').toInt());
        for (; n < carousel.NItems; n++)
            assertEquals('\"left\" style value of item #' + n, expected[carousel.options.NVisibleItems], carousel.items[n].getStyle('left').toInt());

        if (N > 1)
            this.testItemPosition(--N);
    },
    testSelectItem: function() {
        var carousel = new Carousel('.carousel', {playlist: this.playlist, NVisibleItems:5});
        carousel.selectItem(3);
        assertFalse(carousel.items[0].hasClass('active'));
        assertTrue(carousel.items[3].hasClass('active'));
    }
});

new AsyncTestCase('Scroll carousel', {
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
        this.itemWidth = item.getStyle('width').toInt();

        this.shortAnimation = 50;
        this.longAnimation = 1700;
    },
    testAutoSelect: function (queue) {
        var carousel = new Carousel('carouselID', {autoSelect:false, NVisibleItems:1, scrollStep:1, playlist:this.playlist, effectDuration:0});

        queue.call('scrollNext()', function(callbacks) {
            carousel.scrollNext();

            window.setTimeout(callbacks.add(function() {
                assertTrue(carousel.items[0].hasClass('active'));
            }), this.shortAnimation);
        });
    },
    testScrollStep1: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            carousel = new Carousel('carouselID', {NVisibleItems:1, scrollStep:1, playlist:this.playlist, effectDuration:0});

        queue.call('scrollNext()', function(callbacks) {
            carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < carousel.options.playlist.NItems; n++)
                expected[n] = -itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n+carousel.options.scrollStep] =  itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[carousel.options.scrollStep].hasClass('active'));
            }), this.shortAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < carousel.options.playlist.NItems; n++)
                expected[n] = -itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems + carousel.options.scrollStep; n++)
                expected[n] =  itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
            }), this.shortAnimation);
        });
    },
    testScrollStep2: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            carousel = new Carousel('carouselID', {NVisibleItems:4, scrollStep:3, playlist:this.playlist, effectDuration:0});

        queue.call('scrollNext()', function(callbacks) {
            carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < carousel.options.NVisibleItems + carousel.options.scrollStep; n++)
                expected[n] =  itemWidth * n - itemWidth*carousel.options.scrollStep;
            for (; n < carousel.options.playlist.NItems; n++)
                expected[n] = -itemWidth;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[carousel.options.scrollStep].hasClass('active'));
            }), this.shortAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < carousel.options.NVisibleItems + carousel.options.scrollStep; n++)
                expected[n] =  itemWidth * n;
            for (; n < carousel.options.playlist.NItems; n++)
                expected[n] = -itemWidth;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[carousel.options.scrollStep].hasClass('active'));
            }), this.shortAnimation);
        });
    },
    testScrollStep3: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            carousel = new Carousel('carouselID', {NVisibleItems:4, scrollStep:4, playlist:this.playlist, effectDuration:0});

        queue.call('scrollNext()', function(callbacks) {
            carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < carousel.options.NVisibleItems + carousel.options.scrollStep; n++)
                expected[n] = itemWidth * n - itemWidth*carousel.options.scrollStep;
            for (; n < carousel.options.playlist.NItems; n++)
                expected[n] = -itemWidth;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
                assertTrue(carousel.items[carousel.options.playlist.NItems].hasClass('active'));
            }), this.shortAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < carousel.options.NVisibleItems + carousel.options.scrollStep; n++)
                expected[n] = itemWidth * n;
            for (; n < carousel.options.playlist.NItems*2; n++)
                expected[n] = -itemWidth;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
                assertTrue(carousel.items[carousel.options.playlist.NItems].hasClass('active'));
            }), this.shortAnimation);
        });
    },
    testLoopOneDirection1: function(queue) {
        var NVisibleItems = 1,
            scrollStep = 1,
            NScrolls = this.playlist.NItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            assertItems = this.assertItems,
            n,
            expected = [],
            carousel;
        queue.call('scrollNext()', function(callbacks) {
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});
            for (n = 0; n < NScrolls; n++)
                carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = -itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n] =  itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});
            for (n = 0; n < NScrolls; n++)
                carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n] = itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
    },
    testLoopOneDirection2: function(queue) {
        var NVisibleItems = 4,
            scrollStep = 3,
            NScrolls = this.playlist.NItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            assertItems = this.assertItems,
            n,
            expected = [],
            carousel;
        queue.call('scrollNext()', function(callbacks) {
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});
            for (n = 0; n < NScrolls; n++)
                carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = -itemWidth * (playlist.NItems - n);
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n] =  itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});
            for (n = 0; n < NScrolls; n++)
                carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[3].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
    },
    testLoopOneDirection3: function(queue) {
        var NVisibleItems = 4,
            scrollStep = 3,
            NScrolls = 4,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            assertItems = this.assertItems,
            n,
            expected = [],
            carousel;

        queue.call('4*scrollNext()', function(callbacks) {
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});
            for (n = 0; n < NScrolls; n++)
                carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = itemWidth * (n +2 - playlist.NItems);
            for (n = 0; n < 2; n++)
                expected[n] = itemWidth * (n +2);
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[5].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
        queue.call('4*scrollPrevious()', function(callbacks) {
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});
            for (n = 0; n < NScrolls; n++)
                carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = itemWidth * (n -2);
            for (n = 0; n < 2; n++)
                expected[n] = itemWidth * (n + carousel.options.NVisibleItems+1);
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[5].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
    },
    testLoopTwoDirections1: function(queue) {
        var NVisibleItems = 4,
            scrollStep = 2,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            assertItems = this.assertItems,
            n,
            expected = [],
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});

        queue.call('scrollNext()', function(callbacks) {
            carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < scrollStep; n++)
                expected[n] =  itemWidth * (n - scrollStep);
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n+scrollStep] =  itemWidth * n;
            expected[6] = -itemWidth;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[carousel.options.scrollStep].hasClass('active'));
            }), this.longAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = itemWidth * n;
            expected[6] = -itemWidth;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[carousel.options.scrollStep].hasClass('active'));
            }), this.longAnimation);
        });
    },
    testLoopTwoDirections2: function(queue) {
        var NVisibleItems = 4,
            scrollStep = 2,
            NScrolls = 3,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            assertItems = this.assertItems,
            n,
            expected = [],
            carousel = new Carousel('carouselID', {effectDuration:0, playlist:playlist, NVisibleItems:NVisibleItems, scrollStep:scrollStep});

        queue.call('scrollNext()', function(callbacks) {
            for (n = 0; n < NScrolls; n++)
                carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < 3; n++)
                expected[n] = itemWidth * (n + 1);
            expected[3] = -100;
            for (n = 0; n < 3; n++)
                expected[n+4] = itemWidth * (n-2);
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[6].hasClass('active'));
            }), this.longAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < 4; n++)
                expected[n+3] = itemWidth * (n-1);
            for (n = 0; n < 3; n++)
                expected[n] = itemWidth * (n+3);
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[6].hasClass('active'));
            }), this.longAnimation);
        });
        queue.call('scrollPrevious()', function(callbacks) {
            carousel.scrollPrevious();

            // Prepare expected values
            for(n = 0; n < 2; n++)
                expected[n] = itemWidth * (5-n);
            for (n = 0; n < 5; n++)
                expected[n+2] = itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[5].hasClass('active'));
            }), this.longAnimation);
        });
    },
    testLoopFalseScrollLeft1: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            carousel;

        queue.call('scrollNext()', function(callbacks) {
            carousel = new Carousel('carouselID', {loop:false, NVisibleItems:1, scrollStep:1, effectDuration:0, playlist:playlist});
            for (n = 0; n < playlist.NItems+1; n++)
                carousel.scrollNext();

            // Prepare expected values
            for (n = 0; n < playlist.NItems-1; n++)
                expected[n] = -itemWidth;
                expected.push(0);
            window.setTimeout(callbacks.add(function() {
                assertEquals(0, carousel.$chain.length);
                assertFalse(carousel.buttons.next.isEnabled);
                assertItems(expected, carousel);
                assertTrue(carousel.items[6].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
    },
    testLoopFalseScrollLeft2: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            carousel = new Carousel('carouselID', {loop:false, NVisibleItems:4, scrollStep:2, effectDuration:0, playlist:playlist});

        queue.call('scrollNext()', function(callbacks) {
            for (n = 0; n < playlist.NItems+1; n++)
                carousel.scrollNext();

            // Prepare expected values
            expected[0] = -itemWidth*2;
            expected[1] = expected[2] = -itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n+3] = itemWidth * n;

            window.setTimeout(callbacks.add(function() {
                assertEquals(0, carousel.$chain.length);
                assertFalse(carousel.buttons.next.isEnabled);
                assertItems(expected, carousel);
                assertTrue(carousel.items[3].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
    },
    testLoopFalseScrollRight: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            carousel;

        queue.call('scrollPrevious()', function(callbacks) {
            carousel = new Carousel('carouselID', {loop:false, NVisibleItems:1, scrollStep:1, effectDuration:0, playlist:playlist});
            assertFalse(carousel.buttons.previous.isEnabled);
            carousel.scrollPrevious();

            // Prepare expected values
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = -itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n] =  itemWidth * n;
            window.setTimeout(callbacks.add(function() {
                assertItems(expected, carousel);
                assertTrue(carousel.items[0].hasClass('active'));
                carousel.items.destroy();
            }), this.longAnimation);
        });
    },
    testLoopFalseScrollLeftRight1: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            carousel = new Carousel('carouselID', {loop:false, NVisibleItems:1, scrollStep:1, effectDuration:0, playlist:playlist});;

        queue.call('scrollNext() then scrollPrevious()', function(callbacks) {
            for (n = 0; n < playlist.NItems; n++)
                expected[n] = itemWidth;
            for (n = 0; n < carousel.options.NVisibleItems; n++)
                expected[n] =  itemWidth * n;

            for (n = 0; n < playlist.NItems+1; n++)
                carousel.scrollNext();
            window.setTimeout(callbacks.add(function() {
                assertFalse(carousel.buttons.next.isEnabled);
                assertTrue(carousel.buttons.previous.isEnabled);
                assertTrue(carousel.items[6].hasClass('active'));

                for (n = 0; n < playlist.NItems+1; n++)
                    carousel.scrollPrevious();
                window.setTimeout(callbacks.add(function() {
                    assertItems(expected, carousel);
                    assertTrue(carousel.buttons.next.isEnabled);
                    assertFalse(carousel.buttons.previous.isEnabled);
                    assertTrue(carousel.items[0].hasClass('active'));
                }), this.longAnimation);
            }), this.longAnimation);
        });
    },
    testLoopFalseScrollLeftRight2: function(queue) {
        var n,
            expected = [],
            assertItems = this.assertItems,
            itemWidth = this.itemWidth,
            playlist = this.playlist,
            carousel = new Carousel('carouselID', {loop:false, NVisibleItems:4, scrollStep:2, effectDuration:0, playlist:playlist});

        queue.call('scrollNext() then scrollPrevious()', function(callbacks) {
            for (n = 0; n < playlist.NItems+1; n++)
                carousel.scrollNext();
            window.setTimeout(callbacks.add(function() {
                assertFalse(carousel.buttons.next.isEnabled);
                assertTrue(carousel.buttons.previous.isEnabled);
                assertTrue(carousel.items[3].hasClass('active'));

                for (n = 0; n < playlist.NItems; n++)
                    expected[n] = itemWidth * n;
                expected[0] = -itemWidth*2;

                carousel.scrollPrevious();
                window.setTimeout(callbacks.add(function() {
                    assertTrue(carousel.buttons.next.isEnabled);
                    assertTrue(carousel.buttons.previous.isEnabled);
                    assertTrue(carousel.items[3].hasClass('active'));
                    assertItems(expected, carousel);

                    for (n = 0; n < playlist.NItems; n++)
                        expected[n] = itemWidth * (n-3);
                    expected[0] = -itemWidth*2;

                    carousel.scrollNext();
                    window.setTimeout(callbacks.add(function() {
                        assertFalse(carousel.buttons.next.isEnabled);
                        assertItems(expected, carousel);
                        assertTrue(carousel.items[3].hasClass('active'));

                        for (n = 0; n < carousel.options.NVisibleItems; n++)
                            expected[n] = itemWidth * n;
                        expected[4] = expected[5] = itemWidth * 5;
                        expected[4] = itemWidth * 6;

                        for (n = 0; n < playlist.NItems+1; n++)
                            carousel.scrollPrevious();
                        window.setTimeout(callbacks.add(function() {
                            assertTrue(carousel.buttons.next.isEnabled);
                            assertFalse(carousel.buttons.previous.isEnabled);
                            assertItems(expected, carousel);
                            assertTrue(carousel.items[3].hasClass('active'));
                        }), this.longAnimation);
                    }), this.longAnimation);
                }), this.longAnimation);
            }), this.longAnimation);
        });
    },

// helper functions:
    assertItems: function(expected, carousel) {
        for (var n = 0; n < carousel.NItems; n++)
            assertEquals('item #' + n + ' \"left\" style value', expected[n], carousel.items[n].getStyle('left').toInt());
    }
});