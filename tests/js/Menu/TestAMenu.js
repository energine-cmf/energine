/**
 * @file Test the AMenu class.
 *
 * @author Valerii Zinchenko
 *
 * @version 1.0.0
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

new TestCase('AMenu initialisation', {
    setUp: function() {
        loadFixture('simpleMenu.html');

        this.menuEl = $$('.menu')[0];
    },
    testInit: function () {
        var menu = new AMenu('.menu');

        assertEquals(this.menuEl, menu.element);
        assertEquals(3, menu.items.length);
    },
    testThrows: function() {
        var expected = 1,
            NThrows = 0;

        NThrows <<= 1;
        try {
            new AMenu('.m');
        } catch (e) {
            assertEquals('Element for the menu is not found.', e);
            NThrows |= 1
        }

        assertEquals(expected, NThrows);
    }
});