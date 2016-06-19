var Select2_wrapper_jquery;
(function ($) {
    Select2_wrapper_jquery = function (el, url, dataFunction, responseFunction, resultFunction, selectionFunction) {
        var select;

        (select = $('select', el)).select2({
            theme: "classic",
            ajax: {
                "url": url,
                delay: Lookup.TIMEOUT_PERIOD,
                method: 'POST',
                processResults: responseFunction,
                data: dataFunction,
                dataType: 'json',
                cache: true
            },
            templateResult: resultFunction,
            templateSelection: selectionFunction,
            //language: select.data('language'),
            //maximumSelectionLength: 1,
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: Lookup.START_CHAR_COUNT
        });
    }
})(window.jQuery);
;