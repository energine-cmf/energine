ScriptLoader.load('scripts/jquery.nouislider.all.js');
Asset.css('jquery.nouislider.min.css');
var ProductFilter;
(function ($, window, document) {

    ProductFilter = function (el) {

        $('.range', document.id(el)).each(function (idx, el) {
            var el = $(el).prop('slide', null);

            el.noUiSlider({
                start: [el.data('start') || 0, el.data('end') || 0],
                connect: true,
                step: el.data('step'),
                range: {
                    'min': [el.data('min') || 0],
                    'max': [el.data('max') || 0]
                }
            });

            el.Link('lower').to(jQuery('.lower', el));
            el.Link('upper').to(jQuery('.upper', el));
        });

    }
}(window.jQuery, window, document));