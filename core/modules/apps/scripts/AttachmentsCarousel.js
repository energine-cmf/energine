/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[AttachmentsCarousel]{@link AttachmentsCarousel}</li>
 * </ul>
 *
 * @requires share/Carousel
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */
ScriptLoader.load('Carousel');
/**
 * AttachmentsCarousel
 *
 *
 * @constructor
 * @param {string} toolbarName The name of the toolbar.
 */
var AttachmentsCarousel = new Class(/** @lends AttachmentsCarousel# */{
    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    // constructor
    initialize: function (Container) {
        this.element = $(Container);
        this.carousel = new Carousel(this.element, {

        });

    }
});